<?php

/**
 * Relatório
 * 
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       12/08/2010   ELIAS        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Quantidades de Pedidos');

$exibir_barra_lateral = false;

?>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script language="javascript" src="../lib/js/calendario.js"></script>
<script language="javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>

<script>
function atualizar_grafico()
{
	var cod_pizzarias = document.frmFiltro.cod_pizzarias.value;
	var data_inicial = document.frmFiltro.data_inicial.value;
	var data_final = document.frmFiltro.data_final.value;
	var periodo_grafico = document.frmFiltro.periodo_grafico.value;
    
	var grafico_vendas = new FusionCharts('../lib/swf/fusioncharts/scrollline2d.swf', 'grafico_vendas', 900, 250, 0, 0, 'ffffff', 0);
    grafico_vendas.setDataURL('ipi_rel_quantidade_pedidos_ajax.php?param=1,' + cod_pizzarias +','+ data_inicial +','+ periodo_grafico+','+ data_final);
    grafico_vendas.render('grafico_vendas');
    
    var grafico_promocao = new FusionCharts('../lib/swf/fusioncharts/scrollline2d.swf', 'grafico_promocao', 900, 250, 0, 0, 'ffffff', 0);
    grafico_promocao.setDataURL('ipi_rel_quantidade_pedidos_ajax.php?param=2,' + cod_pizzarias +','+ data_inicial +','+ periodo_grafico+','+ data_final);
    grafico_promocao.render('grafico_promocao');
    
    var grafico_fidelidade = new FusionCharts('../lib/swf/fusioncharts/scrollline2d.swf', 'grafico_fidelidade', 900, 250, 0, 0, 'ffffff', 0);
    grafico_fidelidade.setDataURL('ipi_rel_quantidade_pedidos_ajax.php?param=3,' + cod_pizzarias +','+ data_inicial +','+ periodo_grafico+','+ data_final);
    grafico_fidelidade.render('grafico_fidelidade');
    
    var grafico_combo = new FusionCharts('../lib/swf/fusioncharts/scrollline2d.swf', 'grafico_combo', 900, 250, 0, 0, 'ffffff', 0);
    grafico_combo.setDataURL('ipi_rel_quantidade_pedidos_ajax.php?param=4,' + cod_pizzarias +','+ data_inicial +','+ periodo_grafico+','+ data_final);
    grafico_combo.render('grafico_combo');
}

window.addEvent('domready', function()
{
	new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  	new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
});
</script>

<? if ($exibir_barra_lateral): ?>

<table>
    <tr>
        <!-- Conteúdo -->
        <td class="conteudo">

		<? endif; ?>

        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">
			<tr>
				<td class="legenda tdbl tdbt" align="right"><label for="data_inicial">Data Inicial:</label></td>
			    <td>&nbsp;</td>
			    <td class="tdbr tdbt"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" onkeypress="return MascaraData(this, event)">
			    &nbsp;<a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
			    </td>
			</tr>
			  
			<tr>
				<td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
			    <td>&nbsp;</td>
			    <td class="tdbr">
			    <input class="requerido" type="text" name="data_final" id="data_final" size="12" onkeypress="return MascaraData(this, event)">
			    &nbsp;<a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
			    </td>
			</tr>
			
			<tr>
		    	<td class="legenda tdbl" align="right"><label for="periodo_grafico">Periodo:</label></td>
		    	<td class="">&nbsp;</td>
		    	<td class="tdbr">
		      	<select name="periodo_grafico" id=periodo_grafico>
		        	<option value="dia">Dia</option>
		        	<option value="semana">Semana</option>
		      	</select>
		    	</td>
		  	</tr>
		  	
		  	<tr>
				<td class="legenda tdbl" align="right"><label for="cod_pizzarias">Pizzaria:</label></td>
			    <td>&nbsp;</td>
			    <td class="tdbr">
			      	<select name="cod_pizzarias" id="cod_pizzarias">
			        	<option value="">Todas as Pizzarias</option>
				        <?
				        $con = conectabd();
				        
				        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias ORDER BY nome";
				        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
				        
				        while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
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
                <td align="right" class="tdbl tdbb tdbr" colspan="3"><Br />
                    <input class="botaoAzul" type="button" value="Buscar" onclick="atualizar_grafico();">
                </td>
            </tr>

        </table>

        <input type="hidden" name="acao" value="buscar"></form>

        <br><br>
        
        <table align="center" border="0" style="margin: 0px auto;">
        
        <tr><td id="grafico_vendas" align="center"></td></tr>
        <tr><td id="grafico_promocao" align="center"></td></tr>
        <tr><td id="grafico_fidelidade" align="center"></td></tr>
        <tr><td id="grafico_combo" align="center"></td></tr>
        </table>
        
        <br>

<? if ($exibir_barra_lateral): ?>

        </td>
        <!-- Conteúdo -->

        <!-- Barra Lateral -->
        <td class="lateral">
        <div class="blocoNavegacao">
        <ul>
            <li><a href="#">Atalho 1</a></li>
            <li><a href="#">Atalho 2</a></li>
        </ul>
        </div>
        </td>
        <!-- Barra Lateral -->

    </tr>
</table>

<? endif;?>

<? rodape(); ?>
