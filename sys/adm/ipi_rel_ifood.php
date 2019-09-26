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
header("Content-Type: text/html; charset=utf-8",true);
cabecalho('IFood Vendas - BAIXADOS');

function Utf8_ansi($valor='') {
  $Utf8_ansi2 = array(
    "u00c0" =>"À",
    "u00c1" =>"Á",
    "u00c2" =>"Â",
    "u00c3" =>"Ã",
    "u00c4" =>"Ä",
    "u00c5" =>"Å",
    "u00c6" =>"Æ",
    "u00c7" =>"Ç",
    "u00c8" =>"È",
    "u00c9" =>"É",
    "u00ca" =>"Ê",
    "u00cb" =>"Ë",
    "u00cc" =>"Ì",
    "u00cd" =>"Í",
    "u00ce" =>"Î",
    "u00cf" =>"Ï",
    "u00d1" =>"Ñ",
    "u00d2" =>"Ò",
    "u00d3" =>"Ó",
    "u00d4" =>"Ô",
    "u00d5" =>"Õ",
    "u00d6" =>"Ö",
    "u00d8" =>"Ø",
    "u00d9" =>"Ù",
    "u00da" =>"Ú",
    "u00db" =>"Û",
    "u00dc" =>"Ü",
    "u00dd" =>"Ý",
    "u00df" =>"ß",
    "u00e0" =>"à",
    "u00e1" =>"á",
    "u00e2" =>"â",
    "u00e3" =>"ã",
    "u00e4" =>"ä",
    "u00e5" =>"å",
    "u00e6" =>"æ",
    "u00e7" =>"ç",
    "u00e8" =>"è",
    "u00e9" =>"é",
    "u00ea" =>"ê",
    "u00eb" =>"ë",
    "u00ec" =>"ì",
    "u00ed" =>"í",
    "u00ee" =>"î",
    "u00ef" =>"ï",
    "u00f0" =>"ð",
    "u00f1" =>"ñ",
    "u00f2" =>"ò",
    "u00f3" =>"ó",
    "u00f4" =>"ô",
    "u00f5" =>"õ",
    "u00f6" =>"ö",
    "u00f8" =>"ø",
    "u00f9" =>"ù",
    "u00fa" =>"ú",
    "u00fb" =>"û",
    "u00fc" =>"ü",
    "u00fd" =>"ý",
    "u00ff" =>"ÿ",
    "u00bd"=>"½",
    "u2022u2022u2022u2022 "=>"******"
  );
  return strtr($valor, $Utf8_ansi2);      
}
?>
<meta charset="utf-8" />
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />

<script type="text/javascript" src="../lib/js/calendario.js"></script>
<script>
window.addEvent('domready', function() {
    new vlaDatePicker('data_inicial', {
        prefillDate: false
    });
    new vlaDatePicker('data_final', {
        prefillDate: false
    });
});
</script>

<form name="frmFiltro" method="post">

    <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
        <tr>
            <td width="150">
                <select name="cod_pizzarias" style="width: 150px;">
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
                <input type="text" name="data_inicial" id="data_inicial" size="8"
                    value="<? echo (validaVarPost('data_inicial') != '') ? validaVarPost('data_inicial') : date('d/m/Y') ?>">
            </td>
            <td width="135">
                <label for="data_final">Data Final:</label>
                <input type="text" name="data_final" id="data_final" size="8"
                    value="<? echo (validaVarPost('data_final') != '') ? validaVarPost('data_final') : date('d/m/Y') ?>">
            </td>
            <td width="40">
                <input class="botaoAzul" type="submit" value="Filtrar">
            </td>
        </tr>
    </table>

    <br><br>

    <? 
  $con = conectabd(); 
  $data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
  $data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
  $cod_pizzarias = validaVarPost('cod_pizzarias')?validaVarPost('cod_pizzarias'):$_SESSION['usuario']['cod_pizzarias'];
  if(is_array($cod_pizzarias)){
    $cod_pizzarias = implode(',',$cod_pizzarias);
  }
  $sql = "SELECT cod_pedidos,pedido_ifood_json,valor_total FROM ipi_pedidos WHERE cod_pizzarias IN (".$cod_pizzarias.") AND pedido_ifood_json !='' AND situacao='BAIXADO' AND (data_hora_pedido BETWEEN '".$data_inicial." 00:00:00' AND '".$data_final." 23:59:59')";
  $sql = mysql_query($sql);
  $conta = 0;
  $total_pedido = 0; 
  $taxaEntregaTotal = 0;
  while ($dados = mysql_fetch_object($sql)) {
    $json = str_replace(array("\r", "\n"), '', $json);
    $json = json_decode($dados->pedido_ifood_json,true);
    $json = $json['order'];
    $taxaEntregaTotal +=$json['deliveryFee'];
    foreach ($json['items'] as $key => $value) {
      $value['name'] = Utf8_ansi($value['name']);
      #$produtos[$value['name']]['valor'] += $value['price'];
      $produtos[$value['name']]['valor'] += $value['totalPrice'];
      $produtos[$value['name']]['quantidade'] += $value['quantity'];
      
      #TODOS SUB PRODUTOS
      if(isset($value['subItems'])){
        foreach ($value['subItems'] as $k => $v) {
          $v['name'] = Utf8_ansi($v['name']);
          $subprodutos[$v['name']]['valor'] += $v['price']; 
          $subprodutos[$v['name']]['quantidade'] += $v['quantity']; 
        }
      }
      
    }
    //$produtos[$value['name']]['valor'] += $json['deliveryFee'];
    foreach ($json['payments'] as $key => $value) {
        $value['name'] = Utf8_ansi($value['name']);
        $value['name'] = explode('**', $value['name']);
        $value['name'] = $value['name'][0];
        if($value['prepaid']){
          $value['prepaid'] = 'prepago';
        }else{
          $value['prepaid'] = 'pospago';
        }
        $payments[$value['prepaid']][$value['name']] += $value['value'];
    }
  }

