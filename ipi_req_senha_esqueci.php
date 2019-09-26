<script language="JavaScript">
function validacao(frm) {
  if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(frm.email_senha.value))) {
    alert("E-mail digitado inválido!");
    frm.email_senha.focus();
    return false;
  }	
		
  return true;
}
</script>
<?

require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';

$acao = validaVarPost ( 'acao' );

function generatePassword($length = 8) {
  $password = "";
  $possible = "0123456789abcdfghjkmnpqrstvwxyz";
  
  $i = 0;
  
  while ( $i < $length ) {
    $char = substr ( $possible, mt_rand ( 0, strlen ( $possible ) - 1 ), 1 );
    
    if (! strstr ( $password, $char )) {
      $password .= $char;
      $i ++;
    }
  }
  
  return $password;
}

if ($acao == 'enviar') {
  $email = validaVarPost ( 'email_senha' );
  
  $con = conectabd ();
  
  $SqlBuscaEmail = ("SELECT * FROM ipi_clientes WHERE email='$email'");
  $resBuscaEmail = mysql_query($SqlBuscaEmail);
  $objBuscaEmail = mysql_fetch_object($resBuscaEmail);
  $numBuscaEmail = mysql_num_rows($resBuscaEmail);
  
  if ($numBuscaEmail > 0) {
    echo "<br />";
    
    require_once 'config.php';
    require_once 'ipi_email.php';
    
    $senha = generatePassword();
    
    $SqlEdicao = sprintf ( "UPDATE ipi_clientes SET senha = MD5('%s') WHERE cod_clientes='".$objBuscaEmail->cod_clientes."'", $senha);
    
    if(mysql_query($SqlEdicao)) {
      $email_origem = EMAIL_PRINCIPAL;
      $email_destino = $objBuscaEmail->email;
      $assunto = NOME_FANTASIA." - Sua nova senha";
      
      $texto .= '<br><br>Sua nova senha de acesso ao site <a href="http://'.HOST.'/" target="_blank">'.HOST.'</a> é: <b>'.$senha.'</b>';
      $texto .= "<br><br>Não esqueça de trocá-la no seu próximo acesso.";
      $arr_aux = array();
      $arr_aux['cod_pedidos'] = 0;
      $arr_aux['cod_usuarios'] = 0;
      $arr_aux['cod_clientes'] = $objBuscaEmail->cod_clientes;
      $arr_aux['cod_pizzarias'] = 0;
      $arr_aux['tipo'] = 'ESQUECI_SENHA';
      if (enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'senha_nova'))
        echo '<script> alert("Sua senha foi trocada com sucesso e uma cópia enviada por e-mail!")</script>';
      else
        echo '<script> alert("Erro ao enviar E-mail!")</script>';
    }
    else {
      echo '<script> alert("Desculpe, houve um erro ao alterar sua senha.")</script>';
    }
  } else {
    echo '<script> alert("Desculpe, o e-mail digitado não existe.")</script>';
  }
  
  desconectabd ( $con );
}
?>

<form method="post" name="frmEmail" action="<? echo $PHP_SELF; ?>" onSubmit="return validacao(this);">

  <div id="div_esqueci_senha">
    <div id="div_esqueci_senha_cont">
       <p>Digite abaixo o e-mail depois clique no botão "Enviar".</p>
        <p>Depois acesse seu email siga as instruções da mensagem enviada.</p>
        <br />
             
        <label for="email_senha">E-mail</label>
        <input class="campotext" type="text" name="email_senha" id="email_senha" size="32"/>
      
      <div class="esqueci_senha_botoes">
        <!-- <input type="submit" name="envia" class="btn btn-submit" value="Enviar Nova Senha"></label>  -->
        <br/>
        <input type="submit" name="envia"  value="Enviar Nova Senha">
        <input type="hidden" name="acao" value="enviar">
        <!-- <a href="acesso" ><img src="./img/btn_entrar.png" style="width: 72px;"></a> -->
      </div>

    </div>
  </div>
 </form>

