<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Technology_model extends Api_Model
{
    public function __construct()
    {
        parent::__construct();
    }    

    /**
     * Get
     *
     * @param boolean $id
     * @param array $where
     * @return void
     */
    public function get($id = null, $where = array())
    {

        $columns = [
            db_prefix() .'technology.name',
            db_prefix() .'technology.description',
            db_prefix() .'technology.long_description',
            db_prefix() .'technology.folder',
            db_prefix() .'technology.staffid',
            db_prefix() .'technology.dateupdated',
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language_cod as language_cod',               
            db_prefix() .'languages.language as language'
        ];         
        $this->db->select($columns);

        $this->db->where($where);          
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'technology.languageid', 'left');  

        return  $this->db->get(db_prefix() . 'technology')->result();
    }  


    /**
     * Get Items
     *
     * @param string $id
     * @param array $where
     * @return void
     */
    public function get_items($id = '', $where = array())
    {

        $columns = [
            db_prefix() .'technology_items.id',
            db_prefix() .'technology_items.staffid',
            db_prefix() .'technology_items.file_name',
            db_prefix() .'technology_items.folder',
            db_prefix() .'technology_items.visible_draft',
            db_prefix() .'technology_items.dateadded',
            db_prefix() .'technology_items.order',
            db_prefix() .'technology_items_translation.name as name',
            db_prefix() .'technology_items_translation.description as description',            
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language_cod as language_cod',               
            db_prefix() .'languages.language as language', 
        ];           
        $this->db->select($columns);

        $this->db->where($where);
        $this->db->join(db_prefix() . 'technology_items_translation', db_prefix() . 'technology_items.id = ' . db_prefix() . 'technology_items_translation.itemid', 'left');           
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'technology_items_translation.languageid', 'left');  

        $this->db->group_by(db_prefix() . 'technology_items_translation.id');

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'technology_items.id', $id);

            return $this->db->get(db_prefix() . 'technology_items')->row();
        }

        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'technology_items')->result();        
    }   
    

    /**
     * Get Pictures
     *
     * @param string $id
     * @param array $where
     * @return void
     */
    public function get_pictures($id = '',  $where = array())
    {
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'technology_pictures')->row();
        }

        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'technology_pictures')->result();
    }     
    
    
    /**
     * Get Videos
     *
     * @param array $where
     * @return void
     */
    public function get_videos($where = array())
    {
        $this->db->where($where);
        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'technology_videos')->result();
    }   
    
    /**
     * Get Video id
     *
     * @param [type] $id
     * @return void
     */
    public function get_video($id)
    {
        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . 'technology_videos')->row();

        return $file;
    }  

    /**
     * Add Video
     *
     * @param [type] $data
     * @return void
     */
    public function add_video($data)
    {
        $data['dateadded']              = date('Y-m-d H:i:s');
        $data['description']            = nl2br($data['description']);
        $data['visible_to_customer']    = $data['visible_to_customer'];

        $data = hooks()->apply_filters('before_add_video', $data);

        $this->db->insert(db_prefix() . 'technology_videos', $data);
        $insert_id = $this->db->insert_id();     
        if ($insert_id) {
            hooks()->do_action('after_add_video', $insert_id);
            logActivity('New Video Company Created [ID: ' . $insert_id . ']', 'add');

            return $insert_id;
        }   

        return false;        
    }        
    
    /**
     * Delete Video
     *
     * @param [type] $id
     * @param boolean $logActivity
     * @return void
     */
    public function delete_video($id, $logActivity = true)
    {
        hooks()->do_action('before_remove_technology_video', $id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'technology_videos');  

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        
        return false;
    }     
}