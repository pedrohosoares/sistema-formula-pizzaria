<?
require_once '../bd.php';
require_once '../sys/lib/php/formulario.php';
//$mes = '07';
//$ano = '2012';

// o Script é rodado todo dia primeiro de cada mes, então é o mes atual menos 1 mes.
// EDITADO DIA 01/02, agora é rodado todo dia 16, porque a enquete pode ser respondida em 15 dias, alterando os numeros
$data = strtotime(date('Y-m-d').' -1 month');

$mes = date("m",$data);
$ano = date("Y",$data);

if($_GET['m'])
{
  $mes = validar_var_get('m');
}

if($_GET['a'])
{
  $ano = validar_var_get('a');
}
//die('data'.date("Y",$data).' '.date('m',$data).' '.date('d',$data));
/*$mes = 04;
$ano = 2012;*/




$filtro_pizzaria = "";
$filtro_pizzaria_excluir = "";
$filtro_ingrediente_excluir = "";
if($_GET['p'])
{
  $filtro_pizzaria = "WHERE cod_pizzarias in (".validar_var_get('p').")";
  $filtro_pizzaria_excluir = " AND cod_pizzarias in (".validar_var_get('p').")";
}


$con = conectar_bd(); 
$sql_pesquisa_pizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao='ATIVO' $filtro_pizzaria_excluir";
$res_pesquisa_pizzarias = mysql_query($sql_pesquisa_pizzarias);//"SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas
//echo "Sql Busca Pizzarias :".$sql_pesquisa_pizzarias;
while($obj_pesquisa_pizzarias = mysql_fetch_object($res_pesquisa_pizzarias))
{
	$sql_pedidos_mes = "SELECT * FROM ipi_pedidos WHERE cod_pizzarias =".$obj_pesquisa_pizzarias->cod_pizzarias." AND MONTH(data_hora_pedido) = $mes AND YEAR(data_hora_pedido) = $ano AND situacao = 'BAIXADO' $filtro_pizzaria_excluir";
	$res_pedidos_mes = mysql_query($sql_pedidos_mes);
	$arr_pedidos_mes = array();
	$origem_net = 0;
	$origem_out = 0;
	$cont_pizza = 0;
	$cont_pedidos = 0;
	$valor_total = 0;
	$total_pizzas_pagas = 0;
	$arr_tamanhos = array();
/*	$arr_tamanhos[] = array("1" =>"qtd");
	$arr_tamanhos[] = array("2" =>"qtd");
	$arr_tamanhos[] = array("3" =>"qtd");
	$arr_tamanhos[] = array("4" =>"qtd");
	$arr_tamanhos[] = array("5" =>"qtd");
	$arr_tamanhos[] = array("6" =>"qtd");
	$arr_tamanhos[] = array("7" =>"qtd");
	$arr_tamanhos[] = array("8" =>"qtd");
	$arr_tamanhos[] = array("9" =>"qtd");*/


	$arr_tamanhos[1] = array("qtd");
	$arr_tamanhos[2] = array("qtd");
	$arr_tamanhos[3] = array("qtd");
	$arr_tamanhos[4] = array("qtd");
	$arr_tamanhos[5] = array("qtd");
	$arr_tamanhos[6] = array("qtd");
	$arr_tamanhos[7] = array("qtd");
	$arr_tamanhos[8] = array("qtd");
	$arr_tamanhos[9] = array("qtd");
	$total_pizzas = 0;
	while($obj_pedidos_mes = mysql_fetch_object($res_pedidos_mes))
	{
		$arr_pedidos_mes[] = $obj_pedidos_mes->cod_pedidos;
		if($obj_pedidos_mes->origem_pedido=="NET")
		{
			$origem_net ++;
			$origem_out ++;
		}
		else
		{
			$origem_out ++;
		}
		
    $valor_total += $obj_pedidos_mes->valor_total;
/*
    $sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica,unidade, tipo) VALUES (%d,DATE_SUB(NOW(), INTERVAL 1 MONTH) ,now(),'%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $ticket_medio , "Ticket Médio","dinheiro" ); 
	  $res_cadastra = mysql_query($sql_cadastra);

	  $sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica,unidade, tipo) VALUES (%d,DATE_SUB(NOW(), INTERVAL 1 MONTH) ,now(),'%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $aproveitamento_pizzaria , "Eficiência da Pizzaria","porcentagem" ); 
	  $res_cadastra = mysql_query($sql_cadastra);

	  $sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica,unidade, tipo) VALUES (%d,DATE_SUB(NOW(), INTERVAL 1 MONTH) ,now(),'%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $media_pizza_pedido , "Média de Pizzas por Pedidos","pizzas" ); 
	  $res_cadastra = mysql_query($sql_cadastra);

	  $sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica,unidade, tipo) VALUES (%d,DATE_SUB(NOW(), INTERVAL 1 MONTH) ,now(),'%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $porcentagem_online , "Porcentagem de Pedidos Online","porcentagem" ); 
	  $res_cadastra = mysql_query($sql_cadastra);
*/
		$sql_pizzas_pedidos = "SELECT count(*) as pizzas FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos =".$obj_pedidos_mes->cod_pedidos;
		$res_pizzas_pedidos = mysql_query($sql_pizzas_pedidos);
		$obj_pizzas_pedidos = mysql_fetch_object($res_pizzas_pedidos);
		$cont_pizza += $obj_pizzas_pedidos->pizzas;
		$cont_pedidos ++;

		$sql_pizzas_paga = "SELECT count(cod_pedidos_pizzas)as pagas,cod_tamanhos  FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos =".$obj_pedidos_mes->cod_pedidos." and promocional = 0 and fidelidade = 0 group by cod_tamanhos order by cod_tamanhos";
		$res_pizzas_paga = mysql_query($sql_pizzas_paga);
		while($obj_pizzas_paga = mysql_fetch_object($res_pizzas_paga))
		{

/*			if($obj_pizzas_paga->cod_tamanhos=='3')
			{
				$quantidade_quadradas += $obj_pizzas_paga->pagas;
			}
			if($obj_pizzas_paga->cod_tamanhos=='4')
			{
				$quantidade_quadradinhas += $obj_pizzas_paga->pagas;
			}
			if($obj_pizzas_paga->cod_tamanhos=='5')
			{
				$quantidade_quadradas_six += $obj_pizzas_paga->pagas;
			}*/
			$total_pizzas_pagas += $obj_pizzas_paga->pagas;
			$arr_tamanhos[$obj_pizzas_paga->cod_tamanhos]['qtd'] = $arr_tamanhos[$obj_pizzas_paga->cod_tamanhos]['qtd'] + $obj_pizzas_paga->pagas;
		}
		//$sql_pizzas_promocionais = "SELECT count(cod_pedidos_pizzas)as promocionais FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos =".$obj_pedidos_mes->cod_pedidos." and promocional = 1";
		//$res_pizzas_promocionais = mysql_query($sql_pizzas_promocionais);

		//$obj_pizzas_promocionais = mysql_fetch_object($res_pizzas_promocionais);
		//$quantidade_promocionais += $obj_pizzas_promocionais->promocionais;


	}
	$cont_pizza_total = $total_pizzas_pagas;

	foreach($arr_tamanhos as $cod => $arr_dados)
	{
		if($arr_tamanhos[$cod]['qtd']=="")
			$arr_tamanhos[$cod]['qtd'] = 0;

		$arr_tamanhos[$cod]['porc'] = (($arr_dados['qtd']*100/$cont_pizza_total)*100)/100;
	}
	//$porcentagem_quadradas = (($quantidade_quadradas*100/$cont_pizza_total)*100)/100;
	//$porcentagem_quadradinhas = (($quantidade_quadradinhas*100/$cont_pizza_total)*100)/100;
	//$porcentagem_quadradas_six = (($quantidade_quadradas_six*100/$cont_pizza_total)*100)/100;
	//$porcentagem_promocionais = (($quantidade_promocionais*100/$cont_pizza_total)*100)/100;
	//$media_pizza_pedido = round((($cont_pizza / $cont_pedidos)*100)/100);

	$media_pizza_pedido = ((($cont_pizza / $cont_pedidos)*100))/100;
	$porcentagem_online = (($origem_net * 100) / $origem_out);
	$ticket_medio = (($valor_total / $cont_pedidos)*100)/100;
	
	$arr_cod_respostas_boas = array(8,9,12,13,16,17); // são satisfatorias e boas
	$sql_buscar_respostas = "SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas in(3,4)";// 3 e 4 são sobre o produto e sobre a entrega
	//echo "<br/>".$sql_buscar_respostas."<br/>";
	$res_buscar_respostas = mysql_query($sql_buscar_respostas);
	$total = 0;
	$total_boas = 0;
	while($obj_buscar_respostas = mysql_fetch_object($res_buscar_respostas))
	{
		$sql_buscar_respostas_clientes = "SELECT count(*) AS total FROM ipi_clientes_ipi_enquete_respostas WHERE cod_enquete_respostas = ".$obj_buscar_respostas->cod_enquete_respostas." AND cod_pedidos IN (".implode(',',$arr_pedidos_mes).")";
		//echo "<br/>Sql Busca Respostas clientes".$obj_busca_respostas->resposta." :".$sql_buscar_respostas_clientes;
		$res_buscar_respostas_clientes = mysql_query($sql_buscar_respostas_clientes);
		$obj_buscar_respostas_clientes = mysql_fetch_object($res_buscar_respostas_clientes);
		$total += $obj_buscar_respostas_clientes->total;
		
		if(in_array($obj_buscar_respostas->cod_enquete_respostas, $arr_cod_respostas_boas) !== false)
		{
			$total_boas += $obj_buscar_respostas_clientes->total;
		}
	
	}
	$aproveitamento_pizzaria = (($total_boas * 100) / $total);
	echo "<h1>".$obj_pesquisa_pizzarias->nome."</h1>";
	echo "<br/><h3>Aproveitamento da pizzaria : ".bd2moeda($aproveitamento_pizzaria)."%</h3>";
	echo "<br/>Ticket Medio : ".bd2moeda($ticket_medio)."                  (valor total: ".$valor_total."     pedidos:".$cont_pedidos.")";
	echo "<br/>Porcentagem Online : ".bd2moeda($porcentagem_online)."%"."                  (net: ".$origem_net."     total:".$origem_out.")";;
	echo "<br/>Media de pizzas por pedido : ".bd2moeda($media_pizza_pedido)." pizzas                 (pizzas: ".$cont_pizza."    pedidos:".$cont_pedidos;
	
	//echo "<br/>Porcentagem de Pizzas promocionais : ".$porcentagem_promocionais."                      (Vendidas: ".$quantidade_promocionais." pizzas: ".$cont_pizza.")";		
	echo "<br/>Porcentagem de Pizzas Quadradas Pagas : ".$porcentagem_quadradas."                       (Vendidas: ".$quantidade_quadradas." pizzas: ".$cont_pizza.")";
	echo "<br/>Porcentagem de Pizzas Quadrada Six Pagas : ".$porcentagem_quadradinhas."                   (Vendidas: ".$quantidade_quadradinhas." pizzas: ".$cont_pizza.")";
	echo "<br/>Porcentagem de Pizzas Quadradinhas Pagas : ".$porcentagem_quadradas_six."                      (Vendidas: ".$quantidade_quadradas_six." pizzas: ".$cont_pizza.")";
	echo "<br/><br/><br/>";
	$ultimo_dia = $ano.'-'.$mes.'-'.date('t',strtotime($ano.'-'.$mes.'-01'));
	//echo $ultimo_dia;die();
	$primeiro_dia = $ano.'-'.$mes.'-01';
	//echo "///////////////?DIAS $primeiro_dia------------$ultimo_dia /////////////////////";
	$sql_buscar_registros = "SELECT * from ipi_pizzarias_estatisticas where data_inicio = '$primeiro_dia' and data_fim = '$ultimo_dia' and cod_pizzarias = '$obj_pesquisa_pizzarias->cod_pizzarias' and tipo='franquias'";
	$res_buscar_registros = mysql_query($sql_buscar_registros);
	$num_estatisticas = mysql_num_rows($res_buscar_registros);
	//die($sql_buscar_registros);
	if($num_estatisticas>0)
	{
		//die('drops');
		$sql_dropar_estatisticas = "DELETE FROM ipi_pizzarias_estatisticas where data_inicio = '$primeiro_dia' and data_fim = '$ultimo_dia' and cod_pizzarias = '$obj_pesquisa_pizzarias->cod_pizzarias' and tipo='franquias'";
		$res_dropar_estatisticas = mysql_query($sql_dropar_estatisticas);
	}
	//die('ndrops');
	$sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica, tipo) VALUES (%d,'$primeiro_dia','$ultimo_dia','%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $ticket_medio , "2","franquias");
	//echo "<br/>".$sql_cadastra."<br/>";
	$res_cadastra = mysql_query($sql_cadastra);

	$sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica, tipo) VALUES (%d,'$primeiro_dia','$ultimo_dia','%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $aproveitamento_pizzaria , "1","franquias");
	$res_cadastra = mysql_query($sql_cadastra);

	$sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica, tipo) VALUES (%d,'$primeiro_dia','$ultimo_dia','%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $media_pizza_pedido , "3","franquias");
	$res_cadastra = mysql_query($sql_cadastra);

	$sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica, tipo) VALUES (%d,'$primeiro_dia','$ultimo_dia','%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $porcentagem_online , "4" ,"franquias");
	$res_cadastra = mysql_query($sql_cadastra);

/*		$sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica, tipo) VALUES (%d,'$primeiro_dia','$ultimo_dia','%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $porcentagem_quadradas, "5" ,"franquias");
	$res_cadastra = mysql_query($sql_cadastra);
		$sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica, tipo) VALUES (%d,'$primeiro_dia','$ultimo_dia','%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $porcentagem_quadradinhas , "6" ,"franquias");
	$res_cadastra = mysql_query($sql_cadastra);

		$sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica, tipo) VALUES (%d,'$primeiro_dia','$ultimo_dia','%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $porcentagem_quadradas_six , "7" ,"franquias");
	$res_cadastra = mysql_query($sql_cadastra);*/

	foreach($arr_tamanhos as $cod => $arr_dados)
	{
		$sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica, tipo) VALUES (%d,'$primeiro_dia','$ultimo_dia','%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $arr_dados['porc'], $cod+4,"franquias");
		$res_cadastra = mysql_query($sql_cadastra);
	}
	echo "<br/><Br/><pre>";
	print_r($arr_tamanhos);
	echo "</pre><br/><Br/>";
			//$sql_cadastra = sprintf("INSERT INTO ipi_pizzarias_estatisticas (cod_pizzarias, data_inicio, data_fim, valor,estatistica,unidade, tipo) VALUES (%d,'$primeiro_dia','$ultimo_dia','%s','%s','%s')", $obj_pesquisa_pizzarias->cod_pizzarias, $porcentagem_promocionais , "Porcentagem de Pizzas Promocionais","porcentagem" );
	//$res_cadastra = mysql_query($sql_cadastra);

}




desconectar_bd($con);
?>
