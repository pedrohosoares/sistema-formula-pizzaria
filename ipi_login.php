<?
session_start();

require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';
require_once 'classe/facebook.php';
require_once 'classe/cliente.php';
  $facebook = new Facebook(array(
  'appId'  => '264264503687010',
  'secret' => '56d2a493b367a020f407beb1e9d33b40',
));

$origem = validaVarPost('origem', '/[0-9]+/');
$email = validaVarPost('email');
$senha = validaVarPost('senha');
$como = validaVarPost('como');
$user = $facebook->getUser();

switch($origem) {

  case 1:
    $pagina_origem = 'index.php';
		if ($_SESSION['ipi_cliente']['algo_mais']=="1")
			$pagina_destino="pagamentos";
		else
  		$pagina_destino="algo_mais";
    $location = $pagina_origem.'?erro=1'; 
  break;
  case 2:
    //$pagina_origem = 'ipi_req_restrito2.php';
    $pagina_origem = 'pedidos';
    $pagina_destino = 'pedidos';
    $location = $pagina_destino.'&erro=1'; 
  break;
}

$pagina_destino='meu_home';
if(count($_SESSION['ipi_carrinho']['pedido'])>=1) // TODO fazer um redirecionamento com base na origem
{
  $pagina_destino='pagamentos';
}

