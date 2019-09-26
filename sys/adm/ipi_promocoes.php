<?php

/**
 * ipi_pizza.php: Cadastro de Pizzas
 * 
 * Índice: cod_pizzas
 * Tabela: ipi_pizzas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Promocões');

$acao = validaVarPost('acao');

$tabela = 'ipi_promocoes';
$chave_primaria = 'cod_promocoes';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    
    $sql_imagens = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    // die($sql_imagens);
    $res_imagens = mysql_query($sql_imagens);
    // echo "<br>sql_imagens: ".$sql_imagens;
    
    if ($res_imagens)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':

    $codigo = validaVarPost($chave_primaria);
    $promocao = validaVarPost('promocao');
    $descricao = validaVarPost('descricao');
    $exibir_online = validaVarPost('exibir_online');
    $situacao = validaVarPost('situacao');
    $tipo = validaVarPost('tipo');
    $con = conectabd();
    
        if($codigo <= 0) 
        {
            $SqlEdicao = sprintf("INSERT INTO $tabela (promocao, descricao, exibir_online, tipo,situacao) VALUES ('%s', '%s', %d,'%s', '%s')", 
                               $promocao, $descricao, $exibir_online, $tipo,$situacao);
            $resEdicao = mysql_query($SqlEdicao);
            $codigo = mysql_insert_id();
        }
        else 
        {
        	$SqlEdicao = sprintf("UPDATE $tabela SET promocao = '%s', situacao = '%s',tipo = '%s', exibir_online = %d, descricao ='%s' WHERE $chave_primaria = $codigo", 
                           $promocao, $situacao, $tipo, $exibir_online, $descricao);
        $resEdicao = mysql_query($SqlEdicao);
        }
    
        if($resEdicao) 
        {
            mensagemOk('Os registros foram cadastrados com sucesso!');
        }
        else 
        {
            mensagemErro('Erro ao cadastrar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
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

function limpaPrecoFidelidade(cod) {
  document.getElementById('preco_' + cod).value = '';
  document.getElementById('fidelidade_' + cod).value = '';
}

function excluirImagem(cod, tipo) {
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
    var acao = 'excluir_imagem';
    var cod_combos = cod;
    
    if(cod_combos > 0) {
      var url = 'acao=' + acao + '&cod_combos=' + cod_combos + '&tipo=' + tipo;
      
      new Request.JSON({url: 'ipi_combos_ajax.php', onComplete: function(retorno) {
        if(retorno.status != 'OK') {
          alert('Erro ao excluir esta imagem.');
        }
        else 
        {
            if (tipo=="banner")
            {
                $id_foto = "foto_p_figura";
            }
            
            if (tipo=="fundo")
            {
                $id_foto = "foto_imagem";
            }
          
          if($($id_foto)) 
          {
            $($id_foto).destroy();
          }
          
        }
      }}).send(url);
    }
  }
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
      document.frmIncluir.promocao.value = '';
      document.frmIncluir.situacao.value = '';
      document.frmIncluir.descricao.value = '';
      document.frmIncluir.exibir_online.checked = false;
      document.frmIncluir.tipo.value = ''; 
      // Limpando todos os campos input para Preço e Fidelidade
      var input = document.getElementsByTagName('input');
      for (var i = 0; i < input.length; i++) {
        if(input[i].name.match('preco')) { 
          input[i].value = ''; 
        }
      }
      
      var input = document.getElementsByTagName('input');
      for (var i = 0; i < input.length; i++) {
        if(input[i].name.match('fidelidade')) { 
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
              <td align="center" width="70">Promoçao / Sugestão</td>
              <td align="center" width="200">Descrição</td>
              <td align="center" width="40">Tipo</td>
              <td align="center" width="10"><label>Exibir online</label></td>
              <td align="center" width="20" >Situação</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaPromocoes = "SELECT * FROM $tabela ORDER BY tipo";
          $resBuscaPromocoes = mysql_query($SqlBuscaPromocoes);
          
          while ($objBuscaPromocoes = mysql_fetch_object($resBuscaPromocoes)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaPromocoes->$chave_primaria.'"></td>';
            
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaPromocoes->$chave_primaria.')">'.bd2texto($objBuscaPromocoes->promocao).'</a></td>';
            echo '<td align="center">'.bd2texto($objBuscaPromocoes->descricao).'</td>';
            
            echo '<td align="center">'.bd2texto($objBuscaPromocoes->tipo).'</td>';
            
            echo '<td align="center">';
            if($objBuscaPromocoes->exibir_online)
							echo '<img src="../lib/img/principal/ok.gif">';
					  else
							echo '<img src="../lib/img/principal/erro.gif">';
											
											echo '</td>';
            echo '<td align="center">'.$objBuscaPromocoes->situacao.'</td>';
              
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
          <li><a href="ipi_pizzaria_promocoes.php">Pizzaria Promoções</a></li>
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
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="nome_combo">Promoção</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="promocao" id="" maxlength="45" size="50" value="<? echo texto2bd($objBusca->promocao) ?>"></td></tr>

    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="pontos_fidelidade">Descrição</label></td></tr>
    <tr><td class="tdbl tdbr"><textarea class="requerido" type="text" name="descricao" id="descricao" ><? echo texto2bd($objBusca->descricao) ?></textarea></td></tr>

    <tr><td class="legenda tdbl tdbr"><label for="exibir_online">Exibir Online?</label></td></tr>
    <tr><td class="tdbl tdbr"><input type="checkbox" name="exibir_online" id="exibir_online" value="1"<? if($objBusca->exibir_online) echo 'checked' ?> ></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="tipo">Tipo</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select name="tipo" id="tipo" class="requerido">
        <option value=""></option>
        <option value="PROMOCAO" <? if($objBusca->tipo == 'PROMOCAO') echo 'selected' ?>>PROMOÇÃO</option>
        <option value="SUGESTAO" <? if($objBusca->tipo == 'SUGESTAO') echo 'selected' ?>>SUGESTÃO</option>
      </select>
    </td></tr>
    
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="situacao">Situação</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select name="situacao" id="situacao" class="requerido">
        <option value=""></option>
        <option value="ATIVO" <? if($objBusca->situacao == 'ATIVO') echo 'selected' ?>>ATIVO</option>
        <option value="INATIVO" <? if($objBusca->situacao == 'INATIVO') echo 'selected' ?>>INATIVO</option>
      </select>
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
