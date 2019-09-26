<?
/*
ipi_caixa
ipi_caixa_ajax
financeiro classe
ipi_baixa_individual


*/
require_once '../bd.php';
$cone = conectabd();


/*$sql_atualizar_formas_pag = "UPDATE ipi_titulos_parcelas set forma_pagamento = fp.forma_pg  FROM ipi_formas_pg fp inner join ipi_pedidos p on p.forma_pg = fp.forma_pg inner join ipi_titulos t on p.cod_pedidos = t.cod_pedidos inner join  on tp.cod_titulos = t.cod_titulos WHERE p.cod_pedidos,cod_formas_pg,fp.forma_pg,t.cod_titulos,cod_titulos_parcelas"


"UPDATE ipi_titulos_parcelas tp inner join ipi_titulos t on t.cod_titulos = tp.cod_titulos inner join ipi_pedidos p on p.cod_pedidos = t.cod_pedidos inner join ipi_formas_pg fp on fp.forma_pg = p.forma_pg set tp.forma_pagamento = fp.forma_pg , tp.cod_formas_pg = fp.cod_formas_pg"

"UPDATE ipi_titulos_parcelas tp inner join ipi_titulos t on t.cod_titulos = tp.cod_titulos inner join ipi_pedidos p on p.cod_pedidos = t.cod_pedidos inner join ipi_formas_pg fp set tp.forma_pagamento = 'VISANET' , tp.cod_formas_pg = fp.cod_formas_pg WHERE p.forma_pg = 'VISANET1' and fp.forma_pg='VISANET'"
"
"SELECT p.cod_pedidos,cod_formas_pg,fp.forma_pg,t.cod_titulos,cod_titulos_parcelas FROM ipi_formas_pg fp inner join ipi_pedidos p on p.forma_pg = fp.forma_pg inner join ipi_titulos t on p.cod_pedidos = t.cod_pedidos inner join ipi_titulos_parcelas tp on tp.cod_titulos = t.cod_titulos WHERE 1"*/

$logar = true;

$file = 'log_job_pagar.txt';
$data_hoje = date("Y-m-d H:i:s");
$texto = "//////////////////////$data_hoje///////////////////////////////////////";
echo "<br/><br/>$texto";
if($logar) file_put_contents($file, $texto, FILE_APPEND);

