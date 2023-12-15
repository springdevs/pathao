<?php

namespace SpringDevs\Pathao\Admin;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;


class Order {

	/**
	 * The class contructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'create_meta_boxes' ) );
		add_action( 'pathao_order_created', array( $this, 'store_log_after_creation' ) );
	}

	/**
	 * Store log after pathao order created.
	 *
	 * @param mixed $res Response.
	 */
	public function store_log_after_creation( $res ) {
		global $wpdb;
		$log_table = $wpdb->prefix . 'pathao_logs';
		$wpdb->insert(
			$log_table,
			array(
				'order_id'          => (int) $res->merchant_order_id,
				'consignment_id'    => $res->consignment_id,
				'order_status'      => $res->order_status,
				'order_status_slug' => $res->order_status,
				'reason'            => __( 'Pathao Order created & it\'s pending.', 'sdevs_pathao' ),
				'updated_at'        => current_time( 'mysql' ),
			)
		);
	}

	public function create_meta_boxes() {
		$screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';

		add_meta_box(
			'pathao_order_wc',
			__( 'Pathao Shipping', 'sdevs_pathao' ),
			array( $this, 'pathao_shipping' ),
			$screen,
			'side',
			'default'
		);
	}

	public function pathao_shipping() {
		$consignment_id = get_post_meta( get_the_ID(), '_pathao_consignment_id', true );
		$status         = get_post_meta( get_the_ID(), '_pathao_order_status', true );

		if ( $consignment_id && ! in_array( $status, array( 'Pickup_Failed', 'Pickup_Cancelled', 'Delivery_Failed' ), true ) ) {
			$this->display_pathao_details();
		} elseif ( $consignment_id && in_array( $status, array( 'Pickup_Failed', 'Pickup_Cancelled', 'Delivery_Failed' ), true ) ) {
			$this->display_pathao_details();
			$this->pathao_shipping_form();
		} else {
			$this->pathao_shipping_form();
		}
	}

	public function display_pathao_details() {
		$consignment_id = get_post_meta( get_the_ID(), '_pathao_consignment_id', true );
		$delivery_fee   = get_post_meta( get_the_ID(), '_pathao_delivery_fee', true );
		$order_status   = get_post_meta( get_the_ID(), '_pathao_order_status', true );
		include 'views/pathao-shipping-details.php';
	}

	/**
	 * Display shipping form.
	 */
	public function pathao_shipping_form() {
		$order_id = get_the_ID();
		wp_localize_script(
			'pathao_admin_script',
			'pathao_admin_obj',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'order_id' => $order_id,
			)
		);
		wp_enqueue_style( 'pathao_toast_styles' );
		wp_enqueue_script( 'pathao_toast_script' );
		wp_enqueue_script( 'pathao_admin_script' );

		$cities = sdevs_get_pathao_data( 'aladdin/api/v1/countries/1/city-list' );
		$cities = $cities && 'success' === $cities->type ? $cities->data->data : array();

		$order = wc_get_order( $order_id );
		if ( $order->get_payment_method() === 'cod' ) {
			$amount = 0;
		} else {
			$amount = $order->get_total();
		}

		$total_weight = 0;
		foreach ( $order->get_items() as $order_item ) {
			$product = $order_item->get_product();
			if ( ! $product->is_virtual() ) {
				$total_weight += empty( $product->get_weight() ) ? floatval( sdevs_pathao_settings( 'default_weight' ) ) : floatval( intval( $product->get_weight() ) * $order_item['quantity'] );
			}
		}
		$status = get_post_meta( get_the_ID(), '_pathao_order_status', true );

		include 'views/pathao-shipping.php';
	}
}
