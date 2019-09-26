<?php

/**
 * iba_bordas_ajax.php: Exclusão de imagens das bordas
 * 
 * Índice: cod_bordas
 * Tabela: ipi_bordas
 */

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

$tabela = 'ipi_bordas';
$chave_primaria = 'cod_bordas';

switch($acao) {
  case 'excluir_imagem_pequena':
    $codigo = validaVarPost($chave_primaria);
    
    if($codigo > 0) 
    {
      $conexao = conectar_bd();
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo", $conexao);
      
      $arr_json['status'] = UPLOAD_DIR.'/bordas/'.$objBusca->foto_pequena;
      
      $sql_del_image = "UPDATE $tabela SET foto_pequena = '' WHERE $chave_primaria = $codigo";    
      $res_del_image = mysql_query($sql_del_image);  
      if($objBusca->$chave_primaria > 0) {
        if(is_file(UPLOAD_DIR.'/bordas/'.$objBusca->foto_pequena)) {
          if (unlink(UPLOAD_DIR.'/bordas/'.$objBusca->foto_pequena)) {
            $arr_json['status'] = 'OK';
          }
          else {
            $arr_json['status'] = 'ERRO';
          }
        }
        else {
          $arr_json['status'] = 'OK';
        }
      }
      else 
      {
        $arr_json['status'] = 'ERRO';
      }
      desconectar_bd($conexao);
    }
    else
    {
      $arr_json['status'] = 'ERRO';
    }
      echo json_encode($arr_json);
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
        if(is_file(UPLOAD_DIR.'/bordas/'.$objBusca->foto_grande)) {
          if (unlink(UPLOAD_DIR.'/bordas/'.$objBusca->foto_grande)) {
            $arr_json['status'] = 'OK';
          }
          else {
            $arr_json['status'] = 'ERRO';
          }
        }
        else {
          $arr_json['status'] = 'OK';
        }
      }
      else 
      {
        $arr_json['status'] = 'ERRO';
      }
      desconectar_bd($conexao);
    }
    else
    {
        $arrJson['status'] = 'ERRO';
    }
      echo json_encode($arr_json);
  break;
}

?>
