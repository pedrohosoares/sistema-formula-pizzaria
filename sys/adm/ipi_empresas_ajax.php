<?php

/**
 * iba_pizza_ajax.php: Cadastro de Pizzas
 * 
 * Índice: cod_empresas
 * Tabela: ipi_empresas
 */

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

$tabela = 'ipi_empresas';
$chave_primaria = 'cod_empresas';


switch($acao) {
  case 'excluir_logo_pequeno':
    $codigo = validaVarPost($chave_primaria);

    if($codigo > 0) 
    {
      $conexao = conectar_bd();
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo", $conexao);
      $sql_del_image = "UPDATE $tabela SET logo_pequeno = '' WHERE $chave_primaria = $codigo";    
      $res_del_image = mysql_query($sql_del_image);  
      if($objBusca->$chave_primaria > 0) {
        if(is_file(UPLOAD_DIR.'/empresas/'.$objBusca->logo_pequeno)) {
          if (unlink(UPLOAD_DIR.'/empresas/'.$objBusca->logo_pequeno)) {
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
  
  case 'excluir_logo_medio':
    $codigo = validaVarPost($chave_primaria);

    if($codigo > 0) 
    {
      $conexao = conectar_bd();
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo", $conexao);
      $sql_del_image = "UPDATE $tabela SET logo_medio = '' WHERE $chave_primaria = $codigo";    
      $res_del_image = mysql_query($sql_del_image);  
      if($objBusca->$chave_primaria > 0) {
        if(is_file(UPLOAD_DIR.'/pizzas/'.$objBusca->logo_medio)) {
          if (unlink(UPLOAD_DIR.'/pizzas/'.$objBusca->logo_medio)) {
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

  case 'excluir_logo_grande':
    $codigo = validaVarPost($chave_primaria);

    if($codigo > 0) 
    {
      $conexao = conectar_bd();
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo", $conexao);
      $sql_del_image = "UPDATE $tabela SET logo_grande = '' WHERE $chave_primaria = $codigo";    
      $res_del_image = mysql_query($sql_del_image);  
      if($objBusca->$chave_primaria > 0) {
        if(is_file(UPLOAD_DIR.'/pizzas/'.$objBusca->logo_grande)) {
          if (unlink(UPLOAD_DIR.'/pizzas/'.$objBusca->logo_grande)) {
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