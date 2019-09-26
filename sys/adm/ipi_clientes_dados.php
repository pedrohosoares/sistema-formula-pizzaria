<?php

/**
 * Resultados das Enquetes (ajax).
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       12/05/2010   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$param = explode(',', validaVarGet('param'));

$tipo_grafico = $param[0];
$cod_clientes = $param[1];

$meses_ano = array(1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
$dia_semana = array(1 => "Dom", 2 => "Seg", 3 => "Ter", 4 => "Quar", 5 => "Qui", 6 => "Sex", 7 => "Sáb");
$tipo_situacao = "BAIXADO"; //Pode ser adicionado outros tipos de situacao Ex. "BAIXADO, CONCLUIDO, FECHADO etc..."

$conexao = conectabd();

$sql_buscar_cep = "SELECT cep FROM ipi_enderecos WHERE cod_clientes = '".$cod_clientes."'";
$res_buscar_cep = mysql_query($sql_buscar_cep);
$obj_buscar_cep = mysql_fetch_object($res_buscar_cep);
$cep_limpo = str_replace ( "-", "", str_replace('.', '', $obj_buscar_cep->cep));
$sql_buscar_pizzaria = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
$res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
$obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
$cod_pizzarias = $obj_buscar_pizzaria->cod_pizzarias;


$sql_buscar_tamanhos = "SELECT * from ipi_tamanhos order by cod_tamanhos";
$res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);
while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
{
	$pizzas_tamanhos[$obj_buscar_tamanhos->cod_tamanhos] = $obj_buscar_tamanhos->tamanho;
}

?>

<?php if($tipo_grafico == 1): ?>

<chart palette="2" animation="1" lowerLimit="0" upperLimit="10" showShadow="1" caption="Pontuação" subcaption="(Últimos 12 meses)" colorRangeFillRatio="0,10,80,10" showColorRangeBorder="0" roundRadius="0" showValue="1" chartTopMargin="15" chartRightMargin="20">

<value>8</value>

<colorRange>
    <color minValue='0' maxValue='8.9' code='FF654F'/>
    <color minValue='9.0' maxValue='9.4' code='F6BD0F'/>
    <color minValue='9.5' maxValue='10' code='8BBA00'/>
</colorRange>

</chart>
<?php endif; ?>

<?php 
if($tipo_grafico == 2):
?>

<chart caption='Comparação de médias de pedido' shownames='1' showvalues='0' decimals='1' numberPrefix='Qtd. '>
	<categories>
		<?php
		$sql_buscar_data = "SELECT MIN(YEAR(data_hora_pedido)) AS ano_min, MIN(DATE(data_hora_pedido)) AS data_min, MAX(DATE(data_hora_pedido)) AS data_max FROM ipi_pedidos WHERE cod_clientes = '$cod_clientes'";
		$res_buscar_data = mysql_query($sql_buscar_data);
		$obj_buscar_data = mysql_fetch_object($res_buscar_data);
		
		$numero_meses = numero_meses( bd2data($obj_buscar_data->data_min), bd2data($obj_buscar_data->data_max));
		for ($x=0; $x<=$numero_meses; $x++)
		{	
			echo "<category label='".$meses_ano[(int) date('m', strtotime("$obj_buscar_data->data_min" . " +$x month"))].'/'.date('Y', strtotime("$obj_buscar_data->data_min" . " +$x month"))."' />";
		}	
		?>
	</categories>
	
	<dataset seriesName='Média de pedidos do Cliente' color='AFD8F8' showValues='0'>
		<?php
		
		for ($x=0; $x<=$numero_meses; $x++)
		{	
			$sql_buscar_pedidos = "SELECT COUNT(*) AS total_pedidos FROM ipi_pedidos p WHERE p.situacao IN('$tipo_situacao') AND cod_clientes = '$cod_clientes' AND MONTH(data_hora_pedido)=MONTH(DATE_ADD('$obj_buscar_data->data_min', INTERVAL $x MONTH)) AND YEAR(data_hora_pedido)=YEAR(DATE_ADD('$obj_buscar_data->data_min', INTERVAL $x MONTH)) ORDER BY p.cod_pedidos DESC";
			$res_buscar_pedidos = mysql_query($sql_buscar_pedidos);
			$obj_buscar_pedidos = mysql_fetch_object($res_buscar_pedidos);
			
			echo "<set value='".$obj_buscar_pedidos->total_pedidos."' />";
			
		}
		?>
	</dataset>
	<dataset seriesName='Média de pedidos da pizzaria' color='F6BD0F' showValues='0'>
		<?php
		for ($x=0; $x<=$numero_meses; $x++)
		{	
			$sql_buscar_pedidos = "select (select count(distinct(p1.cod_clientes)) from ipi_pedidos p2 WHERE p2.cod_pedidos=p1.cod_pedidos ) num_cliente, count(p1.cod_pedidos) total_pedidos, (count(p1.cod_pedidos)/(select count(distinct(p1.cod_clientes)) from ipi_pedidos p2 WHERE p2.cod_pedidos=p1.cod_pedidos )) media from ipi_pedidos p1 where MONTH(data_hora_pedido)=MONTH(DATE_ADD('$obj_buscar_data->data_min', INTERVAL $x MONTH)) and YEAR(data_hora_pedido)=YEAR(DATE_ADD('$obj_buscar_data->data_min', INTERVAL $x MONTH)) AND p1.situacao IN('$tipo_situacao') AND cod_pizzarias = '$cod_pizzarias' ORDER BY p1.cod_pedidos DESC";
			$res_buscar_pedidos = mysql_query($sql_buscar_pedidos);
			$obj_buscar_pedidos = mysql_fetch_object($res_buscar_pedidos);
			
			echo "<set value='".$obj_buscar_pedidos->media."' />";
		}
		echo $sql_buscar_pedidos;
		//file_put_contents('log.txt', 'teste->'.$sql_buscar_pedidos, FILE_APPEND);
		?>
	</dataset>
</chart>
<?php endif; ?>

<?php if($tipo_grafico == 3):

	$sql_buscar_adicionais = "SELECT i.ingrediente, COUNT(*) AS quantidade FROM ipi_pedidos p INNER JOIN ipi_pedidos_fracoes pf ON(p.cod_pedidos=pf.cod_pedidos) INNER JOIN ipi_pedidos_ingredientes pi ON(pf.cod_pedidos_fracoes=pi.cod_pedidos_fracoes) INNER JOIN ipi_ingredientes i ON(pi.cod_ingredientes=i.cod_ingredientes) WHERE p.situacao IN('$tipo_situacao') AND cod_clientes = '$cod_clientes' AND ingrediente_padrao=0 GROUP BY pi.cod_ingredientes";
	$res_buscar_adicionais = mysql_query($sql_buscar_adicionais);
	
	echo '<chart caption="Ingredientes Adicionais" palette="3">';
	while ($obj_buscar_adicionais = mysql_fetch_object($res_buscar_adicionais))
	{
	    echo '<set label="'.$obj_buscar_adicionais->ingrediente.'" value="'.$obj_buscar_adicionais->quantidade.'"/>';
	}

	echo '</chart>';
	
endif; 
?>

<?php if($tipo_grafico == 4):

	$sql_buscar_bordas = "SELECT pb.cod_bordas, b.borda, COUNT(*) AS quantidade FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_bordas b ON (pb.cod_bordas = b.cod_bordas) WHERE p.situacao IN('$tipo_situacao') AND p.cod_clientes = '$cod_clientes' GROUP BY cod_bordas ORDER BY quantidade DESC LIMIT 5";
	$res_buscar_bordas = mysql_query($sql_buscar_bordas);
	
	echo '<chart caption="Bordas Adicionadas" palette="3">';
	while ($obj_buscar_bordas = mysql_fetch_object($res_buscar_bordas))
	{
	    $arr_cod_bordas[] = $obj_buscar_bordas->cod_bordas;
	    echo '<set label="'.$obj_buscar_bordas->borda.'" value="'.$obj_buscar_bordas->quantidade.'"/>';
	}
	$arr_cod_bordas = implode(',', $arr_cod_bordas);
	
	$sql_buscar_total = "SELECT count(pb.cod_bordas) as total FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_bordas b ON (pb.cod_bordas = b.cod_bordas) WHERE p.situacao IN('$tipo_situacao') AND p.cod_clientes = '$cod_clientes' AND pb.cod_bordas NOT IN (".$arr_cod_bordas.") ";
	$res_buscar_total = mysql_query($sql_buscar_total);
	$num_buscar_total = mysql_num_rows($res_buscar_total);
	
	for ($x=0; $x<$num_buscar_total; $x++)
	{
		$obj_buscar_total = mysql_fetch_object($res_buscar_total);
		echo '<set label="Outros" value="'.$obj_buscar_total->total.'"/>';
	}
	echo '</chart>';
	
endif; 
?>

<?php if($tipo_grafico == 5):

	$sql_buscar_sabor = "SELECT pf.cod_pizzas, pi.pizza, COUNT(*) AS quantidade FROM ipi_pedidos pe INNER JOIN ipi_pedidos_fracoes pf ON(pf.cod_pedidos=pe.cod_pedidos) INNER JOIN ipi_pizzas pi ON(pf.cod_pizzas=pi.cod_pizzas) WHERE pe.situacao IN('$tipo_situacao') AND cod_clientes = '$cod_clientes' GROUP BY pf.cod_pizzas ORDER BY quantidade DESC LIMIT 5";
	$res_buscar_sabor = mysql_query($sql_buscar_sabor);
	
	echo '<chart caption="Sabores dos '.TIPO_PRODUTOS.'" palette="3">';
	while ($obj_buscar_sabor = mysql_fetch_object($res_buscar_sabor))
	{
		$arr_cod_pizzas[] = $obj_buscar_sabor->cod_pizzas;	
	    echo '<set label="'.$obj_buscar_sabor->pizza.'" value="'.$obj_buscar_sabor->quantidade.'"/>';
	}

	$arr_cod_pizzas = implode(',', $arr_cod_pizzas);
	
	$sql_buscar_total = "SELECT count(pb.cod_pizzas) as total FROM ipi_pedidos p INNER JOIN ipi_pedidos_fracoes pb ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_pizzas b ON (pb.cod_pizzas = b.cod_pizzas) WHERE p.situacao IN('$tipo_situacao') AND p.cod_clientes = '$cod_clientes' AND pb.cod_pizzas NOT IN (".$arr_cod_pizzas.") ";
	$res_buscar_total = mysql_query($sql_buscar_total);
	$num_buscar_total = mysql_num_rows($res_buscar_total);
	
	for ($x=0; $x<$num_buscar_total; $x++)
	{
		$obj_buscar_total = mysql_fetch_object($res_buscar_total);
		echo '<set label="Outros" value="'.$obj_buscar_total->total.'"/>';
	}
	
	echo '</chart>';
	
endif; 
?>

<?php if($tipo_grafico == 6):

echo '<chart caption="Preferência pelo dia da Semana" lineThickness="1" showValues="0" formatNumberScale="0" anchorRadius="2" divLineAlpha="20" divLineColor="CC3300" divLineIsDashed="1" showAlternateHGridColor="1" alternateHGridColor="CC3300" shadowAlpha="40" numvdivlines="5" chartRightMargin="35" bgColor="FFFFFF,CC3300" bgAngle="270" bgAlpha="10,10">';
	
	echo'<categories>';
	
	for ($x=1; $x<=7; $x++)
	{
		echo'<category label="'.$dia_semana[$x].'"/>';
	}	
	
	echo'</categories>';
	
	$sql_buscar_numero_pedido = "SELECT COUNT(*) AS total_dia, DAYOFWEEK(data_hora_pedido) AS dia_semana FROM ipi_pedidos WHERE situacao IN('$tipo_situacao') AND cod_clientes = '$cod_clientes' GROUP BY dia_semana";
	$res_buscar_numero_pedido = mysql_query($sql_buscar_numero_pedido);

	echo'<dataset seriesName="Total dos Pedidos no Dia da Semana" color="1D8BD1" anchorBorderColor="1D8BD1" anchorBgColor="1D8BD1">';
	
		while ($obj_buscar_numero_pedido = mysql_fetch_object($res_buscar_numero_pedido))
		{
			$arr_total_dias[$obj_buscar_numero_pedido->dia_semana]=$obj_buscar_numero_pedido->total_dia;
		}	
	
		for ($x=1; $x<=7; $x++)
		{
			echo '<set value="';
			if ($arr_total_dias[$x]!='')
			{
				echo $arr_total_dias[$x];
			}
			else 
			{
				echo '0';	
			}
			echo '"/>';
		}
	
	echo'</dataset>';
	
echo'</chart>';	
	
endif; 
?>

<?php if($tipo_grafico == 7):

echo '<chart caption="Preferência pela Hora do Pedido" lineThickness="1" showValues="0" labelStep="2" formatNumberScale="1" anchorRadius="2" divLineAlpha="20" divLineColor="CC3300" divLineIsDashed="1" showAlternateHGridColor="1" alternateHGridColor="CC3300" shadowAlpha="40" numvdivlines="5" chartRightMargin="35" bgColor="FFFFFF,CC3300" bgAngle="270" bgAlpha="10,10">';
	
	echo'<categories>';
	
	for ($x=0; $x<=23; $x++)
	{
		echo'<category label="'.$x.'"/>';
	}	
	
	echo'</categories>';
	
	$sql_buscar_numero_pedido = "SELECT COUNT(*) total_hora, HOUR(data_hora_pedido) AS hora FROM ipi_pedidos WHERE situacao IN('$tipo_situacao') AND cod_clientes = '$cod_clientes' GROUP BY hora";
	$res_buscar_numero_pedido = mysql_query($sql_buscar_numero_pedido);
	$num_buscar_numero_pedido = mysql_num_rows($res_buscar_numero_pedido);
	
	echo'<dataset seriesName="Hora" color="1D8BD1" anchorBorderColor="1D8BD1" anchorBgColor="1D8BD1">';

		for ($x=0; $x<$num_buscar_numero_pedido; $x++)
		{
			$obj_buscar_numero_pedido = mysql_fetch_object($res_buscar_numero_pedido);
			$arr_total_horas[$obj_buscar_numero_pedido->hora]=$obj_buscar_numero_pedido->total_hora;
		}	
	
		for ($x=0; $x<=23; $x++)
		{
			echo '<set value="';
			if ($arr_total_horas[$x]!='')
			{
				echo $arr_total_horas[$x];
			}
			else 
			{
				echo '0';	
			}
			echo '"/>';
		}	
		
	echo'</dataset>';
	
echo'</chart>';	
	
endif; 
?>

<?php if($tipo_grafico == 8):

echo '<chart palette="0" caption="Tipo Entrega" shownames="1" showvalues="0" showSum="1" decimals="1">';

	$sql_buscar_balcao = "SELECT (SELECT COUNT(agendado) FROM ipi_pedidos WHERE situacao IN('$tipo_situacao') AND agendado=0 AND cod_clientes = '$cod_clientes') AS nao_agendados, (SELECT COUNT(agendado) FROM ipi_pedidos WHERE situacao IN('$tipo_situacao') AND agendado=1 AND cod_clientes = '$cod_clientes') AS agendados, (SELECT COUNT(origem_pedido) FROM ipi_pedidos WHERE situacao IN('$tipo_situacao') AND origem_pedido='TEL' AND cod_clientes = '$cod_clientes') AS pedidos_tel, (SELECT COUNT(origem_pedido) FROM ipi_pedidos WHERE situacao IN('$tipo_situacao') AND origem_pedido='NET' AND cod_clientes = '$cod_clientes') AS pedidos_net, (SELECT COUNT(tipo_entrega) FROM ipi_pedidos WHERE situacao IN('$tipo_situacao') AND tipo_entrega='Balcão' AND cod_clientes = '$cod_clientes') AS balcao, (SELECT COUNT(tipo_entrega) FROM ipi_pedidos WHERE situacao IN('$tipo_situacao') AND tipo_entrega='Entrega' AND cod_clientes = '$cod_clientes') AS entrega FROM ipi_pedidos p";
	$res_buscar_balcao = mysql_query($sql_buscar_balcao);
	$obj_buscar_balcao = mysql_fetch_object($res_buscar_balcao);
	
	echo '<categories>';
		echo '<category label="Balcão/Entrega"/>';
		echo '<category label="Net/Tel"/>';
		echo '<category label="Agen./Não Agen."/>';
	echo '</categories>';
	
	echo '<dataset color="FF1E00" showValues="0">';
		echo '<set value="'.$obj_buscar_balcao->balcao.'"/>';
		echo '<set value="'.$obj_buscar_balcao->pedidos_net.'"/>';
		echo '<set value="'.$obj_buscar_balcao->agendados.'"/>';
	echo '</dataset>';
	
	echo '<dataset color="0DFF00" showValues="0">';
		echo '<set value="'.$obj_buscar_balcao->entrega.'"/>';
		echo '<set value="'.$obj_buscar_balcao->pedidos_tel.'"/>';
		echo '<set value="'.$obj_buscar_balcao->nao_agendados.'"/>';
	echo '</dataset>';
	
echo '</chart>';

endif; 
?>

<?php if($tipo_grafico == 9):
//echo '<chart caption="Quadrada x Quadrada Six x Quadradinha" yAxisName="Unidades" showValues="0" decimals="0" formatNumberScale="0">';

	$sql_buscar_quadrada = "SELECT pf.cod_tamanhos, pi.tamanho, COUNT(*) AS quantidade FROM ipi_pedidos pe INNER JOIN ipi_pedidos_pizzas pf ON(pf.cod_pedidos=pe.cod_pedidos) INNER JOIN ipi_tamanhos pi ON(pf.cod_tamanhos=pi.cod_tamanhos) WHERE pe.situacao IN('$tipo_situacao') AND cod_clientes = ".$cod_clientes." GROUP BY pi.cod_tamanhos ORDER BY quantidade DESC";
	$res_buscar_quadrada = mysql_query($sql_buscar_quadrada);

	echo '<chart caption="'.implode($pizzas_tamanhos,' x ').'" yAxisName="Unidades" showValues="0" decimals="0" formatNumberScale="0">';

	while($obj_buscar_quadrada = mysql_fetch_object($res_buscar_quadrada))
	{
		echo '<set label="'.$pizzas_tamanhos[$obj_buscar_quadrada->cod_tamanhos].'" value="'.$obj_buscar_quadrada->quantidade.'"/>';
	}
	
echo '</chart>';

endif; 
?>

<?php desconectabd($conexao); ?>
