<?
require_once ("config.php");
require_once ("bd.php");

require_once ("sys/lib/php/nusoap/nusoap.php");

$usuario_webservice = WEBSERVICE_USUARIO;
$senha_webservice = WEBSERVICE_SENHA;

function testar_conexao($usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$detalhes = "<testes>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='3' corte='0'>TESTE DE SISTEMA</linha>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>CONEXAO: OK</linha>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>IMPRESSORA: OK</linha>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>DATA E HORA: " . date('d/m/Y H:i:s') . "</linha>";
			$detalhes .= "</testes>";
		}
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticacao</linha>";
			$detalhes .="</erro>";
		}
    
    return $detalhes;
}

function dados_software($cod_pizzarias, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
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
			
			$detalhes .= "<detalhe chave='url_ftp' valor='ftp.internetsistemas.com.br' />";
			$detalhes .= "<detalhe chave='login_ftp' valor='osmuzzarellas' />";
			$detalhes .= "<detalhe chave='senha_ftp' valor='vmTAAHqV' />";
			
			// tempo de espera entre o proximo comando de impressão
			$detalhes .= "<detalhe chave='tempo_espera_impressao' valor='10'/>";
			
			// intervalo de consulta no webservice
			$detalhes .= "<detalhe chave='tempo_verificacao' valor='10'/>";
			
			$detalhes .= "<detalhe chave='ultima_versao' valor='1.0.0.0'/>";
			$detalhes .= "<detalhe chave='pacote_instalacao' valor='http://www.internetsistemas.com.br/download/ipizza.zip'/>";
			
			// Conta de SMTP utilizada para envio log
			$detalhes .= "<detalhe chave='smtp' valor='smtp.osmuzzarellas.com.br'/>";
			$detalhes .= "<detalhe chave='smtp_porta' valor='25'/>";
			$detalhes .= "<detalhe chave='smtp_usuario' valor='suporte@osmuzzarellas.com.br'/>";
			$detalhes .= "<detalhe chave='smtp_senha' valor='Sup@Muz98'/>";
			$detalhes .= "<detalhe chave='email_detino_arquivo_log' valor='contato@internetsistemas.com.br'/>";
			
			$detalhes .= "</detalhes>";
    }
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticacao</linha>";
			$detalhes .="</erro>";
		}
    
    return $detalhes;
}

/**
 * Retorna todos os pedidos pendentes para impressão.
 * 
 * Ficar atento com a regra do agendado (apenas imprime perto do horário de agendamento - 40min. em média)
 *
 * @param int $cod_pizzarias Código da pizzaria
 * @param string $usuario Usuário
 * @param string $senha Senha
 * @return XML com todos os pedidos.
 */
function retorna_todos_pedidos($cod_pizzarias, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$con_web = conectabd();
      if (!$cod_pizzarias) $cod_pizzarias = 0;
      
			$sql_busca_debug = "SELECT debug_pedidos from ipi_pizzarias where cod_pizzarias = '$cod_pizzarias'" ;
			$res_busca_debug = mysql_query($sql_busca_debug);
			$obj_busca_debug = mysql_fetch_object($res_busca_debug);
			
      $sql_relatorio = "SELECT cod_impressao_relatorio FROM ipi_impressao_relatorio WHERE situacao = 'NOVO' AND cod_pizzarias = $cod_pizzarias ORDER BY cod_impressao_relatorio";
			$res_relatorio = mysql_query($sql_relatorio);
			$num_relatorio = mysql_num_rows($res_relatorio);
			
			if ($num_relatorio > 0)
			{
					$pedidos = "<pedidos quantidade='" . $num_relatorio . "' debug='".$obj_busca_debug->debug_pedidos."'>";
					
					for($a = 0; $a < $num_relatorio; $a++)
					{
							$obj_relatorio = mysql_fetch_object($res_relatorio);
							
							$pedidos .= "<pedido reimpressao='0'>" . ($obj_relatorio->cod_impressao_relatorio * -1) . "</pedido>";
					}
					
					$pedidos .= "</pedidos>";
			}
			else
			{
          $sql_pedidos_1 = "SELECT p.cod_pedidos, p.reimpressao, p.impressao_fiscal FROM ipi_pedidos p WHERE (p.situacao = 'NOVO' OR p.reimpressao = 1) AND p.cod_pizzarias = " . $cod_pizzarias . " AND agendado = 0";
					$res_pedidos_1 = mysql_query($sql_pedidos_1);
					$num_pedidos_1 = mysql_num_rows($res_pedidos_1);
					
          $sql_pedidos_2 = "SELECT p.cod_pedidos, p.reimpressao, p.impressao_fiscal FROM ipi_pedidos p WHERE (p.situacao = 'NOVO' OR p.reimpressao = 1) AND p.cod_pizzarias = " . $cod_pizzarias . " AND agendado = 1 AND CONCAT(DATE(data_hora_pedido), ' ', horario_agendamento) <= DATE_ADD(NOW(), INTERVAL 120 MINUTE)";
					$res_pedidos_2 = mysql_query($sql_pedidos_2);
					$num_pedidos_2 = mysql_num_rows($res_pedidos_2);
					
					$pedidos = "<pedidos quantidade='" . ($num_pedidos_1 + $num_pedidos_2) . "' debug='".$obj_busca_debug->debug_pedidos."'>";
					
					for($a = 0; $a < $num_pedidos_1; $a++)
					{
							$obj_pedidos_1 = mysql_fetch_object($res_pedidos_1);
							
							$pedidos .= "<pedido reimpressao='" . $obj_pedidos_1->reimpressao . "' fiscal='" . $obj_pedidos_1->impressao_fiscal . "'>" . $obj_pedidos_1->cod_pedidos . "</pedido>";
					}
					
					for($b = 0; $b < $num_pedidos_2; $b++)
					{
							$obj_pedidos_2 = mysql_fetch_object($res_pedidos_2);
							
							$pedidos .= "<pedido reimpressao='" . $obj_pedidos_2->reimpressao . "' fiscal='" . $obj_pedidos_2->impressao_fiscal . "'>" . $obj_pedidos_2->cod_pedidos . "</pedido>";
					}
					
					$pedidos .= "</pedidos>";
			}
			
			desconectabd($con_web);
    }
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticacao</linha>";
			$detalhes .="</erro>";
		}
    return $pedidos;
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
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$con_web = conectabd();
			
			$sql_pedidos_1 = "SELECT * FROM ipi_pedidos p WHERE p.numero_cupom_fiscal = 0 AND p.impressao_fiscal = 1 AND p.cod_pizzarias = '" . $cod_pizzarias . "' AND agendado = 0";
			$res_pedidos_1 = mysql_query($sql_pedidos_1);
			$num_pedidos_1 = mysql_num_rows($res_pedidos_1);
			
			$sql_pedidos_2 = "SELECT * FROM ipi_pedidos p WHERE p.numero_cupom_fiscal = 0 AND p.impressao_fiscal = 1 AND p.cod_pizzarias = '" . $cod_pizzarias . "' AND agendado = 1 AND CONCAT(DATE(data_hora_pedido), ' ', horario_agendamento) <= DATE_ADD(NOW(), INTERVAL 40 MINUTE)";
			$res_pedidos_2 = mysql_query($sql_pedidos_2);
			$num_pedidos_2 = mysql_num_rows($res_pedidos_2);
			
			$pedidos = "<pedidos quantidade='" . ($num_pedidos_1 + $num_pedidos_2) . "' >";
			
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
    }
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticacao</linha>";
			$detalhes .="</erro>";
		}
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
    
    return $mensagem;
}

/**
 * Monta o XML do pedido
 *
 * @param int $cod_pedidos Código do pedido
 * 
 * @return XML de impressão do pedido
 */
