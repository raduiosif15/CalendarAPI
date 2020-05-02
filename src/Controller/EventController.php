<?php
namespace Src\Controller;

use Src\TableGateways\EventGateway;

class EventController {

    private $db;
    private $requestMethod;
    private $eventId;
    private $eventDate;

    private $eventGateway;

    public function __construct($db, $requestMethod, $eventId, $eventDate)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->eventId = $eventId;
        $this->eventDate = $eventDate;

        $this->eventGateway = new EventGateway($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->eventId) {
                    $response = $this->getEvent($this->eventId);
                } else if ($this->eventDate) {
                    $response = $this->getEventByDate($this->eventDate);
                } else {
                    $response = $this->getAllEvents();
                };
                break;
            case 'POST':
                $response = $this->createUserFromRequest();
                break;
            case 'PUT':
                $response = $this->updateUserFromRequest($this->eventId);
                break;
            case 'DELETE':
                $response = $this->deleteUser($this->eventId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllEvents()
    {
        $result = $this->eventGateway->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getEvent($id)
    {
        $result = $this->eventGateway->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getEventByDate($date)
    {
        $result = $this->eventGateway->findByDate($date);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createUserFromRequest()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateEvent($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->eventGateway->insert($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = null;
        return $response;
    }

    private function updateUserFromRequest($id)
    {
        $result = $this->eventGateway->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateEvent($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->eventGateway->update($id, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function deleteUser($id)
    {
        $result = $this->eventGateway->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $this->eventGateway->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validateEvent($input)
    {
        if (! isset($input['description'])) {
            return false;
        }
        if (! isset($input['fromToDate'])) {
            return false;
        }
        if (! isset($input['location'])) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}