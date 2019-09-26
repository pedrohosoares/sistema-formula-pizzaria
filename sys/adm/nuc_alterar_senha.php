<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Alterar Senha');

$acao = validaVarPost('acao');

switch($acao) {
  case 'editar':
    $senha_atual = validaVarPost('senha_atual');
    $senha_nova = validaVarPost('senha_nova');
    $confirmacao = validaVarPost('confirmacao');
    
    if($senha_nova != $confirmacao) {
      mensagemErro('Erro', 'A senha nova não coencide com o campo confirmação.');
    }
    else {
      $con = conectabd();
      
      $SqlBuscaUsuario = sprintf('SELECT COUNT(cod_usuarios) AS quantidade FROM nuc_usuarios WHERE cod_usuarios = %d AND senha = MD5(\'%s\')', $_SESSION['usuario']['codigo'], $senha_atual);
      $objUsuario = executaBuscaSimples($SqlBuscaUsuario, $con);
      
      if ($objUsuario->quantidade > 0) {
        $SqlUpdateSenha = sprintf('UPDATE nuc_usuarios SET senha = MD5(\'%s\') WHERE cod_usuarios = %d', $senha_nova, $_SESSION['usuario']['codigo']);
        
        if(mysql_query($SqlUpdateSenha)) {
          mensagemOk('Senha alterada com sucesso!');
        }
      }
      else {
        mensagemErro('Erro', 'A senha atual digitada não confere com a senha cadastrada no sistema.');
      }
      
      desconectabd($con);
    }
  break;
}

?>

<form name="frmTroca" method="post" onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

  <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="senha_atual">Senha Atual</label></td></tr>
  <tr><td class="sep tdbl tdbr"><input class="requerido" type="password" name="senha_atual" id="senha_atual" maxlength="45" size="30"></td></tr>
  
  <tr><td class="legenda tdbl tdbr"><label class="requerido" for="senha_nova">Senha Nova</label></td></tr>
  <tr><td class="tdbl tdbr"><input class="requerido" type="password" name="senha_nova" id="senha_nova" maxlength="45" size="30"></td></tr>
  
  <tr><td class="legenda tdbl tdbr"><label class="requerido" for="confirmacao">Confirmação</label></td></tr>
  <tr><td class="sep tdbl tdbr"><input class="requerido" type="password" name="confirmacao" id="confirmacao" maxlength="45" size="30"></td></tr>
  
  <tr><td align="center" class="tdbl tdbb tdbr"><input class="botao" type="submit" name="trocar" value="Trocar Senha"></td></tr>

</table>

<input type="hidden" name="acao" value="editar">

</form>

<? rodape(); ?>