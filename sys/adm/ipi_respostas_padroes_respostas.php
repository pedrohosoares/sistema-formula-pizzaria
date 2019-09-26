<?php

/**
 * ipi_enquete_pergunta.php: Cadastro de Respostas padores
 * 
 * Índice: cod_respostas
 * Tabela: ipi_respostas_resposta_padrao
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Perguntas');

$acao = validaVarPost('acao');

$tabela = 'ipi_respostas_resposta_padrao';
$chave_primaria = 'cod_respostas';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "UPDATE $tabela SET situacao='EXCLUIDO' WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlDel))
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, verifique se a pizzaria não está reposável por algum bairro (cep).');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $cod_respostas_categorias = validaVarPost('cod_categorias');
    $nome_resposta = validaVarPost('nome_resposta');
    $mensagem_resposta =  validaVarPost('mensagem_resposta');
    $cod_respostas_cupom =  validaVarPost('cod_cupoms');
    $situacao = validaVarPost('situacao');
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (cod_respostas_categorias, cod_respostas_cupom,nome_resposta,mensagem_resposta,situacao) VALUES (%d, %d, '%s', '%s','%s')", 
                           $cod_respostas_categorias, $cod_respostas_cupom,$nome_resposta,$mensagem_resposta,$situacao);

      if(mysql_query($SqlEdicao))
      {

        /*if($cod_enquete_perguntas_pai !="")
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
        }*/
       mensagemOk('Registro adicionado com êxito!');
      }
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET mensagem_resposta = '%s', cod_respostas_cupom = %d, nome_resposta = '%s', cod_respostas_categorias = %d , situacao = '%s' WHERE $chave_primaria = $codigo", 
                            $mensagem_resposta, $cod_respostas_cupom,$nome_resposta,$cod_respostas_categorias,$situacao);


      if(mysql_query($SqlEdicao))
      {
          
        /*$sql_perguntas_permitidas = "delete from ipi_enquete_respostas_permitidas where $chave_primaria = $codigo";
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
        
        }*/
        
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

function visualizar_mensagem_final() {

  <? $nome_gerente =  explode(' ',$_SESSION['usuario']['nome']); 
     $nome_gerente = $nome_gerente[0];
  ?>
  var nome_gerente = "<? echo $nome_gerente ?>";
  var numero_cupom = "AAWDWQ=AQW+";
  var nome_cliente = "João";
  var validade_cupom = "21/12/2012";

  var texto_limpo = $('mensagem_resposta').value;

  var texto_modificado = texto_limpo.replace(/##NOME_CLIENTE##/g,nome_cliente);
  texto_modificado = texto_modificado.replace(/##NOME_GERENTE##/g,nome_gerente);
  texto_modificado = texto_modificado.replace(/##VALIDADE_CUPOM##/g,validade_cupom);
  texto_modificado = texto_modificado.replace(/##NUMERO_CUPOM##/g,numero_cupom);

  $('visualizar_mensagem').set('value',texto_modificado);
}

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
  var inputf = new Element('input', {
    'type': 'hidden',
    'name': 'cod_categorias',
    'value': '<? echo validaVarPost("cod_categorias") ?>'
  });
  input.inject(form);
  inputf.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

window.addEvent('domready', function(){

  var tabs = new Tabs('tabs'); 


  if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) {
    <? if ($acao == '') echo 'tabs.irpara(1);'; ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
    //chamar_ajax($('cod_enquetes_perguntas_pai').value,$('perguntaid').value);
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.<? echo $chave_primaria ?>.value = '';
      document.frmIncluir.cod_categorias.value = '';
      document.frmIncluir.nome_resposta.value = '';
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });

});


function validarEnquete(formulario) {

	if (formulario.cod_categorias.value == "") {
      alert('Selecione uma categoria.');
      formulario.cod_categorias.focus();
      return false;
  	}
	return true;
}



/*function chamar_ajax(cod,var3) {
   
    var acao = 'chamar_perguntas_pai';
    var var2 = cod;
    var var3 = var3;
      var url = 'acao=' + acao + '&var2=' + var2 + '&var3=' + var3;
      
      new Request.HTML({
        url: 'ipi_enquete_ajax.php',
        update: $('respostas')
      }).send(url);
}*/


</script>

<form name="frmEnquete" method="post" onsubmit="return validarEnquete(this);">

Categoria: <select name="cod_categorias" style="width: 300px;">
<option value="">Selecione uma categoria</option>
<?
$con = conectabd();
$SqlBuscaEnquetes = "SELECT * FROM ipi_respostas_categorias ORDER BY nome_categoria";
$resBuscaEnquetes = mysql_query($SqlBuscaEnquetes);
          
while($objBuscaEnquetes = mysql_fetch_object($resBuscaEnquetes)) 
{
	echo '<option value="'.$objBuscaEnquetes->cod_respostas_categorias.'" ';
	if(validaVarPost('cod_categorias') == $objBuscaEnquetes->cod_respostas_categorias)
		echo 'selected';
	echo '>'.bd2texto($objBuscaEnquetes->nome_categoria).'</option>';
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
          
          $SqlBuscaRegistros = "SELECT * FROM $tabela r INNER JOIN ipi_respostas_categorias c ON (r.cod_respostas_categorias = c.cod_respostas_categorias) WHERE c.cod_respostas_categorias='".validaVarPost('cod_categorias')."' ORDER BY $chave_primaria";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          //echo $SqlBuscaRegistros;
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->nome_resposta).'</a></td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->mensagem_resposta).'</td>';
            
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
          <li><a href="ipi_respostas_padroes_cupom.php">Pré cadastro de cupoms</a></li>
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
            <table align="right" class="caixa" cellpadding="0" cellspacing="0">
      <tr>
        <td>Legenda:
        </td>
      </tr>
      <tr>
        <td>##NOME_CLIENTE## : Esse texto será substituido pelo nome do cliente que respondeu a enqeuete
        </td>
      </tr>
      <tr>
        <td>##NOME_GERENTE## : Esse texto será substituido pelo nome do SEU usuario
        </td>
      </tr>
      <tr>
        <td>##NUMERO_CUPOM## : Esse texto será substituido pelo código do cupom escolhido
        </td>
      </tr>
      <tr>
        <td>##VALIDADE_CUPOM## : Esse texto será substituido pela data de validade do cupom
        </td>
      </tr>
      <tr>
        <td><br/><br/><textarea name="visualizar_mensagem" id="visualizar_mensagem" rows="11" cols="75"></textarea>
        </td>
      </tr>
    </table>
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_categorias">Categoria</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select class="requerido" name="cod_categorias" id="cod_categorias">
        <option value='0'></option>
        
        <?
        $con = conectabd();
        
        $SqlBuscaCategorias = "SELECT * FROM ipi_respostas_categorias WHERE situacao='ATIVO' ORDER BY cod_respostas_categorias DESC";
        $resBuscaCategorias = mysql_query($SqlBuscaCategorias);
        
        while($objBuscaCategorias = mysql_fetch_object($resBuscaCategorias)) {
          echo '<option value="'.$objBuscaCategorias->cod_respostas_categorias.'"';
          
          if($objBuscaCategorias->cod_respostas_categorias == $objBusca->cod_respostas_categorias)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaCategorias->nome_categoria).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td></tr>

    <tr><td class="legenda tdbl tdbr"><label>Sugestão de Cupom</label></td></tr>
    <tr><td class="tdbl tdbr sep"><select name="cod_cupoms" id="cod_cupoms">
        <option value=""></option>
      <?
        $con = conectabd();
        $sql_pega_tamanho = "SELECT * from ipi_tamanhos ";
        $res_pega_tamanho = mysql_query($sql_pega_tamanho);
        while($obj_pega_tamanho = mysql_fetch_object($res_pega_tamanho))
        {
          $cod_tamanhos = $obj_pega_tamanho->cod_tamanhos;
          $nome_tamanho = explode('(',$obj_pega_tamanho->tamanho);
          $nome_tamanho = $nome_tamanho[0];
          $arr_tamanhos[$cod_tamanhos] = $nome_tamanho;
        }

        $SqlBuscaCupom = "SELECT * FROM ipi_respostas_cupom WHERE situacao='ATIVO' ORDER BY cod_respostas_cupom ASC";
        $resBuscaCupom = mysql_query($SqlBuscaCupom);
        
        while($objBuscaCupom = mysql_fetch_object($resBuscaCupom)) {
          echo '<option value="'.$objBuscaCupom->cod_respostas_cupom.'"';
          
          if($objBuscaCupom->cod_respostas_cupom == $objBusca->cod_respostas_cupom)
            echo 'selected';
          
            $nome_cupom = 'Cupom #'.bd2texto($objBuscaCupom->cod_respostas_cupom);
            $nome_cupom .= ' - '.$objBuscaCupom->nome_cupom;

          echo '>'.$nome_cupom.'</option>';
        }
        
        desconectabd($con);
        ?>
      
      </td></tr>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="nome_resposta">Nome da Resposta</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="nome_resposta" id="nome_resposta" maxlength="100" size="50" value="<? echo texto2bd($objBusca->nome_resposta) ?>"></td></tr>


    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="mensagem_resposta">Resposta Padrão</label></td></tr>
    <tr><td class="tdbl tdbr sep"><textarea class="requerido" name="mensagem_resposta" id="mensagem_resposta" rows="11" cols="75"><? echo texto2bd($objBusca->mensagem_resposta) ?></textarea></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="situacao">Situação</label></td></tr>
    <tr><td class="tdbl tdbr sep"><select class="requerido" name="situacao" id="situacao" >
          <option value="ATIVO"<? if($objBusca->situacao=="ATIVO") echo 'selected '?>>Ativo</option>
          <option value="INATIVO"<? if($objBusca->situacao=="INATIVO") echo 'selected '?>>Inativo</option>
        </select>
      </tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar">&nbsp;<input name="visualizar" class="botao" type="button" value="Visualizar" onclick="javascript:visualizar_mensagem_final()"></td></tr>
    
    </table>

    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
