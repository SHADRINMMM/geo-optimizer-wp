<?php
/**
 * Plugin Name:       GEO Optimizer by Causabi
 * Plugin URI:        https://causabi.com
 * Description:       Make your website visible to ChatGPT, Perplexity, and other AI search engines. Automatically adds Schema.org markup and shows your AI Readiness Score in the dashboard.
 * Version:           1.1.0
 * Requires at least: 5.8
 * Requires PHP:      8.1
 * Author:            Causabi
 * Author URI:        https://causabi.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       causabi-geo-optimizer
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CAUSABI_VERSION',    '1.1.0' );
define( 'CAUSABI_API_URL',    'https://ai.causabi.com' );
define( 'CAUSABI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAUSABI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CAUSABI_PLUGIN_DIR . 'includes/class-api-client.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-schema-injector.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-admin-page.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-dashboard-widget.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-cron.php';

// Load translations
add_action( 'plugins_loaded', function () {
    load_plugin_textdomain( 'causabi-geo-optimizer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

// Inject Schema.org into <head> on every page load
function causabi_init(): void {
    $api_key = get_option( 'causabi_api_key', '' );
    if ( ! $api_key ) return;

    $injector = new Causabi_Schema_Injector( $api_key );
    add_action( 'wp_head', [ $injector, 'inject_schema' ] );
}
add_action( 'init', 'causabi_init' );

// Admin-only classes — only load in WP Admin to avoid frontend overhead
if ( is_admin() ) {
    $causabi_admin = new Causabi_Admin_Page();
    add_action( 'admin_menu',            [ $causabi_admin, 'register_menu' ] );
    add_action( 'admin_init',            [ $causabi_admin, 'register_settings' ] );
    add_action( 'admin_enqueue_scripts', [ $causabi_admin, 'enqueue_assets' ] );

    $causabi_widget = new Causabi_Dashboard_Widget();
    add_action( 'wp_dashboard_setup',    [ $causabi_widget, 'register' ] );
    add_action( 'admin_enqueue_scripts', [ $causabi_widget, 'enqueue_styles' ] );
}

// AJAX handler fires on admin-ajax.php — available even outside is_admin() block
add_action( 'wp_ajax_causabi_refresh', function () {
    ( new Causabi_Admin_Page() )->ajax_refresh();
} );

// Scheduled auto-refresh every 7 days
register_activation_hook(   __FILE__, [ 'Causabi_Cron', 'schedule' ] );
register_deactivation_hook( __FILE__, [ 'Causabi_Cron', 'unschedule' ] );
add_action( 'causabi_refresh_schema', [ 'Causabi_Cron', 'refresh' ] );
