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

cabecalho('Relatório de Média de Pizzas por Pedido');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 50;

switch ($acao)
{
    case 'detalhes':
        $codigo = validaVarPost($chave_primaria);
        
        $con = conectabd();
        
        $objBuscaDetalhamento = executaBuscaSimples("SELECT * FROM ipi_pedidos WHERE cod_pedidos = $codigo", $con);
        
        echo sprintf('<h1 align="center"><b>Pedido nº %08d</b></h1>', $codigo);
        
        echo '<br><br>';
        
        echo '<p><b>Cliente</b></p>';
        echo '<hr noshade="noshade" color="#1c4b93"/>';
        echo '<br>';
        
        $objBuscaCliente = executaBuscaSimples("SELECT * FROM ipi_clientes WHERE cod_clientes = " . $objBuscaDetalhamento->cod_clientes, $con);
        
        echo '<table>';
        
        echo '<tr>';
        echo '<td><b>Nome:</b></td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td><b>CPF:</b></td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td><b>Celular:</b></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . bd2texto($objBuscaCliente->nome) . '</td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td>' . $objBuscaCliente->cpf . '</td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td>' . $objBuscaCliente->celular . '</td>';
        echo '</tr>';
        
        echo '</table>';
        
        echo '<br><br>';
        
        //////////////////////////////
        

        echo '<p><b>Entrega</b></p>';
        echo '<hr noshade="noshade" color="#1c4b93"/>';
        echo '<br>';
        
        echo '<table>';
        
        echo '<tr>';
        echo '<td><b>Endereço:</b></td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td><b>Número:</b></td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td><b>Complemento:</b></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . bd2texto($objBuscaDetalhamento->endereco) . '</td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td>' . bd2texto($objBuscaDetalhamento->numero) . '</td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td>' . bd2texto($objBuscaDetalhamento->complemento) . '</td>';
        echo '</tr>';
        
        echo '<tr><td colspan="5">&nbsp;</td></tr>';
        
        echo '<tr>';
        echo '<td><b>Bairro:</b></td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td><b>Cidade:</b></td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td><b>Estado:</b></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . bd2texto($objBuscaDetalhamento->bairro) . '</td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td>' . bd2texto($objBuscaDetalhamento->cidade) . '</td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td>' . $objBuscaDetalhamento->estado . '</td>';
        echo '</tr>';
        
        echo '<tr><td colspan="5">&nbsp;</td></tr>';
        
        echo '<tr>';
        echo '<td colspan="5"><b>CEP:</b></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . $objBuscaDetalhamento->cep . '</td>';
        echo '</tr>';
        
        echo '<tr><td colspan="5">&nbsp;</td></tr>';
        
        echo '<tr>';
        echo '<td colspan="5"><b>Destino:</b></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . bd2texto($objBuscaDetalhamento->tipo_entrega) . '</td>';
        echo '</tr>';
        
        echo '<tr><td colspan="5">&nbsp;</td></tr>';
        
        $objBuscaEntregador = executaBuscaSimples("SELECT * FROM ipi_entregadores WHERE cod_entregadores = " . $objBuscaDetalhamento->cod_entregadores, $con);
        
        echo '<tr>';
        echo '<td colspan="5"><b>Entregador:</b></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . bd2texto($objBuscaEntregador->nome) . '</td>';
        echo '</tr>';
        
        echo '<tr><td colspan="5">&nbsp;</td></tr>';
        
        echo '<tr>';
        echo '<td colspan="5"><b>Situação:</b></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . bd2texto($objBuscaDetalhamento->situacao) . '</td>';
        echo '</tr>';
        
        echo '<tr><td colspan="5">&nbsp;</td></tr>';
        
        echo '<tr>';
        echo '<td colspan="5"><b>Forma de Pagamento:</b></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . bd2texto($objBuscaDetalhamento->forma_pg) . '</td>';
        echo '</tr>';
        
        echo '<tr><td colspan="5">&nbsp;</td></tr>';
        
        $objBuscaTroco = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = " . $objBuscaDetalhamento->cod_pedidos . " AND chave = 'TROCO'", $con);
        
        echo '<tr>';
        echo '<td colspan="5"><b>Troco:</b></td>';
        echo '</tr>';
        echo '<tr>';
        
        if ($objBuscaTroco->conteudo != '')
            echo '<td>' . bd2texto($objBuscaTroco->conteudo) . '</td>';
        else
            echo '<td>Não</td>';
        
        echo '</tr>';
        
        echo '<tr><td colspan="5">&nbsp;</td></tr>';
        
        echo '<tr>';
        echo '<td colspan="5"><b>Valor do Pedido:</b></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . bd2moeda($objBuscaDetalhamento->valor_total) . '</td>';
        echo '</tr>';
        
        echo '</table>';
        
        echo '<br><br>';
        
        //////////////////////////////
        

        $SqlBuscaPedidosPizzas = 'SELECT * FROM ipi_pedidos_pizzas p INNER JOIN ipi_tamanhos t ON (p.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_tipo_massa m ON (p.cod_tipo_massa = m.cod_tipo_massa) WHERE p.cod_pedidos = ' . $objBuscaDetalhamento->cod_pedidos . ' ORDER BY cod_pedidos_pizzas';
        $resBuscaPedidosPizzas = mysql_query($SqlBuscaPedidosPizzas);
        
        $num_pizza = 1;
        while ($objBuscaPedidosPizzas = mysql_fetch_object($resBuscaPedidosPizzas))
        {
            echo '<p><b>Pizza ' . $num_pizza . '</b></p>';
            echo '<hr noshade="noshade" color="#1c4b93"/>';
            echo '<br>';
            
            echo '<table>';
            
            echo '<tr>';
            echo '<td><b>Tamanho da Pizza:</b></td>';
            echo '<td width="50">&nbsp;</td>';
            echo '<td><b>Quantidade de Sabores:</b></td>';
            echo '<td width="50">&nbsp;</td>';
            echo '<td><b>Recheio da Borda:</b></td>';
            echo '</tr>';
            
            echo '<tr>';
            echo '<td>' . $objBuscaPedidosPizzas->tamanho . '</td>';
            echo '<td width="50">&nbsp;</td>';
            echo '<td>' . $objBuscaPedidosPizzas->quant_fracao . ' ' . $valorQuantFracao . '</td>';
            echo '<td width="50">&nbsp;</td>';
            
            $objBuscaPedidosBorda = executaBuscaSimples("SELECT * FROM ipi_pedidos_bordas p INNER JOIN ipi_bordas b ON (p.cod_bordas = b.cod_bordas) WHERE p.cod_pedidos = " . $objBuscaPedidosPizzas->cod_pedidos . " AND p.cod_pedidos_pizzas = " . $objBuscaPedidosPizzas->cod_pedidos_pizzas, $con);
            
            if ($objBuscaPedidosBorda->borda)
            {
                if ($objBuscaPedidosBorda->promocional)
                    $valorPedidosBorda = 'GRÁTIS';
                else if ($objBuscaPedidosBorda->fidelidade)
                    $valorPedidosBorda = 'FIDELIDADE';
                else
                    $valorPedidosBorda = 'R$' . bd2moeda($objBuscaPedidosBorda->preco);
                
                echo '<td>' . $objBuscaPedidosBorda->borda . ' (' . $valorPedidosBorda . ')</td>';
            }
            else
            {
                echo '<td>Não</td>';
            }
            echo '</tr>';
            
            echo '<tr><td colspan="5">&nbsp;</td></tr>';
            
            echo '<tr>';
            echo '<td><b>Borda salpicada com Gergelim:</b></td>';
            echo '<td width="50">&nbsp;</td>';
            echo '<td colspan="3"><b>Tipo da Massa:</b></td>';
            echo '</tr>';
            
            echo '<tr>';
            
            $objBuscaPedidosAdicional = executaBuscaSimples("SELECT * FROM ipi_pedidos_adicionais p INNER JOIN ipi_adicionais a ON (p.cod_adicionais = a.cod_adicionais) WHERE p.cod_pedidos = " . $objBuscaPedidosPizzas->cod_pedidos . " AND p.cod_pedidos_pizzas = " . $objBuscaPedidosPizzas->cod_pedidos_pizzas, $con);
            
            if ($objBuscaPedidosAdicional->adicional)
            {
                if ($objBuscaPedidosAdicional->promocional)
                    $valorPedidosAdicional = 'GRÁTIS';
                else if ($objBuscaPedidosAdicional->fidelidade)
                    $valorPedidosAdicional = 'FIDELIDADE';
                else
                    $valorPedidosAdicional = 'R$' . bd2moeda($objBuscaPedidosAdicional->preco);
                
                echo '<td>' . $objBuscaPedidosAdicional->adicional . ' (' . $valorPedidosAdicional . ')</td>';
            }
            else
            {
                echo '<td>Não</td>';
            }
            
            echo '<td width="50">&nbsp;</td>';
            
            echo '<td colspan="3">' . $objBuscaPedidosPizzas->tipo_massa;
            
            if ($objBuscaPedidosPizzas->preco_massa > 0)
            {
                echo '&nbsp;(' . bd2moeda($objBuscaPedidosPizzas->preco_massa) . ')';
            }
            
            echo '</td>';
            
            echo '</tr>';
            echo '</table>';
            
            $SqlBuscaPedidosFracoes = "SELECT * FROM ipi_pedidos_fracoes fr INNER JOIN ipi_pizzas p ON (fr.cod_pizzas = p.cod_pizzas) WHERE fr.cod_pedidos = " . $objBuscaPedidosPizzas->cod_pedidos . " AND fr.cod_pedidos_pizzas = " . $objBuscaPedidosPizzas->cod_pedidos_pizzas . " ORDER BY fracao";
            $resBuscaPedidosFracoes = mysql_query($SqlBuscaPedidosFracoes);
            
            while ($objBuscaPedidosFracoes = mysql_fetch_object($resBuscaPedidosFracoes))
            {
                echo '<br><br><b class="laranja">' . $objBuscaPedidosFracoes->fracao . 'º sabor:</b> <b>' . $objBuscaPedidosFracoes->pizza . '</b>';
                
                echo '<br><br><b>Ingredientes Retirados:</b>';
                
                // Consulta que retorna todos os ingredientes da pizza
                //$SqlBuscaPedidosIngredientes = "SELECT * FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_ingredientes ig ON (pi.cod_ingredientes = ig.cod_ingredientes) WHERE pi.ingrediente_padrao = 1 AND pi.cod_pedidos_pizzas = ".$objBuscaPedidosFracoes->cod_pedidos_pizzas." AND pi.cod_pedidos = ".$objBuscaPedidosFracoes->cod_pedidos." AND pi.cod_pedidos_fracoes = ".$objBuscaPedidosFracoes->cod_pedidos_fracoes.' ORDER BY ingrediente';
                

                // Consulta que retorna todos os ingredientes retirados
                $SqlBuscaPedidosIngredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = " . $objBuscaDetalhamento->cod_pedidos . " AND pi.cod_pedidos_pizzas = " . $objBuscaPedidosPizzas->cod_pedidos_pizzas . " AND pi.cod_pedidos_fracoes = " . $objBuscaPedidosFracoes->cod_pedidos_fracoes . " AND pi.ingrediente_padrao = 1) AND p.cod_pizzas = " . $objBuscaPedidosFracoes->cod_pizzas . ' ORDER BY ingrediente';
                $resBuscaPedidosIngredientes = mysql_query($SqlBuscaPedidosIngredientes);
                
                echo '<ol style="margin-bottom: 10px; margin-top: 10px; margin-left: 40px;">';
                
                while ($objBuscaPedidosIngredientes = mysql_fetch_object($resBuscaPedidosIngredientes))
                {
                    echo '<li>' . $objBuscaPedidosIngredientes->ingrediente . '</li>';
                }
                
                echo '</ol>';
                
                echo '<b>Ingredientes Adicionados:</b>';
                
                $SqlBuscaPedidosExtra = "SELECT * FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_ingredientes ig ON (pi.cod_ingredientes = ig.cod_ingredientes) WHERE pi.ingrediente_padrao = 0 AND pi.cod_pedidos_pizzas = " . $objBuscaPedidosFracoes->cod_pedidos_pizzas . " AND pi.cod_pedidos = " . $objBuscaPedidosFracoes->cod_pedidos . " AND pi.cod_pedidos_fracoes = " . $objBuscaPedidosFracoes->cod_pedidos_fracoes . ' ORDER BY ingrediente';
                $resBuscaPedidosExtra = mysql_query($SqlBuscaPedidosExtra);
                
                echo '<ol style="margin-bottom: 10px; margin-top: 10px; margin-left: 40px;">';
                
                while ($objBuscaPedidosExtra = mysql_fetch_object($resBuscaPedidosExtra))
                {
                    echo '<li>' . $objBuscaPedidosExtra->ingrediente . '</li>';
                }
                
                echo '</ol>';
            }
            
            $num_pizza++;
        }
        
        echo '<br><br>';
        
        /////////////////////////
        

        $SqlBuscaPedidosBebidas = "SELECT *, p.preco AS pedidos_preco FROM ipi_pedidos_bebidas p INNER JOIN ipi_bebidas_ipi_conteudos bc ON (p.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) WHERE cod_pedidos = " . $objBuscaDetalhamento->cod_pedidos;
        $resBuscaPedidosBebidas = mysql_query($SqlBuscaPedidosBebidas);
        
        echo '<p><b>Bebida</b></p>';
        echo '<hr noshade="noshade" color="#1c4b93"/>';
        echo '<br>';
        
        echo '<table>';
        echo '<tr>';
        echo '<td><b>Quantidade:</b></td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td><b>Bebida:</b></td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td><b>Conteúdo:</b></td>';
        echo '</tr>';
        
        while ($objBuscaPedidosBebidas = mysql_fetch_object($resBuscaPedidosBebidas))
        {
            echo '<tr>';
            echo '<td>' . $objBuscaPedidosBebidas->quantidade . '</td>';
            echo '<td width="50">&nbsp;</td>';
            echo '<td>' . $objBuscaPedidosBebidas->bebida . '</td>';
            echo '<td width="50">&nbsp;</td>';
            echo '<td>' . $objBuscaPedidosBebidas->conteudo . '</td>';
            echo '</tr>';
            
            echo '<tr><td colspan="7"></td></tr>';
        }
        
        echo '</table>';
        
        echo '<br><br><h3><a href="ipi_rel_historico_pedidos.php">&laquo; Voltar</a></h3><br><br>';
        
        desconectabd($con);
        break;
}