function montar_pedido($cod_pedidos)
{
    $con_web = conectabd();
		$diretorio = "debug/".$cod_pedidos.".xml";
    if ($cod_pedidos > 0)
    {
        $sql_pedido = "SELECT * FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) WHERE (p.situacao = 'NOVO' OR reimpressao = 1) AND cod_pedidos = " . $cod_pedidos;
        $res_pedido = mysql_query($sql_pedido);
				if(mysql_error($con_web)!="")
				{
					file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
				}
        $num_pedido = mysql_num_rows($res_pedido);
        $obj_pedido = mysql_fetch_object($res_pedido);
        
        $detalhes = "<pedido quantidade='$num_pedido'>";
        
        if ($num_pedido > 0)
        {
            //#################### Comanda Cozinha ####################
            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>------------------- CAIXA ------------------</linha>";
            $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='2' corte='0'>PEDIDO " . sprintf("%08d", $cod_pedidos) . "</linha>";
            //$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>ORIGEM: " . $obj_pedido->origem_pedido . "</linha>";
            $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>HORARIO: " . bd2datahora($obj_pedido->data_hora_pedido) . "</linha>";

            // Agendado
            if ($obj_pedido->agendado == 1)
            {
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'># AGENDADO ## AGENDADO #</linha>";
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'># AGEND. AGEND. AGEND. #</linha>";
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'># AGENDADO ## AGENDADO #</linha>";

                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>################## ATENCAO ###################</linha>";
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>AGENDADO: " . bd2datahora($obj_pedido->horario_agendamento) . "</linha>";
                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>################## ATENCAO ###################</linha>";
            }
            

	            if ( (($obj_pedido->origem_pedido == 'TEL') || $obj_pedido->ifood==1) && ($obj_pedido->obs_pedido != "") )
            {
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>OBS. PEDIDO: " . filtrar_caracteres(bd2texto($obj_pedido->obs_pedido)) . "</linha>";
            }
//Pizzas
            $sql_pizzas = "SELECT * FROM ipi_pedidos_pizzas pi INNER JOIN ipi_pedidos pe ON(pi.cod_pedidos = pe.cod_pedidos) WHERE pi.cod_pedidos = '$cod_pedidos'";
            $res_pizzas = mysql_query($sql_pizzas);
						if(mysql_error($con_web)!="")
						{
							file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
						}
            $numero_pizza = 1;
            
            while ( $obj_pizzas = mysql_fetch_object($res_pizzas) )
            {
                if ($numero_pizza == 1)
                  $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
                /*$pizza_gratis = ($obj_pizzas->promocional == 1) ? ' (GRATIS)' : '';
                $pizza_fidelidade = ($obj_pizzas->fidelidade == 1) ? ' (FIDELIDADE)' : '';
                $pizza_combo = ($obj_pizzas->combo == 1) ? ' (COMBO)' : '';*/
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PIZZA: ${numero_pizza}</linha>";
                
                // Tamanho da Pizza
                $sql_tamanhos = "SELECT tamanho FROM ipi_tamanhos WHERE cod_tamanhos = '" . $obj_pizzas->cod_tamanhos . "'";
                $res_tamanhos = mysql_query($sql_tamanhos);
                $obj_tamanhos = mysql_fetch_object($res_tamanhos);
								if(mysql_error($con_web)!="")
								{
									file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
								}
				
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TAMANHO: " . filtrar_caracteres(bd2texto($obj_tamanhos->tamanho)) . "</linha>";
                
                // Massa
                $sql_massa = "SELECT tipo_massa FROM ipi_tipo_massa WHERE cod_tipo_massa = '" . $obj_pizzas->cod_tipo_massa . "'";
                $res_massa = mysql_query($sql_massa);
								if(mysql_error($con_web)!="")
								{
									file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
								}
                $obj_massa = mysql_fetch_object($res_massa);
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>MASSA: " . filtrar_caracteres(bd2texto($obj_massa->tipo_massa)) . "</linha>";

                // Corte
                $sql_corte = "SELECT opcao_corte FROM ipi_opcoes_corte WHERE cod_opcoes_corte = '" . $obj_pizzas->cod_opcoes_corte . "'";
                $res_corte = mysql_query($sql_corte);
                $obj_corte = mysql_fetch_object($res_corte);
								if(mysql_error($con_web)!="")
								{
									file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
								}
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>CORTE: " . filtrar_caracteres(bd2texto($obj_corte->opcao_corte)) . "</linha>";


                // Borda
                $sql_borda = "SELECT * FROM ipi_pedidos_bordas pb INNER JOIN ipi_bordas bo ON(pb.cod_bordas=bo.cod_bordas) WHERE pb.cod_pedidos_pizzas = '" . $obj_pizzas->cod_pedidos_pizzas . "' AND pb.cod_pedidos = '" . $obj_pizzas->cod_pedidos . "'";
                $res_borda = mysql_query($sql_borda);
                $num_borda = mysql_num_rows($res_borda);
                if(mysql_error($con_web)!="")
                {
                  file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
                }
                $obj_borda = mysql_fetch_object($res_borda);
                
                $borda_gratis = ($obj_borda->promocional == 1) ? ' (GRATIS)' : '';
                $borda_fidelidade = ($obj_borda->fidelidade == 1) ? ' (FIDELIDADE)' : '';
                $borda_combo = ($obj_borda->combo == 1) ? ' (COMBO)' : '';
                
                $detalhes .= ($num_borda > 0) ? "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>BORDA: " . filtrar_caracteres(bd2texto($obj_borda->borda)) . "${borda_gratis}${borda_fidelidade}${borda_combo}</linha>" : "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>BORDA: Não</linha>";

                
                // Fraçoes
                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>------------------------------------------------</linha>";
                
                $sql_fracoes = "SELECT * FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pizzas pz ON (pf.cod_pizzas = pz.cod_pizzas) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pp.cod_pedidos = '" . $obj_pizzas->cod_pedidos . "' AND pp.cod_pedidos_pizzas = '" . $obj_pizzas->cod_pedidos_pizzas . "'";
                $res_fracoes = mysql_query($sql_fracoes);
                if(mysql_error($con_web)!="")
								{
									file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
								}
                while ( $obj_fracoes = mysql_fetch_object($res_fracoes) )
                {
                    $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PIZZA: $numero_pizza - PARTE " . $obj_fracoes->fracao . '/' . $obj_fracoes->quant_fracao ."</linha>";
										$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . filtrar_caracteres(bd2texto($obj_fracoes->pizza));
                    
                    // Ingredientes retirados
                    //$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>RETIRAR:</linha>";
                    
                    $sql_ingredientes_retirar = "SELECT i.ingrediente FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = '" . $obj_fracoes->cod_pedidos . "' AND pi.cod_pedidos_pizzas = '" . $obj_fracoes->cod_pedidos_pizzas . "' AND pi.cod_pedidos_fracoes = '" . $obj_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 1) AND p.cod_pizzas = '" . $obj_fracoes->cod_pizzas . "' AND i.consumo = 0";
                    $res_ingredientes_retirar = mysql_query($sql_ingredientes_retirar);
                    if(mysql_error($con_web)!="")
										{
											file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
										}
                    while ( $obj_ingredientes_retirar = mysql_fetch_object($res_ingredientes_retirar) )
                    {
                        $detalhes .= " S/ " . filtrar_caracteres(bd2texto($obj_ingredientes_retirar->ingrediente));
                    }
                    
                    // Ingredientes adicionados
                    
                    $sql_ingredientes_adicionar = "SELECT pzi.ingrediente,(select ingrediente from ipi_ingredientes where cod_ingredientes = pi.cod_ingrediente_trocado) as nome_trocado FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_ingredientes pzi ON (pi.cod_ingredientes = pzi.cod_ingredientes) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = '" . $obj_fracoes->cod_pedidos . "' AND pi.cod_pedidos_pizzas = '" . $obj_fracoes->cod_pedidos_pizzas . "' AND pi.cod_pedidos_fracoes = '" . $obj_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 0";
                    $res_ingredientes_adicionar = mysql_query($sql_ingredientes_adicionar);
                    if(mysql_error($con_web)!="")
										{
											file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
										}
                    while ( $obj_ingredientes_adicionar = mysql_fetch_object($res_ingredientes_adicionar) )
                    {
                        $detalhes .= " EXTRA " . filtrar_caracteres(bd2texto($obj_ingredientes_adicionar->ingrediente));

                        if($obj_ingredientes_adicionar->nome_trocado!="")
                        {
                            $detalhes .= "(TROCA ".$obj_ingredientes_adicionar->nome_trocado.")";
                        } 
                    }
                    $detalhes .= "</linha>";
                    // Obs de frações
                    if ($obj_pedido->origem_pedido == 'TEL' || $obj_pedido->ifood==1)
                    {
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>OBS. FRACAO: " . filtrar_caracteres(bd2texto($obj_fracoes->obs_fracao)) . "</linha>";
                    }
                    
                    if ($obj_fracoes->fracao < $obj_fracoes->quant_fracao)
                    {
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>------------------------------------------------</linha>";
                    }
                }
                
                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
                
                $numero_pizza++;
            }
            
												// Bebidas
            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>BEBIDAS:</linha>";
            
            $sql_bebidas = "SELECT * FROM ipi_pedidos_bebidas pb INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_pedidos pe ON(pb.cod_pedidos = pe.cod_pedidos) WHERE pb.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_bebidas = mysql_query($sql_bebidas);
            if(mysql_error($con_web)!="")
						{
							file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
						}
            while ( $obj_bebidas = mysql_fetch_object($res_bebidas) )
            {
                /*$bebida_gratis = ($obj_bebidas->promocional == 1) ? ' (GRATIS)' : '';
                $bebida_fidelidade = ($obj_bebidas->fidelidade == 1) ? ' (FIDELIDADE)' : '';
                $bebida_combo = ($obj_bebidas->combo == 1) ? ' (COMBO)' : '';*/

                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . $obj_bebidas->quantidade . ' - ' . mb_strtoupper($obj_bebidas->bebida) . ' ' . mb_strtoupper($obj_bebidas->conteudo) . "</linha>";
            }
            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
						
            if($obj_pedido->tipo_entrega=="Balcão")
            {
							$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='2' corte='0'></linha>";
							 // Nome do cliente
	            $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>NOME: " . filtrar_caracteres(bd2texto($obj_pedido->nome)) . "</linha>";

	            if($obj_pedido->celular!="")
	            {
	            	$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>CELULAR: " . filtrar_caracteres(bd2texto($obj_pedido->celular)) . "</linha>";
	          	}

	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TEL. 1: " . filtrar_caracteres(bd2texto($obj_pedido->telefone_1)) . "</linha>";

	            if($obj_pedido->telefone_2)
	            {
		            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TEL. 2: " . filtrar_caracteres(bd2texto($obj_pedido->telefone_2)) . "</linha>";
		          }

	            /*$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>END.: " . filtrar_caracteres(bd2texto($obj_pedido->endereco)) . "</linha>";
	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>NUM.: " . filtrar_caracteres(bd2texto($obj_pedido->numero)) . "</linha>";
	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>COMP.: " . filtrar_caracteres(bd2texto($obj_pedido->complemento)) . "</linha>";
	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>EDIF.: " . filtrar_caracteres(bd2texto($obj_pedido->edificio)) . "</linha>";
	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>BAIRRO: " . filtrar_caracteres(bd2texto($obj_pedido->bairro)) . "</linha>";*/

	            //#################### $$$$$$$ ####################
	            // Forma de pgto
	            //$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>FORMA PG.: " . filtrar_caracteres(bd2texto($obj_pedido->forma_pg)) . "</linha>";
              $sql_formas_pg = "SELECT fp.forma_pg, pfp.valor FROM ipi_pedidos_formas_pg pfp LEFT JOIN ipi_formas_pg fp ON (pfp.cod_formas_pg = fp.cod_formas_pg) WHERE pfp.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
              //echo $sql_formas_pg;
              $res_formas_pg = mysql_query($sql_formas_pg);
              $num_formas_pg = mysql_num_rows($res_formas_pg);
              if ($num_formas_pg>0)
              {
                while ($obj_formas_pg = mysql_fetch_object($res_formas_pg))
                {
                  $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>".($obj_formas_pg->forma_pg).": R$" . bd2moeda($obj_formas_pg->valor) . "</linha>";
                }
              }
							
	            // Valor
	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>VALOR: R$" . bd2moeda($obj_pedido->valor) . "</linha>";
	            // Taxa de Entrega
	            if($obj_pedido->valor_entrega > 0)
	            {
	                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>Taxa de Entrega: R$" . bd2moeda($obj_pedido->valor_entrega) . "</linha>";
	            }
	            // Desconto
	            if ($obj_pedido->desconto > 0)
	            {
	                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>DESCONTO: R$" . bd2moeda($obj_pedido->desconto) . "</linha>";
	            }
	            // Valor Total
	            $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>VALOR TOTAL: R$" . bd2moeda($obj_pedido->valor_total) . "</linha>";
													
              // Troco
              $sql_troco = "SELECT * FROM ipi_pedidos_detalhes_pg WHERE CHAVE = 'TROCO' AND cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
              $res_troco = mysql_query($sql_troco);
							if(mysql_error($con_web)!="")
							{
								file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
							}
              $num_troco = mysql_num_rows($res_troco);
              $obj_troco = mysql_fetch_object($res_troco);
              
              if ($num_troco > 0)
              {
                  $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TROCO PARA: R$ " . bd2moeda($obj_troco->conteudo) . "</linha>";
                  $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='2' corte='0'>TROCO: R$ " . bd2moeda((((float) $obj_troco->conteudo) - ((float) $obj_pedido->valor_total))) . "</linha>";
              }


	          }
	          else
	          {


            // Valor
            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>VALOR: R$" . bd2moeda($obj_pedido->valor) . "</linha>";
            // Taxa de Entrega
            if($obj_pedido->valor_entrega > 0)
            {
                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>Taxa de Entrega: R$" . bd2moeda($obj_pedido->valor_entrega) . "</linha>";
            }
            // Desconto
            if ($obj_pedido->desconto > 0)
            {
                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>DESCONTO: R$" . bd2moeda($obj_pedido->desconto) . "</linha>";
            }
            // Valor Total
            $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>VALOR TOTAL: R$" . bd2moeda($obj_pedido->valor_total) . "</linha>";
                        
            // Troco
            $sql_troco = "SELECT * FROM ipi_pedidos_detalhes_pg WHERE CHAVE = 'TROCO' AND cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_troco = mysql_query($sql_troco);
            if(mysql_error($con_web)!="")
            {
              file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
            }
            $num_troco = mysql_num_rows($res_troco);
            $obj_troco = mysql_fetch_object($res_troco);
            
            if ($num_troco > 0)
            {
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TROCO PARA: R$ " . bd2moeda($obj_troco->conteudo) . "</linha>";
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='2' corte='0'>TROCO: R$ " . bd2moeda((((float) $obj_troco->conteudo) - ((float) $obj_pedido->valor_total))) . "</linha>";
            }
            
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>------------------------------------------------</linha>";



	          	//$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>FORMA PG.: " . filtrar_caracteres(bd2texto($obj_pedido->forma_pg)) . "</linha>";
              $sql_formas_pg = "SELECT fp.forma_pg, pfp.valor FROM ipi_pedidos_formas_pg pfp LEFT JOIN ipi_formas_pg fp ON (pfp.cod_formas_pg = fp.cod_formas_pg) WHERE pfp.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
              //echo $sql_formas_pg;
              $res_formas_pg = mysql_query($sql_formas_pg);
              $num_formas_pg = mysql_num_rows($res_formas_pg);
              if ($num_formas_pg>0)
              {
                while ($obj_formas_pg = mysql_fetch_object($res_formas_pg))
                {
                  $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>".($obj_formas_pg->forma_pg).": R$" . bd2moeda($obj_formas_pg->valor) . "</linha>";
                }
              }    




              $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
               // Nome do cliente
              $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>NOME: " . filtrar_caracteres(bd2texto($obj_pedido->nome)) . "</linha>";

              if($obj_pedido->celular!="")
              {
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>CELULAR: " . filtrar_caracteres(bd2texto($obj_pedido->celular)) . "</linha>";
              }

	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TEL. 1: " . filtrar_caracteres(bd2texto($obj_pedido->telefone_1)) . "</linha>";

	            if($obj_pedido->telefone_2)
	            {
		            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TEL. 2: " . filtrar_caracteres(bd2texto($obj_pedido->telefone_2)) . "</linha>";
		          }

	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>END.: " . filtrar_caracteres(bd2texto($obj_pedido->endereco)) . "</linha>";
	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>NUM.: " . filtrar_caracteres(bd2texto($obj_pedido->numero)) . "</linha>";
	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>COMP.: " . filtrar_caracteres(bd2texto($obj_pedido->complemento)) . "</linha>";
	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>EDIF.: " . filtrar_caracteres(bd2texto($obj_pedido->edificio)) . "</linha>";
	            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>BAIRRO: " . filtrar_caracteres(bd2texto($obj_pedido->bairro)) . "</linha>";	         
  						$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PONTO DE REFERENCIA: " . filtrar_caracteres(bd2texto($obj_pedido->referencia_endereco)) . "</linha>";
							$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>REF. DO CLIENTE: " . filtrar_caracteres(bd2texto($obj_pedido->referencia_cliente)) . "</linha>"; 	
	          }





            /*

						$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='2' corte='1'></linha>";
            $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='2' corte='0'>CONFERENCIA ".($obj_pedido->tipo_entrega=="Balcão"? "Balcão" : "DELIVERY")." </linha>";
            $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='2' corte='0'>PEDIDO " . sprintf("%08d", $cod_pedidos) . "</linha>";
						
            // Destino
            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>DESTINO: " . filtrar_caracteres(bd2texto($obj_pedido->tipo_entrega)) . "</linha>";
            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>HORARIO: " . bd2datahora($obj_pedido->data_hora_pedido) . "</linha>";
						// Agendado
            if ($obj_pedido->agendado == 1)
            {
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'># AGENDADO ## AGENDADO #</linha>";
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'># AGEND. AGEND. AGEND. #</linha>";
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'># AGENDADO ## AGENDADO #</linha>";

                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>################## ATENCAO ###################</linha>";
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>AGENDADO: " . bd2datahora($obj_pedido->horario_agendamento) . "</linha>";
                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>################## ATENCAO ###################</linha>";
            }
						 // Nome do cliente
            $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>NOME: " . filtrar_caracteres(bd2texto($obj_pedido->nome)) . "</linha>";

            // Tempo de entrega
            if ($obj_pedido->agendado == 0)
            {
                $sql_tempo = "SELECT TIME(DATE_ADD('" . $obj_pedido->data_hora_pedido . "', INTERVAL tempo_entrega MINUTE)) AS tempo_entrega FROM ipi_pizzarias_horarios WHERE cod_pizzarias = '" . $obj_pedido->cod_pizzarias . "' AND horario_inicial_entrega <= TIME('" . $obj_pedido->data_hora_pedido . "') AND horario_final_entrega >= TIME('" . $obj_pedido->data_hora_pedido . "') AND dia_semana = '" . date('w', strtotime($obj_pedido->data_hora_pedido)) . "'";
                $res_tempo = mysql_query($sql_tempo);
								if(mysql_error($con_web)!="")
								{
									file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
								}
                $num_tempo = mysql_num_rows($res_tempo);
                $obj_tempo = mysql_fetch_object($res_tempo);
                
                $detalhes .= ($num_tempo > 0) ? "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>HORA DA ENTREGA: " . filtrar_caracteres($obj_tempo->tempo_entrega) . "</linha>" : "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>HORA DA ENTREGA: NAO ENCONTRADO</linha>";
            }
						
						
						$sql_pizzas = "SELECT * FROM ipi_pedidos_pizzas pi INNER JOIN ipi_pedidos pe ON(pi.cod_pedidos = pe.cod_pedidos) WHERE pi.cod_pedidos = '$cod_pedidos'";
            $res_pizzas = mysql_query($sql_pizzas);
						if(mysql_error($con_web)!="")
						{
							file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
						}
            $numero_pizza = 1;
            
            while ( $obj_pizzas = mysql_fetch_object($res_pizzas) )
            {
                $pizza_gratis = ($obj_pizzas->promocional == 1) ? ' (GRATIS)' : '';
                $pizza_fidelidade = ($obj_pizzas->fidelidade == 1) ? ' (FIDELIDADE)' : '';
                $pizza_combo = ($obj_pizzas->combo == 1) ? ' (COMBO)' : '';
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PIZZA: ${numero_pizza}${pizza_gratis}${pizza_fidelidade}${pizza_combo}</linha>";
                
                // Tamanho da Pizza
                $sql_tamanhos = "SELECT tamanho FROM ipi_tamanhos WHERE cod_tamanhos = '" . $obj_pizzas->cod_tamanhos . "'";
                $res_tamanhos = mysql_query($sql_tamanhos);
                $obj_tamanhos = mysql_fetch_object($res_tamanhos);
								if(mysql_error($con_web)!="")
								{
									file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
								}
				
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TAMANHO: " . filtrar_caracteres(bd2texto($obj_tamanhos->tamanho)) . "</linha>";
                
                // Massa
                $sql_massa = "SELECT tipo_massa FROM ipi_tipo_massa WHERE cod_tipo_massa = '" . $obj_pizzas->cod_tipo_massa . "'";
                $res_massa = mysql_query($sql_massa);
								if(mysql_error($con_web)!="")
								{
									file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
								}
                $obj_massa = mysql_fetch_object($res_massa);
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>MASSA: " . filtrar_caracteres(bd2texto($obj_massa->tipo_massa)) . "</linha>";

                // Corte
                $sql_corte = "SELECT opcao_corte FROM ipi_opcoes_corte WHERE cod_opcoes_corte = '" . $obj_pizzas->cod_opcoes_corte . "'";
                $res_corte = mysql_query($sql_corte);
                $obj_corte = mysql_fetch_object($res_corte);
								if(mysql_error($con_web)!="")
								{
									file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
								}
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>CORTE: " . filtrar_caracteres(bd2texto($obj_corte->opcao_corte)) . "</linha>";
                
                // Fraçoes
                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
                
                $sql_fracoes = "SELECT *,pf.preco as preco_fracao FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pizzas pz ON (pf.cod_pizzas = pz.cod_pizzas) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pp.cod_pedidos = '" . $obj_pizzas->cod_pedidos . "' AND pp.cod_pedidos_pizzas = '" . $obj_pizzas->cod_pedidos_pizzas . "'";
                $res_fracoes = mysql_query($sql_fracoes);
                if(mysql_error($con_web)!="")
								{
									file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
								}
                while ( $obj_fracoes = mysql_fetch_object($res_fracoes) )
                {
										$preco_fracao = 0;
                    $preco_fracao += $obj_fracoes->preco_fracao;
                    $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PIZZA: $numero_pizza - PARTE " . $obj_fracoes->fracao . '/' . $obj_fracoes->quant_fracao ."</linha>";
										$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . filtrar_caracteres(bd2texto($obj_fracoes->pizza)). " ".("${pizza_gratis}${pizza_fidelidade}${pizza_combo}" !="" ? "" : "R$ ".bd2moeda($obj_fracoes->preco_fracao));
                    
                    // Ingredientes retirados
                    //$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>RETIRAR:</linha>";
                    
                    $sql_ingredientes_retirar = "SELECT i.ingrediente FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = '" . $obj_fracoes->cod_pedidos . "' AND pi.cod_pedidos_pizzas = '" . $obj_fracoes->cod_pedidos_pizzas . "' AND pi.cod_pedidos_fracoes = '" . $obj_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 1) AND p.cod_pizzas = '" . $obj_fracoes->cod_pizzas . "' AND i.consumo = 0";
                    $res_ingredientes_retirar = mysql_query($sql_ingredientes_retirar);
                    if(mysql_error($con_web)!="")
										{
											file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
										}
                    while ( $obj_ingredientes_retirar = mysql_fetch_object($res_ingredientes_retirar) )
                    {
                        $detalhes .= " S/ " . filtrar_caracteres(bd2texto($obj_ingredientes_retirar->ingrediente));
                    }
                    
                    // Ingredientes adicionados
                    
                    $sql_ingredientes_adicionar = "SELECT pzi.ingrediente,pi.preco as preco_ingrediente,(select ingrediente from ipi_ingredientes where cod_ingredientes = pi.cod_ingrediente_trocado) as nome_trocado FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_ingredientes pzi ON (pi.cod_ingredientes = pzi.cod_ingredientes) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = '" . $obj_fracoes->cod_pedidos . "' AND pi.cod_pedidos_pizzas = '" . $obj_fracoes->cod_pedidos_pizzas . "' AND pi.cod_pedidos_fracoes = '" . $obj_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 0";
                    $res_ingredientes_adicionar = mysql_query($sql_ingredientes_adicionar);
                    if(mysql_error($con_web)!="")
										{
											file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
										}
                    while ( $obj_ingredientes_adicionar = mysql_fetch_object($res_ingredientes_adicionar) )
                    {
                        $detalhes .= "  C/ " . filtrar_caracteres(bd2texto($obj_ingredientes_adicionar->ingrediente))." R$ ".$obj_ingredientes_adicionar->preco_ingrediente;
												$preco_fracao += $obj_ingredientes_adicionar->preco_ingrediente;

                        if($obj_ingredientes_adicionar->nome_trocado!="")
                        {
                            $detalhes .= "(TROCA ".$obj_ingredientes_adicionar->nome_trocado.")";
                        } 
                    }
                    $detalhes .= "</linha>";

                    // Obs de frações
                    if ($obj_pedido->origem_pedido == 'TEL')
                    {
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>OBS. FRACAO: " . filtrar_caracteres(bd2texto($obj_fracoes->obs_fracao)) . "</linha>";
                    }
                    
										$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>Preço total da fração: R$ ".bd2moeda($preco_fracao)."</linha>";
										
                    if ($obj_fracoes->fracao < $obj_fracoes->quant_fracao)
                    {
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>------------------------------------------------</linha>";
                    }
                }
                
                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
                
                $numero_pizza++;
            }
						
						// Bebidas
            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>BEBIDAS:</linha>";
            
            $sql_bebidas = "SELECT *,pb.preco as preco_bebida  FROM ipi_pedidos_bebidas pb INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_pedidos pe ON(pb.cod_pedidos = pe.cod_pedidos) WHERE pb.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_bebidas = mysql_query($sql_bebidas);
            if(mysql_error($con_web)!="")
						{
							file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
						}
            while ( $obj_bebidas = mysql_fetch_object($res_bebidas) )
            {
                $bebida_gratis = ($obj_bebidas->promocional == 1) ? ' (GRATIS)' : '';
                $bebida_fidelidade = ($obj_bebidas->fidelidade == 1) ? ' (FIDELIDADE)' : '';
                $bebida_combo = ($obj_bebidas->combo == 1) ? ' (COMBO)' : '';

                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . $obj_bebidas->quantidade . ' - ' . mb_strtoupper($obj_bebidas->bebida) . ' ' .  mb_strtoupper($obj_bebidas->conteudo) . " ".("${bebida_gratis}${bebida_fidelidade}${bebida_combo}" !="" ? "${bebida_gratis}${bebida_fidelidade}${bebida_combo} " : bd2moeda(($obj_bebidas->quantidade*$obj_bebidas->preco_bebida)))."</linha>";
            }
						
						//#################### $$$$$$$ ####################
            // Forma de pgto
						$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='2' corte='0'></linha>";
            if ( ($obj_pedido->origem_pedido == 'TEL') && ($obj_pedido->obs_pedido != "") )
            {
                $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>OBS. PEDIDO: " . filtrar_caracteres(bd2texto($obj_pedido->obs_pedido)) . "</linha>";
            }
            $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>FORMA PG.: " . filtrar_caracteres(bd2texto($obj_pedido->forma_pg)) . "</linha>";

            */
						


            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";

            if($obj_pedido->valor_entrega > 0)
            {
              $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='2' corte='0'>www.internetsistemas.com.br - Versão: #versao#</linha>";
              $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='1'></linha>";
              $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>PEDIDO " . sprintf("%08d", $cod_pedidos) . "</linha>";
              $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>Taxa de Entrega: R$" . bd2moeda($obj_pedido->valor_entrega) . "</linha>";
            }
            else
            {
              $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='10' corte='0'>www.internetsistemas.com.br - Versão: #versao#</linha>";
            }

            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='2'></linha>";
        }
        
        $detalhes .= "</pedido>";
    }
    else 
        if ($cod_pedidos < 0)
        {
            $cod_pedidos *= -1;
            
            $sql_relatorio = "SELECT * FROM ipi_impressao_relatorio WHERE cod_impressao_relatorio = '$cod_pedidos'";
            $res_relatorio = mysql_query($sql_relatorio);
            $num_relatorio = mysql_num_rows($res_relatorio);
            $obj_relatorio = mysql_fetch_object($res_relatorio);
            
            if ($num_relatorio > 0)
            {
                $detalhes = "<pedido quantidade='$num_relatorio'>";
                
                //$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>################## ATENCAO ###################</linha>";
                //$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>" . NOME_SITE . "</linha>";
                //$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>" . NOME_SITE . "</linha>";
                //$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>" . NOME_SITE . "</linha>";
                //$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>################## ATENCAO ###################</linha>";
                

                if ($obj_relatorio->relatorio == 'CAIXA')
                {
                    // Retornando o caixa associado

                    if ($obj_relatorio->cod_caixa > 0)
                    {
                        $sql_caixa = "SELECT *, c.situacao AS c_situacao FROM ipi_caixa c INNER JOIN ipi_pizzarias p ON (c.cod_pizzarias = p.cod_pizzarias) WHERE cod_caixa = '" . $obj_relatorio->cod_caixa . "'";
                        $res_caixa = mysql_query($sql_caixa);
                        $obj_caixa = mysql_fetch_object($res_caixa);

                        $data_inicial = $obj_caixa->data_hora_abertura;
                        $cod_pizzarias = $obj_caixa->cod_pizzarias;

                        if ($obj_caixa->c_situacao == 'ABERTO')
                        {
                            $obj_data_final = executaBuscaSimples("SELECT NOW() AS horario", $con_web);
                            $data_final = $obj_data_final->horario;
                            
                            $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>RELATORIO DE CAIXA</linha>";
                            $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='2' corte='0'>(PARCIAL)</linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PIZZARIA: " . bd2texto($obj_caixa->nome) . "</linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>ABERTURA: " . bd2datahora($data_inicial) . "</linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>FECHAMENTO PARCIAL: " . bd2datahora($data_final) . "</linha>";
                        }
                        else
                        {
                            $data_final = $obj_caixa->data_hora_fechamento;
                            
                            $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>RELATORIO DE CAIXA</linha>";
                            $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='2' corte='0'>(FECHAMENTO)</linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PIZZARIA: " . bd2texto($obj_caixa->nome) . "</linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>ABERTURA: " . bd2datahora($data_inicial) . "</linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>FECHAMENTO: " . bd2datahora($data_final) . "</linha>";
                        }
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>================================================</linha>";
                        
                        // Relatório de formas de pagamento - baixados
                        $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='2' corte='0'>FORMA DE PAGAMENTO (BAIXADOS)</linha>";
                        
                        $sql_formas_pg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
                        $res_formas_pg = mysql_query($sql_formas_pg);
                        $num_formas_pg = mysql_num_rows($res_formas_pg);
                        
                        $total_geral_tel = 0;
                        $total_geral_net = 0;
                        $total_geral = 0;
                        
                        for($a = 0; $a < $num_formas_pg; $a++)
                        {
                            $obj_formas_pg = mysql_fetch_object($res_formas_pg);

                            $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(valor_total) AS soma_tel FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial' AND '$data_final' AND p.forma_pg = '" . $obj_formas_pg->forma_pg . "' AND p.origem_pedido = 'TEL' AND p.situacao = 'BAIXADO' AND p.cod_pizzarias = $cod_pizzarias", $con_web);
                            $soma_tel = $objBuscaPedidosSoma->soma_tel;
                            $total_geral_tel += $soma_tel;
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . $obj_formas_pg->forma_pg . ' TEL: ' . bd2moeda($soma_tel) . "</linha>";
                            
                            $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(valor_total) AS soma_net FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial' AND '$data_final' AND p.forma_pg = '" . $obj_formas_pg->forma_pg . "' AND p.origem_pedido = 'NET' AND p.situacao = 'BAIXADO' AND p.cod_pizzarias = $cod_pizzarias", $con_web);
                            $soma_net = $objBuscaPedidosSoma->soma_net;
                            $total_geral_net += $soma_net;
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . $obj_formas_pg->forma_pg . ' NET: ' . bd2moeda($soma_net) . "</linha>";
                            
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>" . $obj_formas_pg->forma_pg . ' TOTAL: ' . bd2moeda($soma_tel + $soma_net) . "</linha>";
                            $total_geral += $soma_tel + $soma_net;
                        }
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TOTAL GERAL TEL: " . bd2moeda($total_geral_tel) . "</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TOTAL GERAL NET: " . bd2moeda($total_geral_net) . "</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>TOTAL GERAL: " . bd2moeda($total_geral) . "</linha>";
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>================================================</linha>";
                        
                        // Relatório de formas de pagamento - pendentes
                        $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='2' corte='0'>FORMA DE PAGAMENTO (ABERTO)</linha>";
                        
                        $sql_formas_pg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
                        $res_formas_pg = mysql_query($sql_formas_pg);
                        $num_formas_pg = mysql_num_rows($res_formas_pg);
                        
                        $total_geral_tel = 0;
                        $total_geral_net = 0;
                        $total_geral = 0;
                        
                        for($a = 0; $a < $num_formas_pg; $a++)
                        {
                            $obj_formas_pg = mysql_fetch_object($res_formas_pg);

                            $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(valor_total) AS soma_tel FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial' AND '$data_final' AND p.forma_pg = '" . $obj_formas_pg->forma_pg . "' AND p.origem_pedido = 'TEL' AND p.situacao NOT IN ('BAIXADO', 'CANCELADO') AND p.cod_pizzarias = $cod_pizzarias", $con_web);
                            $soma_tel = $objBuscaPedidosSoma->soma_tel;
                            $total_geral_tel += $soma_tel;
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . $obj_formas_pg->forma_pg . ' TEL: ' . bd2moeda($soma_tel) . "</linha>";
                            
                            $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(valor_total) AS soma_net FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '$data_inicial' AND '$data_final' AND p.forma_pg = '" . $obj_formas_pg->forma_pg . "' AND p.origem_pedido = 'NET' AND p.situacao NOT IN ('BAIXADO', 'CANCELADO') AND p.cod_pizzarias = $cod_pizzarias", $con_web);
                            $soma_net = $objBuscaPedidosSoma->soma_net;
                            $total_geral_net += $soma_net;
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . $obj_formas_pg->forma_pg . ' NET: ' . bd2moeda($soma_net) . "</linha>";
                            
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>" . $obj_formas_pg->forma_pg . ' TOTAL: ' . bd2moeda($soma_tel + $soma_net) . "</linha>";
                            $total_geral += $soma_tel + $soma_net;
                        }
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TOTAL GERAL TEL: " . bd2moeda($total_geral_tel) . "</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TOTAL GERAL NET: " . bd2moeda($total_geral_net) . "</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>TOTAL GERAL: " . bd2moeda($total_geral) . "</linha>";
                        
                        // Relatório de quantidade vendidas
                        $sql_buscar_pizzarias = " AND p.cod_pizzarias = '$cod_pizzarias'";
                        $sql_data_hora_pedido = " AND p.data_hora_pedido BETWEEN '$data_inicial' AND '$data_final' ";
                        $sql_situacao_pedido = " AND p.situacao NOT IN ('CANCELADO') ";
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>================================================</linha>";
                        
                        $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='2' corte='0'>QUANTIDADES VENDIDAS (ABERTOS + BAIXADOS)</linha>";

                        // Realizando a contatem de vendidos para todos os tamanhos
                        $sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
                        $res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);
                        
                        while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
                        {
                            $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = $obj_buscar_tamanhos->cod_tamanhos AND p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
                            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
                        
                            $total_pizza_tam_tel = $obj_quant_pizzas->total_tel;
                        
                            $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE pp.cod_tamanhos = $obj_buscar_tamanhos->cod_tamanhos AND p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                            $res_quant_pizzas = mysql_query($sql_quant_pizzas);
                            $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
                        
                            $total_pizza_tam_net = $obj_quant_pizzas->total_net;
                        
                            $total_pizza_tam = $total_pizza_tam_tel + $total_pizza_tam_net;
                        
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . strtoupper($obj_buscar_tamanhos->tamanho) . " TEL: $total_pizza_tam_tel</linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . strtoupper($obj_buscar_tamanhos->tamanho) . " NET: $total_pizza_tam_net</linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>" . strtoupper($obj_buscar_tamanhos->tamanho) . " TOTAL: $total_pizza_tam</linha>";
                        }

                        // Quantidade de pizzas vendidas
                        $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido = 'TEL' AND pp.fidelidade = 0 AND pp.promocional = 0 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_pizzas = mysql_query($sql_quant_pizzas);
                        $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
                        
                        $total_pizza_tel_vend = $obj_quant_pizzas->total_tel;
                        
                        $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido = 'NET' AND pp.fidelidade = 0 AND pp.promocional = 0 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_pizzas = mysql_query($sql_quant_pizzas);
                        $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
                        
                        $total_pizza_net_vend = $obj_quant_pizzas->total_net;
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT PIZZAS VENDIDAS TEL: $total_pizza_tel_vend</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT PIZZAS VENDIDAS NET: $total_pizza_net_vend</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>QUANT PIZZAS VENDIDAS TOTAL: " . ($total_pizza_tel_vend + $total_pizza_net_vend) . "</linha>";

                        // Quantidade de pizzas promocionais
                        $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido = 'TEL' AND pp.fidelidade = 0 AND pp.promocional = 1 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_pizzas = mysql_query($sql_quant_pizzas);
                        $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
                        
                        $total_pizza_tel_promo = $obj_quant_pizzas->total_tel;
                        
                        $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido = 'NET' AND pp.fidelidade = 0 AND pp.promocional = 1 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_pizzas = mysql_query($sql_quant_pizzas);
                        $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
                        
                        $total_pizza_net_promo = $obj_quant_pizzas->total_net;
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT PIZZAS PROMO TEL: $total_pizza_tel_promo</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT PIZZAS PROMO NET: $total_pizza_net_promo</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>QUANT PIZZAS PROMO TOTAL: " . ($total_pizza_tel_promo + $total_pizza_net_promo) . "</linha>";

                        // Quantidade de pizza fidelidade
                        $sql_quant_pizzas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido = 'TEL' AND pp.fidelidade = 1 AND pp.promocional = 0 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_pizzas = mysql_query($sql_quant_pizzas);
                        $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
                        
                        $total_pizza_tel_fidel = $obj_quant_pizzas->total_tel;
                        
                        $sql_quant_pizzas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) WHERE p.origem_pedido = 'NET' AND pp.fidelidade = 1 AND pp.promocional = 0 $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_pizzas = mysql_query($sql_quant_pizzas);
                        $obj_quant_pizzas = mysql_fetch_object($res_quant_pizzas);
                        
                        $total_pizza_net_fidel = $obj_quant_pizzas->total_net;
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT PIZZAS FIDELIDADE TEL: $total_pizza_tel_fidel</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT PIZZAS FIDELIDADE NET: $total_pizza_net_fidel</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>QUANT PIZZAS FIDELIDADE TOTAL: " . ($total_pizza_tel_fidel + $total_pizza_net_fidel) . "</linha>";
                        
                        // Quant. frações
                        $sql_quant_fracoes_salgada = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'TEL' AND pi.tipo = 'Salgado' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_fracoes_salgada = mysql_query($sql_quant_fracoes_salgada);
                        $obj_quant_fracoes_salgada = mysql_fetch_object($res_quant_fracoes_salgada);
                        
                        $total_quant_fracoes_salgada_tel = $obj_quant_fracoes_salgada->total_tel;
                        
                        $sql_quant_fracoes_salgada = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'NET' AND pi.tipo = 'Salgado' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_fracoes_salgada = mysql_query($sql_quant_fracoes_salgada);
                        $obj_quant_fracoes_salgada = mysql_fetch_object($res_quant_fracoes_salgada);
                        
                        $total_quant_fracoes_salgada_net = $obj_quant_fracoes_salgada->total_net;
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT SABORES SALGADA TEL: $total_quant_fracoes_salgada_tel</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT SABORES SALGADA NET: $total_quant_fracoes_salgada_net</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>QUANT SABORES SALGADA TOTAL: " . ($total_quant_fracoes_salgada_tel + $total_quant_fracoes_salgada_net) . "</linha>";
                        
                        $sql_quant_fracoes_doce = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'TEL' AND pi.tipo = 'Doce' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_fracoes_doce = mysql_query($sql_quant_fracoes_doce);
                        $obj_quant_fracoes_doce = mysql_fetch_object($res_quant_fracoes_doce);
                        
                        $total_quant_fracoes_doce_tel = $obj_quant_fracoes_doce->total_tel;
                        
                        $sql_quant_fracoes_doce = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.origem_pedido = 'NET' AND pi.tipo = 'Doce' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_fracoes_doce = mysql_query($sql_quant_fracoes_doce);
                        $obj_quant_fracoes_doce = mysql_fetch_object($res_quant_fracoes_doce);
                        
                        $total_quant_fracoes_doce_net = $obj_quant_fracoes_doce->total_net;
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT SABORES DOCE TEL: $total_quant_fracoes_doce_tel</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT SABORES DOCE NET: $total_quant_fracoes_doce_net</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>QUANT SABORES DOCE TOTAL: " . ($total_quant_fracoes_doce_tel + $total_quant_fracoes_doce_net) . "</linha>";
                        
                        // Quant. bordas
                        $sql_quant_bordas = "SELECT COUNT(*) AS total_tel FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE p.origem_pedido = 'TEL' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_bordas = mysql_query($sql_quant_bordas);
                        $obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
                        
                        $total_borda_tel = $obj_quant_bordas->total_tel;
                        
                        $sql_quant_bordas = "SELECT COUNT(*) AS total_net FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE p.origem_pedido = 'NET' $sql_buscar_pizzarias $sql_data_hora_pedido $sql_situacao_pedido";
                        $res_quant_bordas = mysql_query($sql_quant_bordas);
                        $obj_quant_bordas = mysql_fetch_object($res_quant_bordas);
                        
                        $total_borda_net = $obj_quant_bordas->total_net;
                        
                        $total_borda = $total_borda_tel + $total_borda_net;
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT BORDAS TEL: $total_borda_tel</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT BORDAS NET: $total_borda_net</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>QUANT BORDAS TOTAL: $total_borda</linha>";
                        
                        $rel_fechamento .= '<tr>';
                        
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
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT GERGELIM TEL: $total_adicionais_tel</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT GERGELIM NET: $total_adicionais_net</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>QUANT GERGELIM TOTAL: $total_adicionais</linha>";
                        
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
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT ADICIONAIS TEL: $total_ingred_adicionais_tel</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>QUANT ADICIONAIS NET: $total_ingred_adicionais_net</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>QUANT ADICIONAIS TOTAL: $total_ingred_adicionais</linha>";
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>================================================</linha>";
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='10' corte='0'>www.internetsistemas.com.br</linha>";
                    }
                    else
                    {
                        $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='2' corte='0'>RELATORIO DE CAIXA</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>Erro, nenhum caixa associado.</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='10' corte='0'>www.internetsistemas.com.br</linha>";
                    }
                }
                elseif ($obj_relatorio->relatorio == 'ENTREGADOR')
                {
                    if ($obj_relatorio->cod_entregadores > 0)
                    {
                        $obj_entregador = executaBuscaSimples("SELECT *, p.nome AS nome_pizzaria, e.nome AS nome_entregador FROM ipi_entregadores e INNER JOIN ipi_pizzarias p ON (e.cod_pizzarias = p.cod_pizzarias) WHERE e.cod_entregadores = '" . $obj_relatorio->cod_entregadores . "'", $con_web);
                        
                        $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='2' corte='0'>RELATORIO DE ENTREGADOR</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>ENTREGADOR: " . bd2texto($obj_entregador->nome_entregador) . "</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PIZZARIA: " . bd2texto($obj_entregador->nome_pizzaria) . "</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>DATA INICIAL: " . bd2datahora($obj_relatorio->data_hora_inicial) . "</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>DATA FINAL: " . bd2datahora($obj_relatorio->data_hora_final) . "</linha>";
                        
                        $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>ENTREGAS DE PEDIDOS</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
                        
                        $sql_buscar_dias = "SELECT DISTINCT DATE(data_hora_pedido) AS data_pedido FROM ipi_pedidos WHERE cod_entregadores = '" . $obj_relatorio->cod_entregadores . "' AND data_hora_pedido BETWEEN '" . $obj_relatorio->data_hora_inicial . "' AND '" . $obj_relatorio->data_hora_final . "' ORDER BY cod_pedidos";
                        $res_buscar_dias = mysql_query($sql_buscar_dias);
                        
                        $total_entregas_pedidos = 0;
                        $total_frete = 0;
                        while ( $obj_buscar_dias = mysql_fetch_object($res_buscar_dias) )
                        {
                            
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'></linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>DIA " . bd2data($obj_buscar_dias->data_pedido) . "</linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>------------------------------------------------</linha>";
                            
                            $sql_buscar_pedidos = "SELECT * FROM ipi_pedidos WHERE cod_entregadores = '" . $obj_relatorio->cod_entregadores . "' AND data_hora_pedido BETWEEN '" . $obj_buscar_dias->data_pedido . " 00:00:00' AND '" . $obj_buscar_dias->data_pedido . " 23:59:59' ORDER BY cod_pedidos";
                            $res_buscar_pedidos = mysql_query($sql_buscar_pedidos);
                            
                            while ( $obj_buscar_pedidos = mysql_fetch_object($res_buscar_pedidos) )
                            {
                                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>* " . sprintf('%08d', $obj_buscar_pedidos->cod_pedidos) . " - " . filtrar_caracteres(strtoupper(bd2texto($obj_buscar_pedidos->endereco))) . ", " . filtrar_caracteres(strtoupper(bd2texto($obj_buscar_pedidos->bairro))) . " Taxa: R$ ".bd2moeda($obj_buscar_pedidos->valor_comissao_frete)."</linha>";
                                
                                $total_entregas_pedidos++;
																$total_frete += $obj_buscar_pedidos->valor_comissao_frete;
                            }
                        }
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'></linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>ENTREGAS AVULSAS</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
                        
                        $sql_buscar_dias = "SELECT DISTINCT DATE(data_hora_entrega) AS data_entrega FROM ipi_entregas_avulsas WHERE cod_entregadores = '" . $obj_relatorio->cod_entregadores . "' AND data_hora_entrega BETWEEN '" . $obj_relatorio->data_hora_inicial . "' AND '" . $obj_relatorio->data_hora_final . "' and tipo_entrega='ENTREGA' ORDER BY cod_entregas_avulsas";
                        $res_buscar_dias = mysql_query($sql_buscar_dias);
                        
                        $total_entregas_avulsas = 0;
                        $total_valor = 0;
                        while ( $obj_buscar_dias = mysql_fetch_object($res_buscar_dias) )
                        {
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'></linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>DIA " . bd2data($obj_buscar_dias->data_entrega) . "</linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>------------------------------------------------</linha>";
                            
                            $sql_buscar_entregas = "SELECT * FROM ipi_entregas_avulsas WHERE cod_entregadores = '" . $obj_relatorio->cod_entregadores . "' AND data_hora_entrega BETWEEN '" . $obj_buscar_dias->data_entrega . " 00:00:00' AND '" . $obj_buscar_dias->data_entrega . " 23:59:59' and tipo_entrega='ENTREGA' ORDER BY cod_entregas_avulsas";
                            $res_buscar_entregas = mysql_query($sql_buscar_entregas);
                            
                            while ( $obj_buscar_entregas = mysql_fetch_object($res_buscar_entregas) )
                            {
                                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>* " . sprintf('%08d', $obj_buscar_entregas->cod_pedidos) . " - " . filtrar_caracteres(strtoupper(bd2texto($obj_buscar_entregas->bairro))) . ' - ' . filtrar_caracteres(strtoupper(bd2texto($obj_buscar_entregas->obs_entrega_avulsa))) . " Valor: R$ ".bd2moeda($obj_buscar_entregas->valor)."</linha>";
                                
                                $total_entregas_avulsas++;
                                $total_valor += $obj_buscar_entregas->valor;
                            }
                        }
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'></linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>DIÁRIAS</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
                        
                        $sql_buscar_dias = "SELECT *,data_hora_entrega AS data_entrega FROM ipi_entregas_avulsas WHERE cod_entregadores = '" . $obj_relatorio->cod_entregadores . "' AND data_hora_entrega BETWEEN '" . $obj_relatorio->data_hora_inicial . "' AND '" . $obj_relatorio->data_hora_final . "' and tipo_entrega='DIARIA' ORDER BY data_entrega";
                        $res_buscar_dias = mysql_query($sql_buscar_dias);
                        
                        $total_dias = 0;
                        $total_diarias = 0;
                        while ( $obj_buscar_dias = mysql_fetch_object($res_buscar_dias) )
                        {
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'></linha>";
                            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>DIA " . date("d/m/Y",strtotime($obj_buscar_dias->data_entrega)) . " Valor da diária: R$ ".bd2moeda($obj_buscar_dias->valor)."</linha>";
                           // $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>* " . sprintf('%08d', $obj_buscar_entregas->cod_pedidos) . " - " . filtrar_caracteres(strtoupper(bd2texto($obj_buscar_entregas->bairro))) . ' - ' . filtrar_caracteres(strtoupper(bd2texto($obj_buscar_entregas->obs_entrega_avulsa))) . " Valor: R$ ".bd2moeda($obj_buscar_entregas->valor)."</linha>";
                            
                            $total_dias++;
                            $total_diarias += $obj_buscar_dias->valor;
                            
                        }
                        
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'></linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>TOTAL DE ENTREGAS</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>================================================</linha>";
                        
                        $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>TOTAL DE PEDIDOS: $total_entregas_pedidos</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>TOTAL DE AVULSOS: $total_entregas_avulsas</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>TOTAL DE PEDIDOS + AVULSOS: " . ($total_entregas_pedidos + $total_entregas_avulsas) . "</linha>";
												$detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>TAXA DE ENTREGA: R$ " . bd2moeda($total_frete) . "</linha>";
												$detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>VALOR ENTREGAS AVULSAS: R$ " . bd2moeda($total_valor) . "</linha>";
												$detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>VALOR DAS DIÁRIAS: R$ " . bd2moeda($total_diarias) . "</linha>";
												$detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>COMISSÃO / DIÁRIA / DESCONTO R$ " . bd2moeda($obj_relatorio->valor_extra) . "</linha>";
												$detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='3' corte='0'>VALOR TOTAL R$ " . bd2moeda(($obj_relatorio->valor_extra + $total_diarias + $total_frete + $total_valor)) . "</linha>";
												$detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='3' corte='0'>".filtrar_caracteres(("Recebi da COMIC COMÉRCIO DE ALIMENTOS LTDA, a importância acima, referente aos serviços de entrega neste período"))."</linha>";

												
												$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>____________________________</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . bd2texto($obj_entregador->nome_entregador) . "</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='3' corte='0'></linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='10' corte='0'>www.internetsistemas.com.br</linha>";
                    }
                    else
                    {
                        $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='2' corte='0'>RELATORIO DE ENTREGADOR</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>Erro, nenhum entregador associado.</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='10' corte='0'>www.internetsistemas.com.br</linha>";
                    }
                } elseif ($obj_relatorio->relatorio == 'DESPACHO')
                {
										
										if ($obj_relatorio->cod_pedidos > 0)
                    {
											$obj_entregador = executaBuscaSimples("SELECT *,e.nome AS nome_entregador FROM ipi_entregadores e WHERE e.cod_entregadores = '" . $obj_relatorio->cod_entregadores . "'", $con_web);
											
											$obj_pedido = executaBuscaSimples("SELECT pe.*, c.celular, c.nome as nome_cliente, p.nome AS nome_pizzaria, p.telefone_1 AS telefone_pizzaria, p.endereco AS endereco_pizzaria,p.numero AS numero_pizzaria,p.bairro as bairro_pizzaria,pe.referencia_endereco as referencia_endereco,pe.referencia_cliente as referencia_cliente  FROM ipi_pedidos pe INNER JOIN ipi_pizzarias p ON (pe.cod_pizzarias = p.cod_pizzarias) INNER JOIN ipi_clientes c on c.cod_clientes = pe.cod_clientes WHERE pe.cod_pedidos= '" . $obj_relatorio->cod_pedidos . "'", $con_web);
											
											$cod_pedidos = $obj_relatorio->cod_pedidos;
											
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>".filtrar_caracteres(bd2texto(NOME_FANTASIA))."</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>".filtrar_caracteres(bd2texto($obj_pedido->endereco_pizzaria." n.:".$obj_pedido->numero_pizzaria.",".$obj_pedido->bairro_pizzaria))."</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>".filtrar_caracteres(bd2texto($obj_pedido->telefone_pizzaria))."</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>COMPROVANTE DE ENTREGA</linha>";

											
											$total_pedido = 0;
											/*$sql_pizzas = "SELECT pi.*,t.* FROM ipi_pedidos_pizzas pi INNER JOIN ipi_pedidos pe ON(pi.cod_pedidos = pe.cod_pedidos) INNER JOIN ipi_tamanhos t on t.cod_tamanhos = pi.cod_tamanhos WHERE pi.cod_pedidos = '$cod_pedidos'";
											$res_pizzas = mysql_query($sql_pizzas);
											if(mysql_error($con_web)!="")
											{
												file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
											}
											$numero_pizza = 1;
											
											while ( $obj_pizzas = mysql_fetch_object($res_pizzas) )
											{
												$sql_fracoes = "SELECT *,pf.preco as preco_fracao FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pizzas pz ON (pf.cod_pizzas = pz.cod_pizzas) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pp.cod_pedidos = '" . $obj_pizzas->cod_pedidos . "' AND pp.cod_pedidos_pizzas = '" . $obj_pizzas->cod_pedidos_pizzas . "'";
												
												$res_fracoes = mysql_query($sql_fracoes);
												if(mysql_error($con_web)!="")
												{
													file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
												}
												while ( $obj_fracoes = mysql_fetch_object($res_fracoes) )
												{
														$preco_fracao = 0;
														$preco_fracao += $obj_fracoes->preco_fracao;
														//$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>PIZZA: $numero_pizza - PARTE " . $obj_fracoes->fracao . '/' . $obj_fracoes->quant_fracao . ': ' . filtrar_caracteres(bd2texto($obj_fracoes->pizza)) . "</linha>";
														$linha_item = bd2texto($obj_fracoes->cod_pizzas)." - ". filtrar_caracteres(bd2texto($obj_fracoes->pizza)) . " ".filtrar_caracteres(bd2texto($obj_pizzas->tamanho));
														$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>";
														
														// Ingredientes retirados
														//$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>RETIRAR:</linha>";
														
														$sql_ingredientes_retirar = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = '" . $obj_fracoes->cod_pedidos . "' AND pi.cod_pedidos_pizzas = '" . $obj_fracoes->cod_pedidos_pizzas . "' AND pi.cod_pedidos_fracoes = '" . $obj_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 1) AND p.cod_pizzas = '" . $obj_fracoes->cod_pizzas . "' AND i.consumo = 0";
														$res_ingredientes_retirar = mysql_query($sql_ingredientes_retirar);
														if(mysql_error($con_web)!="")
														{
															file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
														}
														while ( $obj_ingredientes_retirar = mysql_fetch_object($res_ingredientes_retirar) )
														{
																//$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='1' quebralinha='1' corte='0'>SEM " . filtrar_caracteres(bd2texto($obj_ingredientes_retirar->ingrediente)) . "</linha>"
																;
																$linha_item .= " S/ " . filtrar_caracteres(bd2texto($obj_ingredientes_retirar->ingrediente)) . " ";
														}
														
														
														$sql_ingredientes_adicionar = "SELECT *,pi.preco as preco_ingrediente FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_ingredientes pzi ON (pi.cod_ingredientes = pzi.cod_ingredientes) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = '" . $obj_fracoes->cod_pedidos . "' AND pi.cod_pedidos_pizzas = '" . $obj_fracoes->cod_pedidos_pizzas . "' AND pi.cod_pedidos_fracoes = '" . $obj_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 0";
														$res_ingredientes_adicionar = mysql_query($sql_ingredientes_adicionar);
														if(mysql_error($con_web)!="")
														{
															file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
														}
														while ( $obj_ingredientes_adicionar = mysql_fetch_object($res_ingredientes_adicionar) )
														{
																$linha_item .= " C/ " . filtrar_caracteres(bd2texto($obj_ingredientes_adicionar->ingrediente)) . " ";
																$preco_fracao += $obj_ingredientes_adicionar->preco_ingrediente;
														}
													$total_pedido += $preco_fracao;

														/*if(strlen($linha_item)>50)
														{
																$linha_fim = '';
																$qtd_quebras = (floor(strlen($linha_item)/50)+1);

																for($l=0;$l<$qtd_quebras;$l++)
																{
																		$linha_fim .= substr($linha_item,($l*50),50);

																		if($l!=$qtd_quebras-1)
																		{
																				$linha_fim .= " \n";
																		}

																	 // $linha_fim .= ($l*50)."-0 \n".(50*($l+1))."-1 \n";
																}
																$linha_item = $linha_fim;
														}*/
														
														//$detalhes .= $linha_item."</linha>";
														
											/*	}
											}*/
											
											$sql_pizzas = "SELECT * FROM ipi_pedidos_pizzas pi INNER JOIN ipi_pedidos pe ON(pi.cod_pedidos = pe.cod_pedidos) WHERE pi.cod_pedidos = '$cod_pedidos'";
											$res_pizzas = mysql_query($sql_pizzas);
											if(mysql_error($con_web)!="")
											{
												file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
											}
											$numero_pizza = 1;
											
											while ( $obj_pizzas = mysql_fetch_object($res_pizzas) )
											{
													$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PIZZA: ${numero_pizza}</linha>";
													
													// Tamanho da Pizza
													$sql_tamanhos = "SELECT tamanho FROM ipi_tamanhos WHERE cod_tamanhos = '" . $obj_pizzas->cod_tamanhos . "'";
													$res_tamanhos = mysql_query($sql_tamanhos);
													$obj_tamanhos = mysql_fetch_object($res_tamanhos);
													if(mysql_error($con_web)!="")
													{
														file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
													}
									
													$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TAMANHO: " . filtrar_caracteres(bd2texto($obj_tamanhos->tamanho)) . "</linha>";
													
													/*// Massa
													$sql_massa = "SELECT tipo_massa FROM ipi_tipo_massa WHERE cod_tipo_massa = '" . $obj_pizzas->cod_tipo_massa . "'";
													$res_massa = mysql_query($sql_massa);
													if(mysql_error($con_web)!="")
													{
														file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
													}
													$obj_massa = mysql_fetch_object($res_massa);
													$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>MASSA: " . filtrar_caracteres(bd2texto($obj_massa->tipo_massa)) . "</linha>";*/

													// Corte
													$sql_corte = "SELECT opcao_corte FROM ipi_opcoes_corte WHERE cod_opcoes_corte = '" . $obj_pizzas->cod_opcoes_corte . "'";
													$res_corte = mysql_query($sql_corte);
													$obj_corte = mysql_fetch_object($res_corte);
													if(mysql_error($con_web)!="")
													{
														file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
													}
													$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>CORTE: " . filtrar_caracteres(bd2texto($obj_corte->opcao_corte)) . "</linha>";
													
													/*// Borda
													$sql_borda = "SELECT * FROM ipi_pedidos_bordas pb INNER JOIN ipi_bordas bo ON(pb.cod_bordas=bo.cod_bordas) WHERE pb.cod_pedidos_pizzas = '" . $obj_pizzas->cod_pedidos_pizzas . "' AND pb.cod_pedidos = '" . $obj_pizzas->cod_pedidos . "'";
													$res_borda = mysql_query($sql_borda);
													$num_borda = mysql_num_rows($res_borda);
													if(mysql_error($con_web)!="")
													{
														file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
													}
													$obj_borda = mysql_fetch_object($res_borda);
													
													$borda_gratis = ($obj_borda->promocional == 1) ? ' (GRATIS)' : '';
													$borda_fidelidade = ($obj_borda->fidelidade == 1) ? ' (FIDELIDADE)' : '';
													$borda_combo = ($obj_borda->combo == 1) ? ' (COMBO)' : '';*/
													
													/*$detalhes .= ($num_borda > 0) ? "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>BORDA: " . filtrar_caracteres(bd2texto($obj_borda->borda)) . "${borda_gratis}${borda_fidelidade}${borda_combo}</linha>" : "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>BORDA: NAO</linha>";*/
													
												/*	// Adicional (Gergelim)
													$sql_adicional = "SELECT * FROM ipi_pedidos_adicionais pa INNER JOIN ipi_adicionais ad ON(pa.cod_adicionais = ad.cod_adicionais) WHERE cod_pedidos = '" . $obj_pizzas->cod_pedidos . "' AND cod_pedidos_pizzas = '" . $obj_pizzas->cod_pedidos_pizzas . "'";
													$res_adicional = mysql_query($sql_adicional);
													if(mysql_error($con_web)!="")
													{
														file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
													}
													$num_adicional = mysql_num_rows($res_adicional);
													$obj_adicional = mysql_fetch_object($res_adicional);
													
													$detalhes .= ($num_adicional > 0) ? "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>GERGELIM: " . filtrar_caracteres(bd2texto($obj_adicional->adicional)) . "</linha>" : "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>GERGELIM: NAO</linha>";*/
													
													// Fraçoes
													$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>=======================================</linha>";
													
													$sql_fracoes = "SELECT * FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pizzas pz ON (pf.cod_pizzas = pz.cod_pizzas) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pp.cod_pedidos = '" . $obj_pizzas->cod_pedidos . "' AND pp.cod_pedidos_pizzas = '" . $obj_pizzas->cod_pedidos_pizzas . "'";
													$res_fracoes = mysql_query($sql_fracoes);
													if(mysql_error($con_web)!="")
													{
														file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
													}
													while ( $obj_fracoes = mysql_fetch_object($res_fracoes) )
													{
															$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PIZZA: $numero_pizza - PARTE " . $obj_fracoes->fracao . '/' . $obj_fracoes->quant_fracao .  "</linha>";
															
															$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . filtrar_caracteres(bd2texto($obj_fracoes->pizza));

															$sql_ingredientes_retirar = "SELECT i.ingrediente FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = '" . $obj_fracoes->cod_pedidos . "' AND pi.cod_pedidos_pizzas = '" . $obj_fracoes->cod_pedidos_pizzas . "' AND pi.cod_pedidos_fracoes = '" . $obj_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 1) AND p.cod_pizzas = '" . $obj_fracoes->cod_pizzas . "' AND i.consumo = 0";
															$res_ingredientes_retirar = mysql_query($sql_ingredientes_retirar);
															if(mysql_error($con_web)!="")
															{
																file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
															}
															while ( $obj_ingredientes_retirar = mysql_fetch_object($res_ingredientes_retirar) )
															{
																	$detalhes .= " S/  " . filtrar_caracteres(bd2texto($obj_ingredientes_retirar->ingrediente));
															}
													
															$sql_ingredientes_adicionar = "SELECT pzi.ingrediente,(select ingrediente from ipi_ingredientes where cod_ingredientes = pi.cod_ingrediente_trocado) as nome_trocado FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_ingredientes pzi ON (pi.cod_ingredientes = pzi.cod_ingredientes) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = '" . $obj_fracoes->cod_pedidos . "' AND pi.cod_pedidos_pizzas = '" . $obj_fracoes->cod_pedidos_pizzas . "' AND pi.cod_pedidos_fracoes = '" . $obj_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 0";
															$res_ingredientes_adicionar = mysql_query($sql_ingredientes_adicionar);
															if(mysql_error($con_web)!="")
															{
																file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
															}
															while ( $obj_ingredientes_adicionar = mysql_fetch_object($res_ingredientes_adicionar) )
															{
																	$detalhes .= " C/ " . filtrar_caracteres(bd2texto($obj_ingredientes_adicionar->ingrediente));

																	if($obj_ingredientes_adicionar->nome_trocado!="")
																	{
																			$detalhes .= "(TROCA ".$obj_ingredientes_adicionar->nome_trocado.")";
																	} 

																	
															}
															$detalhes .= "</linha>";
																									
															if ($obj_fracoes->fracao < $obj_fracoes->quant_fracao)
															{
																	$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>---------------------------------------</linha>";
															}
													}
													
													$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>=======================================</linha>";
													
													$numero_pizza++;
											}
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'></linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>BEBIDAS</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>--------------------------------------</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>Qtd.       Bebida</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>--------------------------------------</linha>";
											$sql_bebidas = "SELECT *,pb.preco as preco_bebida FROM ipi_pedidos_bebidas pb INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_pedidos pe ON(pb.cod_pedidos = pe.cod_pedidos) WHERE pb.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
											$res_bebidas = mysql_query($sql_bebidas);
											if(mysql_error($con_web)!="")
											{
													file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
											}
											while ( $obj_bebidas = mysql_fetch_object($res_bebidas) )
											{
													/*$bebida_gratis = ($obj_bebidas->promocional == 1) ? ' (GRATIS)' : '';
													$bebida_fidelidade = ($obj_bebidas->fidelidade == 1) ? ' (FIDELIDADE)' : '';
													$bebida_combo = ($obj_bebidas->combo == 1) ? ' (COMBO)' : '';*/
													
													$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>".$obj_bebidas->quantidade . ' - ' . $obj_bebidas->bebida . ' ' . $obj_bebidas->conteudo . " </linha>";//       ".("${bebida_gratis}${bebida_fidelidade}"!="" ? "${bebida_gratis}${bebida_fidelidade}${bebida_combo}" : bd2moeda($obj_bebidas->preco_bebida))."
											}
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'></linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>FORMA PAGAMENTO: " . filtrar_caracteres(bd2texto($obj_pedido->forma_pg)) . "</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='1' corte='0'>TOTAL DO PEDIDO: " . bd2moeda($obj_pedido->valor_total) . "</linha>";
											if ($obj_pedido->forma_pg == 'DINHEIRO')
											{
													// Troco
													$sql_troco = "SELECT * FROM ipi_pedidos_detalhes_pg WHERE CHAVE = 'TROCO' AND cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
													$res_troco = mysql_query($sql_troco);
													if(mysql_error($con_web)!="")
													{
														file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
													}
													$num_troco = mysql_num_rows($res_troco);
													$obj_troco = mysql_fetch_object($res_troco);
													
													if ($num_troco > 0)
													{
															$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TROCO PARA: R$" . bd2moeda($obj_troco->conteudo) . "</linha>";
															$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TROCO: R$" . bd2moeda((((float) $obj_troco->conteudo) - ((float) $obj_pedido->valor_total))) . "</linha>";
													}
											}
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'></linha>";
											$detalhes .= "<linha formatacao='n' centralizado='1' quadrado='0' quebralinha='2' corte='0'>PEDIDO " . sprintf("%08d", $cod_pedidos) . "</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>HORARIO: " . bd2datahora($obj_pedido->data_hora_pedido) . "</linha>";

											// Tempo de entrega
											if ($obj_pedido->agendado == 0)
											{
													$sql_tempo = "SELECT TIME(DATE_ADD('" . $obj_pedido->data_hora_pedido . "', INTERVAL tempo_entrega MINUTE)) AS tempo_entrega FROM ipi_pizzarias_horarios WHERE cod_pizzarias = '" . $obj_pedido->cod_pizzarias . "' AND horario_inicial_entrega <= TIME('" . $obj_pedido->data_hora_pedido . "') AND horario_final_entrega >= TIME('" . $obj_pedido->data_hora_pedido . "') AND dia_semana = '" . date('w', strtotime($obj_pedido->data_hora_pedido)) . "'";
													$res_tempo = mysql_query($sql_tempo);
													if(mysql_error($con_web)!="")
													{
														file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
													}
													$num_tempo = mysql_num_rows($res_tempo);
													$obj_tempo = mysql_fetch_object($res_tempo);
													
													$detalhes .= ($num_tempo > 0) ? "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>HORA DA ENTREGA: " . filtrar_caracteres($obj_tempo->tempo_entrega) . "</linha>" : "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>HORA DA ENTREGA: NAO ENCONTRADO</linha>";
											}
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>ENTREGADOR: " . filtrar_caracteres(bd2texto($obj_entregador->nome_entregador)) . "</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>CLIENTE: " . filtrar_caracteres(bd2texto($obj_pedido->nome_cliente)) . "</linha>";
                      if($obj_pedido->celular!="")
                      {
                        $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>CELULAR: " . filtrar_caracteres(bd2texto($obj_pedido->celular)) . "</linha>";
                      }                 
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TEL: " . filtrar_caracteres(bd2texto($obj_pedido->telefone_1)) . "</linha>";
                      if($obj_pedido->telefone_2!="")
                      {
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>CEL: " . filtrar_caracteres(bd2texto($obj_pedido->telefone_2)) . "</linha>";
                      }
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>END.: " . filtrar_caracteres(bd2texto($obj_pedido->endereco)) . "</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>NUM.: " . filtrar_caracteres(bd2texto($obj_pedido->numero)) . "</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>COMP.: " . filtrar_caracteres(bd2texto($obj_pedido->complemento)) . "</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>EDIF.: " . filtrar_caracteres(bd2texto($obj_pedido->edificio)) . "</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>BAIRRO: " . filtrar_caracteres(bd2texto($obj_pedido->bairro)) . "</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PONTO DE REFERENCIA: " . filtrar_caracteres(bd2texto($obj_pedido->referencia_endereco)) . "</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>REF. DO CLIENTE: " . filtrar_caracteres(bd2texto($obj_pedido->referencia_cliente)) . "</linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='3' corte='0'></linha>";
											$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='10' corte='1'>www.internetsistemas.com.br</linha>";
										}
								}elseif ($obj_relatorio->relatorio == 'CANCELAMENTO')
                {
                    if ($obj_relatorio->cod_pedidos > 0)
                    {
                        $obj_entregador = executaBuscaSimples("SELECT p.*, pi.nome AS nome_pizzaria,usu.nome as nome_usuario_cancelador  FROM ipi_pedidos p inner join ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) INNER JOIN nuc_usuarios usu on usu.cod_usuarios = p.cod_usuarios_cancelamento WHERE p.cod_pedidos = '" . $obj_relatorio->cod_pedidos . "' and p.situacao = 'CANCELADO'", $con_web);
                        
                        $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='2' corte='0'>PEDIDO CANCELADO</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PEDIDO: " . bd2texto($obj_entregador->cod_pedidos) . "</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>HORARIO DO CANCELAMENTO: " . bd2datahora($obj_entregador->data_hora_baixa) . "</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>CANCELADO POR: " . bd2texto($obj_entregador->nome_usuario_cancelador) . "</linha>";

                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";

                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='3' corte='0'></linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='10' corte='0'>www.internetsistemas.com.br</linha>";
                    }
                    else
                    {
                        $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='2' corte='0'>PEDIDO CANCELADO</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>Erro, nenhum pedido associado.</linha>";
                        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='10' corte='0'>www.internetsistemas.com.br</linha>";
                    }
                }
                
                //$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>################## ATENCAO ###################</linha>";
                //$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>" . NOME_SITE . "</linha>";
                //$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>" . NOME_SITE . "</linha>";
                //$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>" . NOME_SITE . "</linha>";
                //$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>################## ATENCAO ###################</linha>";
                

                $detalhes .= "</pedido>";
            }
        }
    
    desconectabd($con_web);
    file_put_contents($diretorio, $detalhes,FILE_APPEND);
    return $detalhes;
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
        
        $cpf = ($num_nota_p > 0) ? $obj_nota_p->valor : $obj_pedido->cpf;
        
        $detalhes = "<pedido quantidade='$num_pedido' cpf='$cpf' nome='" . strtoupper(bd2texto($obj_pedido->nome)) . "' forma_pg='" . strtoupper(bd2texto($obj_pedido->forma_pg)) . "' mensagem='Teste'>";
        
        if ($num_pedido > 0)
        {
            // Buscando pizzas
            $sql_buscar_pizzas = "SELECT *, pp.preco AS pp_preco, pf.preco AS pf_preco FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas pi ON (pf.cod_pizzas = pi.cod_pizzas) WHERE p.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
            
            while ($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
            {
                $codigo = $obj_buscar_pizzas->cod_pedidos_fracoes;
                $descricao = strtoupper('Pizza: ' . bd2texto($obj_buscar_pizzas->pizza . ' - ' . $obj_buscar_pizzas->tamanho . ' - ' . $obj_buscar_pizzas->fracao . '/' . $obj_buscar_pizzas->quant_fracao));
                $quantidade = 1;
                $valor_unitario = ($obj_buscar_pizzas->pp_preco + $obj_buscar_pizzas->pf_preco) . 'M';
                
                $detalhes .= "<item codigo='$num_pedido' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario='$valor_unitario'/>";
            }
            
            // Buscando bordas
            $sql_buscar_pizzas = "SELECT *, pb.preco AS pb_preco FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_pedidos_bordas pb ON (pp.cod_pedidos = pb.cod_pedidos AND pp.cod_pedidos_pizzas = pb.cod_pedidos_pizzas) INNER JOIN ipi_bordas b ON (pb.cod_bordas = b.cod_bordas)  WHERE p.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
            
            while ($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
            {
                $codigo = $obj_buscar_pizzas->cod_pedidos_bordas;
                $descricao = strtoupper('Borda: ' . bd2texto($obj_buscar_pizzas->borda . ' - ' . $obj_buscar_pizzas->tamanho));
                $quantidade = 1;
                $valor_unitario = ($obj_buscar_pizzas->pb_preco) . 'M';
                
                $detalhes .= "<item codigo='$num_pedido' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario='$valor_unitario'/>";
            }
            
            // Buscando adicionais
            $sql_buscar_pizzas = "SELECT *, pp.preco AS pp_preco, pa.preco AS pa_preco FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_pedidos_adicionais pa ON (pp.cod_pedidos = pa.cod_pedidos AND pp.cod_pedidos_pizzas = pa.cod_pedidos_pizzas) INNER JOIN ipi_adicionais a ON (pa.cod_adicionais = a.cod_adicionais) WHERE p.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
            
            while ($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
            {
                $codigo = $obj_buscar_pizzas->cod_pedidos_adicionais;
                $descricao = strtoupper('Adicional: ' . bd2texto((($obj_buscar_pizzas->adicional == 'Sim') ? 'Gergelim' : $obj_buscar_pizzas->adicional) . ' - ' . $obj_buscar_pizzas->tamanho));
                $quantidade = 1;
                $valor_unitario = ($obj_buscar_pizzas->pa_preco) . 'M';
                
                $detalhes .= "<item codigo='$num_pedido' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario='$valor_unitario'/>";
            }
            
            // Buscando ingredientes adicionais
            $sql_buscar_pizzas = "SELECT *, pi.preco AS pi_preco FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) INNER JOIN ipi_ingredientes i ON (pi.cod_ingredientes = i.cod_ingredientes) WHERE pi.ingrediente_padrao = 0 AND p.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
            
            while ($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
            {
                $codigo = $obj_buscar_pizzas->cod_pedidos_ingredientes;
                $descricao = strtoupper('Ingrediente: ' . bd2texto($obj_buscar_pizzas->ingrediente . ' - ' . $obj_buscar_pizzas->tamanho . ' - ' . $obj_buscar_pizzas->fracao . '/' . $obj_buscar_pizzas->quant_fracao));
                $quantidade = 1;
                $valor_unitario = ($obj_buscar_pizzas->pi_preco) . 'M';
                
                $detalhes .= "<item codigo='$num_pedido' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario='$valor_unitario'/>";
            }
            
            // Buscando bebidas
            $sql_buscar_bebidas = "SELECT *, pb.preco AS pb_preco FROM ipi_pedidos p INNER JOIN ipi_pedidos_bebidas pb ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) WHERE p.cod_pedidos = '" . $obj_pedido->cod_pedidos . "'";
            $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
            
            while ($obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas))
            {
                $codigo = $obj_buscar_bebidas->cod_pedidos_fracoes;
                $descricao = strtoupper('Bebida: ' . bd2texto($obj_buscar_bebidas->bebida . ' ' . $obj_buscar_bebidas->conteudo));
                $quantidade = 1;
                $valor_unitario = ($obj_buscar_bebidas->pb_preco) . 'M';
                
                $detalhes .= "<item codigo='$num_pedido' descricao='$descricao' aliquota='$aliquota' quantidade='$quantidade' valor_unitario='$valor_unitario'/>";
            }
        }
        
        $detalhes .= "</pedido>";
    }
    
    desconectabd($con_web);
    
    return $detalhes;

}

/**
 * Retorna os detalhes do pedido
 *
 * @param int $cod_pedidos Código do pedido
 * @param string $usuario Usuário
 * @param string $senha Senha
 * @return XML do pedido
 */
function detalhes_pedido($cod_pedidos, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			return montar_pedido($cod_pedidos);
		}	
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticacao</linha>";
			$detalhes .="</erro>";
		}
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
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			return montar_pedido_fiscal($cod_pedidos);
		}	
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticacao</linha>";
			$detalhes .="</erro>";
		}
		return $detalhes;
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
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			return strlen(montar_pedido($cod_pedidos));
		}	
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticacao</linha>";
			$detalhes .="</erro>";
		}
		return $detalhes;
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
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			return strlen(montar_pedido_fiscal($cod_pedidos));
		}	
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticacao</linha>";
			$detalhes .="</erro>";
		}
		return $detalhes;
}

