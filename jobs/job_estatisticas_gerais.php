<?php
require_once '../sys/lib/php/formulario.php';
require_once '../bd.php';

$codigo_cliente_job = NOME_FANTASIA;  								

$cod_estatisticas_faturamento = "1";
$cod_estatisticas_numero_pedidos = "2";
$cod_estatisticas_numero_clientes = "3";

// Conectar no BD Local
$conexao_bd_cliente = conectar_bd();


// Conectar no BD de estatísticas
// ischefstats.internetsistemas.com.br ischefstats Bdischef@94statS
$conexao_bd_estatisticas = mysql_connect("ischefstats.internetsistemas.com.br", "ischefstats", "Bdischef@94statS") or die("<center><br><font color=red><b><h1>Atenção</h1><b><br><br>Problemas na conexão com a base de dados. <br>Verifique se o MySQL está funcionando corretamente.</font></center>");
mysql_select_db("ischefstats", $conexao_bd_estatisticas) or die("<center><br><font color=red><b><h1>Atenção</h1><b><br><br>Base de dados (" . BD_NOME . ") inexistente ou configurada incorretamente.</font></center>");
//mysql_set_charset('latin1') or die("<center><br><font color=red><b><h1>Atenção</h1><b><br><br>Não foi possível selecionar o charset apropriado.</font></center>");



echo "<br>".date("d/m/Y H:i:s");
$dia = date("d");
if($_GET['d'])
{
  $dia = $_GET['d'];
}

$mes = date("m");
if($_GET['m'])
{
  $mes = $_GET['m'];
}

$ano = date("Y");
if($_GET['a'])
{
  $ano = $_GET['a'];
}


$data_inicial = strtotime(date("$ano-$mes-$dia"));
$data_final = strtotime(date("$ano-$mes-$dia"));

$data_inicial = date("Y-m-d 00:00:00",$data_inicial);
$data_final = date("Y-m-d 23:59:59",$data_final);

$data_inicial_sem_hora = date("Y-m-d",strtotime(date("$ano-$mes-$dia")) );
$data_final_sem_hora = date("Y-m-d",strtotime(date("$ano-$mes-$dia")) );

//echo "<br>di: ".$data_inicial;


// Descobrir o Código de Franqueadora deste cliente
$sql_estatisticas = "SELECT cod_franqueadoras, nome FROM franqueadoras WHERE codigo_cliente_job = '".$codigo_cliente_job."'";
$res_estatisticas = mysql_query($sql_estatisticas, $conexao_bd_estatisticas);
$obj_estatisticas = mysql_fetch_object($res_estatisticas);
$cod_franqueadoras = $obj_estatisticas->cod_franqueadoras;
//echo "<br>Franq: ".$obj_estatisticas->cod_franqueadoras." - ".$obj_estatisticas->nome;

$arr_pizzarias = array();
// Cadastrar as Pizzarias, no Stats
$sql_pizzarias = "SELECT cod_pizzarias, nome, endereco, numero, bairro, cidade, estado, cep, complemento, lat, lon, situacao FROM ipi_pizzarias";
$res_pizzarias = mysql_query($sql_pizzarias, $conexao_bd_cliente);
$num_pizzarias = mysql_num_rows($res_pizzarias);
for($a=0; $a<$num_pizzarias; $a++)
{
	$obj_pizzarias = mysql_fetch_object($res_pizzarias);
	$arr_pizzarias[$a]["cod_pizzarias"] = $obj_pizzarias->cod_pizzarias;

	$sql_consulta_pizzaria = "SELECT cod_franquias FROM franquias WHERE cod_franqueadoras = '".$cod_franqueadoras."' AND cod_pizzarias_job = '".$obj_pizzarias->cod_pizzarias."'";
	$res_consulta_pizzaria = mysql_query($sql_consulta_pizzaria, $conexao_bd_estatisticas);
	$obj_consulta_pizzaria = mysql_fetch_object($res_consulta_pizzaria);
	$arr_pizzarias[$a]["cod_franquias"] = $obj_consulta_pizzaria->cod_franquias;
	
	$num_consulta_pizzaria = mysql_num_rows($res_consulta_pizzaria);
	if ($num_consulta_pizzaria == 0)
	{
		$sql_inserir_franquia = "INSERT INTO franquias (cod_franqueadoras, cod_pizzarias_job, nome, endereco, numero, bairro, cidade, estado, cep, complemento, latitude, longitude, situacao) VALUES (".$cod_franqueadoras.", ".$obj_pizzarias->cod_pizzarias.", '".$obj_pizzarias->nome."', '".$obj_pizzarias->endereco."', '".$obj_pizzarias->numero."', '".$obj_pizzarias->bairro."', '".$obj_pizzarias->cidade."', '".$obj_pizzarias->estado."', '".$obj_pizzarias->cep."', '".$obj_pizzarias->complemento."', '".$obj_pizzarias->lat."', '".$obj_pizzarias->lon."', '".$obj_pizzarias->situacao."')";
		//echo "<Br>sql_inserir_franquia: ".$sql_inserir_franquia;
		$res_inserir_franquia = mysql_query($sql_inserir_franquia, $conexao_bd_estatisticas);
		$arr_pizzarias[$a]["cod_franquias"] = mysql_insert_id();
	}
}


