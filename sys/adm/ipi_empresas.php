<?php

/**
 * ipi_tamanho.php: Cadastro de Empresas
 * 
 * Índice: cod_empresas
 * Tabela: ipi_empresas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Empresas');

$acao = validaVarPost('acao');

$tabela = 'ipi_empresas';
$chave_primaria = 'cod_empresas';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel5 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    $resDel5 = mysql_query($SqlDel5);
    
    if ($resDel5)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;

  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $nome_empresa = validaVarPost('nome_empresa');
    $logo_pequeno = validaVarFiles('logo_pequeno');
    $logo_medio = validaVarFiles('logo_medio');
    $logo_grande = validaVarFiles('logo_grande');
    
    $con = conectabd();
    
    if($codigo <= 0) 
    {
      $SqlEdicao = sprintf("INSERT INTO $tabela (nome_empresa) VALUES ('%s')", 
                           $nome_empresa);

      if(mysql_query($SqlEdicao))
      {
          $codigo = mysql_insert_id();
          $resEdicao = true;
          mensagemOk('Registro adicionado com êxito!');
      }
       
      else
      { 
          $resEdicao = false;
          mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
      }
       
    }

    else
    {
      $SqlEdicao = sprintf("UPDATE $tabela SET nome_empresa = '%s' WHERE $chave_primaria = $codigo", $nome_empresa);

      if(mysql_query($SqlEdicao))
      {
        $resEdicao = true;
        mensagemOk('Registro alterado com êxito!');
      }
      else
      {
        $resEdicao = false;
        mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
      }
    }

    if ($resEdicao)
        {
          $resEdicaoImagem = true;
          // Alterando Logo pequeno
          if(count($logo_pequeno['name']) > 0) 
          {     
            if(trim($logo_pequeno['name']) != '') {
              $arq_info = pathinfo($logo_pequeno['name']);
              $arq_ext = $arq_info['extension'];
              if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $logo_pequeno["type"])) {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
              }
              else {                
                $resEdicaoImagem &= move_uploaded_file($logo_pequeno['tmp_name'], UPLOAD_DIR."/empresas/${codigo}_empresa_p.${arq_ext}");
                                        
                $SqlEdicaoImagem = sprintf("UPDATE $tabela set logo_pequeno = '%s' WHERE $chave_primaria = $codigo", texto2bd("${codigo}_empresa_p.${arq_ext}"));
                
                $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);

                
              }
            }          
          }

          
          // Alterando Logo medio
          if(count($logo_medio['name']) > 0) 
          {     
            if(trim($logo_medio['name']) != '') {
              $arq_info = pathinfo($logo_medio['name']);
              $arq_ext = $arq_info['extension'];
              if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $logo_medio["type"])) {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
              }
              else {                
                $resEdicaoImagem &= move_uploaded_file($logo_medio['tmp_name'], UPLOAD_DIR."/empresas/${codigo}_empresa_m.${arq_ext}");
                                        
                $SqlEdicaoImagem = sprintf("UPDATE $tabela set logo_medio = '%s' WHERE $chave_primaria = $codigo", texto2bd("${codigo}_empresa_m.${arq_ext}"));
                
                $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);

                
              }
            }          
          }

          // Alterando Logo grande
          if(count($logo_grande['name']) > 0) 
          {     
            if(trim($logo_grande['name']) != '') {
              $arq_info = pathinfo($logo_grande['name']);
              $arq_ext = $arq_info['extension'];
              if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $logo_grande["type"])) {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
              }
              else {                
                $resEdicaoImagem &= move_uploaded_file($logo_grande['tmp_name'], UPLOAD_DIR."/empresas/${codigo}_empresa_g.${arq_ext}");
                                        
                $SqlEdicaoImagem = sprintf("UPDATE $tabela set logo_grande = '%s' WHERE $chave_primaria = $codigo", texto2bd("${codigo}_empresa_g.${arq_ext}"));
                
                $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);

                
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

function excluirLogoPequeno(cod) 
{
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) 
  {
    var acao = 'excluir_logo_pequeno';
    var cod_empresas = cod;

    if(cod_empresas > 0) {
      var url = 'acao=' + acao + '&cod_empresas=' + cod_empresas;

      new Request.JSON({url: 'ipi_empresas_ajax.php', onComplete: function(retorno) 
      {
        if(retorno.status != 'OK') {
          alert('Erro ao excluir esta imagem.');
        }
        else 
        {
          if($('logo_pequeno_figura')) 
          {
            $('logo_pequeno_figura').destroy();

          }
        }
      }}).send(url);
    }
  }
}

function excluirLogoMedio(cod) 
{
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) 
  {
    var acao = 'excluir_logo_medio';
    var cod_empresas = cod;

    if(cod_empresas > 0) {
      var url = 'acao=' + acao + '&cod_empresas=' + cod_empresas;

      new Request.JSON({url: 'ipi_empresas_ajax.php', onComplete: function(retorno) 
      {
        if(retorno.status != 'OK') {
          alert('Erro ao excluir esta imagem.');
        }
        else 
        {
          if($('logo_medio_figura')) 
          {
            $('logo_medio_figura').destroy();

          }
        }
      }}).send(url);
    }
  }
}

function excluirLogoGrande(cod) 
{
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) 
  {
    var acao = 'excluir_logo_grande';
    var cod_empresas = cod;

    if(cod_empresas > 0) {
      var url = 'acao=' + acao + '&cod_empresas=' + cod_empresas;

      new Request.JSON({url: 'ipi_empresas_ajax.php', onComplete: function(retorno) 
      {
        if(retorno.status != 'OK') {
          alert('Erro ao excluir esta imagem.');
        }
        else 
        {
          if($('logo_grande_figura')) 
          {
            $('logo_grande_figura').destroy();

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
      document.frmIncluir.nome_empresa.value = '';

      if ($('logo_grande_figura'))
      {
        $('logo_grande_figura').destroy();
      }

     if ($('logo_medio_figura'))
      {
        $('logo_medio_figura').destroy();
      }

      if ($('logo_pequeno_figura'))
      {
        $('logo_pequeno_figura').destroy();
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
              <td align="center">Empresa</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBusca = "SELECT * FROM $tabela ORDER BY nome_empresa";
          $resBusca = mysql_query($SqlBusca);
          
          while ($objBusca = mysql_fetch_object($resBusca)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBusca->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBusca->$chave_primaria.')">'.bd2texto($objBusca->nome_empresa).'</a></td>';
            
            echo '</tr>';
          }
          
          desconectabd($con);
          
          ?>
          
          </tbody>
        </table>
      
        <input type="hidden" name="acao" value="excluir">
      </form>
    
    </td>
    
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
      
      <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="nome_empresa">Empresa</label></td></tr>
      <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="nome_empresa" id="nome_empresa" maxlength="45" size="45" value="<? echo texto2bd($objBusca->nome_empresa) ?>"></td></tr>

      <!-- LOGO_PEQUENO -->
      <tr>
      <td class="legenda tdbl tdbr"><label for="logo_pequeno">Logo pequeno (*.png, *.jpg)</label></td>
      </tr>
             
      <?
      if (is_file(UPLOAD_DIR . '/empresas/' . $objBusca->logo_pequeno))
      {
          echo '<tr><td class="sep tdbl tdbr" align="center" id="logo_pequeno_figura" style="padding: 15px;">';
          
          echo '<img style=" max-width: 80px ; max-height:40px" src="' . UPLOAD_DIR . '/empresas/' . $objBusca->logo_pequeno . '">';
          
          echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirLogoPequeno(' . $objBusca->$chave_primaria . ');"></td></tr>';
      }
      ?>  

      <tr>
      <td class="sep tdbl tdbr sep"><input type="file" name="logo_pequeno"
        id="logo_pequeno" size="40"></td>
     </tr>

     <!-- LOGO_MEDIO -->
      <tr>
      <td class="legenda tdbl tdbr"><label for="logo_medio">Logo medio (*.png, *.jpg)</label></td>
      </tr>
             
      <?
      if (is_file(UPLOAD_DIR . '/empresas/' . $objBusca->logo_medio))
      {
          echo '<tr><td class="sep tdbl tdbr" align="center" id="logo_medio_figura" style="padding: 15px;">';
          
          echo '<img style=" max-width: 140px ; max-height:140px" src="' . UPLOAD_DIR . '/empresas/' . $objBusca->logo_medio . '">';
          
          echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirLogoMedio(' . $objBusca->$chave_primaria . ');"></td></tr>';
      }
      ?>  
      
      <tr>
      <td class="sep tdbl tdbr sep"><input type="file" name="logo_medio"
        id="logo_medio" size="40"></td>
     </tr>

     <!-- LOGO_GRANDE -->
     <tr>
      <td class="legenda tdbl tdbr"><label for="logo_grande">Logo grande (*.png, *.jpg)</label></td>
      </tr>
             
      <?
      if (is_file(UPLOAD_DIR . '/empresas/' . $objBusca->logo_grande))
      {
          echo '<tr><td class="sep tdbl tdbr" align="center" id="logo_grande_figura" style="padding: 15px;">';
          
          echo '<img style=" max-height: 200px; max-width: 200px" src="' . UPLOAD_DIR . '/empresas/' . $objBusca->logo_grande . '">';
          
          echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirLogoGrande(' . $objBusca->$chave_primaria . ');"></td></tr>';
      }
      ?>  

      <tr>
      <td class="sep tdbl tdbr sep"><input type="file" name="logo_grande"
        id="logo_grande" size="40"></td>
     </tr>


      <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
      
      </table>
      
      <input type="hidden" name="acao" value="editar">
      <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
      
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
