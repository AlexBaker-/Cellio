<?php
if (!defined('TWILIO')) die('Error: This file cannot be called directly.');

$config = array();

$config['OPTS'] = array(

	// URL to the scripts (NO trailing slash)
	'URL' => 'http://twilio.domain.com',

	// From email
	'EMAIL_FROM' => 'twilio@domain.com',

	// Play voicemail beep prompt (needs to be literal true/false string)
	'VOICEMAIL_BEEP' => 'true',

	// Maximum allowable length of voicemails (in seconds)
	'VOICEMAIL_LEN' => 35,

	// Maximum allowed silence (in seconds)
	'VOICEMAIL_TIMEOUT' => 3,

	// Message to say if silence timeout reached
	'VOICEMAIL_TIMEOUT_MSG' => 'No audio detected. Message not recorded.',

	// Trim silent portions (values: trim-silence, do-not-trim)
	'VOICEMAIL_TRIM' => 'trim-silence',

);

$config['ACCOUNTS'] = array(

	'TWILIO_ACCOUNT_SID' => array(
		'APISID' => 'API SID - Generated in TaskRouter',
		'APISECRET' => 'API Secret - Generated in TaskRouter',
		'LOCALUSER' => 'username',
		'LOCALPASS' => 'password hash usable with PHP crypt() function',
		'EMAIL' => 'email@domain.com',
		'VOICEMAIL_PROMPT' => 'Leave a message after the tone. Press any key or hang up when finished.',
	),

);

