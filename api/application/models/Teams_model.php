<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Teams_model extends Api_Model
{
    public function __construct()
    {
        parent::__construct();
    }    

    /**
     * Get All
     *
     * @param array $where
     * @return void
     */
    public function getAll($where = array())
    {
        $columns = [
            db_prefix() .'teams.id as id',
            db_prefix() .'teams.folder',
            db_prefix() .'teams.dateadded',
            db_prefix() .'teams.staffid',
            db_prefix() .'teams.phonenumber', 
            db_prefix() .'teams.email', 
            db_prefix() .'teams.file_avatar', 
            db_prefix() .'teams.order',
            db_prefix() .'teams_translation.name as name',
            db_prefix() .'teams_translation.description as description',
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language as language',  
        ];         
        $this->db->select($columns);
        
        $this->db->where($where);
        $this->db->join(db_prefix() . 'teams_translation', db_prefix() . 'teams.id = ' . db_prefix() . 'teams_translation.teamid', 'left');           
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'teams_translation.languageid', 'left');        

        $this->db->group_by(db_prefix() . 'teams_translation.id');        
        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'teams')->result();            
    }

    /**
     * Get teams
     * @param  string $id    optional id
     * @param  array  $where teams where
     * @return mixed
     */
    public function get($id = '', $where = array())
    {
        $columns = [
            db_prefix() .'teams.id',
            db_prefix() .'teams.folder',
            db_prefix() .'teams.dateadded',
            db_prefix() .'teams.staffid',
            db_prefix() .'teams.phonenumber', 
            db_prefix() .'teams.email', 
            db_prefix() .'teams.file_avatar', 
            db_prefix() .'teams.order',
            db_prefix() .'teams_translation.name as name',
            db_prefix() .'teams_translation.description as description',
            db_prefix() .'languages.languageid as languageid',
            db_prefix() .'languages.language_cod as language_cod',               
            db_prefix() .'languages.language as language',            
        ];         
        $this->db->select($columns);
        $this->db->where($where);

        $this->db->join(db_prefix() . 'teams_translation', db_prefix() . 'teams.id = ' . db_prefix() . 'teams_translation.teamid', 'left');           
        $this->db->join(db_prefix() . 'languages',  db_prefix() . 'languages.languageid = ' . db_prefix() . 'teams_translation.languageid', 'left');        

        $this->db->group_by(db_prefix() . 'teams_translation.id');

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'teams.id', $id);

            $team =  $this->db->get(db_prefix() . 'teams')->row();
            $this->api_object_cache->set('teams-' . $team->name, $team);

            return $team;
        }
        $this->db->order_by('order', 'asc');

        $teams = $this->api_object_cache->get('teams-data');

        if (!$teams && !is_array($teams)) {
            $teams = $this->db->get(db_prefix() . 'teams')->result();
            $this->api_object_cache->add('teams-data', $teams);
        }
    
        return $teams; 
    }  
    
    /**
     * Add new team
     * @param array $data team $_POST data
     */    
	public function add($data)
	{
        $languages = $this->languages_model->get(null, ['active' => 1]);

        unset($data['null']);
        $data['dateadded']          = date('Y-m-d H:i:s');
        $data['description']        = nl2br($data['description']);

        $data = hooks()->apply_filters('before_add_team', $data);

        $this->db->insert(db_prefix() . 'teams', $data);
        $insert_id = $this->db->insert_id();     
        if ($insert_id) {
            foreach($languages as $l) {
                $this->db->insert(db_prefix() . 'teams_translation', array(
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'languageid' => $l->languageid,
                    'teamid' => $insert_id,
                ));            
            }

            hooks()->do_action('after_add_team', $insert_id);
            logActivity('New Team Created [ID: ' . $insert_id . ']', 'add');

            return $insert_id;
        }   

        return false;
    }  
    
    /**
     * Update team info
     * @param  array $data team data
     * @param  mixed $id   team id
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

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'teams', array(
            'name' => $data['name'],
            'phonenumber' => $data['phonenumber'],
            'email' => $data['email'],            
        ));  
        
        if(isset($languageid)){
            $this->db->where('teamid', $id);
            $this->db->where('languageid', $languageid);
            $this->db->update(db_prefix() . 'teams_translation', array(
                'name' => $data['name'],
                'description' => $data['description'],
            ));            
        }   

        if ($this->db->affected_rows() > 0) {
            logActivity('Team Updated [ID:' . $id . ']', 'update');

            hooks()->do_action('after_update_team', $id);
            return true;
        }

        return false;
    }  
    
    public function delete($id)
    {
        hooks()->do_action('before_team_deleted', $id);

        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . 'teams')->row();     
        if ($file) {
            $this->delete_picture($file->id);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'teams'); 

        $this->db->where('teamid', $id);
        $this->db->delete(db_prefix() . 'teams_translation'); 
        
        if ($this->db->affected_rows() > 0) {

            return true;
        }

        return false;
    }    
    
    
    public function delete_picture($id)
    {
        hooks()->do_action('before_remove_team_avatar', $id);

        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix() . 'teams')->row();
        if ($file) {
            $path     = get_upload_path_by_type('teams') . $file->id . '/';
            $fullPath = $path . $file->file_avatar;     
            if (file_exists($fullPath)) {
                @unlink($fullPath);
                $fname     = pathinfo($fullPath, PATHINFO_FILENAME);
                $fext      = pathinfo($fullPath, PATHINFO_EXTENSION);
                $thumbPath = $path . 'small_' . $fname  . '.' . $fext;

                if (file_exists($thumbPath)) {
                    @unlink($thumbPath);
                }                    
            } 

            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'teams', array(
                'file_avatar' => NULL
            ));     
    

            if (is_dir(get_upload_path_by_type('teams') . $file->id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('teams') . $file->id);
                if (count($other_attachments) > 0) {
                    delete_dir(get_upload_path_by_type('teams') . $file->id);
                }
            }            

            return true;
        }  

        return false;
    }  
    
    public function get_social($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'teams_social')->row();
        }

        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'teams_social')->result();        
    }

    public function get_social_teams($team_id = '')
    {
        $this->db->where('teamid', $team_id);

        $this->db->order_by('order', 'asc');

        return $this->db->get(db_prefix() . 'teams_social')->result();        
    }    

    public function add_social($data)
    {
        unset($data['null']);
        $data['dateadded']      = date('Y-m-d H:i:s');

        $data = hooks()->apply_filters('before_add_social', $data);

        $this->db->insert(db_prefix() . 'teams_social', $data);
        $insert_id = $this->db->insert_id();     
        if ($insert_id) {
            hooks()->do_action('after_add_social', $insert_id);
            logActivity('New Social Team Created [ID: ' . $insert_id . ']', 'add');

            return $insert_id;
        }   

        return false;        
    }  
    
    public function update_social($data, $id)
	{  
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'teams_social', $data);  
        
        if ($this->db->affected_rows() > 0) {
            logActivity('Client Social Updated [ID:' . $id . ']', 'update');

            hooks()->do_action('after_update_client_social', $id);

            return true;
        }

        return false;
    } 
    
    public function delete_social($id)
    {
        hooks()->do_action('before_teams_social_deleted', $id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'teams_social');  
        if ($this->db->affected_rows() > 0) {
            
            return true;
        }       
        
        return false;
    }          
}