<?php

namespace SpringDevs\Pathao;


class Ajax
{
	public function __construct()
	{
		add_action('wp_ajax_setup_pathao', array($this, 'setup_pathao'));
		add_action('wp_ajax_get_city_zones', array($this, 'get_city_zones'));
		add_action('wp_ajax_get_zone_areas', array($this, 'get_zone_areas'));
		add_action('wp_ajax_send_order_to_pathao', array($this, 'send_order_to_pathao'));
	}

	public function setup_pathao()
	{
		$client_id = sanitize_text_field($_POST['client_id']);
		$client_secret = sanitize_text_field($_POST['client_secret']);
		$data = [
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'username' => sanitize_email($_POST['client_username']),
			'password' => sanitize_text_field($_POST['client_password']),
			'grant_type' => 'password'
		];

		update_option('pathao_sandbox_mode', (bool)$_POST['sandbox_mode']);
		$base_url = get_pathao_base_url();

		$res = wp_remote_post($base_url . 'aladdin/api/v1/issue-token', [
			'body' => $data,
		]);
		$res_code = wp_remote_retrieve_response_code($res);
		$data = wp_remote_retrieve_body($res);
		$data = wp_remote_retrieve_body($res);
		$data = json_decode($data);

		if ($res_code == 200) {
			update_option('pathao_client_id', $client_id);
			update_option('pathao_client_secret', $client_secret);
			update_option('pathao_access_token', $data->access_token);
			update_option('pathao_refresh_token', $data->refresh_token);
			wp_send_json_success($data);
		}

		wp_send_json_error($data);
		die();
	}

	public function get_city_zones()
	{
		$city = sanitize_text_field($_POST['city']);
		$zones = getData("aladdin/api/v1/cities/$city/zone-list");
		$zones = $zones->type === 'success' ? $zones->data->data : array();

		wp_send_json($zones);
	}

	public function get_zone_areas()
	{
		$zone = sanitize_text_field($_POST['zone']);
		$areas = getData("aladdin/api/v1/zones/$zone/area-list");
		$areas = $areas->type === 'success' ? $areas->data->data : array();

		wp_send_json($areas);
	}

	public function send_order_to_pathao()
	{
		if (wp_verify_nonce($_POST['nonce'], 'pathao_send_order')) {
			$order_id = sanitize_text_field($_POST['order_id']);
			$store = sanitize_text_field($_POST['store']);
			$city = sanitize_text_field($_POST['city']);
			$zone = sanitize_text_field($_POST['zone']);
			$area = sanitize_text_field($_POST['area']);
			$special_instruction = sanitize_text_field($_POST['special_instruction']);
			$delivery_type = sanitize_text_field($_POST['delivery_type']);
			$item_type = sanitize_text_field($_POST['item_type']);
			$amount = sanitize_text_field($_POST['amount']);
			$item_weight = sanitize_text_field($_POST['item_weight']);

			$res = $this->send_order($order_id, $store, $city, $zone, $area, $special_instruction, $delivery_type, $item_type, $amount, $item_weight);

			if ($res->type == 'error') {
				return wp_send_json(['success' => false, 'message' => $res->message, 'errors' => $res->errors]);
			}

			$res_data = $res->data;
			update_post_meta($order_id, '_pathao_consignment_id', $res_data->consignment_id);
			update_post_meta($order_id, '_pathao_delivery_fee', $res_data->delivery_fee);
			update_post_meta($order_id, '_pathao_order_status', $res_data->order_status);

			wp_send_json(['success' => true, 'message' => 'Order sent to Pathao successfull.']);
		}

		wp_send_json(['success' => false, 'message' => 'Invalid nonce']);
	}

	public function send_order($order_id, $store, $city, $zone, $area, $special_instruction, $delivery_type, $item_type, $amount, $item_weight)
	{
		$base_url = get_pathao_base_url();
		$access_token = get_option("pathao_access_token");

		if (!$access_token) {
			return wp_send_json(['success' => true, 'message' => 'Please generate access token to use pathao plugin !!']);
		}

		$order = wc_get_order($order_id);
		$recipient_name = $order->get_formatted_shipping_full_name() != " " ? $order->get_formatted_shipping_full_name() : $order->get_formatted_billing_full_name();
		$recipient_phone = $order->get_shipping_phone() !== '' ? $order->get_shipping_phone() : $order->get_billing_phone();
		$recipient_address = $order->get_formatted_shipping_address() !== '' ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address();

		$res = wp_remote_post($base_url . 'aladdin/api/v1/orders', [
			'headers' => [
				'Authorization' => 'Bearer ' . $access_token
			],
			'body' => [
				'store_id' => $store,
				'merchant_order_id' => $order_id,
				'recipient_name' => $recipient_name,
				'recipient_phone' => $recipient_phone,
				'recipient_address' => $recipient_address,
				'recipient_city' => $city,
				'recipient_zone' => $zone,
				'recipient_area' => $area,
				'delivery_type' => $delivery_type,
				'item_type' => $item_type,
				'special_instruction' => $special_instruction,
				'item_quantity' => $order->get_item_count(),
				'item_weight' => $item_weight,
				'amount_to_collect' => $amount
			]
		]);

		$body = wp_remote_retrieve_body($res);
		$data = json_decode($body);
		return $data;
	}
}
