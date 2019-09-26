<?php

/**
 * iba_banner.php: Cadastro de Banners
 * 
 * Índice: cod_banners
 * Tabela: iba_banners
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Banners');

$acao = validaVarPost('acao');

$tabela = 'iba_banners';
$chave_primaria = 'cod_banners';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlBuscaArquivos = "SELECT * FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    $resBuscaArquivos = mysql_query($SqlBuscaArquivos);
    
    while($objBuscaArquivos = mysql_fetch_object($resBuscaArquivos)) {
      if(is_file(UPLOAD_DIR.'/banners/'.$objBuscaArquivos->imagem))
        unlink(UPLOAD_DIR.'/banners/'.$objBuscaArquivos->imagem);
    }
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if(mysql_query($SqlDel)) {
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');  
    }
    else {
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    }
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $cod_tamanhos = validaVarPost('cod_tamanhos', '/[0-9]+/');
    $link = texto2bd(validaVarPost('link'));
    $descricao = texto2bd(validaVarPost('descricao'));
    $exibicao_dom = (validaVarPost('exibicao_dom') == 'on') ? 1 : 0;
    $exibicao_seg = (validaVarPost('exibicao_seg') == 'on') ? 1 : 0;
    $exibicao_ter = (validaVarPost('exibicao_ter') == 'on') ? 1 : 0;
    $exibicao_qua = (validaVarPost('exibicao_qua') == 'on') ? 1 : 0;
    $exibicao_qui = (validaVarPost('exibicao_qui') == 'on') ? 1 : 0;
    $exibicao_sex = (validaVarPost('exibicao_sex') == 'on') ? 1 : 0;
    $exibicao_sab = (validaVarPost('exibicao_sab') == 'on') ? 1 : 0;
    
    $imagem = validaVarFiles('imagem');
    $tipo = 'DIA SEMANA';
    
    $con = conectabd();
    
    $resEdicao = true;
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (link, descricao, tipo, exibicao_dom, exibicao_seg, exibicao_ter, exibicao_qua, exibicao_qui, exibicao_sex, exibicao_sab, cod_tamanhos) VALUES ('%s', '%s', '%s', %d, %d, %d, %d, %d, %d, %d, %d)", 
                           $link, $descricao, $tipo, $exibicao_dom, $exibicao_seg, $exibicao_ter, $exibicao_qua, $exibicao_qui, $exibicao_sex, $exibicao_sab, $cod_tamanhos);

      $resEdicao = mysql_query($SqlEdicao);
      
      if($resEdicao)
        $codigo = mysql_insert_id();
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET link = '%s', descricao = '%s', tipo = '%s', exibicao_dom = %d, exibicao_seg = %d, exibicao_ter = %d, exibicao_qua = %d, exibicao_qui = %d, exibicao_sex = %d, exibicao_sab = %d, cod_tamanhos = %d WHERE $chave_primaria = $codigo", 
                           $link, $descricao, $tipo, $exibicao_dom, $exibicao_seg, $exibicao_ter, $exibicao_qua, $exibicao_qui, $exibicao_sex, $exibicao_sab, $cod_tamanhos);

      $resEdicao = mysql_query($SqlEdicao);
    }
    
    if($resEdicao) {
      if(trim($imagem['name']) != '') {
        $arq_info = pathinfo($imagem['name']);
        $arq_ext = strtolower($arq_info['extension']);
        
        if(!eregi("^image|application\\/(pjpeg|jpeg|jpg|swf|png|gif|x-shockwave-flash)$", $imagem["type"])) {
          mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png, *.gif, *.swf).');
        }
        else {
          if(move_uploaded_file($imagem['tmp_name'], UPLOAD_DIR."/banners/${codigo}_banner.${arq_ext}")) {
            $SqlEdicaoImagem = sprintf("UPDATE $tabela SET imagem = '%s' WHERE $chave_primaria = $codigo", 
                                       "${codigo}_banner.${arq_ext}");
            
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
    var cod_banners = cod;
    
    if(cod_banners > 0) {
      var url = 'acao=' + acao + '&cod_banners=' + cod_banners;
      
      new Request.JSON({url: 'iba_banner_ajax.php', onComplete: function(retorno) {
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
      document.frmIncluir.cod_tamanhos.value = '';
      document.frmIncluir.link.value = '';
      document.frmIncluir.descricao.value = '';
      document.frmIncluir.exibicao_dom.checked = true;
      document.frmIncluir.exibicao_seg.checked = true;
      document.frmIncluir.exibicao_ter.checked = true;
      document.frmIncluir.exibicao_qua.checked = true;
      document.frmIncluir.exibicao_qui.checked = true;
      document.frmIncluir.exibicao_sex.checked = true;
      document.frmIncluir.exibicao_sab.checked = true;
      
      // Limpando a imagem de referência
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
              <td align="center" width="80">Código</td>
              <td align="center">Descrição</td>
              <td align="center" width="100">Tamanho (A x L)</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaBanners = "SELECT * FROM $tabela b INNER JOIN iba_tamanhos t ON (b.cod_tamanhos = t.cod_tamanhos) ORDER BY $chave_primaria";
          $resBuscaBanners = mysql_query($SqlBuscaBanners);
          
          while ($objBuscaBanners = mysql_fetch_object($resBuscaBanners)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaBanners->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaBanners->$chave_primaria.')">'.$objBuscaBanners->$chave_primaria.'</a></td>';
            echo '<td align="left"><a href="javascript:;" onclick="editar('.$objBuscaBanners->$chave_primaria.')">'.bd2texto($objBuscaBanners->descricao).'</a></td>';
            echo '<td align="center">'.$objBuscaBanners->altura .' x '.$objBuscaBanners->largura.'</td>';
            
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
          <li><a href="iba_tamanho.php">Tamanhos de Banners</a></li>
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
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_tamanhos">Tamanho</label></td></tr>
    <tr><td class="sep tdbl tdbr">
      <select class="requerido" name="cod_tamanhos" id="cod_tamanhos">
        <option value=""></option>
        <?
        $con = conectabd();
        
        $SqlBuscaTamanhos = "SELECT * FROM iba_tamanhos ORDER BY altura, largura";
        $resBuscaTamanhos = mysql_query($SqlBuscaTamanhos);
        
        while ($objBuscaTamanhos = mysql_fetch_object($resBuscaTamanhos)) {
          echo '<option value="'.$objBuscaTamanhos->cod_tamanhos.'" ';
          
          if($objBuscaTamanhos->cod_tamanhos == $objBusca->cod_tamanhos)
            echo 'selected';
            
          echo '>'.$objBuscaTamanhos->altura.' x '.$objBuscaTamanhos->largura.'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="link">Link</label></td></tr>
    <tr><td class="tdbl tdbr"><input type="text" name="link" id="link" maxlength="100" size="52" value="<? echo $objBusca->link ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="descricao">Descrição</label></td></tr>
    <tr><td class="sep tdbl tdbr"><input type="text" name="descricao" id="descricao" maxlength="50" size="52" value="<? echo $objBusca->descricao ?>"></td></tr>

    
    <tr><td class="legenda tdbl tdbr"><label for="imagem">Imagem (*.gif, *.png, *.jpg, *.swf)</label></td></tr>
        
    <?
    if(is_file(UPLOAD_DIR.'/banners/'.$objBusca->imagem)) {
      echo '<tr><td class="sep tdbl tdbr" align="center" id="imagem_figura" style="padding: 15px;">';
      
      $info = pathinfo('upload/banners/'.$objBusca->imagem);
      
      if($info['extension'] == 'swf'){
        echo '<object width="'.$objBusca->largura.'" height="'.$objBusca->altura.'">';
        echo '<param name="movie" value="upload/banners/'.$objBusca->imagem.'">';
        echo '<embed src="'.UPLOAD_DIR.'/banners/'.$objBusca->imagem.'" width="'.$objBusca->largura.'" height="'.$objBusca->altura.'">';
        echo '</embed>';
        echo '</object>';
      }
      else{
        echo '<img src="'.UPLOAD_DIR.'/banners/'.$objBusca->imagem.'">';
      }
      
      echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem('.$objBusca->$chave_primaria.');"></td></tr>';
    }
    ?>
    
    <tr><td class="sep tdbl tdbr"><input type="file" name="imagem" id="imagem" size="40"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label>Programação</label></td></tr>
    <tr><td class="sep tdbl tdbr"><hr noshade="noshade" color="#1C4B93"></td></tr>
    
    <tr><td class="sep tdbl tdbr">
      <table>
        <tr>
          <td><input type="checkbox" name="exibicao_dom" <? if($objBusca->exibicao_dom) echo 'checked' ?>>&nbsp;<label>Dom</label></td>
          <td>&nbsp;</td>
          <td><input type="checkbox" name="exibicao_seg" <? if($objBusca->exibicao_seg) echo 'checked' ?>>&nbsp;<label>Seg</label></td>
          <td>&nbsp;</td>
          <td><input type="checkbox" name="exibicao_ter" <? if($objBusca->exibicao_ter) echo 'checked' ?>>&nbsp;<label>Ter</label></td>
          <td>&nbsp;</td>
          <td><input type="checkbox" name="exibicao_qua" <? if($objBusca->exibicao_qua) echo 'checked' ?>>&nbsp;<label>Qua</label></td>
          <td>&nbsp;</td>
          <td><input type="checkbox" name="exibicao_qui" <? if($objBusca->exibicao_qui) echo 'checked' ?>>&nbsp;<label>Qui</label></td>
          <td>&nbsp;</td>
          <td><input type="checkbox" name="exibicao_sex" <? if($objBusca->exibicao_sex) echo 'checked' ?>>&nbsp;<label>Sex</label></td>
          <td>&nbsp;</td>
          <td><input type="checkbox" name="exibicao_sab" <? if($objBusca->exibicao_sab) echo 'checked' ?>>&nbsp;<label>Sab</label></td>
        </tr>
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
