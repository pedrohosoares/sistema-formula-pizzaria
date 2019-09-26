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

cabecalho('Cadastro de Combos Promocionais');

$acao = validaVarPost('acao');

$tabela = 'ipi_combos';
$chave_primaria = 'cod_combos';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    
    $sql_imagens = "SELECT * FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    $res_imagens = mysql_query($sql_imagens);
    // echo "<br>sql_imagens: ".$sql_imagens;
    while ($obj_imagens = mysql_fetch_object($res_imagens))
    {
        unlink(UPLOAD_DIR."/combos/".$obj_imagens->imagem_p);
        echo "<br>a: ".UPLOAD_DIR."/combos/".$obj_imagens->imagem_p;
    }
    
    $SqlDel3 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    $resDel3 = mysql_query($SqlDel3);
    
    if ($resDel3)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    /*

      1   cod_combos  int(10)     
  2   nome_combo  varchar(50)   
  3   imagem_p  varchar(30)   
  4   imagem_g  varchar(30)   
  5   imagem_fundo  varchar(30)  
  6   cor_combo   varchar(6)  
  7   ordem_combo   int(10)    
  8   situacao  varchar(20)  

    */
    $codigo = validaVarPost($chave_primaria);
    $pontos_fidelidade = validaVarPost('pontos_fidelidade');
    $nome_combo = validaVarPost('nome_combo');
    $descricao_combo = validaVarPost('descricao_combo');
    $ordem_combo = validaVarPost('ordem_combo');
    $cor_combo = validaVarPost('cor_combo');
    $situacao = validaVarPost('situacao');
    $preco = validaVarPost('preco');
    $foto_p = validaVarFiles('foto_p');
    $imagem_fundo = validaVarFiles('imagem_fundo');
    
    $con = conectabd();
    
        if($codigo <= 0) 
        {
            $SqlEdicao = sprintf("INSERT INTO $tabela (nome_combo, descricao_combo, situacao, ordem_combo, cor_combo) VALUES ('%s', '%s', '%s', %d, '%s')", 
                               $nome_combo, $descricao_combo, $situacao, $ordem_combo, $cor_combo);
            $resEdicao = mysql_query($SqlEdicao);
            $codigo = mysql_insert_id();
        }
        else 
        {
        $SqlEdicao = sprintf("UPDATE $tabela SET nome_combo = '%s', descricao_combo = '%s', situacao = '%s', ordem_combo = %d, cor_combo = '%s' WHERE $chave_primaria = $codigo", 
                           $nome_combo, $descricao_combo, $situacao, $ordem_combo, $cor_combo);
        $resEdicao = mysql_query($SqlEdicao);
        }
    
        if($resEdicao) 
        {
            
          if(trim($foto_p['name']) != '') 
          {
            $arq_info = pathinfo($foto_p['name']);
            $arq_ext = strtolower($arq_info['extension']);
            
            if(!eregi("^image\\/(png)$", $foto_p["type"])) 
            {
              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.png).');
            }
            else 
            {
              if(move_uploaded_file($foto_p['tmp_name'], UPLOAD_DIR."/combos/${codigo}_combo_p.${arq_ext}")) 
              {
                $SqlEdicaoImagem = sprintf("UPDATE $tabela SET imagem_p = '%s' WHERE $chave_primaria = $codigo", 
                                           "${codigo}_combo_p.${arq_ext}");
                if(mysql_query($SqlEdicaoImagem))
                  mensagemOk('Registro alterado com êxito!');
                else
                  mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
              }
              else 
              {
                mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
              }
            }
          }
          
            
          if(trim($imagem_fundo['name']) != '') 
          {
            $arq_info = pathinfo($imagem_fundo['name']);
            $arq_ext = strtolower($arq_info['extension']);
            //echo "Tipo: ".$imagem_fundo["type"];
            if(!eregi("^image\\/(jpeg)$", $imagem_fundo["type"])) 
            {
              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg).');
            }
            else 
            {
              if(move_uploaded_file($imagem_fundo['tmp_name'], UPLOAD_DIR."/combos/${codigo}_combo_fundo.${arq_ext}")) 
              {
                $SqlEdicaoImagem = sprintf("UPDATE $tabela SET imagem_fundo = '%s' WHERE $chave_primaria = $codigo", 
                                           "${codigo}_combo_fundo.${arq_ext}");
                if(mysql_query($SqlEdicaoImagem))
                  mensagemOk('Registro alterado com êxito!');
                else
                  mensagemErro('Erro ao Carregar Imagem', 'Por favor, verifique se o registro já não se encontra cadastrado.');
              }
              else 
              {
                mensagemErro('Erro ao Carregar Imagem', 'Por favor, verifique se o registro já não se encontra cadastrado.');
              }
            }
          }
          
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
      document.frmIncluir.nome_combo.value = '';
      document.frmIncluir.situacao.value = '';
      
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
              <td align="center">Nome do Combo</td>
              <td align="center">Descrição do Combo</td>
              <td align="center">Ordem de Exibição</td>
              <td align="center"><label>Ativo</label></td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaPizzas = "SELECT * FROM $tabela ORDER BY ordem_combo";
          $resBuscaPizzas = mysql_query($SqlBuscaPizzas);
          
          while ($objBuscaPizzas = mysql_fetch_object($resBuscaPizzas)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaPizzas->$chave_primaria.'"></td>';
            
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaPizzas->$chave_primaria.')">'.bd2texto($objBuscaPizzas->nome_combo).'</a></td>';
            echo '<td align="center">'.bd2texto($objBuscaPizzas->descricao_combo).'</td>';
            echo '<td align="center">'.bd2texto($objBuscaPizzas->ordem_combo).'</td>';
            echo '<td align="center">'.bd2texto($objBuscaPizzas->situacao).'</td>';
              
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
          <li><a href="ipi_combos_produtos.php">Combos Produtos</a></li>
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
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="nome_combo">Nome do Combo</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="nome_combo" id="nome_combo" maxlength="45" size="50" value="<? echo texto2bd($objBusca->nome_combo) ?>"></td></tr>

    <tr><td class="legenda tdbl tdbr"><label for="nome_combo">Descrição do Combo</label></td></tr>
    <tr>
    <td class="tdbl tdbr">
      <textarea name="descricao_combo" id="descricao_combo" cols="48" rows="3"><? echo texto2bd($objBusca->descricao_combo) ?></textarea><br />(250 caracteres)
    </td>
    </tr>
    
    <!-- <tr><td class="legenda tdbl tdbr"><label class="requerido" for="pontos_fidelidade">Pontos de Fidelidade</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="pontos_fidelidade" id="pontos_fidelidade" maxlength="45" size="10" value="<? echo texto2bd($objBusca->pontos_fidelidade) ?>" onKeyPress="return ApenasNumero(event)"></td></tr> -->

    <!-- <tr><td class="legenda tdbl tdbr"><label class="requerido" for="preco">Preço</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="preco" id="preco" maxlength="45" size="10" value="<? echo bd2moeda($objBusca->preco) ?>" onKeyPress="return formataMoeda(this, '.', ',', event)"></td></tr>
     -->
    <tr><td class="legenda tdbl tdbr"><label for="foto_p">Banner Horizontal (*.png)</label></td></tr>
    <?
    if(is_file(UPLOAD_DIR.'/combos/'.$objBusca->imagem_p)) {
      echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_p_figura" style="padding: 15px;">';
      
      echo '<img src="'.UPLOAD_DIR.'/combos/'.$objBusca->imagem_p.'">';
      
      echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript:excluirImagem('.$objBusca->$chave_primaria.',\'banner\');"></td></tr>';
    }
    ?>
    <tr><td class="sep tdbl tdbr sep"><input type="file" name="foto_p" id="foto_p" size="35"></td></tr>

    
    <tr><td class="legenda tdbl tdbr"><label for="foto_p">Fundo do Carrinho (*.jpg)</label></td></tr>
    <?
    if(is_file(UPLOAD_DIR.'/combos/'.$objBusca->imagem_fundo)) {
      echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_imagem" style="padding: 15px;">';
      
      echo '<img src="'.UPLOAD_DIR.'/combos/'.$objBusca->imagem_fundo.'">';
      
      echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript:excluirImagem('.$objBusca->$chave_primaria.', \'fundo\');"></td></tr>';
    }
    ?>
    <tr><td class="sep tdbl tdbr sep"><input type="file" name="imagem_fundo" id="foto_p" size="35"></td></tr>

    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="situacao">Situação</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select name="situacao" id="situacao" class="requerido">
        <option value=""></option>
        <option value="ATIVO" <? if($objBusca->situacao == 'ATIVO') echo 'selected' ?>>ATIVO</option>
        <option value="INATIVO" <? if($objBusca->situacao == 'INATIVO') echo 'selected' ?>>INATIVO</option>
      </select>
    </td></tr>
    
    <?
    if (!$objBusca->ordem_combo)
        $objBusca->ordem_combo = "1";
    ?>
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="ordem_combo">Ordem de Exibição</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="ordem_combo" id="ordem_combo" maxlength="45" size="10" value="<? echo texto2bd($objBusca->ordem_combo) ?>"> (Quanto menor, mais pra inicio)</td></tr>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="cor_combo">Cor do Combo</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="cor_combo" id="cor_combo" maxlength="45" size="10" value="<? echo texto2bd($objBusca->cor_combo) ?>"> (Em Hexa, Ex.: FFCCDD)</td></tr>

    <tr><td colspan="2" align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>