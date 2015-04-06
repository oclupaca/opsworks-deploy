<!DOCTYPE html>
<html>
<head>
	<title>Cloudwatch Status</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
</head>
<body>

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





// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';
use Aws\CloudWatch\CloudWatchClient;

use Aws\Common\Credentials\Credentials;

$credentials = new Credentials(ACCESS_KEY, SECRET_KEY);

$client = CloudWatchClient::factory(array(
	'credentials' => $credentials,
	'region'  => 'us-east-1'
	));


$alarmsResult = $client->describeAlarms();

$alarms = $alarmsResult->get('MetricAlarms');

?>


<div class="container">
	<div class="page-header">
		<h1>CloudWatch Alarm Status</h1>
	</div>
	<table class="table table-condensed">
		<tbody>
			<?php foreach ($alarms as $alarm) : ?>
				<?php
					if ($alarm['StateValue'] == "OK") {
						$textClass = "text-success";
					} else {
						$rowClass = "danger";
						$textClass = "text-danger";
					}
				?>
				<tr class="<?php echo $rowClass ?>">
					<td><strong class="<?php echo $textClass ?>"><?php echo $alarm['StateValue'] ?></strong></td>
					<td><?php echo $alarm['AlarmName'] ?></td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>


	<pre><?php print_r($alarms);?></pre>
</div>

</body>
</html>