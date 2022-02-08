<?php

// Support the methods to collect hardware informations
require_once __DIR__ . '/ComponentsNotification.php';
// Support the functions to send an email
require_once __DIR__ . '/send.php';

	$mastermind = new ComponentsNotification();
	$mastermind->get_memories();
	$mastermind->get_monitors();
	$mastermind->get_videos();
	$mastermind->get_storages();	
	$mastermind->get_cpus();	
	$mastermind->update_id_assets();
	// html_part_addition e html_part_remove are public attributes of ComponentesNotification
	$body_mail = $mastermind->html_part_addition . $mastermind->html_part_remove;
	if ($body_mail != '')
		Send_Email($body_mail);
	else 
		echo "Nada a enviar :(" . "\n";	
	
