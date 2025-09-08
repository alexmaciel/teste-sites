<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends AdminController {
		
	public function __construct()
	{
		parent::__construct();
		$this->load->model('settings_model');
    }

    public function index()
    {
		$array_dateformat = array();
		foreach(get_available_date_formats() as $key => $val){
			$array_dateformat[] = array(
				'key' => $key,
				'val' => $val,
			);
		}
		$array_timezone = array();
		foreach(get_timezones_list() as $key => $timezones){
			foreach($timezones as $timezone){
				$array_timezone[] = array(
					'val' => $timezone,
				);
			}
		}
		$password = get_option('smtp_password');
		if (!empty($password)) {
			if (false == $this->encryption->decrypt($password)) {
				$password = $this->encryption->encrypt($password);
			} else {
				$password = $this->encryption->decrypt($password);
			}
		}
		$access_token = get_option('whatsapp_access_token');
		if (!empty($access_token)) {
			if (false == $this->encryption->decrypt($access_token)) {
				$access_token = $this->encryption->encrypt($access_token);
			} else {
				$access_token = $this->encryption->decrypt($access_token);
			}
		}		
		$email_signature = clear_textarea_breaks(get_option('email_signature'));
		$email_header = clear_textarea_breaks(get_option('email_header'));
		$email_footer = clear_textarea_breaks(get_option('email_footer'));
		
		$data = array(
			'main_domain' => get_option('main_domain'),
			'company_name' => get_option('company_name'),
			'business_name' => get_option('business_name'),	
			'company_address' => get_option('company_address'),	
			'company_city' => get_option('company_city'),	
			'company_alt_phonenumber' => get_option('company_alt_phonenumber'),	
			'company_postal_code' => get_option('company_postal_code'),	
			'company_phonenumber' => get_option('company_phonenumber'),	
			'company_email' => get_option('company_email'),	
			'company_description' => get_option('company_description'),	
			'active_language' => get_option('active_language'),	
			// files types
			'allowed_files' => get_option('allowed_files'),	
			'avatar_types' => get_option('avatar_types'),	
			'site_pic_types' => get_option('site_pic_types'),
			'array_dateformat' => $array_dateformat,
			'dateformat' => get_option('dateformat'),
			'array_timezone' => $array_timezone,
			'default_timezone' => get_option('default_timezone'),
			'mail_engine' => get_option('mail_engine'),
			'email_protocolo' => get_option('email_protocolo'),
			'smtp_encryption' => get_option('smtp_encryption'),
			'smtp_host' => get_option('smtp_host'),
			'smtp_port' => get_option('smtp_port'),
			'smtp_email' => get_option('smtp_email'),
			'smtp_username' => get_option('smtp_username'),
			'smtp_password' => $password,
			'smtp_email_charset' => get_option('smtp_email_charset'),
			'bcc_emails' => get_option('bcc_emails'),
			'email_signature' => $email_signature,
			'email_header' => $email_header,
			'email_footer' => $email_footer,
			'google_api_key' => get_option('google_api_key'),
			'google_client_id' => get_option('google_client_id'),
			'google_view_id' => get_option('google_view_id'),
			// Whatsapp
			'whatsapp_chat' => get_option('whatsapp_chat'),						
			'whatsapp_chat_clients_area' => get_option('whatsapp_chat_clients_area'),						
			'whatsapp_chat_description' => get_option('whatsapp_chat_description'),						
			'whatsapp_access_token' => $access_token,
			'whatsapp_number_id' => get_option('whatsapp_number_id'),
			'whatsapp_business_id' => get_option('whatsapp_business_id'),						
			'whatsapp_version' => get_option('whatsapp_version'),						
		);

		$response = $data;

		$this->output
			->set_status_header(200)
			->set_content_type('application/json')
			->set_output(json_encode($response));        
    } 

	public function update() 
	{

		$formdata = json_decode(file_get_contents('php://input'), true);
		
		if(!empty($formdata)) {

			$main_domain 	    			= $formdata['main_domain'];
			$company_name 	    			= $formdata['company_name'];
			$business_name 	    			= $formdata['business_name'];
			$company_city 	    			= $formdata['company_city'];
			$company_alt_phonenumber 		= $formdata['company_alt_phonenumber'];
			$company_postal_code 			= $formdata['company_postal_code'];
			$company_phonenumber 			= $formdata['company_phonenumber'];
			$company_address 	    		= $formdata['company_address'];
			$company_email 	    			= $formdata['company_email'];
			$company_description 			= $formdata['company_description'];
			$active_language 				= $formdata['active_language'];
			$allowed_files 	    			= $formdata['allowed_files'];
			$avatar_types 	    			= $formdata['avatar_types'];
			$site_pic_types 				= $formdata['site_pic_types'];
			$dateformat 	    			= $formdata['dateformat'];
			$default_timezone 	    		= $formdata['default_timezone'];
			$mail_engine 	    			= $formdata['mail_engine'];
			$email_protocolo 	    		= $formdata['email_protocolo'];
			$smtp_encryption 	    		= $formdata['smtp_encryption'];
			$smtp_host 	    				= $formdata['smtp_host'];
			$smtp_port 	    				= $formdata['smtp_port'];
			$smtp_email 	    			= $formdata['smtp_email'];
			$smtp_username 	    			= $formdata['smtp_username'];
			$smtp_password 	    			= $formdata['smtp_password'];
			$smtp_email_charset 			= $formdata['smtp_email_charset'];
			$bcc_emails 					= $formdata['bcc_emails'];
			$email_signature 				= $formdata['email_signature'];
			$email_header 					= $formdata['email_header'];
			$email_footer 					= $formdata['email_footer'];
			// Google
			$google_api_key 				= $formdata['google_api_key'];
			$google_client_id 				= $formdata['google_client_id'];
			$google_view_id 				= $formdata['google_view_id'];
			// Whatsapp
			$whatsapp_chat 					= $formdata['whatsapp_chat'];			
			$whatsapp_chat_clients_area 	= $formdata['whatsapp_chat_clients_area'];			
			$whatsapp_chat_description 		= $formdata['whatsapp_chat_description'];			

			$whatsapp_access_token 			= $formdata['whatsapp_access_token'];			
			$whatsapp_number_id 			= $formdata['whatsapp_number_id'];
			$whatsapp_business_id 			= $formdata['whatsapp_business_id'];			
			$whatsapp_version 				= $formdata['whatsapp_version'];			


			$data = array(
                'main_domain' => $main_domain,
                'company_name' => $company_name,					
                'business_name' => $business_name,	
                'company_city' => $company_city,
                'company_alt_phonenumber' => $company_alt_phonenumber,		
                'company_postal_code' => $company_postal_code,		
                'company_phonenumber' => $company_phonenumber,		
                'company_address' => $company_address,		
                'company_email' => $company_email,	
				'company_description' => $company_description,	
				'active_language' => $active_language,	
                'allowed_files' => $allowed_files,	
                'avatar_types' => $avatar_types,		
                'site_pic_types' => $site_pic_types,		
                'dateformat' => $dateformat,		
                'default_timezone' => $default_timezone,		
                'mail_engine' => $mail_engine,		
                'email_protocolo' => $email_protocolo,
				'smtp_encryption' => $smtp_encryption,
				'smtp_host' => $smtp_host,
				'smtp_port' => $smtp_port,
				'smtp_email' => $smtp_email,
				'smtp_username' => $smtp_username,
				'smtp_password' => $smtp_password,	
				'smtp_email_charset' => $smtp_email_charset,
				'bcc_emails' => $bcc_emails,
				'email_signature' => $email_signature,
				'email_header' => $email_header,
				'email_footer' => $email_footer,
				'google_view_id' => $google_view_id,
				'google_api_key' => $google_api_key,
				'google_client_id' => $google_client_id,	
				'whatsapp_chat' => $whatsapp_chat,
				'whatsapp_chat_clients_area' => $whatsapp_chat_clients_area,
				'whatsapp_chat_description' => $whatsapp_chat_description,
				'whatsapp_access_token' => $whatsapp_access_token,
				'whatsapp_number_id' => $whatsapp_number_id,
				'whatsapp_business_id' => $whatsapp_business_id,					
				'whatsapp_version' => $whatsapp_version														
			);	
			
			$success = $this->settings_model->update($data);
			if($success){
				$response = array(
					'type' => 'success',
					'message' => _l('settings_updated')
				);	
			} elseif ($success == 0) {
				$response = array(
					'type' => 'info',
					'message' => _l('settings_save')
				);					
			} else {
				$response = array(
					'type' => 'error',
					'message' => _l('settings_error')
				);					
			}	

			$this->output
				->set_status_header(200)
				->set_content_type('application/json')
				->set_output(json_encode($response)); 
		}		
	}    
    
}