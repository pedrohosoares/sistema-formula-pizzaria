<?php if(!isset($_GET['ref']) or empty($_GET['ref'])){exit;} ?>
<!DOCTYPE html>
<html>
<head>
	<title>Cancelando..</title>
</head>
<body>
	<?php 
	$data = 'acao=cancelar_nota_fiscal&ref='.$_GET['ref'].'&chave=165117047d56ce2487aa718bd8d6c5b7';                   
	$html = "<script>\n";
	$html .= "let url = 'https://formulasys.encontresuafranquia.com.br/index.php/?".$data."';\n";
	$html .= "var enviaPedido = new XMLHttpRequest();\n";
	$html .= "enviaPedido.open('GET',url,true);\n";
	$html .= "enviaPedido.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');\n";
	$html .= "enviaPedido.onreadystatechange = function() {
		if (enviaPedido.readyState == XMLHttpRequest.DONE) {
			window.location = 'http://sistema.formulapizzaria.com.br/formula/production/current/site/sys/adm/ipi_rel_historico_pedidos.php';
		}else{
			window.location = 'http://sistema.formulapizzaria.com.br/formula/production/current/site/sys/adm/ipi_rel_historico_pedidos.php';
		}
	};\n";
	$html .= "enviaPedido.send();\n";
	$html .= "</script>";
	echo $html;
	?>
	<h1 style=" font-family: sans-serif; text-align: center; padding-top: 40px; display: block; ">Cancelando nota, aguarde..</h1>
</body>
</html>