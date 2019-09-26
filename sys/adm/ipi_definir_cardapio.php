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

cabecalho('Definir cardápio da pizzaria');

$acao = validaVarPost('acao');

$tabela = 'ipi_pizzas';
$chave_primaria = 'cod_pizzas';

switch($acao) {
  case 'definir_cardapio':

	  $cod_pizzarias = validaVarPost('cod_pizzarias');
	  $cod_pizzas = validaVarPost('cod_pizzas');
    $cont = count($cod_pizzas);

    $con = conectabd();

    $sql_del_cardapio = "DELETE FROM ipi_cardapios WHERE cod_pizzarias IN (".$cod_pizzarias.") AND cod_pizzarias IN (".implode(", ",$_SESSION['usuario']['cod_pizzarias']).")";
    $res_del_cardapio = mysql_query($sql_del_cardapio);
    //echo "<br>sql_del_cardapio: ".$sql_del_cardapio;
    $res_ins_cardapio = true;

	  for ($a=0; $a<$cont; $a++)
	  {
      $sql_ins_cardapio = sprintf("INSERT INTO ipi_cardapios (cod_pizzarias, cod_pizzas, situacao) VALUES (%d, %d, 'ATIVO')", $cod_pizzarias, $cod_pizzas[$a]);
      //echo "<br />A: ".$sql_ins_cardapio;
      $res_ins_cardapio &= mysql_query($sql_ins_cardapio);
      
	  }

    if ($res_ins_cardapio)
	    mensagemOk('Cardápio definido com Sucesso!');
    else
	    mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');

    desconectabd($con);
	  $acao="";

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
       <li><a href="javascript:;">Cardápio</a></li>
    </ul>
  </div>
    
  <!-- Tab Editar -->
  <div class="painelTab">



  <form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt sep" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA) ?>:</label></td>
    <td class="tdbt sep">&nbsp;</td>
    <td class="tdbr tdbt sep">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS) ?></option>
        <?
		    $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

        $con = conectabd();
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao!='INATIVO' and cod_pizzarias in ($cod_pizzarias_usuario) ORDER BY nome";//cod_pizzarias IN ($cod_pizzarias_usuario)
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


  <table align="center"><tr>
  <!-- Conteúdo -->
  <td class="conteudo" align="center">
  

	<center>
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
				<td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
        <td align="center" width="250"><? echo ucfirst(TIPO_PRODUTO) ?></td>
				<td align="center">Tipo</td>
				<td align="center">Exibir na Sugestão</td>
				<td align="center">Novidade</td>
        <td align="center"><? echo ucfirst(TIPO_PRODUTO) ?> Semana</td>
        <td align="center"><? echo ucfirst(TIPO_PRODUTO) ?> Fit</td>
      </tr>
    </thead>
    <tbody>
			<?
			$sqlBuscaPizzas = "SELECT p.cod_pizzas, p.pizza, p.tipo, p.sugestao, p.novidade,  p.pizza_fit, c.cod_pizzas cod_pizzas_cadastrado FROM $tabela p LEFT JOIN ipi_cardapios c ON (p.cod_pizzas = c.cod_pizzas AND c.cod_pizzarias IN ($cod_pizzarias) AND c.cod_pizzarias IN ($cod_pizzarias_usuario) ) ORDER BY p.pizza";
			$resBuscaPizzas = mysql_query($sqlBuscaPizzas);
			//echo "<br>1: ".$sqlBuscaPizzas;
			while ($objBuscaPizzas = mysql_fetch_object($resBuscaPizzas)) 
			{
				echo '<tr>';

        echo '<td align="center">';
        echo '<input type="checkbox" class="marcar excluir" name="cod_pizzas[]" value="' . $objBuscaPizzas->$chave_primaria . '" '.($objBuscaPizzas->cod_pizzas==$objBuscaPizzas->cod_pizzas_cadastrado ? 'checked=\'checked\'':'n').'>';
        echo '</td>';

				echo '<td align="center">';
				echo bd2texto($objBuscaPizzas->pizza);
				echo '</td>';

        echo '<td align="center">' . bd2texto($objBuscaPizzas->tipo) . '</td>';
        echo '<td align="center">' . ($objBuscaPizzas->sugestao == "1" ? "<b>Sim</b>" : "Não") . '</td>';
        echo '<td align="center">' . ($objBuscaPizzas->novidade == "1" ? "<b>Sim</b>" : "Não") . '</td>';
        echo '<td align="center">' . ($objBuscaPizzas->pizza_semana == "1" ? "<b>Sim</b>" : "Não") . '</td>';
        echo '<td align="center">' . ($objBuscaPizzas->pizza_fit == "1" ? "<b>Sim</b>" : "Não") . '</td>';


				echo '</tr>';
			}
		  ?>
    </tbody>
    </table>


      
    <br>
    <strong>Usuário:</strong> <? echo $_SESSION['usuario']['usuario']; ?><br>
    <strong>Data:</strong> <? echo date("d/m/Y H:i:s"); ?><br>
    <br>

		<input class="botaoAzul" type="submit" value="Definir Cardápio">

        <input type="hidden" name="acao" value="definir_cardapio">
        <input type="hidden" name="cod_pizzarias" value="<? echo $cod_pizzarias; ?>">

      </form>
	</center>


    
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
  <!-- Tab Editar -->
  
	<?
	}
	else
		echo "Selecione a ".ucfirst(TIPO_EMPRESA);
}
?>
  
 </div>

<? rodape(); ?>
