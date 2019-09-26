<?php

/**
 * nuc_palavras_chave.php: Cadastro de Palavras Chaves
 * 
 * Índice: cod_banco_palavras
 * Tabela: nuc_banco_palavras
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Banco de Palavras');

$acao = validaVarPost('acao');

$tabela = 'nuc_banco_palavras';
$chave_primaria = 'cod_banco_palavras';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlDel) )
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $palavra = texto2bd(validaVarPost('palavra'));
    $palavra_antiga = texto2bd(validaVarPost('palavra_antiga'));
    $texto = texto2bd(validaVarPost('texto'));
    $protegido = validaVarPost('protegido');
    
    if($protegido == 1) {
      $palavra = $palavra_antiga;
    }
    
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (palavra, texto) VALUES ('%s', '%s')", 
                           $palavra, $texto);

      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET palavra = '%s', texto = '%s' WHERE $chave_primaria = $codigo", 
                           $palavra, $texto);

      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    
    desconectabd($con);
  break;
}

?>

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
      document.frmIncluir.palavra.value = '';
      document.frmIncluir.palavra_antiga.value = '';
      document.frmIncluir.palavra.disabled = false;
      document.frmIncluir.texto.value = '';
      document.frmIncluir.protegido.value = 0;
      
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
              <td align="center">Palavra Chave</td>
              <td align="center">Texto</td>
              <td align="center">Protegido</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT * FROM $tabela ORDER BY palavra";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
          
            if($objBuscaRegistros->protegido == 1) 
              echo '<td align="center"><input type="checkbox" disabled="disabled" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            else
              echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';

            echo '<td align="center" width="150"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->palavra).'</a></td>';
            
            if(strlen($objBuscaRegistros->texto) >= 100)
              echo '<td align="left">'.substr($objBuscaRegistros->texto, 0, 100).'...</td>';
            else
              echo '<td align="left">'.$objBuscaRegistros->texto.'</td>';

            if($objBuscaRegistros->protegido == 1)
              echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
            else
              echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
            
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
          <li><a href="#">Atalho 1</a></li>
          <li><a href="#">Atalho 2</a></li>
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
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="palavra">Palavra-Chave</label></td></tr>
    
    <? if($objBusca->protegido == 1): ?>
    <tr><td class="tdbl tdbr sep"><input disabled="disabled" class="requerido" type="text" name="palavra" id="palavra" maxlength="20" size="45" value="<? echo bd2texto($objBusca->palavra)  ?>"></td></tr>
    <? else: ?>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="palavra" id="palavra" maxlength="20" size="45" value="<? echo bd2texto($objBusca->palavra)  ?>"></td></tr>
    <? endif; ?>
    
    <input type="hidden" name="palavra_antiga" value="<? echo bd2texto($objBusca->palavra) ?>" >   
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="texto">Texto</label></td></tr>
    <tr><td class="tdbl tdbr sep"><textarea cols="90" rows="20" class="requerido" name="texto" id="texto"><? echo texto2bd($objBusca->texto) ?></textarea></td></tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="protegido" value="<? echo $objBusca->protegido ?>">
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>