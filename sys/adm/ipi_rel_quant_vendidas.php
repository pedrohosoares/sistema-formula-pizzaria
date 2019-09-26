<?php

/**
 * ipi_rel_quant_vendidas.php: Relatório de Quantidades Vendidas
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Quantidades Vendidas');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>

<script type="text/javascript" src="../lib/js/calendario.js"></script>

<script>

function imprimir_quant() {
  var cod_pizzarias = document.frmFiltro.cod_pizzarias.value;
  var data_inicial = document.frmFiltro.data_inicial.value;
  var data_final = document.frmFiltro.data_final.value;
  
  var url = 'ipi_rel_quant_vendidas_impressao.php?cod_pizzarias=' + cod_pizzarias + '&data_inicial=' + data_inicial + '&data_final=' + data_final;
  window.open(url, 'impressao', 'width=700,height=500,resisable=yes,scrollbars=yes');
}

window.addEvent('domready', function() {
  new vlaDatePicker('data_inicial', {prefillDate: false});
  new vlaDatePicker('data_final', {prefillDate: false});
});

</script>

<form name="frmFiltro" method="post">

  <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    <tr>
      <td width="150">
        <select name="cod_pizzarias" style="width: 150px;">
          <option value="TODOS">Todas <? echo ucfirst(TIPO_EMPRESAS)?></option>
          <?
          $con = conectabd();
          
          $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY nome";
          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
          while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
            echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
            
            if(validaVarPost('cod_pizzarias') == $objBuscaPizzarias->cod_pizzarias)
              echo 'selected';
            
            echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
          }
          
          desconectabd($con);
          ?>
        </select>
      </td>
      <td width="140">
        <label for="data_inicial">Data Inicial:</label>
        <input type="text" name="data_inicial" id="data_inicial" size="8" value="<? echo (validaVarPost('data_inicial') != '') ? validaVarPost('data_inicial') : date('d/m/Y') ?>">
      </td>
      <td width="135">
        <label for="data_final">Data Final:</label>
        <input type="text" name="data_final" id="data_final" size="8" value="<? echo (validaVarPost('data_final') != '') ? validaVarPost('data_final') : date('d/m/Y') ?>">
      </td>
      <td width="150">
        <select name="cod_motivo_promocoes" style="width: 150px;">
          <option value="TODOS">Todas Promoções</option>
          <?
          $con = conectabd();
          
          $SqlBuscaPizzarias = "SELECT * FROM ipi_motivo_promocoes ORDER BY cod_motivo_promocoes";
          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
          while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
            echo '<option value="'.$objBuscaPizzarias->cod_motivo_promocoes.'" ';
            
            if(validaVarPost('cod_motivo_promocoes') == $objBuscaPizzarias->cod_motivo_promocoes)
              echo 'selected';
            
            echo '>'.bd2texto($objBuscaPizzarias->motivo_promocao).'</option>';
          }
          
          desconectabd($con);
          ?>
        </select>
      </td>
      <td width="40"><input class="botaoAzul" type="submit" value="Filtrar"></td>
      <td><input class="botaoAzul" type="button" value="Imprimir" onclick="imprimir_quant()"></td>
    </tr>
  </table>

  <br><br>
  
  <? 
  
  $con = conectabd(); 
  $data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
  $data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
  $cod_pizzarias = validaVarPost('cod_pizzarias');
  $cod_motivo_promocoes = validaVarPost('cod_motivo_promocoes');
  require_once 'ipi_req_quant_vendidas.php';
  imprime_quantidade_vendidas($data_inicial, $data_final, $cod_pizzarias, 0, $con,'NET',$cod_motivo_promocoes);//O ORIGEM PEDIDOS NÃO É USADO, APESAR DE ESTAR SENDO REQUERIDO COMO PARAMETRO
  
  desconectabd($con);
  ?>
  
  <br><br>
  
</form>

<? rodape(); ?>
