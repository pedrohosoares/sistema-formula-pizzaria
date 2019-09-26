<?php

/**
 * ipi_ingrediente.php: Cadastro de Ingrediente
 * 
 * Índice: cod_ingredientes
 * Tabela: ipi_ingredientes
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho_relatorio('Estatisticas das Pizzarias');

$tabela = 'ipi_pizzarias_estatisticas';
$chave_primaria = 'cod_pizzarias_estatisticas';

$cod_pizzarias = validaVarPost('cod_pizzarias');
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

$mes = validaVarGet('mes');
$ano = validaVarGet('ano');
$pizzarias = validaVarGet("p");
?>
<script type="text/javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>
	<div id="informacoes_gerais">
	<?
	echo "Gerado por <b>". $_SESSION['usuario']['usuario']. "</b> - " . date("d/m/Y H:i:s");
	?>
	</div>



    <table align="center">
	<tr>
    <td class="conteudo" align="center">
	<b>Mês/Ano:</b> <? echo $mes."/".$ano ;?><br/>
  <b>Pizzaria: </b>
	<?
	$conexao = conectabd();

    $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE ".($p!=""? " cod_pizzarias IN ($pizzarias) and" : "")." cod_pizzarias not in(1)";
	if ($cod_pizzarias)
		$SqlBuscaPizzarias .= " AND cod_pizzarias IN ($cod_pizzarias)";
	$SqlBuscaPizzarias .= " ORDER BY nome";
	//echo $SqlBuscaPizzarias;
    $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
    while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
	{
		echo bd2texto($objBuscaPizzarias->nome).", ";
	}

    ?>
    <br/><br/>
    <?php 

      $sql_buscar_perguntas = "SELECT e.*,p.bairro,p.cidade,p.cod_pizzarias FROM $tabela e inner join ipi_pizzarias p on p.cod_pizzarias = e.cod_pizzarias inner join ipi_pizzarias_estatisticas_nomes nom on nom.cod_estatisticas = e.estatistica WHERE month(e.data_inicio) = '$mes' and year(e.data_inicio) = '$ano' and e.estatistica = '1' AND e.cod_pizzarias NOT IN (1) AND tipo = 'franquias'";
      if($p)
        $sql_buscar_perguntas .= " and p.cod_pizzarias = $p ";
        
        $sql_buscar_perguntas .= "  order by e.valor DESC";
      $res_buscar_perguntas = mysql_query($sql_buscar_perguntas);
      //echo "<br>sql_buscar_perguntas: ".$sql_buscar_perguntas;

      $num_grafico = 0;
      echo "<div id='tudo' style='font-size:10px;margin:0 auto' >";
      while ($obj_buscar_perguntas = mysql_fetch_object($res_buscar_perguntas))
      {//'.(in_array($obj_buscar_perguntas->cod_pizzarias,$_SESSION['usuario']['cod_pizzarias']) ?'background-color: #FFE5B8;' :'' ).'
          echo '<div style="border: 1 !important;width:260px;float:left;padding-bottom:5px; " align="center"><p align="center"><br/></p><div id="indicador_' . $num_grafico . '" style="margin: 0px auto; width: 255px;"></div>';//<b>' .$obj_buscar_perguntas->nome. '</b>
          
          $sql_busca_pizzarias = "select e.*,nom.*from $tabela e inner join ipi_pizzarias p on p.cod_pizzarias = e.cod_pizzarias inner join ipi_pizzarias_estatisticas_nomes nom on nom.cod_estatisticas = e.estatistica where e.cod_pizzarias = ".$obj_buscar_perguntas->cod_pizzarias." and month(data_inicio) = '$mes' and year(data_inicio) = '$ano' AND e.cod_pizzarias NOT IN (1) order by nom.ordem_exibicao";
          //echo $sql_busca_pizzarias;
          $res_busca_pizzarias = mysql_query($sql_busca_pizzarias);
          
          echo "<table style='width:255px !important;border-collapse: collapse' cellpadding='3' border=1 >";
          echo "<tr><td align='center' colspan='2'><strong>".$obj_buscar_perguntas->cidade." - ".$obj_buscar_perguntas->bairro."</strong></td></tr>";
          while($obj_buscar_pizzarias = mysql_fetch_object($res_busca_pizzarias))
          {

            $cor_fundo = "";
            if($obj_buscar_pizzarias->estatistica=="1")
            {
              switch($obj_buscar_pizzarias->valor)
              {
                case ($obj_buscar_pizzarias->valor<90):
                  $cor_fundo = "#E6724C";
                break;
                case ($obj_buscar_pizzarias->valor<95):
                  $cor_fundo = "#F2D57E";
                break;
                default:
                  $cor_fundo = "#D5EDA4";
                break;
              }
            }

            echo "<tr><td style='background-color:$cor_fundo; padding: 1px;'>".$obj_buscar_pizzarias->nome_estatistica."</td>";
            if($obj_buscar_pizzarias->unidade=="porcentagem")
            {
              echo "<td style='background-color:$cor_fundo; padding: 1px;'>".$obj_buscar_pizzarias->valor."%</td></tr>";
            }
            else
            {
              if($obj_buscar_pizzarias->unidade=="dinheiro")
              {
                echo "<td style='background-color:$cor_fundo; padding: 1px;'>R$ ".bd2moeda($obj_buscar_pizzarias->valor)."</td></tr>";
              }
              else
              {
                if($obj_buscar_pizzarias->unidade=="pizzas")
                {
                  echo "<td style='background-color:$cor_fundo; padding: 1px;'>".$obj_buscar_pizzarias->valor." pizzas</td></tr>";
                }
              }
                
            }
            
          
          }
          echo "</table>";
          echo '</div>';
          $num_grafico++;
      }
      echo "</div>";

      $sql_buscar_perguntas = "SELECT e.*,p.nome,nom.nome_estatistica FROM $tabela e INNER JOIN ipi_pizzarias p ON (p.cod_pizzarias = e.cod_pizzarias) inner join ipi_pizzarias_estatisticas_nomes nom on nom.cod_estatisticas = e.estatistica WHERE month(e.data_inicio) = '$mes' AND year(e.data_inicio) = '$ano' AND e.estatistica = '1' AND e.cod_pizzarias NOT IN (1)";
      if($cod_pizzarias)
      {
        $sql_buscar_perguntas .= " and e.cod_pizzarias = $cod_pizzarias ";
      }
      $sql_buscar_perguntas .= "  order by e.valor DESC";
      $res_buscar_perguntas = mysql_query($sql_buscar_perguntas);

      $num_grafico = 0;
      //echo $sql_buscar_perguntas;
      while ($obj_buscar_perguntas = mysql_fetch_object($res_buscar_perguntas)):
        ?>
        <script>
        var indicadores_<? echo $num_grafico; ?> = new FusionCharts('../lib/swf/fusioncharts/angulargauge.swf', 'grafico <?echo $num_grafico; ?>', 255, 150, 0, 0, 'ffffff', 0);
        indicadores_<? echo $num_grafico; ?>.setDataURL('ipi_estatisticas_pizzaria_ajax.php?param=1,<? echo $obj_buscar_perguntas->valor; ?>,<? echo utf8_encode($obj_buscar_perguntas->nome_estatistica); ?>');
        indicadores_<? echo $num_grafico; ?>.render('indicador_<? echo $num_grafico; ?>');
        </script>
        <?
        $num_grafico++;
      endwhile;

      desconectabd($conexao);

      ?>
    </td>
    </tr>
</table>
  
  
  
 </div>

<? rodape_relatorio(); ?>
