<?
require_once 'bd.php';
$con = conectar_bd();

if (isset($codPizzarias))
{
  $cod_pizzarias = $codPizzarias;
}
else if (isset($cod_pizzarias))
{
  $cod_pizzarias = $cod_pizzarias; //FIXME: falta de tempo, feito assim só para garantir se não for nenhuma das anteriores entre no else
}
else
{
  $cod_pizzarias = '1';
}

//FUSO HORARIO
$sql_busca_timezone = "SELECT t.variacao_gmt2, t.variacao_gmt, nome_timezone FROM ipi_pizzarias p INNER JOIN ipi_timezones t ON (p.timezone=t.nome_timezone) WHERE p.cod_pizzarias = '$cod_pizzarias'" ;
$res_busca_timezone = mysql_query($sql_busca_timezone);
$obj_busca_timezone = mysql_fetch_object($res_busca_timezone);
//FUSO HORARIO MYSQL
$sql_timezone = "SET time_zone = '".$obj_busca_timezone->variacao_gmt2."'";
$res_timezone = mysql_query($sql_timezone);
//FUSO HORARIO PHP
date_default_timezone_set($obj_busca_timezone->nome_timezone);
// echo $obj_busca_timezone->nome_timezone;


?>
