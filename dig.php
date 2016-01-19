<?php
header('Content-Type: application/json');

if (isset($_POST['text'])) {
	$text = $_POST['text'];

	$text_array = explode(" ", $text);
	if (count($text_array) < 1) {
		$response['text'] = "Usage: dig [domain name]";
		echo json_encode($response);
		die;
	}
	$domain_name = $text_array[0];
} else {
	$domain_name = $_GET['domain'];
}


$tlds = array('com', 'org', 'net', 'ag', 'coop', 'farm');

$domain_name = str_replace("<http:\/\/", "", $domain_name);
$domain_name = str_replace(">", "", $domain_name);
$domain_name = str_replace("<", "", $domain_name);
if (strpos($domain_name, "|") !== FALSE) {
	$domain_name = substr($domain_name, 0, strpos($domain_name, "|"));
}

// make the domain name into a full url
if (strpos($domain_name, 'http') !== 0) {
    $domain_name = "http://" . $domain_name;
}


// parse the url into peices
$domain_name_parsed = parse_url($domain_name);

// get the full domain name (sans protocol and path)
$full_domain = $domain_name_parsed['host'];

// $response['text'] = "```" . $full_domain . "```";
// echo json_encode($response);
// die;

// Get the apex domain
$host_parts = explode('.', $domain_name_parsed['host']);
$host_parts = array_reverse($host_parts);
$apex_domain = $full_domain;
if(array_search($host_parts[1] . '.' . $host_parts[0], $tlds) !== FALSE) {
  $apex_domain = $host_parts[2] . '.' . $host_parts[1] . '.' . $host_parts[0];
} elseif(array_search($host_parts[0], $tlds) !== FALSE) {
  $apex_domain = $host_parts[1] . '.' . $host_parts[0];
}



$text = "";
$name_servers = "";
$other_records = "";

$ns_records = dns_get_record($apex_domain, DNS_NS);

$text .= "*Name Servers* for $apex_domain: \n";
foreach ($ns_records as $record) {
	if ($record['type'] == 'NS') {
		if ( strpos($record['target'], 'awsdns') !== FALSE) {
			$name_server_company = "AWS";
		}
		if ( strpos($record['target'], 'datawareservices') !== FALSE) {
			$name_server_company = "Dataware";
		}
		if ( strpos($record['target'], 'omnitech') !== FALSE) {
			$name_server_company = "Dataware";
		}
		if ( strpos($record['target'], 'domaincontrol') !== FALSE) {
			$name_server_company = "GoDaddy";
		}
		if ( strpos($record['target'], 'worldnic') !== FALSE) {
			$name_server_company = "Network Solutions";
		}
		$name_servers .= "`" . $record['target'] . "`\n";
	}
}
if (isset($name_server_company) && $name_server_company) {
	$text .= "Looks like DNS is hosted at *" . $name_server_company . "* \n";
}
$text .= "```" . $name_servers . "```\n";

$all_records = dns_get_record($full_domain, DNS_ALL);
// print_r($all_records);

$text .= "*Other Records* for $full_domain: \n";
foreach ($all_records as $record) {
	if ($record['type'] == 'A') {
		$other_records .= $record['type'] . " " . $record['ttl'] . " " . $record['ip'] . "\n";
	} else if ($record['type'] !== 'SOA' && $record['type'] !== 'NS' && $record['type'] !== 'NAPTR' && $record['type'] !== 'TXT') {
		$other_records .= $record['type'] . " " . $record['ttl'] . " " . $record['target'] . "\n";
	}
}
foreach ($all_records as $record) {
	if ($record['type'] == 'TXT') {
		$other_records .= $record['type'] . " " . $record['ttl'] . " " . $record['txt'] . "\n";
	}
}
$text .= "```" . $other_records . "```\n";

// print_r($all_records);
// echo $text;

$response = array('text' => $text);
echo json_encode($response);
?>



