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

cabecalho_popup();

ini_set("allow_url_fopen", 1); // Ativa a diretiva 'allow_url_fopen'

$cod_codigo = validaVarGet('cod');
$con = conectabd();

$objBuscaPedido = executaBuscaSimples("SELECT p.*, pi.num_gateway_pagamento FROM ipi_pedidos p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.cod_pedidos = $cod_codigo", $con);

$codIdentificacao = $objBuscaPedido->num_gateway_pagamento;
$ambiente = "PRODUCAO";             


if ( ($objBuscaPedido->forma_pg=="VISANET")||($objBuscaPedido->forma_pg=="MASTERCARDNET") )
{
    $objBuscaTid = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = $cod_codigo AND chave='retorno_tid'", $con);
    $ret_tid = $objBuscaTid->conteudo;
    $objBuscaTid = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = $cod_codigo AND chave='retorno_valor'", $con);
    $ret_price = $objBuscaTid->conteudo;
    $objBuscaTid = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = $cod_codigo AND chave='RETfree'", $con);
    $ret_free = $objBuscaTid->conteudo;
    echo "Processando...";

    function getURL($var_tid, $var_price, $var_free, $var_codIdentificacao)
    {
          
        // Dados obtidos da loja para a transação
        // - dados do processo
        $identificacao = $var_codIdentificacao;

        $modulo = 'CIELO';
        $operacao = 'Captura';
        $ambiente = 'PRODUCAO';

        // - dados do pedido
        $tid = $var_tid;

        // Monta a variável com os dados para postagem
        $request = 'identificacao=' . $identificacao;
        $request .= '&modulo=' . $modulo;
        $request .= '&operacao=' . $operacao;
        $request .= '&ambiente=' . $ambiente;
        $request .= '&tid=' . $tid;

        //echo "<Br>request: ".$request;
        //die();

        // Faz a postagem para a Cielo
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://comercio.locaweb.com.br/comercio.comp');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
    
    $XMLtransacao = GetURL($ret_tid, $ret_price, $ret_free, $codIdentificacao);

    // Carrega o XML
    $objDom = new DomDocument();
    $loadDom = $objDom->loadXML($XMLtransacao);
/*
    echo "<pre>";
    print_r($XMLtransacao);
    echo "</pre>";
*/
    $nodeErro = $objDom->getElementsByTagName('erro')->item(0);
    if ($nodeErro != '') 
    {
        $nodeCodigoErro = $nodeErro->getElementsByTagName('codigo')->item(0);
        $retorno_codigo_erro = $nodeCodigoErro->nodeValue;

        $nodeMensagemErro = $nodeErro->getElementsByTagName('mensagem')->item(0);
        $retorno_mensagem_erro = $nodeMensagemErro->nodeValue;
    }

    $nodeTransacao = $objDom->getElementsByTagName('transacao')->item(0);
    if ($nodeTransacao != '') 
    {
        $nodeTID = $nodeTransacao->getElementsByTagName('tid')->item(0);
        $retorno_tid = $nodeTID->nodeValue;

        $nodePAN = $nodeTransacao->getElementsByTagName('pan')->item(0);
        $retorno_pan = $nodePAN->nodeValue;

        $nodeDadosPedido = $nodeTransacao->getElementsByTagName('dados-pedido')->item(0);
        if ($nodeTransacao != '') 
        {
            $nodeNumero = $nodeDadosPedido->getElementsByTagName('numero')->item(0);
            $retorno_pedido = $nodeNumero->nodeValue;

            $nodeValor = $nodeDadosPedido->getElementsByTagName('valor')->item(0);
            $retorno_valor = $nodeValor->nodeValue;

            $nodeMoeda = $nodeDadosPedido->getElementsByTagName('moeda')->item(0);
            $retorno_moeda = $nodeMoeda->nodeValue;

            $nodeDataHora = $nodeDadosPedido->getElementsByTagName('data-hora')->item(0);
            $retorno_data_hora = $nodeDataHora->nodeValue;

            $nodeDescricao = $nodeDadosPedido->getElementsByTagName('descricao')->item(0);
            $retorno_descricao = utf8_decode(utf8_decode($nodeDescricao->nodeValue));

            $nodeIdioma = $nodeDadosPedido->getElementsByTagName('idioma')->item(0);
            $retorno_idioma = $nodeIdioma->nodeValue;
        }

        $nodeFormaPagamento = $nodeTransacao->getElementsByTagName('forma-pagamento')->item(0);
        if ($nodeFormaPagamento != '') 
        {
            $nodeBandeira = $nodeFormaPagamento->getElementsByTagName('bandeira')->item(0);
            $retorno_bandeira = $nodeBandeira->nodeValue;

            $nodeProduto = $nodeFormaPagamento->getElementsByTagName('produto')->item(0);
            $retorno_produto = $nodeProduto->nodeValue;

            $nodeParcelas = $nodeFormaPagamento->getElementsByTagName('parcelas')->item(0);
            $retorno_parcelas = $nodeParcelas->nodeValue;
        }

        $nodeStatus = $nodeTransacao->getElementsByTagName('status')->item(0);
        $retorno_status = $nodeStatus->nodeValue;

        $nodeCaptura = $nodeTransacao->getElementsByTagName('captura')->item(0);
        if ($nodeCaptura != '') 
        {
            $nodeCodigoCaptura = $nodeCaptura->getElementsByTagName('codigo')->item(0);
            $retorno_codigo_captura = $nodeCodigoCaptura->nodeValue;

            $nodeMensagemCaptura = $nodeCaptura->getElementsByTagName('mensagem')->item(0);
            $retorno_mensagem_captura = $nodeMensagemCaptura->nodeValue;

            $nodeDataHoraCaptura = $nodeCaptura->getElementsByTagName('data-hora')->item(0);
            $retorno_data_hora_captura = $nodeDataHoraCaptura->nodeValue;

            $nodeValorCaptura = $nodeCaptura->getElementsByTagName('valor')->item(0);
            $retorno_valor_captura = $nodeValorCaptura->nodeValue;
        }

        $nodeURLAutenticacao = $nodeTransacao->getElementsByTagName('url-autenticacao')->item(0);
        $retorno_url_autenticacao = $nodeURLAutenticacao->nodeValue;
    }
    
/*

    echo '<b> TRANSAÇÃO </b><br />';
    echo '<b>Código de identificação do pedido (TID): </b>' . $retorno_tid . '<br />';
    echo '<b>PAN do pedido (pan): </b>' . $retorno_pan . '<br />';

    echo '<b>Número do pedido (numero): </b>' . $retorno_pedido . '<br />';
    echo '<b>Valor do pedido (valor): </b>' . $retorno_valor . '<br />';
    echo '<b>Moeda do pedido (moeda): </b>' . $retorno_moeda . '<br />';
    echo '<b>Data e hora do pedido (data-hora): </b>' . $retorno_data_hora . '<br />';
    echo '<b>Descrição do pedido (descricao): </b>' . $retorno_descricao . '<br />';
    echo '<b>Idioma do pedido (idioma): </b>' . $retorno_idioma . '<br />';

    echo '<b>Bandeira (bandeira): </b>' . $retorno_bandeira . '<br />';
    echo '<b>Forma de pagamento (produto): </b>' . $retorno_produto . '<br />';
    echo '<b>Número de parcelas (parcelas): </b>' . $retorno_parcelas . '<br />';

    echo '<b>XXXStatus do pedido (status): </b>' . $retorno_status . '<br />';

    echo '<b>URL para autenticação (url-autenticacao): </b>' . $retorno_url_autenticacao . '<br /><br />';

    echo '<b> CAPTURA </b><br />';
    echo '<b>Código do captura (codigo): </b>' . $retorno_codigo_captura . '<br />';
    echo '<b>Mensagem do captura (mensagem): </b>' . $retorno_mensagem_captura . '<br />';
    echo '<b>Data e hora do captura (data-hora): </b>' . $retorno_data_hora_captura . '<br />';
    echo '<b>Valor do captura (valor): </b>' . $retorno_valor_captura . '<br /><br />';
*/

    //Verificando se a transação ocorreu com sucesso
  if ($retorno_codigo_erro == '')
    {


        $retorno_status = (int)$retorno_status;
        if ($retorno_status==6)
        {
          
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_tid','".$retorno_tid."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_pan','".$retorno_pan."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_pedido','".$retorno_pedido."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_valor','".$retorno_valor."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_moeda','".$retorno_moeda."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_data_hora','".$retorno_data_hora."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_descricao','".$retorno_descricao."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_idioma','".$retorno_idioma."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_bandeira','".$retorno_bandeira."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_produto','".$retorno_produto."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_parcelas','".$retorno_parcelas."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_status','".$retorno_status."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_url_autenticacao','".$retorno_url_autenticacao."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_codigo_captura','".$retorno_codigo_captura."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_mensagem_captura','".$retorno_mensagem_captura."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_data_hora_captura','".$retorno_data_hora_captura."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES (".$objBuscaPedido->cod_pedidos.",'cap_retorno_valor_captura','".$retorno_valor_captura."')";
            $resAux =  mysql_query($sqlAux);

            
            $sqlPed = "UPDATE ipi_pedidos SET situacao='CAPTURADO' WHERE cod_pedidos=".$objBuscaPedido->cod_pedidos;
            $resPed =  mysql_query($sqlPed);
            echo "<script>window.close();</script>";
        }
        else
        {
        
            echo "<br><strong>ERRO NA CAPTURA!</strong>";
            echo "<br><br><strong>Num. Pedido:</strong> ".sprintf("%08d",$objBuscaPedido->cod_pedidos);
            echo "<br><strong>Motivo:</strong> ".utf8_decode($RETars);
            echo "<br><br><strong>Outras informações:</strong> ";
            echo "<br><strong>Tid:</strong> ".$RETtid;
            echo "<br><strong>LR:</strong> ".$RETlr;
            echo "<br><strong>IdLocaweb:</strong>".$IdReqLocaWeb;
            echo "<br><br><input type='button' name='btFechar' value='Fechar Janela' onclick='window.close();'>";
          
        }
    
    } 
    else 
    {
        $erro_capturado = "O status 'Capturada' não permite captura.";
        if ($erro_capturado==utf8_decode($retorno_mensagem_erro)) //Libera se já foi capturado!
        {
            //echo "Igualzinho só dar baixa!";
            $sqlPed = "UPDATE ipi_pedidos SET situacao='CAPTURADO' WHERE cod_pedidos=".$objBuscaPedido->cod_pedidos;
            $resPed =  mysql_query($sqlPed);
            echo "<script>window.close();</script>";
        }
        else
        {
          echo "<br><strong>ERRO NA CAPTURA!</strong>";
          echo "<br><br><strong>Num. Pedido:</strong> ".sprintf("%08d",$objBuscaPedido->cod_pedidos);
          echo '<br><strong>Erro: </strong>' . $retorno_codigo_erro . '<br />';
          echo '<br><strong>Mensagem: </strong>' . utf8_decode($retorno_mensagem_erro) . '<br />';
          echo "<br><input type='button' name='btFechar' value='Fechar Janela' onclick='window.close();'>";
        }
    }
}
else
{
    echo "<center><b>A forma de pagamento deste pedido NÃO é VISA MOSET!</b></center>";
}
desconectabd($con);

rodape_popup();
	
?>
