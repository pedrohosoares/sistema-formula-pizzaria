<?php

/**
 * ine_ger_images.php: Gerenciamento de Imagens
 * Para cadastro de várias imagens a serem usadas em determinada mensagem. 
 *
 * Índice: cod_imagens
 * Tabela: ine_imagens
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Gerenciamento de Imagens');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_mensagens';
$tabela = 'ine_mensagens';

switch($acao) {
  case 'editar':
    $codigo  = validar_var_post($chave_primaria);
    $arquivo = validar_var_file('arquivo');
    $con = conectabd();   
      if ((is_array($arquivo['name'])) && (count($arquivo['name']) > 0))
      {        
        for ($i = 0; $i < count($arquivo['name']); $i++)
        {  
          if(trim($arquivo['name'][$i]) != '') {
            $arq_info = pathinfo($arquivo['name'][$i]);
                  
            if(!eregi("^image|application\\/(pjpeg|jpeg|jpg|swf|png|gif|x-shockwave-flash)$", $arquivo["type"][$i])) {
              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png, *.gif, *.swf).');
            }
            else {
              if(move_uploaded_file($arquivo['tmp_name'][$i], UPLOAD_DIR."/newsletter/${codigo}/${arq_info[basename]}")) 
              {
                $SqlEdicaoImagem = sprintf("INSERT INTO ine_imagens(tipo, arquivo, cod_mensagens) VALUES ('AVANÇADO', '%s', %d)", 
                                           "${arq_info[basename]}", $codigo);
                
                if(mysql_query($SqlEdicaoImagem))
                  mensagemOk('Registro alterado com êxito!');
                else
                  mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado1.');
              }
              else 
              {
                mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado2.');
              }
            }
          }
        }
      }   

    
    desconectabd($con);
  break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

<script>

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

function excluirImagem(cod, cod2) {
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
    var acao = 'excluir_imagem';
    var cod_imagens = cod;
    var cod_mensagens = cod2;
    
    if(cod_imagens> 0) {
      var url = 'acao=' + acao + '&cod_imagens=' + cod_imagens + '&cod_mensagens=' + cod_mensagens;
      
      new Request.JSON({url: 'ine_ger_imagens_ajax.php', onComplete: function(retorno) {
        if(retorno.status != 'OK') {
          alert('Erro ao excluir esta imagem.');
        }
        else {
          if($('imagem'+cod_imagens)) {
            $('imagem'+cod_imagens).destroy();
          }
        }
      }}).send(url);
    }
  }
}

function inserir_imagem()
{
  var num = $("num_input").selectedIndex;
  for (i = 0; i <= num; i++)
  {
    var div_input = new Element('div');
    div_input.innerHTML = "<input type='file' name='arquivo[]' size='40' style='margin: 2px;'>  <br /\>";
    $("inserir_imagens").appendChild(div_input);
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
      if($('imagens')) {
          $('imagens').destroy();
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
    
      <form name="form_" method="post">
          
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <td align="center">Mensagem</td>
              <td align="center">Nº Imagens adicionadas</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT im.cod_mensagens, im.assunto, (SELECT COUNT(cod_imagens) FROm ine_imagens ii WHERE ii.cod_mensagens = im.cod_mensagens) as img_inseridas FROM $tabela im WHERE im.mensagem_avancada = '1' ORDER BY im.cod_mensagens DESC";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';            
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->cod_mensagens.')">'.bd2texto($objBuscaRegistros->assunto).'</a></td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->img_inseridas).'</td>';
            echo '</tr>';
          }
          
          desconectabd($con);
          
          ?>
          
          </tbody>
        </table>
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
    ?>
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    <tr><td class="legenda tdbl tdbt tdbr" align="center" style="font-size: 14px;">
     <!-- 
     <strong> Endereço das imagens: </strong> <? echo HOST.'/upload/newsletter/'.$codigo.'/</label>'; ?>
      -->
     </td></tr>
    <tr><td>
    <table id='imagens' align='center' class="caixa" cellpadding="0" cellspacing="0" style=" width:100%;">
    <?    
      if($codigo > 0) 
      {
        $i = 0;
        $con = conectar_bd();
        $sql_busca = "SELECT * FROM $tabela im INNER JOIN ine_imagens ii ON ( im.cod_mensagens = ii.cod_mensagens ) WHERE im.$chave_primaria = $codigo";
    		$res_busca = mysql_query($sql_busca);
        while($obj_busca = mysql_fetch_object($res_busca))
    		{
          if(is_file(UPLOAD_DIR.'/newsletter/'.$codigo.'/'.$obj_busca->arquivo)) {
            echo '<tr><td class="sep tdbl tdbr" align="center" id="imagem'.$obj_busca->cod_imagens.'" style="padding: 15px;">';
            
            $info = pathinfo(UPLOAD_DIR.'/newsletter/'.$codigo.'/'.$obj_busca->arquivo);
            
            if($info['extension'] == 'swf'){
              echo '<param name="movie" value="'.UPLOAD_DIR.'/newsletter/'.$codigo.'/'.$obj_busca->arquivo.'">';
              echo '<embed src="'.UPLOAD_DIR.'/newsletter/'.$codigo.'/'.$obj_busca->arquivo.'">';
              echo '</embed>';
              echo '</object>';
              echo '<br /><small>Arquivo: '.$obj_busca->arquivo.'</small>';
            }
            else{
              echo '<img id="img'.$i.'" src="'.UPLOAD_DIR.'/newsletter/'.$codigo.'/'.$obj_busca->arquivo.'">';
              echo '<br /><small>http://'.HOST.'/upload/newsletter/'.$codigo.'/'.$obj_busca->arquivo.'</small>';
            }
            $i += 1;
            echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem('.$obj_busca->cod_imagens.', '.$obj_busca->$chave_primaria.');"></td></tr>';
          }
        }
        desconectar_bd($con);
      }
    ?>
    </table></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido">Imagem (*.gif, *.png, *.jpg, *.swf)</label><br /><small>O tamanho do nome imagem não pode execeder 50 caracteres.</small></td></tr>
        
    <tr><td class="sep tdbl tdbr" align="center"> 
      <div id="inserir_imagens" style="padding: 8px;">
        <input type="file" name="arquivo[]" size="40" style="margin: 2px;">  <br />
        <input type="file" name="arquivo[]" size="40" style="margin: 2px;">  <br />
        <input type="file" name="arquivo[]" size="40" style="margin: 2px;">  <br />
        <input type="file" name="arquivo[]" size="40" style="margin: 2px;">  <br />
        <input type="file" name="arquivo[]" size="40" style="margin: 2px;">  <br />
        <input type="file" name="arquivo[]" size="40" style="margin: 2px;">  <br />
      </div>
      <select id='num_input' style='width="40px;"'> 
        <?
          for ($i = 1; $i <= 20; $i++)
          {
            echo "<option value='".$i."'>&nbsp;".$i."&nbsp;</option>";
          } 
        ?>
      </select>
      &nbsp;&nbsp;
      <input class="botaoAzul" type="button" value="&nbsp;Adicionar linhas&nbsp;" onclick="javascript: inserir_imagem();">
    </td></tr>  
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
