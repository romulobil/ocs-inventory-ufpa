<?php

// Prover os métodos de coleta de informações de Hardware para notificação
require_once __DIR__ . '/ComponentsNotification.php';
// Prover a função para realizar o envio de e-mail 
require_once __DIR__ . '/send.php';

	$mastermind = new ComponentsNotification();
	$mastermind->get_memories();
	$mastermind->get_monitors();
	$mastermind->get_videos();
	$mastermind->get_disks();	
	$mastermind->get_cpus();	
	$mastermind->update_id_assets();
	// html_part_addition e html_part_remove são partes integrantes de ComponentsNotification
	$body_mail = $mastermind->html_part_addition . $mastermind->html_part_remove;
	if ($body_mail != '')
		Send_Email($body_mail);
	else 
		echo "Nada a enviar :(" . "\n";	

