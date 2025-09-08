<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Handles upload for project files
 * @param  mixed $project_id project id
 * @return boolean
 */

function handle_project_pictures_uploads($project_id, $staffid)
{
    $filesIDS = [];
    $errors   = [];

    if (isset($_FILES['file']['name'])
        && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)) {
        hooks()->do_action('before_upload_project_attachment', $project_id);

        if (!is_array($_FILES['file']['name'])) {
            $_FILES['file']['name']     = [$_FILES['file']['name']];
            $_FILES['file']['type']     = [$_FILES['file']['type']];
            $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
            $_FILES['file']['error']    = [$_FILES['file']['error']];
            $_FILES['file']['size']     = [$_FILES['file']['size']];
        }

        $path = get_upload_path_by_type('projects') . $project_id . '/';

        for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
            if (_api_upload_error($_FILES['file']['error'][$i])) {
                $errors[$_FILES['file']['name'][$i]] = _api_upload_error($_FILES['file']['error'][$i]);

                return array(
                    'message' => $errors[$_FILES['file']['name'][$i]]
                );         
                return false; 
            }

            // Get the temp file path
            $tmpFilePath = $_FILES['file']['tmp_name'][$i];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $originalFilename = unique_filename($path, $_FILES['file']['name'][$i]);
                $filename = app_generate_hash() . '.' . get_file_extension($originalFilename);
                
                // In case client side validation is bypassed
                if (!_upload_pictures_allowed($filename)) {
                    return array(
                        'message' => 'Image extension not allowed. Extensions: ' . get_option('site_pic_types')
                    );                  
                    return false;
                }

                $newFilePath = $path . $filename;
                // Upload the file into the company uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $CI = & get_instance();
                    
                    $data = array(
                        'project_id' => $project_id,
                        'file_name'  => $filename,
                        'original_file_name'  => $originalFilename,
                        'filetype'   => $_FILES['file']['type'][$i],
                        'dateadded'  => date('Y-m-d H:i:s'),
                        'staffid'    => $staffid,
                        'subject'    => $originalFilename,
                    );
                    
                    $CI->db->insert(db_prefix() . 'projects_pictures', $data);
                    $insert_id = $CI->db->insert_id();
                    if ($insert_id) {
                        if (is_image($newFilePath)) {
                            create_img_thumb($path, $filename);                            
                        }
                        array_push($filesIDS, $insert_id);
                    } else {
                        unlink($newFilePath);

                        return false;
                    }            
                } 
            }            
        }
    }

    if (count($filesIDS) > 0) {
        return true;
    }    

    return false;
}
/**
 * Handles upload for project files
 * @param  mixed $project_id project id
 * @return boolean
 */
function handle_project_picture_uploads($project_id, $staffid, $subject = '', $description = '')
{
    $errors     = '';
    $message    = '';

    if (isset($_FILES['file']) && _api_upload_error($_FILES['file']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['file']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        $path = get_upload_path_by_type('projects') . $project_id . '/';
        
        hooks()->do_action('before_upload_project_picture', $project_id);  
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];   
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $originalFilename   = unique_filename($path, $_FILES['file']['name']);
            $filename           = app_generate_hash() . '.' . get_file_extension($originalFilename);
         
            // In case client side validation is bypassed
            if (!_upload_pictures_allowed($filename)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . get_option('site_pic_types')
                );                  
                return false; 
            }

            $newFilePath = $path . $filename;      
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI = & get_instance();

                $data = array(
                    'project_id'    => $project_id,
                    'file_name'  => $filename,
                    'original_file_name'  => $originalFilename,
                    'filetype'   => $_FILES['file']['type'],
                    'dateadded'  => date('Y-m-d H:i:s'),
                    'staffid'    => $staffid,
                    'subject'    => $subject,                           
                    'description' => $description,                    
                );

                $CI->db->insert(db_prefix() . 'projects_pictures', $data);
                $insert_id = $CI->db->insert_id();
                if ($insert_id) {
                    if (is_image($newFilePath)) {
                        //create_img_thumb($path, $filename);
                        $config = array(
                            'image_library' => 'gd2',
                            'source_image' => $newFilePath,
                            'new_image' => $path,
                            'maintain_ratio' => true,
                            'create_thumb' => true,
                            'thumb_marker' => '_thumb',
                            'width' => hooks()->apply_filters('project_image_thumb_width', 800),
                            'height' => hooks()->apply_filters('project_image_thumb_height', 800)
                        );
                        $CI->image_lib->initialize($config);
                        $CI->image_lib->resize();
                        $CI->image_lib->clear();

                        $additional_data = $originalFilename;
                        $CI->projects_model->log_activity($project_id, $staffid, '', 'project_activity_uploaded_file', $additional_data);                       
                    }                    
                } else {
                    @unlink($newFilePath);

                    return false;                    
                }
                
                return true;
            }           
        }  
    }

    return false;
}
/**
 * Handles upload for project files
 * @param  mixed $project_id project id
 * @return boolean
 */
