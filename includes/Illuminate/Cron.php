<?php

namespace SpringDevs\Pathao\Illuminate;

/**
 * Class Cron
 *
 * @package SpringDevs\Pathao\Illuminate
 */
class Cron {


	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'pathao_refresh_token_cron', array( $this, 'update_token' ) );
	}

	public function update_token() {
		$client_id     = get_option( 'pathao_client_id' );
		$client_secret = get_option( 'pathao_client_secret' );
		$refresh_token = get_option( 'pathao_refresh_token' );

		if ( ! $client_id || ! $client_secret || ! $refresh_token ) {
			return;
		}

		$base_url = sdevs_pathao_base_url();
		$data     = array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'refresh_token' => $refresh_token,
			'grant_type'    => 'refresh_token',
		);

		$res      = wp_remote_post(
			$base_url . 'aladdin/api/v1/issue-token',
			array(
				'body' => $data,
			)
		);
		$res_code = wp_remote_retrieve_response_code( $res );

		if ( $res_code == 200 ) {
			$data = wp_remote_retrieve_body( $res );
			$data = json_decode( $data );
			update_option( 'pathao_access_token', $data->access_token );
			update_option( 'pathao_refresh_token', $data->refresh_token );
		}
	}
}
