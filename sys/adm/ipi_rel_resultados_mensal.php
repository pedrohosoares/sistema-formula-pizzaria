<?php

/**
 * Relatório de Fluxo de caixa e faturamento.
 *
 * @version 1.0
 * @package gerencial
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       22/03/2012   PEDRO H.      Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Resultado Mensal');

$exibir_barra_lateral = false;

?>

<script language="javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>

<script>
function atualizar_grafico()
{
  atualizar_tabela();
	var ano_ref = document.frmFiltro.ano_ref_filtro.value;
	var sem_ref = document.frmFiltro.sem_ref_filtro.value;
    var cod_pizza = document.frmFiltro.pizza_ref_filtro.value;
    var situacao_filtro = document.frmFiltro.situacao_filtro.value;
    var filtrar_por = document.frmFiltro.filtrar_filtro.value;

  var grafico_detalhado = new FusionCharts('../lib/swf/fusioncharts/mscolumn3d.swf', 'grafico2', 500, 400, 0, 0, 'ffffff', 0);
  grafico_detalhado.setDataURL('ipi_rel_resultados_mensal_ajax.php?param=<? echo date('dmYHis'); ?>,1,' + ano_ref + ',' + sem_ref + ',' + cod_pizza + ',' + situacao_filtro+','+filtrar_por);
  grafico_detalhado.render('grafico_fluxo');
}

function atualizar_tabela()
{
	var ano_ref = document.frmFiltro.ano_ref_filtro.value;
	var sem_ref = document.frmFiltro.sem_ref_filtro.value;
	var cod_pizza = document.frmFiltro.pizza_ref_filtro.value;
    var situacao_filtro = document.frmFiltro.situacao_filtro.value;
	var filtrar_por = document.frmFiltro.filtrar_filtro.value;
    
	new Request.HTML(
	{
	  url: 'ipi_rel_resultados_mensal_ajax.php',
	  method: 'get',
	  update: $('tabela_fluxo')
	}).send('param=<? echo date('dmYHis'); ?>,2,' + ano_ref + ',' + sem_ref + ',' + cod_pizza + ',' + situacao_filtro + ',' + filtrar_por);	
}

window.addEvent('domready', function()
{
	atualizar_grafico();
});

</script>

<? if ($exibir_barra_lateral): ?>

<table>
    <tr>
        <!-- Conteúdo -->
        <td class="conteudo">

		<? endif; ?>
		<? $meses = array(); ?>

        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">
            <tr>
                <td class="legenda tdbl tdbt sep" align="right"><label for="pizza_ref_filtro"><? echo ucfirst(TIPO_EMPRESAS)?>:</label></td>
                <td class="sep tdbt">&nbsp;</td>
                <td class="tdbr sep tdbt">
                	<select name="pizza_ref_filtro" style="width: 250px;">                    	
                    	<?
    
                    	$conexao = conectabd();
                    	
                    	$sql_buscar_pizzarias = "SELECT DISTINCT ip.cod_pizzarias, ip.nome FROM ipi_titulos_parcelas itp INNER JOIN ipi_titulos it ON (itp.cod_titulos = it.cod_titulos) INNER JOIN ipi_pizzarias ip ON (it.cod_pizzarias = ip.cod_pizzarias) WHERE ip.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']).") AND itp.situacao = 'PAGO'";
                    	$res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
                    	
                    	while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
                    	{
                    	  echo '<option value="' . $obj_buscar_pizzarias->cod_pizzarias . '">' . bd2texto($obj_buscar_pizzarias->nome) . '</option>';
                    	}
                    	
                    	desconectabd($conexao);
                    	
                    	?>
                    	
                	</select>
            	</td>
            </tr>
            
            <tr>
                <td class="legenda tdbl sep" align="right"><label for="ano_ref_filtro">Ano:</label></td>
                <td class="sep">&nbsp;</td>
                <td class="tdbr sep">
                	<select name="ano_ref_filtro" style="width: 250px;">                    	
                    	<?
    
                    	$conexao = conectabd();
                    	
                    	$sql_buscar_valores = "SELECT DISTINCT ano_ref FROM ipi_titulos_parcelas ORDER BY ano_ref";
                    	$res_buscar_valores = mysql_query($sql_buscar_valores);
                    	

                    	while($obj_buscar_valores = mysql_fetch_object($res_buscar_valores))
                    	{
                    	  echo '<option value="' . $obj_buscar_valores->ano_ref . '">' . bd2texto($obj_buscar_valores->ano_ref) . '</option>';
                    	}
                    	
                    	desconectabd($conexao);
                    	
                    	?>
                    	
                	</select>
            	</td>
            </tr>
            
            <tr>
                <td class="legenda tdbl sep" align="right"><label for="situacao_filtro">Situação:</label></td>
                <td class="sep">&nbsp;</td>
                <td class="tdbr sep">
                    <select name="situacao_filtro" style="width: 250px;">                  
                      <option value="1">Todos</option>                  
                      <option value="2">Abertos</option>                 
                      <option value="3">Pagos</option>                    
                    </select>
                </td>
            </tr>

            <tr>
                <td class="legenda tdbl sep" align="right"><label for="filtrar_filtro">Filtrar por:</label></td>
                <td class="sep">&nbsp;</td>
                <td class="tdbr sep">
                    <select name="filtrar_filtro" style="width: 250px;">
                        <option value="MES_REFERENCIA">Mês de referencia</option>
                        <option value="DATA_VENCIMENTO">Data de Vencimento</option>
                    </select>
                </td>
            </tr>


            <tr>
                <td class="legenda tdbl sep" align="right"><label for="sem_ref_filtro">Semestre:</label></td>
                <td class="sep">&nbsp;</td>
                <td class="tdbr sep">
                    <select name="sem_ref_filtro" style="width: 250px;">                  
                      <option value="1">1º Semestre</option>                  
                      <option value="2">2º Semestre</option>                    
                    </select>
                </td>
            </tr>
            
            <tr>
                <td align="right" class="tdbl tdbb tdbr" colspan="3">
                    <input class="botaoAzul" type="button" value="Buscar" onclick="atualizar_grafico();">
                </td>
            </tr>

        </table>

        <input type="hidden" name="acao" value="buscar"></form>

        <br><br>
        
        <table align="center" style="margin: 0px auto;">
          <tr>            
            <td colspan="3" align="center" id="tabela_fluxo">
            </td>
          </tr>
          <tr>
            <td colspan="3" id="grafico_fluxo">
            </td>
          </tr>
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
