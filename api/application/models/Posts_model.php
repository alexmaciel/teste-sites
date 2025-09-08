<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Posts_model extends Api_Model
{
    public function __construct()
    {
        parent::__construct();
    }    

    public function getAll($filter = null, $where = array())
    {
        unset($filter['null']);
        if(is_array($filter)) {
            $filter_category = ($filter['filter']['category_id'] == 0) ? db_prefix() . 'posts_categories.category_id = '. db_prefix() . 'posts_categories.category_id' : db_prefix() . 'posts_categories.category_id = ' .  $filter['filter']['category_id'];
        }

        $columns = [
            db_prefix() .'posts.id as id',
            db_prefix() .'posts.dateadded',
            db_prefix() .'posts.active',
            db_prefix() .'posts.order',
            db_prefix() .'posts.staffid',
            db_prefix() .'posts.external_link',
            db_prefix() .'categories.id as category_id',
            db_prefix() .'categories.name as category_name',
            db_prefix() .'posts_translation.name as name',
            db_prefix() .'posts_translation.description as description',
            db_prefix() .'posts_translation.long_description as long_description',
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language as language',    
        ];         
        $this->db->select($columns);

        $this->db->where($where);
        $this->db->join(db_prefix() . 'posts_translation', db_prefix() . 'posts.id = ' . db_prefix() . 'posts_translation.postid', 'left');           
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'posts_translation.languageid', 'left');  
        $this->db->join(db_prefix() . 'posts_categories', db_prefix() . 'posts_categories.post_id = ' . db_prefix() . 'posts.id', 'left'); 
        $this->db->join(db_prefix() . 'categories', db_prefix() . 'categories.id = ' . db_prefix() . 'posts_categories.category_id', 'left'); 
       
        $this->db->where($filter_category);
        
        $this->db->group_by(db_prefix() . 'posts_translation.id');
        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'posts')->result();            
    }

    /**
     * Get Product
     * @param  string $slug  optional slug
     * @param  array  $where perform where
     * @return mixed
     */
    public function get($id = '', $filter = null, $where = array())
    {
        unset($filter['null']);
        if(is_array($filter)) {
            $filter_category = ($filter['filter']['category_id'] == 0) ? db_prefix() . 'posts_categories.category_id = '. db_prefix() . 'posts_categories.category_id' : db_prefix() . 'posts_categories.category_id = ' .  $filter['filter']['category_id'];
        }

        $columns = [
            db_prefix() .'posts.id as id',
            db_prefix() .'posts.dateadded',
            db_prefix() .'posts.active',
            db_prefix() .'posts.order',
            db_prefix() .'posts.staffid',
            db_prefix() .'categories.id as category_id',
            db_prefix() .'categories.name as category_name',
            db_prefix() .'posts_translation.name as name',
            db_prefix() .'posts_translation.description as description',
            db_prefix() .'posts_translation.long_description as long_description',
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language_cod as language_cod',               
            db_prefix() .'languages.language as language',  
            'external_link',            
            'slug',
        ];   
        $this->db->select($columns);
        $this->db->where($where);

        $this->db->join(db_prefix() . 'posts_translation', db_prefix() . 'posts.id = ' . db_prefix() . 'posts_translation.postid', 'left');           
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'posts_translation.languageid', 'left');  
        $this->db->join(db_prefix() . 'posts_categories', db_prefix() . 'posts_categories.post_id = ' . db_prefix() . 'posts.id', 'left'); 
        $this->db->join(db_prefix() . 'categories', db_prefix() . 'categories.id = ' . db_prefix() . 'posts_categories.category_id', 'left'); 

        $this->db->group_by(db_prefix() . 'posts_translation.id');

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'posts.id', $id);
            $post = $this->db->get(db_prefix() . 'posts')->row();
            if ($post) {
                $post            = hooks()->apply_filters('post_get', $post);
                $GLOBALS['post'] = $post;

                return $post;                
            }

            return null;
        }
        
        $this->db->where($filter_category);
        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'posts')->result();
    }  
    
    /**
     * Get Product
     * @param  string $slug    optional slug
     * @param  array  $where perform where
     * @return mixed
     */
    public function slug($slug = '', $where = array())
    {
        $columns = [
            db_prefix() .'posts.id as id',
            db_prefix() .'posts.name as name',
            db_prefix() .'posts.dateadded',
            db_prefix() .'posts.active',
            db_prefix() .'posts.order',
            db_prefix() .'posts.staffid',
            db_prefix() .'categories.id as category_id',
            db_prefix() .'categories.name as category_name',
            db_prefix() .'posts_translation.description as description',
            db_prefix() .'posts_translation.long_description as long_description',
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language_cod as language_cod',               
            db_prefix() .'languages.language as language',    
            'external_link',           
            'slug',
        ];  
        $this->db->select($columns);
        $this->db->where($where);

        $this->db->join(db_prefix() . 'posts_translation', db_prefix() . 'posts.id = ' . db_prefix() . 'posts_translation.postid', 'left');           
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'posts_translation.languageid', 'left');  
        $this->db->join(db_prefix() . 'posts_categories', db_prefix() . 'posts_categories.post_id = ' . db_prefix() . 'posts.id', 'left'); 
        $this->db->join(db_prefix() . 'categories', db_prefix() . 'categories.id = ' . db_prefix() . 'posts_categories.category_id', 'left'); 

        $this->db->group_by(db_prefix() . 'posts_translation.id');

        if (!empty($slug)) {
            $this->db->where(db_prefix() . 'posts.slug', $slug);
            $post = $this->db->get(db_prefix() . 'posts')->result();
            if ($post) {
                $post            = hooks()->apply_filters('post_get', $post);
                $GLOBALS['post'] = $post;

                return $post;                
            }
        }
        
        return null;
    } 

    /**
     * Add new posts
     * @param array $data posts $_POST data
     */    
	public function add($data)
	{
        $languages = $this->languages_model->get(null, ['active' => 1]);

        unset($data['null']);
        $data['dateadded']          = date('Y-m-d H:i:s');
        $data['description']        = nl2br($data['description']);
        $data['long_description']   = html_purify($data['long_description'], true);
        $data['slug']               = slug_it($data['name']);

        $post_categories = '';
        if (isset($data['categories'])) {
            $post_categories = $data['categories'];
            unset($data['categories']);
        }    

        $staff_id = '';
        if (isset($data['staffid'])) {
            $staff_id = $data['staffid'];
        }            

        $data = hooks()->apply_filters('before_add_post', $data);

        $this->db->insert(db_prefix() . 'posts', $data);
        $insert_id = $this->db->insert_id();     
        if ($insert_id) {
            if(isset($languages)){
                foreach($languages as $l) {
                    $this->db->insert(db_prefix() . 'posts_translation', array(
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'long_description' => $data['long_description'],
                        'languageid' => $l->languageid,
                        'postid' => $insert_id,
                    ));            
                }
            }

            if(isset($post_categories)){
                foreach ($post_categories as $category_id) {
                    $this->db->insert(db_prefix() . 'posts_categories', array(
                        'post_id' => $insert_id,
                        'category_id' => $category_id
                        )
                    ); 
                }             
            }  
             
            $this->log_activity($insert_id, $staff_id, '', 'project_activity_created');

            hooks()->do_action('after_add_post', $insert_id);
            logActivity('New Post Created [ID: ' . $insert_id . ']', 'add');

            return $insert_id;
        }   

        return false;
    }   

    /**
     * Update project info
     * @param  array $data project data
     * @param  mixed $id   project id
     * @return boolean
     */    
    public function update($data, $id)
	{  
        $languageid = '';
        if (isset($data['languageid'])) {
            $languageid  = $data['languageid'];
            unset($data['languageid']);
        }   

        $data['description']        = nl2br($data['description']);
        $data['long_description']   = html_purify($data['long_description'], true);
        $data['slug']               = slug_it($data['name']);

        $original_project = $this->get($id);
        $affectedRows = 0;

        $post_categories = '';
        if (isset($data['categories'])) {
            $post_categories = $data['categories'];
            unset($data['categories']);
        }   
        
        $staff_id = '';
        if (isset($data['staffid'])) {
            $staff_id = $data['staffid'];
            unset($data['staffid']);
        }           
        
        if (is_array($post_categories)) {
            $this->db->where('post_id', $id);
            $this->db->delete(db_prefix() . 'posts_categories');  

            foreach ($post_categories as $category_id) {
                $this->db->insert(db_prefix() . 'posts_categories', array(
                    'post_id' => $id,
                    'category_id' => $category_id
                    )
                );   
                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }                    
            }          
        }   

        $data = hooks()->apply_filters('before_update_post', $data, $id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'posts', array(
            'name' => $data['name'],
            'external_link' => $data['external_link'],
            //'slug' => $data['slug'],
        ));  
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }  

        if(isset($languageid)){
            $this->db->where('postid', $id);
            $this->db->where('languageid', $languageid);
            $this->db->update(db_prefix() . 'posts_translation', array(
                'name' => $data['name'],
                'long_description' => $data['long_description'],
                'description' => $data['description'],
            ));            
        }     
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }                
        
        if ($affectedRows > 0) {
            $this->log_activity($id, $staff_id, '', 'project_activity_updated');
            logActivity('Post Updated [ID:' . $id . ']', 'update');

            hooks()->do_action('after_update_post', $id);

            return true;            
        }

        return false;
    }    
    
    public function delete($post_id)
    {
        hooks()->do_action('before_post_deleted', $post_id);

        $post_name = get_post_name_by_id($post_id);

        $this->db->where('id', $post_id);
        $this->db->delete(db_prefix() . 'posts');      
        if ($this->db->affected_rows() > 0) {
            // Delete the custom field values
            $this->db->where('relid', $post_id);
            $this->db->where('fieldto', 'posts');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('post_id', $post_id);
            $this->db->delete(db_prefix() . 'posts_categories');

            $this->db->where('postid', $post_id);
            $this->db->delete(db_prefix() . 'posts_translation');            

            $files = $this->get_pictures($post_id);
            foreach ($files as $file) {
                $this->delete_picture($file->id);
            }  
            
            logActivity('Post Deleted [ID: ' . $post_id . ', Name: ' . $post_name . ']', 'deleted');
            
            hooks()->do_action('after_post_deleted', $post_id);   
            
            return true;
        }  

        return false;
    } 


    public function get_categories($post_id = '', $where = array())
    {
        $columns = [
            db_prefix() .'categories.id as id',
            db_prefix() .'posts_categories.category_id',
            db_prefix() .'posts_categories.post_id',
            db_prefix() .'categories_translation.name as name',
            db_prefix() .'categories_translation.description as description',
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language_cod as language_cod',
            db_prefix() .'languages.language as language',            
        ];         
        $this->db->select($columns);
        $this->db->where($where);
        $this->db->where(db_prefix() . 'posts_categories.post_id = ' . db_prefix() . 'posts_categories.post_id');
        
        $this->db->join(db_prefix() . 'posts_categories', db_prefix() . 'posts_categories.category_id = ' . db_prefix() . 'categories.id', 'left'); 
        $this->db->join(db_prefix() . 'categories_translation', db_prefix() . 'categories.id = ' . db_prefix() . 'categories_translation.categoryid', 'left');           
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'categories_translation.languageid', 'left');   
        $this->db->join(db_prefix() . 'posts',  db_prefix() . 'posts.id = ' . db_prefix() . 'posts_categories.post_id', 'left'); 

        $this->db->group_by(db_prefix() . 'categories_translation.id');

        if (is_numeric($post_id)) {
            $this->db->where(db_prefix() . 'posts_categories.post_id', $post_id);
        }   
        
        $categories = $this->db->get(db_prefix() . 'categories')->result();
       
        if ($categories) {
            return $categories;
        }

        return false;        
    }


    public function get_pictures($post_id = '', $where = array())
    {
        $this->db->where($where);
        if (is_numeric($post_id)) {
            $this->db->where('post_id', $post_id);

            return $this->db->get(db_prefix() . 'posts_pictures')->result();
        }

        return $this->db->get(db_prefix() . 'posts_pictures')->result();
    }

    public function get_picture($id, $post_id = false)
    {
        $this->db->where('post_id', $id);
        $file = $this->db->get(db_prefix() . 'posts_pictures')->row();

        if ($file && $post_id) {
            if ($file->post_id != $post_id) {
                return false;
            }
        }

        return $file;
    }  
 
    
    public function delete_picture($id, $logActivity = true)
    {
        hooks()->do_action('before_remove_post_file', $id);

        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . 'posts_pictures')->row();
        if ($file) {
            if (empty($file->external)) {
                $path     = get_upload_path_by_type('posts') . $file->post_id . '/';
                $fullPath = $path . $file->file_name;     
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                    $fname     = pathinfo($fullPath, PATHINFO_FILENAME);
                    $fext      = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $thumbPath = $path . $fname . '_thumb.' . $fext;

                    if (file_exists($thumbPath)) {
                        @unlink($thumbPath);
                    }                    
                }           
            }

            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'posts_pictures');  
            if ($logActivity) {
                $this->log_activity($file->post_id, '', '', 'post_activity_post_file_removed', $file->originalFilename, $file->visible_to_customer);
            }

            if (is_dir(get_upload_path_by_type('posts') . $file->post_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('posts') . $file->post_id);
                if (count($other_attachments) == 0) {
                    delete_dir(get_upload_path_by_type('posts') . $file->post_id);
                }
            }

            return true;
        }  
        
        return false;
    }    


    /**
     * Get project single project by id
     * @param  mixed $id postid
     * @return object
     */
    public function get_post($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'posts')->row();
    }    

    public function log_activity($post_id, $staff_id = '', $contact_id = '', $description_key, $additional_data = '', $visible_to_customer = 1)
    {
        if (!DEFINED('CRON')) {
            if (is_numeric($contact_id)) {
                $data['contact_id'] = $contact_id;
                $data['staff_id']   = 0;
                $data['fullname']   = get_contact_full_name($contact_id);
            } elseif (is_numeric($staff_id)) {
                $data['contact_id'] = 0;
                $data['staff_id']   = $staff_id;
                $data['fullname']   = get_staff_full_name($staff_id);
            }
        } else {
            $data['contact_id'] = 0;
            $data['staff_id']   = 0;
            $data['fullname']   = '[CRON]';
        }
        $data['description_key']     = $description_key;
        $data['additional_data']     = $additional_data;
        $data['visible_to_customer'] = $visible_to_customer;
        $data['post_id']          = $post_id;
        $data['dateadded']           = date('Y-m-d H:i:s');

        $data = hooks()->apply_filters('before_log_post_activity', $data);

        $this->db->insert(db_prefix() . 'post_activity', $data);
    }   
    
    public function get_activity($id = '', $limit = '', $only_post_members_activity = false)
    {
        if (is_numeric($id)) {
            $this->db->where('post_id', $id);
        }   
        if (is_numeric($limit)) {
            $this->db->limit($limit);
        }   
        $this->db->order_by('dateadded', 'desc'); 
        $activities = $this->db->get(db_prefix() . 'post_activity')->result_array();
        $i          = 0;    
        foreach ($activities as $activity) {
            $seconds          = get_string_between($activity['additional_data'], '<seconds>', '</seconds>');
            $other_lang_keys  = get_string_between($activity['additional_data'], '<lang>', '</lang>');
            $_additional_data = $activity['additional_data'];  
            
            if ($seconds != '') {
                $_additional_data = str_replace('<seconds>' . $seconds . '</seconds>', seconds_to_time_format($seconds), $_additional_data);
            }

            if ($other_lang_keys != '') {
                $_additional_data = str_replace('<lang>' . $other_lang_keys . '</lang>', _l($other_lang_keys), $_additional_data);
            }  
            
            if (strpos($_additional_data, 'post_status_') !== false) {
                $_additional_data = get_post_status_by_id(strafter($_additional_data, 'post_status_'));

                if (isset($_additional_data['name'])) {
                    $_additional_data = $_additional_data['name'];
                }
            }  
            
            $activities[$i]['description']     = _l($activities[$i]['description_key']);
            $activities[$i]['additional_data'] = $_additional_data;
            $activities[$i]['post_name']    = get_post_name_by_id($activity['post_id']);
            unset($activities[$i]['description_key']);
            $i++;            
        } 
        
        return $activities;
    }

    public function get_post_statuses()
    {
        $statuses = hooks()->apply_filters('before_get_post_statuses', [
            [
                'id'             => 1,
                'color'          => '#475569',
                'name'           => _l('post_status_1'),
                'order'          => 1,
                'filter_default' => true,
            ],
            [
                'id'             => 2,
                'color'          => '#2563eb',
                'name'           => _l('post_status_2'),
                'order'          => 2,
                'filter_default' => true,
            ],
            [
                'id'             => 3,
                'color'          => '#f97316',
                'name'           => _l('post_status_3'),
                'order'          => 3,
                'filter_default' => true,
            ],
            [
                'id'             => 4,
                'color'          => '#16a34a',
                'name'           => _l('post_status_4'),
                'order'          => 100,
                'filter_default' => false,
            ],
            [
                'id'             => 5,
                'color'          => '#94a3b8',
                'name'           => _l('post_status_5'),
                'order'          => 4,
                'filter_default' => false,
            ],
        ]);

        usort($statuses, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        return $statuses;
    }    
}