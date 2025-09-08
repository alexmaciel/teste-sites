<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Staff_model extends Api_Model
{
    public function getAll()
	{
        $this->db->select('
            staffid,
            firstname,
            lastname,
            CONCAT(firstname,\' \',lastname) as fullname,
            email,
            phone,
            altphone,
            address,
            website,
            avatar,
            admin,
            role,
            datecreated,
            username,
            default_language,
            token,
            active                         
        ');     
        $this->db->order_by('staffid', 'desc');        

        return $this->db->get(db_prefix() . 'staff')->result();
    }

    /**
     * Get admin member/s
     * @param  mixed $id Optional - admin id
     * @param  mixed $where where in query
     * @return mixed if id is passed return object else array
     */
    public function get($id = '', $active = '', $where = array())
    {
        $select_str = '*,CONCAT(firstname,\' \',lastname) as fullname';

        $this->db->select($select_str);
        $this->db->where($where);

        if (is_int($active)) {
            $this->db->where('active', $active);
        }   

        if (is_numeric($id)) {
            $this->db->where('staffid', $id); 
            $staff = $this->db->get(db_prefix() . 'staff')->row();

            return $staff;
        }

        $this->db->order_by('firstname', 'desc');

        return $this->db->get(db_prefix() . 'staff')->result();
    }  

    /**
     * Add new staff member
     * @param array $data staff $_POST data
     */    
	public function add($data)
	{
        // First check for all cases if the email exists.
        $data = hooks()->apply_filters('before_create_staff_member', $data);        

        $data['admin'] = 1;

        if (is_admin()) {
            if (isset($data['administrator'])) {
                $data['admin'] = 0;
                unset($data['administrator']);
            }
        }

        $send_welcome_email = true;
        $original_password  = $data['password'];
        if (!isset($data['send_welcome_email'])) {
            $send_welcome_email = false;
        } else {
            unset($data['send_welcome_email']);
        }
                  
        $data['password']    = app_hash_password($data['password']);
        $data['datecreated'] = date('Y-m-d H:i:s');        

        $data['token'] = md5(uniqid('token'));

        $this->db->insert('staff', $data);
		$staffid = $this->db->insert_id();
        if ($staffid) {
            $slug = $data['firstname'] . ' ' . $data['lastname'];

            if ($slug == ' ') {
                $slug = 'unknown-' . $staffid;
            }       
            
            if ($send_welcome_email) {
                $this->load->model('emails_model');
                $api_merge_fields = new api_merge_fields();
                
                $merge_fields = array();
                $merge_fields = array_merge($merge_fields, $api_merge_fields->get_staff_merge_fields($staffid, $original_password));             
                $this->emails_model->send_email_template('new-staff-created', $data['email'], $merge_fields);
            }    
            logActivity('New Staff Member Added [ID: ' . $staffid . ', ' . $data['firstname'] . ' ' . $data['lastname'] . ']', 'insert');
            hooks()->do_action('staff_member_created', $staffid);

            return $staffid;
        }

        return false;        
	}    

    /**
     * Update staff member info
     * @param  array $data staff data
     * @param  mixed $id   staff id
     * @return boolean
     */    
    public function update($id, $data)
	{  
        $data = hooks()->apply_filters('before_update_staff_member', $data, $id);

        if (is_admin()) {
            if (isset($data['admin'])) {
                $data['admin'] = 1;
                unset($data['admin']);
            } else {
                $data['admin'] = 0;
            }
        }

        $affectedRows = 0;

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = app_hash_password($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }

		$this->db->where('staffid', $id);
		$this->db->update(db_prefix() . 'staff', $data); 
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            logActivity('Staff Member Updated [ID: ' . $id . ', ' . $data['firstname'] . ' ' . $data['lastname'] . ']', 'update');
            hooks()->do_action('staff_member_updated', $id);

            return true;            
        }

        return false;          
    }   
    
	public function delete($id)
	{
        hooks()->do_action('before_delete_staff_member', ['id' => $id,]);
        $name = get_staff_full_name($id);

        $this->db->where('staffid', $id);
        $file = $this->db->get(db_prefix() . 'staff')->row();
        if ($file) {
            $this->deleteAvatar($file->staffid);
        }

		$this->db->where('staffid', $id);
		$this->db->delete(db_prefix() . 'staff');

        if ($this->db->affected_rows() > 0) {
            //$this->db->where('staffid', $id);
            //$this->db->delete('assignedprojects');

            logActivity('Staff Member Deleted  [Name: ' . $name . ']', 'deleted');
            hooks()->do_action('staff_member_deleted', ['id' => $id]);

            return true;
        }

        return false;
	}     
    
	public function upload($id, $data)
	{
		$this->db->where('staffid', $id);
		$this->db->update(db_prefix() . 'staff', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;        
	}    

	public function deleteAvatar($id)
	{
        $this->db->where('staffid', $id);
        $file = $this->db->get(db_prefix() . 'staff')->row();
        if ($file) {
            $path = get_upload_path_by_type('staff') . $file->staffid . '/'; 
            $fullPath = $path.$file->avatar; 
            if (file_exists($fullPath)) {
                @unlink($fullPath);
                $fname     = pathinfo($fullPath, PATHINFO_FILENAME);
                $fext      = pathinfo($fullPath, PATHINFO_EXTENSION);
                $thumbPath_small = $path . 'small_' . $fname  . '.' . $fext;
                $thumbPath_thumb = $path . 'thumb_' . $fname  . '.' . $fext;

                if (file_exists($thumbPath_small)) {
                    @unlink($thumbPath_small);
                }  
                
                if (file_exists($thumbPath_thumb)) {
                    @unlink($thumbPath_thumb);
                }                  
            } 
            
            $this->db->where('staffid', $id);
            $this->db->update('staff', array(
                'avatar' => null
            )); 
            
            
            if (is_dir(get_upload_path_by_type('staff') . $file->staffid)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('staff') . $file->staffid);
                if (count($other_attachments) > 0) {
                    delete_dir(get_upload_path_by_type('staff') . $file->staffid);
                }
            }     

            return true;
        }
        
        return false;
	}   
 
    /**
     * Change staff passwordn
     * @param  mixed $data   password data
     * @param  mixed $userid staff id
     * @return mixed
     */  
	public function change_password($id, $password)
	{   
        $password = app_hash_password($password);

		$this->db->where('staffid', $id);
		$this->db->update(db_prefix() . 'staff', array(
            'password' => $password,
            'last_password_change' => date('Y-m-d H:i:s'),
        )); 
        if ($this->db->affected_rows() > 0) {
            logActivity('Staff Password Changed [Admin: ' . get_staff_full_name($id) . ']', 'edit', $id);

            return true;
        }       
	}   
    
    /**
     * Get staff permissions
     * @param  mixed $id staff id
     * @return array
     */
    public function get_staff_permissions($id)
    {
        // Fix for version 2.3.1 tables upgrade
        if (defined('DOING_DATABASE_UPGRADE')) {
            return array();
        }

        $permissions = $this->api_object_cache->get('staff-' . $id . '-permissions');

        if (!$permissions && !is_array($permissions)) {
            $this->db->where('staff_id', $id);
            $permissions = $this->db->get('staff_permissions')->result_array();

            $this->api_object_cache->add('staff-' . $id . '-permissions', $permissions);
        }

        return $permissions;
    }    
}