<?php
/**
 * Plugin Name:       Causabi GEO Optimizer
 * Plugin URI:        https://causabi.com/for-wordpress
 * Description:       Make your website visible to ChatGPT, Gemini, Grok, Claude, and other AI search engines. Automatically adds Schema.org markup and shows your AI Readiness Score in the dashboard.
 * Version:           1.2.0
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

define( 'CAUSABI_VERSION',    '1.2.0' );
define( 'CAUSABI_API_URL',    'https://ai.causabi.com' );
define( 'CAUSABI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAUSABI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CAUSABI_PLUGIN_DIR . 'includes/class-crypto.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-api-client.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-schema-injector.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-llms-txt.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-robots.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-admin-page.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-dashboard-widget.php';
require_once CAUSABI_PLUGIN_DIR . 'includes/class-cron.php';

// Sanitize API key for register_setting — encrypt as-is (sanitize_text_field
// would strip characters valid in token strings before encryption).
function causabi_sanitize_api_key( string $value ): string {
    return Causabi_Crypto::encrypt( $value );
}

// Inject Schema.org into <head> on every page load
function causabi_init(): void {
    $stored  = get_option( 'causabi_api_key', '' );
    $api_key = ! empty( $stored ) ? Causabi_Crypto::decrypt( $stored ) : '';
    if ( ! $api_key ) return;

    $injector = new Causabi_Schema_Injector( $api_key );
    add_action( 'wp_head', [ $injector, 'inject_schema' ] );
}
add_action( 'init', 'causabi_init' );

// Virtual /llms.txt — rewrite rule + query var + template_redirect (standard
// WP pattern for virtual files, same one core uses for /wp-sitemap.xml)
$causabi_llms_txt = new Causabi_Llms_Txt();
add_action( 'init',              [ $causabi_llms_txt, 'add_rewrite_rule' ] );
add_filter( 'query_vars',        [ $causabi_llms_txt, 'add_query_var' ] );
add_action( 'template_redirect', [ $causabi_llms_txt, 'maybe_serve' ] );

// robots.txt — allow AI crawlers on the virtual robots.txt WordPress generates
$causabi_robots = new Causabi_Robots();
add_filter( 'robots_txt', [ $causabi_robots, 'filter_robots_txt' ], 10, 2 );

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
function causabi_ajax_refresh(): void {
    ( new Causabi_Admin_Page() )->ajax_refresh();
}
add_action( 'wp_ajax_causabi_refresh', 'causabi_ajax_refresh' );

// Scheduled auto-refresh every 7 days
register_activation_hook(   __FILE__, [ 'Causabi_Cron', 'schedule' ] );
register_deactivation_hook( __FILE__, [ 'Causabi_Cron', 'unschedule' ] );
add_action( 'causabi_refresh_schema', [ 'Causabi_Cron', 'refresh' ] );

// /llms.txt rewrite rule needs a flush on (de)activation to take effect
register_activation_hook(   __FILE__, 'flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
