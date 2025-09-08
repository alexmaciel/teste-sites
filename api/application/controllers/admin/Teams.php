<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Teams extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('teams_model');
    }

    public function getAll()
    {
        
		$teams = $this->teams_model->getAll(['language' => $this->load_lang()]);
		
		$data = array();
		if(!empty($teams)){
			foreach($teams as $row){ 
				$staff = $this->staff_model->get($row->staffid);	
                $admin = array(
					'staffid' => $staff->staffid,
					'firstname' => $staff->firstname,
					'lastname' => $staff->lastname,               
					'fullname' => $staff->fullname                  
                );

                $file_avatar  = null;
                if (!empty($row->file_avatar)) {
                    $file_avatar = 'small_' . $row->file_avatar;
                }

				$data[] = array(
					'id' => $row->id, 
					'name' => $row->name, 
					'description' => $row->description, 
                    'phonenumber' => $row->phonenumber, 
                    'email' => $row->email, 
					'folder' => base_url('api/uploads/teams/'.$row->id.'/'), 
					'file_avatar' => $file_avatar, 
					'date' => $row->dateadded, 
					'order' => $row->order, 
                    'language' => $row->language,
					'staff' => $admin, 
				);
			}
		}

		$response = $data;
		
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));          
    }

    public function getItemById($id)
    {
		$row = $this->teams_model->get($id, ['languages.language' => $this->load_lang()]);

        $file_avatar  = null;
        if (!empty($row->file_avatar)) {
            $file_avatar = 'small_' . $row->file_avatar;
        }

        $data = array(
            'id' => $row->id, 
            'name' => $row->name, 
            'description' => $row->description, 
            'phonenumber' => $row->phonenumber, 
            'email' => $row->email,             
            'folder' => base_url('api/uploads/teams/'.$row->id.'/'), 
            'file_avatar' => $file_avatar, 
            'date' => $row->dateadded, 
            'order' => $row->order, 
            'languageid' => $row->languageid,
            'language' => $row->language,
        );

		$response = $data;
		
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));          
    }      
    
	public function create()
	{
		$formdata = json_decode(file_get_contents('php://input'), true);
		
        if(!empty($formdata)) {
            $name                   = $formdata['name'];
            $description            = $formdata['description'];  
            $phonenumber            = $formdata['phonenumber'];           
            $email                  = $formdata['email'];           
            $staffid                = $formdata['staffid'];

            $data = array(
                'name' => $name,
                'description' => $description,
				'phonenumber' => $phonenumber,
				'email' => $email,
                'staffid' => $staffid,
            );  

            $id = $this->teams_model->add($data);   
            if($id) {
                $response = array(
                    'type' => 'success',
                    'message' => _l('added_successfully', _l('project')),
                    'id' => $id
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
            $name                   = $formdata['name'];
            $description            = $formdata['description'];  
            $phonenumber            = $formdata['phonenumber'];           
            $email                  = $formdata['email'];        

            $languageid             = $formdata['languageid'];            

			$data = array(
				'name' => $name,
				'description' => $description,
				'phonenumber' => $phonenumber,
				'email' => $email,
                'languageid' => $languageid
            );  
            
            $result = $this->teams_model->update($data, $id);
            
            if(isset($result)) {
                $response = set_alert(
                    $result, 'updated_successfully', 'project'
                );	
            }
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));         
        }
    }  
    
	public function sortable() 
	{
		$formdata = json_decode(file_get_contents('php://input'), true);
		if(!empty($formdata)) {
			$data = $formdata['data'];
            if (is_array($data)) {
                foreach ($data as $pos => $category_id) {
                    $this->db->where('id', $category_id['id']);
                    $this->db->update(db_prefix() . 'teams', array(
                        'order' => $pos
                    ));                                   				                                  
                }              
            }
        }       
    }  
    
    public function delete($id)
    {
        $team = $this->teams_model->get($id);
        $success = $this->teams_model->delete($id);   
        
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
    
    /**
     * Handles upload for teams files
     * @param  mixed $teamsid post id
     * @return boolean
     */
	public function uploadPicture() 
	{
		$id        = $this->input->post('id');

        $result = handle_teams_avatar_uploads($id);

        if (is_array($result) && isset($result['message'])) {
            $response = array(
                'type' => 'error',
                'message' => $result['message']
            );
        } elseif ($result === true) {
            $response = array(
                'type' => 'success',
                'message' => _l('file_uploaded_success')
            );                
        } else {
            $response = array(
                'type' => 'error',
                'message' => $result['message']
            );                
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));          
    }  
    
	public function deletePicture($id)
	{
        $picture = $this->teams_model->get($id);     
        $result = $this->teams_model->delete_picture($id);
        
        if ($result) {
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
    
    public function getSocial($id = '')
	{
        $data = $this->teams_model->get_social($id);

        $response = $data;
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));          
    }

	public function addSocial()
	{
		$formdata = json_decode(file_get_contents('php://input'), true);
		
        if(!empty($formdata)) {
            $name                   = $formdata['name'];
            $link                   = $formdata['link'];  
            $teamid                 = $formdata['teamid'];
            $staffid                = $formdata['staffid'];

            $data = array(
                'name' => $name,
                'link' => $link,
                'teamid' => $teamid,
                'staffid' => $staffid,
            );  

            $id = $this->teams_model->add_social($data);   
            if($id) {
                $success = true;
                $response = set_alert(
                    $success, 'added_successfully', ''
                );	
            }
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));                          
        }
    }     
    
	public function updateSocial($id) 
	{
		$formdata = json_decode(file_get_contents('php://input'), true);
		
		if(!empty($formdata)) {
            $name                   = $formdata['name'];
            $link                   = $formdata['link']; 
            $teamid                 = $formdata['teamid'];
            $staffid                = $formdata['staffid'];

			$data = array(
				'name' => $name,
                'link' => $link,
                'teamid' => $teamid,
                'staffid' => $staffid,                
            );  

            $success = $this->teams_model->update_social($data, $id);
            $response = set_alert(
                $success, 'updated_successfully', ''
            );	
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));         
        }
    }  

    public function deleteSocial($id)
    {
        $social = $this->teams_model->get_social($id);
        $success = $this->teams_model->delete_social($id);   
        
        if ($success) {
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
}