/**
 * Realiza a baixa do pedido tornando-o IMPRESSO
 *
 * @param int $cod_pedidos Código do pedido
 * @param string $usuario
 * @param string $senha
 * @return Resultado da baixa (1/0)
 */
function baixa_pedido($cod_pedidos,$versao_software, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$con_web = conectabd();
			
			if ($cod_pedidos > 0)
			{
					$sql_pedidos = "UPDATE ipi_pedidos SET situacao = 'IMPRESSO', software_impressao= '".$versao_software."', reimpressao = 0 WHERE cod_pedidos='" . $cod_pedidos . "'";
					$res_pedidos = mysql_query($sql_pedidos);
			}
			else 
					if ($cod_pedidos < 0)
					{
							$cod_pedidos *= -1;
							
							$sql_pedidos = "UPDATE ipi_impressao_relatorio SET situacao = 'IMPRESSO', data_hora_impressao = NOW() WHERE cod_impressao_relatorio='" . $cod_pedidos . "'";
							$res_pedidos = mysql_query($sql_pedidos);
					}
			
			desconectabd($con_web);
		}		
		else
		{
			$res_pedidos = false;
		}
    
    return $res_pedidos;
}

/**
 * Baixa de reimpressão
 *
 * @param int $cod_pedidos
 * @param string $usuario
 * @param string $senha
 * @return Resultado da baixa (1/0)
 */
