<?php
require_once('data.php');
require_once('crypto.php');

require_once('standards.php');
require_once('storage.php');
require_once('accountlayer.php');

guest_required();
$success = FALSE;
?>

<h1>Register an Account</h1>
<p>This page will create an account for you to use to interact with other users
of SocialNet.</p>
<p>

</p>

<?php



$parsed = new DOMDocument();
$templatefile = 'usertemplate.form'; 

$parsed->loadXML(file_get_contents($templatefile));

$template = UserProfile::fromXML($parsed->documentElement);
$fresh = new UserProfile();
$elements = array();

$password = "";

$elements["Display Name"] = array($template->displayname, &$fresh->displayname);
$elements["Password"] = array("password", &$password);
$elements["Email Address"] = array($template->accountemail, &$fresh->accountemail);
$elements["First Name"] = array($template->firstname, &$fresh->firstname);
$elements["Last Name"] = array("text", &$fresh->lastname);
$elements["Relationship?"] = array($template->relationship, &$fresh->relationship);



if (isset($_POST["submit"])) {
	foreach ($elements as $name => $data) {
		$formname = getName($name);
		$fielddata = $_POST[$formname];
		// set the field data in the template to the data provided
		$data[1] = $fielddata;
	}

	$emailconfig = array(
		'address' => $fresh->accountemail
	);
	$fresh->comms = array(
		DRIVER_EMAIL => new Communication(DRIVER_EMAIL, serialize($emailconfig))
	);
	$fresh->defaultcomms = DRIVER_EMAIL;
	
	// $xml = $fresh->toXML();
	// echo "<br>",htmlspecialchars($xml->ownerDocument->saveXML());
	echo "Your profile has been generated.";

	$account = new Account($fresh);
	
	$imapconfig = array(
		'username' => $fresh->accountemail,
		'password' => $password,
		'server' => '{fuz.fuz:143}',
		
	);
	$smtpconfig = array(
		'username' => $fresh->accountemail,
		'password' => $password,
		'server' => '127.0.0.1',
		'port' => '25'
	);
	$account->comms = array(
		DRIVER_IMAP => new Communication(DRIVER_IMAP, serialize($imapconfig)),
		DRIVER_SMTP => new Communication(DRIVER_SMTP, serialize($smtpconfig))		
		);
	$account->defaultcomms = DRIVER_IMAP;

	$udb->save($account, $password);
	$success = TRUE;
	
} else {
	



?>
<h2>Complete your profile information</h2>
<p>The following information is attached to your profile. Your display name
is seen by everyone. You can decide who sees what later on.</p>
<form method="POST"/>
<?php



foreach($elements as $name => $data) {
	$type = $data[0];

	if (strpos($type, "text") !== FALSE) {
		text_field(getName($name), $name);
	} else if (strpos($type, "dropdown") !== FALSE) {
		dropdown(getName($name), $name, $type);
	} else if (strpos($type, "password") !== FALSE) {
		password(getName($name), $name);

	}
}
?>
<input name="submit" type="submit"/>
<?php
}

function getName($display) {
	return strtolower(str_replace(' ', '', $display));
}

?>
<br/>
</form>
<?php
if ($success) {?>
<a href="login.php">Login now...</a>

<?php
}

function text_field($name, $display) {

?>
<label for="<?php echo $name; ?>"><?php echo $display; ?></label>
<input type="text" id="<?php echo $name; ?>" name="<?php echo $name; ?>" /><br/>
<?php
}
?>


<?php
function dropdown($name, $display, $data) {
$data = str_replace('dropdown:', '', $data);
$options = explode('|', $data);
?>
<label for="<?php echo $name; ?>"><?php echo $display; ?></label>
<select id="<?php echo $name;?>" name="<?php echo $name;?>"/>
<?php
	foreach($options as $item) {
?>
	<option value="<?php echo $item;?>"><?php echo $item;?></option>
<?php
	}
?>
</select><br/>
<?php
}
?>

<?php
function password($name, $display) {
?>
<label for="<?php echo $name; ?>"><?php echo $display; ?></label>
<input type="password" id="<?php echo $name; ?>" name="<?php echo $name; ?>" /><br/>
<?php
}
?>
