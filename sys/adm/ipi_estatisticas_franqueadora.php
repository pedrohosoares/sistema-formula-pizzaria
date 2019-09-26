<?php

/**
 * Resultados das Enquetes.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       06/06/2012   FILIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Estatisticas das Pizzarias');

$acao = validaVarPost('acao');

$tabela = 'ipi_pizzarias_estatisticas';
$chave_primaria = 'cod_pizzarias_estatisticas';


?>
<link rel="stylesheet" type="text/css" media="screen"
    href="../lib/css/tabs_simples.css" />
    
<link rel="stylesheet" type="text/css" media="screen"
    href="../lib/css/tabs_interna.css" />

<link rel="stylesheet" type="text/css" media="screen"
    href="../lib/css/calendario.css" />

<script type="text/javascript" src="../lib/js/calendario.js"></script>
<script type="text/javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>
<script type="text/javascript" src="../lib/js/tabs_interna.js"></script>

<script>

window.addEvent('domready', function() 
{
    
    if($defined(document.getElementById('tabs_internas')))
    {
      var tabs_internas = new TabsInterna('tabs_internas');
    }
});

</script>

<?

$data_filtro = validaVarPost('filtro_mes') ;
$cod_pizzarias = validaVarPost('cod_pizzarias');

?>

<form name="frmFiltro" method="post">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
    <tr>
      <td class="legenda tdbl tdbt" align="right"><label for="filtro_mes">Mês/Ano:</label></td>
      <td class="tdbt ">&nbsp;</td>
      <td class="tdbr tdbt ">
      <select name="filtro_mes" id="filtro_mes" >
      	<?
      		$con = conectar_bd();
      		$sql_busca_meses = "select DISTINCT(data_inicio) as mes from ipi_pizzarias_estatisticas WHERE tipo='franqueadora' order by mes DESC";
      		$res_busca_meses = mysql_query($sql_busca_meses);
      		while($obj_busca_meses = mysql_fetch_object($res_busca_meses))
      		{
      			$mes_value = date("m",strtotime($obj_busca_meses->mes))."##".date("Y",strtotime($obj_busca_meses->mes));
      			$mes = date("m",strtotime($obj_busca_meses->mes));
      			$ano = date("Y",strtotime($obj_busca_meses->mes));
						echo "<option value='".$mes_value."' ".($mes_value==$data_filtro ? "selected":"" )." >".$mes."/".$ano."</option>";
      		}
      		desconectar_bd($con);
      	?>
      </select>
      
      
      </td>
    </tr>


    <tr>
      <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
      <td>&nbsp;</td>
      <td class="tdbr ">
        <select name="cod_pizzarias" id="cod_pizzarias">
          <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
          <?// 
          $con = conectabd();
          //p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")
          $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE situacao='ATIVO' and cod_pizzarias !='1' ORDER BY p.nome";//pedido do rubens,não mostrar a matrix
          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
          {
            echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
            if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
              echo 'selected';
            echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
          }
          desconectabd($con);
          ?>
        </select>
      </td>
    </tr>

    <tr>
      <td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Filtrar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="buscar">

</form>

<br><br>

<?php if($acao == 'buscar'): ?>

<?php 

$conexao = conectabd();

$data_filtro = explode("##",$data_filtro);
$mes = $data_filtro[0];
$ano = $data_filtro[1];
$sql_buscar_perguntas = "SELECT e.*,p.bairro,p.cidade,p.cod_pizzarias FROM $tabela e inner join ipi_pizzarias p on p.cod_pizzarias = e.cod_pizzarias WHERE month(e.data_inicio) = '$mes' and year(e.data_inicio) = '$ano' and e.estatistica = 'Total de Pizzas Vendidas' AND tipo = 'franqueadora'";
if($cod_pizzarias)
	$sql_buscar_perguntas .= " and p.cod_pizzarias = $cod_pizzarias ";
	
	$sql_buscar_perguntas .= "	order by e.valor DESC";
$res_buscar_perguntas = mysql_query($sql_buscar_perguntas);
//echo "<br>sql_buscar_perguntas: ".$sql_buscar_perguntas;
echo "<div id='tudo' style='margin:0 auto' >";
while ($obj_buscar_perguntas = mysql_fetch_object($res_buscar_perguntas))
{    
   echo '<div style="border: 1 !important;width:320px;float:left;padding-bottom:5px; " align="center"><p align="center"><br/></p><div id="indicador_' . $num_grafico . '" style="margin: 0px auto; width: 300px;"></div>';
    
    $sql_busca_pizzarias = "select e.* from $tabela e inner join ipi_pizzarias p on p.cod_pizzarias = e.cod_pizzarias where e.cod_pizzarias = ".$obj_buscar_perguntas->cod_pizzarias." and month(data_inicio) = '$mes' and year(data_inicio) = '$ano' AND tipo='franqueadora'";
    //echo $sql_busca_pizzarias;
    $res_busca_pizzarias = mysql_query($sql_busca_pizzarias);

    echo "<table style='width:300px !important;border-collapse: collapse' cellpadding='3' border=1 >";
    echo "<tr><td align='center' colspan='2'><strong>".$obj_buscar_perguntas->cidade." - ".$obj_buscar_perguntas->bairro."</strong></td></tr>";
    
    while($obj_buscar_pizzarias = mysql_fetch_object($res_busca_pizzarias))
    {
      $destaque = '';
      $estatistica = $obj_buscar_pizzarias->estatistica;
      if($estatistica == 'Total de Pizzas Vendidas' || $estatistica == 'Total de Pizzas Promocionais')
      {
        $destaque = 'font-weight: bold';
      }

      echo "<tr><td style='padding: 1px; $destaque' width='180'>".$estatistica."</td>";
      if($obj_buscar_pizzarias->unidade=="porcentagem")
      {
        echo "<td style='padding: 1px; $destaque'>".$obj_buscar_pizzarias->valor."%</td></tr>";
      }
      else
      {
        if($obj_buscar_pizzarias->unidade=="dinheiro")
        {
          echo "<td style='padding: 1px; $destaque'>R$ ".bd2moeda($obj_buscar_pizzarias->valor)."</td></tr>";
        }
        else
        {
          if($obj_buscar_pizzarias->unidade=="pizzas")
          {
            echo "<td style='padding: 1px; $destaque'>".$obj_buscar_pizzarias->valor." pizzas</td></tr>";
          }
          else
            if($obj_buscar_pizzarias->unidade=="unidade")
            {
              echo "<td style='padding: 1px; $destaque'>".$obj_buscar_pizzarias->valor."</td></tr>";
            }
        }
          
      }
      
    
    }
    echo "</table>";
    echo '</div>';
}
echo "</div>";

desconectabd($conexao);

?>

<?php endif; ?>

<?php rodape(); ?>
