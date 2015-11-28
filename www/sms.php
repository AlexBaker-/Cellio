<?php
define('CELLIO', true);
define('LIBPATH', realpath(dirname(__FILE__).'/..').'/lib');
require_once LIBPATH.'/init.php';

// Variables, assemble!
$time = gmdate('r');
$req = array_change_key_case($_REQUEST, CASE_UPPER);
$recipientSID = $req['ACCOUNTSID'];

$smsnum_recv = $req['TO'];
$smsnum_sender = $req['FROM'];
$sms_message = $req['BODY'];
$geo = gen_geo_output($req, 'post');

$email_to = $config['ACCOUNTS'][$recipientSID]['EMAIL'];
$email_subject = 'Twilio SMS From '.$smsnum_sender;
$email_body = "SMS received to ${smsnum_recv}\n\nTime: ${time}\n\nFrom: ${smsnum_sender}\n\n${geo}Message: ${sms_message}";

// Use either Gearman or non-asynchronous fallback
if ($config['OPTS']['USE_GEARMAN']) {
  $gmc = new GearmanClient();
  $gmc->addServers($config['OPTS']['GEARMAN_SERVERS']);
  // If attachments, spawn background download, else spawn background email
  if ($req['NUMMEDIA'] > 0) {
    $email_body .= "\n\nAttachments:\n\n{ATTACHMENTS}";
    $res = $gmc->doBackground('fetch_media', json_encode(array(
      'SID'	=> $recipientSID,
      'MEDIA'	=> $req,
      'MAIL'	=> array(
        'TO'		=> $email_to,
        'SUBJECT'	=> $email_subject,
        'BODY'		=> $email_body,
      ),
    )));
  } else {
    $res = $gmc->doBackground('send_email', json_encode(array(
      'TO'	=> $email_to,
      'SUBJECT'	=> $email_subject,
      'BODY'	=> $email_body,
    )));
  }
} else {
  // MMS attachments
  $attachments = '';
  if ($req['NUMMEDIA'] > 0) {
    $attachments = "\n\nAttachments:\n";
    foreach ($req as $k => $v) {
      if (substr($k,0,8) == 'MEDIAURL') {
        $purl = parse_url($v);
        $url = unparse_url($purl, $config['ACCOUNTS'][$recipientSID]['APISID'], $config['ACCOUNTS'][$recipientSID]['APISECRET']);
        $attachments .= "* $url\n";
      }
    }
  }
  $email_body .= $attachments;
  // Email recipient
  send_email($email_to, $config['OPTS']['EMAIL_FROM'], $email_subject, $email_body);
}

// Done processing, send blank response
print_TwiML();
