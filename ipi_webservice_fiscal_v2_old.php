<?
require_once ("config.php");
require_once ("bd.php");

require_once ("sys/lib/php/nusoap/nusoap.php");

function testar_conexao($usuario, $senha)
{
    $detalhes = "<testes>";
    $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='3'>TESTE DE SISTEMA FISCAL</linha>";
    $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1'>CONEXAO: OK</linha>";
    $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1'>IMPRESSORA: OK</linha>";
    $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1'>DATA E HORA: " . date('d/m/Y H:i:s') . "</linha>";
    $detalhes .= "</testes>";
    
    return $detalhes;
}

function dados_software($cod_pizzarias, $usuario, $senha)
{
    $con_web = conectabd();
    
    $sql_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias = '$cod_pizzarias'";
    $res_pizzarias = mysql_query($sql_pizzarias);
    $obj_pizzarias = mysql_fetch_object($res_pizzarias);
    
    desconectabd($con_web);
    
    $detalhes = "<detalhes>";
    $detalhes .= "<detalhe chave='cliente' valor='" . NOME_SITE . "'/>";
    $detalhes .= "<detalhe chave='site' valor='" . HOST . "'/>";
    $detalhes .= "<detalhe chave='estabelecimento' valor='" . bd2texto($obj_pizzarias->nome) . "'/>";
    
    // tempo de espera entre o proximo comando de impressão
    $detalhes .= "<detalhe chave='tempo_espera_impressao' valor='40'/>";
    
    // intervalo de consulta no webservice
    $detalhes .= "<detalhe chave='tempo_verificacao' valor='30'/>";
    
    $detalhes .= "<detalhe chave='ultima_versao' valor='5.0.0.3'/>";
    $detalhes .= "<detalhe chave='pacote_instalacao' valor='http://www.internetsistemas.com.br/download/ipizza.zip'/>";
    
    // Conta de SMTP utilizada para envio log
    $detalhes .= "<detalhe chave='smtp' valor='smtp.osmuzzarellas.com.br'/>";
    $detalhes .= "<detalhe chave='smtp_porta' valor='25'/>";
    $detalhes .= "<detalhe chave='smtp_usuario' valor='suporte@osmuzzarellas.com.br'/>";
    $detalhes .= "<detalhe chave='smtp_senha' valor='Sup@Muz98'/>";
    $detalhes .= "<detalhe chave='email_detino_arquivo_log' valor='contato@internetsistemas.com.br'/>";
    
    $detalhes .= "</detalhes>";
    
    return $detalhes;
}

/**
 * Retorna todos os pedidos fiscais pendentes para impressão.
 * 
 * Ficar atento com a regra do agendado (apenas imprime perto do horário de agendamento - 40min. em média)
 *
 * @param int $cod_pizzarias Código da pizzaria
 * @param string $usuario Usuário
 * @param string $senha Senha
 * @return XML com todos os pedidos.
 */
