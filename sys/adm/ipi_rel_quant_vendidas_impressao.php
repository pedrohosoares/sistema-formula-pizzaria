<?php
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$data_inicial = validaVarGet('data_inicial');
$data_final = validaVarGet('data_final');
$cod_pizzarias = validaVarGet('cod_pizzarias');

if($cod_pizzarias > 0) {
  $objBuscaPizzarias = executaBuscaSimples("SELECT nome FROM ipi_pizzarias WHERE cod_pizzarias = $cod_pizzarias");
  $nome_pizzaria = $objBuscaPizzarias->nome;
}
else {
  $nome_pizzaria = 'Todas Pizzarias';  
}

?>

<html>
<head><link rel="stylesheet" type="text/css" media="screen" href="../lib/css/principal.css"/></head>
<body style="background: none !important; padding: 30px;">

<table width="100%">

<tr>
<td width="100" rowspan="4" valign="top" align="center"><img src="../../img/logomuzza_relatorio.gif"></td>
<td style="font-size: 14px; padding-bottom: 10px;" align="center"><b>Relatório de Quantidade Vendida</b></td>
</tr>

<tr>
  <td><b>Pizzaria:</b> <? echo $nome_pizzaria; ?></td>
</tr>

<tr>
  <td><b>Data Inicial:</b> <? echo $data_inicial ?></td>
</tr>

<tr>
  <td><b>Data Final:</b> <? echo $data_final ?></td>
</tr>


</table>

<br><br>

<?
$con = conectabd();

$data_inicial = ($data_inicial != '') ? data2bd($data_inicial) : date('Y-m-d');
$data_final = ($data_final != '') ? data2bd($data_final) : date('Y-m-d');
 
require_once 'ipi_req_quant_vendidas.php';
imprime_quantidade_vendidas($data_inicial, $data_final, $cod_pizzarias, 0, $con);

desconectabd($con);
?>

</body>
</html>