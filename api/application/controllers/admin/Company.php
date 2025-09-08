<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Company extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('company_model');
    }

    public function company()
    {
        $row = $this->company_model->get(null, ['language' => $this->load_lang()]);

        $data = array();
		if(!empty($row)){		
            $data = array(
                'name' => $row->name,
                'description' => $row->description,
                'long_description' => $row->long_description,
                'folder' => base_url('api/uploads/'. $row->folder . '/'),
                'date' => $row->dateupdated,
                'staffid' => $row->staffid,
                'languageid' => $row->languageid,
                'language' => $row->language
            );
        }

		$response = $data;
		
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));          
    }

	public function update() 
	{
		$formdata = json_decode(file_get_contents('php://input'), true);
		
		if(!empty($formdata)) {
            $name                   = $formdata['name'];
            $description            = $formdata['description'];  
            $long_description       = $formdata['long_description'];  
            $staffid                = $formdata['staffid'];

            $languageid             = $formdata['languageid'];

			$data = array(
				'name' => $name,
				'description' => $description,
				'long_description' => $long_description,
				'staffid' => $staffid,
				'languageid' => $languageid,
            );  

            $result = $this->company_model->update($data);
            $response = set_alert(
                $result, 'updated_successfully', ''
            );	
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));         
        }
    }  
    
	public function getItems($id = '')
	{
        $items = $this->company_model->get_items($id, ['language' => $this->load_lang()]);

		$data = array();
		if(is_array($items)){
			foreach($items as $row){ 

				$staff = $this->staff_model->get($row->staffid);	
                $admin = array(
					'staffid' => $staff->staffid,             
					'fullname' => $staff->fullname                  
                );

				$data[] = array(
					'id' => $row->id, 
					'name' => $row->name, 
					'description' => strip_tags(character_limiter($row->description, 50)), 
					'date' => $row->dateadded, 
					'order' => $row->order, 
					'staff' => $admin, 
				);
			}
		} else {
            $row = $items;
            $data = array(
                'id' => $row->id, 
                'name' => $row->name, 
                'description' => $row->description, 
                'date' => $row->dateadded, 
                'order' => $row->order, 
                'languageid' => $row->languageid,
                'language' => $row->language,                 
            );            
        }

		$response = $data;

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));           
	} 
    
	public function addItems()
	{
		$formdata = json_decode(file_get_contents('php://input'), true);
		
        if(!empty($formdata)) {
            $name                   = $formdata['name'];
            $description            = $formdata['description'];  
            $link                   = $formdata['link'];  
            $staffid                = $formdata['staffid'];

            $data = array(
                'name' => $name,
                'description' => $description,
                'link' => $link,
                'staffid' => $staffid,
            );  

            $success = $this->company_model->add_items($data);   
            $response = set_alert(
                $success, 'added_successfully', ''
            );	
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));                          
        }
    }   
    
	public function updateItems($id) 
	{
		$formdata = json_decode(file_get_contents('php://input'), true);
		
		if(!empty($formdata)) {
            $name                   = $formdata['name'];
            $link                   = $formdata['link'];  
            $description            = $formdata['description'];  
            $staffid                = $formdata['staffid'];

            $languageid             = $formdata['languageid'];

			$data = array(
				'name' => $name,
				'description' => $description,
                'link' => $link,
                'staffid' => $staffid,                
                'languageid' => $languageid,                
            );  

            $success = $this->company_model->update_items($data, $id);
            $response = set_alert(
                $success, 'updated_successfully', ''
            );	
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));         
        }
    }   
    
	public function sortableItems() 
	{
		$formdata = json_decode(file_get_contents('php://input'), true);
		if(!empty($formdata)) {
			$data = $formdata['data'];
            if (is_array($data)) {
                foreach ($data as $pos => $items_id) {
                    $this->db->where('id', $items_id['id']);
                    $this->db->update(db_prefix() . 'company_items', array(
                        'order' => $pos
                    ));                                   				                                  
                }              
            }
        }       
    }  
    
    public function deleteItem($id)
    {
        $items = $this->company_model->get_items($id);
        $success = $this->company_model->delete_item($id);   
        
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
     * Handles upload for company files
     * @param  mixed $company
     * @return boolean
     */
	public function uploadPicture() 
	{
        $staffid        = $this->input->post('staffid');
		$subject        = $this->input->post('subject');
		$description    = $this->input->post('description');

        $result = handle_company_picture_uploads($staffid, $subject, $description);

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
    
	public function sortable() 
	{
		$formdata = json_decode(file_get_contents('php://input'), true);
		if(!empty($formdata)) {
			$data = $formdata['data'];
            if (is_array($data)) {
                foreach ($data as $pos => $items_id) {
                    $this->db->where('id', $items_id['id']);
                    $this->db->update(db_prefix() . 'company_pictures', array(
                        'order' => $pos
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

	public function getPictures()
	{
        $response = $this->company_model->get_pictures();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));           
	}   
    
	public function deletePicture($id)
	{
        $success = $this->company_model->delete_picture($id);

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