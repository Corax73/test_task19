<?php

namespace Controllers;

use Exception;
use Models\AbstractModel;
use RuntimeException;

abstract class AbstractController
{
    /**
     * @var string
     */
    protected string $method = '';
    /**
     * @var string
     */
    protected string $action = '';
    /**
     * @var int
     */
    protected int $status = 404;
    /**
     * @var array<mixed, mixed>
     */
    protected array $resp = ['Data not found'];
    /**
     * @var AbstractModel
     */
    protected AbstractModel $model;
    /**
     * @var string
     */
    public string $apiName = '';
    /**
     * @var array<mixed, string>
     */
    public array $requestUri = [];
    /**
     * @var array<string, mixed>
     */
    public array $requestParams = [];

    public function __construct()
    {
        header('Access-Control-Allow-Orgin: *');
        header('Access-Control-Allow-Methods: *');
        header('Content-Type: application/json');

        $this->requestUri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $_POST = $_POST ? $_POST : json_decode(strval(file_get_contents('php://input')), true) ?? [];
        $this->requestParams = array_merge($_REQUEST, $_POST);
        $this->method = $_SERVER['REQUEST_METHOD'];

        if ($this->method == 'POST' && array_key_exists('_METHOD', $_REQUEST)) {
            if ($_REQUEST['_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_REQUEST['_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception('Unexpected Header');
            }
        }
    }

    /**
     * Starts the controller.
     * @return ?string
     */
    public function run(): ?string
    {
        if (array_shift($this->requestUri) !== 'api' || array_shift($this->requestUri) !== $this->apiName) {
            throw new RuntimeException('API Not Found', 404);
        }
        $this->action = $this->getAction();

        if (method_exists($this, $this->action)) {
            return $this->{$this->action}();
        } else {
            throw new RuntimeException('Invalid Method', 405);
        }
    }

    /**
     * Set status. Returns json.
     * @param array<mixed, mixed> $data
     * @param int $status
     * @return string|bool
     */
    protected function response(array $data, $status = 500): string|bool
    {
        header('HTTP/1.1 ' . $status . ' ' . $this->requestStatus($status));
        return json_encode($data);
    }

    /**
     * Returns status as string.
     * @param int $code
     * @return string
     */
    private function requestStatus(int $code): string
    {
        $status = [
            200 => 'OK',

            404 => 'Not Found',

            405 => 'Method Not Allowed',

            500 => 'Internal Server Error'
        ];
        return isset($status[$code]) ? $status[$code] : $status[500];
    }

    /**
     * Returns method name or empty string.
     * @return string
     */
    protected function getAction(): string
    {
        $resp = '';
        $method = $this->method;
        switch ($method) {
            case 'GET':
                if ($this->requestUri) {
                    $resp = 'show';
                } else {
                    $resp = 'index';
                }
                break;
            case 'POST':
                if ($this->requestUri) {
                    $resp = 'find_bus';
                }
                break;
            case 'PUT':
                $resp = 'update';
                break;
            case 'DELETE':
                $resp = 'delete';
                break;
            default:
        }
        return $resp;
    }

    abstract protected function find_bus();
    abstract protected function index();
    abstract protected function show();
    abstract protected function store();
    abstract protected function update();
    abstract protected function delete();
}
