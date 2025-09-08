<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends AuthController
{
    public function __construct()
    {
        parent::__construct();
    } 

    public function getProfileById($id = '')
    {
        if ($id == '') {
            $id = get_staff_user_id();
        }

        hooks()->do_action('user_profile_access', $id);

        $data = $this->users_model->get($id);
        $password = app_hash_password($data->password);

        if ($data) {
            $response = array(
                'userid' => $data->userid,
                'firstname' => $data->firstname,
                'lastname' => $data->lastname,
                'email' => $data->email,
                'phonenumber' => $data->phonenumber,
                'folder' => base_url('api/uploads/users/'.$data->userid.'/'),
                'avatar' => user_profile_image_url($data->userid, 'thumb'),
                'token' => $data->token,           
                'username' => $data->username,
                'language' => $data->language,
                'date' => $data->datecreated,
                'address' => $data->address,
                'password' => $data->password,
                'active' => $data->active,
            );
        }
    
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));        
                
    }     

	public function update($id) 
	{
		$formdata = json_decode(file_get_contents('php://input'), true);

        if(!empty($formdata)) {
            $firstname 	    = $formdata['firstname']; 
            $lastname 	    = $formdata['lastname']; 
            $phonenumber 	= $formdata['country_phone']['phonenumber']; 
            $altphone 	    = $formdata['altphone']; 

            $data = array(
                "firstname" => $firstname,
                "lastname" => $lastname,
                "phonenumber" => $phonenumber,
                "altphone" => $altphone,
            );

            $send_set_password_email 	= false;
            
            $original_contact   = $this->users_model->get($id);
            $success            = $this->users_model->update($data, $id, $send_set_password_email);
            $updated            = false; 
            if (is_array($success)) {
                if (isset($success['set_password_email_sent'])) {
                    $response = array(
                        'type' => 'success',
                        'message' => _l('set_password_email_sent_to_client'),
                    );   
                } elseif (isset($success['set_password_email_sent_and_profile_updated'])) {
                    $updated = true;
                    $response = array(
                        'type' => 'success',
                        'message' => _l('set_password_email_sent_to_client_and_profile_updated'),
                    );                  
                }            
            } else {
                if ($success) {
                    $updated = true;
                    $response = array(
                        'type' => 'success',
                        'message' => _l('updated_successfully', _l('contact')),
                    );                       
                } elseif ($success == 0) {
                    $response = array(
                        'type' => 'warning',
                        'message' => _l('updated_successfully', _l('contact')),
                    );  
                }
            }   

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));                        
        }
    }

    // Avatar
    public function getAvatarById($id = '')
    {
        $data = $this->users_model->get($id);

        if ($data) {
            $response = array(
                'avatar' => user_profile_image_url($data->userid, 'thumb'),
            );
        }
    
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));        
                
    }      

	public function uploadAvatar() 
	{
        if($this->input->post()) {

            $userid = $this->input->post('userid');
            
            $success = handle_avatar_user_uploads($userid);
            if (is_array($success) && isset($success['message'])) {
                $response = array(
                    'type' => 'danger',
                    'message' => $success['message']
                );
            } elseif ($success == true) {
                $response = array(
                    'type' => 'success',
                    'message' => _l('file_uploaded_success')
                );                
            } else {
                $response = array(
                    'type' => 'danger',
                    'message' => $success['message']
                );                
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));         
    }  
    
	public function deleteAvatar($userid)
	{
        $picture = $this->users_model->get($userid);     
        $success = $this->users_model->delete_user_profile_image($userid);
        
        if ($success) {
            $response = array(
                'type' => 'success',
                'message' => _l('delete'),
            );         
        } else {
            $response = array(
                'type' => 'danger',
                'message' => _l('problem_deleting'),
            );   
        }

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));         
    }     
}