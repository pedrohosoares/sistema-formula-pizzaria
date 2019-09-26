<?php

/**
 * ipi_bebida_conteudo.php: Cadastro Conteúdo da Bebida
 * 
 * Índice: cod_bebidas_ipi_conteudos
 * Tabela: ipi_bebidas_ipi_conteudos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Promoções para as '.TIPO_EMPRESAS);

$acao = validaVarPost('acao');

$tabela = 'ipi_promocoes_ipi_pizzarias';
$chave_primaria = 'cod_promocoes';

$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
if(validaVarGet("cod_filt", '/[0-9]+/')!="")
{
$cod_pizzaria_filt = validaVarGet("cod_filt", '/[0-9]+/');

}
if(validaVarPost("cod_filt", '/[0-9]+/')!="")
{
$cod_pizzaria_filt = validaVarPost("cod_filt", '/[0-9]+/');

}
if(validaVarPost('cod_pizzarias_filt')!="")
{
$cod_pizzaria_filt = validaVarPost('cod_pizzarias_filt');
}

switch($acao) {
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $cod_pizzaria = validaVarPost('cod_pizzaria');
    $cod_promocoes = validaVarPost('scod_promocoes');
    $arquivo = validaVarFiles('arquivo');
    $tipo = validaVarPost('tipo');
    $situacao = validaVarPost('situacao');
    
    if ($situacao==0)
    {
    	$situacao = 'INATIVO';
    }else
		{
    	$situacao = 'ATIVO';		
		}
    
   $con = conectabd();//`ipi_conteudos_pizzarias
    
   /* if($situacao=='ATIVO'&&$tipo=='SUGESTAO')
    {
   	 	 $sql_editar = "update ipi_promocoes_ipi_pizzarias pp inner join ipi_promocoes p on p.cod_promocoes = pp.cod_promocoes set pp.stiuacao='INATIVO' where p.tipo='SUGESTAO'";
   		 $res_editar = mysql_query($sql_editar);    
    
    }*/
    /*echo "<pre>";
    print_r($_POST);
    echo "</pre>";*/
    $sql_editar = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes = $cod_promocoes and cod_pizzarias = $cod_pizzaria_filt";
    $res_editar = mysql_query($sql_editar);
    //echo $sql_editar;
    if(mysql_num_rows($res_editar) <= 0) {
      $SqlEdicao = sprintf("INSERT INTO ipi_promocoes_ipi_pizzarias (cod_pizzarias,cod_promocoes, situacao) VALUES (%d, %d, '%s')", 
                           $cod_pizzaria_filt, $cod_promocoes, $situacao);
      //echo "a".$SqlEdicao;
      $res_edicao = mysql_query($SqlEdicao);
      $codigo = mysql_insert_id();
        
     /*   
      if($resEdicaoImagem)
       // mensagemOk('Registro adicionado com êxito!');
      else
       // mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');*/
    }
    else {
    
   /*  if($situacao=='ATIVO'&&$tipo=='SUGESTAO')
		  {
		 	 	 $sql_editar = "update ipi_promocoes_ipi_pizzarias pp inner join ipi_promocoes p on p.cod_promocoes = pp.cod_promocoes set pp.situacao='INATIVO' where p.tipo='SUGESTAO'";
		 	 	// echo $sql_editar;
		 		 $res_editar = mysql_query($sql_editar);    
		  
		  }*/
      $SqlEdicao = sprintf("UPDATE  ipi_promocoes_ipi_pizzarias SET situacao = '%s' WHERE cod_pizzarias = $cod_pizzaria_filt and cod_promocoes = $cod_promocoes ", 
                              $situacao);
      //echo "b".$SqlEdicao;
      $res_edicao = mysql_query($SqlEdicao);
        
      /*if($res_edicao)
     //   mensagemOk('Registro adicionado com êxito!');
      else
      //  mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        //echo $SqlEdicao."AAA".$situacao;*/
			
    }
    
     if ($res_edicao)
         {
		        $resEdicaoImagem = true;
		        // Alterando as Imagens pequenas
		        if(count($arquivo['name']) > 0) {     
		          if(trim($arquivo['name']) != '') {
		            $arq_info = pathinfo($arquivo['name']);
		            $arq_ext = $arq_info['extension'];
		            if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $arquivo["type"])) {
		              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
		            }
		            else {                
		              $resEdicaoImagem &= move_uploaded_file($arquivo['tmp_name'], UPLOAD_DIR."/promocoes/${cod_pizzaria_filt}_pi_${codigo}_pro.${arq_ext}");
		                                      
		              $SqlEdicaoImagem = sprintf("UPDATE $tabela set arquivo= '%s' WHERE $chave_primaria = $codigo and cod_pizzarias = $cod_pizzaria_filt", 
		                         texto2bd("${cod_pizzaria_filt}_pi_${codigo}_pro.${arq_ext}"));
                   echo "IMGIMGIMG".$SqlEdicaoImagem;
		              
		              $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
		            }
		          }          
		        }
          }
          
          
      if($resEdicaoImagem)
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          
    
    desconectabd($con);
  break;
  case 'copiar_promocoes':
  		$cod_matriz = validaVarPost('cod_matriz');
  		$cod_copiar = validaVarPost('cod_filt');
    		
    	$con = conectar_bd();	
  		$sql_deletar = "delete from ipi_promocoes_ipi_pizzarias where cod_pizzarias = $cod_pizzaria_filt";
  		$res_deletar = mysql_query($sql_deletar);
  		
  		if($res_deletar)
  		{
  			$sql_copiar = "insert into ipi_promocoes_ipi_pizzarias(cod_pizzarias,cod_promocoes, arquivo, situacao) (select $cod_copiar, cod_promocoes, arquivo, situacao from $tabela where cod_pizzarias = $cod_matriz)";
  			$res_copiar = mysql_query($sql_copiar);
  			
  			if($res_copiar)
  			{
  					 mensagemOk('Promoções copiados com êxito!');
  			}
  			else
  			{
  					mensagemErro('Erro ao copiar promoções', 'Por favor, verifique se a pizzaria está selecionada e filtrada.');
  			}
  		
  		
  		}
  		else
  		{
  			mensagemErro('Erro dao copiar promoções', 'Por favor, verifique se a pizzaria está selecionada e filtrada.');
  		}
  		
  		
  		desconectar_bd($con);
  break;
  /*case 'excluir':
      $cod_copiar = validaVarPost('cod_filt');
        
      $con = conectar_bd(); 
      $sql_deletar = "delete from ipi_promocoes_ipi_pizzarias where cod_pizzarias = '$cod_pizzaria_filt' and cod_promocoes = '$'";
      $res_deletar = mysql_query($sql_deletar);
      
      if($res_deletar)
      {
        mensagemOk('Promoções excluidas com êxito!');
      }
      else
      {
        mensagemErro('Erro dao apagar promoções', 'Por favor, verifique se a pizzaria está selecionada e filtrada.');
      }
  break;*/
  case 'excluir':
    $excluir = validaVarPost('excluir');
    /*echo "<pre><br/><br/><br/><br/>";
    print_r($excluir);
    echo "</pre>";*/
    $indicesSql = implode(',', $excluir);
    $cod_pizzaria = validaVarPost('cod_filt');
    $con = conectabd();
    
    $sql_imagens = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql) and cod_pizzarias = '$cod_pizzaria'";
    $res_imagens = mysql_query($sql_imagens);
    //echo "<br>sql_imagens: ".$sql_imagens;
    
    if ($res_imagens)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
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
  
  var input2 = new Element('input', {
    'type': 'hidden',
    'name': 'cod_filt',
    'value': <? echo $cod_pizzaria_filt ?>
  });
  
  input.inject(form);
  input2.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function copiar_promocoes()
{
	var cod_pizzarias_matriz = 1;
	var form = new Element('form', {
    'action': '<?
    echo $_SERVER['PHP_SELF']?>',
    'method': 'post'
  });
  var input = new Element('input', {
    'type': 'hidden',
    'name': 'cod_matriz',
    'value': cod_pizzarias_matriz
  });

  var input2 = new Element('input', {
    'type': 'hidden',
    'name': 'cod_filt',
    'value': <? echo $cod_pizzaria_filt ?>
  });
  
  var input3 = new Element('input', {
    'type': 'hidden',
    'name': 'acao',
    'value': 'copiar_promocoes'
  });
  
  input.inject(form);
	input2.inject(form);
	input3.inject(form);
  $(document.body).adopt(form);
  form.submit();	
}

