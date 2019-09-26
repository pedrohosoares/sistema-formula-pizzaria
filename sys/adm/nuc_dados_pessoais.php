<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Dados Pessoais');

$acao = validaVarPost('acao');

switch($acao) {
  case 'editar':
    $nome = validaVarPost('nome');
    $email = validaVarPost('email');
    
    $con = conectabd();
    
    $SqlUpdateDados = sprintf('UPDATE nuc_usuarios SET nome = \'%s\', email = \'%s\' WHERE cod_usuarios = %d', $nome, $email, $_SESSION['usuario']['codigo']);
      
    if(mysql_query($SqlUpdateDados)) {
      mensagemOk('Dados alterados com sucesso!');
      
      // Define as variáveis de sessão, veja o arquivo sessao.php
      defineNome($nome);
      defineEmail($email);
    }
    else {
      mensagemErro('Erro', 'Ocorreu um erro ao atualizar os dados, por favor, verifique os valores digitados.');
    }
    
    desconectabd($con);
  break;
}

if($_SESSION['usuario']['codigo'] > 0) {
  $SqlBusca = "SELECT * FROM nuc_usuarios WHERE cod_usuarios = ".$_SESSION['usuario']['codigo'];
  $objBusca = executaBuscaSimples($SqlBusca);
}

?>

<form name="frmDados" method="post" onsubmit="return validaRequeridos(this)">
    
<table align="center" class="caixa" cellpadding="0" cellspacing="0">

<tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="nome">Nome Completo</label></td></tr>
<tr><td class="sep tdbl tdbr"><input class="requerido" type="text" name="nome" id="nome" maxlength="45" size="30" value="<? echo $objBusca->nome ?>"></td></tr>

<tr><td class="legenda tdbl tdbr"><label class="requerido" for="email">E-mail</label></td></tr>
<tr><td class="sep tdbl tdbr"><input class="requerido" type="text" name="email" id="email" maxlength="80" size="30" value="<? echo $objBusca->email ?>"></td></tr>

<tr><td align="center" class="tdbl tdbb tdbr"><input class="botao" type="submit" name="alterar" value="Alterar Dados"></td></tr>

</table>

<input type="hidden" name="acao" value="editar">

</form>

<? rodape(); ?>