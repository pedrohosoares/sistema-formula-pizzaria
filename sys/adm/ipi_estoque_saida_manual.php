<?php

/**
 * ipi_ingrediente.php: Cadastro de Ingrediente
 * 
 * �ndice: cod_ingredientes
 * Tabela: ipi_ingredientes
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Estoque Baixa Manual de Ingredientes');

$acao = validaVarPost('acao');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';

switch($acao) {
  case 'saida_manual':

    require_once '../../classe/estoque.php';
    $estoque = new Estoque();

	// ATEN��O: ajustar a variavel abaixo de acordo com o bando de dados
	$cod_estoque_tipo_lancamento = "4";

	$cod_pizzarias = validaVarPost('cod_pizzarias');

	$cod_ingredientes = validaVarPost('cod_ingredientes');
  $divisor_comum = validaVarPost('divisor_comum');
	$txt_nova_quantidade = validaVarPost('txt_nova_quantidade');
	$obs = validaVarPost('obs');
	$cont = count($obs);

	$cod_bebidas_ipi_conteudos = validaVarPost('cod_bebidas_ipi_conteudos');
	$txt_nova_quantidade_bebidas = validaVarPost('txt_nova_quantidade_bebidas');
	$obs_bebidas = validaVarPost('obs_bebidas');
	$cont_bebidas = count($obs_bebidas);

    $con = conectabd();

	for ($a=0; $a<$cont; $a++)
	{
		//($quantidade, $cod_ingredientes, $cod_bebidas_ipi_conteudos, $tipo_estoque, $cod_pizzarias, $cod_estoque_tipo_lancamento, $cod_pedidos = 0, $cod_colaboradores_prejuizo = 0, $cod_estoque_entrada_itens = 0, $obs = '')
		if ($txt_nova_quantidade[$a]!="0")
		{
			if ($obs[$a]=="Inserir obs...")
				$observacao = "";
			else
				$observacao = $obs[$a];
      $txt_nova_quantidade[$a] = str_replace(',', '.', $txt_nova_quantidade[$a]);
    //echo "<Br>$a: ".$txt_nova_quantidade[$a]." - ".$divisor_comum[$a]." - ".$cod_ingredientes[$a]." - 0 - INGREDIENTE - ".$cod_pizzarias." - ".$cod_estoque_tipo_lancamento." - 0 - 0 - ".$obs[$a];
		  $res_estoque = $estoque->lancar_estoque("-".$txt_nova_quantidade[$a]*$divisor_comum[$a], $cod_ingredientes[$a], 0, "INGREDIENTE", $cod_pizzarias, $cod_estoque_tipo_lancamento, 0, 0, 0, $observacao );
		}
	}

	for ($a=0; $a<$cont_bebidas; $a++)
	{
		//($quantidade, $cod_ingredientes, $cod_bebidas_ipi_conteudos, $tipo_estoque, $cod_pizzarias, $cod_estoque_tipo_lancamento, $cod_pedidos = 0, $cod_colaboradores_prejuizo = 0, $cod_estoque_entrada_itens = 0, $obs = '')
		//echo "<Br>$a: ".$txt_nova_quantidade[$a]." - ".$cod_ingredientes[$a]." - 0 - INGREDIENTE - ".$cod_pizzarias." - ".$cod_estoque_tipo_lancamento." - 0 - 0 - ".$obs[$a];
		if ($txt_nova_quantidade_bebidas[$a]!="0")
		{
			if ($obs_bebidas[$a]=="Inserir obs...")
				$observacao = "";
			else
				$observacao = $obs_bebidas[$a];
		    $res_estoque = $estoque->lancar_estoque("-".$txt_nova_quantidade_bebidas[$a], 0, $cod_bebidas_ipi_conteudos[$a], "BEBIDA", $cod_pizzarias, $cod_estoque_tipo_lancamento, 0, 0, 0, $observacao );
		}
	}



    if ($res_estoque)
		mensagemOk('Baixa Manual Registrada com Sucesso!');
    else
		mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usu�rios selecionados para exclus�o.');

    desconectabd($con);
	$acao="";

	//echo "<pre>";
	//print_r($obs);
	//echo "</pre>";

	//echo "<br>acao: ".$cod_pizzarias;

	//$acao="";

  break;
}

if ( ($acao=="") || ($acao=="buscar") )
{
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

function limpaPreco(cod) 
{
	document.getElementById('preco_' + cod).value = '';
}

window.addEvent('domready', function()
{
	var tabs = new Tabs('tabs'); 
/*
	if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) 
	{
		<? if ($acao == '') echo 'tabs.irpara(1);'; ?>
		document.frmIncluir.botao_submit.value = 'Alterar';
	}
	else 
	{
		document.frmIncluir.botao_submit.value = 'Cadastrar';
	}
  
	tabs.addEvent('change', function(indice)
	{
		if(indice == 1) 
		{
			document.frmIncluir.<? echo $chave_primaria ?>.value = '';
			document.frmIncluir.ingrediente.value = '';
			document.frmIncluir.ingrediente_abreviado.value = '';
			//document.frmIncluir.tipo.value = '';
			document.frmIncluir.quantidade_minima.value = '';
			document.frmIncluir.quantidade_maxima.value = '';
			document.frmIncluir.quantidade_perda.value = '';
			document.frmIncluir.adicional.checked = false;
			document.frmIncluir.ativo.checked = true;

			marcaTodosEstado('marcar_tamanho', false);

			// Limpando todos os campos input para Pre�o
			var input = document.getElementsByTagName('input');
			for (var i = 0; i < input.length; i++) 
			{
				if((input[i].name.match('preco')) || (input[i].name.match('quantidade_estoque_extra'))) 
				{ 
					input[i].value = ''; 
				}
			}

		document.frmIncluir.botao_submit.value = 'Cadastrar';
		}
	});
*/
});
</script>