$sql_buscar_formas_cartao = "SELECT fpp.cod_formas_pg,fpp.cod_pizzarias,fpp.cod_bancos from ipi_formas_pg_pizzarias fpp where fpp.taxa>0 and fpp.prazo>0 and fpp.cod_bancos >0 order by cod_pizzarias";
$res_buscar_formas_cartao = mysql_query($sql_buscar_formas_cartao);
while($obj_buscar_formas_cartao = mysql_fetch_object($res_buscar_formas_cartao))
{
	//$cods[] = $obj_buscar_formas_cartao->cod_formas_pg;
	//$str_cods = implode(',',$cods);

	echo "<br/><br/>/////////////////////////////////////////";
	echo "<br/>Dados da forma de pagamento";
	echo "<br/>Cod Forma de pagamento = '".$obj_buscar_formas_cartao->cod_formas_pg."'";
	echo "<br/>Cod Pizzaria = '".$obj_buscar_formas_cartao->cod_pizzarias."'";
	echo "<br/>Cod bancos = '".$obj_buscar_formas_cartao->cod_bancos."'";
	echo "<br/><br/>Dados das parcelas<br/>";
	echo "Cod parcela | cod_titulos | cod_pizzarias | cod_pedidos | situacao_antes | vencimento_antes | pagamento_antes | forma pag|<br/>";

		$texto = "\n \n /////////////////////////////////////////";
		if($logar) file_put_contents($file, $texto, FILE_APPEND);
	$texto =  "\n Dados da forma de pagamento";
	if($logar) file_put_contents($file, $texto, FILE_APPEND);
	$texto =  "\n Cod Forma de pagamento = '".$obj_buscar_formas_cartao->cod_formas_pg."'";
	if($logar) file_put_contents($file, $texto, FILE_APPEND);
	$texto =  "\n Cod Pizzaria = '".$obj_buscar_formas_cartao->cod_pizzarias."'";
	if($logar) file_put_contents($file, $texto, FILE_APPEND);
	$texto =  "\n Cod bancos = '".$obj_buscar_formas_cartao->cod_bancos."'";
	if($logar) file_put_contents($file, $texto, FILE_APPEND);
	$texto =  "\n \n Dados das parcelas\n ";
	if($logar) file_put_contents($file, $texto, FILE_APPEND);
	$texto =  "Cod parcela | cod_titulos | cod_pizzarias | cod_pedidos | situacao_antes | vencimento_antes | pagamento_antes | forma pag|\n ";
	if($logar) file_put_contents($file, $texto, FILE_APPEND);

	$sql_selecionar_titulos = "SELECT tp.cod_titulos_parcelas as cod_parcelas, t.cod_titulos as cod_titulos, p.cod_pizzarias as cod_pizzaria,p.cod_pedidos as cod_pedidos, tp.situacao as situacao_antes, tp.data_vencimento as vencimento_antes, tp.data_pagamento as pagamento_antes, tp.cod_formas_pg as forma_pagamento from ipi_titulos_parcelas tp inner join ipi_titulos t on t.cod_titulos = tp.cod_titulos inner join ipi_pedidos p on p.cod_pedidos = t.cod_pedidos where tp.situacao = 'ABERTO'  AND tp.data_vencimento = DATE_FORMAT(NOW(), '%Y-%m-%d') AND tp.data_pagamento = DATE_FORMAT(NOW(), '%Y-%m-%d') AND p.cod_pizzarias = '".$obj_buscar_formas_cartao->cod_pizzarias."' AND tp.cod_formas_pg = '".$obj_buscar_formas_cartao->cod_formas_pg."'";
	$res_selecionar_titulos = mysql_query($sql_selecionar_titulos);
	while($obj_selecionar_titulos = mysql_fetch_object($res_selecionar_titulos))
	{
		$cod_par = $obj_selecionar_titulos->cod_parcelas;
		$cod_tit = $obj_selecionar_titulos->cod_titulos;
		$cod_piz = $obj_selecionar_titulos->cod_pizzaria;
		$cod_ped = $obj_selecionar_titulos->cod_pedidos;
		$situaca = $obj_selecionar_titulos->situacao_antes;
		$venci_a = $obj_selecionar_titulos->vencimento_antes;
		$paga_a = $obj_selecionar_titulos->pagamento_antes;
		$forma_p = $obj_selecionar_titulos->forma_pagamento;

		$texto =  "\n  $cod_par | $cod_tit | $cod_piz | $cod_ped | $situaca | $venci_a | $paga_a | $forma_p|";
		echo "<br/>  $cod_par | $cod_tit | $cod_piz | $cod_ped | $situaca | $venci_a | $paga_a | $forma_p|";
		if($logar) file_put_contents($file, $texto, FILE_APPEND);
	}


	$sql_atualizar_titulos = "UPDATE ipi_titulos_parcelas tp inner join ipi_titulos t on t.cod_titulos = tp.cod_titulos inner join ipi_pedidos p on p.cod_pedidos = t.cod_pedidos set tp.situacao = 'PAGO', tp.cod_bancos_destino = '".$obj_buscar_formas_cartao->cod_bancos."' where tp.situacao = 'ABERTO'  AND tp.data_vencimento = DATE_FORMAT(NOW(), '%Y-%m-%d') AND tp.data_pagamento = DATE_FORMAT(NOW(), '%Y-%m-%d') AND p.cod_pizzarias = '".$obj_buscar_formas_cartao->cod_pizzarias."' AND tp.cod_formas_pg = '".$obj_buscar_formas_cartao->cod_formas_pg."'";

	//	$sql_selecionar_titulos = "UPDATE ipi_titulos_parcelas tp inner join ipi_titulos t on t.cod_titulos = tp.cod_titulos inner join ipi_pedidos p on p.cod_pedidos = t.cod_pedidos set tp.situacao = 'PAGO', tp.cod_bancos_destino = '".$obj_buscar_formas_cartao->cod_bancos."' where tp.situacao = 'ABERTO'  AND tp.data_vencimento = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') AND tp.data_pagamento = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') AND p.cod_pizzarias = '".$obj_buscar_formas_cartao->cod_pizzarias."' AND tp.cod_formas_pg = '".$obj_buscar_formas_cartao->cod_formas_pg."'";
	  echo "<br/><br/> Query : ".$sql_atualizar_titulos."<br/> ";
		$texto = "\n \n Query : ".$sql_atualizar_titulos."\n ";
		if($logar) file_put_contents($file,$texto, FILE_APPEND);

	$res_atualizar_titulos = mysql_query($sql_atualizar_titulos);
}


/*DATE_FORMAT(date, '%Y-%m-%d')
$sql_selecionar_titulos = "SELECT * FROM ipi_titulos_parcelas tp where tp.situacao = 'PAGAR'";
$res_selecionar_titulos = mysql_query($sql_selecionar_titulos);
while($obj_selecionar_titulos = mysql_fetch_object($res_selecionar_titulos))
{

	$sql_alterar_pago = "UPDATE ipi_titulos_parcelas_tp SET situacao='PAGO' WHERE cod_titulos_parcelas='".$obj_selecionar_titulos->cod_titulos_parcelas."'";
	$res_alterar_pago = mysql_query($sql_alterar_pago);
}
*/
desconectar_bd($cone);
?>