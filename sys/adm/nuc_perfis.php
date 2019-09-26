<?php

/**
 * nuc_perfis.php: Cadastro de Perfis
 * 
 * Índice: cod_pergis
 * Tabela: nuc_perfis
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Perfis de Acesso');

$acao = validaVarPost('acao');

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlUpdateUsuarios = "UPDATE nuc_usuarios SET cod_perfis = NULL WHERE cod_perfis IN ($indicesSql)";
    $resUpdateUsuarios = mysql_query($SqlUpdateUsuarios);
    
    if($resUpdateUsuarios) {
      $SqlDel1 = "DELETE FROM nuc_perfis WHERE cod_perfis IN ($indicesSql)";
      $SqlDel2 = "DELETE FROM nuc_paginas_nuc_perfis WHERE cod_perfis IN ($indicesSql)";
      
      $resDel2 = mysql_query($SqlDel2);
      $resDel1 = mysql_query($SqlDel1);
      
      if ($resDel1 && $resDel2)
        mensagemOk('Os registros selecionados foram excluídos com sucesso!');
      else
        mensagemErro('Erro ao excluir os registros', 'Por favor, verifique se algum usuário se encontra cadastrado com este perfil.');
    }
    else {
      mensagemErro('Erro ao excluir os registros', 'Por favor, verifique se algum usuário se encontra cadastrado com este perfil.');
    }
    
    desconectabd($con);
  break;
  case 'editar':
    $cod_perfis = validaVarPost('cod_perfis');
    $perfil     = texto2bd(validaVarPost('perfil'));
    $acesso     = validaVarPost('acesso');
    $inserir    = validaVarPost('inserir');
    $apagar     = validaVarPost('apagar');
    $editar     = validaVarPost('editar');
    
    $con = conectabd();
    
    if($cod_perfis <= 0) {
      $SqlEdicao = sprintf("INSERT INTO nuc_perfis (perfil) VALUES ('%s')", $perfil);

      if(mysql_query($SqlEdicao)) {
        $cod_perfis = mysql_insert_id();
        
        $resEdicaoAcesso  = true;
        $resEdicaoInserir = true;
        $resEdicaoApagar  = true;
        $resEdicaoEditar  = true;
        
        foreach ($acesso as $cor_acesso) {
          $SqlEdicaoAcesso = sprintf("INSERT INTO nuc_paginas_nuc_perfis (cod_perfis, cod_paginas) VALUES (%d, %d)", $cod_perfis, $cor_acesso);
          $resEdicaoAcesso &= mysql_query($SqlEdicaoAcesso);
        }
        
        foreach ($inserir as $cor_inserir) {
          $SqlEdicaoInserir = sprintf("UPDATE nuc_paginas_nuc_perfis SET inserir = 1 WHERE cod_perfis = %d AND cod_paginas = %d", $cod_perfis, $cor_inserir);
          $resEdicaoInserir &= mysql_query($SqlEdicaoInserir);
        }
        
        foreach ($apagar as $cor_apagar) {
          $SqlEdicaoApagar = sprintf("UPDATE nuc_paginas_nuc_perfis SET apagar = 1 WHERE cod_perfis = %d AND cod_paginas = %d", $cod_perfis, $cor_apagar);
          $resEdicaoApagar &= mysql_query($SqlEdicaoApagar);
        }
        
        foreach ($editar as $cor_editar) {
          $SqlEdicaoEditar = sprintf("UPDATE nuc_paginas_nuc_perfis SET editar = 1 WHERE cod_perfis = %d AND cod_paginas = %d", $cod_perfis, $cor_editar);
          $resEdicaoEditar &= mysql_query($SqlEdicaoEditar);
        }
        
        if($resEdicaoAcesso && $resEdicaoInserir && $resEdicaoApagar && $resEdicaoEditar)
          mensagemOk('Registro adicionado com êxito!');
        else
          mensagemErro('Erro ao adicionar o Registro', 'Por favor, verifique se o perfil já não se encontra cadastrado.');
      }
      else {
        mensagemErro('Erro ao adicionar o Registro', 'Por favor, verifique se o perfil já não se encontra cadastrado.');
      }
    }
    else {
      $SqlEdicao = sprintf("UPDATE nuc_perfis SET perfil = '%s' WHERE cod_perfis = %d", 
                           $perfil, $cod_perfis);
                         
      if(mysql_query($SqlEdicao)) {
        $SqlDelAcesso = sprintf("DELETE FROM nuc_paginas_nuc_perfis WHERE cod_perfis = %d", 
                                $cod_perfis);
        $resDelAcesso = mysql_query($SqlDelAcesso);
      
        if($resDelAcesso) {
          $resEdicaoAcesso  = true;
          $resEdicaoInserir = true;
          $resEdicaoApagar  = true;
          $resEdicaoEditar  = true;
          
          if(is_array($acesso))
            foreach ($acesso as $cor_acesso) {
              $SqlEdicaoAcesso = sprintf("INSERT INTO nuc_paginas_nuc_perfis (cod_perfis, cod_paginas) VALUES (%d, %d)", $cod_perfis, $cor_acesso);
              $resEdicaoAcesso &= mysql_query($SqlEdicaoAcesso);
            }
          
          if(is_array($inserir))
            foreach ($inserir as $cor_inserir) {
              $SqlEdicaoInserir = sprintf("UPDATE nuc_paginas_nuc_perfis SET inserir = 1 WHERE cod_perfis = %d AND cod_paginas = %d", $cod_perfis, $cor_inserir);
              $resEdicaoInserir &= mysql_query($SqlEdicaoInserir);
            }
          
          if(is_array($apagar))
            foreach ($apagar as $cor_apagar) {
              $SqlEdicaoApagar = sprintf("UPDATE nuc_paginas_nuc_perfis SET apagar = 1 WHERE cod_perfis = %d AND cod_paginas = %d", $cod_perfis, $cor_apagar);
              $resEdicaoApagar &= mysql_query($SqlEdicaoApagar);
            }
          
          if(is_array($editar))
            foreach ($editar as $cor_editar) {
              $SqlEdicaoEditar = sprintf("UPDATE nuc_paginas_nuc_perfis SET editar = 1 WHERE cod_perfis = %d AND cod_paginas = %d", $cod_perfis, $cor_editar);
              $resEdicaoEditar &= mysql_query($SqlEdicaoEditar);
            }
          
          if($resEdicaoAcesso && $resEdicaoInserir && $resEdicaoApagar && $resEdicaoEditar)
            mensagemOk('Registro alterado com êxito!');
          else
            mensagemErro('Erro ao alterar o registro', 'Por favor, Por favor, comunique a equipe de suporte informando todos os dados cadastrados.');
        }
        else {
          mensagemErro('Erro ao alterar o registro', 'Por favor, Por favor, comunique a equipe de suporte informando todos os dados cadastrados.');
        }
      }
      else
        mensagemErro('Erro ao alterar o usuário', 'Por favor, Por favor, comunique a equipe de suporte informando todos os dados cadastrados.');
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
    'name': 'cod_perfis',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function marcarTodosPai(cod_pai, cod, estado) {
  //marcaTodosEstado
  
  
  
}

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.cod_perfis.value > 0) {
    <? if ($acao == '') echo 'tabs.irpara(1);'; ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.cod_perfis.value = '';
      document.frmIncluir.perfil.value = '';
      marcaTodosEstado('marcar', false);
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
});

</script>

<style>
tr.foco:hover {
    background: #ccc;
}
</style>

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
              <td align="center">Perfil</td>
              <td align="center">Quantidade de Usuários</td>
              <td align="center">Quantidade de Páginas</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaPerfis = "SELECT * FROM nuc_perfis ORDER BY perfil";
          $resBuscaPerfis = mysql_query($SqlBuscaPerfis);
          
          while ($objBuscaPerfis = mysql_fetch_object($resBuscaPerfis)) {
            echo '<tr>';
            
            if($objBuscaPerfis->perfil == 'Administrador')
              echo '<td align="center"><input type="checkbox" disabled="disabled" class="marcar excluir" name="excluir[]" value="'.$objBuscaPerfis->cod_perfis.'"></td>';
            else
              echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaPerfis->cod_perfis.'"></td>';
            
            
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaPerfis->cod_perfis.')">'.bd2texto($objBuscaPerfis->perfil).'</a></td>';
            
            $SqlBuscaQuantUsuarios = "SELECT COUNT(*) AS quantidade FROM nuc_usuarios WHERE cod_perfis = ".$objBuscaPerfis->cod_perfis;
            $objBuscaQuantUsuarios = executaBuscaSimples($SqlBuscaQuantUsuarios, $con);
            
            echo '<td align="center">'.$objBuscaQuantUsuarios->quantidade.'</td>';
            
            $SqlBuscaQuantPaginas = "SELECT COUNT(*) AS quantidade FROM nuc_paginas_nuc_perfis pg INNER JOIN nuc_paginas p ON (pg.cod_paginas = p.cod_paginas) WHERE p.tipo = 'PAGINA' AND pg.cod_perfis = ".$objBuscaPerfis->cod_perfis;
            $objBuscaQuantPaginas = executaBuscaSimples($SqlBuscaQuantPaginas, $con);
            
            echo '<td align="center">'.$objBuscaQuantPaginas->quantidade.'</td>';
            
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
          <li><a href="nuc_usuarios.php">Usuários</a></li>
          <li><a href="nuc_paginas.php">Páginas e Menus</a></li>
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
    $cod_perfis = validaVarPost('cod_perfis', '/[0-9]+/');
    
    if($cod_perfis > 0) {
      $objBusca = executaBuscaSimples("SELECT * FROM nuc_perfis WHERE cod_perfis = ".$cod_perfis);
    } 
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="perfil">Perfil</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="perfil" id="perfil" maxlength="30" size="50" value="<? echo bd2texto($objBusca->perfil) ?>"></td></tr>
    
    <tr><td align="center" class="tdbl tdbr">&nbsp;</td></tr>
    
    <tr>
      <td class="sep tdbl tdbr">
        <table cellpadding="0" cellspacing="0">
          <tr>
            <td align="left"><label for="cod_paginas" class="requerido">Páginas</label></td>
            <td align="center" width="20"><label>Acesso</label></td>
            <td width="20">&nbsp;</td>
            <td align="center" width="20"><label>Inserir</label></td>
            <td width="20">&nbsp;</td>
            <td align="center" width="20"><label>Apagar</label></td>
            <td width="20">&nbsp;</td>
            <td align="center" width="20"><label>Editar</label></td>
          </tr>
          
          <?
          function imprimeTabelaMenu($cod, $espaco, $objBusca) {
            $SqlBuscaPaginas = "SELECT * FROM nuc_paginas WHERE cod_paginas_pai = $cod ORDER BY ordem, menu";
            $resBuscaPaginas = mysql_query($SqlBuscaPaginas);
            $numBuscaPaginas = mysql_num_rows($resBuscaPaginas);
            
            if(($numBuscaPaginas > 0) && ($cod > 0))
              $espaco += 25;
            
            while ($objBuscaPaginas = mysql_fetch_object($resBuscaPaginas)) {
              echo '<tr class="foco">';
              
              echo '<td align="left" style="padding-left: '.$espaco.'px;">'.bd2texto($objBuscaPaginas->menu).'</td>';
              
              $SqlBuscaPaginasPerfis = sprintf("SELECT * FROM nuc_paginas_nuc_perfis WHERE cod_perfis = %d AND cod_paginas = %d LIMIT 1", 
                                               $objBusca->cod_perfis, $objBuscaPaginas->cod_paginas);
              $resBuscaPaginasPerfis = mysql_query($SqlBuscaPaginasPerfis);
              $objBuscaPaginasPerfis = mysql_fetch_object($resBuscaPaginasPerfis);
              
              if($objBuscaPaginasPerfis) {
                $checked_acesso = 'checked="checked"';
                
                $checked_inserir = ($objBuscaPaginasPerfis->inserir) ? 'checked="checked"' : '';
                $checked_apagar = ($objBuscaPaginasPerfis->apagar) ? 'checked="checked"' : '';
                $checked_editar = ($objBuscaPaginasPerfis->editar) ? 'checked="checked"' : '';
              }
              else {
                $checked_acesso = '';
                $checked_inserir = '';
                $checked_apagar = '';
                $checked_editar = '';
              }
              
              if($objBuscaPaginas->tipo == 'PAGINA')
                echo '<td align="center"><input type="checkbox" '.$checked_acesso.' class="marcar_todos marcar_filho_'.$objBuscaPaginas->cod_paginas_pai.'" name="acesso[]" value="'.$objBuscaPaginas->cod_paginas.'" onclick="marcaTodosEstado(\'marcar_'.$objBuscaPaginas->cod_paginas.'\', this.checked); marcarTodosPai('.$objBuscaPaginas->cod_paginas_pai.', '.$objBuscaPaginas->cod_paginas.', this.checked);"></td>';
              else
                echo '<td align="center"><input type="checkbox" '.$checked_acesso.' class="marcar_todos marcar_pai_'.$objBuscaPaginas->cod_paginas.'" name="acesso[]" value="'.$objBuscaPaginas->cod_paginas.'" onclick="marcaTodosEstado(\'marcar_filho_'.$objBuscaPaginas->cod_paginas.'\', this.checked)"></td>';
              
              echo '<td>&nbsp;</td>';
              
              if(($objBuscaPaginas->tipo == 'PAGINA') && ($objBuscaPaginas->permissoes))
                echo '<td align="center"><input type="checkbox" '.$checked_inserir.' class="marcar_todos marcar_'.$objBuscaPaginas->cod_paginas.' marcar_filho_'.$objBuscaPaginas->cod_paginas_pai.'" name="inserir[]" value="'.$objBuscaPaginas->cod_paginas.'"></td>';
              else
                echo '<td>&nbsp;</td>';
              
              echo '<td>&nbsp;</td>';
              
              if(($objBuscaPaginas->tipo == 'PAGINA') && ($objBuscaPaginas->permissoes))
                echo '<td align="center"><input type="checkbox" '.$checked_apagar.' class="marcar_todos marcar_'.$objBuscaPaginas->cod_paginas.' marcar_filho_'.$objBuscaPaginas->cod_paginas_pai.'" name="apagar[]" value="'.$objBuscaPaginas->cod_paginas.'"></td>';
              else
                echo '<td>&nbsp;</td>';
              
              echo '<td>&nbsp;</td>';
              
              if(($objBuscaPaginas->tipo == 'PAGINA') && ($objBuscaPaginas->permissoes))
                echo '<td align="center"><input type="checkbox" '.$checked_editar.' class="marcar_todos marcar_'.$objBuscaPaginas->cod_paginas.' marcar_filho_'.$objBuscaPaginas->cod_paginas_pai.'" name="editar[]" value="'.$objBuscaPaginas->cod_paginas.'"></td>';
              else
                echo '<td>&nbsp;</td>';
              
              echo '</tr>';
              
              imprimeTabelaMenu($objBuscaPaginas->cod_paginas, $espaco, $objBusca);
            }
          }
          
          $con = conectabd();
          imprimeTabelaMenu(0, 3, $objBusca);
          desconectabd($con);
          ?>
        </table>
      </td>
    </tr>
    
    <tr><td align="center" class="tdbl tdbr"><a href="javascript:;" onclick="marcaTodos('marcar_todos')">Marcar/Desmarcar Todos</a></td></tr>
    <tr><td align="center" class="tdbl tdbr">&nbsp;</td></tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="cod_perfis" value="<? echo $objBusca->cod_perfis ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>