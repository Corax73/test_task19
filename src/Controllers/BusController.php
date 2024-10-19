<?php

namespace Controllers;

use Models\Bus;

class BusController extends AbstractController
{
    public $apiName = 'find-bus';

    /**
     * Returns all entries.
     * @return string
     */
    public function index()
    {
        $buses = new Bus();
        $buses = $buses->all();
        $resp = ['Data not found'];
        $status = 404;
        if ($buses) {
            $resp = $buses;
            $status = 200;
        }
        return $this->response($resp, $status);
    }

    /**
     * Returns the first entry.
     * @return string
     */
    public function show() {}

    /**
     * Creates a record.
     * @return string
     */
    public function store() {}

    /**
     * Updates a record.
     * @return string
     */
    public function update() {}

    /**
     * Deletes the record.
     * @return string
     */
    public function delete() {}
}