function excluirArquivo(cod) {
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
    var acao = 'excluir_arquivo';
    var cod_promocoes = cod;
    var cod_pizzarias = <? echo $cod_pizzaria_filt; ?>;
    if(cod_pizzas > 0) {
      var url = 'acao=' + acao + '&cod_promocoes=' + cod_pizzas+'&cod_pizzarias='+cod_pizzarias;
      
      new Request.JSON({url: 'ipi_pizzaria_promocoes_ajax.php', onComplete: function(retorno) {
        if(retorno.status != 'OK') {
          alert('Erro ao excluir esta imagem.');
        }
        else {
          if($('arquivo_f')) {
            $('arquivo_f').destroy();

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
      document.frmIncluir.arquivo.value = '';
      /*document.frmIncluir.situacao.value = '';*/
      document.frmIncluir.arquivo.disabled = false;
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
    else
    {
    	 document.frmIncluir.arquivo.disabled = true;
    }
  });
});

</script>

<form name="frmPizzariaFilt" method="post">

<? echo ucfirst(TIPO_EMPRESA)?>: <select name="cod_pizzarias_filt" style="width: 300px;">
<option value=''>Selecione um(a) <? echo ucfirst(TIPO_EMPRESA)?></option>
<?
$con = conectabd();

$SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
$resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
{
	echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
	if($cod_pizzaria_filt == $objBuscaPizzarias->cod_pizzarias)
		echo 'selected';
	echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
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
    		  <div id='botao_copiar'></div>
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
              <td align="center">Promoção</td>
              <td align="center">Situação</td>
              <td align="center">Tipo</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT *,pp.situacao as situ FROM $tabela pp INNER JOIN ipi_promocoes p ON(p.cod_promocoes=pp.cod_promocoes) INNER JOIN ipi_pizzarias pi ON(pi.cod_pizzarias=pp.cod_pizzarias) and pp.cod_pizzarias = $cod_pizzaria_filt ORDER BY p.cod_promocoes";
          //echo $SqlBuscaRegistros;
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          $i=0;
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.$objBuscaRegistros->promocao.'</a></td>';
            
            echo '<td align="center">';
            if($objBuscaRegistros->situ=='ATIVO')
							echo '<img src="../lib/img/principal/ok.gif">';
            else
							echo '<img src="../lib/img/principal/erro.gif">';
				
            echo '</td>';

            echo "<td>".$objBuscaRegistros->tipo."</td>";
            $i++;
          }
          
          if($cod_pizzaria_filt !="")
          {
  		     // echo 'aaa<script>alert('.$i.');</script>';
  		      if($i<=2)
  		      {
  		      	echo '<script> $("botao_copiar").innerHTML = "<input type=\'button\' id=\'botao_copiar\' onclick=\'copiar_promocoes()\' value=\'Copiar Promoções da matriz\' />" ;</script>';
  		      	
  		      }
          }
          
          desconectabd($con);
          
          ?>
          
          </tbody>
        </table>
      
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="cod_filt" value="<? echo $cod_pizzaria_filt ?>" >
      </form>
    
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
						<li><a href="ipi_promocoes.php">Promoções</a></li>
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
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo and cod_pizzarias = $cod_pizzaria_filt");
      
       $con = conectabd();
      $objTipo =  executaBuscaSimples("SELECT * FROM ipi_promocoes WHERE $chave_primaria = $codigo");
      $tipo = $objTipo->tipo;
    } 
    ?>
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_promocoes">Promoção</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select name="scod_promocoes" id="scod_promocoes" style="width: 150px;">
        <option value=""></option>
        <?
        $con = conectabd();
        
        $SqlBuscaPromocoes = "SELECT * FROM ipi_promocoes ORDER BY cod_promocoes";
        $resBuscaPromocoes = mysql_query($SqlBuscaPromocoes);
        
        while($objBuscaPromocoes = mysql_fetch_object($resBuscaPromocoes)) {
          echo '<option value="'.$objBuscaPromocoes->cod_promocoes.'" ';
          
          if($objBuscaPromocoes->cod_promocoes == $objBusca->cod_promocoes)
            echo 'selected';
            
          echo '>'.bd2texto($objBuscaPromocoes->promocao).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td></tr>
    
    <tr>
		<td class="legenda tdbl tdbr"><label for="arquivo">Arquivo(*.png, *.jpg)</label></td>
	</tr>
     
    <?
    if (is_file(UPLOAD_DIR . '/promocoes/' . $objBusca->arquivo))
    {
        echo '<tr><td class="sep tdbl tdbr" align="center" id="arquivo_f" style="padding: 15px;">';
        
        echo '<img src="' . UPLOAD_DIR . '/promocoes/' . $objBusca->arquivo . '">';
        
        echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem_pequena(' . $objBusca->$chave_primaria . ');"></td></tr>';
    }
    ?>
    <tr>
		<td class="sep tdbl tdbr sep"><input type="file" name="arquivo"
			id="arquivo" size="40"></td>
	</tr>    

    
    <tr><td align="left" class="legenda tdbl tdbr">
      <input type="checkbox" name="situacao" id="situacao" <? if($objBusca->situacao=='ATIVO') echo 'checked' ?> value="1">
      <label for="situacao">Ativo</label>
    </td></tr>

    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>


    
    </table>
    <input type='hidden' name='tipo' id='tipo' value="<? echo $tipo ?>" />
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>"/>
		<input type="hidden" name="cod_filt" value="<? echo $cod_pizzaria_filt ?>" >
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
