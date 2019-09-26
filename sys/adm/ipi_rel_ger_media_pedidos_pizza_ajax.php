<?

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
//require_once '../lib/php/sessao.php';

$param = explode(',', validaVarGet('param'));

$data_hora = $param[0];
$tipo = $param[1];

$ano           = $param[2];
$cod_pizzarias = $param[3];
$cod_tamanhos  = $param[4]; 
$origem        = $param[5];

$conexao = conectabd();

?>

<? if($tipo == 1): ?>

<chart caption='Média de Pizzas por Pedido' subcaption='(<? echo $ano ?>)' lineThickness='1' showValues='1' placeValuesInside='1' rotateValues='1' formatNumberScale='0' anchorRadius='3' divLineAlpha='20' divLineColor='AF6F00' divLineIsDashed='1' showAlternateHGridColor='1' alternateHGridColor='FFA200' shadowAlpha='40' labelStep='1' numvdivlines='5' chartRightMargin="35" bgColor='FFFFFF,CFCFCF' bgAngle='270' bgAlpha='30,30' alternateHGridAlpha='5' decimalSeparator=',' thousandSeparator='.'  legendShadow='0'>

<categories >
    <category label='Jan'/>
    <category label='Fev'/>
    <category label='Mar'/>
    <category label='Abr'/>
    <category label='Mai'/>
    <category label='Jun'/>
    <category label='Jul'/>
    <category label='Ago'/>
    <category label='Set'/>
    <category label='Out'/>
    <category label='Nov'/>
    <category label='Dez'/>
</categories>

<dataset seriesName='Média Geral' color='F1683C' anchorBorderColor='1D8BD1' anchorBgColor='1D8BD1'  dashed='1'  lineDashLen='5'  lineDashGap='5'>

<?

for($m = 1; $m < 12; $m++)
{
    if($cod_tamanhos > 0)
    {
        $sql_buscar_tamanhos = "AND pp.cod_tamanhos = '$cod_tamanhos'";
    }
    else
    {
        $sql_buscar_tamanhos = '';
    }
    
    $sql_buscar_media_geral = "SELECT AVG((SELECT COUNT(*) FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos = p.cod_pedidos $sql_buscar_tamanhos)) AS media FROM ipi_pedidos p WHERE MONTH(data_hora_pedido) = $m AND YEAR(data_hora_pedido) = $ano AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
    
    if($cod_pizzarias > 0)
    {
        $sql_buscar_media_geral .= "AND p.cod_pizzarias = '$cod_pizzarias' ";
    }
    
    if($origem != 'TODOS')
    {
        $sql_buscar_media_geral .= "AND p.origem_pedido = '$origem'";
    }
    
    $res_buscar_media_geral = mysql_query($sql_buscar_media_geral);
    $obj_buscar_media_geral = mysql_fetch_object($res_buscar_media_geral);
    
    echo "<set value=\"" . $obj_buscar_media_geral->media . "\"/>";
}

?>

</dataset>

<dataset seriesName='Pagas' color='FFEF3F' anchorBorderColor='F1683C' anchorBgColor='F1683C'>

<?

for($m = 1; $m < 12; $m++)
{
    if($cod_tamanhos > 0)
    {
        $sql_buscar_tamanhos = "AND pp.cod_tamanhos = '$cod_tamanhos'";
    }
    else
    {
        $sql_buscar_tamanhos = '';
    }
    
    $sql_buscar_media_geral = "SELECT AVG((SELECT COUNT(*) FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos = p.cod_pedidos AND pp.promocional = 0 AND pp.fidelidade = 0 $sql_buscar_tamanhos)) AS media FROM ipi_pedidos p WHERE MONTH(data_hora_pedido) = $m AND YEAR(data_hora_pedido) = $ano AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ";
    
    if($cod_pizzarias > 0)
    {
        $sql_buscar_media_geral .= "AND p.cod_pizzarias = '$cod_pizzarias' ";
    }
    
    if($origem != 'TODOS')
    {
        $sql_buscar_media_geral .= "AND p.origem_pedido = '$origem'";
    }
    
    $res_buscar_media_geral = mysql_query($sql_buscar_media_geral);
    $obj_buscar_media_geral = mysql_fetch_object($res_buscar_media_geral);
    
    echo "<set value=\"" . $obj_buscar_media_geral->media . "\"/>";
}

