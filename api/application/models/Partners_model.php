<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Partners_model extends Api_Model
{
    public function __construct()
    {
        parent::__construct();
    }    

    public function getAll()
    {
        $columns = [
            'id',
            'name',
            'description',
            'file_name', 
            'folder',
            'dateadded',
            'staffid',
            'order',
        ];         
        $this->db->select($columns);
        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'partners')->result();            
    }

    /**
     * Get partners
     * @param  string $id    optional id
     * @param  array  $where partners where
     * @return mixed
     */
    public function get($id = '', $where = array())
    {
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return  $this->db->get(db_prefix() . 'partners')->row();

        }
        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'partners')->result();
    }  
    
    /**
     * Add new partner
     * @param array $data partner $_POST data
     */    
	public function add($data)
	{
        unset($data['null']);
        $data['dateadded']          = date('Y-m-d H:i:s');
        $data['description']        = nl2br($data['description']);
        //$data['long_description']   = html_purify($data['long_description'], true);

        $data = hooks()->apply_filters('before_add_partner', $data);

        $this->db->insert(db_prefix() . 'partners', $data);
        $insert_id = $this->db->insert_id();     
        if ($insert_id) {
            hooks()->do_action('after_add_partner', $insert_id);
            logActivity('New partners Created [ID: ' . $insert_id . ']', 'add');

            return $insert_id;
        }   

        return false;
    }  
    
    /**
     * Update partner info
     * @param  array $data partner data
     * @param  mixed $id   partner id
     * @return boolean
     */    
    public function update($data, $id)
	{  
        $data['description']        = nl2br($data['description']);
       // $data['long_description']   = html_purify($data['long_description'], true);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'partners', $data);  
        
        if ($this->db->affected_rows() > 0) {
            logActivity('Partner Updated [ID:' . $id . ']', 'update');

            hooks()->do_action('after_update_partner', $id);
            return true;
        }

        return false;
    }  
    
    public function delete($id)
    {
        hooks()->do_action('before_partner_deleted', $id);

        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . 'partners')->row();                
        if ($file) {
            $path     = get_upload_path_by_type('partners');
            $fullPath = $path . $file->file_name;     
            if ($fullPath && file_exists($fullPath)) {
                @unlink($fullPath);  
            }
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'partners');         
        
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }      
    
    public function delete_picture($id)
    {
        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . 'partners')->row();
        if ($file) {
            $path     = get_upload_path_by_type('partners');
            $fullPath = $path . $file->file_name;     
            if ($fullPath && file_exists($fullPath)) {
                @unlink($fullPath);  
            }

            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'partners', array(
                'file_name' => NULL
            ));  
        }  

        return true;
    }    
}