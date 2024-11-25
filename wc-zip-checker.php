<?php
/*
* Plugin Name:       Delivery Postcode Validator For WooCommerce
* Plugin URI:        
* Description:       Check shipping is avaible or not at your location using pincode.
* Version:           1.0.0
* Author:            DevStitch
* Author URI:        https://www.devstitch.com
* Requires at least: 4.5
* Tested up to:      6.7.1
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       wc-zip-checker
* Domain Path:       /languages
*/

defined('ABSPATH') || exit;

/**
 * Support for Multi Network Site
 */
if (!function_exists('is_plugin_active_for_network')) {
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');
}

/**
 * @Type
 * @Version
 * @Directory URL
 * @Directory Path
 * @Plugin Base Name
 */
if (!defined('WZC_FILE')) {
    define('WZC_FILE', __FILE__);
}
if (!defined('WZC_VERSION')) {
    define('WZC_VERSION', '1.4');
}
if (!defined('WZC_DIR_URL')) {
    define('WZC_DIR_URL', plugin_dir_url(WZC_FILE));
}
if (!defined('WZC_DIR_PATH')) {
    define('WZC_DIR_PATH', plugin_dir_path(WZC_FILE));
}
if (!defined('WZC_BASENAME')) {
    define('WZC_BASENAME', plugin_basename(WZC_FILE));
}

// Create languages directory if it doesn't exist
if (!file_exists(WZC_DIR_PATH . 'languages')) {
    mkdir(WZC_DIR_PATH . 'languages', 0755, true);
}

include_once WZC_DIR_PATH . 'frontend/Check_Pincode_Form.php';

if (!class_exists('WCZ_Loader')) {
    require_once WZC_DIR_PATH . 'includes/class-loader.php';
    new \WZC\WCZ_Loader();
}

function wzc_add_settings_link($links)
{
    $settings_link = '<a href="admin.php?page=pin-code-setting">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wzc_add_settings_link');
