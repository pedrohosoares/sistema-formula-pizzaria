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

cabecalho('Relação dos Inventários');

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
					input[i].value = ''; SELECT DISTINCT(ec1.data_hora_contagem1), ec1.cod_usuarios, u.nome FROM ipi_estoque_contagem ec1 INNER JOIN nuc_usuarios u ON (ec1.cod_usuarios = u.cod_usuarios )WHERE ec2.cod_pizzarias IN (1,2,3,4,5,6,7,8,9,10) AND ec2.data_hora_contagem1 BETWEEN '2013-03-01 00:00:00' AND '2013-04-23 23:59:59' AND ec2.cod_pizzarias = 1 ORDER BY ec2.data_hora_contagem1 DESC
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
$cod_inventarios = validaVarPost('cod_inventarios');
$cod_pizzarias = validaVarPost('cod_pizzarias');
$acao = validaVarPost('acao');
?>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a style="border:0" href="javascript:;"></a></li>
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
    <td class="legenda tdbl tdbt" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbr tdbt">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <!-- <option value="">Todas as Pizzarias</option> -->
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
      $sql_inventarios = "SELECT eci.data_hora_contagem,eci.cod_inventarios, u.nome FROM ipi_estoque_inventario eci INNER JOIN nuc_usuarios u ON (eci.cod_usuarios_contagem = u.cod_usuarios ) ";
      $sql_inventarios .= "WHERE eci.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND eci.data_hora_contagem BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND eci.cod_pizzarias = '$cod_pizzarias' ORDER BY eci.data_hora_contagem DESC";
	    $res_inventarios = mysql_query($sql_inventarios);
      //echo "<br>sql: ".$sql_inventarios;
      ?>
      <table class="listaEdicao" cellpadding="0" cellspacing="0" align="center" style="width: 600px;">
        <thead>
          <tr>
            <td align="center" width="100">Detalhes</td>
            <td align="center" width="300">Usuário Registrou Inventário</td>
            <td align="center" width="200">Data Inventário</td>
          </tr>
        </thead>

        <tbody>
        <?
	      while ($obj_inventarios = mysql_fetch_object($res_inventarios))
        {
          echo "<tr>";

          echo "<td align='center'>";
          echo '
	        <form method="post" name="frm_detalhes" action="'.$PHP_SELF.'">
		        <input type="hidden" name="cod_inventarios" value="'.$obj_inventarios->cod_inventarios.'">
		        <input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'">
		        <input type="hidden" name="data_inicial" value="'.bd2data($data_inicial).'">
		        <input type="hidden" name="data_final" value="'.bd2data($data_final).'">
		        <input type="hidden" name="acao" value="detalhes">
		        <input type="submit" class="botaoAzul" name="bt_detalhes" value="Detalhes">
	        </form>';
          echo "</td>";

          echo "<td>";
          echo $obj_inventarios->nome;
          echo "</td>";

          echo "<td align='center'>";
          echo bd2datahora($obj_inventarios->data_hora_contagem);
          echo "</td>";

          echo "</tr>";
        }
        echo "</tbody>";

      echo "</table>";
      ?>



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
  {
    echo "Selecione uma pizzaria!";
  }
}
elseif ($acao == "detalhes")
{
  $arr_unidade_padrao = array();
  $sql_buscar_unidades_padroes = "SELECT * from ipi_unidade_padrao";
  $res_buscar_unidades_padroes = mysql_query($sql_buscar_unidades_padroes);
  while($obj_buscar_unidades_padroes = mysql_fetch_object($res_buscar_unidades_padroes))
  {
    $arr_unidade_padrao[$obj_buscar_unidades_padroes->cod_unidade_padrao]['abr'] = $obj_buscar_unidades_padroes->abreviatura;
    $arr_unidade_padrao[$obj_buscar_unidades_padroes->cod_unidade_padrao]['divisor'] = $obj_buscar_unidades_padroes->divisor_comum;

  }
// LEFT JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas_ipi_conteudos = ec2.cod_bebidas_ipi_conteudos) LEFT JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) LEFT JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) WHERE ec2.data_hora_contagem1 IN (SELECT DISTINCT(data_hora_contagem1) FROM ipi_estoque_contagem ec1)
  $sql_inventarios = "SELECT i.ingrediente_abreviado as ingrediente,ec2.quantidade_ajuste,c.conteudo,ec2.tipo_contagem,i.cod_unidade_padrao,b.bebida, ec2.quantidade1, ec2.quantidade2, ec2.quantidade3, ec2.data_hora_contagem1, ec2.data_hora_contagem2, ec2.data_hora_contagem3, ec2.observacao FROM ipi_estoque_inventario eci LEFT JOIN ipi_estoque_contagem ec2 on ec2.cod_inventarios = eci.cod_inventarios INNER JOIN nuc_usuarios u ON (eci.cod_usuarios_contagem = u.cod_usuarios ) LEFT JOIN ipi_ingredientes i ON (i.cod_ingredientes = ec2.cod_ingredientes) LEFT JOIN ipi_bebidas_ipi_conteudos bc on bc.cod_bebidas_ipi_conteudos = ec2.cod_bebidas_ipi_conteudos LEFT JOIN ipi_conteudos c on c.cod_conteudos = bc.cod_conteudos LEFT JOIN ipi_bebidas b on b.cod_bebidas = bc.cod_bebidas";
  $sql_inventarios .= " WHERE eci.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND eci.cod_inventarios='".$cod_inventarios."' AND eci.cod_pizzarias = '$cod_pizzarias'  ORDER BY i.ingrediente,b.bebida";//AND   tipo_contagem = 'INGREDIENTE'
  $res_inventarios = mysql_query($sql_inventarios);
  //echo "<br>sql: ".$sql_inventarios;
  ?>
  <table class="listaEdicao" cellpadding="0" cellspacing="0" align="center" style="width: 950px;">
    <thead>
      <tr>
        <td align="center" width="300">Ingrediente</td>
        <!-- <td align="center" width="90">Data e Hora<br />Contagem1</td>
        <td align="center" width="90">Data e Hora<br />Contagem2</td> -->
        <td align="center" width="90">Data e Hora<br />da Contagem</td>
        <td align="center" width="70">Qtd1</td>
        <td align="center" width="70">Qtd2</td>
        <td align="center" width="70">Qtd3</td>
        <td align="center" width="70">Diferença Ajustada</td>
        <td align="center" width="120">Obs</td>
      </tr>
    </thead>

    <tbody>
    <?
    while ($obj_inventarios = mysql_fetch_object($res_inventarios))
    {
      echo "<tr>";

      echo "<td>";
      echo ($obj_inventarios->ingrediente !="" ? $obj_inventarios->ingrediente." (".$arr_unidade_padrao[$obj_inventarios->cod_unidade_padrao]['abr'].")" : $obj_inventarios->bebida." - ".$obj_inventarios->conteudo);
      echo "</td>";

/*      echo "<td align='center'>";
      echo bd2datahora($obj_inventarios->data_hora_contagem1);
      echo "</td>";

      echo "<td align='center'>";
      echo bd2datahora($obj_inventarios->data_hora_contagem2);
      echo "</td>";*/

      echo "<td align='center'>";
      echo bd2datahora($obj_inventarios->data_hora_contagem1);
      echo "</td>";

      
      if($obj_inventarios->tipo_contagem=="INGREDIENTE")
      {
        $divisor = ($arr_unidade_padrao[$obj_inventarios->cod_unidade_padrao]['divisor'] !="" ? $arr_unidade_padrao[$obj_inventarios->cod_unidade_padrao]['divisor'] : 1);
        echo "<td align='center'>";
        echo number_format($obj_inventarios->quantidade1/$divisor,2);
        echo "</td>";

        echo "<td align='center'>";
        echo number_format($obj_inventarios->quantidade2/$divisor,2);
        echo "</td>";

        echo "<td align='center'>";
        echo number_format($obj_inventarios->quantidade3/$divisor,2);
        echo "</td>";

         echo "<td align='center'>";
        echo number_format($obj_inventarios->quantidade_ajuste/$divisor,2);
        echo "</td>"; 
              
        echo "<td align='center'>";
        echo $obj_inventarios->observacao;
        echo "</td>";
      }else
      {
        echo "<td align='center'>";
        echo bd2moeda($obj_inventarios->quantidade1);
        echo "</td>";

        echo "<td align='center'>";
        echo bd2moeda($obj_inventarios->quantidade2);
        echo "</td>";

        echo "<td align='center'>";
        echo bd2moeda($obj_inventarios->quantidade3);
        echo "</td>";

        echo "<td align='center'>";
        echo bd2moeda($obj_inventarios->quantidade_ajuste);
        echo "</td>";

        echo "<td align='center'>";
        echo bd2moeda($obj_inventarios->observacao);
        echo "</td>";
      }

      echo "</tr>";
    }
    echo "</tbody>";

  echo "</table>";

}
?>


  </div>
  <!-- Tab Editar -->
  
  
  
 </div>

<? rodape(); ?>
