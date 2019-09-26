<?php

/**
 * ipi_bebida_conteudo.php: Cadastro Conteúdo da Bebida
 * 
 * Índice: cod_bebidas_ipi_conteudos
 * Tabela: ipi_bebidas_ipi_conteudos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relacionar Bebidas com Conteúdos');

$acao = validaVarPost('acao');

$tabela = 'ipi_bebidas_ipi_conteudos';
$chave_primaria = 'cod_bebidas_ipi_conteudos';

switch($acao) {
	case 'excluir':
	$excluir = validaVarPost('excluir');
	$indicesSql = implode(',', $excluir);

	$con = conectabd();

	$SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";

	if (mysql_query($SqlDel))
		mensagemOk('Os registros selecionados foram excluídos com sucesso!');
	else
		mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');

	desconectabd($con);
	break;
	case 'editar':
	$codigo  = validaVarPost($chave_primaria);
	$cod_bebidas = validaVarPost('cod_bebidas');
	$cod_conteudos = validaVarPost('cod_conteudos');
	$situacao = validaVarPost('situacao');
	$qtd_embalagem = validaVarPost('qtd_embalagem');    
	$unidade_padrao = validaVarPost('unidade_padrao');
	$codigo_cliente_bebida = trim(validar_var_post('codigo_cliente_bebida'));


	$ncm = validaVarPost('ncm');
	$cfop = validaVarPost('cfop');
	$cest = validaVarPost('cest');
	$cst_icms = validaVarPost('cst_icms');
	$cst_icms_ecf = validaVarPost('cst_icms_ecf');
	$cst_pis_cofins = validaVarPost('cst_pis_cofins');
	$aliq_icms = validaVarPost('aliq_icms');
	

	$venda_net = validaVarPost('venda_net');

	$imagem_g = validaVarFiles('foto_g');
	$imagem_p = validaVarFiles('foto_p');

	if (!$situacao)
	{
		$situacao = 0;
	}

	$con = conectabd();

	if($codigo <= 0) 
	{

		if ($codigo_cliente_bebida != "")
		{
			$sql_codigo_pizza = "SELECT p.pizza FROM ipi_pizzas p WHERE p.codigo_cliente_pizza = '".$codigo_cliente_bebida."'";
			$res_codigo_pizza = mysql_query($sql_codigo_pizza);
			$num_codigo_pizza = mysql_num_rows($res_codigo_pizza);
		}
		else
		{
			$num_codigo_pizza=0;            
		}

		if ($num_codigo_pizza ==0)
		{

			if ($codigo_cliente_bebida != "")
			{
				$sql_codigo = "SELECT bc.codigo_cliente_bebida, b.bebida, c.conteudo,bc.ncm,bc.cest,bc.cst_icms,bc.cst_icms_ecf,bc.cst_pis_cofins,bc.aliq_icms,bc.cfop FROM $tabela bc LEFT JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) LEFT JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) LEFT JOIN ipi_tipo_bebida tb ON (b.cod_tipo_bebida= tb.cod_tipo_bebida) WHERE bc.codigo_cliente_bebida = '".$codigo_cliente_bebida."'";
				$res_codigo = mysql_query($sql_codigo);
				$num_codigo = mysql_num_rows($res_codigo);
			}
			else
			{
				$num_codigo=0;            
			}

			if ($num_codigo ==0)
			{
				$SqlEdicao = sprintf("INSERT INTO $tabela (codigo_cliente_bebida, cod_bebidas, cod_conteudos, cod_unidade_padrao, situacao,ncm,cest,cst_icms,cst_icms_ecf,cst_pis_cofins,aliq_icms,cfop) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s')", 
					$codigo_cliente_bebida, $cod_bebidas, $cod_conteudos, $unidade_padrao, $situacao,$ncm,$cest,$cst_icms,$cst_icms_ecf,$cst_pis_cofins,$aliq_icms,$cfop);
				
				#echo "<pre>".$SqlEdicao."</pre>";
				#exit;
				$res_edicao = mysql_query($SqlEdicao);
          //echo $SqlEdicao;
				$codigo = mysql_insert_id();
				$resEdicaoTamanhoIngrediente = true;

            // Inserindo as Imagens grandes
				$resEdicaoImagem = true;
				if(count($imagem_g['name']) > 0) {     
					if(trim($imagem_g['name']) != '') {
						$arq_info = pathinfo($imagem_g['name']);
						$arq_ext = $arq_info['extension'];
						if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_g["type"])) {
							mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
						}
						else {                
							$resEdicaoImagem &= move_uploaded_file($imagem_g['tmp_name'], UPLOAD_DIR."/bebidas/${codigo}_beb_g.${arq_ext}");

							$SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_grande = '%s' WHERE $chave_primaria = $codigo", 
								texto2bd("${codigo}_beb_g.${arq_ext}"));

							$resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
						}
					}          
				}

            // Inserindo as Imagens pequenas
				$resEdicaoImagem = true;
				if(count($imagem_p['name']) > 0) {     
					if(trim($imagem_p['name']) != '') {
						$arq_info = pathinfo($imagem_p['name']);
						$arq_ext = $arq_info['extension'];
						if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_p["type"])) {
							mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
						}
						else {                
							$resEdicaoImagem &= move_uploaded_file($imagem_p['tmp_name'], UPLOAD_DIR."/bebidas/${codigo}_beb_p.${arq_ext}");

							$SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_pequena = '%s' WHERE $chave_primaria = $codigo", 
								texto2bd("${codigo}_beb_p.${arq_ext}"));

							$resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
						}
					}          
				}

				if($res_edicao && $resEdicaoTamanhoIngrediente && $resEdicaoImagem)
					mensagemOk('Registro adicionado com êxito!');
				else
					mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
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
			mensagemErro('Erro ao adicionar o registro', 'O código: <strong>'.$codigo_cliente_bebida.'</strong>, já está sendo usado no produto: <strong>'.$obj_codigo_pizza->pizza.'</strong>');
		}
	}
	else 
	{

		if ($codigo_cliente_bebida != "")
		{
			$sql_codigo_pizza = "SELECT p.pizza FROM ipi_pizzas p WHERE p.codigo_cliente_pizza = '".$codigo_cliente_bebida."'";
			$res_codigo_pizza = mysql_query($sql_codigo_pizza);
			$num_codigo_pizza = mysql_num_rows($res_codigo_pizza);
		}
		else
		{
			$num_codigo_pizza=0;            
		}

		if ($num_codigo_pizza ==0)
		{
			if ($codigo_cliente_bebida != "")
			{        
				$sql_codigo = "SELECT bc.codigo_cliente_bebida, b.bebida, c.conteudo,bc.ncm,bc.cest,bc.cst_icms,bc.cst_icms_ecf,bc.cst_pis_cofins,bc.aliq_icms,bc.cfop FROM $tabela bc LEFT JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) LEFT JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) LEFT JOIN ipi_tipo_bebida tb ON (b.cod_tipo_bebida= tb.cod_tipo_bebida) WHERE bc.codigo_cliente_bebida = '".$codigo_cliente_bebida."' AND bc.cod_bebidas_ipi_conteudos <> '".$codigo."'";
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


				$SqlEdicao = sprintf("UPDATE $tabela SET codigo_cliente_bebida = '%s', cod_bebidas = %d, cod_conteudos = %d, cod_unidade_padrao = %d, situacao = '%s',ncm = '%s',cest = '%s',cst_icms = '%s',cst_icms_ecf = '%s',cst_pis_cofins = '%s',aliq_icms = '%s',cfop = '%s'  WHERE $chave_primaria = $codigo", $codigo_cliente_bebida, $cod_bebidas, $cod_conteudos, $unidade_padrao, $situacao,$ncm,$cest,$cst_icms,$cst_icms_ecf,$cst_pis_cofins,$aliq_icms,$cfop);
          		//echo $SqlEdicao ;
          		//echo "<pre>".$SqlEdicao."</pre>";
				//exit;
				$res_edicao = mysql_query($SqlEdicao);
				$resEdicaoTamanhoIngrediente = true;

            //print_r($imagem_g);
            // Inserindo as Imagens grandes
				$resEdicaoImagem = true;
				if(count($imagem_g['name']) > 0) {     
					if(trim($imagem_g['name']) != '') {
						$arq_info = pathinfo($imagem_g['name']);
						$arq_ext = $arq_info['extension'];
						if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_g["type"])) {
							mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
						}
						else {                
							$resEdicaoImagem &= move_uploaded_file($imagem_g['tmp_name'], UPLOAD_DIR."/bebidas/${codigo}_beb_g.${arq_ext}");

							$SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_grande = '%s' WHERE $chave_primaria = $codigo", 
								texto2bd("${codigo}_beb_g.${arq_ext}"));

							$resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
						}
					}          
				}

            // Inserindo as Imagens pequenas
				$resEdicaoImagem = true;
				if(count($imagem_p['name']) > 0) 
				{
					if(trim($imagem_p['name']) != '') 
					{
						$arq_info = pathinfo($imagem_p['name']);
						$arq_ext = $arq_info['extension'];
						if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_p["type"])) 
						{
							mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
						}
						else 
						{
							$resEdicaoImagem &= move_uploaded_file($imagem_p['tmp_name'], UPLOAD_DIR."/bebidas/${codigo}_beb_p.${arq_ext}");

							$SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_pequena = '%s' WHERE $chave_primaria = $codigo", texto2bd("${codigo}_beb_p.${arq_ext}"));
							$resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
						}
					}          
				}

				if($res_edicao && $resEdicaoTamanhoIngrediente && $resEdicaoImagem)
					mensagemOk('Registro adicionado com êxito!');
				else
					mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');

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
			mensagemErro('Erro ao adicionar o registro', 'O código: <strong>'.$codigo_cliente_bebida.'</strong>, já está sendo usado no produto: <strong>'.$obj_codigo_pizza->pizza.'</strong>');
		}

    //echo "<br>res_edicao: ".$res_edicao;
    //echo "<br>resEdicaoTamanhoIngrediente: ".$resEdicaoTamanhoIngrediente; 
    //echo "<br>resEdicaoImagem: ".$resEdicaoImagem;
    //echo "<br>x: ".$SqlEdicao;
	}

	desconectabd($con);
	break;
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

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
			'action': '<? echo $_SERVER['PHP_SELF'] ?>',
			'method': 'post'
		});

		var input = new Element('input', {
			'type': 'hidden',
			'name': '<? echo $chave_primaria ?>',
			'value': cod
		});

		input.inject(form);
		$(document.body).adopt(form);

		form.submit();
	}

	function excluirImagem(cod) {
		if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
			var acao = 'excluir_imagem';
			var url = 'acao=' + acao + '&cod_bebidas_ipi_conteudos=' + cod;

			new Request.JSON({url: 'ipi_bebida_conteudo_ajax.php', onComplete: function(retorno) {
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

	function excluirImagem_pequena(cod) {
		if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
			var acao = 'excluir_imagem_pequena';
			var url = 'acao=' + acao + '&cod_bebidas_ipi_conteudos=' + cod;

			new Request.JSON({url: 'ipi_bebida_conteudo_ajax.php', onComplete: function(retorno) {
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

	function atalhos(event)
	{

		if (event.key == '+' && event.shift) 
		{
			tabs.irpara(1);
			$("cod_bebidas").focus();
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
		if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) 
		{
			<? if ($acao == '') echo 'tabs.irpara(1);'; ?>
			document.frmIncluir.botao_submit.value = 'Alterar';
		}
		else 
		{
			document.frmIncluir.botao_submit.value = 'Cadastrar';
		}

		tabs.addEvent('change', function(indice){
			if(indice == 1) {
				document.frmIncluir.<? echo $chave_primaria ?>.value = '';
				document.frmIncluir.cod_bebidas.value = '';
				document.frmIncluir.cod_conteudos.value = '';
				document.frmIncluir.situacao.value = '';
				document.frmIncluir.unidade_padrao.value = '';
				document.frmIncluir.codigo_cliente_bebida.value = '';
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
		<table><tr>

			<!-- Conteúdo -->
			<td class="conteudo">

				<form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">

					<table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
						<tr>
							<td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
						</tr>
					</table>

					<table class="listaEdicao" cellpadding="0" cellspacing="0">
						<thead>
							<tr>
								<td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
								<td align="center">Código Bebida</td>
								<td align="center">Tipo de Bebida</td>
								<td align="center">Bebida</td>
								<td align="center">Conteúdo</td>
								<td align="center">Situação</td>
							</tr>
						</thead>
						<tbody>

							<?

							$con = conectabd();

							$SqlBuscaRegistros = "SELECT bc.cod_bebidas_ipi_conteudos, bc.codigo_cliente_bebida, b.bebida, c.conteudo, bc.situacao, tb.tipo_bebida,bc.ncm,bc.cest,bc.cst_icms,bc.cst_icms_ecf,bc.cst_pis_cofins,bc.aliq_icms,bc.cfop FROM ipi_bebidas_ipi_conteudos bc LEFT JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) LEFT JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) LEFT JOIN ipi_tipo_bebida tb ON (b.cod_tipo_bebida= tb.cod_tipo_bebida) ORDER BY bc.codigo_cliente_bebida ASC, tb.tipo_bebida, b.bebida, c.conteudo";
							$resBuscaRegistros = mysql_query($SqlBuscaRegistros);

							while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
								echo '<tr>';

								echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
								echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.$objBuscaRegistros->codigo_cliente_bebida.'</a></td>';
								echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.$objBuscaRegistros->tipo_bebida.'</a></td>';
								echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.$objBuscaRegistros->bebida.'</a></td>';
								echo '<td align="center">'.$objBuscaRegistros->conteudo.'</td>';
								echo '<td align="center">'.$objBuscaRegistros->situacao.'</td>';


								echo '</tr>';
							}

							desconectabd($con);

							?>

						</tbody>
					</table>

					<input type="hidden" name="acao" value="excluir">
				</form>

			</td>
			<!-- Conteúdo -->

			<!-- Barra Lateral -->
			<td class="lateral">
				<div class="blocoNavegacao">
					<ul>
						<li><a href="ipi_bebida_tipo.php">Tipo de Bebidas</a></li>
						<li><a href="ipi_bebida.php">Cadastro de Bebidas</a></li>
						<li><a href="ipi_conteudo.php">Tamanhos de Bebidas</a></li>
						<li><a href="ipi_bebida_conteudo.php">Vincular Tamanho a Bebida</a></li>
						<li><a href="ipi_preco_bebida.php">Preços de Bebidas</a></li>
					</ul>
				</div>
			</td>
			<!-- Barra Lateral -->

		</tr></table>
	</div>
	<!-- Tab Editar -->



	<!-- Tab Incluir -->
	<div class="painelTab">
		<? 
		$codigo = validaVarPost($chave_primaria, '/[0-9]+/');

		if($codigo > 0) {
			$objBusca = executaBuscaSimples("SELECT *,t.ncm,t.cest,t.cst_icms,t.cst_icms_ecf,t.cst_pis_cofins,t.aliq_icms FROM $tabela t LEFT JOIN ipi_bebidas b ON (b.cod_bebidas = t.cod_bebidas) WHERE t.$chave_primaria = $codigo");
		} 
		?>

		<form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">

			<table align="center" class="caixa" cellpadding="0" cellspacing="0">

				<tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_bebidas">Bebidas</label></td></tr>
				<tr><td class="tdbl tdbr sep">
					<select name="cod_bebidas" id="cod_bebidas" style="width: 250px;">
						<option value=""></option>
						<?
						$con = conectabd();

						$SqlTiposBebidas = "SELECT * FROM ipi_tipo_bebida tb WHERE situacao = 'ATIVO' ORDER BY tb.tipo_bebida";
						$resTiposBebidas = mysql_query($SqlTiposBebidas);

						while($objTiposBebidas = mysql_fetch_object($resTiposBebidas)) 
						{
							$SqlBuscaBebidas = "SELECT * FROM ipi_bebidas b WHERE b.cod_tipo_bebida='".$objTiposBebidas->cod_tipo_bebida."' ORDER BY b.bebida";
							$resBuscaBebidas = mysql_query($SqlBuscaBebidas);
							$numBuscaBebidas = mysql_num_rows($resBuscaBebidas);
							if ($numBuscaBebidas >0)
							{
								echo '<optgroup label="'.$objTiposBebidas->tipo_bebida.'">';
								while($objBuscaBebidas = mysql_fetch_object($resBuscaBebidas)) 
								{
									echo '<option value="'.$objBuscaBebidas->cod_bebidas.'" ';
									if($objBuscaBebidas->cod_bebidas == $objBusca->cod_bebidas)
										echo 'selected';
									echo '>'.bd2texto($objBuscaBebidas->bebida).'</option>';
								}
								echo '</optgroup>';
							}
						}

						desconectabd($con);
						?>
					</select>
				</td></tr>

				<tr><td class="legenda tdbl tdbr"><label class="requerido" for="cod_conteudos">Conteúdos</label></td></tr>
				<tr><td class="tdbl tdbr sep">
					<select name="cod_conteudos" id="cod_conteudos" style="width: 125px;">
						<option value=""></option>
						<?
						$con = conectabd();

						$SqlBuscaConteudos = "SELECT * FROM ipi_conteudos ORDER BY conteudo";
						$resBuscaConteudos = mysql_query($SqlBuscaConteudos);

						while($objBuscaConteudos = mysql_fetch_object($resBuscaConteudos)) {
							echo '<option value="'.$objBuscaConteudos->cod_conteudos.'" ';

							if($objBuscaConteudos->cod_conteudos == $objBusca->cod_conteudos)
								echo 'selected';

							echo '>'.bd2texto($objBuscaConteudos->conteudo).'</option>';
						}

						desconectabd($con);
						?>
					</select>
				</td>
			</tr>

			<tr>
				<td class="legenda tdbl tdbr"><label for="codigo_cliente_bebida">Código Cliente da Bebida</label></td>
			</tr>
			<tr>
				<td class="tdbl tdbr sep">
					<input type="text" name="codigo_cliente_bebida" id="codigo_cliente_bebida" maxlength="30" size="10" value="<? echo texto2bd($objBusca->codigo_cliente_bebida) ?>">
				</td>
			</tr>






			<?php
	#SE FOR DIRETOR ADM OU CONTADOR
			$user = $_SESSION['usuario']['perfil'];
			if($user == 1 or $user == 2 or $user == 15){
				?>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="ncm">NCM</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<input type="text" required name="ncm" id="ncm" maxlength="30" size="10" value="<? echo texto2bd($objBusca->ncm) ?>">
					</td>
				</tr>
				<tr>
					<td class="legenda tdbl tdbr"><label for="cest">CEST</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<input type="text" name="cest" id="cest" maxlength="30" size="10" value="<? echo texto2bd($objBusca->cest) ?>">
					</td>
				</tr>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="cest_icms">CEST ICMS</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<input type="text" required name="cst_icms" id="cst_icms" maxlength="30" size="10" value="<? echo texto2bd($objBusca->cst_icms) ?>">
					</td>
				</tr>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="cst_pis_cofins">PIS COFINS</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<input type="text" required name="cst_pis_cofins" id="cst_pis_cofins" maxlength="30" size="10" value="<? echo texto2bd($objBusca->cst_pis_cofins) ?>">
					</td>
				</tr>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="aliq_icms">ALIQ ICMS</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<input type="text" required name="aliq_icms" id="aliq_icms" maxlength="30" size="10" value="<? echo texto2bd($objBusca->aliq_icms) ?>">
					</td>
				</tr>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="cfop">CFOP</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<input type="text" required name="cfop" id="cfop" maxlength="30" size="10" value="<? echo texto2bd($objBusca->cfop) ?>">
					</td>
				</tr>
				<?php
				#FIM SÓ DIRETOR ADM E CONTADOR
			}else{
				?>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="ncm">NCM</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<? echo texto2bd($objBusca->ncm) ?>
					</td>
				</tr>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="cest">CEST</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<? echo texto2bd($objBusca->cest) ?>
					</td>
				</tr>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="cest_icms">CEST ICMS</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<? echo texto2bd($objBusca->cst_icms) ?>
					</td>
				</tr>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="cst_pis_cofins">PIS COFINS</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<? echo texto2bd($objBusca->cst_pis_cofins) ?>
					</td>
				</tr>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="aliq_icms">ALIQ ICMS</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<? echo texto2bd($objBusca->aliq_icms) ?>
					</td>
				</tr>
				<tr>
					<td class="legenda tdbl tdbr"><label class="requerido" for="cfop">CFOP</label></td>
				</tr>
				<tr>
					<td class="tdbl tdbr sep">
						<input type="text" required name="cfop" id="cfop" maxlength="30" size="10" value="<? echo texto2bd($objBusca->cfop) ?>">
					</td>
				</tr>
				<?php
				#FIM SÓ DIRETOR ADM E CONTADOR
			}
			?>






			<tr>
				<td class="legenda tdbl  tdbr">
					<label class="requerido" for="situacao">Situação</label>
				</td>
			</tr>
			<tr>
				<td class="tdbl tdbr sep">
					<select class="requerido" name="situacao" id="situacao">
						<?
						$con = conectabd();

						$SqlBuscaConteudos = "SELECT cod FROM ipi_conteudos ORDER BY conteudo";
						$resBuscaConteudos = mysql_query($SqlBuscaConteudos);

						while($objBuscaConteudos = mysql_fetch_object($resBuscaConteudos)) {
							echo '<option value="'.$objBuscaConteudos->cod_conteudos.'" ';

							if($objBuscaConteudos->cod_conteudos == $objBusca->cod_conteudos)
								echo 'selected';

							echo '>'.bd2texto($objBuscaConteudos->conteudo).'</option>';
						}

						desconectabd($con);
						?>
						<option value=""></option>
						<option value="ATIVO" <? if($objBusca->situacao == 'ATIVO') echo 'selected'; ?>> Ativo </option>
						<option value="INATIVO" <? if($objBusca->situacao == 'INATIVO') echo 'selected'; ?>> Inativo </option>
					</select>
				</td>
			</tr>



			<tr><td class="legenda tdbl tdbr"><label for="unidade_padrao">Unidade padrão</label></td></tr>
			<tr><td class="tdbl tdbr sep">

				<select name="unidade_padrao" id="unidade_padrao">
					<option></option>
					<?
					$con = conectabd();
					$sql_unidades = 'SELECT * FROM ipi_unidade_padrao ORDER BY unidade';
					$res_unidades = mysql_query($sql_unidades);
					while ($obj_unidades = mysql_fetch_object($res_unidades)) 
					{
						echo '<option value="'.$obj_unidades->cod_unidade_padrao.'" ';
						if($obj_unidades->cod_unidade_padrao == $objBusca->cod_unidade_padrao)
							echo 'SELECTED';
						echo '>'.$obj_unidades->abreviatura.'</option>';
					}
					desconectabd($con);
					?>
				</select>
			</td></tr> 

			<? $objBusca = executaBuscaSimples("SELECT *,t.ncm,bc.cest,t.cst_icms,t.cst_icms_ecf,t.cst_pis_cofins,t.aliq_icms FROM $tabela t LEFT JOIN ipi_bebidas b ON (b.cod_bebidas = t.cod_bebidas) WHERE t.$chave_primaria = $codigo");
			?>
			<tr>
				<td class="legenda tdbl tdbr"><label for="foto_g">Imagem grande (*.png, *.jpg)</label></td>
			</tr>

			<?
			if (is_file(UPLOAD_DIR . '/bebidas/' . $objBusca->foto_grande))
			{
				echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_g_figura" style="padding: 15px;">';

				echo '<img height="300" src="' . UPLOAD_DIR . '/bebidas/' . $objBusca->foto_grande . '">';

				echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem(' . $objBusca->$chave_primaria . ');"></td></tr>';
			}
			?>

			<tr>
				<td class="sep tdbl tdbr sep"><input type="file" name="foto_g"
					id="foto_g" size="40"></td>
				</tr>

				<tr>
					<td class="legenda tdbl tdbr"><label for="foto_p">Imagem pequena (*.png, *.jpg)</label></td>
				</tr>

				<?
				if (is_file(UPLOAD_DIR . '/bebidas/' . $objBusca->foto_pequena))
				{
					echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_p_figura" style="padding: 15px;">';

					echo '<img src="' . UPLOAD_DIR . '/bebidas/' . $objBusca->foto_pequena . '">';

					echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem_pequena(' . $objBusca->$chave_primaria . ');"></td></tr>';
				}
				?>

				<tr>
					<td class="sep tdbl tdbr sep"><input type="file" name="foto_p"
						id="foto_p" size="40"></td>
					</tr>




					<tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>



				</table>

				<input type="hidden" name="acao" value="editar">
				<input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">

			</form>
		</div>
		<!-- Tab Incluir -->

	</div>

	<? rodape(); ?>
