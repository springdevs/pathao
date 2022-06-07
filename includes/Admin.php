<?php

/**
 * The admin class
 *
 * @package SpringDevs\Pathao\Admin
 */

namespace SpringDevs\Pathao;

/**
 * The admin class
 */
class Admin
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
        $this->dispatch_actions();
        new Admin\Menu();
        new Admin\Order();
        new Illuminate\Cron();
    }

    /**
     * Dispatch and bind actions.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function dispatch_actions()
    {
    }
}
