<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    die();
}

error_reporting(0);

require_once 'call-service.php';
require_once 'database-service.php';

// Get json post data
$jsonPostdata = file_get_contents("php://input");
if (!$jsonPostdata) {
    header("HTTP/1.1 400 Bad Request");
    die('{ "success": false, "error": "json post data missing" }');
}

if (isset($_SERVER['PATH_INFO'])) {
    switch ($_SERVER['PATH_INFO']) {
        case '/test':
            $isTest = true;
        break;
        default:        
            http_response_code(404);
            die();
    }
} else {
    $isTest = false;
}

// Get username e password from json
$objectPostData = json_decode($jsonPostdata);
$username = $objectPostData->Request->Source->{'@attributes'}->ClientID;
$password = $objectPostData->Request->Source->{'@attributes'}->Password;

// Login
$database = new DatabaseService();
$database->login($username, $password);

if ($database->loggedUser) {
    $callService = new CallService();
    $response = $callService->makeCall($objectPostData, $isTest);

    // AddBookingRequest
    if (isset($objectPostData->Request->RequestDetails->AddBookingRequest)) {
        $reference = $response->BookingResponse->BookingReference;
        $status = $response->BookingResponse->BookingStatus;
        $creationUserId = $database->loggedUser['id'];
        $creationDate = $response->BookingResponse->BookingCreationDate;
        $creationJson = json_encode($response);
        $database->addBooking($reference, $status, $creationUserId, $creationDate, $creationJson);
    }

    // CancelBookingRequest
    if (isset($objectPostData->Request->RequestDetails->CancelBookingRequest)) {
        $reference = $response->BookingResponse->BookingReference;
        $status = $response->BookingResponse->BookingStatus;
        $cancellationUserId = $database->loggedUser['id'];
        $cancellationJson = json_encode($response);
        $database->cancelBooking($reference, $status, $cancellationUserId, $cancellationJson);
    }

    // SearchBookingRequest
    if (isset($response->SearchBookingResponse)) {       
        $bookingList = $database->getUserBookings($database->loggedUser['id']);
        for ($i = 0; $i < count($response->SearchBookingResponse->Bookings->Booking); $i++) {
            if (!in_array($response->SearchBookingResponse->Bookings->Booking[$i]->BookingReference, $bookingList)) {
                unset($response->SearchBookingResponse->Bookings->Booking[$i]);
                $i--;
            }
        }
    }

    $callService->getJsonResponse();
}

?>