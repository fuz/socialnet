<?php

/**
Saves a profile in the proper fashion. Currently only implemented as
a file.
*/

class ProfileSaver {
	public $accounts;
	
	public function __construct($accounts) {
		$this->accounts = $accounts;
	}

	public function save($account, $password) {
		// make sure the account has a GUID.
		$account->stamp();


		$manager = new Security($this->accounts);
		// create some keys and add them to the account
		$manager->createKeys($account, $password);
		$guid = $account->guid;
		
		$filename = 'users/' . $account->guid. '.xml';
		echo "Preparing to save new account ",$guid, " filename is: ",$filename;
		$file = fopen($filename, 'w');
		if ($file === FALSE) {
			echo "Unable to save";
			return;
		}
		$xmltext = $account->toXML()->ownerDocument->saveXML();

		fwrite($file, $xmltext);

		fclose($file);
	} 
}




?>