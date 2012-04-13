<?php

/**
Test cases class
*/

require_once('static.php');
require('data.php');
require('parser.php');
require('accountlayer.php');
require_once('standards.php');

// Test 001: UserRef XML

$ref = new UserRef("121","Blah");
UserRef::test();

echo '<code>',htmlspecialchars($ref->toXMl()->ownerDocument->saveXML()),'</code><br><br>';

// Test 002: UserProfile XML

$profile = new UserProfile();
$profile->displayname = "fuz";
$profile->firstname = "the";
$profile->lastname = "fuz";
$profile->relationship = "complicated";
$profile->emails = array(
	array("personal", "fuz@example.com"),
	array("work", "fuz@example.net")
);
$profile->publickey = "empty";
$profile->comms = array(
	'httppost' => new Communication('httppost', 'http://localhost/client/server.php')
);
$profile->defaultcomms = 'httppost';

echo '<hr><code>',htmlspecialchars($profile->toXMl()->ownerDocument->saveXML()),'</code>';

// Test 003: StatusUpdate XML

StatusUpdate::test();

// Test 004: UserProfile XML

UserProfile::test();

// Test 005: Message XML

Message::test();

// Test 006: UserProfile XML

echo "<hr>";
echo "<br/>","TESTPROFILE PARSE","<br/>";

$doc = new DOMDocument();
$doc->loadXML(file_get_contents('testpackets/testprofile.xml'));

$profile = UserProfile::fromXML($doc->documentElement);

echo '<code>',htmlspecialchars($profile->toXML()->ownerDocument->saveXML()),'</code>';

// Test 007: Account XML

$acct = new Account($profile);
Account::test();


// Test 008: Test parsing account
echo "<hr>";
echo "<br/>","LOOK FOR TEST ACCOUNT","<br/>";

$search = $udb->getAccount(TEST_USER);
echo "<br>Found account? ",var_dump($search !== FALSE);


echo '<br>Deserialized:<br><code>',htmlspecialchars($search->toXML()->ownerDocument->saveXML()),'</code>';

// Test 009: Comments 

Comment::test();

?>