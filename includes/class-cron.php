<?php
/**
 * WP-Cron: auto-refresh Schema.org data every 7 days.
 *
 * On activation  → schedule weekly event.
 * On deactivation → remove scheduled event (clean uninstall).
 * On fire        → delete transient cache so next page load fetches fresh data.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Causabi_Cron {

    public static function schedule(): void {
        if ( ! wp_next_scheduled( 'causabi_refresh_schema' ) ) {
            wp_schedule_event( time(), 'weekly', 'causabi_refresh_schema' );
        }
    }

    public static function unschedule(): void {
        $timestamp = wp_next_scheduled( 'causabi_refresh_schema' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'causabi_refresh_schema' );
        }
    }

    /**
     * Called by WP-Cron every 7 days.
     * Clears the transient — next wp_head call will fetch fresh data from API.
     */
    public static function refresh(): void {
        $stored  = get_option( 'causabi_api_key', '' );
        $api_key = ! empty( $stored ) ? Causabi_Crypto::decrypt( $stored ) : '';
        if ( ! $api_key ) return;

        $host      = wp_parse_url( get_site_url(), PHP_URL_HOST ) ?? '';
        $domain    = str_replace( 'www.', '', strtolower( $host ) );
        $cache_key = 'causabi_data_' . md5( $domain );

        delete_transient( $cache_key );

        // Pre-warm cache immediately instead of waiting for next page load
        $client = new Causabi_API_Client( $api_key );
        $data   = $client->analyze( get_site_url(), $domain );
        if ( $data ) {
            set_transient( $cache_key, $data, DAY_IN_SECONDS );
        }
    }
}
