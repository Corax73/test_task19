<?php

namespace Controllers;

use Models\Bus;
use Services\TimeService;

class BusController extends AbstractController
{
    public $apiName = 'find-bus';
    const COUNT_ARRIVALS = 3;

    /**
     * @return string
     */
    public function find_bus()
    {
        $time = time();
        $buses = new Bus();
        $buses = $buses->searchByStopsId([$this->requestParams['from'], $this->requestParams['to']]);
        $resp = ['Data not found'];
        $status = 404;
        if ($buses) {
            $resp = [
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

                $resp['buses'][] = [
                    'route' => 'Автобус ' . $bus['title'] . ' в сторону ост. ' . $stops->last()['id'],
                    'next_arrivals' => $currentStopArrival
                ];
            }
            $status = 200;
        }
        return $this->response($resp, $status);
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
    public function update() {}

    /**
     * Deletes the record.
     * @return string
     */
    public function delete() {}
}
