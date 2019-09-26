<?php

/**
 * iba_pizza_ajax.php: Cadastro de Arquivos para Promoções em pizzarias
 * 
 * Índice: cod_promoções && cod_pizzarias
 * Tabela: ipi_promoções_ipi_pizzarias
 */

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

$tabela = 'ipi_promocoes_ipi_pizzarias';
$chave_primaria = 'cod_promocoes';

switch($acao) {
  case 'excluir_arquivo':
    $codigo = validaVarPost($chave_primaria);
		$cod_pizzarias = validaVarPost($chave_primaria);
    if($codigo > 0 && $cod_pizzarias > 0) 
    {
      $conexao = conectar_bd();
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo and cod_pizzarias = $cod_pizzarias", $conexao);
      $sql_del_image = "UPDATE $tabela SET arquivo = '' WHERE $chave_primaria = $codigo and cod_pizzarias = $cod_pizzarias";    
      $res_del_image = mysql_query($sql_del_image);  
      if($objBusca->$chave_primaria > 0) {
        if(is_file(UPLOAD_DIR.'/promocoes/'.$objBusca->arquivo)) {
          if (unlink(UPLOAD_DIR.'/promocoes/'.$objBusca->arquivo)) {
            $arrJson['status'] = 'OK';
          }
          else {
            $arrJson['status'] = 'ERRO';
          }
        }
        else {
          $arrJson['status'] = 'OK';
        }
      }
      else 
      {
        $arrJson['status'] = 'ERRO';
      }
      desconectar_bd($conexao);
    }
    else
    {
        $arrJson['status'] = 'ERRO';
    }
      echo json_encode($arrJson);
  break;
}

?>
