<?php
// Miscallaneous
define('BASE', getcwd());

define('XML_NAMESPACE', 'http://protocol.sofabric.com');

define('TEST_USER', '42018EBC-E278-402A-A1D3-B44359CF138A');

// Path constants should always end with a / so they can be concatenated.

define('INPACKETS_DIR', 'inpackets/' );		// packets to be parsed+processed
define('OUTPACKETS_DIR', 'outpackets/' );	// packets to be delivered
define('ACCOUNTS_DIR', 'users/' );		// local user accounts+personal data
define('USERDATA_DIR', ACCOUNTS_DIR . 'userdata/' );		// storage for a particular user
define('PROFILES_DIR', 'userprofiles/' );	// external user personal data

define('QUERIES_DIR', 'queries/' );		// where database queries are stored

// where installation queries are stored
define('INSTALL_DIR', QUERIES_DIR . 'installation/' ); 

define('TEMPLATES_DIR', 'templates/');			// for HTML templates
define('COMPILED_DIR', TEMPLATES_DIR . 'compiled/');	// for compiled HTML templates

define('PRIVATE_KEY_DIR', BASE . '/private/' );		// file system private key
define('PUBLIC_KEY_DIR', BASE . '/public/' );		// file system public keys
define('ENCRYPTED_OUTPACKETS_DIR', OUTPACKETS_DIR . 'encrypted/');
define('ENCRYPTED_INPACKETS_DIR', INPACKETS_DIR . 'encrypted/');


// Extensions
define('XML_EXT', '.xml');
define('KEY_EXT', '.pem');
define('SQL_EXT', '.sql');
define('DATABASE_EXT', '.sqli');
define('PAGE_EXT', '.php');
define('TEMPLATE_EXT', '.haml');
define('COMPILEDTEMPLATE_EXT', '.compiled.php');
define('ENCRYPTED_EXT', '.enc');
define('ENCRYPTED_KEY_EXT', '.key');
define('ENCRYPTED_DELIM', '@');

// Forms
define('FORMS_STATUS', 'status');
define('FORMS_RECIPIENTS', 'recipients');

// Errors
define('LOGIN_ERROR', 'LOGIN ERROR');
define('PACKET_NOT_FOUND', 'PACKET NOT FOUND');
define('SEND_ERROR', 'SEND ERROR');



define('SESSION_LOGGEDIN', 'loggedin');			// $_SESSION loggedin flag
define('SESSION_GUID', 'guid');				// $_SESSION guid for logged in user
define('SESSION_PASSWORD', 'password');			// $_SESSION user's password



$menulinks = array('My Feed' => 'main',
		'Status Update' => 'statusupdate',
		'Contacts' => 'contacts');

// SocialNet Sections
define('SECTION_LOGIN', 'login');
define('SECTION_USERPROFILE', 'userprofile');
define('SECTION_FEED', 'main');

class Sections {
	public static $valid = array(
		SECTION_LOGIN,
		SECTION_USERPROFILE,
		SECTION_FEED
	);
	public static function isValid($section) {
		return in_array($section, self::$valid);
	}
}

// Templates
$title = "SocialNet - default title";

define('TEMPLATES_LOGIN', 'login');
define('TEMPLATES_LAYOUT', 'layout');
define('TEMPLATES_FEED', 'feed');
define('TEMPLATES_USERPROFILE', 'userprofile');
define('TEMPLATES_STATUSUPDATE', 'statusupdate');

define('TEMPLATES_FEEDITEM_COMMENT', 'feeditem_comment');
define('TEMPLATES_FEEDITEM_STATUS', 'feeditem_statusupdate');
define('TEMPLATES_FEEDITEM_REPLY', 'feeditem_reply');

define('HAML_HELPERS_FILE', 'SocialHelpers.php');

// File path helper functions

function user_file($guid) {
	return ACCOUNTS_DIR . $guid . XML_EXT;
}

function inpacket($guid) {
	return INPACKETS_DIR . $guid . XML_EXT;
}

function outpacket($guid) {
	return OUTPACKETS_DIR . $guid . XML_EXT;
}

function encryptedoutpacket($guid) {
	return ENCRYPTED_OUTPACKETS_DIR . $guid . ENCRYPTED_EXT;
}

function encryptedinpacket($guid) {
	return ENCRYPTED_INPACKETS_DIR . $guid . ENCRYPTED_EXT;
}

