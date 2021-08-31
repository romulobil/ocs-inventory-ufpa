<?php

// Prover os métodos de coleta de informações de Hardware para notificação
require_once __DIR__ . '/ComponentsNotification.php';
// Prover a função para realizar o envio de e-mail 
require_once __DIR__ . '/send.php';

	$obj = new ComponentsNotification();
	$obj->get_memories();
	$obj->get_monitors();
	$obj->get_videos();
	$obj->get_storages();	
	$obj->get_cpus();	

	// html_part_addition e html_part_remove são partes integrantes de ComponentsNotification
	$body_mail = $obj->html_part_addition . $obj->html_part_remove;
	if ($body_mail != '')
		Send_Email($body_mail);
	else 
		echo "Nada a enviar :(" . "\n";	
?>
