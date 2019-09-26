<?php

/**
 * Tela de consulta e alteração de dados de clientes.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR            DESCRIÇÃO 
 * ======    ==========   ==============   =============================================================
 *
 * 1.0       21/09/2012   FilipeGranato    Criado.
 *
 */
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once 'ipi_req_quant_vendidas.php';

cabecalho('Estatísticas dos Clientes');

$tabela = 'ipi_clientes';
$chave_primaria = 'cod_clientes';
$quant_pagina = 50;
$acao = validavarPost('acao');


function normalizar_nome_internet ($nome)
{
    $p = strtr($nome, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ", "aaaaeeiooouucAAAAEEIOOOUUC");
    $p = preg_replace("/[^a-zA-Z0-9]+/", '', $p);
    //$url = strtolower($url);
    //$url = $p;
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
    <?// 
    $con = conectabd();
    //p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")
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


<!-- <div name='fAtivos' id='fAtivos' style='border: 1px black solid;width: 226px;float:left;margin:5px 5px'>
	<h1>Clientes Ativos</h2>
<? 
$con = conectar_bd();


$sql_buscar_ativos = "select (select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.situacao='ATIVO' and c.origem_cliente = 'TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-','')) cli_tel, (select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.situacao='ATIVO' and c.origem_cliente = 'NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-','')) cli_net";
$res_buscar_ativos = mysql_query($sql_buscar_ativos);
$obj_buscar_ativos = mysql_fetch_object($res_buscar_ativos);
$cont_net = $obj_buscar_ativos->cli_net;
$cont_tel = $obj_buscar_ativos->cli_tel;
//and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-','')

//$sql_buscar_ativos = "select (select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.situacao='ATIVO' and c.origem_cliente = 'TEL' and e.cep between cep.cep_inicial and cep.cep_final) cli_tel, (select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.situacao='ATIVO' and c.origem_cliente = 'NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-','')) cli_net";
/*


$sql_buscar_ceps = "select cep.cep_final,cep.cep_inicial from ipi_cep cep where cep.cod_pizzarias = '".$cod_pizzarias."' ";
$res_buscar_ceps = mysql_query($sql_buscar_ceps);
$cont_net = 0;
$cont_tel = 0;
while($obj_buscar_ceps = mysql_fetch_object($res_buscar_ceps))
{

	$sql_cont_end = "select count(e.cod_clientes) as ccli from ipi_enderecos e inner join ipi_clientes c on c.cod_clientes = e.cod_clientes where replace(e.cep,'-','') <= '".$obj_buscar_ceps->cep_final."' and replace(e.cep,'-','') >= '".$obj_buscar_ceps->cep_inicial."' and c.origem_cliente = 'NET' group by c.cod_clientes";
	//echo "<br/>".$sql_cont_end."<br/>";
	$res_cont_end = mysql_query($sql_cont_end);
	if(mysql_num_rows($res_cont_end)>0)
	{
		while($obj_cont_end = mysql_fetch_object($res_cont_end))
		$cont_net += $obj_cont_end->ccli;
	}

	$sql_cont_end = "select count(e.cod_clientes) as ccli from ipi_enderecos e inner join ipi_clientes c on c.cod_clientes = e.cod_clientes where replace(e.cep,'-','') <= '".$obj_buscar_ceps->cep_final."' and replace(e.cep,'-','') >= '".$obj_buscar_ceps->cep_inicial."' and c.origem_cliente = 'TEL' group by c.cod_clientes";
	//echo "<br/>".$sql_cont_end."<br/>";
	$res_cont_end = mysql_query($sql_cont_end);
	if(mysql_num_rows($res_cont_end)>0)
	{
		while($obj_cont_end = mysql_fetch_object($res_cont_end))
		$cont_tel += $obj_cont_end->ccli;
	}

}NET: <? echo $cont_net ?>
TEL: <? echo $cont_tel ?>
*/

?>
<div id='grfAtivos'></div>

<script>
var sabores = new FusionCharts('../lib/swf/fusioncharts/pie3d.swf', 'CLIENTES_ATIVOS', 225, 300, 0, 0, 'ffffff', 0);
sabores.setDataURL('ipi_clientes_estatisticas_ajax.php?param=1,<? echo $cont_net; ?>,<? echo $cont_tel ?>');
sabores.render('grfAtivos');
</script>
</div> -->
<!-- <div id='sexo' name='sexo' style='border: 1px black solid;width: 301px;float:left;margin:5px 5px'>
<h1>Sexo Clientes</h2>
<? 
	/*$sql_cont_sexo = "select (select count(cod_clientes) from ipi_clientes where sexo='F')femi,(select count(cod_clientes) from ipi_clientes where sexo='M')masc";
	$res_cont_sexo = mysql_query($sql_cont_sexo);
	$obj_cont_sexo = mysql_fetch_object($res_cont_sexo);

	$cont_f = $obj_cont_sexo->femi;
	$cont_m = $obj_cont_sexo->masc;*/
?>
<div id='grfSexo'></div>
<script>
var sabores = new FusionCharts('../lib/swf/fusioncharts/pie3d.swf', 'SEXO', 300, 300, 0, 0, 'ffffff', 0);
sabores.setDataURL('ipi_clientes_estatisticas_ajax.php?param=2,<? echo $cont_f; ?>,<? echo $cont_m ?>');
sabores.render('grfSexo');
</script>
</div> -->

<!-- <div id='idade' name='idade' style='border: 1px black solid;width: 450px;float:left;margin:5px 5px'>
<h1>Idade dos Clientes</h2>
<? 
/*$filtro_pizzaria = " cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-','') ";

	$sql_cont_idade = "select (select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on e.cod_clientes = c.cod_clientes inner join ipi_cep cep where year(NOW()) - year(c.nascimento)<=18 and c.nascimento!='0000-00-00' and c.nascimento !='1960-01-01' and c.nascimento !='2009-09-03' and $filtro_pizzaria)menos18,
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on e.cod_clientes = c.cod_clientes inner join ipi_cep cep where year(NOW()) - year(c.nascimento) between 18 and 25 and c.nascimento!='0000-00-00' and c.nascimento !='1960-01-01' and c.nascimento !='2009-09-03' and $filtro_pizzaria)i18a25,
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on e.cod_clientes = c.cod_clientes inner join ipi_cep cep where year(NOW()) - year(c.nascimento) between 26 and 30 and c.nascimento!='0000-00-00' and c.nascimento !='1960-01-01' and c.nascimento !='2009-09-03' and $filtro_pizzaria)i26a30,
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on e.cod_clientes = c.cod_clientes inner join ipi_cep cep where year(NOW()) - year(c.nascimento) between 31 and 40 and c.nascimento!='0000-00-00' and c.nascimento !='1960-01-01' and c.nascimento !='2009-09-03' and $filtro_pizzaria)i31a40,
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on e.cod_clientes = c.cod_clientes inner join ipi_cep cep where year(NOW()) - year(c.nascimento) between 41 and 55 and c.nascimento!='0000-00-00' and c.nascimento !='1960-01-01' and c.nascimento !='2009-09-03' and $filtro_pizzaria)i41a55,
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on e.cod_clientes = c.cod_clientes inner join ipi_cep cep where year(NOW()) - year(c.nascimento)>=56 and c.nascimento!='0000-00-00' and c.nascimento !='1960-01-01' and c.nascimento !='2009-09-03' and $filtro_pizzaria)i56mais";
	//echo $sql_cont_idade;
	$res_cont_idade = mysql_query($sql_cont_idade);
	$obj_cont_idade = mysql_fetch_object($res_cont_idade);

	$cont_menos18 = $obj_cont_idade->menos18;
	$cont_i18a25 = $obj_cont_idade->i18a25;
	$cont_i26a30 = $obj_cont_idade->i26a30;
	$cont_i31a40 = $obj_cont_idade->i31a40;
	$cont_i41a55 = $obj_cont_idade->i41a55;
	$cont_i56mais = $obj_cont_idade->i56mais;*/
?>
<div id='grfIdade'></div>
<script>
var sabores = new FusionCharts('../lib/swf/fusioncharts/pie3d.swf', 'IDADE', 450, 300, 0, 0, 'ffffff', 0);
sabores.setDataURL('ipi_clientes_estatisticas_ajax.php?param=3,<? echo $cont_menos18; ?>,<? echo $cont_i18a25 ?>,<? echo $cont_i26a30; ?>,<? echo $cont_i31a40 ?>,<? echo $cont_i41a55; ?>,<? echo $cont_i56mais ?>');
sabores.render('grfIdade');
</script>
</div> -->

<!-- <div id='bairro_cadastrado' name='bairro_cadastrado' style='border: 1px black solid;width: 501px;float:left;margin:5px 5px'>
	<h1>Bairros com mais clientes</h2>
	<?
		/*$sql_buscar_bairro_cadastrado = "select e.bairro,count(e.cod_enderecos) as cont from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.situacao='ATIVO' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-','') group by e.bairro order by cont DESC LIMIT 10";
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
		}*/
		//echo $parametros;
	?>
	<div id='grfBairroCadastrado'></div>
		<script>
			var sabores = new FusionCharts('../lib/swf/fusioncharts/column3d.swf', 'BAIRROCADASTRADO', 500, 300, 0, 0, 'ffffff', 0);
			sabores.setDataURL('ipi_clientes_estatisticas_ajax.php?param=5,<? echo $parametros; ?>');
			sabores.render('grfBairroCadastrado');
		</script>
</div>
 -->
<div id='bairro_mais_pedido' name='bairro_mais_pedido' style='border: 1px black solid;width: 501px;float:left;margin:5px 5px'>
	<h1>Bairros com mais Pedidos</h1>
	<?
		$sql_buscar_bairro_cadastrado = "select p.bairro,count(p.cod_pedidos) as cont from ipi_pedidos p where p.situacao='BAIXADO' and p.cod_pizzarias = '".$cod_pizzarias."' and data_hora_pedido between '".$data_inicial." 00:00:00' and '".$data_final." 23:59:59' and p.bairro!='' group by p.bairro order by cont DESC LIMIT 10";
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
		//echo $parametros;
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
			$sql_buscar_bairro_cadastrado = "select p.bairro,count(p.cod_pedidos) as cont from ipi_pedidos p where p.situacao='BAIXADO' and p.cod_pizzarias = '".$cod_pizzarias."' and data_hora_pedido between '".$data_inicial." 00:00:00' and '".$data_final." 23:59:59' and p.bairro!='' group by p.bairro order by cont DESC";
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
		$sql_buscar_bairro_cadastrado = "select p.bairro,sum(p.valor_total) as tot from ipi_pedidos p where p.situacao='BAIXADO' and p.cod_pizzarias = '".$cod_pizzarias."' and data_hora_pedido between '".$data_inicial." 00:00:00' and '".$data_final." 23:59:59' and p.bairro!='' group by p.bairro order by tot DESC LIMIT 10";
		//echo $sql_buscar_bairro_cadastrado;
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
		//echo $parametros
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
			$sql_buscar_bairro_cadastrado = "select p.bairro,sum(p.valor_total) as tot from ipi_pedidos p where p.situacao='BAIXADO' and p.cod_pizzarias = '".$cod_pizzarias."' and data_hora_pedido between '".$data_inicial." 00:00:00' and '".$data_final." 23:59:59' and p.bairro!='' group by p.bairro order by tot DESC LIMIT 10";
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
		<h1>Onde Conheceu</h1>

			<?
			$sql_buscar_bairro_cadastrado = "select count(oc.cod_onde_conheceu) as qtd_conheceu, oc.onde_conheceu from ipi_clientes c inner join ipi_onde_conheceu oc on oc.cod_onde_conheceu = c.cod_onde_conheceu where c.cod_clientes in (SELECT p.cod_clientes from ipi_pedidos p where p.situacao='BAIXADO' and p.cod_pizzarias = '".$cod_pizzarias."' and data_hora_pedido between '".$data_inicial." 00:00:00' and '".$data_final." 23:59:59' and p.bairro!='') group by oc.onde_conheceu order by qtd_conheceu DESC";
			$res_buscar_bairro_cadastrado = mysql_query($sql_buscar_bairro_cadastrado);
			//echo $sql_buscar_bairro_cadastrado;
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
		sabores.setDataURL('ipi_clientes_estatisticas_ajax.php?param=8,<? echo $i?>,<? echo $parametros; ?>');
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

<!-- <div id='crescimento' name='crescimento' style='border: 1px black solid;width: 500;float:left;margin:5px 5px'>
<h1>Crescimento dos Clientes</h2>

<? 

	$meses = array('Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');

	$mes_atual = (date('m')-1);
	$ano_atual = date('Y');
	$campos = array();
	$datas = array();
	for($i = 0;$i<=12;$i++)
	{
		$mes_usar = $mes_atual - ($i+1);
		$ano_usar = $ano_atual;
		if($mes_usar<0)
		{
			$ano_usar = (date('Y')-1);
			$mes_usar = (12+$mes_usar);
		}
		$campos[] = $meses[$mes_usar].'/'.$ano_usar;
		$mes_usar = sprintf('%1$02d',($mes_usar+1));
		$datas[] = $ano_usar.'-'.($mes_usar);
	}

	
	$campo1 = 'Setembro/2011';
	$campo2 = 'Outubro/2011';
	$campo3 = 'Novembro/2011';
	$campo4 = 'Dezembro/2011';
	$campo5 = 'Janeiro/2012';
	$campo6 = 'Fevereiro/2012';
	$campo7 = 'Março/2012';
	$campo8 = 'Abril/2012';
	$campo9 = 'Maio/2012';
	$campo10 = 'Junho/2012';
	$campo11 = 'Julho/2012';
	$campo12 = 'Agosto/2012';
	//$campo13 = 'Setembro/2012';

	$sql_buscar_cres = "select 
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[11]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[11]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[10]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[10]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[9]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[9]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[8]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[8]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[7]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[7]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[6]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[6]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[5]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[5]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[4]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[4]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[3]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[3]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[2]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[2]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[1]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[1]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[0]."%' and c.origem_cliente='TEL' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[0]."'";

	//echo "</br>11111===".$sql_buscar_cres."<br/>";
	//$res_busca_cres = mysql_query($sql_buscar_cres);
	//$obj_busca_cres = mysql_fetch_object($res_busca_cres);

	$valor1 = $obj_busca_cres->$campo1 ;
	$valor2 = $obj_busca_cres->$campo2 ;
	$valor3 = $obj_busca_cres->$campo3 ;
	$valor4 = $obj_busca_cres->$campo4 ;
	$valor5 = $obj_busca_cres->$campo5 ;
	$valor6 = $obj_busca_cres->$campo6 ;
	$valor7 = $obj_busca_cres->$campo7 ;
	$valor8 = $obj_busca_cres->$campo8 ;
	$valor9 = $obj_busca_cres->$campo9 ;
	$valor10 = $obj_busca_cres->$campo10;
	$valor11 = $obj_busca_cres->$campo11;
	$valor12 = $obj_busca_cres->$campo12;

	/*$sql_buscar_cres = "select (select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2011-09%' and origem_cliente='NET')'$campo1',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2011-10%' and origem_cliente='NET')'$campo2',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2011-11%' and origem_cliente='NET')'$campo3',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2011-12%' and origem_cliente='NET')'$campo4',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2012-01%' and origem_cliente='NET')'$campo5',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2012-02%' and origem_cliente='NET')'$campo6',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2012-03%' and origem_cliente='NET')'$campo7',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2012-04%' and origem_cliente='NET')'$campo8',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2012-05%' and origem_cliente='NET')'$campo9',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2011-09%'),(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2012-06%' and origem_cliente='NET')'$campo10',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2012-07%' and origem_cliente='NET')'$campo11',(select count(cod_clientes) from ipi_clientes where data_hora_cadastro like '2012-08%' and origem_cliente='NET')'$campo12'";*/

	$sql_buscar_cres = "select 
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[11]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[11]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[10]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[10]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[9]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[9]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[8]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[8]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[7]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[7]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[6]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[6]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[5]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[5]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[4]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[4]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[3]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[3]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[2]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[2]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[1]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[1]."',
	(select count(c.cod_clientes) from ipi_clientes c inner join ipi_enderecos e on c.cod_clientes = e.cod_clientes inner join ipi_cep cep where c.data_hora_cadastro like '".$datas[0]."%' and c.origem_cliente='NET' and cep.cod_pizzarias = '".$cod_pizzarias."' and cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-',''))'".$campos[0]."'";
	//echo "<br/>222===".$sql_buscar_cres;
	//$res_busca_cres = mysql_query($sql_buscar_cres);
	//$obj_busca_cres = mysql_fetch_object($res_busca_cres);

	$valorn1 = $obj_busca_cres->$campo1 ;
	$valorn2 = $obj_busca_cres->$campo2 ;
	$valorn3 = $obj_busca_cres->$campo3 ;
	$valorn4 = $obj_busca_cres->$campo4 ;
	$valorn5 = $obj_busca_cres->$campo5 ;
	$valorn6 = $obj_busca_cres->$campo6 ;
	$valorn7 = $obj_busca_cres->$campo7 ;
	$valorn8 = $obj_busca_cres->$campo8 ;
	$valorn9 = $obj_busca_cres->$campo9 ;
	$valorn10 = $obj_busca_cres->$campo10;
	$valorn11 = $obj_busca_cres->$campo11;
	$valorn12 = $obj_busca_cres->$campo12;

?>
<div id='grfCrescimento'></div>
<script>
var sabores = new FusionCharts('../lib/swf/fusioncharts/msspline.swf', 'Crescimento', 503, 300, 0, 0, 'ffffff', 0);
sabores.setDataURL('ipi_clientes_estatisticas_ajax.php?param=4,<? echo $valor1; ?>,<? echo $valor2; ?>,<? echo $valor3; ?>,<? echo $valor4; ?>,<? echo $valor5; ?>,<? echo $valor6; ?>,<? echo $valor7; ?>,<? echo $valor8; ?>,<? echo $valor9; ?>,<? echo $valor10; ?>,<? echo $valor11; ?>,<? echo $valor12; ?>,<? echo $valorn1; ?>,<? echo $valorn2; ?>,<? echo $valorn3; ?>,<? echo $valorn4; ?>,<? echo $valorn5; ?>,<? echo $valorn6; ?>,<? echo $valorn7; ?>,<? echo $valorn8; ?>,<? echo $valorn9; ?>,<? echo $valorn10; ?>,<? echo $valorn11; ?>,<? echo $valorn12; ?>');
sabores.render('grfCrescimento');
</script>
</div> -->
<?endif ; ?>
<?
rodape();
?>
