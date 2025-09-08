<?php

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use GuzzleHttp\Exception\RequestException;


defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: text/html; charset=utf-8');


/**
 * @deprecated
 */
function add_encryption_key_old()
{
    $CI =& get_instance();
    $key         = generate_encryption_key();
    $config_path = APPPATH . 'config/config.php';
    $CI->load->helper('file');
    @chmod($config_path, FILE_WRITE_MODE);
    $config_file = read_file($config_path);
    $config_file = trim($config_file);
    $config_file = str_replace("\$config['encryption_key'] = '';", "\$config['encryption_key'] = '" . $key . "';", $config_file);
    if (!$fp = fopen($config_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
        return false;
    }
    flock($fp, LOCK_EX);
    fwrite($fp, $config_file, strlen($config_file));
    flock($fp, LOCK_UN);
    fclose($fp);
    @chmod($config_path, FILE_READ_MODE);

    return $key;
}
/**
 * Generate encryption key for app-config.php
 * @return stirng
 */
function generate_encryption_key()
{
    $CI =& get_instance();
    // In case accessed from my_functions_helper.php
    $CI->load->library('encryption');
    $key = bin2hex($CI->encryption->create_key(16));

    return $key;
}

/**
 * Is user logged in
 * @return boolean
 */
function is_logged_in()
{
    return (is_client_logged_in() || is_staff_logged_in());
}
/**
 * Is client logged in
 * @return boolean
 */
function is_client_logged_in()
{
    return get_instance()->session->has_userdata('client_logged_in');
}
/**
 * Is staff logged in
 * @return boolean
 */
function is_staff_logged_in()
{
    return get_instance()->session->has_userdata('staff_logged_in');
}
/**
 * Return logged staff User ID from session
 * @return mixed
 */
function get_staff_user_id()
{
    $CI = &get_instance();

    if (defined('API')) {
        $CI->load->config('rest');

        $api_key_variable = $CI->config->item('rest_key_name');
        $key_name         = 'HTTP_' . strtoupper(str_replace('-', '_', $api_key_variable));

        if ($key = $CI->input->server($key_name)) {
            $CI->db->where('key', $key);
            $key = $CI->db->get($CI->config->item('rest_keys_table'))->row();
            if ($key) {
                return $key->user_id;
            }
        }
    }

    if (!is_staff_logged_in()) {
        return false;
    }

    return $CI->session->userdata('staff_user_id');
}
/**
 * Return logged client User ID from session
 * @return mixed
 */
function get_client_user_id()
{
    $CI =& get_instance();
    if (!$CI->session->has_userdata('client_logged_in')) {
        return false;
    }

    return $CI->session->userdata('client_user_id');
}
function get_contact_user_id()
{
    $CI =& get_instance();
    if (!$CI->session->has_userdata('contact_user_id')) {
        return false;
    }

    return $CI->session->userdata('contact_user_id');
}

/**
 * Check if passed string is valid date
 * @param  string  $date
 * @return boolean
 */
function is_date($date)
{
    if (empty($date) || strlen($date) < 10) {
        return false;
    }

    return (bool) strtotime($date);
}

/**
 * Get current url with query vars
 * @return string
 */
function current_full_url()
{
    $CI =& get_instance();
    $url = $CI->config->site_url($CI->uri->uri_string());

    return $_SERVER['QUERY_STRING'] ? $url . '?' . $_SERVER['QUERY_STRING'] : $url;
}
/**
 * Load custom lang for the given language
 *
 * @since 3.0.0
 *
 * @param  string $language
 *
 * @return void
 */
function load_custom_lang_file($language)
{
    $CI = &get_instance();
    if (file_exists(APPPATH . 'language/' . $language . '/custom_lang.php')) {
        if (array_key_exists('custom_lang.php', $CI->lang->is_loaded)) {
            unset($CI->lang->is_loaded['custom_lang.php']);
        }
        $CI->lang->load('custom_lang', $language);
    }
}

/**
 * Get country short name by passed id
 * @param  mixed $id county id
 * @return mixed
 */
function get_country_short_name($id)
{
    $CI =& get_instance();
    $CI->db->where('country_id', $id);
    $country = $CI->db->get(db_prefix() . 'countries')->row();
    if ($country) {
        return $country->iso2;
    }

    return '';
}

/**
 * Available date formats
 * @return array
 */
function get_available_date_formats()
{
    $date_formats = [
        'd-m-Y|%d-%m-%Y' => 'd-m-Y',
        'd/m/Y|%d/%m/%Y' => 'd/m/Y',
        'm-d-Y|%m-%d-%Y' => 'm-d-Y',
        'm.d.Y|%m.%d.%Y' => 'm.d.Y',
        'm/d/Y|%m/%d/%Y' => 'm/d/Y',
        'Y-m-d|%Y-%m-%d' => 'Y-m-d',
        'd.m.Y|%d.%m.%Y' => 'd.m.Y',
    ];

    return $date_formats;
}
function getByLanguage($language = 'english')
{
    $locale = 'en';
    if ($language == '') {
        return $locale;
    }

    $locales = get_locales();

    if (isset($locales[$language])) {
        $locale = $locales[$language];
    } elseif (isset($locales[ucfirst($language)])) {
        $locale = $locales[ucfirst($language)];
    } else {
        foreach ($locales as $key => $val) {
            $key      = strtolower($key);
            $language = strtolower($language);
            if (strpos($key, $language) !== false) {
                $locale = $val;
            // In case $language is bigger string then $key
            } elseif (strpos($language, $key) !== false) {
                $locale = $val;
            }
        }
    }

    return $locale;
}
/**
 * Get timezones list
 * @return array timezones
 */
function get_timezones_list() 
{
    $timezone = [
        'AMERICA'    => \DateTimeZone::listIdentifiers(\DateTimeZone::AMERICA),
        'UTC'        => \DateTimeZone::listIdentifiers(\DateTimeZone::UTC),
    ];

    return $timezone;
}
/**
 * Get available locaes predefined for the system
 * If you add a language and the locale do not exist in this array you can use action hook to add new locale
 * @return array
 */
function get_locales()
{
    $locales = [
        'Estonian'    => 'et',
        'Arabic'      => 'ar',
        'Bulgarian'   => 'bg',
        'Catalan'     => 'ca',
        'Czech'       => 'cs',
        'Danish'      => 'da',
        'Albanian'    => 'sq',
        'German'      => 'de',
        'Deutsch'     => 'de',
        'Dutch'       => 'nl',
        'Greek'       => 'el',
        'English'     => 'en',
        'Finland'     => 'fi',
        'Spanish'     => 'es',
        'Persian'     => 'fa',
        'Finnish'     => 'fi',
        'French'      => 'fr',
        'Hebrew'      => 'he',
        'Hindi'       => 'hi',
        'Indonesian'  => 'id',
        'Hindi'       => 'hi',
        'Croatian'    => 'hr',
        'Hungarian'   => 'hu',
        'Icelandic'   => 'is',
        'Italian'     => 'it',
        'Japanese'    => 'ja',
        'Korean'      => 'ko',
        'Lithuanian'  => 'lt',
        'Latvian'     => 'lv',
        'Norwegian'   => 'nb',
        'Netherlands' => 'nl',
        'Polish'      => 'pl',
        'Portuguese'  => 'pt',
        'Romanian'    => 'ro',
        'Russian'     => 'ru',
        'Slovak'      => 'sk',
        'Slovenian'   => 'sl',
        'Serbian'     => 'sr',
        'Swedish'     => 'sv',
        'Thai'        => 'th',
        'Turkish'     => 'tr',
        'Ukrainian'   => 'uk',
        'Vietnamese'  => 'vi',
        'Chinese'     => 'zh',
    ];

    return hooks()->apply_filters('before_get_locales', $locales);
}
/**
 * Get country row from database based on passed country id
 * @param  mixed $id
 * @return object
 */
function get_country($id)
{
    $CI = & get_instance();

    $country = $CI->api_object_cache->get('db-country-' . $id);

    if (!$country) {
        $CI->db->where('country_id', $id);
        $country = $CI->db->get(db_prefix().'countries')->row();
        $CI->api_object_cache->add('db-country-' . $id, $country);
    }

    return $country;
}
/**
 * Get locale key by system language
 * @param  string $language language name from (application/languages) folder name
 * @return string
 */
function get_locale_key($language = 'english')
{
    $locale = getByLanguage($language);

    return hooks()->apply_filters('before_get_locale', $locale);
}
/**
 * Set session alert / flashdata
 * @param string $type    Alert type
 * @param string $message Alert message
 */
function set_alert($value, String $message = '', String $name = '')
{
    $response = array();

    if($value) {
        $response = array(
            'type' => 'success',
            'message' => _l($message, _l($name))
        );	
    } elseif ($value == 0) {
        $response = array(
            'type' => 'info',
            'message' => _l($message, _l($name))
        );					
    } else {
        $response = array(
            'type' => 'error',
            'message' => _l('error_action')
        );					
    } 

    return $response;
}

/**
 * Convert string to sql date based on current date format from options
 * @param  string $date date string
 * @return mixed
 */
function to_sql_date($date, $datetime = false)
{
    if ($date == '' || $date == null) {
        return null;
    }

    $to_date     = 'Y-m-d';
    $from_format = get_current_date_format(true);

    $date = hooks()->apply_filters('before_sql_date_format', $date, [
        'from_format' => $from_format,
        'is_datetime' => $datetime,
    ]);

    if ($datetime == false) {
        // Is already Y-m-d format?
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $date)) {
            return $date;
        }

        return hooks()->apply_filters(
            'to_sql_date_formatted',
            DateTime::createFromFormat($from_format, $date)->format($to_date)
        );
    }

    if (strpos($date, ' ') === false) {
        $date .= ' 00:00:00';
    } else {
        $hour12 = (get_option('time_format') == 24 ? false : true);
        if ($hour12 == false) {
            $_temp = explode(' ', $date);
            $time  = explode(':', $_temp[1]);
            if (count($time) == 2) {
                $date .= ':00';
            }
        } else {
            $tmp  = _simplify_date_fix($date, $from_format);
            $time = date('G:i', strtotime($tmp));
            $tmp  = explode(' ', $tmp);
            $date = $tmp[0] . ' ' . $time . ':00';
        }
    }

    $date = _simplify_date_fix($date, $from_format);
    $d    = date('Y-m-d H:i:s', strtotime($date));

    return hooks()->apply_filters('to_sql_date_formatted', $d);
}

