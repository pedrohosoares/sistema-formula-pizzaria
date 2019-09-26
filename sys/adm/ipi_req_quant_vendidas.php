<?php
// Função que Calcula as quantidades...
function imprime_quantidade_vendidas ($data_inicial, $data_final, $cod_pizzarias, $cod_clientes, $con, $origem_pedido = 'NET',$cod_motivo_promocoes)
{
    
    $sql_buscar_pizzarias = " AND cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";

    if ($cod_pizzarias > 0)
    {
        $sql_buscar_pizzarias .= " AND p.cod_pizzarias = $cod_pizzarias";
    }
    else
    {
        $sql_buscar_pizzarias .= '';
    }
    
    if ($cod_clientes > 0)
    {
        $sql_buscar_clientes = " AND p.cod_clientes = $cod_clientes";
    }
    else
    {
        $sql_buscar_clientes = '';
    }
    
    $sql_data_hora_pedido = " AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' ";
    $sql_situacao_pedido = " AND p.situacao NOT IN ('CANCELADO') ";
    if ($cod_motivo_promocoes != 'TODOS' AND !empty($cod_motivo_promocoes))
    {
        $sql_motivo_promocao = " AND pp.cod_motivo_promocoes = '$cod_motivo_promocoes'";
        $sql_motivo_promocao_borda = " AND pb.cod_motivo_promocoes = '$cod_motivo_promocoes'";
    }
    
    ?>
    
    <table class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
        <tr>
            <td>&nbsp;</td>
            <td style="background-color: #e5e5e5;" align="center"><b>TEL</b></td>
            <td style="background-color: #e5e5e5;" align="center"><b>NET</b></td>
            <td style="background-color: #e5e5e5;" align="center"><b>TOTAL</b></td>
        </tr>
        
        <tr>
            <?
            $sql_tamanhos = "SELECT cod_tamanhos, tamanho FROM ipi_tamanhos ORDER BY cod_tamanhos DESC";
            $res_tamanhos = mysql_query($sql_tamanhos);
            // echo $sql_tamanhos;
            while ($obj_tamanhos = mysql_fetch_object($res_tamanhos)) 
            {
                $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = ".$obj_tamanhos->cod_tamanhos." AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            //echo $sql_quant_pizzas;
            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
            
            $total_pizza_3_tel = $obj_quant_pizzas->total_tel;

            
            $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = ".$obj_tamanhos->cod_tamanhos." AND p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
            
            $total_pizza_3_net = $obj_quant_pizzas->total_net;
            
            
            $total_pizza_3 = $total_pizza_3_tel + $total_pizza_3_net;
?>
                        <td style="background-color: #e5e5e5;" align="center"><b><? echo $obj_tamanhos->tamanho?></b></td>
            
            <td align="center"><? echo $total_pizza_3_tel ?></td>
            <td align="center"><? echo $total_pizza_3_net ?></td>
            <td align="center"><? echo $total_pizza_3 ?></td>
        </tr>
        <?
            }
            

?>
        

        

        
        <tr>
        	<?
            $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido = 'TEL' AND pp.fidelidade = 0 AND pp.promocional = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
            
            $total_pizza_tel_vend = $obj_quant_pizzas->total_tel;
            
            
            $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido IN ('NET','IFOOD') AND pp.fidelidade = 0 AND pp.promocional = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
            
            $total_pizza_net_vend = $obj_quant_pizzas->total_net;
            ?>
        
            <td style="background-color: #e5e5e5;" align="center"><b>Total Vendidas</b></td>
            
            <td align="center"><? echo $total_pizza_tel_vend ?></td>
            <td align="center"><? echo $total_pizza_net_vend ?></td>
            <td align="center"><? echo ($total_pizza_tel_vend + $total_pizza_net_vend) ?></td>
        </tr>
        
        <tr>
        	<?
            $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido = 'TEL' AND pp.fidelidade = 0 AND pp.promocional = 1 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
            
            $total_pizza_tel_promo = $obj_quant_pizzas->total_tel;
            
            
            $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido IN ('NET','IFOOD') AND pp.fidelidade = 0 AND pp.promocional = 1 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
            
            $total_pizza_net_promo = $obj_quant_pizzas->total_net;
            ?>
        
            <td style="background-color: #e5e5e5;" align="center"><b>Total Promocionais</b></td>
            
            <td align="center"><? echo $total_pizza_tel_promo ?></td>
            <td align="center"><? echo $total_pizza_net_promo ?></td>
            <td align="center"><? echo ($total_pizza_tel_promo + $total_pizza_net_promo) ?></td>
        </tr>
        
        <tr>
        	<?
            $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido = 'TEL' AND pp.fidelidade = 1 AND pp.promocional = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
            
            $total_pizza_tel_fidel = $obj_quant_pizzas->total_tel;
            
            
            $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido IN ('NET','IFOOD') AND pp.fidelidade = 1 AND pp.promocional = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
            
            $total_pizza_net_fidel = $obj_quant_pizzas->total_net;
            ?>
        
            <td style="background-color: #e5e5e5;" align="center"><b>Total Fidelidades</b></td>
            
            <td align="center"><? echo $total_pizza_tel_fidel ?></td>
            <td align="center"><? echo $total_pizza_net_fidel ?></td>
            <td align="center"><? echo ($total_pizza_tel_fidel + $total_pizza_net_fidel) ?></td>
        </tr>
        
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        
		<tr>
        	<?
            $sql_quant_fracoes_salgada = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'TEL' AND pi.tipo = 'Salgado' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_fracoes_salgada = mysql_query($sql_quant_fracoes_salgada);
            $obj_quant_fracoes_salgada = mysql_fetch_object($res_quant_fracoes_salgada);
            
            $total_quant_fracoes_salgada_tel = $obj_quant_fracoes_salgada->total_tel;
            
            
            $sql_quant_fracoes_salgada = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido IN ('NET','IFOOD') AND pi.tipo = 'Salgado' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_fracoes_salgada = mysql_query($sql_quant_fracoes_salgada);
            $obj_quant_fracoes_salgada = mysql_fetch_object($res_quant_fracoes_salgada);
            
            $total_quant_fracoes_salgada_net = $obj_quant_fracoes_salgada->total_net;
            ?>
        
            <td style="background-color: #e5e5e5;" align="center"><b>Frações Salgadas</b></td>
            
            <td align="center"><? echo $total_quant_fracoes_salgada_tel ?></td>
            <td align="center"><? echo $total_quant_fracoes_salgada_net ?></td>
            <td align="center"><? echo ($total_quant_fracoes_salgada_tel + $total_quant_fracoes_salgada_net) ?></td>
        </tr>
        
        <tr>
        	<?
            $sql_quant_fracoes_doce = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'TEL' AND pi.tipo = 'Doce' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_fracoes_doce = mysql_query($sql_quant_fracoes_doce);
            $obj_quant_fracoes_doce = mysql_fetch_object($res_quant_fracoes_doce);
            
            $total_quant_fracoes_doce_tel = $obj_quant_fracoes_doce->total_tel;
            
            
            $sql_quant_fracoes_doce = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido IN ('NET','IFOOD') AND pi.tipo = 'Doce' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_fracoes_doce = mysql_query($sql_quant_fracoes_doce);
            $obj_quant_fracoes_doce = mysql_fetch_object($res_quant_fracoes_doce);
            
            $total_quant_fracoes_doce_net = $obj_quant_fracoes_doce->total_net;
            ?>
        
            <td style="background-color: #e5e5e5;" align="center"><b>Frações Doces</b></td>
            
            <td align="center"><? echo $total_quant_fracoes_doce_tel ?></td>
            <td align="center"><? echo $total_quant_fracoes_doce_net ?></td>
            <td align="center"><? echo ($total_quant_fracoes_doce_tel + $total_quant_fracoes_doce_net) ?></td>
        </tr>     
        
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        
        <tr>
            <?
            // Quant. bordas
            
            $sql_quant_bordas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON (pb.cod_pedidos_pizzas = pp.cod_pedidos_pizzas)  WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao_borda";
            $res_quant_bordas = mysql_query($sql_quant_bordas);
            $obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
            
            $total_borda_tel = $obj_quant_bordas->total_tel;
            
            
            $sql_quant_bordas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON (pb.cod_pedidos_pizzas = pp.cod_pedidos_pizzas)  WHERE p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao_borda";
            $res_quant_bordas = mysql_query($sql_quant_bordas);
            $obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
            
            $total_borda_net = $obj_quant_bordas->total_net;
            
            
            $total_borda = $total_borda_tel + $total_borda_net;
            ?>
            
            <td style="background-color: #e5e5e5;" align="center"><b>Quant. Bordas</b></td>
            
            <td align="center"><? echo $total_borda_tel ?></td>
            <td align="center"><? echo $total_borda_net ?></td>
            <td align="center"><? echo $total_borda ?></td>
        </tr>
        
        <tr>
            <?
            // Quant. adicionais (gergelim)
            
            $sql_quant_adicionais = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_adicionais pa ON (p.cod_pedidos = pa.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON (pa.cod_pedidos_pizzas = pp.cod_pedidos_pizzas)  WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_adicionais = mysql_query($sql_quant_adicionais);
            $obj_quant_adicionais = mysql_fetch_object($res_quant_adicionais);
            
            $total_adicionais_tel = $obj_quant_adicionais->total_tel;
            
            
            $sql_quant_adicionais = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_adicionais pa ON (p.cod_pedidos = pa.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON (pa.cod_pedidos_pizzas = pp.cod_pedidos_pizzas)  WHERE p.origem_pedido IN ('NET','IFOOD') $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_quant_adicionais = mysql_query($sql_quant_adicionais);
            $obj_quant_adicionais = mysql_fetch_object($res_quant_adicionais);
            
            $total_adicionais_net = $obj_quant_adicionais->total_net;
            
            
            $total_adicionais = $total_adicionais_tel + $total_adicionais_net;
            ?>
            
            <td style="background-color: #e5e5e5;" align="center"><b>Quant. Gergelim</b></td>
            
            <td align="center"><? echo $total_adicionais_tel ?></td>
            <td align="center"><? echo $total_adicionais_net ?></td>
            <td align="center"><? echo $total_adicionais ?></td>
        </tr>
        
        <tr>
            <?
            // Quant. indredientes não padrão (adicionais)
            
            $sql_ingred_adicionais = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) WHERE p.origem_pedido = 'TEL' AND pi.ingrediente_padrao = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_ingred_adicionais = mysql_query($sql_ingred_adicionais);
            $obj_ingred_adicionais = mysql_fetch_object($res_ingred_adicionais);
            
            $total_ingred_adicionais_tel = $obj_ingred_adicionais->total_tel;
            
            
            $sql_ingred_adicionais = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) WHERE p.origem_pedido IN ('NET','IFOOD') AND pi.ingrediente_padrao = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido $sql_motivo_promocao";
            $res_ingred_adicionais = mysql_query($sql_ingred_adicionais);
            $obj_ingred_adicionais = mysql_fetch_object($res_ingred_adicionais);
            
            $total_ingred_adicionais_net = $obj_ingred_adicionais->total_net;
            
            
            $total_ingred_adicionais = $total_ingred_adicionais_tel + $total_ingred_adicionais_net;
            ?>
            <td style="background-color: #e5e5e5;" align="center"><b>Quant. Adicionais</b></td>
            
            <td align="center"><? echo $total_ingred_adicionais_tel ?></td>
            <td align="center"><? echo $total_ingred_adicionais_net ?></td>
            <td align="center"><? echo $total_ingred_adicionais ?></td>
        </tr>
    </table>  
    
    <br><br>
    
    <p>* São considerados pedidos NOVOS, IMPRESSOS e BAIXADOS.</p>
    
    <?
    
    /*
    
    $tabela = 'ipi_pedidos';
    $chave_primaria = 'cod_pedidos';
    
    if ($cod_pizzarias > 0)
    {
        $SqlCodPizzariasClientes = "AND p.cod_pizzarias = " . $cod_pizzarias;
    }
    else
    {
        $SqlCodPizzariasClientes = '';
    }
    
    if ($cod_clientes > 0)
    {
        $SqlCodPizzariasClientes .= " AND p.cod_clientes = " . $cod_clientes;
    }
    
    $SqlCodPizzariasClientes .= " AND p.origem_pedido = '$origem_pedido'";
    
    // Pizza
    $quantidade_total_pizza_paga = 0;
    $valor_total_pizza_paga = 0;
    
    $quantidade_total_pizza_promocao = 0;
    $valor_total_pizza_promocao = 0;
    
    $quantidade_total_pizza_fidelidade = 0;
    $valor_total_pizza_fidelidade = 0;
    
    // Borda
    $quantidade_total_borda_paga = 0;
    $valor_total_borda_paga = 0;
    
    $quantidade_total_borda_promocao = 0;
    $valor_total_borda_promocao = 0;
    
    $quantidade_total_borda_fidelidade = 0;
    $valor_total_borda_fidelidade = 0;
    
    // Adicionais (Gergelim)
    $quantidade_total_adicionais_paga = 0;
    $valor_total_adicionais_paga = 0;
    
    // Ingredientes Adicionais (Extra)
    $quantidade_total_ingredientes_paga = 0;
    $valor_total_ingredientes_paga = 0;
    
    // Bebidas
    $quantidade_total_bebidas_paga = 0;
    $valor_total_bebidas_paga = 0;
    
    $quantidade_total_bebidas_promocao = 0;
    $valor_total_bebidas_promocao = 0;
    
    $quantidade_total_bebidas_fidelidade = 0;
    $valor_total_bebidas_fidelidade = 0;
    
    // Valor Total
    $valor_total = 0;
    ?>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Pizza (Pagas)</td>
            <td align="center" width="200">Tamanho</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
    
    <?
    
    $SqlBuscaPizzas = "SELECT * FROM ipi_pizzas p INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (p.cod_pizzas = pt.cod_pizzas) INNER JOIN ipi_tamanhos t ON (pt.cod_tamanhos = t.cod_tamanhos) ORDER BY t.tamanho, p.pizza";
    $resBuscaPizzas = mysql_query($SqlBuscaPizzas);
    $numBuscaPizzas = mysql_num_rows($resBuscaPizzas);
    
    for ($i = 0; $i < $numBuscaPizzas; $i++)
    {
        $objBuscaPizzas = mysql_fetch_object($resBuscaPizzas);
        
        $SqlBuscaPedidosFracoes = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS quantidade FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzas pz ON (pz.cod_pizzas = pf.cod_pizzas) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pf.cod_pizzas = " . $objBuscaPizzas->cod_pizzas . " AND pp.cod_tamanhos = " . $objBuscaPizzas->cod_tamanhos . " AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaPedidosFracoes = executaBuscaSimples($SqlBuscaPedidosFracoes, $con);
        $quantidade = ($objBuscaPedidosFracoes->quantidade > 0) ? $objBuscaPedidosFracoes->quantidade : 0;
        
        $SqlBuscaPedidosValor = "SELECT SUM(pf.preco) AS valor FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzas pz ON (pz.cod_pizzas = pf.cod_pizzas) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pf.cod_pizzas = " . $objBuscaPizzas->cod_pizzas . " AND pp.cod_tamanhos = " . $objBuscaPizzas->cod_tamanhos . " AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaPedidosValor = executaBuscaSimples($SqlBuscaPedidosValor, $con);
        $valor = ($objBuscaPedidosValor->valor > 0) ? $objBuscaPedidosValor->valor : '0.00';
        
        $quantidade_total_pizza_paga += $quantidade;
        $valor_total_pizza_paga += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            echo '<td align="center">' . $objBuscaPizzas->pizza . '</td>';
            echo '<td align="center">' . $objBuscaPizzas->tamanho . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            echo '</tr>';
        }
    }
    ?>
    
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Pizza (Promoção)</td>
            <td align="center" width="200">Tamanho</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
    
    <?
    
    $SqlBuscaPizzas = "SELECT * FROM ipi_pizzas p INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (p.cod_pizzas = pt.cod_pizzas) INNER JOIN ipi_tamanhos t ON (pt.cod_tamanhos = t.cod_tamanhos) ORDER BY t.tamanho, p.pizza";
    $resBuscaPizzas = mysql_query($SqlBuscaPizzas);
    $numBuscaPizzas = mysql_num_rows($resBuscaPizzas);
    
    for ($i = 0; $i < $numBuscaPizzas; $i++)
    {
        $objBuscaPizzas = mysql_fetch_object($resBuscaPizzas);
        
        $SqlBuscaPedidosFracoes = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS quantidade FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzas pz ON (pz.cod_pizzas = pf.cod_pizzas) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pf.cod_pizzas = " . $objBuscaPizzas->cod_pizzas . " AND pp.cod_tamanhos = " . $objBuscaPizzas->cod_tamanhos . " AND pp.promocional = 1 AND pp.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaPedidosFracoes = executaBuscaSimples($SqlBuscaPedidosFracoes, $con);
        $quantidade = ($objBuscaPedidosFracoes->quantidade > 0) ? $objBuscaPedidosFracoes->quantidade : 0;
        
        $SqlBuscaPedidosValor = "SELECT SUM(pf.preco) AS valor FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzas pz ON (pz.cod_pizzas = pf.cod_pizzas) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pf.cod_pizzas = " . $objBuscaPizzas->cod_pizzas . " AND pp.cod_tamanhos = " . $objBuscaPizzas->cod_tamanhos . " AND pp.promocional = 1 AND pp.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaPedidosValor = executaBuscaSimples($SqlBuscaPedidosValor, $con);
        $valor = ($objBuscaPedidosValor->valor > 0) ? $objBuscaPedidosValor->valor : '0.00';
        
        $quantidade_total_pizza_promocao += $quantidade;
        $valor_total_pizza_promocao += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            echo '<td align="center">' . $objBuscaPizzas->pizza . '</td>';
            echo '<td align="center">' . $objBuscaPizzas->tamanho . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            echo '</tr>';
        }
    }
    
    ?>
    
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Pizza (Fidelidade)</td>
            <td align="center" width="200">Tamanho</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
    
    <?
    
    $SqlBuscaPizzas = "SELECT * FROM ipi_pizzas p INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (p.cod_pizzas = pt.cod_pizzas) INNER JOIN ipi_tamanhos t ON (pt.cod_tamanhos = t.cod_tamanhos) ORDER BY t.tamanho, p.pizza";
    $resBuscaPizzas = mysql_query($SqlBuscaPizzas);
    $numBuscaPizzas = mysql_num_rows($resBuscaPizzas);
    
    for ($i = 0; $i < $numBuscaPizzas; $i++)
    {
        $objBuscaPizzas = mysql_fetch_object($resBuscaPizzas);
        
        $SqlBuscaPedidosFracoes = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS quantidade FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzas pz ON (pz.cod_pizzas = pf.cod_pizzas) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pf.cod_pizzas = " . $objBuscaPizzas->cod_pizzas . " AND pp.cod_tamanhos = " . $objBuscaPizzas->cod_tamanhos . " AND pp.promocional = 0 AND pp.fidelidade = 1 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaPedidosFracoes = executaBuscaSimples($SqlBuscaPedidosFracoes, $con);
        $quantidade = ($objBuscaPedidosFracoes->quantidade > 0) ? $objBuscaPedidosFracoes->quantidade : 0;
        
        $SqlBuscaPedidosValor = "SELECT SUM(pf.preco) AS valor FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzas pz ON (pz.cod_pizzas = pf.cod_pizzas) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pf.cod_pizzas = " . $objBuscaPizzas->cod_pizzas . " AND pp.cod_tamanhos = " . $objBuscaPizzas->cod_tamanhos . " AND pp.promocional = 0 AND pp.fidelidade = 1 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaPedidosValor = executaBuscaSimples($SqlBuscaPedidosValor, $con);
        $valor = ($objBuscaPedidosValor->valor > 0) ? $objBuscaPedidosValor->valor : '0.00';
        
        $quantidade_total_pizza_fidelidade += $quantidade;
        $valor_total_pizza_fidelidade += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            echo '<td align="center">' . $objBuscaPizzas->pizza . '</td>';
            echo '<td align="center">' . $objBuscaPizzas->tamanho . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            echo '</tr>';
        }
    }
    
    ?>
    
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Bordas (Pagas)</td>
            <td align="center" width="200">Tamanho</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
      <?
    $SqlBuscaBordas = "SELECT * FROM ipi_bordas b INNER JOIN ipi_tamanhos_ipi_bordas tb ON (b.cod_bordas = tb.cod_bordas) INNER JOIN ipi_tamanhos t ON (tb.cod_tamanhos = t.cod_tamanhos) ORDER BY t.tamanho, b.borda";
    $resBuscaBordas = mysql_query($SqlBuscaBordas);
    
    while ($objBuscaBordas = mysql_fetch_object($resBuscaBordas))
    {
        
        $SqlBuscaQuantidade = "SELECT COUNT(*) AS quantidade FROM ipi_pedidos_bordas pb INNER JOIN ipi_pedidos_pizzas pp ON (pb.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pb.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bordas = " . $objBuscaBordas->cod_bordas . " AND pp.cod_tamanhos = " . $objBuscaBordas->cod_tamanhos . " AND pb.promocional = 0 AND pb.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(pb.preco) AS valor FROM ipi_pedidos_bordas pb INNER JOIN ipi_pedidos_pizzas pp ON (pb.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pb.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bordas = " . $objBuscaBordas->cod_bordas . " AND pp.cod_tamanhos = " . $objBuscaBordas->cod_tamanhos . " AND pb.promocional = 0 AND pb.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_borda_paga += $quantidade;
        $valor_total_borda_paga += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            
            echo '<td align="center">' . bd2texto($objBuscaBordas->borda) . '</td>';
            echo '<td align="center">' . bd2texto($objBuscaBordas->tamanho) . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            
            echo '</tr>';
        }
    }
    ?>
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Bordas (Promoção)</td>
            <td align="center" width="200">Tamanho</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
      <?
    $SqlBuscaBordas = "SELECT * FROM ipi_bordas b INNER JOIN ipi_tamanhos_ipi_bordas tb ON (b.cod_bordas = tb.cod_bordas) INNER JOIN ipi_tamanhos t ON (tb.cod_tamanhos = t.cod_tamanhos) ORDER BY t.tamanho, b.borda";
    $resBuscaBordas = mysql_query($SqlBuscaBordas);
    
    while ($objBuscaBordas = mysql_fetch_object($resBuscaBordas))
    {
        
        $SqlBuscaQuantidade = "SELECT COUNT(*) AS quantidade FROM ipi_pedidos_bordas pb INNER JOIN ipi_pedidos_pizzas pp ON (pb.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pb.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bordas = " . $objBuscaBordas->cod_bordas . " AND pp.cod_tamanhos = " . $objBuscaBordas->cod_tamanhos . " AND pb.promocional = 1 AND pb.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(pb.preco) AS valor FROM ipi_pedidos_bordas pb INNER JOIN ipi_pedidos_pizzas pp ON (pb.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pb.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bordas = " . $objBuscaBordas->cod_bordas . " AND pp.cod_tamanhos = " . $objBuscaBordas->cod_tamanhos . " AND pb.promocional = 1 AND pb.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_borda_promocao += $quantidade;
        $valor_total_borda_promocao += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            
            echo '<td align="center">' . bd2texto($objBuscaBordas->borda) . '</td>';
            echo '<td align="center">' . bd2texto($objBuscaBordas->tamanho) . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            
            echo '</tr>';
        }
    }
    ?>
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Bordas (Fidelidade)</td>
            <td align="center" width="200">Tamanho</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
      <?
    $SqlBuscaBordas = "SELECT * FROM ipi_bordas b INNER JOIN ipi_tamanhos_ipi_bordas tb ON (b.cod_bordas = tb.cod_bordas) INNER JOIN ipi_tamanhos t ON (tb.cod_tamanhos = t.cod_tamanhos) ORDER BY t.tamanho, b.borda";
    $resBuscaBordas = mysql_query($SqlBuscaBordas);
    
    while ($objBuscaBordas = mysql_fetch_object($resBuscaBordas))
    {
        
        $SqlBuscaQuantidade = "SELECT COUNT(*) AS quantidade FROM ipi_pedidos_bordas pb INNER JOIN ipi_pedidos_pizzas pp ON (pb.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pb.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bordas = " . $objBuscaBordas->cod_bordas . " AND pp.cod_tamanhos = " . $objBuscaBordas->cod_tamanhos . " AND pb.promocional = 0 AND pb.fidelidade = 1 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(pb.preco) AS valor FROM ipi_pedidos_bordas pb INNER JOIN ipi_pedidos_pizzas pp ON (pb.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pb.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bordas = " . $objBuscaBordas->cod_bordas . " AND pp.cod_tamanhos = " . $objBuscaBordas->cod_tamanhos . " AND pb.promocional = 0 AND pb.fidelidade = 1 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_borda_fidelidade += $quantidade;
        $valor_total_borda_fidelidade += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            
            echo '<td align="center">' . bd2texto($objBuscaBordas->borda) . '</td>';
            echo '<td align="center">' . bd2texto($objBuscaBordas->tamanho) . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            
            echo '</tr>';
        }
    }
    ?>
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Gergelim</td>
            <td align="center" width="200">Tamanho</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
      <?
    $SqlBuscaAdicionais = "SELECT * FROM ipi_adicionais a INNER JOIN ipi_tamanhos_ipi_adicionais ta ON (a.cod_adicionais = ta.cod_adicionais) INNER JOIN ipi_tamanhos t ON (ta.cod_tamanhos = t.cod_tamanhos) ORDER BY t.tamanho, a.adicional";
    $resBuscaAdicionais = mysql_query($SqlBuscaAdicionais);
    
    while ($objBuscaAdicionais = mysql_fetch_object($resBuscaAdicionais))
    {
        
        $SqlBuscaQuantidade = "SELECT COUNT(*) AS quantidade FROM ipi_pedidos_adicionais pa INNER JOIN ipi_pedidos_pizzas pp ON (pa.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pa.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pa.cod_adicionais = " . $objBuscaAdicionais->cod_adicionais . " AND pp.cod_tamanhos = " . $objBuscaAdicionais->cod_tamanhos . " AND pa.promocional = 0 AND pa.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(pa.preco) AS valor FROM ipi_pedidos_adicionais pa INNER JOIN ipi_pedidos_pizzas pp ON (pa.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pa.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pa.cod_adicionais = " . $objBuscaAdicionais->cod_adicionais . " AND pp.cod_tamanhos = " . $objBuscaAdicionais->cod_tamanhos . " AND pa.promocional = 0 AND pa.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_adicionais_paga += $quantidade;
        $valor_total_adicionais_paga += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            
            echo '<td align="center">' . bd2texto($objBuscaAdicionais->adicional) . '</td>';
            echo '<td align="center">' . bd2texto($objBuscaAdicionais->tamanho) . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            
            echo '</tr>';
        }
    }
    ?>
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Fração</td>
            <td align="center">Tamanho</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
    
    <?
    
    $SqlBuscaFracoes = "SELECT * FROM ipi_fracoes_precos fp INNER JOIN ipi_tamanhos_ipi_fracoes_precos tf ON (fp.cod_fracoes_precos = tf.cod_fracoes_precos) INNER JOIN ipi_tamanhos t ON (tf.cod_tamanhos = t.cod_tamanhos) ORDER BY t.tamanho, fp.fracao";
    $resBuscaFracoes = mysql_query($SqlBuscaFracoes);
    $numBuscaFracoes = mysql_num_rows($resBuscaFracoes);
    
    for ($i = 0; $i < $numBuscaFracoes; $i++)
    {
        $objBuscaFracoes = mysql_fetch_object($resBuscaFracoes);
        
        $SqlBuscaQuantidade = "SELECT COUNT(*) AS quantidade FROM ipi_pedidos_pizzas pp INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pp.quant_fracao = " . $objBuscaFracoes->fracao . " AND pp.cod_tamanhos = " . $objBuscaFracoes->cod_tamanhos . " AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaPedidosValor = "SELECT SUM(pp.preco) AS valor FROM ipi_pedidos_pizzas pp INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pp.quant_fracao = " . $objBuscaFracoes->fracao . " AND pp.cod_tamanhos = " . $objBuscaFracoes->cod_tamanhos . " AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaPedidosValor = executaBuscaSimples($SqlBuscaPedidosValor, $con);
        $valor = ($objBuscaPedidosValor->valor > 0) ? $objBuscaPedidosValor->valor : '0.00';
        
        if ($valor > 0)
        {
            $quantidade_total_fracoes_paga += $quantidade;
            $valor_total_fracoes_paga += $valor;
            $valor_total += $valor;
            
            echo '<tr>';
            echo '<td align="center">' . $objBuscaFracoes->fracao . '</td>';
            echo '<td align="center">' . $objBuscaFracoes->tamanho . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            echo '</tr>';
        }
    }
    ?>
    
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Ingredientes Adicionais</td>
            <td align="center" width="200">Tamanho</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
      <?
    $SqlBuscaIngredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos ti ON (i.cod_ingredientes = ti.cod_ingredientes) INNER JOIN ipi_tamanhos t ON (ti.cod_tamanhos = t.cod_tamanhos) ORDER BY t.tamanho, i.ingrediente";
    $resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
    
    while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes))
    {
        $SqlBuscaQuantidade = "SELECT SUM(pf.fracao / pp.quant_fracao) AS quantidade FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pi.cod_ingredientes = " . $objBuscaIngredientes->cod_ingredientes . " AND pp.cod_tamanhos = " . $objBuscaIngredientes->cod_tamanhos . " AND pi.promocional = 0 AND pi.fidelidade = 0 AND pi.ingrediente_padrao = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(pi.preco) AS valor FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pi.cod_ingredientes = " . $objBuscaIngredientes->cod_ingredientes . " AND pp.cod_tamanhos = " . $objBuscaIngredientes->cod_tamanhos . " AND pi.promocional = 0 AND pi.fidelidade = 0 AND pi.ingrediente_padrao = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_ingredientes_paga += $quantidade;
        $valor_total_ingredientes_paga += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            
            echo '<td align="center">' . bd2texto($objBuscaIngredientes->ingrediente) . '</td>';
            echo '<td align="center">' . bd2texto($objBuscaIngredientes->tamanho) . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            
            echo '</tr>';
        }
    }
    ?>
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Bebida (Pagas)</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
      <?
    $SqlBuscaBebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) ORDER BY bebida";
    $resBuscaBebidas = mysql_query($SqlBuscaBebidas);
    
    while ($objBuscaBebidas = mysql_fetch_object($resBuscaBebidas))
    {
        $SqlBuscaQuantidade = "SELECT SUM(quantidade) AS quantidade FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = " . $objBuscaBebidas->cod_bebidas_ipi_conteudos . " AND pb.promocional = 0 AND pb.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(preco) AS valor FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = " . $objBuscaBebidas->cod_bebidas_ipi_conteudos . " AND pb.promocional = 0 AND pb.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_bebidas_paga += $quantidade;
        $valor_total_bebidas_paga += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            
            echo '<td align="center">' . $objBuscaBebidas->bebida . ' ' . $objBuscaBebidas->conteudo . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            
            echo '</tr>';
        }
    }
    ?>
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Bebida (Promoção)</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
      <?
    $SqlBuscaBebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) ORDER BY bebida";
    $resBuscaBebidas = mysql_query($SqlBuscaBebidas);
    
    while ($objBuscaBebidas = mysql_fetch_object($resBuscaBebidas))
    {
        $SqlBuscaQuantidade = "SELECT SUM(quantidade) AS quantidade FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = " . $objBuscaBebidas->cod_bebidas_ipi_conteudos . " AND pb.promocional = 1 AND pb.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(preco) AS valor FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = " . $objBuscaBebidas->cod_bebidas_ipi_conteudos . " AND pb.promocional = 1 AND pb.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_bebidas_promocao += $quantidade;
        $valor_total_bebidas_promocao += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            
            echo '<td align="center">' . $objBuscaBebidas->bebida . ' ' . $objBuscaBebidas->conteudo . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            
            echo '</tr>';
        }
    }
    ?>
    </tbody>
</table>

<br>
<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Bebida (Fidelidade)</td>
            <td align="center" width="100">Quantidade</td>
            <td align="center" width="100">Valor</td>
        </tr>
    </thead>
    <tbody>
      <?
    $SqlBuscaBebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) ORDER BY bebida";
    $resBuscaBebidas = mysql_query($SqlBuscaBebidas);
    
    while ($objBuscaBebidas = mysql_fetch_object($resBuscaBebidas))
    {
        $SqlBuscaQuantidade = "SELECT SUM(quantidade) AS quantidade FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = " . $objBuscaBebidas->cod_bebidas_ipi_conteudos . " AND pb.promocional = 0 AND pb.fidelidade = 1 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(preco) AS valor FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = " . $objBuscaBebidas->cod_bebidas_ipi_conteudos . " AND pb.promocional = 0 AND pb.fidelidade = 1 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_bebidas_fidelidade += $quantidade;
        $valor_total_bebidas_fidelidade += $valor;
        $valor_total += $valor;
        
        if ($quantidade > 0)
        {
            echo '<tr>';
            
            echo '<td align="center">' . $objBuscaBebidas->bebida . ' ' . $objBuscaBebidas->conteudo . '</td>';
            echo '<td align="center">' . $quantidade . '</td>';
            echo '<td align="center">' . bd2moeda($valor) . '</td>';
            
            echo '</tr>';
        }
    }
    ?>
    </tbody>
</table>

<?
    
    // Pizza
    echo '<br><p><b>Pizzas</b></p>';
    echo '<hr color="#cccccc" size="1" noshadow>';
    
    $SqlBuscaPedidosPizzas = "SELECT COUNT(*) AS quantidade FROM ipi_pedidos_pizzas pp INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
    $objBuscaPedidosPizzas = executaBuscaSimples($SqlBuscaPedidosPizzas, $con);
    $quantidade_total_pizza_paga = $objBuscaPedidosPizzas->quantidade;
    
    $SqlBuscaPedidosPizzas = "SELECT COUNT(*) AS quantidade FROM ipi_pedidos_pizzas pp INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pp.promocional = 1 AND pp.fidelidade = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
    $objBuscaPedidosPizzas = executaBuscaSimples($SqlBuscaPedidosPizzas, $con);
    $quantidade_total_pizza_promocao = $objBuscaPedidosPizzas->quantidade;
    
    $SqlBuscaPedidosPizzas = "SELECT COUNT(*) AS quantidade FROM ipi_pedidos_pizzas pp INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pp.promocional = 0 AND pp.fidelidade = 1 AND p.situacao = 'BAIXADO' $SqlCodPizzariasClientes";
    $objBuscaPedidosPizzas = executaBuscaSimples($SqlBuscaPedidosPizzas, $con);
    $quantidade_total_pizza_fidelidade = $objBuscaPedidosPizzas->quantidade;
    
    echo '<br><p><b>Quantidade de Pizzas Pagas:</b> ' . $quantidade_total_pizza_paga . '</p>';
    echo '<p><b>Valor Total de Pizzas Pagas:</b> ' . bd2moeda($valor_total_pizza_paga) . '</p>';
    
    echo '<br><p><b>Quantidade de Pizzas Promoção:</b> ' . $quantidade_total_pizza_promocao . '</p>';
    
    echo '<br><p><b>Quantidade de Pizzas Fidelidade:</b> ' . $quantidade_total_pizza_fidelidade . '</p>';
    
    echo '<br><p><b>Quantidade de Pizzas:</b> ' . ($quantidade_total_pizza_paga + $quantidade_total_pizza_promocao + $quantidade_total_pizza_fidelidade) . '</p>';
    echo '<p><b>Valor Total de Pizzas:</b> ' . bd2moeda($valor_total_pizza_paga) . '</p>';
    
    // Borda
    echo '<br><p><b>Bordas</b></p>';
    echo '<hr color="#cccccc" size="1" noshadow>';
    
    echo '<br><p><b>Quantidade de Bordas Pagas:</b> ' . $quantidade_total_borda_paga . '</p>';
    echo '<p><b>Valor Total de Bordas Pagas:</b> ' . bd2moeda($valor_total_borda_paga) . '</p>';
    
    echo '<br><p><b>Quantidade de Bordas Promoção:</b> ' . $quantidade_total_borda_promocao . '</p>';
    
    echo '<br><p><b>Quantidade de Bordas Fidelidade:</b> ' . $quantidade_total_borda_fidelidade . '</p>';
    
    echo '<br><p><b>Quantidade de Bordas:</b> ' . ($quantidade_total_borda_paga + $quantidade_total_borda_promocao + $quantidade_total_borda_fidelidade) . '</p>';
    echo '<p><b>Valor Total de Bordas:</b> ' . bd2moeda($valor_total_borda_paga) . '</p>';
    
    // Adicionais
    echo '<br><p><b>Gergelim</b></p>';
    echo '<hr color="#cccccc" size="1" noshadow>';
    
    echo '<br><p><b>Quantidade de Gergelins:</b> ' . $quantidade_total_adicionais_paga . '</p>';
    echo '<p><b>Valor Total de Gergelins:</b> ' . bd2moeda($valor_total_adicionais_paga) . '</p>';
    
    //Frações
    echo '<br><p><b>Frações</b></p>';
    echo '<hr color="#cccccc" size="1" noshadow>';
    
    echo '<br><p><b>Quantidade de Frações:</b> ' . $quantidade_total_fracoes_paga . '</p>';
    echo '<p><b>Valor Total de Frações:</b> ' . bd2moeda($valor_total_fracoes_paga) . '</p>';
    
    // Ingredientes
    echo '<br><p><b>Ingredientes Adicionais</b></p>';
    echo '<hr color="#cccccc" size="1" noshadow>';
    
    echo '<br><p><b>Quantidade de Ingredientes Adicionais:</b> ' . $quantidade_total_ingredientes_paga . '</p>';
    echo '<p><b>Valor Total de Ingredientes Adicionais:</b> ' . bd2moeda($valor_total_ingredientes_paga) . '</p>';
    
    // Bebidas
    echo '<br><p><b>Bebidas</b></p>';
    echo '<hr color="#cccccc" size="1" noshadow>';
    
    echo '<br><p><b>Quantidade de Bordas Pagas:</b> ' . $quantidade_total_bebidas_paga . '</p>';
    echo '<p><b>Valor Total de Bordas Pagas:</b> ' . bd2moeda($valor_total_bebidas_paga) . '</p>';
    
    echo '<br><p><b>Quantidade de Bordas Promoção:</b> ' . $quantidade_total_bebidas_promocao . '</p>';
    
    echo '<br><p><b>Quantidade de Bordas Fidelidade:</b> ' . $quantidade_total_bebidas_fidelidade . '</p>';
    
    echo '<br><p><b>Quantidade de Bordas:</b> ' . ($quantidade_total_bebidas_paga + $quantidade_total_bebidas_promocao + $quantidade_total_bebidas_fidelidade).'</p>';
  echo '<p><b>Valor Total de Bordas:</b> '.bd2moeda($valor_total_bebidas_paga).'</p>';
  
  // Total
  echo '<br><p><b>Total</b></p>';
  echo '<hr color="#cccccc" size="1" noshadow>';
  
  echo '<br><p><b>Valor Total do Período:</b> '.bd2moeda($valor_total).'</p>';
	*/
}

?>
