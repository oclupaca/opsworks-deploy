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
$app_name = $text_array[0];
$stack_name = $text_array[1];

if (sizeof($text_array) < 2 && $app_name !== 'list') {
	$response['text'] = "Usage: /deploy [app name] [stack name]";
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

if ($app_name == 'list') {
	$response['text'] .= "*Available Stacks / Apps:* \n";
	$response['text'] .= "*---- int-lamp -----* \n";

	$appsResult = $client->describeApps([
	    'StackId' => '2b4ab5c9-f8a8-40f6-9101-f976e3248477',
	]);
	$apps = $appsResult->get('Apps');
	foreach ($apps as $app) {
		$response['text'] .= $app['Shortname'] . "\n";

	}

	$response['text'] .= "*---- prod-lamp ----* \n";

	$appsResult = $client->describeApps([
	    'StackId' => 'e67ae7db-8d36-4adc-894a-7a49bb1cdca6',
	]);
	$apps = $appsResult->get('Apps');
	foreach ($apps as $app) {
		$response['text'] .= $app['Shortname'] . "\n";

	}


	echo json_encode($response);
	die;
}

foreach ($stacks as $stack) {

	if (strtolower($stack['Name']) == strtolower($stack_name)) {

		$stack_found = true;

		$appResult = $client->describeApps(array('StackId' => $stack['StackId']));

		$apps = $appResult->get('Apps');

		foreach ($apps as $app) {
			if (strtolower($app['Shortname']) == strtolower($app_name)) {

				$app_found = true;

				$deployResult = $client->createDeployment(array(
					'StackId' => $stack['StackId'],
					'AppId' => $app['AppId'],
					'Command' => array(
						'Name' => 'deploy',
						),
					));
				$deployment_id = $deployResult->get('DeploymentId');
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