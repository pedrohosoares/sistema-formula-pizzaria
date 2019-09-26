<?php

/**
 * ipi_fechamento_caixa.php: Fechamento de caixa
 * 
 * Índice: cod_bebidas
 * Tabela: ipi_bebidas
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Fechamento de Caixa');

$acao = validaVarPost('acao');

switch ($acao)
{
    case 'fechar_caixa':
      $cod_caixa = validaVarPost('cod_caixa');
      $obs_caixa = validaVarPost('obs_caixa');
  
      $motivo_gargalo = validaVarPost('motivo_gargalo');
  		$erro_atendimento = validaVarPost('erro_atendimento');
  		$erro_cozinha = validaVarPost('erro_cozinha');
  		$erro_motoboy = validaVarPost('erro_motoboy');
  		$erro_sistema = validaVarPost('erro_sistema');
  		$tempo_maximo = validaVarPost('tempo_maximo');
  		$sobra_excesso = validaVarPost('sobra_excesso');
  		$valor_contagem = moeda2bd(validaVarPost('valor_contagem'));
  
      $atendente = $_SESSION['usuario']['codigo'];
		
		$valor_contagem = ($valor_contagem*$sobra_excesso);
        $conexao = conectabd();

        $sql_buscar_caixa = "SELECT c.cod_caixas_fisicos, c.cod_pizzarias, c.situacao, u.usuario, c.data_hora_fechamento FROM ipi_caixa c INNER JOIN nuc_usuarios u ON (c.cod_usuarios_fechamento = u.cod_usuarios) WHERE c.cod_caixa = '$cod_caixa'";
        $res_buscar_caixa = mysql_query($sql_buscar_caixa);
        $obj_buscar_caixa = mysql_fetch_object($res_buscar_caixa);

        $cod_pizzarias = $obj_buscar_caixa->cod_pizzarias;
        require_once("../../pub_req_fuso_horario1.php");
     
        if ($obj_buscar_caixa->situacao == "ABERTO")
        {

            
            $sql_fechar_caixa = sprintf("UPDATE ipi_caixa SET situacao = 'FECHADO', data_hora_fechamento = NOW(),tempo_maximo_entrega = %d, cod_motivos = %d, erro_atendimento = '%s', erro_cozinha = '%s', erro_motoboy = '%s', erro_sistema  = '%s', contagem_caixa = '%s', obs_caixa = '%s', cod_usuarios_fechamento = '%s',cod_usuarios_atendentes = %d WHERE cod_caixa = '$cod_caixa'",$tempo_maximo,$motivo_gargalo,texto2bd($erro_atendimento),texto2bd($erro_cozinha),texto2bd($erro_motoboy),texto2bd($erro_sistema),$valor_contagem, texto2bd($obs_caixa), $_SESSION['usuario']['codigo'],$atendente);
            //echo "SQL FECHAR ".$sql_fechar_caixa."<br/><br/>";
/*
        	echo "<pre>";
        	print_r($_POST);
        	echo "</pre>";
        	echo "<br/>";
        	echo "Vlc ".$valor_contagem;
        	echo "<br/>";
        	echo  $sql_fechar_caixa;
*/
            $res_fechar_caixa = mysql_query($sql_fechar_caixa);
            
            $sql_buscar_pizzaria = "SELECT c.*,p.*,m.*,usu.nome as nome_usu,usu.cod_usuarios FROM ipi_caixa c INNER JOIN ipi_pizzarias p ON (c.cod_pizzarias = p.cod_pizzarias)  left join ipi_caixa_motivos m on c.cod_motivos = m.cod_motivos inner join nuc_usuarios usu on usu.cod_usuarios = c.cod_usuarios_abertura WHERE cod_caixa = '$cod_caixa'";
            $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
            $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
            //echo "<br>SQL busca Pizzaria: ".$sql_buscar_pizzaria."<br/><br/>";

            $res_abrir_caixa = false;
            
            if($obj_buscar_pizzaria->cod_pizzarias > 0)
            {
                $sql_inserir_pedidos_caixa = "INSERT INTO ipi_caixa_ipi_pedidos (cod_caixa, cod_pedidos) (SELECT $cod_caixa, cod_pedidos FROM ipi_pedidos WHERE cod_pizzarias = '" . $obj_buscar_pizzaria->cod_pizzarias . "' AND data_hora_pedido BETWEEN '" . $obj_buscar_pizzaria->data_hora_abertura . "' AND '" . $obj_buscar_pizzaria->data_hora_fechamento . "' AND cod_caixas_fisicos = '" . $obj_buscar_pizzaria->cod_caixas_fisicos . "' AND situacao NOT IN ('CANCELADO'))";
                $res_inserir_pedidos_caixa = mysql_query($sql_inserir_pedidos_caixa);
                //echo "SQL inserir pedidos caixa ".$sql_inserir_pedidos_caixa."<br/><br/>";

                //$sql_abrir_caixa = sprintf("INSERT INTO ipi_caixa (cod_pizzarias, cod_usuarios_abertura, cod_usuarios_fechamento, data_hora_abertura, situacao) VALUES ('%s', '%s', '%s', NOW(), 'ABERTO')", $obj_buscar_pizzaria->cod_pizzarias, $_SESSION['usuario']['codigo'], $_SESSION['usuario']['codigo']);
                //$res_abrir_caixa = mysql_query($sql_abrir_caixa);

                // Voltar o tempo de entrega ideal
                //$sql_update_tempo_entrega = "UPDATE ipi_pizzarias_horarios SET tempo_entrega = tempo_entrega_ideal WHERE cod_pizzarias = '" . $obj_buscar_pizzaria->cod_pizzarias . "'";
                //$res_update_tempo_entrega = mysql_query($sql_update_tempo_entrega);
            }
            
            if(true)
            {
                // Enviar relatório de fechamento
                
                require_once '../../ipi_email.php';
                
                $assunto = 'Relatório de fechamento de caixa - Loja ' . $obj_buscar_pizzaria->cod_pizzarias . ': ' . bd2texto($obj_buscar_pizzaria->nome);
                
                $data_inicial = $obj_buscar_pizzaria->data_hora_abertura;
                $data_final = $obj_buscar_pizzaria->data_hora_fechamento;
                $cod_pizzarias = $obj_buscar_pizzaria->cod_pizzarias;
                //echo "<Br>cod_pizzarias: ".$cod_pizzarias;
                
                $rel_fechamento = '<br><br><p align="center"><b>Relatório de Fechamento de Caixa</b></p><br><br><br>' . "\r\n";
                $rel_fechamento .= '<b>Loja ' . $obj_buscar_pizzaria->cod_pizzarias . '</b>: ' . bd2texto($obj_buscar_pizzaria->nome) . '<br>' . "\r\n";
                $rel_fechamento .= '<b>Data de Abertura do Caixa</b>: ' . bd2datahora($data_inicial) . '<br>' . "\r\n";
                $rel_fechamento .= '<b>Data de Fechamento do Caixa</b>: ' . bd2datahora($data_final) . '<br>' . "\r\n";
    			
    			$rel_fechamento .= '<b>Tempo maximo de entrega</b>: ' . nl2br($obj_buscar_pizzaria->tempo_maximo_entrega) . '<br>' . "\r\n";
    			if($obj_buscar_pizzaria->cod_motivos!="0")
    			$rel_fechamento .= '<b>Motivo Gargalo</b>: ' . bd2texto(nl2br($obj_buscar_pizzaria->motivo)) . '<br>' . "\r\n";
    			$rel_fechamento .= '<b>Erro de atendimento </b>: ' . bd2texto(nl2br($obj_buscar_pizzaria->erro_atendimento)) . '<br>' . "\r\n";
    			$rel_fechamento .= '<b>Erro da cozinha</b>: ' . bd2texto(nl2br($obj_buscar_pizzaria->erro_cozinha)) . '<br>' . "\r\n";
    			$rel_fechamento .= '<b>Erro do motoboy</b>: ' . bd2texto(nl2br($obj_buscar_pizzaria->erro_motoboy)) . '<br>' . "\r\n";
    			$rel_fechamento .= '<b>Erro do sistema</b>: ' . bd2texto(nl2br($obj_buscar_pizzaria->erro_sistema)) . '<br>' . "\r\n";
    			$rel_fechamento .= '<b>Atendente </b>: ' . bd2texto(nl2br($obj_buscar_pizzaria->nome_usu)) . '<br>' . "\r\n";

    			if($obj_buscar_pizzaria->contagem_caixa>0)
    			{
    				$rel_fechamento .= '<b>Contagem do caixa</b>: Sobrou R$' . bd2moeda($obj_buscar_pizzaria->contagem_caixa). '<br>' . "\r\n";
    			}
    			else
    			{
    				$rel_fechamento .= '<b>Contagem do caixa</b>: Faltou R$' . bd2moeda($obj_buscar_pizzaria->contagem_caixa*-1) . '<br>' . "\r\n";
    			}
                $rel_fechamento .= '<b>Observações gerais</b>: ' . bd2texto(nl2br($obj_buscar_pizzaria->obs_caixa)) . '<br><br><br><br>' . "\r\n";
                
                // Relatório de formas de pagamento - baixados
                $rel_fechamento .= '<table border="1" cellpadding="0" cellspacing="0" width="500" align="center">' . "\r\n";
                $rel_fechamento .= '<tr><td colspan="5" style="background-color: #e5e5e5;" align="center"><b>Formas de Pagamento</b></td></tr>' . "\r\n";
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;">&nbsp;</td>' . "\r\n";
                //$rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>MESA</b></td>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Loja</b></td>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Internet</b></td>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Total</b></td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
            
                $sql_formas_pg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
                $res_formas_pg = mysql_query($sql_formas_pg);
                $num_formas_pg = mysql_num_rows($res_formas_pg);
            
                $total_geral_forma_pg_tel = 0;
                $total_geral_forma_pg_net = 0;
                $total_geral_forma_pg = 0;
                
                for ($a = 0; $a < $num_formas_pg; $a++)
                {
                    $obj_formas_pg = mysql_fetch_object($res_formas_pg);
                
                    $rel_fechamento .= '<tr>' . "\r\n";
                    $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>' . $obj_formas_pg->forma_pg . '</b></td>' . "\r\n";
                    

                    //$sqlecho = "SELECT SUM(valor_total) AS soma_tel FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.forma_pg = '".$obj_formas_pg->forma_pg."' AND p.origem_pedido = 'TEL' AND p.situacao NOT IN ('CANCELADO') AND p.cod_pizzarias = $cod_pizzarias";
                    
                    //echo "<br/>".$sqlecho."<br/>";
/*
                    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_mesa FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.forma_pg = '".$obj_formas_pg->forma_pg."' AND p.origem_pedido = 'MESA' AND p.situacao NOT IN ('CANCELADO') AND p.cod_pizzarias = $cod_pizzarias", $conexao);
                    //echo "SELECT SUM(valor_total) AS soma_mesa FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.forma_pg = '".$obj_formas_pg->forma_pg."' AND p.origem_pedido = 'MESA' AND p.situacao NOT IN ('CANCELADO') AND p.cod_pizzarias = $cod_pizzarias";
                    $soma_mesa = $objBuscaPedidosSoma->soma_mesa;
                    $total_geral_forma_pg_mesa += $soma_mesa;
                    $rel_fechamento .= '<td align="center">' . bd2moeda($soma_mesa) . '</td>';
*/

                    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'TEL' AND p.situacao NOT IN ('CANCELADO') AND p.cod_pizzarias = $cod_pizzarias", $conexao);
                    $soma_tel = $objBuscaPedidosSoma->soma_tel;
                    $total_geral_forma_pg_tel += $soma_tel;
                    $rel_fechamento .= '<td align="center">' . bd2moeda($soma_tel) . '</td>';

                    //$sqlecho = "SELECT SUM(valor_total) AS soma_net FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.forma_pg = '".$obj_formas_pg->forma_pg."' AND p.origem_pedido = 'NET' AND p.situacao NOT IN ('CANCELADO') AND p.cod_pizzarias = $cod_pizzarias";
                    //echo "<br/>".$sqlecho."<br/>";
                    
                    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_net FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'NET' AND p.situacao NOT IN ('CANCELADO') AND p.cod_pizzarias = $cod_pizzarias", $conexao);
                    
                    $soma_net = $objBuscaPedidosSoma->soma_net;
                    $total_geral_forma_pg_net += $soma_net;
                    $rel_fechamento .= '<td align="center">' . bd2moeda($soma_net) . '</td>' . "\r\n";
                    
                    $rel_fechamento .= '<td align="center"><b>' . bd2moeda($soma_tel + $soma_net + $soma_mesa) . '</b></td>' . "\r\n";
                    $rel_fechamento .= '</tr>' . "\r\n";
                    
                    $total_geral_forma_pg += ($soma_tel + $soma_net + $soma_mesa);
                }

                //die();

                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center">&nbsp;</td>' . "\r\n";
                //$rel_fechamento .= '<td align="center">' . bd2moeda($total_geral_forma_pg_mesa) . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda($total_geral_forma_pg_tel) . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda($total_geral_forma_pg_net) . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center"><b>' . bd2moeda($total_geral_forma_pg) . '</b></td>' . "\r\n";
                $rel_fechamento .= '</tr>';
                
          		$rel_fechamento .= '</table><br><br><br><br>' . "\r\n";
          		
                
                // Relatório de quantidade vendidas
                $sql_buscar_pizzarias = " AND p.cod_pizzarias = " . $obj_buscar_pizzaria->cod_pizzarias;
                $sql_data_hora_pedido = " AND p.data_hora_pedido BETWEEN '$data_inicial' AND '$data_final' ";
                $sql_data_hora_pedido_sem_ant = " AND p.data_hora_pedido BETWEEN '" . date('Y-m-d H:i:s', strtotime('-1 week', strtotime($data_inicial))) . "' AND '" . date('Y-m-d H:i:s', strtotime('-1 week', strtotime($data_final))) . "' ";
                $sql_situacao_pedido = " AND p.situacao NOT IN ('CANCELADO') ";
                
                
                $rel_fechamento .= '<table border="1" cellpadding="0" cellspacing="0" width="500" align="center">' . "\r\n";
                $rel_fechamento .= '<tr><td colspan="4" style="background-color: #e5e5e5;" align="center"><b>Quantidades Vendidas (Débitos + Baixados)</b></td></tr>' . "\r\n";
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;">&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Loja</b></td>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Internet</b></td>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Total</b></td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                    
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
    	            
    	            $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "' AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    	            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    	            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    	            $total_pizza_net = $obj_quant_pizzas->total_net;
    	            
    	            $total_pizza = $total_pizza_tel + $total_pizza_net;
    	            $total_geral += $total_pizza;
    	            
    	            $rel_fechamento .= '<tr>' . "\r\n";
    	            $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_tamanhos->tamanho) . ' - vendidas</b></td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza_tel . '</td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza_net . '</td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza . '</td>' . "\r\n";
    	            $rel_fechamento .= '</tr>' . "\r\n";
                }
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_geral . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>';
                
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
    	            
    	            $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "' AND pp.promocional = 0 AND pp.fidelidade = 0 AND p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido_sem_ant $sql_situacao_pedido";
    	            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    	            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    	            $total_pizza_net = $obj_quant_pizzas->total_net;
    	            
    	            $total_pizza = $total_pizza_tel + $total_pizza_net;
    	            $total_geral += $total_pizza;
    	            
    	            $rel_fechamento .= '<tr>' . "\r\n";
    	            $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_tamanhos->tamanho) . ' - vendidas sem. ant.</b></td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza_tel . '</td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza_net . '</td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza . '</td>' . "\r\n";
    	            $rel_fechamento .= '</tr>' . "\r\n";
                }
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_geral . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
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
    	            //echo "<Br>sql_quant_pizzas: ".$sql_quant_pizzas;

    	            $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "'  AND pp.promocional = 1 AND pp.fidelidade = 0 AND p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    	            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    	            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    	            $total_pizza_net = $obj_quant_pizzas->total_net;
    	            
    	            $total_pizza = $total_pizza_tel + $total_pizza_net;
    	            $total_geral += $total_pizza;
    	            
    	            $rel_fechamento .= '<tr>' . "\r\n";
    	            $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_tamanhos->tamanho) . ' - promocional</b></td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza_tel . '</td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza_net . '</td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza . '</td>' . "\r\n";
    	            $rel_fechamento .= '</tr>' . "\r\n";
                }
            	
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_geral . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
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
    	            
    	            $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = '" . $obj_buscar_tamanhos->cod_tamanhos . "'  AND pp.promocional = 1 AND pp.fidelidade = 0 AND p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido_sem_ant $sql_situacao_pedido";
    	            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
    	            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
    	            $total_pizza_net = $obj_quant_pizzas->total_net;
    	            
    	            $total_pizza = $total_pizza_tel + $total_pizza_net;
    	            $total_geral += $total_pizza;
    	            
    	            $rel_fechamento .= '<tr>' . "\r\n";
    	            $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_tamanhos->tamanho) . ' - promocional sem. ant.</b></td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza_tel . '</td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza_net . '</td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_pizza . '</td>' . "\r\n";
    	            $rel_fechamento .= '</tr>' . "\r\n";
                }
            	
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_geral . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                // Frações
                $sql_quant_fracoes_salgada = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'TEL' AND pi.tipo = 'Salgado' AND pp.promocional = 0 AND pp.fidelidade = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
                $res_quant_fracoes_salgada = mysql_query($sql_quant_fracoes_salgada);
                $obj_quant_fracoes_salgada = mysql_fetch_object($res_quant_fracoes_salgada);
                $total_quant_fracoes_salgada_tel = $obj_quant_fracoes_salgada->total_tel;
                
                $sql_quant_fracoes_salgada = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'NET' AND pi.tipo = 'Salgado' AND pp.promocional = 0 AND pp.fidelidade = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
                $res_quant_fracoes_salgada = mysql_query($sql_quant_fracoes_salgada);
                $obj_quant_fracoes_salgada = mysql_fetch_object($res_quant_fracoes_salgada);
                $total_quant_fracoes_salgada_net = $obj_quant_fracoes_salgada->total_net;
            
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>'.TIPO_PRODUTOS.' Salgadas</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_quant_fracoes_salgada_tel . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_quant_fracoes_salgada_net . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . ($total_quant_fracoes_salgada_tel + $total_quant_fracoes_salgada_net) . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";

                
                $sql_quant_fracoes_doce = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'TEL' AND pi.tipo = 'Doce' AND pp.promocional = 0 AND pp.fidelidade = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
                $res_quant_fracoes_doce = mysql_query($sql_quant_fracoes_doce);
                $obj_quant_fracoes_doce = mysql_fetch_object($res_quant_fracoes_doce);
                $total_quant_fracoes_doce_tel = $obj_quant_fracoes_doce->total_tel;
                
                $sql_quant_fracoes_doce = "SELECT SUM(TRUNCATE(pf.fracao / pp.quant_fracao, 2)) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'NET' AND pi.tipo = 'Doce' AND pp.promocional = 0 AND pp.fidelidade = 0 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
                $res_quant_fracoes_doce = mysql_query($sql_quant_fracoes_doce);
                $obj_quant_fracoes_doce = mysql_fetch_object($res_quant_fracoes_doce);
                $total_quant_fracoes_doce_net = $obj_quant_fracoes_doce->total_net;
            
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>'.TIPO_PRODUTOS.' Doces</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_quant_fracoes_doce_tel . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_quant_fracoes_doce_net . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . ($total_quant_fracoes_doce_tel + $total_quant_fracoes_doce_net) . '</td>' . "\r\n";
            	  $rel_fechamento .= '</tr>' . "\r\n";
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                    
                
                // Quant. bordas vendidas
                $sql_quant_bordas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 0 AND pb.fidelidade = 0 AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_quant_bordas = mysql_query($sql_quant_bordas);
                $obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
                $total_borda_tel = $obj_quant_bordas->total_tel;
                
                $sql_quant_bordas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 0 AND pb.fidelidade = 0 AND p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_quant_bordas = mysql_query($sql_quant_bordas);
                $obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
                $total_borda_net = $obj_quant_bordas->total_net;
                
                $total_borda = $total_borda_tel + $total_borda_net;
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Quant. Bordas Vendidas</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_borda_tel . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_borda_net . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_borda . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                if (PRODUTO_USA_BORDA != "N")
                {
                  // Quant. bordas promo
                  $sql_quant_bordas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 1 AND pb.fidelidade = 0 AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                  $res_quant_bordas = mysql_query($sql_quant_bordas);
                  $obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
                  $total_borda_tel = $obj_quant_bordas->total_tel;
                  
                  $sql_quant_bordas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 1 AND pb.fidelidade = 0 AND p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                  $res_quant_bordas = mysql_query($sql_quant_bordas);
                  $obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
                  $total_borda_net = $obj_quant_bordas->total_net;
                  
                  $total_borda = $total_borda_tel + $total_borda_net;
                  
                  $rel_fechamento .= '<tr>' . "\r\n";
                  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Quant. Bordas Promocionais</b></td>' . "\r\n";
                  $rel_fechamento .= '<td align="center">' . $total_borda_tel . '</td>' . "\r\n";
                  $rel_fechamento .= '<td align="center">' . $total_borda_net . '</td>' . "\r\n";
                  $rel_fechamento .= '<td align="center">' . $total_borda . '</td>' . "\r\n";
                  $rel_fechamento .= '</tr>' . "\r\n";
                }
                
                
                // Quant. bordas fidelidade
                $sql_quant_bordas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 0 AND pb.fidelidade = 1 AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_quant_bordas = mysql_query($sql_quant_bordas);
                $obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
                $total_borda_tel = $obj_quant_bordas->total_tel;
                
                $sql_quant_bordas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 0 AND pb.fidelidade = 0 AND p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_quant_bordas = mysql_query($sql_quant_bordas);
                $obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
                $total_borda_net = $obj_quant_bordas->total_net;
                
                $total_borda = $total_borda_tel + $total_borda_net;
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Quant. Bordas Fidelidade</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_borda_tel . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_borda_net . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_borda . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                    
                
                // Quant. adicionais (gergelim)
                $sql_quant_adicionais = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_adicionais pa ON (p.cod_pedidos = pa.cod_pedidos) WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_quant_adicionais = mysql_query($sql_quant_adicionais);
                $obj_quant_adicionais = mysql_fetch_object($res_quant_adicionais);
                $total_adicionais_tel = $obj_quant_adicionais->total_tel;
                
                $sql_quant_adicionais = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_adicionais pa ON (p.cod_pedidos = pa.cod_pedidos) WHERE p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_quant_adicionais = mysql_query($sql_quant_adicionais);
                $obj_quant_adicionais = mysql_fetch_object($res_quant_adicionais);
                $total_adicionais_net = $obj_quant_adicionais->total_net;
                
                $total_adicionais = $total_adicionais_tel + $total_adicionais_net;
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Quant. Gergelim</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_adicionais_tel . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_adicionais_net . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_adicionais . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";

                
                // Quant. indredientes não padrão (adicionais)
                $sql_ingred_adicionais = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) WHERE p.origem_pedido = 'TEL' AND pi.ingrediente_padrao = 0 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_ingred_adicionais = mysql_query($sql_ingred_adicionais);
                $obj_ingred_adicionais = mysql_fetch_object($res_ingred_adicionais);
                $total_ingred_adicionais_tel = $obj_ingred_adicionais->total_tel;
                
                $sql_ingred_adicionais = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) WHERE p.origem_pedido = 'NET' AND pi.ingrediente_padrao = 0 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_ingred_adicionais = mysql_query($sql_ingred_adicionais);
                $obj_ingred_adicionais = mysql_fetch_object($res_ingred_adicionais);
                $total_ingred_adicionais_net = $obj_ingred_adicionais->total_net;
                
                $total_ingred_adicionais = $total_ingred_adicionais_tel + $total_ingred_adicionais_net;
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Quant. de Ingredientes Adicionais</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_ingred_adicionais_tel . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_ingred_adicionais_net . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $total_ingred_adicionais . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                // Valor médio por pedido
                $sql_buscar_media_valor = "SELECT AVG(p.valor_total) AS media_total FROM ipi_pedidos p WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_buscar_media_valor = mysql_query($sql_buscar_media_valor);
                $obj_buscar_media_valor = mysql_fetch_object($res_buscar_media_valor);
                $media_valor_tel = $obj_buscar_media_valor->media_total;
                
                $sql_buscar_media_valor = "SELECT AVG(p.valor_total) AS media_total FROM ipi_pedidos p WHERE p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_buscar_media_valor = mysql_query($sql_buscar_media_valor);
                $obj_buscar_media_valor = mysql_fetch_object($res_buscar_media_valor);
                $media_valor_net = $obj_buscar_media_valor->media_total;
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Valor Médio por Pedido</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda($media_valor_tel) . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda($media_valor_net) . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda(($media_valor_tel + $media_valor_net) / 2) . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                
                // Média de pizzas por pedido
                $sql_media_pizzas = "SELECT AVG(contagem) AS media FROM (SELECT (SELECT COUNT(*) FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos = p.cod_pedidos AND pp.promocional = 0 AND pp.fidelidade = 0) AS contagem FROM ipi_pedidos p WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido) AS t1";
                $res_media_pizzas = mysql_query($sql_media_pizzas);
                $obj_media_pizzas = mysql_fetch_object($res_media_pizzas);
                $media_pizzas_tel = $obj_media_pizzas->media;
                
                $sql_media_pizzas = "SELECT AVG(contagem) AS media FROM (SELECT (SELECT COUNT(*) FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos = p.cod_pedidos AND pp.promocional = 0 AND pp.fidelidade = 0) AS contagem FROM ipi_pedidos p WHERE p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido) AS t1";
                $res_media_pizzas = mysql_query($sql_media_pizzas);
                $obj_media_pizzas = mysql_fetch_object($res_media_pizzas);
                $media_pizzas_net = $obj_media_pizzas->media;
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Média de '.TIPO_PRODUTOS.' Vendidas por Pedidos</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda($media_pizzas_tel) . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda($media_pizzas_net) . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda(($media_pizzas_tel + $media_pizzas_net) / 2) . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td>&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";

                
                
                
                // Número de Tickets
                $sql_num_tickets = "SELECT count(cod_pedidos) as contagem, sum(valor) as total, (sum(valor)/count(cod_pedidos)) as ticket_medio FROM ipi_pedidos p WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
                $res_num_tickets = mysql_query($sql_num_tickets);
                $obj_num_tickets = mysql_fetch_object($res_num_tickets);
                $num_tickets_tel = $obj_num_tickets->contagem;
                $valor_tickets_medio_tel = $obj_num_tickets->ticket_medio;
                
                $sql_num_tickets = "SELECT count(cod_pedidos) as contagem, sum(valor) as total, (sum(valor)/count(cod_pedidos)) as ticket_medio FROM ipi_pedidos p WHERE p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
                $res_num_tickets = mysql_query($sql_num_tickets);
                $obj_num_tickets = mysql_fetch_object($res_num_tickets);
                $num_tickets_net = $obj_num_tickets->contagem;
                $valor_tickets_medio_net = $obj_num_tickets->ticket_medio;

                $sql_ticket_medio = "SELECT (sum(valor)/count(cod_pedidos)) as ticket_medio FROM ipi_pedidos p WHERE 1=1 $sql_buscar_pizzarias $sql_buscar_clientes $sql_data_hora_pedido $sql_situacao_pedido";
                $res_ticket_medio = mysql_query($sql_ticket_medio);
                $obj_ticket_medio = mysql_fetch_object($res_ticket_medio);
                $valor_ticket_medio = $obj_ticket_medio->ticket_medio;

                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Número de Tickets</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $num_tickets_tel . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $num_tickets_net . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . ( $num_tickets_tel + $num_tickets_net ) . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Valor Médio dos Tickets</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda($valor_tickets_medio_tel) . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda($valor_tickets_medio_net) . '</td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . bd2moeda($valor_ticket_medio) . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                
                $rel_fechamento .= '</table><br><br><br><br>' . "\r\n";  
                
                
                //Elias promocoes pedido
                $rel_fechamento .= '<table border="1" cellpadding="0" cellspacing="0" width="500" align="center">';
                $rel_fechamento .= '<tr><td colspan="5" align="center" style="background-color: #e5e5e5;"><b>Promoções</b></td></tr>';
                
    			$rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;">&nbsp;</td>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>TEL</b></td>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>NET</b></td>' . "\r\n";
                $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>TOTAL</b></td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
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
    	            $sql_promocoes = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_motivo_promocoes mp ON(pp.cod_motivo_promocoes=mp.cod_motivo_promocoes) WHERE p.origem_pedido = 'NET' AND mp.cod_motivo_promocoes=".$obj_buscar_promocoes->cod_motivo_promocoes." $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    	            $res_promocoes = mysql_query($sql_promocoes);
    	            $obj_promocoes = mysql_fetch_object($res_promocoes);
    	            $total_promocoes_net += $obj_promocoes->total_net;
    	            
    	            $sql_promocoes = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON(p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_motivo_promocoes mp ON(pb.cod_motivo_promocoes=mp.cod_motivo_promocoes) WHERE p.origem_pedido = 'NET' AND mp.cod_motivo_promocoes=".$obj_buscar_promocoes->cod_motivo_promocoes." $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    	            $res_promocoes = mysql_query($sql_promocoes);
    	            $obj_promocoes = mysql_fetch_object($res_promocoes);
    	            $total_promocoes_net += $obj_promocoes->total_net;
    	            
    	            $sql_promocoes = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bebidas pb ON(p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_motivo_promocoes mp ON(pb.cod_motivo_promocoes=mp.cod_motivo_promocoes) WHERE p.origem_pedido = 'NET' AND mp.cod_motivo_promocoes=".$obj_buscar_promocoes->cod_motivo_promocoes." $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
    	            $res_promocoes = mysql_query($sql_promocoes);
    	            $obj_promocoes = mysql_fetch_object($res_promocoes);
    	            $total_promocoes_net += $obj_promocoes->total_net;
    	            
    	            $total_promocoes = ($total_promocoes_tel+$total_promocoes_net);
    	            
    	            $rel_fechamento .= '<tr>' . "\r\n";
    	            $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_promocoes->motivo_promocao) . '</b></td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_promocoes_tel . '</td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_promocoes_net . '</td>' . "\r\n";
    	            $rel_fechamento .= '<td align="center">' . $total_promocoes . '</td>' . "\r\n";
    	            $rel_fechamento .= '</tr>' . "\r\n";
                	
                }

                $rel_fechamento .= '</table><br><br><br><br>' . "\r\n";
                
                
                $rel_fechamento .= '<table border="1" cellpadding="0" cellspacing="0" width="500" align="center">';
                $rel_fechamento .= '<tr><td colspan="2" align="center" style="background-color: #e5e5e5;"><b>Outros Dados</b></td></tr>';
                
                $sql_buscar_cupons = "SELECT COUNT(*) AS total_cupons FROM ipi_pedidos p INNER JOIN ipi_pedidos_ipi_cupons pc ON (p.cod_pedidos = pc.cod_pedidos) INNER JOIN ipi_cupons c ON (pc.cod_cupons = c.cod_cupons) WHERE c.produto = 'PIZZA' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_buscar_cupons = mysql_query($sql_buscar_cupons);
                $obj_buscar_cupons = mysql_fetch_object($res_buscar_cupons);

                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td align="center" style="background-color: #e5e5e5;"><b>Cupons de '.TIPO_PRODUTO.'</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $obj_buscar_cupons->total_cupons . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
    			$sql_buscar_cupons = "SELECT COUNT(*) AS total_cupons FROM ipi_pedidos p INNER JOIN ipi_pedidos_ipi_cupons pc ON (p.cod_pedidos = pc.cod_pedidos) INNER JOIN ipi_cupons c ON (pc.cod_cupons = c.cod_cupons) WHERE c.produto = 'BORDA' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_buscar_cupons = mysql_query($sql_buscar_cupons);
                $obj_buscar_cupons = mysql_fetch_object($res_buscar_cupons);
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td align="center" style="background-color: #e5e5e5;"><b>Cupons de Borda</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $obj_buscar_cupons->total_cupons . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                $sql_buscar_pontos_total = "SELECT SUM(p.pontos_fidelidade_total) AS pontos_total FROM ipi_pedidos p WHERE 1=1 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                $res_buscar_pontos_total = mysql_query($sql_buscar_pontos_total);
                $obj_buscar_pontos_total = mysql_fetch_object($res_buscar_pontos_total);
                
                $rel_fechamento .= '<tr>' . "\r\n";
                $rel_fechamento .= '<td align="center" style="background-color: #e5e5e5;"><b>Pontos de Fidelidade Utilizados</b></td>' . "\r\n";
                $rel_fechamento .= '<td align="center">' . $obj_buscar_pontos_total->pontos_total . '</td>' . "\r\n";
                $rel_fechamento .= '</tr>' . "\r\n";
                
                $rel_fechamento .= '</table><br><br><br><br>' . "\r\n";
                
                /*
                
                // FIXME cod_enquete forçado 1 
                $cod_enquetes = 1;
                
                // FIXME cod_enquete_respostas forçado para muito satisfeito(a) e satisfeito(a)
                $cod_enquete_respostas = '8,9,12,13,16,17';
                
                $rel_fechamento .= '<table border="1" cellpadding="0" cellspacing="0" width="500" align="center">' . "\r\n";
                $rel_fechamento .= '<tr><td colspan="2" align="center" style="background-color: #e5e5e5;"><b>Enquete</b></td></tr>' . "\r\n";
                
                $sql_buscar_enquete_perguntas = "SELECT * FROM ipi_enquete_perguntas WHERE cod_enquetes = '$cod_enquetes' and pergunta_pessoal = 0 ORDER BY pergunta";
                $res_buscar_enquete_perguntas = mysql_query($sql_buscar_enquete_perguntas);
                
                while($obj_buscar_enquete_perguntas = mysql_fetch_object($res_buscar_enquete_perguntas))
                {
                    //Mudado para o Sr. Mendes ver a média móvel
                    //$sql_buscar_enquete_respostas = "SELECT COUNT(*) AS total FROM ipi_enquete_respostas er INNER JOIN ipi_clientes_ipi_enquete_respostas ere ON (er.cod_enquete_respostas = ere.cod_enquete_respostas) INNER JOIN ipi_pedidos cp ON (ere.cod_pedidos = cp.cod_pedidos) WHERE er.cod_enquete_perguntas = '" . $obj_buscar_enquete_perguntas->cod_enquete_perguntas . "' AND er.cod_enquete_respostas IN ($cod_enquete_respostas) AND cp.data_hora_pedido BETWEEN DATE_SUB('$data_final', INTERVAL 7 DAY) AND '$data_final'";
                    $sql_buscar_enquete_respostas = "SELECT COUNT(*) AS total FROM ipi_enquete_respostas er INNER JOIN ipi_clientes_ipi_enquete_respostas ere ON (er.cod_enquete_respostas = ere.cod_enquete_respostas) INNER JOIN ipi_pedidos p on p.cod_pedidos = ere.cod_pedidos WHERE er.cod_enquete_perguntas = '" . $obj_buscar_enquete_perguntas->cod_enquete_perguntas . "' $sql_buscar_pizzarias AND er.cod_enquete_respostas IN ($cod_enquete_respostas) AND ere.data_hora_resposta BETWEEN DATE_SUB('$data_final', INTERVAL 7 DAY) AND '$data_final'";
                    
                    $res_buscar_enquete_respostas = mysql_query($sql_buscar_enquete_respostas);
                    $obj_buscar_enquete_respostas = mysql_fetch_object($res_buscar_enquete_respostas);
                    $parcela = $obj_buscar_enquete_respostas->total;
                    
                    //Mudado para o Sr. Mendes ver a média móvel
                    //$sql_buscar_enquete_respostas = "SELECT COUNT(*) AS total FROM ipi_enquete_respostas er INNER JOIN ipi_clientes_ipi_enquete_respostas ere ON (er.cod_enquete_respostas = ere.cod_enquete_respostas) INNER JOIN ipi_pedidos cp ON (ere.cod_pedidos = cp.cod_pedidos) WHERE er.cod_enquete_perguntas = '" . $obj_buscar_enquete_perguntas->cod_enquete_perguntas . "' AND cp.data_hora_pedido BETWEEN DATE_SUB('$data_final', INTERVAL 7 DAY) AND '$data_final'";
                    $sql_buscar_enquete_respostas = "SELECT COUNT(*) AS total FROM ipi_enquete_respostas er INNER JOIN ipi_clientes_ipi_enquete_respostas ere ON (er.cod_enquete_respostas = ere.cod_enquete_respostas) INNER JOIN ipi_pedidos p on p.cod_pedidos = ere.cod_pedidos WHERE er.cod_enquete_perguntas = '" . $obj_buscar_enquete_perguntas->cod_enquete_perguntas . "' $sql_buscar_pizzarias AND ere.data_hora_resposta BETWEEN DATE_SUB('$data_final', INTERVAL 7 DAY) AND '$data_final'";
                    
                    $res_buscar_enquete_respostas = mysql_query($sql_buscar_enquete_respostas);
                    $obj_buscar_enquete_respostas = mysql_fetch_object($res_buscar_enquete_respostas);
                    $total = $obj_buscar_enquete_respostas->total;
                    
                    $rel_fechamento .= '<tr>' . "\r\n";
                    $rel_fechamento .= '<td align="center" style="background-color: #e5e5e5;"><b>' . bd2texto($obj_buscar_enquete_perguntas->pergunta) . '</b></td>' . "\r\n";
                    $rel_fechamento .= '<td align="center">' . bd2moeda(round(($parcela / $total) * 100, 2)) . '%</td>' . "\r\n";
                    $rel_fechamento .= '</tr>' . "\r\n";
                }
                */
                
                $rel_fechamento .= '</table>' . "\r\n";
                
                $res_enviar_email = true;
                
    			      //$res_enviar_email &= enviar_email (EMAIL_PRINCIPAL, 'rubens@osmuzzarellas.com.br', $assunto, $rel_fechamento, 'neutro');
                //$res_enviar_email &= enviar_email (EMAIL_PRINCIPAL, 'suzana@osmuzzarellas.com.br', $assunto, $rel_fechamento, 'neutro');
                //$res_enviar_email &= enviar_email (EMAIL_PRINCIPAL, 'rogerio@osmuzzarellas.com.br', $assunto, $rel_fechamento, 'neutro');

    			      
        				
        				
        		$emails_recebimento = 'thiago@internetsistemas.com.br';
        	    if ($obj_buscar_pizzaria->emails_diretoria)
        	    {
        		   $emails_recebimento .= ",".$obj_buscar_pizzaria->emails_diretoria;
        	    }
                //$emails_recebimento = ',filipegranato@internetsistemas.com.br';

                $arr_aux = array();
                $arr_aux['cod_pedidos'] = 0;
                $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
                $arr_aux['cod_clientes'] = 0;
                $arr_aux['cod_pizzarias'] = $obj_buscar_pizzaria->cod_pizzarias;
                $arr_aux['tipo'] = 'FECHAMENTO_CAIXA';
                $res_enviar_email &= enviar_email (EMAIL_PRINCIPAL, $emails_recebimento, $assunto, $rel_fechamento, $arr_aux, 'neutro');
                //echo "<br>res_enviar_email: ".$res_enviar_email;
                
                $conexao = conectar_bd();
                
                if($res_enviar_email)
                {
                    echo '<br><br><br><center><font color="#1A498F" size="4">O CAIXA FOI FECHADO COM SUCESSO E O RELATÓRIO FOI ENVIADO.</font></center>';
                }
                else
                {
                    echo '<br><br><br><center><font color="red" size="4">ERRO!!! O caixa FOI FECHADO, porem o e-mail com relatório não foi entregue, por favor, comunique a equipe de suporte.</font></center>';
                }
            }
            else
            {
                echo '<br><br><br><center><font color="red" size="4">ERRO!!! O caixa não foi fechado, por favor, avise a equipe de suporte.</font></center>';
            }
            
            desconectabd($conexao);
        }
        else
        {
          echo '<br><br><br><center><font color="red" size="4">ERRO!!! O caixa solicitado já está FECHADO! <br />Foi fechado pelo usuário: '.$obj_buscar_caixa->usuario.' as '.bd2datahora($obj_buscar_caixa->data_hora_fechamento).'.</font></center>';
        }
            
        break;
}

