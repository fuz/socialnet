<?php

require_once('/swiftmail/swift_required.php');

interface SenderDriver {
	public function deliver($driverconfig, $files);
	public function __construct(Account $sender, UserProfile $recipient, $senderconfig = array());
	
}

abstract class BaseDriver implements SenderDriver {
	public $sender;
	public $recipient;

	public function __construct(Account $sender, UserProfile $recipient, $senderconfig = array()) {
		$this->sender = $sender;
		$this->recipient = $recipient;
	}


}

class Sender {
	public $account;
	public static $drivers = array(
		DRIVER_HTTPPOST => 'POSTSender',
		DRIVER_EMAIL => 'EmailSender',
		DRIVER_SMTP => 'SMTPSender',
		DRIVER_XMPP => 'XMPPSender'
	);
	public function __construct($account) {
		$this->account = $account;
	}

	public function send($recipient, $files) {
		// 1. decide what driver to use for recipient
		// 2. initialize driver with account settings (if any)
		// 3. use the driver to send to the recipient

		// the recipient's preferred packet method
		$defaultcomms = $recipient->defaultcomms;

		// instantiate the driver		
		$drivername = self::$drivers[$defaultcomms];

		$driver = new $drivername($this->account, $recipient);

		// configuration for the recipient's driver
		$driverconfig = unserialize($recipient->comms[$defaultcomms]->commstring);
		
		var_dump($driverconfig);
		// send the files with the driver
		$driver->deliver($driverconfig, $files);
	}
	
}

class SMTPSender extends BaseDriver {
	public $sender;
	public $mailer;
	public function __construct(Account $sender, UserProfile $recipient, $senderconfig = array()) {
		parent::__construct($sender, $recipient, $senderconfig);
		
		$server = $senderconfig['server'];
		$port = $senderconfig['port'];
		$username = $senderconfig['username'];
		$password = $senderconfig['password'];
		
		$transport = Swift_SmtpTransport::newInstance($server, $port)
		 ->setUsername($username)
		 ->setPassword($password);

		$this->mailer = Swift_Mailer::newInstance($transport);
	}

	public function deliver($driverconfig, $files) {
		echo "Sending files via email";
		
		$sender = $this->sender;
		$myprofile = $this->sender->profile;
		$recipient = $this->recipient;

		//Create the message
		$message = Swift_Message::newInstance()

		//Give the message a subject
		->setSubject('Packet')

		//Set the From address with an associative array
		->setFrom(array($myprofile->accountemail => $myprofile->displayname))

		//Set the To addresses with an associative array
		->setTo(array($recipient->accountemail => $recipient->displayname))

		//Give it a body
		->setBody('Incoming Packet')

		//And optionally an alternative body
		->addPart('<strong>You have received a packet. Run your Social Network client to read it.</strong>', 'text/html');

		// attach the encrypted messages
		foreach ($files as $file) {
			$message->attach(Swift_Attachment::fromPath($file));
		}

		$result = $this->mailer->send($message);
	}
}

class EmailSender extends BaseDriver {

	public $smtp;
	
	public function __construct(Account $sender, UserProfile $recipient, $senderconfig = array()) {
		parent::__construct($sender, $recipient);
		
		// email driver depends on the SMTP driver
		// 1. decide how to send to email address
		// 2. use SMTP driver if available
		$smtpconfig = unserialize($sender->comms[DRIVER_SMTP]->commstring);

		echo "Created email sender.";
		$this->smtp = new SMTPSender($sender, $recipient, $smtpconfig);

	}

	public function deliver($driverconfig, $files) {
		$this->smtp->deliver($driverconfig, $files);
	}

	
}

class POSTSender extends BaseDriver {
	
	/** 
	Sends a packet to its recipients.
	*/
	public function deliver($driverconfig, $files) {
		$url = $driverconfig['url'];

		$ch = curl_init();
		$filefield = 'packets';
		$data = array();
		$x = 0;
		foreach($files as $file) {
			$path = realpath($file);
			if (!file_exists($path)) {
				return SEND_ERROR;
			}
			$data[$filefield . '[' . $x . ']'] = '@' . $path;
			$x++;
		}

		echo "Attempting to send file ", $path,"...<br>";

		

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$success = curl_exec($ch);
		echo $success;
		curl_close($ch);
		return $success;
	}

}

class XMPPSender extends BaseDriver {
	public function deliver($driverconfig , $files) {

	}
}

?>