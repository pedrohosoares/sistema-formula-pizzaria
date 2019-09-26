<?php

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho_relatorio('Relatório de Entregas por Entregadores');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';

$cod_pizzarias = validaVarPost('cod_pizzarias');
$data_inicial = validaVarPost('data_inicial');
$data_final = validaVarPost('data_final');
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
    $arr_pizzarias = array();

    $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario)";
	  if ($cod_pizzarias)
    {
      $SqlBuscaPizzarias .= " AND cod_pizzarias IN ($cod_pizzarias)";
    }
		  
	  $SqlBuscaPizzarias .= " ORDER BY nome";

    $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);

     

    $num_pizzarias = mysql_num_rows($resBuscaPizzarias);

    for($n=1; $n<= $num_pizzarias; $n++)
    {
      $objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias);
      
      $arr_pizzarias[] = $objBuscaPizzarias->nome; 
        
    }

   echo implode($arr_pizzarias, ", ");

    ?>
    <br><br>
      
  <table class="listaEdicao" cellpadding="0" cellspacing="0" style="width: 700px" border="1">
    <thead>
      <tr>
        <td align="center" width="80">Código</td>
        <td align="center">Entregador</td>
        <td align="center">Quantidade (Pedidos + Avulsos)</td>
        <td align="center">Número de dias Trabalhados</td>
      </tr>
    </thead>
    <tbody>
      <?

      $sql_relatorio = "SELECT e.nome, p.cod_entregadores, p.data_hora_pedido, COUNT(p.cod_pedidos) AS quantidade, COUNT(DISTINCT DAY(p.data_hora_pedido))  AS dias
        FROM ipi_pedidos p
        INNER JOIN ipi_entregadores e
        ON (p.cod_entregadores = e.cod_entregadores)
        WHERE date(p.data_hora_pedido) between '$data_inicial' AND '$data_final'
        AND p.tipo_entrega = 'ENTREGA'
        AND p.cod_entregadores >0 ";

        if ($cod_pizzarias>0)
        {
          $sql_relatorio .= " AND p.cod_pizzarias = $cod_pizzarias";
          $sql_relatorio.= " AND p.cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ";
        }

        $sql_relatorio .= " GROUP BY e.nome ORDER BY quantidade DESC";
        $res_relatorio = mysql_query($sql_relatorio);
  
      while ($obj_relatorio = mysql_fetch_object($res_relatorio)) 
      {
        
        echo '<tr>';
        echo '<td align="center">'.$obj_relatorio->cod_entregadores.'</td>';
        echo '<td align="left" style="padding-left: 10px;">'.bd2texto($obj_relatorio->nome).'</td>';
        echo '<td align="center">' . $obj_relatorio->quantidade . '</td>';
        echo '<td align="center">' . $obj_relatorio->dias . '</td>';
        echo '</tr>';
      }
    ?>            
    </tbody>
  </table>
  


    </td>
  </tr>
</table>
  
 </div>

<? rodape_relatorio(); ?>
