## Plugin Installation Procedures ##

See: http://pwaplusphp.smccandl.net/support/responses/how-do-i-install-pwa-php

## Procedure For Standalone Installation ##

  * Unzip the archive you downloaded under the public\_html folder on your webserver
```
$ tar xvf pwa+php_v1.8.3.tar
pwaplusphp/gallery.php
pwaplusphp/index.php
pwaplusphp/install.php
pwaplusphp/INSTALL.txt
pwaplusphp/LICENSE.txt
pwaplusphp/style.css
```
  * Point your browser to http://yoursite.com/pwaplusphp/install.php, you should see something like:
```
Setting up PicasaWeb tokens for site: yoursite.com
If this is correct, Login to your Google Account
```
  * Click the link to login and then you should see a page from google asking you to Grant or Deny access.
  * Click Grant and the token you just generated will be displayed and saved to the config file.  If not, see TokenTroubleshooting.
  * Click the link to continue to the next step and set your preferences
  * After setup is complete, you can rename the directory or otherwise move the files to another location as desired.

## Additional Steps For Non-Standalone Installation ##

If you want to include this php script within another PHP code via the include() function or if you want to embed the code within a CMS, like Drupal, you'll need to modify the code a bit.  See below.

  * Ensure `$STANDALONE_MODE=FALSE` in the config.php
  * Rename index.php to something less popular, like albums.php and move it, config.php, gallery.php and pwaplusphp.css to your site's directory.
  * Update all instances of the $action variable in index.php (which you renamed) to point to the embedded or included location of gallery.php.
```
23:			$action = "gallery.php";
```
  * Update $back\_link variable in gallery.php to point to the new name of index.php, for links back to the main page.
```
24:		       $back_link="albums.php";
```
  * Add a PHP include statement to your site design where you want the gallery to appear:
```
<?php include("albums.php"); ?>
```