function handle_project_map_uploads($project_id, $staffid, $subject = '', $description = '')
{
    $errors     = '';
    $message    = '';

    if (isset($_FILES['file']) && _api_upload_error($_FILES['file']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['file']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        $path = get_upload_path_by_type('projects') . $project_id . '/';
        
        hooks()->do_action('before_upload_project_picture', $project_id);  
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];   
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $originalFilename   = unique_filename($path, $_FILES['file']['name']);
            $filename           = app_generate_hash() . '.' . get_file_extension($originalFilename);
         
            // In case client side validation is bypassed
            if (!_upload_pictures_allowed($filename)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . get_option('site_pic_types')
                );                  
                return false; 
            }

            $newFilePath = $path . $filename;      
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI = & get_instance();

                $data = array(
                    'project_id'    => $project_id,
                    'file_name'  => $filename,
                    'original_file_name'  => $originalFilename,
                    'filetype'   => $_FILES['file']['type'],
                    'dateadded'  => date('Y-m-d H:i:s'),
                    'staffid'    => $staffid,
                    'subject'    => $subject,                           
                    'description' => $description,                    
                );

                $CI->db->insert(db_prefix() . 'projects_maps', $data);
                $insert_id = $CI->db->insert_id();
                if ($insert_id) {
                    if (is_image($newFilePath)) {
                        //create_img_thumb($path, $filename);
                        $config = array(
                            'image_library' => 'gd2',
                            'source_image' => $newFilePath,
                            'new_image' => $path,
                            'maintain_ratio' => true,
                            'create_thumb' => true,
                            'thumb_marker' => '_thumb',
                            'width' => hooks()->apply_filters('project_image_thumb_width', 500),
                            'height' => hooks()->apply_filters('project_image_thumb_height', 500)
                        );
                        $CI->image_lib->initialize($config);
                        $CI->image_lib->resize();
                        $CI->image_lib->clear();

                        $additional_data = $originalFilename;
                        $CI->projects_model->log_activity($project_id, $staffid, '', 'project_activity_uploaded_file', $additional_data);                       
                    }                    
                } else {
                    @unlink($newFilePath);

                    return false;                    
                }
                
                return true;
            }           
        }  
    }

    return false;
}
/**
 * Handles upload for post files
 * @param  mixed $post_id post id
 * @return boolean
 */
function handle_post_picture_uploads($post_id, $staffid, $subject = '', $description = '')
{
    $errors     = '';
    $message    = '';

    if (isset($_FILES['file']) && _api_upload_error($_FILES['file']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['file']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        $path = get_upload_path_by_type('posts') . $post_id . '/';
        
        hooks()->do_action('before_upload_post_picture', $post_id);  
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];   
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $originalFilename   = unique_filename($path, $_FILES['file']['name']);
            $filename           = app_generate_hash() . '.' . get_file_extension($originalFilename);
         
            // In case client side validation is bypassed
            if (!_upload_pictures_allowed($filename)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . get_option('site_pic_types')
                );                  
                return false; 
            }

            $newFilePath = $path . $filename;      
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI = & get_instance();

                $data = array(
                    'post_id'    => $post_id,
                    'file_name'  => $filename,
                    'original_file_name'  => $originalFilename,
                    'filetype'   => $_FILES['file']['type'],
                    'dateadded'  => date('Y-m-d H:i:s'),
                    'staffid'    => $staffid,
                    'subject'    => $subject,                           
                    'description' => $description,                    
                );

                $CI->db->insert(db_prefix() . 'posts_pictures', $data);
                $insert_id = $CI->db->insert_id();
                if ($insert_id) {
                    if (is_image($newFilePath)) {
                        $config = array(
                            'image_library' => 'gd2',
                            'source_image' => $newFilePath,
                            'new_image' => $path,
                            'maintain_ratio' => true,
                            'create_thumb' => true,
                            'thumb_marker' => '_thumb',
                            'width' => hooks()->apply_filters('post_image_thumb_width', 500),
                            'height' => hooks()->apply_filters('post_image_thumb_height', 500)
                        );
                        $CI->image_lib->initialize($config);
                        $CI->image_lib->resize();
                        $CI->image_lib->clear();
                                                
                        $additional_data = $originalFilename;
                        $CI->posts_model->log_activity($post_id, $staffid, '', 'post_activity_uploaded_file', $additional_data);                       
                    }                    
                } else {
                    @unlink($newFilePath);

                    return false;                    
                }
                
                return true;
            }           
        }  
    }

    return false;
}
/**
 * Handles upload for slide files
 * @param  mixed $slide_id slide id
 * @return boolean
 */
