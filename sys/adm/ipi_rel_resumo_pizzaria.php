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
 * 1.0       12/05/2010   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Resumo de '.ucfirst(TIPO_EMPRESA).' por Período');

$acao = validaVarPost('acao');

$tabela = 'ipi_enquetes';
$chave_primaria = 'cod_enquetes';

$cod_enquetes = 1; // Forçado para a enquete mail

?>
<link rel="stylesheet" type="text/css" media="screen"
    href="../lib/css/calendario.css" />

<script type="text/javascript" src="../lib/js/calendario.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/js/datepicker_vista/datepicker_vista.css" />
<script>
window.addEvent('domready', function() 
{
    new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
    new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
});
</script>

<?
$data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
$data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
$cod_pizzarias = validaVarPost('cod_pizzarias');
$hora_final = validaVarPost('hora_final');
$hora_inicial = validaVarPost('hora_inicial');
?>

<form name="frmFiltro" method="post">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
    <tr>
        <td class="legenda tdbl tdbt" align="right"><label for="data_inicial">Data
        Inicial:</label></td>
        <td class="tdbt ">&nbsp;</td>
        <td class="tdbr tdbt "><input class="requerido" type="text"
            name="data_inicial" id="data_inicial" size="8"
            value="<?
            echo bd2data($data_inicial)?>"
            onkeypress="return MascaraData(this, event)"> &nbsp; <a
            href="javascript:;" id="botao_data_inicial"><img
            src="../lib/img/principal/botao-data.gif"></a>&nbsp;
    <input type="text" name="hora_inicial" id="hora_inicial" size="3" value="<? echo $hora_inicial ?>" onkeypress="return MascaraHora(this, event)"></td>
    </tr>

    <tr>
        <td class="legenda tdbl " align="right"><label for="data_final">Data
        Final:</label></td>
        <td >&nbsp;</td>
        <td class="tdbr "><input class="requerido" type="text"
            name="data_final" id="data_final" size="8"
            value="<?
            echo bd2data($data_final)?>"
            onkeypress="return MascaraData(this, event)"> &nbsp; <a
            href="javascript:;" id="botao_data_final"><img
            src="../lib/img/principal/botao-data.gif"></a>&nbsp;
    <input type="text" name="hora_final" id="hora_final" size="3" value="<? echo $hora_final ?>" onkeypress="return MascaraHora(this, event)"></td>
    </tr>


    <tr>
        <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
        <td>&nbsp;</td>
        <td class="tdbr ">
          <select name="cod_pizzarias" id="cod_pizzarias">
            <?
            $con = conectabd();
            
            $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY nome";
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
        <td align="right" class="tdbl tdbb tdbr" colspan="3"><input
            class="botaoAzul" type="submit" value="Filtrar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="buscar">

</form>

<br><br>

<?php if($acao == 'buscar'): ?>

<?php 

$conexao = conectabd();
  if (($data_inicial) && ($data_final))
        {
            //  $data_inicial_sql = data2bd($data_inicial); 
            // $data_final_sql = data2bd($data_final);
            $data_inicial_sql = ($data_inicial) ;
            $data_final_sql = ($data_final);
            if(validar_hora($hora_inicial))
            {
              $data_inicial_sql .= ' '.$hora_inicial.':00'; 
            }
            else
            {
              $data_inicial_sql .= ' 00:00:00'; 
            }

            if(validar_hora($hora_final))
            {
              $data_final_sql .= ' '.$hora_final.':59'; 
            }
            else
            {
              $data_final_sql .= ' 23:59:59'; 
            }
            // die($data_inicial_sql);
        }

/*
echo '<br><br><p align="center"><b>Relatório de Fechamento de Caixa</b></p><br><br><br>';
echo '<b>Loja ' . $cod_pizzarias . '</b>: ' . bd2texto($obj_buscar_pizzaria->nome) . '<br>';
echo '<b>Data de Abertura do Caixa</b>: ' . bd2datahora($data_inicial) . '<br>';
echo '<b>Data de Fechamento do Caixa</b>: ' . bd2datahora($data_final) . '<br>';
echo '<b>Observações</b>: ' . bd2texto(nl2br($obj_buscar_pizzaria->obs_caixa)) . '<br><br><br><br>';
*/

// Relatório de formas de pagamento - baixados
echo '<table border="1" cellpadding="0" cellspacing="0" width="500" align="center" style="margin: 0 auto;">';
echo '<tr><td colspan="4" style="background-color: #e5e5e5;" align="center"><b>Formas de Pagamento</b></td></tr>';
echo '<tr>';
echo '<td style="background-color: #e5e5e5;">&nbsp;</td>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>TEL</b></td>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>NET</b></td>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>TOTAL</b></td>';
echo '</tr>';

$sql_formas_pg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
$res_formas_pg = mysql_query($sql_formas_pg);
$num_formas_pg = mysql_num_rows($res_formas_pg);

