<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Posts extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('posts_model');
    }

    public function getAll()
    {
		$ts_filter_data = array();
		$ts_filter_data['category_id'] = $this->input->get('category_id');
		$ts_filter_data['search_string'] = $this->input->get('search_string');
		$filter = array('filter' => $ts_filter_data);

		$posts = $this->posts_model->getAll($filter, ['language' => $this->load_lang()]);
		
		$data = array();
		if(!empty($posts)){
			foreach($posts as $row){ 
				$staff = $this->staff_model->get($row->staffid);	
                $admin = array(
					'staffid' => $staff->staffid,
					'firstname' => $staff->firstname,
					'lastname' => $staff->lastname,               
					'fullname' => $staff->fullname                  
                );

                $category = $this->posts_model->get_categories($row->id, ['language' => $this->load_lang()]);
                $categories = array();
                if(!empty($category)){
                    foreach($category as $c){
                        $categories[] = array(
                            'name' => $c->name,
                        );                    
                    }
                } 

				$data[] = array(
					'id' => $row->id, 
					'name' => $row->name, 
					'description' => strip_tags(character_limiter($row->description, 50)), 
					'long_description' => $row->long_description, 
					'folder' => base_url('api/uploads/posts/'.$row->id.'/'), 
					'date' => $row->dateadded, 
					'order' => $row->order, 
					'active' => $row->active, 
					'external_link' => $row->external_link, 
					'categories' => $categories, 
					'staff' => $admin, 
				);                
            }
        }

		$response = $data;
		
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));          
    }  
    
    public function getItemById($id = '')
    {
		$row = $this->posts_model->get($id, null, ['language' => $this->load_lang()]);

        $categories = $this->posts_model->get_categories($id, ['language' => $this->load_lang()]);
        $category = array();
        if(!empty($categories)){
            foreach($categories as $c){
                $category[] = array(
                    'category_id' => $c->category_id,
                    'name' => $c->name,
                );                    
            }
        } 

        $data = array(
            'id' => $row->id,
            'name' => $row->name,
            'description' => $row->description,
            'long_description' => $row->long_description,
            'folder' => base_url('api/uploads/posts/'.$row->id.'/'), 
            'staffid' => $row->staffid,
            'active' => $row->active,
            'external_link' => $row->external_link, 
            'categories' => $category,
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
            $long_description       = $formdata['long_description'];
            $categories             = $formdata['categories'];                     
            $external_link          = $formdata['external_link'];                     
            $staffid                = $formdata['staffid'];
            $active                 = $formdata['active'];

			$data = array(
				'name' => $name,
				'description' => $description,
				'long_description' => $long_description,
				'categories' => $categories, 
                'external_link' => $external_link, 
				'staffid' => $staffid,
				'active' => $active,
            );  

            $id = $this->posts_model->add($data);   
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
            $long_description       = $formdata['long_description'];
            $categories             = $formdata['categories'];     
            $external_link          = $formdata['external_link'];                     
            $staffid                = $formdata['staffid'];
            $active                 = $formdata['active'];

            $languageid             = $formdata['languageid'];

			$data = array(
				'name' => $name,
				'description' => $description,
				'long_description' => $long_description,
				'categories' => $categories,   
                'external_link' => $external_link, 
				'staffid' => $staffid,
				'active' => $active,
                'languageid' => $languageid
            );  

            $result = $this->posts_model->update($data, $id);

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
      

    public function updateStatus()
    {
		$formdata = json_decode(file_get_contents('php://input'), true);
        if(!empty($formdata)) {
            $ids 	    = $formdata['ids'];
            $active 	= $formdata['active'];
            if (is_array($ids)) {
                foreach ($ids as $post_id) {
                    $this->db->where('id', $post_id);
                    $this->db->update(db_prefix() . 'posts', array(
                        'active' => $active
                    ));                                   
                }                 
            }
        }    
		  
        $response = array(
            'type' => 'success',
            'message' => _l('updated_successfully', _l('project'))
        );  

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));         
    } 
    
    /* Delete contact */
	public function delete($id)
	{
		$contact = $this->posts_model->get($id);
        $success = $this->posts_model->delete($id);

        if(isset($success)) {
            $response = array(
                'type' => 'success',
                'message' => _l('deleted', _l('project')),
            );         
        } else {
            $response = array(
                'type' => 'error',
                'message' => _l('problem_deleting', _l('project')),
            );                
        }   
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));          
    }   
    
    public function deleteItems()
    {
		$formdata = json_decode(file_get_contents('php://input'), true);
		
        if(!empty($formdata)) {
            $ids = $formdata['ids'];
            if (is_array($ids)) {
                foreach ($ids as $post_id) {
                    $project = $this->posts_model->get($post_id);
                    $success = $this->posts_model->delete($post_id);                                
                }                 
            }
        }   
        
        if(isset($success)) {
            $response = array(
                'type' => 'success',
                'message' => _l('deleted', _l('project')),
            );         
        } else {
            $response = array(
                'type' => 'error',
                'message' => _l('problem_deleting', _l('project')),
            );                
        }   
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));            
    }  

    /**
     * Handles upload for slide files
     * @param  mixed $promoid promo id
     * @return boolean
     */
	public function uploadPicture() 
	{
        $staffid        = $this->input->post('staffid');
		$post_id        = $this->input->post('post_id');

		$subject        = $this->input->post('subject');
		$description    = $this->input->post('description');

        $result = handle_post_picture_uploads($post_id, $staffid, $subject, $description);

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
                'message' => $result['message'] ?? null
            );                
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));          
    }    

	public function getPictures($post_id = '')
	{
        $data = $this->posts_model->get_pictures($post_id);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));           
	}     
    
	public function deletePicture($id)
	{
        $success = $this->posts_model->delete_picture($id);

        if(isset($success)) {
            $response = set_alert(
                'success', 'deleted', ''
            ); 
        }
        
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));    
    }   
    
	public function getActivity($id)
	{
        $data = $this->posts_model->get_activity($id, $limit = 10);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));          
    }    
}