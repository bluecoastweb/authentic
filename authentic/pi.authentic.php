<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
    'pi_name'        => 'authentic',
    'pi_version'     => '1.1',
    'pi_author'      => 'Steve Pedersen',
    'pi_author_url'  => 'http://www.bluecoastweb.com',
    'pi_description' => 'Simple HTTP Basic Authentication',
    'pi_usage'       => Authentic::usage()
);

/*
 * Note: values of ‘y’, ‘on’ and ‘yes’ will all return ‘yes’, while ‘n’, ‘off’ and ‘no’ all return ‘no’.
 * http://expressionengine.com/user_guide/development/usage/template.html
 */
class Authentic {
    public $return_data;

    public function __construct() {
        $this->EE =& get_instance();
        $username = $this->EE->TMPL->fetch_param('username');
        $password = $this->EE->TMPL->fetch_param('password');
        $realm    = $this->EE->TMPL->fetch_param('realm');

        if (! $this->have_credentials()) {
            // no credentials found, so try to retrieve them from alt server var
            $var = $this->EE->TMPL->fetch_param('var', 'REDIRECT_REMOTE_USER');
            $this->get_credentials_from($var);
        }

        if ($this->have_credentials() && $this->valid_credentials($username, $password)) {
            // authenticated: noop
        } else { 
            // challenge
            header("WWW-Authenticate: Basic realm='$realm'");
            header('HTTP/1.0 401 Unauthorized');
            exit('Authentication is required to view this page.');
        }
    }

    // try to populate PHP_AUTH_* from alternate server var
    public static function get_credentials_from($var) {
        if (isset($_SERVER[$var]) && (strlen($_SERVER[$var]) > 0)) {
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER[$var], 6)));
        }
    }

    public static function have_credentials() {
        return (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']));
    }

    public static function valid_credentials($username, $password) {
        return ((strcmp($_SERVER['PHP_AUTH_USER'], $username) == 0) && (strcmp($_SERVER['PHP_AUTH_PW'], $password) == 0));
    }

    public static function usage() {
        ob_start();
?>
--------------------------------------------------------------------------------
Description
--------------------------------------------------------------------------------

Password protect an arbitrary public facing page or URL without involving either the EE Member module or the web server configuration (.htaccess, htpasswd etc).

--------------------------------------------------------------------------------
Usage
--------------------------------------------------------------------------------

Specify credentials statically:

{exp:authentic username='hearst' password='rosebud' realm='Confidential'}

Or dynamically, eg, from channel-derived data:

{exp:channel:entries channel='channel'}

{exp:authentic username='{username}' password='{password}' realm='{title}'}

{/exp:channel:entries}

Nonexistent or invalid credentials results in a standard HTTP 401 Unauthorized error.

--------------------------------------------------------------------------------
Caveat
--------------------------------------------------------------------------------

Do not use the following as username or password:

n, off, on, y

EE automatically converts 'n' and 'off' to 'no', and 'y' and 'on' to 'yes'.
http://expressionengine.com/user_guide/development/usage/template.html

--------------------------------------------------------------------------------
Dependency
--------------------------------------------------------------------------------

This plugin requires that the HTTP Authentication credentials be stored in the PHP_AUTH_USER and PHP_AUTH_PW server variables. This in turn implies mod_php.

But there are work arounds. For example, if you are running CGI PHP on Dreamhost you can ammend .htaccess:

RewriteEngine On
RewriteRule .* - [E=REMOTE_USER:%{HTTP:Authorization}]

The HTTP Auth credentials will then be stored in a server variable named REDIRECT_REMOTE_USER which will in turn be found and used by the plugin.

If a server variable other than REDIRECT_REMOTE_USER is required then specify it:

{exp:authentic username='hearst' password='rosebud' realm='Confidential' var='VARIABLE_NAME'}
<?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}

