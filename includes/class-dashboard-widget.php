<?php
/**
 * Dashboard widget shown on WP Admin home screen.
 * Displays AI Score at a glance + link to full report.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Causabi_Dashboard_Widget {

    public function register(): void {
        wp_add_dashboard_widget(
            'causabi_geo_score',
            __( 'AI Search Visibility Score', 'causabi-geo-optimizer' ),
            [ $this, 'render' ]
        );
    }

    public function enqueue_styles( string $hook ): void {
        if ( $hook !== 'index.php' ) return;
        wp_enqueue_style(
            'causabi-admin',
            CAUSABI_PLUGIN_URL . 'assets/admin.css',
            [],
            CAUSABI_VERSION
        );
    }

    public function render(): void {
        $api_key = get_option( 'causabi_api_key', '' );

        if ( ! $api_key ) {
            $this->render_not_configured();
            return;
        }

        $host      = wp_parse_url( get_site_url(), PHP_URL_HOST ) ?? '';
        $domain    = str_replace( 'www.', '', strtolower( $host ) );
        $cache_key = 'causabi_data_' . md5( $domain );
        $data      = get_transient( $cache_key );

        if ( ! $data ) {
            echo '<p>' . esc_html__( 'Analyzing your site...', 'causabi-geo-optimizer' ) . '</p>';
            return;
        }

        $score = intval( $data['score'] ?? 0 );
        $grade = $data['grade'] ?? '?';
        $class = $score >= 80 ? 'good' : ( $score >= 50 ? 'medium' : 'poor' );

        $schema_active = ! empty( $data['schema_json'] );
        $faq_active    = ! empty( $data['faq_json'] );
        $issues_count  = count( $data['issues'] ?? [] );

        echo '<div class="causabi-widget-center">';
        printf(
            '<div class="causabi-widget-score causabi-widget-score--%s">%d</div>',
            esc_attr( $class ),
            (int) $score
        );
        printf(
            '<div class="causabi-widget-label">%s — %s %s</div>',
            esc_html__( 'AI Readiness Score', 'causabi-geo-optimizer' ),
            esc_html__( 'Grade', 'causabi-geo-optimizer' ),
            esc_html( $grade )
        );
        echo '</div>';

        echo '<hr>';
        echo '<ul class="causabi-widget-list">';

        if ( $schema_active ) {
            echo '<li>✅ ' . esc_html__( 'Schema.org active', 'causabi-geo-optimizer' ) . '</li>';
        } else {
            echo '<li>❌ ' . esc_html__( 'Schema.org not found', 'causabi-geo-optimizer' ) . '</li>';
        }
        if ( $faq_active ) {
            echo '<li>✅ ' . esc_html__( 'FAQ Schema active', 'causabi-geo-optimizer' ) . '</li>';
        } else {
            echo '<li>⚠️ ' . esc_html__( 'FAQ Schema missing', 'causabi-geo-optimizer' ) . '</li>';
        }
        if ( $issues_count > 0 ) {
            echo '<li>⚠️ ' . sprintf(
                /* translators: %d: number of issues */
                esc_html( _n( '%d issue to fix', '%d issues to fix', $issues_count, 'causabi-geo-optimizer' ) ),
                (int) $issues_count
            ) . '</li>';
        }
        echo '</ul>';

        printf(
            '<p class="causabi-widget-footer"><a href="%s" class="button">%s</a></p>',
            esc_url( admin_url( 'options-general.php?page=causabi-geo-optimizer' ) ),
            esc_html__( 'View full report →', 'causabi-geo-optimizer' )
        );
    }

    private function render_not_configured(): void {
        echo '<p>' . esc_html__( 'Connect your Causabi account to see your AI Readiness Score.', 'causabi-geo-optimizer' ) . '</p>';
        printf(
            '<a href="%s" class="button button-primary">%s</a>',
            esc_url( admin_url( 'options-general.php?page=causabi-geo-optimizer' ) ),
            esc_html__( 'Set up now →', 'causabi-geo-optimizer' )
        );
    }
}
