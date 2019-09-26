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

$param = explode(',', validar_var_get('param'));

$tipo_grafico = $param[0];
$codigo_enquetes = $param[1];
$cod_enquete_perguntas = $param[2];
$data_inicial = $param[3];
$data_final = $param[4];
$cod_pizzarias = $param[5];
$pergunta = $param[6];
/*
echo "count: ". count($param);
echo "param: ". validar_var_get('param');
echo "cod_pizzarias: ". $cod_pizzarias;
echo "pergunta: ". $pergunta;
*/
$conexao = conectabd();

?>

<?php if($tipo_grafico == 1): ?>

<chart lowerLimit='0' upperLimit='100' gaugeStartAngle='180' gaugeEndAngle='0' palette='1' numberSuffix='%' tickValueDecimals='0' forceTickValueDecimals='1' showValue='1'>

<colorRange>
    <color minValue='0' maxValue='89' code='FF654F'/>
    <color minValue='90' maxValue='94' code='F6BD0F'/>
    <color minValue='95' maxValue='100' code='8BBA00'/>
</colorRange>

<dials>
   
<?
$data_inicial_sql = ($data_inicial) . ' 00:00:00';
$data_final_sql = ($data_final) . ' 23:59:59';

$sql_buscar_pedidos_tempo = "SELECT cod_pedidos from ipi_pedidos where 1=1 ";
if (($data_inicial) && ($data_final))
{
  $sql_buscar_pedidos_tempo .= "AND data_hora_pedido >= '$data_inicial_sql' AND data_hora_pedido <= '$data_final_sql'";
}

if($cod_pizzarias)
{
  $sql_buscar_pedidos_tempo .= "AND cod_pizzarias = '$cod_pizzarias'";    
}
else
{
  $sql_buscar_pedidos_tempo .= " AND cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
}

$res_buscar_pedidos_tempo = mysql_query($sql_buscar_pedidos_tempo);
while($obj_buscar_pedidos_tempo = mysql_fetch_object($res_buscar_pedidos_tempo))
{
  $arr_pedidos[] = $obj_buscar_pedidos_tempo->cod_pedidos;
}

$sql_buscar_respostas = "SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas = '$cod_enquete_perguntas'";
$res_buscar_respostas = mysql_query($sql_buscar_respostas);

$arr_cod_respostas_metricas = array(8,9,12,13,16,17,20);

$total_repostas = 0;
$total_metrica = 0;

while($obj_buscar_respostas = mysql_fetch_object($res_buscar_respostas))
{
    $sql_buscar_quantidade_total = "SELECT COUNT(*) AS total FROM ipi_clientes_ipi_enquete_respostas cer LEFT JOIN ipi_pedidos p ON (cer.cod_pedidos=p.cod_pedidos) WHERE cer.cod_enquete_respostas = '" . $obj_buscar_respostas->cod_enquete_respostas . "'  AND p.cod_pedidos in(".implode(',',$arr_pedidos).") ";

    //file_put_contents("teste.txt", "\n1:   ".$sql_buscar_quantidade_total, FILE_APPEND);
    
    $obj_buscar_quantidade_total = executaBuscaSimples($sql_buscar_quantidade_total, $conexao);
    
    $total_repostas += $obj_buscar_quantidade_total->total;
    
    if(in_array($obj_buscar_respostas->cod_enquete_respostas, $arr_cod_respostas_metricas) !== false)
    {
        $total_metrica += $obj_buscar_quantidade_total->total;    
    }
}

echo '<dial value="' . round(($total_metrica * 100) / $total_repostas) . '"/>';

?>
  
</dials>
</chart>

<?php elseif($tipo_grafico == 2): ?>

<chart palette='1' decimals='0' enableSmartLabels='1' enableRotation='0' bgAngle='360' showBorder='1' startingAngle='70' formatNumberScale='0'  showPercentValues='1'  showValues='1'>

<?

$data_inicial_sql = ($data_inicial) . ' 00:00:00';
$data_final_sql = ($data_final) . ' 23:59:59';

$sql_buscar_pedidos_tempo = "SELECT cod_pedidos from ipi_pedidos where 1=1 ";
if (($data_inicial) && ($data_final))
{
  $sql_buscar_pedidos_tempo .= "AND data_hora_pedido >= '$data_inicial_sql' AND data_hora_pedido <= '$data_final_sql'";
}

