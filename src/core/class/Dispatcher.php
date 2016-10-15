<?php
namespace Srvlets;

/**
 * Dispatches requests to their corresponding servlets.
 */
abstract class Dispatcher
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    abstract public function dispatch(Request $request): Response;
}
