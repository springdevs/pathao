<?php

function sdevs_pathao_shipping_method_init()
{
	if (!class_exists('WC_Pathao_Method')) {
		class WC_Pathao_Method extends WC_Shipping_Method
		{

			/**
			 * Constructor for your shipping class
			 *
			 * @access public
			 * @return void
			 */
			public function __construct()
			{
				$this->id                 = 'pathao'; // Id for Pathao. Should be unique.
				$this->method_title       = __('Pathao', 'sdevs_pathao');  // Title shown in admin
				$this->method_description = __('Implement Pathao within WooCommerce fully effective way.', 'sdevs_pathao'); // Description shown in admin

				$this->availability = 'including';
				$this->countries    = ['BD'];

				$this->enabled = is_sdevs_pathao_pro_activated() ? $this->get_option('enabled') : 'no';
				$this->title   = $this->get_option('title');
				$this->init();
			}

			/**
			 * Init your settings
			 *
			 * @access public
			 * @return void
			 */
			function init()
			{
				// Load the settings API
				$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
				$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

				// Save settings in admin if you have any defined
				add_action('woocommerce_update_options_shipping_' . $this->id, array(
					$this,
					'process_admin_options'
				));
			}

			public function init_form_fields()
			{
				$stores          = sdevs_get_pathao_data("aladdin/api/v1/stores");
				$stores          = $stores && $stores->type === 'success' ? $stores->data->data : array();
				$dropdown_stores = [];
				foreach ($stores as $store) {
					$dropdown_stores[$store->store_id] = $store->store_name;
				}
				$this->form_fields = array(
					'enabled'                 => array(
						'title'       => __('Enable', 'sdevs_pathao'),
						'type'        => 'checkbox',
						'description' => __('Enable this shipping.', 'sdevs_pathao'),
						'default'     => is_sdevs_pathao_pro_activated() ? 'yes' : 'no',
						'disabled'    => !is_sdevs_pathao_pro_activated()
					),
					'title'                   => array(
						'title'       => __('Title', 'sdevs_pathao'),
						'type'        => 'text',
						'description' => __('Title to be display on site', 'sdevs_pathao'),
						'default'     => __('Pathao', 'sdevs_pathao'),
						'disabled'    => !is_sdevs_pathao_pro_activated(),
						'required'    => true
					),
					'store'                   => array(
						'title'    => __('Store', 'sdevs_pathao'),
						'type'     => 'select',
						'options'  => $dropdown_stores,
						'disabled' => !is_sdevs_pathao_pro_activated()
					),
					'replace_checkout_fields' => array(
						'title'    => __('Replace Checkout Fields', 'sdevs_pathao'),
						'type'     => 'checkbox',
						'class'    => array('input-checkbox'),
						'label'    => sprintf(__('Display %sCity, Zone, Area%s fields & Hide default %sTown / City, District, Postcode / ZIP%s fields in Checkout.', 'sdevs_pathao_pro'), '<b>', '</b>', '<b>', '</b>'),
						'default'  => 'yes',
						'disabled' => !is_sdevs_pathao_pro_activated()
					),
					'delivery_type'           => array(
						'title'    => __('Delivery Type', 'sdevs_pathao'),
						'type'     => 'select',
						'options'  => [
							48 => __('Normal', 'sdevs_pathao'),
							12 => __('On Demand', 'sdevs_pathao'),
						],
						'default'  => 48,
						'disabled' => !is_sdevs_pathao_pro_activated()
					),
					'default_weight'          => array(
						'title'             => __('Default Item Weight (KG)', 'sdevs_pathao'),
						'type'              => 'number',
						'custom_attributes' => array(
							'steps'     => 'any',
							'min'       => '0.5',
							'max'       => '10.0',
							'required'  => "required"
						),
						'description'       => __('This value will be replaced when you set weight on individual product ! Minimum 0.5 KG to Maximum 10 KG', 'sdevs_pathao'),
						'default'           => __('0.5', 'sdevs_pathao'),
						'disabled'          => !is_sdevs_pathao_pro_activated()
					),
				);
			}

			/**
			 * calculate_shipping function.
			 *
			 * @access public
			 *
			 * @param array $package
			 *
			 * @return void
			 */
			public function calculate_shipping($package = array())
			{
				do_action('pathao_calculate_shipping', $package, $this);
			}
		}
	}
}

add_action('woocommerce_shipping_init', 'sdevs_pathao_shipping_method_init');
