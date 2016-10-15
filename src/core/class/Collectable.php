<?php
namespace Srvlets;

/**
 * Transports a request and its corresponding output
 * buffer from an IO thread to a worker thread.
 */
final class Collectable extends \Threaded
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Output
     */
    private $output;

    /**
     * @param Output  $output
     * @param Request $request
     */
    public function __construct(Request $request, Output $output)
    {
        $this->request = $request;
        $this->output  = $output;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->output->buffer($this->worker->dispatch($this->request));
        $this->setGarbage();
    }
}
