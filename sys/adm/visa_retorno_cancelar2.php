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

$codIdentificacao = "3868689"; 
$ambiente = "producao";             
$cod_codigo = validaVarGet('cod');

$con = conectabd();

$objBuscaPedido = executaBuscaSimples("SELECT * FROM ipi_pedidos p WHERE p.cod_pedidos = $cod_codigo", $con);
if ($objBuscaPedido->forma_pg=="VISANET")
{
    $objBuscaTid = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = $cod_codigo AND chave='RETtid'", $con);
    $ret_tid = $objBuscaTid->conteudo;
    
    $objBuscaTid = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = $cod_codigo AND chave='RETprice'", $con);
    $ret_price = $objBuscaTid->conteudo;
        
    echo "Processando...";

    function getURL($var_tid, $var_price)
    {
    
        // Dados obtidos da loja para a transação
    
        // - dados do processo
        $identificacao = "3868689";
        $modulo = "VISAMOSET";
        $operacao = "Cancelamento";
        $ambiente = "teste";
    
        // - dados do pedido
        $tid = $var_tid;
        $price = $var_price;
    
        // Monta a variável com os dados para postagem
        $request = "identificacao=" . $identificacao;
        $request .= "&modulo=" . $modulo;
        $request .= "&operacao=" . $operacao;
        $request .= "&ambiente=" . $ambiente;
    
        // - dados do pedido
        $request .= "&tid=" . $tid;
        $request .= "&free=" . $free;
    
    
        // Faz a postagem para o Paggo
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://comercio.locaweb.com.br/LocaWebCE/comercio.aspx");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
    
    $XMLtransacao = GetURL($ret_tid, $ret_price);
    
    // Ajuste no XML para possibilitar seu tratamento
    $XMLtransacao = str_replace('<REGISTROS>', '<REGISTROS><![CDATA[', $XMLtransacao);
    $XMLtransacao = str_replace('</REGISTROS>', ']]></REGISTROS>', $XMLtransacao);
    
    // Carrega o XML
    $objDom = new DomDocument();
    $loadDom = $objDom->loadXML($XMLtransacao);
    
    // Resgata os dados iniciais do retorno da consulta
    $nodeErro = $objDom->getElementsByTagName('erro')->item(0);
    $Erro = $nodeErro->nodeValue;
    
    $nodeOrigemErro = $objDom->getElementsByTagName('origemErro')->item(0);
    $OrigemErro = $nodeOrigemErro->nodeValue;
    
    
    //Verificando se a transação ocorreu com sucesso
    if ($Erro == '') 
    {
        
        // Resgata o XML dos pedidos
        $nodeIdReqLocaWeb = $objDom->getElementsByTagName('idReqLocaWeb')->item(0);
        $IdReqLocaWeb = $nodeIdReqLocaWeb->nodeValue;
    
        $nodeOperacao = $objDom->getElementsByTagName('operacao')->item(0);
        $Operacao = $nodeOperacao->nodeValue;
    
        $nodeLR = $objDom->getElementsByTagName('LR')->item(0);
        $RETlr = $nodeLR->nodeValue;
    
        $nodeARS = $objDom->getElementsByTagName('ARS')->item(0);
        $RETars = $nodeARS->nodeValue;
    
        $nodeTID = $objDom->getElementsByTagName('TID')->item(0);
        $RETtid = $nodeTID->nodeValue;
    
        $nodeCANCEL_AMOUNT = $objDom->getElementsByTagName('CANCEL_AMOUNT')->item(0);
        $RETcancel_amount = $nodeCANCEL_AMOUNT->nodeValue;
    
        $nodeFREE = $objDom->getElementsByTagName('FREE')->item(0);
        $RETfree = $nodeFREE->nodeValue;
    
    
        // Exibe os dados de retorno
        /*
        echo '<b>ID Requisição Locaweb: </b>' . $IdReqLocaWeb . '<br>'; 
        echo '<b>Operação: </b>' . $Operacao . '<br>';
        echo '<b>Código de retorno (lr): </b>' . $RETlr . '<br>';
        echo '<b>Mensagem de retorno (ars): </b>' . utf8_decode($RETars) . '<br>';
        echo '<b>Código de identificação da transação (TID): </b>' . $RETtid . '<br>'; 
        echo '<b>Valor cancelado (cancel_amount): </b>' . $RETcancel_amount . '<br>';
        echo '<b>Campo livre (free): </b>' . utf8_decode($RETfree) . '<br>';
        */
        $RETlr = (int)$RETlr;
        if ( ($RETlr==0) )
        {
          
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($objBuscaPedido->cod_pedidos,'cancel_IdReqLocaWeb','".$IdReqLocaWeb."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($objBuscaPedido->cod_pedidos,'cancel_Operacao','".$Operacao."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($objBuscaPedido->cod_pedidos,'cancel_RETlr','".$RETlr."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($objBuscaPedido->cod_pedidos,'cancel_RETars','".utf8_decode($RETars)."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($objBuscaPedido->cod_pedidos,'cancel_RETtid','".$RETtid."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($objBuscaPedido->cod_pedidos,'cancel_RETcancel_amount','".$RETcancel_amount."')";
            $resAux =  mysql_query($sqlAux);
            $sqlAux = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($objBuscaPedido->cod_pedidos,'cancel_RETfree','".utf8_decode($RETfree)."')";
            $resAux =  mysql_query($sqlAux);
            
            $sqlPed = "UPDATE ipi_pedidos SET situacao='CAPTURADO' WHERE cod_pedidos=".$objBuscaPedido->cod_pedidos;
            $resPed =  mysql_query($sqlPed);
            echo "<script>window.close();</script>";
            
        }
        else
        {
        
            echo "<br><strong>ERRO AO CANCELAR!</strong>";
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
        // Exibe mensagem de erro
        //echo utf8_decode($Erro) . ' - ' . utf8_decode($OrigemErro);
        // Exibe mensagem de erro
        //echo utf8_decode($Erro) . ' - ' . utf8_decode($OrigemErro);
        
        echo "<br><strong>ERRO AO CANCELAR!</strong>";
        echo "<br><br><strong>Num. Pedido:</strong> ".sprintf("%08d",$objBuscaPedido->cod_pedidos);
        echo "<br><strong>Motivo:</strong> ".utf8_decode($Erro);
        echo "<br><br><strong>Outras informações:</strong> ";
        echo "<br>Origem Erro: ".utf8_decode($OrigemErro);
        echo "<br><input type='button' name='btFechar' value='Fechar Janela' onclick='window.close();'>";
  
    }
        
}
else
{
    echo "<center><b>A forma de pagamento deste pedido NÃO é VISA MOSET!</b></center>";
}
desconectabd($con);

rodape_popup();
	
?>