<?php

require_once('static.php');

/**
The packet saver simply takes a packet and then saves it.
*/

class PacketSaver {

	public function toText($packet) {
		$doc = $this->encapsulate($packet);
		
		$xmltext = $doc->saveXML();
		return $xmltext;
	}

	/**
	Convert a packet to an encapsulated packet.
	<packet>...actual packet...</packet>
	*/
	public function encapsulate($packet) {
		$doc = new DOMDocument();
		
		$container = $doc->createElementNS(XML_NAMESPACE, "packets");
		$container->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
		
		$doc->appendChild($container);
		$xml = $packet->toXML();
		
		$packet_element = $doc->importNode($xml, true);
		$container->appendChild($packet_element);

		return $doc;
	}

	/**
	Save the packet to the filesystem.
	*/
	
	public function save($packet) {
		$packet->stamp();
		$guid = "";
		$guid = $packet->guid;
		
		$filename = outpacket($guid);
		echo "Preparing to save packet ", $guid, " filename is: ",$filename;
		$file = fopen($filename, 'w');
		if ($file === FALSE) {
			echo "Unable to save";
			return;
		}

		
		$xmltext = $this->toText($packet);
		fwrite($file, $xmltext);

		fclose($file);
		
	}

}

?>