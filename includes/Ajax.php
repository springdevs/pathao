<?php

namespace SpringDevs\Pathao;

use stdClass;

/**
 * The Ajax class.
 */
class Ajax {

	/**
	 * Initialize the class.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_setup_pathao', array( $this, 'setup_pathao' ) );
		add_action( 'wp_ajax_get_city_zones', array( $this, 'get_city_zones' ) );
		add_action( 'wp_ajax_get_zone_areas', array( $this, 'get_zone_areas' ) );
		add_action( 'wp_ajax_send_order_to_pathao', array( $this, 'send_order_to_pathao' ) );
	}

	/**
	 * Generate token & save it.
	 *
	 * @return void
	 */
	public function setup_pathao() {
		if ( ! isset( $_POST['client_id'], $_POST['client_secret'], $_POST['client_username'], $_POST['_wpnonce'], $_POST['client_password'], $_POST['sandbox_mode'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), '_pathao_setup_nonce' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$client_id     = sanitize_text_field( wp_unslash( $_POST['client_id'] ) );
		$client_secret = sanitize_text_field( wp_unslash( $_POST['client_secret'] ) );
		$data          = array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'username'      => sanitize_email( wp_unslash( $_POST['client_username'] ) ),
			'password'      => sanitize_text_field( wp_unslash( $_POST['client_password'] ) ),
			'grant_type'    => 'password',
		);

		update_option( 'pathao_sandbox_mode', 'true' === $_POST['sandbox_mode'] ? true : false );
		$base_url = sdevs_pathao_base_url();

		$res      = wp_remote_post(
			$base_url . 'aladdin/api/v1/issue-token',
			array(
				'body' => $data,
			)
		);
		$res_code = wp_remote_retrieve_response_code( $res );
		$data     = wp_remote_retrieve_body( $res );
		$data     = wp_remote_retrieve_body( $res );
		$data     = json_decode( $data );

		if ( 200 === $res_code ) {
			update_option( 'pathao_client_id', $client_id );
			update_option( 'pathao_client_secret', $client_secret );
			update_option( 'pathao_access_token', $data->access_token );
			update_option( 'pathao_refresh_token', $data->refresh_token );
			wp_send_json_success( $data );
		}

		wp_send_json_error( $data );
		die();
	}

	/**
	 * Get zones from pathao server.
	 *
	 * @return void
	 */
	public function get_city_zones() {
		if ( ! isset( $_POST['nonce'], $_POST['order_id'], $_POST['city'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pathao_send_order' ) ) {
			return;
		}

		$order_id = sanitize_text_field( wp_unslash( $_POST['order_id'] ) );
		$city     = sanitize_text_field( wp_unslash( $_POST['city'] ) );
		$zones    = sdevs_get_pathao_data( "aladdin/api/v1/cities/$city/zone-list" );
		$zones    = 'success' === $zones->type ? $zones->data->data : array();

		wp_send_json(
			array(
				'zones' => $zones,
				'value' => apply_filters( 'pathao_selected_order_zone_value', null, $order_id ),
			)
		);
	}

	/**
	 * Get areas from pathao server.
	 *
	 * @return void
	 */
	public function get_zone_areas() {
		if ( ! isset( $_POST['nonce'], $_POST['order_id'], $_POST['zone'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pathao_send_order' ) ) {
			return;
		}

		$order_id = sanitize_text_field( wp_unslash( $_POST['order_id'] ) );
		$zone     = sanitize_text_field( wp_unslash( $_POST['zone'] ) );
		$areas    = sdevs_get_pathao_data( "aladdin/api/v1/zones/$zone/area-list" );
		$areas    = 'success' === $areas->type ? $areas->data->data : array();

		wp_send_json(
			array(
				'areas' => $areas,
				'value' => apply_filters( 'pathao_selected_order_area_value', null, $order_id ),
			)
		);
	}

	/**
	 * Send order to Pathao.
	 */
	public function send_order_to_pathao() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pathao_send_order' ) && isset( $_POST['order_id'] ) && isset( $_POST['city'] ) && isset( $_POST['zone'] ) && isset( $_POST['area'] ) && isset( $_POST['special_instruction'] ) && isset( $_POST['delivery_type'] ) && isset( $_POST['item_type'] ) && isset( $_POST['amount'] ) && isset( $_POST['item_weight'] ) ) {
			$order_id            = sanitize_text_field( wp_unslash( $_POST['order_id'] ) );
			$store               = sdevs_pathao_store_id();
			$city                = sanitize_text_field( wp_unslash( $_POST['city'] ) );
			$zone                = sanitize_text_field( wp_unslash( $_POST['zone'] ) );
			$area                = sanitize_text_field( wp_unslash( $_POST['area'] ) );
			$special_instruction = trim( sanitize_text_field( wp_unslash( $_POST['special_instruction'] ) ) );
			$delivery_type       = sanitize_text_field( wp_unslash( $_POST['delivery_type'] ) );
			$item_type           = sanitize_text_field( wp_unslash( $_POST['item_type'] ) );
			$amount              = sanitize_text_field( wp_unslash( $_POST['amount'] ) );
			$item_weight         = sanitize_text_field( wp_unslash( $_POST['item_weight'] ) );

			$res = $this->send_order( $order_id, $store, $city, $zone, $area, $special_instruction, $delivery_type, $item_type, $amount, $item_weight );

			if ( 'error' === $res->type ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => $res->message,
						'errors'  => $res->errors,
					)
				);
			}

			$res_data = $res->data;
			$order    = wc_get_order( $order_id );
			$order->update_meta_data( '_pathao_consignment_id', $res_data->consignment_id );
			$order->update_meta_data( '_pathao_delivery_fee', $res_data->delivery_fee );
			$order->update_meta_data( '_pathao_order_status', $res_data->order_status );
			$order->save();

			do_action( 'pathao_order_created', $res_data );

			wp_send_json(
				array(
					'success' => true,
					'message' => 'Order sent to Pathao successfull.',
				)
			);
		}

		wp_send_json(
			array(
				'success' => false,
				'message' => 'Invalid nonce',
			)
		);
	}

	/**
	 * Send order to pathao server.
	 *
	 * @param int        $order_id Order Id.
	 * @param int        $store Pathao Store Id.
	 * @param int        $city City Id.
	 * @param int        $zone Zone Id.
	 * @param string|int $area Area Id.
	 * @param string     $special_instruction instructions.
	 * @param string     $delivery_type Normal or On-Demand.
	 * @param string     $item_type document or parcel.
	 * @param float      $amount amount.
	 * @param float      $item_weight total order weight.
	 *
	 * @return mixed
	 */
	public function send_order( $order_id, $store, $city, $zone, $area, $special_instruction, $delivery_type, $item_type, $amount, $item_weight ) {
		$base_url     = sdevs_pathao_base_url();
		$access_token = get_option( 'pathao_access_token' );

		if ( ! $access_token ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => 'Please generate access token to use pathao plugin !!',
				)
			);
		}

