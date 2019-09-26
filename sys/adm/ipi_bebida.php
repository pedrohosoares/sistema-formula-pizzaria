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

cabecalho('Cadastro de Bebidas');

$acao = validaVarPost('acao');

$tabela = 'ipi_bebidas';
$chave_primaria = 'cod_bebidas';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    //echo $SqlDel;
    if (mysql_query($SqlDel))
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $bebida = validaVarPost('bebida');
    $tipo_bebida = validaVarPost('cod_tipo_bebida');
    //echo $unidade_padrao;
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (bebida,cod_tipo_bebida) VALUES ('%s',%d)", 
                           $bebida,$tipo_bebida);
      //echo $SqlEdicao;
      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET bebida = '%s',cod_tipo_bebida = %d WHERE $chave_primaria = $codigo", 
                           $bebida,$tipo_bebida);
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
      document.frmIncluir.bebida.value = '';
      document.frmIncluir.cod_tipo_bebida.value = '';
      
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
              <td align="center" width="20" ><input type="checkbox" onclick="marcaTodos('marcar');"></td>
              <td align="center" width="">Tipo de Bebida</td>
              <td align="center" width="">Bebida</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT b.cod_bebidas, b.cod_tipo_bebida, b.bebida, tb.tipo_bebida FROM ipi_bebidas b LEFT JOIN ipi_tipo_bebida tb ON (b.cod_tipo_bebida= tb.cod_tipo_bebida) ORDER BY tb.tipo_bebida ASC, b.bebida ASC ";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->tipo_bebida).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->bebida).'</a></td>';
            
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
          <li><a href="ipi_bebida_tipo.php">Tipo de Bebidas</a></li>
          <li><a href="ipi_bebida.php">Cadastro de Bebidas</a></li>
          <li><a href="ipi_conteudo.php">Tamanhos de Bebidas</a></li>
          <li><a href="ipi_bebida_conteudo.php">Vincular Tamanho a Bebida</a></li>
          <li><a href="ipi_preco_bebida.php">Preços de Bebidas</a></li>
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
      $objBusca = executaBuscaSimples("SELECT cod_tipo_bebida, bebida FROM $tabela WHERE $chave_primaria = $codigo ");
    } 
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">

   <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_tipo_bebida">Tipo da Bebida</label></td></tr>
    <tr><td class="tdbl tdbr sep">

      <select class="requerido" name="cod_tipo_bebida" id="cod_tipo_bebida" >
        <option value =""></option>
        <?
          $con = conectar_bd();
          $sql_busca_tipos = "SELECT cod_tipo_bebida, tipo_bebida FROM ipi_tipo_bebida WHERE situacao = 'ATIVO' order by tipo_bebida ASC";
          $res_busca_tipos = mysql_query($sql_busca_tipos);
          while($obj_busca_tipos = mysql_fetch_object($res_busca_tipos))
          {
            echo "<option value='".$obj_busca_tipos->cod_tipo_bebida."'";
            if($objBusca->cod_tipo_bebida==$obj_busca_tipos->cod_tipo_bebida)
            {
              echo " SELECTED ";
            }
            echo ">".$obj_busca_tipos->tipo_bebida."</option>";

          }
          desconectar_bd($con);
        ?>
      </select>

    </td></tr>
    
    <tr><td class="legenda tdbl  tdbr"><label class="requerido" for="bebida">Bebida</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="bebida" id="bebida" maxlength="45" size="45" value="<? echo texto2bd($objBusca->bebida) ?>"></td></tr>



    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>