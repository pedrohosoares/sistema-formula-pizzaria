<?php
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';
$acao = ValidaVarPost('acao');

$resultado = array();
$con = conectabd();

if ($acao == "bairro")
{

  $bairro = ValidaVarPost('bairro');
  $cod_pizzarias = ValidaVarPost("pizzaria");
  if (strlen($bairro)>2)
  {

    $sql_bairros = "SELECT DISTINCT(bairro) FROM ipi_cep WHERE cod_pizzarias ='".$cod_pizzarias."' AND bairro LIKE '%".utf8_decode($bairro)."%' ORDER BY bairro ";
    $res_bairros = mysql_query($sql_bairros);
    $num_bairros = mysql_num_rows($res_bairros);
    //echo "".$sql_bairros;//cod_pizzarias in (".implode(",",$_SESSION['usuario']['cod_pizzarias']).")
    while ( $obj_bairros = mysql_fetch_object($res_bairros) )
    {
      $resultado[] = utf8_encode($obj_bairros->bairro);
    }

  }
}
else if ($acao == "cidade")
{

  $cidade = ValidaVarPost('cidade');
  $cod_pizzarias = ValidaVarPost("pizzaria"); 
  if (strlen($cidade)>2)
  {
    $sql_cidades = "SELECT DISTINCT(cidade) FROM ipi_cep WHERE cod_pizzarias ='".$cod_pizzarias."' AND cidade LIKE '%".utf8_decode($cidade)."%' ORDER BY cidade ";
    //echo $sql_cidades;
    $res_cidades = mysql_query($sql_cidades);
    $num_cidades = mysql_num_rows($res_cidades);

    while ( $obj_cidades = mysql_fetch_object($res_cidades) )
    {
      $resultado[] = utf8_encode($obj_cidades->cidade);
    }

  }
}

desconectabd($con);

header('Content-type: application/json');
echo json_encode($resultado); 
?>
