<?

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$param = explode(',', validaVarGet('param'));

$data_hora      = $param[0];
$cod_pizzarias  = $param[1];
$cod_pizzas     = $param[2];
$cod_tamanhos   = $param[3];

$conexao = conectabd();

?>


<chart caption="Comparativo de CPV" subcaption="" lineThickness='1' showValues='0' placeValuesInside='1' rotateValues='1' formatNumberScale='0' anchorRadius='3' divLineAlpha='20' divLineColor='AF6F00' divLineIsDashed='1' showAlternateHGridColor='1' alternateHGridColor='FFA200' shadowAlpha='40' labelStep='1' numvdivlines='5' chartRightMargin="35" bgColor='FFFFFF,CFCFCF' bgAngle='270' bgAlpha='30,30' alternateHGridAlpha='5' decimalSeparator=',' thousandSeparator='.'  legendShadow='0'>


<?
  $sql_datas_cpv = "SELECT * FROM ipi_cpv c WHERE c.cod_pizzarias='".$cod_pizzarias."' AND c.cod_pizzas='".$cod_pizzas."' AND c.cod_tamanhos='".$cod_tamanhos."' ORDER BY c.data_registro LIMIT 6";
  //echo "<Br>1: ". $sql_datas_cpv;
  $res_datas_cpv = mysql_query($sql_datas_cpv);
  echo "<categories>";
  while ($obj_datas_cpv = mysql_fetch_object($res_datas_cpv))
  {
    echo "<category label='".bd2data($obj_datas_cpv->data_registro)."'/>";
  }
  echo "</categories>";




  $sql_preco_venda = "SELECT * FROM ipi_cpv c WHERE c.cod_pizzarias='".$cod_pizzarias."' AND c.cod_pizzas='".$cod_pizzas."' AND c.cod_tamanhos='".$cod_tamanhos."' ORDER BY c.data_registro LIMIT 6";
  //echo "<Br>1: ". $sql_datas_cpv;
  $res_preco_venda = mysql_query($sql_preco_venda);
	echo "<dataset seriesName='Preço de Venda'>";
  while ($obj_preco_venda = mysql_fetch_object($res_preco_venda))
  {
    echo "<set value='" . ($obj_preco_venda->preco_venda) . "' />";
  }
	echo "</dataset>";




  $sql_preco_venda = "SELECT * FROM ipi_cpv c WHERE c.cod_pizzarias='".$cod_pizzarias."' AND c.cod_pizzas='".$cod_pizzas."' AND c.cod_tamanhos='".$cod_tamanhos."' ORDER BY c.data_registro LIMIT 6";
  //echo "<Br>1: ". $sql_datas_cpv;
  $res_preco_venda = mysql_query($sql_preco_venda);
	echo "<dataset seriesName='CPV Calculado'>";
  while ($obj_preco_venda = mysql_fetch_object($res_preco_venda))
  {
    echo "<set value='" . ($obj_preco_venda->cpv_teorico) . "' />";
  }
	echo "</dataset>";

/*


  $sql_preco_venda = "SELECT * FROM ipi_cpv c ORDER BY c.data_registro LIMIT 6";
  //echo "<Br>1: ". $sql_datas_cpv;
  $res_preco_venda = mysql_query($sql_preco_venda);
	echo "<dataset seriesName='CPV Real'>";
  while ($obj_preco_venda = mysql_fetch_object($res_preco_venda))
  {
    echo "<set value='" . ($obj_preco_venda->cpv_real) . "' />";
  }
	echo "</dataset>";
*/

?>


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

<?

desconectabd($conexao);

?>
