<?php

/**
 * nuc_paginas.php: Cadastro de Páginas
 * 
 * Índice: cod_paginas
 * Tabela: paginas, paginas_grupos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Páginas e Menus');

$acao = validaVarPost('acao');

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM nuc_paginas WHERE cod_paginas IN ($indicesSql)";
    
    if (mysql_query($SqlDel)) {
      $SqlDelPaginasFilha = "DELETE FROM nuc_paginas WHERE cod_paginas_pai IN ($indicesSql)";
      
      if (mysql_query($SqlDelPaginasFilha)) {
        mensagemOk('As páginas e menus selecionadas foram excluídas com sucesso!');
      }
      else {
        mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os páginas e menus selecionados para exclusão.');
      }
    }
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, verifique se os menus selecionados não estão atribuidos a um perfil.');
    
    desconectabd($con);
  break;
  case 'editar':
    $cod_paginas     = validaVarPost('cod_paginas');
    $menu            = texto2bd(validaVarPost('menu'));
    $arquivo         = validaVarPost('arquivo');
    $arquivo_aux1    = validaVarPost('arquivo_aux1');
    $arquivo_aux2    = validaVarPost('arquivo_aux2');
    $arquivo_aux3    = validaVarPost('arquivo_aux3');
    $tipo            = validaVarPost('tipo');
    $cod_paginas_pai = validaVarPost('cod_paginas_pai');
    $ordem           = validaVarPost('ordem');
    $habilitado      = validaVarPost('habilitado');
    
    if ($habilitado == 'on')
      $habilitado = 1;
    else
      $habilitado = 0;
    
    if($tipo != 'PAGINA')
      $arquivo = '';
      
    if($tipo == 'MENU')
      $cod_paginas_pai = 0;
    
    $con = conectabd();
    
    if($cod_paginas <= 0) {
      $SqlEdicao = sprintf("INSERT INTO nuc_paginas (menu, arquivo, arquivo_aux1, arquivo_aux2, arquivo_aux3, tipo, cod_paginas_pai, ordem, habilitado) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', %d, %d, %d)",
                           $menu, $arquivo, $arquivo_aux1, $arquivo_aux2, $arquivo_aux3, $tipo, $cod_paginas_pai, $ordem, $habilitado);
      
      if(mysql_query($SqlEdicao)) {
        mensagemOk('Menu adicionado com êxito!');
      }
      else {
        mensagemErro('Erro ao adicionar o menu', 'Por favor, comunique a equipe de suporte e informe os dados digitados.');
      }
    }
    else {
      $SqlEdicao = sprintf("UPDATE nuc_paginas SET menu = '%s', arquivo = '%s', arquivo_aux1 = '%s', arquivo_aux2 = '%s', arquivo_aux3 = '%s', tipo = '%s', cod_paginas_pai = %d, ordem = %d, habilitado = %d WHERE cod_paginas = %d", 
                           $menu, $arquivo, $arquivo_aux1, $arquivo_aux2, $arquivo_aux3, $tipo, $cod_paginas_pai, $ordem, $habilitado, $cod_paginas);
      
      if(mysql_query($SqlEdicao)) {
        mensagemOk('Menu alterado com êxito!');
      }
      else {
        mensagemErro('Erro ao alterar o menu', 'Por favor, comunique a equipe de suporte e informe os dados digitados.');
      }
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
    if (confirm("Deseja excluir os registros selecionados?\n\nATENÇÃO as páginas filhas ou submenus serão excluídos.")) {
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
    'name': 'cod_paginas',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function alteraRequerido() {
  if ($('tipo').getProperty('value') == 'PAGINA') {
    $('arquivo_legenda').addClass('requerido');
    $('arquivo').addClass('requerido');
  }
  else {
    $('arquivo_legenda').removeClass('requerido');
    $('arquivo').removeClass('requerido');
  }
}

window.addEvent('domready', function() {
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.cod_paginas.value > 0) {
    <? if ($acao == '') echo 'tabs.irpara(1);'; ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.cod_paginas.value = '';
      document.frmIncluir.menu.value = '';
      document.frmIncluir.arquivo.value = '';
      document.frmIncluir.arquivo_aux1.value = '';
      document.frmIncluir.arquivo_aux2.value = '';
      document.frmIncluir.arquivo_aux3.value = '';
      document.frmIncluir.tipo.value = '';
      document.frmIncluir.cod_paginas_pai.value = '';
      document.frmIncluir.ordem.value = '0';
      document.frmIncluir.habilitado.checked = 'checked';
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
  
  alteraRequerido();
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
            <td width="110"><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
              <td align="center">Menu</td>
              <td align="center" width="60">Habilitado</td>
              <td align="center" width="60">Protegido</td>
              <td align="center" width="40">Ordem</td>
            </tr>
          </thead>
          <tbody>
          
          <?
            function imprimeTabelaMenu($cod, $espaco) {
              $SqlBuscaPaginas = "SELECT * FROM nuc_paginas WHERE cod_paginas_pai = $cod ORDER BY ordem, menu";
              $resBuscaPaginas = mysql_query($SqlBuscaPaginas);
              $numBuscaPaginas = mysql_num_rows($resBuscaPaginas);
              
              if(($numBuscaPaginas > 0) && ($cod > 0))
                $espaco += 25;
              
              while ($objBuscaPaginas = mysql_fetch_object($resBuscaPaginas)) {
                echo '<tr>';
                
                if($objBuscaPaginas->protegido)
                  echo '<td align="center"><input type="checkbox" class="marcar disabled="disabled" excluir" name="excluir[]" value="'.$objBuscaPaginas->cod_paginas.'"></td>';
                else
                  echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaPaginas->cod_paginas.'"></td>';
                
                
                if($objBuscaPaginas->protegido)
                  echo '<td align="left" style="padding-left: '.$espaco.'px;">'.bd2texto($objBuscaPaginas->menu).'</td>';  
                else
                  echo '<td align="left" style="padding-left: '.$espaco.'px;"><a href="javascript:;" onclick="editar('.$objBuscaPaginas->cod_paginas.')">'.bd2texto($objBuscaPaginas->menu).'</a></td>';
                  
                
                if($objBuscaPaginas->habilitado)
                  echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
                else
                  echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
                  
                if($objBuscaPaginas->protegido)
                  echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
                else
                  echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
                  
                echo '<td align="center">'.$objBuscaPaginas->ordem.'</td>';
                echo '</tr>';
                
                imprimeTabelaMenu($objBuscaPaginas->cod_paginas, $espaco);
              }
            }
            
            $con = conectabd();
            imprimeTabelaMenu(0, 3);
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
          <li><a href="nuc_usuarios.php">Usuários</a></li>
          <li><a href="nuc_perfis.php">Perfis de Acesso</a></li>
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
      $cod_paginas = validaVarPost('cod_paginas', '/[0-9]+/');
      
      if($cod_paginas > 0) {
        $objBusca = executaBuscaSimples("SELECT * FROM nuc_paginas WHERE cod_paginas = ".$cod_paginas);
      } 
    ?>
    
    <form name="frmIncluir" id="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
      <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="menu">Nome do Menu, Submenu ou Página</label></td></tr>
      <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="menu" id="menu" maxlength="45" size="38" value="<? echo $objBusca->menu ?>"></td></tr>
      
      <tr><td class="legenda tdbl tdbr"><label class="requerido" for="tipo">Tipo</label></td></tr>
      <tr><td class="sep tdbl tdbr">
        <select class="requerido" name="tipo" id="tipo" onchange="alteraRequerido()">
          <option></option>
          <option value="MENU" <? if ($objBusca->tipo == 'MENU') echo 'SELECTED' ?>>Menu</option>
          <option value="SUBMENU" <? if ($objBusca->tipo == 'SUBMENU') echo 'SELECTED' ?>>Submenu</option>
          <option value="PAGINA" <? if ($objBusca->tipo == 'PAGINA') echo 'SELECTED' ?>>Página</option>
        </select>
      </td></tr>
      
      <tr><td class="legenda tdbl tdbr"><label id="arquivo_legenda" class="requerido" for="arquivo">Arquivo</label></td></tr>
      <tr><td class="sep tdbl tdbr">
        <select class="requerido" name="arquivo" id="arquivo">
          <option></option>
          
          <?
            foreach (glob('*.php') as $arquivo) {
              echo '<option value="'.$arquivo.'"';
              
              if($objBusca->arquivo == $arquivo)
                echo 'SELECTED';
              
              echo '>'.$arquivo.'</option>';
            }
          ?>
          
        </select>
      </td></tr>
      
      <tr><td class="legenda tdbl tdbr"><label for="arquivo_aux1">Arquivo Auxiliar 1</label></td></tr>
      <tr><td class="tdbl tdbr">
        <select name="arquivo_aux1" id="arquivo_aux1">
          <option></option>
          
          <?
            foreach (glob('*.php') as $arquivo) {
              echo '<option value="'.$arquivo.'"';
              
              if($objBusca->arquivo_aux1 == $arquivo)
                echo 'SELECTED';
              
              echo '>'.$arquivo.'</option>';
            }
          ?>
          
        </select>
      </td></tr>
      
      <tr><td class="legenda tdbl tdbr"><label for="arquivo_aux2">Arquivo Auxiliar 2</label></td></tr>
      <tr><td class="tdbl tdbr">
        <select name="arquivo_aux2" id="arquivo_aux2">
          <option></option>
          
          <?
            foreach (glob('*.php') as $arquivo) {
              echo '<option value="'.$arquivo.'"';
              
              if($objBusca->arquivo_aux2 == $arquivo)
                echo 'SELECTED';
              
              echo '>'.$arquivo.'</option>';
            }
          ?>
          
        </select>
      </td></tr>
      
      <tr><td class="legenda tdbl tdbr"><label for="arquivo_aux3">Arquivo Auxiliar 3</label></td></tr>
      <tr><td class="sep tdbl tdbr">
        <select name="arquivo_aux3" id="arquivo_aux3">
          <option></option>
          
          <?
            foreach (glob('*.php') as $arquivo) {
              echo '<option value="'.$arquivo.'"';
              
              if($objBusca->arquivo_aux3 == $arquivo)
                echo 'SELECTED';
              
              echo '>'.$arquivo.'</option>';
            }
          ?>
          
        </select>
      </td></tr>
      
      <tr><td class="legenda tdbl tdbr"><label class="requerido" for="cod_paginas_pai">Menu ou Submenu Pai</label></td></tr>
      <tr><td class="sep tdbl tdbr">
        <select class="requerido" name="cod_paginas_pai" id="cod_paginas_pai">
          <option></option>
          <option value="0" <? if(($objBusca->cod_paginas_pai == 0) && ($objBusca->cod_paginas_pai != '')) echo 'SELECTED'; ?>>RAIZ</option>
          
          <?
            $con = conectabd();
            
            $SqlBuscaMenus = "SELECT * FROM nuc_paginas WHERE tipo in ('MENU', 'SUBMENU') ORDER BY ordem, menu";
            $resBuscaMenus = mysql_query($SqlBuscaMenus);
            
            while ($objBuscaMenus = mysql_fetch_object($resBuscaMenus)) {
              echo '<option value="'.$objBuscaMenus->cod_paginas.'" ';
              
              if($objBusca->cod_paginas_pai == $objBuscaMenus->cod_paginas)
                echo 'SELECTED';
              
              echo '>'.bd2texto($objBuscaMenus->menu).'</option>';
            }
            
            desconectabd($con);
          ?>
          
        </select>
      </td></tr>
      
      <tr><td class="legenda tdbl tdbr"><label class="requerido" for="ordem">Ordem</label></td></tr>
      <tr><td class="sep tdbl tdbr">
        <select class="requerido" name="ordem" id="ordem">
          <option></option>
          
          <?
            for($i = -20; $i <= 10; $i++) {
              echo '<option value="'.$i.'"';
              
              if($objBusca->ordem == $i)
                echo 'SELECTED';
              
              echo '>'.$i.'</option>';
            }
          ?>
          
        </select>
      </td></tr>
      
      <tr><td class="legenda sep tdbl tdbr">
      
      <?
        if($cod_paginas > 0) {
          echo '<input type="checkbox" name="habilitado" id="habilitado" ';
          
          if($objBusca->habilitado)
            echo 'checked="checked"';
          
          echo '>';
        }
        else {
          echo '<input type="checkbox" name="habilitado" id="habilitado" checked="checked">';
        }
      ?>
      
        <label for="habilitado">Habilitado</label>
      </td></tr>
      
      <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="cod_paginas" value="<? echo $objBusca->cod_paginas ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>