function encryptedoutpacketkey($packetguid, $recipientguid) {
	return encryptedoutpacketdir($recipientguid) . $packetguid . ENCRYPTED_DELIM . $recipientguid . ENCRYPTED_KEY_EXT;
}

function encryptedpacketdata($name) {
	$components = explode(ENCRYPTED_DELIM, pathinfo($name, PATHINFO_FILENAME));
	return array(
		'packetguid' => $components[0],
		'recipientguid' => $components[1]
	);
}

function encryptedoutpacketdir($recipientguid) {
	return ENCRYPTED_OUTPACKETS_DIR . $recipientguid . '/';
}

function encryptedinpacketkey($packetguid, $recipientguid) {
	return encryptedinpacketdir($recipientguid) . $packetguid . ENCRYPTED_DELIM . $recipientguid . ENCRYPTED_KEY_EXT;
}

function encryptedinpacketdir($recipientguid) {
	return ENCRYPTED_INPACKETS_DIR . $recipientguid . '/';
}

function pubkeyfile($guid) {
	return PUBLIC_KEY_DIR . $guid . KEY_EXT;
}

function userprofile($guid) {
	return PROFILES_DIR . $guid . XML_EXT;
}

function privkeyfile($guid) {
	return PRIVATE_KEY_DIR . $guid . KEY_EXT;
}


// Page names

function page_filename($name) {
	return $name . PAGE_EXT;
}

// Helpers
function is_logged() {
	return isset($_SESSION[SESSION_LOGGEDIN]) && $_SESSION[SESSION_LOGGEDIN] === TRUE;
}

function is_encrypted($name) {
	return is_ext($name, ENCRYPTED_EXT);
}

function is_keyfile($name) {
	return is_ext($name, ENCRYPTED_KEY_EXT);
}

function is_ext($name, $ext) {
	return (pathinfo($name, PATHINFO_EXTENSION) === substr($ext, 1));
}


// Queries
// Packet Destinations

define('INPACKETS_FEED', 'feed_packets' );
define('INPACKETS_PROFILE', 'profile_packets' );

// Server Installation queries
define('INSTALLATION_KNOWNPROFILES', 'knownprofiles');
define('INSTALLATION_TEST', 'installation');

$serverinstall = array(
	INSTALLATION_KNOWNPROFILES,
	INSTALLATION_TEST
);

// User Installation queries
define('INSTALLATION_MYCONTACTS', 'my_contacts');		// known contacts
define('INSTALLATION_MYGROUPS', 'my_groups');			// contact group
define('INSTALLATION_MYPROFILEFEED', 'my_profilefeed');		// 
define('INSTALLATION_PROFILEPACKETS', 'profile_packets');	// displayed on profile 
define('INSTALLATION_FEEDPACKETS', 'feed_packets');		// displayed on frontpage
define('INSTALLATION_WAITINGPACKETS', 'waiting_packets');	// encrypted packets
$userinstall = array(
	INSTALLATION_MYCONTACTS,
	INSTALLATION_MYGROUPS,
	INSTALLATION_MYPROFILEFEED,
	INSTALLATION_PROFILEPACKETS,
	INSTALLATION_FEEDPACKETS,
	INSTALLATION_WAITINGPACKETS
);

// Runtime Queries

define('QUERIES_ADD_KNOWN_PROFILE', 'addknownprofile');
define('QUERIES_GET_ALL_KNOWN_PROFILES', 'getallknownprofiles');
define('QUERIES_ADD_PACKET', 'addpacket');
define('QUERIES_ADD_WAITING', 'addwaiting');
define('QUERIES_GET_INPACKETS', 'getinpackets');
define('QUERIES_UPDATE_WAITING', 'updatewaiting');
define('QUERIES_CHECK_WAITING', 'checkpackets');

// Drivers

define('DRIVER_IMAP', 'imap');
define('DRIVER_SMTP', 'smtp');
define('DRIVER_EMAIL', 'email');
define('DRIVER_HTTPPOST', 'httppost');
define('DRIVER_XMPP', 'xmpp');

// Experimental:

class Post {
	public $message;
	public $from;

	public function __construct($message, $from) {
		$this->message = $message;
		$this->from = $from;
	}
}

$posts = array(
	new Post("Hey Sam, what's up", "Hari Seldon"),
	new Post("Wazzup!", "Mr Awesome")	
);

?>