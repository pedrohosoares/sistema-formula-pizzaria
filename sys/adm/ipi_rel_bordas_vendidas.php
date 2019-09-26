<?php

/**
 * ipi_rel_sabores_vendidos.php: Sabores Mais Vendidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Bordas Mais Vendidas');

$acao = validaVarPost('acao');

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script language="javascript" src="../lib/js/calendario.js"></script>

<script>

window.addEvent('domready', function() { 
  // DatePick
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 

</script>

<?
$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 
$pedido = (validaVarPost('pedido', '/[0-9]+/')) ? (int) validaVarPost('pedido', '/[0-9]+/') : '';
$cliente = (validaVarPost('cliente')) ? validaVarPost('cliente') : '';
$data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('01/m/Y'));
$data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('t/m/Y'));
$cod_pizzarias = validaVarPost('cod_pizzarias');
$cod_tamanhos = validaVarPost('cod_tamanhos');
$situacao = validaVarPost('situacao');
$origem = validaVarPost('origem');
$promocional = validaVarPost('promocional');
$hora_final = validaVarPost('hora_final');
$hora_inicial = validaVarPost('hora_inicial');
?>

<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <!-- 
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="pedido">Código do Pedido:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbt tdbr"><input class="requerido" type="text" name="pedido" id="pedido" size="60" value="<? echo $pedido ?>" onkeypress="return ApenasNumero(event)"></td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="cliente">Cliente:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr"><input class="requerido" type="text" name="cliente" id="cliente" size="60" value="<? echo $cliente ?>"></td>
  </tr>
   -->
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="data_inicial">Data Inicial:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbr tdbt"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo $data_inicial ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
     <input type="text" name="hora_inicial" id="hora_inicial" size="3" value="<? echo $hora_inicial ?>" onkeypress="return MascaraHora(this, event)">
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
    <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo $data_final ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    <input type="text" name="hora_final" id="hora_final" size="3" value="<? echo $hora_final ?>" onkeypress="return MascaraHora(this, event)">
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="cod_pizzarias">Pizzaria:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as Pizzarias</option>
        <?
        $con = conectabd();
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).")  ORDER BY nome";
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
    <td class="legenda tdbl" align="right"><label for="cod_pizzarias">Tamanho:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_tamanhos" id="cod_tamanhos">
        <option value="">Todos os Tamanhos</option>
        <?
        $sql_busca_tamanhos = "SELECT * FROM ipi_tamanhos";
        $res_busca_tamanhos = mysql_query($sql_busca_tamanhos);
        while($obj_busca_tamanhos = mysql_fetch_object($res_busca_tamanhos)) 
        {
            echo '<option value="'.$obj_busca_tamanhos->cod_tamanhos.'" ';
            if($obj_busca_tamanhos->cod_tamanhos == $cod_tamanhos)
                echo 'selected';
            echo '>'.bd2texto($obj_busca_tamanhos->tamanho).'</option>';
        }
        desconectabd($con);
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="situacao">Situação:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr">
    <?
    if (!$situacao)
        $situacao = "BAIXADO";
    ?>
      <select name="situacao" id=situacao>
        <option value="TODOS" <? if($situacao == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="CANCELADO" <? if($situacao == 'CANCELADO') echo 'selected' ?>>Cancelado</option>
        <option value="BAIXADO" <? if($situacao == 'BAIXADO') echo 'selected' ?>>Baixado</option>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="origem">Origem:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="origem" id="origem">
        <option value="TODOS" <? if($origem == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="NET" <? if($origem == 'NET') echo 'selected' ?>>Net</option>
        <option value="TEL" <? if($origem == 'TEL') echo 'selected' ?>>Tel</option>
      </select>
    </td>
  </tr>
  
<!--   <tr>
    <td class="legenda tdbl sep" align="right"><label for="promocional">Promocional:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="promocional" id="promocional">
        <option value="TODOS" <? if($promocional == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="Sim" <? if($promocional == 'Sim') echo 'selected' ?>>Sim</option>
        <option value="Não" <? if($promocional == 'Não') echo 'selected' ?>>Não</option>
      </select>
    </td>
  </tr> -->

  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
</form>

<br>

<?
$con = conectabd();

switch ($acao) 
{
  case 'buscar':
    
    // $SqlBuscaRegistros =  "SELECT pb.cod_bordas, b.borda, COUNT(pb.cod_bordas) AS quantidade FROM ipi_pedidos_bordas pb INNER JOIN ipi_pedidos_pizzas pp ON (pp.cod_pedidos_pizzas = pb.cod_pedidos_pizzas) INNER JOIN ipi_bordas b ON (b.cod_bordas = pb.cod_bordas) INNER JOIN ipi_pedidos p ON (p.cod_pedidos = pb.cod_pedidos) WHERE p.cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).")  ";

$SqlBuscaRegistros = "SELECT pb.cod_bordas, b.borda, COUNT(pb.cod_bordas) AS quantidade FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos)  INNER JOIN ipi_pedidos_pizzas pp ON (pp.cod_pedidos_pizzas = pb.cod_pedidos_pizzas) INNER JOIN ipi_bordas b ON (pb.cod_bordas = b.cod_bordas) WHERE p.cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).")  ";
    

    if($origem != 'TODOS')
    {
      $SqlBuscaRegistros .= " AND p.origem_pedido = '$origem'";
      $sql_origem = " AND  p.origem_pedido = '$origem'";
    }
    else
    {
      $sql_origem = "";
    }
        
    if($origem == 'NET')
    {
      $SqlBuscaRegistros .= " AND p.origem_pedido IN ('NET','IFOOD')";
      $sql_origem = " AND  p.origem_pedido IN ('NET','IFOOD')";
    }

    if($cod_pizzarias)
    {
        $SqlBuscaRegistros .= " AND p.cod_pizzarias = '$cod_pizzarias'";
        $sql_cod_pizzarias = "AND p.cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") AND p.cod_pizzarias = '$cod_pizzarias'";
    }
    else
    {
      $sql_cod_pizzarias = " AND p.cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).")  ";
    }
        
        
    if($cod_tamanhos)
    {
      $SqlBuscaRegistros .= " AND pp.cod_tamanhos = '$cod_tamanhos'";
    }

        
    if(($data_inicial) && ($data_final)) 
    {
        $data_inicial_sql = data2bd($data_inicial); 
        $data_final_sql = data2bd($data_final);

        if(validar_hora($hora_inicial))
        {
          $data_inicial_sql .= ' '.$hora_inicial.':00'; 
        }
        else
        {
          $data_inicial_sql .= ' 00:00:00'; 
        }

        if(validar_hora($hora_final))
        {
          $data_final_sql .= ' '.$hora_final.':59'; 
        }
        else
        {
          $data_final_sql .= ' 23:59:59'; 
        }

        $SqlBuscaRegistros .= " AND p.data_hora_pedido >= '$data_inicial_sql' AND p.data_hora_pedido <= '$data_final_sql'";
    }

    // if($promocional == 'Sim')
    //   $SqlBuscaRegistros .= " AND (pb.fidelidade = 1 OR pb.promocional = 1)";
    // else if($promocional == 'Não')
    //   $SqlBuscaRegistros .= " AND pb.fidelidade = 0 AND pb.promocional = 0";

    if($situacao != 'TODOS')
    {
      $SqlBuscaRegistros .= " AND p.situacao = '$situacao'";
      $sql_situacao = "AND p.situacao = '$situacao'";
    }
    else
    {
      $sql_situacao = "";
    }
      


    $SqlBuscaRegistros .= " GROUP BY b.borda ORDER BY borda";
    // echo "<br><br>" . $SqlBuscaRegistros;
    $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
    $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

    ?>

    <br>

    <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center">Borda</td>
        <td align="center">Vendidas</td>
        <td align="center">Promocional</td>  
        <td align="center">Fidelidade</td>              
        <td align="center">Total</td>
      </tr>
    </thead>
    <tbody>

    <?
    $total_geral = 0;
    $total_promocional = 0;
    $total_fidelidade = 0;
    while($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) 
    {
        $sql_borda_promocional = " SELECT COUNT(*) AS total_promocional FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 1 AND pb.fidelidade = 0 AND p.data_hora_pedido BETWEEN '$data_inicial_sql' AND '$data_final_sql' AND cod_bordas = $objBuscaRegistros->cod_bordas $sql_cod_pizzarias $sql_situacao $sql_origem";
        $res_borda_promocional = mysql_query($sql_borda_promocional);
        $obj_borda_promocional = mysql_fetch_object($res_borda_promocional);
        // echo "<br><br>" . $sql_borda_promocional;

        $sql_borda_fidelidade = " SELECT COUNT(*) AS total_fidelidade FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) WHERE pb.promocional = 0 AND pb.fidelidade = 1 AND p.data_hora_pedido BETWEEN '$data_inicial_sql' AND '$data_final_sql' AND cod_bordas = $objBuscaRegistros->cod_bordas $sql_cod_pizzarias $sql_situacao $sql_origem";
        // echo "<br><br>" . $sql_borda_fidelidade;
        $res_borda_fidelidade = mysql_query($sql_borda_fidelidade);
        $obj_borda_fidelidade = mysql_fetch_object($res_borda_fidelidade);

        echo '<tr>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->borda).'</td>';
        echo '<td align="center">'.($objBuscaRegistros->quantidade - ($obj_borda_promocional->total_promocional + $obj_borda_fidelidade->total_fidelidade)).'</td>';
        echo '<td align="center">'.$obj_borda_promocional->total_promocional.'</td>';
        echo '<td align="center">'.$obj_borda_fidelidade->total_fidelidade.'</td>';               
        echo '<td align="center">'.$objBuscaRegistros->quantidade.'</td>';
        echo '</tr>';
        $total_promocional += $obj_borda_promocional->total_promocional;
        $total_fidelidade += $obj_borda_fidelidade->total_fidelidade;
        $total_geral += $objBuscaRegistros->quantidade;
    }
    desconectabd($con);
    ?>

      <tr>

        <td align="right" ><b>Total</b></td>
        <td align="center"><b><? echo $total_geral - ($total_fidelidade + $total_promocional); ?></b></td>
        <td align="center"><b><? echo $total_promocional; ?></b></td>
        <td align="center"><b><? echo $total_fidelidade; ?></b></td>        
        <td align="center"><b><? echo $total_geral; ?></b></td>
      </tr>

    </tbody>
    </table>

<?php
    break;
  
  default:
    
    break;
}

 rodape(); ?>