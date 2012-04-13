<?php

require_once('static.php');

/** 
This class is responsible for implementing the PACKET security of the Socialnet.
This is possible through encryption.
*/


class Security {
	public $accounts;
	public function __construct($accounts) {
		$this->accounts = $accounts;
	}
	/**
	Creates new credentials for a user given that user's profile and their
	password. 
	*/
	public function createKeys($account, $password) {
		$profile = $account->profile;
		$guid = $account->guid;
	
		$dn = array(
		"countryName" => 'XX',
		"stateOrProvinceName" => 'State',
		"localityName" => 'SomewhereCity',
		"organizationName" => 'MySelf',
		"organizationalUnitName" => 'Whatever',
		"commonName" => $profile->displayname,
		"emailAddress" => $profile->accountemail);

		$numberofdays = 365;

		// create key pair for the new account
		$privkey = openssl_pkey_new();
		// create a signing request
 		$csr = openssl_csr_new($dn, $privkey);
 		// sign the certificate
		$sscert = openssl_csr_sign($csr, null, $privkey, $numberofdays);

		// save the public key to the user
		openssl_x509_export($sscert, $pubtext);
		// save the private key to the user
 		openssl_pkey_export($privkey, $privtext, $password);

 		$account->pkey = $privtext;
 		$profile->publickey = $pubtext;
		
		// save copies to the file system too
 		openssl_x509_export_to_file($sscert, pubkeyfile($guid));
 		openssl_pkey_export_to_file($privkey, privkeyfile($guid), $password);
		
	}

		/**
		Encrypt a packet for its recipients and produce a list
		of encrypted keys for each user that itself needs to be 
		decrypted.
		*/
		public function encryptPacket($packet) {
			if ($packet->fromfile === FALSE) {
				echo "Cannot find packet file.";
				return PACKET_NOT_FOUND;
			}
			$guid = $packet->guid;
			$public = array();
			$secrets = array();
			// get the public keys for each recipient
			foreach ($packet->recipients as $recipient) {
				$key = $this->getPublicKey($recipient);
				array_push($public, $key);
			}
			
			$unencrypted = file_get_contents($packet->filename);
			$encrypted = "";
			// encrypt the data using each recipient's public key
			openssl_seal($unencrypted, $encrypted, $newkeys, $public);

			
			
			// save the encrypted data to a file
			$filename = encryptedoutpacket($guid);
			$file = fopen($filename, 'w');
			echo "Preparing to save encrypted packet ", $guid, " filename is: ",$filename,"<br>";
			if ($file === FALSE) {
				echo "Unable to save<br>";
				return;
			}
			fwrite($file, $encrypted);
			fclose($file);
			
			$keyfiles = array();
			// recipientguid => keytext
			$secrets = array_combine($packet->recipients, $newkeys);
			
			foreach ($secrets as $recipient => $keydata) {
				$dir = encryptedoutpacketdir($recipient);
			
				if (file_exists($dir) === FALSE) {
					mkdir($dir);
				}
			
				$keyfilename = encryptedoutpacketkey($guid, $recipient);
				$keyfile = fopen($keyfilename, 'w');
				$keyfiles[$recipient] = $keyfilename;
				// write the secret key
				fwrite($keyfile, $keydata);
				fclose($keyfile);
			}

			
			$response = array(
				'file' => $filename,
				'keyfiles' => $keyfiles,
				
			);
			return $response;
		}

		public function decryptPacket($encryptedguid, $account, $password) {
			$filename = encryptedinpacket($encryptedguid);
			$recipient = $account->guid;
			$keyfilename = encryptedinpacketkey($encryptedguid, $recipient);

			echo "<br>Encrypted file:",$filename,"<br>";
			echo "<br>Encrypted keyfile:",$keyfilename,"<br>";
			if (file_exists($filename) === FALSE || file_exists($keyfilename) === FALSE) {
				echo "<br>Cannot find key<br>";
				return;
			}
			$privtext = $account->pkey;
			$private = openssl_pkey_get_private($privtext, $password);
			if ($private === FALSE) {
				echo "<br>Error decrypting packet.<br>";
				
				return FALSE;
			}
			$encrypted = file_get_contents($filename);
			$sealkey = file_get_contents($keyfilename);
			$unencrypted = "";
			$decryption = openssl_open($encrypted, $unencrypted, $sealkey, $private);
			openssl_free_key($private);
			if ($decryption === FALSE) {
				return FALSE;
			}
			echo '<code>',htmlspecialchars($unencrypted),'</code><br><br>';
			$packetfilename = inpacket($encryptedguid);
			$packetfile = fopen($packetfilename, 'w');
			
			fwrite($packetfile, $unencrypted);
			fclose($packetfile);

			// delete the encrypted data
			unlink($keyfilename);
			return TRUE;
			
		}

		public function getPublicKey($guid) {
			$profile = $this->accounts->getProfile($guid);
			$pubkey = $profile->publickey;
			$key = openssl_pkey_get_public($pubkey);
			return $key;
		}
}




/*
$security = new Security();

$dn = array("countryName" => 'XX', "stateOrProvinceName" => 'State', "localityName" => 'SomewhereCity', "organizationName" => 'MySelf', "organizationalUnitName" => 'Whatever', "commonName" => 'mySelf', "emailAddress" => 'user@domain.com');
 $privkeypass = '1234';


 $privkey = openssl_pkey_new();
 $csr = openssl_csr_new($dn, $privkey);
 $sscert = openssl_csr_sign($csr, null, $privkey, $numberofdays);

 
 openssl_x509_export($sscert, $publickey);
 openssl_pkey_export($privkey, $privatekey, $privkeypass);
 openssl_csr_export($csr, $csrStr);


 openssl_x509_export_to_file($sscert, getcwd() . "\pub.pem");
 openssl_pkey_export_to_file($privkey, getcwd() . "\priv.pem", "password");

  // echo $privatekey; // Will hold the exported PriKey
  // echo $publickey;  // Will hold the exported PubKey
  // echo $csrStr;     // Will hold the exported Certificate
  */
?>