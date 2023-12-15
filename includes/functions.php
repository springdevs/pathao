<?php

/**
 * All our plugins custom functions.
 *
 * @since 1.0.0
 */

/**
 * Get filename extension.
 *
 * @param string $file_name File name.
 *
 * @return false|string
 * @since 1.0.0
 */
function sdevs_get_pathao_get_extension( $file_name ) {
	$n = strrpos( $file_name, '.' );

	return ( false === $n ) ? '' : substr( $file_name, $n + 1 );
}

function sdevs_pathao_base_url(): string {
	return get_option( 'pathao_sandbox_mode' ) ? 'https://courier-api-sandbox.pathao.com/' : 'https://api-hermes.pathao.com/';
}

function sdevs_get_pathao_data( string $endpoint ) {
	$base_url     = sdevs_pathao_base_url();
	$access_token = get_option( 'pathao_access_token' );

	if ( ! $access_token ) {
		return (object) array(
			'type'  => 'failed',
			'error' => 'Please generate access token to use pathao plugin !!',
		);
	}

	$res = wp_remote_get(
		$base_url . $endpoint,
		array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Accept'        => 'application/json',
			),
		)
	);

	$data     = json_decode( wp_remote_retrieve_body( $res ) );
	$res_code = wp_remote_retrieve_response_code( $res );

	if ( $res_code == 200 ) {
		return $data;
	}

	return (object) array(
		'type'     => 'failed',
		'res_code' => $res_code,
		'body'     => $data,
	);
}

function sdevs_send_pathao_data( string $endpoint, array $body ) {
	$base_url     = sdevs_pathao_base_url();
	$access_token = get_option( 'pathao_access_token' );

	if ( ! $access_token ) {
		return (object) array(
			'type'  => 'failed',
			'error' => 'Please generate access token to use pathao plugin !!',
		);
	}

	$res = wp_remote_post(
		$base_url . $endpoint,
		array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Accept'        => 'application/json',
			),
			'body'    => $body,
		)
	);

	$data     = json_decode( wp_remote_retrieve_body( $res ) );
	$res_code = wp_remote_retrieve_response_code( $res );

	if ( $res_code == 200 ) {
		return $data;
	}

	return (object) array(
		'type'     => 'failed',
		'res_code' => $res_code,
		'body'     => $data,
	);
}

/**
 * Check if pathao pro activated.
 *
 * @return bool
 */
function is_sdevs_pathao_pro_activated(): bool {
	return class_exists( 'Sdevs_Pathao_Pro' );
}

/**
 * Check if pathao shipping enabled.
 *
 * @return bool
 */
function is_pathao_shipping_enabled(): bool {
	$settings = get_option( 'woocommerce_pathao_settings' );

	return $settings && isset( $settings['enabled'] ) && 'yes' === $settings['enabled'];
}

function sdevs_pathao_store_id() {
	$settings = get_option( 'woocommerce_pathao_settings' );

	if ( $settings && isset( $settings['store'] ) ) {
		return $settings['store'];
	}

	return false;
}

function sdevs_pathao_settings( $key ) {
	$settings = get_option( 'woocommerce_pathao_settings' );

	return $settings && is_array( $settings ) ? $settings[ $key ] : false;
}
