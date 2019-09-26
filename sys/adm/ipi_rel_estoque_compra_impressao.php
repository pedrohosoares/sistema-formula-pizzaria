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

cabecalho_relatorio('Relatório de Compra de Ingredientes');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';

$cod_pizzarias = validaVarPost('cod_pizzarias');
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
?>

	<div id="informacoes_gerais">
	<?
	echo "Gerado por <b>". $_SESSION['usuario']['usuario']. "</b> - " . date("d/m/Y H:i:s");
	?>
	</div>



    <table align="center">
	<tr>
    <td class="conteudo" align="center">
	<b>Pizzaria: </b>
	<?
	$conexao = conectabd();

    $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario)";
	if ($cod_pizzarias)
		$SqlBuscaPizzarias .= " AND cod_pizzarias IN ($cod_pizzarias)";
	$SqlBuscaPizzarias .= " ORDER BY nome";
	//echo $SqlBuscaPizzarias;
    $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
    while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
	{
		echo bd2texto($objBuscaPizzarias->nome).", ";
	}

    ?>
    <br><br>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0" border="1" align="center">
          <thead>
            <tr>
              <td align="center" width="250">Ingrediente</td>
              <td align="center" width="90">Qtde Atual</td>
              <td align="center" width="90">Qtde Minima</td>
              <td align="center" width="90">Qtde Maxima</td>
              <td align="center" width="90">Comprar</td>
            </tr>
          </thead>

          <tbody>
			<?

			$SqlBuscaIngredientes = "SELECT i.ingrediente, ip.quantidade_minima, ip.quantidade_maxima, ip.quantidade_perda, i.cod_ingredientes, (SELECT SUM(e.quantidade) FROM ipi_estoque e WHERE e.cod_ingredientes = i.cod_ingredientes AND e.cod_pizzarias IN ($cod_pizzarias_usuario)";
			if ($cod_pizzarias)
				$SqlBuscaIngredientes .= " AND cod_pizzarias IN ($cod_pizzarias)";

			$SqlBuscaIngredientes .= ") quantidade_atual FROM $tabela i LEFT JOIN ipi_ingredientes_pizzarias ip ON (i.cod_ingredientes = ip.cod_ingredientes AND ip.cod_pizzarias IN ($cod_pizzarias))  ORDER BY i.ingrediente";
			$resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
			//echo "<br>1: ".$SqlBuscaIngredientes;

			while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) 
			{
        $sql_buscar_unidade = "SELECT iup.divisor_comum FROM ipi_ingredientes ii INNER JOIN ipi_unidade_padrao iup ON (iup.cod_unidade_padrao = ii.cod_unidade_padrao) WHERE ii.cod_ingredientes = '".$objBuscaIngredientes->cod_ingredientes."'";
        $res_buscar_unidade = mysql_query($sql_buscar_unidade);
        $obj_buscar_unidade = mysql_fetch_object($res_buscar_unidade);
				echo '<tr>';


				echo '<td align="center">';
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "<font color='red'>";
        echo bd2texto($objBuscaIngredientes->ingrediente);
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "</font>";
        echo '</td>';




        echo '<td align="center">';
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "<font color='red'>";

        if ($objBuscaIngredientes->quantidade_atual)
          if($obj_buscar_unidade->divisor_comum)
            echo bd2texto(($objBuscaIngredientes->quantidade_atual/$obj_buscar_unidade->divisor_comum));
          else
            echo bd2texto($objBuscaIngredientes->quantidade_atual);

        else
          echo "0";

        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "</font>";
        echo '</td>';




        echo '<td align="center">';
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "<font color='red'>";
        if ($objBuscaIngredientes->quantidade_minima)
          if($obj_buscar_unidade->divisor_comum)
            echo bd2texto(($objBuscaIngredientes->quantidade_minima/$obj_buscar_unidade->divisor_comum));
          else
            echo bd2texto($objBuscaIngredientes->quantidade_minima);
        else
          echo "0";
  
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "</font>";
        echo '</td>';




        echo '<td align="center">';
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "<font color='red'>";
        if ($objBuscaIngredientes->quantidade_maxima)
          if($obj_buscar_unidade->divisor_comum)
            echo bd2texto(($objBuscaIngredientes->quantidade_maxima/$obj_buscar_unidade->divisor_comum));
          else
            echo bd2texto($objBuscaIngredientes->quantidade_maxima);
        else
          echo "0";
  
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "</font>";
        echo '</td>';



        echo '<td align="center">';
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "<font color='red'>";

        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          if($obj_buscar_unidade->divisor_comum)
            echo bd2texto((($objBuscaIngredientes->quantidade_maxima-$objBuscaIngredientes->quantidade_atual)/$obj_buscar_unidade->divisor_comum));
          else
            echo $objBuscaIngredientes->quantidade_maxima-$objBuscaIngredientes->quantidade_atual;
        else
          echo "0";

        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "</font>";
        echo '</td>';



				echo '</tr>';
			}
			?>
          </tbody>
        </table>
      



		<br>

      
        <table class="listaEdicao" cellpadding="0" cellspacing="0" border="1" align="center">
          <thead>
            <tr>
              <td align="center" width="250">Bebida</td>
              <td align="center" width="90">Estoque Atual</td>
              <td align="center" width="90">Qtde Minima</td>
              <td align="center" width="90">Qtde Maxima</td>
              <td align="center" width="90">Comprar</td>
            </tr>
          </thead>

          <tbody>
			<?
          	$SqlBuscaIngredientes = "SELECT bc.cod_bebidas_ipi_conteudos, b.bebida, c.conteudo, cp.quantidade_minima, cp.quantidade_maxima, cp.quantidade_perda, (SELECT SUM(e.quantidade) FROM ipi_estoque e WHERE e.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND e.cod_pizzarias IN ($cod_pizzarias) ) quantidade_atual FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) LEFT JOIN ipi_conteudos_pizzarias cp ON (bc.cod_bebidas_ipi_conteudos = cp.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias IN ($cod_pizzarias)) ORDER BY b.bebida, c.conteudo";
			$resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);

			//echo "<br>1: ".$SqlBuscaIngredientes;
			while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) 
			{
        $sql_buscar_unidade = "SELECT iup.divisor_comum FROM ipi_ingredientes ii INNER JOIN ipi_unidade_padrao iup ON (iup.cod_unidade_padrao = ii.cod_unidade_padrao) WHERE ii.cod_ingredientes = '".$objBuscaIngredientes->cod_ingredientes."'";
        $res_buscar_unidade = mysql_query($sql_buscar_unidade);
        $obj_buscar_unidade = mysql_fetch_object($res_buscar_unidade);
				echo '<tr>';



				echo '<td align="center">';
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "<font color='red'>";
        echo bd2texto($objBuscaIngredientes->bebida)." - ".bd2texto($objBuscaIngredientes->conteudo);
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "</font>";
        echo '</td>';

        echo '<td align="center">';
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "<font color='red'>";
        if ($objBuscaIngredientes->quantidade_atual)
          if($obj_buscar_unidade->divisor_comum)
            echo (int)bd2texto(($objBuscaIngredientes->quantidade_atual/$obj_buscar_unidade->divisor_comum));
          else
            echo (int)bd2texto($objBuscaIngredientes->quantidade_atual);
        else
          echo "0";
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "</font>";
        echo '</td>';

        echo '<td align="center">';
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "<font color='red'>";
        if ($objBuscaIngredientes->quantidade_minima)
          if($obj_buscar_unidade->divisor_comum)
            echo (int)bd2texto(($objBuscaIngredientes->quantidade_minima/$obj_buscar_unidade->divisor_comum));
          else
            echo (int)bd2texto($objBuscaIngredientes->quantidade_minima);
        else
          echo "0";
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "</font>";
        echo '</td>';



        echo '<td align="center">';
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "<font color='red'>";
        if ($objBuscaIngredientes->quantidade_maxima)
          if($obj_buscar_unidade->divisor_comum)
            echo (int)bd2texto(($objBuscaIngredientes->quantidade_maxima/$obj_buscar_unidade->divisor_comum));
          else
            echo (int)bd2texto($objBuscaIngredientes->quantidade_maxima);
        else
          echo "0";
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "</font>";
        echo '</td>';



        echo '<td align="center">';
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "<font color='red'>";
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          if($obj_buscar_unidade->divisor_comum)
            echo (int)bd2texto((($objBuscaIngredientes->quantidade_atual-$objBuscaIngredientes->quantidade_minima)/$obj_buscar_unidade->divisor_comum));
          else
            echo (int)$objBuscaIngredientes->quantidade_atual-$objBuscaIngredientes->quantidade_minima;
        else
          echo "0";
        if ($objBuscaIngredientes->quantidade_atual<$objBuscaIngredientes->quantidade_minima)
          echo "</font>";
        echo '</td>';



				echo '</tr>';
			}

			desconectabd($conexao);
			?>
          </tbody>
        </table>


    
    </td>
    </tr>
</table>
  
  
  
 </div>

<? rodape_relatorio(); ?>