?>
    <br />
    <?php 
?>

    <h2>Produtos Vendidos do Ifood</h2>
    <p>Soma dos itens e sub-itens de cada produto</p>
    <hr />
    <table id='produtos' class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
        <tbody>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Valor Total</th>
            </tr>
            <?php
    $totalPedidos = 0;
    foreach ($produtos as $key => $value) {
      $totalPedidos += $value['valor'];
      ?>
            <tr>
                <td align="center"><?php echo $key; ?></td>
                <td align="center"><?php echo $value['quantidade']; ?></td>
                <td align="center"><span>R$</span><span><?php echo $value['valor']; ?></span></td>
            </tr>
            <?php
    }
    ?>
            <tr>
                <td align="center">Total</td>
                <td align="center"></td>
                <td align="center">R$<?php echo $totalPedidos; ?></td>
            </tr>
        </tbody>
    </table>
    <br />
    <h2>Sub-Produtos Vendidos do Ifood</h2>
    <p>Soma dos itens e sub-itens de cada produto. *Os valores dessa parte não precisam ser considerados, pois, o iFood leva em consideração o valor do item principal.</p>
    <hr />
    <table id='subProdutos' class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
        <tbody>
            <tr>
                <th>Sub-produto</th>
                <th>Quantidade</th>
                <th>Valor Total</th>
            </tr>
            <?php
    $totalPedidos = 0;
    foreach ($subprodutos as $key => $value) {
      $totalPedidos += $value['valor'];
      ?>
            <tr>
                <td align="center"><?php echo $key; ?></td>
                <td align="center"><?php echo $value['quantidade']; ?></td>
                <td align="center"><span>R$</span><span><?php echo $value['valor']; ?></span></td>
            </tr>
            <?php
    }
    ?>
            <tr>
                <td align="center">Total</td>
                <td align="center"></td>
                <td align="center"><span>R$</span><?php echo $totalPedidos; ?></td>
            </tr>
        </tbody>
    </table>
    <h2>Total de Frete</h2>
    <hr />
    <table class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
        <tbody>
            <tr>
                <td align="center">Frete</td>
                <td align="center"><span>R$</span><?php echo $taxaEntregaTotal; ?></td>
            </tr>
        </tbody>
    </table>
    <br />
    <h2>Pagamentos Pós-pago do Ifood</h2>
    <hr />
    <table class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
        <tbody>
            <tr>
                <th>Método</th>
                <th>Valor Total</th>
            </tr>
            <?php
    $totalPos = 0;
    foreach ($payments['pospago'] as $key => $value) {
      $totalPos += $value;
      ?>
            <tr>
                <td align="center"><?php echo $key; ?></td>
                <td align="center">R$<?php echo $value; ?></td>
            </tr>
            <?php
    }
    ?>
            <tr>
                <td align="center">Total</td>
                <td align="center">R$<?php echo $totalPos; ?></td>
            </tr>
        </tbody>
    </table>
    <br />
    <h2>Pagamentos Pré-pago do Ifood</h2>
    <hr />
    <table class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
        <tbody>
            <tr>
                <th>Método</th>
                <th>Valor Total</th>
            </tr>
            <?php
    $totalPre = 0;
    foreach ($payments['prepago'] as $key => $value) {
      $totalPre +=$value;
      ?>
            <tr>
                <td align="center"><?php echo $key; ?></td>
                <td align="center">R$<?php echo $value; ?></td>
            </tr>
            <?php
    }
    ?>
            <tr>
                <td align="center">Total</td>
                <td align="center">R$<?php echo $totalPre; ?></td>
            </tr>
        </tbody>
    </table>
    <br />
    <h2>Pré-pago + Pós-pago </h2>
    <hr />
    <table class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
        <tbody>
            <tr>
                <td align="center">Total Pré-pago e Pós-Pago</td>
                <td align="center">R$<?php echo $totalPre+$totalPos; ?></td>
            </tr>
            <tr>
                <td align="center">Diferença dos produtos e valores pagos</td>
                <td align="center">R$<?php echo number_format(($totalPre+$totalPos)-$totalPedidos,2); ?></td>
            </tr>
        </tbody>
    </table>
    <?php
desconectabd($con);
?>

    <br><br>

</form>

<? rodape(); ?>