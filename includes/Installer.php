<?php

/**
 * The Installer class.
 * Install all dependency from here while activating the plugin.
 *
 * @package SpringDevs\Pathao\Installer
 */

namespace SpringDevs\Pathao;

/**
 * Class Installer
 * @package SpringDevs\Pathao
 */
class Installer
{

    /**
     * Run the installer.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function run()
    {
        $this->add_version();
        $this->create_tables();
    }

    /**
     * Add time and version on DB.
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function add_version()
    {
        $installed = get_option('pathao_installed');

        if (!$installed) {
            update_option('pathao_installed', time());
        }

        update_option('pathao_version', SDEVS_PATHAO_VERSION);

        if (!wp_next_scheduled('pathao_refresh_token_cron')) {
            wp_schedule_event(time(), 'twicedaily', 'pathao_refresh_token_cron');
        }
    }

    /**
     * Create necessary database tables.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function create_tables()
    {
        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
    }
}
