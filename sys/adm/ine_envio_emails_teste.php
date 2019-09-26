<?php

/**
 * ine_envio_emails_teste.php: Envio de E-Mails Teste
 * 
 * Índice: cod_mensagens
 * Tabela: ine_mensagens
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Envio de E-Mails Teste');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_mensagens';
$tabela = 'ine_mensagens';
$codigo_usuario = $_SESSION['usuario']['codigo'];
if($acao == 'enviar') 
{
  require_once 'ine_email.php';
  
  $email_origem = EMAIL_PRINCIPAL;
  $email_destino = validaVarPost('email');
  $cod_mensagens =  validaVarPost($chave_primaria);

  $con = conectabd();
  $sqlMensagem = "SELECT * FROM ine_mensagens WHERE cod_mensagens='".$cod_mensagens."'";
  $resMensagem = mysql_query($sqlMensagem);
  $objMensagem = mysql_fetch_object($resMensagem);
  

  $sql_logar_envio = sprintf("INSERT INTO ine_log(cod_mensagens,cod_usuario_enviador,email_envio,tipo_retorno_envio, observacao, data_hora_envio) values(%d,%d,'%s','%s','%s',NOW())",$cod_mensagens,$codigo_usuario,$email_destino,'MENSAGEM_TESTE','');
  //echo $sql_logar_envio;
  $res_logar_envio = mysql_query($sql_logar_envio);

  $assunto = "Mensagem Teste - ".$objMensagem->assunto;
  $envia = enviaEmail($email_origem, $email_destino, $assunto, $cod_mensagens);
  desconectabd($con);


  if ($envia)
    mensagemOk('E-mail teste enviado com êxito!');
  else
    mensagemErro('Erro ao enviar o e-mail teste', 'Por favor, contacte a equipe de suporte e informe a mensagem enviada.');
}

?>

<script>  
function mostrar_mensagem(obj) {
  var url = 'cod_mensagens=' + obj.value;

  if(obj.value > 0) {
    $('carrega_mensagem').setStyle('border', '1px solid #3768AD');
  }
  else {
    $('carrega_mensagem').setStyle('border', 'none');
  }

  new Request.HTML({
    url: 'ine_envio_emails_teste_ajax.php',
    update: $('carrega_mensagem')
  }).send(url);
}

function validarForm(envio) {

 if (envio.cod_mensagens.value == "") {
    alert('Campo Mensagem Requerido.');
    frm.cod_mensagens.focus();
    return false;
  	}
  	
  if (frm.email.value == "") {
    alert('Campo E-Mail Requerido.');
    frm.email.focus();
    return false;
  	}
  	
  return true; 
}
</script>

<form id="envio" name="envio" method="post" action="<? echo $PHP_SELF; ?>" onsubmit="return validarForm(this);">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_mensagens">Mensagem</label></td></tr>
  <tr><td class="tdbl tdbr sep">
    <select class="requerido" name="cod_mensagens" id="cod_mensagens" onchange="mostrar_mensagem(this)" >
      <option value=""></option>
      
      <?
      $con = conectabd();
      
      $sqlMensagem = "SELECT * FROM ine_mensagens";
      $resMensagem = mysql_query($sqlMensagem);
  
      while ($objMensagem = mysql_fetch_object($resMensagem)) {
        echo '<option value="'.$objMensagem->$chave_primaria.'">'.$objMensagem->assunto.'</option>';
      }
      
      desconectabd($con);
      ?>
    </select>
  </td></tr>

  <tr><td class="legenda tdbl tdbr"><label class="requerido" for="email">E-mail de Teste</label></td></tr>
  <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="email" id="email" maxlength="45" size="50"></td></tr>
  
  <tr><td align="center" class="tdbl tdbr sep"><input name="botao_submit" class="botao" type="submit" value="Enviar E-mail Teste"></td></tr>
  
  <tr><td class="tdbl tdbb tdbr sep" align="center" id="carrega_mensagem" style="padding: 20px;"></td></tr>
</table>

<input type="hidden" name="acao" value="enviar"/>

</form>

<br><br>

<? rodape(); ?>