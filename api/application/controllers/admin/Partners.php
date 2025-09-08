<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Partners extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('partners_model');
    }

    public function getAll()
    {
		$cases = $this->partners_model->getAll();
		
		$data = array();
		if(!empty($cases)){
			foreach($cases as $row){ 

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
					'description' => $row->description, 
					'folder' => base_url('api/uploads/'.$row->folder.'\/'), 
					'file_name' => $row->file_name, 
					'date' => $row->dateadded, 
					'order' => $row->order, 
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
		$row = $this->partners_model->get($id);

        $data = array(
            'id' => $row->id, 
            'name' => $row->name, 
            'description' => $row->description, 
            'long_description' => $row->long_description, 
            'folder' => base_url('api/uploads/'.$row->folder.'\/'), 
            'file_name' => $row->file_name, 
            'date' => $row->dateadded, 
            'order' => $row->order, 
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
            $staffid                = $formdata['staffid'];

            $data = array(
                'name' => $name,
                'description' => $description,
                'staffid' => $staffid,
            );  

            $success = $this->partners_model->add($data);   
            $response = set_alert(
                $success, 'added_successfully', ''
            );	
            
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

			$data = array(
				'name' => $name,
				'description' => $description,
            );  

            $success = $this->partners_model->update($data, $id);
            $response = set_alert(
                $success, 'updated_successfully', ''
            );	
            
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
                foreach ($data as $pos => $partner_id) {
                    $this->db->where('id', $partner_id['id']);
                    $this->db->update(db_prefix() . 'partners', array(
                        'order' => $pos
                    ));                                   				                                  
                }              
            }
        }       
    }  
    
    public function delete($id)
    {

        $success = $this->partners_model->delete($id);   
        
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
     * Handles upload for cases files
     * @param  mixed $casesid post id
     * @return boolean
     */
	public function uploadPicture() 
	{
		$id      = $this->input->post('id');

        $success = handle_partner_file_uploads($id);

        if (is_array($success) && isset($success['message'])) {
            $response = array(
                'type' => 'error',
                'message' => $success['message']
            );
        } elseif ($success == true) {
            $response = array(
                'type' => 'success',
                'message' => _l('file_uploaded_success')
            );                
        } else {
            $response = array(
                'type' => 'error',
                'message' =>  $success['message'] ?? null
            );                
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));          
    }  
    
	public function deletePicture($id)
	{
        $picture = $this->partners_model->get($id);     
        $success = $this->partners_model->delete_picture($id);
        
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
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));         
    }     
}