if(($acao == '') || ($acao == 'escolher_pizzaria')):

$conexao = conectabd();

?>

<script>
function fechar_caixa()
{
	if(validaRequeridos(document.frmIncluir))
	{
		if(confirm('TEM CERTEZA QUE DESEJA FECHAR O CAIXA ABERTO?\n\n' + $('caixa_extenso').value )) 
		{ 
			$('botao_submit').disabled = true;
			$('barra_loader').setStyle("display","block");
			$('botao_submit').setStyle("backgroundColor","#CCCCCC");
			$('botao_submit').setStyle("border-color","#999999");
			document.frmIncluir.submit();
		} 
		else
		{
			$('botao_submit').disabled = false;
			$('barra_loader').setStyle("display","none");
			$('botao_submit').setStyle("backgroundColor","#EB8612");
			$('botao_submit').setStyle("border-color","#D44E08");
		}
	}
}
</script>

<form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
<table align="center" class="caixa" cellpadding="0" cellspacing="0">

<?

// Busca o último caixa aberto de acordo com as pizzarias do perfil

$sql_buscar_caixas = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) WHERE p.cod_pizzarias = '" . $_SESSION['usuario']['cod_pizzarias'][0] . "' AND c.situacao = 'ABERTO'";
$res_buscar_caixas = mysql_query($sql_buscar_caixas);
$num_buscar_caixas = mysql_num_rows($res_buscar_caixas);

