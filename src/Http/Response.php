<?php

namespace Shorter\Backend\Http;

class Response
{
    /**
     * @param int $code
     * @param mixed $data
     * @param array $headers
     */
    public function __construct(private readonly int $code, private readonly mixed $data, private array $headers = [])
    {
    }

    /**
     * @param $index
     * @param $value
     * @return void
     */
    public function setHeader($index, $value): void
    {
        $this->headers[$index] = $value;
    }

    /**
     * @param $index
     * @return void
     */
    public function removeHeader($index): void
    {
        unset($this->headers[$index]);
    }

    /**
     * @return void
     */
    public function dispatch(): void
    {

        foreach ($this->headers as $header => $value) {

            header("$header: $value");

        }

        http_response_code($this->code);
        exit($this->data);

    }

    /**
     * @param int $code
     * @param array $array
     * @return self
     */
    public static function json(int $code, array $array): self
    {

        $arrayJsonFormatted = json_encode($array, 1);
        $response = new self($code, $arrayJsonFormatted);
        $response->setHeader("Content-Type", "application/json");

        return $response;

    }

    public static function html(int $code, string $html): self
    {

        $response = new self($code, $html);
        $response->setHeader("Content-Type", "text/html");

        return $response;
    }

}