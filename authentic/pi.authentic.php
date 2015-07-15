<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
    'pi_name'        => 'authentic',
    'pi_version'     => '1.2',
    'pi_author'      => 'Steve Pedersen',
    'pi_author_url'  => 'http://www.bluecoastweb.com',
    'pi_description' => 'Simple HTTP Basic Authentication',
    'pi_usage'       => Authentic::usage()
);

class Authentic {
    public $return_data;

    public function __construct() {
        $this->EE =& get_instance();
        $username = explode('|', $this->EE->TMPL->tagparams['username']);
        $password = explode('|', $this->EE->TMPL->tagparams['password']);
        $realm    = $this->EE->TMPL->tagparams['realm'];
        if (! $this->have_credentials()) {
            // no credentials found
            // so try to retrieve them from alternate server variable
            $server_var = $this->EE->TMPL->fetch_param('server_var', 'REDIRECT_REMOTE_USER');
            $this->get_credentials_from($server_var);
        }
        if ($this->have_credentials() && $this->valid_credentials($username, $password)) {
            // authenticated: noop
        } else { 
            $this->challenge($realm);
        }
    }

    /**
     * Trigger HTTP 401 pop-up
     *
     * @param string -- the security "realm" displayed in the 401 pop-up
     */
    public static function challenge($realm) {
        header("WWW-Authenticate: Basic realm=\"{$realm}\"");
        header('HTTP/1.0 401 Unauthorized');
        exit('Authentication is required to view this page.');
    }

    /**
     * Try to populate PHP_AUTH_* from alternate server variable
     *
     * @param string -- HTTP server variable name
     */
    public static function get_credentials_from($server_var) {
        if (isset($_SERVER[$server_var]) && (strlen($_SERVER[$server_var]) > 0)) {
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER[$server_var], 6)));
        }
    }

    /**
     * Return true if PHP_AUTH_* server variables exist
     *
     * @return bool
     */
    public static function have_credentials() {
        return (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']));
    }

    /**
     * Try to authenticate using username(s) and password(s) from tag parameters
     *
     * @param array -- usernames
     * @param array -- passwords
     * @return bool -- true if username/password match user input
     */
    public static function valid_credentials($usernames, $passwords) {
        foreach ($usernames as $i => $username) {
            if (isset($passwords[$i])) {
                $password = $passwords[$i];
                if ((strcmp($_SERVER['PHP_AUTH_USER'], $username) == 0) && (strcmp($_SERVER['PHP_AUTH_PW'], $password) == 0)) {
                    return true;
                }
            }
        }
        return false;
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

Specify a single username and password:

{exp:authentic username='hello' password='world' realm='keep out!'}

Or, specify multiple usernames and passwords separated by '|':

{exp:authentic username='user-1|user-2' password='pass-1|pass-2' realm='super secret'}

Obviously, there must be a password for each username and the usernames and passwords themselves may not contain a '|' character.

Finally, the parameters may be set dynamically, eg, from channel-derived data:

{exp:channel:entries channel='channel'}

{exp:authentic username='{username}' password='{password}' realm='{title}'}

{/exp:channel:entries}

Nonexistent or invalid credentials result in a standard HTTP 401 Unauthorized error.

--------------------------------------------------------------------------------
Dependency
--------------------------------------------------------------------------------

This plugin requires that the HTTP Authentication credentials be stored in the PHP_AUTH_USER and PHP_AUTH_PW server variables. This in turn implies mod_php.

But there are work arounds. For example, if you are running CGI PHP on Dreamhost you can ammend .htaccess:

RewriteEngine On
RewriteRule .* - [E=REMOTE_USER:%{HTTP:Authorization}]

The HTTP Auth credentials will then be stored in a server variable named REDIRECT_REMOTE_USER which will in turn be found and used by the plugin.

If a server variable other than REDIRECT_REMOTE_USER is required then specify it:

{exp:authentic username='hearst' password='rosebud' realm='Confidential' server_var='VARIABLE_NAME'}
<?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}

