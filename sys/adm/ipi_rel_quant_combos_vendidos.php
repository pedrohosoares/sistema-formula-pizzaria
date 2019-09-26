<?php

/**
 * ipi_rel_quant_vendidas.php: Relatório de Quantidades Vendidas
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Quantidades de Combos Vendidos');

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
        $sql_buscar_combos = "SELECT c.nome_combo, 
                              (SELECT COUNT(*) FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_combos_produtos cp ON (pp.cod_combos_produtos = cp.cod_combos_produtos) WHERE pp.combo = 1 AND p.origem_pedido = 'TEL' AND cp.cod_combos = c.cod_combos AND cp.cod_combos_produtos = pp.cod_combos_produtos $sql_datas_inicial $sql_pizzarias) AS quantidade_tel, 
                              (SELECT COUNT(*) FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_combos_produtos cp ON (pp.cod_combos_produtos = cp.cod_combos_produtos) WHERE pp.combo = 1 AND p.origem_pedido IN ('NET','IFOOD') AND cp.cod_combos = c.cod_combos AND cp.cod_combos_produtos = pp.cod_combos_produtos $sql_datas_inicial $sql_pizzarias) AS quantidade_net,
                              (SELECT SUM(quantidade) FROM ipi_combos_produtos cp WHERE c.cod_combos=cp.cod_combos AND cp.tipo='PIZZA') qtde_pizzas_no_combo
                              FROM ipi_combos c ORDER BY quantidade_tel + quantidade_net DESC, nome_combo";
        /*
        $sql_buscar_combos = "SELECT c.nome_combo, 
                              (SELECT COUNT(*) FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_combos_produtos cp ON (pp.cod_combos_produtos = cp.cod_combos_produtos) WHERE pp.combo = 1 AND p.origem_pedido = 'TEL' AND cp.cod_combos = c.cod_combos AND cp.cod_combos_produtos = pp.cod_combos_produtos $sql_datas_inicial $sql_pizzarias) AS quantidade_tel, 
                              (SELECT COUNT(*) FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_combos_produtos cp ON (pp.cod_combos_produtos = cp.cod_combos_produtos) WHERE pp.combo = 1 AND p.origem_pedido IN ('NET','IFOOD') AND cp.cod_combos = c.cod_combos AND cp.cod_combos_produtos = pp.cod_combos_produtos $sql_datas_inicial $sql_pizzarias) AS quantidade_net
                              FROM ipi_combos c ORDER BY quantidade_tel + quantidade_net DESC, nome_combo";
        */
        $res_buscar_combos = mysql_query($sql_buscar_combos);
        //echo $sql_buscar_combos;
        while($obj_buscar_combos = mysql_fetch_object($res_buscar_combos))
        {
            echo '<tr>';
            echo '<td style="background-color: #e5e5e5;" align="center"><b>' . bd2texto($obj_buscar_combos->nome_combo) . '</b></td>';
            /*
            echo '<td align="center">' . $obj_buscar_combos->quantidade_tel . '</td>';
            echo '<td align="center">' . $obj_buscar_combos->quantidade_net . '</td>';
            echo '<td align="center">' . ($obj_buscar_combos->quantidade_tel + $obj_buscar_combos->quantidade_net) . '</td>';
            */

            echo '<td align="center">' . floor($obj_buscar_combos->quantidade_tel/$obj_buscar_combos->qtde_pizzas_no_combo) . '</td>';
            echo '<td align="center">' . floor($obj_buscar_combos->quantidade_net/$obj_buscar_combos->qtde_pizzas_no_combo) . '</td>';
            echo '<td align="center">' . floor(($obj_buscar_combos->quantidade_tel + $obj_buscar_combos->quantidade_net)/$obj_buscar_combos->qtde_pizzas_no_combo) . '</td>';
                        
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
