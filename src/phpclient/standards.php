<?php
require_once('static.php');
require_once('accountlayer.php');

session_set_cookie_params(0, '/');
session_start();

class Authorisation {
	public $user;

	public function __construct($user) {
		$this->user = $user;
	}
	public function getUser() {
		return $this->user;
	}

	public static function revoke() {
		$_SESSION[SESSION_LOGGEDIN] = FALSE;
		unset($_SESSION[SESSION_GUID]);
		unset($auth);
		$_SESSION[SESSION_PASSWORD] = "";
		session_destroy();
		session_start();
		
	}
}

// Initialize the server storage layer
$serverdata = new Storage($serverinstall);
// Initialize Account Layer
$udb = new Accounts($serverdata, $userinstall);

class StaticAccounts {
	public static $udb;
	public static function setUDB($udb) {
		self::$udb = $udb;
	}
	public static function getUDB() {
		return self::$udb;
	}
}

StaticAccounts::setUDB($udb);

if (!is_logged()) {
	$_SESSION[SESSION_LOGGEDIN] = FALSE;
} else {

	$search = $udb->getAccount($_SESSION[SESSION_GUID]);
	if ($search === FALSE) {
		Authorisation::revoke();
	} else {
		$auth = new Authorisation($search);
	}
}
$old = session_id();
session_regenerate_id();
$new = session_id();



/**
Ensures that the user is logged in to see this page.
*/
function login_required() {
	if (!is_logged()) {
		redirect_to(SECTION_LOGIN);
	}
}

function guest_required() {
	if (is_logged()) {
		redirect_to(SECTION_FEED);
	}
}

function redirect_to($page, $queries = array()) {
	http_redirect(page_filename($page), $queries, false, HTTP_REDIRECT_TEMP);
}
/**
Returns a user to 
*/
function redirect_then($page, $return, $queries = array()) {
	$queries['returnto'] = $return;
	
	redirect_to($page, $queries);
}

function return_to($queries = array()) {
	if (isset($_GET) && isset($_GET['returnto'])) {
		$dest = SECTION_FEED;
		
		if (array_key_exists($_GET['returnto'], Sections::valid)) {
			$dest = $_GET['returnto'];
		}
		
		redirect_to($dest, $queries);
	}
}

function needed($what, $checks) {

	if (!isset($what)) {
		return FALSE;
	}
	
	foreach($checks as $value) {
		if (!isset($what[$value])) {
			return FALSE;
		}
	}
	return TRUE;
}



?>