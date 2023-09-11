<?php

namespace SpringDevs\Pathao\Admin;

class Settings
{

	public function __construct()
	{
		add_action('woocommerce_settings_page_init', [$this, 'init_scripts']);
		add_action('woocommerce_sections_shipping', [$this, 'display_sandbox_notice']);
		add_action('woocommerce_after_settings_shipping', [$this, 'display_setup_settings'], 20);
		add_action('woocommerce_settings_shipping', [$this, 'pro_version_notice'], 20);
	}

	public function display_sandbox_notice()
	{
		if (empty($_GET['section']) || 'pathao' !== $_GET['section'] || !get_option('pathao_sandbox_mode')) {
			return;
		}
?>
		<div class="notice notice-warning">
			<p><?php _e('Sandbox mode is enabled.', 'sdevs_pathao'); ?></p>
		</div>
		<?php
	}

	public function pro_version_notice()
	{
		if (!is_sdevs_pathao_pro_activated() && isset($_GET['section']) && 'pathao' === $_GET['section']) :
		?>
			<p style="color:red;">Pathao pro version required to enable frontend shipping !</p>
			<!--		<div style="position: absolute; inset: 0; background-color: blue;"></div>-->
<?php
		endif;
	}

	public function init_scripts()
	{
		add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
	}

	/**
	 * Enqueue Style & scripts related to settings form.
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

	public function display_setup_settings()
	{
		if (!isset($_GET['section']) || 'pathao' !== $_GET['section']) {
			return;
		}

		include_once 'views/setup.php';
	}
}