$total_geral_forma_pg_tel = 0;
$total_geral_forma_pg_net = 0;
$total_geral_forma_pg = 0;

for ($a = 0; $a < $num_formas_pg; $a++)
{
    $obj_formas_pg = mysql_fetch_object($res_formas_pg);

    echo '<tr>';
    echo '<td style="background-color: #e5e5e5;" align="center"><b>' . $obj_formas_pg->forma_pg . '</b></td>';

    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(valor_total) AS soma_tel FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.data_hora_pedido BETWEEN '$data_inicial_sql' AND '$data_final_sql' AND p.forma_pg = '".$obj_formas_pg->forma_pg."' AND p.origem_pedido = 'TEL' AND p.situacao = 'BAIXADO' AND p.cod_pizzarias = $cod_pizzarias", $conexao);
    $soma_tel = $objBuscaPedidosSoma->soma_tel;
    $total_geral_forma_pg_tel += $soma_tel;
    echo '<td align="center">' . bd2moeda($soma_tel) . '</td>';
    
    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(valor_total) AS soma_net FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.data_hora_pedido BETWEEN '$data_inicial_sql' AND '$data_final_sql' AND p.forma_pg = '".$obj_formas_pg->forma_pg."' AND p.origem_pedido IN ('NET','IFOOD') AND p.situacao = 'BAIXADO' AND p.cod_pizzarias = $cod_pizzarias", $conexao);
    $soma_net = $objBuscaPedidosSoma->soma_net;
    $total_geral_forma_pg_net += $soma_net;
    echo '<td align="center">' . bd2moeda($soma_net) . '</td>';
    
    echo '<td align="center"><b>' . bd2moeda($soma_tel + $soma_net) . '</b></td>';
    echo '</tr>';
    
    $total_geral_forma_pg += ($soma_tel + $soma_net);
}
 
echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center">&nbsp;</td>';
echo '<td align="center">' . bd2moeda($total_geral_forma_pg_tel) . '</td>';
echo '<td align="center">' . bd2moeda($total_geral_forma_pg_net) . '</td>';
echo '<td align="center"><b>' . bd2moeda($total_geral_forma_pg) . '</b></td>';
echo '</tr>';

echo '</table><br><br><br><br>';


// Relatório de quantidade vendidas
$sql_buscar_pizzarias = " AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.cod_pizzarias = " . $cod_pizzarias;
$sql_data_hora_pedido = " AND p.data_hora_pedido BETWEEN '$data_inicial_sql' AND '$data_final_sql' ";
$sql_data_hora_pedido_sem_ant = " AND p.data_hora_pedido BETWEEN '" . date('Y-m-d H:i:s', strtotime('-1 week', strtotime($data_inicial))) . "' AND '" . date('Y-m-d H:i:s', strtotime('-1 week', strtotime($data_final))) . "' ";
$sql_situacao_pedido = " AND p.situacao = 'BAIXADO' ";


echo '<table border="1" cellpadding="0" cellspacing="0" width="500" align="center" style="margin: 0 auto;">';
echo '<tr><td colspan="4" style="background-color: #e5e5e5;" align="center"><b>Quantidades Vendidas (Débitos + Baixados)</b></td></tr>';
echo '<tr>';
echo '<td style="background-color: #e5e5e5;">&nbsp;</td>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>TEL</b></td>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>NET</b></td>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>TOTAL</b></td>';
echo '</tr>';
    
//Buscando pizzas vendidas por tamanho
$sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
$res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);

$total_geral = 0;

while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
{
    $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "' AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    $total_pizza_tel = $obj_quant_pizzas->total_tel;
    
    $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "' AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    $total_pizza_net = $obj_quant_pizzas->total_net;
    
    $total_pizza = $total_pizza_tel + $total_pizza_net;
    $total_geral += $total_pizza;

    echo '<tr>';
    echo '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_tamanhos->tamanho) . ' - vendidas</b></td>';
    echo '<td align="center">' . $total_pizza_tel . '</td>';
    echo '<td align="center">' . $total_pizza_net . '</td>';
    echo '<td align="center">' . $total_pizza . '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td align="center">' . $total_geral . '</td>';
echo '</tr>';

//Buscando pizzas vendidas por tamanho sem ant
$sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
$res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);

$total_geral = 0;

while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
{
    $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "' AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido_sem_ant $sql_situacao_pedido";
    $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    $total_pizza_tel = $obj_quant_pizzas->total_tel;
    
    $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "' AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_data_hora_pedido_sem_ant $sql_situacao_pedido";
    $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    $total_pizza_net = $obj_quant_pizzas->total_net;
    
    $total_pizza = $total_pizza_tel + $total_pizza_net;
    $total_geral += $total_pizza;
    
    echo '<tr>';
    echo '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_tamanhos->tamanho) . ' - vendidas sem. ant.</b></td>';
    echo '<td align="center">' . $total_pizza_tel . '</td>';
    echo '<td align="center">' . $total_pizza_net . '</td>';
    echo '<td align="center">' . $total_pizza . '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td align="center">' . $total_geral . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';

//Buscando pizzas promo por tamanho
$sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
$res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);

