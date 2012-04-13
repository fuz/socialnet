<?php

define('ENCODING', 'UTF-8');

/**
All packets must implement this if they want to input or output into XML.
Note that toXML() can return a node in a different DOMDocument which needs
to be imported with $doc->importNodes($node, true);
*/
interface HasXML {
	public function toXMl();
	public static function fromXML($xml);
	
}

function create_guid() {
	$guid = com_create_guid();
	return str_replace(array('}','{'), '', $guid);
}

/**
This is a base packet, that includes the most basic information
for any packet that is sent between users.
*/

class Packet {
	/** What the packet looks like when rendered */
	public $feed_template = "";
	public $compiled_template = "";
	/** Where the packet should appear. */
	public $destination = "";
	
	public $guid = "";
	/** The GUIDs this packet is for. */
	public $recipients = array();
	public $from = "";
	public $sentdate = "";
	public $document;

	// Metadata for the in-memory objects that are not serialized into XML
	public $fromfile = FALSE;
	public $filename;

	/** When the packet is ready to be sent, it should be stamped. */
	public function stamp() {
		$this->guid = create_guid();
		$this->sentdate = date(DATE_W3C);
		echo "STAMPED!";
	}

	public function __construct() {
		
	}

	public function toXML() {
		$this->document = new DOMDocument("1.0", ENCODING);
		$doc = $this->document;
		$metadata = $doc->createElement("metadata");

		// guid of the packet
		$guid = $doc->createElement("guid");
		$guid->appendChild($doc->createTextNode($this->guid));
			$metadata->appendChild($guid);

		// who the packet was sent by
		$from = $doc->createElement("from");
		$from->appendChild($doc->createTextNode($this->from));
			$metadata->appendChild($from);

		// when the packet was sent from the client's PC
		$sentdate = $doc->createElement("sentdate");
		$sentdate->appendChild($doc->createTextNode($this->sentdate));
			$metadata->appendChild($sentdate);

		// those who should receive the packet
		$recipients = $doc->createElement("recipients");
			$metadata->appendChild($recipients);
			
		// add each recipient to the XML
		foreach ($this->recipients as $rguid) {
			$recipient = $doc->createElement("recipient");
			$recipient->appendChild($doc->createTextNode($rguid));
			$recipients->appendChild($recipient);
		}
	
			
		return $metadata;
	}

	public static function appendToXML($xml, $packet) {
		$sentdate = $xml->getElementsByTagName("sentdate")->item(0)->nodeValue;
		$guid = $xml->getElementsByTagName("guid")->item(0)->nodeValue;
		$from = $xml->getElementsByTagName("from")->item(0)->nodeValue;
		
		$packet->sentdate = $sentdate;
		$packet->guid = $guid;
		$packet->from = $from;
		

		// add the recipients back to the packet
		$recipients = $xml->getElementsByTagName("recipients")->item(0);
		foreach($recipients->childNodes as $node) {
			if ($node->tagName === "recipient") {
				$email = $node->nodeValue;
				array_push($packet->recipients, $email);
			}
			
		}
	}
}


class Account implements HasXML {
	public $pkey;
	public $profile;
	public $guid;

	// types of comms
	public $comms;
	// the preferred comms method
	public $defaultcomms;

	/** When an account is ready to be saved, it should be stamped. */
	public function stamp() {
		$this->guid = create_guid();
		$this->profile->guid = $this->guid;
	}
	
	public static function fromXML($xml) {
		$doc = new DOMDocument("1.0", ENCODING);
		$privatekey = $xml->getElementsByTagName("privatekey")->item(0)->nodeValue;
		$guid = $xml->getElementsByTagName("guid")->item(0)->nodeValue;
		
		// container
		$comms = $xml->getElementsByTagName("incomms")->item(0);
		$defaultcomms = $comms->getAttribute("default");
		$drivers = $comms->getElementsByTagName("commstrategy");
		
		$commstable = array();
		foreach($drivers as $strategyxml) {
			$parsed = Communication::fromXML($strategyxml);	
			$driver = $parsed->driver;
			$commstable[$driver] = $parsed;
		}


		$profile = UserProfile::fromXML($xml->getElementsByTagName("userprofile")->item(0));
		$account = new Account($profile);
		$account->pkey = $privatekey;
		$account->guid = $guid;
		$account->profile = $profile;
		$account->comms = $commstable;
		$account->defaultcomms = $defaultcomms;

		return $account;
		
	}

	function __construct($profile) {
		$this->profile = $profile;
	}

