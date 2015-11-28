<?php
define('CELLIO', true);
define('LIBPATH', realpath(dirname(__FILE__).'/..').'/lib');
require_once LIBPATH.'/init.php';

// Variables, assemble!
$time = gmdate('r');
$req = array_change_key_case($_REQUEST, CASE_UPPER);
$recipientSID = $req['ACCOUNTSID'];

$callnum_recv = $req['TO'];
$callnum_from = $req['FROM'];
$geo = gen_geo_output($req, 'pre');

$email_to = $config['ACCOUNTS'][$recipientSID]['EMAIL'];
$email_subject = 'Twilio Call From '.$callnum_from;
$email_body = "Call received to ${callnum_recv}\n\nTime: ${time}\n\nFrom: ${callnum_from}${geo}";

// Use either Gearman or non-asynchronous fallback
if ($config['OPTS']['USE_GEARMAN']) {
  $gmc = new GearmanClient();
  $gmc->addServers($config['OPTS']['GEARMAN_SERVERS']);
  $res = $gmc->doBackground('send_email', json_encode(array(
    'TO'	=> $email_to,
    'SUBJECT'	=> $email_subject,
    'BODY'	=> $email_body,
  )));
} else {
  send_email($email_to, $config['OPTS']['EMAIL_FROM'], $email_subject, $email_body);
}

// Craft voicemail request
$twiml = array(
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
print_TwiML($twiml);
