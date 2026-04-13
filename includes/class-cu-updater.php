<?php

if (! defined('ABSPATH')) {
    exit;
}

class CU_Updater
{
    private const GITHUB_REPO = 'CageUndefined/cu-wp-plugin';
    private const CACHE_KEY = 'cu_github_update';
    private const CACHE_TTL = 6 * HOUR_IN_SECONDS;

    private string $plugin_file;
    private string $plugin_basename;

    public static function init(string $plugin_file): void
    {
        $instance = new self($plugin_file);

        add_filter('pre_set_site_transient_update_plugins', [$instance, 'check_for_update']);
        add_filter('plugins_api', [$instance, 'plugin_info'], 10, 3);
        add_filter('upgrader_source_selection', [$instance, 'fix_directory_name'], 10, 4);
        add_filter('auto_update_plugin', [$instance, 'enable_auto_update'], 10, 2);
    }

    private function __construct(string $plugin_file)
    {
        $this->plugin_file = $plugin_file;
        $this->plugin_basename = plugin_basename($plugin_file);
    }

    public function check_for_update(object $transient): object
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_latest_release();

        if (! $release) {
            return $transient;
        }

        $current_version = $transient->checked[$this->plugin_basename] ?? CU_VERSION;

        if (version_compare($release['version'], $current_version, '>')) {
            $transient->response[$this->plugin_basename] = (object) [
                'slug'        => dirname($this->plugin_basename),
                'plugin'      => $this->plugin_basename,
                'new_version' => $release['version'],
                'url'         => 'https://github.com/' . self::GITHUB_REPO,
                'package'     => $release['zip_url'],
            ];
        } else {
            $transient->no_update[$this->plugin_basename] = (object) [
                'slug'        => dirname($this->plugin_basename),
                'plugin'      => $this->plugin_basename,
                'new_version' => $release['version'],
                'url'         => 'https://github.com/' . self::GITHUB_REPO,
            ];
        }

        return $transient;
    }

    public function plugin_info($result, string $action, object $args)
    {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (($args->slug ?? '') !== dirname($this->plugin_basename)) {
            return $result;
        }

        $release = $this->get_latest_release();

        if (! $release) {
            return $result;
        }

        return (object) [
            'name'          => 'Cage Undefined',
            'slug'          => dirname($this->plugin_basename),
            'version'       => $release['version'],
            'author'        => '<a href="https://cageundefined.org">Cage Undefined</a>',
            'homepage'      => 'https://github.com/' . self::GITHUB_REPO,
            'download_link' => $release['zip_url'],
            'sections'      => [
                'description' => 'CU Panel269 integration for WordPress.',
                'changelog'   => nl2br(esc_html($release['body'])),
            ],
        ];
    }

    public function enable_auto_update(bool $update, object $item): bool
    {
        if (($item->plugin ?? '') === $this->plugin_basename) {
            return true;
        }

        return $update;
    }

    public function fix_directory_name(string $source, string $remote_source, $upgrader, $hook_extra): string
    {
        if (! isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_basename) {
            return $source;
        }

        $expected = trailingslashit($remote_source) . dirname($this->plugin_basename) . '/';

        if ($source === $expected) {
            return $source;
        }

        global $wp_filesystem;

        if ($wp_filesystem->move($source, $expected)) {
            return $expected;
        }

        return $source;
    }

    private function get_latest_release(): ?array
    {
        $cached = get_transient(self::CACHE_KEY);

        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_get(
            'https://api.github.com/repos/' . self::GITHUB_REPO . '/releases/latest',
            [
                'headers' => [
                    'Accept' => 'application/vnd.github.v3+json',
                ],
                'timeout' => 10,
            ]
        );

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['tag_name'])) {
            return null;
        }

        $zip_url = $body['zipball_url'];

        foreach ($body['assets'] ?? [] as $asset) {
            if (str_ends_with($asset['name'] ?? '', '.zip')) {
                $zip_url = $asset['browser_download_url'];
                break;
            }
        }

        $data = [
            'version' => ltrim($body['tag_name'], 'v'),
            'zip_url' => $zip_url,
            'body'    => $body['body'] ?? '',
        ];

        set_transient(self::CACHE_KEY, $data, self::CACHE_TTL);

        return $data;
    }
}
