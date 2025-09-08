<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_Controller extends CI_Controller
{
    protected $current_db_version;

    public function __construct()
    {
        parent::__construct();

        $globals = $GLOBALS; // Ostensibly by-value copy

        $GLOBALS['EXT']->call_hook('pre_controller_constructor');
        
        /**
         * Fix for users who don't replace all files during update !!!
         */
        if (!class_exists('ForceUTF8\Encoding') && file_exists(APPPATH . 'vendor/autoload.php')) {
            require_once(APPPATH . 'vendor/autoload.php');
        }

        if (CI_VERSION != '3.1.11') {
            echo '<h2>Additionally you will need to replace the <b>system</b> folder. We updated Codeigniter to 3.1.13.</h2>';
            echo '<p>From the newest downloaded files upload the <b>system</b> folder to your installation directory.';
            die;
        }

        if (!extension_loaded('mbstring') && (!function_exists('mb_strtoupper') || !function_exists('mb_strtolower'))) {
            die('<h1>"mbstring" PHP extension is not loaded. Enable this extension from cPanel or consult with your hosting provider to assist you enabling "mbstring" extension.</h4>');
        }

        /**
         * Set system timezone based on selected timezone from options
         * @var string
         */
        $this->db->reconnect();    
        $timezone = get_option('default_timezone');
        if ($timezone != '') {
            date_default_timezone_set($timezone);
        }    

        $this->load->model('authentication_model');
        $this->authentication_model->autologin();

        if ($this instanceof ClientsController) {
            load_client_language();
        } elseif ($this instanceof AdminController) {
            load_admin_language();
        } else {
            // When App_Controller is only extended or any other CORE controller that is not instance of ClientsController or AdminController
            // Will load the default sytem language, so we can get the locale and language from $GLOBALS;
            load_admin_language();
        }        

        $vars             = [];
        $vars['locale']   = $GLOBALS['locale'];
        $vars['language'] = $GLOBALS['language'];

        $this->load->vars($vars);

        $this->current_db_version = $this->api->get_current_db_version();

        hooks()->do_action('api_init');
    }

    /**
     * Sets view data
     * @param  array $data
     * @return core/ClientsController
     */
    public function data($data)
    {
        if (!is_array($data)) {
            return false;
        }

        $this->data = array_merge($this->data, $data);

        return $this;
    }    
}
