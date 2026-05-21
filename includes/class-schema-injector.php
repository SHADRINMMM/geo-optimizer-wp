<?php
/**
 * Injects Schema.org JSON-LD markup into <head> on every page load.
 *
 * Flow: WP Transient cache hit → output immediately.
 *       Cache miss → call API → cache result for 24h → output.
 *
 * JSON-LD in <script type="application/ld+json"> is the correct,
 * Google-approved way to add structured data — fully visible to AI crawlers.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Causabi_Schema_Injector {

    private string $api_key;
    private const  CACHE_TTL = DAY_IN_SECONDS; // 24 hours

    public function __construct( string $api_key ) {
        $this->api_key = $api_key;
    }

    public function inject_schema(): void {
        $domain    = $this->get_domain();
        $cache_key = 'causabi_data_' . md5( $domain );
        $data      = get_transient( $cache_key );

        // Never make HTTP requests on the frontend — only serve from cache.
        // Initial population happens via admin save / Refresh button / WP-Cron.
        if ( empty( $data ) ) return;

        // Organization / WebSite schema — injected on every page
        if ( ! empty( $data['schema_json'] ) ) {
            $this->output_json_ld( $data['schema_json'] );
        }

        // FAQ schema — only on homepage (Google penalizes it on inner pages)
        if ( ! empty( $data['faq_json'] ) && is_front_page() ) {
            $this->output_json_ld( $data['faq_json'] );
        }
    }

    private function output_json_ld( array $schema ): void {
        printf(
            '<script type="application/ld+json">%s</script>' . "\n",
            wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
        );
    }

    private function get_domain(): string {
        $host = wp_parse_url( get_site_url(), PHP_URL_HOST ) ?? '';
        return str_replace( 'www.', '', strtolower( $host ) );
    }
}
