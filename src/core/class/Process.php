<?php
namespace Srvlets;

use \RuntimeException;

/**
 * An external process.
 */
final class Process
{
    /**
     * @var resource
     */
    private $process;

    /**
     * @var array
     */
    private $pipes;

    /**
     * @param string $path
     *
     * @throws RuntimeException
     */
    public function __construct(string $path)
    {
        $this->process = proc_open($path, [

            ['pipe', 'r'],
            ['pipe', 'w'],
            ['pipe', 'w']

        ], $this->pipes);

        if (!is_resource($this->process)) {

            throw new RuntimeException;
        }
    }

    /**
     * @internal
     */
    public function __destruct()
    {
        foreach ($this->pipes as $index => $pipe) {

            if (!fclose($pipe)) {

                // Log something...
            }
        }

        if (!proc_terminate($this->process)) {

            // Log something...
        }
    }
}
