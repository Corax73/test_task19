<?php

namespace Controllers;

use Models\Bus;
use Repositories\BusStopsRepository;

class BusController extends AbstractController
{
    public string $apiName = 'buses';
    const COUNT_ARRIVALS = 3;

    public function __construct()
    {
        $this->model = new Bus();
        parent::__construct();
    }

    /**
     * @return string
     */
    public function find_bus(): string|bool
    {
        $time = time();
        if (
            isset($this->requestParams['from']) &&
            isset($this->requestParams['to']) &&
            $this->requestParams['from'] &&
            $this->requestParams['to']
        ) {
            $buses = $this->model->searchByStopsId(intval($this->requestParams['from']), intval($this->requestParams['to']));
            if ($buses) {
                $this->resp = BusStopsRepository::getFindBusResponse(
                    $buses,
                    $this->requestParams['from'],
                    $this->requestParams['to'],
                    self::COUNT_ARRIVALS,
                    $time
                );
                if (isset($this->resp['buses']) && $this->resp['buses']) {
                    $this->status = 200;
                }
            }
        }
        return $this->response($this->resp, $this->status);
    }

    /**
     * Returns all entries.
     * @return string
     */
    public function index() {
        header('Content-Type: text/html; charset=UTF-8');
        require_once '../api/swagger/index.php';
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
    public function update(): string
    {
        if (isset($this->requestParams['bus_id'])) {
            $newData = [];
            if (isset($this->requestParams['title'])) {
                $newData['title'] = $this->requestParams['title'];
            }
            if (isset($this->requestParams['bus_stops'])) {
                $newData['bus_stops'] = $this->requestParams['bus_stops'];
            }

            if ($newData) {
                if ($this->model->update($newData, $this->requestParams['bus_id'])) {
                    $this->resp = ['success' => true];
                    $this->status = 200;
                }
            }
        }
        return $this->response($this->resp, $this->status);
    }

    /**
     * Deletes the record.
     * @return string
     */
    public function delete() {}
}
