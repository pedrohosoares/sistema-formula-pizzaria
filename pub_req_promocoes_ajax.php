<?
require_once 'ipi_session.php';
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';
$cod_promocoes = validaVarPost('cod');
$acao = validaVarPost('acao');
$pizza_pai = validaVarPost("pizza_pai");
$arr_json = array();
$conteudo ="";
switch($acao)
{
	case 'carregar_promocao':
		$con = conectabd();
		
		////////////////BUSCANDO CODIGO DAS PIZZARIAS//////////
		if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
		{
			$cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
		}
		else
		{
			$cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
			$cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
			$sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
			$res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
			$obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
			$cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
		}
			//////////////////////////////////////////////////////////////
	/*	if($cod_pomocoes=="")
		{	
			$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
		 // $dia_semana_completo = array('domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado');
		 if(validaVarPost('tam')==3)
	   {
				if($dia_semana[date("w")]=='Seg')
					$cod_promocoes = 1;
 		}
		  
		 /* if($dia_semana[date("w")]=='Ter')
		  	$cod_promocoes = 2;*/
		  	
	/*  	if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
			{
				$num_pizzas  = count($_SESSION['ipi_carrinho']['pedido']);
				$cont_g = 0;
				for($p = 0;$p<$num_pizzas ; $p++)
				{
					if($_SESSION['ipi_carrinho']['pedido'][$p]["cod_tamanhos"] == 3)
					$cont_g++;
				}
		
				if($cont_g==1)
				{
					if(validaVarPost('tam')==3)
					{
			      if(!isset($_SESSION['ipi_carrinho']['promocao']['promocao_4']))
							$cod_promocoes = 4 ;
					}
				}
			}
					
		}*/
		
		if($cod_promocoes=="3") //fazer a promo ferias 2013 sobrescrever a doce
		{
			$sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '7' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
			$res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
			if(mysql_num_rows($res_buscar_promocoes)>0)
			{
				$cod_promocoes = '7';
			}
		}

		$sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '".$cod_promocoes."' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
		$res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
	//echo $sql_buscar_promocoes;
	 // die($sql_buscar_promocoes);
			
		//	$obj_buscar_promocoes = mysql_fetch_object($res_buscar_promocoes);
		if(mysql_num_rows($res_buscar_promocoes)>0)
		{
			$conteudo .="<div class='modal_promo_$cod_promocoes'>";
			$arr_json["resposta"] = "achou";
			$botoes = 1;
			$trocar = false;  //bool que define se irá aparecer 'trocar' ou não
			$arr_promocoes = array(3,4,7); //codigo das promoções em que pode trocar o produto por outro
			if(in_array($cod_promocoes,$arr_promocoes)) // se a promoção a ser escolhida estiver entre as que podem trocar
			{
				$trocar=true;
			}
	
			//////////////CARREGANDO BOTÕES PADRÕES////////////////
			$btn_sim = 'btn_eu_quero.png';
			$alt_sim = 'Eu quero';
	
			$btn_ok = 'btn_ok2.png';
			$alt_ok = 'Ok';
	
			$btn_nao = 'btn_nao_obrigado.png';
			$alt_nao = 'Não Obrigado';
			//////////////////////////////////////////////////////
	
			$preco_trocar = 6;//preço para trocar por padraão é 1 ,mas se em alguma pizzaria for mais caro,
			/*if($trocar)
			{
			
				$arr_pizzarias = array(5); // colocar nesse array para que o preço mude, 5=> Barra Rio
				if(in_array($cod_pizzarias,$arr_pizzarias)) 
				{
						$preco_trocar = 2;
				}
			}*/
			$sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = $cod_pizzarias and pr.cod_promocoes = '$cod_promocoes' and pp.situacao='ATIVO'";

			$res_buscar_promocao = mysql_query($sql_buscar_promocao);
			$obj_buscar_promocao = mysql_fetch_object($res_buscar_promocao);
			
			//$conteudo .= "<strong>".$obj_buscar_promocao->promocao."</strong><br/>".$obj_buscar_promocao->descricao;
			switch($cod_promocoes) //verifico a promoção para serem tratadas 
			{
				case 1:
				/*	echo '<form name="frmAdicionarSugestao'.$cod_promocoes.'" method="post" action="ipi_req_carrinho_acoes.php">';
					echo '<input type="hidden" name="acao" value="adicionar_redirecionar_promocao"/>';
					echo '	</form>';  <img src="img/pizzinhasdoce.jpg"/>*/
					$conteudo .= '<div style="float: right; margin: 5px;"></div>';
				break;
				case 3:
					if($trocar) // se a promoção permite trocar
					{
						$conteudo .= '<div class="botao_trocar"><input type="checkbox" name="trocar_check" id="trocar_check" onclick="checkar_trocar()"/><label for="trocar_check">Trocar por (Gratis)</label><select name="combo_bebida_trocar" onchange=\'checkar_trocar()\' id="combo_bebida_trocar"><option value=""></option><option value=\'1\'>Coca-Cola</option><option value=\'7\'>Coca-Cola Zero</option></select></div>';
					}
				break;
				case 4:
					if($trocar) // se a promoção permite trocar
					{
						$conteudo .= '<div class="botao_trocar"><input type="checkbox" name="trocar_check" id="trocar_check" onclick="checkar_trocar()"/><label for="trocar_check">Trocar por (R$ '.bd2moeda($preco_trocar).')</label><select name="combo_bebida_trocar" onchange=\'checkar_trocar()\' id="combo_bebida_trocar"><option value=""></option><option value=\'1\'>Coca-Cola</option><option value=\'7\'>Coca-Cola Zero</option></select></div>';
					}
				break;
				case 7: // PROMOCAO MALUCA 1 PIZZA 1 REFRI
					if($trocar) // se a promoção permite trocar
					{
						$conteudo .= '<div class="botao_trocar"><input type="checkbox" name="trocar_check" id="trocar_check" onclick="checkar_trocar()"/><label for="trocar_check">Trocar por (Gratis)</label><select name="combo_bebida_trocar" onchange=\'checkar_trocar()\' id="combo_bebida_trocar"><option value=""></option><option value=\'1\'>Coca-Cola</option><option value=\'7\'>Coca-Cola Zero</option></select></div>';
					}
				break;
				case 11:
					if($trocar) // se a promoção permite trocar
					{
						$conteudo .= '<div class="botao_trocar"><input type="checkbox" name="trocar_check" id="trocar_check" onclick="checkar_trocar()"/><label for="trocar_check">Trocar por (R$ '.bd2moeda($preco_trocar).')</label><select name="combo_bebida_trocar" onchange=\'checkar_trocar()\' id="combo_bebida_trocar"><option value=""></option><option value=\'1\'>Coca-Cola</option><option value=\'7\'>Coca-Cola Zero</option></select></div>';
					}
			}
			$conteudo .= "<div class='botoes_promo' >";
			switch($cod_promocoes) //switch para os botoes das promocoes
			{
				case 1:
					$conteudo .= "<h1 class='center'>Hoje na compra de :<br/> 1 pizza <small>(8 pedaços)</small> ganhe 1 kuat 2 litros";
					$conteudo .= "<br/>1 pizza <small>(4 pedaços)</small> ganhe 1 kuat lata</h1>";
					$conteudo .= "<br/><a href='javascript:void(0)' onclick='$.nmTop().close();' class='btn btn-secondary'>OK</a>";
				break;
				case 2://document.frmAdicionarSugestao".$cod_promocoes.".submit();
					$conteudo .= "<h1 class='center'>Hoje na compra de:<br/> 1 pizza <small>(4 pedaços)</small> ganhe 1 kuat lata</h1>";
					$conteudo .= "<br/><a href='javascript:void(0)' onclick='$.nmTop().close();' class='btn btn-secondary'>OK</a>";
				break;
				case 3:
					$conteudo .= '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='finalizar_promocao()'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' /></a>";
				//echo '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='document.getElementById(\"trocar\").value=\"n\";finalizar_promocao()'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
				break;
				case 4:
					$conteudo .= '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='finalizar_promocao()'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' /></a>";
				//echo '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='document.getElementById(\"trocar\").value=\"n\";finalizar_promocao()'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
				break;
				case 7:
					$conteudo .= '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='finalizar_promocao()'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' /></a>";
				//echo '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='document.getElementById(\"trocar\").value=\"n\";finalizar_promocao()'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
				break;
				case 9:
					$conteudo .= '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='finalizar_promocao(\"aceitar_promocao\")'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' /></a>";
				//echo '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='document.getElementById(\"trocar\").value=\"n\";finalizar_promocao()'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
				break;
				case 10://document.frmAdicionarSugestao".$cod_promocoes.".submit();
					$conteudo .= "<a href='javascript:void(0)' onclick='$.nmTop().close();'><img src='img/pc/".$btn_ok."' alt='".$alt_ok."' /></a>";
				break;
				case 11:
					$conteudo .= '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='finalizar_promocao()'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' /></a>";
				//echo '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='document.getElementById(\"trocar\").value=\"n\";finalizar_promocao()'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
				break;
				case 12:
					$conteudo .= '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='finalizar_promocao()'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' /></a>";
				//echo '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='document.getElementById(\"trocar\").value=\"n\";finalizar_promocao()'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
				break;
				case 15:
					$conteudo .= '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='finalizar_promocao()'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' /></a>";
				//echo '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='document.getElementById(\"trocar\").value=\"n\";finalizar_promocao()'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
				break;
				case 16:
					$conteudo .= "<a href='javascript:void(0)' onclick='$.nmTop().close();'><img src='img/pc/".$btn_ok."' alt='".$alt_ok."' /></a>";
				break;
				case 17:
					$conteudo .= '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='finalizar_promocao(\"aceitar_promocao\")'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' /></a>";
				//echo '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='document.getElementById(\"trocar\").value=\"n\";finalizar_promocao()'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
				break;
				case 19:
					$conteudo .= '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='finalizar_promocao(\"aceitar_promocao\")'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' /></a>";
				//echo '<br/>';
					$conteudo .= "<a href='javascript:void(0)' onclick='document.getElementById(\"trocar\").value=\"n\";finalizar_promocao()'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
				break;
			}		
			$conteudo .= "</div>";
				$conteudo .="</div>";
					}
					else
					$arr_json["resposta"] = "nao";
		
	 desconectar_bd($con);
	 $arr_json["conteudo"] = utf8_encode($conteudo);
	break;
	case 'carregar_sugestao':
			$con = conectabd();
		////////////////BUSCANDO CODIGO DAS PIZZARIAS//////////
		if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
		{
			$cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
		}
		else
		{
			$cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
			$cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
			$sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
			$res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
			$obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
			$cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
		}
		//////////////////////////////////////////////////////////////
		
		$sql_pizza_doce = 'select p.cod_pizzas from ipi_pizzas p inner join ipi_pizzas_ipi_tamanhos pt on pt.cod_pizzas = p.cod_pizzas where p.tipo = "Doce" and pt.cod_pizzarias = '.$cod_pizzarias;//
		/*echo $sql_pizza_doce;*/
		$res_pizza_doce = mysql_query($sql_pizza_doce);
		while($obj_pizza_doce = mysql_fetch_object($res_pizza_doce))
		{
			$cod_pizzas[] = $obj_pizza_doce->cod_pizzas;
		
		}
		$num_pizzas = count($_SESSION['ipi_carrinho']['pedido']);
		//$conteudo .= "cod_pro".$cod_promocoes;
	//	$conteudo .="<br/>".implode(',',$cod_pizzas)."</br>";
		if($cod_promocoes==0)
		{
			for($p = 0; $p <= $num_pizzas-1; $p++)
			{
				$num_fracoes = count($_SESSION['ipi_carrinho']['pedido'][$p]['fracao']);
				for($f = 0;$f<=$num_fracoes-1;$f++)
				{
			//		$conteudo .= "<br/>PEDI ".$_SESSION['ipi_carrinho']['pedido'][$p]['fracao'][$f]['cod_pizzas'];
					 if(in_array($_SESSION['ipi_carrinho']['pedido'][$p]['fracao'][$f]['cod_pizzas'],$cod_pizzas))
					 {
					 	$cod_promocoes = 5;
					 //	break 2;
					 }
					$arr_pedidas[] =  $_SESSION['ipi_carrinho']['pedido'][$p]['fracao'][$f]['cod_pizzas'];
				}
			}
		}
		
		if($cod_promocoes==0)
				$cod_promocoes = 6;
		/*
			if(condições da doçe)
					$cod_promocoes = 6
			else
			$cod_promocoes = 5;
			*/
		if($_SESSION['ipi_cliente']['autenticado'] != true && $cod_promocoes!=8 && $cod_promocoes!=9 && $cod_promocoes!=12 && $cod_promocoes!=13 && $cod_promocoes!=15 && $cod_promocoes!=18)
		{
			$cod_promocoes = 6;
		}

		//$cod_promocoes = 8; //TIGS: 11/02 - Teste Retirar

		$sql_buscar_sugestao = "select pp.cod_promocoes from ipi_promocoes_ipi_pizzarias pp inner join ipi_promocoes p on p.cod_promocoes = pp.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and  pp.cod_promocoes='$cod_promocoes'  and pp.situacao='ATIVO' and p.tipo='SUGESTAO'";
		$res_buscar_sugestao = mysql_query($sql_buscar_sugestao);

		if(mysql_num_rows($res_buscar_sugestao)>0)
		{
			$obj_buscar_sugestao = mysql_fetch_object($res_buscar_sugestao); 
			$cod_sugestoes= $obj_buscar_sugestao->cod_promocoes;

			if($obj_buscar_sugestao->cod_promocoes==18)
			{
				$conteudo .= "<div class='popup_sugestao' style='background-image:url(\"img/pc/promocao_indaiatuba.png\")'>"; //style="background-image:url(\'img/pc/promocao_natal.png\')"
			}
			elseif($obj_buscar_sugestao->cod_promocoes==15)
			{
				$conteudo .= "<div class='popup_sugestao' style='background-image:url(\"img/pc/popup_promocao_pais2014.png\")'>"; //style="background-image:url(\'img/pc/promocao_natal.png\')"
			}
			elseif($obj_buscar_sugestao->cod_promocoes==13)
			{
				$conteudo .= "<div class='popup_sugestao' style='background-image:url(\"img/pc/promocao_natal.png\")'>"; //style="background-image:url(\'img/pc/promocao_natal.png\')"
			}
			elseif($obj_buscar_sugestao->cod_promocoes==12)
			{
				$conteudo .= "<div class='popup_sugestao' style='background-image:url(\"img/pc/popup_promocao_pascoa2015.png\")'>"; //style="background-image:url(\'img/pc/promocao_natal.png\')"
			}
			elseif($obj_buscar_sugestao->cod_promocoes==9)
			{
				$conteudo .= "<div class='popup_sugestao' style='background-image:url(\"img/pc/popup_promocao_carnaval2014.png\")'>"; //style="background-image:url(\'img/pc/promocao_natal.png\')"
			}
			elseif($obj_buscar_sugestao->cod_promocoes==8)
			{
				$conteudo .= "<div class='popup_sugestao' style='background-image:url(\"img/pc/promocao_natal.png\")'>"; //style="background-image:url(\'img/pc/promocao_natal.png\')"
			}
			else
			{
				$conteudo .= "<div class='popup_sugestao'>"; 
			}
			$arr_json["resposta"] = "achou";

			$botoes=1;
			//////////////////////////////////////////////////////////////


			//////////////CARREGANDO BOTÕES PADRÕES////////////////
			$btn_sim = 'btn_eu_quero.png';
			$alt_sim = 'Eu quero';

			$btn_nao = 'btn_nao_obrigado.png';
			$alt_nao = 'Não Obrigado';
			//////////////////////////////////////////////////////

			$sql_sugestao = "select * from ipi_promocoes where cod_promocoes=".$cod_sugestoes;
			$res_sugestao = mysql_query($sql_sugestao);
			$obj_sugestao = mysql_fetch_object($res_sugestao);

					//  echo $cod_sugestoes;
					
			$codigo_pizza = 0;
			switch($cod_sugestoes)
			{
				case 18: //Promoção Aniversario Indaiatuba
					$cod_tamanhos = 4; //quadradinha 
					$codigo_pizza = 47; //pizza de confete
					break;
				case 15: //Promoção Dia dos Pais 2014
					$cod_tamanhos = 4; //quadradinha 
					$codigo_pizza = 42; //pizza de chocolate
					break;
				case 13: //Promoção Dia dos Namorados 2014
					$cod_tamanhos = 4; //quadradinha 
					$codigo_pizza = 72; //pizza de nutella
					break;
				case 12: //Promoção Pascoa 2014
					$cod_tamanhos = 4; //quadradinha 
					$codigo_pizza = 42; //pizza de chocolate
					break;
				case 9: //Promoção Carnaval 2014
					$cod_tamanhos = 3; //Quadrada 
					$codigo_pizza = 70; //Pizza de Natal
				break;
				case 8: //Promoção Natal 2014
					$cod_tamanhos = 4; //quadradinha 
					$codigo_pizza = 79; //Pizza de Natal - Chocotone
				break;
				case 6: //Sugestão Muzza:Pizza De Chocolate 
					$cod_tamanhos = 4; //quadradinha 
					$codigo_pizza = 42; //pizza de chocolate
				break;
				case 5;
					$sql_busca_pedidos = "select cod_pedidos from ipi_pedidos where cod_clientes = ".$_SESSION['ipi_cliente']['codigo'];
					$res_busca_pedidos = mysql_query($sql_busca_pedidos);
					$arr_codigos_pedidos = array();
					$arr_codigos_pedidos_pizzas = array();
					if(mysql_num_rows($res_busca_pedidos)>0)
						{
							while($obj_busca_pedidos = mysql_fetch_object($res_busca_pedidos))
							{
								$arr_codigos_pedidos[] = $obj_busca_pedidos->cod_pedidos;
							}
							$str_codigos = implode(',',$arr_codigos_pedidos);
				
							$sql_busca_pedidos_pizzas = "select cod_pedidos_pizzas from ipi_pedidos_pizzas where cod_pedidos in (".$str_codigos.")";
							$res_busca_pedidos_pizzas = mysql_query($sql_busca_pedidos_pizzas);
							while($obj_busca_pedidos_pizzas  = mysql_fetch_object($res_busca_pedidos_pizzas))
							{
								$arr_codigos_pedidos_pizzas[] = $obj_busca_pedidos_pizzas->cod_pedidos_pizzas;
							}
							$str_codigos_pizzas = implode(',',$arr_codigos_pedidos_pizzas);

							$cod_tamanhos = 4;

							$sql_busca_favorita = "SELECT fr.cod_pizzas,p.pizza,count(fr.cod_pizzas) as qtd FROM ipi_pedidos_fracoes fr INNER JOIN ipi_pizzas p ON (fr.cod_pizzas = p.cod_pizzas) INNER JOIN ipi_pizzas_ipi_tamanhos pt on pt.cod_pizzas = fr.cod_pizzas and pt.cod_tamanhos = '$cod_tamanhos' and pt.cod_pizzarias = '$cod_pizzarias' WHERE fr.cod_pedidos in (".$str_codigos.") AND fr.cod_pedidos_pizzas in (".$str_codigos_pizzas.") and p.venda_online = 1 group BY fr.cod_pizzas order by qtd DESC limit 3";
							//echo $sql_busca_favorita;
							$res_busca_favorita = mysql_query($sql_busca_favorita);
							while($obj_busca_favorita = mysql_fetch_object($res_busca_favorita))
							{
								if(!in_array($obj_busca_favorita->cod_pizzas,$arr_pedidas))
								{
								$codigo_pizza = $obj_busca_favorita->cod_pizzas;
								break;
								}
							}
							
							if($codigo_pizza == 0)
								$codigo_pizza = 42;	
						}
						else
						{
							$cod_tamanhos = 4;
							$codigo_pizza = 42;
						}
				
				break;
			}
				




					$sql_busca_pizza = "Select p.*,pt.preco from ipi_pizzas p inner join ipi_pizzas_ipi_tamanhos pt on pt.cod_pizzas = p.cod_pizzas where p.cod_pizzas =".$codigo_pizza." and pt.cod_tamanhos = $cod_tamanhos and pt.cod_pizzarias = ".$cod_pizzarias;
	//				echo $sql_busca_pizza;
					$res_busca_pizza = mysql_query($sql_busca_pizza);
					$obj_busca_pizza = mysql_fetch_object($res_busca_pizza);
		
					$SqlBuscaIngredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_pizzas = ".$codigo_pizza." AND i.consumo = 0	";
					$resBuscaIngredientes = mysql_query ($SqlBuscaIngredientes);
//		echo "\n\n X:: ".$SqlBuscaIngredientes;
					while($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) {
						$ingredientes[] = $objBuscaIngredientes->ingrediente;
						$cod_ingredientes[] = $objBuscaIngredientes->cod_ingredientes;
						}

					
					$conteudo .='
						<form name="frmAdicionarSugestao" method="post" action="ipi_req_carrinho_acoes.php">
							<input type="hidden" name="gergelim" value="N"/>
							<input type="hidden" name="borda" value="N"/>
							<input type="hidden" name="num_sabores" value="1"/>
							<input type="hidden" name="tam_pizza" value="'.$cod_tamanhos.'"/>
							<input type="hidden" name="sabor1_pizza" value="'.$codigo_pizza.'"/>
							<input type="hidden" name="sabor2_pizza" value="0"/>
							<input type="hidden" name="sabor3_pizza" value="0"/>
							<input type="hidden" name="sabor4_pizza" value="0"/>
							<input type="hidden" name="num_fracao[]" value="1"/>';
							 foreach ($cod_ingredientes as $cor_ingrediente) { $conteudo .= '<input type="hidden" name="ingredientes1[]" value="NORMAL###'.$cor_ingrediente.'###0"/>'; } 
					
					if($cod_promocoes==8 || $cod_promocoes==12 || $cod_promocoes==13 || $cod_promocoes==15  || $cod_promocoes==18) //se for sugestão cod 8. redireciona para algo mais ao inves de pagamentos
					{
						if($cod_promocoes==15 || $cod_promocoes==18)
						{
								$conteudo .='
								<input type="hidden" name="acao" value="adicionar_promocao"/>
								<input type="hidden" name="cpp" value="'.$pizza_pai.'"/>
								<input type="hidden" name="corte" value="5"/>
								<input type="hidden" name="sugestao" value="'.$cod_promocoes.'"/>
							</form>
				
							<form name="frmNao" method="post" action="ipi_req_carrinho_acoes.php">
								<input type="hidden" name="acao" value="algo_mais"/>
								<input type="hidden" name="sugestao" value="'.$cod_promocoes.'"/>
								</form>';
						}
						else
						{
							$conteudo .='
								<input type="hidden" name="acao" value="adicionar"/>
								<input type="hidden" name="cpp" value="'.$pizza_pai.'"/>
								<input type="hidden" name="corte" value="5"/>
								<input type="hidden" name="sugestao" value="'.$cod_promocoes.'"/>
							</form>
				
							<form name="frmNao" method="post" action="ipi_req_carrinho_acoes.php">
								<input type="hidden" name="acao" value="verificar_login"/>
								<input type="hidden" name="sugestao" value="'.$cod_promocoes.'"/>
								</form>';
						}

															//$conteudo .= "<strong>".$obj_sugestao->promocao."</strong>";
								//$conteudo .= "<p>".$obj_sugestao->descricao."</p>";
								$conteudo .= '<div class="sugestao_conteudo centralizar" style="margin-top:445px">';
								//$conteudo .= "<h1>Pizza de ".$obj_busca_pizza->pizza."</h1>";
								//$conteudo .= "<span class='span_tamanho_sugestao'></span>";
								//$conteudo .= "<br/><img src='".($obj_busca_pizza->foto_grande ? "upload/pizzas/".$obj_busca_pizza->foto_grande  : 'img/pc/pizza_nutella_g.png')."' width='250' heigth='250' alt='Pizza ".$obj_busca_pizza->pizza."' ></img>";
								//$conteudo .= "<p><span class='span_tamanho_sugestao'>(QUADRADINHA 4 PEDAÇOS - </span><span class='span_preco_sugestao'>".bd2moeda($obj_busca_pizza->preco)."</span><span class='span_tamanho_sugestao'>)</span></p>";
								if($botoes==1)//se $botoes forem 1, que é o valor padrão
								{
									//$conteudo .= "<div class='botoes_promo' >";
									$conteudo .= "<a href='javascript:void(0)' onclick='document.frmAdicionarSugestao.submit();'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' ></img></a>";
									$conteudo .= "&nbsp;&nbsp;&nbsp;&nbsp;";
									$conteudo .= "<a href='javascript:void(0)' onclick='document.frmNao.submit();'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
									//$conteudo .= "</div>";
								}
									
						 $conteudo .='</div>';
					}
					else
					{
							$conteudo .='
							<input type="hidden" name="acao" value="sugestao_verificar_login"/>
							<input type="hidden" name="sugestao" value="1"/>
						</form>
			
						<form name="frmNao" method="post" action="ipi_req_carrinho_acoes.php">
							<input type="hidden" name="acao" value="verificar_login"/>
							<input type="hidden" name="sugestao" value="1"/>
							</form>';

												//$conteudo .= "<strong>".$obj_sugestao->promocao."</strong>";
					//$conteudo .= "<p>".$obj_sugestao->descricao."</p>";
					$conteudo .= '<div class="sugestao_conteudo centralizar">';
					$conteudo .= "<h1>Pizza de ".$obj_busca_pizza->pizza."</h1>";
					//$conteudo .= "<span class='span_tamanho_sugestao'></span>";

					if($_SESSION['ipi_carrinho']['desconto_balcao'] == 'sim')
					{
						$obj_busca_pizza->preco = $obj_busca_pizza->preco*0.7;
					}

					$conteudo .= "<br/><img src='".($obj_busca_pizza->foto_grande ? "upload/pizzas/".$obj_busca_pizza->foto_grande  : 'img/pc/pizza_nutella_g.png')."' width='250' heigth='250' alt='Pizza ".$obj_busca_pizza->pizza."' ></img>";
					if($obj_buscar_sugestao->cod_promocoes!=12)
					{
						$conteudo .= "<p><span class='span_tamanho_sugestao'>(QUADRADINHA 4 PEDAÇOS - </span><span class='span_preco_sugestao'>".bd2moeda($obj_busca_pizza->preco)."</span><span class='span_tamanho_sugestao'>)</span></p>";
					}
					else
					{
						$conteudo .= "<p><span class='span_tamanho_sugestao'>(QUADRADINHA 4 PEDAÇOS - </span><span class='span_preco_sugestao'>GRÁTIS</span><span class='span_tamanho_sugestao'>)</span></p>";
					}
					if($botoes==1)//se $botoes forem 1, que é o valor padrão
					{
						//$conteudo .= "<div class='botoes_promo' >";
						$conteudo .= "<a href='javascript:void(0)' onclick='document.frmAdicionarSugestao.submit();'><img src='img/pc/".$btn_sim."' alt='".$alt_sim."' ></img></a>";
						$conteudo .= "&nbsp;&nbsp;&nbsp;&nbsp;";
						$conteudo .= "<a href='javascript:void(0)' onclick='document.frmNao.submit();'><img src='img/pc/".$btn_nao."' alt='".$alt_nao."' ></img></a>";
						//$conteudo .= "</div>";
					}
						
			 $conteudo .='</div>';
					}

			 $conteudo .='</div>';
			 $arr_json["conteudo"] = utf8_encode($conteudo);
		}
		else
		$arr_json["resposta"] = "nao";
		break;
		desconectar_bd($con);
		case 'verificar_sabor_doce':
		$con = conectabd();
			$cod_sabores = validaVarPost('arr');
			$arr_sabores = explode(',',$cod_sabores);
			$cont_sabores = count($arr_sabores);
			$lin = 0;
			$cadastrada = false;
			for($cod = 0;$cod<$cont_sabores;$cod++)
			{
				$sql_verifica = "select * from ipi_pizzas where cod_pizzas in(".$arr_sabores[$cod].") and tipo= 'Doce'";
				$res_verifica = mysql_query($sql_verifica);
				if(mysql_num_rows($res_verifica)>0)
				{
					$lin ++;
				}
			}
			$cod_promocoes = 3;
			if($cod_promocoes=="3") //fazer a promo ferias 2013 sobrescrever a doce
			{
				////////////////BUSCANDO CODIGO DAS PIZZARIAS//////////
				if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
				{
					$cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
				}
				else
				{
					$cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
					$cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
					$sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
					$res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
					$obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
					$cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
				}

				$sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '3' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
				$res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
				if(mysql_num_rows($res_buscar_promocoes)>0)
				{
					$cadastrada = true;
				}
			}
			if($lin==$cont_sabores && $cadastrada)
			{
				$arr_json["resposta"] = 'sim';
			}
			else
			{
				$arr_json["resposta"] = "nao";
			}
			desconectar_bd($con);
		break;
}

echo json_encode($arr_json);