/**
 * Function that will check the date before formatting and replace the date places
 * This function is custom developed because for some date formats converting to y-m-d format is not possible
 * @param  string $date        the date to check
 * @param  string $from_format from format
 * @return string
 */
function _simplify_date_fix($date, $from_format)
{
    if ($from_format == 'd/m/Y') {
        $date = preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$2-$1 $4', $date);
    } elseif ($from_format == 'm/d/Y') {
        $date = preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    } elseif ($from_format == 'm.d.Y') {
        $date = preg_replace('#(\d{2}).(\d{2}).(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    } elseif ($from_format == 'm-d-Y') {
        $date = preg_replace('#(\d{2})-(\d{2})-(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    }

    return $date;
}
/**
 * Output easy-to-read numbers
 * by james at bandit.co.nz
 */
function bd_nice_number($date) {
    // first strip any formatting;
    $date = (0 + str_replace(",","", $date));
    
    // is this a number?
    if(!is_numeric($date)) return false;
    
    // now filter it;
    if($date>1000000000000) return round(($date/1000000000000),1).' tri';
    else if($date>1000000000) return round(($date/1000000000),1).' bi';
    else if($date>1000000) return round(($date/1000000),1).' mi';
    else if($date>1000) return round(($date/1000),1).' k';
    
    return number_format($date);
}
/**
 * Get current date format from options
 * @return string
 */
function get_current_date_format($php = false)
{
    $format = get_option('dateformat');
    $format = explode('|', $format);

    $format = hooks()->apply_filters('get_current_date_format', $format, $php);

    if ($php == false) {
        return $format[1];
    }

    return $format[0];
}
/**
 * Format date to selected dateformat
 * @param  date $date Valid date
 * @return date/string
 */
function _d($date)
{
    if ($date == '' || is_null($date) || $date == '0000-00-00') {
        return '';
    }
    if (strpos($date, ' ') !== false) {
        return _dt($date);
    }
    $format = get_current_date_format();
    $date   = strftime($format, strtotime($date));

    return hooks()->apply_filters('after_format_date', $date);
}
/**
 * Format datetime to selected datetime format
 * @param  datetime $date datetime date
 * @return datetime/string
 */
function _dt($date, $is_timesheet = false)
{
    if ($date == '' || is_null($date) || $date == '0000-00-00 00:00:00') {
        return '';
    }
    $format = get_current_date_format();
    $hour12 = (get_option('time_format') == 24 ? false : true);

    if ($is_timesheet == false) {
        $date = strtotime($date);
    }

    if ($hour12 == false) {
        $tf = 'H:i:s';
        if ($is_timesheet == true) {
            $tf = 'H:i';
        }

        if (is_numeric($date)) {
            $date = date('Y-m-d H:i:s', $date);
        }

        try {
            $dateTime = new DateTime($date);
            $date     = $dateTime->format(str_replace('%', '', $format . ' ' . $tf));
        } catch (Exception $e) {
        }
    } else {
        $date = date(get_current_date_format(true) . ' g:i A', $date);
    }

    return hooks()->apply_filters('after_format_datetime', $date);
}

/**
 * Outputs language string based on passed line
 * @since  Version 1.0.1
 * @param  string $line   language line key
 * @param  mixed $label   sprint_f label
 * @return string         language text
 */
function _l($line, $label = '', $log_errors = true)
{
    $CI = &get_instance();

    $hook_data = hooks()->apply_filters('before_get_language_text', ['line' => $line, 'label' => $label]);

    $line  = $hook_data['line'];
    $label = $hook_data['label'];

    if (is_array($label) && count($label) > 0) {
        $_line = vsprintf($CI->lang->line(trim($line), $log_errors), $label);
    } else {
        if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
            try {
                $_line = sprintf($CI->lang->line(trim($line), $log_errors), $label);
            } catch (\ValueError $e) {
                $_line = $CI->lang->line(trim($line), $log_errors);
            }
        } else {
            $_line = @sprintf($CI->lang->line(trim($line), $log_errors), $label);
        }
    }

    $hook_data = hooks()->apply_filters('after_get_language_text', ['line' => $line, 'formatted_line' => $_line]);

    $_line = $hook_data['formatted_line'];
    $line  = $hook_data['line'];

    if ($_line != '') {
        if (preg_match('/"/', $_line) && !is_html($_line)) {
            $_line = html_escape($_line);
        }

        return ForceUTF8\Encoding::toUTF8($_line);
    }

    if (mb_strpos($line, '_db_') !== false) {
        return 'db_translate_not_found';
    }

    return ForceUTF8\Encoding::toUTF8($line);
}
/**
 * In some places of the script we use app_happy_text function to output some words in orange color
 * @param  string $text the text to check
 * @return string
 */
function app_happy_text($text)
{
    // We won't do this on texts with URL's
    if (strpos($text, 'http') !== false) {
        return $text;
    }

    $regex = hooks()->apply_filters('app_happy_text_regex', '\b(congratulations!?|congrats!?|happy!?|feel happy!?|awesome!?|yay!?)\b');
    $re    = '/' . $regex . '/i';

    $app_happy_color = hooks()->apply_filters('app_happy_text_color', 'rgb(255, 59, 0)');

    preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);
    foreach ($matches as $match) {
        $text = preg_replace(
            '/' . $match[0] . '/i',
            '<span style="color:' . $app_happy_color . ';font-weight:bold;">' . $match[0] . '</span>',
            $text
        );
    }

    return $text;
}
/**
 * Generate md5 hash
 * @return string
 */
function app_generate_hash()
{
    return md5(rand() . microtime() . time() . uniqid());
}
/**
 * Generate random alpha numeric string
 * @param  integer $length the length of the string
 * @return string
 */
function generate_two_factor_auth_key()
{
    return bin2hex(get_instance()->encryption->create_key(4));
}
/**
 * Generate random alpha numeric string
 * @param  integer $length the length of the string
 * @return string
 */
function generate_auth_key()
{
    return bin2hex(get_instance()->encryption->create_key(4));
}
/**
 * Creates instance of phpass
 * @since  2.3.1
 * @return object PasswordHash class
 */
function app_hasher()
{
    global $app_hasher;

    if (empty($app_hasher)) {
        require_once(APPPATH . 'third_party/phpass.php');
        // By default, use the portable hash from phpass
        $app_hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
    }

    return $app_hasher;
}

/**
 * Hashes password for user
 * @since  2.3.1
 * @param  string $password plain password
 * @return string
 */
function app_hash_password($password)
{
    return app_hasher()->HashPassword($password);
}

/**
 * Detect mobile users and provide a different link URL, related to the app
 * @return null
 */
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/**
 * Terms and conditions URL
 * @return string
 */
function terms_url()
{
    return hooks()->apply_filters('terms_and_condition_url', site_url('terms-and-conditions'));
}
/**
 * Privacy policy URL
 * @return string
 */
function privacy_policy_url()
{
    return hooks()->apply_filters('privacy_policy_url', site_url('privacy-policy'));
}

if (!function_exists('collect')) {
    /**
     * Collect items in a Collection instance
     * @since  2.9.2
     * @param  array $items
     * @return \Illuminate\Support\Collection
     */
    function collect($items)
    {
        return new Illuminate\Support\Collection($items);
    }
}
