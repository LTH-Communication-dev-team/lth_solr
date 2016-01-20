<?php
require_once( '../vendor/solr/Service.php' );

// 
// Try to connect to the named server, port, and url
// 
$solr = new Apache_Solr_Service( 'www2.lth.se', '8080', '/solr/kronos' );

if ( ! $solr->ping() ) {
  echo 'Solr service not responding.';
  exit;
}

// prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
 
// get what user typed in autocomplete input
$term = trim($_GET['term']);
 
$a_json = array();
$a_json_row = array();
 
$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "Only letters and digits are permitted..."));
$json_invalid = json_encode($a_json_invalid);
 
// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);
 
// SECURITY HOLE ***************************************************************
// allow space, any unicode letter and digit, underscore and dash
if(preg_match("/[^\040\pL\pN_-]/u", $term)) {
  print $json_invalid;
  exit;
}
// *****************************************************************************
 
// Run some queries. Provide the raw path, a starting offset
//   for result documents, and the maximum number of result
//   documents to return. You can also use a fourth parameter
//   to control how results are sorted and highlighted, 
//   among other options.
//
$offset = 0;
$limit = 10;

$queries = array(
    "first_name:$term* last_name:$term* text:$term"
);

$parts = explode(' ', $term);

// 

foreach ( $queries as $query ) {
    $response = $solr->search( $query, $offset, $limit );

    if ( $response->getHttpStatus() == 200 ) { 
	// print_r( $response->getRawResponse() );

	if ( $response->response->numFound > 0 ) {
		//echo "$query <br />";

	    foreach ( $response->response->docs as $doc ) { 
		//echo "$doc->partno $doc->name <br />";
		$a_json_row["id"] = $doc->id;
		$a_json_row["value"] = $doc->title . $doc->first_name . ' ' . $doc->last_name;
		$a_json_row["label"] = $doc->title . $doc->first_name . ' ' . $doc->last_name;
		array_push($a_json, $a_json_row);
	    }
	    
	    // highlight search results
	    //$a_json = apply_highlight($a_json, $parts);
	    
	    $json = json_encode($a_json);
	    print $json;
	}
    }
    else {
	echo $response->getHttpStatusMessage();
    }
}
  
  /**
   * while($row = $rs->fetch_assoc()) {
  $a_json_row["id"] = $row['url'];
  $a_json_row["value"] = $row['post_title'];
  $a_json_row["label"] = $row['post_title'];
  array_push($a_json, $a_json_row);
}
 
// highlight search results
$a_json = apply_highlight($a_json, $parts);
 
$json = json_encode($a_json);
print $json;
 * mb_stripos all occurences
 * based on http://www.php.net/manual/en/function.strpos.php#87061
 *
 * Find all occurrences of a needle in a haystack
 *
 * @param string $haystack
 * @param string $needle
 * @return array or false
 */
function mb_stripos_all($haystack, $needle) {
 
  $s = 0;
  $i = 0;
 
  while(is_integer($i)) {
 
    $i = mb_stripos($haystack, $needle, $s);
 
    if(is_integer($i)) {
      $aStrPos[] = $i;
      $s = $i + mb_strlen($needle);
    }
  }
 
  if(isset($aStrPos)) {
    return $aStrPos;
  } else {
    return false;
  }
}
 
/**
 * Apply highlight to row label
 *
 * @param string $a_json json data
 * @param array $parts strings to search
 * @return array
 */
function apply_highlight($a_json, $parts) {
 
  $p = count($parts);
  $rows = count($a_json);
 
  for($row = 0; $row < $rows; $row++) {
 
    $label = $a_json[$row]["label"];
    $a_label_match = array();
 
    for($i = 0; $i < $p; $i++) {
 
      $part_len = mb_strlen($parts[$i]);
      $a_match_start = mb_stripos_all($label, $parts[$i]);
 
      foreach($a_match_start as $part_pos) {
 
        $overlap = false;
        foreach($a_label_match as $pos => $len) {
          if($part_pos - $pos >= 0 && $part_pos - $pos < $len) {
            $overlap = true;
            break;
          }
        }
        if(!$overlap) {
          $a_label_match[$part_pos] = $part_len;
        }
 
      }
 
    }
 
    if(count($a_label_match) > 0) {
      ksort($a_label_match);
 
      $label_highlight = '';
      $start = 0;
      $label_len = mb_strlen($label);
 
      foreach($a_label_match as $pos => $len) {
        if($pos - $start > 0) {
          $no_highlight = mb_substr($label, $start, $pos - $start);
          $label_highlight .= $no_highlight;
        }
        $highlight = '<span class="hl_results">' . mb_substr($label, $pos, $len) . '</span>';
        $label_highlight .= $highlight;
        $start = $pos + $len;
      }
 
      if($label_len - $start > 0) {
        $no_highlight = mb_substr($label, $start);
        $label_highlight .= $no_highlight;
      }
 
      $a_json[$row]["label"] = $label_highlight;
    }
 
  }
 
  return $a_json;
 
}