$total_geral = 0;

while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
{
    $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "'  AND pp.promocional = 1 AND pp.fidelidade = 0 AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    $total_pizza_tel = $obj_quant_pizzas->total_tel;
    
    $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "'  AND pp.promocional = 1 AND pp.fidelidade = 0 AND p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    $total_pizza_net = $obj_quant_pizzas->total_net;
    
    $total_pizza = $total_pizza_tel + $total_pizza_net;
    $total_geral += $total_pizza;
    
    echo '<tr>';
    echo '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_tamanhos->tamanho) . ' - promocional</b></td>';
    echo '<td align="center">' . $total_pizza_tel . '</td>';
    echo '<td align="center">' . $total_pizza_net . '</td>';
    echo '<td align="center">' . $total_pizza . '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td align="center">' . $total_geral . '</td>';
echo '</tr>';

//Buscando pizzas promo por tamanho sem ant
$sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
$res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);

$total_geral = 0;

while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
{
    $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "'  AND pp.promocional = 1 AND pp.fidelidade = 0 AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido_sem_ant $sql_situacao_pedido";
    $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    $total_pizza_tel = $obj_quant_pizzas->total_tel;
    
    $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "'  AND pp.promocional = 1 AND pp.fidelidade = 0 AND p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_data_hora_pedido_sem_ant $sql_situacao_pedido";
    $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    $total_pizza_net = $obj_quant_pizzas->total_net;
    
    $total_pizza = $total_pizza_tel + $total_pizza_net;
    $total_geral += $total_pizza;
    
    echo '<tr>';
    echo '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_tamanhos->tamanho) . ' - promocional sem. ant.</b></td>';
    echo '<td align="center">' . $total_pizza_tel . '</td>';
    echo '<td align="center">' . $total_pizza_net . '</td>';
    echo '<td align="center">' . $total_pizza . '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td align="center">' . $total_geral . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';

// Frações
$sql_quant_fracoes_salgada = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'TEL' AND pi.tipo = 'Salgado' AND pp.promocional = 0 AND pp.fidelidade = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_fracoes_salgada = mysql_query($sql_quant_fracoes_salgada);
$obj_quant_fracoes_salgada = mysql_fetch_object($res_quant_fracoes_salgada);
$total_quant_fracoes_salgada_tel = $obj_quant_fracoes_salgada->total_tel;

$sql_quant_fracoes_salgada = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido IN ('NET','IFOOD') AND pi.tipo = 'Salgado' AND pp.promocional = 0 AND pp.fidelidade = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_fracoes_salgada = mysql_query($sql_quant_fracoes_salgada);
$obj_quant_fracoes_salgada = mysql_fetch_object($res_quant_fracoes_salgada);
$total_quant_fracoes_salgada_net = $obj_quant_fracoes_salgada->total_net;

echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>'.ucfirst(TIPO_PRODUTOS).' Salgadas</b></td>';
echo '<td align="center">' . $total_quant_fracoes_salgada_tel . '</td>';
echo '<td align="center">' . $total_quant_fracoes_salgada_net . '</td>';
echo '<td align="center">' . ($total_quant_fracoes_salgada_tel + $total_quant_fracoes_salgada_net) . '</td>';
echo '</tr>';


$sql_quant_fracoes_doce = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'TEL' AND pi.tipo = 'Doce' AND pp.promocional = 0 AND pp.fidelidade = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_fracoes_doce = mysql_query($sql_quant_fracoes_doce);
$obj_quant_fracoes_doce = mysql_fetch_object($res_quant_fracoes_doce);
$total_quant_fracoes_doce_tel = $obj_quant_fracoes_doce->total_tel;

$sql_quant_fracoes_doce = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido IN ('NET','IFOOD') AND pi.tipo = 'Doce' AND pp.promocional = 0 AND pp.fidelidade = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_fracoes_doce = mysql_query($sql_quant_fracoes_doce);
$obj_quant_fracoes_doce = mysql_fetch_object($res_quant_fracoes_doce);
$total_quant_fracoes_doce_net = $obj_quant_fracoes_doce->total_net;

echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>'.ucfirst(TIPO_PRODUTOS).' Doces</b></td>';
echo '<td align="center">' . $total_quant_fracoes_doce_tel . '</td>';
echo '<td align="center">' . $total_quant_fracoes_doce_net . '</td>';
echo '<td align="center">' . ($total_quant_fracoes_doce_tel + $total_quant_fracoes_doce_net) . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
    

// Quant. bordas vendidas
$sql_quant_bordas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 0 AND pb.fidelidade = 0 AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_bordas = mysql_query($sql_quant_bordas);
$obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
$total_borda_tel = $obj_quant_bordas->total_tel;

$sql_quant_bordas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 0 AND pb.fidelidade = 0 AND p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_bordas = mysql_query($sql_quant_bordas);
$obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
$total_borda_net = $obj_quant_bordas->total_net;

$total_borda = $total_borda_tel + $total_borda_net;

echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>Quant. Bordas Vendidas</b></td>';
echo '<td align="center">' . $total_borda_tel . '</td>';
echo '<td align="center">' . $total_borda_net . '</td>';
echo '<td align="center">' . $total_borda . '</td>';
echo '</tr>';


// Quant. bordas promo
$sql_quant_bordas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 1 AND pb.fidelidade = 0 AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_bordas = mysql_query($sql_quant_bordas);
$obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
$total_borda_tel = $obj_quant_bordas->total_tel;

$sql_quant_bordas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 1 AND pb.fidelidade = 0 AND p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_bordas = mysql_query($sql_quant_bordas);
$obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
$total_borda_net = $obj_quant_bordas->total_net;

$total_borda = $total_borda_tel + $total_borda_net;

echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>Quant. Bordas Promocionais</b></td>';
echo '<td align="center">' . $total_borda_tel . '</td>';
echo '<td align="center">' . $total_borda_net . '</td>';
echo '<td align="center">' . $total_borda . '</td>';
echo '</tr>';


// Quant. bordas fidelidade
$sql_quant_bordas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 0 AND pb.fidelidade = 1 AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_bordas = mysql_query($sql_quant_bordas);
$obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
$total_borda_tel = $obj_quant_bordas->total_tel;

$sql_quant_bordas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 0 AND pb.fidelidade = 0 AND p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_bordas = mysql_query($sql_quant_bordas);
$obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
$total_borda_net = $obj_quant_bordas->total_net;

$total_borda = $total_borda_tel + $total_borda_net;

echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>Quant. Bordas Fidelidade</b></td>';
echo '<td align="center">' . $total_borda_tel . '</td>';
echo '<td align="center">' . $total_borda_net . '</td>';
echo '<td align="center">' . $total_borda . '</td>';
echo '</tr>';
    

// Quant. adicionais (gergelim)
$sql_quant_adicionais = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_adicionais pa ON (p.cod_pedidos = pa.cod_pedidos) WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_adicionais = mysql_query($sql_quant_adicionais);
$obj_quant_adicionais = mysql_fetch_object($res_quant_adicionais);
$total_adicionais_tel = $obj_quant_adicionais->total_tel;

$sql_quant_adicionais = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_adicionais pa ON (p.cod_pedidos = pa.cod_pedidos) WHERE p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_quant_adicionais = mysql_query($sql_quant_adicionais);
$obj_quant_adicionais = mysql_fetch_object($res_quant_adicionais);
$total_adicionais_net = $obj_quant_adicionais->total_net;

$total_adicionais = $total_adicionais_tel + $total_adicionais_net;

echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>Quant. Gergelim</b></td>';
echo '<td align="center">' . $total_adicionais_tel . '</td>';
echo '<td align="center">' . $total_adicionais_net . '</td>';
echo '<td align="center">' . $total_adicionais . '</td>';
echo '</tr>';


// Quant. indredientes não padrão (adicionais)
$sql_ingred_adicionais = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) WHERE p.origem_pedido = 'TEL' AND pi.ingrediente_padrao = 0 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_ingred_adicionais = mysql_query($sql_ingred_adicionais);
$obj_ingred_adicionais = mysql_fetch_object($res_ingred_adicionais);
$total_ingred_adicionais_tel = $obj_ingred_adicionais->total_tel;