	public function toXML() {
		$this->document = new DOMDocument("1.0", ENCODING);
		$doc = $this->document;
		$account = $doc->createElement("account");
			$doc->appendChild($account);

		// private key
		$key = $doc->createElement("privatekey");
			$key->appendChild($doc->createTextNode($this->pkey));
		$account->appendChild($key);

		// guid
		$guid = $doc->createElement("guid");
			$guid->appendChild($doc->createTextNode($this->guid));
		$account->appendChild($guid);

		// communications
		$communications = $doc->createElement("incomms");
		$communications->setAttribute("default", $this->defaultcomms);
		
		foreach ($this->comms as $method => $strategy) {
			$node = $doc->importNode($strategy->toXML(), true);
			$communications->appendChild($node);
		}
		$account->appendChild($communications);

		// profile
		$profile = $doc->importNode($this->profile->toXML(), true);
		$account->appendChild($profile);

		return $account;		
	}

	public static function test() {
		$profile = new UserProfile();
		$profile->displayname = "fuz";
		$profile->firstname = "Fuz";
		$profile->lastname = "the";
		$profile->relationship = "complicated";
		$profile->accountemail ="fuz@example.net";
		
		$profile->emails = array(
			array("personal", "fuz@example.net"),
			array("work", "fuz@example.net")
		);
		
		$profile->publickey = "empty";
		$profile->comms = array(
			'ftp' => new Communication('ftp', 'ftp://server/folder')
		);
		$profile->defaultcomms = 'ftp';
		$profile->guid = "aaa";

		$account = new Account($profile);
		$account->pkey = "GHWJSH";
		$account->guid = "aaa";
		$account->comms = array(
			'ftp'=> new Communication('ftp', 'ftp://server/folder')
		);
		$account->defaultcomms = 'ftp';
		
		Tester::test("USERPROFILE TEST", 'UserProfile', $profile);
		Tester::test("ACCOUNT TEST", 'Account', $account);
	
	}
}

/**
A UserRef is a reference to another user. Reference to other users usually
involves their names being linked to. They may also be informed of the message,
except for private messages.
*/
class UserRef implements HasXML {
	private $id;
	private $user;
	private $name;

	public static function fromXML($xml) {
		$id = $xml->getAttribute("id");
		$name = $xml->nodeValue;
		return new UserRef($id, $name);
	}

	public function toXMl() {
		$doc = new DOMDocument();
		$xml = new DOMELement('userref');
		$doc->appendChild($xml);
		$xml->setAttribute("id", $this->id);
		$text = new DOMText($this->name);
		$xml->appendChild($text);
		
		return $xml;
	}
	/** Create a userref to a particular ID with a certain display name. */
	function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}

	/**
	This is used to ensure that when we use toXML() to generate an XML
	representation of an object, that when we convert it back into the object
	the objects are the same (equal).
	 */
	public static function test() {
		$ref = new UserRef("660", "Bob");
		Tester::test("USERREF Tests", 'UserRef', $ref);
	}

	/** Output a friendly representation of this reference. */
	public function __toString() {
		return '<a href="userprofile.php?' . $this->id . '">'. $this->name . '</a>';
	}
}
/**
The user profile is a summary of personal information for a user. This would be 
displayed on the USERPROFILE screen.
*/
class UserProfile implements HasXML {
	// this user's GUID
	public $guid;
	public $accountemail;
	public $displayname;
	public $firstname;
	public $lastname;
	public $relationship;

	public $emails = array();

	public $publickey;

	// communication methods
	public $comms;
	// preferred communication method
	public $defaultcomms;

	public static function fromXML($xml) {
		$displayname = $xml->getElementsByTagName("displayname")->item(0)->nodeValue;
		$firstname = $xml->getElementsByTagName("firstname")->item(0)->nodeValue;
		$lastname = $xml->getElementsByTagName("lastname")->item(0)->nodeValue;
		$relationship = $xml->getElementsByTagName("relationship")->item(0)->nodeValue;
		$accountemail = $xml->getElementsByTagName("accountemail")->item(0)->nodeValue;
		$publickey = $xml->getElementsByTagName("publickey")->item(0)->nodeValue;

		$guid = $xml->getElementsByTagName("guid")->item(0)->nodeValue;

		// create communications object properly
		$comms = $xml->getElementsByTagName("communication")->item(0);
		$defaultcomms = $comms->getAttribute("default");
		$drivers = $comms->getElementsByTagName("commstrategy");
		
		$commstable = array();
		// add each method to the object
		foreach($drivers as $strategyxml) {
			$parsed = Communication::fromXML($strategyxml);
			$driver = $parsed->driver;
			$commstable[$driver] = $parsed;
		}
		
		
		$user = new UserProfile();
		$user->displayname = $displayname;
		$user->firstname = $firstname;
		$user->lastname = $lastname;
		$user->relationship = $relationship;
		$user->accountemail = $accountemail;
		$user->publickey = $publickey;
		$user->comms = $commstable;
		$user->defaultcomms = $defaultcomms;
		$user->guid = $guid;

		
		$emailNodes = $xml->getElementsByTagName("email");
		
		foreach($emailNodes as $emailNode) {
			// types of email: personal, work
			$address = $emailNode->nodeValue;
			$type = $emailNode->getAttribute("type");
			if (strlen($type) == 0) {
				$type = "personal";
			}
			// type, email
			$entry = array($type, $address);
			$user->emails[] = $entry;
		}
		return $user;
	}

