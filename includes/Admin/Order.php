<?php

namespace SpringDevs\Pathao\Admin;

class Order
{
	public function __construct()
	{
		add_action('add_meta_boxes', array($this, 'create_meta_boxes'));
	}

	public function create_meta_boxes()
	{
		add_meta_box(
			'pathao_order_wc',
			__('Pathao Shipping', 'sdevs_pathao'),
			array($this, 'pathao_shipping'),
			'shop_order',
			'side',
			'default'
		);
	}

	public function pathao_shipping()
	{
		if (get_post_meta(get_the_ID(), '_pathao_consignment_id', true)) {
			$this->display_pathao_details();
		} else {
			$this->pathao_shipping_form();
		}
	}

	public function display_pathao_details()
	{
		$consignment_id = get_post_meta(get_the_ID(), '_pathao_consignment_id', true);
		$delivery_fee = get_post_meta(get_the_ID(), '_pathao_delivery_fee', true);
		$order_status = get_post_meta(get_the_ID(), '_pathao_order_status', true);
		include 'views/pathao-shipping-details.php';
	}

	public function pathao_shipping_form()
	{
		$order_id = get_the_ID();
		wp_localize_script('pathao_admin_script', 'pathao_admin_obj', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'order_id' => $order_id,
		]);
		wp_enqueue_style('pathao_toast_styles');
		wp_enqueue_script('pathao_toast_script');
		wp_enqueue_script('pathao_admin_script');

		$cities = sdevs_get_pathao_data("aladdin/api/v1/countries/1/city-list");
		$cities = $cities && $cities->type === 'success' ? $cities->data->data : array();

		$order = wc_get_order($order_id);
		if ($order->get_payment_method() === 'cod') {
			$amount = 0;
		} else {
			$amount = $order->get_total();
		}

		$total_weight = 0.5;
		foreach ($order->get_items() as $order_item) {
			$product = $order_item->get_product();
			if (!$product->is_virtual()) {
				$total_weight += floatval(intval($product->get_weight()) * $order_item['quantity']);
			}
		}

		include 'views/pathao-shipping.php';
	}
}
