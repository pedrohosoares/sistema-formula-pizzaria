<?
$hora_atual = date("H:i:s");
//$hora_atual = "23:35:01";
require_once 'bd.php';

if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
{
  $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
}
else
{
  $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
  $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
  $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= '$cep_limpo' AND cep_final >= '$cep_limpo' LIMIT 1";
  $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
  $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
//  echo $sql_cod_pizzarias;
  $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
}

require("pub_req_fuso_horario1.php");

$sql_buscar_horarios = "SELECT horario_inicial as inicio,horario_final as fim from ipi_pizzarias_funcionamento where cod_pizzarias = '".$cod_pizzarias."' and dia_semana = '".date('w')."' order by horario_inicial DESC";
//echo $sql_buscar_horarios."<br/>";
$res_buscar_horarios = mysql_query($sql_buscar_horarios);
$obj_buscar_horarios = mysql_fetch_object($res_buscar_horarios);

if($obj_buscar_horarios->inicio!="")
{
	$hora_inicio_aviso = $obj_buscar_horarios->inicio;
	$hora_final_aviso = $obj_buscar_horarios->fim;
}
/*else
{
	$hora_inicio_aviso = "23:30:00";
	$hora_final_aviso = "23:59:59";
}*/
//echo "<br/><br/>i=".$hora_inicio_aviso."-final=".$hora_final_aviso;
$hora_atual_convertida = strtotime($hora_atual);
$hora_inicio_aviso_convertida = strtotime($hora_inicio_aviso);
$hora_final_aviso_convertida = strtotime($hora_final_aviso)+1;
$hora_final_aviso_convertida_tolerancia = strtotime($hora_final_aviso)+301;
if ($hora_atual_convertida>$hora_inicio_aviso_convertida && $hora_atual_convertida<$hora_final_aviso_convertida_tolerancia)
{
	//echo $_GET['pagina'];
	echo "<div align='center' class='trava_meia_noite'><div class='texto_meia_noite'><b>Já são  ".date("H:i", strtotime($hora_atual)).", você tem até ".date("H:i", $hora_final_aviso_convertida_tolerancia)." para terminar seu pedido ou ele será automaticamente cancelado.</b></div></div>";
}
?>
