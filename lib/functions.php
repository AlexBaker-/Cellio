<?php
if (!defined('CELLIO')) die('Error: This file cannot be called directly.');

// Send email alert
function send_email($to, $from, $subject, $body) {
  $headers = "From: $from";
  return mail($to, $subject, $body, $headers);
}


// Generate geograpical information output
function gen_geo_output($request, $newlines='') {
  $geo = '';
  if ($request['FROMCITY'] || $request['FROMSTATE'] || $request['FROMZIP'] || $request['FROMCOUNTRY']) {
    $city = '* City: '.($request['FROMCITY'] ? $request['FROMCITY'] : '-');
    $state = '* State: '.($request['FROMSTATE'] ? $request['FROMSTATE'] : '-');
    $zip = '* ZIP: '.($request['FROMZIP'] ? $request['FROMZIP'] : '-');
    $country = '* Country: '.($request['FROMCOUNTRY'] ? $request['FROMCOUNTRY'] : '-');
    $geo  = ($newlines == 'pre' || $newlines == 'both' ? "\n\n" : '');
    $geo .= "Geographical Information:\n$city\n$state\n$zip\n$country";
    $geo .= ($newlines == 'post' || $newlines == 'both' ? "\n\n" : '');
  }
  return $geo;
}


// Generate media/voicemail URL with API login info
function unparse_url($parsed_url, $user, $pass) {
  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : $user;
  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : $pass;
  $pass     = ($user || $pass) ? ":$pass@" : '';
  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
  return "$scheme$user$pass$host$port$path$query$fragment";
}


// MIME types supported by Twilio
// Source: https://www.twilio.com/docs/api/rest/accepted-mime-types
function mime_to_ext($str, $ext_to_mime=false) {
  $mimes = array(
    'audio/basic' => 'au',
    'audio/L24' => 'l24',
    'audio/mp4' => 'm4a',
    'audio/mpeg' => 'mp3',
    'audio/ogg' => 'ogg',
    'audio/vorbis' => 'vob',
    'audio/vnd.rn-realaudio' => 'ra',
    'audio/vnd.wave' => 'wav',
    'audio/3gpp' => 'a3gp',
    'audio/3gpp2' => 'a3g2',
    'audio/ac3' => 'ac3',
    'audio/webm' => 'awebm',
    'audio/amr-nb' => 'amrn',
    'audio/amr' => 'amr',
    'video/mpeg' => 'mpg',
    'video/mp4' => 'mp4',
    'video/quicktime' => 'mov',
    'video/webm' => 'webm',
    'video/3gpp' => '3gp',
    'video/3gpp2' => '3g2',
    'video/3gpp-tt' => '3gt',
    'video/H261' => 'h261',
    'video/H263' => 'h263',
    'video/H263-1998' => 'h26398',
    'video/H263-2000' => 'h26300',
    'video/H264' => 'h264',
    'image/jpeg' => 'jpg',
    'image/gif' => 'gif',
    'image/png' => 'png',
    'image/bmp' => 'bmp',
    'text/vcard' => 'vcf',
    'text/csv' => 'csv',
    'text/rtf' => 'rtf',
    'text/richtext' => 'rtx',
    'text/calendar' => 'ics',
    'application/pdf' => 'pdf',
  );
  if ($ext_to_mime) {
    $key = array_search($str, $mimes);
    return ($key ? $key : 'application/octet-stream');
  } else {
    return (array_key_exists($str, $mimes) ? $mimes[$str] : 'xxx');
  }
}


// Print TwiML XML
function print_TwiML($data=array()) {
  if (!is_array($data)) $data = array();
  $data['name'] = 'Response';
  $data['value'] = "\n";

  $dom = new DOMDocument('1.0', 'utf-8');
  $child = generate_xml_element($dom, $data);
  if ($child) $dom->appendChild($child);

  header('Content-type: text/xml');
  echo $dom->saveXML();
  return;
}


// Generate XML child objects
// Source: http://www.viper007bond.com/2011/06/29/easily-create-xml-in-php-using-a-data-array/
function generate_xml_element($dom, $data) {
  if (empty( $data['name'])) return false;

  // Create the element
  $element_value = (!empty($data['value'])) ? $data['value'] : null;
  $element = $dom->createElement($data['name'], $element_value);

  // Add any attributes
  if (!empty($data['attributes']) && is_array($data['attributes'])) {
    foreach ($data['attributes'] as $attribute_key => $attribute_value) {
      $element->setAttribute($attribute_key, $attribute_value);
    }
  }

  // Recursively iterate through any other items in the data array
  foreach ($data as $data_key => $child_data) {
    if (!is_numeric($data_key)) continue;
    $child = generate_xml_element($dom, $child_data);
    if ($child) $element->appendChild($child);
  }

  return $element;
}
