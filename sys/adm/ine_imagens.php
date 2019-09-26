<?php

/**
 * ine_images.php: Cadastro de Imagens
 * 
 * Índice: cod_imagens
 * Tabela: ine_imagens
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Imagens');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_imagens';
$tabela = 'ine_imagens';

switch($acao) {
  
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlBuscaArquivos = "SELECT * FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    $resBuscaArquivos = mysql_query($SqlBuscaArquivos);
    
    while($objBuscaArquivos = mysql_fetch_object($resBuscaArquivos)) {
      if(is_file(UPLOAD_DIR.'/newsletter/'.$objBuscaArquivos->arquivo))
        unlink(UPLOAD_DIR.'/newsletter/'.$objBuscaArquivos->arquivo);
    }
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlDel))
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $tipo = texto2bd(validaVarPost('tipo'));
    $arquivo = validaVarFiles('arquivo');
    $titulo = texto2bd(validaVarPost('titulo'));
    
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (tipo, titulo) VALUES ('%s', '%s')", 
                           $tipo, $titulo);

     $resEdicao = mysql_query($SqlEdicao);

      if($resEdicao)
        $codigo = mysql_insert_id();
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET tipo = '%s', titulo = '%s' WHERE $chave_primaria = $codigo", 
                           $tipo, $titulo);
                           
      $resEdicao = mysql_query($SqlEdicao);
    }         
    
    if($resEdicao) {
      
      
      if(trim($arquivo['name']) != '') {
        $arq_info = pathinfo($arquivo['name']);
        $arq_ext = strtolower($arq_info['extension']);
              
        if(!eregi("^image|application\\/(pjpeg|jpeg|jpg|swf|png|gif|x-shockwave-flash)$", $arquivo["type"])) {
          mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png, *.gif, *.swf).');
        }
        else {
          if(move_uploaded_file($arquivo['tmp_name'], UPLOAD_DIR."/newsletter/${codigo}_newsletter.${arq_ext}")) {
            $SqlEdicaoImagem = sprintf("UPDATE $tabela SET arquivo = '%s' WHERE $chave_primaria = $codigo", 
                                       "${codigo}_newsletter.${arq_ext}");
            
            if(mysql_query($SqlEdicaoImagem))
              mensagemOk('Registro alterado com êxito!');
            else
              mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          }
          else {
            mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          }
        }
      }
      
      
    }
    else {
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

function excluirImagem(cod) {
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
    var acao = 'excluir_imagem';
    var cod_imagens = cod;
    
    if(cod_imagens> 0) {
      var url = 'acao=' + acao + '&cod_imagens=' + cod_imagens;
      
      new Request.JSON({url: 'ine_imagens_ajax.php', onComplete: function(retorno) {
        if(retorno.status != 'OK') {
          alert('Erro ao excluir esta imagem.');
        }
        else {
          if($('imagem_figura')) {
            $('imagem_figura').destroy();
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
      document.frmIncluir.tipo.value = '';
      document.frmIncluir.titulo.value = '';
      
    if($('imagem_figura')) {
        $('imagem_figura').destroy();
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
              <td align="center">Titulo da Imagem</td>
              <td align="center">Tipo</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT * FROM $tabela ORDER BY titulo";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->titulo).'</a></td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->tipo).'</td>';
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
          <li><a href="ine_cadastro_emails_envio.php">Cadastro de E-mails para envio</a></li>
          <li><a href="ine_mensagens.php">Cadastro de Mensagens</a></li>
          <li><a href="ine_emails_cadastro.php">Cadastro de E-Mails</a></li>
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
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo AND tipo not in ('AVANÇADO')");
    } 
    ?>
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
     
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="titulo">Título</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="titulo" id="titulo" maxlength="100" size="45" value="<? echo bd2texto($objBusca->titulo) ?>"></td></tr>
     
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="tipo">Tipo</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select class="requerido" name="tipo" id="tipo">
        <option value=""></option>
        <option value="CABECALHO" <? if($objBusca->tipo == 'CABECALHO') echo 'selected' ?>>Cabeçalho</option>
        <option value="RODAPE" <? if($objBusca->tipo == 'RODAPE') echo 'selected' ?>>Rodapé</option>
        <option value="IMAGEM" <? if($objBusca->tipo == 'IMAGEM') echo 'selected' ?>>Imagem de Conteúdo</option>
      </select>
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="arquivo">Imagem (*.gif, *.png, *.jpg, *.swf)</label></td></tr>
        
    <?
    
    if(is_file(UPLOAD_DIR.'/newsletter/'.$objBusca->arquivo)) {
      echo '<tr><td class="sep tdbl tdbr" align="center" id="imagem_figura" style="padding: 15px;">';
      
      $info = pathinfo(UPLOAD_DIR.'/newsletter/'.$objBusca->arquivo);
      
      if($info['extension'] == 'swf'){
        echo '<param name="movie" value="'.UPLOAD_DIR.'/newsletter/'.$objBusca->arquivo.'">';
        echo '<embed src="'.UPLOAD_DIR.'/newsletter/'.$objBusca->arquivo.'">';
        echo '</embed>';
        echo '</object>';
      }
      else{
        echo '<img src="'.UPLOAD_DIR.'/newsletter/'.$objBusca->arquivo.'">';
      }
      
      echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem('.$objBusca->$chave_primaria.');"></td></tr>';
    }
    
    ?>
    
    <tr><td class="sep tdbl tdbr"><input type="file" name="arquivo" id="arquivo" size="40"></td></tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
