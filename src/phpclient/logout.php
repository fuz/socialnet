<?php

require_once('static.php');
require_once('standards.php');

if (is_logged()) {
	Authorisation::revoke();
}

redirect_to(SECTION_LOGIN);


?>