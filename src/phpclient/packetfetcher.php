<?php

require_once('parser.php');
require_once('core.php');

class PacketFetcher {

	public $folder;

	public function __construct($packetFolder) {
		$this->folder = $packetFolder;
	}
	
	public function getPacketFiles() {
		$packets = scandir($this->folder);
		return array_filter($packets, array($this, 'packets_only'));
	}

	public static function packets_only($var) {
		return pathinfo($var, PATHINFO_EXTENSION) === 'xml';
	}

	/**
	Parses all packets in a folder and returns a list of
	packet objects from the folder.
	*/
	public function getPackets() {
		$parser = new PacketParser();
		$allpackets = array();
		$packetfiles = $this->getPacketFiles();
		
		foreach($packetfiles as $filename) {
			// get the packets from the file
			$found = $parser->parse($this->folder . $filename);
			// add them to our array of all packets
			$allpackets = array_merge($allpackets, $found);
		}

		return $allpackets;
	}

}

class Receiver {
	public $account;
	
	public static $drivers = array(
		DRIVER_IMAP => 'IMAPReceiver'
	);
	public function __construct($account) {
		$this->account = $account;
		
	}

	/**
	Ask for messages.
	*/

	public function receive($core) {
		$account = $this->account;
		$defaultcomms = $account->defaultcomms;
		$drivername = self::$drivers[$defaultcomms];

		$driver = new $drivername($core);
		$driverconfig = unserialize($account->comms[$defaultcomms]->commstring);
		$driver->receive($driverconfig);
	}
}

class IMAPReceiver {
	public $core;
	public function __construct($core) {
		$this->core = $core;
	}
	
	public $decoder = array(
		0 => 'decode_7bit',
		1 => 'decode_8bit',
		2 => 'decode_binary',
		3 => 'decode_base64',
		4 => 'decode_quoted',
		5 => 'decode_other'
	);

	public $handlers = array(
		'filename' => 'filename'
	);

	public function filename($parameter) {
		$encoded = $parameter->value;
		$decoded = imap_mime_header_decode($encoded);
		$filename = $decoded[0]->text;
		return $filename;
	}


	public function decode_7bit($encoded) {
		return imap_7bit($encoded);
	}
	public function decode_8bit($encoded) {
		return imap_8bit($encoded);
	} 
	public function decode_binary($encoded) {
		return imap_binary($encoded);
	}
	public function decode_base64($encoded) {
		return imap_base64($encoded);
	}
	public function decode_quoted($encoded) {
		return quoted_printable($encoded);
	}
	public function decode_other($encoded) {
		return $encoded;
	}
	
	public function receive($driverconfig) {
		$server = $driverconfig['server'];
		$username = $driverconfig['username'];
		$password = $driverconfig['password'];
		
		$mbox = imap_open($server, $username, $password);

		$total = imap_num_msg($mbox);

		
		echo "<br>there are ", $total," messages available.<br>";
		
		for ($cur = 1; $cur < $total; $cur++) {
			$info = imap_headerinfo($mbox, $cur);
			
		}

		$unseen = imap_search($mbox, "UNSEEN");
		if ($unseen === FALSE) { // no messages
			return false;
		}
		var_dump($unseen);
		echo "There are ", count($unseen), " unsen messages.";
		
		echo "Structures:<br>\\n";
		$handlers = $this->handlers;
		
		foreach($unseen as $message) {
			$incoming = array();
		
			// get the email
			$structure = imap_fetchstructure($mbox, $message);
			if (!isset($structure->parts)) {
				continue;
			}
			$attachments = array();
			// var_dump($structure);
			$partnumber = 0;
			foreach($structure->parts as $part) {
				$partnumber++;
				$attachment = array();

				// var_dump($structure);
				
				if ($part->ifdparameters) {
					foreach($part->dparameters as $parameter) {
						$attribute = strtolower($parameter->attribute);
						
						if (array_key_exists($attribute, $handlers)) {
							$value = call_user_func(array(
									$this,	
									$handlers[$attribute]
								), $parameter);
								
							$attachment[$attribute] = $value;
							if ($attribute == 'filename') {
								$filename = $value;
								$text = imap_fetchbody($mbox, $message, $partnumber);
								$encoding = $part->encoding;
								$decoder = $this->decoder[$encoding];
								$attachment['data'] = call_user_func(array($this, $decoder), $text);
								
								$attachments[$filename] = $attachment;
								$incoming[$filename] = $filename;
								var_dump($attachment);
							}
						}
						
						
					}
				}

			
				
			}


			// put the attachments in this email in 
			// the right place
			$destinations = $this->core->handleReceivedFiles($incoming);
			foreach($destinations as $key => $newpath) {
				$attachment = $attachments[$key];
				$data = $attachment['data'];
				file_put_contents($newpath, $data);
			}
			
		} // each message
	
		imap_close($mbox);
		

	}
}

?>