$arr_faturamento = array();
for($a=0; $a<$num_pizzarias; $a++)
{
	$arr_faturamento[$a]["cod_pizzarias"] = $arr_pizzarias[$a]["cod_pizzarias"];
	$arr_faturamento[$a]["cod_franquias"] = $arr_pizzarias[$a]["cod_franquias"];

	$sql_calculos = "SELECT SUM(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_faturamento[$a]["cod_pizzarias"]."' AND data_hora_pedido <= '$data_final'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_faturamento[$a]["total_acumulado"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); //O banco volta nulo, para SQL funcionar tem q ser zero.

	$sql_calculos = "SELECT SUM(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_faturamento[$a]["cod_pizzarias"]."' AND data_hora_pedido >= '$data_inicial'  AND data_hora_pedido <= '$data_final'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_faturamento[$a]["total_periodo"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	$sql_calculos = "SELECT SUM(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_faturamento[$a]["cod_pizzarias"]."' AND data_hora_pedido >= '$data_inicial'  AND data_hora_pedido <= '$data_final' AND origem_pedido = 'NET'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_faturamento[$a]["total_net"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	$sql_calculos = "SELECT SUM(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_faturamento[$a]["cod_pizzarias"]."' AND data_hora_pedido >= '$data_inicial'  AND data_hora_pedido <= '$data_final' AND origem_pedido = 'TEL'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_faturamento[$a]["total_tel"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	$sql_calculos = "SELECT SUM(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_faturamento[$a]["cod_pizzarias"]."' AND data_hora_pedido >= '$data_inicial'  AND data_hora_pedido <= '$data_final' AND tipo_entrega = 'Entrega'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_faturamento[$a]["total_entrega"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	$sql_calculos = "SELECT SUM(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_faturamento[$a]["cod_pizzarias"]."' AND data_hora_pedido >= '$data_inicial'  AND data_hora_pedido <= '$data_final' AND tipo_entrega = 'Balcão'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_faturamento[$a]["total_balcao"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	//echo "<Br>$a: ".$sql_calculos." - ".$obj_calculos->total;
}

for($a=0; $a<$num_pizzarias; $a++)
{
	$sql_deletar_valores = "DELETE FROM estatisticas_valores WHERE cod_estatisticas = ".$cod_estatisticas_faturamento." AND cod_franquias = ".$arr_faturamento[$a]["cod_franquias"]." AND cod_franqueadoras = ".$cod_franqueadoras." AND data_inicial = '".$data_inicial_sem_hora."' AND data_final = '".$data_final_sem_hora."'";
	$res_deletar_valores = mysql_query($sql_deletar_valores, $conexao_bd_estatisticas);
	//echo "<br><br>".$sql_deletar_valores;
	$sql_valores = "INSERT INTO estatisticas_valores (cod_estatisticas, cod_franquias, cod_franqueadoras, data_inicial, data_final, total_acumulado, total_periodo, total_net, total_tel, total_entrega, total_balcao) VALUES (".$cod_estatisticas_faturamento.", ".$arr_faturamento[$a]["cod_franquias"].", ".$cod_franqueadoras.", '".$data_inicial."', '".$data_final."', ".$arr_faturamento[$a]["total_acumulado"].", ".$arr_faturamento[$a]["total_periodo"].", ".$arr_faturamento[$a]["total_net"].", ".$arr_faturamento[$a]["total_tel"].", ".$arr_faturamento[$a]["total_entrega"].", ".$arr_faturamento[$a]["total_balcao"].")";
	$res_valores = mysql_query($sql_valores, $conexao_bd_estatisticas);
	//echo "<br>".$sql_valores;
}

/*
echo "<pre>";
print_r($arr_faturamento);
echo "</pre>";

*/


$arr_qtde_pedidos = array();
for($a=0; $a<$num_pizzarias; $a++)
{
	$arr_qtde_pedidos[$a]["cod_pizzarias"] = $arr_pizzarias[$a]["cod_pizzarias"];
	$arr_qtde_pedidos[$a]["cod_franquias"] = $arr_pizzarias[$a]["cod_franquias"];

	$sql_calculos = "SELECT COUNT(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_qtde_pedidos[$a]["cod_pizzarias"]."' AND data_hora_pedido <= '$data_final'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_qtde_pedidos[$a]["total_acumulado"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	$sql_calculos = "SELECT COUNT(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_qtde_pedidos[$a]["cod_pizzarias"]."' AND  	data_hora_pedido >= '$data_inicial'  AND data_hora_pedido <= '$data_final'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_qtde_pedidos[$a]["total_periodo"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	$sql_calculos = "SELECT COUNT(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_qtde_pedidos[$a]["cod_pizzarias"]."' AND  	data_hora_pedido >= '$data_inicial'  AND data_hora_pedido <= '$data_final' AND origem_pedido = 'NET'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_qtde_pedidos[$a]["total_net"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	$sql_calculos = "SELECT COUNT(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_qtde_pedidos[$a]["cod_pizzarias"]."' AND  	data_hora_pedido >= '$data_inicial'  AND data_hora_pedido <= '$data_final' AND origem_pedido = 'TEL'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_qtde_pedidos[$a]["total_tel"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	$sql_calculos = "SELECT COUNT(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_qtde_pedidos[$a]["cod_pizzarias"]."' AND  	data_hora_pedido >= '$data_inicial'  AND data_hora_pedido <= '$data_final' AND tipo_entrega = 'Entrega'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_qtde_pedidos[$a]["total_entrega"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	$sql_calculos = "SELECT COUNT(valor_total) as total FROM ipi_pedidos WHERE cod_pizzarias = '".$arr_qtde_pedidos[$a]["cod_pizzarias"]."' AND  	data_hora_pedido >= '$data_inicial'  AND data_hora_pedido <= '$data_final' AND tipo_entrega = 'Balcão'";
	$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
	$obj_calculos = mysql_fetch_object($res_calculos);
	$arr_qtde_pedidos[$a]["total_balcao"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

	//echo "<Br>$a: ".$sql_calculos." - ".$obj_calculos->total;
}

for($a=0; $a<$num_pizzarias; $a++)
{
	$sql_deletar_valores = "DELETE FROM estatisticas_valores WHERE cod_estatisticas = ".$cod_estatisticas_numero_pedidos." AND cod_franquias = ".$arr_qtde_pedidos[$a]["cod_franquias"]." AND cod_franqueadoras = ".$cod_franqueadoras." AND data_inicial = '".$data_inicial_sem_hora."' AND data_final = '".$data_final_sem_hora."'";
	$res_deletar_valores = mysql_query($sql_deletar_valores, $conexao_bd_estatisticas);
	//echo "<br><br>".$sql_deletar_valores;
	$sql_valores = "INSERT INTO estatisticas_valores (cod_estatisticas, cod_franquias, cod_franqueadoras, data_inicial, data_final, total_acumulado, total_periodo, total_net, total_tel, total_entrega, total_balcao) VALUES (".$cod_estatisticas_numero_pedidos.", ".$arr_qtde_pedidos[$a]["cod_franquias"].", ".$cod_franqueadoras.", '".$data_inicial."', '".$data_final."', ".$arr_qtde_pedidos[$a]["total_acumulado"].", ".$arr_qtde_pedidos[$a]["total_periodo"].", ".$arr_qtde_pedidos[$a]["total_net"].", ".$arr_qtde_pedidos[$a]["total_tel"].", ".$arr_qtde_pedidos[$a]["total_entrega"].", ".$arr_qtde_pedidos[$a]["total_balcao"].")";
	$res_valores = mysql_query($sql_valores, $conexao_bd_estatisticas);
	//echo "<br>".$sql_valores;
}

/*
echo "<pre>";
print_r($arr_qtde_pedidos);
echo "</pre>";
*/

$arr_qtde_clientes = array();

$sql_calculos = "SELECT COUNT(cod_clientes) AS total FROM ipi_clientes c WHERE c.cod_clientes AND data_hora_cadastro <= '$data_final'";
$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
$obj_calculos = mysql_fetch_object($res_calculos);
$arr_qtde_clientes["total_acumulado"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

$sql_calculos = "SELECT COUNT(cod_clientes) AS total FROM ipi_clientes c WHERE c.cod_clientes AND data_hora_cadastro >= '$data_inicial' AND data_hora_cadastro <= '$data_final'";
$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
$obj_calculos = mysql_fetch_object($res_calculos);
$arr_qtde_clientes["total_periodo"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

$sql_calculos = "SELECT COUNT(cod_clientes) AS total FROM ipi_clientes c WHERE c.cod_clientes AND data_hora_cadastro >= '$data_inicial' AND data_hora_cadastro <= '$data_final' AND origem_cliente = 'TEL' ";
$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
$obj_calculos = mysql_fetch_object($res_calculos);
$arr_qtde_clientes["total_tel"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 

$sql_calculos = "SELECT COUNT(cod_clientes) AS total FROM ipi_clientes c WHERE c.cod_clientes AND data_hora_cadastro >= '$data_inicial' AND data_hora_cadastro <= '$data_final' AND origem_cliente = 'NET' ";
$res_calculos = mysql_query($sql_calculos, $conexao_bd_cliente);
$obj_calculos = mysql_fetch_object($res_calculos);
$arr_qtde_clientes["total_net"] = ($obj_calculos->total=="" ? 0 : $obj_calculos->total); 
//echo "<Br> ".$sql_calculos;


$sql_deletar_valores = "DELETE FROM estatisticas_valores WHERE cod_estatisticas = ".$cod_estatisticas_numero_clientes." AND cod_franqueadoras = ".$cod_franqueadoras." AND data_inicial = '".$data_inicial_sem_hora."' AND data_final = '".$data_final_sem_hora."'";
$res_deletar_valores = mysql_query($sql_deletar_valores, $conexao_bd_estatisticas);
//echo "<br><br>".$sql_deletar_valores;
$sql_valores = "INSERT INTO estatisticas_valores (cod_estatisticas, cod_franquias, cod_franqueadoras, data_inicial, data_final, total_acumulado, total_periodo, total_net, total_tel, total_entrega, total_balcao) VALUES (".$cod_estatisticas_numero_clientes.", -1, ".$cod_franqueadoras.", '".$data_inicial."', '".$data_final."', ".$arr_qtde_clientes["total_acumulado"].", ".$arr_qtde_clientes["total_periodo"].", ".$arr_qtde_clientes["total_net"].", ".$arr_qtde_clientes["total_tel"].", -1, -1)";
$res_valores = mysql_query($sql_valores, $conexao_bd_estatisticas);
//echo "<br>".$sql_valores;
/*
echo "<pre>";
print_r($arr_qtde_clientes);
echo "</pre>";
*/

echo "<br>".date("d/m/Y H:i:s");


?>