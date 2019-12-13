<?php
namespace App;

class Json
{
    /**
     * The Json data.
     *
     * @var json
     */
    private $data;

    /**
     * Initializes a new instance of the Json class.
     *
     * @param json $data the Json data.
     */
    public function __construct($data, $encoded = false) {
        if (!$encoded) {
            $data = json_encode($data);
        }

        $this->data = $data;
    }

    /**
     * Prints the Json data.
     *
     * @return void
     */
    public function show() {
        header('Content-Type: application/json');
        echo $this->data;
    }
}