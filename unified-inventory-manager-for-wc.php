<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @package              Uimfwc_Unified_Inventory_Manager_For_Wc
 * @version              1.0.0
 *
 * Plugin Name:          Unified Inventory Manager For WooCommerce
 * Plugin URI:           https://wordpress.org/plugins/unified-inventory-manager-for-wc/
 * Description:          Unified Inventory Manager For WooCommerce is a powerful inventory management solution that allows you to manage stock for products ensuring seamless tracking and management.
 * Version:              1.0.0
 * Requires at least:    6.1
 * Requires PHP:         7.4
 * Author:               Sajjad Hossain Sagor
 * Author URI:           https://sajjadhsagor.com/
 * License:              GPL-2.0+
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:          unified-inventory-manager-for-wc
 * Domain Path:          /languages
 * Requires Plugins:     woocommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Plugin Version
 *
 * Defines the current version of the plugin.
 *
 * @since    1.0.0
 */
define( 'UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_VERSION', '1.0.0' );

/**
 * The absolute path to the main plugin file.
 *
 * Defines the full path to the main plugin file.
 * This constant stores the absolute filesystem path to the plugin's primary file.
 *
 * @since    1.0.0
 */
define( 'UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_FULLPATH', __FILE__ );

/**
 * Plugin Path
 *
 * Defines the absolute server path to the plugin's main directory.  This is
 * determined using the WordPress `plugin_dir_path()` function.
 *
 * @since    1.0.0
 */
define( 'UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin URL
 *
 * Defines the URL to the plugin's main directory. This is determined using
 * the WordPress `plugin_dir_url()` function.
 *
 * @since    1.0.0
 */
define( 'UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin Base Name
 *
 * Defines the base name of the plugin's main file (e.g., `my-plugin/my-plugin.php`).
 * This is determined using the WordPress `plugin_basename()` function.
 *
 * @since    1.0.0
 */
define( 'UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-unified-inventory-manager-activator.php
 *
 * @since    1.0.0
 */
function uimfwc_on_activate_unified_inventory_manager_for_wc() {
	require_once UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH . 'includes/class-uimfwc-unified-inventory-manager-for-wc-activator.php';

	Uimfwc_Unified_Inventory_Manager_For_Wc_Activator::on_activate();
}

register_activation_hook( __FILE__, 'uimfwc_on_activate_unified_inventory_manager_for_wc' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-unified-inventory-manager-deactivator.php
 *
 * @since    1.0.0
 */
function uimfwc_on_deactivate_unified_inventory_manager_for_wc() {
	require_once UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH . 'includes/class-uimfwc-unified-inventory-manager-for-wc-deactivator.php';

	Uimfwc_Unified_Inventory_Manager_For_Wc_Deactivator::on_deactivate();
}

register_deactivation_hook( __FILE__, 'uimfwc_on_deactivate_unified_inventory_manager_for_wc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 *
 * @since    1.0.0
 */
require UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH . 'includes/class-uimfwc-unified-inventory-manager-for-wc.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function uimfwc_run_unified_inventory_manager_for_wc() {
	// Instantiate the plugin main class.
	$plugin = new Uimfwc_Unified_Inventory_Manager_For_Wc();

	$plugin->run();
}

uimfwc_run_unified_inventory_manager_for_wc();
