<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Staff extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_model');
    }

    /* List all staff members */
    public function getAll()
    {
		$admins = $this->staff_model->getAll();
		
		$admin = array();
		if(!empty($admins)){
			foreach($admins as $row){

                if(empty($row->adminWebsite)) {
                    $email  = $row->email;
                    $parts = explode("@",$email);
                    // Get the website by slicing string
                    $website = $parts[1];
                } else {
                    $website  = $row->adminWebsite;
                }                

				$admin[] = array(
					'id' => $row->staffid,
                    'staffid' => $row->staffid,
                    'firstname' => $row->firstname,
                    'lastname' => $row->lastname,
                    'fullname' => $row->fullname,
                    'email' => $row->email,
                    'phone' => $row->phone,
                    //'folder' => base_url('api/uploads/avatars/'),
                    'avatar' => staff_profile_image_url($row->staffid, 'thumb'),
                    'token' => $row->token,
                    'role' => $row->role,
                    'admin' => $row->admin,
                    'username' => $row->username,
                    'default_language' => $row->default_language,
                    'date' => $row->datecreated,
                    'address' => $row->address,
                    'website' => $website,
                    'active' => $row->active,
				);
			}
		}

		$response = $admin;
		
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));  
	} 

    public function getItemById($id = '')
    {
        $data = $this->staff_model->get($id);

        //$password = app_hash_password($data->password);

        if(empty($data->website)) {
            $email  = $data->email;
            $parts = explode("@",$email);
            // Get the website by slicing string
            $website = $parts[1];
        } else {
            $website  = $data->website;
        }   

        $response = array();
        if ($data) {
            $response = array(
                'id' => $data->staffid,
                'staffid' => $data->staffid,
                'firstname' => $data->firstname,
                'lastname' => $data->lastname,
                'fullname' => $data->fullname,
                'email' => $data->email,
                'phone' => $data->phone,
                //'folder' => base_url('api/uploads/avatars/'),
                'avatar' => staff_profile_image_url($data->staffid, 'thumb'),
                'token' => $data->token,
                'admin' => $data->admin,
                'role' => $data->role,       
                'username' => $data->username,
                'default_language' => $data->default_language,
                'date' => $data->datecreated,
                'address' => $data->address,
                'password' => $data->password,
                'active' => $data->active,
                'website' => $website,
            );
        }
    
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));        
                
    }   

	public function create()
	{
		$formdata = json_decode(file_get_contents('php://input'), true);
		
		if(!empty($formdata)) {

            $dupEmail = false;

            $this->db->where('email', $formdata['email']);
            $admin = $this->db->get(db_prefix() . 'staff')->row();
            if ($admin) {
                $dupEmail = true;
            }
    
            if (!$dupEmail) {

                $firstname 	    = $formdata['firstname'];
                $lastname 	    = $formdata['lastname'];
                $email 	        = $formdata['email'];
                $role 	        = $formdata['role'];
                $password 	    = $formdata['password'];
                $active 	    = $formdata['active'];
                $send_mail 	    = $formdata['send_mail'];

                //$data['datecreated'] = date('Y-m-d');
                //$data['password'] = md5('password');
                //$data['token'] = md5(uniqid('token'));

                $data = array(
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'role' => $role,					
                    'password' => $password,	
                    'active' => $active,		
                    'send_welcome_email' => $send_mail,	
                    'default_language'    => (get_staff_default_language() != '') ? get_staff_default_language() : get_option('active_language'),
                );

                $id = $this->staff_model->add($data);
                if ($id) { 
                    $response = array(
                        'type' => 'success',
                        'message' => _l('added_successfully', _l('staff_member'))
                    );
                }
            } else {
                $response = array(
                    'type' => 'warning',
                    'message' => 'Email already exists.'
                );                
            }

			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($response)); 	
		}
	}    

	public function update($id) 
	{

		$formdata = json_decode(file_get_contents('php://input'), true);
		
		if(!empty($formdata)) {

			$firstname 	    = $formdata['firstname'];
			$lastname 	    = $formdata['lastname'];
			$phone 	        = $formdata['phone'];
			$email 	        = $formdata['email'];
			$address 	    = $formdata['address'];
			$username 	    = $formdata['username'];
			$role 	        = $formdata['role'];
			$admin 	        = $formdata['admin'];

			$language 	    = $formdata['default_language'];

			$data = array(
				'firstname' => $firstname,
				'lastname' => $lastname,
				'phone' => $phone,
				'email' => $email,
				'address' => $address,
				'username' => $username,
				'role' => $role,
				'admin' => $admin,
				'default_language' => $language,
			);	
			
			$success = $this->staff_model->update($id, $data);

            if(isset($success)) {
                $response = set_alert(
                    $success, 'updated_successfully', 'staff_member'
                );	
            }
			
			$this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response)); 
		}		
	}    
    
    
	public function uploadAvatar($id) 
	{
        $admin = $this->staff_model->get($id);

        $success = handle_profile_image_upload($id);

        if (is_array($success) && isset($success['message'])) {
            $response = array(
                'type' => 'error',
                'message' => $success['message']
            );
        } elseif ($success === true) {
            $response = array(
                'type' => 'success',
                'message' => _l('file_uploaded_success'),
				'avatar' => staff_profile_image_url($admin->staffid, 'small')
            );                
        } else {
            $response = array(
                'type' => 'error',
                'message' => $success['message']
            );                
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));  				      			
	}  
    
    /* Remove staff profile image / ajax */
	public function deleteAvatar($id)
	{
		$staff_id = get_staff_user_id();
		if (is_numeric($id)) {
			$staff_id = $id;
		}
		hooks()->do_action('before_remove_staff_profile_image');
        $success = $this->staff_model->deleteAvatar($staff_id);

        if (isset($success)) {
            $response = array(
                'type' => 'success',
                'message' => _l('deleted'),
            );         
        } else {
            $response = array(
                'type' => 'error',
                'message' => _l('problem_deleting'),
            );   
        }

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
    }

	public function delete($id)
	{
        $isDeleteError = false;

        $staff = $this->staff_model->get($id);
        if ($staff->staffid == 1 && $staff->admin == 0) {
            $isDeleteError = true;

            $response = array(
                'type' => 'error',
                'message' => 'Busted, you can\'t delete administrators'
            );            
        }

        if (!$isDeleteError) {        
            $success = $this->staff_model->delete($id);

            $response = array(
                'type' => 'success',
                'message' => _l('deleted', _l('staff_member'))
            );
        }

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response)); 		
	}	       
    
    /* When staff change his password */
    public function change_password($id) 
	{
        $isError = false;
		$formdata = json_decode(file_get_contents('php://input'), true);

		if(!empty($formdata)) {
            $currentPassword 	= $formdata['currentPassword'];
            $password 	        = $formdata['password'];

            $admin = $this->staff_model->get($id);
            //$passwordOld = $admin->password;            

            if(!app_hasher()->CheckPassword($currentPassword, $admin->password)) {
                $isError = true;
                $response = array(
                    'type' => 'error',
                    'message' => _l('staff_old_password_incorrect')
                );
            } else {
                $isError = false;
            }

            if(!$isError) {
			    $success = $this->staff_model->change_password($id, $password);
                if($success) {
                    $response = array(
                        'type' => 'success',
                        'message' => _l('staff_password_changed')
                    );	
                } else {
                    $response = array(
                        'type' => 'warnning',
                        'message' => _l('staff_problem_changing_password')
                    );					
                }
            }
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));              
        }         
    } 
}