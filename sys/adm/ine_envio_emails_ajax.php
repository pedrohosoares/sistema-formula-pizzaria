<?php

/**
 * ine_envio_emails_ajax.php: Envio de Mensagens
 * 
 * Índice: cod_mensagens
 * Tabela: ine_mensagens
 */

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$tabela = 'ine_mensagens';
$chave_primaria = 'cod_mensagens';

$cod_mensagens = validaVarPost($chave_primaria);

$con = conectabd();

$objBuscaMensagem = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $cod_mensagens" , $con);

echo '<table align="center" width="600" cellpadding="0" cellspacing="0">';

if($objBuscaMensagem->cod_imagens_cabecalho > 0) {
  $objBuscaImagem = executaBuscaSimples("SELECT * FROM ine_imagens WHERE tipo = 'CABECALHO' AND cod_imagens = ".$objBuscaMensagem->cod_imagens_cabecalho, $con);
   
  echo '<tr><td><center><img src="'.UPLOAD_DIR.'/newsletter/'.$objBuscaImagem->arquivo.'"></center></td></tr><br>';
}

if($objBuscaMensagem->cod_imagens_mensagem > 0) {
  $objBuscaImagem = executaBuscaSimples("SELECT * FROM ine_imagens WHERE tipo = 'IMAGEM' AND cod_imagens = ".$objBuscaMensagem->cod_imagens_mensagem, $con);
  
  echo '<tr><td style="padding: 20px;"><center><img src="'.UPLOAD_DIR.'/newsletter/'.$objBuscaImagem->arquivo.'"></center></td></tr><br>';
}

if($objBuscaMensagem->mensagem != '') {
  echo '<br><tr><td style="padding: 20px;" align="left">'.utf8_encode(bd2texto($objBuscaMensagem->mensagem)).'</td></tr><br>';
}

if($objBuscaMensagem->cod_imagens_rodape > 0) {
  $objBuscaImagem = executaBuscaSimples("SELECT * FROM ine_imagens WHERE tipo = 'RODAPE' AND cod_imagens = ".$objBuscaMensagem->cod_imagens_rodape, $con);
  
  echo '<tr><td><center><img src="'.UPLOAD_DIR.'/newsletter/'.$objBuscaImagem->arquivo.'"></center></td></tr><br>';
}
 
echo '</table>';

desconectabd($con);

?>
