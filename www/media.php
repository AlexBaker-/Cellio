<?php
define('CELLIO', true);
define('LIBPATH', realpath(dirname(__FILE__).'/..').'/lib');
require_once LIBPATH.'/init.php';

if (!isset($_GET['f'])) {
  header('Location: '.$config['OPTS']['URL']);
  exit;
}

ignore_user_abort(true);
set_time_limit(0);

// Prepare filename and path
$dl_file = preg_replace("([^\w\s\d\-_~,;:\[\]\(\].]|[\.]{2,})", '', $_GET['f']);
$dl_file = filter_var($dl_file, FILTER_SANITIZE_URL);
$filepath = $config['OPTS']['MEDIA_PATH'].'/'.$dl_file;

// If file exists, prompt for auth digest
if (file_exists($filepath)) {
  if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="'.$config['OPTS']['DIGEST_REALM'].
           '",qop="auth",nonce="'.md5(uniqid()).'",opaque="'.md5($config['OPTS']['DIGEST_REALM'].time()).'"');
    die('Error: You must authenticate to see this file.');
  }
} else {
  die('Error: That file does not exist.');
}

// Process a logout request (sending another 401 after one was already processed *sometimes* flushes the browser's cache... sometimes.)
if (isset($_GET['logout'])) {
  $getvals = $_GET;
  unset($getvals['logout']);
  header('HTTP/1.1 401 Unauthorized');
  die('Logged out.  <a href="'.strtok($_SERVER['REQUEST_URI'], '?').'?'.http_build_query($getvals).'">Click here</a> to authenticate.  (DO NOT refresh this page!)');
}

// Validate digest data
if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST']))) {
  die('Error: Invalid authentication digest.');
}

// Validate authentication
$authsuccess = 0;
foreach ($config['LOCALUSERS'] as $userhash) {
  $endhash = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
  $valid_response = md5($userhash.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$endhash);
  if ($data['response'] == $valid_response) {
    $authsuccess = 1;
    break;
  }
}
if (!$authsuccess) {
  die('Error: Username/password combination is invalid.  <a href="'.$_SERVER['REQUEST_URI'].'&logout=true">Logout</a>, then try again.');
}

// All good, present the file
if ($fd = fopen ($filepath, 'r')) {
  $fsize = filesize($filepath);
  $path_parts = pathinfo($filepath);
  $mime = mime_to_ext($path_parts['extension'], true);
  header('Content-type: '.$mime);
  header('Content-Disposition: '.(isset($_GET['dl']) ? 'attachment; ' : '').'filename="'.$path_parts['basename'].'"');
  header('Content-length: '.$fsize);
  header('Cache-control: private');
  while(!feof($fd)) {
    $buffer = fread($fd, 2048);
    print $buffer;
  }
}
fclose($fd);
exit;

// Parse and validate HTTP digest
function http_digest_parse($txt) {
  $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
  $data = array();
  $keys = implode('|', array_keys($needed_parts));
  preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);
  foreach ($matches as $m) {
    $data[$m[1]] = $m[3] ? $m[3] : $m[4];
    unset($needed_parts[$m[1]]);
  }
  return $needed_parts ? false : $data;
}
