<?php

/**
 * ipi_tamanho.php: Cadastro de Tamanhos
 * 
 * Índice: cod_tamanhos
 * Tabela: ipi_tamanhos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Tamanhos');

$acao = validaVarPost('acao');

$tabela = 'ipi_tamanhos';
$chave_primaria = 'cod_tamanhos';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel1 = "DELETE FROM ipi_pizzas_ipi_tamanhos WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel2 = "DELETE FROM ipi_ingredientes_ipi_tamanhos WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel3 = "DELETE FROM ipi_tamanhos_ipi_bordas WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel4 = "DELETE FROM ipi_tamanhos_ipi_adicionais WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel5 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    $resDel1 = mysql_query($SqlDel1);
    $resDel2 = mysql_query($SqlDel2);
    $resDel3 = mysql_query($SqlDel3);
    $resDel4 = mysql_query($SqlDel4);
    $resDel5 = mysql_query($SqlDel5);
    
    if ($resDel1 && $resDel2 && $resDel3 && $resDel4 && $resDel5)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $tamanho = validaVarPost('tamanho');
    $situacao = validaVarPost('situacao');
    
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (tamanho, situacao) VALUES ('%s','%s')", 
                           $tamanho, $situacao);

      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET tamanho = '%s', situacao = '%s' WHERE $chave_primaria = $codigo", 
                           $tamanho, $situacao);

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
    if (confirm('Deseja excluir os registros selecionados?\n\nATENÇÃO: Todos os valores de tamanho associados anteriormente juntamente com seus respectivos preços e fidelidade (pizza, ingredientes, bordas e adicionais) serão APAGADOS definitivamente do sistema.')) {
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
      document.frmIncluir.tamanho.value = '';
      document.frmIncluir.situacao.value = '';
      
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
              <td align="center">COD Tamanho</td>
              <td align="center">Tamanho</td>
              <td align="center">Situação</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaTamanhos = "SELECT * FROM $tabela ORDER BY tamanho";
          $resBuscaTamanhos = mysql_query($SqlBuscaTamanhos);
          
          while ($objBuscaTamanhos = mysql_fetch_object($resBuscaTamanhos)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaTamanhos->$chave_primaria.'"></td>';
            echo '<td align="center">'.$objBuscaTamanhos->cod_tamanhos.'</td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaTamanhos->$chave_primaria.')">'.bd2texto($objBuscaTamanhos->tamanho).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaTamanhos->$chave_primaria.')">'.bd2texto($objBuscaTamanhos->situacao).'</a></td>';
            
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
         <li><a href="ipi_adicional.php">Adicionais</a></li>
          <li><a href="ipi_borda.php">Bordas</a></li>
          <li><a href="ipi_pizza.php"><? echo ucfirst(TIPO_PRODUTOS) ?></a></li>
          <li><a href="ipi_tamanho.php">Tamanhos</a></li>
          <li><a href="ipi_unidade_padrao.php">Unidade Padrão</a></li>
          <li><a href="ipi_ingrediente_marcas.php">Ingredientes - Marcas</a></li>
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
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="tamanho">Tamanho</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="tamanho" id="tamanho" maxlength="45" size="45" value="<? echo texto2bd($objBusca->tamanho) ?>"></td></tr>

         <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="situacao">Situação</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <select class="requerido" name="situacao" id="situacao">
            <option value=""></option>
            <option value="ATIVO" <? if($objBusca->situacao == 'ATIVO') echo 'selected'; ?>> Ativo </option>
            <option value="INATIVO" <? if($objBusca->situacao == 'INATIVO') echo 'selected'; ?>> Inativo </option>
        </select>
        </td>
    </tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>