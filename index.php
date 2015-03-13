<?php

// Include local configuration
if (file_exists(dirname(__FILE__) . '/local-config.php')) {
	include(dirname(__FILE__) . '/local-config.php');
}

if (getenv('access_key')) {
	define('ACCESS_KEY', getenv('access_key'));
}
if (getenv('secret_key')) {
	define('SECRET_KEY', getenv('secret_key'));
}
if (getenv('slack_token')) {
	define('SLACK_TOKEN', getenv('slack_token'));
}

// Make sure the token matches
if (!isset($_POST['token']) || $_POST['token'] !== SLACK_TOKEN) {
	echo "nope";
	die;
}

header('Content-Type: application/json');



// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';
use Aws\OpsWorks\OpsWorksClient;
use Aws\Common\Credentials\Credentials;

$response = array('text' => "");

$stack_found = false;
$app_found = false;
$text = $_POST['text'];

$text_array = explode(" ", $text);
$app_name = $text_array[1];
$stack_name = $text_array[2];

if (sizeof($text_array) < 3) {
	$response['text'] = "Usage: deploy [app name] [stack name]";
	echo json_encode($response);
	die;
}


$credentials = new Credentials(ACCESS_KEY, SECRET_KEY);

$client = OpsWorksClient::factory(array(
	'credentials' => $credentials,
	'region'  => 'us-east-1'
	));


$stacksResult = $client->describeStacks();

$stacks = $stacksResult->get('Stacks');

foreach ($stacks as $stack) {

	if (strtolower($stack['Name']) == strtolower($stack_name)) {

		$stack_found = true;

		$appResult = $client->describeApps(array('StackId' => $stack['StackId']));

		$apps = $appResult->get('Apps');

		foreach ($apps as $app) {
			if (strtolower($app['Shortname']) == strtolower($app_name)) {

				$app_found = true;

				// $deployResult = $client->createDeployment(array(
				// 	'StackId' => $stack['StackId'],
				// 	'AppId' => $app['AppId'],
				// 	'Command' => array(
				// 		'Name' => 'deploy',
				// 		),
				// 	));
				// $deployment_id = $deployResult->get('DeploymentId');
				$response['text'] = "Deploying *" . $app['Name'] . "* to *" . $stack['Name'] .  "*\n";
				$response['text'] .= "Deployment ID:  `" . $deployment_id . '`';
			}

		}


	}
}

// if (!$stack_found || !$app_found) {
// 	$response['text'] = "*Deploy Error*: \n";
// }
// if (!$stack_found) {
// 	$response['text'] .=  "The stack `$stack_name` was not found. \n";
// } else {
// 	if (!$app_found) {
// 		$response['text'] .=  "The app `$app_name` was not found. \n";
// 	}

// }

echo json_encode($response);

?>