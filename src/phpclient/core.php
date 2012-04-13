<?php

require_once('static.php');
require_once('storage.php');
require_once('accountlayer.php');
require_once('packetfetcher.php');

/**
The CORE is responsible for reading inpackets and acting upon them.
*/

class Core {
	// the server's storage mechanism
	public $serverdata;
	// the folder to read for incoming packets
	public $in;
	// the folder to place outgoing packets, should they be generated
	public $out;
	// the parser for reading packet files (xml->objects)
	public $parser;
	// the account layer
	public $udb;
	
	public function __construct($serverdata, $udb, $in, $out) {
		$this->serverdata = $serverdata;
		$this->udb = $udb;
		$this->parser = new PacketParser();
		$this->in = $in;
		$this->out = $out;		
	}
	/**
	Handle a packet.
	*/
	public function handlePacket($packet) {
		echo get_class($packet);
		// get the user datastore
		$recipients = $packet->recipients;
		$udb = $this->udb;
		
		foreach($recipients as $recipient) {
			$userdata = $udb->getUserStorage($recipient);
			echo "<br><strong>Connected to user storage for ", $recipient,"<br></strong>";

			$update = new IncomingPacket($packet);
			$update->insert($userdata);
		}

		 echo "Processing packet","</br>";
		 var_dump($packet);
		 echo '<pre>',htmlspecialchars($packet->toXMl()->ownerDocument->saveXML()),'</pre>';
		 echo "<br>",var_dump($packet->fromfile);
		 if ($packet->fromfile) {
			echo $packet->filename,"<br>";
		 }

		 
	}

	/**
	Process a folder and handle the packets within the folder.
	*/
	public function processFolder() {
		$parser = $this->parser;
		$newPackets = array();
		
		// parse this folder
		$pf = new PacketFetcher($this->in);
		$packets = $pf->getPacketFiles();

		foreach($packets as $packetfile) {
			$multipackets = $parser->parse($this->in . $packetfile);
			$newPackets = array_merge($newPackets, $multipackets);
		}
		
		foreach($newPackets as $aPacket) {
			$this->handlePacket($aPacket);
			unlink($aPacket->filename);
		}
	}

	/**
	Determines the proper path of a file depending on its filename.
	The input array should be in the format:
		$realfilename => $curpath

	The new path is then created and an array returned that is indexed
	with the old filename like:
		$curpath => $newpath

	This way you can loop over the result set and do some move operations
	yourself or even create the files if you have not created them yet.
	*/
	public function handleReceivedFiles($files) {
		$newdest = array();

		// decide where received files should go depending
		// on filename
		foreach($files as $name => $curpath) {
			
			$dest = "";
			if (is_encrypted($name)) {
				$dest = ENCRYPTED_INPACKETS_DIR . $name;

			} else if (is_keyfile($name)) {
				$data = encryptedpacketdata($name);

				$recipient = $data['recipientguid'];
				$packetguid = $data['packetguid'];
				
				$dir = encryptedinpacketdir($recipient);
				if (file_exists($dir) === FALSE) {
					mkdir($dir);
				}
				$dest = $dir . $name;

				$userdata = $this->udb->getUserStorage($recipient);
				$waiting = new IncomingEncryptedPacket($packetguid);
				$waiting->insert($userdata);			
			} else {
				// un encrypted
				$dest = INPACKETS_DIR . $name;
			}
		$newdest[$curpath] = $dest;
		}
		
		return $newdest;
	}


	/**
	Accepts an array of uploaded files in the array format:
		$currentfilename => $newfilename
	*/
	public function handleUploadedFiles($received) {
		$newpaths = $this->handleReceivedFiles($received);

		foreach($newpaths as $tmp => $newpath) {
			echo "Received file: ", $newpath," moving from ", $tmp, "<br>";
			var_dump(move_uploaded_file($tmp, $newpath));
		}
	}

	/**
	This user is requesting remote packets if they are available.
	*/

	public function checkForRemotePackets($guid) {
		$account = $this->udb->getAccount($guid);

		$remote = new Receiver($account);
		
		$remote->receive($this);
	}
}



?>