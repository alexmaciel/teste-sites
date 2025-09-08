<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Authentication extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        hooks()->do_action('clients_authentication_constructor', $this);

        $this->load->model('users_model');
    }

    public function index()
    {
        $this->login();
    }

    public function login()
    {
        $formdata = json_decode(file_get_contents('php://input'), true);

        if (!empty($formdata)) {
            //$remember = $formdata['remember'];
            $this->load->model('authentication_model');
            $user = $this->authentication_model->login(
                $formdata['email'], 
                $formdata['password'], 
                true,
                true
            );

            if (is_array($user) && isset($user['memberinactive'])) {
				$response = array(
					'type' => 'error',
					'message' => _l('admin_auth_inactive_account'),
				);	
            } elseif ($user == false) {
				$response = array(
					'type' => 'error',
					'message' => _l('admin_auth_invalid_email_or_password'),
				);	                
            } elseif (isset($user)) {
                // is logged in   
                $refreshToken = substr(md5(uniqid(rand())), 0, 16);
                $response = array(                 
                    'token' => $user->token,
                    'refreshToken' => $refreshToken,
                    'expiresIn' => 60 * 60 * 24 * 31 * 2,
                );                
            }
            hooks()->do_action('after_staff_login');
                        
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));                
        }            
    }    

    public function register()
    {
        if (get_option('allow_registration') != 1 || is_client_logged_in()) {
            //redirect(site_url());
        }

        $honeypot = get_option('enable_honeypot_spam_validation') == 1;

        $fields = [
            'firstname' => $honeypot ? 'firstnamemjxw' : 'firstname',
            'lastname'  => $honeypot ? 'lastnamemjxw' : 'lastname',
            'email'     => $honeypot ? 'emailmjxw' : 'email',
            'username'   => $honeypot ? 'usernamemjxw' : 'username',
        ];
                
        $formdata = json_decode(file_get_contents('php://input'), true);

        if (!empty($formdata)) {
            if ($honeypot) {

                return true;
            }  
            
            $countryId = is_numeric($formdata['country']) ? $formdata['country'] : 0;
            if (is_automatic_calling_codes_enable()) {
                $customerCountry = get_country($countryId);
                
                if ($customerCountry) {
                    $callingCode = '+' . ltrim($customerCountry->calling_code, '+');
                    
                    if (startsWith($formdata['contact_phonenumber'], $customerCountry->calling_code)) { // with calling code but without the + prefix
                        $formdata['contact_phonenumber'] = '+' . $formdata['contact_phonenumber'];
                    } elseif (!startsWith($formdata['contact_phonenumber'], $callingCode)) {
                        $formdata['contact_phonenumber'] = $callingCode . $formdata['contact_phonenumber'];
                    }
                }                
            }

            $this->db->where('email', $formdata['email']);
            $email = $this->db->get(db_prefix() . 'users')->row();
            
            if ($email) {
                $response = array(
                    'type' => 'error',
                    'message' => 'E-mail já existe',
                );
                
                return $this->output
                            ->set_content_type('application/json')
                            ->set_output(json_encode($response));                 
            }            
            
            define('CONTACT_REGISTERING', true);
            
            $clientid = $this->users_model->add([      
                'firstname'           => $formdata[$fields['firstname']],
                'lastname'            => $formdata[$fields['lastname']],
                'email'               => $formdata[$fields['email']],
                'password'            => $formdata['rPassword'],             
                'username'            => $formdata[$fields['username']],
                'description'         => $formdata['description'],
                'billing_street'      => $formdata['address'],
                'billing_city'        => $formdata['city'],
                'billing_state'       => $formdata['state'],
                'billing_zip'         => $formdata['zip'],
                'billing_country'     => $countryId,     
                'contact_phonenumber' => $formdata['contact_phonenumber'] ,   
                'company'             => $formdata[$fields['company']],
                //'vat'                 => isset($formdata['vat']) ? $data['vat'] : '',
                'phone'                 => $formdata['phone'],
                'country'             => $formdata['country'],
                'city'                => $formdata['city'],
                'address'             => $formdata['address'],
                'zip'                 => $formdata['zip'],
                'state'               => $formdata['state'],                        
                'default_language'    => get_option('active_language') //(get_contact_language() != '') ? get_contact_language() : get_option('active_language'),
            ], false);   
            

            if ($clientid) {
                hooks()->do_action('after_client_register', $clientid);
            
                if (get_option('customers_register_require_confirmation') == '1') {
                    send_customer_registered_email_to_administrators($clientid);

                    $this->clients_model->require_confirmation($clientid);
                    $response = array(
                        'type' => 'success',
                        'message' => _l('customer_register_account_confirmation_approval_notice'),
                    );	 
                }                

                $this->load->model('authentication_model');

                $logged_in = $this->authentication_model->login(
                    $formdata['email'],
                    $formdata['password'],
                    false,
                    false
                );     
                
                if ($logged_in) {
                    hooks()->do_action('after_client_register_logged_in', $clientid);
                    $response = array(
                        'type' => 'success',
                        'message' => _l('clients_successfully_registered'),
                    );	                    
                } else {
                    $response = array(
                        'type' => 'warning',
                        'message' => _l('clients_account_created_but_not_logged_in'),
                    );	                     
                }

                send_customer_registered_email_to_administrators($clientid);                
            }      
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));                 
        }        
    }    
    

    public function checkToken($token) 
    {
        $this->load->model('authentication_model');
        $isValidToken = $this->authentication_model->check_token($token, true);

        if ($isValidToken) {
            $user = $this->authentication_model->staff($isValidToken->staffid, true);

            $result = array();
            $result = array(
                'staffid' => $user->staffid,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'phone' => $user->phone,
                //'folder' => base_url('api/uploads/users/'.$user->staffid.'/'),
                'avatar' => staff_profile_image_url($user->staffid, 'small'),
                'token' => $user->token,
                'username' => $user->username,
                'default_language' => (get_staff_default_language($user->staffid) !== '') ? get_staff_default_language($user->staffid) : get_option('active_language'),
                'role' => $user->role,
                'admin' => $user->admin,
                'address' => $user->address,
                'website' => $user->website,
                'active' => $user->active,
            );
                 
            $response = $result;          
 
        } else {
            $response = array(
                'type' => 'error',
                'message' => 'Faça o login novamente.'
            );	
        }  
        
        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));          

    }

    public function forgotPassword() 
    {
        $formdata = json_decode(file_get_contents('php://input'), true);

        if (!empty($formdata)) {
            $email = $formdata['email'];

            $this->load->model('authentication_model');
            $success = $this->authentication_model->forgot_password($email, true);

            if (is_array($success) && isset($success['memberinactive'])) {
                $response = array(
                    'type' => 'error',
                    'message' => _l('inactive_account')
                );
            } elseif ($success == true) {
                $response = array(
                    'type' => 'success',
                    'message' => _l('check_email_for_resetting_password')
                );                
            } else {
                $response = array(
                    'type' => 'error',
                    'message' => _l('error_setting_new_password_key')
                );                
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));              
        }        
    }  

    public function newPassword() 
    {
        $formdata = json_decode(file_get_contents('php://input'), true);

        if ($formdata) {
            $new_pass_key = $formdata['currentPassword'];
            $password = $formdata['password'];

            $this->load->model('authentication_model');
            if (!$this->authentication_model->can_reset_password(true, $new_pass_key)) {
                $response = array(
                    'type' => 'error',
                    'message' => _l('password_reset_key_expired')
                ); 
            }
            $success = $this->authentication_model->new_password(true, $new_pass_key, $password);
            if (is_array($success) && isset($success['expired'])) {
                $response = array(
                    'type' => 'error',
                    'message' => _l('password_reset_key_expired')
                );
            } elseif ($success == true){
                $response = array(
                    'type' => 'success',
                    'message' => _l('password_reset_message')
                );                
            } else {
                $response = array(
                    'type' => 'error',
                    'message' => _l('password_reset_message_fail')
                );                
            }   

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));                    

        }
    }  

    public function setPassword() 
    {
        $formdata = json_decode(file_get_contents('php://input'), true);

        if ($formdata) {
            $new_pass_key = $formdata['currentPassword'];
            $password = $formdata['password'];

            //$admin = $this->admin_model->get($currentPassword);
            $success = $this->authentication_model->set_password($staff = false, $userid, $new_pass_key, $password);
            if (is_array($success) && isset($success['expired'])) {
                $response = array(
                    'type' => 'error',
                    'message' => _l('password_reset_key_expired')
                );
            } else {
                $response = array(
                    'type' => 'success',
                    'message' => _l('password_reset_message')
                );                
            }   

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));                    

        }
    }    

    public function logout()
    {
        hooks()->do_action('after_admin_logout');
        
        $this->load->model('authentication_model');
        $success = $this->authentication_model->logout(true);
        
        if($success == true) {
            $response = array(
                'type' => 'success',
                'message' => 'logout'
            );  
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));  
    }    
       
}
