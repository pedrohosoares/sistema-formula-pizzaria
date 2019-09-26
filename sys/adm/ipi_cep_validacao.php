<?php

/**
 * ipi_cep_range.php: Consulta de Range de Ceps
 * 
 * Índice: cod_cep, cod_cep_aprovacao
 * Tabela: ipi_cep, ipi_cep_aprovacao
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Validação de conflitos dos CEP de Entrega');

$acao = validaVarPost('acao');

$tabela = 'ipi_cep';
$chave_primaria = 'cod_cep';
$quant_pagina = 120;
?>



<br>

<table><tr>

<!-- Conteúdo -->
<td class="conteudo">


<? 
$conexao = conectabd();

$sqlCEPS = "SELECT * FROM ipi_cep";
$resCEPS = mysql_query($sqlCEPS);
$linCEPS = mysql_num_rows($resCEPS);
echo "<table border=1 cellspacing=0 cellpadding=10 class='listaEdicao'>";
for ($a=0; $a<$linCEPS; $a++)
	{
	$objCEPS = mysql_fetch_object($resCEPS);

	$sqlCEPS2 = "SELECT * FROM ipi_cep WHERE cep_inicial<=".$objCEPS->cep_inicial." and cep_final>=".$objCEPS->cep_inicial;
	$resCEPS2 = mysql_query($sqlCEPS2);
	$linCEPS2 = mysql_num_rows($resCEPS2);
	//$objCEPS = mysql_fetch_object($sqlCEPS);

	if ($linCEPS2>1)
		{
		echo "<tr>";
	
		echo "<td>";
		echo $objCEPS->cep_inicial;
		echo "<br>".$linCEPS2. " - resultados" ;
		echo "</td>";
		echo "<td>";
	
	
		echo "<table border=1 cellspacing=0 cellpadding=3 class='listaEdicao'>";
		echo "<tr>";
	
		echo "<td width=100>";
		echo "<strong>CEP INICIAL</strong>";
		echo "</td>";
	
		echo "<td width=100>";
		echo "<strong>CEP FINAL</strong>";
		echo "</td>";
	
		echo "<td width=300>";
		echo "<strong>RUA</strong>";
		echo "</td>";
	
		echo "<td width=300>";
		echo "<strong>BAIRRO</strong>";
		echo "</td>";
	
		echo "</tr>";
	
		for ($b=0; $b<$linCEPS2; $b++)
			{
			$objCEPS2 = mysql_fetch_object($resCEPS2);
			echo "<tr>";
		
			echo "<td width=100>";
			echo $objCEPS2->cep_inicial;
			echo "</td>";
		
			echo "<td width=100>";
			echo $objCEPS2->cep_final;
			echo "</td>";
	
			echo "<td width=300>";
			echo $objCEPS2->rua;
			echo "</td>";
		
			echo "<td width=300>";
			echo $objCEPS2->bairro;
			echo "</td>";
		
			echo "</tr>";
			}
		echo "</table>";
		
		
	
	
	
	
		echo "</td>";
	
		echo "</tr>";
		}
	}
echo "</table>";
desconectabd($conexao);
?>



</td>
<!-- Conteúdo -->

<!-- Barra Lateral -->
<td class="lateral">
  <div class="blocoNavegacao">
    <ul>
      <li><a href="ipi_pizzaria.php"><? echo ucfirst(TIPO_EMPRESAS) ?></a></li>
      <li><a href="ipi_entregador.php">Entregadores</a></li>
    </ul>
  </div>
</td>
<!-- Barra Lateral -->

</tr></table>

<? rodape(); ?>