function baixa_reimpressao($cod_pedidos,$versao_software, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$con_web = conectabd();
			
			if ($cod_pedidos > 0)
			{
					$sql_pedidos = "UPDATE ipi_pedidos SET reimpressao='0' ,software_impressao='".$versao_software."' WHERE cod_pedidos='" . $cod_pedidos . "'";
					$res_pedidos = mysql_query($sql_pedidos);
			}
			else 
					if ($cod_pedidos < 0)
					{
							$cod_pedidos *= -1;
							
							$sql_pedidos = "UPDATE ipi_impressao_relatorio SET situacao = 'IMPRESSO', data_hora_impressao = NOW() WHERE cod_impressao_relatorio = '" . $cod_pedidos . "'";
							$res_pedidos = mysql_query($sql_pedidos);
					}
			
			desconectabd($con_web);
    }		
		else
		{
			$res_pedidos = false;
		}
    return $res_pedidos;
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
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$con_web = conectabd();
			
			if (($cod_pedidos > 0) && ($num_cupom_fiscal > 0))
			{
					$sql_pedidos = "UPDATE ipi_pedidos SET numero_cupom_fiscal = '$num_cupom_fiscal' WHERE cod_pedidos='" . $cod_pedidos . "' AND impressao_fiscal = 1";
					$res_pedidos = mysql_query($sql_pedidos);
			}
			
			desconectabd($con_web);
    }		
		else
		{
			$res_pedidos = false;
		}
    return $res_pedidos;
}

