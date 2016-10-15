<?php
namespace Srvlets\Bootstrapper;

use \Srvlets;

/**
 * @inheritDoc
 */
final class Main extends \Srvlets\Bootstrapper
{
    /**
     * @inheritDoc
     */
    public static function bootstrap(): Srvlets\Dispatcher
    {
        return new Srvlets\Dispatcher\Dummy;
    }
}
