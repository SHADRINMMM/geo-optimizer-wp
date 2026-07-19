<?php
/**
 * Adds explicit "Allow" rules for AI crawlers to WordPress's virtual robots.txt.
 *
 * Hooks into the standard `robots_txt` filter. Only affects the virtual
 * robots.txt WordPress generates on the fly — if the site has a physical
 * robots.txt file in its root, WordPress never calls this filter at all
 * (Apache/Nginx serves the physical file directly), so we warn about that
 * in the admin instead of pretending we patched it.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Causabi_Robots {

    // The bots most GEO-relevant AI search engines use to crawl for citations.
    private const AI_BOTS = [
        'GPTBot',
        'ChatGPT-User',
        'OAI-SearchBot',
        'ClaudeBot',
        'Claude-Web',
        'anthropic-ai',
        'PerplexityBot',
        'Google-Extended',
        'GoogleOther',
        'Bingbot',
    ];

    public function filter_robots_txt( string $output, bool $public ): string {
        if ( ! $public ) {
            return $output; // site set to discourage search engines — respect that, don't override
        }
        if ( '1' !== get_option( 'causabi_ai_bots_enabled', '1' ) ) {
            return $output;
        }

        $lines = [ '', '# Added by Causabi GEO Optimizer — allow AI search crawlers' ];
        foreach ( self::AI_BOTS as $bot ) {
            $lines[] = 'User-agent: ' . $bot;
            $lines[] = 'Allow: /';
        }

        return rtrim( $output ) . "\n" . implode( "\n", $lines ) . "\n";
    }

    /**
     * True if a physical robots.txt exists in the site root — WordPress's
     * `robots_txt` filter never fires in that case, so our Allow rules
     * above are silently ignored. Admin page shows a warning when this
     * is true instead of implying the toggle did something.
     */
    public function physical_file_exists(): bool {
        return file_exists( ABSPATH . 'robots.txt' );
    }

    /**
     * Checks the *existing* virtual robots.txt content for Disallow rules
     * that would block known AI bots, so we can warn the admin even when
     * our own Allow rules (added above) are present — some site configs
     * (other SEO plugins, manual filters) add blanket Disallow: / rules
     * that a later Allow: / cannot override for crawlers that honor the
     * first matching rule.
     */
    public function find_blocking_rules(): array {
        $content = $this->current_robots_txt();
        if ( ! $content ) return [];

        $blocked = [];
        $current_agents = [];
        foreach ( preg_split( '/\r\n|\r|\n/', $content ) as $line ) {
            $line = trim( $line );
            if ( '' === $line || '#' === ( $line[0] ?? '' ) ) continue;

            if ( preg_match( '/^user-agent:\s*(.+)$/i', $line, $m ) ) {
                $agent = trim( $m[1] );
                $current_agents = ( '*' === $agent || in_array( $agent, self::AI_BOTS, true ) )
                    ? array_merge( $current_agents, [ $agent ] )
                    : [];
                continue;
            }

            if ( preg_match( '/^disallow:\s*\/\s*$/i', $line ) && $current_agents ) {
                $blocked = array_merge( $blocked, $current_agents );
                $current_agents = [];
            }
        }

        return array_values( array_unique( $blocked ) );
    }

    private function current_robots_txt(): string {
        if ( $this->physical_file_exists() ) {
            $file = @file_get_contents( ABSPATH . 'robots.txt' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
            return $file !== false ? $file : '';
        }

        ob_start();
        do_action( 'do_robots' );
        return (string) ob_get_clean();
    }
}
