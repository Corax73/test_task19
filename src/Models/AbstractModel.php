<?php

namespace Models;

use DB\Connection;
use PDO;

/**
 * @property string $table
 * @property array $fillable
 * @property array $guarded
 * @property Connection $connect
 * @property string $unique
 */
abstract class AbstractModel
{
    protected string $table;
    /**
     * @var array<string>
     */
    protected array $fillable;
    /**
     * @var array<string>
     */
    protected array $guarded;
    protected Connection $connect;
    protected string $unique;

    public function __construct()
    {
        $this->connect = new Connection;
    }

    /**
     * Returns the name of the model table.
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Searches by model ID. Returns an array with data or false.
     * @param int|array<int, int> $id
     * @return array<string, mixed> | bool
     */
    public function find(int|array $id): array | bool
    {
        if (is_array($id)) {
            $params = $id;
            $placeholders = str_repeat('?, ',  count($id) - 1) . '?';
            $query = "SELECT id, " . implode(', ', array_diff($this->fillable, $this->guarded)) . ",created_at FROM public.$this->table WHERE id IN ($placeholders)";
        } else {
            $query = 'SELECT id, ' . implode(', ', array_diff($this->fillable, $this->guarded)) . ',created_at FROM ' . $this->table . ' WHERE id = :id';
            $params = [
                ':id' => $id
            ];
        }
        $query .= ' ORDER BY id DESC';
        $stmt = $this->connect->connect(PATH_CONF)->prepare($query);
        $stmt->execute($params);
        $resp = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resp ? $resp : false;
    }

    /**
     * Returns an array of all model entries.
     * @param int $limit
     * @param int $offset
     * @param string $filter_field
     * @param int $filter_id
     * @return array<string, mixed>
     */
    public function all(int $limit = 12, int $offset = 0, string $filter_field = '', int $filter_id = 0): array
    {
        if (!empty($filter_field) && $filter_id) {
            $query = 'SELECT * FROM public.' . $this->table . ' WHERE ' . $filter_field . ' = :' . $filter_field . ' ORDER BY id DESC LIMIT :limit';
        } else {
            $query = 'SELECT * FROM public.' . $this->table . ' ORDER BY id DESC LIMIT :limit';
            if ($offset) {
                $query .= ' OFFSET :offset';
            }
        }
        $stmt = $this->connect->connect(PATH_CONF)->prepare($query);
        if (!empty($filter_field) && $filter_id) {
            $stmt->bindValue(':' . $filter_field, $filter_id, PDO::PARAM_INT);
        } elseif ($offset) {
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $resp = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resp ? $resp : [];
    }

    /**
     * Search in json field.
     * @param int $limit
     * @param int $id
     * @return array<string, mixed>
     */
    public function searchByStopsId(int $limit = 12, int $id = 0): array
    {
        $query = 'SELECT * FROM public.' . $this->table . " WHERE EXISTS(SELECT FROM jsonb_array_elements(buses.bus_stops->'stops') el
        WHERE el->>'id' = :id)" . ' ORDER BY id DESC LIMIT :limit';
        $stmt = $this->connect->connect(PATH_CONF)->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $resp = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resp ? $resp : [];
    }

    /**
     * Returns the number of model records.
     * @return int
     */
    public function getTotal(): int
    {
        $query = 'SELECT COUNT(*) FROM ' . $this->table . '';
        $stmt = $this->connect->connect(PATH_CONF)->prepare($query);
        $stmt->execute();
        $countNum = intval($stmt->fetchColumn());
        return $countNum;
    }

    /**
     * If the uniqueness property is filled, it searches for the passed string or number in this column of the model table.
     * If the property is empty or if there is an entry in the table, returns false.
     * @param string|int $search
     * @return bool
     */
    public function checkUnique(string|int $search): bool
    {
        $resp = false;
        if ($this->unique) {
            $query = 'SELECT COUNT(*) FROM ' . $this->table . ' WHERE ' . $this->unique . ' = :search';
            $params = [
                ':search' => $search
            ];
            $stmt = $this->connect->connect(PATH_CONF)->prepare($query);
            $stmt->execute($params);
            $countNum = intval($stmt->fetchColumn());
            if ($countNum === 0) {
                $resp = true;
            }
        }
        return $resp;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected abstract function validate(array $data): array;

    /**
     * Receives an array to fill the properties, calls the validation method, and if successful, fills the model.
     * @param array <string, mixed> $data
     * @return bool
     */
    public function fill(array $data): bool
    {
        $resp = false;
        $validDate = $this->validate($data);
        if ($validDate) {
            if ($this->checkUnique(trim($data[$this->unique]))) {
                foreach ($this->fillable as $prop) {
                    $this->$prop = $validDate[$prop];
                }
                $resp = true;
            }
        }
        return $resp;
    }
}
