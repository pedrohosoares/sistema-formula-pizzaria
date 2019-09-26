<?
require_once '../../bd.php';
require_once '../../config.php';
require_once '../lib/php/formulario.php';
// Monta cabeçalho para pagina popup
function cabecalho_popup($titulo = '') 
{
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
  
  if ($titulo) 
  {
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
function rodape_popup() 
{
  echo '</div>';
  echo '</div>';
  
  echo '</body>';
  echo '</html>';
}



cabecalho_popup("Consultar Pedido Cartão de Crédito");

ini_set("allow_url_fopen", 1); // Ativa a diretiva 'allow_url_fopen'

$cod_codigo = validaVarGet('cod');
$con = conectabd();

  $objBuscaPedido = executaBuscaSimples("SELECT p.*, pi.num_afiliacao_cartao, pi.chave_cielo FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.cod_pedidos = $cod_codigo", $con);

  $objDetalhesPagamento = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = $cod_codigo AND chave='tid'", $con);

	require_once "../../classe/cielo/include.php";

	$objResposta = null;
	
	//$acao = $_POST["acao"];
	$acao = "CONSULTA";

	$Pedido = new Pedido();

  //echo "Aki: "."SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = 234448 AND chave='tid'";

	$Pedido->tid = $objDetalhesPagamento->conteudo; 
	
  //$Pedido->dadosEcNumero = $_POST["numeroLoja"];
	$Pedido->dadosEcNumero = $objBuscaPedido->num_afiliacao_cartao;
	$Pedido->dadosEcChave = $objBuscaPedido->chave_cielo;


/*
	if($Pedido->dadosEcNumero == LOJA)
  {
		$Pedido->dadosEcChave = LOJA_CHAVE;
	}
  else if($Pedido->dadosEcNumero == CIELO)
  {
		$Pedido->dadosEcChave = CIELO_CHAVE;
	}
  else
  {
		$Pedido->dadosEcChave = md5($Pedido->dadosEcNumero);
	}
*/
	
	switch($acao)
	{
		case "AUTORIZACAO":  
			$objResposta = $Pedido->RequisicaoAutorizacaoTid();
			break;
		case "CAPTURA": 
			//$valor = $_POST["valor"];
      $total_formato_cielo = str_replace(",","", str_replace(".","", sprintf("%s",$objBuscaPedido->valor_total) ) );   
			$objResposta = $Pedido->RequisicaoCaptura($total_formato_cielo, null);
			break;
		case "CANCELAMENTO":
			$objResposta = $Pedido->RequisicaoCancelamento();
			break;
		case "CONSULTA": 
			$objResposta = $Pedido->RequisicaoConsulta();
			break; 
	}

  foreach ($objResposta as $resposta => $valor)
  {
    if ( count($valor)>0 )
    {
      foreach ($valor as $subresposta => $subvalor)
      {
				//echo "<Br><Br>Y: ".$subresposta." - ".utf8_decode($subvalor);
        if ($subresposta == "mensagem")
        {
          $retorno_mensagem_autorizacao = utf8_decode($subvalor);
        }
        else if ($subresposta == "lr")
        {
          $retorno_lr_autorizacao = utf8_decode($subvalor);
        }
        else if ($subresposta == "arp")
        {
          $retorno_arp_autorizacao = utf8_decode($subvalor);
        }
        else if ($subresposta == "bandeira")
        {
          $retorno_bandeira_autorizacao = utf8_decode($subvalor);
        }
        else if ($subresposta == "valor")
        {
          $retorno_valor_autorizacao = utf8_decode($subvalor);
        }
      }
    }
  }

  //echo '<textarea name="xmlRetorno" cols="70" rows="40">';
  //echo htmlentities($objResposta->asXML()); 
  //echo '</textarea>';

  switch($objResposta->status)
	{
		case "0": $status = "Criada";
				break;
		case "1": $status = "Em andamento";
				break;
		case "2": $status = "Autenticada";
				break;
		case "3": $status = "Não autenticada";
				break;
		case "4": $status = "Autorizada";
				break;
		case "5": $status = "Não autorizada";
				break;
		case "6": $status = "Capturada";
				break;
		case "8": $status = "Não capturada";
				break;
		case "9": $status = "Cancelada";
				break;
		case "10": $status = "Em autenticação";
				break;
		default: $status = "n/a";
				break;
	}

  echo "<br><br><strong>Num. Pedido:</strong> ".sprintf("%08d",$cod_codigo);
  echo "<br><strong>Status:</strong>  ".$status." - (".$objResposta->status.")";
  echo "<br><strong>Mensagem:</strong> ".($retorno_mensagem_autorizacao);

  echo "<br><br><strong>Outras informações:</strong> ";
  echo "<br><strong>Valor:</strong> ".$retorno_valor_autorizacao;
  echo "<br><strong>Bandeira:</strong> ".$retorno_bandeira_autorizacao;
  echo "<br><strong>Tid:</strong> ".$objResposta->tid;
  echo "<br><strong>LR:</strong> ".$retorno_lr_autorizacao;
  echo "<br><strong>ARP:</strong> ".$retorno_arp_autorizacao;
  echo "<br><br><input type='button' name='btFechar' value='Fechar Janela' onclick='window.close();'>";

desconectabd($con);

rodape_popup();
	
?>
