<?php
require __DIR__ . '/Components_Notification.php';
require_once( __DIR__ . '/send.php');

	$obj = new Components_Notification();
	$obj->get_memories();
	$obj->get_monitors();
	$obj->get_videos();
	$obj->get_storages();	

	$body_mail = $obj->html_part_addition . $obj->html_part_remove;
	if ($body_mail != '')
		Send_Email($body_mail);
	else 
		echo "Nada a enviar :(" . "\n";	
?>
