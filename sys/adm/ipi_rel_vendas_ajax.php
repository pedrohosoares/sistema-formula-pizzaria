<?php

/**
 * Resultados
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       12/08/2010   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$param = explode(',', validaVarGet('param'));

$tipo_grafico = $param[0];
$cod_pizzarias = $param[1];
$data_inicial = data2bd($param[2]);
$periodo_grafico = $param[3];
$data_final = data2bd($param[4]);

$meses_ano = array(1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Des");
$dia_semana = array(1 => "Seg", 2 => "Ter", 3 => "Quar", 4 => "Qui", 5 => "Sex", 6 => "Sáb", 7 => "Dom");
$pizzas_tamanhos = array(3 => "Quardrada", 4 => "Quardradinha");

$conexao = conectabd();

if ($cod_pizzarias>0)
{
	$pizzaria = 'AND cod_pizzarias="'.$cod_pizzarias.'"';
}
function dias_mes($date1, $date2) 
{
	$time1 = strtotime($date1);
   	$time2 = strtotime($date2);
   	$my = date('mY', $time2);

   	$months = array(date('t', $time1));

   	while ($time1 < $time2) 
   	{
      	$time1 = strtotime(date('Y-m-d', $time1).' +1 month');
      	if (date('mY', $time1) != $my && ($time1 < $time2))
      	{
        	$months[] = date('t', $time1);
      	}	
   	}

   	$months[] = date('t', $time2);
   	return $months;
}
?>

<?php 
if($tipo_grafico == 1):
      
if ($periodo_grafico=='semana')
{
	echo '<chart caption="Total dos Pedidos na Semana" lineThickness="1" numberPrefix="R$" showValues="0" formatNumberScale="0" anchorRadius="2" divLineAlpha="20" divLineColor="CC3300" divLineIsDashed="1" showAlternateHGridColor="1" alternateHGridColor="CC3300" shadowAlpha="40" numvdivlines="5" chartRightMargin="35" bgColor="FFFFFF,CC3300" bgAngle="270" bgAlpha="10,10">';
		
		$total_dias = ((strtotime($data_final)-strtotime($data_inicial))/86400)+1;

		$semanas = (INT)($total_dias/7);
		echo'<categories>';
		for ($x=1; $x<=$semanas; $x++)
		{
			echo'<category label="Semana '.$x.'"/>';
		}	
		echo'</categories>';
	
		echo'<dataset seriesName="Total dos Pedidos na Semana" color="1D8BD1" anchorBorderColor="1D8BD1" anchorBgColor="1D8BD1">';
		
		for ($x=7; $x<=$total_dias; $x=$x+7)
		{
			$data_sql_adicional = date("Y-m-d", strtotime($data_inicial."+".$x." days"));
			if ($x<=7)
			{
				$data_sql_inicio = $data_inicial;
			}
			$sql_buscar_numero_pedido = "SELECT SUM(valor_total) AS preco_total FROM ipi_pedidos WHERE data_hora_pedido>='$data_sql_inicio' AND data_hora_pedido<'$data_sql_adicional' AND situacao IN('BAIXADO') $pizzaria";
			$res_buscar_numero_pedido = mysql_query($sql_buscar_numero_pedido);
			$obj_buscar_numero_pedido = mysql_fetch_object($res_buscar_numero_pedido);
			
			if ($obj_buscar_numero_pedido->preco_total=='')
			{
				echo '<set value="0"/>';
			}
			else 
			{
				echo '<set value="'.abs($obj_buscar_numero_pedido->preco_total).'"/>';
			}
			
			$data_sql_inicio = $data_sql_adicional;
		}
		echo'</dataset>';
		
		$data_sql_inicio='';
		$data_sql_adicional='';
		
		echo'<dataset seriesName="Total dos Gastos na Semana" color="DD600E" anchorBorderColor="DD600E" anchorBgColor="DD600E">';
		
		for ($x=7; $x<=$total_dias; $x=$x+7)
		{
			$data_sql_adicional = date("Y-m-d", strtotime($data_inicial."+".$x." days"));
			if ($x<=7)
			{
				$data_sql_inicio = $data_inicial;
			}
			$sql_buscar_gastos = "SELECT SUM(tp.valor) total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.tipo_titulo='PAGAR' AND tp.data_pagamento BETWEEN '" . ($data_sql_inicio) . "' AND '" . ($data_sql_adicional) . "' $pizzaria";
			$res_buscar_gastos = mysql_query($sql_buscar_gastos);
			$obj_buscar_gastos = mysql_fetch_object($res_buscar_gastos);
			
			if ($obj_buscar_gastos->total=='')
			{
				echo '<set value="0"/>';
			}
			else 
			{
				echo '<set value="'.abs($obj_buscar_gastos->total).'"/>';
			}

			$data_sql_inicio = $data_sql_adicional;
		}
		echo'</dataset>';
		
	echo'</chart>';
}
elseif ($periodo_grafico=='dia')
{
	echo '<chart caption="Total dos Pedidos no dia" numdivlines="9" numberPrefix="R$" numVisiblePlot="6" lineThickness="2" formatNumberScale="0" showValues="0" anchorRadius="3" anchorBgAlpha="50" showAlternateVGridColor="1" numVisiblePlot="12" animation="0">';	
		$total_dias = ((strtotime($data_final)-strtotime($data_inicial))/86400)+1;

		echo'<categories>';
		for ($x=0; $x<$total_dias; $x++)
		{
			$data_adicional = date("d/m", strtotime($data_inicial."+".$x." days"));
			
			echo'<category label="'.$data_adicional.'"/>';
		}	
		echo'</categories>';
	
		echo'<dataset seriesName="Total dos Pedidos no dia" color="1D8BD1" anchorBorderColor="1D8BD1" anchorBgColor="1D8BD1">';
		
		for ($x=0; $x<$total_dias; $x++)
		{
			$data_sql_adicional = date("Y-m-d", strtotime($data_inicial."+".$x." days"));
			
			$sql_buscar_numero_pedido = "SELECT SUM(valor_total) AS preco_total FROM ipi_pedidos WHERE DATE(data_hora_pedido)='$data_sql_adicional' AND situacao IN('BAIXADO') $pizzaria";
			$res_buscar_numero_pedido = mysql_query($sql_buscar_numero_pedido);
			$obj_buscar_numero_pedido = mysql_fetch_object($res_buscar_numero_pedido);
			
			if ($obj_buscar_numero_pedido->preco_total=='')
			{
				echo '<set value="0"/>';
			}
			else 
			{
				echo '<set value="'.abs($obj_buscar_numero_pedido->preco_total).'"/>';
			}

		}
		echo'</dataset>';
		
		$data_sql_inicio='';
		$data_sql_adicional='';
		
		echo'<dataset seriesName="Total dos Gastos no dia" color="DD600E" anchorBorderColor="DD600E" anchorBgColor="DD600E">';
		
		for ($x=0; $x<=$total_dias; $x++)
		{
			$data_sql_adicional = date("Y-m-d", strtotime($data_inicial."+".$x." days"));
			
			$sql_buscar_gastos = "SELECT SUM(tp.valor) total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.tipo_titulo='PAGAR' AND tp.data_pagamento='$data_sql_inicio' $pizzaria";
			$res_buscar_gastos = mysql_query($sql_buscar_gastos);
			$obj_buscar_gastos = mysql_fetch_object($res_buscar_gastos);
			
			if ($obj_buscar_gastos->total=='')
			{
				echo '<set value="0"/>';
			}
			else 
			{
				echo '<set value="'.abs($obj_buscar_gastos->total).'"/>';
			}
			
			$data_sql_inicio = $data_sql_adicional;
		}
		echo'</dataset>';
		
	echo'</chart>';
}
elseif ($periodo_grafico=='mes')
{
	echo '<chart caption="Total dos Pedidos no mês" numdivlines="9" numberPrefix="R$" numVisiblePlot="6" lineThickness="2" formatNumberScale="0" showValues="0" anchorRadius="3" anchorBgAlpha="50" showAlternateVGridColor="1" numVisiblePlot="12" animation="0">';	
		$total_dias = ((strtotime($data_final)-strtotime($data_inicial))/86400)+1;
		$arr_dias_mes = dias_mes($data_inicial, $data_final);
		
		$total_meses = count($arr_dias_mes);

		echo'<categories>';
		for ($x=0; $x<$total_meses; $x++)
		{
			if ($x==0)
			{
				$arr_data = explode("-",$data_inicial);
				$data_sql_inicio = $arr_data[0].'-'.$arr_data[1].'-01';
			}
			if ($x>0)
			{
				$cont=1;
			}
			else 
			{
				$cont=0;
			}
			$data_adicional = date("Y-m-d", strtotime($data_sql_inicio."+".$cont." month"));
			$data_exibicao = date("m/Y", strtotime($data_sql_inicio."+".$cont." month"));
		
			echo'<category label="'.$data_exibicao.'"/>';
		
			$data_sql_inicio = $data_adicional;
		}	
		echo'</categories>';
	
		echo'<dataset seriesName="Total dos Pedidos no mês">';
		$total_meses = count($arr_dias_mes);
		for ($x=0; $x<$total_meses; $x++)
		{
			if ($x==0)
			{
				$arr_data = explode("-",$data_inicial);
				$data_sql_inicio = $arr_data[0].'-'.$arr_data[1].'-01';
			}
			if ($x>=0)
			{
				$cont=1;
			}
			else 
			{
				$cont=0;
			}
			$data_sql_adicional = date("Y-m-d", strtotime($data_sql_inicio."+".$cont." month"));
			
			$sql_buscar_numero_pedido = "SELECT SUM(valor_total) AS preco_total FROM ipi_pedidos WHERE data_hora_pedido>='$data_sql_inicio' AND data_hora_pedido<'$data_sql_adicional' AND situacao IN('BAIXADO') $pizzaria";
			$res_buscar_numero_pedido = mysql_query($sql_buscar_numero_pedido);
			$obj_buscar_numero_pedido = mysql_fetch_object($res_buscar_numero_pedido);
//			file_put_contents('pagseguro.log', $sql_buscar_numero_pedido."\r\n", FILE_APPEND);
			if ($obj_buscar_numero_pedido->preco_total=='')
			{
				echo '<set value="0"/>';
			}
			else 
			{
				echo '<set value="'.$obj_buscar_numero_pedido->preco_total.'"/>';
			}
			$data_sql_inicio = $data_sql_adicional;
		}
		echo'</dataset>';
		
		$data_sql_inicio='';
		$data_sql_adicional='';
		
		echo'<dataset seriesName="Total dos Pedidos no mês">';
		$total_meses = count($arr_dias_mes);
		for ($x=0; $x<$total_meses; $x++)
		{
			if ($x==0)
			{
				$arr_data = explode("-",$data_inicial);
				$data_sql_inicio = $arr_data[0].'-'.$arr_data[1].'-01';
			}
			if ($x>=0)
			{
				$cont=1;
			}
			else 
			{
				$cont=0;
			}
			$data_sql_adicional = date("Y-m-d", strtotime($data_sql_inicio."+".$cont." month"));
			
			//$sql_buscar_numero_pedido = "SELECT SUM(valor_total) AS preco_total FROM ipi_pedidos WHERE data_hora_pedido>='$data_sql_inicio' AND data_hora_pedido<'$data_sql_adicional' AND situacao IN('BAIXADO') $pizzaria";
			$sql_buscar_numero_pedido = "SELECT SUM(tp.valor) total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.tipo_titulo='PAGAR' AND tp.data_pagamento>='$data_sql_inicio' AND data_pagamento<'$data_sql_adicional' $pizzaria";
			$res_buscar_numero_pedido = mysql_query($sql_buscar_numero_pedido);
			$obj_buscar_numero_pedido = mysql_fetch_object($res_buscar_numero_pedido);

			if ($obj_buscar_numero_pedido->total=='')
			{
				echo '<set value="0"/>';
			}
			else 
			{
				echo '<set value="'.abs($obj_buscar_numero_pedido->total).'"/>';
			}
			$data_sql_inicio = $data_sql_adicional;
		}
		echo'</dataset>';
		
	echo'</chart>';
}

endif; 

if($tipo_grafico == 4):

if ($periodo_grafico=='semana')
{
	echo '<chart caption="Total dos Pedidos em Combo" lineThickness="1" showValues="0" formatNumberScale="0" anchorRadius="2" divLineAlpha="20" divLineColor="CC3300" divLineIsDashed="1" showAlternateHGridColor="1" alternateHGridColor="CC3300" shadowAlpha="40" numvdivlines="5" chartRightMargin="35" bgColor="FFFFFF,CC3300" bgAngle="270" bgAlpha="10,10">';
		
		$total_dias = ((strtotime($data_final)-strtotime($data_inicial))/86400)+1;

		$semanas = (INT)($total_dias/7);
		echo'<categories>';
		for ($x=1; $x<=$semanas; $x++)
		{
			echo'<category label="Semana '.$x.'"/>';
		}	
		echo'</categories>';
	
		$sql_buscar_formas = "SELECT * FROM ipi_formas_pg";
		$res_buscar_formas = mysql_query($sql_buscar_formas);
		while ($obj_buscar_formas = mysql_fetch_object($res_buscar_formas))
		{
			echo'<dataset seriesName="'.$obj_buscar_formas->forma_pg.'" >';
			
			for ($x=7; $x<=$total_dias; $x=$x+7)
			{
				$data_sql_adicional = date("Y-m-d", strtotime($data_inicial."+".$x." days"));
				if ($x<=7)
				{
					$data_sql_inicio = $data_inicial;
				}
				$sql_buscar_numero_pedido = "SELECT SUM(valor_total) AS preco_total FROM ipi_pedidos p WHERE p.forma_pg='".$obj_buscar_formas->forma_pg."' AND p.data_hora_pedido>='$data_sql_inicio' AND p.data_hora_pedido<'$data_sql_adicional' AND p.situacao IN('BAIXADO') $pizzaria";
				$res_buscar_numero_pedido = mysql_query($sql_buscar_numero_pedido);
				$obj_buscar_numero_pedido = mysql_fetch_object($res_buscar_numero_pedido);
				//file_put_contents('pagseguro.log', $sql_buscar_numero_pedido."\r\n", FILE_APPEND);
				
				if ($obj_buscar_numero_pedido->preco_total=='')
				{
					echo '<set value="0"/>';
				}
				else 
				{
					echo '<set value="'.abs($obj_buscar_numero_pedido->preco_total).'"/>';
				}
				
				$data_sql_inicio = $data_sql_adicional;
			}
			echo'</dataset>';
		}
		
	echo'</chart>';
}
elseif ($periodo_grafico=='dia')
{
	echo '<chart caption="Total de Pizzas da Promoção" numdivlines="9" numVisiblePlot="6" lineThickness="2" formatNumberScale="0" showValues="0" anchorRadius="3" anchorBgAlpha="50" showAlternateVGridColor="1" numVisiblePlot="12" animation="0">';	
		$total_dias = ((strtotime($data_final)-strtotime($data_inicial))/86400)+1;

		echo'<categories>';
		for ($x=0; $x<$total_dias; $x++)
		{
			$data_adicional = date("d/m", strtotime($data_inicial."+".$x." days"));
			
			echo'<category label="'.$data_adicional.'"/>';
		}	
		echo'</categories>';
	
		$sql_buscar_formas = "SELECT * FROM ipi_formas_pg";
		$res_buscar_formas = mysql_query($sql_buscar_formas);
		while ($obj_buscar_formas = mysql_fetch_object($res_buscar_formas))
		{
			echo'<dataset seriesName="'.$obj_buscar_formas->forma_pg.'" >';
			
			for ($x=0; $x<$total_dias; $x++)
			{
				$data_sql_adicional = date("Y-m-d", strtotime($data_inicial."+".$x." days"));
				if ($x<=0)
				{
					$data_sql_inicio = $data_inicial;
				}
				$sql_buscar_numero_pedido = "SELECT SUM(valor_total) AS preco_total FROM ipi_pedidos p WHERE p.forma_pg='".$obj_buscar_formas->forma_pg."' AND p.data_hora_pedido>='$data_sql_inicio' AND p.data_hora_pedido<'$data_sql_adicional' AND p.situacao IN('BAIXADO') $pizzaria";
				$res_buscar_numero_pedido = mysql_query($sql_buscar_numero_pedido);
				$obj_buscar_numero_pedido = mysql_fetch_object($res_buscar_numero_pedido);
				//file_put_contents('pagseguro.log', $sql_buscar_numero_pedido."\r\n", FILE_APPEND);
				
				if ($obj_buscar_numero_pedido->preco_total=='')
				{
					echo '<set value="0"/>';
				}
				else 
				{
					echo '<set value="'.abs($obj_buscar_numero_pedido->preco_total).'"/>';
				}
				
				$data_sql_inicio = $data_sql_adicional;
			}
			echo'</dataset>';
		}
		
	echo'</chart>';
}

elseif ($periodo_grafico=='mes')
{
	echo '<chart caption="Total dos Pedidos no mês" numdivlines="9" numberPrefix="R$" numVisiblePlot="6" lineThickness="2" formatNumberScale="0" showValues="0" anchorRadius="3" anchorBgAlpha="50" showAlternateVGridColor="1" numVisiblePlot="12" animation="0">';	
		$total_dias = ((strtotime($data_final)-strtotime($data_inicial))/86400)+1;
		$arr_dias_mes = dias_mes($data_inicial, $data_final);
		
		$total_meses = count($arr_dias_mes);

		echo'<categories>';
		for ($x=0; $x<$total_meses; $x++)
		{
			if ($x==0)
			{
				$arr_data = explode("-",$data_inicial);
				$data_sql_inicio = $arr_data[0].'-'.$arr_data[1].'-01';
			}
			if ($x>0)
			{
				$cont=1;
			}
			else 
			{
				$cont=0;
			}
			$data_adicional = date("Y-m-d", strtotime($data_sql_inicio."+".$cont." month"));
			$data_exibicao = date("m/Y", strtotime($data_sql_inicio."+".$cont." month"));
		
			echo'<category label="'.$data_exibicao.'"/>';
		
			$data_sql_inicio = $data_adicional;
		}	
		echo'</categories>';
	
		$sql_buscar_formas = "SELECT * FROM ipi_formas_pg";
		$res_buscar_formas = mysql_query($sql_buscar_formas);
		while ($obj_buscar_formas = mysql_fetch_object($res_buscar_formas))
		{
			echo'<dataset seriesName="'.$obj_buscar_formas->forma_pg.'" >';
			$total_meses = count($arr_dias_mes);
			for ($x=0; $x<$total_meses; $x++)
			{
				if ($x==0)
				{
					$arr_data = explode("-",$data_inicial);
					$data_sql_inicio = $arr_data[0].'-'.$arr_data[1].'-01';
				}
				if ($x>=0)
				{
					$cont=1;
				}
				else 
				{
					$cont=0;
				}
				$data_sql_adicional = date("Y-m-d", strtotime($data_sql_inicio."+".$cont." month"));
				
				//$sql_buscar_numero_pedido = "SELECT SUM(valor_total) AS preco_total FROM ipi_pedidos WHERE data_hora_pedido>='$data_sql_inicio' AND data_hora_pedido<'$data_sql_adicional' AND situacao IN('BAIXADO') $pizzaria";
				$sql_buscar_numero_pedido = "SELECT SUM(valor_total) AS preco_total FROM ipi_pedidos p WHERE p.forma_pg='".$obj_buscar_formas->forma_pg."' AND p.data_hora_pedido>='$data_sql_inicio' AND p.data_hora_pedido<'$data_sql_adicional' AND p.situacao IN('BAIXADO') $pizzaria";
				$res_buscar_numero_pedido = mysql_query($sql_buscar_numero_pedido);
				$obj_buscar_numero_pedido = mysql_fetch_object($res_buscar_numero_pedido);
				//file_put_contents('pagseguro.log', $sql_buscar_numero_pedido."\r\n", FILE_APPEND);
				if ($obj_buscar_numero_pedido->preco_total=='')
				{
					echo '<set value="0"/>';
				}
				else 
				{
					echo '<set value="'.$obj_buscar_numero_pedido->preco_total.'"/>';
				}
				$data_sql_inicio = $data_sql_adicional;
			}
			echo'</dataset>';
		}
	echo'</chart>';
}


endif; 
?>

<?php desconectabd($conexao); ?>