<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_model');
    } 

	public function update($id) 
	{
		$formdata = json_decode(file_get_contents('php://input'), true);

		if(!empty($formdata)) {

			$firstname 	    	= $formdata['firstname'];
			$lastname 	    	= $formdata['lastname'];
			$phone 	        	= $formdata['phone'];
			$email 	        	= $formdata['email'];
			$address 	    	= $formdata['address'];

			$username 	    	= $formdata['username'];
			$role 	        	= $formdata['role'];
			$admin 	    		= $formdata['admin'];
			$default_language 	= $formdata['default_language'];

			$data = array(
				'firstname' => $firstname,
				'lastname' => $lastname,
				'phone' => $phone,
				'email' => $email,
				'address' => $address,
				'username' => $username,
				'role' => $role,
				'admin' => $admin,
				'default_language' => $default_language,
			);	
			
			$success = $this->staff_model->update($id, $data);

			if(isset($success)){
				$response = set_alert($success, 'updated_successfully', 'staff_member');	
			}
			
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($response)); 
		}	
	}    
    
	public function uploadAvatar($id) 
	{
        $profile = $this->staff_model->get($id);

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
				'avatar' => staff_profile_image_url($profile->staffid, 'small')
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
	
	/* When staff change his password */
	public function change_password($id) 
	{
		$formdata = json_decode(file_get_contents('php://input'), true);

		if(!empty($formdata)) {
            $password 	= $formdata['password'];

			$success = $this->staff_model->change_password($id, $password);
			if($success) {
				$response = array(
					'type' => 'success',
					'message' => _l('staff_password_changed')
				);	
			} else {
				$response = array(
					'type' => 'warning',
					'message' => _l('staff_problem_changing_password')
				);					
			}
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));              
        }
    }	
  
}
