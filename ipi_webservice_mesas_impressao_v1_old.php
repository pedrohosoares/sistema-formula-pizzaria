<?
require_once ("config.php");
require_once ("bd.php");

require_once ("sys/lib/php/nusoap/nusoap.php");

$usuario_webservice = WEBSERVICE_USUARIO;
$senha_webservice = WEBSERVICE_SENHA;
$arr_cod_tipo_agrupados_comanda = array();

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
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticação</linha>";
			$detalhes .="</erro>";
		}
    
    return $detalhes;
}

function dados_software($cod_pizzarias, $cod_impressoras, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$con_web = conectabd();
			
			$sql_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias = '$cod_pizzarias'";
			$res_pizzarias = mysql_query($sql_pizzarias);
			$obj_pizzarias = mysql_fetch_object($res_pizzarias);

			$sql_impressoras = "SELECT * FROM ipi_impressoras WHERE cod_impressoras = '$cod_impressoras'";
			$res_impressoras = mysql_query($sql_impressoras);
			$obj_impressoras = mysql_fetch_object($res_impressoras);
			
			desconectabd($con_web);
			
			$detalhes = "<detalhes>";
			$detalhes .= "<detalhe chave='cliente' valor='" . NOME_SITE . "'/>";
			$detalhes .= "<detalhe chave='site' valor='" . HOST . "'/>";
			$detalhes .= "<detalhe chave='estabelecimento' valor='" . bd2texto($obj_pizzarias->nome) . "'/>";
			$detalhes .= "<detalhe chave='impressora' valor='" . bd2texto($obj_impressoras->nome_impressora) . "'/>";
			
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
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticação</linha>";
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
 * @return XML com todos os produtos das mesas para impressão.
 */
function retorna_todos_pedidos($cod_pizzarias, $cod_impressoras, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$con_web = conectabd();
			$sql_busca_debug = "SELECT debug_pedidos from ipi_pizzarias where cod_pizzarias = '$cod_pizzarias'" ;
			$res_busca_debug = mysql_query($sql_busca_debug);
			$obj_busca_debug = mysql_fetch_object($res_busca_debug);

			$sql_mesas_impressao = "SELECT moi.cod_mesas_ordem_impressao FROM ipi_mesas_ordem_impressao moi WHERE moi.situacao_ordem_impressao = 'NOVO' AND moi.cod_pizzarias = '".$cod_pizzarias."' AND moi.cod_impressoras = '".$cod_impressoras."'";
			$res_mesas_impressao = mysql_query($sql_mesas_impressao);
			$num_mesas_impressao = mysql_num_rows($res_mesas_impressao);
			
			$pedidos = "<pedidos quantidade='" . ($num_mesas_impressao) . "' debug='".$obj_busca_debug->debug_pedidos."'>";
			
			for($a = 0; $a < $num_mesas_impressao; $a++)
			{
					$obj_mesas_impressao = mysql_fetch_object($res_mesas_impressao);
					
					$pedidos .= "<pedido reimpressao='0' fiscal='0'>" . $obj_mesas_impressao->cod_mesas_ordem_impressao . "</pedido>";
			}
			
			$pedidos .= "</pedidos>";
			
			desconectabd($con_web);
    }
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticação</linha>";
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
function montar_pedido($cod_mesas_ordem_impressao)
{
  $con_web = conectabd();

  global $arr_cod_tipo_agrupados_comanda;

  if ($cod_mesas_ordem_impressao > 0)
  {
  	$nome_colaborador = "";
  	$data_hora_impressao = "";

    $sql_pedido = "SELECT mi.cod_pedidos, mi.tipo_impressao, mi.cod_pedidos_bebidas, mi.cod_pedidos_pizzas, mi.cod_mesas_pedidos FROM ipi_mesas_impressao mi WHERE mi.cod_mesas_ordem_impressao = '".$cod_mesas_ordem_impressao."' ORDER BY mi.tipo_impressao DESC";
    //echo "\n\n".$sql_pedido;
    $res_pedido = mysql_query($sql_pedido);
    $num_pedido = mysql_num_rows($res_pedido);



    $sql_mesa = "SELECT mp.cod_pedidos, m.codigo_cliente_mesa FROM ipi_mesas_impressao mi LEFT JOIN ipi_mesas_pedidos mp ON (mp.cod_mesas_pedidos = mi.cod_mesas_pedidos) LEFT JOIN ipi_mesas m ON (mp.cod_mesas = m.cod_mesas) WHERE mi.cod_mesas_ordem_impressao = '".$cod_mesas_ordem_impressao."' LIMIT 1";
    //echo "\n\n".$sql_mesa;
    $res_mesa = mysql_query($sql_mesa);
    $obj_mesa = mysql_fetch_object($res_mesa);
		$cod_pedidos = $obj_mesa->cod_pedidos;
    
    $detalhes = "<pedido quantidade='$num_pedido'>";

    $sql_pedido2 = "SELECT * FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) WHERE (p.situacao = 'NOVO' OR reimpressao = 1) AND cod_pedidos = " . $cod_pedidos;
    $res_pedido2 = mysql_query($sql_pedido2);
    $obj_pedido2 = mysql_fetch_object($res_pedido2);

    $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='2' corte='0'>------------------- COZINHA ------------------</linha>";
    $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='2' corte='0'>PEDIDO " . sprintf("%08d", $cod_pedidos) . "</linha>";
    //$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>ORIGEM: " . $obj_pedido2->origem_pedido . "</linha>";
    $detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>HORARIO: " . bd2datahora($obj_pedido2->data_hora_pedido) . "</linha>";

/*
    $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>.................................................</linha>";	
    $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>MESA: ".$obj_mesa->codigo_cliente_mesa."</linha>";
    $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>.................................................</linha>";	
*/

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
                    if ($obj_fracoes->obs_fracao)
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
            
						
            /*
            // Bebidas
            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>BEBIDAS:</linha>";
            
            $sql_bebidas = "SELECT * FROM ipi_pedidos_bebidas pb INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_pedidos pe ON(pb.cod_pedidos = pe.cod_pedidos) WHERE pb.cod_pedidos = '" . $cod_pedidos . "'";
            $res_bebidas = mysql_query($sql_bebidas);
            if(mysql_error($con_web)!="")
						{
							file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
						}
            while ( $obj_bebidas = mysql_fetch_object($res_bebidas) )
            {
                //$bebida_gratis = ($obj_bebidas->promocional == 1) ? ' (GRATIS)' : '';
                //$bebida_fidelidade = ($obj_bebidas->fidelidade == 1) ? ' (FIDELIDADE)' : '';
                //$bebida_combo = ($obj_bebidas->combo == 1) ? ' (COMBO)' : '';

                $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>" . $obj_bebidas->quantidade . ' - ' . mb_strtoupper($obj_bebidas->bebida) . ' ' . mb_strtoupper($obj_bebidas->conteudo) . "</linha>";
            }
            $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
            */

    /*
    $arr_produtos_agrupados = array();

		while ($obj_pedido = mysql_fetch_object($res_pedido))
		{

	    if ($obj_pedido->tipo_impressao == "BEBIDAS")
	    {
        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";
		    $sql_bebidas = "SELECT pb.data_hora_inclusao, pb.quantidade, b.bebida, c.conteudo, pb.cod_colaboradores_inclusao FROM ipi_pedidos_bebidas pb INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) WHERE pb.cod_pedidos_bebidas = '" . $obj_pedido->cod_pedidos_bebidas . "'";
	      //echo "\n\n".$sql_bebidas;
				$res_bebidas = mysql_query($sql_bebidas);
				$obj_bebidas = mysql_fetch_object($res_bebidas);
		    $detalhes.="<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>".$obj_bebidas->quantidade.' - '.$obj_bebidas->bebida.' '.$obj_bebidas->conteudo."</linha>";

		    if ($data_hora_impressao=="")
		    {
					$data_hora_impressao = $obj_bebidas->data_hora_inclusao;
		    }

		    if ($nome_colaborador == "")
		    {
		    	$sql_colaborador = "SELECT nome FROM ipi_colaboradores WHERE cod_colaboradores = '".$obj_bebidas->cod_colaboradores_inclusao."'";
		    	$res_colaborador = mysql_query($sql_colaborador);
		    	$obj_colaborador = mysql_fetch_object($res_colaborador);
		    	$nome_colaborador = $obj_colaborador->nome;
		    }

	    }
	    elseif ($obj_pedido->tipo_impressao == "PRODUTOS")
	    {
        $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";


				$sql_pizzas = "SELECT * FROM ipi_pedidos_pizzas WHERE cod_pedidos_pizzas = '".$obj_pedido->cod_pedidos_pizzas."'";
				//echo $sql_pizzas;
				$res_pizzas = mysql_query($sql_pizzas);
				$numero_pizza = 1;

				while ( $obj_pizzas = mysql_fetch_object($res_pizzas) )
				{

			    if ($data_hora_impressao=="")
			    {
						$data_hora_impressao = $obj_pizzas->data_hora_inclusao;
			    }
			    
			    if ($nome_colaborador == "")
			    {
			    	$sql_colaborador = "SELECT nome FROM ipi_colaboradores WHERE cod_colaboradores = '".$obj_pizzas->cod_colaboradores_inclusao."'";
			    	$res_colaborador = mysql_query($sql_colaborador);
			    	$obj_colaborador = mysql_fetch_object($res_colaborador);
			    	$nome_colaborador = $obj_colaborador->nome;
			    }					
					//$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>PIZZA: ${numero_pizza}</linha>";

					// Tamanho da Pizza
					$sql_tamanhos = "SELECT tamanho FROM ipi_tamanhos WHERE cod_tamanhos = '" . $obj_pizzas->cod_tamanhos . "'";
					$res_tamanhos = mysql_query($sql_tamanhos);
					$obj_tamanhos = mysql_fetch_object($res_tamanhos);
					$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>TAMANHO: " . filtrar_caracteres(bd2texto($obj_tamanhos->tamanho)) . "</linha>";
					

					// Massa
					$sql_massa = "SELECT tipo_massa FROM ipi_tipo_massa WHERE cod_tipo_massa = '" . $obj_pizzas->cod_tipo_massa . "'";
					$res_massa = mysql_query($sql_massa);
					$obj_massa = mysql_fetch_object($res_massa);
					$detalhes .= "<linha formatacao='g' centralizado='0' quadrado='0' quebralinha='1' corte='0'>MASSA: " . filtrar_caracteres(bd2texto($obj_massa->tipo_massa)) . "</linha>";
					

					// Corte
					$sql_corte = "SELECT opcao_corte FROM ipi_opcoes_corte WHERE cod_opcoes_corte = '" . $obj_pizzas->cod_opcoes_corte . "'";
					$res_corte = mysql_query($sql_corte);
					$obj_corte = mysql_fetch_object($res_corte);
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
					//$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>================================================</linha>";

					$sql_fracoes = "SELECT * FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pizzas pz ON (pf.cod_pizzas = pz.cod_pizzas) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pp.cod_pedidos = '" . $obj_pizzas->cod_pedidos . "' AND pp.cod_pedidos_pizzas = '" . $obj_pizzas->cod_pedidos_pizzas . "'";
					$res_fracoes = mysql_query($sql_fracoes);

					while ( $obj_fracoes = mysql_fetch_object($res_fracoes) )
					{
            if ( in_array($obj_fracoes->cod_tipo_pizza, $arr_cod_tipo_agrupados_comanda) == 0)
            {
							$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'> ".sprintf("%03d",1)." - " . filtrar_caracteres(bd2texto($obj_fracoes->pizza))."</linha>";

							// Ingredientes retirados
							//$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>RETIRAR:</linha>";

							$sql_ingredientes_retirar = "SELECT i.ingrediente FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = '" . $obj_fracoes->cod_pedidos . "' AND pi.cod_pedidos_pizzas = '" . $obj_fracoes->cod_pedidos_pizzas . "' AND pi.cod_pedidos_fracoes = '" . $obj_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 1) AND p.cod_pizzas = '" . $obj_fracoes->cod_pizzas . "' AND i.consumo = 0";
							$res_ingredientes_retirar = mysql_query($sql_ingredientes_retirar);

							while ( $obj_ingredientes_retirar = mysql_fetch_object($res_ingredientes_retirar) )
							{
								$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>Sem ".filtrar_caracteres(bd2texto($obj_ingredientes_retirar->ingrediente))."</linha>";
							}

							// Ingredientes adicionados
							$sql_ingredientes_adicionar = "SELECT pzi.ingrediente,(select ingrediente from ipi_ingredientes where cod_ingredientes = pi.cod_ingrediente_trocado) as nome_trocado FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_ingredientes pzi ON (pi.cod_ingredientes = pzi.cod_ingredientes) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = '" . $obj_fracoes->cod_pedidos . "' AND pi.cod_pedidos_pizzas = '" . $obj_fracoes->cod_pedidos_pizzas . "' AND pi.cod_pedidos_fracoes = '" . $obj_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 0";
							$res_ingredientes_adicionar = mysql_query($sql_ingredientes_adicionar);

							while ( $obj_ingredientes_adicionar = mysql_fetch_object($res_ingredientes_adicionar) )
							{
								$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>Adicionar ".filtrar_caracteres(bd2texto($obj_ingredientes_adicionar->ingrediente))."</linha>";

								if($obj_ingredientes_adicionar->nome_trocado!="")
								{
									$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>Trocar ".$obj_ingredientes_adicionar->nome_trocado."</linha>";							
								} 
							}

							// Obs de frações
							if ($obj_fracoes->obs_fracao)
							{
								$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>OBS Produto: " . filtrar_caracteres(bd2texto($obj_fracoes->obs_fracao)) . "</linha>";
							}
						}
						else
						{

              $num_itens_agrupados++;
              if ( isset($arr_produtos_agrupados[$obj_fracoes->cod_pizzas]["qtde"]) )
              {
                $arr_produtos_agrupados[$obj_fracoes->cod_pizzas]["qtde"] = $arr_produtos_agrupados[$obj_fracoes->cod_pizzas]["qtde"] + 1;
                $arr_produtos_agrupados[$obj_fracoes->cod_pizzas]["preco_pizza"] = $arr_produtos_agrupados[$obj_fracoes->cod_pizzas]["preco_pizza"] + $_SESSION['ipi_caixa']['pedido'][$a]['preco_pizza'];
                //echo "<br>2: ".$_SESSION['ipi_caixa']['pedido'][$a]['preco_pizza'];
              }
              else
              {
                $arr_produtos_agrupados[$obj_fracoes->cod_pizzas]["qtde"]=1;
                $arr_produtos_agrupados[$obj_fracoes->cod_pizzas]["preco_pizza"] = $_SESSION['ipi_caixa']['pedido'][$a]['preco_pizza'];
                $arr_produtos_agrupados[$obj_fracoes->cod_pizzas]["sabor"]=$obj_fracoes->pizza;
                //$arr_produtos_agrupados[$obj_fracoes->cod_pizzas]["tipo_produto"]=$obj_fracoes->tipo_pizza;
              }

						}
					}

					//$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='3' corte='0'>================================================</linha>";

					$numero_pizza++;
				}
			}
    }
*/
/*
    //PRODUTOS GRUPADOS
    $cont = count($arr_produtos_agrupados);
    if($cont > 0)
    {
      foreach( $arr_produtos_agrupados as $arr_produto)
      {
				$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'> " . filtrar_caracteres(bd2texto( (sprintf("%03d", $arr_produto["qtde"])." - ".$arr_produto["sabor"]) )) . "</linha>";
      }
    }
*/

/*
    $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>.................................................</linha>";
		$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>Colaborador: " . filtrar_caracteres(bd2texto($nome_colaborador)) . "</linha>";
		$detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>Data e Hora: " . filtrar_caracteres(bd2datahora($data_hora_impressao)) . "</linha>";
    $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='1' corte='0'>.................................................</linha>";
*/
    $detalhes .= "<linha formatacao='n' centralizado='0' quadrado='0' quebralinha='3' corte='1'></linha>";
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
function detalhes_pedido($cod_mesas_ordem_impressao, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			return montar_pedido($cod_mesas_ordem_impressao);
		}	
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticação</linha>";
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
function checksum_pedido($cod_pedidos, $cod_impressoras, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			return strlen(montar_pedido($cod_pedidos, $cod_impressoras));
		}	
		else
		{
			$detalhes = "<erro>";
			$detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticação</linha>";
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
function baixa_pedido($cod_mesas_ordem_impressao, $versao_software, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$con_web = conectabd();
      $sql_pedidos = "UPDATE ipi_mesas_ordem_impressao SET situacao_ordem_impressao = 'IMPRESSO', software_impressao='".$versao_software."' WHERE cod_mesas_ordem_impressao = '".$cod_mesas_ordem_impressao."'";
  		$res_pedidos = mysql_query($sql_pedidos);
			desconectabd($con_web);
			//$res_pedidos = 1;
		}		
		else
		{
			$res_pedidos = 0;
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
function baixa_reimpressao($cod_mesas_impressao, $versao_software, $usuario, $senha)
{
		global  $usuario_webservice,$senha_webservice;
		if($usuario == $usuario_webservice && $senha == $senha_webservice)
		{
			$con_web = conectabd();

			$sql_pedidos = "UPDATE ipi_mesas_impressao SET reimpressao='0', software_impressao='".$versao_software."' WHERE cod_mesas_impressao='" . $cod_mesas_impressao . "'";
			$res_pedidos = mysql_query($sql_pedidos);

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
$server->register('dados_software', array('cod_pizzarias' => 'xsd:int', 'cod_impressoras' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna os dados de exibicao do software.');
$server->register('retorna_todos_pedidos', array('cod_pizzarias' => 'xsd:int', 'cod_impressoras' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna todos os novos pedidos do sistema');
$server->register('retorna_todos_pedidos_fiscais', array('cod_pizzarias' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna todos os novos pedidos fiscais do sistema');
$server->register('detalhes_pedido', array('cod_mesas_ordem_impressao' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna o pedido completo no sistema');
$server->register('checksum_pedido', array('cod_pedidos' => 'xsd:int', 'cod_impressoras' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:int'), $namespace, false, 'rpc', 'encoded', 'Retorna o checksum do pedido para comparação');
$server->register('baixa_pedido', array('cod_mesas_ordem_impressao' => 'xsd:int', 'versao' => 'xsd:string','usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:int'), $namespace, false, 'rpc', 'encoded', 'Confirmação da Impressão');
$server->register('baixa_reimpressao', array('cod_mesas_ordem_impressao' => 'xsd:int', 'versao' => 'xsd:string', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:int'), $namespace, false, 'rpc', 'encoded', 'Baixa flag de Reimpressão');
$server->register('logar', array('cod_pizzarias' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string', 'tipo' => 'xsd:string', 'mensagem' => 'xsd:string'), array(), $namespace, false, 'rpc', 'encoded', 'Loga o erro de impressão');

$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

$server->service($POST_DATA);

exit();
?>
