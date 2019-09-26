<?php

/**
 * ipi_rel_historico_pedidos.php: Histórico de Pedidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/pedido.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title><? echo NOME_SITE; ?> - Expedição de Pedidos</title>

	<link href="../lib/css/principal.css" media="screen" type="text/css" rel="stylesheet">
	<link type="text/css" rel="stylesheet" href="../../css/autocompleter.css">

	<script type="text/javascript" src="../lib/js/mascara.js"></script>
	<script type="text/javascript" src="../lib/js/mootools-1.2-core.js"></script>
	<script type="text/javascript" src="../lib/js/tabs.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs.css" />

	<script type="text/javascript" src="../../js/autocompleter.js"></script>
	<script type="text/javascript" src="../../js/autocompleter.request.js"></script>
	<script type="text/javascript" src="../../js/observer.js"></script>

	<link  href="../lib/js/moodialog/css/MooDialog.css" rel="stylesheet" type="text/css" media="screen" />
	<script src="../lib/js/moodialog/MooDialog.js" type="text/javascript"></script>
	<script src="../lib/js/moodialog/Overlay.js" type="text/javascript"></script>
	<script src="../lib/js/moodialog/MooDialog.Fx.js" type="text/javascript"></script>
	<script src="../lib/js/moodialog/MooDialog.Alert.js" type="text/javascript"></script>
	<script src="../lib/js/moodialog/MooDialog.Request.js" type="text/javascript"></script>
	<script src="../lib/js/moodialog/MooDialog.Confirm.js" type="text/javascript"></script>
	<script src="../lib/js/moodialog/MooDialog.Prompt.js" type="text/javascript"></script>
	<script src="../lib/js/moodialog/MooDialog.Error.js" type="text/javascript"></script>
	<script type="text/javascript" src="../lib/js/form.js"></script>
	<style>

		body 
		{
			background: ''!important;
		}

	</style>
</head>

<body style='background:none !important'>

	<?

	/*cabecalho('Expedição de Pedidos');*/

	$acao = validaVarPost('acao');
/*echo"<html><body><pre>";
print_r($_POST);
echo "</pre></body></html>";*/
$codigo_usuario = $_SESSION['usuario']['codigo'];

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 135;


