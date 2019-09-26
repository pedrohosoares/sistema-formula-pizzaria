<?php
 
/**
 * ipi_unidade_padrao.php: Cadastro de Unidades Padrões
 * 
 * Índice: cod_unidade_padrao
 * Tabela: ipi_unidade_padrao
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Unidades Padrões');

$acao = validaVarPost('acao');

$tabela = 'ipi_unidade_padrao';
$chave_primaria = 'cod_unidade_padrao';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    $resDel = mysql_query($SqlDel);
    
    if ($resDel)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $unidade = validaVarPost('unidade');
    $abreviatura = validaVarPost('abreviatura');
    $divisor_comum = validaVarPost('divisor_comum');
    
    
    $con = conectabd();
    
    if($codigo <= 0) 
    {
      $SqlEdicao = sprintf("INSERT INTO $tabela (unidade, abreviatura, divisor_comum) VALUES ('%s', '%s', '%s')", 
                           $unidade, $abreviatura, $divisor_comum);    
          
      if(mysql_query($SqlEdicao))
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
      $SqlEdicao = sprintf("UPDATE $tabela SET unidade = '%s', abreviatura = '%s', divisor_comum = '%s' WHERE $chave_primaria = $codigo"                    ,$unidade, $abreviatura, $divisor_comum);
      
      if(mysql_query($SqlEdicao))
      {
        mensagemOk('Registro alterar com êxito!');
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
      document.frmIncluir.unidade.value = '';
      document.frmIncluir.abreviatura.value = '';
      document.frmIncluir.divisor_comum.value = '';
            
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
              <td align="center">Unidade</td>
              <td align="center">Abreviatura</td>
              <td align="center">Divisor comum</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $sql_buscar_unidades = "SELECT * FROM $tabela ORDER BY unidade";
          $res_buscar_unidades = mysql_query($sql_buscar_unidades);
          
          while ($obj_buscar_unidades = mysql_fetch_object($res_buscar_unidades)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$obj_buscar_unidades->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$obj_buscar_unidades->$chave_primaria.')">'.bd2texto($obj_buscar_unidades->unidade).'</a></td>';
            echo '<td align="center">'.bd2texto($obj_buscar_unidades->abreviatura).'</td>';
            echo '<td align="center">'.bd2texto($obj_buscar_unidades->divisor_comum).'</td>';
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
          <li><a href="ipi_pizza.php"><? echo ucfirst(TIPO_PRODUTOS)?></a></li>
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
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)" enctype="multipart/form-data">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="unidade">Unidade</label></td></tr>
    <tr><td class="tdbl tdbr "><input class="requerido" type="text" name="unidade" id="unidade" maxlength="45" size="58" value="<? echo texto2bd($objBusca->unidade) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="abreviatura">Abreviatura</label></td></tr>
    <tr><td class="tdbl tdbr "><input class="requerido" type="text" name="abreviatura" id="abreviatura" maxlength="45" size="58" value="<? echo texto2bd($objBusca->abreviatura) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="divisor_comum">Divisor Comum</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="divisor_comum" id="divisor_comum" maxlength="45" size="58" value="<? echo texto2bd($objBusca->divisor_comum) ?>"></td></tr>
    
    <tr><td class="tdbl tdbr sep">
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
