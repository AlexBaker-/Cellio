<?php
define('CELLIO', true);
define('LIBPATH', realpath(dirname(__FILE__).'/..').'/lib');
require_once LIBPATH.'/init.php';

// Variables, assemble!
$time = gmdate('r');
$req = array_change_key_case($_REQUEST, CASE_UPPER);
$recipientSID = $req['ACCOUNTSID'];

$vmnum_recv = $req['TO'];
$vmnum_from = $req['FROM'];
$vm_url = $req['RECORDINGURL'].'.mp3';
$vm_len = $req['RECORDINGDURATION'].' second'.($req['RECORDINGDURATION'] == 1 ? '' : 's');
$geo = gen_geo_output($req, 'post');

$email_to = $config['ACCOUNTS'][$recipientSID]['EMAIL'];
$email_subject = 'Twilio Voicemail From '.$vmnum_from;
$email_body = "Voicemail received to ${vmnum_recv}\n\nTime: ${time}\n\nFrom: ${vmnum_from}\n\n${geo}Voicemail Length: ${vm_len}\n\nVoicemail URL:";

// Use either Gearman or non-asynchronous fallback
if ($config['OPTS']['USE_GEARMAN']) {
  $gmc = new GearmanClient();
  $gmc->addServers($config['OPTS']['GEARMAN_SERVERS']);
  $email_body .= "\n\n{ATTACHMENTS}";
  $res = $gmc->doBackground('fetch_media', json_encode(array(
    'SID'	=> $recipientSID,
    'MEDIA'	=> array(
      'NUMMEDIA'	  => 1,
      'MEDIAURL0'	  => $vm_url,
      'MEDIACONTENTTYPE0' => 'audio/mpeg',
    ),
    'MAIL'	=> array(
      'TO'		=> $email_to,
      'SUBJECT'		=> $email_subject,
      'BODY'		=> $email_body,
    ),
  )));
} else {
  $purl = parse_url($vm_url);
  $url = unparse_url($purl, $config['ACCOUNTS'][$recipientSID]['APISID'], $config['ACCOUNTS'][$recipientSID]['APISECRET']);
  send_email($email_to, $config['OPTS']['EMAIL_FROM'], $email_subject, $email_body." ${url}");
}

// Done processing, send hangup response
print_TwiML(array(array(
  'name' => 'Hangup',
  'value' => '',
)));
