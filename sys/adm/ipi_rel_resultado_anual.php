<?php

/**
 * ipi_rel_ger_media_pedidos_pizza.php: Relatório gerencial de média de pizzas por pedido
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Volume de Vendas');

$acao = validaVarPost('acao');
?>

<? if ($acao != 'detalhes'): ?>

<script language="javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>

<script>
function atualizar_grafico_geral()
{
    var ano = document.getElementById('ano').value;
    var cod_pizzarias = document.getElementById('cod_pizzarias').value;
    //var origem = document.getElementById('origem').value;

    var grafico_geral = new FusionCharts('../lib/swf/fusioncharts/msline.swf', 
                                         'grafico', 900, 400, 0, 0, 'ffffff', 0);

    grafico_geral.setDataURL('ipi_rel_resultado_anual_ajax.php?param=<? echo date('dmYHis'); ?>,1,' + ano + ',' + cod_pizzarias);
    grafico_geral.render('grafico_geral');
}

function init()
{
    atualizar_grafico_geral();
}

window.addEvent('domready', init); 
</script>


<?
$ano = validaVarPost('ano');
$cod_pizzarias = validaVarPost('cod_pizzarias');
$origem = validaVarPost('origem');
?>

<form name="frmFiltro" method="post">
<table align="center" class="caixa" cellpadding="0" cellspacing="0">

    <tr>
        <td class="legenda tdbl tdbt" align="right"><label for="ano">Ano:</label></td>
        <td class="tdbt">&nbsp;</td>
        <td class="tdbr tdbt"><select name="ano" id="ano" style="width: 200px;">
        
        <?
        $con = conectabd();
        
        $sql_buscar_ano_min = "SELECT MIN(YEAR(data_hora_pedido)) AS ano_min FROM ipi_pedidos WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
        $res_buscar_ano_min = mysql_query($sql_buscar_ano_min);
        $obj_buscar_ano_min = mysql_fetch_object($res_buscar_ano_min);
        
        $sql_buscar_ano_max = "SELECT MAX(YEAR(data_hora_pedido)) AS ano_max FROM ipi_pedidos WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
        $res_buscar_ano_max = mysql_query($sql_buscar_ano_max);
        $obj_buscar_ano_max = mysql_fetch_object($res_buscar_ano_max);
		if (($obj_buscar_ano_max->ano_max!="")&&($obj_buscar_ano_min->ano_min))
		{
		    for ($a = $obj_buscar_ano_max->ano_max; $a >= $obj_buscar_ano_min->ano_min; $a--)
		    {
		        echo '<option value="' . $a . '">' . $a . '</option>';
		    }
		}
		else
		{
		        echo '<option value="' . date("Y") . '">' . date("Y") . '</option>';
		}
        desconectabd($con);
        ?>
        </select></td>
    </tr>

    <tr>
        <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
        <td>&nbsp;</td>
        <td class="tdbr">
          <select name="cod_pizzarias" id="cod_pizzarias" style="width: 200px;">
            <?
            //if(count($_SESSION['usuario']['cod_pizzarias']) > 0)
            //{
            //	echo '<option value="0">Todas as Pizzarias</option>';	
            //}
            $con = conectabd();
            $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY nome";
            $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
            while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias))
            {
              echo '<option value="' . $objBuscaPizzarias->cod_pizzarias . '" ';
              if ($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
                  echo 'selected';
              echo '>' . bd2texto($objBuscaPizzarias->nome) . '</option>';
            }
            desconectabd($con);
            ?>
        </select>
      </td>
    </tr>
<!--
    <tr>
        <td class="legenda tdbl sep" align="right"><label for="origem">Origem:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep"><select name="origem" id="origem"
            style="width: 200px;">
            <option value="TODOS" <? if ($origem == 'TODOS') { echo 'selected'; } ?>>Todas</option>
            <option value="NET" <? if ($origem == 'NET') { echo 'selected'; } ?>>Net</option>
            <option value="TEL" <? if ($origem == 'TEL') { echo 'selected'; } ?>>Tel</option>
        </select></td>
    </tr>
-->
    <tr>
        <td align="right" class="tdbl tdbb tdbr" colspan="3">
          <input class="botaoAzul" type="button" value="Buscar" onclick="atualizar_grafico_geral()">
        </td>
    </tr>

</table>

<input type="hidden" name="acao" value="buscar"></form>

<br><br>

<table align="center" style="margin: 0px auto;" cellpadding="0" cellspacing="0">
  <tr>
    <td width="900" align="center">
		  <div id="grafico_geral"></div>
	  </td>
  </tr>
</table>

<br><br>

<!-- 
<table class="listaEdicao" cellpadding="0" cellspacing="0">
<thead>
  <tr>
    <td align="center" width="70">Pedido</td>
    <td align="center">Cliente</td>
    <td align="center">Endereço</td>
    <td align="center" width="50">Número</td>
    <td align="center" width="100">Complemento</td>
    <td align="center" width="100">Bairro</td>
    <td align="center" width="70">Situação</td>
    <td align="center" width="70">Agendado</td>
    <td align="center" width="80">Pizzaria</td>
    <td align="center">Horário do Pedido</td>
    <td align="center">Horário da Baixa</td>
    <td align="center" width="70">Valor Total</td>
    <td align="center" width="70">Origem</td>
  </tr>
</thead>
<tbody>

</tbody>
</table>
 -->


<? endif; ?>

<? rodape(); ?>
