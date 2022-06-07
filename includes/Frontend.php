<?php

/**
 * Frontend handler class
 * 
 * @package SpringDevs\Pathao\Frontend
 */

namespace SpringDevs\Pathao;

/**
 * Frontend handler class
 */
class Frontend
{

    /**
     * Frontend constructor.
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function __construct()
    {
        new Illuminate\Cron();
    }
}
