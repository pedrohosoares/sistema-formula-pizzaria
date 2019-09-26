<?php

  /**
   * Relatório CPV - AJAX
   *
   * @version 1.0
   * @package osmuzzarellas
   * 
   * LISTA DE MODIFICAÇÕES:
   *
   * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
   * ======    ==========   ===========   =============================================================
   *
   * 1.0       17/09/2012   PEDRO H       Criado.
   *
   */

  require_once '../../bd.php';
  require_once '../lib/php/formatacao.php';
  require_once '../lib/php/formulario.php';
  require_once '../lib/php/mensagem.php';

  $acao = validaVarPost('acao');

  if($acao == "carregar_pizzas")
  {
    $codigo = validar_var_post("cod_pizzarias");
    $cod_pizzas = validar_var_post("cod_pizzas");
    echo '<td class="legenda tdbl " align="right"><label for="cod_pizza">Pizza:</label></td>';
    echo '<td class=" ">&nbsp;</td>';
    echo '<td class="tdbr  ">';
    echo '  <select name="cod_pizzas" id="cod_pizzas">';
    echo '    <option value="">&nbsp;</option>';
      $con = conectabd();
      $SqlBuscaPizzas = "SELECT DISTINCT(ip.cod_pizzas), ip.pizza FROM ipi_pizzas ip INNER JOIN ipi_pizzas_ipi_tamanhos ipit ON (ip.cod_pizzas = ipit.cod_pizzas) WHERE ipit.cod_pizzarias IN ('$codigo') ORDER BY pizza";
      $resBuscaPizzas = mysql_query($SqlBuscaPizzas);
      //echo $SqlBuscaPizzas;
      while ($objBuscaPizzas = mysql_fetch_object($resBuscaPizzas)) 
      {
        echo '<option value="'.$objBuscaPizzas->cod_pizzas.'" ';
        if($objBuscaPizzas->cod_pizzas == $cod_pizzas)
        {
          echo 'selected';
        }
        echo '>'.utf8_encode(bd2texto($objBuscaPizzas->pizza)).'</option>';
      }
    echo '  </select>';
    echo '</td>';

  }
  ?>