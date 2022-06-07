<?php

/**
 * Admin Pages Handler
 * Class Menu
 *
 * @package SpringDevs\Pathao\Admin
 */

namespace SpringDevs\Pathao\Admin;

/**
 * Class Menu
 */
class Menu
{
	/**
	 * Menu constructor.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function __construct()
	{
		add_action('admin_menu', [$this, 'admin_menu']);
		add_action('admin_init', array($this, 'register_settings'));
	}

	/**
	 * Register our menu page
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function admin_menu()
	{
		add_menu_page(__('Pathao', 'sdevs_pathao'), __('Pathao', 'sdevs_pathao'), 'manage_options', 'pathao-setup', array($this, 'setup_page'), 'dashicons-products', 50);
		$hook = add_submenu_page('pathao-setup', __('Setup', 'sdevs_pathao'), __('Setup', 'sdevs_pathao'), 'manage_options', 'pathao-setup', array($this, 'setup_page'));
		// add_submenu_page('pathao-setup', __('Logs', 'sdevs_pathao'), __('Logs', 'sdevs_pathao'), 'manage_options', 'pathao-logs', array($this, 'logs_page'));

		add_action('load-' . $hook, array($this, 'init_hooks'));
	}

	/**
	 * Initialize our hooks for the admin page
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function init_hooks()
	{
		add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
	}

	/**
	 * Load scripts and styles for the app
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function enqueue_scripts()
	{
		wp_localize_script('pathao_admin_script', 'pathao_admin_obj', [
			'ajax_url' => admin_url('admin-ajax.php')
		]);
		wp_enqueue_style('pathao_toast_styles');
		wp_enqueue_script('pathao_toast_script');
		wp_enqueue_script('pathao_admin_script');
	}

	/**
	 * Handles the setup page
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function setup_page()
	{
		include 'views/setup.php';
	}

	/**
	 * Handles the logs page
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function logs_page()
	{
		include 'views/logs.php';
	}

	/**
	 * register settings options
	 **/
	public function register_settings()
	{
		register_setting('pathao_settings', 'pathao_access_token');
		register_setting('pathao_settings', 'pathao_refresh_token');
		do_action('pathao_register_settings', 'pathao_settings');
	}
}
