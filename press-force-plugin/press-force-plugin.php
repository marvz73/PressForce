<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://alphasys.com.au
 * @since             1.0.0
 * @package           Press_Force_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       PressForce
 * Plugin URI:        http://alphasys.com.au
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Marvin Ayaay
 * Author URI:        http://alphasys.com.au
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       press-force-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-press-force-plugin-activator.php
 */
function activate_press_force_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-press-force-plugin-activator.php';
	Press_Force_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-press-force-plugin-deactivator.php
 */
function deactivate_press_force_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-press-force-plugin-deactivator.php';
	Press_Force_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_press_force_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_press_force_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-press-force-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_press_force_plugin() {

	$plugin = new Press_Force_Plugin();
	$plugin->run();

}
run_press_force_plugin();