function handle_slide_picture_uploads($slideid, $staffid, $subject = '', $description = '')
{
    $errors     = '';
    $message    = '';

    if (isset($_FILES['file']) && _api_upload_error($_FILES['file']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['file']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        $path = get_upload_path_by_type('slides');
        
        hooks()->do_action('before_upload_slide_picture', $slideid);  
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];   
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $originalFilename   = unique_filename($path, $_FILES['file']['name']);
            $filename           = app_generate_hash() . '.' . get_file_extension($originalFilename);
         
            // In case client side validation is bypassed
            if (!_upload_pictures_allowed($filename)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . get_option('site_pic_types')
                );                  
                return false; 
            }

            $newFilePath = $path . $filename;      
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI = & get_instance();
                
                $data = array(
                    'slideid'    => $slideid,
                    'file_name'  => $filename,
                    'original_file_name'  => $originalFilename,
                    'filetype'   => $_FILES['file']['type'],
                    'dateadded'  => date('Y-m-d H:i:s'),
                    'staffid'    => $staffid,
                    'subject'    => $subject,                    
                    'description'    => $description,                    
                ); 

                $CI->slides_model->upload_picture($data); 
                
                return true;
            }           
        }  
    }

    return false;
}
/**
 * Handles upload for company pictures
 * @return boolean
 */
function handle_company_picture_uploads($staffid, $subject = '', $description = '')
{

    if (isset($_FILES['file']) && _api_upload_error($_FILES['file']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['file']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        $path = get_upload_path_by_type('company');
        
        hooks()->do_action('before_upload_company_picture', $staffid);  
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];   
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $originalFilename   = unique_filename($path, $_FILES['file']['name']);
            $filename           = app_generate_hash() . '.' . get_file_extension($originalFilename);
         
            // In case client side validation is bypassed
            if (!_upload_pictures_allowed($filename)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . get_option('site_pic_types')
                );                  
                return false; 
            }

            $newFilePath = $path . $filename;      
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI = & get_instance();

                $data = array(
                    'file_name'  => $filename,
                    'original_file_name'  => $originalFilename,
                    'filetype'   => $_FILES['file']['type'],
                    'dateadded'  => date('Y-m-d H:i:s'),
                    'staffid'    => $staffid,
                    'subject'    => $subject,                    
                    'description'    => $description,                    
                );            

                $CI->company_model->upload_picture($data);

                return true;
            }       
        }  
    }

    return false;
}
/**
 * Partner file
 * @param  mixed $id Partner ID to add file
 * @return array  - Result values
 */
function handle_partner_file_uploads($id)
{
    $message    = '';

    if (isset($_FILES['file']) && _api_upload_error($_FILES['file']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['file']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        $path = get_upload_path_by_type('partners');
        
        hooks()->do_action('before_upload_partner_file');
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);  
            $filename   = unique_filename($path, $_FILES['file']['name']);
         
            // In case client side validation is bypassed
            if (!_upload_pictures_allowed($filename)) {
                return array(
                    'message' => _l('settings_allowed_upload_file_types') . get_option('site_pic_types')
                );                  
                return false; 
            }            
            
            $CI = & get_instance();
                 
            // Remove old image  
            $CI->db->where('id', $id);
            $_file = $CI->db->get(db_prefix() . 'partners')->row();
            $_filename = $path . $_file->file_name;
            if($_filename && file_exists($path . $_file->file_name)) {
                @unlink($_filename);
            }	  
            
            $newFilePath = $path . $filename;                           
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {

                $CI->db->where('id', $id);
                $CI->db->update(db_prefix() . 'partners', [
                    'file_name' => $filename,
                ]);

                return true; 
            }                         
        }       
    }

    return false;
}
/**
 * Maybe upload team profile image
 * @param  string $team_id team_id or current logged in team id will be used if not passed
 * @return boolean
 */