	public function __construct() {
		
	}

	public function toXMl() {
		$doc = new DOMDocument();
		$xml = new DOMELement('userprofile');
		$doc->appendChild($xml);

		$guid = $doc->createElement("guid");
		$guid->appendChild($doc->createTextNode($this->guid));
			$xml->appendChild($guid);

		// the various names of a user
		$names = new DOMElement('name');
		$xml->appendChild($names);
			$displayname = new DOMElement('displayname', $this->displayname);
			$firstname = new DOMElement('firstname', $this->firstname);
			$lastname = new DOMElement('lastname', $this->lastname);
		$names->appendChild($displayname);
		$names->appendChild($firstname);
		$names->appendChild($lastname);

		// the main account email
		$accountEmail = $doc->createElement("accountemail");
		$accountEmail->appendChild($doc->createTextNode($this->accountemail));
		$xml->appendChild($accountEmail);

		// alternative profile email addresses
		$emails = $doc->createElement("emails");
		$xml->appendChild($emails);

		foreach($this->emails as $emaildata) {
			$type = $emaildata[0];
			$address = $emaildata[1];
			$emailNode = $doc->createElement("email");
			$emailNode->appendChild($doc->createTextNode($address));
			$emailNode->setAttribute("type", $type);
			$emails->appendChild($emailNode);
		}

		// relationship status
		$relationship = $doc->createElement('relationship');
		$xml->appendChild($relationship);
		$relationship->appendChild($doc->createTextNode($this->relationship));

		// public key, needed to contact user
		$publickey = $doc->createElement("publickey");
		$publickey->appendChild($doc->createTextNode($this->publickey));
			$xml->appendChild($publickey);

		// communication methods
		$comms = $doc->createElement("communication");
		$comms->setAttribute("default", $this->defaultcomms);
			
		foreach($this->comms as $method => $strategy) {	
			$node = $doc->importNode($strategy->toXML(), true);
			$comms->appendChild($node);
		}
		$xml->appendChild($comms);
		
		return $xml;
	}

	public static function test() {
		$profile = new UserProfile();
		$profile->displayname = "fuz";
		$profile->firstname = "Fuz";
		$profile->lastname = "the";
		$profile->relationship = "complicated";
		$profile->accountemail = "fuz@example.net";
		$profile->publickey = "empty";
		$profile->emails = array(
			array("personal","fuz@example.net"),
			array("work","fuz@example.net")
		);
		$profile->comms = array('email' => new Communication('email', "fuz@example.net"));
		$profile->defaultcomms = 'email';

		Tester::test("USERPROFILE TESTS", 'UserProfile', $profile);
		
	}
	
}


/**
Represents the communication method when sending to this client.
<comms></comms>
*/
class Communication implements HasXML {
	public $driver;
	public $commstring;
	public static $drivers = array(
		'httppost' => 'POSTSender'
	);

	public static function fromXML($xml) {
		$commstring = $xml->nodeValue;
		$driver = $xml->getAttribute("driver");
		
		$co = new Communication($driver, $commstring);
		return $co;
	}

	public function __construct($driver, $commstring = "") {
		$this->driver = $driver;
		$this->commstring = $commstring;
	}

	public function toXML() {
		$doc = new DOMDocument();
		$comms = $doc->createElement('commstrategy');
		$doc->appendChild($comms);
		$comms->setAttribute("driver", $this->driver);
		$comms->appendChild($doc->createTextNode($this->commstring));

		return $comms;
	}
}

class StatusUpdate extends Packet implements HasXML {
	public $msg;
		
	public static function fromXML($xml) {
		$msgXML = $xml->getElementsByTagName("message")->item(0);
		$newmsg = Message::fromXML($msgXML);

		// create a new SU
		$su = new StatusUpdate($newmsg);
		// update the metadata
		parent::appendToXML($xml, $su);

		return $su;
	}

	public function __construct($message) {
		parent::__construct();
		$this->msg = $message;
		$this->feed_template = TEMPLATES_FEEDITEM_STATUS;
		$this->destination = INPACKETS_FEED;
	}

