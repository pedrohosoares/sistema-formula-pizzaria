<?
require_once 'ipi_session.php';
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';

$acao = validaVarPost('acao', '/inserir/');

?>

<script type="text/javascript">

function validaForm(form) {
  if(form.senha_antiga.value == '') {
    alert('Campo Senha Antiga obrigatório.');
    form.senha_antiga.focus();
    return false;
  }
  
  if(form.senha_nova.value == '') {
    alert('Campo Senha Nova obrigatório.');
    form.senha_nova.focus();
    return false;
  }
  
  if(form.confirmacao.value == '') {
    alert('Campo Confirmação obrigatório.');
    form.confirmacao.focus();
    return false;
  }
  
  if(form.senha_nova.value != form.confirmacao.value) {
    alert('As senhas, Senha Nova e Confirmação, devem ser identicas.');
    form.senha_nova.focus();
    return false;
  }
  
  return true;
}

</script>

<form id="frmAlterarSenha" action="" method="post" onsubmit="return validaForm(this);">
	<div id="div_alterar_tudo" class="divbranco" style="font-size: 15px; padding-left: 25px;">
	  &nbsp;<br />

    
	  <label for="senha_antiga">Senha Atual:</label> <br/>
	  <input name="senha_antiga" id="senha_antiga" class="campotextcf" type="password" />
    <br/><br/>
 
	  <label for="senha_nova">Nova Senha:</label><br/>
	  <input name="senha_nova" id="senha_nova" class="campotextcf" type="password" />  
    <br/><br/>

	  <label for="confirmacao">Confirmar Senha:</label><br/>
	  <input name="confirmacao" id="confirmacao" class="campotextcf" type="password" />
    <br/><br/>

	 <input type="submit" alt="Botão para Alterar a sua Senha" value="Alterar" class="btn btn-primary" />
	
	<input type="hidden" name="acao" value="inserir"/>
	</div>
</form>

<div class="bottom_div"></div>


<?
if($acao == 'inserir') 
{
  $senha_antiga = validaVarPost('senha_antiga');
  $senha_nova = validaVarPost('senha_nova');
  $confirmacao = validaVarPost('confirmacao');
  
  $con = conectabd();
  
  $SqlBuscaSenha = sprintf("SELECT * FROM ipi_clientes WHERE email = '%s' AND senha = MD5('%s')",
                           $_SESSION['ipi_cliente']['email'], $senha_antiga);
                           
  $resBuscaSenha = mysql_query($SqlBuscaSenha);
  $numBuscaSenha = mysql_num_rows($resBuscaSenha);
  $objBuscaSenha = mysql_fetch_object($resBuscaSenha);
  
  if($numBuscaSenha > 0) 
  {
    $SqlEdicao = sprintf("UPDATE ipi_clientes set senha = MD5('%s') WHERE cod_clientes = %d",
                         $senha_nova, $objBuscaSenha->cod_clientes);
    
    if(mysql_query($SqlEdicao)) 
    {
      echo '<script>alert("Senha alterada com sucesso...")</script>';
    }
    else 
    {
      echo '<script>alert("Erro ao alterar a senha...\n Ocorreu um erro interno do sistema. ")</script>';
    }
  }
  else 
  {
    echo '<script>alert("Senha antiga incorreta, por favor, tente novamente.");</script>';
  }
  
  desconectabd($con);
} 
?>
