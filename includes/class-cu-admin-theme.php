<?php
if (! defined('ABSPATH')) {
    exit;
}

/**
 * CU admin theme: brand colors, logo, footer, and login page.
 */
class CU_Admin_Theme
{
    public static function init(): void
    {
        // Register and force CU color scheme.
        add_action('admin_init', [__CLASS__, 'register_color_scheme']);
        add_filter('get_user_option_admin_color', fn () => 'cu');

        // Admin panel styles (font, extra tweaks beyond the color scheme).
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

    public static function register_color_scheme(): void
    {
        wp_admin_css_color(
            'cu',
            'Cage Undefined',
            CU_URL . 'assets/css/color-scheme.css',
            ['#1a181d', '#242129', '#7542fe', '#198e7b']
        );
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
            CU_URL . 'assets/css/admin-theme.css',
            ['cu-google-fonts', 'wp-admin'],
            CU_VERSION
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
            CU_URL . 'assets/css/login-page.css',
            ['cu-google-fonts'],
            CU_VERSION
        );
    }

    /**
     * Replace the WordPress logo with the CU mark and add a hosting panel link.
     */
    public static function replace_admin_bar_logo(\WP_Admin_Bar $wp_admin_bar): void
    {
        // Replace the logo icon but keep the wp-logo node (preserves the dropdown).
        $wp_admin_bar->add_node([
            'id'    => 'wp-logo',
            'title' => '<span class="cu-admin-bar-logo"></span>',
        ]);

        // Add CU group at the top of the dropdown (before wp-logo-default).
        $wp_admin_bar->add_group([
            'id'     => 'wp-logo-cu',
            'parent' => 'wp-logo',
        ]);

        // Move the existing groups after ours by re-registering them.
        $wp_admin_bar->add_group([
            'id'     => 'wp-logo-default',
            'parent' => 'wp-logo',
        ]);
        $wp_admin_bar->add_group([
            'id'     => 'wp-logo-external',
            'parent' => 'wp-logo',
        ]);

        $wp_admin_bar->add_node([
            'id'     => 'cu-hosting-panel',
            'parent' => 'wp-logo-cu',
            'title'  => __('Hosting Panel', 'cage-undefined'),
            'href'   => get_option('panel269_url', '#'),
            'meta'   => ['target' => '_blank'],
        ]);

        $wp_admin_bar->add_node([
            'id'     => 'cu-website',
            'parent' => 'wp-logo-cu',
            'title'  => 'Cage Undefined',
            'href'   => 'https://cageundefined.org',
            'meta'   => ['target' => '_blank'],
        ]);
    }

    /**
     * Inline CSS for the admin bar logo icon.
     */
    public static function inline_logo_css(): void
    {
        $logo = CU_URL . 'assets/img/cu-mark.svg';

        echo '<style>
            #wpadminbar .cu-admin-bar-logo {
                display: inline-block !important;
                width: 32px !important;
                height: 32px !important;
                background-image: url("' . esc_url($logo) . '") !important;
                background-repeat: no-repeat !important;
                background-position: center !important;
                background-size: contain !important;
            }
            #wpadminbar .cu-admin-bar-logo::before { content: none !important; }

            /* CU dropdown group */
            #wpadminbar #wp-admin-bar-wp-logo-cu {
                border-bottom: none !important;
            }
            #wpadminbar #wp-admin-bar-wp-logo-cu li a {
                color: #fffdee !important;
                background: #7542fe !important;
            }
            #wpadminbar #wp-admin-bar-wp-logo-cu li a:hover {
                background: #198e7b !important;
                color: #fff !important;
            }
        </style>' . "\n";
    }

    public static function footer_text(): string
    {
        return 'Hosted by <a href="https://cageundefined.org" target="_blank" rel="noopener">Cage Undefined</a>';
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
