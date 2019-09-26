<?php

/**
 * ine_emails_envio.php: Cadastro de E-mails para envio.
 * 
 * Índice: cod_emails_envio
 * Tabela: ine_emails_envio
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de E-Mails para Envio');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_emails_envio';
$tabela = 'ine_emails_envio';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlDel))
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $email = texto2bd(validaVarPost('email'));
    $nome = texto2bd(validaVarPost('nome'));
    $ativo = (validaVarPost('ativo') == 'on') ? 1 : 0;
    $login = texto2bd(validaVarPost('login'));
    $smtp = texto2bd(validaVarPost('smtp'));
    $porta_smtp = texto2bd(validaVarPost('porta_smtp'));
    $senha = texto2bd(validaVarPost('senha'));
    $numero_envios = validaVarPost('numero_envios');
    
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (email, nome, ativo, login, smtp, porta_smtp , senha, numero_envios) VALUES ('%s', '%s', %d, '%s', '%s', '%s', '%s', '%s')", 
                           $email, $nome, $ativo, $login, $smtp, $porta_smtp, $senha, $numero_envios);

      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET email = '%s', nome = '%s', ativo = '%d', login = '%s', smtp = '%s', porta_smtp = '%s', senha = '%s', numero_envios = '%s'  WHERE $chave_primaria = $codigo", 
                           $email, $nome, $ativo, $login, $smtp, $porta_smtp, $senha, $numero_envios);

      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    
    desconectabd($con);
  break;
}

?>
<script src="../lib/js/mascara.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

<script>

function verificaCheckbox(form) {
  var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
   
  if(cInput > 0) {
    if (confirm('Deseja excluir os registros selecionados?')) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja excluir.');
     
    return false;
  }
}

function editar(cod) {
  var form = new Element('form', {
    'action': '<? echo $_SERVER['PHP_SELF'] ?>',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': '<? echo $chave_primaria ?>',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function validaFormEmail(form) {
  if (!validarEmail(form.email.value)) {
    alert('E-mail inválido.');
    return false;
  }
  
  return true;
}


window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) {
    <? if ($acao == '') echo 'tabs.irpara(1);'; ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.<? echo $chave_primaria ?>.value = '';
      document.frmIncluir.email.value = '';
      document.frmIncluir.nome.value = '';
      document.frmIncluir.login.value = '';
      document.frmIncluir.smtp.value = '';
      document.frmIncluir.porta_smtp.value = '';
      document.frmIncluir.senha.value = '';
      document.frmIncluir.numero_envios.value = '';
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
});

</script>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a href="javascript:;">Editar</a></li>
       <li><a href="javascript:;">Incluir</a></li>
    </ul>
  </div>
    
  <!-- Tab Editar -->
  <div class="painelTab">
    <table><tr>
  
    <!-- Conteúdo -->
    <td class="conteudo">
    
      <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">
    
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
          <tr>
            <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
              <td align="center">E-Mail</td>
              <td align="center">Nome</td>
              <td align="center">Login</td>
              <td align="center">SMTP</td>
              <td align="center">Porta SMTP</td>
              <td align="center">Ativo</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT * FROM $tabela ORDER BY nome";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->email).'</a></td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->nome).'</td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->login).'</td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->smtp).'</td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->porta_smtp).'</td>';
            echo '<td align="center">';
              if ($objBuscaRegistros->ativo == 1) 
                 echo '<img src="../lib/img/principal/ok.gif">';
              else 
                 echo '<img src="../lib/img/principal/erro.gif"></td>';
                 
           
            echo '</tr>';
          
          
          }
          
          desconectabd($con);
          
          ?>
          
          </tbody>
        </table>
      
        <input type="hidden" name="acao" value="excluir">
      </form>
    
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="ine_emails_cadastro.php">E-Mails</a></li>
          <li><a href="ine_mensagens.php">Mensagens</a></li>
          <li><a href="ine_imagens.php">Imagens</a></li>
        </ul>
      </div>
    </td>
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Editar -->
  
  
  
  <!-- Tab Incluir -->
  <div class="painelTab">
    <? 
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    
    if($codigo > 0) {
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    } 
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaFormEmail(this)&& validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="email">E-Mail</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="email" id="email" maxlength="45" size="45" value="<? echo bd2texto($objBusca->email) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="nome">Nome</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="nome" id="nome" maxlength="45" size="45" value="<? echo bd2texto($objBusca->nome) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="login">Login</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="login" id="login" maxlength="45" size="45" value="<? echo bd2texto($objBusca->login) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="senha">Senha:</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="password" name="senha" id="senha" maxlength="45" size="45" value="<? echo bd2texto($objBusca->senha) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="smtp">SMTP</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="smtp" id="smtp" maxlength="45" size="45"  value="<? echo bd2texto($objBusca->smtp) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="porta_smtp">Porta SMTP</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="porta_smtp" id="porta_smtp" maxlength="45" size="45" onKeyPress="return ApenasNumero(event)" value="<? echo bd2texto($objBusca->porta_smtp) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="numero_envios">Numero de Envios</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="numero_envios" id="numero_envios" maxlength="45" size="45"  value="<? echo bd2texto($objBusca->numero_envios) ?>"></td></tr>
        
    <tr><td class="legenda tdbl tdbr sep"><input class="requerido" type="checkbox" name="ativo" id="ativo" <? if($objBusca->ativo) echo 'checked'; ?>>&nbsp;<label for="ativo">Habilitado</label></td></tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>