<?
$cod_pizzarias = validaVarPost('cod_pizzarias');

?>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a href="javascript:;">Relat�rio</a></li>
    </ul>
  </div>
    
  <!-- Tab Editar -->
  <div class="painelTab">



  <form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt sep" align="right"><label for="cod_pizzarias">Pizzaria:</label></td>
    <td class="tdbt sep">&nbsp;</td>
    <td class="tdbr tdbt sep">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as Pizzarias</option>
        <?
		$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

        $con = conectabd();
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
        while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
		{
			echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
			if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
			{
				echo 'selected';
			}
			echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
		}
        ?>
      </select>
    </td>
  </tr>

  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
  </form>

	<?
	if ($cod_pizzarias)
	{
	?>


    <table><tr>
    <!-- Conte�do -->
    <td class="conteudo" align="center">
    
	  <form name="frmSaidaManual" method="post">
		<table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" align="center">
          <tr>
            <td>
			<!--
			<input class="botaoAzul" type="submit" value="Excluir Selecionados">
			-->
			</td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0" align="center">
          <thead>
            <tr>
              <td align="center" width="250">Ingrediente</td>
              <td align="center">Estoque Atual</td>
              <td align="center">Qtde <br>Saida Manual</td>
              <td align="center">OBS</td>
            </tr>
          </thead>

          <tbody>
			<?
			if (!$cod_pizzarias)
			{
				$cod_pizzarias = $cod_pizzarias_usuario;
			}

			$SqlBuscaIngredientes = "SELECT i.cod_ingredientes, i.ingrediente, ip.quantidade_minima, ip.quantidade_maxima, ip.quantidade_perda, (SELECT SUM(e.quantidade) FROM ipi_estoque e WHERE e.cod_ingredientes = i.cod_ingredientes AND e.cod_pizzarias IN ($cod_pizzarias) ) quantidade_atual, iup.abreviatura, iup.divisor_comum FROM $tabela i LEFT JOIN ipi_ingredientes_pizzarias ip ON (i.cod_ingredientes = ip.cod_ingredientes AND ip.cod_pizzarias IN ($cod_pizzarias)) LEFT JOIN ipi_unidade_padrao iup ON (iup.cod_unidade_padrao = i.cod_unidade_padrao) ORDER BY i.ingrediente";
			$resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
			//echo "<br>1: ".$SqlBuscaIngredientes;
			while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) 
			{
				echo '<tr>';

				echo '<td align="center">';
        $abreviatura = ($objBuscaIngredientes->abreviatura != '' ? ' (em '.$objBuscaIngredientes->abreviatura.')' : '');
        $divisor_comum = ($objBuscaIngredientes->divisor_comum > 0 ? $objBuscaIngredientes->divisor_comum : 1);
				echo bd2texto($objBuscaIngredientes->ingrediente.$abreviatura);

				echo '</td>';

				echo '<td align="center">';
				echo '<input type="hidden" name="cod_ingredientes[]" size="6" value="'.$objBuscaIngredientes->cod_ingredientes.'"/>';
        echo '<input type="hidden" name="divisor_comum[]" value="'.$divisor_comum.'"/>';
				if ($objBuscaIngredientes->quantidade_atual)
					echo bd2texto(($objBuscaIngredientes->quantidade_atual/$divisor_comum));
				else
					echo "0";
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="text" name="txt_nova_quantidade[]" size="6" value="0" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}" onkeypress="formataMoeda3casas(this, 2)">';
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="text" name="obs[]" size="30" value="Inserir obs..." style="font-style: italic; color: #AAAAAA" onclick="javascript: if(this.value==\'Inserir obs...\'){this.style.color=\'#000000\'; this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.style.color=\'#AAAAAA\';  this.value=\'Inserir obs...\';}">';
				echo '</td>';

				echo '</tr>';
			}
			?>
          </tbody>
        </table>