function handle_teams_avatar_uploads($team_id)
{
    $message    = '';

    if (isset($_FILES['file_avatar']) && _api_upload_error($_FILES['file_avatar']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['file_avatar']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['file_avatar']['name']) && $_FILES['file_avatar']['name'] != '') {
        $path = get_upload_path_by_type('teams') . $team_id . '/';
        
        hooks()->do_action('before_upload_team_file_avatar');
        // Get the temp file path
        $tmpFilePath = $_FILES['file_avatar']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);  
            $filename    = unique_filename($path, $_FILES['file_avatar']['name']); 
            /*
            // Getting file extension
            $extension = strtolower(pathinfo($_FILES['file_avatar']['name'], PATHINFO_EXTENSION));   
            $allowed_extensions = explode(',', get_option('avatar_types'));
            $allowed_extensions = array_map('trim', $allowed_extensions);

            $allowed_extensions = hooks()->apply_filters('team_file_avatar_upload_allowed_extensions', $allowed_extensions);

            if (!in_array($extension, $allowed_extensions)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . get_option('avatar_types')
                );                  
                return false; 
            }   
            */

            // In case client side validation is bypassed
            if (!_upload_avatar_allowed($filename)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . get_option('avatar_types')
                );                  
                return false; 
            }

            $CI = & get_instance();
                 
            // Remove old image  
            $CI->db->where('id', $team_id);
            $_file = $CI->db->get(db_prefix() . 'teams')->row();
            $_filename = $path . $_file->file_avatar;
            if($_filename && file_exists($path . $_file->file_avatar)) {
                @unlink($_filename);
            }	  
            
            $newFilePath = $path . $filename;                           
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $config                   = [];
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'small_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('contact_file_avatar_small_width', 250);
                $config['height']         = hooks()->apply_filters('contact_file_avatar_small_height', 250);
               
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->image_lib->clear();

                $CI->db->where('id', $team_id);
                $CI->db->update(db_prefix() . 'teams', [
                    'file_avatar' => $filename,
                ]);            

                return true; 
            }                         
        }       
    }

    return false;
}
/**
 * Category file
 * @param  mixed $id Category ID to add file
 * @return array  - Result values
 */
function handle_category_file_uploads($id)
{
    $message    = '';

    if (isset($_FILES['file']) && _api_upload_error($_FILES['file']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['file']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        $path = get_upload_path_by_type('categories_icons');
        
        hooks()->do_action('before_upload_category_file');
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);  
            $filename    = unique_filename($path, $_FILES['file']['name']); 
            // Getting file extension
            $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));   
            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
                'svg'
            ];

            $allowed_extensions = hooks()->apply_filters('contact_category_file_upload_allowed_extensions', $allowed_extensions);

            if (!in_array($extension, $allowed_extensions)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . get_option('site_pic_types')
                );                  
                return false; 
            }                   
            $CI = & get_instance();
                 
            // Remove old image  
            $CI->db->where('id', $id);
            $_file = $CI->db->get(db_prefix() . 'categories')->row();
            $_filename = $path . $_file->file_name;
            if($_filename && file_exists($path . $_file->file_name)) {
                @unlink($_filename);
            }	  
            
            $newFilePath = $path . $filename;                           
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {

                $CI->db->where('id', $id);
                $CI->db->update(db_prefix() . 'categories', [
                    'file_name' => $filename,
                ]);
                // Remove original image
                //unlink($newFilePath);

                return true; 
            }                         
        }       
    }

    return false;
}
/**
 * Clients file
 * @param  mixed $userid Client ID to add file
 * @return array  - Result values
 */
