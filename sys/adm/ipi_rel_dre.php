<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório DRE');
?>


<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script type="text/javascript" src="../lib/js/calendario.js"></script>
<script>
window.addEvent('domready', function() { 
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 
</script>




<form name="frmFiltro" method="post">

 <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA); ?>:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbr tdbt">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as <? echo ucfirst(TIPO_EMPRESA); ?>s</option>
        <?
        $con = conectabd();
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY p.nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
        {
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
    <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo $data_inicial ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
    <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo $data_final ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>

  <tr><td align="center" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>

  </table>

</form>

<br /><br />





<table class="listaEdicao" cellpadding="0" cellspacing="0" width="<? echo LARGURA_PADRAO ?>">
    <thead>
        <tr>
            <td align="center">Conta</td>
            <td align="center" width="100">Valor (R$)</td>
        </tr>
    </thead>
    <tbody>
  
    <?

    $data_inicial_filtro = validaVarPost('data_inicial');
    $data_final_filtro = validaVarPost('data_final');
    $cod_pizzarias = validaVarPost('cod_pizzarias');

    function imprimir_plano_contas($cod_plano_contas, $espaco, $data_inicial_filtro, $data_final_filtro, $cod_pizzarias)
    {
        $sql_buscar_plano_contas = "SELECT * FROM ipi_plano_contas WHERE cod_plano_contas_pai = '$cod_plano_contas' ORDER BY conta_indice";
        $res_buscar_plano_contas = mysql_query($sql_buscar_plano_contas);
        $num_buscar_plano_contas = mysql_num_rows($res_buscar_plano_contas);

        if(($num_buscar_plano_contas > 0) && ($cod_plano_contas > 0))
        {
            $espaco += 25;
        }

        while ($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_plano_contas))
        {

            $sql_total_conta = "SELECT sum(valor_total) valor_total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos=t.cod_titulos) INNER JOIN ipi_titulos_subcategorias ts ON (t.cod_titulos_subcategorias = ts.cod_titulos_subcategorias) WHERE tp.data_pagamento >= '".data2bd($data_inicial_filtro)."' AND tp.data_pagamento <= '".data2bd($data_final_filtro)."'";
            if ($cod_pizzarias)
                $sql_total_conta .= " AND t.cod_pizzarias = ".$cod_pizzarias;
            $sql_total_conta .= " AND ts.cod_titulos_subcategorias IN (SELECT cod_titulos_subcategorias FROM ipi_titulos_subcategorias_plano_contas WHERE cod_plano_contas = ".$obj_buscar_plano_contas->cod_plano_contas.")";
            $res_total_conta = mysql_query($sql_total_conta);
            $obj_total_conta = mysql_fetch_object($res_total_conta);
            //echo "<br />".$sql_total_conta;

            echo '<tr>';
            echo '<td align="left" style="padding-left: ' . $espaco . 'px;">' . bd2texto($obj_buscar_plano_contas->conta_indice) . ' ' . bd2texto($obj_buscar_plano_contas->conta_nome) . '</td>';
            echo '<td align="center">'.bd2moeda(abs($obj_total_conta->valor_total)).'</td>';
            echo '</tr>';

            imprimir_plano_contas($obj_buscar_plano_contas->cod_plano_contas, $espaco, $data_inicial_filtro, $data_final_filtro, $cod_pizzarias);
        }
    }
    
    $conexao = conectabd();
    imprimir_plano_contas(0, 3, $data_inicial_filtro, $data_final_filtro, $cod_pizzarias);


    $sql_buscar_plano_contas = "SELECT * FROM ipi_plano_contas WHERE tipo_conta='ENTRADA' ORDER BY conta_indice";
    $res_buscar_plano_contas = mysql_query($sql_buscar_plano_contas);
    $num_buscar_plano_contas = mysql_num_rows($res_buscar_plano_contas);
    $total_entradas = 0;
    while ($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_plano_contas))
    {
        $sql_total_conta = "SELECT sum(valor_total) valor_total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos=t.cod_titulos) INNER JOIN ipi_titulos_subcategorias ts ON (t.cod_titulos_subcategorias = ts.cod_titulos_subcategorias) WHERE tp.data_pagamento >= '".data2bd($data_inicial_filtro)."' AND tp.data_pagamento <= '".data2bd($data_final_filtro)."'";
        if ($cod_pizzarias)
            $sql_total_conta .= " AND t.cod_pizzarias = ".$cod_pizzarias;
        $sql_total_conta .= " AND ts.cod_titulos_subcategorias IN (SELECT cod_titulos_subcategorias FROM ipi_titulos_subcategorias_plano_contas WHERE cod_plano_contas = ".$obj_buscar_plano_contas->cod_plano_contas.")";
        //echo "<br />".$sql_total_conta;
        $res_total_conta = mysql_query($sql_total_conta);
        $obj_total_conta = mysql_fetch_object($res_total_conta);
        $total_entradas += $obj_total_conta->valor_total;
    }


    $sql_buscar_plano_contas = "SELECT * FROM ipi_plano_contas WHERE tipo_conta='SAIDA' ORDER BY conta_indice";
    $res_buscar_plano_contas = mysql_query($sql_buscar_plano_contas);
    $num_buscar_plano_contas = mysql_num_rows($res_buscar_plano_contas);
    $total_saidas = 0;
    while ($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_plano_contas))
    {
        $sql_total_conta = "SELECT sum(valor_total) valor_total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos=t.cod_titulos) INNER JOIN ipi_titulos_subcategorias ts ON (t.cod_titulos_subcategorias = ts.cod_titulos_subcategorias) WHERE tp.data_pagamento >= '".data2bd($data_inicial_filtro)."' AND tp.data_pagamento <= '".data2bd($data_final_filtro)."'";
        if ($cod_pizzarias)
            $sql_total_conta .= " AND t.cod_pizzarias = ".$cod_pizzarias;
        $sql_total_conta .= " AND ts.cod_titulos_subcategorias IN (SELECT cod_titulos_subcategorias FROM ipi_titulos_subcategorias_plano_contas WHERE cod_plano_contas = ".$obj_buscar_plano_contas->cod_plano_contas.")";
        //echo "<br />".$sql_total_conta;
        $res_total_conta = mysql_query($sql_total_conta);
        $obj_total_conta = mysql_fetch_object($res_total_conta);
        $total_saidas += $obj_total_conta->valor_total;
    }
    

    desconectabd($conexao);
    
    ?>
  
    </tbody>
</table>

<?
echo "<br />Receita - Despesas = Resultado";
echo "<br />R$ ".bd2moeda($total_entradas)." - R$ ".bd2moeda($total_saidas)." = ".bd2moeda($total_entradas+$total_saidas);
?>

<? rodape(); ?>
