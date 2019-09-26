<?php

/**
 * ipi_tipo_cortes.php: Cadastro de Tipo de Corte
 * 
 * Índice: cod_tipo_cortes
 * Tabela: ipi_tipo_cortes
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Opções de Corte');

$acao = validaVarPost('acao');

$tabela = 'ipi_opcoes_corte';
$chave_primaria = 'cod_opcoes_corte';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel1 = "DELETE FROM ipi_tamanhos_ipi_opcoes_corte WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel3 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    $resDel1 = mysql_query($SqlDel1);
    $resDel3 = mysql_query($SqlDel3);
    
    if ($resDel1 && $resDel3)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $opcao_corte = validaVarPost('opcao_corte');
    $tamanho = validaVarPost('tamanho');
    $tamanho_checkbox = validaVarPost('tamanho_checkbox');
    $preco = validaVarPost('preco');
    $selecao_padrao = validaVarPost("selecao");
    /*echo "<pre>";
    print_r($_POST);
    echo "</pre>";*/
    $con = conectabd();
    
    if($codigo <= 0) 
    {
      $SqlEdicao = sprintf("INSERT INTO $tabela (opcao_corte) VALUES ('%s')", $opcao_corte);

      if(mysql_query($SqlEdicao)) 
      {
        $codigo = mysql_insert_id();
        $resEdicaoTamanhoCorte = true;
        if(is_array($tamanho_checkbox)) 
        {
          for($t = 0; $t < count($tamanho); $t++) 
          {
            if(in_array($tamanho[$t], $tamanho_checkbox)) 
            {
              $cor_preco = ($preco[$t] > 0) ? moeda2bd($preco[$t]) : 0;
              $selecao = ($selecao_padrao[$tamanho[$t]] > 0) ? moeda2bd($selecao_padrao[$tamanho[$t]]) : 0;

              $SqlEdicaoTamanhoCorte = sprintf("INSERT INTO ipi_tamanhos_ipi_opcoes_corte (cod_opcoes_corte, cod_tamanhos, preco, selecao_padrao_corte) VALUES (%d, %d, %s, %d)", $codigo, $tamanho[$t], $cor_preco,$selecao);
              $resEdicaoTamanhoCorte &= mysql_query($SqlEdicaoTamanhoCorte);
            }
          }
        }
        
        if($resEdicaoTamanhoCorte) 
        {
          mensagemOk('Registro adicionado com êxito!');
        }
        else 
        {
          mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        
      }
      else 
      {
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
      }
    }
    else 
    {
      $SqlEdicao = sprintf("UPDATE $tabela SET opcao_corte = '%s' WHERE $chave_primaria = $codigo", $opcao_corte);
      if(mysql_query($SqlEdicao)) 
      {
        $resEdicaoTamanhoCorte = true;
        
        if(is_array($tamanho)) 
        {
          $SqlDelTamanhoMassa = "DELETE FROM ipi_tamanhos_ipi_opcoes_corte WHERE $chave_primaria = $codigo";
          $resDelTamanhoMassa = mysql_query($SqlDelTamanhoMassa);
        }
        else 
        {
          $resDelTamanhoMassa = true;
        }
        
        if($resDelTamanhoMassa) 
        {
          if(is_array($tamanho_checkbox)) 
          {
            for($t = 0; $t < count($tamanho); $t++) 
            {
              if(in_array($tamanho[$t], $tamanho_checkbox)) 
              {
                $cor_preco = ($preco[$t] > 0) ? moeda2bd($preco[$t]) : 0;
                $selecao = ($selecao_padrao[$tamanho[$t]] > 0) ? moeda2bd($selecao_padrao[$tamanho[$t]]) : 0;
                $SqlEdicaoTamanhoCorte = sprintf("INSERT INTO ipi_tamanhos_ipi_opcoes_corte (cod_opcoes_corte, cod_tamanhos, preco,selecao_padrao_corte) VALUES (%d, %d, %s, %d)", $codigo, $tamanho[$t], $cor_preco,$selecao);
                $resEdicaoTamanhoCorte &= mysql_query($SqlEdicaoTamanhoCorte);
              }
            }
          }
          
          if($resEdicaoTamanhoCorte) 
          {
            mensagemOk('Registro alterado com êxito!');
          }
          else 
          {
            mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          }
          
          
        }
        else 
        {
          mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
      }
      else 
      {
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

function limpaPreco(cod) {
  document.getElementById('preco_' + cod).value = '';
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
      document.frmIncluir.opcao_corte.value = '';
      
      marcaTodosEstado('marcar_tamanho', false);
      
      // Limpando todos os campos input para Preço
      var input = document.getElementsByTagName('input');
      for (var i = 0; i < input.length; i++) {
        if(input[i].name.match('preco')) { 
          input[i].value = ''; 
        }
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
              <td align="center">Opções de Corte</td>
              <td align="center">Tamanho</td>
              <td align="center">Padrão</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaIngredientes = "SELECT * FROM $tabela oc INNER JOIN ipi_tamanhos_ipi_opcoes_corte toc ON (oc.cod_opcoes_corte = toc.cod_opcoes_corte) INNER JOIN ipi_tamanhos t ON (toc.cod_tamanhos = t.cod_tamanhos) ORDER BY toc.cod_tamanhos";
          $resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
          //echo $SqlBuscaIngredientes;

          while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaIngredientes->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaIngredientes->$chave_primaria.')">'.bd2texto($objBuscaIngredientes->opcao_corte).'</a></td>';
            echo '<td align="center">'.bd2texto($objBuscaIngredientes->tamanho).'</td>';
            echo '<td align="center">'.($objBuscaIngredientes->selecao_padrao_corte ?' Sim' : ' Não' ).'</td>';
            
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
          <li><a href="ipi_tipo_cortes.php">Cortes</a></li>
          <li><a href="ipi_tipo_massa.php">Massas</a></li>
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
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="opcao_corte">Corte</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="opcao_corte" id="opcao_corte" maxlength="30" size="45" value="<? echo texto2bd($objBusca->opcao_corte) ?>"></td></tr>
    
    <tr><td class="tdbl tdbr sep">
      <table class="listaEdicao" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <td align="center" width="20"><input type="checkbox" class="marcar_tamanho" onclick="marcaTodosEstado('marcar_tamanho', this.checked);"></td>
            <td align="center"><label>Tamanho</label></td>
            <td align="center"><label>Preço</label></td>
            <td align="center"><label>Seleção Padrão</label></td>
          </tr>
        </thead>
        <tbody>
      
        <?
        $con = conectabd();
        $SqlBuscaTamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
        $resBuscaTamanhos = mysql_query($SqlBuscaTamanhos);
        while ($objBuscaTamanhos = mysql_fetch_object($resBuscaTamanhos)) 
        {
          echo '<tr>';
          
          if($codigo > 0)
            $objBuscaPrecos = executaBuscaSimples(sprintf("SELECT * FROM ipi_tamanhos_ipi_opcoes_corte WHERE cod_opcoes_corte = %d AND cod_tamanhos = %d", $codigo, $objBuscaTamanhos->cod_tamanhos), $con);
          else
            $objBuscaPrecos = null;
          
          echo '<input type="hidden" name="tamanho[]" value="'.$objBuscaTamanhos->cod_tamanhos.'">';
            
          if($objBuscaPrecos)
            echo '<td align="center"><input type="checkbox" class="marcar_tamanho" name="tamanho_checkbox[]" value="'.$objBuscaTamanhos->cod_tamanhos.'" onclick="limpaPreco('.$objBuscaTamanhos->cod_tamanhos.')" checked="checked"></td>';
          else
            echo '<td align="center"><input type="checkbox" class="marcar_tamanho" name="tamanho_checkbox[]" value="'.$objBuscaTamanhos->cod_tamanhos.'" onclick="limpaPreco('.$objBuscaTamanhos->cod_tamanhos.')"></td>';
          
          echo '<td><label>'.$objBuscaTamanhos->tamanho.'</label></td>';
          echo '<td align="center"><input type="text" name="preco[]" id="preco_'.$objBuscaTamanhos->cod_tamanhos.'" maxsize="5" size="3" value="'.bd2moeda($objBuscaPrecos->preco).'" onKeyPress="return formataMoeda(this, \'.\', \',\', event)"></td>';

          echo '<td align="center"><input type="checkbox" name="selecao['.$objBuscaTamanhos->cod_tamanhos.']" id="selecao_' . $objBuscaTamanhos->cod_tamanhos . '" value="1" '.($objBuscaPrecos->selecao_padrao_corte ? 'checked="checked"' : '').' ></td>';
          
          echo '</tr>';
        }
        desconectabd($con);
        ?>
        
        </tbody>
      </table>
    </td></tr>
    
    <tr><td colspan="2" align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
