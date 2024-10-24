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
     * Search in json field.
     * @param array<int, int> $ids
     * @return array<string, mixed>
     */
    public function searchByStopsId(array $ids = []): array
    {
        $placeholders = str_repeat('?, ',  count($ids) - 1) . '?';
        $query = 'SELECT id, ' . implode(', ', array_diff($this->fillable, $this->guarded)) .
            ' FROM public.' . $this->table . " WHERE EXISTS(SELECT FROM jsonb_array_elements(buses.bus_stops->'stops') el WHERE el->>'id' IN (
        $placeholders
        ))" . ' ORDER BY id DESC';
        $stmt = $this->connect->connect(PATH_CONF)->prepare($query);
        $stmt->execute($ids);
        $resp = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resp ? $resp : [];
    }
}
