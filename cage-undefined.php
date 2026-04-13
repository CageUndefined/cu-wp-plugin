<?php
/**
 * Plugin Name: Cage Undefined
 * Plugin URI: https://cageundefined.org
 * Description: CU Panel269 integration for WordPress.
 * Version: 1.0.0
 * Author: Cage Undefined
 * Author URI: https://cageundefined.org
 * License: GPL-2.0-or-later
 * Text Domain: cage-undefined
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) {
    exit;
}

define('CU_VERSION', '1.0.0');
define('CU_PATH', plugin_dir_path(__FILE__));
define('CU_URL', plugin_dir_url(__FILE__));

require_once CU_PATH . 'includes/class-cu-login.php';
require_once CU_PATH . 'includes/class-cu-admin-theme.php';
require_once CU_PATH . 'includes/class-cu-updater.php';

add_action('plugins_loaded', function () {
    CU_Login::init();
    CU_Admin_Theme::init();
});

CU_Updater::init(__FILE__);

// Register WP-CLI commands.
if (defined('WP_CLI') && WP_CLI) {
    require_once CU_PATH . 'includes/class-cu-login-command.php';
    WP_CLI::add_command('login', 'CU_Login_Command');
}
