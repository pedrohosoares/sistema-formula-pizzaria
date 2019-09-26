<?php

/**
 * ipi_ingrediente.php: Cadastro de Ingrediente
 * 
 * Índice: cod_ingredientes
 * Tabela: ipi_ingredientes
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Estoque Limites dos Ingredientes em Estoque');

$acao = validaVarPost('acao');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';

switch($acao) {
  case 'saida_manual':

	$cod_pizzarias = validaVarPost('cod_pizzarias');

	$cod_ingredientes = validaVarPost('cod_ingredientes');
  $divisor_comum = validaVarPost('divisor_comum');
	$txt_nova_quantidade_minima = validaVarPost('txt_nova_quantidade_minima');
	$txt_nova_quantidade_maxima = validaVarPost('txt_nova_quantidade_maxima');
	$txt_nova_quantidade_perda = validaVarPost('txt_nova_quantidade_perda');
  $txt_nova_tempo_entrega = validar_var_post('txt_nova_tempo_entrega');
	$cont = count($cod_ingredientes);

	$cod_bebidas_ipi_conteudos = validaVarPost('cod_bebidas_ipi_conteudos');
	$txt_nova_quantidade_bebidas_minima = validaVarPost('txt_nova_quantidade_bebidas_minima');
	$txt_nova_quantidade_bebidas_maxima = validaVarPost('txt_nova_quantidade_bebidas_maxima');
	$txt_nova_quantidade_bebidas_perda = validaVarPost('txt_nova_quantidade_bebidas_perda');
  $txt_nova_bebidas_tempo_entrega = validar_var_post('txt_nova_bebidas_tempo_entrega');
	$obs_bebidas = validaVarPost('obs_bebidas');
	$cont_bebidas = count($cod_bebidas_ipi_conteudos);

    $con = conectabd();

	$sql_del_ingredientes = "DELETE FROM ipi_ingredientes_pizzarias WHERE cod_pizzarias = ".$cod_pizzarias;
	$res_del_ingredientes = mysql_query($sql_del_ingredientes);
  $res_edicao_ingredientes = true;
	for ($a=0; $a<$cont; $a++)
	{
    if($txt_nova_quantidade_minima[$a] > 0 || $txt_nova_quantidade_maxima[$a] > 0 || $txt_nova_quantidade_perda[$a] > 0 || $txt_nova_tempo_entrega[$a] > 0)
    {
      $txt_nova_quantidade_minima[$a] = str_replace(',', '.', $txt_nova_quantidade_minima[$a]);
      $txt_nova_quantidade_maxima[$a] = str_replace(',', '.', $txt_nova_quantidade_maxima[$a]);
      $txt_nova_quantidade_perda[$a]  = str_replace(',', '.', $txt_nova_quantidade_perda[$a]);
    	$sql_edicao_ingredientes = sprintf("INSERT INTO ipi_ingredientes_pizzarias (cod_pizzarias, cod_ingredientes, quantidade_minima, quantidade_maxima, quantidade_perda, tempo_entrega) VALUES('%d', '%d', '%d', '%d', '%d', '%d')", $cod_pizzarias, $cod_ingredientes[$a], $txt_nova_quantidade_minima[$a]*$divisor_comum[$a], $txt_nova_quantidade_maxima[$a]*$divisor_comum[$a], $txt_nova_quantidade_perda[$a]*$divisor_comum[$a], $txt_nova_tempo_entrega[$a]);
  		$res_edicao_ingredientes &= mysql_query($sql_edicao_ingredientes);
  		//echo "<Br>$a: ".$sql_edicao_ingredientes;
    }
	}


	//$sql_del_bebidas = "DELETE FROM ipi_conteudos_pizzarias WHERE cod_pizzarias = ".$cod_pizzarias;
	//$res_del_bebidas = mysql_query($sql_del_bebidas);
	for ($a=0; $a<$cont_bebidas; $a++)
	{
      	//$sql_edicao_bebidas = sprintf("INSERT INTO ipi_conteudos_pizzarias (cod_pizzarias, cod_bebidas_ipi_conteudos, quantidade_minima, quantidade_maxima, quantidade_perda) VALUES('%d', '%d', '%d', '%d', '%d')",$cod_pizzarias, $cod_bebidas_ipi_conteudos[$a], $txt_nova_quantidade_bebidas_minima[$a], $txt_nova_quantidade_bebidas_maxima[$a], $txt_nova_quantidade_bebidas_perda[$a]) ;
    $sql_edicao_bebidas = sprintf("UPDATE ipi_conteudos_pizzarias SET quantidade_minima = '%d', quantidade_maxima = '%d', quantidade_perda = '%d', tempo_entrega = '%d' WHERE cod_pizzarias = '%d' AND cod_bebidas_ipi_conteudos = '%d'", $txt_nova_quantidade_bebidas_minima[$a], $txt_nova_quantidade_bebidas_maxima[$a], $txt_nova_quantidade_bebidas_perda[$a], $txt_nova_bebidas_tempo_entrega[$a], $cod_pizzarias, $cod_bebidas_ipi_conteudos[$a]) ;
		$res_edicao_ingredientes &= mysql_query($sql_edicao_bebidas);
		//echo "<Br>$a: ".$sql_edicao_bebidas;
	}



    if ($res_edicao_ingredientes)
		mensagemOk('Limites do estoque ajustados com Sucesso!');
    else
		mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');

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

			// Limpando todos os campos input para Preço
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
       <li><a href="javascript:;">Relatório</a></li>
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

<div style="display: inline; margin: 0 auto;">
    <table><tr>
    <!-- Conteúdo -->
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
              <td align="center">Qtde Minima</td>
              <td align="center">Qtde Maxima</td>
              <td align="center">Qtde Perda</td>
              <td align="center">Tempo Entrega<br />(dias)</td>
            </tr>
          </thead>

          <tbody>
			<?
			if (!$cod_pizzarias)
			{
				$cod_pizzarias = $cod_pizzarias_usuario;
			}

			$SqlBuscaIngredientes = "SELECT iup.abreviatura, iup.divisor_comum, i.cod_ingredientes, i.ingrediente, ip.quantidade_minima, ip.quantidade_maxima, ip.quantidade_perda, ip.tempo_entrega, (SELECT SUM(e.quantidade) FROM ipi_estoque e WHERE e.cod_ingredientes = i.cod_ingredientes AND e.cod_pizzarias IN ($cod_pizzarias) ) quantidade_atual FROM $tabela i LEFT JOIN ipi_ingredientes_pizzarias ip ON (i.cod_ingredientes = ip.cod_ingredientes AND ip.cod_pizzarias IN ($cod_pizzarias)) LEFT JOIN ipi_unidade_padrao iup ON (iup.cod_unidade_padrao = i.cod_unidade_padrao) ORDER BY i.ingrediente";
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
				echo '<input type="text" name="txt_nova_quantidade_minima[]" size="12" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}" onkeypress="formataMoeda3casas(this, 2)" value="'.($objBuscaIngredientes->quantidade_minima/$divisor_comum).'">';
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="text" name="txt_nova_quantidade_maxima[]" size="12" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}" onkeypress="formataMoeda3casas(this, 2)" value="'.($objBuscaIngredientes->quantidade_maxima/$divisor_comum).'">';
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="text" name="txt_nova_quantidade_perda[]" size="12" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}" onkeypress="formataMoeda3casas(this, 2)" value="'.($objBuscaIngredientes->quantidade_perda/$divisor_comum).'">';
				echo '</td>';

        echo '<td align="center">';
        echo '<input type="text" name="txt_nova_tempo_entrega[]" size="12" value="'.($objBuscaIngredientes->tempo_entrega!="" ? $objBuscaIngredientes->tempo_entrega : '0').'" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}">';
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
              <td align="center">Qtde Minima</td>
              <td align="center">Qtde Maxima</td>
              <td align="center">Qtde Perda</td>
              <td align="center">Tempo Entrega<br />(dias)</td>
            </tr>
          </thead>

          <tbody>
			<?
          	$SqlBuscaIngredientes = "SELECT bc.cod_bebidas_ipi_conteudos, b.bebida, c.conteudo, cp.quantidade_minima, cp.quantidade_maxima, cp.quantidade_perda, cp.tempo_entrega, (SELECT SUM(e.quantidade) FROM ipi_estoque e WHERE e.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND e.cod_pizzarias IN ($cod_pizzarias) ) quantidade_atual FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) LEFT JOIN ipi_conteudos_pizzarias cp ON (bc.cod_bebidas_ipi_conteudos = cp.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias IN ($cod_pizzarias)) ORDER BY b.bebida, c.conteudo";
			$resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);

			//echo "<br>1: ".$SqlBuscaIngredientes;
			while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) 
			{
				echo '<tr>';

				echo '<td align="center">';
				echo bd2texto($objBuscaIngredientes->bebida)." - ".bd2texto($objBuscaIngredientes->conteudo);
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="hidden" name="cod_bebidas_ipi_conteudos[]" value="'.$objBuscaIngredientes->cod_bebidas_ipi_conteudos.'">';
				if ($objBuscaIngredientes->quantidade_atual)
					echo bd2texto($objBuscaIngredientes->quantidade_atual);
				else
					echo "0";
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="text" name="txt_nova_quantidade_bebidas_minima[]" size="12" value="'.$objBuscaIngredientes->quantidade_minima.'" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}">';
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="text" name="txt_nova_quantidade_bebidas_maxima[]" size="12" value="'.$objBuscaIngredientes->quantidade_maxima.'" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}">';
				echo '</td>';

        echo '<td align="center">';
        echo '<input type="text" name="txt_nova_quantidade_bebidas_perda[]" size="12" value="'.$objBuscaIngredientes->quantidade_perda.'" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}">';
        echo '</td>';

        echo '<td align="center">';
        echo '<input type="text" name="txt_nova_bebidas_tempo_entrega[]" size="12" value="'.$objBuscaIngredientes->tempo_entrega.'" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}">';
        echo '</td>';



				echo '</tr>';
			}

			desconectabd($con);
			?>
          </tbody>
        </table>
<br>
<strong>Usuário:</strong> <? echo $_SESSION['usuario']['usuario']; ?><br>
<strong>Data:</strong> <? echo date("d/m/Y H:i:s"); ?><br>
<br>
		<input class="botaoAzul" type="submit" value="Ajustar Limites">

        <input type="hidden" name="acao" value="saida_manual">
        <input type="hidden" name="cod_pizzarias" value="<? echo $cod_pizzarias; ?>">

      </form>
    
    </td>
    <!-- Conteúdo -->
    
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
