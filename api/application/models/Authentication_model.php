<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Authentication_model extends Api_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_autologin');
        $this->autologin();
    }

    /**
     * @param  string Email address for login
     * @param  string User Password
     * @param  boolean Set cookies for user if remember me is checked
     * @param  boolean Is User Or Client
     * @return boolean if not redirect url found, if found redirect to the url
     */
    public function login($email, $password, $remember, $staff)
    {
        if ((!empty($email)) and (!empty($password))) {
            $table  = db_prefix() . 'users';
            $_id    = 'userid';

            if ($staff == true) {
                $table  = db_prefix() . 'staff';
                $_id    = 'staffid';
            }

            $this->db->where('email', $email);
            $user = $this->db->get($table)->row();
            
            if($user) {
                // Email is okey lets check the password now
                if (!app_hasher()->CheckPassword($password, $user->password)) {
                    hooks()->do_action('failed_login_attempt', [
                        'user' => $user,
                        'is_staff_member' => $staff,
                    ]);                    
                    logActivity('Failed Login Attempt [Email:' . $email . ', Is Staff Member:' . ($staff == true ? 'Yes' : 'No')  . $this->input->ip_address() . ']', 'failed', $user->$_id);
                    
                    // Password failed, return
                    return false;
                } 
                if ($user->active == 0) {
                    hooks()->do_action('inactive_user_login_attempt', [
                        'user'            => $user,
                        'is_staff_member' => $staff,
                    ]);                    
                    logActivity('Inactive User Tried to Login [Email:' . $email . ', Is Staff Member:' . ($staff == true ? 'Yes' : 'No')  . $this->input->ip_address() . ']', 'failed', $user->$_id);
                    
                    return array(
                        'memberinactive' => true
                    ); 
                    
                    return false;
                }

                $twoFactorAuth = false;
                if ($staff == true) {
                    $twoFactorAuth = $user->two_factor_auth_enabled == 0 ? false : true;
    
                    if (!$twoFactorAuth) {                
                        hooks()->do_action('before_staff_login', array(
                            'email' => $email,
                            'userid' => $user->$_id
                        ));
                        $user_data = array(
                            'staff_user_id' => $user->$_id,
                            'staff_logged_in' => true
                        );     
                    }     
                } else {
                    hooks()->do_action('before_client_login', [
                        'email'           => $email,
                        'userid'          => $user->userid,
                        'contact_user_id' => $user->$_id,
                    ]);   
                    $user_data = array(
                        'client_user_id'   => $user->userid,
                        'contact_user_id'  => $user->$_id,
                        'client_logged_in' => true,
                    );                     
                }
                $this->session->set_userdata($user_data);

                if (!$twoFactorAuth) {
                    if ($remember) {
                        $this->create_token($user->$_id, $staff);
                    }
                    $this->update_login_info($user->$_id, $staff);
                }

                return $user;

            } else {
                hooks()->do_action('non_existent_user_login_attempt', [
                    'email'           => $email,
                    'is_staff_member' => $staff,
                ]);                
                logActivity('Non Existing User Tried to Login [Email:' . $email . ', Is Staff Member:' . ($staff == true ? 'Yes' : 'No')  . $this->input->ip_address() .  ']', 'failed', $user->$_id);

                return false;
            }
        } 
        
        return false;     
    }

    /**
     * @param  integer ID
     * @param  boolean Is Client or Staff
     * @return none
     * Update login info on autologin
     */
    private function update_login_info($user_id, $staff)
    {
        $table = db_prefix() . 'users';
        $_id   = 'userid';
        if ($staff == true) {
            $table = db_prefix() . 'staff';
            $_id   = 'staffid';
        }
        $this->db->set('last_ip', $this->input->ip_address());
        $this->db->set('last_login', date('Y-m-d H:i:s'));
        $this->db->where($_id, $user_id);
        $this->db->update($table);

        logActivity('User Successfully Logged In [User Id: ' . $user_id . ', Is Staff Member: ' . ($staff == true ? 'Yes' : 'No') . ']', 'update');
    }    

    /**
     * @param  boolean If Client or Admin
     * @return none
     */
    public function logout($staff = true)
    {
        $this->delete_token($staff);
        $this->delete_autologin($staff);

        if (is_client_logged_in()) {
            hooks()->do_action('before_admin_logout', get_admin_id());

            $this->session->unset_userdata('client_user_id');
            $this->session->unset_userdata('client_logged_in');
        } else {
            hooks()->do_action('before_staff_logout', get_staff_user_id());

            $this->session->unset_userdata('staff_user_id');
            $this->session->unset_userdata('staff_logged_in');
        }

        $this->session->sess_destroy();

        return true;
    } 

    /**
     * @param  integer ID to create autologin
     * @param  boolean Is Client or Staff
     * @return boolean
     */
    private function create_token($user_id, $staff)
    {
        $this->load->helper('cookie');
        $key = substr(md5(uniqid(rand())), 0, 16);
        $this->user_autologin->delete($user_id, $key, $staff);
        if ($this->user_autologin->set($user_id, md5($key), $staff)) {
            set_cookie([
                'name'  => 'autologin',
                'value' => serialize([
                    'user_id' => $user_id,
                    'key'     => $key,
                ]),
                'expire' => 60 * 60 * 24 * 31 * 2, // 2 months
            ]);

            return true;
        }

        return false;
    }  

    /**
     * @param  boolean Is Client or Staff
     * @return none
     */
    private function delete_token($staff)
    {
        $this->load->helper('cookie');
        if ($cookie = get_cookie('autologin', true)) {
            $data = unserialize($cookie);
            $this->user_autologin->delete($data['user_id'], md5($data['key']), $staff);
            delete_cookie('autologin', 'aal');
        }
    }    
    
    /**
     * @return boolean
     * Check if autologin found
     */
    public function autologin()
    {
        if (!is_logged_in()) {
            $this->load->helper('cookie');
            if ($cookie = get_cookie('autologin', true)) {
                $data = unserialize($cookie);
                if (isset($data['key']) and isset($data['user_id'])) {
                    if (!is_null($user = $this->user_autologin->get($data['user_id'], $data['key']))) {
                        // Login user
                        if ($user->staff == 1) {
                            $user_data = [
                                'staff_user_id'   => $user->id,
                                'staff_logged_in' => true,
                            ];
                        } else {
                            // Get the customer id
                            $this->db->select('userid');
                            $this->db->where('id', $user->id);
                            $contact = $this->db->get(db_prefix() . 'users')->row();

                            $user_data = [
                                'client_user_id'   => $contact->userid,
                                'contact_user_id'  => $user->id,
                                'client_logged_in' => true,
                            ];
                        }
                        $this->session->set_userdata($user_data);
                        // Renew users cookie to prevent it from expiring
                        set_cookie([
                            'name'   => 'autologin',
                            'value'  => $cookie,
                            'expire' => 60 * 60 * 24 * 31 * 2, // 2 months
                        ]);
                        $this->update_login_info($user->id, $user->staff);

                        return true;
                    }
                }
            }            
        }

        return false;
    }    

    /**
     * @param  boolean Is Client or Staff
     * @return none
     */
    private function delete_autologin($staff)
    {
        $this->load->helper('cookie');
        if ($cookie = get_cookie('autologin', true)) {
            $data = unserialize($cookie);
            $this->user_autologin->delete($data['user_id'], md5($data['key']), $staff);
            delete_cookie('autologin', 'aal');
        }
    }
        
    public function staff($id = '', $staff,  $where = array())
    {
        $table = db_prefix() . 'users';
        $_id   = 'userid';

        if ($staff == true) {
            $table = db_prefix() . 'staff';
            $_id   = 'staffid';
        }

        $this->db->select('*');
        $this->db->where($where);
        $this->db->where($_id, $id); 
        
        return $this->db->get($table)->row();
    }    

	public function check_token($token, $staff)
	{
        $table = db_prefix() . 'users';
        if ($staff == true) {
            $table = db_prefix() . 'staff';
        }
                
		$this->db->where('token', $token);
		$token = $this->db->get($table)->row();

		if($token) {
			return $token;
		}

		return false;
	}  
    
    /**
     * @param  string Email from the user
     * @param  Is Client or Staff
     * @return boolean
     * Generate new password key for the user to reset the password.
     */
	public function forgot_password($email, $staff)
	{
        $table  = db_prefix() . 'users';
        $_id    = 'userid';
        if ($staff == true) {
            $table = db_prefix() . 'staff';
            $_id   = 'staffid';            
        }  
        $this->db->where('email', $email);
        $user = $this->db->get($table)->row();

        if ($user) {
            if ($user->active == 0) {
                logActivity('Inactive User Tried Password Reset [Email: ' . $email . ', Is Staff Member: ' . ($staff == true ? 'Yes' : 'No') . ', IP: ' . $this->input->ip_address() . ']', 'failed');

                return array(
                    'memberinactive' => true
                );
            }

            $new_pass_key = generate_auth_key();
            $this->db->where($_id, $user->$_id);
            $this->db->update($table, array(
                'new_pass_key'           => $new_pass_key,
                'new_pass_key_requested' => date('Y-m-d H:i:s'),
            ));
            
            if ($this->db->affected_rows() > 0) {
                $this->load->model('emails_model');
                $data['new_pass_key'] = $new_pass_key;
                $data['staff']        = $staff;
                $data['userid']       = $user->$_id;
                $merge_fields         = array();
                
                $api_merge_fields = new api_merge_fields();
                
                if ($staff == false) {
                    $template     = 'contact-forgot-password';
                    $merge_fields = array_merge($merge_fields, $api_merge_fields->get_client_contact_merge_fields($user->userid, $user->$_id));
                } else {
                    $template     = 'staff-forgot-password';
                    $merge_fields = array_merge($merge_fields, $api_merge_fields->get_staff_merge_fields($user->$_id));
                }
                $merge_fields       = array_merge($merge_fields, $api_merge_fields->get_password_merge_field($data, $staff, 'forgot'));
                $send               = $this->emails_model->send_email_template($template, $user->email, $merge_fields);
                               
                if ($send) {
                    return true;
                }         
                
                return false;                
            }
            
            return false;                
        }
        log_activity('Non Existing User Tried Password Reset [Email: ' . $email . ', Is Staff Member: ' . ($staff == true ? 'Yes' : 'No') . ']', 'failed');

        return false;        
	} 
    
    /**
     * @param  boolean Is Client or Staff
     * @param  integer ID
     * @param  string
     * @param  string
     * @return boolean
     * User new password after successful validation of the key
     */
    public function new_password($staff, $new_pass_key, $password)
    {
        if (!$this->can_reset_password($staff, $new_pass_key)) {
            return array(
                'expired' => true,
            );
        }
        $password = app_hash_password($password);
        $table    = db_prefix() . 'users';
        $_id      = 'userid';
        if ($staff == true) {
            $table = db_prefix() . 'staff';
            $_id   = 'staffid';
        }   

        //$this->db->where($_id, $userid);
        $this->db->where('new_pass_key', $new_pass_key);
        $this->db->update($table, array(
            'password' => $password,
        ));           
        if ($this->db->affected_rows() > 0) {
            logActivity('User Reseted Password [User ID: ' . $_id . ', Is Staff Member: ' . ($staff == true ? 'Yes' : 'No') . ', IP: ' . $this->input->ip_address() . ']', 'update');
            
            $this->db->set('new_pass_key', null);
            $this->db->set('new_pass_key_requested', null);
            $this->db->set('last_password_change', date('Y-m-d H:i:s'));
            //$this->db->where($_id, $userid);
            $this->db->where('new_pass_key', $new_pass_key);
            $this->db->update($table);
            //$this->db->where($_id, $userid);
            $user = $this->db->get($table)->row();

            return true;
        }  
        
        return null;
    }    

    /**
     * Send set password email
     * @param string $email
     * @param boolean $staff is staff of contact
     */
    public function set_password_email($email, $staff)
    {
        $table = db_prefix() . 'users';
        $_id   = 'userid';
        if ($staff == true) {
            $table = db_prefix() . 'staff';
            $_id   = 'staffid';
        }

        $this->db->where('email', $email);
        $user = $this->db->get($table)->row();  
        
        if ($user) {
            if ($user->active == 0) {
                return [
                    'memberinactive' => true,
                ];
            }

            $new_pass_key = app_generate_hash();
            $this->db->where($_id, $user->$_id);
            $this->db->update($table, [
                'new_pass_key'           => $new_pass_key,
                'new_pass_key_requested' => date('Y-m-d H:i:s'),
            ]); 
            if ($this->db->affected_rows() > 0) {
                $this->load->model('emails_model');
                $data['new_pass_key'] = $new_pass_key;
                $data['staff']        = $staff;
                $data['userid']       = $user->$_id;
                $data['email']        = $email;

                $merge_fields = array();
                $api_merge_fields = new api_merge_fields();

                if ($staff == false) {
                    $merge_fields = array_merge($merge_fields, $api_merge_fields->get_client_contact_merge_fields($user->userid, $user->$_id));
                } else {
                    $merge_fields = array_merge($merge_fields, $api_merge_fields->get_staff_merge_fields($user->$_id));
                }
                $merge_fields = array_merge($merge_fields, $api_merge_fields->get_password_merge_field($data, $staff, 'set'));
                $send         = $this->emails_model->send_email_template('contact-set-password', $user->email, $merge_fields);

                if ($send) {
                    return true;
                }

                return false;                
            } 

            return false;                      
        }   
        
        return false;
    }    

    /**
     * Update user password from forgot password feature or set password
     * @param boolean $staff        is staff or contact
     * @param mixed $userid
     * @param string $new_pass_key the password generate key
     * @param string $password     new password
     */
    public function set_password($staff, $userid, $new_pass_key, $password)
    {
        if (!$this->can_set_password($staff, $userid, $new_pass_key)) {
            return [
                'expired' => true,
            ];
        } 

        $password = app_hash_password($password);
        $table    = 'users';
        $_id      = 'id';
        if ($staff == true) {
            $table = 'staff';
            $_id   = 'staffid';
        }      
        $this->db->where($_id, $userid);
        $this->db->where('new_pass_key', $new_pass_key);
        $this->db->update($table, [
            'password' => $password,
        ]);          
        
        if ($this->db->affected_rows() > 0) {
            logActivity('User Set Password [User ID: ' . $userid . ', Is Staff Member: ' . ($staff == true ? 'Yes' : 'No') . ']', 'update');

            $this->db->set('new_pass_key', null);
            $this->db->set('new_pass_key_requested', null);
            $this->db->set('last_password_change', date('Y-m-d H:i:s'));
            $this->db->where($_id, $userid);
            $this->db->where('new_pass_key', $new_pass_key);
            $this->db->update($table);

            return true;
        }
        
        return false;
    }    

    /**
     * @param  integer Is Client or Staff
     * @param  integer ID
     * @param  string Password reset key
     * @return boolean
     * Check if the key is not expired or not exists in database
     */
    public function can_reset_password($staff, $new_pass_key)
    {
        $table = db_prefix() . 'users';
        $_id   = 'userid';
        if ($staff == true) {
            $table = db_prefix() . 'staff';
            $_id   = 'staffid';
        }

        //$this->db->where($_id, $userid);
        $this->db->where('new_pass_key', $new_pass_key);
        $user = $this->db->get($table)->row();

        if ($user) {
            $timestamp_now_minus_1_hour = time() - (60 * 60);
            $new_pass_key_requested     = strtotime($user->new_pass_key_requested);
            if ($timestamp_now_minus_1_hour > $new_pass_key_requested) {
                return false;
            }

            return true;
        }

        return false;
    }    

    /**
     * @param  integer Is Client or Staff
     * @param  integer ID
     * @param  string Password reset key
     * @return boolean
     * Check if the key is not expired or not exists in database
     */
    public function can_set_password($staff, $userid, $new_pass_key)
    {
        $table = db_prefix() . 'users';
        $_id   = 'userid';
        if ($staff == true) {
            $table = db_prefix() . 'staff';
            $_id   = 'staffid';
        }    
        $this->db->where($_id, $userid);
        $this->db->where('new_pass_key', $new_pass_key);
        $user = $this->db->get($table)->row();   
        if ($user) {
            $timestamp_now_minus_48_hour = time() - (3600 * 48);
            $new_pass_key_requested      = strtotime($user->new_pass_key_requested);
            if ($timestamp_now_minus_48_hour > $new_pass_key_requested) {
                return false;
            }

            return true;
        }

        return false;                 
    }  
    
    public function encrypt($string)
    {
        $this->load->library('encryption');

        return $this->encryption->encrypt($string);
    }

    public function decrypt($string)
    {
        $this->load->library('encryption');

        return $this->encryption->decrypt($string);
    }    
}
