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

$tabela = 'ipi_combos';
$chave_primaria = 'cod_combos';

switch($acao) {
  case 'excluir_imagem':
    $codigo = validaVarPost($chave_primaria);
    $tipo = validaVarPost('tipo');
    if ($tipo=="fundo")
    {
        $campo_foto = "imagem_fundo";
    }
    elseif ($tipo=="banner")
    {
        $campo_foto = "imagem_p";
    }
    
    if($codigo > 0) {
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
      
      if($objBusca->$chave_primaria > 0) {
        
        if(is_file(UPLOAD_DIR.'/combos/'.$objBusca->$campo_foto)) 
        {
          if (unlink(UPLOAD_DIR.'/combos/'.$objBusca->$campo_foto)) 
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
      else {
        $arrJson['status'] = 'ERRO';
      }
      
      echo json_encode($arrJson);
    }
  break;
}

?>