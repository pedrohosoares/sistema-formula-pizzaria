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

cabecalho('Relatório de CPV');

$acao = validaVarPost('acao');
?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script language="javascript" src="../lib/js/calendario.js"></script>
<script>
window.addEvent('domready', function() { 
  // DatePick
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 
</script>


<? if ($acao != 'detalhes'): ?>

<script language="javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>

<script>
function atualizar_grafico_geral()
{
    var cod_pizzarias = $('cod_pizzarias').value;
    var cod_tamanhos = $('cod_tamanhos').value;
    var cod_pizzas = $('cod_pizzas').value;

    var grafico_geral = new FusionCharts('../lib/swf/fusioncharts/msline.swf', 
                                         'grafico', 900, 400, 1, 0, 'ffffff', 0);

    grafico_geral.setDataURL('ipi_rel_financeiro_grafico_cpv_ajax.php?param=<? echo date('dmYHis'); ?>,' + cod_pizzarias + ',' + cod_pizzas + ',' + cod_tamanhos);
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
      <td class="legenda tdbl tdbt" align="right"><label for="data_inicial">Data Inicial:</label></td>
      <td class="tdbt">&nbsp;</td>
      <td class="tdbr tdbt"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo $data_inicial ?>" onkeypress="return MascaraData(this, event)">
      &nbsp;
      <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
      </td>
    </tr>
    


    <tr>
      <td class="legenda tdbl" align="right">
        <label for="data_final">Data Final:</label>
      </td>
      <td>&nbsp;</td>
      <td class="tdbr">
        <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo $data_final ?>" onkeypress="return MascaraData(this, event)">
        &nbsp;
        <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
      </td>
    </tr>



    <tr>
        <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
        <td>&nbsp;</td>
        <td class="tdbr">
        <select name="cod_pizzarias" id="cod_pizzarias" style="width: 200px;">
          <option value="">Selecione</option>
            <?
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
            ?>
      </select></td>
    </tr>



    <tr>
        <td class="legenda tdbl" align="right"><label for="cod_pizzarias">Tamanho (<? echo ucfirst(TIPO_PRODUTO)?>):</label></td>
        <td>&nbsp;</td>
        <td class="tdbr">
	        <select name="cod_tamanhos" id="cod_tamanhos" style="width: 200px;">
            <option value="0">Selecione</option>
				    <?
				    $sqlBordas = "SELECT * FROM ipi_tamanhos t ORDER BY t.tamanho";
				    $resBordas = mysql_query ( $sqlBordas );
				    $linBordas = mysql_num_rows ( $resBordas );
				    if ($linBordas > 0) {
					    for($a = 0; $a < $linBordas; $a ++) {
						    $objBordas = mysql_fetch_object ( $resBordas );
						    echo '<option value="'.$objBordas->cod_tamanhos.'">'.$objBordas->tamanho.'</option>';
					    }
				    }
				    //desconectabd ($conexao);
				    ?>
          </select>
      </td>
    </tr>


	    

    <tr>
        <td class="legenda tdbl" align="right"><label for="cod_pizzarias">Sabor (<? echo ucfirst(TIPO_PRODUTO)?>):</label></td>
        <td>&nbsp;</td>
        <td class="tdbr">
	        <select name="cod_pizzas" id="cod_pizzas" style="width: 200px;">
            <option value="0">Selecione</option>
				    <?
				    $sql_pizzas = "SELECT * FROM ipi_pizzas p ORDER BY p.pizza";
				    $res_pizzas = mysql_query ( $sql_pizzas );
				    $lin_pizzas = mysql_num_rows ( $res_pizzas );
				    if ($lin_pizzas > 0) 
			      {
					    for($a = 0; $a < $lin_pizzas; $a ++) 
				      {
						    $obj_pizzas = mysql_fetch_object ( $res_pizzas );
						    echo '<option value="'.$obj_pizzas->cod_pizzas.'">'.$obj_pizzas->pizza.'</option>';
					    }
				    }
				    //desconectabd ($conexao);
				    ?>
          </select>
      </td>
    </tr>



    <tr>
        <td align="right" class="tdbl tdbb tdbr" colspan="3">
          <input class="botaoAzul" type="button" value="Buscar" onclick="atualizar_grafico_geral()">
        </td>
    </tr>



</table>

<input type="hidden" name="acao" value="buscar">
</form>

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
