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

$url = 'http://xml.mondial-travel.com:8080/monDetailServiceV3/get/hotels/json';

// Get json post data
$jsonPostdata = file_get_contents("php://input");
if ($jsonPostdata) {
	$objectPostData = json_decode($jsonPostdata);

	if (isset($objectPostData->locale) && $objectPostData->locale) {
		$url .= '/locale/' . $objectPostData->locale;
	}

	if (isset($objectPostData->season) && $objectPostData->season) {
		$url .= '/season/' . $objectPostData->season;
	}

	if (isset($objectPostData->id) && $objectPostData->id) {
		$url .= '/id/' . $objectPostData->id;
	}

	if (isset($objectPostData->name) && $objectPostData->name) {
		$url .= '/name/' . $objectPostData->name;
	}

	if (isset($objectPostData->code) && $objectPostData->code) {
		$url .= '/code/' . $objectPostData->code;
	}
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
$response = curl_exec($ch);
curl_close ($ch);

die($response);

?>