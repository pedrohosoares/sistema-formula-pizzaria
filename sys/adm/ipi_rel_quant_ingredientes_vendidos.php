<?php

/**
 * ipi_rel_quant_vendidas.php: Relat�rio de Quantidades Vendidas
 * 
 * �ndice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relat�rio de Quantidades de Ingredientes Vendidos');

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>

<script type="text/javascript" src="../lib/js/calendario.js"></script>

<script>

window.addEvent('domready', function() {
  new vlaDatePicker('data_inicial', {prefillDate: false});
  new vlaDatePicker('data_final', {prefillDate: false});
});

</script>

<form name="frmFiltro" method="post">

  <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    <tr>
      <td width="150">
        <select name="cod_pizzarias" style="width: 150px;">
          <option value="TODOS">Todas <? echo ucfirst(TIPO_EMPRESAS)?></option>
          <?
          $con = conectabd();
          
          $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY nome";
          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
          while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
            echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
            
            if(validaVarPost('cod_pizzarias') == $objBuscaPizzarias->cod_pizzarias)
              echo 'selected';
            
            echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
          }
          
          desconectabd($con);
          ?>
        </select>
      </td>
      <td width="140">
        <label for="data_inicial">Data Inicial:</label>
        <input type="text" name="data_inicial" id="data_inicial" size="8" value="<? echo (validaVarPost('data_inicial') != '') ? validaVarPost('data_inicial') : date('d/m/Y') ?>">
      </td>
      <td width="135">
        <label for="data_final">Data Final:</label>
        <input type="text" name="data_final" id="data_final" size="8" value="<? echo (validaVarPost('data_final') != '') ? validaVarPost('data_final') : date('d/m/Y') ?>">
      </td>
      <td><input class="botaoAzul" type="submit" value="Filtrar"></td>
    </tr>
  </table>

  <br><br>
  
  <? 
  
  $con = conectabd(); 
  $data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
  $data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
  $cod_pizzarias = validaVarPost('cod_pizzarias');
  
  if(($data_inicial != '') && ($data_final != ''))
  {
      $sql_datas_inicial = " AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59'";
  }
  else
  {
      $sql_datas_inicial = '';
  }

  $sql_pizzarias = " AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
  if($cod_pizzarias > 0)
  {
      $sql_pizzarias .= " AND p.cod_pizzarias = '$cod_pizzarias'";
  }
  else
  {
      $sql_pizzarias .= '';
  }
  
  ?>
  
  <table class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
        <tr>
            <td>&nbsp;</td>
            <td style="background-color: #e5e5e5;" align="center"><b>TEL</b></td>
            <td style="background-color: #e5e5e5;" align="center"><b>NET</b></td>
            <td style="background-color: #e5e5e5;" align="center"><b>TOTAL</b></td>
        </tr>
        
        <?

        $sql_buscar_ingredientes = "SELECT i.ingrediente, 
                                    (SELECT COUNT(*) FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) WHERE pi.ingrediente_padrao = 0 AND pi.promocional = 0 AND pi.fidelidade = 0 AND pi.cod_ingredientes = i.cod_ingredientes AND p.origem_pedido = 'TEL' $sql_datas_inicial $sql_pizzarias) AS quantidade_tel, 
                                    (SELECT COUNT(*) FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) WHERE pi.ingrediente_padrao = 0 AND pi.promocional = 0 AND pi.fidelidade = 0 AND pi.cod_ingredientes = i.cod_ingredientes AND p.origem_pedido IN ('NET','IFOOD') $sql_datas_inicial $sql_pizzarias) AS quantidade_net
                                     FROM ipi_ingredientes i ORDER BY quantidade_tel + quantidade_net DESC, ingrediente";
        $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
        
        while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
        {
            echo '<tr>';
            echo '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_ingredientes->ingrediente) . '</b></td>';
            
            echo '<td align="center">' . $obj_buscar_ingredientes->quantidade_tel . '</td>';
            echo '<td align="center">' . $obj_buscar_ingredientes->quantidade_net . '</td>';
            echo '<td align="center">' . ($obj_buscar_ingredientes->quantidade_tel + $obj_buscar_ingredientes->quantidade_net) . '</td>';
        	
        	echo '</tr>';
        }
        
        ?>
        
        
    </table>
  
  <?
  
  desconectabd($con);
  ?>
  
  <br><br>
  
</form>

<? rodape(); ?>
