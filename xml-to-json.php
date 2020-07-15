<?php

class XmlToJson extends SimpleXmlElement implements JsonSerializable
{
	function simplexml_to_array ($xml, &$array, $index = 0) {
		$array[$xml->getName()] = [];
		
		// Node with children
		foreach ($xml->children() as $child) {
			if (count($xml->xpath($child->getName())) > 1 || in_array($xml->getName(), ['Rooms', 'Children', 'Hotels'])) {
				$this->simplexml_to_array($child, $array[$xml->getName()][$index++]);
			} else {
				$this->simplexml_to_array($child, $array[$xml->getName()]);
			}			
		}
		
		// Node attributes
		foreach ($xml->attributes() as $key => $att) {
			$array[$xml->getName()][$key] = (string) $att;
		}
		
		// Node with value
		if (trim((string) $xml) != '') {
			$array[$xml->getName()]['@value'] = (string) $xml;
		}
		
		return $array;
	}
	
	
	function jsonSerialize()
	{				
		return $this->simplexml_to_array($this, $array);
	}
}

?>