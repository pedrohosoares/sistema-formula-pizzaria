<?php

/**
 * ipi_ingrediente.php: Cadastro de Ingrediente
 * 
 * Índice: cod_ingredientes
 * Tabela: ipi_ingredientes
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Grupos Pedido Minimo');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedido_minimo';
$chave_primaria = 'cod_pedido_minimo';
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    //$SqlDel1 = "DELETE FROM ipi_ingredientes_ipi_tamanhos WHERE $chave_primaria IN ($indicesSql)";
    ///$SqlDel2 = "DELETE FROM ipi_ingredientes_ipi_pizzas WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel3 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    //$resDel1 = mysql_query($SqlDel1);
    //$resDel2 = mysql_query($SqlDel2);
    $resDel3 = mysql_query($SqlDel3);
    
    if ($resDel3)//$resDel1 && $resDel2 && 
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os fretes selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);

    $descricao = validaVarPost("descricao"); 
    $valor_pedido_minimo = moeda2bd(validaVarPost("valor_pedido_minimo"));
    
    $con = conectabd();
    
    if($codigo <= 0) 
    {
      $SqlEdicao = sprintf("INSERT INTO $tabela (valor_pedido_minimo,descricao) VALUES ('%s','%s')", 
                           $valor_pedido_minimo,$descricao);
      $res_edicao = mysql_query($SqlEdicao);
      if($res_edicao) 
      {
        $codigo = mysql_insert_id();
        
        mensagemOk('Registro adicionado com êxito!');
        }
        else {
          mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
      }  
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET valor_pedido_minimo = '%s', descricao = '%s' WHERE $chave_primaria = $codigo", 
                           $valor_pedido_minimo,$descricao);
                           
      if(mysql_query($SqlEdicao)) 
      {
        mensagemOk('Registro editado com êxito!');
      }
      else {
        mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
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
      document.frmIncluir.valor_frete.value = '';
      document.frmIncluir.valor_comissao_frete.value = '';
      //document.frmIncluir.tipo.value = '';
      document.frmIncluir.descricao_taxa.checked = false;
     // document.frmIncluir.ativo.checked = true;
      
      marcaTodosEstado('marcar_tamanho', false);
          
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
              <td align="center">Descrição</td>
              <td align="center">Valor Pedido Minimo</td>
            </tr>
          </thead>
          <tbody>
          <?
          $con = conectabd();
          $SqlBuscaIngredientes = "SELECT * FROM $tabela ORDER BY cod_pedido_minimo";
          $resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
          while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) 
          {
            echo '<tr>';
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaIngredientes->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaIngredientes->$chave_primaria.')">'.bd2texto($objBuscaIngredientes->descricao).'</a></td>';
            echo '<td align="center">'.bd2moeda($objBuscaIngredientes->valor_pedido_minimo).'</td>';
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
<!--     <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="ipi_adicional.php">Adicionais</a></li>
          <li><a href="ipi_borda.php">Bordas</a></li>
          <li><a href="ipi_pizza.php">Pizzas</a></li>
          <li><a href="ipi_tamanho.php">Tamanhos</a></li>
          <li><a href="ipi_unidade_padrao.php">Unidade Padrão</a></li>
          <li><a href="ipi_ingrediente_marcas.php">Ingredientes - Marcas</a></li>
        </ul>
      </div>
    </td> -->
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
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">



    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="descricao">Descrição</label></td></tr>
    <tr><td class="tdbl sep tdbr"><input class="requerido" type="text" name="descricao" id="descricao" maxlength="50" size="50" value="<? echo texto2bd($objBusca->descricao) ?>"></td></tr>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="valor_pedido_minimo">Valor do Pedido Minimo</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" onKeyPress="return formataMoeda(this, '.', ',', event)" type="text" name="valor_pedido_minimo" id="valor_pedido_minimo" maxlength="7" size="7" value="<? echo bd2moeda($objBusca->valor_pedido_minimo) ?>"></td></tr>
    
    <tr><td class="tdbl tdbr">
    <tr><td colspan="2" align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
