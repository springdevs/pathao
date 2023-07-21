<?php

namespace SpringDevs\Pathao;

use SpringDevs\Pathao\Illuminate\Cron;
use SpringDevs\Pathao\Illuminate\Method;

class Illuminate {

	public function __construct() {
		$this->dispatch_actions();
		new Cron();
		new Method();
	}

	/**
	 * Dispatch and bind actions.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function dispatch_actions() {
		include_once __DIR__ . '/Illuminate/class-pathao-method.php';
	}

}
