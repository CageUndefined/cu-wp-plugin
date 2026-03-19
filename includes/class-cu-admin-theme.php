<?php
/**
 * CU admin theme: brand colors, logo, footer, and login page.
 */
class CU_Admin_Theme
{
    public static function init(): void
    {
        // Admin panel styles.
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_styles']);
        add_action('admin_head', [__CLASS__, 'inline_logo_css']);
        add_action('wp_head', [__CLASS__, 'inline_logo_css']);

        // Admin bar logo.
        add_action('admin_bar_menu', [__CLASS__, 'replace_admin_bar_logo'], 11);

        // Footer.
        add_filter('admin_footer_text', [__CLASS__, 'footer_text']);
        add_filter('update_footer', [__CLASS__, 'footer_version'], 11);

        // Login page.
        add_action('login_enqueue_scripts', [__CLASS__, 'enqueue_login_styles']);
        add_filter('login_headerurl', [__CLASS__, 'login_logo_url']);
        add_filter('login_headertext', [__CLASS__, 'login_logo_text']);
    }

    public static function enqueue_admin_styles(): void
    {
        wp_enqueue_style(
            'cu-google-fonts',
            'https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800&display=swap',
            [],
            null
        );

        wp_enqueue_style(
            'cu-admin-theme',
            CU_BRANDING_URL . 'assets/css/admin-theme.css',
            ['cu-google-fonts'],
            CU_BRANDING_VERSION
        );
    }

    public static function enqueue_login_styles(): void
    {
        wp_enqueue_style(
            'cu-google-fonts',
            'https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800&display=swap',
            [],
            null
        );

        wp_enqueue_style(
            'cu-login-theme',
            CU_BRANDING_URL . 'assets/css/login-page.css',
            ['cu-google-fonts'],
            CU_BRANDING_VERSION
        );
    }

    /**
     * Replace the WordPress logo in the admin bar with the CU mark.
     */
    public static function replace_admin_bar_logo(\WP_Admin_Bar $wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('wp-logo');

        $wp_admin_bar->add_node([
            'id'    => 'cu-logo',
            'title' => '<span class="ab-icon cu-admin-bar-logo"></span>',
            'href'  => admin_url(),
        ]);
    }

    /**
     * Inline CSS for the admin bar logo icon (embedded SVG).
     */
    public static function inline_logo_css(): void
    {
        // CU mark: 3 vertical bars + teal crossbar.
        $svg = rawurlencode('<svg viewBox="0 0 1080 1080" fill="none" xmlns="http://www.w3.org/2000/svg"><g fill="#fffdee"><rect x="505.28" y="186.29" width="54.11" height="786.33" rx="10.83" ry="10.83"/><rect x="253.42" y="286.61" width="54.11" height="585.69" rx="10.83" ry="10.83"/><rect x="757.14" y="286.61" width="54.11" height="585.69" rx="10.83" ry="10.83"/></g><rect fill="#198e7b" x="54.83" y="546.9" width="955.01" height="65.11"/></svg>');

        echo '<style>
            .cu-admin-bar-logo {
                display: inline-block !important;
                width: 20px !important;
                height: 20px !important;
                background: url("data:image/svg+xml,' . $svg . '") no-repeat center !important;
                background-size: contain !important;
            }
            .cu-admin-bar-logo::before { content: none !important; }
        </style>' . "\n";
    }

    public static function footer_text(): string
    {
        return 'Hosted by <a href="https://cageundefined.com" target="_blank" rel="noopener">Cage Undefined</a>';
    }

    public static function footer_version(): string
    {
        return '';
    }

    public static function login_logo_url(): string
    {
        return home_url('/');
    }

    public static function login_logo_text(): string
    {
        return get_bloginfo('name');
    }
}
