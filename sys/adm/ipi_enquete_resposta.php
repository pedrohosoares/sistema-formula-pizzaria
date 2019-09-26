<?php

/**
 * ipi_enquete_resposta.php: Cadastro de Respostas de Enquete
 * 
 * Índice: cod_enquete_respostas
 * Tabela: ipi_enquete_respostas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Respostas');

$acao = validaVarPost('acao');
$cod_enquetes = validaVarPost('cod_enquetes');
$cod_enquete_perguntas = validaVarPost('cod_enquete_perguntas');


$tabela = 'ipi_enquete_respostas';
$chave_primaria = 'cod_enquete_respostas';

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
    $cod_enquete_perguntas = validaVarPost('cod_enquete_perguntas');
    $resposta = validaVarPost('resposta');
    $justifica = (validaVarPost('justifica') == 'on') ? 1 : 0;
    $justifica_opcional = (validaVarPost('justifica_opcional') == 'on') ? 1 : 0;
    
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (resposta, justifica, justifica_opcional, cod_enquete_perguntas) VALUES ('%s', %d, %d, %d)", 
                           $resposta, $justifica, $justifica_opcional, $cod_enquete_perguntas);

      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET resposta = '%s', justifica = %d, justifica_opcional = %d, cod_enquete_perguntas = %d WHERE $chave_primaria = $codigo", 
                            $resposta, $justifica, $justifica_opcional, $cod_enquete_perguntas);

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
      document.frmIncluir.cod_enquete_perguntas.value = '';
      document.frmIncluir.resposta.value = '';
      document.frmIncluir.justifica.checked = false;
      document.frmIncluir.justifica_opcional.checked = false;
      
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

</script>

<form name="frmEnquete" method="post" onsubmit="return validarEnquete(this);">
&nbsp;Enquete: <select name="cod_enquetes" style="width: 300px;">
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
<br>Pergunta: <select name="cod_enquete_perguntas" style="width: 300px;">
<option value="">Selecione uma pergunta desta enquete</option>
<?
$con = conectabd();
$SqlBuscaPerguntas = "SELECT * FROM ipi_enquete_perguntas WHERE cod_enquetes=".$cod_enquetes." ORDER BY pergunta";

$resBuscaPerguntas = mysql_query($SqlBuscaPerguntas);
          
while($objBuscaPerguntas = mysql_fetch_object($resBuscaPerguntas)) 
{
	echo '<option value="'.$objBuscaPerguntas->cod_enquete_perguntas.'" ';
	if(validaVarPost('cod_enquete_perguntas') == $objBuscaPerguntas->cod_enquete_perguntas)
		echo 'selected';
	echo '>'.bd2texto($objBuscaPerguntas->pergunta).'</option>';
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
              <td align="center">Resposta</td>
              <td align="center">Pergunta</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT * FROM $tabela p INNER JOIN ipi_enquete_perguntas ep ON (p.cod_enquete_perguntas = ep.cod_enquete_perguntas) WHERE ep.cod_enquete_perguntas='".$cod_enquete_perguntas."' ORDER BY $chave_primaria";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->pergunta).'</td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->resposta).'</a></td>';
            
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
          <li><a href="ipi_enquete_pergunta.php">Perguntas</a></li>
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
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_enquete_perguntas">Pergunta</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select class="requerido" name="cod_enquete_perguntas" id=cod_enquete_perguntas>
        <option value=""></option>
        
        <?
        $con = conectabd();
        
        $SqlBuscaPerguntas = "SELECT * FROM ipi_enquete_perguntas p INNER JOIN ipi_enquetes e ON (p.cod_enquetes = e.cod_enquetes) ORDER BY cod_enquete_perguntas DESC";
        $resBuscaPerguntas = mysql_query($SqlBuscaPerguntas);
        
        while($objBuscaPerguntas = mysql_fetch_object($resBuscaPerguntas)) {
          echo '<option value="'.$objBuscaPerguntas->cod_enquete_perguntas.'"';
          
          if($objBuscaPerguntas->cod_enquete_perguntas == $objBusca->cod_enquete_perguntas)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaPerguntas->enquete.' - '.$objBuscaPerguntas->pergunta).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="resposta">Resposta</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="resposta" id="resposta" maxlength="100" size="50" value="<? echo texto2bd($objBusca->resposta) ?>"></td></tr>
    
    <tr><td class="tdbl tdbr"><input type="checkbox" name="justifica" <? if($objBusca->justifica) echo 'checked' ?>>&nbsp;<label for="justifica">Justificativa</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="checkbox" name="justifica_opcional" <? if($objBusca->justifica_opcional) echo 'checked' ?>>&nbsp;<label for="justifica_opcional">Resposta da Justificativa Opcional</label></td></tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>