<?php
require 'errorHandling.php';
require_once 'pedido.php';
require_once 'logger.php';

define('VERSAO_CIELO', "1.1.0");

if(!isset($_SESSION["pedidos"]))
{
	$_SESSION["pedidos"] = new ArrayObject();
}

// CONSTANTES
define("ENDERECO_BASE", "https://qasecommerce.cielo.com.br");   // Endereço de teste
//define("ENDERECO_BASE", "https://ecommerce.cielo.com.br");   // Endereço de produção
define("ENDERECO", ENDERECO_BASE."/servicos/ecommwsec.do");

//define("LOJA", "1006993069");
//define("LOJA_CHAVE", "25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3");
//define("CIELO", "1001734898");
//define("CIELO_CHAVE", "e84827130b9837473681c2787007da5914d6359947015a5cdb2b8843db0fa832");


// Envia requisição
function httprequest($paEndereco, $paPost)
{
  //echo " - ". $paEndereco." - ".$paPost;
	$sessao_curl = curl_init();

	curl_setopt($sessao_curl, CURLOPT_URL, $paEndereco);
	
	curl_setopt($sessao_curl, CURLOPT_FAILONERROR, true);

	//  CURLOPT_SSL_VERIFYPEER
	//  verifica a validade do certificado
	curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, true);
	//  CURLOPPT_SSL_VERIFYHOST
	//  verifica se a identidade do servidor bate com aquela informada no certificado
	curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 2);

	//  CURLOPT_SSL_CAINFO
	//  informa a localização do certificado para verificação com o peer

  if ( strstr(getcwd(), "/sys/") != false )
  {
  	curl_setopt($sessao_curl, CURLOPT_CAINFO, getcwd() . "/../../classe/cielo/ssl/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt");
  }
  else
  {
  	curl_setopt($sessao_curl, CURLOPT_CAINFO, getcwd() . "/classe/cielo/ssl/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt");
  }
	curl_setopt($sessao_curl, CURLOPT_SSLVERSION, 4);

	//  CURLOPT_CONNECTTIMEOUT
	//  o tempo em segundos de espera para obter uma conexão
	curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, 10);

	//  CURLOPT_TIMEOUT
	//  o tempo máximo em segundos de espera para a execução da requisição (curl_exec)
	curl_setopt($sessao_curl, CURLOPT_TIMEOUT, 40);

	//  CURLOPT_RETURNTRANSFER
	//  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
	//  invés de imprimir o resultado na tela. Retorna FALSE se há problemas na requisição
	curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($sessao_curl, CURLOPT_POST, true);
	curl_setopt($sessao_curl, CURLOPT_POSTFIELDS, $paPost );
  //echo "<BR>XXX<br><br>";
  //var_dump( $sessao_curl );

	$resultado = curl_exec($sessao_curl);
  //echo "<br><br>At1 ".$resultado;

	curl_close($sessao_curl);
  //echo "<br><br>".$sessao_curl;

	if ($resultado)
	{
    $resultado;
		return $resultado;
	}
	else
	{
    curl_error($sessao_curl);
		return curl_error($sessao_curl);
	}
}

// Monta URL de retorno
function ReturnURL()
{
	$pageURL = 'http';

	if ($_SERVER["SERVER_PORT"] == 443) // protocolo https
	{
		$pageURL .= 's';
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80")
	{
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"]. substr($_SERVER["REQUEST_URI"], 0);
	}
	// ALTERNATIVA PARA SERVER_NAME -> HOST_HTTP

	$file = substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);

	$ReturnURL = str_replace($file, "ipi_req_retorno_cielo.php", $pageURL);

	return $ReturnURL;
}

?>
