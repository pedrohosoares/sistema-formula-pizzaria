<?
session_start();
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';

$cod = validaVarPost("cod");
$acao = validaVarPost("acao");

$cep_visitante = validaVarPost("cep_visitante");
$cp = validaVarPost("cod_pizzarias","/[0-9]+/");

$lista_cods = validaVarPost("arr_cods");
$arr_cods = explode(",",$lista_cods);

switch($acao)
{
	case 'mostrar_detalhes':
		$con = conectabd();
		$sql_select_pizza = "select * from ipi_pizzas where cod_pizzas = '".$cod."'";
		$res_select_pizza = mysql_query($sql_select_pizza);
		if(mysql_num_rows($res_select_pizza)<=0)
		{
			$sql_select_pizza = "select * from ipi_pizzas where cod_pizzas";
			$res_select_pizza = mysql_query($sql_select_pizza);
		}
		$obj_select_pizza = mysql_fetch_object($res_select_pizza);
		//pizza_nutella_g.png
		if($obj_select_pizza->foto_grande)
		{
			echo "<div id='imagem_pizza' class='imagem_pizza' ><img class='tamanho_imagem_pizza' src='upload/pizzas/".($obj_select_pizza->foto_grande)."'/></div>";
		}else
		{
			echo "<div id='imagem_pizza' class='imagem_pizza' ><img class='tamanho_imagem_pizza' src='upload/pizzas/preview_pizza.png'/ ></div>";
		}
		$fit = false;
		if($obj_select_pizza->pizza_fit==1) $fit=true;
		echo "<div id='detalhes_pizza' align='left' class='detalhes_pizza' >";
			echo utf8_encode("<h1 style='margin-top:40px'>".mb_strtoupper($obj_select_pizza->pizza)."</h1>");
			
        
        $ingredientes = array ();
        $SqlBuscaIngredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_pizzas = " . $obj_select_pizza->cod_pizzas." AND i.consumo = 0	";
        $resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
        while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes))
        {
            $ingredientes[] = $objBuscaIngredientes->ingrediente;
        }
        echo "<div id='cardapio_ingredientes' class='cardapio_texto_ingredientes' > <h2 class='cor_laranja2 fonte16 fonte_muzza1'> Ingredientes </h2> <p class='cor_cinza1 fonte14 fonte_audimat'>".utf8_encode(implode(', ', $ingredientes))."</p></div>";
        

		  $sql_busca_preco = "SELECT * FROM ipi_pizzas_ipi_tamanhos p INNER JOIN ipi_tamanhos t ON (p.cod_tamanhos = t.cod_tamanhos) WHERE p.cod_pizzas = " . $obj_select_pizza->cod_pizzas . " and cod_pizzarias = $cp ORDER BY tamanho";
      $res_busca_preco = mysql_query($sql_busca_preco);
      
        
			echo "<table width='500'><tr>";
			echo "<td colspan='".mysql_num_rows($res_busca_preco)."'><strong>Valores</strong></td></tr><tr>";
			while($obj_busca_preco = mysql_fetch_object($res_busca_preco))
			{
			  $arr_tamanho_aux = explode(')', utf8_encode($obj_busca_preco->tamanho));
				echo "<td width='80px'> <span style='font-size: 8pt'>(".$arr_tamanho_aux[0].")</span>";
				echo "<p width='80px' class='fonte_audimat fonte14 cor_cinza1'>".$arr_tamanho_aux[1]."</p></td>";
			}
			$res_busca_preco = mysql_query($sql_busca_preco);
			echo "</tr><tr >";
			while($obj_busca_preco = mysql_fetch_object($res_busca_preco))
			{
				echo "<td width='17px' ><div class='modal_preco_cardapio'><span style='font-size:8pt;'>R$</span> <strong> ".bd2moeda($obj_busca_preco->preco)."</strong></div></td>";
			}
			
			// onClick="mostrar_detalhes('.$objBuscaPizzas->cod_pizzas.',\'mostrar_detalhes\')
			$qtd = count($arr_cods);
			$array_i = array_search($obj_select_pizza->cod_pizzas,$arr_cods);
			if($array_i<=0)
			{
				$cod_ant = $arr_cods[$qtd - 1];
			}
			else
			{
				$cod_ant = $arr_cods[$array_i-1];
			}
			if($array_i>=$qtd -1)
			{
				$cod_prox= $arr_cods[0];
			}
			else
			{
				$cod_prox = $arr_cods[$array_i+1];
			}
			echo "</table>";
			echo "<br>";
			//echo "<div id='tabela_nutricional' class='tabela_nutricional' ></div>";
		echo "</div>";

		echo "<div class='clear'></div>";
			echo "<div id='cardapio_botoes' style='float: right; width: 600px'>";
			echo "<div class='seta_esquerda'>";//btn_anterior_p.png btn_proximo_p.png
				echo "<a href='javascript:void(0)' onClick='mostrar_detalhes_sem_nodal(".$cod_ant.",\"mostrar_detalhes\")' class='' style='color: #FFCC08;'>Anterior</a></div>";
			echo "<div class='seta_direita'><a style='color: #FFCC08;' href='javascript:void(0)' onClick='mostrar_detalhes_sem_nodal(".$cod_prox.",\"mostrar_detalhes\")' >Proximo</a>";
			echo "</div>";
		echo '</div>';

		desconectabd($con);
	break;
	case 'mostrar_detalhes_borda':
		$con = conectabd();
		$sql_select_pizza = "select * from ipi_bordas where cod_bordas = ".$cod;
		$res_select_pizza = mysql_query($sql_select_pizza);
		$obj_select_pizza = mysql_fetch_object($res_select_pizza);
		//pizza_nutella_g.png
		echo "<div id='imagem_pizza' class='imagem_pizza' ><img class='tamanho_imagem_pizza' src='upload/bordas/".$obj_select_pizza->foto_grande."'/></div>";
		echo "<div id='detalhes_pizza' align='left' class='detalhes_pizza'>";
			echo utf8_encode("<h2 class='cor_marrom2'>".mb_strtoupper($obj_select_pizza->borda)."</h2>");
			echo '<br />';
        
        echo "<div id='cardapio_ingredientes' class='cardapio_texto_ingredientes' >".utf8_encode($obj_select_pizza->borda)."</div>";
        
			echo "<br />";
		  $sql_busca_preco = "SELECT * FROM ipi_tamanhos_ipi_bordas b INNER JOIN ipi_tamanhos t ON (b.cod_tamanhos = t.cod_tamanhos) WHERE b.cod_bordas = " . $obj_select_pizza->cod_bordas . " and cod_pizzarias = $cp ORDER BY tamanho";
      $res_busca_preco = mysql_query($sql_busca_preco);
      
        
			echo "<table ><tr>";
			echo "<td colspan='".mysql_num_rows($res_busca_preco)."' class='cor_marrom2 cardapio_texto_valores'>Valores para as pizzas</td></tr><tr>";
			while($obj_busca_preco = mysql_fetch_object($res_busca_preco))
			{
				echo "<td width='80px' class='cardapio_texto_tamanho'>".utf8_encode($obj_busca_preco->tamanho)."</td>";
			}
			$res_busca_preco = mysql_query($sql_busca_preco);
			echo "</tr><tr >";
			while($obj_busca_preco = mysql_fetch_object($res_busca_preco))
			{
				echo "<td width='17px' class='cardapio_texto_tamanho'><strong> R$ ".bd2moeda($obj_busca_preco->preco)."</strong></td>";
			}
			
			// onClick="mostrar_detalhes('.$objBuscaPizzas->cod_pizzas.',\'mostrar_detalhes\')
			$qtd = count($arr_cods);
			$array_i = array_search($obj_select_pizza->cod_bordas,$arr_cods);
			if($array_i<=0)
			{
				$cod_ant = $arr_cods[$qtd - 1];
			}
			else
			{
				$cod_ant = $arr_cods[$array_i-1];
			}
			if($array_i>=$qtd -1)
			{
				$cod_prox= $arr_cods[0];
			}
			else
			{
				$cod_prox = $arr_cods[$array_i+1];
			}
			echo "</table>";
			echo "<br>";
			echo "<div id='tabela_nutricional' class='tabela_nutricional' ></div>";
		echo "</div>";

		echo "<div class='clear'></div>";
			echo "<div id='cardapio_botoes' class='cardapio_botoes'>";//btn_anterior_p.png btn_proximo_p.png
				echo "<a href='javascript:void(0)' onClick='mostrar_detalhes_sem_nodal(".$cod_ant.",\"mostrar_detalhes_borda\")' > <img alt='Anterior' src='img/pc/btn_anterior_p.png' ></img> </a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<a href='javascript:void(0)' onClick='mostrar_detalhes_sem_nodal(".$cod_prox.",\"mostrar_detalhes_borda\")' > <img alt='Proximo' src='img/pc/btn_proximo_p.png' ></img></a>";
			//	echo "<img alt='Pedir' class='cardapio_botao_pedir' src='img/pc/btn_pedir_g.png' >";
			echo "</div>";

		desconectabd($con);
	break;
	case 'buscar_cep':
		$con = conectar_bd();
 			$cpc = validaVarPost('cp',"/[0-9]+/");
 			if($cpc=="")
 			{
				$cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));

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
			}else
			{
				$cod_pizzarias = $cpc;
			}
			 if ($cod_pizzarias=="")
				{
					echo '<br/>';
					// echo '<br>CEP: '.$objPizzarias->cep;
					$SqlPizzarias = 'SELECT * FROM ipi_pizzarias WHERE situacao="ATIVO" order by cidade';
					$resPizzarias = mysql_query ($SqlPizzarias);
					echo "<h1>SAIBA SE ENTREGAMOS EM SUA CASA</h1>";
					echo utf8_encode("<br />Seu CEP <b>(".$cep_visitante.")</b> não está na nossa área de cobertura.");
					echo utf8_encode("<br />Escolha uma de nossas pizzarias para ver o cardápio: ");
					echo "<div class='cardapio_cmb_pizzaria left'><select id='cmb_pizzarias' name='pizzarias' class='select_pizzaria'>";
					while($objPizzarias = mysql_fetch_object($resPizzarias)) 
					{
						echo utf8_encode("<option value='".$objPizzarias->cod_pizzarias."'>".$objPizzarias->cidade." - ".$objPizzarias->bairro."</option>");
					}
					echo "</select></div><a href='javascript:void(0)' onclick='buscar_cep_2(\"cmb_pizzarias\",\"buscar_cep\")' class='btn btn-success enviar_cep_modal left'>&nbsp;&nbsp;Enviar Cep</a>";
					echo '<div style="clear:both"></div><br/><br/>';
				}
				else
				{
					    $dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
					    $dia_semana_completo = array('domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado');
						  $sql_verificar_funcionamento = "SELECT * FROM ipi_pizzarias_funcionamento WHERE cod_pizzarias = ".$cod_pizzarias." AND '".date("w")."' IN (dia_semana)";
					    $res_verificar_funcionamento = mysql_query($sql_verificar_funcionamento);
    					$num_verificar_funcionamento = mysql_num_rows($res_verificar_funcionamento);
    					
    				//	if($num_verificar_funcionamento>=1)
    					//{
    						echo "<script>salvar_cep('$cod_pizzarias')</script>";
								$sql_cep_pizzaria = "SELECT cep,cod_pizzarias FROM ipi_pizzarias WHERE cod_pizzarias = ".$cod_pizzarias;
					   		$res_cep_pizzaria = mysql_query($sql_cep_pizzaria);
								$obj_cep_pizzaria = mysql_fetch_object($res_cep_pizzaria);
								$_SESSION['ipi_carrinho']['cep_cardapio'] =  $obj_cep_pizzaria->cep;	
								$_SESSION['ipi_carrinho']['pizzaria_cardapio']	= $cod_pizzarias;			    					
    				//	}
    				/*	else
    					{
    						$_SESSION['ipi_carrinho']['pizzaria_cardapio']	= $cod_pizzarias;
    						echo utf8_encode('<div id="cardapio_exclamacao" class="cardapio_exclamacao"><img src="img/pc/exclamacao_g_03.png" alt="Atenção"></img></div>');
    						echo utf8_encode('<div id="cardapio_texto_fechada" class="cardapio_texto_fechada"><span class="fonte_muzza1_negrito fonte20 cor_marrom2">Hoje é '.$dia_semana_completo[date("w")].' e esta pizzaria <br/>está fechada no momento.<br/>Clique abaixo para ver o carpapio <br/> mesmo assim.</span>');
    						echo '<br/><br/><input type="image" align="right" class="cardapio_botao_ver" name="botaoVerCardapio" onclick="salvar_cep('.$cod_pizzarias.');" src="img/pc/btn_ver_cardapio.png" /></div>';
    					
    					}*/
				}
				desconectar_bd($con);
			
	break;
}
?>
