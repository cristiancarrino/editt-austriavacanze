<?php

class JsonToXml
{
    private $XMLRequest;

	function __construct($jsonRequest) {
		$objectRequest = is_string ($jsonRequest) ? json_decode($jsonRequest) : $jsonRequest;

		$this->XMLRequest = new SimpleXMLElement('<Root></Root>');
		$this->jsonToXml($objectRequest, $this->XMLRequest);
		$this->XMLRequest = $this->XMLRequest->children();
    }

	function jsonToXML($json, &$Request) {
		if (is_object($json)) {
			foreach (get_object_vars($json) as $key => $value) {
				if ($key == '@attributes') {
					foreach ($value as $attrKey => $attrValue) {
					    $Request->addAttribute($attrKey, $attrValue);
					}
				} else if ($key == '@value') {
					$Request[0] = $value;
				} else {
					$child = $Request->addChild($key);
					$this->jsonToXML($value, $child);
				}
			}

		} else if (is_array($json)) {

			foreach ($json as $items) {
				foreach ($items as $key => $value) {
					$child = $Request->addChild($key);
					$this->jsonToXML($value, $child);
				}
			}
			
		} else {
			$Request[0] = $json;
		}
	}

	function __toString() 
    {    
        return '<?xml version="1.0" encoding="UTF-8"?>' . $this->XMLRequest->asXML();
    }

	function getXml() 
    {    
        return '<?xml version="1.0" encoding="UTF-8"?>' . $this->XMLRequest->asXML();
    }
}

?>