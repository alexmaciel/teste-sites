<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Slides extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('slides_model');
    }

    public function getAll()
    {
		$slides = $this->slides_model->getAll(['language' => $this->load_lang()]);
		
		$data = array();
		if(!empty($slides)){
			foreach($slides as $row){ 

				$staff = $this->staff_model->get($row->staffid);	
                $admin = array(
					'staffid' => $staff->staffid,
					'firstname' => $staff->firstname,
					'lastname' => $staff->lastname,               
					'fullname' => $staff->fullname                  
                );

				$data[] = array(
					'id' => $row->id, 
					'name' => $row->name, 
					'description' => strip_tags(character_limiter($row->description, 50)), 
					'folder' => base_url('api/uploads/'.$row->folder.'/'), 
					'link' => $row->link, 
					'date' => $row->dateadded, 
					'active' => $row->active, 
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
		$row = $this->slides_model->get($id, ['language' => $this->load_lang()]);

        $data = array(
            'id' => $row->id, 
            'name' => $row->name, 
            'description' => $row->description, 
            'link' => $row->link, 
            'folder' => base_url('api/uploads/'.$row->folder.'/'), 
            'link' => $row->link, 
            'mask' => $row->mask, 
            'date' => $row->dateadded, 
            'active' => $row->active, 
            'staffid' => $row->staffid,
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
            $link                   = $formdata['link'];
            $mask                   = $formdata['mask'];
            $staffid                = $formdata['staffid'];
            $active                 = $formdata['active'];

			$data = array(
				'name' => $name,
				'description' => $description,
				'link' => $link,
                'mask' => $mask,             
				'staffid' => $staffid,
				'active' => $active,
            );  

            $id = $this->slides_model->add($data);   
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
            $link                   = $formdata['link'];
            $mask                   = $formdata['mask'];  
            
            $languageid             = $formdata['languageid'];

			$data = array(
				'name' => $name,
				'description' => $description,
				'link' => $link,
                'mask' => $mask,    
                'languageid' => $languageid       
            );  

            $success = $this->slides_model->update($data, $id);

            if(isset($success)) {
                $response = set_alert(
                    $success, 'updated_successfully', 'project'
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
                foreach ($data as $pos => $slide_id) {
                    $this->db->where('id', $slide_id['id']);
                    $this->db->update(db_prefix() . 'slides', array(
                        'order' => $pos
                    ));                                   				                                  
                }              
            }
        }       
    }     

    public function updateStatus()
    {
		$formdata = json_decode(file_get_contents('php://input'), true);
        if(!empty($formdata)) {
            $ids 	    = $formdata['ids'];
            $active 	= $formdata['active'];
            if (is_array($ids)) {
                foreach ($ids as $slide_id) {
                    $this->db->where('id', $slide_id);
                    $this->db->update(db_prefix() . 'slides', array(
                        'active' => $active
                    ));                                   
                }                 
            }
        }    
		  
        $response = array(
            'type' => 'success',
            'message' => _l('updated_successfully')
        );  

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));         
    }    
    
    public function delete($slideid)
    {
        $slide = $this->slides_model->get($slideid);
        $success = $this->slides_model->delete($slideid);   
        
        if ($success) {
            $response = set_alert('success', _l('deleted'));             
        } else {
            $response = set_alert('error', _l('problem_deleting'));   
        }

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));          
    }    

    /**
     * Handles upload for slide files
     * @param  mixed $slideid post id
     * @return boolean
     */
	public function uploadPicture() 
	{
        $staffid        = $this->input->post('staffid');
		$slideid        = $this->input->post('slideid');

		$subject        = $this->input->post('subject');
		$description    = $this->input->post('description');

        $result = handle_slide_picture_uploads($slideid, $staffid, $subject, $description);

        if (is_array($result) && isset($result['message'])) {
            $response = array(
                'type' => 'error',
                'message' => $result['message']
            );
        } elseif ($result == true) {
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

	public function getPictures($slideid = '')
	{
        $data = $this->slides_model->get_pictures($slideid);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));           
	}     
    
	public function deletePicture($id)
	{
        $success = $this->slides_model->delete_picture($id);

        $response = set_alert(
            'success', 'deleted', ''
        ); 
        
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));    
    }      
}