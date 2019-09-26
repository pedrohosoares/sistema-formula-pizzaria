<?
/**
 * Função de envio de e-mail personalizado
 */

function enviaEmail ($email_origem, $email_destino, $assunto, $cod_mensagens, $bcc = '', $debug = false, $con = '',$cod_disparo_mensagens='',$cod_emails_cadastro='')
{
    require_once '../../config.php';
    require_once '../../bd.php';
    require_once '../lib/php/phpmailer/class.phpmailer.php';
    
    if (!$con)
    {
        $con = conectabd();
    }
    
    $objBuscaMensagem = executaBuscaSimples("SELECT * FROM ine_mensagens WHERE cod_mensagens = $cod_mensagens", $con);
    
    //$cor_fundo_email = "83471A";  //laranja
    $cor_fundo_email = "FFFFFF";

    $largura = "600";
    
    /*
    $headers = "MIME-Version: 1.0\n";
    $headers .= "Content-Type: text/html; charset=iso-8859-1\n";
    $headers .= "From: <$email_origem>\n";
    $headers .= "Bcc: $bcc\n";
    $headers .= "Return-Path: <$email_origem>\n";
	*/
    
    $msg_email = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
    $msg_email .= "<html>";
    $msg_email .= '<head><meta http-equiv=Content-Type content="text/html; charset=utf-8"></head>';
    $msg_email .= "<body bgcolor='#" . $cor_fundo_email . "' style='text-align: center; font-family: verdana;'>";
    
		if($objBuscaMensagem->mensagem_avancada==0)
		{
		  $msg_email .= '<div style="background-color: #' . $cor_fundo_email . '; padding: 50px;">';

		  $msg_email .= '<table align="center" width="'.$largura.'" bgcolor="#FFFFFF" bordercolor="#' . $cor_fundo_email . '" cellpadding="0" cellspacing="0" border="0">';
		  
		  if ($objBuscaMensagem->cod_imagens_cabecalho > 0)
		  {
		      $objBuscaImagem = executaBuscaSimples("SELECT * FROM ine_imagens WHERE tipo = 'CABECALHO' AND cod_imagens = " . $objBuscaMensagem->cod_imagens_cabecalho, $con);
		      
		      $msg_email .= '<tr><td><img src="http://' . HOST . '/upload/newsletter/' . $objBuscaImagem->arquivo . '" /></td></tr>';
		  }
		  
		  $msg_email .= '<tr><td align="left">';
		  
		  if ($objBuscaMensagem->cod_imagens_mensagem > 0)
		  {
		      $objBuscaImagem = executaBuscaSimples("SELECT * FROM ine_imagens WHERE tipo = 'IMAGEM' AND cod_imagens = " . $objBuscaMensagem->cod_imagens_mensagem, $con);
		      
		      $msg_email .= '<img src="http://'.HOST.'/upload/newsletter/'.$objBuscaImagem->arquivo.'" />';
		  }
		  
		  if ($objBuscaMensagem->mensagem != '')
		  {
		      $msg_email .= '<font face="verdana" size="2"><br><br>'.bd2texto($objBuscaMensagem->mensagem).'<br><br></font>';
		  }
		  
		  $msg_email .= '</td></tr>';
		  
		  if ($objBuscaMensagem->cod_imagens_rodape > 0)
		  {
		      $objBuscaImagem = executaBuscaSimples("SELECT * FROM ine_imagens WHERE tipo = 'RODAPE' AND cod_imagens = " . $objBuscaMensagem->cod_imagens_rodape, $con);
		      
		      $msg_email .= '<tr><td><img src="http://' . HOST . '/upload/newsletter/' . $objBuscaImagem->arquivo . '" /></td></tr>';
		  }
		  $msg_email .= '</table>';
		}
		else
		{
			$msg_email .= bd2texto($objBuscaMensagem->mensagem);
		}
    $msg_email .= '<br><br>';
    $msg_email .= '<p align="center"><font color="#000000">Nós respeitamos a sua privacidade e somos contra o spam na rede.<br>Este e-mail foi enviado através do sistema de publicidade '.NOME_SITE.', <br>e nenhuma informação cadastral de seus clientes foi repassada a terceiros.<br> Se você deseja cancelar o recebimento dos nossos boletins, <a href="http://' . HOST . '/remover_email_da_newsletter">acesse aqui</a>.</font></p>';

    if($cod_disparo_mensagens!='' && $cod_emails_cadastro!='')
    {
        $msg_email .= '<img src="http://' . HOST . '/imagem.php?cdm='.$cod_disparo_mensagens.'&cec='.$cod_emails_cadastro.'"/>';
    }

    $msg_email .= '</div>';
    
    $msg_email .= "</body>";
    $msg_email .= "</html>";
    
    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->IsSMTP();
    $mail->SMTPDebug = 2;
    $mail->SMTPAuth = true;
    
    //$mail->AddCustomHeader('Errors-To: ' . $email_origem);
    //$mail->AddCustomHeader('Errors-To: contato@internetsistemas.com.br');
    
    $mail->AddCustomHeader('Return-Path: ' . $email_origem);
    $mail->AddCustomHeader('X-Complaints-To: ' . $email_origem);
    $mail->AddCustomHeader('X-Mailer: iPizza - http://internetsistemas.com.br');
    $mail->AddCustomHeader('X-Abuse-Info: ' . HOST);
    $mail->AddCustomHeader('X-ListMember: ' . $email_destino);
    
    /*
    $mail->Host = 'server12.marketingrapido.ws';
    $mail->Port = 25;
    $mail->Username = 'mendesferreira';
    $mail->Password = 'chk180';
	*/

    $mail->Port = 443;
    $mail->Host = "ssl://email-smtp.us-east-1.amazonaws.com";
    $mail->Username = "AKIAJGQ24Z74UZUXQP5A";
    $mail->Password = "AoIWmVRI5vLEDqfErahqFw/DLrAEqQPDRKx2W24H2m2v";
    //Con34@pizza79
    $mail->SetFrom('contato@formulapizzaria.com.br', NOME_SITE);
    $mail->AddAddress($email_destino);
    $mail->Subject = utf8_encode($assunto);
    $mail->AltBody = "Para visualizar esta mensagem utilize um leitor de e-mail compatível com HTML.";
    
    $mail->MsgHTML(utf8_encode($msg_email));
    
    $res = $mail->Send();
    
    if (!$res)
    {
        echo "Mailer Error: " . $mail->ErrorInfo . "<br>";
        $sql_logar_envio = sprintf("INSERT INTO ine_log(cod_mensagens,cod_disparo_mensagens,email_envio,tipo_retorno_envio,observacao,data_hora_envio) values(%d,%d,'%s','%s','%s',NOW())",$cod_mensagens,$cod_disparo_mensagens,$email_destino,'ERRO',$mail->ErrorInfo);
        $res_logar_envio = mysql_query($sql_logar_envio);
    }
    else
    {
        $sql_logar_envio = sprintf("INSERT INTO ine_log(cod_mensagens,cod_disparo_mensagens,email_envio,tipo_retorno_envio,observacao,data_hora_envio) values(%d,%d,'%s','%s','%s',NOW())",$cod_mensagens,$cod_disparo_mensagens,$email_destino,'ENVIADO','');
        $res_logar_envio = mysql_query($sql_logar_envio);
    }
    
    if (!$con)
    {
        desconectabd($con);
    }
    
    return $res;
    //return mail($email_destino, $assunto, $msg_email, $headers);
}
?>
