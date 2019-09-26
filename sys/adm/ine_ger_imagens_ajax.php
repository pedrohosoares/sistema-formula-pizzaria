<?php

/**
 * ine_imagens_ajax.php: Cadastro de imagens
 * 
 * Índice: cod_imagens
 * Tabela: ine_imagens
 */

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$acao = validaVarPost('acao');

$tabela = 'ine_imagens';
$chave_primaria = 'cod_imagens';

switch($acao) {
  case 'excluir_imagem':
    $codigo = validaVarPost($chave_primaria);
    $cod_mensagens = validaVarPost('cod_mensagens');

    if($codigo > 0) {
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
      
      if($objBusca->$chave_primaria > 0) {
        $con = conectar_bd();
        $sql_deletar = "DELETE FROM $tabela WHERE $chave_primaria = $codigo";
        $res_deletar = mysql_query($sql_deletar);
        desconectar_bd($con);
        if($res_deletar)
        {
          if(is_file(UPLOAD_DIR.'/newsletter/'.$cod_mensagens.'/'.$objBusca->arquivo)) 
          {
            if (unlink(UPLOAD_DIR.'/newsletter/'.$cod_mensagens.'/'.$objBusca->arquivo)) 
            {
              $arrJson['status'] = 'OK';
            }
            else 
            {
              $arrJson['status'] = 'ERRO';
            }
          }
          else 
          {
            $arrJson['status'] = 'OK';
          }
        }
        else
        {
          $arrJson['status'] = 'ERRO';
        }
      }
      else {
        $arrJson['status'] = 'ERRO';
      }
      
      echo json_encode($arrJson);
    }
  break;
}

?>
