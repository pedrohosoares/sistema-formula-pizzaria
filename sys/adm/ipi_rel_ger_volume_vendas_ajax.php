<?

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$param = (validaVarPost('param') ? explode(',', validaVarPost('param')) : explode(',', validaVarGet('param')));

$data_hora = $param[0];
$tipo          = $param[1];
$ano           = $param[2];
$cod_pizzarias = $param[3];
$origem        = $param[4];
$periodo       = date('m', strtotime('-1 month'));

$conexao = conectabd();

?>

<? if($tipo == 1): ?>

<chart caption='Volume de Vendas' subcaption='(<? echo $ano ?>)' lineThickness='1' showValues='0' placeValuesInside='1' rotateValues='1' formatNumberScale='0' anchorRadius='3' divLineAlpha='20' divLineColor='AF6F00' divLineIsDashed='1' showAlternateHGridColor='1' alternateHGridColor='FFA200' shadowAlpha='40' labelStep='1' numvdivlines='5' chartRightMargin="35" bgColor='FFFFFF,CFCFCF' bgAngle='270' bgAlpha='30,30' alternateHGridAlpha='5' decimalSeparator=',' thousandSeparator='.'  legendShadow='0'>

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

$sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao = 'ATIVO' ";

if($cod_pizzarias > 0)
{
	$sql_buscar_pizzarias .= "AND cod_pizzarias = '$cod_pizzarias' ";
}
else
{
	$sql_buscar_pizzarias .= "AND cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).")";
}

$sql_buscar_pizzarias .= ' ORDER BY nome';

$res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);

