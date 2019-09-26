<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/pedido.php';

cabecalho('Cancelamento de nota');
header('Content-Type: text/html; charset=utf-8');
#SE FOR POST
if (!isset($_GET['ref']) or empty($_GET['ref'])) {exit;}
$get = $_GET['ref'];
$get = str_replace(array("'", '"', "=", ".", "+", "_", "-", "*", 'INSERT', 'UPDATE', 'EXIT', "echo", "$", '%'), "", $get);
$con = conectabd();
$idsUsuarios = implode(',', $_SESSION['usuario']['cod_pizzarias']);
$sqlPizzaria = "SELECT dados_extra,cnpj FROM ipi_pizzarias WHERE cod_pizzarias IN ($idsUsuarios)";
$sqlPizzaria = mysql_query($sqlPizzaria);
$sqlPizzaria = mysql_fetch_assoc($sqlPizzaria);
$cnpj = $sqlPizzaria['cnpj'];
$sqlPizzaria = $sqlPizzaria['dados_extra'];
$idsUsuarios = $idsUsuarios.',24';
$sql = "SELECT cod_pedidos,ref_nota_fiscal FROM ipi_pedidos WHERE situacao NOT IN ('CANCELADO','BAIXADO') AND cod_pedidos='" . $get . "' AND cod_pizzarias IN ($idsUsuarios)";
$sql = mysql_query($sql);
$encontrouNota = mysql_num_rows($sql);
if($encontrouNota){
	while($nota = mysql_fetch_assoc($sql)){
		$notas[] = $nota;
	}
}

desconectabd($con);
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="../lib/js/tooltipster-master/dist/css/tooltipster.bundle.min.css" />
	<script src="../lib/js/tooltipster-master/dist/js/tooltipster.bundle.min.js"></script>
	<title>Cancelamento de nota</title>
	<style>
		#conteudo{
			width:100%;
		}
		div.cancelar{
			text-align: center; width: 100%;font-size:23px;
			display:block;
			margin-top:20px;
		}
		div.cancelar span{
			color:red;
		}
		div.cancelar label{
			font-weight:900;
		}
		div.cancelar textarea{
			width: 80%;
			height: 115px;
			border-radius: 10px;
			border: 1px solid #CCC;
			display: block;
			margin: auto;
			font-size: 18px;
			padding:10px;
			color: #333;
		}
		div.cancelar input[type="submit"]{
			background-color:#00aa41;
			color:#FFF;
			font-size:25px;
			padding:10px 20px 10px 20px;
			border:0px;
			border-radius:10px;
			box-shadow: #666 1px 8px 17px;
			cursor:pointer;
		}
		div.cancelar input[type="submit"]:hover{
			background-color:#00ce4b;
		}
	</style>
