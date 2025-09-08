<?php
$config['useragent'] = get_option('mail_engine') ; // phpmailer or codeigniter
$config['protocol']  = get_option('email_protocol');
$config['mailpath']  = '/usr/bin/sendmail'; // or "/usr/sbin/sendmail"
$config['smtp_host'] = trim(get_option('smtp_host'));

if (get_option('smtp_username') == '') {
    $config['smtp_user'] = trim(get_option('smtp_email'));
} else {
    $config['smtp_user'] = trim(get_option('smtp_username'));
}

$config['smtp_pass']    = get_instance()->encryption->decrypt(get_option('smtp_password'));
$config['smtp_port']    = trim(get_option('smtp_port'));
$config['smtp_timeout'] = 30;
$config['smtp_crypto']  = get_option('smtp_encryption');
$config['smtp_debug']   = 0;                        // PHPMailer's SMTP debug info level: 0 = off, 1 = commands, 2 = commands and data, 3 = s 2 plus connection status, 4 = low level data output.

$config['debug_output'] = 'html';                       // PHPMailer's SMTP debug output: 'html', 'echo', 'error_log' or user defined unction with parameter $str and $level. NULL or '' means 'echo' on CLI, 'html' otherwise.

$config['smtp_auto_tls'] = false;                     // Whether to enable TLS encryption automatically if a server supports it, even if smtp_crypto` is not set to 'tls'.

$config['smtp_conn_options'] = [];                 // SMTP connection options, an array passed to the function stream_context_create() when onnecting via SMTP.

$config['wordwrap'] = true;
$config['mailtype'] = 'html';
$charset            = strtoupper(get_option('smtp_email_charset'));
$charset            = trim($charset);
if ($charset == '' || strcasecmp($charset, 'utf8') == 'utf8') {
    $charset = 'utf-8';
}

$config['charset']  = $charset;
$config['validate'] = false;
$config['priority'] = 3;                        // 1, 2, 3, 4, 5; on PHPMailer useragent NULL is a possible option, it means that -priority header is not set at all, see https://github.com/PHPMailer/PHPMailer/issues/449

$config['newline']        = "\r\n";
$config['crlf']           = "\r\n";
$config['bcc_batch_mode'] = false;
$config['bcc_batch_size'] = 200;
$config['encoding']       = '8bit';                   // The body encoding. For CodeIgniter: '8bit' or '7bit'. For PHPMailer: '8bit', '7bit', binary', 'base64', or 'quoted-printable'.

// XOAUTH2 mechanism for authentication.
// See https://github.com/PHPMailer/PHPMailer/wiki/Using-Gmail-with-XOAUTH2
$config['oauth_type']           = 'xoauth2_google';      // XOAUTH2 authentication mechanism:
                                                        // ''                  - disabled;
                                                        // 'xoauth2'           - custom implementation;
                                                        // 'xoauth2_google'    - Google provider;
                                                        // 'xoauth2_yahoo'     - Yahoo provider;
                                                        // 'xoauth2_microsoft' - Microsoft provider.
$config['oauth_instance']      = null;                  // Initialized instance of \PHPMailer\PHPMailer\OAuth (OAuthTokenProvider interface) that contains a custom token provider. Needed for 'xoauth2' custom implementation only. 
$config['oauth_user_email']    = '';                    // If this option is an empty string or null, $config['smtp_user'] will be used.
$config['oauth_client_id']     = '237644427849-g8d0pnkd1jh3idcjdbopvkse2hvj0tdp.apps.googleusercontent.com';
$config['oauth_client_secret'] = 'mklHhrns6eF-qjwuiLpSB4DL';
$config['oauth_refresh_token'] = '1/7Jt8_RHX86Pk09VTfQd4O_ZqKbmuV7HpMNz-rqJ4KdQMEudVrK5jSpoR30zcRFq6';

// DKIM Signing
// See https://yomotherboard.com/how-to-setup-email-server-dkim-keys/
// See http://stackoverflow.com/questions/24463425/send-mail-in-phpmailer-using-dkim-keys
// See https://github.com/PHPMailer/PHPMailer/blob/v5.2.14/test/phpmailerTest.php#L1708
$config['dkim_domain']         = '';                       // DKIM signing domain name, for exmple 'example.com'.
$config['dkim_private']        = '';                       // DKIM private key, set as a file path.
$config['dkim_private_string'] = '';                    // DKIM private key, set directly from a string.
$config['dkim_selector']       = '';                       // DKIM selector.
$config['dkim_passphrase']     = '';                       // DKIM passphrase, used if your key is encrypted.
$config['dkim_identity']       = '';                       // DKIM Identity, usually the email address used as the source of the email.

if (file_exists(APPPATH . 'config/my_email.php')) {
    include_once(APPPATH . 'config/my_email.php');
}
