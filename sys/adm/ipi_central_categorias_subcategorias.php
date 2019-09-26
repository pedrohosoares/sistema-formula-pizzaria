<?php

/**
 * Cadastro de Situações para tickets.
 *
 * @version 1.0
 * @package iti
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       14/11/2012   Filipe         Criado.
 *
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Subcategorias para tickets');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_ticket_subcategorias';
$tabela = 'ipi_comunicacao_subcategorias';
$campo_ordenacao = 'nome_subcategoria';
$campo_filtro_padrao = 'nome_subcategoria';
$quant_pagina = 50;
$exibir_barra_lateral = false;

/*
Tipos de STATUS

PUBLICADO
EXCLUIDO
RASCUNHO

*/

switch ($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        
        $sql_del = "UPDATE $tabela SET situacao='INATIVO' WHERE $chave_primaria IN ($indices_sql)";
        
        if (mysql_query($sql_del))
        {
            mensagemok('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemerro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        }
        
        desconectabd($conexao);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);

        $cod_categoria = validaVarPost('cod_categorias');
        $nome_subcategoria = validaVarPost('nome_subcategoria');
        $emails_associados = validaVarPost('emails_associados');
        $situacao = validaVarPost('situacao');
        $conexao = conectabd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (cod_categorias,nome_subcategoria,emails_associados,situacao) VALUES (%d,'%s','%s','%s')", $cod_categoria,$nome_subcategoria,$emails_associados,$situacao);
            $res_edicao = mysql_query($sql_edicao);
            if ($res_edicao)
            {
                $codigo = mysql_insert_id();
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET cod_categorias = %d, situacao = '%s',nome_subcategoria = '%s', emails_associados = '%s' WHERE $chave_primaria = $codigo", $cod_categoria, $situacao,$nome_subcategoria,$emails_associados);
            $res_edicao = mysql_query($sql_edicao);
        }
        
        if ($res_edicao)
        {
            mensagemok('Registro alterado com êxito!');
        }  
        else
        {
            mensagemerro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }        
        
        desconectabd($conexao);
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
  var inputf = new Element('input', {
    'type': 'hidden',
    'name': 'cod_categorias',
    'value': '<? echo validaVarPost("cod_categorias") ?>'
  });
  input.inject(form);
  inputf.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

window.addEvent('domready', function(){

  var tabs = new Tabs('tabs'); 


  if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) {
    <? if ($acao == '') echo 'tabs.irpara(1);'; ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
    //chamar_ajax($('cod_Categorias_perguntas_pai').value,$('perguntaid').value);
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.<? echo $chave_primaria ?>.value = '';
      document.frmIncluir.cod_categorias.value = '';
      document.frmIncluir.nome_situacao.value = '';
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });

});


function validarCategoria(formulario) {

    if (formulario.cod_categorias.value == "") {
      alert('Selecione uma categoria.');
      formulario.cod_categorias.focus();
      return false;
    }
    return true;
}



/*function chamar_ajax(cod,var3) {
   
    var acao = 'chamar_perguntas_pai';
    var var2 = cod;
    var var3 = var3;
      var url = 'acao=' + acao + '&var2=' + var2 + '&var3=' + var3;
      
      new Request.HTML({
        url: 'ipi_Categoria_ajax.php',
        update: $('respostas')
      }).send(url);
}*/


</script>

<form name="frmCategoria" method="post" onsubmit="return validarCategoria(this);">

Categoria: <select name="cod_categorias" style="width: 300px;">
<option value="">Selecione uma categoria</option>
<?
$con = conectabd();
$SqlBuscaCategorias = "SELECT * FROM ipi_comunicacao_categorias WHERE status='ATIVO' ORDER BY nome_categoria";
$resBuscaCategorias = mysql_query($SqlBuscaCategorias);
          
while($objBuscaCategorias = mysql_fetch_object($resBuscaCategorias)) 
{
    echo '<option value="'.$objBuscaCategorias->cod_categorias.'" ';
    if(validaVarPost('cod_categorias') == $objBuscaCategorias->cod_categorias)
        echo 'selected';
    echo '>'.bd2texto($objBuscaCategorias->nome_categoria).'</option>';
}
desconectabd($con);
?>
</select>
<input class="botaoAzul" type="submit" value="Filtrar">
</form>

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
              <td align="center">Nome da Subcategoria</td>
              <td align="center">Emails Associados</td>
              <td align="center">Situação</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT s.* FROM $tabela s INNER JOIN ipi_comunicacao_categorias c ON (s.cod_categorias = c.cod_categorias) WHERE c.cod_categorias='".validaVarPost('cod_categorias')."' ORDER BY $chave_primaria";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          //echo $SqlBuscaRegistros;
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->nome_subcategoria).'</a></td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->emails_associados).'</td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->situacao).'</td>';
            
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
          <li><a href="ipi_central_categorias.php">Categorias</a></li>
          <li><a href="ipi_central_situacoes.php">Situações</a></li>
          <li><a href="ipi_central_situacoes_subcategorias.php">Situações por Subcategorias</a></li>       
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
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_categorias">Categoria</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select class="requerido" name="cod_categorias" id="cod_categorias">
        <option value='0'></option>
        
        <?
        $con = conectabd();
        
        $SqlBuscaCategorias = "SELECT * FROM ipi_comunicacao_categorias WHERE status='ATIVO' ORDER BY cod_categorias DESC";
        $resBuscaCategorias = mysql_query($SqlBuscaCategorias);
        
        while($objBuscaCategorias = mysql_fetch_object($resBuscaCategorias)) {
          echo '<option value="'.$objBuscaCategorias->cod_categorias.'"';
          
          if($objBuscaCategorias->cod_categorias == $objBusca->cod_categorias || $objBuscaCategorias->cod_categorias == validaVarPost('cod_categorias'))
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaCategorias->nome_categoria).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td></tr>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="nome_resposta">Nome da Subcategoria</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="nome_subcategoria" id="nome_subcategoria" maxlength="100" size="50" value="<? echo texto2bd($objBusca->nome_subcategoria) ?>"></td></tr>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="situacao">Situação</label></td></tr>
    <tr><td class="tdbl tdbr sep"><select class="requerido" name="situacao" id="situacao" >
          <option value="ATIVO"<? if($objBusca->situacao=="ATIVO") echo 'selected '?>>Ativo</option>
          <option value="INATIVO"<? if($objBusca->situacao=="INATIVO") echo 'selected '?>>Inativo</option>
        </select>
      </tr>

    <tr>
        <td class="legenda tdbr tdbl"><label for="emails">Emails</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr tdbl"><input type="text" name="emails_associados" id="emails_associados" maxlength="250" size="60" value="<? echo bd2texto($objBusca->emails_associados)?>"></td>
    </tr>

    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"</td></tr>
    
    </table>

    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