if (($acao == 'escolher_pizzaria') && (validaVarPost('cod_caixa') > 0))
{
    //echo "<br>AKI1";
    $cod_caixa = validaVarPost('cod_caixa');
    
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) WHERE c.cod_caixa = '$cod_caixa'";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
    
    echo '<tr><td class="tdbl tdbt tdbr sep"><font color="red"><b>'.ucfirst(TIPO_EMPRESA).': ' . $obj_buscar_pizzarias->cod_pizzarias . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</b></font></td></tr>';
    echo '<input type="hidden" name="cod_caixa" value="' . $cod_caixa . '">';
    echo '<input type="hidden" name="caixa_extenso" id="caixa_extenso" value="Pizzaria: ' . $obj_buscar_pizzarias->cod_pizzarias . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )">';
    
    $cod_pizzarias = $obj_buscar_pizzarias->cod_pizzarias;
    $data_inicial = $obj_buscar_pizzarias->data_hora_abertura;
    $data_final = date('Y-m-d H:i:s');
}
else if (count($_SESSION['usuario']['cod_pizzarias']) > 1)
{
    //echo "<br>AKI2";
    echo '<tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_caixa">'.ucfirst(TIPO_EMPRESAS).'</label></td></tr>';
    echo '<tr><td class="tdbl tdbr sep"><select class="requerido" name="cod_caixa" id="cod_caixa" onchange="javascript: document.frmIncluir.acao.value = \'escolher_pizzaria\'; document.frmIncluir.submit(); ">';
    
    echo '<option value="0">Escolha a '.TIPO_EMPRESA.'</option>';
    
    foreach ($_SESSION['usuario']['cod_pizzarias'] as $cod_pizzarias_valor)
    {
        $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) LEFT JOIN nuc_usuarios u ON () WHERE p.cod_pizzarias = '$cod_pizzarias_valor' AND c.situacao = 'ABERTO'";
        $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
        $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
        
        echo '<option value="' . $obj_buscar_pizzarias->cod_caixa . '">' . $cod_pizzarias_valor . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</option>';
    }
    
    echo '</select></td></tr>';
}
else if ($num_buscar_caixas >= 1)
{
    //echo "<br>AKI2";
    echo '<tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_caixa">Selecione o Caixa</label></td></tr>';
    echo '<tr><td class="tdbl tdbr sep"><select class="requerido" name="cod_caixa" id="cod_caixa" onchange="javascript: document.frmIncluir.acao.value = \'escolher_pizzaria\'; document.frmIncluir.submit(); ">';
    
    echo '<option value="0">Escolha o caixa</option>';
    
    foreach ($_SESSION['usuario']['cod_pizzarias'] as $cod_pizzarias_valor)
    {
        $sql_buscar_pizzarias = "SELECT c.cod_caixa, c.data_hora_abertura, cf.numero_caixa, u.nome FROM ipi_caixa c LEFT JOIN  ipi_caixas_fisicos cf ON (c.cod_caixas_fisicos = cf.cod_caixas_fisicos) LEFT JOIN nuc_usuarios u ON (u.cod_usuarios = c.cod_usuarios_abertura) WHERE c.cod_pizzarias = '$cod_pizzarias_valor' AND c.situacao = 'ABERTO'";
        $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
        while ($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
        {

            echo '<option value="' . $obj_buscar_pizzarias->cod_caixa . '">' . bd2texto($obj_buscar_pizzarias->numero_caixa) . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</option>';
        }
    }
    
    echo '</select>';
    echo '</td></tr>';
}

else
{
    echo "<br>Nenhum caixa aberto!";
    /*
    //echo "<br>AKI3";
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) WHERE p.cod_pizzarias = '" . $_SESSION['usuario']['cod_pizzarias'][0] . "' AND c.situacao = 'ABERTO'";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
    
    echo '<tr><td class="tdbl tdbt tdbr sep"><font color="red"><b>'.ucfirst(TIPO_EMPRESA).': ' . $_SESSION['usuario']['cod_pizzarias'][0] . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</b></font></td></tr>';
    echo '<input type="hidden" name="cod_caixa" value="' . $obj_buscar_pizzarias->cod_caixa . '">';
    echo '<input type="hidden" name="caixa_extenso" id="caixa_extenso" value="Pizzaria: ' . $obj_buscar_pizzarias->cod_pizzarias . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )">';
    
    $cod_pizzarias = $_SESSION['usuario']['cod_pizzarias'][0];
    $data_inicial = $obj_buscar_pizzarias->data_hora_abertura;
    $data_final = date('Y-m-d H:i:s');
    */
}
if($cod_pizzarias > 0):

?>

<tr>
    <td class="tdbl tdbr set">
    
    <?
    $cod_caixa = validaVarPost('cod_caixa');
    $cod_caixas_fisicos = $obj_buscar_pizzarias->cod_caixas_fisicos;
    //echo "<br>x:: ".$cod_caixas_fisicos;
    // Relatório de formas de pagamento baixados
    $sql_formas_pg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
	$res_formas_pg = mysql_query($sql_formas_pg);
	$num_formas_pg = mysql_num_rows($res_formas_pg);
	

    echo '<table class="listaEdicao" cellpadding="0" cellspacing="0">';
    echo '<tr>';
    echo '<td class="legenda tdbl tdbr"><label  for="">Observação de abertura do caixa:</label> '.$obj_buscar_pizzarias->obs_caixa_abertura.'</td>';
    echo '</tr>';
    echo '</table><br />';

	$rel_fechamento .= '<table class="listaEdicao" cellpadding="0" cellspacing="0" width="500">';


    $rel_fechamento .= '<tr><td colspan="5" style="background-color: #e5e5e5;" align="center"><b>Formas de Pagamento (Baixados)</b></td></tr>';
    $rel_fechamento .= '<tr>';
    $rel_fechamento .= '<td style="background-color: #e5e5e5;">&nbsp;</td>';
    //$rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>MESA</b></td>';
    $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Loja</b></td>';
    $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Internet</b></td>';
    $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Total</b></td>';
    $rel_fechamento .= '</tr>';
	
	for ($a = 0; $a < $num_formas_pg; $a++)
	{
		$obj_formas_pg = mysql_fetch_object($res_formas_pg);
            
		$rel_fechamento .= '<tr>';
		$rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>' . $obj_formas_pg->forma_pg . '</b></td>';

    //$objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(valor_total) AS soma_mesa FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.forma_pg = '".$obj_formas_pg->forma_pg."' AND p.origem_pedido = 'MESA' AND p.situacao = 'BAIXADO' AND p.cod_pizzarias = $cod_pizzarias", $conexao);
    //$soma_mesa = $objBuscaPedidosSoma->soma_mesa;
    //$rel_fechamento .= '<td align="center">' . bd2moeda($soma_mesa) . '</td>';
                
		$objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'TEL' AND p.situacao = 'BAIXADO' AND p.cod_pizzarias = $cod_pizzarias AND cod_caixas_fisicos = '".$cod_caixas_fisicos."'", $conexao);
    
    //echo "<Br><br>SELECT SUM(valor_total) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'TEL' AND p.situacao = 'BAIXADO' AND p.cod_pizzarias = $cod_pizzarias";

		$soma_tel = $objBuscaPedidosSoma->soma_tel;
		$rel_fechamento .= '<td align="center">' . bd2moeda($soma_tel) . '</td>';
                
		$objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_net FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'NET' AND p.situacao = 'BAIXADO' AND p.cod_pizzarias = $cod_pizzarias AND cod_caixas_fisicos = '".$cod_caixas_fisicos."'", $conexao);
		$soma_net = $objBuscaPedidosSoma->soma_net;
		$rel_fechamento .= '<td align="center">' . bd2moeda($soma_net) . '</td>';
                
		$rel_fechamento .= '<td align="center"><b>' . bd2moeda($soma_tel + $soma_net + $soma_mesa) . '</b></td>';
		$rel_fechamento .= '</tr>';
	}
             
    $rel_fechamento .= '</table><br>';
    
    // Relatório de formas de pagamento débito
    $res_formas_pg = mysql_query($sql_formas_pg);
	$num_formas_pg = mysql_num_rows($res_formas_pg);
	
	$rel_fechamento .= '<table class="listaEdicao" cellpadding="0" cellspacing="0" width="500">';
    $rel_fechamento .= '<tr><td colspan="5" style="background-color: #e5e5e5;" align="center"><b>Formas de Pagamento (Débito)</b></td></tr>';
    $rel_fechamento .= '<tr>';
    $rel_fechamento .= '<td style="background-color: #e5e5e5;">&nbsp;</td>';
    //$rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>MESA</b></td>';
    $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Loja</b></td>';
    $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Internet</b></td>';
    $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Total</b></td>';
    $rel_fechamento .= '</tr>';
	
	for ($a = 0; $a < $num_formas_pg; $a++)
	{
		$obj_formas_pg = mysql_fetch_object($res_formas_pg);
            
		$rel_fechamento .= '<tr>';
		$rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>' . $obj_formas_pg->forma_pg . '</b></td>';

    //$objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(valor_total) AS soma_mesa FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.forma_pg = '".$obj_formas_pg->forma_pg."' AND p.origem_pedido = 'MESA' AND p.situacao NOT IN ('BAIXADO', 'CANCELADO') AND p.cod_pizzarias = $cod_pizzarias", $conexao);
    //$soma_mesa = $objBuscaPedidosSoma->soma_mesa;
    //$rel_fechamento .= '<td align="center">' . bd2moeda($soma_mesa) . '</td>';

		$objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'TEL' AND p.situacao NOT IN ('BAIXADO', 'CANCELADO') AND p.cod_pizzarias = $cod_pizzarias AND cod_caixas_fisicos = '".$cod_caixas_fisicos."'", $conexao);
		$soma_tel = $objBuscaPedidosSoma->soma_tel;
		$rel_fechamento .= '<td align="center">' . bd2moeda($soma_tel) . '</td>';
                
		$objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_net FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'NET' AND p.situacao NOT IN ('BAIXADO', 'CANCELADO') AND p.cod_pizzarias = $cod_pizzarias AND cod_caixas_fisicos = '".$cod_caixas_fisicos."'", $conexao);
		$soma_net = $objBuscaPedidosSoma->soma_net;
		$rel_fechamento .= '<td align="center">' . bd2moeda($soma_net) . '</td>';
                
		$rel_fechamento .= '<td align="center"><b>' . bd2moeda($soma_tel + $soma_net + $soma_mesa) . '</b></td>';
		$rel_fechamento .= '</tr>';
	}
             
    $rel_fechamento .= '</table><br>';
    
    echo $rel_fechamento;
    
    ?>
    
    </td>
</tr>
<?
$sql_buscar_tempo_max = "select max(tempo_entrega) as tempo from ipi_pizzarias_horarios where cod_pizzarias = '".$cod_pizzarias."' and dia_semana = '".date('w')."' ";

$res_buscar_tempo_max = mysql_query($sql_buscar_tempo_max);
$obj_buscar_tempo_max = mysql_fetch_object($res_buscar_tempo_max);
$tempo_max = $obj_buscar_tempo_max->tempo;
?>


<!--
<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="atendente">Colaborador que estava no Caixa?</label></td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <select class="requerido" name="atendente" id="atendente">
      <option value="" ></option>
        <?
        $cod_perfis_fechamento_caixa = (defined('COD_PERFIS_FECHAMENTO_CAIXA') ? COD_PERFIS_FECHAMENTO_CAIXA : '2, 3, 4, 5, 6');
        $sql_buscar_atendentes = "select usu.nome,usu.cod_usuarios from nuc_usuarios usu inner join ipi_pizzarias_nuc_usuarios pu on pu.cod_usuarios = usu.cod_usuarios where situacao='ATIVO' and cod_perfis IN (".$cod_perfis_fechamento_caixa.") and pu.cod_pizzarias = '".$cod_pizzarias."'";
        $res_buscar_atendentes = mysql_query($sql_buscar_atendentes);
        
        while($obj_buscar_atendentes = mysql_fetch_object($res_buscar_atendentes))
        {
            echo "<option value='".$obj_buscar_atendentes->cod_usuarios."'>".$obj_buscar_atendentes->nome."</option>";
        }
      ?>
    </select>
  </td>
</tr>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="tempo_maximo">Qual o maior tempo entrega?</label>(minutos)</td>
</tr>
<tr>
    <td class="tdbl tdbr sep"><input type="text" class="requerido" id="tempo_maximo" name="tempo_maximo" size='3' value="<? echo $tempo_max ; ?>" onkeypress="return ApenasNumero(event);" /></td>
</tr>
<tr>
    <td class="legenda tdbl tdbr"><label for="motivo_gargalo">Qual o motivo passar o tempo máximo padrão?</label></td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
	  <select name="motivo_gargalo" id="motivo_gargalo">
		<option value="" ></option>
				<?
				
				$sql_buscar_motivos = "select * from ipi_caixa_motivos where situacao='ATIVO'";
				$res_buscar_motivos = mysql_query($sql_buscar_motivos);
				
				while($obj_buscar_motivos = mysql_fetch_object($res_buscar_motivos))
				{
					echo "<option value='".$obj_buscar_motivos->cod_motivos."'>".$obj_buscar_motivos->motivo."</option>";
				}
			?>
    </select>
  </td>
</tr>
-->



<tr>
    <td class="legenda tdbl tdbr"><label  for="erro_atendimento">Ocorreu erro no atendimento?</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep"><textarea rows="5" cols="100" id="erro_atendimento" name="erro_atendimento"></textarea></td>
</tr>
<tr>
    <td class="legenda tdbl tdbr"><label  for="erro_cozinha">Ocorreu erro na cozinha?</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep"><textarea rows="5" cols="100" id="erro_cozinha" name="erro_cozinha"></textarea></td>
</tr>
<tr>
    <td class="legenda tdbl tdbr"><label  for="erro_motoboy">Ocorreu erro na entrega/motoboy?</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep"><textarea rows="5" cols="100" id="erro_motoboy" name="erro_motoboy"></textarea></td>
</tr>
<tr>
    <td class="legenda tdbl tdbr"><label  for="erro_sistema">Ocorreu erro no sistema?</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep"><textarea rows="5" cols="100" id="erro_sistema" name="erro_sistema"></textarea></td>
</tr>
<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="valor_contagem">Contagem do Caixa</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep">Houve <select name="sobra_excesso">
			<option value='1'>Sobra</option>
			<option value='-1'>Falta</option> </select> de <input class="requerido" type="text" name="valor_contagem"  id="valor_contagem"  onkeypress="return formataMoeda(this, '.', ',', event)"/>
		</td>
</tr>

<tr>
    <td class="legenda tdbl tdbr"><label  for="obs_caixa">Observações Gerais</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep"><textarea rows="10" cols="100" id="obs_caixa" name="obs_caixa"></textarea></td>
</tr>

<tr>
  <td align="center" class="tdbl tdbb tdbr">
    <div id="barra_loader" style="display: none" align="center">
      Processando...<br />
      <img src="../lib/img/principal/ajax_loader_barra.gif" />
    </div>
    <input name="botao_submit" id="botao_submit" class="botao" type="button" value="Fechar Caixa" onclick="javascript:fechar_caixa();">
  </td>
</tr>

<? else: ?>

<tr>
    <td align="center" class="tdbl tdbb tdbr">&nbsp;</td>
</tr>

<? endif; ?>

</table>

<input type="hidden" name="acao" value="fechar_caixa"></form>

<? 

desconectabd($conexao);

endif;

rodape(); 

?>