		$order             = wc_get_order( $order_id );
		$recipient_name    = $order->get_formatted_shipping_full_name() !== ' ' ? $order->get_formatted_shipping_full_name() : $order->get_formatted_billing_full_name();
		$recipient_phone   = $order->get_shipping_phone() !== '' ? $order->get_shipping_phone() : $order->get_billing_phone();
		$recipient_address = $order->get_formatted_shipping_address() !== '' ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address();

		$body = array(
			'store_id'            => $store,
			'merchant_order_id'   => $order_id,
			'recipient_name'      => $recipient_name,
			'recipient_phone'     => $recipient_phone,
			'recipient_address'   => $recipient_address,
			'recipient_city'      => $city,
			'recipient_zone'      => $zone,
			'delivery_type'       => $delivery_type,
			'item_type'           => $item_type,
			'special_instruction' => $special_instruction,
			'item_quantity'       => $order->get_item_count(),
			'item_weight'         => $item_weight,
			'amount_to_collect'   => $amount,
		);

		if ( '' !== $area ) {
			$body['recipient_area'] = $area;
		}

		$res = wp_remote_post(
			$base_url . 'aladdin/api/v1/orders',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
				'body'    => $body,
			)
		);

		$body        = wp_remote_retrieve_body( $res );
		$status_code = wp_remote_retrieve_response_code( $res );
		$data        = json_decode( $body );

		if ( 401 === $status_code ) {
			$err_res          = new stdClass();
			$err_res->type    = 'error';
			$err_res->message = __( 'Please generate your token !!', 'sdevs_pathao' );
			$err_res->errors  = array(
				'auth' => array( __( 'Please generate your token !!', 'sdevs_pathao' ) ),
			);
			return $err_res;
		}

		return $data;
	}
}
