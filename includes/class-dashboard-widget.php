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
        $grade = esc_html( $data['grade'] ?? '?' );
        $color = $score >= 80 ? '#00a32a' : ( $score >= 50 ? '#dba617' : '#d63638' );

        $schema_active = ! empty( $data['schema_json'] );
        $faq_active    = ! empty( $data['faq_json'] );
        $issues_count  = count( $data['issues'] ?? [] );

        echo '<div style="text-align:center;padding:8px 0 12px">';
        printf(
            '<div style="font-size:52px;font-weight:700;color:%s;line-height:1">%d</div>',
            esc_attr( $color ),
            $score
        );
        printf(
            '<div style="color:#666;font-size:13px;margin-top:4px">%s — %s %s</div>',
            esc_html__( 'AI Readiness Score', 'causabi-geo-optimizer' ),
            esc_html__( 'Grade', 'causabi-geo-optimizer' ),
            $grade
        );
        echo '</div>';

        echo '<hr style="margin:8px 0">';
        echo '<ul style="margin:0;padding:0 0 0 4px;list-style:none;font-size:13px">';

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
                $issues_count
            ) . '</li>';
        }
        echo '</ul>';

        printf(
            '<p style="margin:12px 0 0;text-align:center"><a href="%s" class="button">%s</a></p>',
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
