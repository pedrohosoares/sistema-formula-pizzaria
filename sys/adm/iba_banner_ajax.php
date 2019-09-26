<?php

/**
 * iba_banner_ajax.php: Cadastro de Banners
 * 
 * Índice: cod_banners
 * Tabela: iba_banners
 */

require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

$tabela = 'iba_banners';
$chave_primaria = 'cod_banners';

switch($acao) {
  case 'excluir_imagem':
    $codigo = validaVarPost($chave_primaria);

    if($codigo > 0) {
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
      
      if($objBusca->$chave_primaria > 0) {
        if(is_file(UPLOAD_DIR.'/banners/'.$objBusca->imagem)) {
          if (unlink(UPLOAD_DIR.'/banners/'.$objBusca->imagem)) {
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
      else {
        $arrJson['status'] = 'ERRO';
      }
      
      echo json_encode($arrJson);
    }
  break;
}

?>