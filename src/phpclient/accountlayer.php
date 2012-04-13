<?php

require_once('static.php');
require_once('data.php');
require_once('storage.php');
require_once('profilesaver.php');
require_once('crypto.php');

interface AccountLayer {
	public function getAccount($guid);
}

/**
Lookup accounts.
*/

class Accounts implements AccountLayer {
	public $saver;
	public $storage;
	public $userinstall;
	
	public function __construct($storage, $userinstall) {
		$this->storage = $storage;
		$this->saver = new ProfileSaver($this);
		$this->userinstall = $userinstall;
	}

	public function getAccount($guid) {
		$accfile = user_file($guid);
		if (file_exists($accfile)) {
			$doc = new DOMDocument();
			$text = file_get_contents($accfile);
			$doc->loadXML($text);
			// create an account object
			$account = Account::fromXML($doc->documentElement);
			return $account;
		} else {
			return FALSE;
		}
	}

	public function login($guid, $password) {
		$fetch = $this->getAccount($guid);
		// no such account exists
		if ($fetch === FALSE) {
			return LOGIN_ERROR;
		}
		$account = $fetch;
		$privatekey = openssl_pkey_get_private($account->pkey, $password);
		// password is wrong
		if ($privatekey === FALSE) {
			return LOGIN_ERROR;
		}
		return TRUE;
	}

	/**
	Fetch the profile of a user using their GUID. This user may be a remote
	or a local account.
	*/
	public function getProfile($guid) {
		$localsearch = $this->getAccount($guid);
		// this account is local to this server
		if ($localsearch !== FALSE) {
			return $localsearch->profile;
		}
		return FALSE;
	}

	/**
	Add a newly discovered profile.
	*/
	public function addProfile($profile) {
		echo "saving profile ", $profile->guid," to database";
		$change = new KnownProfile($profile);
		$this->storage->insert($change);
	}

	/**
	Creates an account:
	 - saves the account file to the filesystem
	 - saves it to the database
	*/
	public function save($account, $password) {
		// creates account files, keys and saves to filesystem
		$this->saver->save($account, $password);
		// save knowledge of the profile to the database
		$this->addProfile($account->profile);
	}

	public function getKnownNames() {
		$names = array();
		$known = new KnownProfile();
		return $this->storage->get($known);
	}

	public function getUserStorage($guid) {
		$userdata = new UserStorage($guid, $this->userinstall);
		return $userdata;
	}

	/**
	Process the waiting packets for a user.
	*/
	public function processWaiting($guid, $password) {
		$security = new Security($this);
		$userdata = $this->getUserStorage($guid);
		if ($userdata === FALSE) {
			return;
		}
		$account = $this->getAccount($guid);
		
		$waiting = new IncomingEncryptedPacket();
		$packets = $waiting->getWaitingPackets($userdata);
		
		// no packets to process
		if ($packets === FALSE) {
			return;
		}

		// decrypt each packet
		foreach($packets as $packet) {
			$packetguid = $packet['packet'];
			$decrypted = $security->decryptPacket($packetguid, $account, $password);
			if ($decrypted === TRUE) {
				$waiting->data['packet'] = $packetguid;
				// delete the record
				$waiting->update($userdata);
			}
		}
		
	}
}



?>