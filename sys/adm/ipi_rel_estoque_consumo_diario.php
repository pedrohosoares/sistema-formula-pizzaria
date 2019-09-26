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

cabecalho('Relatório de Consumo de Ingredientes por Período');

$acao = validaVarPost('acao');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';

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
$data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
$data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
$cod_pizzarias = validaVarPost('cod_pizzarias');
$acao = validaVarPost('acao');
?>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a href="javascript:;">Relatório</a></li>
    </ul>
  </div>
    
  <!-- Tab Editar -->
  <div class="painelTab">


  <link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
  <script type="text/javascript" src="../lib/js/calendario.js"></script>
  <script>
  window.addEvent('domready', function() 
  {
      new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
      new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
  });
  </script>



  <form name="frmFiltro" method="post">

  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="cod_pizzarias">Pizzaria:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbr tdbt">
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

  <tr>
      <td class="legenda tdbl" align="right"><label for="data_inicial">Data
      Inicial:</label></td>
      <td class="">&nbsp;</td>
      <td class="tdbr"><input class="requerido" type="text"
          name="data_inicial" id="data_inicial" size="8"
          value="<?
          echo bd2data($data_inicial)?>"
          onkeypress="return MascaraData(this, event)"> &nbsp; <a
          href="javascript:;" id="botao_data_inicial"><img
          src="../lib/img/principal/botao-data.gif"></a></td>
  </tr>

  <tr>
      <td class="legenda tdbl " align="right"><label for="data_final">Data
      Final:</label></td>
      <td >&nbsp;</td>
      <td class="tdbr "><input class="requerido" type="text"
          name="data_final" id="data_final" size="8"
          value="<?
          echo bd2data($data_final)?>"
          onkeypress="return MascaraData(this, event)"> &nbsp; <a
          href="javascript:;" id="botao_data_final"><img
          src="../lib/img/principal/botao-data.gif"></a></td>
  </tr>

  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3">
  <input class="botaoAzul" type="submit" value="Buscar">
  </td></tr>
  
  </table>

  <br />

  <input type="hidden" name="acao" value="buscar">

  </form>

