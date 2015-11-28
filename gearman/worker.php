<?php
define('CELLIO', true);
define('LIBPATH', realpath(dirname(__FILE__).'/..').'/lib');
require_once LIBPATH.'/init.php';

gc_enable();

$gmw = new GearmanWorker();
$gmw->addServer();

$gmw->addFunction('send_email', function(GearmanJob $job) use (&$config) {
  $work = json_decode($job->workload());
  $mail = send_email($work->TO, $config['OPTS']['EMAIL_FROM'], $work->SUBJECT, $work->BODY);
  // Do some garbage collection and return results
  unset($work);
  gc_collect_cycles();
  return $mail;
});

$gmw->addFunction('fetch_media', function(GearmanJob $job) use (&$config) {
  $work = json_decode($job->workload());
  // This could be done using curl_multi, but we don't need parallelism
  // here since it's already a background task.
  $attachments = '';
  $fails = 0;
  for ($i=0; $i<$work->MEDIA->NUMMEDIA; $i++) {
    $purl = parse_url($work->MEDIA->{'MEDIAURL'.$i});
    $url = unparse_url($purl, $config['ACCOUNTS'][$work->SID]['APISID'], $config['ACCOUNTS'][$work->SID]['APISECRET']);
    $filename = time().'-'.sprintf('%u', crc32($url)).'.'.mime_to_ext($work->MEDIA->{'MEDIACONTENTTYPE'.$i});
    // Fetch media item and write to file
    $ch = curl_init();
    $fp = fopen($config['OPTS']['MEDIA_PATH'].'/'.$filename, 'w');
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    // Twilio media URLs are api.twilio.com, but they have redirects to Amazon AWS
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $ex = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    // Delete the file if there was an error, and bump the fail count
    if ($ex === FALSE) {
      $fails++;
      unlink($config['OPTS']['MEDIA_PATH'].'/'.$filename);
      $attachments .= '* Failed to download attachment #'.($i+1).", download from Twilio directly.\n";
    } else {
      $attachments .= '* '.$config['OPTS']['URL'].$config['OPTS']['MEDIA_URI'].$filename."\n";
    }
  }
  $message = str_replace('{ATTACHMENTS}', $attachments, $work->MAIL->BODY);
  $mail = send_email($work->MAIL->TO, $config['OPTS']['EMAIL_FROM'], $work->MAIL->SUBJECT, $message);
  // Do some garbage collection and return results
  unset($work, $attachments, $message);
  gc_collect_cycles();
  return ($fails == 0 && $mail);
});

while ($gmw->work());
