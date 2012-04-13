<?php

require_once('data.php');
/**
We parse an XML file to create an object hierarchy that we can operate upon.
*/



class PacketParser {
	public $packetTypes = array(
		'statusupdate' => 'StatusUpdate',
		'comment' => 'Comment'
	);

	/**
	Create an array of Packets from a PACKETFILE.
	*/
	public function parse($packetfile) {
		$xml = file_get_contents($packetfile);
		return $this->parseText($xml, TRUE, $packetfile);
	}

	/**
	Create an array of packets from XML text.
	*/	
	public function parseText($xml, $fromfile = FALSE, $filename = "") {
		$packets = array();
		$doc = new DOMDocument();
		// create a DOM representation
		$doc->loadXML($xml);
		
		foreach($doc->documentElement->childNodes as $child) {
			$tag = $child->tagName;

			if (array_key_exists($tag, $this->packetTypes)) {
				$type = $this->packetTypes[$tag];
				// php does not support this syntax
				// $newPacket = $type::fromXML($child);
				// so we have to use call_user_func
				$newPacket = call_user_func(array($type, 'fromXML'), $child);
				
				if ($fromfile) {
					$newPacket->fromfile = TRUE;
					$newPacket->filename = $filename;
				}
				array_push($packets, $newPacket);
			}
		}
		
		return $packets;
	}
}



?>