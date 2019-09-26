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
 * 1.0       12/05/2012   FILIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$param = explode(',', validaVarGet('param'));

$tipo_grafico = $param[0];
$valor = $param[1];
$texto = $param[2];
//$texto = 'Eficiência da Pizzaria';
$conexao = conectabd();
$sql_buscar_nome = "SELECT * from ipi_pizzarias_estatisticas_nomes where cod_estatisticas = '$texto'";
$res_buscar_nome = mysql_query($sql_buscar_nome);
$obj_buscar_nome = mysql_fetch_object($res_buscar_nome);
$texto = $obj_buscar_nome->nome_estatistica;
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
echo '<dial value="' .$valor. '" toolText="'.$texto.'"/>';
?>
  
</dials>
<!--<annotations>
      <annotationGroup xPos='220' yPos='10' >
         <annotation type='text' x='0' y='0' label='<? 
         echo $texto; ?>'  
                     align='center' bold='1' color='666666' size='11'/> 
      </annotationGroup> 
</annotations> -->
</chart>


<?php endif; ?>

<?php desconectabd($conexao); ?>