$cliente = new Cliente();
if($como=='facebook')
{
  if($user)
  {
    try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
    } catch (FacebookApiException $e) {
      // console.log($e);
      echo '<script>location.href="home&erro=2"</script>';
      $user = null;
    }
  }

  if($user)
  {
    $con = conectabd();

    $sql_procura_email = "select c.cod_clientes from ipi_clientes_redes_sociais cr inner join ipi_clientes c on c.cod_clientes = cr.cod_clientes where cr.hash_acesso_cliente_site = '".$_SESSION['fb_264264503687010_access_token']."' and cr.status='ATIVO'";

    $res_procura_email = mysql_query($sql_procura_email);
    $num_procura_email = mysql_num_rows($res_procura_email);
   // echo "<pre>";
    //print_r($user_profile);
    //echo "</pre>";
    //echo "sql".$sql_procura_email."<br/>";
    //echo "num".$num_procura_email;
    //die();
    if($num_procura_email>0)
    {
      $obj_procura_email = mysql_fetch_object($res_procura_email);

      $cliente->criar_sessao_login($obj_procura_email->cod_clientes);
      
      if ($pagina_origem=="pedidos")
      {
        require_once 'ipi_req_carrinho_classe.php';
        $carrinho = new ipi_carrinho();
        if ($carrinho->pontos_fidelidade()=="0")
          $pagina_destino="pagamentos";
        else
          $pagina_destino="usar_fidelidade";
      }
      desconectabd($con);
      header('Location: '.$pagina_destino);

    }else
    {
      // $user_profile['email']
      $sql_procura_email = "select c.cod_clientes from ipi_clientes_redes_sociais cr inner join ipi_clientes c on c.cod_clientes = cr.cod_clientes where cr.rs_email = '".$user_profile['email']."' AND cr.status='ATIVO'";

      $res_procura_email = mysql_query($sql_procura_email);
      $num_procura_email = mysql_num_rows($res_procura_email);
      if($num_procura_email>0)
      {
        $sql_atualiza_hash = "update ipi_clientes_redes_sociais set hash_acesso_cliente_site = '".$_SESSION['fb_264264503687010_access_token']."' where rs_email = '".$user_profile['email']."'";
        $res_atualiza_hash = mysql_query($sql_atualiza_hash);

        if($res_atualiza_hash)
        {
          $obj_procura_email = mysql_fetch_object($res_procura_email);

          $cliente->criar_sessao_login($obj_procura_email->cod_clientes);
          
          if ($pagina_origem=="pedidos")
          {
            require_once 'ipi_req_carrinho_classe.php';
            $carrinho = new ipi_carrinho();
            if ($carrinho->pontos_fidelidade()=="0")
              $pagina_destino="pagamentos";
            else
              $pagina_destino="usar_fidelidade";
          }
          desconectabd($con);
          header('Location: '.$pagina_destino);
        }
        else
        {
         header('Location: home&erro=2');
        }
      }
      else
      {
        header('Location: cadastro_social');
        die();
      }
    }

    desconectabd($con);

  }

}else
if(($email != '') && ($senha != '')) 
{
  $con = conectabd();
  
  $SqlBuscaUsuario = sprintf("SELECT * FROM ipi_clientes WHERE email = '%s' AND senha = MD5('%s') AND situacao = 'ATIVO'", 
                             $email, $senha);

  $resBuscaUsuario = mysql_query($SqlBuscaUsuario);
  $numBuscaUsuario = mysql_num_rows($resBuscaUsuario);
  $objBuscaUsuario = mysql_fetch_object($resBuscaUsuario);
  
	if($numBuscaUsuario > 0) 
	{

    $_SESSION['ipi_cliente']['codigo'] = $objBuscaUsuario->cod_clientes;
    $_SESSION['ipi_cliente']['nome'] = bd2texto($objBuscaUsuario->nome);
    $_SESSION['ipi_cliente']['email'] = bd2texto($objBuscaUsuario->email);
    $_SESSION['ipi_cliente']['cpf'] = $objBuscaUsuario->cpf;

    //$objQuantidadePontos = executaBuscaSimples("SELECT SUM(pontos) AS soma_pontos FROM ipi_fidelidade_clientes WHERE cod_clientes = ".$objBuscaUsuario->cod_clientes." AND (data_validade > NOW() OR data_validade = '0000-00-00' OR data_validade IS NULL) ORDER BY data_hora_fidelidade DESC", $con);
    $objQuantidadePontos = executaBuscaSimples("SELECT SUM(pontos) AS soma_pontos FROM ipi_fidelidade_clientes WHERE cod_clientes = ".$objBuscaUsuario->cod_clientes." ORDER BY data_hora_fidelidade DESC", $con);

    $objQuantidadePontos = executaBuscaSimples("SELECT SUM(pontos) AS soma_pontos FROM ipi_fidelidade_clientes WHERE cod_clientes = ".$objBuscaUsuario->cod_clientes." ORDER BY data_hora_fidelidade DESC", $con);
      
	  $soma = ($objQuantidadePontos->soma_pontos > 0) ? $objQuantidadePontos->soma_pontos : 0;
	  $_SESSION['ipi_cliente']['pontos_fidelidade'] = $soma;
    
    if($objBuscaUsuario->ultimo_acesso != '')
      $_SESSION['ipi_cliente']['ultimo_acesso'] = bd2datahora($objBuscaUsuario->ultimo_acesso);
    else
      $_SESSION['ipi_cliente']['ultimo_acesso'] = '';
    
    $_SESSION['ipi_cliente']['autenticado'] = true;
    
    $SqlUpdateAcesso = 'UPDATE ipi_clientes SET ultimo_acesso = NOW() WHERE cod_clientes = '.$objBuscaUsuario->cod_clientes;
    mysql_query($SqlUpdateAcesso);

    
    if ($pagina_origem=="pedidos")
    {
		  require_once 'ipi_req_carrinho_classe.php';
      $carrinho = new ipi_carrinho();
      if ($carrinho->pontos_fidelidade()=="0")
  		  $pagina_destino="pagamentos";
		  else
			  $pagina_destino="usar_fidelidade";
    }
  	desconectabd($con);
    header('Location: '.$pagina_destino);
  }
  else 
  {
      if ($pagina_origem=="pedidos")
      {
          header('Location: identifique&erro=1');
      }
      else
      {
            $con = conectabd();
      require_once 'ipi_req_carrinho_classe.php';
      
      $carrinho = new ipi_carrinho();
      $carrinho->log($con, "ERRO_LOGIN_PUBLICO", $email."@@@".$senha);
      
      desconectabd($con);
       header('Location: home&erro=2');
      }

  }
  
}else
{
  header('Location: home&erro=1');

}
?>