function retorna_todos_pedidos_fiscais($cod_pizzarias, $usuario, $senha)
{
    $con_web = conectabd();
    
    $sql_pedidos_1 = "SELECT * FROM ipi_pedidos p WHERE p.numero_cupom_fiscal = 0 AND p.impressao_fiscal = 1 AND p.cod_pizzarias = '" . $cod_pizzarias . "' AND agendado = 0 AND valor_total > 0";
    $res_pedidos_1 = mysql_query($sql_pedidos_1);
    $num_pedidos_1 = mysql_num_rows($res_pedidos_1);
    
    $sql_pedidos_2 = "SELECT * FROM ipi_pedidos p WHERE p.numero_cupom_fiscal = 0 AND p.impressao_fiscal = 1 AND p.cod_pizzarias = '" . $cod_pizzarias . "' AND agendado = 1 AND valor_total > 0 AND CONCAT(DATE(data_hora_pedido), ' ', horario_agendamento) <= DATE_ADD(NOW(), INTERVAL 120 MINUTE)";
    $res_pedidos_2 = mysql_query($sql_pedidos_2);
    $num_pedidos_2 = mysql_num_rows($res_pedidos_2);
    
    $pedidos = "<pedidos quantidade='" . ($num_pedidos_1 + $num_pedidos_2) . "'>";
    
    for($a = 0; $a < $num_pedidos_1; $a++)
    {
        $obj_pedidos_1 = mysql_fetch_object($res_pedidos_1);
        
        $pedidos .= "<pedido cancelamento='0'>" . $obj_pedidos_1->cod_pedidos . "</pedido>";
    }
    
    for($b = 0; $b < $num_pedidos_2; $b++)
    {
        $obj_pedidos_2 = mysql_fetch_object($res_pedidos_2);
        
        $pedidos .= "<pedido cancelamento='0'>" . $obj_pedidos_2->cod_pedidos . "</pedido>";
    }
    
    $pedidos .= "</pedidos>";
    
    desconectabd($con_web);
    
    return $pedidos;
}

/**
 * Filtra os caracteres inválidos do XML e quebras de linha.
 *
 * @param string $mensagem
 * 
 * @return Mensagem filtrada.
 */
function filtrar_caracteres($mensagem)
{
    $mensagem = str_replace("\r", '', $mensagem);
    $mensagem = str_replace("\n", '', $mensagem);
    
    $mensagem = str_replace('&', '', $mensagem);
    $mensagem = str_replace('<', '', $mensagem);
    $mensagem = str_replace('<', '', $mensagem);
    
    $mensagem = str_replace('"', '', $mensagem);
    $mensagem = str_replace('\'', '', $mensagem);

    return $mensagem;
}

/**
 * Monta o XML do pedido fiscal
 *
 * @param int $cod_pedidos Código do pedido
 * 
 * @return XML de impressão fiscal do pedido
 */
