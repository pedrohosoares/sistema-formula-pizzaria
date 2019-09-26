<?php

/**
 * ipi_rel_entregadores.php: Relatório de Entregas por Entregadores
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Entregas por Entregadores');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>

<script type="text/javascript" src="../lib/js/calendario.js"></script>

<script>

function trocar(idTrocar) {
  if (document.getElementById(idTrocar).style.display=="none")
    document.getElementById(idTrocar).style.display='table-row';
  else
    document.getElementById(idTrocar).style.display='none';
}

window.addEvent('domready', function() { 
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 

</script>
<?php
$data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
$data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
$cod_pizzarias = validaVarPost('cod_pizzarias');
?>

<form name="frmFiltro" method="post">
 <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbr tdbt">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
        <?
        $con = conectabd();
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY p.nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
        while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
          echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
          
          if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
        }
        ?>
      </select>
    </td>
  </tr>


  <tr>
    <td class="legenda tdbl" align="right"><label for="data_inicial">Data Inicial:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo bd2data($data_inicial) ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>
  

  <tr>
    <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
    <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo bd2data($data_final) ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>

  <tr>
    <td align="center" class="tdbl tdbb tdbr" colspan="3">
    <input class="botaoAzul" type="submit" value="Buscar">
    </td>
  </tr>

  <input type="hidden" name="acao" value="buscar">

  </table><br /><br />
</form>



  <?
  switch ($acao) {
     case 'buscar':
       $con = conectabd();
  ?>
       <table class="listaEdicao" cellpadding="0" cellspacing="0" style="width: 800px">
    <thead>
      <tr>
        <td align="center" width="80">Código</td>
        <td align="center">Entregador</td>
        <td align="center">Quantidade (Pedidos + Avulsos)</td>
        <td align="center"> Número de dias Trabalhados</td>
      </tr>
    </thead>
    <tbody>
    
    <?
    
    $SqlBuscaEntregas = "SELECT * FROM ipi_entregadores e WHERE 1=1";

    if ($cod_pizzarias > 0) 
    {
      $SqlBuscaEntregas .= " AND cod_pizzarias = ".$cod_pizzarias;
      $SqlBuscaEntregas .= " AND cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ";
    }
    $SqlBuscaEntregas .= " ORDER BY e.nome";
      
    $resBuscaEntregas = mysql_query($SqlBuscaEntregas);
    

    while ($objBuscaEntregas = mysql_fetch_object($resBuscaEntregas)) 
    {
      $sql_relatorio = "SELECT  p.data_hora_pedido, COUNT(p.cod_pedidos) AS quantidade_pedidos, COUNT(DISTINCT DAY(p.data_hora_pedido))  AS dias
        FROM ipi_pedidos p
        INNER JOIN ipi_entregadores e
        ON (p.cod_entregadores = e.cod_entregadores)
        WHERE date(p.data_hora_pedido) between '$data_inicial' AND '$data_final'
        AND p.tipo_entrega = 'ENTREGA'
        AND p.cod_entregadores = $objBuscaEntregas->cod_entregadores
        AND p.situacao  NOT IN ('CANCELADO')
        AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";


        $sql_buscar_total_entregas_avulsas = "SELECT COUNT(*) AS avulsas 
        FROM ipi_entregas_avulsas en
        WHERE cod_entregadores = $objBuscaEntregas->cod_entregadores
        AND date(en.data_hora_entrega) between '$data_inicial' AND '$data_final'
        AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";


      $obj_relatorio = executaBuscaSimples($sql_relatorio, $con);
      $obj_avulsas = executaBuscaSimples($sql_buscar_entregas_avulsas, $con);
      
      
      echo '<tr>';
      echo '<td align="center">'.$objBuscaEntregas->cod_entregadores.'</td>';
      echo '<td align="left" style="padding-left: 10px;"><a href="javascript:;" onclick="trocar(\''.'result_'.$objBuscaEntregas->cod_entregadores.'\')">'.bd2texto($objBuscaEntregas->nome).'</a></td>';
      echo '<td align="center">' . ($obj_relatorio->quantidade_pedidos + $obj_avulsas->avulsas) . '</td>';     
      echo '<td align="center">'.$obj_relatorio->dias.'</td>';
      echo '</tr>';

      echo '<tr style="display: none;" id="result_'.$objBuscaEntregas->cod_entregadores.'">';
      echo '<td colspan="4" align="left" style="padding: 20px;">';
      
      echo '<table cellpadding="0" cellspacing="0">';
      echo '<thead>';
      echo '<tr><td align="center" colspan="3">Entregas Pedidos</td></tr>';
      echo '<tr><td align="center">Bairro</td><td align="center">Quantidade</td><td align="center">Soma da Comissão</td></tr>';
      echo '</thead>';
      echo '<tbody>';

      $SqlBuscaBairros = "SELECT DISTINCT bairro FROM ipi_pedidos p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND tipo_entrega = 'ENTREGA' AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND cod_entregadores = '".$objBuscaEntregas->cod_entregadores."'";
      if ($cod_pizzarias > 0) 
        $SqlBuscaBairros .= " AND p.cod_pizzarias = ".$cod_pizzarias;
      
      $resBuscaBairros = mysql_query($SqlBuscaBairros);
      
      $total_pedidos = 0;
      $total_comissao = 0;
      
      while($objBuscaBairros = mysql_fetch_object($resBuscaBairros)) 
      {

        $SqlBuscaQuantidadeBairros = "SELECT COUNT(*) AS quantidade,sum(valor_comissao_frete) as total_comissao FROM ipi_pedidos p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND tipo_entrega = 'ENTREGA' AND REPLACE(bairro, \"'\",\"\") = '".str_replace("'", "",$objBuscaBairros->bairro)."' AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.situacao  NOT IN ('CANCELADO') AND cod_entregadores = '".$objBuscaEntregas->cod_entregadores."' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";

        if ($cod_pizzarias > 0) 
          $SqlBuscaQuantidadeBairros .= " AND p.cod_pizzarias = ".$cod_pizzarias;
        
        $objBuscaQuantidadeBairros = executaBuscaSimples($SqlBuscaQuantidadeBairros, $con);
        $quantidade = ($objBuscaQuantidadeBairros->quantidade > 0) ? $objBuscaQuantidadeBairros->quantidade : 0; 
        $comissao = ($objBuscaQuantidadeBairros->total_comissao > 0) ? $objBuscaQuantidadeBairros->total_comissao : 0; 
        $total_pedidos += $quantidade;
        $total_comissao += $comissao;
              
        echo '<tr>';
        echo '<td align="left">'.bd2texto($objBuscaBairros->bairro).'</td>';
        echo '<td align="center">'.$quantidade.'</td>';
        echo '<td align="center">'.bd2moeda($comissao).'</td>';
        echo '</tr>';
      }
      
      echo '<tr><td align="center">Total:</td><td align="right">' . $total_pedidos . '</td><td align="right">' . bd2moeda($total_comissao) . '</td></tr>';
      
      echo '</tbody>';
      echo '</table>';
      
      echo '<br><br>';
      
      echo '<table cellpadding="0" cellspacing="0">';
      echo '<thead>';
      echo '<tr><td align="center" colspan="4">Entregas Avulsas</td></tr>';
      echo '<tr><td align="center">Pedido</td><td align="center">Bairro</td><td align="center">Obs</td><td align="center">Valor</td></tr>';
      echo '</thead>';

      $sql_buscar_entregas_avulsas = "SELECT * FROM ipi_entregas_avulsas WHERE cod_entregadores = '" . $objBuscaEntregas->cod_entregadores . "' AND date(data_hora_entrega) BETWEEN '" . $data_inicial . "' AND '" . $data_final . "' AND cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND tipo_entrega = 'ENTREGA'";
      
      if ($cod_pizzarias > 0) 
        $sql_buscar_entregas_avulsas .= " AND cod_pizzarias = ".$cod_pizzarias;
      $sql_buscar_entregas_avulsas .= " ORDER BY cod_entregas_avulsas";
      $res_buscar_entregas_avulsas = mysql_query($sql_buscar_entregas_avulsas);
      
      $total_avulso = 0;
      $total_valor = 0;

      while($obj_buscar_entregas_avulsas = mysql_fetch_object($res_buscar_entregas_avulsas))
      {
        $valor = ($obj_buscar_entregas_avulsas->valor > 0) ? $obj_buscar_entregas_avulsas->valor : 0; 
        $total_valor += $valor;
        echo '<tr>';
        echo '<td align="left">' . sprintf('%08d', $obj_buscar_entregas_avulsas->cod_pedidos) . '</td>';
        echo '<td align="left">' . bd2texto($obj_buscar_entregas_avulsas->bairro) . '</td>';
        echo '<td align="center">' . bd2texto($obj_buscar_entregas_avulsas->obs_entrega_avulsa) . '</td>';
        echo '<td align="center">'.bd2moeda($valor).'</td>';
        echo '</tr>';
          
          $total_avulso++;
      }
      
      echo '<tr><td align="center" colspan="3">Total: ' . $total_avulso . '</td><td align="center">'.bd2moeda($total_valor).'</td></tr>';
      
      echo '</tbody>';
      echo '</table>';
      echo "<br/></br/>";
      echo '<table cellpadding="0" cellspacing="0">';
      echo '<thead>';
      echo '<tr><td align="center" colspan="4">Diárias</td></tr>';
      echo '<tr><td align="center">Dia</td><td align="center">Obs</td><td align="center">Valor</td></tr>';
      echo '</thead>';

      $sql_buscar_entregas_avulsas = "SELECT * FROM ipi_entregas_avulsas WHERE cod_entregadores = '" . $objBuscaEntregas->cod_entregadores . "' AND date(data_hora_entrega) BETWEEN '" . $data_inicial . "' AND '" . $data_final . "' AND cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND tipo_entrega = 'DIARIA'";
      
      if ($cod_pizzarias > 0) 
        $sql_buscar_entregas_avulsas .= " AND cod_pizzarias = ".$cod_pizzarias;
      $sql_buscar_entregas_avulsas .= " ORDER BY cod_entregas_avulsas";
      $res_buscar_entregas_avulsas = mysql_query($sql_buscar_entregas_avulsas);
      
      $quant_diaria = 0;
      $total_diaria = 0;

      while($obj_buscar_entregas_avulsas = mysql_fetch_object($res_buscar_entregas_avulsas))
      {
        $valor = ($obj_buscar_entregas_avulsas->valor > 0) ? $obj_buscar_entregas_avulsas->valor : 0; 
        $total_diaria += $valor;
        echo '<tr>';
        echo '<td align="left">' . date("d/m/Y",strtotime($obj_buscar_entregas_avulsas->data_hora_entrega)) . '</td>';
        echo '<td align="center">' . bd2texto($obj_buscar_entregas_avulsas->obs_entrega_avulsa) . '</td>';
        echo '<td align="center">'.bd2moeda($valor).'</td>';
        echo '</tr>';
          
          $quant_diaria++;
      }
      
      echo '<tr><td align="center" colspan="2">Total: ' . $quant_diaria . '</td><td align="center">'.bd2moeda($total_diaria).'</td></tr>';
      
      echo '</tbody>';
      echo '</table>';
       echo "<br/></br/>";

       echo '<table  cellpadding="0" cellspacing="0">';
      echo '<thead>';
      echo '<tr><td align="center">Total Geral</td><td align="center">'.bd2moeda(($total_valor+$total_comissao+$total_diaria)).'</td></tr>';
      echo '</thead>';
      echo "</table>";      
      echo '</td>';
      echo '</tr>';
      
    }

    
    ?>
    
    </tbody>
  </table>
  <br/>
  <?php

       desconectabd($con);
  ?>

  <form method="post" name="frmImpressao" action="ipi_rel_entregadores_impressao.php" target="_blank">
  <input type="hidden" name="cod_pizzarias" value="<? echo $cod_pizzarias; ?>">
  <input type="hidden" name="data_inicial" value="<? echo $data_inicial; ?>">
  <input type="hidden" name="data_final" value="<? echo $data_final; ?>">
  <input type="submit" name="bt_imprimir" value="Impressão">
</form>

  <?php

       break;
     
     default:
       # code...
       break;
   } 
 ?>

 <? rodape(); ?>
