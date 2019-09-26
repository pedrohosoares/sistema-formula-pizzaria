<?php

/**
 * ipi_rel_forma_pagamentos.php: Relatório de Formas de Pagamentos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Formas de Pagamentos');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';

$cod_pizzarias = validaVarPost('cod_pizzarias');
$data_inicial = (validaVarPost('data_inicial') != '') ? (validaVarPost('data_inicial')) : date('d/m/Y');
$data_final = (validaVarPost('data_final') != '') ? (validaVarPost('data_final')) : date('d/m/Y');

$hora_final = validaVarPost('hora_final');
$hora_inicial = validaVarPost('hora_inicial');


if(validar_hora($hora_inicial))
{
  $hora_inicial_sql = $hora_inicial.':00'; 
}
else
{
  $hora_inicial_sql = '00:00:00'; 
}

if(validar_hora($hora_final))
{
  $hora_final_sql = $hora_final.':59'; 
}
else
{
  $hora_final_sql = '23:59:59'; 
}


?>


<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script type="text/javascript" src="../lib/js/calendario.js"></script>
<script type="text/javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>


<script>
window.addEvent('domready', function() 
{
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});

  var data_inicial = $('data_inicial').value;
  var data_final = $('data_final').value;
  var cod_pizzarias = $('cod_pizzarias').value;
  var hora_inicial = $('hora_inicial').value.toString().replace(":","X"); //FIXME: ajustar, Por algum motivo não vai os ":" mesmo com URL encode, sem tempo de pesquisa, substituido
  var hora_final = $('hora_final').value.toString().replace(":","X"); //FIXME: ajustar, Por algum motivo não vai os ":" mesmo com URL encode, sem tempo de pesquisa, substituido

  //alert(hora_inicial + ' - ' +hora_final);

	var formas_pagamento = new FusionCharts('../lib/swf/fusioncharts/pie2d.swf', 'grafico_formas_pagamento', 400, 400, 0, 0, 'ffffff', 0);
  formas_pagamento.setDataURL('ipi_rel_forma_pagamentos_ajax_v1_1.php?param=1,'+data_inicial+','+data_final+','+cod_pizzarias+','+ hora_inicial+','+hora_final);
  formas_pagamento.render('grafico_formas_pagamento');

	var net_tel = new FusionCharts('../lib/swf/fusioncharts/pie2d.swf', 'grafico_net_tel', 400, 400, 0, 0, 'ffffff', 0);
  net_tel.setDataURL('ipi_rel_forma_pagamentos_ajax_v1_1.php?param=2,'+data_inicial+','+data_final+','+cod_pizzarias+','+hora_inicial+','+hora_final);
  net_tel.render('grafico_net_tel');
});
</script>


<form name="frmFiltro" method="post">
<?
//$data_inicial = validaVarPost('data_inicial');
//$data_final = validaVarPost('data_final');
?>

 <table align="center" class="caixa" cellpadding="0" cellspacing="0">

  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbr tdbt">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
        <?
        $con = conectabd();
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY p.nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
        while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
          echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
          
          if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
        }
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="data_inicial">Data Inicial:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo ($data_inicial != '') ? $data_inicial : date('d/m/Y') ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
    &nbsp;
    <input type="text" name="hora_inicial" id="hora_inicial" size="3" value="<? echo $hora_inicial ?>" onkeypress="return MascaraHora(this, event)">
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
    <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo ($data_final != '') ? $data_final : date('d/m/Y') ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    &nbsp;
    <input type="text" name="hora_final" id="hora_final" size="3" value="<? echo $hora_final ?>" onkeypress="return MascaraHora(this, event)">
    </td>
  </tr>

  <tr><td align="center" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>

  </table><br /><br />

  <? 

  $con = conectabd(); 

  $data_inicial = data2bd($data_inicial);
  $data_final = data2bd($data_final);

  if($cod_pizzarias > 0)
  {
    $SqlCodPizzarias = "AND p.cod_pizzarias = ".$cod_pizzarias;
  }
  else
  {
    $SqlCodPizzarias = '';
  }
      
  ?>

  <table class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
    <tr>
        <td style="background-color: #e5e5e5;">&nbsp;</td>
        <td style="background-color: #e5e5e5;" align="center"><b>Mesa</b></td>
        <td style="background-color: #e5e5e5;" align="center"><b>Loja</b></td>
        <td style="background-color: #e5e5e5;" align="center"><b>Internet</b></td>
        <td style="background-color: #e5e5e5;" align="center"><b>Total</b></td>
    </tr>
    <?


    /**
    *  ipi_rel_resumo_pizzaria
    *   p.situacao NOT IN ('CANCELADO')
    *  ipi_rel_forma_pagamento/_ajax
    *   p.situacao = 'BAIXADO'
    */


    // Totais pra efeito de calculo de porcentagens

    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_mesa FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND p.origem_pedido = 'MESA' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $con);
    $valor_total_mesa = $objBuscaPedidosSoma->soma_mesa;

    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND p.origem_pedido = 'TEL' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $con);
    $valor_total_tel = $objBuscaPedidosSoma->soma_tel;

    //echo "SELECT SUM(pfp.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND p.origem_pedido = 'TEL' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
    
    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_net FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND p.origem_pedido = 'NET' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $con);
    $valor_total_net = $objBuscaPedidosSoma->soma_net;

    $valor_total_geral =  $valor_total_mesa + $valor_total_tel + $valor_total_net;
    // Totais pra efeito de calculo de porcentagens

    $sql_formas_pg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
    $res_formas_pg = mysql_query($sql_formas_pg);
    $num_formas_pg = mysql_num_rows($res_formas_pg);

    $total_mesa = 0;
    $total_net = 0;
    $total_tel = 0;
    $total_geral = 0;
    
    for ($a = 0; $a < $num_formas_pg; $a++)
    {
        $obj_formas_pg = mysql_fetch_object($res_formas_pg);
        
        ?>    
        <tr>
            <td style="background-color: #e5e5e5;" align="center"><b><? echo $obj_formas_pg->forma_pg; ?></b></td>
            <?
            $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_mesa FROM $tabela p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND pfp.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'MESA' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $con);
            $soma_mesa = $objBuscaPedidosSoma->soma_mesa;
            $total_mesa += $soma_mesa;

            $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_tel FROM $tabela p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND pfp.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'TEL' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $con);
            $soma_tel = $objBuscaPedidosSoma->soma_tel;
            $total_tel += $soma_tel;
            //echo "SELECT SUM(pfp.valor) AS soma_tel FROM $tabela p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND pfp.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'TEL' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
            
            $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_net FROM $tabela p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND pfp.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'NET' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $con);
            $soma_net = $objBuscaPedidosSoma->soma_net;
            $total_net += $soma_net;

            $total_forma_pgto = $soma_mesa + $soma_tel + $soma_net;

            if ($total_forma_pgto!=0)
            {
              $porc_mesa = ($soma_mesa*100)/$total_forma_pgto;
              $porc_tel = ($soma_tel*100)/$total_forma_pgto;
              $porc_net = ($soma_net*100)/$total_forma_pgto;
            }
            else
            {
              $porc_mesa = 0;
              $porc_tel = 0;
              $porc_net = 0;
            }
            if ($valor_total_geral)
            {
              $porc_geral = ($total_forma_pgto*100)/$valor_total_geral;
            }
            else
            {
              $porc_geral = 0;
            }
            
            echo '<td align="center"><strong>' . ($soma_mesa?bd2moeda($soma_mesa):"0") . '</strong><br />('.($porc_mesa?bd2moeda($porc_mesa):"0").'%)' . '</td>';
            echo '<td align="center"><strong>' . ($soma_tel?bd2moeda($soma_tel):"0") . '</strong><br />('.($porc_tel?bd2moeda($porc_tel):"0").'%)' . '</td>';
            echo '<td align="center"><strong>' . ($soma_net?bd2moeda($soma_net):"0") . '</strong><br />('.($porc_net?bd2moeda($porc_net):"0").'%)' . '</td>';
            echo '<td align="center"><b>' . ($total_forma_pgto?bd2moeda($total_forma_pgto):"0") . '</b><br />('.($porc_geral?bd2moeda($porc_geral):"0").'%)' . '</td>';

            $total_geral += ($soma_tel+$soma_net);
            ?>
        </tr>
        <?
    }

    if ($valor_total_geral)
    {
      $porc_mesa = ($total_mesa*100)/$valor_total_geral;
      $porc_tel = ($total_tel*100)/$valor_total_geral;
      $porc_net = ($total_net*100)/$valor_total_geral;
      $porc_geral = ($valor_total_geral*100)/$valor_total_geral;
    }
    else
    {
      $porc_mesa = 0;
      $porc_tel = 0;
      $porc_net = 0;
      $porc_geral = 0;
    }

    ?>
        <tr>
            <td style="background-color: #e5e5e5;" align="right">
            <b>Total:</b>
            </td>
            <td align="center">   <? echo ($total_mesa?bd2moeda($total_mesa):"0") . '<br />('.($porc_mesa?bd2moeda($porc_mesa):"0").'%)'; ?></td>
            <td align="center">   <? echo ($total_tel?bd2moeda($total_tel):"0") . '<br />('.($porc_tel?bd2moeda($porc_tel):"0").'%)'; ?></td>
            <td align="center">   <? echo ($total_net?bd2moeda($total_net):"0") . '<br />('.($porc_tel?bd2moeda($porc_net):"0").'%)'; ?></td>
            <td align="center"><b><? echo ($total_geral?bd2moeda($total_geral):"0") . '<br />('.($porc_tel?bd2moeda($porc_geral):"0").'%)'; ?></b></td>
        </tr>
  </table>

  <br />

  <table border="0" align="center" cellspacing="30">
    <tr>

      <td id="grafico_formas_pagamento">
      </td>

      <td id="grafico_net_tel">
      </td>

    </tr>
  </table>
   
  <?
  desconectabd($con);
  ?>
</form>

<? rodape(); ?>
