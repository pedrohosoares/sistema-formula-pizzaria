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

cabecalho('Calculo do CMV');

$acao = validaVarPost('acao');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';

switch($acao) {
  case 'ajustar_estoque':

    require_once '../../classe/estoque.php';
    $estoque = new Estoque();

	// ATENÇÃO: ajustar a variavel abaixo de acordo com o bando de dados
	$cod_estoque_tipo_lancamento = "5";
	$cod_pizzarias = validaVarPost('cod_pizzarias');



	$cod_ingredientes = validaVarPost('cod_ingredientes');
	$txt_nova_quantidade = validaVarPost('txt_nova_quantidade');
	$txt_quantidade_atual = validaVarPost('txt_quantidade_atual');
	$obs = validaVarPost('obs');
	$cont = count($obs);



	$cod_bebidas_ipi_conteudos = validaVarPost('cod_bebidas_ipi_conteudos');
	$txt_nova_quantidade_bebidas = validaVarPost('txt_nova_quantidade_bebidas');
	$txt_quantidade_atual_bebidas = validaVarPost('txt_quantidade_atual_bebidas');



	$obs_bebidas = validaVarPost('obs_bebidas');
	$cont_bebidas = count($obs_bebidas);

    $con = conectabd();



	for ($a=0; $a<$cont; $a++)
	{

		if ($txt_nova_quantidade[$a]!="0")
		{
			$float_estoque_atual = (float) $txt_quantidade_atual[$a];
			$float_estoque_novo = (float) moeda2bd($txt_nova_quantidade[$a]);
			$float_estoque_ajuste = $float_estoque_novo + (-1 * $float_estoque_atual);
			//echo "<Br>$a: ".$txt_quantidade_atual[$a]."   #   ".$txt_nova_quantidade[$a]."    #    ".$float_estoque_ajuste."    #    ".$cod_ingredientes[$a]."    #    0    #    INGREDIENTE - ".$cod_pizzarias." - ".$cod_estoque_tipo_lancamento."    #    0    #    0    #    ".$obs[$a];



			if ($obs[$a]=="Inserir obs...")
				$observacao = "";
			else
				$observacao = $obs[$a];
		    $res_estoque = $estoque->lancar_estoque($float_estoque_ajuste, $cod_ingredientes[$a], 0, "INGREDIENTE", $cod_pizzarias, $cod_estoque_tipo_lancamento, 0, 0, 0, $observacao );
		}
	}




	for ($a=0; $a<$cont_bebidas; $a++)
	{

		if ($txt_nova_quantidade_bebidas[$a]!="0")
		{



			$float_estoque_atual = (float) $txt_quantidade_atual_bebidas[$a];
			$float_estoque_novo = (float) moeda2bd($txt_nova_quantidade_bebidas[$a]);
			$float_estoque_ajuste = $float_estoque_novo + (-1 * $float_estoque_atual);
			//echo "<Br>$a: "."   #   ".$txt_quantidade_atual_bebidas[$a]."   #   ".$txt_nova_quantidade_bebidas[$a]."    #    ".$float_estoque_ajuste." - ".$cod_ingredientes[$a]." - 0 - BEBIDA - ".$cod_pizzarias." - ".$cod_estoque_tipo_lancamento." - 0 - 0 - ".$obs[$a];



			if ($obs_bebidas[$a]=="Inserir obs...")
				$observacao = "";
			else
				$observacao = $obs_bebidas[$a];
		    $res_estoque = $estoque->lancar_estoque($float_estoque_ajuste, 0, $cod_bebidas_ipi_conteudos[$a], "BEBIDA", $cod_pizzarias, $cod_estoque_tipo_lancamento, 0, 0, 0, $observacao );
		}
	}



    if ($res_estoque)
		mensagemOk('Estoque Ajustado com Sucesso!');
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
$mes = validaVarPost('mes');
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
    <td class="legenda tdbl tdbt sep" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td class="tdbt sep">&nbsp;</td>
    <td class="tdbr tdbt sep">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
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



  <tr>
    <td class="legenda tdbl sep" align="right"><label for="mes">Mês:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="mes" id="mes">
        <option value=""></option>
        <option value="1" <? if ($mes=="1") echo 'selected="selected"'; ?>>Janeiro</option>
        <option value="2" <? if ($mes=="2") echo 'selected="selected"'; ?>>Fevereiro</option>
        <option value="3" <? if ($mes=="3") echo 'selected="selected"'; ?>>Março</option>
        <option value="4" <? if ($mes=="4") echo 'selected="selected"'; ?>>Abril</option>
        <option value="5" <? if ($mes=="5") echo 'selected="selected"'; ?>>Maio</option>
        <option value="6" <? if ($mes=="6") echo 'selected="selected"'; ?>>Junho</option>
        <option value="7" <? if ($mes=="7") echo 'selected="selected"'; ?>>Julho</option>
        <option value="8" <? if ($mes=="8") echo 'selected="selected"'; ?>>Agosto</option>
        <option value="9" <? if ($mes=="9") echo 'selected="selected"'; ?>>Setembro</option>
        <option value="10" <? if ($mes=="10") echo 'selected="selected"'; ?>>Outubro</option>
        <option value="11" <? if ($mes=="11") echo 'selected="selected"'; ?>>Novembro</option>
        <option value="12" <? if ($mes=="12") echo 'selected="selected"'; ?>>Dezembro</option>
      </select>
    </td>
  </tr>



  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
  </form>

	<?
	if ( ($cod_pizzarias) || ($mes) )
	{
    $con = conectabd();

    $aliquota_imposto = 7.5;
    $aliquota_desperdicio = 5;

    // echo "<br />cod_pizzarias: ".$cod_pizzarias;
    // echo "<br />mes: ".$mes;

    $arr_preco_ingredientes = array();  //Indexado pelo próprio cod_ingredientes
    $arr_cmv = array(); //Indice 0 = cod_tamanhos / 1 = cod_pizzas / 2 = cmv / 3 = preco venda // 4 = imposto // 5 = disperdicio // 6 - lucro // 7 - Porc CMV

    $sql_ingredientes = "SELECT cod_ingredientes FROM ipi_ingredientes";
    $res_ingredientes = mysql_query($sql_ingredientes);
    while ($obj_ingredientes = mysql_fetch_object($res_ingredientes))
    {

      $sql_precos = "SELECT ee.cod_estoque_entrada, preco_unitario_entrada, quantidade_entrada, quantidade_embalagem_entrada, data_hota_entrada_estoque, ( (quantidade_entrada* preco_unitario_entrada) / (quantidade_entrada*quantidade_embalagem_entrada) ) preco_grama FROM ipi_estoque_entrada ee INNER JOIN ipi_estoque_entrada_itens eei ON (ee.cod_estoque_entrada = eei.cod_estoque_entrada) WHERE cod_pizzarias = '$cod_pizzarias' AND MONTH(data_hota_entrada_estoque) = $mes AND YEAR(data_hota_entrada_estoque) = ".date("Y")." AND eei.cod_ingredientes = ".$obj_ingredientes->cod_ingredientes;
      $res_precos = mysql_query($sql_precos);
      $preco_total = 0;
      $peso_total = 0;
      // echo "<br>1: ".$sql_precos;

      while ($obj_precos = mysql_fetch_object($res_precos))
      {
        $preco_total += ($obj_precos->quantidade_entrada * $obj_precos->preco_unitario_entrada);
        $peso_total += ($obj_precos->quantidade_entrada * $obj_precos->quantidade_embalagem_entrada);
      }

      if ($peso_total>0)
      {
        $media_ponderada = $preco_total / $peso_total;
      }
      else
      {
        $media_ponderada = 0;
      }
      $arr_preco_ingredientes[$obj_ingredientes->cod_ingredientes] = $media_ponderada;

    }

    // echo "<br />media_ponderada: ".$media_ponderada;

    // echo "<br /><pre>";
    // print_r($arr_preco_ingredientes);
    // echo "</pre>";


    $sql_pizzas = "SELECT p.cod_pizzas, pizza FROM ipi_pizzas p";
    $res_pizzas = mysql_query($sql_pizzas);
    $i = 0;
    while ($obj_pizzas = mysql_fetch_object($res_pizzas))
    {
      echo "<br><br><br><strong>".ucfirst(TIPO_PRODUTO).":</strong> ".$obj_pizzas->pizza;
      $sql_tamanhos = "SELECT cod_tamanhos, tamanho FROM ipi_tamanhos";
      $res_tamanhos = mysql_query($sql_tamanhos);
      while ($obj_tamanhos = mysql_fetch_object($res_tamanhos))
      {
       echo "<br><br>tamanho: ".$obj_tamanhos->tamanho;
        $sql_ingredientes_usados = "SELECT ie.cod_ingredientes, ie.quantidade_estoque_ingrediente, i.ingrediente FROM ipi_ingredientes_estoque ie INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes = ie.cod_ingredientes) WHERE ie.cod_tamanhos = '".$obj_tamanhos->cod_tamanhos."' AND ie.cod_pizzas = '".$obj_pizzas->cod_pizzas."'";
        $res_ingredientes_usados = mysql_query($sql_ingredientes_usados);
        // echo "<br>X: ".$sql_ingredientes_usados;
        $cmv = 0;
        while ($obj_ingredientes_usados = mysql_fetch_object($res_ingredientes_usados))
        {
          //echo "<br />".$sql_quantidade_ingrediente;
          //$res_quantidade_ingrediente = mysql_query($sql_quantidade_ingrediente);
          //$obj_quantidade_ingrediente = mysql_fetch_object($res_quantidade_ingrediente);

          echo "<br><br>Cod Ing: ".$obj_ingredientes_usados->cod_ingredientes." - ".$obj_ingredientes_usados->ingrediente;
          echo "<br>Qtde Usa: ".$obj_ingredientes_usados->quantidade_estoque_ingrediente;
          echo "<br>Preço Grama: ".$arr_preco_ingredientes[$obj_ingredientes_usados->cod_ingredientes];
          echo "<br>Tot ing: ".$obj_ingredientes_usados->quantidade_estoque_ingrediente * $arr_preco_ingredientes[$obj_ingredientes_usados->cod_ingredientes];

          $cmv += $obj_ingredientes_usados->quantidade_estoque_ingrediente * $arr_preco_ingredientes[$obj_ingredientes_usados->cod_ingredientes];
        }
        echo "<br>CMV: ".$cmv;
        $imposto = ($cmv * $aliquota_imposto) / 100;
        $cmv += $imposto;
        echo "<br>imposto: ".$imposto;
        $desperdicio = ($cmv * $aliquota_desperdicio) / 100;
        $cmv += $desperdicio;
        echo "<br>desperdicio: ".$desperdicio;
        echo "<br>Custo: ".$cmv;

        $sql_preco_pizza = "SELECT pt.preco FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_tamanhos='".$obj_tamanhos->cod_tamanhos."' AND pt.cod_pizzas = '".$obj_pizzas->cod_pizzas."' ";
        $res_preco_pizza = mysql_query($sql_preco_pizza);
        $obj_preco_pizza = mysql_fetch_object($res_preco_pizza);


        $arr_cmv[$i][0] = $obj_tamanhos->cod_tamanhos;
        $arr_cmv[$i][1] = $obj_pizzas->cod_pizzas;
        $arr_cmv[$i][2] = $cmv;
        $arr_cmv[$i][3] = $obj_preco_pizza->preco;
        $arr_cmv[$i][4] = $imposto;
        $arr_cmv[$i][5] = $desperdicio;
        $arr_cmv[$i][6] = $obj_preco_pizza->preco - $cmv;
        $arr_cmv[$i][7] = ($cmv * 100) / $obj_preco_pizza->preco;

        $sql_cmv = "INSERT INTO ipi_cpv (cod_pizzarias, cod_tamanhos, cod_pizzas, data_registro, preco_venda, cpv_real, cpv_teorico) VALUES($cod_pizzarias, ".$obj_tamanhos->cod_tamanhos.", ".$obj_pizzas->cod_pizzas.", '".date("Y-m-d")."', '".$obj_preco_pizza->preco."', '0', '".$cmv."')";
        $res_cmv = mysql_query($sql_cmv);





        echo "<br>Preço Venda: ".$obj_preco_pizza->preco;
        echo "<br>Lucro: ".$arr_cmv[$i][6];
        echo "<br>porc cmv: ".$arr_cmv[$i][7];


        $i++;
      }
    }

    // echo "<br /><pre>";
    // print_r($arr_cmv);
    // echo "</pre>";

	}
	else
		echo "Selecione a ".TIPO_EMPRESA." e o mês!";
}
?>

  </div>
  <!-- Tab Editar -->
  
 </div>

<? rodape(); ?>
