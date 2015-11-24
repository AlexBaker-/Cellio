<?php
define('TWILIO', true);
define('LIBPATH', realpath(dirname(__FILE__).'/..').'/lib');
require_once LIBPATH.'/init.php';

// Gather variables from Twilio
$time = gmdate('r');
$req = array_change_key_case($_REQUEST, CASE_UPPER);
$recipientSID = $req['ACCOUNTSID'];
$receiver = $req['TO'];
$from = $req['FROM'];
$body = $req['BODY'];
$geo = gen_geo_output($req, 'post');

// MMS attachments
$attachments = '';
if ($req['NUMMEDIA'] > 0) {
  $attachments = "\n\nAttachments:\n";
  foreach ($req as $k => $v)
    if (substr($k,0,8) == 'MEDIAURL')
      $attachments .= "* $v\n";
}

// Email recipient
$recipient = $config['ACCOUNTS'][$recipientSID]['EMAIL'];
$email_body = "SMS received to $receiver\n\nTime: $time\n\nFrom: $from\n\n${geo}Message: ${body}${attachments}";
send_email($recipient, $config['OPTS']['EMAIL_FROM'], 'Twilio SMS From '.$from, $email_body);

// Done processing, send blank response
print_TwiML();
