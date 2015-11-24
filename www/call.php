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
$geo = gen_geo_output($req, 'pre');

// Email recipient
$recipient = $config['ACCOUNTS'][$recipientSID]['EMAIL'];
$email_body = "Call received to $receiver\n\nTime: $time\n\nFrom: ${from}${geo}";
send_email($recipient, $config['OPTS']['EMAIL_FROM'], 'Twilio Call From '.$from, $email_body);

// Craft voicemail request
$data = array(
	array(
		'name' => 'Say',
		'value' => $config['ACCOUNTS'][$recipientSID]['VOICEMAIL_PROMPT'],
	),
	array(
		'name' => 'Record',
		'attributes' => array(
			'action' => $config['OPTS']['URL'].'/voicemail.php',
			'timeout' => $config['OPTS']['VOICEMAIL_TIMEOUT'],
			'maxLength' => $config['OPTS']['VOICEMAIL_LEN'],
			'trim' => $config['OPTS']['VOICEMAIL_TRIM'],
			'playBeep' => $config['OPTS']['VOICEMAIL_BEEP'],
		),
	),
	array(
		'name' => 'Say',
		'value' => $config['OPTS']['VOICEMAIL_TIMEOUT_MSG'],
	),
);

// Tell Twilio to capture a voicemail
print_TwiML($data);
