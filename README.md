authentic
=========

HTTP Basic Authentication plugin for ExpressionEngine.

Password protect an arbitrary public facing page or URL without involving either the EE Member module or the web server configuration (.htaccess, htpasswd etc).

Usage
-----

Specify a single username and password:

    {exp:authentic username='hello' password='world' realm='Keep Out!'}

Or, specify multiple usernames and passwords separated by `|`:

    {exp:authentic username='karl|groucho' password='marx|marx' realm='Marx Bros ONLY'}

Obviously, there must be a password for each username, and the usernames and passwords themselves may not contain a `|`.

Finally, the parameters may be set dynamically, eg, from channel-derived data:

    {exp:channel:entries channel='channel'}

        {exp:authentic username='{username}' password='{password}' realm='{title}'}

    {/exp:channel:entries}

Nonexistent or invalid credentials result in a standard HTTP 401 Unauthorized error.

Fine Print
----------

This plugin requires that the HTTP Authentication credentials be stored in the `PHP_AUTH_USER` and `PHP_AUTH_PW` server variables. This in turn implies `mod_php`.

But there are work arounds. For example, if you are running CGI PHP on Dreamhost you can ammend `.htaccess`:

    RewriteEngine On
    RewriteRule .* - [E=REMOTE_USER:%{HTTP:Authorization}]

The HTTP Auth credentials will then be stored in a server variable named `REDIRECT_REMOTE_USER` which will in turn be found and used by the plugin.

If a server variable *other than* `REDIRECT_REMOTE_USER` is required then specify it:

    {exp:authentic username='hearst' password='rosebud' realm='Confidential' var='VARIABLE_NAME'}
