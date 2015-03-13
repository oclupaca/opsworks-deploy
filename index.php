<?php
// header('Content-Type: application/json');

// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';
use Aws\OpsWorks\OpsWorksClient;
use Aws\Common\Credentials\Credentials;

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

$stack_name = "int-lamp";
$app_name = "opsworks_deploy";

$response = array('text' => "");

$credentials = new Credentials(ACCESS_KEY, SECRET_KEY);

$client = OpsWorksClient::factory(array(
	'credentials' => $credentials,
	'region'  => 'us-east-1'
	));


$stacksResult = $client->describeStacks();

$stacks = $stacksResult->get('Stacks');

foreach ($stacks as $stack) {

	if (strtolower($stack['Name']) == strtolower($stack_name)) {

		$appResult = $client->describeApps(array('StackId' => $stack['StackId']));

		$apps = $appResult->get('Apps');

		$response = $apps;
		foreach ($apps as $app) {
			if (strtolower($app['Shortname']) == strtolower($app_name)) {
				// echo $app['Shortname'];
				// $deployResult = $client->createDeployment(array(
				// 	'StackId' => $stack['StackId'],
				// 	'AppId' => $app['AppId'],
				// 	'Command' => array(
				// 		'Name' => 'deploy',
				// 		),
				// 	));
				$response['text'] = "Deploying *" . $app['Name'] . "* \n";
				$response['text'] .= "App Short Name:  " . $app['Shortname'];
			}

		}


	}
}

echo json_encode($response);

?>