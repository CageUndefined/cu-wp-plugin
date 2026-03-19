<?php
/**
 * WP-CLI commands for magic login links.
 *
 * Provides `wp login create` and `wp login install` for backward
 * compatibility with the aaemnnosttv/wp-cli-login-command interface
 * used by panel269's WordPressService.
 */
class CU_Login_Command
{
    /**
     * Creates a magic login URL for a WordPress user.
     *
     * ## OPTIONS
     *
     * <user-locator>
     * : A user login, email address, or numeric ID.
     *
     * [--url-only]
     * : Output only the URL with no extra text.
     *
     * ## EXAMPLES
     *
     *     wp login create admin --url-only
     *     wp login create editor@example.com
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function create($args, $assoc_args)
    {
        $locator = $args[0];

        $user = get_user_by('login', $locator)
            ?: get_user_by('email', $locator)
            ?: (is_numeric($locator) ? get_user_by('id', (int) $locator) : null);

        if (! $user) {
            WP_CLI::error("No user found for: {$locator}");
        }

        $token = CU_Login::create_token($user->ID);
        $url = CU_Login::get_login_url($token);

        if (WP_CLI\Utils\get_flag_value($assoc_args, 'url-only')) {
            WP_CLI::line($url);
        } else {
            WP_CLI::success("Magic login URL for {$user->user_login}:");
            WP_CLI::line($url);
        }
    }

    /**
     * Installs the magic login companion plugin.
     *
     * This is a no-op — the CU Branding plugin provides magic login
     * natively. Kept for backward compatibility with panel269.
     *
     * ## OPTIONS
     *
     * [--activate]
     * : Ignored (plugin is already active).
     *
     * [--yes]
     * : Ignored.
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function install($args, $assoc_args)
    {
        WP_CLI::success('Magic login is provided by the CU Branding plugin (already active).');
    }
}