while ($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
{
	echo "<dataset seriesName='".bd2texto($obj_buscar_pizzarias->nome)."'>";
	
	$media_pizzaria = 0;
	$contador = 0;
	for($m = 1; $m <= 12; $m++)
	{
	    $sql_buscar_soma = "SELECT SUM(valor_total) AS total FROM ipi_pedidos p WHERE MONTH(data_hora_pedido) = $m AND YEAR(data_hora_pedido) = $ano AND p.cod_pizzarias = '$obj_buscar_pizzarias->cod_pizzarias' AND p.situacao = 'BAIXADO' ";
	    
	    if($origem != 'TODOS')
	    {
	        $sql_buscar_soma .= "AND p.origem_pedido IN ('NET','IFOOD')";
	    }
	    
        $res_buscar_soma = mysql_query($sql_buscar_soma);
	    $obj_buscar_soma = mysql_fetch_object($res_buscar_soma);
	    
	    echo "<set value=\"" . $obj_buscar_soma->total . "\"/>";
	     $media_pizzaria = ($obj_buscar_soma->total+$media_pizzaria) ;
		if ($obj_buscar_soma->total>0)
	    {
	    	$contador++;
	    }
	}
	echo "</dataset>";
	$arr_media_pizzaria['media'][] = $media_pizzaria/$periodo;
	$arr_media_pizzaria['nome'][] = bd2texto($obj_buscar_pizzarias->nome);
}

?>
<!-- <trendLines>
	<?
	for ($x=0; $x<count($arr_media_pizzaria); $x++)
	{
		echo "<line startValue='".(INT)(($arr_media_pizzaria['media'][$x])/$contador)."' displayValue='".$arr_media_pizzaria['nome'][$x]." (". bd2moeda((INT)(($arr_media_pizzaria['media'][$x])/$contador)).")' thickness='2' dashed='1' dashLen='5' dashGap='5'  valueOnRight='1' />";
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

<? elseif($tipo == 2): ?>

  <?
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao = 'ATIVO' ";

    if($cod_pizzarias > 0)
    {
      $sql_buscar_pizzarias .= "AND cod_pizzarias = '$cod_pizzarias' ";
    }
    else
    {
      $sql_buscar_pizzarias .= "AND cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).")";
    }

    $sql_buscar_pizzarias .= ' ORDER BY nome';

    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);

    $media_geral = 0;
    $media_mes_rede = 0;
    $venda_acumulada = 0;
    $contador_rede = 0;

    $sql_buscar_ultimo_pedido = "SELECT YEAR(data_hora_pedido) AS ano, MONTH(data_hora_pedido) AS mes FROM ipi_pedidos p WHERE p.situacao = 'BAIXADO' ORDER BY data_hora_pedido DESC LIMIT 1";
    $res_buscar_ultimo_pedido = mysql_query($sql_buscar_ultimo_pedido);
    $obj_buscar_ultimo_pedido = mysql_fetch_object($res_buscar_ultimo_pedido);

    if($obj_buscar_ultimo_pedido->ano == $ano)
    {
      $periodo = $obj_buscar_ultimo_pedido->mes - 1;
    }
    else
    {
      $periodo = 12;
    }
    // echo $periodo.'<br/>';

    while ($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
    {

      $media_pizzaria = 0;
      $contador = 0;

      for($m = 1; $m <= $periodo; $m++)
      {
        $sql_buscar_soma = "SELECT SUM(valor_total) AS total FROM ipi_pedidos p WHERE MONTH(data_hora_pedido) = $m AND YEAR(data_hora_pedido) = $ano AND p.cod_pizzarias = '$obj_buscar_pizzarias->cod_pizzarias' AND p.situacao = 'BAIXADO' ";
        if($origem != 'TODOS')
        {
            $sql_buscar_soma .= "AND p.origem_pedido IN ('NET','IFOOD')";
        }
        
        $res_buscar_soma = mysql_query($sql_buscar_soma);
        $obj_buscar_soma = mysql_fetch_object($res_buscar_soma);
        
        $media_pizzaria = ($obj_buscar_soma->total+$media_pizzaria) ;

        if($obj_buscar_soma->total>0)
        {
          $contador++;

          $venda_acumulada += $obj_buscar_soma->total;

          if($m==$periodo)
          {
            $media_mes_rede += $obj_buscar_soma->total;
            $contador_rede++;
          }
        }

      }
      if($contador<=0) $contador = 1;

      $media_geral += $media_pizzaria/$contador;
      $arr_media_pizzaria['media'][] = $media_pizzaria/$contador;
      $arr_media_pizzaria['nome'][] = bd2texto($obj_buscar_pizzarias->nome);
    }
    if($contador_rede<=0) $contador_rede = 1;
    $arr_media_pizzaria['media_geral'] = $media_geral;
    $arr_media_pizzaria['media_geral_mes_passado'] = $media_mes_rede/$contador_rede;

    // echo '<pre>';
    // print_r($arr_media_pizzaria);
    // echo '</pre>';
    // echo '<br/>'.count($arr_media_pizzaria['nome']);
  ?>

    <table class='listaEdicao'>
    <thead>
      <tr>
        <td align='center'>Venda acumulada da rede até o ultimo mês</td>
        <td align='center'>Média da rede no ultimo mês</td>
      </tr>
    </thead>
    <tbody>
      <tr>
      <?
        $valor = bd2moeda($venda_acumulada);
        echo '<td align="center">R$ '.($valor<=0 ? '0,00' : $valor ).'</td>';
        $valor = bd2moeda($arr_media_pizzaria['media_geral_mes_passado']);
        echo '<td align="center">R$ '.($valor<=0 ? '0,00' : $valor ).'</td>';
      ?>
      </tr>
    </tbody>
  </table>

  <br/><br/>

  <table class='listaEdicao'>
    <thead>
      <tr>
        <td align='center'>Média anual geral</td>
      </tr>
    </thead>
    <tbody>
      <tr>
      <?
        $valor = bd2moeda(($arr_media_pizzaria['media_geral'])/count($arr_media_pizzaria['nome']));
        echo '<td align="center">R$ '.($valor<=0 ? '0,00' : $valor ).'</td>';
      ?>
      </tr>
    </tbody>
  </table>

  <br/><br/>
  
  <table class='listaEdicao'>
    <thead>
      <tr>
        <td colspan='2' align='center'>Média anual por franquia</td>
      </tr>
    </thead>
    <tbody>
      <?
        foreach ($arr_media_pizzaria['nome'] as $key => $value) 
        {
          echo '<tr>';
          echo '<td>'.utf8_encode($arr_media_pizzaria['nome'][$key]).'</td>';
          $valor = bd2moeda($arr_media_pizzaria['media'][$key]);
          echo '<td>R$ '.($valor<=0 ? '0,00' : $valor ).'</td>';
          echo '</tr>';
        }
      ?>
    </tbody>
  </table>

<? endif ?>

<?
  
desconectabd($conexao);

?>
