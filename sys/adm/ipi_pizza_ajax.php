<?php

/**
 * iba_pizza_ajax.php: Cadastro de Pizzas
 * 
 * Índice: cod_pizzas
 * Tabela: ipi_pizzas
 */

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

$tabela = 'ipi_pizzas';
$chave_primaria = 'cod_pizzas';

switch($acao) {
  case 'excluir_imagem_pequena':
    $codigo = validaVarPost($chave_primaria);

    if($codigo > 0) 
    {
      $conexao = conectar_bd();
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo", $conexao);
      $sql_del_image = "UPDATE $tabela SET foto_pequena = '' WHERE $chave_primaria = $codigo";    
      $res_del_image = mysql_query($sql_del_image);  
      if($objBusca->$chave_primaria > 0) {
        if(is_file(UPLOAD_DIR.'/pizzas/'.$objBusca->foto_pequena)) {
          if (unlink(UPLOAD_DIR.'/pizzas/'.$objBusca->foto_pequena)) {
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
  
  case 'excluir_imagem':
    $codigo = validaVarPost($chave_primaria);

    if($codigo > 0) 
    {
      $conexao = conectar_bd();
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo", $conexao);
      $sql_del_image = "UPDATE $tabela SET foto_grande = '' WHERE $chave_primaria = $codigo";    
      $res_del_image = mysql_query($sql_del_image);  
      if($objBusca->$chave_primaria > 0) {
        if(is_file(UPLOAD_DIR.'/pizzas/'.$objBusca->foto_grande)) {
          if (unlink(UPLOAD_DIR.'/pizzas/'.$objBusca->foto_grande)) {
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
