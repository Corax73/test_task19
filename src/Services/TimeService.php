<?php

namespace Services;

class TimeService
{
    /**
     * From the passed array of time strings in the `h:m` format, selects the passed number of elements greater than the passed timestamp.
     * @param array<int, string> $times
     * @param int $count
     * @param int $nowTimestamp
     * @return  array<int, string> $times
     */
    public static function getEarliestTime(array $times, int $count, int $nowTimestamp): array
    {
        $resp = [];
        if ($times && $count > 0) {
            foreach ($times as $val) {
                if ($nowTimestamp < strtotime(date('Y-m-d') . ' ' . $val)) {
                    $resp[] = $val;
                }
                if (count($resp) == $count) {
                    break;
                }
            }
        }
        return $resp;
    }
}
