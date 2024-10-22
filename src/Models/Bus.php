<?php

namespace Models;

/**
 * @property string $table
 */
class Bus extends AbstractModel
{
    protected string $table = 'buses';
    public array $bus_stops;
    protected array $fillable = [
        'bus_stops',
        'title'
    ];
    protected array $guarded = [];
    protected string $unique = '';

    /**
     * Save call data.
     * @param array <string, mixed> $bus_stops
     * @return bool
     */
    public function save(array $bus_stops): bool
    {
        $resp = false;
        $strFields = implode(', ', $this->fillable);
        if ($strFields) {
            try {
                $query = 'INSERT INTO `' . $this->table . '` (' . $strFields . ', created_at) VALUES (:bus_stops, :now)';
                $params = [
                    ':bus_stops' => $bus_stops,
                    ':now' => date('Y-m-d h:i:s', time())
                ];
                $stmt = $this->connect->connect(PATH_CONF)->prepare($query);
                $this->connect->connect(PATH_CONF)->beginTransaction();
                $resp = $stmt->execute($params);
                $this->connect->connect(PATH_CONF)->commit();
            } catch (\Exception $e) {
                if ($this->connect->connect(PATH_CONF)->inTransaction()) {
                    $this->connect->connect(PATH_CONF)->rollback();
                }
                throw $e;
            }
        }
        return $resp;
    }

    /**
     * Overridden.
     * Receives an array to fill the properties, calls the validation method, and if successful, fills the model.
     * @param array <string, mixed> $data
     * @return bool
     */
    public function fill(array $data): bool
    {
        $resp = false;
        $validDate = $this->validate($data);
        if ($validDate) {
            foreach ($this->fillable as $prop) {
                $this->$prop = $validDate[$prop];
            }
            $resp = true;
        }
        return $resp;
    }

    /**
     * Calls the save method if the instance properties are filled.
     * @return bool
     */
    public function saveByFill(): bool
    {
        $resp = false;
        if ($this->bus_stops) {
            $resp = $this->save($this->bus_stops);
        }
        return $resp;
    }

    /**
     * In the incoming array checks for the presence of keys for model properties and values, and if successful, returns a data array.
     * @param array <string, mixed> $data
     * @return array <string, mixed>
     */
    protected function validate(array $data): array
    {
        $resp = [];
        if (isset($data['bus_stops']) && $data['bus_stops']) {
            $resp['bus_stops'] = $data['bus_stops'];
        }
        return $resp;
    }
}
