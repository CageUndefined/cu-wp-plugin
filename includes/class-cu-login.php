<?php
/**
 * Magic login link functionality.
 *
 * Generates one-time-use, time-limited login tokens and handles
 * the login endpoint that consumes them.
 */
class CU_Login
{
    /** Token lifetime in seconds. */
    const TOKEN_EXPIRY = 300;

    /** Random bytes for token generation (produces 64-char hex string). */
    const TOKEN_LENGTH = 32;

    /** Query parameter used for the magic login URL. */
    const QUERY_VAR = 'cu-magic-login';

    public static function init(): void
    {
        add_action('init', [__CLASS__, 'handle_magic_login'], 1);
    }

    /**
     * Generate a magic login token for a user.
     */
    public static function create_token(int $user_id): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $hash = hash('sha256', $token);

        set_transient("cu_magic_login_{$hash}", $user_id, self::TOKEN_EXPIRY);

        return $token;
    }

    /**
     * Build the full magic login URL for a token.
     */
    public static function get_login_url(string $token): string
    {
        return add_query_arg(self::QUERY_VAR, $token, home_url('/'));
    }

    /**
     * Intercept requests with a magic login token and log the user in.
     */
    public static function handle_magic_login(): void
    {
        if (empty($_GET[self::QUERY_VAR])) {
            return;
        }

        $token = sanitize_text_field(wp_unslash($_GET[self::QUERY_VAR]));

        if (! preg_match('/^[a-f0-9]{64}$/', $token)) {
            wp_safe_redirect(wp_login_url());
            exit;
        }

        $hash = hash('sha256', $token);
        $transient_key = "cu_magic_login_{$hash}";

        $user_id = get_transient($transient_key);

        if (! $user_id) {
            wp_safe_redirect(wp_login_url());
            exit;
        }

        // One-time use: delete before login to prevent replay.
        delete_transient($transient_key);

        $user = get_user_by('id', $user_id);

        if (! $user) {
            wp_safe_redirect(wp_login_url());
            exit;
        }

        wp_set_auth_cookie($user_id, false);
        wp_set_current_user($user_id);

        /** This action is documented in wp-includes/user.php */
        do_action('wp_login', $user->user_login, $user);

        wp_safe_redirect(admin_url());
        exit;
    }
}
