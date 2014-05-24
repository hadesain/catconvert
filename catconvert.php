<?php
/*
   Plugin Name: Catconvert
   Plugin URI: http://wordpress.org/extend/plugins/catconvert/
   Version: 0.10
   Author: <a href="http://www.catconvert.com">Catconvert</a>
   Description: Adds a link under embedded video's which allows the users to convert the video to mp3. It supports Youtube, Vimeo, DailyMotion and many others.
   Text Domain: catconvert
   License: GPLv3
  */

$Catconvert_minimalRequiredPhpVersion = '5.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function Catconvert_noticePhpVersionWrong() {
    global $Catconvert_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "Catconvert" requires a newer version of PHP to be running.',  'catconvert').
            '<br/>' . __('Minimal version of PHP required: ', 'catconvert') . '<strong>' . $Catconvert_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'catconvert') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}


function Catconvert_PhpVersionCheck() {
    global $Catconvert_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $Catconvert_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'Catconvert_noticePhpVersionWrong');
        return false;
    }
    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function Catconvert_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('catconvert', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// First initialize i18n
Catconvert_i18n_init();


// Next, run the version check.
// If it is successful, continue with initialization for this plugin
if (Catconvert_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('catconvert_init.php');
    Catconvert_init(__FILE__);
}