function logar($cod_pizzarias, $usuario, $senha, $tipo, $mensagem)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$con_web = conectabd();
			
			$sql_inserir_log = "INSERT INTO ipi_log (data_hora, cod_pizzarias, tipo, palavra_chave, valor) VALUES (NOW(), '" . texto2bd($cod_pizzarias) . "', '" . texto2bd($tipo) . "', 'SISTEMA_IMPRESSAO', '" . texto2bd($mensagem) . "')";
			$res_inserir_log = mysql_query($sql_inserir_log);
			
			desconectabd($con_web);
		}		
		else
		{
			$res_pedidos = false;
		}
}

$namespace = "urn:ipizza";

$server = new soap_server();

$server->configureWSDL("IPizza Impressao");
$server->wsdl->schemaTargetNamespace = $namespace;

$server->register('testar_conexao', array('usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Testa a conexão com o servidor.');
$server->register('dados_software', array('cod_pizzarias' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna os dados de exibicao do software.');
$server->register('retorna_todos_pedidos', array('cod_pizzarias' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna todos os novos pedidos do sistema');
$server->register('retorna_todos_pedidos_fiscais', array('cod_pizzarias' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna todos os novos pedidos fiscais do sistema');
$server->register('detalhes_pedido', array('cod_pedidos' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna o pedido completo no sistema');
$server->register('detalhes_pedido_fiscal', array('cod_pedidos' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna o pedido fiscal completo no sistema');
$server->register('checksum_pedido', array('cod_pedidos' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:int'), $namespace, false, 'rpc', 'encoded', 'Retorna o checksum do pedido para comparação');
$server->register('checksum_pedido_fiscal', array('cod_pedidos' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:int'), $namespace, false, 'rpc', 'encoded', 'Retorna o checksum do pedido fiscal para comparação');
$server->register('baixa_pedido', array('cod_pedidos' => 'xsd:int', 'versao' => 'xsd:string','usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:int'), $namespace, false, 'rpc', 'encoded', 'Confirmação da Impressão');
$server->register('baixa_reimpressao', array('cod_pedidos' => 'xsd:int', 'versao' => 'xsd:string', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:int'), $namespace, false, 'rpc', 'encoded', 'Baixa flag de Reimpressão');
$server->register('baixa_pedido_fiscal', array('cod_pedidos' => 'xsd:int', 'num_cupom_fiscal' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:int'), $namespace, false, 'rpc', 'encoded', 'Confirmação da Impressão do cupom fiscal e registra seu número');
$server->register('logar', array('cod_pizzarias' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string', 'tipo' => 'xsd:string', 'mensagem' => 'xsd:string'), array(), $namespace, false, 'rpc', 'encoded', 'Loga o erro de impressão');

$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

$server->service($POST_DATA);

exit();
?>
