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

switch($acao) 
{
  case 'carregar_tamanhos':

    $cod_pizzas = validaVarPost('cod_pizzas');
		$cod_pizzarias = validaVarPost('cod_pizzarias');

    $cone = conectabd();
    $sql_busca_pizza = "SELECT t.cod_tamanhos, t.tamanho FROM ipi_pizzas_ipi_tamanhos pt LEFT JOIN ipi_tamanhos t ON (pt.cod_tamanhos = t.cod_tamanhos) WHERE pt.cod_pizzarias='".$cod_pizzarias."' and pt.cod_pizzas='".$cod_pizzas."' ORDER BY t.tamanho"; 
    $res_busca_pizza = mysql_query($sql_busca_pizza);
    echo '<option value=""></option>';
    while($obj_busca_pizza = mysql_fetch_object($res_busca_pizza))
    {
      echo utf8_encode('<option value='.$obj_busca_pizza->cod_tamanhos.'>'.$obj_busca_pizza->tamanho.'</option>');
    }
    desconectabd($cone);

  break;
}

?>
