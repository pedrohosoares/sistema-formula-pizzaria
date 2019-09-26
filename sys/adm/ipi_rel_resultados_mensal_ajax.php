<?
require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$param = explode(',', validaVarGet('param'));

$data_hora     = $param[0];
$tipo          = $param[1];
$ano_ref		   = $param[2];
$sem_ref       = $param[3];
$cod_pizzarias = $param[4];
if ($param[5] == 2)
  $situacao = "AND tp.situacao = 'ABERTO'";
elseif ($param[5] == 3)
  $situacao = "AND tp.situacao = 'PAGO'";
else
  $situacao = '';

$filtrar_por = $param[6];

print_r($situacao);

$arr_meses     = '';
$meses         = '';

($sem_ref == 1) ? $arr_meses = array(0,1,2,3,4,5) : $arr_meses = array(6,7,8,9,10,11);
($sem_ref == 1) ? $data = "(01,02,03,04,05,06)" : $data = "(07,08,09,10,11,12)";

$arr_meses_grafico = array('Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez');

if($filtrar_por=="MES_REFERENCIA")
{ 
  $filtro_data2 = "tp.ano_ref = '$ano_ref' AND tp.mes_ref IN $data ";
}
else
{
  $filtro_data2 = "YEAR(tp.data_vencimento) = '$ano_ref' AND MONTH(tp.data_vencimento) IN $data ";
}

$conexao = conectabd();

/*$obj_buscar_credito = executaBuscaSimples("SELECT SUM(itp.valor_total) AS credito FROM ipi_titulos t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) INNER JOIN ipi_titulos_subcategorias ts ON (t.cod_titulos_subcategorias = ts.cod_titulos_subcategorias) INNER JOIN ipi_titulos_categorias tc ON (ts.cod_titulos_categorias = tc.cod_titulos_categorias) INNER JOIN ipi_pizzarias pizr ON (pizr.cod_pizzarias = t.cod_pizzarias) WHERE data_vencimento BETWEEN $data AND pizr.cod_pizzarias = $cod_pizzarias AND valor_total > 0 AND itp.situacao = 'PAGO'", $conexao);

$obj_buscar_debito = executaBuscaSimples("SELECT SUM(itp.valor_total) AS debito FROM ipi_titulos t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) INNER JOIN ipi_titulos_subcategorias ts ON (t.cod_titulos_subcategorias = ts.cod_titulos_subcategorias) INNER JOIN ipi_titulos_categorias tc ON (ts.cod_titulos_categorias = tc.cod_titulos_categorias) INNER JOIN ipi_pizzarias pizr ON (pizr.cod_pizzarias = t.cod_pizzarias) WHERE data_vencimento BETWEEN $data AND pizr.cod_pizzarias = $cod_pizzarias  AND valor_total < 0 AND itp.situacao = 'PAGO'", $conexao);*/

