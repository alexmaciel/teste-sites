<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Clients extends ClientsController
{
    public function __construct()
    {
        parent::__construct();

        hooks()->do_action('after_clients_area_init', $this);
    }    

    public function index()
    {
        $data['title']            = get_option('company_name');

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));           
    }       
    
	public function languages()
	{
        $data = $this->languages_model->get(null, ['active' => 1]);

        $this->data($data); 
	}  

    /**
     * Slides
     *
     * @return void
     */
    public function slides()
    {

        $slides = $this->slides_model->get('', ['slides.active' => 1]);

		$data = array();
		if(!empty($slides)){
			foreach($slides as $row){
				$short_desc = strip_tags(character_limiter($row->description, 150));
                
				$pic = $this->slides_model->get_picture($row->id);	
                $pictures = array();	
                if(!empty($pic)) {     
                    $url = '';
                    $path = 'api/uploads/slides/';
                    if ($path) {
                        $url = base_url($path);
                    }                                     
                    $pictures = array(         
                        'file_name' => $pic->file_name,                    
                        'original_file_name' => $pic->original_file_name,                    
                        'subject' => $pic->subject,                    
                        'description' => $pic->description,  
                        'thumb' => $url . $pic->file_name,                                     
                    );
                }
				$data[] = array(
					'id' => $row->id,
					'name' => $row->name,
					'description' => $short_desc,
					'link' => $row->link,
					'mask' => $row->mask,
					'folder' => base_url('api/uploads/slides/'),
                    'active' => $row->active,
					'pictures' => $pictures,
                    'language' => $row->language_cod,
				);				
			}
		}

        $this->data($data);
    }  

    /**
     * Projects
     *
     * @return void
     */
    public function projects()
    {
		$ts_filter_data = array();
		$ts_filter_data['category_id'] = $this->input->get('category_id');
		$ts_filter_data['search_string'] = $this->input->get('search_string');
		$filter = array('filter' => $ts_filter_data);

		$projects = $this->projects_model->get('', $filter, ['projects.active' => 1]);
		
		$data = array();
		if(!empty($projects)){
			foreach($projects as $row){ 
                $short_desc = strip_tags(character_limiter($row->description, 100));

                $project_pic = $this->projects_model->get_pictures($row->id);
                $pictures = array();
                if(!empty($project_pic)) {
                    foreach($project_pic as $pic){               
                        $pictures[] = array(         
                            'file_name' => $pic->file_name,                    
                            'original_file_name' => $pic->original_file_name,  
                            'visible_full' => $pic->visible_full,                             
                            'subject' => $pic->subject,                             
                            'description' => $pic->description, 
                            'thumb' => project_image_url($pic->id, $pic->project_id, 'thumb'),                                                    
                        );      
                    }      
                }

				$data[] = array(
					'id' => $row->id, 
					'name' => $row->name, 
					'description' => $short_desc, 
					'long_description' => $row->long_description, 
					'folder' => base_url('api/uploads/projects/'.$row->id.'/'), 
                    'city' => $row->city, 
                    'year' => $row->year,                     
					'order' => $row->order,                     
					'slug' => $row->slug, 
                    'language' => $row->language_cod,
                    'pictures' => $pictures
				);                
            }
        }

        $this->data($data);         
    }  

    /**
     * Get Projects
     *
     * @param [type] $slug
     * @return void
     */
    public function getProjectBySlug($slug)
    {
        $projects = $this->projects_model->slug($slug);

        $data = array();
		if(!empty($projects)){
			foreach($projects as $row){ 
                $project_pic = $this->projects_model->get_pictures($row->id);
                $pictures = array();
                if(!empty($project_pic)) {
                    foreach($project_pic as $pic){
                        $url = '';
                        $path = 'api/uploads/projects/' . $row->id. '/';
                        if ($path) {
                            $url = base_url($path);
                        }                    
                        $pictures[] = array(         
                            'file_name' => $url . $pic->file_name,                    
                            'original_file_name' => $pic->original_file_name,  
                            'visible_full' => $pic->visible_full,                             
                            'subject' => $pic->subject,                             
                            'description' => $pic->description,                                                       
                        );      
                    }      
                }

                $category = array();
                if(!empty($row->category_id)){
                    $category = array(
                        'id' => $row->category_id,
                    );
                }                 
            
                $data[] = array(
                    'id' => $row->id, 
                    'name' => $row->name, 
                    'description' => $row->description, 
                    'long_description' => $row->long_description, 
                    'folder' => base_url('api/uploads/projects/'.$row->id.'/'), 
                    'slug' => $row->slug, 
                    'city' => $row->city, 
                    'year' => $row->year, 
                    'order' => $row->order, 
                    'language' => $row->language_cod,
                    'category' => $category,
                    'pictures' => $pictures,
                );   
            }
        }

        $this->data($data); 
    }
  

    /**
     * Posts
     *
     * @return void
     */
    public function posts()
    {
		$ts_filter_data = array();
		$ts_filter_data['category_id'] = $this->input->get('category_id');
		$ts_filter_data['search_string'] = $this->input->get('search_string');
		$filter = array('filter' => $ts_filter_data);

		$posts = $this->posts_model->get('', $filter, ['posts.active' => 1]);
		
		$data = array();
		if(!empty($posts)){
			foreach($posts as $row){ 
                $short_desc = strip_tags(character_limiter($row->description, 50));

				$data[] = array(
					'id' => $row->id, 
					'name' => $row->name, 
					'description' => $short_desc, 
					'long_description' => $row->long_description, 
					'folder' => base_url('api/uploads/posts/'.$row->id.'/'), 
					'order' => $row->order, 
					'external_link' => $row->external_link, 
					'slug' => $row->slug, 
                    'language' => $row->language_cod,
                    'pictures' => array(
                        'thumb' => post_image_url($row->id, 'thumb')
                    )
				);                
            }
        }

        $this->data($data);         
    }   
    
    /**
     * Get Post By Slug
     *
     * @param [type] $slug
     * @return void
     */
    public function getPostsBySlug($slug)
    {
        $posts = $this->posts_model->slug($slug);

        $data = array();
		if(!empty($posts)){
			foreach($posts as $row){ 
                $project_pic = $this->posts_model->get_pictures($row->id);
                $pictures = array();
                if(!empty($project_pic)) {
                    foreach($project_pic as $pic){
                        $url = '';
                        $path = 'api/uploads/posts/' . $row->id. '/';
                        if ($path) {
                            $url = base_url($path);
                        }                    
                        $pictures[] = array(         
                            'file_name' => $url . $pic->file_name,                    
                            'original_file_name' => $pic->original_file_name,  
                            'visible_full' => $pic->visible_full,                             
                            'subject' => $pic->subject,                             
                            'description' => $pic->description,                                                       
                        );      
                    }      
                }  

                $post_cat = $this->posts_model->get_categories($row->id);
                $categories = array();
                if(!empty($post_cat)) {
                    foreach($post_cat as $cat){            
                        $categories[] = array(
                            'id' => $cat->id,
                            'name' => $cat->name,
                        );
                    }            
                }             
            
                $data[] = array(
                    'id' => $row->id, 
                    'name' => $row->name, 
                    'description' => $row->description, 
                    'long_description' => $row->long_description, 
                    'folder' => base_url('api/uploads/posts/'.$row->id.'/'), 
                    'slug' => $row->slug, 
                    'order' => $row->order, 
                    'date' => _d($row->dateadded), 
                    'language' => $row->language_cod,
                    'categories' => $categories,
                    'pictures' => $pictures,
                    'time_read' => estimateReadingTime($row->long_description),
                );   
            }
        }

        $this->data($data); 
    }    

    /**
     * Categories
     *
     * @return void
     */
    public function categories()
    {
		$categories = $this->posts_model->get_categories();
		
        $data = array();
        if(!empty($categories)){
            foreach($categories as $c){
                $data[] = array(
					'id' => $c->id, 
					'name' => $c->name, 
					'description' => $c->description, 
                    'language' => $c->language_cod
                );                    
            }
        } 

        $this->data($data);
    }

    /**
     * Get Category
     *
     * @param [type] $id
     * @return void
     */
    public function category($id)
    {
        $categories = $this->posts_model->get_categories($id);
        $data = array();
        if(!empty($categories)){
            foreach($categories as $c){
                $data[] = array(
                    'category_id' => $c->category_id,
                    'name' => $c->name,
                    'language' => $c->language_cod
                );                    
            }
        } 

        $this->data($data);
    }     

    /**
     * Company
     *
     * @return void
     */
    public function company()
    {
        //$company = $this->company_model->get();
        $columns = [
            db_prefix() .'company.name',
            db_prefix() .'company.description',
            db_prefix() .'company.folder',
            db_prefix() .'company.long_description',
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language_cod as language_cod',               
            db_prefix() .'languages.language as language', 
        ];         
        $this->db->select($columns);
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'company.languageid', 'left'); 

        $company = $this->db->get(db_prefix() . 'company')->result();

        $data = array();
		if(!empty($company)){		
			foreach($company as $row){        
                $short_desc = strip_tags(character_limiter($row->description, 250));

                $data[] = array(
                    'name' => $row->name,
                    'description' => $short_desc,
                    'long_description' => $row->long_description,
                    'folder' => base_url('api/uploads/'. $row->folder . '/'),
                    'language' => $row->language_cod
                );
            }
        }

		$this->data($data);          
    }    

    /**
     * Company Items
     *
     * @return void
     */
	public function companyItems()
	{
        $items = $this->company_model->get_items();

		$data = array();
		if(!empty($items)){
			foreach($items as $row){ 
				$data[] = array(
					'id' => $row->id, 
					'name' => $row->name, 
					'description' => $row->description, 
					'order' => $row->order, 
                    'language' => $row->language_cod
				);
			}
		}

		$this->data($data);          
	}   
    
    /**
     * Company Index Pictures
     *
     * @param boolean $limit
     * @return void
     */
	public function companyIndexPictures($limit = true)
	{
        $data = $this->company_model->get_pictures('', $limit);
		$this->data($data);          
	}  
    
	public function companyPictures($limit = false)
	{
        $project_pic = $this->company_model->get_pictures('', $limit);

        $data = array();
        if(!empty($project_pic)) {
            foreach($project_pic as $pic){
                $url = '';
                $path = 'api/uploads/company/';
                if ($path) {
                    $url = base_url($path);
                }                    
                $data[] = array(         
                    'file_name' => $url . $pic->file_name,                    
                    'original_file_name' => $pic->original_file_name,                          
                    'subject' => $pic->subject,                             
                    'description' => $pic->description,                                                       
                );      
            }      
        }

		$this->data($data);          
	}         

    /**
     * Technology
     *
     * @return void
     */
    public function technology()
    {
        $technology = $this->technology_model->get('');
        $data = array();
		if(!empty($technology)){		
			foreach($technology as $row){        
                $short_desc = strip_tags(character_limiter($row->description, 250));

                $data[] = array(
                    'name' => $row->name,
                    'description' => $short_desc,
                    'long_description' => $row->long_description,
                    'folder' => base_url('api/uploads/'. $row->folder . '/'),
                    'language' => $row->language_cod
                );
            }
        }

		$this->data($data);          
    }    

    /**
     * Technolony Items
     *
     * @return void
     */
	public function technologyItems()
	{
        $items = $this->technology_model->get_items();

		$data = array();
		if(!empty($items)){
			foreach($items as $row){ 
				$data[] = array(
                    'id' => $row->id,
                    'name' => $row->name,
                    'folder' => base_url('api/uploads/'.$row->folder.'/icons\/'), 
                    'file_name' => $row->file_name,                        
                    'description' => strip_tags(character_limiter($row->description, 625)),
                    'visible_draft' => $row->visible_draft,
                    'language' => $row->language_cod
				);
			}
		}

		$this->data($data);          
	} 

    /**
     * Technology Pictures
     *
     * @return void
     */
	public function technologyPictures()
	{
        $tech_pic = $this->technology_model->get_pictures('');

        $data = array();
        if(!empty($tech_pic)) {
            foreach($tech_pic as $pic){
                $url = '';
                $path = 'api/uploads/technology/';
                if ($path) {
                    $url = base_url($path);
                }                    
                $data[] = array(         
                    'file_name' => $pic->file_name,                    
                    'original_file_name' => $pic->original_file_name,                          
                    'subject' => $pic->subject,                             
                    'description' => $pic->description,  
                    'thumb' => $url . $pic->file_name,                    
                );      
            }      
        }

		$this->data($data);          
	}    
    
    /**
     * Technology Videos
     *
     * @return void
     */
    public function technologyVideos()
    {
        $videos = $this->technology_model->get_videos(['visible_to_customer' => 0]);
        $data = array();
        if(!empty($videos)) {
            foreach($videos as $v){
                $data[] = array(                                                    
                    'id' => $v->id,                             
                    'name' => $v->subject,                             
                    'description' => $v->description,  
                    'video_id' => getVideoLocation($v->external),
                    'visible_to_customer' => $v->visible_to_customer,
                    'thumb' => $v->external ? video_image($v->external) : $v->external                                                
                );                     
            }
        } 
        
        $this->data($data); 
    }    

    /**
     * Partners
     *
     * @return void
     */
    public function partners()
     {
         $partners = $this->partners_model->get();
         
         $data = array();
         if(!empty($partners)){
             foreach($partners as $row){ 
 
                 $data[] = array(
                     'id' => $row->id, 
                     'name' => $row->name, 
                     'description' => $row->description, 
                     'long_description' => $row->long_description, 
                     'folder' => base_url('api/uploads/'.$row->folder . '\/'), 
                     'file_name' => $row->file_name, 
                     'order' => $row->order, 
                 );
             }
         }
 
         $this->data($data);           
    }      

    /**
     * Teams
     *
     * @return void
     */
    public function teams()
    {
		$teams = $this->teams_model->get();

        $data = array();
		if(!empty($teams)){
			foreach($teams as $row){ 
                $path = base_url('api/uploads/teams/'.$row->id.'/');

                $data[] = array(
                    'id' => $row->id, 
                    'name' => $row->name, 
                    'description' => $row->description, 
                    'phonenumber' => $row->phonenumber, 
                    'email' => $row->email,             
                    'folder' => base_url('api/uploads/teams/'.$row->id.'/'), 
                    'file_avatar' => $path . $row->file_avatar, 
                    'date' => $row->dateadded, 
                    'order' => $row->order, 
                    'language' => $row->language_cod, 
                );
            }
        }

		$this->data($data);          
    }     
      

    public function social()
    {
        $data = $this->social_model->get(null, ['active' => 1]);
        $this->data($data);
    }    

    public function addLead()
    {
		$formdata = json_decode(file_get_contents('php://input'), true);
		
        if(!empty($formdata)) {
            $name                    = $formdata['name'];
            $phonenumber             = $formdata['phonenumber'];
            $email                   = $formdata['email'];

			$data = array(
				'name' => $name,
				'phonenumber' => $phonenumber,
				'email' => $email,
                'source' => 3
            );  

            $id = $this->leads_model->add($data);
            if($id) {
                $response = array(
                    'type' => 'success',
                    'message' => 'Cadastrado com Sucesso!'
                );	
            }

            $this->data($response);
        }        
    }  

    /**
     * Countries
     *
     * @return void
     */
    public function countries()
    {
        $countries = get_all_countries();

        $data = array();
		if(!empty($countries)){
			foreach($countries as $row){ 

                $data[] = array(
                    'name'     => $row['short_name'],
                    'iso'      => $row['iso2'],
                    'code'      => $row['calling_code'],
                    'value'    => (int) $row['country_id'],
                );
            }
        }        
        
        $this->data($data);
    }

    /**
     * Settings
     *
     * @return void
     */
    public function settings()
    {
        $whatsapp_chat_clients_area = get_option('whatsapp_chat_clients_area');
        $whatsapp_chat_clients_area = html_entity_decode(clear_textarea_breaks($whatsapp_chat_clients_area));

        if(isMobile()){
            $service = 'api.whatsapp.com';
        }
        else {
            $service = 'web.whatsapp.com';
        }

		$data = array(
			'main_domain' => get_option('main_domain'),
			'company_name' => get_option('company_name'),
			'business_name' => get_option('business_name'),	
			'company_address' => get_option('company_address'),	
			'company_city' => get_option('company_city'),	
			'company_postal_code' => get_option('company_postal_code'),	
			'company_phonenumber' => get_option('company_phonenumber'),	
			'company_alt_phonenumber' => get_option('company_alt_phonenumber'),	
			'company_description' => get_option('company_description'),	
			'company_email' => get_option('company_email'),	
            'ticket_attachments_file_extensions' => get_option('ticket_attachments_file_extensions'),
			// Whatsapp
			'whatsapp_chat' => get_option('whatsapp_chat'),						
			'whatsapp_chat_clients_area' => 'https://' . $service . '/send?phone=' . $whatsapp_chat_clients_area,						
			'whatsapp_chat_description' => '&text=' . get_option('whatsapp_chat_description'),	            
        );
        
		$this->data($data);        
    }    

    // Send email - No templates used only simple string
    public function send_email()
    {
		
        if($this->input->post()) { 

            $subject 	    = $this->input->post('subject'); 
            $firstname 	    = $this->input->post('firstname'); 
            $lastname 	    = $this->input->post('lastname'); 
            $email 	        = $this->input->post('email'); 
            $phone 	        = $this->input->post('phone'); 
            $message 	    = $this->input->post('message'); 

            $data = array(
                'subject' => $subject,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
      
            );
            $this->load->model('emails_model');
            if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
                $attachments = true;

                // Getting file extension
                $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));   
                $allowed_extensions = explode(',', get_option('ticket_attachments_file_extensions'));
                $allowed_extensions = array_map('trim', $allowed_extensions);
                               
                $allowed_extensions = hooks()->apply_filters('ticket_attachments_file_extensions', $allowed_extensions);

                if (!in_array($extension, $allowed_extensions)) {
                    $attachments = false;
                } 

                $this->emails_model->add_attachment(array(
                    'attachment' => $_FILES['file']['tmp_name'],
                    'filename' => $_FILES['file']['name'],
                    'type' => $_FILES['file']['type'],
                    'read' => true
                )); 

                $attachments = true;
            }             
            $success = $this->emails_model->send_email_contact($data);

            if (!in_array($extension, $allowed_extensions) && !$attachments) {
                $response = array(
                    'type' => 'error',
                    'title' => 'Error!',                        
                    'message' => 'Extensão de arquivo não permitido. Extensões: ' . get_option('ticket_attachments_file_extensions')
                );                  
            } elseif ($success == true) {
                $response = array(
                    'type' => 'success',
                    'title' => 'Success!',   
                    'message' => _l('custom_file_success_send')
                );
            } else {
                $response = array(
                    'type' => 'warning',
                    'title' => 'Warning!', 
                    'message' => _l('custom_file_fail_send')
                );            
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));             
        }
    }     
}
