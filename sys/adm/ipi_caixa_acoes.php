<?
session_start();

require_once '../../config.php';
require_once 'ipi_caixa_classe.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

$caixa = new ipi_caixa();

$erro = ""; //variavel que carrega qualquer erro acontecido e devole para a pagina de caixa.
$redirect = "";
if ($acao == 'limpar_pedido')
{
		$caixa->apagar_pedido();
}
else if ($acao=="adicionar_pizza_combo")
{

		$indice_atual_combo = validaVarPost('indice_atual_combo');
		$id_combo = validaVarPost('id_combo');

		$cod_adicionais = validaVarPost('cod_adicionais');
		$cod_tipo_massa = validaVarPost('cod_tipo_massa');
		$cod_opcoes_corte = validaVarPost('cod_opcoes_corte');
		$cod_bordas = validaVarPost('cod_bordas');
		$num_sabores = validaVarPost('num_sabores');
		$cod_tamanhos = validaVarPost('cod_tamanhos');
		$pizza_promocional = validaVarPost('pizza_promocional');
		$borda_promocional = validaVarPost('borda_promocional');
		$num_fracao = validaVarPost('num_fracao');

		$cod_motivo_promocoes_pizza = validaVarPost('cod_motivo_promocoes_pizza');
		$cod_motivo_promocoes_borda = validaVarPost('cod_motivo_promocoes_borda');
 
		$cod_pizzas_1 = validaVarPost('cod_pizzas_1');
		$cod_pizzas_2 = validaVarPost('cod_pizzas_2');
		$cod_pizzas_3 = validaVarPost('cod_pizzas_3');
		$cod_pizzas_4 = validaVarPost('cod_pizzas_4');

		$ingredientes1 = validaVarPost('ingredientes1');
		$ingredientes2 = validaVarPost('ingredientes2');
		$ingredientes3 = validaVarPost('ingredientes3');
		$ingredientes4 = validaVarPost('ingredientes4');

		$ingredientes_adicionais1 = validaVarPost('ingredientes_adicionais1');
		$ingredientes_adicionais2 = validaVarPost('ingredientes_adicionais2');
		$ingredientes_adicionais3 = validaVarPost('ingredientes_adicionais3');
		$ingredientes_adicionais4 = validaVarPost('ingredientes_adicionais4');

		$observacao_1 = validaVarPost('observacao_1');
		$observacao_2 = validaVarPost('observacao_2');
		$observacao_3 = validaVarPost('observacao_3');
		$observacao_4 = validaVarPost('observacao_4');

		$_SESSION['ipi_caixa']['combo']['produtos'][$indice_atual_combo]['foi_pedido']='S';
		
		
/*    
		$cod_adicionais = validaVarPost('gergelim');
		$cod_tipo_massa = validaVarPost('tipo_massa');
		$cod_bordas = validaVarPost('borda');
		$quant_fracao = validaVarPost('num_sabores');
		$cod_tamanhos = validaVarPost('tam_pizza');
		
		$indice_atual_combo = validaVarPost('indice_atual_combo');
		$id_combo = validaVarPost('id_combo');
		
		$cod_motivo_promocoes_borda = validaVarPost('cod_motivo_promocoes_borda');
		$cod_motivo_promocoes_pizza = validaVarPost('cod_motivo_promocoes_pizza');
		
		$pizza_promocional = validaVarPost('pizza_promocional');
		$borda_promocional = validaVarPost('borda_promocional');
		
		// Confirmar que essa pizza do combo foi pedida na sessão, para continuar da próxima pizza
		$_SESSION['ipi_caixa']['combo']['produtos'][$indice_atual_combo]['foi_pedido']='S';
		
		$num_fracao = validaVarPost('num_fracao');
		$sabor1_pizza = validaVarPost('sabor1_pizza');
		$sabor2_pizza = validaVarPost('sabor2_pizza');
		$sabor3_pizza = validaVarPost('sabor3_pizza');
		$sabor4_pizza = validaVarPost('sabor4_pizza');
		
		$ingredientes1 = validaVarPost('ingredientes1');
		$ingredientes2 = validaVarPost('ingredientes2');
		$ingredientes3 = validaVarPost('ingredientes3');
		$ingredientes4 = validaVarPost('ingredientes4');
		
		$ingredientes_adicionais1 = validaVarPost('ingredientes_adicionais1');
		$ingredientes_adicionais2 = validaVarPost('ingredientes_adicionais2');
		$ingredientes_adicionais3 = validaVarPost('ingredientes_adicionais3');
		$ingredientes_adicionais4 = validaVarPost('ingredientes_adicionais4');
*/
		
		$indice_pizza = $caixa->adicionar_pizza($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $cod_opcoes_corte, $num_sabores, $pizza_promocional, $borda_promocional, $cod_motivo_promocoes_pizza, $cod_motivo_promocoes_borda, $id_combo);
		
	 
		//Encontrar o id_sessao do método de exclusão
		//$id_sessao_pai = $_SESSION['ipi_caixa']['pedido'][$indice_pizza]['pizza_id_sessao'];
		
		if (($cod_pizzas_1!="0")&&($cod_pizzas_1!=""))
		{
				$indice_fracao1 = $caixa->adicionar_fracao($indice_pizza, $cod_pizzas_1, $num_fracao[0], $observacao_1);
				$num_ingredientes = count ($ingredientes1);
				for ($a=0; $a<$num_ingredientes; $a++)
				{
						if ($ingredientes1[$a]!="")
						$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao1, $ingredientes1[$a], true);
				}
				
				$num_ingredientes_adicionais = count ($ingredientes_adicionais1);
				for ($a=0; $a<$num_ingredientes_adicionais; $a++)
				{
						if ($ingredientes_adicionais1[$a]!="")
						{
								$arr_ingrediente = explode("###",$ingredientes_adicionais1[$a]);
								$cod_ingredientes = $arr_ingrediente[1];
								$tipo_ingrediente = $arr_ingrediente[0];
								$cod_codigo_ingre_troca = $arr_ingrediente[2];
								if($tipo_ingrediente!="TROCA")
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao1, $ingredientes_adicionais1[$a], false);
								}
								else
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes, false,true,$cod_codigo_ingre_troca);
								}
						}
				}
		}
		
		if (($cod_pizzas_2!="0")&&($cod_pizzas_2!=""))
		{
				$indice_fracao2 = $caixa->adicionar_fracao($indice_pizza, $cod_pizzas_2, $num_fracao[1], $observacao_2);
				$num_ingredientes = count ($ingredientes2);
				for ($a=0; $a<$num_ingredientes; $a++)
				{
						if ($ingredientes2[$a]!="")
						$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao2, $ingredientes2[$a], true);
				}
				
				$num_ingredientes_adicionais = count ($ingredientes_adicionais2);
				for ($a=0; $a<$num_ingredientes_adicionais; $a++)
				{
						//if ($ingredientes_adicionais2[$a]!="")
						//$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao2, $ingredientes_adicionais2[$a], false);

						if ($ingredientes_adicionais2[$a]!="")
						{
								$arr_ingrediente = explode("###",$ingredientes_adicionais2[$a]);
								$cod_ingredientes = $arr_ingrediente[1];
								$tipo_ingrediente = $arr_ingrediente[0];
								$cod_codigo_ingre_troca = $arr_ingrediente[2];
								if($tipo_ingrediente!="TROCA")
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao2, $ingredientes_adicionais2[$a], false);
								}
								else
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes, false,true,$cod_codigo_ingre_troca);
								}
						}
				}
		}
		
		if (($cod_pizzas_3!="0")&&($cod_pizzas_3!=""))
		{
				$indice_fracao3 = $caixa->adicionar_fracao($indice_pizza, $cod_pizzas_3, $num_fracao[2], $observacao_3);
				$num_ingredientes = count ($ingredientes3);
				for ($a=0; $a<$num_ingredientes; $a++)
				{
						if ($ingredientes3[$a]!="")
						$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao3, $ingredientes3[$a], true);
				}
				
				$num_ingredientes_adicionais = count ($ingredientes_adicionais3);
				for ($a=0; $a<$num_ingredientes_adicionais; $a++)
				{
						//if ($ingredientes_adicionais3[$a]!="")
					 // $caixa->adicionar_ingrediente($indice_pizza, $indice_fracao3, $ingredientes_adicionais3[$a], false);

						if ($ingredientes_adicionais3[$a]!="")
						{
								$arr_ingrediente = explode("###",$ingredientes_adicionais3[$a]);
								$cod_ingredientes = $arr_ingrediente[1];
								$tipo_ingrediente = $arr_ingrediente[0];
								$cod_codigo_ingre_troca = $arr_ingrediente[2];
								if($tipo_ingrediente!="TROCA")
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao3, $ingredientes_adicionais3[$a], false);
								}
								else
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes, false,true,$cod_codigo_ingre_troca);
								}
						}        
				}
		}
		
		if (($cod_pizzas_4!="0")&&($cod_pizzas_4!=""))
		{
				$indice_fracao4 = $caixa->adicionar_fracao($indice_pizza, $cod_pizzas_4, $num_fracao[3], $observacao_4);
				$num_ingredientes = count ($ingredientes4);
				for ($a=0; $a<$num_ingredientes; $a++)
				{
						if ($ingredientes4[$a]!="")
						$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao4, $ingredientes4[$a], true);
				}
				
				$num_ingredientes_adicionais = count ($ingredientes_adicionais4);
				for ($a=0; $a<$num_ingredientes_adicionais; $a++)
				{
						//if ($ingredientes_adicionais4[$a]!="")
						//$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao4, $ingredientes_adicionais4[$a], false);

						if ($ingredientes_adicionais4[$a]!="")
						{
								$arr_ingrediente = explode("###",$ingredientes_adicionais4[$a]);
								$cod_ingredientes = $arr_ingrediente[1];
								$tipo_ingrediente = $arr_ingrediente[0];
								$cod_codigo_ingre_troca = $arr_ingrediente[2];
								if($tipo_ingrediente!="TROCA")
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao4, $ingredientes_adicionais4[$a], false);
								}
								else
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes, false,true,$cod_codigo_ingre_troca);
								}
						}        
				}
		}
		
		
		$deb=0;
		$indice_opcoes = -1;
		if ($deb==1)  echo "<br>indice_opcoes1: ".$indice_opcoes;
		$num_opcoes = count($_SESSION['ipi_caixa']['combo']['produtos']);
		for ($a=0; $a<$num_opcoes; $a++)
		{
				if ($_SESSION['ipi_caixa']['combo']['produtos'][$a]['foi_pedido']=='N')
				{
						$indice_opcoes = $a;
						break;
				}
		}
		if ($indice_opcoes==-1)
		{
				unset($_SESSION['ipi_caixa']['combo']);
		}
		
		/*
		if ($indice_opcoes==-1)
		{
				$acao = "combo_redirecionar_pedido";
		}
		else
		{
				if ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_opcoes]['tipo']=='PIZZA')
				{
						$acao = "combo_redirecionar_pizza";
				}
				elseif ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_opcoes]['tipo']=='BEBIDA')
				{
						$acao = "combo_redirecionar_bebida";
				}
		}
				if ($deb==1) echo "<br>indice_opcoes2: ".$indice_opcoes;
				if ($deb==1) echo "<Br>Acao: ".$acao;
		*/
		
		
}
else if ($acao == 'adicionar_cliente')
{
		
		$situacao_cliente = mb_strtoupper(validaVarPost('situacao_cliente'));
		$cod_onde_conheceu = mb_strtoupper(validaVarPost('cod_onde_conheceu'));
		$nome = mb_strtoupper(trim(validaVarPost('nome')));
		$email = mb_strtoupper(validaVarPost('email'));
		$cpf = mb_strtoupper(validaVarPost('cpf'));
		$sexo = mb_strtoupper(validaVarPost('sexo'));
		$nascimento = mb_strtoupper(validaVarPost('nascimento'));
		$celular = mb_strtoupper(validaVarPost('celular'));
		$telefone_1 = mb_strtoupper(validaVarPost('telefone_1'));
		$telefone_2 = mb_strtoupper(validaVarPost('telefone_2'));
		$cep = mb_strtoupper(validaVarPost('cep'));
		$endereco = mb_strtoupper(validaVarPost('endereco'));
		$numero = mb_strtoupper(validaVarPost('numero'));
		$complemento = mb_strtoupper(validaVarPost('complemento'));
		$edificio = mb_strtoupper(validaVarPost('edificio'));
		$bairro = mb_strtoupper(trim(validaVarPost('bairro')));
		$cidade = mb_strtoupper(trim(validaVarPost('cidade')));
		$estado = mb_strtoupper(validaVarPost('estado'));
		$tipo_cliente = mb_strtoupper(validaVarPost('tipo_cliente'));
		$cod_clientes = mb_strtoupper(validaVarPost('cod_clientes'));
		$cod_enderecos = mb_strtoupper(validaVarPost('cod_enderecos'));
		$ref_endereco = mb_strtoupper(validaVarPost('ref_endereco'));
		$ref_cliente = mb_strtoupper(validaVarPost('ref_cliente'));
		$obs_cliente = mb_strtoupper(validaVarPost('obs_cliente'));

		/*echo "<pre>";
		print_r($_POST);
		echo "</pre>";
		echo "b -".$bairro."- ci -".$cidade."- n -".$nome;
		die();*/
		$entrega = validaVarPost("tipo_entrega");
		$cod_pizzarias = validaVarPost("cod_pizzarias");
		$_SESSION['ipi_caixa']['data_hora_inicial'] = date('Y-m-d H:i:s',strtotime('+3 hours'));
		
		if($entrega == "Entrega")
		{
			$conexao = conectabd();
			if($cep!="")
			{
        $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep));
        $sql_buscar_pizzaria = "SELECT cod_pizzarias from ipi_cep where cep_inicial >= '$cep_limpo' and cep_final <='$cep_limpo'";
       // echo ".cep".$sql_buscar_pizzaria;
        $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
        $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
			}
			else
			{
        $sql_buscar_pizzaria = "SELECT cod_pizzarias from ipi_cep where bairro like '$bairro' AND cod_pizzarias IN (".implode($_SESSION['usuario']['cod_pizzarias'], ",").")";
        //echo ".bs".$sql_buscar_pizzaria;
        $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
        $num_buscar_pizzaria = mysql_num_rows($res_buscar_pizzaria);
        if ($num_buscar_pizzaria>0)
        {
          $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
        }
        else
        {
          $sql_buscar_pizzaria = "SELECT cod_pizzarias from ipi_cep where bairro like '%$bairro%' AND cod_pizzarias IN (".implode($_SESSION['usuario']['cod_pizzarias'], ",").")";
          //echo ".bs".$sql_buscar_pizzaria;
          $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
          $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
        }
			}




			$cod_pizzarias = $obj_buscar_pizzaria->cod_pizzarias;
			desconectabd($conexao);
		}

		if($celular == $_SESSION['usuario']['ddd_pizzaria'])
		{
				$celular = "";
		}

		if($telefone_1 == $_SESSION['usuario']['ddd_pizzaria'])
		{
				$telefone_1 = "";
		}

		if($telefone_2 == $_SESSION['usuario']['ddd_pizzaria'])
		{
				$telefone_2 = "";
		}

		if (($tipo_cliente=="ANTIGO")&&($email!=""))
		{
				$conexao = conectabd();
				// atualizar o endereço simplesmente sem fazer nenhum pedido
				$sql_buscar_cliente = "SELECT cod_clientes FROM ipi_clientes WHERE email='".$email."' AND cod_clientes<>'".$cod_clientes."'";
				//echo "<br>1: ".$sql_buscar_cliente;
				$res_buscar_cliente = mysql_query($sql_buscar_cliente);
				$num_buscar_cliente = mysql_num_rows($res_buscar_cliente);
				if ($num_buscar_cliente==0)
				{
						$sql_novo_cliente = sprintf("UPDATE ipi_clientes SET cod_onde_conheceu='%s', nome='%s', email='%s', cpf='%s', nascimento='%s', sexo = '%s', celular='%s', observacao='%s', situacao='%s' WHERE cod_clientes = '%s'", $cod_onde_conheceu, $nome, $email, $cpf, data2bd($nascimento), $sexo, $celular, $obs_cliente, $situacao_cliente, $cod_clientes);
						$res_novo_cliente = mysql_query($sql_novo_cliente);
						//echo "<br>1: ".$sql_novo_cliente;
						$sql_endereco_cliente = sprintf("UPDATE ipi_enderecos SET endereco = '%s', numero = '%s', complemento = '%s', edificio = '%s', bairro = '%s', cidade = '%s', estado = '%s', cep = '%s', telefone_1 = '%s', telefone_2 = '%s', cod_clientes = '%s', referencia_endereco = '%s', referencia_cliente = '%s' WHERE cod_enderecos = '%s'",
															texto2bd($endereco), texto2bd($numero), texto2bd($complemento), texto2bd($edificio), ($bairro), ($cidade), texto2bd($estado), texto2bd($cep), texto2bd($telefone_1), texto2bd($telefone_2),  texto2bd($cod_clientes),texto2bd($ref_endereco),texto2bd($ref_cliente),  texto2bd($cod_enderecos) );
						$res_endereco_cliente = mysql_query($sql_endereco_cliente);
				}
				else 
				{
						$erro = "O E-mail ($email), já está sendo utilizado por outro cliente! Os dados não foram atualizados!" ;
				}
				desconectabd($conexao);
		}
		elseif (($tipo_cliente=="ANTIGO")&&($email==""))
		{
				$conexao = conectabd();
						
				$sql_novo_cliente = sprintf("UPDATE ipi_clientes SET cod_onde_conheceu='%s', nome='%s', email='%s', cpf='%s', nascimento='%s', sexo = '%s', celular='%s', observacao='%s', situacao='%s' WHERE cod_clientes = '%s'", $cod_onde_conheceu, ($nome), $email, $cpf, data2bd($nascimento), $sexo, $celular, $obs_cliente, $situacao_cliente, $cod_clientes);
				$res_novo_cliente = mysql_query($sql_novo_cliente);
				//echo "<br>1a: ".$sql_novo_cliente;
				$sql_endereco_cliente = sprintf("UPDATE ipi_enderecos SET endereco = '%s', numero = '%s', complemento = '%s', edificio = '%s', bairro = '%s', cidade = '%s', estado = '%s', cep = '%s', telefone_1 = '%s', telefone_2 = '%s', cod_clientes = '%s', referencia_endereco = '%s', referencia_cliente = '%s' WHERE cod_enderecos = '%s'",
													texto2bd($endereco), texto2bd($numero), texto2bd($complemento), texto2bd($edificio), ($bairro), ($cidade), texto2bd($estado), texto2bd($cep), texto2bd($telefone_1), texto2bd($telefone_2),  texto2bd($cod_clientes),texto2bd($ref_endereco),texto2bd($ref_cliente),  texto2bd($cod_enderecos) );
				$res_endereco_cliente = mysql_query($sql_endereco_cliente);
				
				desconectabd($conexao);
		}
		else if (($tipo_cliente=="NOVO")&&($email!=""))
		{
				$conexao = conectabd();
				// atualizar o endereço simplesmente sem fazer nenhum pedido
				$sql_buscar_cliente = "SELECT cod_clientes FROM ipi_clientes WHERE email='".$email."'";
				$res_buscar_cliente = mysql_query($sql_buscar_cliente);
				$num_buscar_cliente = mysql_num_rows($res_buscar_cliente);
				if ($num_buscar_cliente>0)
				{
						$erro = "O E-mail ($email), já está sendo utilizado por outro cliente! Os dados não foram atualizados!" ;
				}
				else
				{
						
					$sql_novo_cliente = sprintf("INSERT INTO ipi_clientes (cod_onde_conheceu, nome, email, cpf, nascimento, celular, cod_clientes_indicador, indicador_recebeu_pontos, origem_cliente,observacao,data_hora_cadastro, sexo) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%d', 0, 'TEL', '%s','%s', '%s')",
                         $cod_onde_conheceu, $nome, $email, $cpf, data2bd($nascimento), $celular, 0, $obs_cliente, date("Y-m-d H:i:s"), $sexo);
            		$res_novo_cliente = mysql_query($sql_novo_cliente);
            		
            		$codigo_novo_cliente = mysql_insert_id();
            		
            		 $sql_endereco_cliente = sprintf("INSERT INTO ipi_enderecos (apelido, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2, cod_clientes, referencia_endereco,referencia_cliente) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s')",
                              'Endereço Padrão', $endereco, $numero, $complemento, $edificio, $bairro, $cidade ,$estado, $cep, $telefone_1, $telefone_2, $codigo_novo_cliente, $ref_endereco, $ref_cliente);
            		$res_endereco_cliente = mysql_query($sql_endereco_cliente);
            		$codigo_novo_endereco = mysql_insert_id();
            		
					$_SESSION['ipi_caixa']['codigo_novo_endereco'] = $codigo_novo_endereco;
					$_SESSION['ipi_caixa']['codigo_novo_cliente'] = $codigo_novo_cliente;
					
				}
				desconectabd($conexao);
		}
		elseif (($tipo_cliente=="NOVO")&&($email==""))
		{
				$conexao = conectabd();
				// $tipo_cliente = "ANTIGO";
				// $_SESSION['ipi_caixa']['tipo_cliente']=$tipo_cliente;
				// echo $_SESSION['ipi_caixa']['tipo_cliente'];
				// die();
				$sql_novo_cliente = sprintf("INSERT INTO ipi_clientes (cod_onde_conheceu, nome, email, cpf, nascimento, celular, cod_clientes_indicador, indicador_recebeu_pontos, origem_cliente,observacao,data_hora_cadastro, sexo) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%d', 0, 'TEL', '%s','%s', '%s')",
                         $cod_onde_conheceu, $nome, $email, $cpf, data2bd($nascimento), $celular, 0, $obs_cliente, date("Y-m-d H:i:s"), $sexo);
            		$res_novo_cliente = mysql_query($sql_novo_cliente);
            		
            		$codigo_novo_cliente = mysql_insert_id();
            		
            		 $sql_endereco_cliente = sprintf("INSERT INTO ipi_enderecos (apelido, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2, cod_clientes, referencia_endereco,referencia_cliente) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s')",
                              'Endereço Padrão', $endereco, $numero, $complemento, $edificio, $bairro, $cidade ,$estado, $cep, $telefone_1, $telefone_2, $codigo_novo_cliente, $ref_endereco, $ref_cliente);
            		$res_endereco_cliente = mysql_query($sql_endereco_cliente);
            		$codigo_novo_endereco = mysql_insert_id();

					$_SESSION['ipi_caixa']['codigo_novo_endereco'] = $codigo_novo_endereco;
					$_SESSION['ipi_caixa']['codigo_novo_cliente'] = $codigo_novo_cliente;

					
				desconectabd($conexao);
		}
		//echo "<br>erro: ".$erro;
		//die();
		if ($erro=="")
		{
				$_SESSION['ipi_caixa']['cliente']['confirmado']="1";  // Um flag pra sinalizar que o botão confirmar foi clicado
				$_SESSION['ipi_caixa']['tipo_cliente']=$tipo_cliente;
				$_SESSION['ipi_caixa']['cliente']['nome']=($nome);
				$_SESSION['ipi_caixa']['cliente']['cod_clientes']=$cod_clientes;
					if ($cod_onde_conheceu!="")
					{
						$_SESSION['ipi_caixa']['cliente']['cod_onde_conheceu']=$cod_onde_conheceu;
					}
				$_SESSION['ipi_caixa']['cliente']['cod_enderecos']=$cod_enderecos;
				$_SESSION['ipi_caixa']['cliente']['email']=$email;
				$_SESSION['ipi_caixa']['cliente']['cpf']=$cpf;
				$_SESSION['ipi_caixa']['cliente']['sexo']=$sexo;
				$_SESSION['ipi_caixa']['cliente']['nascimento']=$nascimento;
				$_SESSION['ipi_caixa']['cliente']['celular']=$celular;
				$_SESSION['ipi_caixa']['cliente']['telefone_1']=$telefone_1;
				$_SESSION['ipi_caixa']['cliente']['telefone_2']=$telefone_2;
				$_SESSION['ipi_caixa']['cliente']['cep']=$cep;
				$_SESSION['ipi_caixa']['cliente']['endereco']=$endereco;
				$_SESSION['ipi_caixa']['cliente']['numero']=$numero;
				$_SESSION['ipi_caixa']['cliente']['complemento']=$complemento;
				$_SESSION['ipi_caixa']['cliente']['edificio']=$edificio;
				$_SESSION['ipi_caixa']['cliente']['bairro']=($bairro);
				$_SESSION['ipi_caixa']['cliente']['cidade']=($cidade);
				$_SESSION['ipi_caixa']['cliente']['estado']=$estado;
				$_SESSION['ipi_caixa']['cliente']['obs_cliente']=$obs_cliente;
				$_SESSION['ipi_caixa']['cliente']['ref_endereco'] = $ref_endereco;
				$_SESSION['ipi_caixa']['cliente']['ref_cliente'] = $ref_cliente;

				$conexao = conectabd();
				$_SESSION['ipi_caixa']['entregac'] = $entrega; 
				$_SESSION['ipi_caixa']['pizzaria_atual'] = $cod_pizzarias; 
				//echo "<br/>".date('H')."<br/>";
				require("../../pub_req_fuso_horario1.php");

				if($cep!="")
				{
						$cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep));

						$sql_cep = "SELECT COUNT(*) AS contagem FROM ipi_cep WHERE cep_inicial <= '$cep_limpo' AND cep_final >= '$cep_limpo'";
						//echo $sql_cep."<br/>";
						$res_cep = mysql_query($sql_cep);
						$ObjCep = mysql_fetch_object($res_cep);
						$contagem = $objCep->contagem; 

						$sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= '$cep_limpo' AND cep_final >= '$cep_limpo' LIMIT 1";
						$res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
						$obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
						$cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
						while($obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias))
						{
								$arr_cod_pizzarias[]['cod_pizzarias'] = $obj_cod_pizzarias->cod_pizzarias;
						}
						if($cod_pizzarias=="")
						{
								$_SESSION['ipi_caixa']['cliente']['cobertura'] = "Fora";
						}
				}
				if($entrega!="Balcão")
				{
						$sql_buscar_frete = "SELECT t.valor_frete,t.valor_comissao_frete from ipi_cep c inner join ipi_taxa_frete t on t.cod_taxa_frete = c.cod_taxa_frete where cep_inicial <= '$cep_limpo' and cep_final >= '$cep_limpo'";
						//die($sql_buscar_frete);
						$res_buscar_frete = mysql_query($sql_buscar_frete);
						$qtd_frete_cep = mysql_num_rows($res_buscar_frete);

						$sql_buscar_frete_bairro = "SELECT t.valor_frete,t.valor_comissao_frete from ipi_cep c inner join ipi_taxa_frete t on t.cod_taxa_frete = c.cod_taxa_frete where bairro = '$bairro' AND c.cod_pizzarias IN (".implode($_SESSION['usuario']['cod_pizzarias'], ",").") AND t.valor_frete !='' and t.valor_comissao_frete !=''";
						
						$res_buscar_frete_bairro = mysql_query($sql_buscar_frete_bairro);
						$qtd_frete_cep_bairro = mysql_num_rows($res_buscar_frete_bairro);
						//die($sql_buscar_frete_bairro);
						if($qtd_frete_cep>0)
						{
								$obj_buscar_frete = mysql_fetch_object($res_buscar_frete);
						}
						else if($qtd_frete_cep_bairro>0)
						{
								$obj_buscar_frete = mysql_fetch_object($res_buscar_frete_bairro);
						}

						if($obj_buscar_frete->valor_frete>0)
						{
								$_SESSION['ipi_caixa']['cliente']['preco_frete'] = $obj_buscar_frete->valor_frete;
								$_SESSION['ipi_caixa']['cliente']['valor_comissao_frete'] = $obj_buscar_frete->valor_comissao_frete;
						}

				}
				desconectabd($conexao);
				
		}
		else // garantia que em hipotese alguma quando der erro não salvar um cliente
		{
				$_SESSION['ipi_caixa']['tipo_cliente']='';
				$_SESSION['ipi_caixa']['cliente']['cod_clientes']='';
				$_SESSION['ipi_caixa']['cliente']['cod_enderecos']='';
		}
		
		
}
else if ($acao == 'adicionar_pizza')
{
		//echo '<pre>';//print_r($_SESSION['ipi_caixa']['pedido']);
		//print_r($_POST);
	//echo '</pre>';
		//die();
		$cod_adicionais = validaVarPost('cod_adicionais');
		$cod_tipo_massa = validaVarPost('cod_tipo_massa');
		$cod_opcoes_corte = validaVarPost('cod_opcoes_corte');
		$cod_bordas = validaVarPost('cod_bordas');
		$num_sabores = validaVarPost('num_sabores');
		$cod_tamanhos = validaVarPost('cod_tamanhos');
		$pizza_promocional = validaVarPost('pizza_promocional');
		$borda_promocional = validaVarPost('borda_promocional');
		$num_fracao = validaVarPost('num_fracao');
		
		$cod_motivo_promocoes_pizza = validaVarPost('cod_motivo_promocoes_pizza');
		$cod_motivo_promocoes_borda = validaVarPost('cod_motivo_promocoes_borda');
		
		$cod_pizzas_1 = validaVarPost('cod_pizzas_1');
		$cod_pizzas_2 = validaVarPost('cod_pizzas_2');
		$cod_pizzas_3 = validaVarPost('cod_pizzas_3');
		$cod_pizzas_4 = validaVarPost('cod_pizzas_4');
		
		$ingredientes1 = validaVarPost('ingredientes1');
		$ingredientes2 = validaVarPost('ingredientes2');
		$ingredientes3 = validaVarPost('ingredientes3');
		$ingredientes4 = validaVarPost('ingredientes4');
		
		$ingredientes_adicionais1 = validaVarPost('ingredientes_adicionais1');
		$ingredientes_adicionais2 = validaVarPost('ingredientes_adicionais2');
		$ingredientes_adicionais3 = validaVarPost('ingredientes_adicionais3');
		$ingredientes_adicionais4 = validaVarPost('ingredientes_adicionais4');
		
		$observacao_1 = validaVarPost('observacao_1');
		$observacao_2 = validaVarPost('observacao_2');
		$observacao_3 = validaVarPost('observacao_3');
		$observacao_4 = validaVarPost('observacao_4');

		$indice_pizza = $caixa->adicionar_pizza($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $cod_opcoes_corte, $num_sabores, $pizza_promocional, $borda_promocional, $cod_motivo_promocoes_pizza, $cod_motivo_promocoes_borda, '');
		
		if($num_sabores==1)		
		{
			$resultado = true;
		}
		else if($num_sabores==2)		
		{
			if(is_array($ingredientes_adicionais1) && is_array($ingredientes_adicionais2))
			{
				$resultado = true;
			}
			else
				$restultado = false;

		}
		else if($num_sabores==3)		
		{
			//$resultado = array_intersect($ingredientes_adicionais1, $ingredientes_adicionais2,$ingredientes_adicionais3);

			if(is_array($ingredientes_adicionais1) && is_array($ingredientes_adicionais2) && is_array($ingredientes_adicionais3))
			{
				$resultado = true;
			}
			else
				$restultado = false;
		}
		else if($num_sabores==4)		
		{
			//$resultado = array_intersect($ingredientes_adicionais1, $ingredientes_adicionais2,$ingredientes_adicionais3,$ingredientes_adicionais4);

			if(is_array($ingredientes_adicionais1) && is_array($ingredientes_adicionais2) && is_array($ingredientes_adicionais3) && is_array($ingredientes_adicionais4))
			{
				$resultado = true;
			}
			else
				$restultado = false;
		}
		else
		{
			$resultado = false;
		}

		if($resultado!="")
		{
			//echo "s";
			
			$_SESSION['ipi_caixa']['pedido'][$indice_pizza]['adicionais_inteira'] = $resultado;
			//print_r($_SESSION['ipi_caixa']['pedido']);
		}
		//echo "N";

		//die();

		if (($cod_pizzas_1 != '0') && ($num_fracao >= 1))
		{
				$indice_fracao1 = $caixa->adicionar_fracao($indice_pizza, $cod_pizzas_1, $num_fracao[0], $observacao_1);
				$num_ingredientes = count($ingredientes1);
				
				for ($a = 0; $a < $num_ingredientes; $a++)
				{
						if ($ingredientes1[$a] != '')
						{
								$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao1, $ingredientes1[$a], true);
						}
				}
				
				$num_ingredientes_adicionais = count($ingredientes_adicionais1);
				for ($a = 0; $a < $num_ingredientes_adicionais; $a++)
				{
					 // if ($ingredientes_adicionais1[$a] != '')
						//{
						//    $caixa->adicionar_ingrediente($indice_pizza, $indice_fracao1, $ingredientes_adicionais1[$a], false);
					 // }

						if ($ingredientes_adicionais1[$a]!="")
						{
								$arr_ingrediente = explode("###",$ingredientes_adicionais1[$a]);
								$cod_ingredientes = $arr_ingrediente[1];
								$tipo_ingrediente = $arr_ingrediente[0];
								$cod_codigo_ingre_troca = $arr_ingrediente[2];
								if($tipo_ingrediente!="TROCA")
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao1, $ingredientes_adicionais1[$a], false);
								}
								else
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes, false,true,$cod_codigo_ingre_troca);
								}
						}
				}
		}
		
		
		
		if (($cod_pizzas_2 != '') && ($num_fracao >= 2))
		{
				$indice_fracao2 = $caixa->adicionar_fracao($indice_pizza, $cod_pizzas_2, $num_fracao[1], $observacao_2);
				$num_ingredientes = count($ingredientes2);
				
				for ($a = 0; $a < $num_ingredientes; $a++)
				{
						if ($ingredientes2[$a] != '')
						{
								$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao2, $ingredientes2[$a], true);
						}
				}
				
				$num_ingredientes_adicionais = count($ingredientes_adicionais2);
				
				for ($a = 0; $a < $num_ingredientes_adicionais; $a++)
				{
						//if ($ingredientes_adicionais2[$a] != '')
					 // {
					 //     $caixa->adicionar_ingrediente($indice_pizza, $indice_fracao2, $ingredientes_adicionais2[$a], false);
					 // }

						if ($ingredientes_adicionais2[$a]!="")
						{
								$arr_ingrediente = explode("###",$ingredientes_adicionais2[$a]);
								$cod_ingredientes = $arr_ingrediente[1];
								$tipo_ingrediente = $arr_ingrediente[0];
								$cod_codigo_ingre_troca = $arr_ingrediente[2];
								if($tipo_ingrediente!="TROCA")
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao2, $ingredientes_adicionais2[$a], false);
								}
								else
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes, false,true,$cod_codigo_ingre_troca);
								}
						}            
				}
		}

		
		
		if (($cod_pizzas_3 != '') && ($num_fracao >= 3))
		{
				$indice_fracao3 = $caixa->adicionar_fracao($indice_pizza, $cod_pizzas_3, $num_fracao[2], $observacao_3);
				$num_ingredientes = count($ingredientes3);
				
				for ($a = 0; $a < $num_ingredientes; $a++)
				{
						if ($ingredientes3[$a] != '')
						{
								$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao3, $ingredientes3[$a], true);
						}
				}
				
				$num_ingredientes_adicionais = count($ingredientes_adicionais3);
				
				for ($a = 0; $a < $num_ingredientes_adicionais; $a++)
				{
						//if ($ingredientes_adicionais3[$a] != '')
						//{
						//    $caixa->adicionar_ingrediente($indice_pizza, $indice_fracao3, $ingredientes_adicionais3[$a], false);
					 // }

						if ($ingredientes_adicionais3[$a]!="")
						{
								$arr_ingrediente = explode("###",$ingredientes_adicionais3[$a]);
								$cod_ingredientes = $arr_ingrediente[1];
								$tipo_ingrediente = $arr_ingrediente[0];
								$cod_codigo_ingre_troca = $arr_ingrediente[2];
								if($tipo_ingrediente!="TROCA")
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao3, $ingredientes_adicionais3[$a], false);
								}
								else
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes, false,true,$cod_codigo_ingre_troca);
								}
						}
				}
		}
		
		
		
		if (($cod_pizzas_4 != '') && ($num_fracao >= 4))
		{
				$indice_fracao4 = $caixa->adicionar_fracao($indice_pizza, $cod_pizzas_4, $num_fracao[3], $observacao_4);
				$num_ingredientes = count($ingredientes4);
				for ($a = 0; $a < $num_ingredientes; $a++)
				{
						if ($ingredientes4[$a] != '')
						{
								$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao4, $ingredientes4[$a], true);
						}
				}
				
				$num_ingredientes_adicionais = count($ingredientes_adicionais4);
				for ($a = 0; $a < $num_ingredientes_adicionais; $a++)
				{
						//if ($ingredientes_adicionais4[$a] != '')
						//{
						//    $caixa->adicionar_ingrediente($indice_pizza, $indice_fracao4, $ingredientes_adicionais4[$a], false);
						//}

						if ($ingredientes_adicionais4[$a]!="")
						{
								$arr_ingrediente = explode("###",$ingredientes_adicionais4[$a]);
								$cod_ingredientes = $arr_ingrediente[1];
								$tipo_ingrediente = $arr_ingrediente[0];
								$cod_codigo_ingre_troca = $arr_ingrediente[2];
								if($tipo_ingrediente!="TROCA")
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao4, $ingredientes_adicionais4[$a], false);
								}
								else
								{
										$caixa->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes, false,true,$cod_codigo_ingre_troca);
								}
						}            
				}
		}
		
		
}
else if ($acao == 'preco_promocional')
{
	$redirect = 4;
  $tipo = validaVarPost('tipo');
  $id_sessao = validaVarPost('id_sessao_pizza');
  $cod_motivo_promocoes = validaVarPost("valor_preco_motivos");
  
  $id_sessao_exclusao = 0;
  $valor_preco_promocional = "";
  $valor_porcentagem_promocional = "";
  switch ($cod_motivo_promocoes) 
  {
  	case 6:
  		$valor_preco_promocional = 9.90;
		break;

  	case 7:
  		$valor_preco_promocional = 25.00;
		break;

		case 8:
  		$valor_porcentagem_promocional = 50;
		break;
  }

	if($tipo=="incluir")
	{
		$numero_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
    if ($numero_pizzas > 0)
    {
        for ($a = 0; $a < $numero_pizzas; $a++)
        {
            if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_id_sessao']==$id_sessao)
            {
                //echo "<br>aki: ".$a;
                $id_sessao_exclusao = $a;
                break;
            }
        }
        
        if($valor_preco_promocional!="")
        {
        	$_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]['preco_promocional'] = 1;
	        $_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]["valor_preco_promocional"] = $valor_preco_promocional;
	      }
	      else
	      {
	      	$_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]['porcentagem_promocional'] = 1;
	      	$_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]["valor_porcentagem_promocional"] = $valor_porcentagem_promocional;
	      }
        $_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]['cod_motivo_promocoes_pizza'] = $cod_motivo_promocoes;


    }
	}
	else if ($tipo=="excluir")
	{
		$numero_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
    if ($numero_pizzas > 0)
    {
        for ($a = 0; $a < $numero_pizzas; $a++)
        {
            if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_id_sessao']==$id_sessao)
            {
                //echo "<br>aki: ".$a;
                $id_sessao_exclusao = $a;
                break;
            }
        }
        $_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]['cod_motivo_promocoes_pizza'] = "";
        unset($_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]["valor_preco_promocional"]);
        unset($_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]['preco_promocional']);
        unset($_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]['porcentagem_promocional']);
        unset($_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]['valor_porcentagem_promocional']);

    }
	}
}
else if ($acao == 'adicionar_bebidas')
{
		$cod_bebidas_conteudos = validaVarPost('cod_bebidas_conteudos');
		$quantidades = validaVarPost('quantidades');
		$bebida_promocional = validaVarPost('bebida_promocional');
		$cod_motivo_promocoes_bebida = validaVarPost('cod_motivo_promocoes_bebida');
		
		$num_bebida = count($cod_bebidas_conteudos);
		for ($a = 0; $a < $num_bebida; $a++)
		{
				if ($quantidades[$a] != '0')
				{
						$caixa->adicionar_bebida($cod_bebidas_conteudos[$a], $quantidades[$a], $bebida_promocional, $cod_motivo_promocoes_bebida, '');
				}
		}
}
else if ($acao == 'adicionar_bebidas_combo')
{
		
		$indice_atual_combo = validaVarPost('indice_atual_combo');
		$id_combo = validaVarPost('id_combo');
		
		$cod_bebidas_conteudos = validaVarPost('cod_bebidas_conteudos');
		$quantidades = validaVarPost('quantidades');
		$bebida_promocional = validaVarPost('bebida_promocional');
		$cod_motivo_promocoes_bebida = validaVarPost('cod_motivo_promocoes_bebida');
		
		$num_bebida = count($cod_bebidas_conteudos);
		for ($a = 0; $a < $num_bebida; $a++)
		{
				if ($quantidades[$a] != '0')
				{
						$caixa->adicionar_bebida($cod_bebidas_conteudos[$a], $quantidades[$a], $bebida_promocional, $cod_motivo_promocoes_bebida, $id_combo);
				}
		}
		$_SESSION['ipi_caixa']['combo']['produtos'][$indice_atual_combo]['foi_pedido']='S';
		
		
		$indice_opcoes = -1;
		$num_opcoes = count($_SESSION['ipi_carrinho']['combo']['produtos']);
		for ($a=0; $a<$num_opcoes; $a++)
		{
				if ($_SESSION['ipi_caixa']['combo']['produtos'][$a]['foi_pedido']=='N')
				{
						$indice_opcoes = $a;
						break;
				}
		}
		if ($indice_opcoes==-1)
		{
				unset($_SESSION['ipi_caixa']['combo']);
		}
		
}
else if ($acao == 'remover')
{
		$tipo = validaVarPost('tipo');
		$id_sessao = validaVarPost('id_sessao');
		$id_sessao_exclusao = 0;

		if ($tipo=="pizza")
		{
				$numero_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
				if ($numero_pizzas > 0)
				{
						for ($a = 0; $a < $numero_pizzas; $a++)
						{
								if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_id_sessao']==$id_sessao)
								{
										//echo "<br>aki: ".$a;
										$id_sessao_exclusao = $a;
										break;
								}
						}
						
						unset($_SESSION['ipi_caixa']['pedido'][$id_sessao_exclusao]);
						
						if (count($_SESSION['ipi_caixa']['pedido'])>0)
						{
								$arr_novos_indices = range (0, (count($_SESSION['ipi_caixa']['pedido']) - 1));
								$_SESSION['ipi_caixa']['pedido'] = array_combine ($arr_novos_indices, $_SESSION['ipi_caixa']['pedido']);
						}
				}
		}
		
		if ($tipo=="bebida")
		{
				$numero_bebidas = isset($_SESSION['ipi_caixa']['bebida']) ? count($_SESSION['ipi_caixa']['bebida']) : 0;
				if ($numero_bebidas > 0)
				{
						for ($a = 0; $a < $numero_bebidas; $a++)
						{
								if ($_SESSION['ipi_caixa']['bebida'][$a]['bebida_id_sessao']==$id_sessao)
								{
										//echo "<br>aki: ".$a;
										$id_sessao_exclusao = $a;
										break;
								}
						}
						
						unset($_SESSION['ipi_caixa']['bebida'][$id_sessao_exclusao]);
						
						if (count($_SESSION['ipi_caixa']['bebida'])>0)
						{
								$arr_novos_indices = range (0, (count($_SESSION['ipi_caixa']['bebida']) - 1));
								$_SESSION['ipi_caixa']['bebida'] = array_combine ($arr_novos_indices, $_SESSION['ipi_caixa']['bebida']);
						}
				}
		}
		
		
}

/*
 * o redirect foi mudado para form post pois necessário informar a variável de erro e não quero usar GET 
switch ($acao)
{
		case 'remover':
		case 'adicionar_cliente':
		case 'adicionar_pizza':
		case 'adicionar_bebidas':
				header('Location: ipi_caixa.php');
				break;
}
*/
?>
<html>
<body>
<form action="ipi_caixa.php" method="post" name="frm_redirecionar">
<input type="hidden" name="erro" value="<? echo $erro; ?>">
<input type="hidden" name="redirecionar" value="<? echo $redirect; ?>">
</form>
<script>
		document.frm_redirecionar.submit();
</script>
</body>
</html>
