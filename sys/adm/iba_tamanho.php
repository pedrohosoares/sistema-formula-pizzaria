<?php

/**
 * iba_tamanho.php: Cadastro de Tamanhos de Banners
 * 
 * Índice: cod_tamanhos
 * Tabela: iba_tamanhos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Tamanhos de Banners');

$acao = validaVarPost('acao');

$tabela = 'iba_tamanhos';
$chave_primaria = 'cod_tamanhos';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if(mysql_query($SqlDel)) {
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');  
    }
    else {
      mensagemErro('Erro ao excluir os registros', 'Por favor, verifique se não há banners cadastrados com este tamanho.');
    }
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $altura = validaVarPost('altura', '/[0-9]+/');
    $largura = validaVarPost('largura', '/[0-9]+/');
    
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (altura, largura) VALUES (%d, %d)", 
                           $altura, $largura);

      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET altura = %d, largura = %d WHERE $chave_primaria = $codigo", 
                           $altura, $largura);

      if(mysql_query($SqlEdicao))
        mensagemOk('Registro alterado com êxito!');
      else
        mensagemErro('Erro ao alterado o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
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
      document.frmIncluir.altura.value = '';
      document.frmIncluir.largura.value = '';
      
      // Limpando a imagem de referência
      if($('ref_img_legenda')) {
        $('ref_img_legenda').destroy();
        $('ref_img').destroy();
      }
      
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
              <td align="center">Tamanho (L x A)</td>
              <td align="center" width="60">Código</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaTamanhos = "SELECT * FROM $tabela ORDER BY altura, largura";
          $resBuscaTamanhos = mysql_query($SqlBuscaTamanhos);
          
          while ($objBuscaTamanhos = mysql_fetch_object($resBuscaTamanhos)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaTamanhos->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaTamanhos->$chave_primaria.')">'.$objBuscaTamanhos->largura .' x '.$objBuscaTamanhos->altura.'</a></td>';
            echo '<td align="center">'.$objBuscaTamanhos->$chave_primaria.'</td>';
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
          <li><a href="iba_banner.php">Banners</a></li>
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
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="altura">Altura</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="altura" id="altura" maxlength="10" size="10" value="<? echo $objBusca->altura ?>" onkeypress="return ApenasNumero(event)"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="largura">Largura</label></td></tr>
    <tr><td class="sep tdbl tdbr"><input class="requerido" type="text" name="largura" id="largura" maxlength="10" size="10" value="<? echo $objBusca->largura ?>" onkeypress="return ApenasNumero(event)"></td></tr>

    <? if($codigo > 0):?>

    <tr><td class="legenda tdbl tdbr" id="ref_img_legenda"><label>Banner de Referência</label></td></tr>
    <tr><td class="sep tdbl tdbr" id="ref_img"><img src="iba_gera_img_referencia.php?altura=<? echo $objBusca->altura ?>&largura=<? echo $objBusca->largura ?>"></td></tr>
    
    <? endif; ?>
    
    <tr><td colspan="2" align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>