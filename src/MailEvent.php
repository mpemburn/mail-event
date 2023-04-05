<?php

namespace MailEvent;

class MailEvent
{
    private static $instance = null;

    private function __construct()
    {
        $this->addActions();
    }

    public static function boot()
    {
        if (!self::$instance) {
            self::$instance = new MailEvent();
        }

        return self::$instance;
    }

    public function addActions()
    {
    }
}