$sql_ingred_adicionais = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) WHERE p.origem_pedido IN ('NET','IFOOD') AND pi.ingrediente_padrao = 0 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_ingred_adicionais = mysql_query($sql_ingred_adicionais);
$obj_ingred_adicionais = mysql_fetch_object($res_ingred_adicionais);
$total_ingred_adicionais_net = $obj_ingred_adicionais->total_net;

$total_ingred_adicionais = $total_ingred_adicionais_tel + $total_ingred_adicionais_net;

echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>Quant. de Ingredientes Adicionais</b></td>';
echo '<td align="center">' . $total_ingred_adicionais_tel . '</td>';
echo '<td align="center">' . $total_ingred_adicionais_net . '</td>';
echo '<td align="center">' . $total_ingred_adicionais . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';

// Valor médio por pedido
$sql_buscar_media_valor = "SELECT AVG(p.valor_total) AS media_total FROM ipi_pedidos p WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_buscar_media_valor = mysql_query($sql_buscar_media_valor);
$obj_buscar_media_valor = mysql_fetch_object($res_buscar_media_valor);
$media_valor_tel = $obj_buscar_media_valor->media_total;

$sql_buscar_media_valor = "SELECT AVG(p.valor_total) AS media_total FROM ipi_pedidos p WHERE p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_buscar_media_valor = mysql_query($sql_buscar_media_valor);
$obj_buscar_media_valor = mysql_fetch_object($res_buscar_media_valor);
$media_valor_net = $obj_buscar_media_valor->media_total;

echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>Valor Médio por Pedido</b></td>';
echo '<td align="center">' . bd2moeda($media_valor_tel) . '</td>';
echo '<td align="center">' . bd2moeda($media_valor_net) . '</td>';
echo '<td align="center">' . bd2moeda(($media_valor_tel + $media_valor_net) / 2) . '</td>';
echo '</tr>';


// Média de pizzas por pedido
$sql_media_pizzas = "SELECT AVG(contagem) AS media FROM (SELECT (SELECT COUNT(*) FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos = p.cod_pedidos AND pp.promocional = 0 AND pp.fidelidade = 0) AS contagem FROM ipi_pedidos p WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido) AS t1";
$res_media_pizzas = mysql_query($sql_media_pizzas);
$obj_media_pizzas = mysql_fetch_object($res_media_pizzas);
$media_pizzas_tel = $obj_media_pizzas->media;

$sql_media_pizzas = "SELECT AVG(contagem) AS media FROM (SELECT (SELECT COUNT(*) FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos = p.cod_pedidos AND pp.promocional = 0 AND pp.fidelidade = 0) AS contagem FROM ipi_pedidos p WHERE p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido) AS t1";
$res_media_pizzas = mysql_query($sql_media_pizzas);
$obj_media_pizzas = mysql_fetch_object($res_media_pizzas);
$media_pizzas_net = $obj_media_pizzas->media;

echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>Média de '.ucfirst(TIPO_PRODUTOS).' Vendidas por Pedidos</b></td>';
echo '<td align="center">' . bd2moeda($media_pizzas_tel) . '</td>';
echo '<td align="center">' . bd2moeda($media_pizzas_net) . '</td>';
echo '<td align="center">' . bd2moeda(($media_pizzas_tel + $media_pizzas_net) / 2) . '</td>';
echo '</tr>';


echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';




// Número de Tickets
$total_pedidos_geral = 0;

$sql_num_tickets = "SELECT count(cod_pedidos) as contagem, sum(valor_total) as total, (sum(valor_total)/count(cod_pedidos)) as ticket_medio FROM ipi_pedidos p WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
$res_num_tickets = mysql_query($sql_num_tickets);
$obj_num_tickets = mysql_fetch_object($res_num_tickets);
$num_tickets_tel = $obj_num_tickets->contagem;
$valor_tickets_medio_tel = $obj_num_tickets->ticket_medio;
$total_pedidos_geral += $obj_num_tickets->total;

//echo "<br/><br/>".$sql_num_tickets;

$sql_num_tickets = "SELECT count(cod_pedidos) as contagem, sum(valor_total) as total, (sum(valor_total)/count(cod_pedidos)) as ticket_medio FROM ipi_pedidos p WHERE p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
$res_num_tickets = mysql_query($sql_num_tickets);
$obj_num_tickets = mysql_fetch_object($res_num_tickets);
$num_tickets_net = $obj_num_tickets->contagem;
$valor_tickets_medio_net = $obj_num_tickets->ticket_medio;
$total_pedidos_geral += $obj_num_tickets->total;

//echo "<br/><br/>".$sql_num_tickets;
echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>Número de Tickets</b></td>';
echo '<td align="center">' . $num_tickets_tel . '</td>';
echo '<td align="center">' . $num_tickets_net . '</td>';
echo '<td align="center">' . ( $num_tickets_tel + $num_tickets_net ) . '</td>';
echo '</tr>';
/*
$sql_tel = "SELECT count(cod_pedidos) as contagem, sum(valor) as total, (sum(valor)/count(cod_pedidos)) as ticket_medio FROM ipi_pedidos p WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";

$sql_net = "SELECT count(cod_pedidos) as contagem, sum(valor) as total, (sum(valor)/count(cod_pedidos)) as ticket_medio FROM ipi_pedidos p WHERE p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";*/

echo '<tr>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>Valor Médio dos Tickets</b></td>';
echo '<td align="center">' . bd2moeda($valor_tickets_medio_tel) . '</td>';
echo '<td align="center">' . bd2moeda($valor_tickets_medio_net) . '</td>';
echo '<td align="center">' . bd2moeda((($total_pedidos_geral)/($num_tickets_tel+$num_tickets_net))) . '</td>';
echo '</tr>';


echo '</table><br><br><br><br>';  


