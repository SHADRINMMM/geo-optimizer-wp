<?php
/**
 * HTTP client for ai.causabi.com API.
 * Uses WordPress built-in wp_remote_post/get — no cURL dependency.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Causabi_API_Client {

    private string $api_key;
    private string $base_url;
    private int    $timeout = 45; // seconds — crawling takes time
    private int    $last_status_code = 0;

    // provision/verify are cheap (no crawl) — short timeout, no need for
    // the 45s crawl budget above.
    private const PROVISION_TIMEOUT = 10;

    public function __construct( string $api_key = '' ) {
        $this->api_key  = $api_key;
        $this->base_url = CAUSABI_API_URL;
    }

    /**
     * Step 1/2 of the keyless provision flow (no API key needed yet —
     * this call is what MINTS one). Returns the challenge token on
     * success, null on any error.
     */
    public function provision( string $site_url ): ?string {
        $response = wp_remote_post( $this->base_url . '/api/v1/wp/provision', [
            'timeout' => self::PROVISION_TIMEOUT,
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( [ 'site_url' => $site_url ] ),
        ] );
        $body = $this->parse_response( $response, 'provision' );
        return $body['challenge_token'] ?? null;
    }

    /**
     * Step 2/2: backend fetches our published challenge file itself and,
     * on match, mints (or returns the existing) keyless API key.
     * Returns the raw key on success, null on any error (invalid/expired
     * challenge, rate limited, network error).
     */
    public function provision_verify( string $site_url ): ?string {
        $response = wp_remote_post( $this->base_url . '/api/v1/wp/provision/verify', [
            'timeout' => self::PROVISION_TIMEOUT,
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( [ 'site_url' => $site_url ] ),
        ] );
        $body = $this->parse_response( $response, 'provision_verify' );
        return $body['key'] ?? null;
    }

    /**
     * Full analysis: crawl site + score + JSON-LD fixes.
     * Returns array on success, null on any error.
     */
    public function analyze( string $url, string $domain ): ?array {
        $response = $this->request_with_retry( 'POST', $this->base_url . '/api/v1/wp/analyze', [
            'timeout' => $this->timeout,
            'headers' => [
                'Content-Type'  => 'application/json',
                'X-Causabi-Key' => $this->api_key,
            ],
            'body' => wp_json_encode( [
                'url'    => $url,
                'domain' => $domain,
            ] ),
        ] );

        return $this->parse_response( $response, 'analyze' );
    }

    /**
     * Quick score lookup from cache — used by dashboard widget.
     * Returns array on success, null on any error.
     */
    public function get_score( string $domain ): ?array {
        $response = $this->request_with_retry( 'GET', $this->base_url . '/api/v1/wp/score/' . rawurlencode( $domain ), [
            'timeout' => 10,
            'headers' => [ 'X-Causabi-Key' => $this->api_key ],
        ] );

        return $this->parse_response( $response, 'get_score' );
    }

    /**
     * HTTP status of the most recent request — used by callers to detect
     * 403 (domain-mismatch: key scoped to a different domain than the
     * current site, e.g. after a domain move) and trigger re-provision
     * (plan §risk 3), without changing the null-on-any-error contract of
     * analyze()/get_score() above.
     */
    public function last_status_code(): int {
        return $this->last_status_code;
    }

    private function request_with_retry( string $method, string $url, array $args, int $max_attempts = 2 ) {
        for ( $i = 1; $i <= $max_attempts; $i++ ) {
            $response = ( $method === 'POST' ) ? wp_remote_post( $url, $args ) : wp_remote_get( $url, $args );
            if ( is_wp_error( $response ) ) {
                if ( $i < $max_attempts ) { sleep( 2 ); continue; }
                return $response;
            }
            $code = wp_remote_retrieve_response_code( $response );
            if ( $code >= 500 && $i < $max_attempts ) { sleep( 2 ); continue; }
            return $response;
        }
        return new WP_Error( 'max_retries', 'Request failed after retries' );
    }

    private function parse_response( $response, string $context ): ?array {
        if ( is_wp_error( $response ) ) {
            $this->last_status_code = 0;
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log( 'Causabi [' . $context . '] request error: ' . $response->get_error_message() );
            }
            return null;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $this->last_status_code = (int) $code;
        if ( $code !== 200 ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log( 'Causabi [' . $context . '] HTTP ' . $code );
            }
            return null;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $body ) ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log( 'Causabi [' . $context . '] invalid JSON response' );
            }
            return null;
        }

        return $body;
    }
}
