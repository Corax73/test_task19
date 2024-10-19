<?php

namespace Controllers;

use Exception;
use RuntimeException;

abstract class AbstractController
{
    public $apiName = '';
    protected $method = '';
    public $requestUri = [];
    public $requestParams = [];
    protected $action = '';

    public function __construct()
    {
        header('Access-Control-Allow-Orgin: *');
        header('Access-Control-Allow-Methods: *');
        header('Content-Type: application/json');

        $this->requestUri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $this->requestParams = $_REQUEST;
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
     */
    public function run()
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
     * Returns status and json
     * @return string
     */
    protected function response(array $data, $status = 500): string
    {
        header('HTTP/1.1 ' . $status . ' ' . $this->requestStatus($status));
        return json_encode($data);
    }

    private function requestStatus($code)
    {
        $status = [
            200 => 'OK',

            404 => 'Not Found',

            405 => 'Method Not Allowed',

            500 => 'Internal Server Error'
        ];
        return $status[$code] ? $status[$code] : $status[500];
    }

    protected function getAction()
    {
        $method = $this->method;
        switch ($method) {
            case 'GET':
                if ($this->requestUri) {
                    return 'show';
                } else {
                    return 'index';
                }
                break;
            case 'POST':
                return 'store';
                break;
            case 'PUT':
                return 'update';
                break;
            case 'DELETE':
                return 'delete';
                break;
            default:
                return null;
        }
    }

    abstract protected function index();
    abstract protected function show();
    abstract protected function store();
    abstract protected function update();
    abstract protected function delete();
}
