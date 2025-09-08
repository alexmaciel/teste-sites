<?php
defined('BASEPATH') or exit('No direct script access allowed');
define('AUTH_AREA',true);

class AuthController extends Api_Controller
{

    public $output = array();

    function __construct()
    {
        parent::__construct();      
    
        $this->load->model('authentication_model');
        $this->load->model('users_model');

        hooks()->do_action('pre_auth_init');

    }    
   
}