?>

<? if ($acao != 'detalhes'): ?>

<script language="javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>

<script>

function editar(cod) {
    var form = new Element('form', 
    {
        'action': '<? echo $_SERVER['PHP_SELF']?>',
        'method': 'post'
    });
    
    var input1 = new Element('input', 
    {
        'type': 'hidden',
        'name': '<? echo $chave_primaria ?>',
        'value': cod
    });
    
    var input2 = new Element('input', 
    {
        'type': 'hidden',
        'name': 'acao',
        'value': 'detalhes'
    });
    
    input1.inject(form);
    input2.inject(form);
    
    $(document.body).adopt(form);
    
    form.submit();
}

function atualizar_grafico_geral()
{
    var ano = document.getElementById('ano').value;
    var cod_pizzarias = document.getElementById('cod_pizzarias').value;
    var cod_tamanhos = document.getElementById('cod_tamanhos').value;
    var origem = document.getElementById('origem').value;

    var grafico_geral = new FusionCharts('../lib/swf/fusioncharts/mscolumn3d.swf', 
                                         'grafico', 900, 400, 0, 0, 'ffffff', 0);

    grafico_geral.setDataURL('ipi_rel_ger_media_pedidos_pizza_ajax.php?param=<? echo date('dmYHis'); ?>,1,' + ano + ',' + cod_pizzarias + ',' + cod_tamanhos + ',' + origem);
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
$cod_tamanhos = validaVarPost('cod_tamanhos');
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
        <td class="tdbr"><select name="cod_pizzarias" id="cod_pizzarias"
            style="width: 200px;">
            <option value="0">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
            <?
            $con = conectabd();
            
	        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY p.nome";
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
      </select></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl" align="right"><label for="cod_tamanhos">Tamanho:</label></td>
        <td>&nbsp;</td>
        <td class="tdbr"><select name="cod_tamanhos" id="cod_tamanhos"
            style="width: 200px;">
            <option value="0">Todas os Tamanhos</option>
            <?
            $con = conectabd();
            
            $sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
            $res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);
            
            while ($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
            {
                echo '<option value="' . $obj_buscar_tamanhos->cod_tamanhos . '" ';
                
                if ($obj_buscar_tamanhos->cod_tamanhos == $cod_tamanhos)
                    echo 'selected';
                
                echo '>' . bd2texto($obj_buscar_tamanhos->tamanho) . '</option>';
            }
            
            desconectabd($con);
            ?>
      </select></td>
    </tr>

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

    <tr>
        <td align="right" class="tdbl tdbb tdbr" colspan="3"><input
            class="botaoAzul" type="submit" value="Buscar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="buscar"></form>

<br><br>

<table align="center" style="margin: 0px auto;" cellpadding="0" cellspacing="0">
    <tr>
        <td width="900"><div id="grafico_geral"></div></td>
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
