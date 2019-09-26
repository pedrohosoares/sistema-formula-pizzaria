<?php

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once 'ipi_req_quant_vendidas.php';

cabecalho('Estatísticas dos Novos Clientes');

$tabela = 'ipi_clientes';
$chave_primaria = 'cod_clientes';
$quant_pagina = 50;
$acao = validavarPost('acao');


function normalizar_nome_internet ($nome)
{
    $p = strtr($nome, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ", "aaaaeeiooouucAAAAEEIOOOUUC");
    $p = preg_replace("/[^a-zA-Z0-9]+/", '', $p);
    return $p;
}

?>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
  <script type="text/javascript" src="../lib/js/calendario.js"></script>
<script type="text/javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>
<script>
window.addEvent('domready', function() { 
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 


<?
$cod_pizzarias = validavarPost('cod_pizzarias');
$data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
$data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');

?>

</script>
<form name="frmFiltro" method="post" >
<table >
<tr>
<td class="tdbt tdbl"><label for='cod_pizzarias'><? echo ucfirst(TIPO_EMPRESA)?></label></td>
<td class="tdbt">&nbsp;</td>
<td class="tdbr tdbt">
  <select name="cod_pizzarias" id="cod_pizzarias">
    <?
    $con = conectabd();
 
    $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") and situacao='ATIVO' ORDER BY p.nome";//pedido do rubens,não mostrar a matrix
    $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
    while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
    {
      echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
      if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
        echo 'selected';
      echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
    }
    desconectabd($con);
    ?>
  </select>
</td>
</tr>
<tr>
    <td class="legenda tdbl" align="right"><label for="data_inicial">Data Inicial:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo bd2data($data_inicial) ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
    <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo bd2data($data_final) ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>

<tr>
	<td colspan='3' align='right' class="tdbr tdbl tdbb">
		<input class='botao' type='submit' value='Buscar'/>
	</td>
</tr>
</table>

<input type="hidden" name="acao" value="buscar">

</form>

<? if($acao=='buscar' && $cod_pizzarias): ?>


<?php  

$con = conectar_bd();

?>
<div id='bairro_mais_pedido' name='bairro_mais_pedido' style='border: 1px black solid;width: 501px;float:left;margin:5px 5px'>
	<h1>Bairros com mais Pedidos</h1>
	<?
		$sql_buscar_bairro_cadastrado = "select p.bairro,count(p.cod_pedidos) as cont from ipi_pedidos p inner join ipi_clientes c on (c.cod_clientes = p.cod_clientes) where c.data_hora_cadastro between '$data_inicial 00:00:00' and '$data_final 23:59:59' and p.situacao='BAIXADO' and p.cod_pizzarias = '".$cod_pizzarias."' and data_hora_pedido between '".$data_inicial." 00:00:00' and '".$data_final." 23:59:59' and p.bairro!=''  group by p.bairro order by cont DESC LIMIT 10";
		$res_buscar_bairro_cadastrado = mysql_query($sql_buscar_bairro_cadastrado);
		$parametros = '';
		$i = 0;
		while($obj_buscar_bairro_cadastrado = mysql_fetch_object($res_buscar_bairro_cadastrado))
		{
			$parametros .= normalizar_nome_internet($obj_buscar_bairro_cadastrado->bairro).',';
			$parametros .= $obj_buscar_bairro_cadastrado->cont;
			if($i<9)
				$parametros .=',';
			$i++;
		}
		
	?>
	<div id='grfBairroPedido'></div>
		<script>
			var sabores = new FusionCharts('../lib/swf/fusioncharts/column3d.swf', 'BAIRROPEDIDO', 500, 300, 0, 0, 'ffffff', 0);
			sabores.setDataURL('ipi_clientes_estatisticas_ajax.php?param=6,<? echo $parametros; ?>');
			sabores.render('grfBairroPedido');
		</script>
		
		<div align="center">
		<h2>Lista com todos os bairros</h2>
			<table class="listaEdicao">
			<thead>
				<tr>
					<td >
					Bairro
					</td>
					<td>
					Nº. Pedidos
					</td>
				</tr>
			</thead>
			<tbody>
			<?
			$sql_buscar_bairro_cadastrado = "select p.bairro,count(p.cod_pedidos) as cont from ipi_pedidos p inner join ipi_clientes c on (c.cod_clientes = p.cod_clientes) where c.data_hora_cadastro between '$data_inicial 00:00:00' and '$data_final 23:59:59' and p.situacao='BAIXADO' and p.cod_pizzarias = '".$cod_pizzarias."' and data_hora_pedido between '".$data_inicial." 00:00:00' and '".$data_final." 23:59:59' and p.bairro!='' group by p.bairro order by cont DESC";
			$res_buscar_bairro_cadastrado = mysql_query($sql_buscar_bairro_cadastrado);
			$parametros = '';
			$i = 0;
			while($obj_buscar_bairro_cadastrado = mysql_fetch_object($res_buscar_bairro_cadastrado))
			{
				echo "<tr>";
				echo "<td >".$obj_buscar_bairro_cadastrado->bairro."</td>";
				echo "<td >".$obj_buscar_bairro_cadastrado->cont."</td>";
				echo "</tr>";
			}
			
		?>
		</tbody>
		</table>
	</div>
	<br/>
</div>

<div id='bairro_mais_faturamento' name='bairro_mais_faturamento' style='border: 1px black solid;width: 501px;float:left;margin:5px 5px'>
	<h1>Bairros que promovem mais faturamento</h1>
	<?
		$sql_buscar_bairro_cadastrado = "select p.bairro,sum(p.valor_total) as tot from ipi_pedidos p inner join ipi_clientes c on (c.cod_clientes = p.cod_clientes) where c.data_hora_cadastro between '$data_inicial 00:00:00' and '$data_final 23:59:59' and p.situacao='BAIXADO' and p.cod_pizzarias = '".$cod_pizzarias."' and data_hora_pedido between '".$data_inicial." 00:00:00' and '".$data_final." 23:59:59' and p.bairro!='' group by p.bairro order by tot DESC LIMIT 10";
		
		$res_buscar_bairro_cadastrado = mysql_query($sql_buscar_bairro_cadastrado);
		$parametros = '';
		$i = 0;
		while($obj_buscar_bairro_cadastrado = mysql_fetch_object($res_buscar_bairro_cadastrado))
		{
			$parametros .= normalizar_nome_internet($obj_buscar_bairro_cadastrado->bairro).',';
			$parametros .= $obj_buscar_bairro_cadastrado->tot;
			if($i<9)
				$parametros .=',';
			$i++;
		}
		
	?>
	<div id='grfBairroFaturamento'></div>
		<script>
			var sabores = new FusionCharts('../lib/swf/fusioncharts/column3d.swf', 'BAIRROFATURAMENTO', 500, 300, 0, 0, 'ffffff', 0);
			sabores.setDataURL('ipi_clientes_estatisticas_ajax.php?param=7,<? echo $parametros; ?>');
			sabores.render('grfBairroFaturamento');
		</script>

		<div align="center">
		<h2>Lista com todos os bairros</h2>
			<table class="listaEdicao">
			<thead>
				<tr>
					<td >
					Bairro
					</td>
					<td>
					Total com Frete 
					</td>
				</tr>
			</thead>
			<tbody>
			<?
			$sql_buscar_bairro_cadastrado = "select p.bairro,sum(p.valor_total) as tot from ipi_pedidos p inner join ipi_clientes c on (c.cod_clientes = p.cod_clientes) where c.data_hora_cadastro between '$data_inicial 00:00:00' and '$data_final 23:59:59' and p.situacao='BAIXADO' and p.cod_pizzarias = '".$cod_pizzarias."' and data_hora_pedido between '".$data_inicial." 00:00:00' and '".$data_final." 23:59:59' and p.bairro!='' group by p.bairro order by tot DESC LIMIT 10";
			$res_buscar_bairro_cadastrado = mysql_query($sql_buscar_bairro_cadastrado);
			$parametros = '';
			$i = 0;
			while($obj_buscar_bairro_cadastrado = mysql_fetch_object($res_buscar_bairro_cadastrado))
			{
				echo "<tr>";
				echo "<td>".$obj_buscar_bairro_cadastrado->bairro."</td>";
				echo "<td>R$ ".bd2moeda($obj_buscar_bairro_cadastrado->tot)."</td>";
				echo "</tr>";
			}
			
		?>
		</tbody>
		</table>
	</div>
	<br/>
</div>

<div align="center" style='border: 1px black solid;width: 501px;float:left;margin:5px 5px'>
		<h1>Onde Conheceu (Novos Clientes)</h1>

			<?
			$sql_buscar_bairro_cadastrado = "SELECT count(oc.cod_onde_conheceu) as qtd_conheceu, oc.onde_conheceu from ipi_clientes c inner join ipi_onde_conheceu oc on oc.cod_onde_conheceu = c.cod_onde_conheceu where c.cod_clientes in (SELECT  DISTINCT (p.cod_clientes) from ipi_pedidos p where p.situacao='BAIXADO' and p.cod_pizzarias = '$cod_pizzarias' and data_hora_pedido between '$data_inicial 00:00:00' and '$data_final 23:59:59' and p.bairro!='') and c.data_hora_cadastro BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' group by oc.onde_conheceu order by qtd_conheceu DESC";
			$res_buscar_bairro_cadastrado = mysql_query($sql_buscar_bairro_cadastrado);
			
			$parametros = '';
			$i = 0;
			$tabela = '';
			while($obj_buscar_bairro_cadastrado = mysql_fetch_object($res_buscar_bairro_cadastrado))
			{
				$i++;
				if($i>1)
				{
					$parametros .=',';
				}
				$tabela .= "<tr>";
				$tabela .= "<td>".$obj_buscar_bairro_cadastrado->onde_conheceu."</td>";
				$tabela .= "<td>".$obj_buscar_bairro_cadastrado->qtd_conheceu."</td>";
				$tabela .= "</tr>";

				$parametros .= normalizar_nome_internet($obj_buscar_bairro_cadastrado->onde_conheceu).',';
				$parametros .= $obj_buscar_bairro_cadastrado->qtd_conheceu;
			
			}
			
		?>

		
		<div id='grfConheceu'></div>
		<script>
		var sabores = new FusionCharts('../lib/swf/fusioncharts/pie3d.swf', 'ONDECONHECEU', 300, 300, 0, 0, 'ffffff', 0);
		sabores.setDataURL('ipi_clientes_novos_estatisticas_ajax.php?param=8,<? echo $i?>,<? echo $parametros; ?>');
		sabores.render('grfConheceu');
		</script>
			<table class="listaEdicao">
			<thead>
				<tr>
					<td >
					Onde Conheceu
					</td>
					<td>
					Quantidade
					</td>
				</tr>
			</thead>
			<tbody>
			<? echo $tabela ?>
			</tbody>
		</table>
	</div>
	
<?endif ; ?>
<?
rodape();
?>
