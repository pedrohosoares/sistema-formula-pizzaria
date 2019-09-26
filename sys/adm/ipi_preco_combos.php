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

cabecalho('Cadastro de Combos por '.TIPO_EMPRESA);

$acao = validaVarPost('acao');

$tabela = 'ipi_combos_pizzarias';
$chave_primaria = 'cod_combos';

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
    $cod_combos = validaVarPost('cod_combos');
    $preco = moeda2bd(validaVarPost('preco'));
    $pontos_fidelidade = validaVarPost('fidelidade');
    
   $con = conectabd();//`ipi_conteudos_pizzarias
    
    $sql_editar = "select * from $tabela where $chave_primaria = $cod_combos and cod_pizzarias = $cod_pizzaria_filt";
    $res_editar = mysql_query($sql_editar);
    
    if(mysql_num_rows($res_editar) <= 0) {
      $SqlEdicao = sprintf("INSERT INTO ipi_combos_pizzarias (cod_pizzarias,cod_combos, preco, pontos_fidelidade) VALUES (%d, %d, '%s', %d)", 
                           $cod_pizzaria_filt, $cod_combos, $preco,$pontos_fidelidade);
     // echo $sqlEdicao;
      $res_edicao = mysql_query($SqlEdicao);
      $codigo = mysql_insert_id();
        
    }
    else 
    {
        $sql_editar = sprintf("UPDATE ipi_combos_pizzarias set preco = '%s' , pontos_fidelidade = %d where cod_combos = $cod_combos and cod_pizzarias = $cod_pizzaria_filt", 
                           $preco,$pontos_fidelidade);
        //echo $sql_editar;
        $res_edicao = mysql_query($sql_editar);
    }
    
      if($res_edicao)
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          
    
    desconectabd($con);
  break;
  case 'copiar_combos':
  		$cod_matriz = validaVarPost('cod_matriz');
  		$cod_copiar = validaVarPost('cod_filt');
    		
    	$con = conectar_bd();	
  		$sql_deletar = "delete from $tabela where cod_pizzarias = $cod_pizzaria_filt";
  		$res_deletar = mysql_query($sql_deletar);
  		
  		if($res_deletar)
  		{
  			$sql_copiar = "insert into ipi_combos_pizzarias(cod_pizzarias,cod_combos, preco, pontos_fidelidade) (select $cod_copiar, cod_combos, preco, pontos_fidelidade from $tabela where cod_pizzarias = $cod_matriz)";
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
    'value': <? echo "'$cod_pizzaria_filt'" ?>
  });
  
  input.inject(form);
  input2.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function copiar_combos()
{
  if(confirm('TEM CERTEZA QUE DESEJA COPIAR OS PREÇOS?\n(ESSE PROCESSO NÃO TERÁ VOLTA)\n')) 
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
      'value': <? echo "'$cod_pizzaria_filt'" ?>
    });
    
    var input3 = new Element('input', {
      'type': 'hidden',
      'name': 'acao',
      'value': 'copiar_combos'
    });
    
    input.inject(form);
  	input2.inject(form);
  	input3.inject(form);
    $(document.body).adopt(form);
    form.submit();	
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
      document.frmIncluir.preco.value = '';
      document.frmIncluir.fidelidade.disabled = false;
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
    else
    {
    	 //document.frmIncluir.arquivo.disabled = true;
    }
    });
  });

function gerar_imagem()
{
    var url = 'acao=gerar_imagem';
  
    //url += '&combo=' + combo;
  
    //var cod_produtos = $('cod_produtos').getProperty('value');
    //url += '&cod_produtos=' + cod_produtos;
  
    $('imagem_status').set('html', 'Carregando...');
  
    new Request.HTML({
        url: 'ipi_preco_combos_ajax.php',
        update: $('imagem_Status'),
        onComplete: function() 
        {
            $('imagem_status').set('html', 'Imagens dos combos atualizadas com sucesso!');
        }
    }).send(url);

}
</script>

<form name="frmPizzariaFilt" method="post">

  <?php echo TIPO_EMPRESA; ?>: 
  <select name="cod_pizzarias_filt" style="width: 300px;">
    <option value=''>Selecione um(a) <?php echo TIPO_EMPRESA; ?></option>
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
      <? if($cod_pizzaria_filt > 0): ?>
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
          <tr>
            <td><input class="botaoAzul" type="submit" value="Excluir Selecionados">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input class="botaoAzul" type="button" value="Gerar Novas Imagens dos Combos" onclick='gerar_imagem()'><div id="imagem_status"></div></td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
              <td align="center">Combo</td>
              <td align="center">Preço</td>
              <td align="center">Potos Fidelidade</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT *,c.cod_combos as codigo_combo from  ipi_combos c LEFT JOIN $tabela cop ON(c.cod_combos=cop.cod_combos and cop.cod_pizzarias = '$cod_pizzaria_filt') LEFT JOIN ipi_pizzarias pi ON(pi.cod_pizzarias=cop.cod_pizzarias)  ORDER BY c.ordem_combo";
          //echo $SqlBuscaRegistros;
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          $i=0;
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->codigo_combo.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->codigo_combo.')">'.$objBuscaRegistros->nome_combo.'</a></td>';
            echo '<td align="center">'.bd2moeda($objBuscaRegistros->preco).'</td>';
            echo '<td align="center">'.$objBuscaRegistros->pontos_fidelidade.'</td>';
            $i++;
            echo '</tr>';
          }
          
        if($cod_pizzaria_filt !=""  && $cod_pizzaria_filt !="1")
        {
		     // echo 'aaa<script>alert('.$i.');</script>';
		     // if($i<=2)
		     // {
		      	echo '<script> $("botao_copiar").innerHTML = "<input type=\'button\' class=\'botao\' id=\'botao_copiar\' onclick=\'copiar_combos()\' value=\'Copiar Preços da pizzarias matriz\' />" ;</script>';
		      	
		    // }
        }
          
          desconectabd($con);
          
          ?>
          
          </tbody>
        </table>
        <? endif; ?>
        <input type="hidden" name="acao" value="excluir">
      </form>
    
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
						<li><a href="ipi_combos.php">Combos</a></li>
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
      $objBusca = executaBuscaSimples("SELECT * FROM ipi_combos c LEFT join $tabela cop on c.cod_combos = cop.cod_combos and cop.cod_pizzarias = '$cod_pizzaria_filt' WHERE c.$chave_primaria = $codigo");
      
       $con = conectabd();
    } 
    ?>
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="nome_combo">Combo</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    <input type="text" name="nome_combo" disabled id="nome_combo" value = "<? echo $objBusca->nome_combo; ?>">
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="preco">Preço</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    <input type="text" name="preco" id="preco" maxsize="5" size="6"  value = "<? echo bd2moeda($objBusca->preco); ?>" onKeyPress="return formataMoeda(this, '.', ',', event)">
    </td></tr>

        <tr><td class="legenda tdbl tdbr"><label class="requerido" for="fidelidade">Pontos Fidelidade</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    <input type="text" name="fidelidade" id="fidelidade" value = "<? echo $objBusca->pontos_fidelidade; ?>" maxsize="5" size="3">
    </td></tr>
        <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    </table>
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>"/>
		<input type="hidden" name="cod_filt" value="<? echo $cod_pizzaria_filt ?>" >
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
