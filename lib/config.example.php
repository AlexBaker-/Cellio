<?php
if (!defined('CELLIO')) die('Error: This file cannot be called directly.');

$config = array();

$config['OPTS'] = array(

	// URL to the scripts (NO trailing slash)
	'URL' => 'http://twilio.domain.com',

	// Use Gearman to process emails and fetch media
	'USE_GEARMAN' => true,

	// Comma-separated list of Server:Port,Server2:Port,... to connect to.
	// Leave blank for localhost default.
	'GEARMAN_SERVERS' => '',

	// Local path where to store fetched media
	// - Only used with Gearman enabled
	// - NO trailing slash
	// - MUST be writable by www user
	'MEDIA_PATH' => '/PATH/TO/Cellio/media',

	// URI to 'MEDIA_PATH', added to 'URL' (NO FQDN)
	// - Only used with Gearman enabled
	'MEDIA_URI' => '/media.php?f=',

	// HTTP Digest realm
	// - All local user hashes MUST be regenerated if this changes!
	'DIGEST_REALM' => 'Cellio Digest Realm',

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
		'EMAIL' => 'email@domain.com',
		'VOICEMAIL_PROMPT' => 'Leave a message after the tone. Press any key or hang up when finished.',
	),

);

// Group of MD5 hashes for authenticated users allowed to access
// media.php .  Each hash must be "user:realm:password" with 'realm'
// matching the DIGEST_REALM setting above.  You can generate these
// at a command line prompt like so:
//  $ php -r 'echo md5("user:realm:password");'
$config['LOCALUSERS'] = array(
	'MD5-HASH1',
	'MD5-HASH2',
);