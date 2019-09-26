<?
require_once '../../bd.php';
require_once '../../config.php';
require_once '../lib/php/formulario.php';
// Monta cabeçalho para pagina popup
function cabecalho_popup($titulo = '') {
  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt_br" lang="pt_br">';
  echo '<head>';
  echo '<title>'.NOME_SITE.' | Painel de Administração</title>';
  echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>';
  echo '<meta name="author" content="Internet Sistemas http://www.internetsistemas.com.br"/>';
  echo '<meta name="copyright" content="Copyright (c) '.date('Y'). ' Internet Sistemas. Todos os direitos reservados."/>';
  echo '<meta name="description" content="Painel de Administração do Site"/>';
  echo '<meta name="keywords" content=""/>';
  echo '<meta name="robots" content="noindex, nofollow"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/principal.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/menu.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/validacao.css"/>';
  
  echo '<link rel="shortcut icon" type="image/x-icon" href="../lib/img/principal/icone.png"/>';
  
  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-core.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-more.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/form.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mascara.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/tabs.js"></script>';
  
  echo '</head>';
  echo '<body>';
  
  echo '<div id="logo">';
  echo '<div id="nome_site">'.NOME_SITE.'</div>';
  echo '</div>';
  
  echo '<div id="pagina">';
  
  if ($titulo) {
    echo '<div id="caixa">';
    echo '<div id="titulo"><h1>'.$titulo.'</h1></div>';
    echo '</div>';
  }
  
  echo '<div id="conteudo">';
  
  ?>
  <script>
  window.addEvent('load', function() {
  //window.addEvent('domready', function() {
    // Define automaticamente a altura do rodape e coloca uma barra de rolagem no conteudo.
    $('conteudo').setStyle('overflow', 'auto');
    $('conteudo').setStyle('height', $(document.body).getCoordinates().height - $('logo').getCoordinates().height - $('caixa').getCoordinates().height - 20);
  });
  </script>
  <?
}

// Monta Rodapé para popup
function rodape_popup() {
  echo '</div>';
  echo '</div>';
  
  echo '</body>';
  echo '</html>';
}

cabecalho_popup();
if (validaVarGet('cod'))
{
	ini_set("allow_url_fopen", 1); 
	$codIdentificacao = "2432533"; 
	$ambiente = "producao"; 			
	
	$codigo = validaVarPost($chave_primaria);
    $cod_entregador = validaVarPost('cod_entregador');
    $cod_codigo = validaVarGet('cod');
    
    $con = conectabd();

	$objBuscaPedido = executaBuscaSimples("SELECT * FROM ipi_pedidos p WHERE p.cod_pedidos = $cod_codigo", $con);
	if ($objBuscaPedido->forma_pg=="VISANET")
		{
		$objBuscaTid = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = $cod_codigo AND chave='tid'", $con);
		echo "Processando...";
		
		$host = "comercio.locaweb.com.br";
		$port = 80;
		$path = "/comercio.comp";
		$fullhost = "https://comercio.locaweb.com.br:80";
	
		// Monta a url para captura do lr
		$request = "tid=".$objBuscaTid->conteudo."&modulo=VISAVBV&operacao=Captura&identificacao=".$codIdentificacao."&ambiente=".$ambiente."&free=".$free;
		
		$request_length = strlen($request);
		
		
		$header  = "POST $path HTTP/1.0\r\n";
		$header .= "Host: $host\r\n";
		$header .= "User-Agent: DoCoMo/1.0/P503i\r\n";
		$header .= "Content-type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-length: $request_length\r\n";
		$header .= "\r\n";
	
	    $fp = fsockopen($host,$port,$err_num,$err_msg,30);
	
	    fputs($fp, $header . $request);
	    
	    while(trim(fgets($fp,4096)) != '');
	    
	    while(!feof($fp)){
	        $response .= fgets($fp,4096);
	    }

		echo $response;
	}
desconectabd($con);	
}
else 
	{
    $con = conectabd();
	$objBuscaPedido = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE chave = 'tid' AND conteudo = '".$_REQUEST['tid']."'", $con);
	$lr = (int)$_REQUEST['lr'];
	if (($lr==0)||($lr==3))
		{
			
		
		foreach ($_REQUEST as $campo => $valor)
			{
			$sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($objBuscaPedido->cod_pedidos,'cap_".$campo."','".$valor."')";
			$resAux =  mysql_query($sqlAux);
			}	

		$sqlPed = "UPDATE ipi_pedidos SET situacao='CAPTURADO' WHERE cod_pedidos=".$objBuscaPedido->cod_pedidos;
		$resPed =  mysql_query($sqlPed);
			
		echo "<script>window.close();</script>";
		}
	else
		{	
		echo "<br><strong>ERRO NA CAPTURA!</strong>";
		echo "<br><br><strong>Num. Pedido:</strong> ".sprintf("%08d",$objBuscaPedido->cod_pedidos);
		echo "<br><strong>Motivo:</strong> ".$_REQUEST['ars'];
		echo "<br><br><strong>Outras informações:</strong> ";
		echo "<br>Tid: ".$_REQUEST['tid'];
		echo "<br>Lr: ".$_REQUEST['lr'];
		echo "<br>Cap: ".$_REQUEST['cap'];
		echo "<br><input type='button' name='btFechar' value='Fechar Janela' onclick='window.close();'>";
		}
	desconectabd($con);	
	}
rodape_popup();
	
?>