function handle_client_file_uploads($userid)
{
    $message    = '';

    if (isset($_FILES['file']) && _api_upload_error($_FILES['file']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['file']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        $path = get_upload_path_by_type('client_logo_images') . $userid . '/';
        
        hooks()->do_action('before_upload_client_file');
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);  
            $filename    = unique_filename($path, $_FILES['file']['name']); 
            // Getting file extension
            $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));   
            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png'
            ];

            $allowed_extensions = hooks()->apply_filters('contact_client_file_upload_allowed_extensions', $allowed_extensions);

            if (!in_array($extension, $allowed_extensions)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . $allowed_extensions
                );                  
                return false; 
            }                   
            $CI = & get_instance();
                 
            // Remove old image  
            $CI->db->where('userid', $userid);
            $_file = $CI->db->get(db_prefix() . 'clients')->row();
            $_filename = $path . $_file->logo_image;
            if($_filename && file_exists($path . $_file->logo_image)) {
                @unlink($_filename);
            }	  
            
            $newFilePath = $path . $filename;                           
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $config                   = [];
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                //$config['new_image']      = 'thumb_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('contact_client_logo_thumb_width', 320);
                $config['height']         = hooks()->apply_filters('contact_client_logo_thumb_height', 320);
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->image_lib->clear();
                $CI->db->where('userid', $userid);
                $CI->db->update(db_prefix() . 'clients', [
                    'logo_image' => $filename,
                ]);
                // Remove original image
                //unlink($newFilePath);

                return true; 
            }                         
        }       
    }

    return false;
}
/**
 * Maybe upload contact profile image
 * @param  string $contact_id contact_id or current logged in contact id will be used if not passed
 * @return boolean
 */
function handle_contact_profile_image_upload($contact_id)
{
    $message    = '';

    if (isset($_FILES['profile_image']) && _api_upload_error($_FILES['profile_image']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['profile_image']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['profile_image']['name']) && $_FILES['profile_image']['name'] != '') {
        $path = get_upload_path_by_type('contact_profile_images') . $contact_id . '/';
        
        hooks()->do_action('before_upload_contact_profile_image');
        // Get the temp file path
        $tmpFilePath = $_FILES['profile_image']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);  
            $filename    = unique_filename($path, $_FILES['profile_image']['name']); 
            // Getting file extension
            $extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));   
            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
            ];

            $allowed_extensions = hooks()->apply_filters('contact_profile_image_upload_allowed_extensions', $allowed_extensions);

            if (!in_array($extension, $allowed_extensions)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . get_option('site_pic_types')
                );                  
                return false; 
            }                   
            $CI = & get_instance();
                 
            // Remove old image  
            $CI->db->where('id', $contact_id);
            $_file = $CI->db->get(db_prefix() . 'contacts')->row();
            $_filename = $path . $_file->profile_image;
            if($_filename && file_exists($path . $_file->profile_image)) {
                @unlink($_filename);
            }	  
            
            $newFilePath = $path . $filename;                           
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $config                   = [];
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'small_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('contact_profile_image_small_width', 150);
                $config['height']         = hooks()->apply_filters('contact_profile_image_small_height', 150);
               
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->image_lib->clear();

                $CI->db->where('id', $contact_id);
                $CI->db->update(db_prefix() . 'contacts', [
                    'profile_image' => $filename,
                ]);
                // Remove original image
                unlink($newFilePath);                

                return true; 
            }                         
        }       
    }

    return false;
}
/**
 * Maybe upload profile avatar
 * @param  string $profile_id profile_id or current logged in contact id will be used if not passed
 * @return boolean
 */
