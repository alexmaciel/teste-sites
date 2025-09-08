<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Slides_model extends Api_Model
{
    public function __construct()
    {
        parent::__construct();
    }    

    /**
     * Get all
     *
     * @param array $where
     * @return void
     */
    public function getAll($where = array())
    {       
        $columns = [
            db_prefix() .'slides.id',
            db_prefix() .'slides.dateadded',
            db_prefix() .'slides.staffid',
            db_prefix() .'slides.active as active',
            db_prefix() .'slides_translation.name as name',
            db_prefix() .'slides_translation.description as description',
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language as language',   
            'order', 
            'link', 
            'mask', 
            'folder',       
        ];         
        $this->db->select($columns);

        $this->db->where($where);
        $this->db->join(db_prefix() . 'slides_translation', db_prefix() . 'slides.id = ' . db_prefix() . 'slides_translation.slideid', 'left');           
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'slides_translation.languageid', 'left');          

        $this->db->group_by(db_prefix() . 'slides_translation.id');
        $this->db->order_by('order', 'asc');        

        return $this->db->get(db_prefix() . 'slides')->result();            
    }

    /**
     * Get
     *
     * @param string $id
     * @param array $where
     * @return void
     */
    public function get($id = '', $where = array())
    {
        $columns = [
            db_prefix() .'slides.id as id',
            db_prefix() .'slides.dateadded',
            db_prefix() .'slides.staffid',
            db_prefix() .'slides.active',
            db_prefix() .'slides_translation.name as name',
            db_prefix() .'slides_translation.description as description',
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language_cod as language_cod',               
            db_prefix() .'languages.language as language',  
            'order', 
            'link', 
            'mask', 
            'folder',       
        ];     
        $this->db->select($columns);        
        
        $this->db->where($where);
        $this->db->join(db_prefix() . 'slides_translation', db_prefix() . 'slides.id = ' . db_prefix() . 'slides_translation.slideid', 'left');           
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'slides_translation.languageid', 'left');  

        $this->db->group_by(db_prefix() . 'slides_translation.id');

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'slides.id', $id);
            
            return $this->db->get(db_prefix() . 'slides')->row();
        }

        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'slides')->result();
    }      

    /**
     * Add
     *
     * @param [type] $data
     * @return void
     */
	public function add($data)
	{
        $languages = $this->languages_model->get(null, ['active' => 1]);

        unset($data['null']);
        $data['dateadded']      = date('Y-m-d H:i:s');
        $data['description']    = nl2br($data['description']);

        $data = hooks()->apply_filters('before_add_slide', $data);

        $this->db->insert(db_prefix() . 'slides', $data);
        $insert_id = $this->db->insert_id();     
        if ($insert_id) {
            if(isset($languages)){
                foreach($languages as $l) {
                    $this->db->insert(db_prefix() . 'slides_translation', array(
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'languageid' => $l->languageid,
                        'slideid' => $insert_id,
                    ));            
                }
            }

            hooks()->do_action('after_add_slide', $insert_id);
            logActivity('New Slide Created [ID: ' . $insert_id . ']', 'add');

            return $insert_id;
        }   

        return false;
    }

    /**
     * Update
     *
     * @param [type] $data
     * @param [type] $id
     * @return void
     */
    public function update($data, $id)
	{  
        $languageid = '';
        if (isset($data['languageid'])) {
            $languageid  = $data['languageid'];
            unset($data['languageid']);
        }  

        $affectedRows = 0;

        $data['description']    = nl2br($data['description']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'slides', array(
            'link' => $data['link'],
            'mask' => $data['mask'],
        ));  
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }          
        
        if(isset($languageid)){
            $this->db->where('slideid', $id);
            $this->db->where('languageid', $languageid);
            $this->db->update(db_prefix() . 'slides_translation', array(
                'name' => $data['name'],
                'description' => $data['description'],
            ));            
        }     
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }          

        if ($affectedRows > 0) {
            logActivity('Slide Updated [ID:' . $id . ']', 'update');

            hooks()->do_action('after_update_slide', $id);
            return true;
        }

        return false;
    }  
    
    public function delete($slideid)
    {
        hooks()->do_action('before_slide_deleted', $slideid);

        $this->db->where('id', $slideid);
        $this->db->delete(db_prefix() . 'slides');      
        if ($this->db->affected_rows() > 0) {

            $this->db->where('slideid', $slideid);
            $this->db->delete(db_prefix() . 'slides_translation');    

            $files = $this->get_pictures($slideid);
            foreach ($files as $file) {
                $this->delete_picture($file->id);
            }  
            
            return true;
        }  

        return false;
    }    

    public function upload_picture($data)
	{  
		$this->db->insert(db_prefix() . 'slides_pictures', $data);  
        $insert_id = $this->db->insert_id(); 
        if ($insert_id) {
        
            return $insert_id;
        }
        
        return false;        
    }      

    public function get_pictures($slideid = '', $where = array())
    {
        $this->db->where($where);
        if (is_numeric($slideid)) {
            $this->db->where('slideid', $slideid);

            return $this->db->get(db_prefix() . 'slides_pictures')->result();
        }

        return $this->db->get(db_prefix() . 'slides_pictures')->result();
    }

    public function get_picture($id, $slideid = false)
    {
        $this->db->where('slideid', $id);
        $file = $this->db->get(db_prefix() . 'slides_pictures')->row();

        if ($file && $slideid) {
            if ($file->slideid != $slideid) {
                return false;
            }
        }

        return $file;
    }    

    public function delete_picture($id)
    {
        hooks()->do_action('before_remove_slide_picture', $id);

        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . 'slides_pictures')->row();
        if ($file) {
            if (empty($file->external)) {
                $path     = get_upload_path_by_type('slides');
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
            $this->db->delete(db_prefix() . 'slides_pictures');  

            return true;
        }  
        
        return false;
    }
}