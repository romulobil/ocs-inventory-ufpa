<?php
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;
 use PHPMailer\PHPMailer\SMTP;


function Send_Email($html) {
	require __DIR__.'/../../vendor/phpmailer/phpmailer/src/Exception.php';
	require __DIR__.'/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
	require __DIR__.'/../../vendor/phpmailer/phpmailer/src/SMTP.php';

	$config_mail = parse_ini_file(__DIR__ . '/mail.ini');
	$mail = new PHPMailer();

	/* #########################
	 * # CONFIGURAÇÕES BÁSICAS # 
	 * #########################
	 */
	$assunto = 'Teste de email de ativos';
	$message = $html;
	$usermail = $config_mail['usermail'];
	$username = $config_mail['username'];
	$password = $config_mail['password'];

	/* Se for do Gmail o servidor é: smtp.gmail.com */
	$host_do_email = 'smtp.gmail.com';

	/* Configura os destinatários  */
	$mail->AddAddress($config_mail['receivermail'], $config_mail['receiveruser']);
	// $mail->AddAddress('email@email.com');
	// $mail->AddCC('email@email.com', 'Nome da pessoa'); // Copia
	// $mail->AddBCC('email@email.com', 'Nome da pessoa'); // Cópia Oculta

	/* ###########################
	 * # CONFIGURAÇÕES AVANÇADAS # 
	 * ###########################
	 */
					
	/* Define que é uma conexão SMTP */
	$mail->IsSMTP();
	/* Define o endereço do servidor de envio */
	$mail->Host = $host_do_email;
	/* Utilizar autenticação SMTP */ 
	$mail->SMTPAuth = true;
	/* Protocolo da conexão */
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	/* Porta da conexão */
	$mail->Port = "587";
	/* Email ou usuário para autenticação */
	$mail->Username = $usermail;
	/* Senha do usuário */
	$mail->Password = $password;
	$mail->SMTPDebug = SMTP::DEBUG_SERVER;

	/* Configura os dados do remetente do email */
	$mail->From = $usermail; // Seu e-mail
	$mail->FromName = $username; // Seu nome

	/* Configura a mensagem */
	$mail->IsHTML(true); // Configura um e-mail em HTML

	/*   
	 * Se tiver problemas com acentos, modifique o charset
	 * para ISO-8859-1  
	 */
	$mail->CharSet = 'ISO-8859-1'; // Charset da mensagem (opcional)

	/* Configura o texto e assunto */
	$mail->Subject  = $assunto; // Assunto da mensagem
	$mail->Body = $message; // A mensagem em HTML
	$mail->AltBody = trim(strip_tags($message)); // A mesma mensagem em texto puro

	/* Configura o anexo a ser enviado (se tiver um) */
	//$mail->AddAttachment("foto.jpg", "foto.jpg");  // Insere um anexo

	/* Envia o email */
	$sended_mail = $mail->Send();

	/* Limpa tudo */
	$mail->ClearAllRecipients();
	$mail->ClearAttachments();

	/* Mostra se o email foi enviado ou não */
	if ($sended_mail) {
		echo "Email has been send!";
	} else {
		echo "Don't be possible send this mail.<br /><br />";
		echo "<b>Error logs:</b> <br />" . $mail->ErrorInfo;
	}

}
?>
