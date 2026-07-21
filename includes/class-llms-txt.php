<?php
/**
 * Serves a virtual /llms.txt at the site root.
 *
 * Content comes from the same transient the Causabi API already returns
 * (data['llms_txt']) — populated by analyze() / the 7-day cron refresh,
 * same cache the Schema Injector reads. No extra API calls from the frontend.
 *
 * Uses the standard WP "virtual file" pattern: rewrite rule + query var +
 * template_redirect, same approach WP core uses for /wp-sitemap.xml.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Causabi_Llms_Txt {

    private const QUERY_VAR = 'causabi_llms_txt';

    public function add_rewrite_rule(): void {
        add_rewrite_rule( '^llms\.txt$', 'index.php?' . self::QUERY_VAR . '=1', 'top' );
    }

    public function add_query_var( array $vars ): array {
        $vars[] = self::QUERY_VAR;
        return $vars;
    }

    /**
     * Cancels the canonical trailing-slash redirect for /llms.txt —
     * without this WP 301s the request to /llms.txt/ before we can serve it.
     */
    public function cancel_canonical_redirect( $redirect_url ) {
        if ( get_query_var( self::QUERY_VAR ) ) {
            return false;
        }
        return $redirect_url;
    }

    public function maybe_serve(): void {
        // template_redirect passes no arguments — read the main query directly.
        if ( empty( get_query_var( self::QUERY_VAR ) ) ) {
            return;
        }

        if ( '1' !== get_option( 'causabi_llms_txt_enabled', '1' ) ) {
            status_header( 404 );
            nocache_headers();
            exit;
        }

        $content = $this->get_content();

        status_header( 200 );
        nocache_headers();
        header( 'Content-Type: text/plain; charset=utf-8' );
        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plain text file, not HTML
        exit;
    }

    /**
     * Returns cached llms.txt content, generating a fallback locally
     * if the API hasn't returned one yet (e.g. before first scan).
     */
    public function get_content(): string {
        $data = get_transient( $this->cache_key() );
        if ( ! empty( $data['llms_txt'] ) ) {
            return $data['llms_txt'];
        }

        return $this->build_fallback();
    }

    /**
     * True if a physical llms.txt file already exists in the site root —
     * in that case Apache/Nginx serves it directly and this rewrite rule
     * never fires. Used to warn the admin instead of silently no-op'ing.
     */
    public function physical_file_exists(): bool {
        return file_exists( ABSPATH . 'llms.txt' );
    }

    private function cache_key(): string {
        $host = wp_parse_url( get_site_url(), PHP_URL_HOST ) ?? '';
        return 'causabi_data_' . md5( str_replace( 'www.', '', strtolower( $host ) ) );
    }

    /**
     * TODO(API): once ai.causabi.com/api/v1/wp/analyze always includes a
     * populated llms_txt field for every plan, this fallback can be removed.
     * Today it only covers the gap between activation and the first scan.
     */
    private function build_fallback(): string {
        $name = get_bloginfo( 'name' );
        $desc = get_bloginfo( 'description' );
        $url  = get_site_url();

        $lines   = [ '# ' . $name ];
        if ( $desc ) {
            $lines[] = '';
            $lines[] = '> ' . $desc;
        }
        $lines[] = '';
        $lines[] = '## Links';
        $lines[] = '- Website: ' . $url;

        $pages = $this->recent_pages();
        if ( $pages ) {
            $lines[] = '';
            $lines[] = '## Pages';
            foreach ( $pages as $page ) {
                $lines[] = '- [' . $page['title'] . '](' . $page['url'] . ')';
            }
        }

        return implode( "\n", $lines ) . "\n";
    }

    /**
     * @return array<int, array{title: string, url: string}>
     */
    private function recent_pages(): array {
        $posts = get_posts( [
            'post_type'      => [ 'page', 'post' ],
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ] );

        $pages = [];
        foreach ( $posts as $post ) {
            $pages[] = [
                'title' => get_the_title( $post ),
                'url'   => get_permalink( $post ),
            ];
        }
        return $pages;
    }
}
