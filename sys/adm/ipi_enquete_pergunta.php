<?php

/**
 * ipi_enquete_pergunta.php: Cadastro de Perguntas de Enquete
 * 
 * Índice: cod_enquete_perguntas
 * Tabela: ipi_enquete_perguntas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Perguntas');

$acao = validaVarPost('acao');

$tabela = 'ipi_enquete_perguntas';
$chave_primaria = 'cod_enquete_perguntas';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlDel))
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, verifique se a pizzaria não está reposável por algum bairro (cep).');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $cod_enquetes = validaVarPost('cod_enquetes');
    $pergunta = validaVarPost('pergunta');
    $pergunta_pessoal =  validaVarPost('pergunta_pessoal');
    $cod_enquete_perguntas_pai =  validaVarPost('cod_enquete_perguntas_pai');
    $respostas = validaVarPost('check_respostas');
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (pergunta, cod_enquetes,pergunta_pessoal,cod_enquete_perguntas_pai) VALUES ('%s', %d, %d, %d)", 
                           $pergunta, $cod_enquetes,$pergunta_pessoal,$cod_enquete_perguntas_pai);

      

      if(mysql_query($SqlEdicao))
      {

        if($cod_enquete_perguntas_pai !="")
        {
          $codigo2 = mysql_insert_id();
          $sql_perguntas_permitidas = "delete from ipi_enquete_respostas_permitidas where $chave_primaria = $codigo2";
          mysql_query($sql_perguntas_permitidas);
          if(count($respostas)>0)
          {
              
               for($c = 0; $c<count($respostas); $c++)
              {
                $sql_permitidas_novo = 'insert into ipi_enquete_respostas_permitidas(cod_enquete_perguntas,cod_enquete_respostas) values('.$codigo.','.$respostas[$c].')';
                $res_permitidas_novo = mysql_query($sql_permitidas_novo);
              }

          }
        }
       mensagemOk('Registro adicionado com êxito!');
      }
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET pergunta = '%s', cod_enquetes = %d, pergunta_pessoal = %d, cod_enquete_perguntas_pai = %d WHERE $chave_primaria = $codigo", 
                            $pergunta, $cod_enquetes,$pergunta_pessoal,$cod_enquete_perguntas_pai);


      if(mysql_query($SqlEdicao))
      {
          
        $sql_perguntas_permitidas = "delete from ipi_enquete_respostas_permitidas where $chave_primaria = $codigo";
        mysql_query($sql_perguntas_permitidas);
        if($cod_enquete_perguntas_pai !="")
        {

          if(count($respostas)>0)
          {
              for($c = 0; $c<count($respostas); $c++)
              {
                $sql_permitidas_novo = 'insert into ipi_enquete_respostas_permitidas(cod_enquete_perguntas,cod_enquete_respostas) values('.$codigo.','.$respostas[$c].')';
                $res_permitidas_novo = mysql_query($sql_permitidas_novo);
              }
            

          }
        
        }
        
        mensagemOk('Registro adicionado com êxito!');
      }  
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
    chamar_ajax($('cod_enquetes_perguntas_pai').value,$('perguntaid').value);
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.<? echo $chave_primaria ?>.value = '';
      document.frmIncluir.cod_enquetes.value = '';
      document.frmIncluir.pergunta.value = '';
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });

});


function validarEnquete(formulario) {

	if (formulario.cod_enquetes.value == "") {
      alert('Selecione uma enquete.');
      formulario.cod_enquetes.focus();
      return false;
  	}
	return true;
}


function chamar_ajax(cod,var3) {
   
    var acao = 'chamar_perguntas_pai';
    var var2 = cod;
    var var3 = var3;
      var url = 'acao=' + acao + '&var2=' + var2 + '&var3=' + var3;
      
      new Request.HTML({
        url: 'ipi_enquete_ajax.php',
        update: $('respostas')
      }).send(url);
      
      
    
  
}


</script>

<form name="frmEnquete" method="post" onsubmit="return validarEnquete(this);">

Enquete: <select name="cod_enquetes" style="width: 300px;">
<option value="">Selecione uma enquete</option>
<?
$con = conectabd();
$SqlBuscaEnquetes = "SELECT * FROM ipi_enquetes ORDER BY enquete";
$resBuscaEnquetes = mysql_query($SqlBuscaEnquetes);
          
while($objBuscaEnquetes = mysql_fetch_object($resBuscaEnquetes)) 
{
	echo '<option value="'.$objBuscaEnquetes->cod_enquetes.'" ';
	if(validaVarPost('cod_enquetes') == $objBuscaEnquetes->cod_enquetes)
		echo 'selected';
	echo '>'.bd2texto($objBuscaEnquetes->enquete).'</option>';
}
desconectabd($con);
?>
</select>
<input class="botaoAzul" type="submit" value="Filtrar">
</form>

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
              <td align="center">Pergunta</td>
              <td align="center">Enquete</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT * FROM $tabela p INNER JOIN ipi_enquetes e ON (p.cod_enquetes = e.cod_enquetes) WHERE e.cod_enquetes='".validaVarPost('cod_enquetes')."' ORDER BY $chave_primaria";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->pergunta).'</a></td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->enquete).'</td>';
            
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
          <li><a href="ipi_enquete.php">Enquetes</a></li>
          <li><a href="ipi_enquete_resposta.php">Respostas</a></li>
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
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_enquetes">Enquete</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select class="requerido" name="cod_enquetes" id=cod_enquetes>
        <option value='0'></option>
        
        <?
        $con = conectabd();
        
        $SqlBuscaEnquetes = "SELECT * FROM ipi_enquetes ORDER BY cod_enquetes DESC";
        $resBuscaEnquetes = mysql_query($SqlBuscaEnquetes);
        
        while($objBuscaEnquetes = mysql_fetch_object($resBuscaEnquetes)) {
          echo '<option value="'.$objBuscaEnquetes->cod_enquetes.'"';
          
          if($objBuscaEnquetes->cod_enquetes == $objBusca->cod_enquetes)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaEnquetes->enquete).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td></tr>

    <tr><td class="legenda tdbl tdbr"><label>Pergunta Pai</label></td></tr>
    <tr><td class="tdbl tdbr sep"><select name="cod_enquete_perguntas_pai" id="cod_enquetes_perguntas_pai" onChange="chamar_ajax(this.value,'')">
        <option value=""></option>
      <?
        $con = conectabd();
        
        $SqlBuscaEnquetes = "SELECT * FROM ipi_enquete_perguntas ORDER BY cod_enquete_perguntas DESC";
        $resBuscaEnquetes = mysql_query($SqlBuscaEnquetes);
        
        while($objBuscaEnquetes = mysql_fetch_object($resBuscaEnquetes)) {
          echo '<option value="'.$objBuscaEnquetes->cod_enquete_perguntas.'"';
          
          if($objBuscaEnquetes->cod_enquete_perguntas == $objBusca->cod_enquete_perguntas_pai)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaEnquetes->pergunta).'</option>';
        }
        
        desconectabd($con);
        ?>
      
      </td></tr>
    <tr><td><div id="respostas" name="respostas"></div></td></tr>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="pergunta">Pergunta</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="pergunta" id="pergunta" maxlength="200" size="50" value="<? echo texto2bd($objBusca->pergunta) ?>"> <input type="hidden" name="perguntaid" id="perguntaid" value="<? echo texto2bd($objBusca->cod_enquete_perguntas) ?>"></td></tr>
    
    
    <tr><td class="legenda tdbl tdbr"><label>Pergunta Pessoal</label></td></tr>
    <tr><td class="tdbl tdbr sep"><select name="pergunta_pessoal" id="pergunta_pessoal" >
                                    <option value=""></option>
                                    <option value="1"<? if($objBusca->pergunta_pessoal==1) echo 'selected '?>>Sim</option>
                                    <option value="0"<? if($objBusca->pergunta_pessoal==0) echo 'selected '?>>Não</option>
                                  </select>
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
