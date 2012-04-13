<div class="menu">
<?php
$logged = is_logged();

if ($logged) {
?>
Logged in as <?php echo $auth->getUser()->profile->displayname;

?> | <?php

foreach($menulinks as $display => $link) {?>
<a href="<?php echo $link; ?>.php"><?php echo $display; ?></a> |
<?php
	}
?>
<a href="logout.php">Logout</a>
<?php // end of logged in
}

if (!$logged) {?>
	Not logged in | <a href="login.php">Login...</a> | <a href="register.php">Register</a>
<?php
}
?>
</div>