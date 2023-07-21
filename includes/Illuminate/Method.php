<?php

/**
 * Class Method
 *
 * @package SpringDevs\Pathao\Illuminate\Method
 */

namespace SpringDevs\Pathao\Illuminate;

/**
 * Class Method
 */
class Method {
	public function __construct() {
		add_filter( 'woocommerce_shipping_methods', [ $this, 'add_your_shipping_method' ] );
	}

	public function add_your_shipping_method( $methods ) {
		$methods['pathao'] = 'WC_Pathao_Method';

		return $methods;
	}
}
