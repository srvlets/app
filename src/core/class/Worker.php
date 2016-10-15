<?php
namespace Srvlets;

/**
 * A worker that dispatches requests
 * to their corresponding servlets.
 */
final class Worker extends \Worker
{
    /**
     * @var Dispatcher
     */
    private static $dispatcher;

    /**
     * @var Bootstrapper
     */
    private $bootstrapper;

    /**
     * @param Bootstrapper $bootstrapper
     */
    public function __construct(Bootstrapper $bootstrapper)
    {
        $this->bootstrapper = $bootstrapper;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        require '/opt/vendor/autoload.php';

        self::$dispatcher = $this->bootstrapper::bootstrap();
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function dispatch(Request $request): Response
    {
        return self::$dispatcher->dispatch($request);
    }

    /**
     * @inheritdoc
     */
    public function start(int $options = null): bool
    {
        return parent::start(PTHREADS_INHERIT_NONE);
    }
}
