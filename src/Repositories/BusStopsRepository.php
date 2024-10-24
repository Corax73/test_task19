<?php

namespace Repositories;

use Models\Stop;
use Services\TimeService;

class BusStopsRepository
{
    /**
     * Returns an array for the route search response based on the route array passed,
     * the integer IDs of the start and end stop, the number of nearest arrival times and the reference timestamp.
     * @param array<string, mixed> $dataBuses
     * @param string $from
     * @param string $to
     * @param int $countArrivals
     * @param int $unixTimestamp
     * @return array<string, mixed>
     */
    public static function getFindBusResponse(array $dataBuses, string $from, string $to, int $countArrivals, int $unixTimestamp): array
    {
        $resp = [];
        if ($dataBuses && !empty($from) && !empty($to) && $countArrivals > 0 && $unixTimestamp > 0) {
            $resp = [
                'from' => $from,
                'to' => $to,
                'buses' => []
            ];
            foreach ($dataBuses as $bus) {
                if (isset($bus['bus_stops']) && $bus['bus_stops'] && isset($bus['title']) && !empty($bus['title'])) {
                    $stops = json_decode($bus['bus_stops'], true);
                    if (isset($stops['stops'])) {
                        $stops = collect($stops['stops']);
                        $currentStop = $stops->where('id', $from);
                        $currentStopArrival = ['Data not found'];
                        if ($currentStop->isNotEmpty() && isset($currentStop->first()['arrival']) && $currentStop->first()['arrival']) {
                            $currentStopArrival = TimeService::getEarliestTime($currentStop->first()['arrival'], $countArrivals, $unixTimestamp);
                        }

                        $lastStopTitle = '';
                        if (isset($stops->last()['id'])) {
                            $stop = new Stop();
                            $lastStop = $stop->find($stops->last()['id']);
                            $lastStopTitle = $lastStop && isset($lastStop[0]['title']) ? $lastStop[0]['title'] : $stops->last()['id'];
                        }
                        $resp['buses'][] = [
                            'route' => 'Автобус ' . $bus['title'] . ' в сторону ост. ' . $lastStopTitle,
                            'next_arrivals' => $currentStopArrival
                        ];
                    }
                }
            }
        }
        return $resp;
    }
}
