<?php
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;
 use PHPMailer\PHPMailer\SMTP;
 use League\OAuth2\Client\Provider\Google;
 
 session_start();
function get_credentials() {
	require_once(__DIR__ . '/../../var.php');
	require_once(CONF_MYSQL);
	require_once(__DIR__ . '/../function_commun.php');

	$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
		
	$sql = "SELECT * FROM notification_config";
	$result_query = mysqli_query($_SESSION["OCS"]["readServer"], $sql);
	$all_credentials = array();
	while ($credentials = mysqli_fetch_array($result_query)) {
		$all_credentials[$credentials['NAME']]['TVALUE'] = $credentials['TVALUE'];
	}

	return $all_credentials;	
}

	
function Send_Email($html) {
	require __DIR__.'/../../vendor/phpmailer/phpmailer/src/Exception.php';
	require __DIR__.'/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
	require __DIR__.'/../../vendor/phpmailer/phpmailer/src/SMTP.php';

	$all_credentials = array();
	$all_credentials = get_credentials();
	$mail = new PHPMailer();

	/* #########################
	 * 	Base Config
	 * #########################
	 */
	$assunto = 'CTIC/CSIC - Monitoramento de Ativos';
	$message = $html;
	$usermail = $all_credentials['NOTIF_MAIL_ADMIN']['TVALUE'];
	$username = $all_credentials['NOTIF_NAME_ADMIN']['TVALUE'];
	$password = $all_credentials['NOTIF_PASSWD_SMTP']['TVALUE'];

	/* Se for do Gmail o servidor é: smtp.gmail.com */
	$host_do_email = $all_credentials['NOTIF_SMTP_HOST']['TVALUE'];

	/* Configura os destinatários  */
	$mail->AddAddress($all_credentials['NOTIF_MAIL_ADMIN']['TVALUE'], $all_credentials['NOTIF_NAME_ADMIN']['TVALUE']);
	// $mail->AddAddress('email@email.com');
	// $mail->AddCC('email@email.com', 'Nome da pessoa'); // Copia
	// $mail->AddBCC('email@email.com', 'Nome da pessoa'); // Cópia Oculta

	/* ###########################
	 * # 	USER CONFIG          # 
	 * ###########################
	 */
	/* Define que é uma conexão SMTP */
	$mail->IsSMTP();
	/* Define o endereço do servidor de envio */
	$mail->Host = $host_do_email;
	/* Utilizar autenticação SMTP */ 
	$mail->SMTPAuth = true;
	/* Protocolo da conexão */
	$mail->SMTPSecure = $all_credentials['NOTIF_SEND_MODE']['TVALUE'];
	/* Porta da conexão */
	$mail->Port = $all_credentials['NOTIF_PORT_SMTP']['TVALUE'];
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
	if ($all_credentials['NOTIF_FOLLOW']['TVALUE'] == 'ON')
		$sended_mail = $mail->Send();

	/* Limpa tudo */
	$mail->ClearAllRecipients();
	$mail->ClearAttachments();

	/* Mostra se o email foi enviado ou não */
	date_default_timezone_set("America/Belem");
	$log_file = fopen(__DIR__ . '/log_file', 'a') or die('Unable to open the log file');
	if ($sended_mail) {
		fwrite($log_file, date('d/m/Y -- H:i:s') . " | The email has been send sucessfully.\n");
		fclose($log_file);
	} else {
		fwrite($log_file, date('d/m/Y == H:i:s') . " | The email hasn't been send with sucess. Please contact the suport\n");
		echo "<b>Error logs:</b> <br />" . $mail->ErrorInfo;
	}

}

