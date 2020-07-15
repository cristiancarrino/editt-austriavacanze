<?php

require_once 'json-to-xml.php';

$jsonPostdata = file_get_contents("php://input");
$xmlRequest = strval(new JsonToXml($jsonPostdata));
echo $xmlRequest;

?>