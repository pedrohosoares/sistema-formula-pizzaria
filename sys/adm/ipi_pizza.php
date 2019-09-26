<?php

/**
 * ipi_pizza.php: Cadastro de Pizzas
 * 
 * Índice: cod_pizzas
 * Tabela: ipi_pizzas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de '.TIPO_PRODUTOS);

$acao = validaVarPost('acao');

$tabela = 'ipi_pizzas';
$chave_primaria = 'cod_pizzas';

switch ($acao)
{
	case 'excluir' :
	$excluir = validaVarPost('excluir');
	$indicesSql = implode(',', $excluir);

	$con = conectabd();


	$SqlDel1 = "DELETE FROM ipi_ingredientes_ipi_pizzas WHERE $chave_primaria IN ($indicesSql)";
	$SqlDel4 = "DELETE FROM ipi_ingredientes_estoque WHERE $chave_primaria IN ($indicesSql)";
	$SqlDel2 = "DELETE FROM ipi_pizzas_ipi_tamanhos WHERE $chave_primaria IN ($indicesSql)";
	$SqlDel3 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";

	$resDel1 = mysql_query($SqlDel1);
	$resDel4 = mysql_query($SqlDel4);
	$resDel2 = mysql_query($SqlDel2);
	$resDel3 = mysql_query($SqlDel3);

	if ($resDel1 && $resDel2 && $resDel3 && $resDel4)
		mensagemOk('Os registros selecionados foram excluídos com sucesso!');
	else
		mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');

	desconectabd($con);
	break;
	case 'editar' :
	$codigo = validaVarPost($chave_primaria);
	$pizza = validaVarPost('pizza');
	$tipo_pizza = validaVarPost('cod_tipo_pizza');
	$tipo = validaVarPost('tipo');
	$sugestao = (validaVarPost('sugestao') == 'on') ? 1 : 0;
	$novidade = (validaVarPost('novidade') == 'on') ? 1 : 0;
	$pizza_fit = (validaVarPost('pizza_fit') == 'on') ? 1 : 0;
	$tamanho = validaVarPost('tamanho');
	$tamanho_checkbox = validaVarPost('tamanho_checkbox');
	$preco = validaVarPost('preco');
	$fidelidade = validaVarPost('fidelidade');
	$ingrediente = validaVarPost('ingrediente');
	$venda_online = (validaVarPost('venda_online') == 'on') ? 1 : 0;
	$codigo_cliente_pizza = validaVarPost('codigo_cliente_pizza');

	$ncm = validaVarPost('ncm');
	$cfop = validaVarPost('cfop');
	$cest = validaVarPost('cest');
	$cst_icms = validaVarPost('cst_icms');
	$cst_pis_cofins = validaVarPost('cst_pis_cofins');
	$aliq_icms = validaVarPost('aliq_icms');


	$foto_p = validaVarFiles('foto_p');
	$foto_g = validaVarFiles('foto_g');

	$con = conectabd();

	if ($codigo <= 0)
	{

		if ($codigo_cliente_pizza != "")
		{
			$sql_codigo_pizza = "SELECT p.pizza FROM ipi_pizzas p WHERE p.codigo_cliente_pizza = '".$codigo_cliente_pizza."'";
            //echo $sql_codigo_pizza;
			$res_codigo_pizza = mysql_query($sql_codigo_pizza);
			$num_codigo_pizza = mysql_num_rows($res_codigo_pizza);
		}
		else
		{
			$num_codigo_pizza=0;            
		}

		if ($num_codigo_pizza ==0)
		{
			if ($codigo_cliente_pizza != "")
			{            
				$sql_codigo = "SELECT bc.codigo_cliente_bebida, b.bebida, c.conteudo FROM ipi_bebidas_ipi_conteudos bc LEFT JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) LEFT JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) LEFT JOIN ipi_tipo_bebida tb ON (b.cod_tipo_bebida= tb.cod_tipo_bebida) WHERE bc.codigo_cliente_bebida = '".$codigo_cliente_pizza."'";
				$res_codigo = mysql_query($sql_codigo);
              //echo $sql_codigo;
				$num_codigo = mysql_num_rows($res_codigo);
			}
			else
			{
				$num_codigo=0;            
			}


			if ($num_codigo ==0)
			{




				$SqlEdicao = sprintf("INSERT INTO $tabela (pizza, cod_tipo_pizza,tipo, sugestao, novidade, pizza_fit,venda_online, codigo_cliente_pizza,ncm,cest,cst_icms,cst_pis_cofins,aliq_icms,cfop) VALUES ('%s','%s', '%s', %d, %d, %d,%d,'%s','%s')", $pizza, $tipo_pizza, $tipo, $sugestao, $novidade,  $pizza_fit, $venda_online, $codigo_cliente_pizza,$ncm,$cest,$cst_icms,$cst_pis_cofins,$aliq_icms,$cfop);
              //echo"INSERT ". $SqlEdicao;
				$resEdicaoIngrediente = true;
				$res_edicao_estoque = true;


				if (mysql_query($SqlEdicao))
				{
					$codigo = mysql_insert_id();


					if (is_array($ingrediente))
					{
						foreach ( $ingrediente as $cod_ingrediente )
						{
							$SqlEdicaoIngrediente = sprintf("INSERT INTO ipi_ingredientes_ipi_pizzas (cod_pizzas, cod_ingredientes) VALUES (%d, %d)", $codigo, $cod_ingrediente);
                      //echo "<br />B: ".$SqlEdicaoIngrediente;
							$resEdicaoIngrediente &= mysql_query($SqlEdicaoIngrediente);
						}
					}

					$sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
					$res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);

					if (is_array($ingrediente))
					{
						while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
						{
							foreach ( $ingrediente as $cod_ingrediente )
							{
								$nome_campo_quantidade = 'quantidade_'.$obj_buscar_tamanhos->cod_tamanhos.'_'.$cod_ingrediente.'';
								$$nome_campo_quantidade = $nome_campo_quantidade;
								$valor_campo_quantidade = validaVarPost($nome_campo_quantidade);

								$sql_edicao_estoque = sprintf("INSERT INTO ipi_ingredientes_estoque (cod_pizzas, cod_tamanhos, cod_ingredientes, quantidade_estoque_ingrediente) VALUES (%d, %d, %d, '%f')", $codigo, $obj_buscar_tamanhos->cod_tamanhos, $cod_ingrediente, moeda2bd($valor_campo_quantidade));
                        //echo "<br />I: ".$nome_campo_quantidade." - ".$sql_edicao_estoque;
//echo "v ESTESTEST v";
								$res_edicao_estoque &= mysql_query($sql_edicao_estoque);
							}
						}
					}


					if ($resEdicaoIngrediente && $res_edicao_estoque)
					{
						$resEdicao = true;
                    //mensagemOk('Registro adicionado com êxito!');
					}
					else
					{
						$resEdicao = false;
                    //echo "erro1";
                    //mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
					}
				}
				else
				{
					$resEdicao = false;
               // echo "erro2";
                //mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
				}

			}
			else
			{
				$obj_codigo = mysql_fetch_object($res_codigo);
				mensagemErro('Erro ao adicionar o registro', 'O código: <strong>'.$obj_codigo->codigo_cliente_bebida.'</strong>, já está sendo usado na bebida: <strong>'.$obj_codigo->bebida.' - '.$obj_codigo->conteudo).'</strong>';
			}
		}
		else
		{
			$obj_codigo_pizza = mysql_fetch_object($res_codigo_pizza);
			mensagemErro('Erro ao adicionar o registro', 'O código: <strong>'.$codigo_cliente_pizza.'</strong>, já está sendo usado no produto: <strong>'.$obj_codigo_pizza->pizza.'</strong>');
		}




	}
	else
	{   

		if ($codigo_cliente_pizza != "")
		{
			$sql_codigo_pizza = "SELECT p.pizza FROM ipi_pizzas p WHERE p.codigo_cliente_pizza = '".$codigo_cliente_pizza."' AND p.cod_pizzas <> '".$codigo."'";
			$res_codigo_pizza = mysql_query($sql_codigo_pizza);
			$num_codigo_pizza = mysql_num_rows($res_codigo_pizza);
		}
		else
		{
			$num_codigo_pizza=0;            
		}

		if ($num_codigo_pizza ==0)
		{
			if ($codigo_cliente_pizza != "")
			{
				$sql_codigo = "SELECT bc.codigo_cliente_bebida, b.bebida, c.conteudo FROM ipi_bebidas_ipi_conteudos bc LEFT JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) LEFT JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) LEFT JOIN ipi_tipo_bebida tb ON (b.cod_tipo_bebida= tb.cod_tipo_bebida) WHERE bc.codigo_cliente_bebida = '".$codigo_cliente_pizza."'";
              //echo $sql_codigo;
				$res_codigo = mysql_query($sql_codigo);
				$num_codigo = mysql_num_rows($res_codigo);
			}
			else
			{
				$num_codigo=0;            
			}

			if ($num_codigo ==0)
			{


				$SqlEdicao = sprintf("UPDATE $tabela SET pizza = '%s', cod_tipo_pizza = '%s', tipo = '%s', sugestao = %d, novidade = %d,  pizza_fit = %d, venda_online = %d, codigo_cliente_pizza = '%s',ncm = '%s',cest = '%s',cst_icms = '%s',cst_pis_cofins = '%s',aliq_icms = '%s',cfop = '%s'  WHERE $chave_primaria = $codigo", $pizza, $tipo_pizza, $tipo, $sugestao, $novidade, $pizza_fit, $venda_online, $codigo_cliente_pizza,$ncm,$cest,$cst_icms,$cst_pis_cofins,$aliq_icms,$cfop);
              //echo $SqlEdicao;
				if (mysql_query($SqlEdicao))
				{
					$resEdicaoIngrediente = true;
					$res_edicao_estoque = true;


					if (is_array($ingrediente))
					{
						$SqlDelIngredientes = "DELETE FROM ipi_ingredientes_ipi_pizzas WHERE $chave_primaria = $codigo";
						$resDelIngredientes = mysql_query($SqlDelIngredientes);
                      //echo "<br />E: ".$SqlDelIngredientes;
					}
					else
					{
						$resDelIngredientes = true;
					}

                  //if (is_array($cod_ingredientes_estoque))
                  //{
					$sql_del_estoque = "DELETE FROM ipi_ingredientes_estoque WHERE $chave_primaria = $codigo";
					$res_del_estoque = mysql_query($sql_del_estoque);
                      //echo "<br />F: ".$sql_del_estoque;
                      //}
                  //else
                  //{
                  //    $res_del_estoque = true;
                  //}

                  //echo "INGREDIENTE ".$ingrediente. "                    tam ".$tamanho_checkbox;    
					if (is_array($ingrediente))
					{
						foreach ( $ingrediente as $cod_ingrediente )
						{
							$SqlEdicaoIngrediente = sprintf("INSERT INTO ipi_ingredientes_ipi_pizzas (cod_pizzas, cod_ingredientes) VALUES (%d, %d)", $codigo, $cod_ingrediente);
                              //echo "<br />H: ".$SqlEdicaoIngrediente;
							$resEdicaoIngrediente &= mysql_query($SqlEdicaoIngrediente);
						}
					}


					$sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
					$res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);


					if (is_array($ingrediente))
					{
						while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
						{
							foreach ( $ingrediente as $cod_ingrediente )
							{
								$nome_campo_quantidade = 'quantidade_'.$obj_buscar_tamanhos->cod_tamanhos.'_'.$cod_ingrediente.'';
								$$nome_campo_quantidade = $nome_campo_quantidade;
								$valor_campo_quantidade = validaVarPost($nome_campo_quantidade);

								$sql_edicao_estoque = sprintf("INSERT INTO ipi_ingredientes_estoque (cod_pizzas, cod_tamanhos, cod_ingredientes, quantidade_estoque_ingrediente) VALUES (%d, %d, %d, '%f')", $codigo, $obj_buscar_tamanhos->cod_tamanhos, $cod_ingrediente, moeda2bd($valor_campo_quantidade));
                           // echo "<br />I: ".$nome_campo_quantidade." - ".$sql_edicao_estoque;
								$res_edicao_estoque &= mysql_query($sql_edicao_estoque);
							}
						}
					}



					if ($resEdicaoIngrediente && $res_edicao_estoque)
					{
						$resEdicao = true;
                        //mensagemOk('Registro alterado com êxito!');
					}
					else
					{
						$resEdicao = false;
                        //mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
					}
				}
				else
				{
					$resEdicao = false;
                    //mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
				}

			}
			else
			{
				$obj_codigo = mysql_fetch_object($res_codigo);
				mensagemErro('Erro ao adicionar o registro', 'O código: <strong>'.$obj_codigo->codigo_cliente_bebida.'</strong>, já está sendo usado na bebida: <strong>'.$obj_codigo->bebida.' - '.$obj_codigo->conteudo).'</strong>';
			}
		}
		else
		{
			$obj_codigo_pizza = mysql_fetch_object($res_codigo_pizza);
			mensagemErro('Erro ao adicionar o registro', 'O código: <strong>'.$codigo_cliente_pizza.'</strong>, já está sendo usado no produto: <strong>'.$obj_codigo_pizza->pizza.'</strong>');
		}
	}

	if ($resEdicao)
	{
		$resEdicaoImagem = true;
          // Alterando as Imagens pequenas
		if(count($foto_p['name']) > 0) {     
			if(trim($foto_p['name']) != '') {
				$arq_info = pathinfo($foto_p['name']);
				$arq_ext = $arq_info['extension'];
				if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $foto_p["type"])) {
					mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
				}
				else {                
					$resEdicaoImagem &= move_uploaded_file($foto_p['tmp_name'], UPLOAD_DIR."/pizzas/${codigo}_pizza_p.${arq_ext}");

					$SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_pequena = '%s' WHERE $chave_primaria = $codigo", 
						texto2bd("${codigo}_pizza_p.${arq_ext}"));

					$resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
				}
			}          
		}

          // Alterando as Imagens grandes
		if(count($foto_g['name']) > 0) {     
			if(trim($foto_g['name']) != '') {
				$arq_info = pathinfo($foto_g['name']);
				$arq_ext = $arq_info['extension'];
				if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $foto_g["type"])) {
					mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
				}
				else {                
					$resEdicaoImagem &= move_uploaded_file($foto_g['tmp_name'], UPLOAD_DIR."/pizzas/${codigo}_pizza_g.${arq_ext}");

					$SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_grande = '%s' WHERE $chave_primaria = $codigo", 
						texto2bd("${codigo}_pizza_g.${arq_ext}"));

					$resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
				}
			}          
		}          

		if($resEdicaoImagem) {
			mensagemOk('Registro alterado com êxito!');
		}
		else {
			mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
		}
	}
        /*
        else
        {
            mensagemErro('Erro ao cadastrar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        */
        
        desconectabd($con);
        break;
    }

    ?>

    <link rel="stylesheet" type="text/css" media="screen"
    href="../lib/css/tabs_simples.css" />

    <script>

    	function verificaCheckbox(form) {
    		var cInput = 0;
    		var checkBox = form.getElementsByTagName('input');

    		for (var i = 0; i < checkBox.length; i++) {
    			if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
    				cInput++; 
    			}
    		}

    		if(cInput > 0) {
    			if (confirm('Deseja excluir os registros selecionados?')) {
    				return true;
    			}
    			else {
    				return false;
    			}
    		}
    		else {
    			alert('Por favor, selecione os itens que deseja excluir.');

    			return false;
    		}
    	}

    	function editar(cod) {
    		var form = new Element('form', {
    			'action': '<?
    			echo $_SERVER['PHP_SELF']?>',
    			'method': 'post'
    		});

    		var input = new Element('input', {
    			'type': 'hidden',
    			'name': '<?
    			echo $chave_primaria?>',
    			'value': cod
    		});

    		input.inject(form);
    		$(document.body).adopt(form);

    		form.submit();
    	}

    	function limpaPrecoFidelidade(cod) {
    		document.getElementById('preco_' + cod).value = '';
    		document.getElementById('fidelidade_' + cod).value = '';
    	}

    	function excluirImagem_pequena(cod) {
    		if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
    			var acao = 'excluir_imagem_pequena';
    			var cod_pizzas = cod;

    			if(cod_pizzas > 0) {
    				var url = 'acao=' + acao + '&cod_pizzas=' + cod_pizzas;

    				new Request.JSON({url: 'ipi_pizza_ajax.php', onComplete: function(retorno) {
    					if(retorno.status != 'OK') {
    						alert('Erro ao excluir esta imagem.');
    					}
    					else {
    						if($('foto_p_figura')) {
    							$('foto_p_figura').destroy();

    						}
    					}
    				}}).send(url);
    			}
    		}
    	}

    	function excluirImagem(cod) {
    		if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
    			var acao = 'excluir_imagem';
    			var cod_pizzas = cod;

    			if(cod_pizzas > 0) {
    				var url = 'acao=' + acao + '&cod_pizzas=' + cod_pizzas;

    				new Request.JSON({url: 'ipi_pizza_ajax.php', onComplete: function(retorno) {
    					if(retorno.status != 'OK') {
    						alert('Erro ao excluir esta imagem.');
    					}
    					else {
    						if($('foto_g_figura')) {
    							$('foto_g_figura').destroy();

    						}
    					}
    				}}).send(url);
    			}
    		}
    	}

    	function atalhos(event)
    	{

    		if (event.key == '+' && event.shift) 
    		{
    			tabs.irpara(1);
    			$("pizza").focus();
    		}
    		else if (event.key == '-' && event.shift) 
    		{
    			tabs.irpara(0);
    		}
    	}

    	var tabs;

    	window.addEvent('keydown', atalhos);

    	window.addEvent('domready', function(){
    		tabs = new Tabs('tabs'); 

    		if (document.frmIncluir.<?
    			echo $chave_primaria?>.value > 0) {
    			<?
    			if ($acao == '')
    				echo 'tabs.irpara(1);';
    			?>

    			document.frmIncluir.botao_submit.value = 'Alterar';
    		}
    		else {
    			document.frmIncluir.botao_submit.value = 'Cadastrar';
    		}

    		tabs.addEvent('change', function(indice){
    			if(indice == 1) {
    				document.frmIncluir.<?php echo $chave_primaria?>.value = '';
    				document.frmIncluir.pizza.value = '';
    				document.frmIncluir.tipo.value = '';
    				document.frmIncluir.cod_tipo_pizza.value = '';
    				document.frmIncluir.codigo_cliente_pizza.value = '';
    				document.frmIncluir.sugestao.checked = false;
    				document.frmIncluir.novidade.checked = false;
    				document.frmIncluir.pizza_fit.checked = false;

    				marcaTodosEstado('marcar_tamanho', false);
    				marcaTodosEstado('marcar_ingrediente', false);

      // Limpando todos os campos input para Preço e Fidelidade
      var input = document.getElementsByTagName('input');
      for (var i = 0; i < input.length; i++) {
      	if(input[i].name.match('preco')) { 
      		input[i].value = ''; 
      	}
      }
      
      var input = document.getElementsByTagName('input');
      for (var i = 0; i < input.length; i++) {
      	if(input[i].name.match('fidelidade')) { 
      		input[i].value = ''; 
      	}
      }
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
});
    	});

    </script>

    <div id="tabs">
    	<div class="menuTab">
    		<ul>
    			<li><a href="javascript:;">Editar</a></li>
    			<li><a href="javascript:;">Incluir</a></li>
    		</ul>
    	</div>

    	<!-- Tab Editar -->
    	<div class="painelTab">
    		<table>
    			<tr>

    				<!-- Conteúdo -->
    				<td class="conteudo">

    					<form name="frmExcluir" method="post"
    					onsubmit="return verificaCheckbox(this)">

    					<table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    						<tr>
    							<td><input class="botaoAzul" type="submit"
    								value="Excluir Selecionados"></td>
    							</tr>
    						</table>

    						<table class="listaEdicao" cellpadding="0" cellspacing="0">
    							<thead>
    								<tr>
    									<td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
    									<td align="center" width="80">Código</td>
    									<td align="center">Tipo <?php echo TIPO_PRODUTO ?></td>
    									<td align="center"><?php echo TIPO_PRODUTO ?></td>
    									<td align="center">Sabor</td>
    									<td align="center" width="80">Exibir na Sugestão</td>
    									<td align="center" width="80">Novidade</td>
    									<td align="center" width="80">Fit</td>
    									<td align="center" width="80">Vender Online</td>
    								</tr>
    							</thead>
    							<tbody>

    								<?

    								$con = conectabd();

        //$SqlBuscaPizzas = "SELECT * FROM $tabela ORDER BY pizza";
    								$SqlBuscaPizzas= "SELECT p.cod_pizzas, p.cod_tipo_pizza,p.pizza,  p.sugestao, p.novidade, p.pizza_fit, p.tipo, tp.tipo_pizza, p.codigo_cliente_pizza, p.venda_online  FROM ipi_pizzas p LEFT JOIN ipi_tipo_pizza tp ON (p.cod_tipo_pizza= tp.cod_tipo_pizza) ORDER BY tp.tipo_pizza ASC, p.pizza ASC, p.codigo_cliente_pizza ASC";
        //echo $SqlBuscaPizzas;
    								$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
    								$resBuscaPizzas = mysql_query($SqlBuscaPizzas);

    								while ( $objBuscaPizzas = mysql_fetch_object($resBuscaPizzas) )
    								{
    									echo '<tr>';

    									echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $objBuscaPizzas->$chave_primaria . '"></td>';

    									echo '<td align="center">' . bd2texto($objBuscaPizzas->codigo_cliente_pizza) . '</td>';
    									echo '<td align="center">' . bd2texto($objBuscaPizzas->tipo_pizza) . '</td>';
    									echo '<td align="center"><a href="javascript:;" onclick="editar(' . $objBuscaPizzas->$chave_primaria . ')">' . bd2texto($objBuscaPizzas->pizza) . '</a></td>';
    									echo '<td align="center">' .($objBuscaPizzas->tipo=='Salgado' ? 'Salgada' : bd2texto($objBuscaPizzas->tipo)) . '</td>';
    									echo '<td align="center">' . ($objBuscaPizzas->sugestao == "1" ? '<img src="../lib/img/principal/ok.gif">' : '<img src="../lib/img/principal/erro.gif">') . '</td>';
    									echo '<td align="center">' . ($objBuscaPizzas->novidade == "1" ? '<img src="../lib/img/principal/ok.gif">' : '<img src="../lib/img/principal/erro.gif">') . '</td>';
    									echo '<td align="center">' . ($objBuscaPizzas->pizza_fit == "1" ? '<img src="../lib/img/principal/ok.gif">' : '<img src="../lib/img/principal/erro.gif">') . '</td>';
    									echo '<td align="center">' . ($objBuscaPizzas->venda_online == "1" ? '<img src="../lib/img/principal/ok.gif">' : '<img src="../lib/img/principal/erro.gif">') . '</td>';

    									echo '</tr>';
    								}

    								desconectabd($con);

    								?>

    							</tbody>
    						</table>

    						<input type="hidden" name="acao" value="excluir"></form>

    					</td>
    					<!-- Conteúdo -->

    					<!-- Barra Lateral -->
    					<td class="lateral">
    						<div class="blocoNavegacao">
    							<ul>
    								<li><a href="ipi_adicional.php">Adicionais</a></li>
    								<li><a href="ipi_borda.php">Bordas</a></li>
    								<li><a href="ipi_pizza.php"><? echo ucfirst(TIPO_PRODUTOS) ?></a></li>
    								<li><a href="ipi_tamanho.php">Tamanhos</a></li>
    								<li><a href="ipi_unidade_padrao.php">Unidade Padrão</a></li>
    								<li><a href="ipi_ingrediente_marcas.php">Ingredientes - Marcas</a></li>
    								<li><a href="ipi_tipo_cortes.php">Cortes</a></li>
    								<li><a href="ipi_tipo_massa.php">Massas</a></li>
    							</ul>
    						</div>
    					</td>
    					<!-- Barra Lateral -->

    				</tr>
    			</table>
    		</div>
    		<!-- Tab Editar --> <!-- Tab Incluir -->
    		<div class="painelTab">
    			<?
    			$codigo = validaVarPost($chave_primaria, '/[0-9]+/');

    			if ($codigo > 0)
    			{
    				$objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");

    			}
    			?>

    			<form name="frmIncluir" method="post" enctype="multipart/form-data"
    			onsubmit="return validaRequeridos(this)">

    			<table align="center" class="caixa" cellpadding="0" cellspacing="0">

    				<tr>
    					<td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_tipo_pizza">Tipo (<?php echo TIPO_PRODUTO ?>)</label></td>
    				</tr>
    				<tr>
    					<td class="tdbl tdbr sep">   <select class="requerido" name="cod_tipo_pizza" id="cod_tipo_pizza" >
    						<option value =""></option>
    						<?
    						$con = conectar_bd();
    						$sql_busca_tipos = "SELECT cod_tipo_pizza, tipo_pizza FROM ipi_tipo_pizza WHERE situacao = 'ATIVO' order by tipo_pizza ASC";



    						$res_busca_tipos = mysql_query($sql_busca_tipos);

    						while($obj_busca_tipos = mysql_fetch_object($res_busca_tipos))
    						{
    							echo "<option value='".$obj_busca_tipos->cod_tipo_pizza."'";
    							if($objBusca->cod_tipo_pizza==$obj_busca_tipos->cod_tipo_pizza)
    							{
    								echo " SELECTED ";
    							}
    							echo ">".$obj_busca_tipos->tipo_pizza."</option>";

    						}
    						desconectar_bd($con);
    						?>
    					</select>
    				</tr>

    				<tr>
    					<td class="legenda tdbl   tdbr">
    						<label class="requerido" for="pizza"><?php echo TIPO_PRODUTO ?></label>
    					</td>
    				</tr>
    				<tr>
    					<td class="tdbl tdbr sep ">
    						<input class="requerido" type="text" name="pizza" id="pizza" maxlength="45" size="45" value="<? echo texto2bd($objBusca->pizza)?>">
    					</td>
    				</tr>


    				<tr>
    					<td class="legenda tdbl tdbr">
    						<label class="" for="codigo_cliente_pizza">Código (<?php echo TIPO_PRODUTO ?>)</label>
    					</td>
    				</tr>
    				<tr>
    					<td class="tdbl tdbr sep ">
    						<input class="" type="text" name="codigo_cliente_pizza" id="codigo_cliente_pizza" maxlength="10" size="10" value="<? echo texto2bd($objBusca->codigo_cliente_pizza)?>">
    					</td>
    				</tr>


    				<?php
	#SE FOR DIRETOR ADM OU CONTADOR
    				$user = $_SESSION['usuario']['perfil'];
    				if($user == 1 or $user == 2 or $user == 15){
    					?>
    					<tr>
    						<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">NCM</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<input class="" required type="text" name="ncm" id="ncm" maxlength="10" size="10" value="<? echo texto2bd($objBusca->ncm)?>">
    						</td>
    					</tr>
    					<tr>
    						<td class="legenda tdbl tdbr "><label for="tipo">CEST</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<input class="" type="text" name="cest" id="cest" maxlength="10" size="10" value="<? echo texto2bd($objBusca->cest)?>">
    						</td>
    					</tr>
    					<tr>
    						<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">CFOP</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<input class="" type="text" required name="cfop" id="cfop" maxlength="10" size="10" value="<? echo texto2bd($objBusca->cfop)?>">
    						</td>
    					</tr>
    					<tr>
    						<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">ICMS</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<input class="" type="text" required name="cst_icms" id="cst_icms" maxlength="10" size="10" value="<? echo texto2bd($objBusca->cst_icms)?>">
    						</td>
    					</tr>
    					<tr>
    						<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">PIS - COFINS</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<input class="" type="text" required name="cst_pis_cofins" id="cst_pis_cofins" maxlength="10" size="10" value="<? echo texto2bd($objBusca->cst_pis_cofins)?>">
    						</td>
    					</tr>
    					<tr>
    						<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">Aliquota ICMS</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<input class="" type="text" required name="aliq_icms" id="aliq_icms" maxlength="10" size="10" value="<? echo texto2bd($objBusca->aliq_icms)?>">
    						</td>
    					</tr>
    					<?php
	#FIM SÓ DIRETOR ADM E CONTADOR
    				}else{
    					?>
    					<tr>
    						<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">NCM</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<? echo texto2bd($objBusca->ncm)?>
    						</td>
    					</tr>
    					<tr>
    						<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">CEST</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<? echo texto2bd($objBusca->cest)?>
    						</td>
    					</tr>
    					<tr>
    						<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">ICMS</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<? echo texto2bd($objBusca->cst_icms)?>
    						</td>
    					</tr>
    					<tr>
    						<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">PIS - COFINS</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<? echo texto2bd($objBusca->cst_pis_cofins)?>
    						</td>
    					</tr>
    					<tr>
    						<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">Aliquota ICMS</label></td>
    					</tr>
    					<tr>
    						<td class="tdbl tdbr sep ">
    							<? echo texto2bd($objBusca->aliq_icms)?>
    						</td>
    					</tr>
    					<?php
    				}
    				?>

    				<tr>
    					<td class="legenda tdbl tdbr "><label class="requerido" for="tipo">Opções de Sabor</label></td>
    				</tr>
    				<tr>
    					<td class="tdbl tdbr sep">   
    						<select class="requerido" name="tipo" id="tipo" >
    							<option value=""></option>
    							<option value="Doce" <? if ($objBusca->tipo=='Doce') echo 'selected'?>>Doce</option>
    							<option value="Salgado" <? if ($objBusca->tipo=='Salgado') echo 'selected'?>>Salgada</option>
    						</select>
    					</tr>



    					<tr>
    						<td class="tdbl tdbr"><input type="checkbox" name="sugestao"
    							<? if ($objBusca->sugestao) echo 'checked'?>>&nbsp;<label for="sugestao">Exibir como sugestão</label></td>
    						</tr>

    						<tr>
    							<td class="tdbl tdbr "><input type="checkbox" name="novidade"
    								<? if ($objBusca->novidade) echo 'checked'?>>&nbsp;<label for="novidade">Novidade</label></td>
    							</tr>

    							<tr>
    								<td class="tdbl tdbr"><input type="checkbox" name="pizza_fit" <? if ($objBusca->pizza_fit) echo 'checked'?>>&nbsp;<label for="pizza_fit">Fit</label></td>
    							</tr>

    							<tr>
    								<td class="tdbl tdbr sep"><input type="checkbox" name="venda_online" <? if ($objBusca->venda_online) echo 'checked'?>>&nbsp;<label for="venda_online">Vender Online</label></td>
    							</tr>

    							<tr>
    								<td class="legenda tdbl tdbr"><label for="foto_g">Imagem grande (*.png, *.jpg)</label></td>
    							</tr>

    							<?
    							if (is_file(UPLOAD_DIR . '/pizzas/' . $objBusca->foto_grande))
    							{
    								echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_g_figura" style="padding: 15px;">';

    								echo '<img height="600" src="' . UPLOAD_DIR . '/pizzas/' . $objBusca->foto_grande . '">';

    								echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem(' . $objBusca->$chave_primaria . ');"></td></tr>';
    							}
    							?>  
    							<tr>
    								<td class="sep tdbl tdbr sep"><input type="file" name="foto_g"
    									id="foto_g" size="40"></td>
    								</tr>


    								<tr>
    									<td class="legenda tdbl tdbr"><label for="foto_p">Imagem pequena(*.png, *.jpg)</label></td>
    								</tr>

    								<?
    								if (is_file(UPLOAD_DIR . '/pizzas/' . $objBusca->foto_pequena))
    								{
    									echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_p_figura" style="padding: 15px;">';

    									echo '<img src="' . UPLOAD_DIR . '/pizzas/' . $objBusca->foto_pequena . '">';

    									echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem_pequena(' . $objBusca->$chave_primaria . ');"></td></tr>';
    								}
    								?>
    								<tr>
    									<td class="sep tdbl tdbr sep"><input type="file" name="foto_p"
    										id="foto_p" size="40"></td>
    									</tr>    

    									<tr>
    										<td class="tdbl tdbr sep">
    										</td>
    									</tr>

    									<tr>
    										<td class="tdbl tdbr sep">

    											<table class="listaEdicao" cellpadding="0" cellspacing="0">
    												<thead>
    													<tr>
    														<td align="center" width="20">
    															<input type="checkbox" class="marcar_ingrediente" onclick="marcaTodosEstado('marcar_ingrediente', this.checked);">
    														</td>
    														<td align="center">
    															<label>Ingrediente</label>
    														</td>
    														<?

    														$conexao = conectabd();

    														$sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
    														$res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);

    														while ( $obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos) )
    														{
    															echo '<td align="center" width="120"><label>' . bd2texto($obj_buscar_tamanhos->tamanho) . ' (Qtde Consumo)</label></td>';
    														}

    														desconectabd($conexao);

    														?>

    													</tr>
    												</thead>
    												<tbody>

    													<?
    													$con = conectabd();

    													$SqlBuscaIngredientes = "SELECT * FROM ipi_ingredientes ORDER BY ingrediente";
    													$resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);

    													while ( $objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes) )
    													{
    														echo '<tr>';

    														if ($codigo > 0)
    														{
    															$objBuscaIngredienteTamanho = executaBuscaSimples(sprintf("SELECT * FROM ipi_ingredientes_ipi_pizzas WHERE cod_pizzas = %d AND cod_ingredientes = %d", $codigo, $objBuscaIngredientes->cod_ingredientes), $con);
    														}

    														if ($objBuscaIngredienteTamanho)
    														{
    															echo '<td align="center"><input type="checkbox" checked="checked" class="marcar_ingrediente" name="ingrediente[]" value="' . $objBuscaIngredientes->cod_ingredientes . '"></td>';
    														}
    														else
    														{
    															echo '<td align="center"><input type="checkbox" class="marcar_ingrediente" name="ingrediente[]" value="' . $objBuscaIngredientes->cod_ingredientes . '"></td>';
    														}

    														echo '<td><label>' . $objBuscaIngredientes->ingrediente . '<label></td>';

    														$sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
    														$res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);

    														while ( $obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos) )
    														{
    															$sql_buscar_quantidade_estoque = sprintf("SELECT * FROM ipi_ingredientes_estoque WHERE cod_ingredientes = '%s' AND cod_tamanhos = '%s' AND cod_pizzas = '%s'", $objBuscaIngredientes->cod_ingredientes, $obj_buscar_tamanhos->cod_tamanhos, $codigo);

    															$res_buscar_quantidade_estoque = mysql_query($sql_buscar_quantidade_estoque);
    															$obj_buscar_quantidade_estoque = mysql_fetch_object($res_buscar_quantidade_estoque);
                    // Dentro do text do ingrediente vai estar o cod_ingredientes, tamanho
    															echo '<td align="center"><input type="text" name="quantidade_'.$obj_buscar_tamanhos->cod_tamanhos.'_'.$objBuscaIngredientes->cod_ingredientes.'" size="7" maxlenght="10" value="' . str_replace(".", ",", $obj_buscar_quantidade_estoque->quantidade_estoque_ingrediente) . '" onkeypress="return formataMoeda4Casas(this,\'.\',\',\',event)"></td>';
                    //formataMoeda(this, \'.\', \',\', event)
    														}


    														echo '<input type="hidden" name="cod_ingredientes[]" value="' . $objBuscaIngredientes->cod_ingredientes . '">';

    														echo '</tr>';
    													}

    													desconectabd($con);
    													?>

    												</tbody>
    											</table>

    										</td>
    									</tr>

    									<tr>
    										<td colspan="2" align="center" class="tdbl tdbb tdbr"><input
    											name="botao_submit" class="botao" type="submit" value="Cadastrar"></td>
    										</tr>

    									</table>

    									<input type="hidden" name="acao" value="editar"> <input type="hidden"
    									name="<?
    									echo $chave_primaria?>" value="<?
    									echo $codigo?>"></form>
    								</div>
    								<!-- Tab Incluir --></div>

    								<?
    								rodape();
    								?>
