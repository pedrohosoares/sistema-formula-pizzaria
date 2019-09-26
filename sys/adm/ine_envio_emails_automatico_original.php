<?
set_time_limit (0);

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/phpmailer/class.phpmailer.php';

$con = conectabd();

// OBS: O Job está na dreamhost e roda a cada 10 minutos
// Limite atual da Amazon AWS - 100000 - 28 emails/seg , deixar folga para os envios do site, tenho usado folga de 10Mil

//$limite_envio = 15;
$limite_envio = 900; // Fazer a conta do limite atual dividido por 24horas dividido por 6 (o disparo na dreamhost de 10 em 10 min, dá 6 por hora)
$taxa_emails_por_segundo = 26; // Velocidade máxima por segundo do envio ver limite na aws, deixar folga para os envios do site.

$numEmail = 0;



//echo "<br>limite_envio: ".$limite_envio;

$sqlEmail = "SELECT cm.cod_mensagens, c.cod_emails_cadastro, c.email,cm.agendamento,cm.cod_disparo_mensagens FROM ine_emails_cadastro_ine_mensagens cm INNER JOIN ine_emails_cadastro c ON (cm.cod_emails_cadastro = c.cod_emails_cadastro) WHERE c.ativo = 1 AND cm.situacao='NOVO' AND cm.agendamento <= NOW() ORDER BY RAND()";
$resEmail = mysql_query($sqlEmail);
$numEmail = mysql_num_rows($resEmail);
//echo "<br>sqlEmail: ".$sqlEmail;

if($numEmail<$limite_envio)
{
  $limite_envio = $numEmail;
}
/*
echo "<br/><br/><hr><br/>";
    echo "Inicio :".date("H:i:s");
    echo "<br/<hr><br/>><br/>";
*/
$mail = new PHPMailer();
$mail->CharSet = 'UTF-8';
$mail->IsSMTP();
$mail->SMTPDebug = 0;
$mail->SMTPAuth = true;
$mail->SMTPKeepAlive = true;
$mail->AddCustomHeader('Return-Path: ' . $email_origem);
$mail->AddCustomHeader('X-Complaints-To: ' . $email_origem);
$mail->AddCustomHeader('X-Mailer: iPizza - http://internetsistemas.com.br');
$mail->AddCustomHeader('X-Abuse-Info: ' . HOST);
$mail->AddCustomHeader('X-ListMember: ' . $email_destino);
$mail->Port = 443;
$mail->Host = "ssl://email-smtp.us-east-1.amazonaws.com";
$mail->Username = "AKIAJGQ24Z74UZUXQP5A";
$mail->Password = "AoIWmVRI5vLEDqfErahqFw/DLrAEqQPDRKx2W24H2m2v";
$mail->SetFrom('contato@formulapizzaria.com.br', 'Formula Pizzaria');
$mail->AltBody = "Para visualizar esta mensagem utilize um leitor de e-mail compatível com HTML.";

$SqlRecebimento = "";
$SqlDelete = "";
$sql_logar_envio = "";
$sql_erro = "";

