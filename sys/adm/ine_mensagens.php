<?php

/**
 * exemplo.php: Cadastro de Mensagens
 * 
 * Índice: cod_mensagens
 * Tabela: ine_mensagens
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Mensagens');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_mensagens';
$tabela = 'ine_mensagens';

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
    $assunto = texto2bd(validaVarPost('assunto'));
    $mensagem = texto2bd(validaVarPost('mensagem'));
    //$agendamento = data2bd(validaVarPost('agendamento'));
    $avancada = validaVarPost('modo_avancado');
    $imagem_cabecalho = validaVarPost('imagem_cabecalho');
    $imagem_conteudo = validaVarPost('imagem_conteudo');
    $imagem_rodape = validaVarPost('imagem_rodape');
    
    if($avancada)
    {
    	$avancada = 1;
    }else
    {
			$avancada = 0;    
    }
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (assunto, mensagem, cod_imagens_mensagem, cod_imagens_cabecalho, cod_imagens_rodape,mensagem_avancada) VALUES ('%s', '%s', '%d', '%d', '%d',%d)", 
                           $assunto, $mensagem, $imagem_conteudo, $imagem_cabecalho, $imagem_rodape,$avancada);
    
    	
      if(mysql_query($SqlEdicao))
      {
        mensagemOk('Registro adicionado com êxito!');
        mkdir(UPLOAD_DIR."/newsletter/".mysql_insert_id(),0777);
      }
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET assunto = '%s', mensagem = '%s', cod_imagens_mensagem = '%d', cod_imagens_cabecalho = '%d', cod_imagens_rodape = '%d',mensagem_avancada = %d WHERE $chave_primaria = $codigo", 
                           $assunto, $mensagem, $imagem_conteudo, $imagem_cabecalho, $imagem_rodape,$avancada);

      if(mysql_query($SqlEdicao))
      {
        mensagemOk('Registro adicionado com êxito!');
        if(is_dir(UPLOAD_DIR."/newsletter/".$codigo))
        {
        
        }else
        {
        	mkdir(UPLOAD_DIR."/newsletter/".$codigo,0777);
        }
      }
      else
        mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    desconectabd($con);
  break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

<script type="text/javascript" src="../lib/js/mascara.js"></script>
<script type="text/javascript" src="../../sys/lib/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
  mode : "textareas",
  theme : "advanced",
  skin : "o2k7",
  language : "pt",
  plugins: "inlinepopups,fullscreen",
  theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,|,bullist,numlist,|,link,unlink,|,fullscreen,|,code",
  theme_advanced_buttons2 : "",
  theme_advanced_buttons3 : "",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_statusbar_location : "bottom",
  theme_advanced_resizing : false,
  auto_reset_designmode : true,
  entity_encoding : "raw",
 	forced_root_block : false
});
</script>
<script>
 forced_root_block : false
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
      document.frmIncluir.assunto.value = '';
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
});

function desabilitar_cab_cont_rod(bool)
{
	$('imagem_cabecalho').disabled = !$('imagem_cabecalho').disabled;
	$('imagem_conteudo').disabled = !$('imagem_conteudo').disabled;
	$('imagem_rodape').disabled = !$('imagem_rodape').disabled;
}

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
              <td align="center">Assunto</td>
              <td align="center">Avançada</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT * FROM $tabela ORDER BY cod_mensagens DESC";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->assunto).'</a></td>';
            echo '<td align="center"><a href="javascript:;">'.($objBuscaRegistros->mensagem_avancada ? "Sim" : "Não").'</a></td>';
            
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
          <li><a href="ine_emails_cadastro.php">Cadastro de E-Mails</a></li>
          <li><a href="ine_imagens.php">Cadastro de Imagens</a></li>
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
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="assunto">Assunto</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="assunto" id="assunto" maxlength="250" size="97" value="<? echo bd2texto($objBusca->assunto) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="imagem_cabecalho">Modo Avançado</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="checkbox" id="modo_avancado" name="modo_avancado" <? echo ($objBusca->mensagem_avancada ==1 ? "checked" : "" ) ?> onclick="desabilitar_cab_cont_rod(true)" value="1" />  </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="imagem_cabecalho">Cabeçalho</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select name="imagem_cabecalho"  <? echo ($objBusca->mensagem_avancada ==1 ? "disabled" : "" ) ?> id="imagem_cabecalho">
        <option value=""></option>
        
        <?
        $con = conectabd();
        
        $SqlBuscaImagem = "SELECT * FROM ine_imagens WHERE tipo = 'CABECALHO'";
        $resBuscaImagem = mysql_query($SqlBuscaImagem);
        
        while($objBuscaImagem = mysql_fetch_object($resBuscaImagem)) {
          echo '<option value="'.$objBuscaImagem->cod_imagens.'" ';
          
          if($objBuscaImagem->cod_imagens == $objBusca->cod_imagens_cabecalho)
            echo 'selected';
            
          echo '>'.bd2texto($objBuscaImagem->titulo).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>  
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="imagem_conteudo">Imagens de Conteúdo</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select name="imagem_conteudo" <? echo ($objBusca->mensagem_avancada ==1 ? "disabled" : "" ) ?> id="imagem_conteudo">
        <option value=""></option>
        <?
        $con = conectabd();
        
        $SqlBuscaImagem = "SELECT * FROM ine_imagens WHERE tipo = 'IMAGEM'";
        $resBuscaImagem = mysql_query($SqlBuscaImagem);
        
        while($objBuscaImagem = mysql_fetch_object($resBuscaImagem)) {
          echo '<option value="'.$objBuscaImagem->cod_imagens.'" ';
          
          if($objBuscaImagem->cod_imagens == $objBusca->cod_imagens_mensagem)
            echo 'selected';
            
          echo '>'.bd2texto($objBuscaImagem->titulo).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>  
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="imagem_rodape">Rodapé</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select name="imagem_rodape" <? echo ($objBusca->mensagem_avancada ==1 ? "disabled" : "" ) ?> id="imagem_rodape">
        <option value=""></option>
        <?
        $con = conectabd();
        
        $SqlBuscaImagem = "SELECT * FROM ine_imagens WHERE tipo = 'RODAPE'";
        $resBuscaImagem = mysql_query($SqlBuscaImagem);
        
        while($objBuscaImagem = mysql_fetch_object($resBuscaImagem)) {
          echo '<option value="'.$objBuscaImagem->cod_imagens.'" ';
          
          if($objBuscaImagem->cod_imagens == $objBusca->cod_imagens_rodape)
            echo 'selected';
            
          echo '>'.bd2texto($objBuscaImagem->titulo).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>  
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="mensagem">Mensagem</label><small>*Proibido tags head,html,body</small></td></tr>
    <tr><td class="tdbl tdbr sep"><textarea name="mensagem" rows="13" cols="100"><? echo bd2texto($objBusca->mensagem)?></textarea></td></tr>
    
    <!-- 
    <tr><td class="legenda tdbl tdbr"><label for="agendamento">Agendamento</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="text" name="agendamento" id="agendamento" maxlength="10" size="12" onkeypress="return MascaraData(this, event)" value="<? echo bd2data($objBusca->agendamento) ?>"></td></tr>
    -->
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
