<?php

/**
 * ipi_pizzarias_ajax.php: Exclusão de imagens das Pizzarias
 * 
 * Índice: cod_pizzarias
 * Tabela: ipi_pizzarias
 */

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');
$tabela = 'ipi_pizzarias';
$chave_primaria = 'cod_pizzarias';

switch($acao) {

  case 'excluir_imagem':  
    $codigo = validaVarPost($chave_primaria);
    if($codigo > 0) 
    {
      $conexao = conectar_bd();
      $objBuscaArquivo = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo LIMIT 1", $conexao);
      $SqlDelImagem = "UPDATE $tabela SET foto_grande = '' WHERE $chave_primaria = $codigo";
      $res_del_imagem = mysql_query($SqlDelImagem);
      if(file_exists(UPLOAD_DIR."/pizzarias/".$objBuscaArquivo->foto_grande)) 
      {
        if (unlink(UPLOAD_DIR."/pizzarias/".$objBuscaArquivo->foto_grande)) 
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
      echo json_encode($arrJson);
      desconectar_bd($conexao);
    }
  break;

  case 'excluir_imagem_pequena':  
    $codigo = validaVarPost($chave_primaria);
    if($codigo > 0) 
    {
      $conexao = conectar_bd();
      $objBuscaArquivo = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo LIMIT 1", $conexao);
      $SqlDelImagem = "UPDATE $tabela SET foto_pequena = '' WHERE $chave_primaria = $codigo";
      $res_del_imagem = mysql_query($SqlDelImagem);
      if(file_exists(UPLOAD_DIR."/pizzarias/".$objBuscaArquivo->foto_grande)) 
      {
        if (unlink(UPLOAD_DIR."/pizzarias/".$objBuscaArquivo->foto_grande)) 
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
      echo json_encode($arrJson);
      desconectar_bd($conexao);
    }
  break;
}
?>
