<?php

/**
 * API Class
 * 
 * @package SpringDevs\Pathao\API
 */

namespace SpringDevs\Pathao;

/**
 * API Class
 */
class API
{

    /**
     * Initialize the class.
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_api']);
    }

    /**
     * Register the API.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function register_api()
    {
        register_rest_route('api/v1', 'pathao-status-endpoint/', array(
            'methods'  => 'POST',
            'callback' => [$this, 'pathao_status_changed'],
            'permission_callback' => '__return_true'
        ));
    }

    public function pathao_status_changed(\WP_REST_Request $request)
    {
        $signature = $request->get_header('X-PATHAO-Signature');
        if (!get_option('pathao_client_secret') || !hash_equals(
            'sha1=' . hash_hmac('sha1', $signature, get_option('pathao_client_secret')),
            $_SERVER['HTTP_X_PATHAO_SIGNATURE']
        )) {
            return ["success" => false, "message" => "Invalid signature"];
        }

        $consignment_id = sanitize_text_field($request->get_param('consignment_id'));
        $order_id = sanitize_text_field($request->get_param('merchant_order_id'));
        $status = sanitize_text_field($request->get_param('Order_status'));

        $order_consignment_id = get_post_meta($order_id, '_pathao_consignment_id', true);

        if ($consignment_id != $order_consignment_id) {
            return new \WP_Error('invalid_consignment_id', 'Invalid consignment id.', array('status' => 400));
        }

        if ($status == 'Delivered') {
            $order = wc_get_order($order_id);
            $order->update_status('completed');
        }

        if ($status == 'Returned') {
            $order = wc_get_order($order_id);
            $order->update_status('on-hold');
        }

        update_post_meta($order_id, '_pathao_order_status', $status);
        return ["success" => true];
    }
}
