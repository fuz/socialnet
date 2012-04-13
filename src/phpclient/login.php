<?php
require_once('standards.php');
require_once('accountlayer.php');

guest_required();

/*
Attempt to login as a user using their email address and password.
*/

if ( isset ($_POST["submitted"]) ) {
	// now we can check the login credentials
	$guid = $_POST["guid"];
	$password = $_POST["password"];
	$attempt = $udb->login($guid, $password);
	// login successful
	if ($attempt === LOGIN_ERROR) {
		echo "Login error. Please try again.";
	} else {
		// we are now logged in
		$logged = $_SESSION[SESSION_LOGGEDIN];
		$_SESSION[SESSION_LOGGEDIN] = TRUE;
		$_SESSION[SESSION_PASSWORD] = $password;
		$_SESSION[SESSION_GUID] = $guid;
		http_redirect("main.php", array("name" => "value"), true, HTTP_REDIRECT_PERM);
	}
}

?>

<form method="POST" action="login.php">
<label for="guid">GUID</label><input id="guid" name="guid" type="text" value="<?php echo TEST_USER ?>"/><br>
<label for="password">Password</label><input id="password" name="password" type="password"/><br>
<input name="submitted" type="submit" value="Login" />
</form>
Don't have an account? <a href="register.php">Register</a>