<br>

      
        <table class="listaEdicao" cellpadding="0" cellspacing="0" align="center">
          <thead>
            <tr>
              <td align="center" width="250">Bebida</td>
              <td align="center">Estoque Atual</td>
              <td align="center">Qtde <br>Saida Manual</td>
              <td align="center">OBS</td>
            </tr>
          </thead>

          <tbody>
			<?
          	$SqlBuscaIngredientes = "SELECT bc.cod_bebidas_ipi_conteudos, b.bebida, c.conteudo, (SELECT SUM(e.quantidade) FROM ipi_estoque e WHERE e.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND e.cod_pizzarias IN ($cod_pizzarias) ) quantidade_atual FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) ORDER BY b.bebida, c.conteudo";
			$resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);

			//echo "<br>1: ".$SqlBuscaIngredientes;
			while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) 
			{
				echo '<tr>';

				echo '<td align="center">';
				echo bd2texto($objBuscaIngredientes->bebida)." - ".bd2texto($objBuscaIngredientes->conteudo);
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="hidden" name="cod_bebidas_ipi_conteudos[]" size="6" value="'.$objBuscaIngredientes->cod_bebidas_ipi_conteudos.'">';
				if ($objBuscaIngredientes->quantidade_atual)
					echo bd2texto($objBuscaIngredientes->quantidade_atual);
				else
					echo "0";
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="text" name="txt_nova_quantidade_bebidas[]" size="6" value="0" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}">';
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="text" name="obs_bebidas[]" size="30" value="Inserir obs..." style="font-style: italic; color: #AAAAAA" onclick="javascript: if(this.value==\'Inserir obs...\'){this.style.color=\'#000000\'; this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.style.color=\'#AAAAAA\';  this.value=\'Inserir obs...\';}">';
				echo '</td>';

				echo '</tr>';
			}

			desconectabd($con);
			?>
          </tbody>
        </table>

<br>
<strong>Usu�rio:</strong> <? echo $_SESSION['usuario']['usuario']; ?><br>
<strong>Data:</strong> <? echo date("d/m/Y H:i:s"); ?><br>
<br>
		<input class="botaoAzul" type="submit" value="Saida Manual">

        <input type="hidden" name="acao" value="saida_manual">
        <input type="hidden" name="cod_pizzarias" value="<? echo $cod_pizzarias; ?>">

      </form>
    
    </td>
    <!-- Conte�do -->
    
    <!-- Barra Lateral -->
	<!--
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="ipi_adicional.php">Adicionais</a></li>
          <li><a href="ipi_borda.php">Bordas</a></li>
          <li><a href="ipi_pizza.php">Pizzas</a></li>
          <li><a href="ipi_tamanho.php">Tamanhos</a></li>
        </ul>
      </div>
    </td>
	-->
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Editar -->
  
	<?
	}
	else
		echo "Selecione a pizzaria!";
}
?>
  
 </div>

<? rodape(); ?>
