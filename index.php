<?php
header('Content-Type: application/json');
$data = array('text' => 'this is from the server');
echo json_encode($data);
?>
