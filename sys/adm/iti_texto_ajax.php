<?php

/**
 * iti_texto_ajax.php: Cadastro de Texto
 * 
 * Índice: cod_textos
 * Tabela: iti_textos
 */

require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validar_var_post('acao');

$tabela = 'iti_textos';
$chave_primaria = 'cod_textos';

switch($acao) {
  case 'excluir_imagem':
    $codigo = validar_var_post($chave_primaria);
    if ($codigo > 0)
    {
        $objBusca = executar_busca_simples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
        
        if ($objBusca->$chave_primaria > 0) 
        {
            if (is_file(UPLOAD_DIR.'/imagens_iti/'.$objBusca->imagem_pna)) 
            {
                if (unlink(UPLOAD_DIR.'/imagens_iti/'.$objBusca->imagem_pna)) 
                {
                    if (unlink(UPLOAD_DIR.'/imagens_iti/'.$objBusca->imagem_gde))
                    {
                        $arrJson['status'] = 'OK';
                    }    
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
      
      echo json_encode($arrJson);
    }
  break;
}

?>