//Elias promocoes pedido
echo '<table border="1" cellpadding="0" cellspacing="0" width="500" align="center" style="margin: 0 auto;">';
echo '<tr><td colspan="5" align="center" style="background-color: #e5e5e5;"><b>Promoções</b></td></tr>';

echo '<tr>';
echo '<td style="background-color: #e5e5e5;">&nbsp;</td>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>TEL</b></td>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>NET</b></td>';
echo '<td style="background-color: #e5e5e5;" align="center"><b>TOTAL</b></td>';
echo '</tr>';

$sql_buscar_promocoes = 'SELECT * FROM ipi_motivo_promocoes';
$res_buscar_promocoes = mysql_query($sql_buscar_promocoes);

while($obj_buscar_promocoes = mysql_fetch_object($res_buscar_promocoes))
{
    $total_promocoes_tel = 0;
    $total_promocoes_net = 0;
    
    $sql_promocoes = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_motivo_promocoes mp ON(pp.cod_motivo_promocoes=mp.cod_motivo_promocoes) WHERE p.origem_pedido = 'TEL' AND mp.cod_motivo_promocoes=".$obj_buscar_promocoes->cod_motivo_promocoes." $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    $res_promocoes = mysql_query($sql_promocoes);
    $obj_promocoes = mysql_fetch_object($res_promocoes);
    $total_promocoes_tel += $obj_promocoes->total_tel;
    
    $sql_promocoes_bordas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON(p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_motivo_promocoes mp ON(pb.cod_motivo_promocoes=mp.cod_motivo_promocoes) WHERE p.origem_pedido = 'TEL' AND mp.cod_motivo_promocoes=".$obj_buscar_promocoes->cod_motivo_promocoes." $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    $res_promocoes_bordas = mysql_query($sql_promocoes_bordas);
    $obj_promocoes_bordas = mysql_fetch_object($res_promocoes_bordas);
    $total_promocoes_tel += $obj_promocoes_borda->total_tel;
    
    $sql_promocoes_bedidas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_bebidas pb ON(p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_motivo_promocoes mp ON(pb.cod_motivo_promocoes=mp.cod_motivo_promocoes) WHERE p.origem_pedido = 'TEL' AND mp.cod_motivo_promocoes=".$obj_buscar_promocoes->cod_motivo_promocoes." $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    $res_promocoes_bedidas = mysql_query($sql_promocoes_bedidas);
    $obj_promocoes_bedidas = mysql_fetch_object($res_promocoes_bedidas);
    
    $total_promocoes_tel += $obj_promocoes_bedidas->total_tel;
    
    // ---------------------------------- //
    // FIXME: Thiago, Identifiquei um erro nas consultas abaixo, não deu tempo de corrigir, nas pizzas promocionais via internet, o modelo não tem relacionamento com motivos promocionais, as query abaixo sempre vão zerar. 
    $sql_promocoes = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_motivo_promocoes mp ON(pp.cod_motivo_promocoes=mp.cod_motivo_promocoes) WHERE p.origem_pedido IN ('NET','IFOOD') AND mp.cod_motivo_promocoes=".$obj_buscar_promocoes->cod_motivo_promocoes." $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    $res_promocoes = mysql_query($sql_promocoes);
    $obj_promocoes = mysql_fetch_object($res_promocoes);
    $total_promocoes_net += $obj_promocoes->total_net;
    
    $sql_promocoes = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON(p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_motivo_promocoes mp ON(pb.cod_motivo_promocoes=mp.cod_motivo_promocoes) WHERE p.origem_pedido IN ('NET','IFOOD') AND mp.cod_motivo_promocoes=".$obj_buscar_promocoes->cod_motivo_promocoes." $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    $res_promocoes = mysql_query($sql_promocoes);
    $obj_promocoes = mysql_fetch_object($res_promocoes);
    $total_promocoes_net += $obj_promocoes->total_net;
    
    $sql_promocoes = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bebidas pb ON(p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_motivo_promocoes mp ON(pb.cod_motivo_promocoes=mp.cod_motivo_promocoes) WHERE p.origem_pedido IN ('NET','IFOOD') AND mp.cod_motivo_promocoes=".$obj_buscar_promocoes->cod_motivo_promocoes." $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    $res_promocoes = mysql_query($sql_promocoes);
    $obj_promocoes = mysql_fetch_object($res_promocoes);
    $total_promocoes_net += $obj_promocoes->total_net;
    
    $total_promocoes = ($total_promocoes_tel+$total_promocoes_net);
    
    echo '<tr>';
    echo '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_promocoes->motivo_promocao) . '</b></td>';
    echo '<td align="center">' . $total_promocoes_tel . '</td>';
    echo '<td align="center">' . $total_promocoes_net . '</td>';
    echo '<td align="center">' . $total_promocoes . '</td>';
    echo '</tr>';
    
}

echo '</table><br><br><br><br>';


echo '<table border="1" cellpadding="0" cellspacing="0" width="500" align="center" style="margin: 0 auto;">';
echo '<tr><td colspan="2" align="center" style="background-color: #e5e5e5;"><b>Outros Dados</b></td></tr>';

$sql_buscar_cupons = "SELECT COUNT(*) AS total_cupons FROM ipi_pedidos p INNER JOIN ipi_pedidos_ipi_cupons pc ON (p.cod_pedidos = pc.cod_pedidos) INNER JOIN ipi_cupons c ON (pc.cod_cupons = c.cod_cupons) WHERE c.produto = 'PIZZA' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_buscar_cupons = mysql_query($sql_buscar_cupons);
$obj_buscar_cupons = mysql_fetch_object($res_buscar_cupons);


echo '<tr>';
echo '<td align="center" style="background-color: #e5e5e5;"><b>Cupons de '.ucfirst(TIPO_PRODUTO).'</b></td>';
echo '<td align="center">' . $obj_buscar_cupons->total_cupons . '</td>';
echo '</tr>';

$sql_buscar_cupons = "SELECT COUNT(*) AS total_cupons FROM ipi_pedidos p INNER JOIN ipi_pedidos_ipi_cupons pc ON (p.cod_pedidos = pc.cod_pedidos) INNER JOIN ipi_cupons c ON (pc.cod_cupons = c.cod_cupons) WHERE c.produto = 'BORDA' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_buscar_cupons = mysql_query($sql_buscar_cupons);
$obj_buscar_cupons = mysql_fetch_object($res_buscar_cupons);

echo '<tr>';
echo '<td align="center" style="background-color: #e5e5e5;"><b>Cupons de Borda</b></td>';
echo '<td align="center">' . $obj_buscar_cupons->total_cupons . '</td>';
echo '</tr>';

$sql_buscar_pontos_total = "SELECT SUM(p.pontos_fidelidade_total) AS pontos_total FROM ipi_pedidos p WHERE 1=1 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
$res_buscar_pontos_total = mysql_query($sql_buscar_pontos_total);
$obj_buscar_pontos_total = mysql_fetch_object($res_buscar_pontos_total);

echo '<tr>';
echo '<td align="center" style="background-color: #e5e5e5;"><b>Pontos de Fidelidade Utilizados</b></td>';
echo '<td align="center">' . $obj_buscar_pontos_total->pontos_total . '</td>';
echo '</tr>';

echo '</table><br><br><br><br>';


// FIXME cod_enquete forçado 1 
$cod_enquetes = 1;

// FIXME cod_enquete_respostas forçado para muito satisfeito(a) e satisfeito(a)
$arr_cod_respostas_metricas = array(8,9,12,13,16,17);

echo '<table border="1" cellpadding="0" cellspacing="0" width="500" align="center" style="margin: 0 auto;">';
echo '<tr><td colspan="2" align="center" style="background-color: #e5e5e5;"><b>Enquete</b></td></tr>';

$sql_buscar_enquete_perguntas = "SELECT * FROM ipi_enquete_perguntas WHERE cod_enquetes = '$cod_enquetes' and pergunta_pessoal = '0' ORDER BY pergunta";
$res_buscar_enquete_perguntas = mysql_query($sql_buscar_enquete_perguntas);

$total_repostas = 0;
$total_metrica = 0;

while($obj_buscar_enquete_perguntas = mysql_fetch_object($res_buscar_enquete_perguntas))
{

    //Alterado novamento, por os valores deste, não batem com os valores da tela de responder enquete.
    /*
    //Mudado para o Sr. Mendes ver a média móvel
    //$sql_buscar_enquete_respostas = "SELECT COUNT(*) AS total FROM ipi_enquete_respostas er INNER JOIN ipi_clientes_ipi_enquete_respostas ere ON (er.cod_enquete_respostas = ere.cod_enquete_respostas) INNER JOIN ipi_pedidos cp ON (ere.cod_pedidos = cp.cod_pedidos) WHERE er.cod_enquete_perguntas = '" . $obj_buscar_enquete_perguntas->cod_enquete_perguntas . "' AND er.cod_enquete_respostas IN ($cod_enquete_respostas) AND cp.data_hora_pedido BETWEEN DATE_SUB('$data_final', INTERVAL 7 DAY) AND '$data_final'";

    $sql_buscar_enquete_respostas = "SELECT COUNT(*) AS total FROM ipi_enquete_respostas er INNER JOIN ipi_clientes_ipi_enquete_respostas ere ON (er.cod_enquete_respostas = ere.cod_enquete_respostas) INNER JOIN ipi_pedidos p on p.cod_pedidos = ere.cod_pedidos WHERE er.cod_enquete_perguntas = '" . $obj_buscar_enquete_perguntas->cod_enquete_perguntas . "' AND er.cod_enquete_respostas IN ($cod_enquete_respostas) $sql_buscar_pizzarias AND ere.data_hora_resposta BETWEEN DATE_SUB('$data_final', INTERVAL 7 DAY) AND '$data_final'";
    //echo "<br/>".$sql_buscar_enquete_respostas."<br/>";
    $res_buscar_enquete_respostas = mysql_query($sql_buscar_enquete_respostas);
    $obj_buscar_enquete_respostas = mysql_fetch_object($res_buscar_enquete_respostas);
    $parcela = $obj_buscar_enquete_respostas->total;
    
    //Mudado para o Sr. Mendes ver a média móvel
    //$sql_buscar_enquete_respostas = "SELECT COUNT(*) AS total FROM ipi_enquete_respostas er INNER JOIN ipi_clientes_ipi_enquete_respostas ere ON (er.cod_enquete_respostas = ere.cod_enquete_respostas) INNER JOIN ipi_pedidos cp ON (ere.cod_pedidos = cp.cod_pedidos) WHERE er.cod_enquete_perguntas = '" . $obj_buscar_enquete_perguntas->cod_enquete_perguntas . "' AND cp.data_hora_pedido BETWEEN DATE_SUB('$data_final', INTERVAL 7 DAY) AND '$data_final'";

    $sql_buscar_enquete_respostas = "SELECT COUNT(*) AS total FROM ipi_enquete_respostas er INNER JOIN ipi_clientes_ipi_enquete_respostas ere ON (er.cod_enquete_respostas = ere.cod_enquete_respostas) INNER JOIN ipi_pedidos p on p.cod_pedidos = ere.cod_pedidos WHERE er.cod_enquete_perguntas = '" . $obj_buscar_enquete_perguntas->cod_enquete_perguntas . "' $sql_buscar_pizzarias AND ere.data_hora_resposta BETWEEN DATE_SUB('$data_final', INTERVAL 7 DAY) AND '$data_final'";
    
    $res_buscar_enquete_respostas = mysql_query($sql_buscar_enquete_respostas);
    $obj_buscar_enquete_respostas = mysql_fetch_object($res_buscar_enquete_respostas);
    $total = $obj_buscar_enquete_respostas->total;
    */
    
    $sql_buscar_respostas = "SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas = '$obj_buscar_enquete_perguntas->cod_enquete_perguntas'";
    $res_buscar_respostas = mysql_query($sql_buscar_respostas);

    $arr_cod_respostas_metricas = array(8,9,12,13,16,17);

    $total_repostas = 0;
    $total_metrica = 0;
    $a = 0;
    $b = 0;


    while($obj_buscar_respostas = mysql_fetch_object($res_buscar_respostas))
    {
        $sql_buscar_quantidade_total = "SELECT COUNT(*) AS total FROM ipi_clientes_ipi_enquete_respostas cer LEFT JOIN ipi_pedidos p ON (cer.cod_pedidos=p.cod_pedidos) WHERE cer.cod_enquete_respostas = '" . $obj_buscar_respostas->cod_enquete_respostas . "' ";

        if ($cod_pizzarias)
        {
            $sql_buscar_quantidade_total .= " AND p.cod_pizzarias='".$cod_pizzarias."'";
        }
        else
        {
            $sql_buscar_quantidade_total .= " AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
        }
        
        if (($data_inicial) && ($data_final))
        {
            $data_inicial_sql = ($data_inicial) . ' 00:00:00';
            $data_final_sql = ($data_final) . ' 23:59:59';
            
            $sql_buscar_quantidade_total .= " AND cer.data_hora_resposta >= '$data_inicial_sql' AND cer.data_hora_resposta <= '$data_final_sql'";
        }
        //echo $sql_buscar_quantidade_total;
        $obj_buscar_quantidade_total = executaBuscaSimples($sql_buscar_quantidade_total, $conexao);
        $total_repostas += $obj_buscar_quantidade_total->total;
        
        if(in_array($obj_buscar_respostas->cod_enquete_respostas, $arr_cod_respostas_metricas) !== false)
        {
            $total_metrica += $obj_buscar_quantidade_total->total;    
        }
    }

    /*echo '<br><br>';
        echo $total_metrica.' '.$total_repostas;
    echo '<br><br>';
        echo round(($total_metrica * 100) / $total_repostas);
    echo '<br><br>';*/


    echo '<tr>';
    echo '<td align="center" style="background-color: #e5e5e5;"><b>' . bd2texto($obj_buscar_enquete_perguntas->pergunta) . '</b></td>';
    if ($total_metrica>0)
        echo '<td align="center">' . round(($total_metrica * 100) / $total_repostas) . '%</td>';
    else
        echo '<td align="center">0%</td>';
    echo '</tr>';
}

echo '</table>';



desconectabd($conexao);

?>

<?php endif; ?>

<?php rodape(); ?>
