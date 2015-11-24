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
$voicemail_url = $req['RECORDINGURL'].'.mp3';
$voicemail_len = $req['RECORDINGDURATION'];
$geo = gen_geo_output($req, 'post');

// Email recipient
$recipient = $config['ACCOUNTS'][$recipientSID]['EMAIL'];
$email_body = "Voicemail received to $receiver\n\nTime: $time\n\nFrom: $from\n\n${geo}Voicemail Length: $voicemail_len seconds\n\nVoicemail URL: $voicemail_url";
send_email($recipient, $config['OPTS']['EMAIL_FROM'], 'Twilio Voicemail From '.$from, $email_body);

// Done processing, send hangup response
print_TwiML(array(array(
	'name' => 'Hangup',
	'value' => '',
)));