?>

</dataset>

<dataset seriesName='Fidelidade' color='2AD62A' anchorBorderColor='2AD62A' anchorBgColor='2AD62A'>

<?

for($m = 1; $m < 12; $m++)
{
    if($cod_tamanhos > 0)
    {
        $sql_buscar_tamanhos = "AND pp.cod_tamanhos = '$cod_tamanhos'";
    }
    else
    {
        $sql_buscar_tamanhos = '';
    }
    
    $sql_buscar_media_geral = "SELECT AVG((SELECT COUNT(*) FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos = p.cod_pedidos AND pp.promocional = 0 AND pp.fidelidade = 1 $sql_buscar_tamanhos)) AS media FROM ipi_pedidos p WHERE MONTH(data_hora_pedido) = $m AND YEAR(data_hora_pedido) = $ano AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
    
    if($cod_pizzarias > 0)
    {
        $sql_buscar_media_geral .= "AND p.cod_pizzarias = '$cod_pizzarias' ";
    }
    
    if($origem != 'TODOS')
    {
        $sql_buscar_media_geral .= "AND p.origem_pedido = '$origem'";
    }
    
    $res_buscar_media_geral = mysql_query($sql_buscar_media_geral);
    $obj_buscar_media_geral = mysql_fetch_object($res_buscar_media_geral);
    
    echo "<set value=\"" . $obj_buscar_media_geral->media . "\"/>";
}

?>

</dataset>

<dataset seriesName='Grátis' color='3F61FF' anchorBorderColor='DBDC25' anchorBgColor='DBDC25'>

<?

for($m = 1; $m < 12; $m++)
{
    if($cod_tamanhos > 0)
    {
        $sql_buscar_tamanhos = "AND pp.cod_tamanhos = '$cod_tamanhos'";
    }
    else
    {
        $sql_buscar_tamanhos = '';
    }
    
    $sql_buscar_media_geral = "SELECT AVG((SELECT COUNT(*) FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos = p.cod_pedidos AND pp.promocional = 1 AND pp.fidelidade = 0 $sql_buscar_tamanhos)) AS media FROM ipi_pedidos p WHERE MONTH(data_hora_pedido) = $m AND YEAR(data_hora_pedido) = $ano AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ";
    
    if($cod_pizzarias > 0)
    {
        $sql_buscar_media_geral .= "AND p.cod_pizzarias = '$cod_pizzarias' ";
    }
    
    if($origem != 'TODOS')
    {
        $sql_buscar_media_geral .= "AND p.origem_pedido = '$origem'";
    }
    
    $res_buscar_media_geral = mysql_query($sql_buscar_media_geral);
    $obj_buscar_media_geral = mysql_fetch_object($res_buscar_media_geral);
    
    echo "<set value=\"" . $obj_buscar_media_geral->media . "\"/>";
}

?>

</dataset>

<trendLines>
    <line startValue='1.7' color='4F4F4F' displayValue='Meta Superior (1.7)' thickness='2'  dashed='1'  dashLen='5' dashGap='5'  valueOnRight='1' />
    <line startValue='1.5' color='4F4F4F' displayValue='Meta Inferior (1.5)' thickness='2'  dashed='1'  dashLen='5' dashGap='5'  valueOnRight='1' />
</trendLines>

<styles>                
    <definition>
        <style name='CaptionFont' type='font' size='11'/>
        <style name='SubCaptionFont' type='font' size='9'/>
    </definition>
    
    <application>
        <apply toObject='CAPTION' styles='CaptionFont' />
        <apply toObject='SUBCAPTION' styles='SubCaptionFont' />
    </application>
</styles>

</chart>

<? endif; ?>

<?

desconectabd($conexao);

?>
