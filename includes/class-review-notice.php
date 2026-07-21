<?php
/**
 * A one-time, dismissible admin notice asking for a WordPress.org review.
 *
 * Only shown to admins on our own settings page and the dashboard, and only
 * once the plugin has proven its value: an API key is connected, a score has
 * been fetched, and at least a week has passed since activation. Either button
 * dismisses it for good — we never nag twice.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Causabi_Review_Notice {

    private const DISMISS_OPTION   = 'causabi_review_dismissed';
    private const ACTIVATED_OPTION = 'causabi_activated_at';
    private const MIN_AGE          = 7 * DAY_IN_SECONDS;
    private const REVIEW_URL       = 'https://wordpress.org/support/plugin/causabi-geo-optimizer/reviews/#new-post';

    /** Stamp the activation time once, so we can wait before asking. */
    public static function record_activation(): void {
        if ( ! get_option( self::ACTIVATED_OPTION ) ) {
            add_option( self::ACTIVATED_OPTION, time() );
        }
    }

    public function maybe_show(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( get_option( self::DISMISS_OPTION ) ) return;

        $screen = get_current_screen();
        $allowed = [ 'dashboard', 'settings_page_causabi-geo-optimizer' ];
        if ( ! $screen || ! in_array( $screen->id, $allowed, true ) ) return;

        $activated = (int) get_option( self::ACTIVATED_OPTION, 0 );
        if ( ! $activated || ( time() - $activated ) < self::MIN_AGE ) return;

        if ( ! $this->has_score() ) return;

        $this->render();
    }

    private function has_score(): bool {
        if ( ! get_option( 'causabi_api_key', '' ) ) return false;
        $host   = wp_parse_url( get_site_url(), PHP_URL_HOST ) ?? '';
        $domain = str_replace( 'www.', '', strtolower( $host ) );
        $data   = get_transient( 'causabi_data_' . md5( $domain ) );
        return ! empty( $data ) && isset( $data['score'] );
    }

    private function render(): void {
        $go      = wp_nonce_url( add_query_arg( 'causabi_review_action', 'go' ), 'causabi_review' );
        $dismiss = wp_nonce_url( add_query_arg( 'causabi_review_action', 'dismiss' ), 'causabi_review' );
        ?>
        <div class="notice notice-info">
            <p><?php esc_html_e( 'Enjoying Causabi GEO Optimizer? A quick review on WordPress.org helps other site owners find it — it takes a minute.', 'causabi-geo-optimizer' ); ?></p>
            <p>
                <a href="<?php echo esc_url( $go ); ?>" class="button button-primary"><?php esc_html_e( 'Leave a review', 'causabi-geo-optimizer' ); ?></a>
                <a href="<?php echo esc_url( $dismiss ); ?>" class="button"><?php esc_html_e( 'Maybe later', 'causabi-geo-optimizer' ); ?></a>
            </p>
        </div>
        <?php
    }

    /** Handles both buttons: each dismisses the notice permanently. */
    public function handle_action(): void {
        if ( empty( $_GET['causabi_review_action'] ) ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;
        check_admin_referer( 'causabi_review' );

        update_option( self::DISMISS_OPTION, 1 );

        if ( 'go' === $_GET['causabi_review_action'] ) {
            wp_redirect( self::REVIEW_URL ); // external — wp_safe_redirect would strip it
            exit;
        }
        wp_safe_redirect( remove_query_arg( [ 'causabi_review_action', '_wpnonce' ] ) );
        exit;
    }
}
