<?php
namespace Srvlets;

/**
 * Encapsulates response content and headers.
 */
final class Response
{
    /**
     * @var string
     */
    private $content;

    /**
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $length = mb_strlen($this->content);

        $response[] = "Status: 200 OK";
        $response[] = "\r\n";
        $response[] = "Content-Type: text/plain";
        $response[] = "\r\n";
        $response[] = "Content-Length: {$length}";
        $response[] = "\r\n";
        $response[] = "\r\n";
        $response[] = $this->content;

        return implode("", $response);
    }
}
