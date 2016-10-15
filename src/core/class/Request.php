<?php
namespace Srvlets;

use \DomainException;

/**
 * Parses and encapsulates request
 * content and SCGI headers.
 */
final class Request
{
    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $content;

    /**
     * @param string $data
     *
     * @throws DomainException
     */
    public function __construct(string $data)
    {
        $headers = [];

        // Grab the start of the netstring.

        if (false === $start = strpos($data, ':')) {

            throw new DomainException;
        }

        // Grab the length of the netstring.

        $length = substr($data, 0, $start);

        // Grab the netstring.

        if (false === $netstring = substr($data, $start + 1, $length)) {

            throw new DomainException;
        }

        // Parse the netstring.

        preg_match_all('/(.*?)\x00(.*?)\x00/s', $netstring, $matches);

        foreach ($matches[1] as $index => $name) {

            $headers[$name] = $matches[2][$index];
        }

        // Remove the netstring.

        $content = substr($data, $start + $length + 2);

        // Check if request body is complete.

        $expected = (int) $headers['CONTENT_LENGTH'];
        $received = (int) mb_strlen($content);

        if ($received < $expected) {

            throw new DomainException;
        }

        // Request complete

        $this->headers = $headers;
        $this->content = $content;
    }
}
