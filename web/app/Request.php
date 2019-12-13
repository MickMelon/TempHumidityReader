<?php
namespace App;

class Request {
    /**
     * The request method. i.e. POST or GET.
     *
     * @var string
     */
    private $method;

    /**
     * The parameters associated with the request. (taken from POST and GET)
     *
     * @var array
     */
    private $params;

    /**
     * Initializes a new instance of the Request class.
     *
     * @param string $method The request method. i.e. POST or GET.
     * @param string $path The path in the URI.
     * @param array $params The parameters associated with the request.
     */
    public function __construct(string $method, array $params = array()) {
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * Gets the request method.
     *
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Gets the request parameters.
     *
     * @return array
     */
    public function getParams() {
        return $this->params;
    }
}