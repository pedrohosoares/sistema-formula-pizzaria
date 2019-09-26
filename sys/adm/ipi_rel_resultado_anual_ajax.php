<?

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$param = explode(',', validaVarGet('param'));

$data_hora = $param[0];
$tipo          = $param[1];
$ano           = $param[2];
$cod_pizzarias = $param[3];
$origem        = $param[4];

$conexao = conectabd();

?>

<? if($tipo == 1): 

if($cod_pizzarias > 0)
{
  $sql_buscar_pizzarias = "SELECT cod_pizzarias, nome FROM ipi_pizzarias WHERE ";
  $sql_buscar_pizzarias .= "cod_pizzarias = '$cod_pizzarias' AND ";
  $sql_buscar_pizzarias .= "cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).")";
  $sql_buscar_pizzarias .= " ORDER BY nome";
  $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
  $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
}
?>

<chart caption='Volume de Vendas' subcaption='(<? echo $obj_buscar_pizzarias->nome." - ".$ano; ?>)' lineThickness='1' showValues='0' placeValuesInside='1' rotateValues='1' formatNumberScale='0' anchorRadius='3' divLineAlpha='20' divLineColor='AF6F00' divLineIsDashed='1' showAlternateHGridColor='1' alternateHGridColor='FFA200' shadowAlpha='40' labelStep='1' numvdivlines='5' chartRightMargin="35" bgColor='FFFFFF,CFCFCF' bgAngle='270' bgAlpha='30,30' alternateHGridAlpha='5' decimalSeparator=',' thousandSeparator='.'  legendShadow='0'>

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

<?

$sql_buscar_pizzarias = "SELECT cod_pizzarias, nome FROM ipi_pizzarias WHERE ";

if($cod_pizzarias > 0)
{
	$sql_buscar_pizzarias .= "cod_pizzarias = '$cod_pizzarias' ";
}
else
{
	$sql_buscar_pizzarias .= "cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).")";
}

$sql_buscar_pizzarias .= ' ORDER BY nome';

$res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);

$arr_totais[] = array();

while ($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
{

	echo "<dataset seriesName='Receber'>";
	$media_pizzaria = 0;
	$contador = 0;
	for($m = 1; $m <= 12; $m++)
	{
	    $sql_buscar_soma = "SELECT SUM(valor_total) AS total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE MONTH(tp.data_pagamento) = $m AND YEAR(tp.data_pagamento) = $ano AND t.cod_pizzarias = '$obj_buscar_pizzarias->cod_pizzarias' AND tp.situacao = 'PAGO' AND t.tipo_titulo='RECEBER' ";
	    //echo "<Br>1: ". $sql_buscar_soma;
      $res_buscar_soma = mysql_query($sql_buscar_soma);
	    $obj_buscar_soma = mysql_fetch_object($res_buscar_soma);
	    
	    echo "<set value=\"" . $obj_buscar_soma->total . "\"/>";
		  if ( ($obj_buscar_soma->total>0) && ((int)$m!=(int)date("m")) )
	    {
		    $media_pizzaria = ($obj_buscar_soma->total + $media_pizzaria);
			  $arr_totais[$m][0] = $obj_buscar_soma->total;
	    	$contador++;
	    }
	}
	echo "</dataset>";



	echo "<dataset seriesName='Pagar'>";
	$media_pizzaria = 0;
	$contador = 0;
	for($m = 1; $m <= 12; $m++)
	{
	    $sql_buscar_soma = "SELECT (SUM(valor_total)*-1) AS total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE MONTH(tp.data_pagamento) = $m AND YEAR(tp.data_pagamento) = $ano AND t.cod_pizzarias = '".$obj_buscar_pizzarias->cod_pizzarias."' AND tp.situacao = 'PAGO' AND t.tipo_titulo='PAGAR' ";
	    //echo "<Br>1: ". $sql_buscar_soma;
      $res_buscar_soma = mysql_query($sql_buscar_soma);
	    $obj_buscar_soma = mysql_fetch_object($res_buscar_soma);
	    
	    echo "<set value=\"" . $obj_buscar_soma->total . "\"/>";
		  if ( ($obj_buscar_soma->total>0) && ((int)$m!=(int)date("m")) )
	    {
		    $media_pizzaria = ($obj_buscar_soma->total + $media_pizzaria);
			  $arr_totais[$m][1] = $obj_buscar_soma->total;
	    	$contador++;
	    }
	}
	echo "</dataset>";




	echo "<dataset seriesName='Lucro'>";
	$media_pizzaria = 0;
	$contador = 0;
	for($m = 1; $m <= 12; $m++)
	{
	    echo "<set value=\"" . ($arr_totais[$m][0] - $arr_totais[$m][1]) . "\"/>";

	}
	echo "</dataset>";


	$arr_media_pizzaria['media'][] = $media_pizzaria;
	$arr_media_pizzaria['nome'][] = bd2texto($obj_buscar_pizzarias->nome);

}
?>
<!--
<trendLines>
	<?
	for ($x=0; $x<count($arr_media_pizzaria); $x++)
	{
		echo "<line startValue='".(int)(($arr_media_pizzaria['media'][$x])/$contador)."' displayValue='".$arr_media_pizzaria['nome'][$x]." (". bd2moeda((int)(($arr_media_pizzaria['media'][$x])/$contador)).")' thickness='2' dashed='1' dashLen='5' dashGap='5'  valueOnRight='1' />";
	}
	?>
</trendLines>
-->

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
