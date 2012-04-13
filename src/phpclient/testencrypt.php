<?php
	$pubpath = 'file://' . getcwd() . '\pub.pem';
	echo $pubpath,"<br>";
	$public = openssl_pkey_get_public($pubpath);
	var_dump($public);

	
	$plaintext = "Encryption works :-)";

	openssl_public_encrypt($plaintext, $encrypted, $public);

	echo $encrypted,"<br>";

	$privpath = 'file://' . getcwd() . '\priv.pem';
	$private = openssl_pkey_get_private($privpath, "password");
	var_dump($private);
	
	openssl_private_decrypt($encrypted, $decrypted, $private);
	

	echo "Decrypted: ",$decrypted,"<br>";
	

?>