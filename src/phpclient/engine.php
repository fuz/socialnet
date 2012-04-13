<?php

if (defined('SOCIALNET_LOADED') === FALSE) {
	// exit;
}
require_once('static.php');
require_once('sender.php');
require_once('crypto.php');
require_once('storage.php');
require_once('packetfetcher.php');
require_once('accountlayer.php');

class Server {
	public $accounts;
	public function __construct($accounts) {
		$this->accounts = $accounts;
		
	}

	/**
	Deliver outpackets to their respective destinations.
	*/
	public function deliverPackets($folder) {
		$fetcher = new PacketFetcher($folder);
		// get all packets in the folder
		$allpackets = $fetcher->getPackets();
		
		$security = new Security($this->accounts);
		
		foreach($allpackets as $packet) {
			$from = $packet->from;
			// get the sending user
			$fromaccount = $this->accounts->getAccount($from);
		
			$sender = new Sender($fromaccount);
		
			// we need to encrypt the packet

			$encryption = $security->encryptPacket($packet);
			$newfile = $encryption['file'];
			$keyfiles = $encryption['keyfiles'];
			
			foreach($packet->recipients as $recipient) {
								
				$user = $this->accounts->getProfile($recipient);
				if ($user === FALSE) {
				// skip profiles that could not be found
					echo "No profile found for ", $recipient;
					continue;
				}
				// files to be sent
				$files =  array(
					$newfile,
					$keyfiles[$recipient]
				);
				// send the encrypted data and the encrypted key
				// needed for the user to decrypt it
				$sender->send($user, $files);
				// unlink the key file when we've delivered it
				unlink($keyfiles[$recipient]);
				echo "Attempting to send to recipient",$recipient,"<br><b>Filename:</b> ", $packet->filename,"<br>";
			}
		// delete the encrypted file when we are done
		unlink($newfile);
		// delete the unencrypted packet
		unlink($packet->filename);
		}
	}
	
}
?>