if($tipo == 1):

  echo '<chart caption="Recebimentos x Pagamentos" yAxisName="Valor" showValues="0" decimals="0" formatNumberScale="0">';
  
  echo '<categories>';  
  foreach ($arr_meses as &$m) 
  {
    echo "<category label='" . $arr_meses_grafico[$m] . "'/>";
  }
	echo '</categories>';

  echo "<dataset seriesName='Recebimentos'>";
  foreach ($arr_meses as &$m) 
  {  
    if($filtrar_por=="MES_REFERENCIA")
    { 
      $filtro_data = "tp.ano_ref = '$ano_ref' AND tp.mes_ref = '".($m + 1)."'";
    }
    else
    {
      $filtro_data = "YEAR(tp.data_vencimento) = '$ano_ref' AND MONTH(tp.data_vencimento) = '".($m + 1)."'";
    }

    $sql_buscar_registros = "SELECT tp.valor_total, t.tipo_titulo FROM ipi_titulos t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) INNER JOIN ipi_pizzarias pizr ON (pizr.cod_pizzarias = t.cod_pizzarias) WHERE $filtro_data AND pizr.cod_pizzarias = $cod_pizzarias ".$situacao;
    $res_buscar_registros = mysql_query($sql_buscar_registros);

    $total_credito = 0;

    while($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
    {
      if($obj_buscar_registros->tipo_titulo == 'RECEBER' || (($obj_buscar_registros->tipo_titulo == 'TRANSFER') && ($obj_buscar_registros->valor_total > 0)))
      {
          $total_credito += $obj_buscar_registros->valor_total;
      }
    }

    echo "<set value='" . abs($total_credito) . "' />";
  }  
  echo '</dataset>';
  
  
  echo "<dataset seriesName='Pagamentos'>";
  foreach ($arr_meses as &$m) 
  {
    if($filtrar_por=="MES_REFERENCIA")
    { 
      $filtro_data = "tp.ano_ref = '$ano_ref' AND tp.mes_ref = '".($m + 1)."'";
    }
    else
    {
      $filtro_data = "YEAR(tp.data_vencimento) = '$ano_ref' AND MONTH(tp.data_vencimento) = '".($m + 1)."'";
    }
    
    $sql_buscar_registros = "SELECT tp.valor_total, t.tipo_titulo FROM ipi_titulos t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) INNER JOIN ipi_pizzarias pizr ON (pizr.cod_pizzarias = t.cod_pizzarias) WHERE $filtro_data AND pizr.cod_pizzarias = $cod_pizzarias ".$situacao;
    $res_buscar_registros = mysql_query($sql_buscar_registros);

    $total_debito = 0;

    while($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
    {
      if ($obj_buscar_registros->tipo_titulo == 'PAGAR' || (($obj_buscar_registros->tipo_titulo == 'TRANSFER') && ($obj_buscar_registros->valor_total < 0)))
      {    
          $total_debito += $obj_buscar_registros->valor_total * -1;
      }      
    }
    echo "<set value='" . abs($total_debito) . "' />";
  }  
  echo '</dataset>';
  echo '</chart>';

elseif($tipo == 2):
  $sql_buscar_registros = "SELECT tp.valor_total, t.tipo_titulo FROM ipi_titulos t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) INNER JOIN ipi_pizzarias pizr ON (pizr.cod_pizzarias = t.cod_pizzarias) WHERE $filtro_data2 AND pizr.cod_pizzarias = $cod_pizzarias ".$situacao;
    $res_buscar_registros = mysql_query($sql_buscar_registros);

    $total_debito = 0;
    $total_credito = 0;

    while($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
    {
      if ($obj_buscar_registros->tipo_titulo == 'PAGAR' || (($obj_buscar_registros->tipo_titulo == 'TRANSFER') && ($obj_buscar_registros->valor_total < 0)))
      {    
          $total_debito += $obj_buscar_registros->valor_total * -1;
      }
      else if($obj_buscar_registros->tipo_titulo == 'RECEBER' || (($obj_buscar_registros->tipo_titulo == 'TRANSFER') && ($obj_buscar_registros->valor_total > 0)))
      {
          $total_credito += $obj_buscar_registros->valor_total;
      }      
    }
  echo '<table align="center" style="height: 50px; width: 300px; background-color: #FFF3DD; border: 1px solid #EB8212; border-collapse: collapse;" cellpadding=5>';  
  echo '<tr>';
  echo '<td colspan= "2" style="font-size: 20px; text-align: center; border-bottom: 1px dotted #EB8212; background-color:#FFD485;">';
  echo '<b>Resumo</b>'; 
  echo '</td>';
  echo '</tr>';
  
  
  echo '<tr>';
  echo '<td style="font-size: 16px; text-align: center; border-bottom: 1px dotted #EB8212;">';
  echo 'Crédito:'; 
  echo '</td>';
  echo '<td style="font-size: 16px; text-align: center; border-bottom: 1px dotted #EB8212;">';
  echo (abs($total_credito) == 0) ? 'R$ 0,00' : 'R$ '.bd2moeda(abs($total_credito));
  echo '</td>';
  echo '</tr>';
  
  
  echo '<tr>';
  echo '<td style="font-size: 16px; text-align: center; border-bottom: 1px dotted #EB8212;">';
  echo 'Débito:';
  echo '</td>';
  echo '<td style="font-size: 16px; text-align: center; border-bottom: 1px dotted #EB8212;">';
  echo (abs($total_debito) == 0) ? 'R$ 0,00' : 'R$ '.bd2moeda(abs($total_debito));
  echo '</td>';
  echo '</tr>';
  
  
  echo '<tr>';
  echo '<td style="font-size: 16px; text-align: center; background-color:#FFE5B5; border-bottom: 1px dotted #EB8212;">';
  echo '<b>Lucro: </b>';
  echo '</td>';
  echo (($total_credito - $total_debito) >= 0) ? '<td style="font-size: 16px; text-align: center; background-color:#FFE5B5; border-bottom: 1px dotted #EB8212;">' : '<td style="color:#CE0000; font-size: 16px; text-align: center; background-color:#FFE5B5; border-bottom: 1px dotted #EB8212;">';
  echo (($total_credito - $total_debito) == 0) ? 'R$ 0,00' : 'R$ '.bd2moeda(($total_credito - $total_debito));
  echo '</td>';
  echo '</tr>';
  
  echo '</table>'; 
endif;

desconectabd($conexao); 
?>