	public function toXMl() {
		// create the metadata and the document
		$metadata = parent::toXML();
		$doc = $this->document;
		
		$xml = $doc->createElement('statusupdate');
		// the metadata is contained within the packet
		$xml->appendChild($metadata);
		$doc->appendChild($xml);

		$message = $doc->importNode($this->msg->toXML(), true);
		$xml->appendChild($message);

		return $xml;

	}

	public function getUpdate() {
		return $this->msg->contents;
	}

	public static function test() {
		$testmessage = array("Hello ",new UserRef("6660404","Alice")," how are you?");
		$su = new StatusUpdate(new Message($testmessage));
		Tester::test("STATUSUPDATE Tests", 'StatusUpdate', $su);
		
	}
}

class Comment extends Packet {
	public $msg;
	// the packet that this is a comment about
	public $parent;

	public static function fromXML($xml) {
		
		$msgXML = $xml->getElementsByTagName('message')->item(0);
		$parent = $xml->getElementsByTagName('parent')->item(0)->nodeValue;
		echo "trying to parse from xml";
		var_dump($msgXML);
		$newmsg = Message::fromXML($msgXML);

		// create a new SU
		$su = new Comment($parent, $newmsg);
		
		// update the metadata
		parent::appendToXML($xml, $su);

		return $su;
	}

	public function __construct($parent, $message) {
		parent::__construct();
		$this->parent = $parent;
		$this->msg = $message;
		$this->feed_template = TEMPLATES_FEEDITEM_COMMENT;
		$this->destination = INPACKETS_FEED;
	}

	public function toXMl() {
		// create the metadata and the document
		$metadata = parent::toXML();
		$doc = $this->document;
		
		$xml = $doc->createElement('comment');
		// the metadata is contained within the packet
		$xml->appendChild($metadata);
		$doc->appendChild($xml);

		// add the parent
		$parent = $doc->createElement('parent');
		$parent->appendChild($doc->createTextNode($this->parent));
		$xml->appendChild($parent);
		
		$message = $doc->importNode($this->msg->toXML(), true);
		$xml->appendChild($message);

		return $xml;
	}

	public static function test() {
		$testmessage = array("Hello ",new UserRef("6660404","Julian")," how are you?");
		$su = new Comment("55551", new Message($testmessage));
		Tester::test("COMMENT Tests", 'Comment', $su);
	}
}

class Message implements HasXML {
	public $contents = array();
	/** These are XML element names => PHP objects that this element may contain.
	The PHP objects must have a fromXML($xml) method */
	public static $contains = array("userref" => 'UserRef');

	public static function fromXML($xml) {
		$data = array();
		/** We need to take the XML and*/
		foreach($xml->childNodes as $node) {
			
			if ($node->nodeType === XML_TEXT_NODE) {
				array_push($data, $node->nodeValue);
				continue;
			}
			
			$tag = $node->tagName;
			if (array_key_exists($tag, self::$contains)) {
				$newtype = self::$contains[$tag];
				// used because php does not support $newtype::fromXML()
				$newnode = call_user_func(array($newtype, 'fromXML'), $node);
				array_push($data, $newnode);
			}
		}

		return new Message($data);
	}

	public function __construct($data) {
		$this->contents = $data;
	}

	public function toXML() {
		$doc = new DOMDocument();
		$message = new DOMElement('message');
		$doc->appendChild($message);
		foreach($this->contents as $text) {
			
			if (is_string($text)) {
				$message->appendChild(new DOMText($text));
			} else {
				$xmlnode = $doc->importNode($text->toXML(), true);
				$message->appendChild($xmlnode);
			}
		}
		return $message;
	}

	public static function test() {
		$msg = new Message(array("£%FVD <test> Lol \"blah ^"));
		
		Tester::test("MESSAGE object serialization", 'Message', $msg);
		
	}
}

function bool2str($bool)
{
    if ($bool === false) {
        return 'FALSE';
    } else {
        return 'TRUE';
    }
}

/*
Tester class, ensures that serialization avoids losing data.
*/

class Tester {
	public static function test($name, $type, $instance) {
		echo "<hr>";
		echo "<br>", $name, "<br>";
		$xmldata = $instance->toXML()->ownerDocument->saveXML();
		echo "<br>First serialization:<br><code>",htmlspecialchars($xmldata),"</code><br><br>";
		$doc = new DOMDocument();
		$doc->loadXML($xmldata);

		$newInstance = call_user_func(array($type, 'fromXML'), $doc->documentElement);
		
		// $newmsg = Message::fromXML($doc->documentElement);
		echo "<br>Reparsed:<br><code>",htmlspecialchars($newInstance->toXML()->ownerDocument->saveXML()),"</code><br><br>";
		echo "Are the equal? ",bool2str($instance == $newInstance);
	}
}
?>
