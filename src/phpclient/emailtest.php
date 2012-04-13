<?php

require_once('/swiftmail/swift_required.php');

//Create the message
$message = Swift_Message::newInstance()

  //Give the message a subject
  ->setSubject('Packet')

  //Set the From address with an associative array
  ->setFrom(array('fuz@fuz.fuz' => 'Fuz Fuz'))

  //Set the To addresses with an associative array
  ->setTo(array('fuz@fuz.fuz' => 'Meh'))

  //Give it a body
  ->setBody('This is a new packet for you.')

  //And optionally an alternative body
  ->addPart('<q>Here is the message itself</q>', 'text/html')

  //Optionally add any attachments
  ->attach(Swift_Attachment::fromPath('test.txt'))
  ;

  $transport = Swift_SmtpTransport::newInstance('127.0.0.1', 25)
  ->setUsername('fuz@fuz.fuz')
  ->setPassword('password')
  ;
  $mailer = Swift_Mailer::newInstance($transport);
  $result = $mailer->send($message);


$mbox = imap_open("{fuz.fuz:143}", "fuz@fuz.fuz", "cheese");

echo "<h1>Mailboxes</h1>\n";
$folders = imap_listmailbox($mbox, "{fuz.fuz:143}", "*");

if ($folders == false) {
    echo "Call failed<br />\n";
} else {
    foreach ($folders as $val) {
        echo $val . "<br />\n";
    }
}

echo "<h1>Headers in INBOX</h1>\n";
$headers = imap_headers($mbox);

if ($headers == false) {
    echo "Call failed<br />\n";
} else {
    foreach ($headers as $val) {
        echo $val . "<br />\n";
    }
}

imap_close($mbox);

?>