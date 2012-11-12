authentic
=========

HTTP Basic Authentication plugin for ExpressionEngine.

Password protect an arbitrary public facing page or URL without involving either the EE Member module or the web server configuration (.htaccess, htpasswd etc).

Usage
-----

Specify credentials statically:

    {exp:authentic username='hearst' password='rosebud' realm='Confidential'}

Or dynamically, for example, from channel-derived data:

    {exp:channel:entries channel='channel'}

        {exp:authentic username='{username}' password='{password}' realm='{title}'}

    {/exp:channel:entries}

Nonexistent or invalid credentials results in a standard HTTP 401 Unauthorized error.

Caveat
------

Do not use the following as username or password:

* n
* off
* on
* y

[EE automatically converts these, respectively to 'no' and 'yes'](http://expressionengine.com/user_guide/development/usage/template.html).

Fine Print
----------

This plugin requires that the HTTP Authentication credentials be stored in the `PHP_AUTH_USER` and `PHP_AUTH_PW` server variables. This in turn implies `mod_php`.

But there are work arounds. For example, if you are running CGI PHP on Dreamhost you can ammend `.htaccess`:

    RewriteEngine On
    RewriteRule .* - [E=REMOTE_USER:%{HTTP:Authorization}]

The HTTP Auth credentials will then be stored in a server variable named `REDIRECT_REMOTE_USER` which will in turn be found and used by the plugin.

If a server variable *other than* `REDIRECT_REMOTE_USER` is required then specify it:

    {exp:authentic username='hearst' password='rosebud' realm='Confidential' var='VARIABLE_NAME'}
