<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://unqode.com
 * @since             1.0.0
 * @package           Woo_Flexpay
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Flexpay
 * Plugin URI:        https://flexpay.co.ke
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Flexpay 
 * Author URI:        https://flexpay.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-flexpay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC') && !in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WOO_FLEXPAY_VERSION', '1.0.0');

define('FLEX_LIVE_ENDPOINT', 'https://www.flexpay.co.ke/3Api/api/v1/book/flexpay/endpoint');

define('FLEX_SANDBOX_ENDPOINT', 'https://staging.flexpay.co.ke/3Api/api/v1/book/flexpay/endpoint');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-flexpay-activator.php
 */
function activate_woo_flexpay()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-woo-flexpay-activator.php';
	Woo_Flexpay_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-flexpay-deactivator.php
 */
function deactivate_woo_flexpay()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-woo-flexpay-deactivator.php';
	Woo_Flexpay_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_woo_flexpay');
register_deactivation_hook(__FILE__, 'deactivate_woo_flexpay');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-woo-flexpay.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_flexpay()
{

	$plugin = new Woo_Flexpay();
	$plugin->run();
}
run_woo_flexpay();