function handle_profile_image_upload($profile_id)
{
    $message    = '';

    if (isset($_FILES['avatar']) && _api_upload_error($_FILES['avatar']['error'])) {   
        return array(
            'message' => _api_upload_error($_FILES['avatar']['error'])
        );         
        return false; 
    }    
    if (isset($_FILES['avatar']['name']) && $_FILES['avatar']['name'] != '') {
        $path = get_upload_path_by_type('staff') . $profile_id . '/';
        
        hooks()->do_action('before_upload_profile_avatar');
        // Get the temp file path
        $tmpFilePath = $_FILES['avatar']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);  
            $filename    = unique_filename($path, $_FILES['avatar']['name']); 
            
            $allowed_extensions = hooks()->apply_filters('profile_avatar_upload_allowed_extensions', $allowed_extensions);
            // In case client side validation is bypassed
            if (!_upload_avatar_allowed($filename)) {
                return array(
                    'message' => 'Image extension not allowed. Extensions: ' . get_option('avatar_types')
                );                  
                return false; 
            }

            $CI = & get_instance();
                 
            // Remove old image  
            $CI->db->where('staffid', $profile_id);
            $_file = $CI->db->get(db_prefix() . 'staff')->row();
            $_filename = $path . $_file->avatar;
            if($_filename && file_exists($path . $_file->avatar)) {
                @unlink($_filename);
                $fname     = pathinfo($_filename, PATHINFO_FILENAME);
                $fext      = pathinfo($_filename, PATHINFO_EXTENSION);
                $thumbPath_small = $path . 'small_' . $fname  . '.' . $fext;
                $thumbPath_thumb = $path . 'thumb_' . $fname  . '.' . $fext;

                if (file_exists($thumbPath_small)) {
                    @unlink($thumbPath_small);
                }  
                
                if (file_exists($thumbPath_thumb)) {
                    @unlink($thumbPath_thumb);
                }                   
            }	  
            
            $newFilePath = $path . $filename;                           
            // Upload the file into the staff uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI                       = & get_instance();
                $config                   = [];
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'thumb_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = 320;
                $config['height']         = 320;
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->image_lib->clear();
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'small_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = 96;
                $config['height']         = 96;

                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->image_lib->clear();

                $CI->db->where('staffid', $profile_id);
                $CI->db->update(db_prefix() . 'staff', [
                    'avatar' => $filename,
                ]);
                // Remove original image
                unlink($newFilePath);

                return true;
            }                       
        }       
    }

    return false;
}
/**
 * Maybe upload staff profile image
 * @param  string $staff_id staff_id or current logged in staff id will be used if not passed
 * @return boolean
 */
function handle_admin_avatar_upload($staff_id = '')
{

    if (isset($_FILES['adminAvatar']['name']) && $_FILES['adminAvatar']['name'] != '') {
        do_action('before_upload_admin_avatar');
        $path = get_upload_path_by_type('avatars');
        // Get the temp file path
        $tmpFilePath = $_FILES['adminAvatar']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $extension = strtolower(pathinfo($_FILES['adminAvatar']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
            ];

            if (!in_array($extension, $allowed_extensions)) {
                //set_alert('warning', _l('file_php_extension_blocked'));

                return false;
            }
            //_maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['adminAvatar']['name']);
            $newFilePath = $path . '/' . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI                       = & get_instance();
                $config                   = [];
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'thumb_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = 320;
                $config['height']         = 320;
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->image_lib->clear();
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'small_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = 96;
                $config['height']         = 96;
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->db->where('adminId', $staff_id);
                $CI->db->update('admins', [
                    'adminAvatar' => $filename,
                ]);
                // Remove original image
                unlink($newFilePath);

                return true;
            }
        }
    }

    return false;
}
/**
 * Check if path exists if not exists will create one
 * This is used when uploading files
 * @param  string $path path to check
 * @return null
 */
function _maybe_create_upload_path($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0755);
        fopen(rtrim($path, '/') . '/' . 'index.html', 'w');
    }
}

function create_img_thumb($path, $filename, $width = 1000, $height = 1000)
{
    $CI = & get_instance();

    $source_path = rtrim($path, '/') . '/' . $filename;
    $target_path = $path;
    $config_manip = array(
        'image_library' => 'gd2',
        'source_image' => $source_path,
        'new_image' => $target_path,
        'maintain_ratio' => true,
        'create_thumb' => true,
        'thumb_marker' => '_thumb',
        'width' => $width,
        'height' => $height
    );

    $CI->image_lib->initialize($config_manip);
    $CI->image_lib->resize();
    $CI->image_lib->clear();
}

function create_img_posts_thumb($path, $filename, $width = 520, $height = 520)
{
    $CI = &get_instance();

    $source_path = rtrim($path, '/') . '/' . $filename;
    $target_path = $path;
    $config_manip = array(
        'image_library'  => 'gd2',
        'source_image'   => $source_path,
        'new_image'      => $target_path,
        'maintain_ratio' => true,
        'create_thumb'   => true,
        'thumb_marker'   => '_thumb',
        'width'          => $width,
        'height'         => $height,
    );

    $CI->image_lib->initialize($config_manip);
    $CI->image_lib->resize();
    $CI->image_lib->clear();
}
/**
 * Handles uploads error with translation texts
 * @param  mixed $error type of error
 * @return mixed
 */
