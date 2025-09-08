<?php
defined('BASEPATH') or exit('No direct script access allowed');


/**
 * Get staff full name
 * @param  string $userid Optional
 * @return string Firstname and Lastname
 */
function get_staff_full_name($userid = '')
{
    $tmpStaffUserId = get_staff_user_id();
    if ($userid == '' || $userid == $tmpStaffUserId) {
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->firstname . ' ' . $GLOBALS['current_user']->lastname;
        }
        $userid = $tmpStaffUserId;
    }

    $CI = & get_instance();

    $staff = $CI->api_object_cache->get('staff-full-name-data-' . $userid);

    if (!$staff) {
        $CI->db->where('staffid', $userid);
        $staff = $CI->db->select('firstname,lastname')->from(db_prefix() . 'staff')->get()->row();
        $CI->api_object_cache->add('staff-full-name-data-' . $userid, $staff);
    }

    return html_escape($staff ? $staff->firstname . ' ' . $staff->lastname : '');
}
/**
 * Return staff profile image url
 * @param  mixed $staff_id
 * @param  string $type
 * @return string
 */
function staff_profile_image_url($staff_id, $type = '')
{
    $url  = '';
    $CI   = &get_instance();
    $path = $CI->api_object_cache->get('staff-profile-image-path-' . $staff_id);

    if (!$path) {
        $CI->api_object_cache->add('staff-profile-image-path-' . $staff_id, $url);

        $CI->db->select('avatar');
        $CI->db->from(db_prefix() . 'staff');
        $CI->db->where('staffid', $staff_id);
        $user = $CI->db->get()->row();

        if ($user && !empty($user->avatar)) {
            $path = 'api/uploads/staff/' . $staff_id . '/' . $type . '_' . $user->avatar;
            $CI->api_object_cache->set('staff-profile-image-path-' . $staff_id, $path);
        }
    }

    if ($path) {
        $url = base_url($path);
    }

    return $url;
}
/**
 * Get staff default language
 * @param  mixed $staffid
 * @return mixed
 */
function get_staff_default_language($staffid = '')
{
    if (!is_numeric($staffid)) {
        // checking for current user if is admin
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->default_language;
        }

        //$staffid = $staffid;
    }
    $CI = & get_instance();
    $CI->db->select('default_language');
    $CI->db->from(db_prefix() . 'staff');
    $CI->db->where('staffid', $staffid);
    $staff = $CI->db->get()->row();
    if ($staff) {
        return $staff->default_language;
    }

    return '';
}