if($cod_pizzarias)
{
  $sql_buscar_pedidos_tempo .= "AND cod_pizzarias = '$cod_pizzarias'";    
}
else
{
  $sql_buscar_pedidos_tempo .= " AND cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
}

$res_buscar_pedidos_tempo = mysql_query($sql_buscar_pedidos_tempo);
while($obj_buscar_pedidos_tempo = mysql_fetch_object($res_buscar_pedidos_tempo))
{
  $arr_pedidos[] = $obj_buscar_pedidos_tempo->cod_pedidos;
}

$sql_buscar_respostas = "SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas = '$cod_enquete_perguntas'";
$res_buscar_respostas = mysql_query($sql_buscar_respostas);

while($obj_buscar_respostas = mysql_fetch_object($res_buscar_respostas))
{
    $sql_buscar_quantidade_total = "SELECT COUNT(*) AS total FROM ipi_clientes_ipi_enquete_respostas cer LEFT JOIN ipi_pedidos p ON (cer.cod_pedidos=p.cod_pedidos) WHERE cer.cod_enquete_respostas = '" . $obj_buscar_respostas->cod_enquete_respostas . "' AND p.cod_pedidos in(".implode(',',$arr_pedidos).") ";

    //file_put_contents("teste.txt", "\n2:   ".$sql_buscar_quantidade_total, FILE_APPEND);
    $obj_buscar_quantidade_total = executaBuscaSimples($sql_buscar_quantidade_total, $conexao);
    
    echo '<set label="' . bd2texto($obj_buscar_respostas->resposta) . '" value="' . $obj_buscar_quantidade_total->total . '"/>';
}

?>

</chart>

<?php elseif($tipo_grafico == 3): ?>

<?

$obj_buscar_pergunta = executaBuscaSimples("SELECT * FROM ipi_enquete_perguntas WHERE cod_enquete_perguntas = '$cod_enquete_perguntas'", $conexao);

?>

<graph caption='<? echo $obj_buscar_pergunta->pergunta ?>' showValues='0' decimals='0' formatNumberScale='0'>

<?

$sql_buscar_respostas = "SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas = '$cod_enquete_perguntas'";
$res_buscar_respostas = mysql_query($sql_buscar_respostas);

$data_inicial_sql = ($data_inicial) . ' 00:00:00';
$data_final_sql = ($data_final) . ' 23:59:59';

$sql_buscar_pedidos_tempo = "SELECT cod_pedidos from ipi_pedidos where 1=1 ";
if (($data_inicial) && ($data_final))
{
  $sql_buscar_pedidos_tempo .= "AND data_hora_pedido >= '$data_inicial_sql' AND data_hora_pedido <= '$data_final_sql'";
}

if($cod_pizzarias)
{
  $sql_buscar_pedidos_tempo .= "AND cod_pizzarias = '$cod_pizzarias'";    
}
else
{
  $sql_buscar_pedidos_tempo .= " AND cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
}

$res_buscar_pedidos_tempo = mysql_query($sql_buscar_pedidos_tempo);
while($obj_buscar_pedidos_tempo = mysql_fetch_object($res_buscar_pedidos_tempo))
{
  $arr_pedidos[] = $obj_buscar_pedidos_tempo->cod_pedidos;
}


while($obj_buscar_respostas = mysql_fetch_object($res_buscar_respostas))
{
    $sql_buscar_quantidade_total = "SELECT COUNT(*) AS total FROM ipi_clientes_ipi_enquete_respostas cer LEFT JOIN ipi_pedidos p ON (cer.cod_pedidos=p.cod_pedidos) WHERE cer.cod_enquete_respostas = '" . $obj_buscar_respostas->cod_enquete_respostas . "'  AND p.cod_pedidos in(".implode(',',$arr_pedidos).") ";

    //file_put_contents("teste.txt", "\n3:   ".$sql_buscar_quantidade_total, FILE_APPEND);
    $obj_buscar_quantidade_total = executaBuscaSimples($sql_buscar_quantidade_total, $con);
    
    echo '<set label="' . bd2texto($obj_buscar_respostas->resposta) . '" value="' . $obj_buscar_quantidade_total->total . '"/>';
}

?>

</graph>

<?php endif; ?>

<?php desconectabd($conexao); ?>
