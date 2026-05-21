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

// Remove cached analysis data (transient key is causabi_data_{md5(domain)})
$host      = wp_parse_url( get_site_url(), PHP_URL_HOST ) ?? '';
$domain    = str_replace( 'www.', '', strtolower( $host ) );
$cache_key = 'causabi_data_' . md5( $domain );
delete_transient( $cache_key );
delete_transient( 'causabi_scan_queued' );

// Remove scheduled cron event
$timestamp = wp_next_scheduled( 'causabi_refresh_schema' );
if ( $timestamp ) {
    wp_unschedule_event( $timestamp, 'causabi_refresh_schema' );
}
