<?
require_once 'ipi_session.php';
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';
  
$acao = validaVarPost('acao', '/alterar/');

if($acao == 'alterar') 
{
  $codigo = $_SESSION['ipi_cliente']['codigo'];
  $nascimento = validaVarPost('nascimento');
  $nome = validaVarPost('nome');
  $celular = validaVarPost('celular');
  $email = validaVarPost('email');
  $sexo = validaVarPost('sexo');

  if($codigo > 0) 
  {
    $con = conectabd();

    $sql_trocou_email = "SELECT * FROM ipi_clientes WHERE cod_clientes = '".$codigo."' AND email='".$email."'";
    //echo "<br />sql_trocou_email: ".$sql_trocou_email;
    $res_trocou_email = mysql_query($sql_trocou_email);
    $num_trocou_email = mysql_num_rows($res_trocou_email);
    if ($num_trocou_email==0) // Se for igual a zero, significa que trocou o email.
    {
      $sql_verificar_email = "SELECT * FROM ipi_clientes WHERE cod_clientes <> '".$codigo."' AND email='".$email."'";
      //echo "<br />sql_verificar_email: ".$sql_verificar_email;
      $res_verificar_email = mysql_query($sql_verificar_email);
      $num_verificar_email = mysql_num_rows($res_verificar_email);
      if ($num_verificar_email==0) // Se for igual a zero, significa que pode trocar o email.
      {
        $sql_trocar_email = sprintf( "UPDATE ipi_clientes SET email = '%s' WHERE cod_clientes = $codigo", texto2bd($email) );
        //echo "<br />sql_trocar_email: ".$sql_trocar_email;
        $res_trocar_email = mysql_query($sql_trocar_email);
      }
      else  // se tiver resultado alguem já utiliza este email.
      {
        //echo '<p style="text-align: center; color: #FF0000;">';
        echo '<span style="color: #FF0000;"><strong>Erro ao alterar e-mail!</strong>';
        echo '<br />O E-Mail (<strong>'.$email.'</strong>) já está cadastrado no sistema.';
        echo '</span>';
        //echo '</p>';
      }
    }
   
    $SqlEdicao = sprintf("UPDATE ipi_clientes SET nome = '%s', celular = '%s', nascimento = '%s', sexo = '%s' WHERE cod_clientes = $codigo", texto2bd($nome), $celular, data2bd($nascimento), $sexo);
    $ResEdicao = mysql_query($SqlEdicao);

    if ($num_verificar_email==0)
    {
      if($ResEdicao) 
      {
        $_SESSION['ipi_cliente']['nome'] = $nome;
        //echo '<p style="text-align: center; color: #FF0000;">';
        echo '<h1>Cadastro alterado com sucesso!</h1>';
        //echo '</p>';
        //echo '<p><a href="pedidos">Clique aqui</a> e comece a pedir a sua pizza agora mesmo!</p>';
      }
      else 
      {
        //echo '<p style="text-align: center; color: #FF0000;">';
        echo '<h1><span style="color: #FF0000;">Erro ao alterar o cadastro!</span></h1>';
        //echo '</p>';
      }
    }
    
    desconectabd($con);
  }
}
?>

<script type="text/javascript" src="sys/lib/js/mascara.js"></script>
<script type="text/javascript">
  
function validaForm(form) {

  if(form.email.value == '') {
    alert('Campo e-mail obrigatório.');
    form.email.focus();
    return false;
  }
  
  if(!validarEmail(form.email.value)) {
    alert('O campo e-mail não é válido ou não foi digitado corretamente.');
    form.email.focus();
    return false;
  }

  if(form.nome.value == '') {
    alert('Campo nome obrigatório.');
    form.nome.focus();
    return false;
  }


  return true;
}

</script>

<?
$codigo = $_SESSION['ipi_cliente']['codigo'];

if($codigo > 0) {
  $objBusca = executaBuscaSimples("SELECT * FROM ipi_clientes WHERE cod_clientes = $codigo");
}
?>
<br/>
<form id="enviar_meus_dados" action="" method="post" onsubmit="return validaForm(this);">
	<div id="meus_dados_cont" class="divcadastro" style="font-size: 15px; padding-left: 25px;"><br/>
      
  	  <label for="email">E-mail:</label><br/>
  	  <input type="text" name="email" id="email" class="campotextcf" value="<? echo bd2texto($objBusca->email) ?>" /><br/><br/>
    
	
    
  	  <label for="nome">Nome Completo:</label><br/>
  	  <input type="text" name="nome" id="nome" class="campotextcf" value="<? echo bd2texto($objBusca->nome) ?>" /><br/><br/>
    

    
      <label for="sexo" title="Sexo">Sexo:</label><br/>
      <select name="sexo" id="sexo" style="width: 113px" class="form-control">
        <option value=""></option>
        <option value="M" <? echo ($objBusca->sexo == "M" ? "selected = selected" : ""); ?>>Masculino</option>
        <option value="F" <? echo ($objBusca->sexo == "F" ? "selected = selected" : ""); ?>>Feminino</option>
      </select><br/><br/>
  	

    
  	  <label for="nascimento">Nascimento (DD/MM/AAAA):</label><br/>
  	  <input name="nascimento" id="nascimento" class="campotextcf" type="text"  value="<? echo bd2data($objBusca->nascimento) ?>" onkeypress="return MascaraData(this, event);"/><span class="fonte10">Responda e concorra a brindes</span><br/><br/>
  	

 
  	  <label for="celular">Celular:</label><br/>
  	  <input name="celular" id="celular" class="campotextcf" type="text" value="<? echo $objBusca->celular ?>" onkeypress="return MascaraTelefone(this, event);"/><br /><br />
  
      <div>
        <a onclick="$('#enviar_meus_dados').submit()"  class="btn btn-secondary" style="width:80px; display:inline">Alterar</a>
    <a href="meu_home" class="btn btn-secondary"style="width:80px; display:inline" >Cancelar</a>
      </div>
	  
	
		<input type="hidden" name="acao" value="alterar" />
  </div>
</form>

<div class="bottom_div"></div>