</head>
<body>
	<div class="cancelar">
	<?php echo isset($mensagem)?$mensagem:""; ?>
	<?php if ($encontrouNota > 0) {?>
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">

			Pedido: <a target="_blank" href="http://sistema.formulapizzaria.com.br/formula/production/current/site/sys/adm/ipi_rel_historico_pedidos.php?p=<?php echo $notas[0]['cod_pedidos']; ?>"><?php echo $notas[0]['cod_pedidos']; ?></a><br />
			Ref Nota: <?php echo !empty($notas[0]['ref_nota_fiscal'])?$notas[0]['ref_nota_fiscal']:"Sem nota"; ?><br />
			<input type="hidden" name="cod_pedidos" value="<?php echo $notas[0]['cod_pedidos']; ?>" />
			<input type="hidden" name="cod_usuarios" value="<?php echo $_SESSION['usuario']['codigo']; ?>" />
			<input type="hidden" name="cod_pizzarias" value="<?php echo implode(',',$_SESSION['usuario']['cod_pizzarias']); ?>" />
			<input type="hidden" name="cnpj" value="<?php echo $cnpj; ?>" />
			<input type="hidden" name="ref" value="<?php echo $notas[0]['ref_nota_fiscal']; ?>" />
			<?php 
			$sqlPizzaria  = json_decode($sqlPizzaria,true);
			?>
			<input type="hidden" name="token" value="<?php echo $sqlPizzaria['token_focusnfe']['producao']['token']; ?>" />
			<input type="hidden" name="login" value="<?php echo $sqlPizzaria['token_focusnfe']['producao']['login']; ?>" />
			<input type="hidden" name="justificativa" value="" />
			<label>Informe o motivo do cancelamento:</label>
			<br />
			<textarea></textarea>
			<br />
			<?php if(!empty($notas[0]['ref_nota_fiscal'])){ ?>
				<input type="checkbox" checked="checked" style=" width: 30px; height: 30px; " name="cancelar_nota" /> Cancelar nota?
			<?php }else{
				?>
				Nenhuma nota foi emitida <?php echo utf8_decode('até'); ?> o momento ou o pedido já foi cancelado.
				<?php
			} ?>
			<br />
			<span class="alert"></span>
			<br />
			<input type="submit" value="Enviar" />
		</form>
		<?php }else{
			echo utf8_decode("Nota não encontrada ou pedido já cancelado");
		}?>
	</div>
	<script>
		let cancelar = document.querySelector('.cancelar');
		let form  = document.querySelector('form');
		let cod_pizzarias = form.querySelector('input[name="cod_pizzarias"]');
		let cod_pedidos = form.querySelector('input[name="cod_pedidos"]');
		let textarea = form.querySelector('textarea');
		let cnpj = form.querySelector('input[name="cnpj"]');
		let justificativa = form.querySelector('input[name="justificativa"]');
		let span = form.querySelector('span.alert');
		let ref = form.querySelector('input[name="ref"]');
		let login = form.querySelector('input[name="login"]');
		let token = form.querySelector('input[name="token"]');
		let submit = form.querySelector('input[type="submit"]');
		let checked = form.querySelector('input[name="cancelar_nota"]');
		let cod_usuarios = form.querySelector('input[name="cod_usuarios"]');
		submit.onclick = (e)=>{
			e.preventDefault();
			if(textarea.value.length > 15 && textarea.value.length < 255){
				console.log(checked);
				if(checked == null){
					checked = false;
				}else{
					checked = checked.checked;
				}
				justificativa.value = textarea.value;
				textarea.setAttribute('disabled','true');
				textarea.style = "";
				submit.value = "Enviando..";
				let link = "https://formulasys.encontresuafranquia.com.br";
				//let link = "http://localhost";
				let url = link+'/focusnfe/cancelamento.php?cod_usuarios='+cod_usuarios.value+'&checked='+checked+'&cod_pizzarias='+cod_pizzarias.value+'&cnpj='+cnpj.value+'&cod_pedidos='+cod_pedidos.value+'&ref='+ref.value+'&justificativa='+justificativa.value+'&login='+login.value+'&token='+token.value;
				let xhr = new XMLHttpRequest();
				xhr.open('GET',url,true);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
				xhr.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						form.remove();
						let mensagem;
						mensagem = "<h3 class='text-align:center;'>Falha ao Cancelar nota</h3>";
						if(this.response != ""){
							let response = JSON.parse(this.response);
							if(response.mensagem){
								mensagem = "<h3 class='text-align:center;'>"+response.mensagem+"</h3>";
							}
							if(response.justificativa){
								mensagem += "<br /><h3 class='text-align:center;'>"+response.justificativa+"</h3>";
							}
							if(response.mensagem_sefaz){
								mensagem = "<h3 class='text-align:center;'>"+response.mensagem_sefaz+". Link para download: <br /> <a href='https://api.focusnfe.com.br"+response.caminho_xml_cancelamento+"' download>Dowload da nota</a></h3>";
							}
						}
						cancelar.innerHTML = mensagem;					
					}
				}
				xhr.send();
			}else{
				textarea.style = "border-color:red;";
				span.innerText = "A justificativa precisa ter entre 15 e 255 caracteres.";
			}
		};
	</script>
<? rodape(); ?>