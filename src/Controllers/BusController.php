<?php

namespace Controllers;

use Models\Bus;
use Models\Stop;
use Services\TimeService;

class BusController extends AbstractController
{
    public string $apiName = 'find-bus';
    const COUNT_ARRIVALS = 3;

    public function __construct()
    {
        $this->model = new Bus();
        parent::__construct();
    }

    /**
     * @return string
     */
    public function find_bus()
    {
        $time = time();
        if (
            isset($this->requestParams['from']) &&
            isset($this->requestParams['to']) &&
            $this->requestParams['from'] &&
            $this->requestParams['to']
        ) {
            $buses = $this->model->searchByStopsId([intval($this->requestParams['from']), intval($this->requestParams['to'])]);
        }
        if ($buses) {
            $this->resp = [
                'from' => $this->requestParams['from'],
                'to' => $this->requestParams['to'],
                'buses' => []
            ];
            foreach ($buses as $bus) {
                $stops = collect(json_decode($bus['bus_stops'], true)['stops']);
                $currentStop = $stops->where('id', $this->requestParams['from']);
                $currentStopArrival = ['Data not found'];
                if ($currentStop->isNotEmpty()) {
                    $currentStopArrival = TimeService::getEarliestTime($currentStop->first()['arrival'], self::COUNT_ARRIVALS, $time);
                }

                $stop = new Stop();
                $lastStop = $stop->find($stops->last()['id']);
                $lastStopTitle = $lastStop && isset($lastStop[0]['title']) ? $lastStop[0]['title'] : $stops->last()['id'];
                $this->resp['buses'][] = [
                    'route' => 'Автобус ' . $bus['title'] . ' в сторону ост. ' . $lastStopTitle,
                    'next_arrivals' => $currentStopArrival
                ];
            }
            $this->status = 200;
        }
        return $this->response($this->resp, $this->status);
    }

    /**
     * Returns all entries.
     * @return string
     */
    public function index() {}

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
                if($this->model->update($newData, $this->requestParams['bus_id'])) {
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
