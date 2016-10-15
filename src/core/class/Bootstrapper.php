<?php
namespace Srvlets;

/**
 * Bootstraps a dispatcher.
 */
abstract class Bootstrapper
{
    /**
     * @return Dispatcher
     */
    abstract public static function bootstrap(): Dispatcher;
}
