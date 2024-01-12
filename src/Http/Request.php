<?php


namespace Shorter\Backend\Http;

class Request
{

    private static self $instance;

    public static function getInstance(): self
    {

        if (!isset(self::$instance)) self::$instance = new self();
        return self::$instance;

    }

    private function __construct(private array $headers = [])
    {

        $this->headers = getallheaders();

    }

    public function getMethod(): string
    {

        return $_SERVER["REQUEST_METHOD"];

    }

    public function getQueryString(): string
    {

        return $_SERVER["QUERY_STRING"];

    }

    public function getURI(): string
    {

        return $_SERVER["REQUEST_URI"];

    }

    public function getUriWithoutQueryString(): string
    {

        return str_replace("?" . $this->getQueryString(), "", $this->getURI());

    }

    public function getClientIp(): string
    {

        return $_SERVER["REMOTE_ADDR"];

    }

    public function getHeaders(): array
    {

        return $this->headers;

    }

    public function getHeaderLine(string $headerName): ?string
    {
        return $this->headers[$headerName];
    }

    public function getPost(string $key): mixed
    {

        return $_POST[$key];

    }

}