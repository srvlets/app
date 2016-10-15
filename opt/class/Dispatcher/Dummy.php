<?php
namespace Srvlets\Dispatcher;

use \Srvlets;

/**
 * @inheritDoc
 */
final class Dummy extends \Srvlets\Dispatcher
{
    /**
     * @inheritDoc
     */
    public function dispatch(Srvlets\Request $request): Srvlets\Response
    {
        return new Srvlets\Response('Hello world.');
    }
}