$cod_mensagens_anterior = '';
for ($i = 1; $i <= $limite_envio; $i++)
{
  $objEmail = mysql_fetch_object($resEmail);

  $email_destino = $objEmail->email;
  $email_origem = EMAIL_PRINCIPAL;
  $cod_mensagens = $objEmail->cod_mensagens;
  $data_agendamento = $objEmail->agendamento;

  $cod_disparo_mensagens =$objEmail->cod_disparo_mensagens;
  $cod_emails_cadastro = $objEmail->cod_emails_cadastro;
  if($cod_mensagens!=$cod_mensagens_anterior)
  {
    $objBuscaMensagem = executaBuscaSimples("SELECT * FROM ine_mensagens WHERE cod_mensagens = $cod_mensagens", $con);
    $cod_mensagens_anterior = $cod_mensagens;
    $assunto = bd2texto($objBuscaMensagem->assunto);
  
    $cor_fundo_email = "FFFFFF";

    $largura = "600";
    $mail->Subject = utf8_encode($assunto);
  
  
    $msg_email_raw = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
    $msg_email_raw .= "<html>";
    $msg_email_raw .= '<head><meta http-equiv=Content-Type content="text/html; charset=utf-8"></head>';
    $msg_email_raw .= "<body bgcolor='#" . $cor_fundo_email . "' style='text-align: center; font-family: verdana;'>";
    
    if($objBuscaMensagem->mensagem_avancada==0)
    {
      $msg_email_raw .= '<div style="background-color: #' . $cor_fundo_email . '; padding: 50px;">';

      $msg_email_raw .= '<table align="center" width="'.$largura.'" bgcolor="#FFFFFF" bordercolor="#' . $cor_fundo_email . '" cellpadding="0" cellspacing="0" border="0">';
      
      if ($objBuscaMensagem->cod_imagens_cabecalho > 0)
      {
          $objBuscaImagem = executaBuscaSimples("SELECT * FROM ine_imagens WHERE tipo = 'CABECALHO' AND cod_imagens = " . $objBuscaMensagem->cod_imagens_cabecalho, $con);
          
          $msg_email_raw .= '<tr><td><img src="http://' . HOST . '/upload/newsletter/' . $objBuscaImagem->arquivo . '" /></td></tr>';
      }
      
      $msg_email_raw .= '<tr><td align="left">';
      
      if ($objBuscaMensagem->cod_imagens_mensagem > 0)
      {
          $objBuscaImagem = executaBuscaSimples("SELECT * FROM ine_imagens WHERE tipo = 'IMAGEM' AND cod_imagens = " . $objBuscaMensagem->cod_imagens_mensagem, $con);
          
          $msg_email_raw .= '<img src="http://'.HOST.'/upload/newsletter/'.$objBuscaImagem->arquivo.'" />';
      }
      
      if ($objBuscaMensagem->mensagem != '')
      {
          $msg_email_raw .= '<font face="verdana" size="2"><br><br>'.bd2texto($objBuscaMensagem->mensagem).'<br><br></font>';
      }
      
      $msg_email_raw .= '</td></tr>';
      
      if ($objBuscaMensagem->cod_imagens_rodape > 0)
      {
          $objBuscaImagem = executaBuscaSimples("SELECT * FROM ine_imagens WHERE tipo = 'RODAPE' AND cod_imagens = " . $objBuscaMensagem->cod_imagens_rodape, $con);
          
          $msg_email_raw .= '<tr><td><img src="http://' . HOST . '/upload/newsletter/' . $objBuscaImagem->arquivo . '" /></td></tr>';
      }
      $msg_email_raw .= '</table>';
    }
    else
    {
      $msg_email_raw .= bd2texto($objBuscaMensagem->mensagem);
    }
    $msg_email_raw .= '<br><br>';
    $msg_email_raw .= '<p align="center"><font color="#000000">Nós respeitamos a sua privacidade e somos contra o spam na rede.<br>Este e-mail foi enviado através do sistema de publicidade Os Muzzarellas, <br>e nenhuma informação cadastral de seus clientes foi repassada a terceiros.<br> Se você deseja cancelar o recebimento dos nossos boletins, <a href="http://' . HOST . '/remover_email_da_newsletter">acesse aqui</a>.</font></p>';

  }
  $msg_email_enviar = '';
  if($cod_disparo_mensagens!='' && $cod_emails_cadastro!='')
  {
      $msg_email_enviar .= '<img src="http://' . HOST . '/imagem.php?cdm='.$cod_disparo_mensagens.'&cec='.$cod_emails_cadastro.'"/>';
      //$msg_email .= '<img src="http://192.168.0.100/~is01/osmuzzarellas/fontes/site/imagem.php?cdm='.$cod_disparo_mensagens.'&cec='.$cod_emails_cadastro.'"/>';
  }

  $msg_email_enviar .= '</div>';
  
  $msg_email_enviar .= "</body>";
  $msg_email_enviar .= "</html>";

  $mail->MsgHTML(utf8_encode($msg_email_raw.$msg_email_enviar));

  $mail->AddAddress($email_destino);
  

  
  //if (enviaEmail($email_origem, $email_destino, $assunto, $cod_mensagens, '', false, $con,$cod_disparo_mensagens,$cod_emails_cadastro))
  $res = $mail->Send();
  if($res)
  {
    
    $SqlRecebimento .= "UPDATE ine_emails_cadastro SET recebimentos = recebimentos + 1, ultimo_recebimento = '".date("Y-m-d H:i:s")."' WHERE cod_emails_cadastro=" . $objEmail->cod_emails_cadastro.";";
    
    
    $SqlDelete .= "DELETE FROM ine_emails_cadastro_ine_mensagens WHERE cod_mensagens = '$objEmail->cod_mensagens' AND cod_emails_cadastro = " . $objEmail->cod_emails_cadastro." AND cod_mensagens = ".$cod_mensagens." AND agendamento = '".$data_agendamento."';";
    

    $sql_logar_envio .= sprintf("INSERT INTO ine_log(cod_mensagens,cod_disparo_mensagens,email_envio,tipo_retorno_envio,observacao,data_hora_envio) values(%d,%d,'%s','%s','%s','".date("Y-m-d H:i:s")."');",$cod_mensagens,$cod_disparo_mensagens,$email_destino,'ENVIADO','');
    
  }
  else
  {
    //echo "Mailer Error: " . $mail->ErrorInfo . "<br>";
    $sql_erro .= "UPDATE ine_emails_cadastro_ine_mensagens SET situacao='ERRO' WHERE cod_mensagens = '$objEmail->cod_mensagens' AND cod_emails_cadastro = " . $objEmail->cod_emails_cadastro ." AND cod_mensagens = ".$cod_mensagens." AND agendamento = '".$data_agendamento."';";
    


    $sql_logar_envio .= sprintf("INSERT INTO ine_log(cod_mensagens,cod_disparo_mensagens,email_envio,tipo_retorno_envio,observacao,data_hora_envio) values(%d,%d,'%s','%s','%s','".date("Y-m-d H:i:s")."');",$cod_mensagens,$cod_disparo_mensagens,$email_destino,'ERRO',$mail->ErrorInfo);
    
  }
  $mail->ClearAddresses();
  if ( ($i%$taxa_emails_por_segundo==0) && ($i!=0))
  {
      // O AWS suporta apenas X emails por segundo...
      //usleep(100000);
      //echo "<br>###";
  }
}
$mail->SmtpClose();
  /*
    echo "<br/><br/><hr><br/>";
    echo "Inicio Logs :".date("H:i:s");
    echo "<br/<hr><br/>><br/>";

    echo "$SqlRecebimento <br/><br/> $SqlDelete <br/><br/> $sql_logar_envio<br/><br/> $sql_erro</br/><br/>";
*/
    if($SqlRecebimento!="")
    {
      foreach (explode(";",$SqlRecebimento) as $query ) {
        $resRecebimento = mysql_query($query);
      }
      
    }

    if($SqlDelete!="")
    {
      foreach (explode(";",$SqlDelete) as $query ) {
        $resDelete = mysql_query($query);
      }
      //$resDelete = mysql_query($SqlDelete);
    }

    if($sql_logar_envio!="")
    {
      foreach (explode(";",$sql_logar_envio) as $query ) {
        $res_logar_envio = mysql_query($query);
      }
      //$res_logar_envio = mysql_query($sql_logar_envio);
    }

    if($sql_erro!="")
    {
      foreach (explode(";",$sql_erro) as $query ) {
        $res_erro = mysql_query($query);
      }
     // $res_erro = mysql_query($sql_erro);
    }
    //$res_logar_envio = mysql_query($sql_logar_envio);
/*
    echo "<br/><br/><hr><br/>";
    echo "Fim :".date("H:i:s");
    echo "<br/<hr><br/>><br/>";

*/
desconectabd($con);
?>
