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

cabecalho('Mapa de estoque');

$acao = validaVarPost('acao');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>
<link  href="../lib/js/moodialog/css/MooDialog.css" rel="stylesheet" type="text/css" media="screen" />
<script src="../lib/js/moodialog/MooDialog.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/Overlay.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Fx.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Alert.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Request.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Confirm.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Prompt.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Error.js" type="text/javascript"></script>
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
$filtros = "+'&cod_pizzarias=".$cod_pizzarias."&data_inicial=".$data_inicial."&data_final=".$data_final."'";
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
  function detalhes_ingrediente(cod,nome_ingrediente,unidade,divisor)
  {
    var opcoes = "method:'post'";//method:'post'  
    var variaveis = 'acao=explodir_ingrediente&nome_ingrediente='+nome_ingrediente+'&unidade='+unidade+'&divisor='+divisor+'&cod_ingredientes='+cod<? echo $filtros ?>;
    var reqDialog = new MooDialog.Request('ipi_rel_mapa_estoque_ajax.php',variaveis,opcoes, {
      'class': 'MooDialog',
      autoOpen: false,
      title: nome_ingrediente,
      size: {
             width: 900,
             height: 500
            }
    });
    // You want the request dialog instance to set the onRequest message, so you have to do it in two steps.
    reqDialog.setRequestOptions({
      onRequest: function(){
        reqDialog.setContent('Carregando...')
      }
    }).open();
  }

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
        
        <?
    		$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
        $con = conectabd();
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        echo "<option value='$cod_pizzarias_usuario'>Todas as Pizzarias</option>";
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

  
    $arr_consumo1 = array();

      //ingredientes padroes
    $sql_buscar_pizzas = "SELECT pf.cod_pizzas,pp.cod_tamanhos,pp.quant_fracao,ie.cod_ingredientes,ie.quantidade_estoque_ingrediente,pp.cod_pedidos_pizzas from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_pedidos_fracoes pf on pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_ingredientes_estoque ie on ie.cod_pizzas = pf.cod_pizzas where ie.cod_tamanhos = pp.cod_tamanhos and p.cod_pedidos in (SELECT p.cod_pedidos from ipi_pedidos p inner join ipi_pizzarias pi on pi.cod_pizzarias = p.cod_pizzarias where p.cod_pizzarias in ($cod_pizzarias) AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.situacao IN ('BAIXADO') AND p.cod_pizzarias IN ($cod_pizzarias_usuario))";

    $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
    //echo $sql_buscar_pizzas;
    while($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
    {
      $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] = $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] + ($obj_buscar_pizzas->quantidade_estoque_ingrediente / $obj_buscar_pizzas->quant_fracao);
      //echo "<br/>pedido= ".$obj_buscar_pizzas->cod_pedidos_pizzas." tamanho = ".$obj_buscar_pizzas->cod_tamanhos." fracao=".$obj_buscar_pizzas->quant_fracao." qtd=".($obj_buscar_pizzas->quantidade_estoque_ingrediente / $obj_buscar_pizzas->quant_fracao);
    }
    $obj_buscar_pizzas = "";
    $sql_buscar_pizzas2 = "SELECT pf.cod_pizzas,pp.cod_tamanhos,pp.quant_fracao,it.cod_ingredientes,it.quantidade_estoque_extra,pp.cod_pedidos_pizzas from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_pedidos_fracoes pf on pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_pedidos_ingredientes pe on pe.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_ingredientes_ipi_tamanhos it on it.cod_ingredientes = pe.cod_ingredientes where it.cod_pizzarias = 1 and it.cod_tamanhos = pp.cod_tamanhos and pe.ingrediente_padrao = 0  and p.cod_pedidos in (SELECT p.cod_pedidos from ipi_pedidos p inner join ipi_pizzarias pi on pi.cod_pizzarias = p.cod_pizzarias where p.cod_pizzarias in ($cod_pizzarias) AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.situacao IN ('BAIXADO') AND p.cod_pizzarias IN ($cod_pizzarias_usuario))";

    $res_buscar_pizzas2 = mysql_query($sql_buscar_pizzas2);
    //echo "<br/>".$sql_buscar_pizzas2;
    while($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas2))
    {
      $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] = $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] + ($obj_buscar_pizzas->quantidade_estoque_extra / $obj_buscar_pizzas->quant_fracao);
      //echo "<br/>22pedido= ".$obj_buscar_pizzas->cod_pedidos_pizzas." tamanho = ".$obj_buscar_pizzas->cod_tamanhos." fracao=".$obj_buscar_pizzas->quant_fracao." qtd=".($obj_buscar_pizzas->quantidade_estoque_extra / $obj_buscar_pizzas->quant_fracao);
    }

    /*echo "<pre>";
    print_r($arr_consumo1);
    echo "</pre>";
    echo "<br/><br/><br/><br/><br/><br/><br/>";
        echo "<pre>";
    print_r($arr_consumo2);
    echo "</pre>";
        echo "<br/><br/><br/><br/><br/><br/><br/>";
        echo "<pre>";
    print_r($arr_consumo3);
    echo "</pre>";*/
    ?>
      <table class="listaEdicao" cellpadding="0" cellspacing="0" align="center">
      <thead>
        <tr>
          <td align="center" width="250">Ingrediente</td>
          <td align="center" width="90">Qtde Consumida</td>
        </tr>
      </thead>
      <tbody>
          
            <?
            arsort($arr_consumo1);
            $arr_nome_ing = array();

            $sql_buscar_uni_padrao = "SELECT u.cod_unidade_padrao,i.ingrediente,i.cod_ingredientes,u.abreviatura,u.divisor_comum from ipi_unidade_padrao u inner join ipi_ingredientes i on i.cod_unidade_padrao = u.cod_unidade_padrao";
            $res_buscar_uni_padrao = mysql_query($sql_buscar_uni_padrao);
            while($obj_buscar_uni_padrao = mysql_fetch_object($res_buscar_uni_padrao))
            {
              $unidades[$obj_buscar_uni_padrao->cod_unidade_padrao]['abr'] = $obj_buscar_uni_padrao->abreviatura;
              $unidades[$obj_buscar_uni_padrao->cod_unidade_padrao]['divisor'] = $obj_buscar_uni_padrao->divisor_comum;
              $ing_unidade[$obj_buscar_uni_padrao->cod_ingredientes] = $obj_buscar_uni_padrao->cod_unidade_padrao;
              $arr_nome_ing[$obj_buscar_uni_padrao->cod_ingredientes] = $obj_buscar_uni_padrao->ingrediente;
            }


            foreach ( $arr_consumo1 as $ingrediente => $quantidade) 
            {
              $arr_unidade = $unidades[$ing_unidade[$ingrediente]];
              $nome_ingrediente =  $arr_nome_ing[$ingrediente];
              $quant_dividida = ($quantidade/$arr_unidade['divisor']);
              $quant_exibir = round($quant_dividida,2)." ".$arr_unidade['abr'];
              echo "<tr><td align='center'><a href='javascript:void(0);' onclick='detalhes_ingrediente(\"".$ingrediente."\",\"".$nome_ingrediente."\",\"".$arr_unidade['abr']."\",\"".$arr_unidade['divisor']."\")'>".$nome_ingrediente."</a></td><td align='center'>".$quant_exibir."</td></tr>";
            }
    ?>
        </tbody>
        </table>
      




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
desconectar_bd($con);
?>


  </div>
  <!-- Tab Editar -->
  
  
  
 </div>

<? rodape(); ?>
