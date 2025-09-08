<?php
defined('BASEPATH') OR exit('No direct script access allowed');


function getLast()
{
    $CI = &get_instance();
    $CI->db->select('id');
    $CI->db->order_by('id', 'desc');
    $CI->db->limit(1);

    return $CI->db->get(db_prefix() . 'activity_log')->row();
}

/**
 * Count total rows on table based on params
 * @param  string $table Table from where to count
 * @param  array  $where
 * @return mixed  Total rows
 */
function total_rows($table, $where = array())
{
    $CI =& get_instance();
    if (is_array($where)) {
        if (sizeof($where) > 0) {
            $CI->db->where($where);
        }
    } elseif (strlen($where) > 0) {
        $CI->db->where($where);
    }

    return $CI->db->count_all_results($table);
}
/**
 * Log Activity for everything
 * @param  string $description Activity Description
 * @param  string $action Activity Action
 * @param  integer $staffid    Who done this activity
 */
function logActivity($description, $action = '', $staffid = '')
{
    $CI =& get_instance();
    $log = array(
        'action' => $action,
        'description' => $description,
        'date' => date('Y-m-d H:i:s')
	);
    if ($staffid != '' && is_numeric($staffid)) {
        $log['admin'] = get_staff_full_name($staffid);
        $log['staffid'] = $staffid;
    } else {
        $log['staffid'] = '[CRON]';        
    }

    $CI->db->insert(db_prefix() . 'activity_log', $log);
}
/**
 * Prefix all columns from table with the table name
 * Used for select statements eq db_prefix().'clients.company'
 * @param  string $table table name
 * @param  bool $string if true, a string will be returned otherwise array
 * @param  array $exclude exclude fields from prefixing
 * @return array|string
 */
function prefixed_table_fields_array($table, $string = false, $exclude = [])
{
    $CI     = & get_instance();
    $fields = $CI->db->list_fields($table);

    foreach ($exclude as $field) {
        if (in_array($field, $fields)) {
            unset($fields[array_search($field, $fields)]);
        }
    }

    $fields = array_values($fields);

    $i = 0;
    foreach ($fields as $f) {
        $fields[$i] = $table . '.' . $f;
        $i++;
    }

    return $string == false ? $fields : implode(',', $fields);
}
/**
 * Return last system activity id
 * @return mixed
 */
function get_last_system_activity_id()
{
    return getLast();
}
/**
 * Add user notifications
 * @param array $values array of values [description,fromuserid,touserid,fromcompany,isread]
 */
function add_notification($values)
{
    $CI = & get_instance();
    foreach ($values as $key => $value) {
        $data[$key] = $value;
    }
    if (is_client_logged_in()) {
        $data['fromuserid']    = 0;
        $data['fromclientid']  = get_contact_user_id();
        $data['from_fullname'] = get_contact_full_name(get_contact_user_id());
    } else {
        $data['fromuserid']    = get_staff_user_id();
        $data['fromclientid']  = 0;
        $data['from_fullname'] = get_staff_full_name(get_staff_user_id());
    }

    if (isset($data['fromcompany'])) {
        $data['fromuserid']    = 0;
        $data['from_fullname'] = '';
    }

    $data['date'] = date('Y-m-d H:i:s');
    $data         = hooks()->apply_filters('notification_data', $data);

    // Prevent sending notification to non active users.
    if (isset($data['touserid']) && $data['touserid'] != 0) {
        $CI->db->where('staffid', $data['touserid']);
        $user = $CI->db->get(db_prefix() . 'staff')->row();
        if (!$user || $user && $user->active == 0) {
            return false;
        }
    }

    $CI->db->insert(db_prefix() . 'notifications', $data);

    if ($notification_id = $CI->db->insert_id()) {
        hooks()->do_action('notification_created', $notification_id);
    }

    return true;
}
/**
 * Triggers
 * @param  array  $users id of users to receive notifications
 * @return null
 */
function pusher_trigger_notification($users = [])
{
    if (get_option('pusher_realtime_notifications') == 0) {
        return false;
    }

    if (!is_array($users) || count($users) == 0) {
        return false;
    }

    $channels = [];
    foreach ($users as $id) {
        array_push($channels, 'notifications-channel-' . $id);
    }

    $channels = array_unique($channels);

    $CI = &get_instance();

    $CI->load->library('app_pusher');

    $CI->app_pusher->trigger($channels, 'notification', []);
}