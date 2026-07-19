<?php
/**
 * Runs when the plugin is deleted from the WordPress admin.
 * Removes all plugin options and cached transients.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove API key option
delete_option( 'causabi_api_key' );
delete_option( 'causabi_llms_txt_enabled' );
delete_option( 'causabi_ai_bots_enabled' );

// Remove cached analysis data (transient key is causabi_data_{md5(domain)})
$causabi_host      = wp_parse_url( get_site_url(), PHP_URL_HOST ) ?? '';
$causabi_domain    = str_replace( 'www.', '', strtolower( $causabi_host ) );
$causabi_cache_key = 'causabi_data_' . md5( $causabi_domain );
delete_transient( $causabi_cache_key );
delete_transient( 'causabi_scan_queued' );

// Remove scheduled cron event
$causabi_timestamp = wp_next_scheduled( 'causabi_refresh_schema' );
if ( $causabi_timestamp ) {
    wp_unschedule_event( $causabi_timestamp, 'causabi_refresh_schema' );
}

flush_rewrite_rules();
