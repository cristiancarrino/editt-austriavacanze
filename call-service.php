<?php

require_once 'json-to-xml.php';
require_once 'xml-to-json.php';
require_once 'parameters.php';

class CallService
{
	public $jsonRequest;
	private $jsonResponse;

	public function makeCall ($jsonRequest, $isTest) {
		$this->jsonRequest = $jsonRequest;
		$this->jsonRequest->Request->Source->{'@attributes'}->ClientID = $isTest ? TEST_CLIENT_ID : CLIENT_ID;
		$this->jsonRequest->Request->Source->{'@attributes'}->Password = $isTest ? TEST_PASSWORD : PASSWORD;
	
		$xmlRequest = (new JsonToXml($this->jsonRequest))->getXml();
		return $this->sendXmlGetJson($xmlRequest);
	}

	public function sendXmlGetJson ($xmlRequest) {
		// Get XML respsonse
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, SERVER_URL);
		curl_setopt($ch, CURLOPT_POST, $xmlRequest);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: text/xml; charset=utf-8',
			'Connection: Keep-Alive'
		));
		$xmlResponse = curl_exec($ch);
		curl_close ($ch);

		// Clean XML Response
		$xmlResponse = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $xmlResponse);

		// XML to JSON Response
		$this->jsonResponse = new XmlToJson($xmlResponse);

		return $this->jsonResponse;
	}

	public function getJsonResponse() {
		header('Content-Type: application/json');
		echo json_encode($this->jsonResponse);
	}
}

?>