<?
if ($acao == "buscar")
{

  if ($cod_pizzarias)
  {


    $sql_pizzas_periodo = "
      SELECT pp.quant_fracao, i.ingrediente, ip.cod_ingredientes, ie.quantidade_estoque_ingrediente FROM ipi_pedidos p 
      INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) 
      INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) 
      INNER JOIN ipi_ingredientes_ipi_pizzas ip ON (ip.cod_pizzas = pf.cod_pizzas)
      INNER JOIN ipi_ingredientes_estoque ie ON (ip.cod_ingredientes = ie.cod_ingredientes AND pp.cod_tamanhos = ie.cod_tamanhos AND pf.cod_pizzas = ie.cod_pizzas) 
      INNER JOIN ipi_ingredientes i ON (ip.cod_ingredientes = i.cod_ingredientes)
      WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") 
      AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' 
      AND p.situacao NOT IN ('CANCELADO') 
      AND p.cod_pizzarias = $cod_pizzarias 
      ORDER BY i.ingrediente
      ";

    //echo "<br>sql: ".$sql_pizzas_periodo;
	  $res_pizzas_periodo = mysql_query($sql_pizzas_periodo);
    //echo "<table border='1'>";
    $ingredientes_consumidos = array();
	  while ($obj_pizzas_periodo = mysql_fetch_object($res_pizzas_periodo))
    {
      /*
      echo "<tr>";
        echo "<td>";
        echo $obj_pizzas_periodo->cod_ingredientes . " - ";
        echo $obj_pizzas_periodo->ingrediente;
        echo "</td>";

        echo "<td>";
        echo $obj_pizzas_periodo->quant_fracao;
        echo "</td>";

        echo "<td>";
        echo $obj_pizzas_periodo->quantidade_estoque_ingrediente;
        echo "</td>";

        echo "<td>";
        echo $obj_pizzas_periodo->quantidade_estoque_ingrediente/$obj_pizzas_periodo->quant_fracao;
        echo "</td>";

      echo "</tr>";
      */
      $ingredientes_consumidos[$obj_pizzas_periodo->cod_ingredientes] = $ingredientes_consumidos[$obj_pizzas_periodo->cod_ingredientes] + $obj_pizzas_periodo->quantidade_estoque_ingrediente/$obj_pizzas_periodo->quant_fracao;
    }







    $sql_ingredientes_adicionais = "
      SELECT pp.quant_fracao, i.ingrediente, pi.cod_ingredientes, it.quantidade_estoque_extra FROM ipi_pedidos p 

      INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) 
      INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) 
      INNER JOIN ipi_pedidos_ingredientes pi ON (pi.cod_pedidos = pf.cod_pedidos AND pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas)
      INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (pi.cod_ingredientes = it.cod_ingredientes AND pp.cod_tamanhos = it.cod_tamanhos) 
      INNER JOIN ipi_ingredientes i ON (pi.cod_ingredientes = i.cod_ingredientes)

      WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") 
      AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' 
      AND p.situacao NOT IN ('CANCELADO') 
      AND p.cod_pizzarias = $cod_pizzarias 
      AND pi.ingrediente_padrao = false 

      ORDER BY i.ingrediente
      ";


  //  echo "<br>sqlX: ".$sql_ingredientes_adicionais;
	  $res_ingredientes_adicionais = mysql_query($sql_ingredientes_adicionais);
  //  echo "<table border='1'>";
	  while ($obj_ingredientes_adicionais = mysql_fetch_object($res_ingredientes_adicionais))
    {
  /*    
      echo "<tr>";
        echo "<td>";
        echo $obj_ingredientes_adicionais->cod_ingredientes . " - ";
        echo $obj_ingredientes_adicionais->ingrediente;
        echo "</td>";

        echo "<td>";
        echo $obj_ingredientes_adicionais->quant_fracao;
        echo "</td>";

        echo "<td>";
        echo $obj_ingredientes_adicionais->quantidade_estoque_extra;
        echo "</td>";

        echo "<td>";
        echo $obj_ingredientes_adicionais->quantidade_estoque_extra/$obj_ingredientes_adicionais->quant_fracao;
        echo "</td>";

      echo "</tr>";
  */
      $ingredientes_consumidos[$obj_ingredientes_adicionais->cod_ingredientes] = $ingredientes_consumidos[$obj_ingredientes_adicionais->cod_ingredientes] + $obj_ingredientes_adicionais->quantidade_estoque_extra/$obj_ingredientes_adicionais->quant_fracao;
    }
  /*
    echo "</table>";

    echo "<pre>";
    print_r($ingredientes_consumidos);
    echo "</pre>";
  */
    ?>






  <?
    $sql_borda = "
      SELECT i.ingrediente, b.cod_ingredientes, tb.quantidade_estoque_borda FROM ipi_pedidos p 
      INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) 
      INNER JOIN ipi_pedidos_bordas pb ON (pb.cod_pedidos = pp.cod_pedidos AND pb.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) 
      INNER JOIN ipi_tamanhos_ipi_bordas tb ON (tb.cod_bordas = pb.cod_bordas AND tb.cod_tamanhos = pp.cod_tamanhos) 
      INNER JOIN ipi_bordas b ON (b.cod_bordas = pb.cod_bordas)
      INNER JOIN ipi_ingredientes i ON (b.cod_ingredientes = i.cod_ingredientes)

      WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") 
      AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' 
      AND p.situacao NOT IN ('CANCELADO') 
      AND p.cod_pizzarias = $cod_pizzarias 

      ORDER BY i.ingrediente
      ";

    //echo "<br>sqlX: ".$sql_borda;
	  $res_borda = mysql_query($sql_borda);
    //echo "<table border='1'>";
	  while ($obj_borda = mysql_fetch_object($res_borda))
    {
      /*
      echo "<tr>";
        echo "<td>";
        echo $obj_borda->cod_ingredientes . " - ";
        echo $obj_borda->ingrediente;
        echo "</td>";

        echo "<td>";
        echo $obj_borda->quant_fracao;
        echo "</td>";

        echo "<td>";
        echo $obj_borda->quantidade_estoque_extra;
        echo "</td>";

        echo "<td>";
        echo $obj_borda->quantidade_estoque_borda;
        echo "</td>";

      echo "</tr>";
      */
      $ingredientes_consumidos[$obj_borda->cod_ingredientes] = $ingredientes_consumidos[$obj_borda->cod_ingredientes] + $obj_borda->quantidade_estoque_borda;
    }
  /*
    echo "</table>";

    echo "<pre>";
    print_r($ingredientes_consumidos);
    echo "</pre>";
  */
    ?>

      <table align="center">
      <tr>
      <td class="conteudo" align="center">
      
      
      <table class="listaEdicao" cellpadding="0" cellspacing="0" align="center">
        <thead>
          <tr>
            <td align="center" width="250">Ingrediente</td>
            <td align="center" width="90">Qtde Consumida</td>
          </tr>
        </thead>

        <tbody>
			  <?

        foreach ($ingredientes_consumidos as $key => $value )
			  {
				  echo '<tr>';

          $obj_ingredientes = executaBuscaSimples(sprintf("SELECT * FROM ipi_ingredientes i WHERE i.cod_ingredientes = %d", $key), $con);


				  echo '<td align="center">';
				  echo bd2texto($obj_ingredientes->ingrediente);
				  echo '</td>';


				  echo '<td align="center">';
				  echo bd2moeda($value);
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
            <td align="center" width="90">Qtde Consumida</td>
          </tr>
        </thead>

        <tbody>
			  <?

      $sql_bebidas = "
      SELECT bc.cod_bebidas_ipi_conteudos, b.bebida, c.conteudo, pb.quantidade FROM ipi_pedidos p 

      INNER JOIN ipi_pedidos_bebidas pb ON (p.cod_pedidos = pb.cod_pedidos) 
      INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) 
      INNER JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) 
      INNER JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos)

      WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") 
      AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' 
      AND p.situacao NOT IN ('CANCELADO') 
      AND p.cod_pizzarias = $cod_pizzarias

      ORDER BY b.bebida
      ";

		  $res_bebidas = mysql_query($sql_bebidas);
      $bebidas_consumidas = array();
      //echo "<table>";
      while ($obj_bebidas = mysql_fetch_object($res_bebidas))
      {
  /*
        echo "<tr>";

          echo "<td>";
          echo $obj_bebidas->cod_bebidas_ipi_conteudos . " - ";
          echo $obj_bebidas->bebida . " - " . $obj_bebidas->conteudo;
          echo "</td>";

          echo "<td>";
          echo $obj_bebidas->quantidade;
          echo "</td>";

        echo "</tr>";
  */
        $bebidas_consumidas[$obj_bebidas->cod_bebidas_ipi_conteudos] = $bebidas_consumidas[$obj_bebidas->cod_bebidas_ipi_conteudos] + $obj_bebidas->quantidade;
      }
  /*  
    echo "</table>";

    echo "<pre>";
    print_r($bebidas_consumidas);
    echo "</pre>";
  */


        foreach ($bebidas_consumidas as $key => $value )
			  {
				  echo '<tr>';

          $obj_bebida = executaBuscaSimples(sprintf("SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos = %d", $key), $con);


				  echo '<td align="center">';
				  echo $obj_bebida->bebida . " - " . $obj_bebida->conteudo;
				  echo '</td>';


				  echo '<td align="center">';
				  echo ($value);
				  echo '</td>';


				  echo '</tr>';
			  }

			  desconectabd($con);
			  ?>
            </tbody>
          </table>

    <!--  
	  <form method="post" name="frmImpressao" action="ipi_rel_estoque_compra_impressao.php" target="_blank">
		  <input type="hidden" name="cod_pizzarias" value="<? echo $cod_pizzarias; ?>">
		  <input type="submit" name="bt_imprimir" value="Impressão">
	  </form>
    -->

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
    <?
    }
    else
      echo "Selecione uma pizzaria!";
  }
?>


  </div>
  <!-- Tab Editar -->
  
  
  
 </div>

<? rodape(); ?>
