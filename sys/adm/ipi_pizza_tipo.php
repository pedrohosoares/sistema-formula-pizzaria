<?php

/**
 * ipi_bebida.php: Cadastro Bebidas
 * 
 * Índice: cod_bebidas
 * Tabela: ipi_bebidas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Tipo de '.TIPO_PRODUTO);

$acao = validaVarPost('acao');

$tabela = 'ipi_tipo_pizza';
$chave_primaria = 'cod_tipo_pizza';

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
    $pizza = validaVarPost('pizza');
    $situacao = validaVarPost('situacao');
    //echo $unidade_padrao;
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (tipo_pizza, situacao) VALUES ('%s', '%s')", 
                           $pizza, $situacao);
      //echo $SqlEdicao;
      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET tipo_pizza = '%s', situacao = '%s' WHERE $chave_primaria = $codigo", 
           
                       $pizza, $situacao);

      //echo $SqlEdicao;
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
      document.frmIncluir.pizza.value = '';
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
              <td align="center">Tipo (<? echo ucfirst(TIPO_PRODUTO)?>)</td>
              <td align="center">Situação</td>
           
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT cod_tipo_pizza, tipo_pizza, situacao FROM $tabela  ORDER BY tipo_pizza ASC ";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          //echo  $SqlBuscaRegistros;
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->tipo_pizza).'</a></td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->situacao).'</td>';
            
          
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
          <li><a href="ipi_pizza_tipo.php">Tipo (<? echo ucfirst(TIPO_PRODUTOS) ?>)</a></li>
          <li><a href="ipi_pizza.php">Cadastro (<? echo ucfirst(TIPO_PRODUTOS) ?>)</a></li>
          <li><a href="ipi_preco_pizza.php">Preços (<? echo ucfirst(TIPO_PRODUTOS) ?>)</a></li>
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
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo ");
    } 
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="pizza">Tipo (<? echo ucfirst(TIPO_PRODUTO) ?>)</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="pizza" id="pizza" maxlength="45" size="45" value="<? echo texto2bd($objBusca->tipo_pizza) ?>"></td></tr>
       <tr>
        <td class="legenda tdbl  tdbr">
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