switch($acao) {
	case 'despachar':
	$codigo = validaVarPost($chave_primaria);
	$indicesSql = implode(',', $codigo);
	$cod_entregadores = validaVarPost("cod_entregador");

	$con = conectabd();

    //$SqlUpdate = "UPDATE $tabela SET situacao = 'ENVIADO' , cod_entregadores = '".$cod_entregadores[0]."' , cod_usuarios_envio = '".$codigo_usuario."' , data_hora_envio = NOW()  WHERE $chave_primaria IN ($indicesSql) AND situacao = 'IMPRESSO'";
		//echo $SqlUpdate;
    //if (mysql_query($SqlUpdate))
	{
		for($i = 0;$i<count($codigo);$i++)
		{
			$arr_infos = explode("_",$codigo[$i]);
			$cod_pedidos = $arr_infos[0];
			$cod_pizzarias = $arr_infos[1];
			require("../../pub_req_fuso_horario1.php");
			$sqlPegaPedido = "SELECT ifood_polling FROM ipi_pedidos WHERE cod_pedidos='".$cod_pedidos."'";
			$sqlQueryPegaPedido = mysql_query($sqlPegaPedido);
			$sqlQueryPegaPedido = mysql_fetch_object($sqlQueryPegaPedido);
			if(!empty($sqlQueryPegaPedido->ifood_polling)){
				$codPedidosIfood[] = $sqlQueryPegaPedido->ifood_polling;
			}
			$SqlUpdate = "UPDATE $tabela SET situacao = 'ENVIADO' , cod_entregadores = '".$cod_entregadores[0]."' , cod_usuarios_envio = '".$codigo_usuario."' , data_hora_envio = NOW() WHERE $chave_primaria IN (".$cod_pedidos.") AND situacao = 'IMPRESSO'";
				//echo $SqlUpdate;
			/*
    		//SCRIPT PARA NOTA FISCAL ELETRONICA
			$data = 'acao=gerar_nota_fiscal&cod_pedidos='.$cod_pedidos.'&chave=165117047d56ce2487aa718bd8d6c5b7';                   
			$html = "<script>\n";
			$html .= "let url = 'https://formulasys.encontresuafranquia.com.br/index.php/?".$data."';\n";
			$html .= "var enviaPedido = new XMLHttpRequest();\n";
			$html .= "enviaPedido.open('GET',url,true);\n";
			$html .= "enviaPedido.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');\n";
			$html .= "enviaPedido.send();\n";
			$html .= "</script>";
			echo $html;
			*/
			//FIM NOTA FISCAL ELETRONICA
			$resUpdate = mysql_query($SqlUpdate);
			if (defined('IMPRIMIR_VIA_DESPACHO'))
			{
				if (IMPRIMIR_VIA_DESPACHO == "S")
				{
					$sql_inserir_relatorio = sprintf("INSERT into ipi_impressao_relatorio (cod_entregadores,cod_pedidos,cod_usuarios,cod_pizzarias,relatorio,data_hora_inicial,situacao) values( %d,%d,%d,%d,'%s',NOW(),'NOVO')",$cod_entregadores[0],$cod_pedidos,$codigo_usuario,$_SESSION['usuario']['cod_pizzarias'][0],"DESPACHO");
					$res_inserir_relatorio = mysql_query($sql_inserir_relatorio);
				}
			}
        // else
        // {
        //   $sql_inserir_relatorio = sprintf("INSERT into ipi_impressao_relatorio (cod_entregadores,cod_pedidos,cod_usuarios,cod_pizzarias,relatorio,data_hora_inicial,situacao) values( %d,%d,%d,%d,'%s',NOW(),'NOVO')",$cod_entregadores[0],$cod_pedidos,$codigo_usuario,$_SESSION['usuario']['cod_pizzarias'][0],"DESPACHO");
        //   $res_inserir_relatorio = mysql_query($sql_inserir_relatorio);
        // }


				//echo "<br>Ins".$sql_inserir_relatorio;
		}
		mensagemOk('O(s) pedido(s) foram despachados com sucesso!');
	}
    //else
//      mensagemErro('Erro ao despachar o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para o despacho.');

	desconectabd($con);
	break;
	case 'salvar_lista':
	$cod_entregadores = validaVarPost("edit_cod_entregador");
	/*echo "asdadsadsdaqui";*/
  	//die();
/*  	if(!isset($_SESSION['ipi_despacho']['entregadores'][$objBuscaEntregadores->cod_entregadores]))
		{
			$_SESSION['ipi_despacho']['entregadores'][$objBuscaEntregadores->cod_entregadores] = $objBuscaEntregadores->nome;
		}*/

		unset($_SESSION['ipi_despacho']['entregadores']);
		for($i = 0;$i<count($cod_entregadores);$i++)
		{
			$_SESSION['ipi_despacho']['entregadores'][$cod_entregadores[$i]] = validaVarPost("nome_entregador_".$cod_entregadores[$i]);
		}

		/*mensagemOk('Lista atualizada com sucesso!');*/


		break;
  /*case 'cancelar':
    $codigo = validaVarPost($chave_primaria);
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $sql_verificar = "SELECT * FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao='BAIXADO'";
    $res_verificar = mysql_query($sql_verificar);
    $num_verificar = mysql_num_rows($res_verificar);
    if ($num_verificar>0)
    {
      echo "<div style='color: #FF0000; font-weight: bold; font-size: 14px; text-align: center;'>";
      echo "Os pedidos não podem ser cancelados, pois já foram BAIXADOS: ";
      while ($obj_verificar = mysql_fetch_object($res_verificar))
      {
        echo $obj_verificar->$chave_primaria.", ";
      }
      echo "</div><br /><br />";
    }

    $SqlUpdate = "UPDATE $tabela SET situacao = 'CANCELADO', data_hora_baixa = NOW(), data_hora_cancelamento = NOW(), cod_usuarios_cancelamento='".$_SESSION['usuario']['codigo']."' WHERE $chave_primaria IN ($indicesSql) AND situacao <> 'BAIXADO'";
    $SqlEstornoFidelidade = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, data_validade, pontos) (SELECT cod_clientes, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), pontos_fidelidade_total FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao <> 'BAIXADO')";
    
    if (mysql_query($SqlUpdate) && mysql_query($SqlEstornoFidelidade))
      mensagemOk('O pedido foi CANCELADO com sucesso!');
    else
      mensagemErro('Erro ao CANCELAR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
    break;*/
}

