<?php
/**
 * WordPress Admin settings page: Settings → GEO Optimizer.
 *
 * Shows when not configured: onboarding (get API key → paste → save).
 * Shows when configured: AI Score, breakdown by category, issue list, Refresh button.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Causabi_Admin_Page {

    // Matches Schema_Injector so both read the same transient
    private function cache_key(): string {
        $host = wp_parse_url( get_site_url(), PHP_URL_HOST ) ?? '';
        return 'causabi_data_' . md5( str_replace( 'www.', '', strtolower( $host ) ) );
    }

    public function register_menu(): void {
        add_options_page(
            __( 'GEO Optimizer — AI Visibility', 'causabi-geo-optimizer' ),
            __( 'GEO Optimizer', 'causabi-geo-optimizer' ),
            'manage_options',
            'causabi-geo-optimizer',
            [ $this, 'render_page' ]
        );
    }

    public function register_settings(): void {
        register_setting( 'causabi_options', 'causabi_api_key', [
            'type'              => 'string',
            'sanitize_callback' => 'causabi_sanitize_api_key',
            'default'           => '',
        ] );
        register_setting( 'causabi_options', 'causabi_llms_txt_enabled', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
            'default'           => '1',
        ] );
        register_setting( 'causabi_options', 'causabi_ai_bots_enabled', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
            'default'           => '1',
        ] );

        add_settings_section( 'causabi_main', '', '__return_false', 'causabi-geo-optimizer' );

        add_settings_field(
            'causabi_api_key_field',
            __( 'API Key', 'causabi-geo-optimizer' ),
            [ $this, 'render_api_key_field' ],
            'causabi-geo-optimizer',
            'causabi_main'
        );
        add_settings_field(
            'causabi_llms_txt_field',
            __( 'llms.txt', 'causabi-geo-optimizer' ),
            [ $this, 'render_llms_txt_field' ],
            'causabi-geo-optimizer',
            'causabi_main'
        );
        add_settings_field(
            'causabi_ai_bots_field',
            __( 'AI crawlers in robots.txt', 'causabi-geo-optimizer' ),
            [ $this, 'render_ai_bots_field' ],
            'causabi-geo-optimizer',
            'causabi_main'
        );
    }

    // Checkboxes only POST a value when checked — normalize to '1' / '0'.
    public function sanitize_checkbox( $value ): string {
        return '1' === $value ? '1' : '0';
    }

    public function render_api_key_field(): void {
        $value = Causabi_Crypto::get_api_key();
        echo '<input type="text" name="causabi_api_key" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="causabi_..." />';
        echo '<p class="description">' . wp_kses_post( sprintf(
            /* translators: %s: link to causabi.com */
            __( 'Get your free API key at %s', 'causabi-geo-optimizer' ),
            '<a href="https://causabi.com" target="_blank">causabi.com</a>'
        ) ) . '</p>';
    }

    public function render_llms_txt_field(): void {
        $enabled = '1' === get_option( 'causabi_llms_txt_enabled', '1' );
        echo '<label><input type="checkbox" name="causabi_llms_txt_enabled" value="1"' . checked( $enabled, true, false ) . ' /> ';
        esc_html_e( 'Serve /llms.txt for AI agents', 'causabi-geo-optimizer' );
        echo '</label>';
        echo '<p class="description">' . esc_html__( 'A plain-text summary of your site, generated automatically and refreshed every 7 days. Read by AI agents and research tools that support llms.txt — not all of them do yet.', 'causabi-geo-optimizer' ) . '</p>';

        $llms_txt = new Causabi_Llms_Txt();
        if ( $llms_txt->physical_file_exists() ) {
            echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'A physical llms.txt file already exists in your site root. Your web server will serve that file directly — this setting has no effect until it is removed.', 'causabi-geo-optimizer' ) . '</p></div>';
        }
    }

    public function render_ai_bots_field(): void {
        $enabled = '1' === get_option( 'causabi_ai_bots_enabled', '1' );
        echo '<label><input type="checkbox" name="causabi_ai_bots_enabled" value="1"' . checked( $enabled, true, false ) . ' /> ';
        esc_html_e( 'Explicitly allow AI crawlers (GPTBot, ClaudeBot, PerplexityBot, and others) in robots.txt', 'causabi-geo-optimizer' );
        echo '</label>';
        echo '<p class="description">' . esc_html__( 'Adds Allow rules to your robots.txt so AI search engines are not accidentally blocked from reading your site.', 'causabi-geo-optimizer' ) . '</p>';

        $robots = new Causabi_Robots();
        if ( $robots->physical_file_exists() ) {
            echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'A physical robots.txt file exists in your site root. WordPress cannot modify it — this setting has no effect. Check that file manually for Disallow rules blocking AI crawlers.', 'causabi-geo-optimizer' ) . '</p></div>';
        } else {
            $blocked = $robots->find_blocking_rules();
            if ( $blocked ) {
                echo '<div class="notice notice-warning inline"><p>' . esc_html( sprintf(
                    /* translators: %s: comma-separated list of blocked bot names */
                    __( 'Your robots.txt currently blocks: %s. Review your SEO plugin settings if this is unintentional.', 'causabi-geo-optimizer' ),
                    implode( ', ', $blocked )
                ) ) . '</p></div>';
            }
        }
    }

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $api_key = Causabi_Crypto::get_api_key();
        $data    = get_transient( $this->cache_key() );
        ?>
        <div class="wrap causabi-admin">
            <h1><?php esc_html_e( 'Causabi GEO Optimizer', 'causabi-geo-optimizer' ); ?></h1>
            <p class="causabi-tagline"><?php esc_html_e( 'Make your website visible to ChatGPT, Gemini, Grok, Claude, and other AI search engines.', 'causabi-geo-optimizer' ); ?></p>

            <?php if ( $api_key && $data ) : ?>

                <?php $this->render_score_card( $data ); ?>
                <?php $this->render_breakdown( $data ); ?>
                <?php $this->render_issues( $data ); ?>
                <?php $this->render_refresh_button( $data ); ?>
                <?php $this->render_what_was_added( $data ); ?>

            <?php elseif ( $api_key ) : ?>

                <?php if ( $this->trigger_first_scan( $api_key ) ) : ?>

                    <div class="notice notice-info inline">
                        <p><?php esc_html_e( 'Analyzing your website for the first time — this may take up to 30 seconds. Reload this page in a moment.', 'causabi-geo-optimizer' ); ?></p>
                    </div>

                <?php else : ?>

                    <div class="notice notice-error inline">
                        <p><?php esc_html_e( 'Could not reach the Causabi API to scan your site. This can be a temporary network issue, or your API key may be invalid.', 'causabi-geo-optimizer' ); ?></p>
                        <p><a href="<?php echo esc_url( admin_url( 'options-general.php?page=causabi-geo-optimizer' ) ); ?>" class="button"><?php esc_html_e( 'Try again', 'causabi-geo-optimizer' ); ?></a></p>
                    </div>

                <?php endif; ?>

            <?php else : ?>

                <?php $this->render_onboarding(); ?>

            <?php endif; ?>

            <hr class="causabi-settings-divider">
            <h2><?php esc_html_e( 'Settings', 'causabi-geo-optimizer' ); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'causabi_options' );
                do_settings_sections( 'causabi-geo-optimizer' );
                submit_button( __( 'Save & Connect', 'causabi-geo-optimizer' ) );
                ?>
            </form>
        </div>
        <?php
    }

    private function render_score_card( array $data ): void {
        $score = intval( $data['score'] ?? 0 );
        $grade = $data['grade'] ?? '?';
        $class = $score >= 80 ? 'good' : ( $score >= 50 ? 'medium' : 'poor' );
        ?>
        <div class="causabi-score-card causabi-score-<?php echo esc_attr( $class ); ?>">
            <div class="causabi-score-number"><?php echo (int) $score; ?>/100</div>
            <div class="causabi-score-label">
                <?php esc_html_e( 'AI Readiness Score', 'causabi-geo-optimizer' ); ?>
                &nbsp;—&nbsp; <?php esc_html_e( 'Grade', 'causabi-geo-optimizer' ); ?> <?php echo esc_html( $grade ); ?>
            </div>
            <p class="causabi-score-desc">
                <?php esc_html_e( 'This score shows how easily ChatGPT, Gemini, Grok, Claude, and other AI search engines can find, understand, and cite your website.', 'causabi-geo-optimizer' ); ?>
            </p>
        </div>
        <?php
    }

    private function render_breakdown( array $data ): void {
        if ( empty( $data['breakdown'] ) ) return;

        $labels = [
            'robots_txt'    => [
                __( 'robots.txt', 'causabi-geo-optimizer' ),
                __( 'Controls whether AI crawlers like GPTBot, ClaudeBot, and Gemini-Bot are allowed to scan your site. If blocked, they cannot read or cite your content.', 'causabi-geo-optimizer' ),
                15,
            ],
            'schema_org'    => [
                __( 'Schema.org markup', 'causabi-geo-optimizer' ),
                __( 'Structured data that tells AI exactly what your business does, who you are, and how to contact you. Makes you more likely to appear in AI answers.', 'causabi-geo-optimizer' ),
                25,
            ],
            'faq_schema'    => [
                __( 'FAQ Schema', 'causabi-geo-optimizer' ),
                __( 'FAQ markup has been shown to increase AI citation rate by up to 41%. AI engines prefer sites with clear Q&A content.', 'causabi-geo-optimizer' ),
                20,
            ],
            'content_depth' => [
                __( 'Content depth', 'causabi-geo-optimizer' ),
                __( 'AI search engines need concrete text to quote: explicit prices, facts, and confident wording. Thin pages or hedged claims ("may", "possibly") are rarely cited.', 'causabi-geo-optimizer' ),
                15,
            ],
            'brand_signals' => [
                __( 'Brand signals (NAP)', 'causabi-geo-optimizer' ),
                __( 'Name, Address, Phone — contact details that help AI verify your business identity and show it in local and branded searches.', 'causabi-geo-optimizer' ),
                10,
            ],
            'freshness'     => [
                __( 'Content freshness', 'causabi-geo-optimizer' ),
                __( 'AI search engines prefer recently updated content. Stale pages are ranked lower in AI-generated answers.', 'causabi-geo-optimizer' ),
                10,
            ],
        ];
        ?>
        <h2><?php esc_html_e( 'Score Breakdown', 'causabi-geo-optimizer' ); ?></h2>
        <table class="wp-list-table widefat fixed causabi-breakdown">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Category', 'causabi-geo-optimizer' ); ?></th>
                    <th class="causabi-col-score"><?php esc_html_e( 'Score', 'causabi-geo-optimizer' ); ?></th>
                    <th><?php esc_html_e( 'What it means for your visibility', 'causabi-geo-optimizer' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $labels as $key => [ $label, $desc, $max ] ) :
                    $val  = intval( $data['breakdown'][ $key ] ?? 0 );
                    $icon = esc_html( $val >= $max ? '✅' : ( $val > 0 ? '⚠️' : '❌' ) );
                ?>
                <tr>
                    <td><strong><?php echo esc_html( $icon ) . ' ' . esc_html( $label ); ?></strong></td>
                    <td><?php echo (int) $val . '/' . (int) $max; ?></td>
                    <td><?php echo esc_html( $desc ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function render_issues( array $data ): void {
        if ( empty( $data['issues'] ) ) {
            echo '<div class="notice notice-success inline"><p>' . esc_html__( '🎉 No critical issues found. Your site is well-optimized for AI search engines!', 'causabi-geo-optimizer' ) . '</p></div>';
            return;
        }
        ?>
        <h2><?php esc_html_e( 'Issues to Fix', 'causabi-geo-optimizer' ); ?></h2>
        <p><?php esc_html_e( 'Fixing these issues will increase how often your site appears in ChatGPT, Gemini, Grok, and Claude answers.', 'causabi-geo-optimizer' ); ?></p>
        <ul class="causabi-issues">
            <?php foreach ( $data['issues'] as $issue ) :
                $severity = sanitize_key( $issue['severity'] ?? 'warning' );
                $icon     = esc_html( 'critical' === $severity ? '❌' : '⚠️' );
            ?>
            <li class="causabi-issue causabi-issue--<?php echo esc_attr( $severity ); ?>">
                <strong><?php echo esc_html( $icon ) . ' ' . esc_html( $issue['title'] ?? '' ); ?></strong>
                <span><?php echo esc_html( $issue['description'] ?? '' ); ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    private function render_refresh_button( array $data ): void {
        $scanned = $data['scanned_at'] ?? '';
        ?>
        <p class="causabi-refresh-row">
            <button id="causabi-refresh-btn" class="button button-primary">
                <?php esc_html_e( 'Refresh Now', 'causabi-geo-optimizer' ); ?>
            </button>
            <span id="causabi-refresh-status" class="causabi-refresh-status"></span>
        </p>
        <p><small>
            <?php
            printf(
                /* translators: %s: scan timestamp */
                esc_html__( 'Last update to Schema.org markup and llms.txt: %s. Auto-refreshes every 7 days.', 'causabi-geo-optimizer' ),
                esc_html( $scanned )
            );
            ?>
        </small></p>
        <?php
    }

    private function render_what_was_added( array $data ): void {
        ?>
        <h2><?php esc_html_e( 'What Was Added to Your Site', 'causabi-geo-optimizer' ); ?></h2>
        <p><?php esc_html_e( 'The following markup is now automatically injected into your site\'s <head> section:', 'causabi-geo-optimizer' ); ?></p>
        <ul>
            <?php if ( ! empty( $data['schema_json'] ) ) : ?>
            <li>✅ <strong><?php esc_html_e( 'Organization Schema', 'causabi-geo-optimizer' ); ?></strong> — <?php esc_html_e( 'tells AI your business name, website, and contact info', 'causabi-geo-optimizer' ); ?></li>
            <?php endif; ?>
            <?php if ( ! empty( $data['faq_json'] ) ) : ?>
            <li>✅ <strong><?php esc_html_e( 'FAQ Schema', 'causabi-geo-optimizer' ); ?></strong> — <?php esc_html_e( 'boosts AI citation rate by up to 41%', 'causabi-geo-optimizer' ); ?></li>
            <?php endif; ?>
            <?php if ( ! empty( $data['llms_txt'] ) && '1' === get_option( 'causabi_llms_txt_enabled', '1' ) ) : ?>
            <li>✅ <strong><a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank">llms.txt</a></strong> — <?php esc_html_e( 'plain-text site summary for AI agents that support it', 'causabi-geo-optimizer' ); ?></li>
            <?php endif; ?>
            <?php if ( ! empty( $data['robots_patch'] ) ) : ?>
            <li>⚠️ <strong><?php esc_html_e( 'robots.txt patch available', 'causabi-geo-optimizer' ); ?></strong> — <?php esc_html_e( 'AI crawlers are currently blocked. Download the patch to fix this.', 'causabi-geo-optimizer' ); ?>
                <a href="<?php echo esc_url( CAUSABI_API_URL . '/api/score/' . wp_parse_url( get_site_url(), PHP_URL_HOST ) ); ?>" target="_blank"><?php esc_html_e( 'View score page →', 'causabi-geo-optimizer' ); ?></a>
            </li>
            <?php endif; ?>
        </ul>
        <?php
    }

    private function render_onboarding(): void {
        ?>
        <div class="causabi-onboarding">
            <h2><?php esc_html_e( 'Get Started in 2 Minutes', 'causabi-geo-optimizer' ); ?></h2>
            <p><?php esc_html_e( '73% of websites are invisible to AI search engines. This plugin fixes that automatically — no coding required.', 'causabi-geo-optimizer' ); ?></p>
            <ol class="causabi-steps">
                <li>
                    <strong><?php esc_html_e( 'Get your free API key', 'causabi-geo-optimizer' ); ?></strong><br>
                    <?php echo wp_kses_post( sprintf(
                        /* translators: %s: link to causabi.com */
                        __( 'Go to %s, sign up for free, and copy your API key.', 'causabi-geo-optimizer' ),
                        '<a href="https://causabi.com" target="_blank">causabi.com</a>'
                    ) ); ?>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Paste it below and click Save', 'causabi-geo-optimizer' ); ?></strong><br>
                    <?php esc_html_e( 'We will analyze your site and add Schema.org markup automatically.', 'causabi-geo-optimizer' ); ?>
                </li>
                <li>
                    <strong><?php esc_html_e( 'See your AI Readiness Score', 'causabi-geo-optimizer' ); ?></strong><br>
                    <?php esc_html_e( 'Your score and detailed report will appear right here within 30 seconds.', 'causabi-geo-optimizer' ); ?>
                </li>
            </ol>
        </div>
        <?php
    }

    // Returns true if a scan is in flight or just succeeded (show "Analyzing...").
    // Returns false if the scan just failed (show the error notice + retry link) —
    // the transient lock is released immediately so the retry link works right away
    // instead of forcing the user to wait out the full hour-long lock (MAJOR-1 fix).
    private function trigger_first_scan( string $api_key ): bool {
        if ( get_transient( 'causabi_scan_queued' ) ) return true;

        set_transient( 'causabi_scan_queued', 1, HOUR_IN_SECONDS );
        $domain = str_replace( 'www.', '', strtolower( wp_parse_url( get_site_url(), PHP_URL_HOST ) ?? '' ) );
        $client = new Causabi_API_Client( $api_key );
        $data   = $client->analyze( get_site_url(), $domain );

        if ( ! $data ) {
            delete_transient( 'causabi_scan_queued' );
            return false;
        }

        set_transient( 'causabi_data_' . md5( $domain ), $data, DAY_IN_SECONDS );
        return true;
    }

    public function ajax_refresh(): void {
        check_ajax_referer( 'causabi_refresh', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $lock_key = 'causabi_refreshing_' . get_current_user_id();
        if ( get_transient( $lock_key ) ) {
            wp_send_json_error( [ 'message' => __( 'Already refreshing. Please wait.', 'causabi-geo-optimizer' ) ] );
            return;
        }
        set_transient( $lock_key, 1, 60 );

        $api_key = Causabi_Crypto::get_api_key();
        if ( ! $api_key ) {
            delete_transient( $lock_key );
            wp_send_json_error( 'API key not configured' );
        }

        $domain    = str_replace( 'www.', '', strtolower( wp_parse_url( get_site_url(), PHP_URL_HOST ) ?? '' ) );
        $cache_key = 'causabi_data_' . md5( $domain );
        delete_transient( $cache_key );

        $client = new Causabi_API_Client( $api_key );
        $data   = $client->analyze( get_site_url(), $domain );

        if ( ! $data ) {
            delete_transient( $lock_key );
            wp_send_json_error( 'Could not reach Causabi API. Please try again.' );
        }

        set_transient( $cache_key, $data, DAY_IN_SECONDS );
        delete_transient( $lock_key );
        wp_send_json_success( [ 'score' => $data['score'], 'grade' => $data['grade'] ] );
    }

    public function enqueue_assets( string $hook ): void {
        if ( $hook !== 'settings_page_causabi-geo-optimizer' ) return;

        wp_enqueue_style(
            'causabi-admin',
            CAUSABI_PLUGIN_URL . 'assets/admin.css',
            [],
            CAUSABI_VERSION
        );
        wp_enqueue_script(
            'causabi-admin',
            CAUSABI_PLUGIN_URL . 'assets/admin.js',
            [ 'jquery' ],
            CAUSABI_VERSION,
            true
        );
        wp_localize_script( 'causabi-admin', 'causabiAjax', [
            'nonce'   => wp_create_nonce( 'causabi_refresh' ),
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'strings' => [
                'analyzing' => __( 'Analyzing...', 'causabi-geo-optimizer' ),
                'done'      => __( '✅ Done! Reloading...', 'causabi-geo-optimizer' ),
                'error'     => __( '❌ Error. Please try again.', 'causabi-geo-optimizer' ),
                'refresh'   => __( 'Refresh Now', 'causabi-geo-optimizer' ),
            ],
        ] );
    }
}