function montar_pedido_fiscal($cod_pedidos)
{
    $con_web = conectabd();
    $diretorio = "debug/fiscal/".$cod_pedidos.".xml";

    if ($cod_pedidos > 0)
    {
        $aliquota = 'FF';
        
        $sql_pedido = "SELECT * FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) WHERE impressao_fiscal = 1 AND numero_cupom_fiscal = 0 AND cod_pedidos = '$cod_pedidos'";
        $res_pedido = mysql_query($sql_pedido);
        $num_pedido = mysql_num_rows($res_pedido);
        $obj_pedido = mysql_fetch_object($res_pedido);
        
        $sql_nota_p = "SELECT * FROM ipi_pedidos_detalhes_pg WHERE CHAVE = 'CPF_NOTA_PAULISTA' AND cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
        $res_nota_p = mysql_query($sql_nota_p);
        $num_nota_p = mysql_num_rows($res_nota_p);
        $obj_nota_p = mysql_fetch_object($res_nota_p);

        $cpf = ($num_nota_p > 0) ? $obj_nota_p->conteudo : '';
        $desconto_total = ($obj_pedido->desconto > 0) ? str_replace(".", ",", sprintf("%.2f", $obj_pedido->desconto)) : str_replace(".", ",", sprintf("%.2f", 0));
        
        /**
         * As formas de pagamento devem ser cadastradas na impressora. Para ver todas basta realizar a impressão de relatório de leitura X.
         * Formas atuais:
         * Dinheiro
         * Cheque
         * Cartao (sem acento)
         * Cheque pre
         * Cheque a vista
         * Ticket
         * Contravale
         */
        switch(strtoupper(bd2texto($obj_pedido->forma_pg)))
        {
            case 'CARTÃO DÉBITO VISA':
            case 'VISANET':
            case 'CARTÃO CRÉDITO VISA':
            case 'CARTÃO CRÉDITO MASTER':
            case 'CARTÃO DÉBITO MASTER':
            case 'MASTERCARDNET':
                $forma_pg = 'CARTAO';
                break;
            case 'CHEQUE':
                $forma_pg = 'CHEQUE';
                break;
            case 'TICKET':
                $forma_pg = 'TICKET';
            default:
            case 'DINHEIRO':
                $forma_pg = 'DINHEIRO';
                break;
        }

        if(($cpf == '000.000.000-00') || ($cpf == '00.000.000.0000/00'))
        {
            $detalhes = "<pedido quantidade=\"$num_pedido\" desconto_total=\"$desconto_total\" cpf=\"\" nome=\"\" forma_pg=\"" . $forma_pg . "\" >";
        }
        else
        {
            $detalhes = "<pedido quantidade=\"$num_pedido\" desconto_total=\"$desconto_total\" cpf=\"$cpf\" nome=\"" . strtoupper(filtrar_caracteres(bd2texto($obj_pedido->nome))) . "\" forma_pg=\"" . $forma_pg . "\" >";
        }
        
        if ($num_pedido > 0)
        {
            // Buscando combos
            $sql_buscar_combos = "SELECT *, pc.preco AS pc_preco FROM ipi_combos c INNER JOIN ipi_pedidos_combos pc ON (c.cod_combos = pc.cod_combos) WHERE pc.cod_pedidos = '$obj_pedido->cod_pedidos'";
            $res_buscar_combos = mysql_query($sql_buscar_combos);
            
            while($obj_buscar_combos = mysql_fetch_object($res_buscar_combos))
            {
                $valor_unitario = $obj_buscar_combos->pc_preco;
                $valor_unitario_desconto = 0;

                $codigo = $obj_buscar_combos->cod_pedidos_combos;
                $descricao = filtrar_caracteres(strtoupper('Combo:' . bd2texto(substr($obj_buscar_combos->nome_combo, 0, 12))));
                $quantidade = 1;
                $valor_unitario = str_replace(".", ",", sprintf("%.2f", $valor_unitario));
                $valor_unitario_desconto = str_replace(".", ",", sprintf("%.2f", $valor_unitario_desconto));
                $valor_imposto = 0;

                $detalhes .= "<item codigo='$codigo' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario_desconto='$valor_unitario_desconto' valor_unitario='$valor_unitario' valor_imposto='$valor_imposto'/>";
            }

            // Buscando pizzas
            $sql_buscar_pizzas = "SELECT *, pp.preco AS pp_preco, pf.preco AS pf_preco, pt.preco AS pt_preco, pp.promocional AS pp_promocional, pp.fidelidade AS pp_fidelidade, pp.combo AS pp_combo FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (pi.cod_pizzas = pt.cod_pizzas AND t.cod_tamanhos = pt.cod_tamanhos AND pt.cod_pizzarias = '".$obj_pedido->cod_pizzarias."') WHERE p.cod_pedidos = '$obj_pedido->cod_pedidos'";
            $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
            
            while ($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
            {
                // FIXME Corrigir o problema de preço zerado para pizzas promocionais.
                $valor_unitario = ($obj_buscar_pizzas->pf_preco > 0) ? $obj_buscar_pizzas->pf_preco :  ($obj_buscar_pizzas->pt_preco / $obj_buscar_pizzas->quant_fracao);

                if(($obj_buscar_pizzas->pp_promocional == 1) || ($obj_buscar_pizzas->pp_fidelidade == 1))
                {
                    $descricao_add = "PF";
                    $valor_unitario_desconto = $valor_unitario;
                }
                else
                {
                    $valor_unitario_desconto = 0;
                    $descricao_add = "N"; 
                }

                if($obj_buscar_pizzas->pp_combo == 0)
                {
                    // Necessitamos retirar o combo no IF porque se retirar no SQL (como na borda e bebida) pode ocorrer do cliente comprar a fração e não ser cobrada na NF
                    $codigo = $obj_buscar_pizzas->cod_pedidos_fracoes;
                    $descricao = filtrar_caracteres(strtoupper('Pizza ' . $descricao_add . ':' . bd2texto(substr($obj_buscar_pizzas->pizza, 0, 12) . ' ' . $obj_buscar_pizzas->fracao . '/' . $obj_buscar_pizzas->quant_fracao)));
                    $quantidade = 1;
                    $aliquota_imposto = $obj_buscar_pizzas->valor_imposto;
                    $valor_imposto = $valor_unitario * ($aliquota_imposto/100);
                    $valor_unitario = str_replace(".", ",", sprintf("%.2f", $valor_unitario));
                    $valor_unitario_desconto = str_replace(".", ",", sprintf("%.2f", $valor_unitario_desconto));

                    $detalhes .= "<item codigo='$codigo' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario_desconto='$valor_unitario_desconto' valor_unitario='$valor_unitario' valor_imposto='$valor_imposto'/>";
                }

                // Se existe valor para fração adicionar no final das pizzas
                if (($obj_buscar_pizzas->fracao == $obj_buscar_pizzas->quant_fracao) && ($obj_buscar_pizzas->pp_preco > 0))
                {
                    //Preço de frações não calcula no imposto por é serviço e no um produto
                    $codigo = $obj_buscar_pizzas->cod_pedidos_pizzas;
                    $descricao = filtrar_caracteres(strtoupper(bd2texto(substr($obj_buscar_pizzas->quant_fracao, 0, 10)) . ' Sabores'));
                    $quantidade = 1;

                    $valor_unitario = $obj_buscar_pizzas->pp_preco;
                    $valor_unitario_desconto = 0;
                    $valor_imposto = 0;

                    $valor_unitario = str_replace(".", ",", sprintf("%.2f", $valor_unitario));
                    $valor_unitario_desconto = str_replace(".", ",", sprintf("%.2f", $valor_unitario_desconto));
                    
                    $detalhes .= "<item codigo='$codigo' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario_desconto='$valor_unitario_desconto' valor_unitario='$valor_unitario' valor_imposto='$valor_imposto'/>";
                }
            }
            
            // Buscando bordas
            $sql_buscar_pizzas = "SELECT *, pb.preco AS pb_preco, tb.preco AS tb_preco, pb.fidelidade AS pb_fidelidade, pb.promocional AS pb_promocional FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_pedidos_bordas pb ON (pp.cod_pedidos = pb.cod_pedidos AND pp.cod_pedidos_pizzas = pb.cod_pedidos_pizzas) INNER JOIN ipi_bordas b ON (pb.cod_bordas = b.cod_bordas) INNER JOIN ipi_tamanhos_ipi_bordas tb ON (tb.cod_bordas = b.cod_bordas AND tb.cod_tamanhos = t.cod_tamanhos AND tb.cod_pizzarias = '".$obj_pedido->cod_pizzarias."') WHERE pb.combo = 0 AND p.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);

            while ($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
            {
                // FIXME Corrigir o problema de preço zerado para bordas promocionais.
                $valor_unitario = ($obj_buscar_pizzas->pb_preco > 0) ? $obj_buscar_pizzas->pb_preco : $obj_buscar_pizzas->tb_preco;

                if(($obj_buscar_pizzas->pb_promocional == 1) || ($obj_buscar_pizzas->pb_fidelidade == 1))
                {
                    $descricao_add = "PF";
                    $valor_unitario_desconto = $valor_unitario;
                }
                else
                {
                    $valor_unitario_desconto = 0;
                    $descricao_add = "N";
                }
                
                $codigo = $obj_buscar_pizzas->cod_pedidos_bordas;
                $descricao = filtrar_caracteres(strtoupper('Borda ' . $descricao_add . ':' . bd2texto(substr($obj_buscar_pizzas->borda, 0, 10))));
                $quantidade = 1;
                $valor_unitario = str_replace(".", ",", sprintf("%.2f", $valor_unitario));
                $valor_unitario_desconto = str_replace(".", ",", sprintf("%.2f", $valor_unitario_desconto));

                $aliquota_imposto = $obj_buscar_pizzas->valor_imposto;
                $valor_imposto = $valor_unitario * ($aliquota_imposto/100);

                $detalhes .= "<item codigo='$codigo' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario_desconto='$valor_unitario_desconto' valor_unitario='$valor_unitario' valor_imposto='$valor_imposto'/>";
            }
            
            // Buscando adicionais
            $sql_buscar_pizzas = "SELECT *, pp.preco AS pp_preco, pa.preco AS pa_preco, ta.preco AS ta_precol, pa.fidelidade AS pa_fidelidade, pa.promocional AS pa_promocional FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_pedidos_adicionais pa ON (pp.cod_pedidos = pa.cod_pedidos AND pp.cod_pedidos_pizzas = pa.cod_pedidos_pizzas) INNER JOIN ipi_adicionais a ON (pa.cod_adicionais = a.cod_adicionais) INNER JOIN ipi_tamanhos_ipi_adicionais ta ON (ta.cod_adicionais = a.cod_adicionais AND ta.cod_tamanhos = t.cod_tamanhos AND ta.cod_pizzarias = '".$obj_pedido->cod_pizzarias."') WHERE p.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
            
            while ($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
            {
                $codigo = $obj_buscar_pizzas->cod_pedidos_adicionais;
                $descricao = filtrar_caracteres(strtoupper('Adicional: ' . bd2texto(substr((($obj_buscar_pizzas->adicional == 'Sim') ? 'Gergelim' : $obj_buscar_pizzas->adicional), 0, 10))));
                $quantidade = 1;
                $valor_unitario = $obj_buscar_pizzas->pa_preco;
                $valor_unitario_desconto = 0;
                
                $valor_unitario = str_replace(".", ",", sprintf("%.2f", $valor_unitario));
                $valor_unitario_desconto = str_replace(".", ",", sprintf("%.2f", $valor_unitario_desconto));

                $aliquota_imposto = $obj_buscar_pizzas->valor_imposto;
                $valor_imposto = $valor_unitario * ($aliquota_imposto/100);

                $detalhes .= "<item codigo='$codigo' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario_desconto='$valor_unitario_desconto' valor_unitario='$valor_unitario' valor_imposto='$valor_imposto'/>";
            }
            
            // Buscando ingredientes adicionais
            $sql_buscar_pizzas = "SELECT *, pi.preco AS pi_preco, it.preco AS it_preco, pi.fidelidade AS pi_fidelidade, pi.promocional AS pi_promocional FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) INNER JOIN ipi_ingredientes i ON (pi.cod_ingredientes = i.cod_ingredientes) INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (it.cod_ingredientes = i.cod_ingredientes AND it.cod_tamanhos = t.cod_tamanhos AND it.cod_pizzarias = '".$obj_pedido->cod_pizzarias."') WHERE pi.ingrediente_padrao = 0 AND p.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
            
            while ($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
            {
                $codigo = $obj_buscar_pizzas->cod_pedidos_ingredientes;
								$troca = "";
								if($obj_buscar_pizzas->cod_ingrediente_trocado!=0)
								{
									$troca = " TROCA";
								}
                $descricao = filtrar_caracteres(strtoupper('Ing:'.$troca.' '. bd2texto(substr($obj_buscar_pizzas->ingrediente, 0, 10) . ' ' . $obj_buscar_pizzas->fracao . '/' . $obj_buscar_pizzas->quant_fracao)));
                $quantidade = 1;
                $valor_unitario = $obj_buscar_pizzas->pi_preco;
                $valor_unitario_desconto = 0;

                $aliquota_imposto = $obj_buscar_pizzas->valor_imposto;
                $valor_imposto = $valor_unitario * ($aliquota_imposto/100);

                $valor_unitario = str_replace(".", ",", sprintf("%.2f", $valor_unitario));
                $valor_unitario_desconto = str_replace(".", ",", sprintf("%.2f", $valor_unitario_desconto));
                if ($valor_unitario > 0)
                {
                  $detalhes .= "<item codigo='$codigo' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario_desconto='$valor_unitario_desconto' valor_unitario='$valor_unitario' valor_imposto='$valor_imposto'/>";
                }
            }
            
            // Buscando bebidas  156967
            $sql_buscar_bebidas = "SELECT *, pb.preco AS pb_preco, cp.preco AS cp_preco, pb.fidelidade AS pb_fidelidade, pb.promocional AS pb_promocional FROM ipi_pedidos p INNER JOIN ipi_pedidos_bebidas pb ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos)  INNER JOIN ipi_conteudos_pizzarias cp ON (bc.cod_bebidas_ipi_conteudos = cp.cod_bebidas_ipi_conteudos) WHERE pb.combo = 0 AND p.cod_pedidos = '" . $obj_pedido->cod_pedidos . "' and cp.cod_pizzarias = '".$obj_pedido->cod_pizzarias."'";
            $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
            
            while ($obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas))
            {
                $valor_unitario = ($obj_buscar_bebidas->pb_preco > 0) ? $obj_buscar_bebidas->pb_preco : $obj_buscar_bebidas->cp_preco;

                if(($obj_buscar_bebidas->pb_promocional == 1) || ($obj_buscar_bebidas->pb_fidelidade == 1))
                {
                    $descricao_add = "PF";
                    $valor_unitario_desconto = $valor_unitario;
                }
                else
                {
                    $descricao_add = "N";
                    $valor_unitario_desconto = 0;
                }

                $codigo = $obj_buscar_bebidas->cod_pedidos_bebidas;
                //$codigo = "456";
                $descricao = filtrar_caracteres(strtoupper('Bebida ' . $descricao_add . ':' . bd2texto(substr($obj_buscar_bebidas->bebida, 0, 10) . ' ' . $obj_buscar_bebidas->conteudo)));
                $quantidade = $obj_buscar_bebidas->quantidade;

                $aliquota_imposto = $obj_buscar_bebidas->valor_imposto;
                $valor_imposto = $valor_unitario * ($aliquota_imposto/100);

                $valor_unitario = str_replace(".", ",", sprintf("%.2f", $valor_unitario));
                $valor_unitario_desconto = str_replace(".", ",", sprintf("%.2f", $valor_unitario_desconto));


                $detalhes .= "<item codigo='$codigo' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario_desconto='$valor_unitario_desconto' valor_unitario='$valor_unitario' valor_imposto='$valor_imposto'/>";
            }

            // Buscando o valor da entrega
            if($obj_pedido->valor_entrega > 0)
            {
                $valor_unitario = $obj_pedido->valor_entrega;
                $valor_unitario_desconto = 0;

                $codigo = 1;
                $descricao = filtrar_caracteres(strtoupper('Taxa de Entrega'));
                $quantidade = 1;
                $valor_unitario = str_replace(".", ",", sprintf("%.2f", $valor_unitario));
                $valor_unitario_desconto = str_replace(".", ",", sprintf("%.2f", $valor_unitario_desconto));
                $valor_imposto = 0; //Serviço de entrega não tem imposto apenas venda de produtos

                $detalhes .= "<item codigo='$codigo' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario_desconto='$valor_unitario_desconto' valor_unitario='$valor_unitario' valor_imposto='$valor_imposto'/>";
            }

        }
        
        $detalhes .= "</pedido>";
    }
    
    desconectabd($con_web);
    //file_put_contents($diretorio, $detalhes,FILE_APPEND);

    return $detalhes;
}

/**
 * Retorna os detalhes do pedido fiscal
 *
 * @param int $cod_pedidos Código do pedido
 * @param string $usuario Usuário
 * @param string $senha Senha
 * @return XML do pedido
 */
function detalhes_pedido_fiscal($cod_pedidos, $usuario, $senha)
{
    return montar_pedido_fiscal($cod_pedidos);
}

/**
 * Retorna o checksum do pedido
 *
 * @param int $cod_pedidos Código do pedido
 * @param string $usuario Usuário
 * @param string $senha Senha
 * @return Checksum do pedido
 */
function checksum_pedido($cod_pedidos, $usuario, $senha)
{
    return strlen(montar_pedido($cod_pedidos));
}

/**
 * Retorna o checksum do pedido fiscal
 *
 * @param int $cod_pedidos Código do pedido
 * @param string $usuario Usuário
 * @param string $senha Senha
 * @return Checksum do pedido
 */
function checksum_pedido_fiscal($cod_pedidos, $usuario, $senha)
{
    return strlen(montar_pedido_fiscal($cod_pedidos));
}

/**
 * Realiza a baixa do pedido fiscal tornando-o IMPRESSO
 *
 * @param int $cod_pedidos Código do peidod
 * @param int $num_cupom_fiscal Número do cupom fiscal
 * @param string $usuario
 * @param string $senha
 * @return Resultado da baixa (1/0)
 */
function baixa_pedido_fiscal($cod_pedidos, $num_cupom_fiscal, $usuario, $senha)
{
    $con_web = conectabd();
    
    if (($cod_pedidos > 0) && ($num_cupom_fiscal > 0))
    {
        $sql_pedidos = "UPDATE ipi_pedidos SET numero_cupom_fiscal = '$num_cupom_fiscal' WHERE cod_pedidos='" . $cod_pedidos . "' AND impressao_fiscal = 1";
        $res_pedidos = mysql_query($sql_pedidos);
    }
    
    desconectabd($con_web);
    
    return $res_pedidos;
}

function logar($cod_pizzarias, $usuario, $senha, $tipo, $mensagem)
{
    $con_web = conectabd();
    
    $sql_inserir_log = "INSERT INTO ipi_log (data_hora, cod_pizzarias, tipo, palavra_chave, valor) VALUES (NOW(), '" . texto2bd($cod_pizzarias) . "', '" . texto2bd($tipo) . "', 'SISTEMA_IMPRESSAO', '" . texto2bd($mensagem) . "')";
    $res_inserir_log = mysql_query($sql_inserir_log);
    
    desconectabd($con_web);
}

$namespace = "urn:ipizzafiscal";

$server = new soap_server();

$server->configureWSDL("IPizza ImpressaoFiscal");
$server->wsdl->schemaTargetNamespace = $namespace;

$server->register('testar_conexao', array('usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Testa a conexão com o servidor.');
$server->register('dados_software', array('cod_pizzarias' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna os dados de exibicao do software.');
$server->register('retorna_todos_pedidos_fiscais', array('cod_pizzarias' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna todos os novos pedidos fiscais do sistema');
$server->register('detalhes_pedido_fiscal', array('cod_pedidos' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna o pedido fiscal completo no sistema');
$server->register('checksum_pedido_fiscal', array('cod_pedidos' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:int'), $namespace, false, 'rpc', 'encoded', 'Retorna o checksum do pedido fiscal para comparação');
$server->register('baixa_pedido_fiscal', array('cod_pedidos' => 'xsd:int', 'num_cupom_fiscal' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:int'), $namespace, false, 'rpc', 'encoded', 'Confirmação da Impressão do cupom fiscal e registra seu número');
$server->register('logar', array('cod_pizzarias' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string', 'tipo' => 'xsd:string', 'mensagem' => 'xsd:string'), array(), $namespace, false, 'rpc', 'encoded', 'Loga o erro de impressão');

$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

$server->service($POST_DATA);

exit();
?>
