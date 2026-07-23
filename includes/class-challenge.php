<?php
/**
 * Serves the one-button provision challenge token at
 * /.well-known/causabi-challenge — same virtual-file pattern (rewrite
 * rule + query var + template_redirect) as Causabi_Llms_Txt.
 *
 * Flow (wp-one-button-plan-2026-07-23.md §1): the backend's
 * /wp/provision endpoint hands the plugin a token; the plugin stores it
 * in a short-lived option and publishes it here; the backend then fetches
 * this URL itself to prove the plugin controls the domain before minting
 * a keyless API key.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Causabi_Challenge {

    private const QUERY_VAR = 'causabi_challenge';
    private const OPTION_KEY = 'causabi_challenge_token';

    public function add_rewrite_rule(): void {
        add_rewrite_rule( '^\.well-known/causabi-challenge$', 'index.php?' . self::QUERY_VAR . '=1', 'top' );
    }

    public function add_query_var( array $vars ): array {
        $vars[] = self::QUERY_VAR;
        return $vars;
    }

    /**
     * Cancels the canonical trailing-slash redirect — same reason as
     * Causabi_Llms_Txt::cancel_canonical_redirect.
     */
    public function cancel_canonical_redirect( $redirect_url ) {
        if ( get_query_var( self::QUERY_VAR ) ) {
            return false;
        }
        return $redirect_url;
    }

    public function maybe_serve(): void {
        if ( empty( get_query_var( self::QUERY_VAR ) ) ) {
            return;
        }

        $token = get_option( self::OPTION_KEY, '' );
        status_header( $token ? 200 : 404 );
        nocache_headers();
        header( 'Content-Type: text/plain; charset=utf-8' );
        if ( $token ) {
            echo esc_html( $token ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_html already applied
        }
        exit;
    }

    /**
     * Stores the token the backend issued from /wp/provision so
     * maybe_serve() can publish it. No explicit TTL here — the backend
     * enforces the 10-minute challenge TTL server-side (Redis); an
     * option lingering past that only matters if a NEW provision call
     * overwrites it, which store_token() always does.
     */
    public function store_token( string $token ): void {
        update_option( self::OPTION_KEY, $token, false );
    }

    public function clear_token(): void {
        delete_option( self::OPTION_KEY );
    }
}
