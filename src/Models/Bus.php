<?php

namespace Models;

class Bus extends AbstractModel
{
    /**
     * @var string
     */
    protected string $table = 'buses';
    /**
     * @var string
     */
    public string $title;
    /**
     * @var array<string, mixed>
     */
    public array $bus_stops;
    /**
     * @var string
     */
    protected string $unique = '';
    /**
     * @var array<int, string>
     */
    protected array $fillable = [
        'bus_stops',
        'title'
    ];
    /**
     * @var array<int, string>
     */
    protected array $guarded = [];

    /**
     * Updates report data.
     * @param array<string, mixed> $newData
     * @param int $id
     * @return bool
     */
    public function update(array $newData, int $id, string $jsonKey = ''): bool
    {
        $resp = false;
        if ($this->find($id)) {
            $keys = array_keys($newData);
            $check = array_diff($keys, $this->fillable);
            if (!$check) {
                $query = 'UPDATE public.' . $this->table . ' SET ';
                $params = [];
                foreach ($keys as $key) {
                    $query .= $key . ' = :' . $key . ', ';
                    $params[':' . $key] = $newData[$key];
                }
                $query = mb_substr($query, 0, -2);
                $query .= ' WHERE id = ' . $id;

                $stmt = $this->connect->connect(PATH_CONF)->prepare($query);
                $resp =  $stmt->execute($params);
            }
        }
        return $resp;
    }
}