function _api_upload_error($error)
{
    // Get the Max Upload Size allowed
    $maxUpload = (int)(ini_get('upload_max_filesize'));  

    $uploadErrors = [
        0 => _l('file_uploaded_success'),
        1 => _l('file_exceeds_max_filesize') . '. Maximum size: ' . $maxUpload . 'MB',
        2 => _l('file_exceeds_maxfile_size_in_form'),
        3 => _l('file_uploaded_partially'),
        4 => _l('file_not_uploaded'),
        6 => _l('file_missing_temporary_folder'),
        7 => _l('file_failed_to_write_to_disk'),
        8 => _l('file_php_extension_blocked'),
    ];

    if (isset($uploadErrors[$error]) && $error != 0) {
        return $uploadErrors[$error];
    }

    return false;
}
/**
 * Check if extension is allowed for upload
 * @param  string $filename filename
 * @return boolean
 */
function _upload_extension_allowed($filename)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $browser = get_instance()->agent->browser();

    $allowed_extensions = explode(',', get_option('allowed_files'));
    $allowed_extensions = array_map('trim', $allowed_extensions);

    //  https://discussions.apple.com/thread/7229860
    //  Used in main.js too for Dropzone
    if (strtolower($browser) === 'safari'
        && in_array('.jpg', $allowed_extensions)
        && !in_array('.jpeg', $allowed_extensions)
    ) {
        $allowed_extensions[] = '.jpeg';
    }
    // Check for all cases if this extension is allowed
    if (!in_array('.' . $extension, $allowed_extensions)) {
        return false;
    }

    return true;
}
/**
 * Check if extension is allowed for upload
 * @param  string $filename filename
 * @return boolean
 */
function _upload_pictures_allowed($filename)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $browser = get_instance()->agent->browser();

    $allowed_extensions = explode(',', get_option('site_pic_types'));
    $allowed_extensions = array_map('trim', $allowed_extensions);

    //  https://discussions.apple.com/thread/7229860
    //  Used in main.js too for Dropzone
    if (strtolower($browser) === 'safari'
        && in_array('.jpg', $allowed_extensions)
        && !in_array('.jpeg', $allowed_extensions)
    ) {
        $allowed_extensions[] = '.jpeg';
    }
    // Check for all cases if this extension is allowed
    if (!in_array('.' . $extension, $allowed_extensions)) {
        return false;
    }

    return true;
}
/**
 * Check if extension is allowed for upload
 * @param  string $filename filename
 * @return boolean
 */
function _upload_avatar_allowed($filename)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $browser = get_instance()->agent->browser();

    $allowed_extensions = explode(',', get_option('avatar_types'));
    $allowed_extensions = array_map('trim', $allowed_extensions);

    //  https://discussions.apple.com/thread/7229860
    //  Used in main.js too for Dropzone
    if (strtolower($browser) === 'safari'
        && in_array('.jpg', $allowed_extensions)
        && !in_array('.jpeg', $allowed_extensions)
    ) {
        $allowed_extensions[] = '.jpeg';
    }
    // Check for all cases if this extension is allowed
    if (!in_array('.' . $extension, $allowed_extensions)) {
        return false;
    }

    return true;
}
/**
 * Function that return full path for upload based on passed type
 * @param  string $type
 * @return string
 */
function get_upload_path_by_type($type)
{
    $path = '';
    switch ($type) {
        case 'avatars':
            $path = AVATAR_ATTACHMENTS_FOLDER;

        break;   
        case 'slides':
            $path = SLIDES_UPLOADS_FOLDER;

        break;  
        case 'categories':
            $path = CATEGORIES_UPLOADS_FOLDER;

        break;      
        case 'company':
            $path = COMPANY_UPLOADS_FOLDER;

        break;   
        case 'staff':
            $path = STAFF_UPLOADS_FOLDER;
    
        break;
        case 'client_logo_images':
            $path = CLIENT_LOGO_IMAGES_FOLDER;
    
        break; 
        case 'contact_profile_images':
            $path = CONTACT_PROFILE_IMAGES_FOLDER;
    
        break;                       
        case 'services':
            $path = SERVICES_UPLOADS_FOLDER;

        break;   
        case 'teams':
            $path = TEAMS_UPLOADS_FOLDER;

        break;   
        case 'partners':
            $path = PARTNERS_UPLOADS_FOLDER;            

        break;  
        case 'projects':
            $path = PROJECTS_UPLOADS_FOLDER;

        break; 
        case 'posts':
            $path = POSTS_UPLOADS_FOLDER;

        break;                                           
    }

    return hooks()->apply_filters('get_upload_path_by_type', $path, $type);
}