?>


<? if($acao != 'detalhes'): ?>

	<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
	<style>

		#frmBaixa td 
		{

			font-size :15pt;

		}

	</style>
	<script language="javascript" src="../lib/js/calendario.js"></script>

	<script>
		var timer_atualizador;
		function verificaCheckbox(form) {
			var cInput = 0;
			var cRadio = 0;
			var checkBox = form.getElementsByTagName('input');

			for (var i = 0; i < checkBox.length; i++) {
				if((checkBox[i].className.match('situacao')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
					cInput++; 
				}
			}

			if(cInput > 0) {
				var entregador = document.getElementsByName('cod_entregador[]');

				for (var i = 0; i < entregador.length; i++) 
				{
					if(((entregador[i].type == 'radio')) && (checkBox[i].disabled != true) && ((entregador[i].checked == true))) { 
						cRadio++; 
					}
				}

				if(cRadio > 0)
				{
				//if (confirm('Deseja expedir o(s) pedido(s) selecionado(s)?')) {
					return true;
				//}
				//else {
				//	return false;
				//}
			}
			else
			{
				alert('Por favor, selecione o entregador para fazer a expedição.');

				return false;
			}
		}
		else {
			alert('Por favor, selecione os itens que deseja expedir.');

			return false;
		}
		
	}

	/*EMITE NOTA*/

	/*FIM EMITE NOTA*/

	function despachar() {
		if(verificaCheckbox(document.frmBaixa)) {
			document.frmBaixa.acao.value = "despachar";
			<?php 
			$notas_ativas = nota_ativa();
			foreach($notas_ativas as $i=>$v){
				if($v['nota_ativa'] == 3){
					if(isset($codPedidosIfood)){
						$codPedidosIfood = implode(',', $codPedidosIfood);
					}else{
						$codPedidosIfood = '';
					}
					?>
					
					var id = '';
					var checkboxes = frmBaixa.querySelectorAll('input[type="checkbox"]:checked');
					checkboxes.forEach(function(v,i){
						id += "&cod_pedidos[]="+v.getAttribute('id').split('_').pop();
					});
					id = id.substring(1,id.length);
					let url = "<?php echo CAMINHO_DESPACHO_PEDIDOS_NOTA_FISCAL; ?>"+id; 
					let win = window.open(url, '_blank');
					win.focus();
					<?php 
				} 
			}
			?>
			document.frmBaixa.submit();
		}
	}

	function salvar_entregadores() {
		var entregador = document.getElementsByName('edit_cod_entregador[]');
		var cBox = 0;
		var checkBox = document.frmBaixa.getElementsByTagName('input');

		for (var i = 0; i < entregador.length; i++) 
		{
			if(((entregador[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((entregador[i].checked == true))) { 
				cBox++; 
			}
		}

		if(cBox > 0)
		{
			//if (confirm('Deseja expedir o(s) pedido(s) selecionado(s)?')) {

				document.frmBaixa.acao.value = "salvar_lista";
				document.frmBaixa.submit();
				/*alert(document.frmBaixa.acao.value);*/
			//}
			//else {
			//	return false;
			//}
		}
		else
		{
			alert('Por favor, selecione o entregador para fazer a expedição.');

			return false;
		}

	}

	function editar_entregadores() {
		$clear(timer_atualizador);
		var url = "acao=editar_entregadores";
		new Request.HTML(
		{
			url: 'ipi_despacho_pedidos_ajax.php',
			update: "relacao_despacho",
			method:'post'
		}).send(url);
		$('botao_despachar').setStyle('display' , 'none' );
		$('botao_salvar').setStyle('display' , 'block' );
	}

	function checkar(id) {
		if(id) 
		{
			$(id).setProperty('checked', !$(id).get("checked"));
		}
	}

	function checkar_edit(id,td_pai) {
		if(id) 
		{
			$(id).setProperty('checked', !$(id).get("checked"));
			var c = $(id).get("checked");

			if(c==true)
			{
				td_pai.setStyle('backgroundColor' , 'lightgreen' );
				/*td.style.backgroundColor = '';*/
			}
			else
			{
				td_pai.setStyle('backgroundColor' , '' );
				/*td.style.backgroundColor = '';*/
			}
		}
	}

	function tabela_pedidos()
	{
		var url = "";
		new Request.HTML(
		{
			url: 'ipi_despacho_pedidos_ajax.php',
			update: "relacao_despacho",
			method:'post'
		}).send(url);
	}

	window.addEvent("domready",function(){
		tabela_pedidos();
		timer_atualizador = tabela_pedidos.periodical(30000);
	});
//json pra saber qtd e só se for != adicionar
</script>

<?
$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 

$pedido = (validaVarPost('pedido', '/[0-9]+/')) ? (int) validaVarPost('pedido', '/[0-9]+/') : (validaVarGet('p', '/[0-9]+/') ? (int) validaVarGet('p', '/[0-9]+/') : '');

$data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('d/m/Y'));
$data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('d/m/Y'));
$cod_pizzarias = validaVarPost('cod_pizzarias');
?>




<?
$con = conectabd();

desconectabd($con);
?>


<form name="frmBaixa"  id="frmBaixa" method="post">
	<div id="relacao_despacho"> 
	</div>			

	<div align="center" id='botao_despachar'><input style='height:40px;width:120px' class="botaoAzul" type="button" value="Despachar Pedidos" onclick="despachar()"> <input type="hidden" name="acao" value=""></div>
	<div align="center" id='botao_salvar' style='display:none'><input style='height:40px;width:120px' class="botaoAzul" type="button" value="Salvar Lista" onclick="salvar_entregadores()"></div>
</form>
<style type="text/css">
	#preloader{
		position: fixed;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		background: #FFF;
		text-align: center;
		padding-top: 82px;
		opacity: 0.8;
	}
	#preloader #simbolo{
		border: 2px solid red;
		width: 55px;
		height: 53px;
		display: block;
		border-radius: 50%;
		border-left: 0px;
		margin: auto;
		-webkit-animation:spin 1.4s linear infinite;
		-moz-animation:spin 1.4s linear infinite;
		animation:spin 1.4s linear infinite;
	}
	#preloader p{
		font-size: 29px;
	}
	@-moz-keyframes spin { 100% { -moz-transform: rotate(360deg); } }
	@-webkit-keyframes spin { 100% { -webkit-transform: rotate(360deg); } }
	@keyframes spin { 100% { -webkit-transform: rotate(360deg); transform:rotate(360deg); } }
</style>
<div id="preloader" style="display: none;">
	<div id="simbolo"></div>
	<p>Aguarde, emitindo NFC-e...</p>
</div>
<? endif; ?>


</body>
</html>

<!-- 
<? rodape(); ?>
 -->