<?php

/**
 * Cadastro de Titulos Categorias.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       22/01/2010   Elias         Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório Comparativo de Gastos');

$acao = validaVarPost('acao');

?>


<?
//$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0;
//$opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : $campo_filtro_padrao;
//$filtro = validaVarPost('filtro');

$detalhado = validaVarPost('detalhado');
$situacao_parcelas = validaVarPost('situacao_parcelas');
$tipo_titulo = validaVarPost('tipo_titulo');
$cod_pizzarias = validaVarPost('cod_pizzarias');
$data_inicial_filtro = (validaVarPost('data_inicial_filtro')) ? validaVarPost('data_inicial_filtro') : date("01/m/Y");
$data_final_filtro = (validaVarPost('data_final_filtro')) ? validaVarPost('data_final_filtro') : date("t/m/Y", mktime(0, 0, 0, date('m'), 1, date('Y')));

$tipo_data_filtro = validaVarPost('tipo_data_filtro');
$nf_filtro = validaVarPost('nf_filtro');


function exibir_mes_entenso($mes_numerico)
{
    $mes_numerico = (int)$mes_numerico;
    $arr_meses = array("1" => "Janeiro", "2" => "Fevereiro", "3" => "Março", "4" => "Abril", "5" => "Maio", "6" => "Junho", "7" => "Julho", "8" => "Agosto", "9" => "Setembro", "10" => "Outubro", "11" => "Novembro", "12" => "Dezembro");
    echo $arr_meses[$mes_numerico];
}
?>

<form name="frmFiltro" method="post">
<table align="center" class="caixa" cellpadding="0" cellspacing="0">
    <!--  
    <tr>
        <td class="legenda tdbl tdbt" align="right"><label for="data_inicial_filtro">Data Inicial:</label></td>
        <td class="tdbt">&nbsp;</td>
        <td class="tdbr tdbt">
            <input class="requerido" type="text" name="data_inicial_filtro" id="data_inicial_filtro" size="10" value="<? echo $data_inicial_filtro ?>" onkeypress="return MascaraData(this, event)">
            &nbsp;<a href="javascript:;" id="botao_data_inicial_filtro"><img src="../lib/img/principal/botao-data.gif"></a>
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl sep" align="right"><label for="data_final_filtro">Data Final:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
            <input class="requerido" type="text" name="data_final_filtro" id="data_final_filtro" size="10" value="<? echo $data_final_filtro ?>" onkeypress="return MascaraData(this, event)">
            &nbsp;<a href="javascript:;" id="botao_data_final_filtro"><img src="../lib/img/principal/botao-data.gif"></a>
        </td>
    </tr>
    -->
    <tr>
        <td class="legenda tdbl sep tdbt" align="right"><label for="data_final_filtro">Mês Atual:</label></td>
        <td class="sep tdbt">&nbsp;</td>
        <td class="tdbr sep tdbt">
            <?
            echo  exibir_mes_entenso(date("m"));
            ?>
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl sep" align="right"><label>Filtrar por:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
        <select name="tipo_data_filtro" style="width: 200px;">
            <option value="VENC" <? if ($tipo_data_filtro == 'VENC') echo 'selected'; ?>>Data Vencimento</option>
            <option value="REF" <? if ($tipo_data_filtro == 'REF') echo 'selected'; ?>>Mês Referência</option>
        </select>
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl sep" align="right"><label><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
        <select name="cod_pizzarias" style="width: 200px;">
            <option value="TODOS">Todas</option>
            
            <?

            $conexao = conectabd();
            
            $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";
            $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
            
            while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
            {
                echo '<option value="' . $obj_buscar_pizzarias->cod_pizzarias . '" ';
                
                if($cod_pizzarias == $obj_buscar_pizzarias->cod_pizzarias)
                {
                    echo 'selected';
                }
                
                echo '>' . bd2texto($obj_buscar_pizzarias->nome) . '</option>';
            }
            
            desconectabd($conexao);
            
            ?>
            
        </select>
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl sep" align="right"><label>Tipo de Título:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
        <select name="tipo_titulo" style="width: 200px;">
            <option value="PAGAR_RECEBER" <? if ($tipo_titulo == 'PAGAR_RECEBER') echo 'selected'; ?>>Pagar/Receber</option>
            <option value="PAGAR" <? if ($tipo_titulo == 'PAGAR') echo 'selected'; ?>>Pagar</option>
            <option value="RECEBER" <? if ($tipo_titulo == 'RECEBER') echo 'selected'; ?>>Receber</option>
        </select>
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl sep" align="right"><label>Situação:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
        <select name="situacao_parcelas" style="width: 200px;">
            <option value="PAGO_ABERTO" <? if ($situacao_parcelas == 'PAGO_ABERTO') echo 'selected'; ?>>Pago/Aberto</option>
            <option value="PAGO" <? if ($situacao_parcelas == 'PAGO') echo 'selected'; ?>>Pago</option>
            <option value="ABERTO" <? if ($situacao_parcelas == 'ABERTO') echo 'selected'; ?>>Aberto</option>
        </select>
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl sep" align="right"><label>NF:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
        <select name="nf_filtro" style="width: 200px;">
            <option value="TODOS" <? if ($nf_filtro == 'TODOS') echo 'selected'; ?>>Todos</option>
            <option value="NF" <? if ($nf_filtro == 'NF') echo 'selected'; ?>>NF</option>
            <option value="SEM_NF" <? if ($nf_filtro == 'SEM_NF') echo 'selected'; ?>>Sem NF</option>
        </select>
        </td>
    </tr>
<!--
    <tr>
        <td class="legenda tdbl" align="right">
        &nbsp;
        </td>
        <td class="tdbt"></td>
        <td class="tdbr">
        <input type="checkbox" name="detalhado" value="1" <? if ($detalhado) echo "checked='checked'"; ?>>&nbsp;<label>Exibir Detalhes</label>
        </td>
    </tr>
-->
    <tr>
        <td align="right" class="tdbl tdbb tdbr" colspan="3"><input
            class="botaoAzul" type="submit" value="Buscar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="buscar"></form>

<br>

<? if ($acao=="buscar"): ?>

<?

$conexao = conectabd();

?>

<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0" style="width: 600px; margin: 0px auto;" align="center">
    <thead>
        <tr>
            <td align="center">Categoria</td>
            <td align="center">
            <? echo  exibir_mes_entenso(date("m")-2); ?>
            </td>
            <td align="center">
            <? echo  exibir_mes_entenso(date("m")-1); ?>
            </td>
            <td align="center">
            <? echo  exibir_mes_entenso(date("m")); ?>
            </td>
            
            
        </tr>
    </thead>
    <tbody>
  
    <?

    $sql_categorias = "SELECT * FROM ipi_titulos_categorias tc";
    $res_categorias = mysql_query($sql_categorias);
    $num_categorias = mysql_num_rows($res_categorias);
    
    $total = 0;
    $total_1mes_antes = 0;
    $total_2mes_antes = 0;
    $total_nf = 0;
    $total_sem_nf = 0;
    


    $mes_atual = date("m");
    $ano_atual = date("Y");
    $data_inicial_filtro = date("Y-m-01");
    $data_final_filtro = date("Y-m-t");

    if ($mes_atual==0)
    {
      $mes_atual=12;
      $ano_atual--;
    }
    $mes_atual--;
    $data_inicial_filtro_1mes_antes = date("Y-m-d", strtotime($ano_atual."-".$mes_atual."-01"));
    $data_final_filtro_1mes_antes = date("Y-m-t", strtotime($data_inicial_filtro_1mes_antes));

    
    if ($mes_atual==0)
    {
      $mes_atual=12;
      $ano_atual--;
    }
    $mes_atual--;
    $data_inicial_filtro_2mes_antes = date("Y-m-d", strtotime($ano_atual."-".$mes_atual."-01"));
    $data_final_filtro_2mes_antes = date("Y-m-t", strtotime($data_inicial_filtro_2mes_antes));

    
    if ($mes_atual==0)
    {
      $mes_atual=12;
      $ano_atual--;
    }
    $mes_atual--;
    $data_inicial_filtro_3mes_antes = date("Y-m-d", strtotime($ano_atual."-".$mes_atual."-01"));
    $data_final_filtro_3mes_antes = date("Y-m-t", strtotime($data_inicial_filtro_3mes_antes));    
    
    
    
    while ($obj_categorias = mysql_fetch_object($res_categorias))
    {

      
        // INICIO SQL 2 MES ANTES
        $sql_total_cat_2mes_antes = "SELECT SUM(tp.valor) total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND t.tipo_titulo='PAGAR' AND t.cod_titulos_subcategorias IN ( SELECT cod_titulos_subcategorias FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = ".$obj_categorias->cod_titulos_categorias.")";

        if ($tipo_data_filtro == 'VENC')
        {
            $sql_total_cat_2mes_antes .= " AND tp.data_vencimento BETWEEN '" . ($data_inicial_filtro_2mes_antes) . "' AND '" . ($data_final_filtro_2mes_antes) . "'";
        }
        else if($tipo_data_filtro == 'REF')
        {
            $sql_total_cat_2mes_antes .= " AND tp.mes_ref BETWEEN MONTH('" . ($data_inicial_filtro_2mes_antes) . "') AND MONTH('" . ($data_final_filtro_2mes_antes) . "') AND tp.ano_ref BETWEEN YEAR('" . ($data_inicial_filtro_2mes_antes) . "') AND YEAR('" . ($data_final_filtro_2mes_antes) . "')";
        }
        
        if ($tipo_titulo != "PAGAR_RECEBER")
        {
            $sql_total_cat_2mes_antes .= " AND (t.tipo_titulo='" . $tipo_titulo . "')";
        }
        
        if($cod_pizzarias != 'TODOS')
        {
            $sql_total_cat_2mes_antes .= " AND (t.cod_pizzarias='" . $cod_pizzarias . "')";
        }
        
        if($situacao_parcelas != 'PAGO_ABERTO')
        {
            $sql_total_cat_2mes_antes .= " AND (tp.situacao='$situacao_parcelas')";
        }
        
        if($nf_filtro == 'NF')
        {
            $sql_total_cat_2mes_antes .= " AND (t.numero_nota_fiscal <> '' AND t.numero_nota_fiscal IS NOT NULL)";
        }
        else if($nf_filtro == 'SEM_NF')
        {
            $sql_total_cat_2mes_antes .= " AND (t.numero_nota_fiscal = '' AND t.numero_nota_fiscal IS NULL)";
        }
        // FIM SQL 2 MES ANTES
      //echo $sql_total_cat_2mes_antes . "<br>";
      
      
      
        // INICIO SQL 1 MES ANTES
        $sql_total_cat_1mes_antes = "SELECT SUM(tp.valor) total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND t.tipo_titulo='PAGAR' AND t.cod_titulos_subcategorias IN ( SELECT cod_titulos_subcategorias FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = ".$obj_categorias->cod_titulos_categorias.")";
      
        if ($tipo_data_filtro == 'VENC')
        {
            $sql_total_cat_1mes_antes .= " AND tp.data_vencimento BETWEEN '" . ($data_inicial_filtro_1mes_antes) . "' AND '" . ($data_final_filtro_1mes_antes) . "'";
        }
        else if($tipo_data_filtro == 'REF')
        {
            $sql_total_cat_1mes_antes .= " AND tp.mes_ref BETWEEN MONTH('" . ($data_inicial_filtro_1mes_antes) . "') AND MONTH('" . ($data_final_filtro_1mes_antes) . "') AND tp.ano_ref BETWEEN YEAR('" . ($data_inicial_filtro_1mes_antes) . "') AND YEAR('" . ($data_final_filtro_1mes_antes) . "')";
        }
        
        if ($tipo_titulo != "PAGAR_RECEBER")
        {
            $sql_total_cat_1mes_antes .= " AND (t.tipo_titulo='" . $tipo_titulo . "')";
        }
        
        if($cod_pizzarias != 'TODOS')
        {
            $sql_total_cat_1mes_antes .= " AND (t.cod_pizzarias='" . $cod_pizzarias . "')";
        }
        
        if($situacao_parcelas != 'PAGO_ABERTO')
        {
            $sql_total_cat_1mes_antes .= " AND (tp.situacao='$situacao_parcelas')";
        }
        
        if($nf_filtro == 'NF')
        {
            $sql_total_cat_1mes_antes .= " AND (t.numero_nota_fiscal <> '' AND t.numero_nota_fiscal IS NOT NULL)";
        }
        else if($nf_filtro == 'SEM_NF')
        {
            $sql_total_cat_1mes_antes .= " AND (t.numero_nota_fiscal = '' AND t.numero_nota_fiscal IS NULL)";
        }
        // FIM SQL 1 MES ANTES
      
      
      
        // INICIO SQL MES CORRENTE
        $sql_total_cat = "SELECT SUM(tp.valor) total, ($sql_total_cat_1mes_antes) total_1mes_antes, ($sql_total_cat_2mes_antes) total_2mes_antes FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND t.tipo_titulo='PAGAR' AND t.cod_titulos_subcategorias IN ( SELECT cod_titulos_subcategorias FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = ".$obj_categorias->cod_titulos_categorias.")";

      
        if ($tipo_data_filtro == 'VENC')
        {
            $sql_total_cat .= " AND tp.data_vencimento BETWEEN '" . ($data_inicial_filtro) . "' AND '" . ($data_final_filtro) . "'";
        }
        else if($tipo_data_filtro == 'REF')
        {
            $sql_total_cat .= " AND tp.mes_ref BETWEEN MONTH('" . ($data_inicial_filtro) . "') AND MONTH('" . ($data_final_filtro) . "') AND tp.ano_ref BETWEEN YEAR('" . ($data_inicial_filtro) . "') AND YEAR('" . ($data_final_filtro) . "')";
        }
        
        if ($tipo_titulo != "PAGAR_RECEBER")
        {
            $sql_total_cat .= " AND (t.tipo_titulo='" . $tipo_titulo . "')";
        }
        
        if($cod_pizzarias != 'TODOS')
        {
            $sql_total_cat .= " AND (t.cod_pizzarias='" . $cod_pizzarias . "')";
        }
        
        if($situacao_parcelas != 'PAGO_ABERTO')
        {
            $sql_total_cat .= " AND (tp.situacao='$situacao_parcelas')";
        }
        
        if($nf_filtro == 'NF')
        {
            $sql_total_cat .= " AND (t.numero_nota_fiscal <> '' AND t.numero_nota_fiscal IS NOT NULL)";
        }
        else if($nf_filtro == 'SEM_NF')
        {
            $sql_total_cat .= " AND (t.numero_nota_fiscal = '' AND t.numero_nota_fiscal IS NULL)";
        }
        // FIM SQL MES CORRENTE
        //echo $sql_total_cat . "<br><br>";
        $res_total_cat = mysql_query($sql_total_cat);
        $obj_total_cat = mysql_fetch_object($res_total_cat);
        
        echo '<tr bgcolor="#DEDEFF">';
        echo '<td><b><i>' . bd2texto($obj_categorias->titulos_categoria) . '</i></b></td>';
        $sinal = ($obj_categorias->titulos_categoria=="RECEBIVEIS" ? "+" : "-" );
        echo '<td align="right"><b><i>' .$sinal. bd2moeda(abs($obj_total_cat->total_2mes_antes)) . '</i></b></td>';
        echo '<td align="right"><b><i>' . $sinal.bd2moeda(abs($obj_total_cat->total_1mes_antes)) . '</i></b></td>';
        echo '<td align="right"><b><i>' . $sinal. bd2moeda(abs($obj_total_cat->total)) . '</i></b></td>';
        echo '</tr>';
                
        $sql_subcat = "SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = " . $obj_categorias->cod_titulos_categorias;
        $res_subcat = mysql_query($sql_subcat);
        $num_subcat = mysql_num_rows($res_subcat);
        
        while ($obj_subcat = mysql_fetch_object($res_subcat))
        {
        
            // INICIO SQL DE 2 MES ANTES
            $sql_total_sub_2mes_antes = "SELECT SUM(tp.valor) total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_titulos_subcategorias = ".$obj_subcat->cod_titulos_subcategorias." AND t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ";
            
            if ($tipo_data_filtro == 'VENC')
            {
                $sql_total_sub_2mes_antes .= " AND tp.data_vencimento BETWEEN '" . ($data_inicial_filtro_2mes_antes) . "' AND '" . ($data_final_filtro_2mes_antes) . "'";
            }
            else if($tipo_data_filtro == 'REF')
            {
                $sql_total_sub_2mes_antes .= " AND tp.mes_ref BETWEEN MONTH('" . ($data_inicial_filtro_2mes_antes) . "') AND MONTH('" . ($data_final_filtro_2mes_antes) . "') AND tp.ano_ref BETWEEN YEAR('" . ($data_inicial_filtro_2mes_antes) . "') AND YEAR('" . ($data_final_filtro_2mes_antes) . "')";
            }
            
            if ($tipo_titulo != "PAGAR_RECEBER")
            {
                $sql_total_sub_2mes_antes .= " AND (t.tipo_titulo='".$tipo_titulo."')";
            }
            
            if($cod_pizzarias != 'TODOS')
            {
                $sql_total_sub_2mes_antes .= " AND (t.cod_pizzarias='" . $cod_pizzarias . "')";
            }
            
            if($situacao_parcelas != 'PAGO_ABERTO')
            {
                $sql_total_sub_2mes_antes .= " AND (tp.situacao='$situacao_parcelas')";
            }
            
            if($nf_filtro == 'NF')
            {
                $sql_total_sub_2mes_antes .= " AND (t.numero_nota_fiscal <> '' AND t.numero_nota_fiscal IS NOT NULL)";
            }
            else if($nf_filtro == 'SEM_NF')
            {
                $sql_total_sub_2mes_antes .= " AND (t.numero_nota_fiscal = '' AND t.numero_nota_fiscal IS NULL)";
            }
            // FIM SQL DE 2 MES ANTES
          
            
            
            // INICIO SQL DE 1 MES ANTES
            $sql_total_sub_1mes_antes = "SELECT SUM(tp.valor) total FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_titulos_subcategorias = ".$obj_subcat->cod_titulos_subcategorias." AND t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
            
            if ($tipo_data_filtro == 'VENC')
            {
                $sql_total_sub_1mes_antes .= " AND tp.data_vencimento BETWEEN '" . ($data_inicial_filtro_1mes_antes) . "' AND '" . ($data_final_filtro_1mes_antes) . "'";
            }
            else if($tipo_data_filtro == 'REF')
            {
                $sql_total_sub_1mes_antes .= " AND tp.mes_ref BETWEEN MONTH('" . ($data_inicial_filtro_1mes_antes) . "') AND MONTH('" . ($data_final_filtro_1mes_antes) . "') AND tp.ano_ref BETWEEN YEAR('" . ($data_inicial_filtro_1mes_antes) . "') AND YEAR('" . ($data_final_filtro_1mes_antes) . "')";
            }
            
            if ($tipo_titulo != "PAGAR_RECEBER")
            {
                $sql_total_sub_1mes_antes .= " AND (t.tipo_titulo='".$tipo_titulo."')";
            }
            
            if($cod_pizzarias != 'TODOS')
            {
                $sql_total_sub_1mes_antes .= " AND (t.cod_pizzarias='" . $cod_pizzarias . "')";
            }
            
            if($situacao_parcelas != 'PAGO_ABERTO')
            {
                $sql_total_sub_1mes_antes .= " AND (tp.situacao='$situacao_parcelas')";
            }
            
            if($nf_filtro == 'NF')
            {
                $sql_total_sub_1mes_antes .= " AND (t.numero_nota_fiscal <> '' AND t.numero_nota_fiscal IS NOT NULL)";
            }
            else if($nf_filtro == 'SEM_NF')
            {
                $sql_total_sub_1mes_antes .= " AND (t.numero_nota_fiscal = '' AND t.numero_nota_fiscal IS NULL)";
            }
            // FIM SQL DE 1 MES ANTES
                      
          
          
            // INICIO SQL DO MES CORRENTE
            $sql_total_sub = "SELECT SUM(tp.valor) total, ($sql_total_sub_2mes_antes) AS total_2mes_antes, ($sql_total_sub_1mes_antes) AS total_1mes_antes FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_titulos_subcategorias = ".$obj_subcat->cod_titulos_subcategorias." AND t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
            
            if ($tipo_data_filtro == 'VENC')
            {
                $sql_total_sub .= " AND tp.data_vencimento BETWEEN '" . ($data_inicial_filtro) . "' AND '" . ($data_final_filtro) . "'";
            }
            else if($tipo_data_filtro == 'REF')
            {
                $sql_total_sub .= " AND tp.mes_ref BETWEEN MONTH('" . ($data_inicial_filtro) . "') AND MONTH('" . ($data_final_filtro) . "') AND tp.ano_ref BETWEEN YEAR('" . ($data_inicial_filtro) . "') AND YEAR('" . ($data_final_filtro) . "')";
            }
            
            if ($tipo_titulo != "PAGAR_RECEBER")
            {
                $sql_total_sub .= " AND (t.tipo_titulo='".$tipo_titulo."')";
            }
            
            if($cod_pizzarias != 'TODOS')
            {
                $sql_total_sub .= " AND (t.cod_pizzarias='" . $cod_pizzarias . "')";
            }
            
            if($situacao_parcelas != 'PAGO_ABERTO')
            {
                $sql_total_sub .= " AND (tp.situacao='$situacao_parcelas')";
            }
            
            if($nf_filtro == 'NF')
            {
                $sql_total_sub .= " AND (t.numero_nota_fiscal <> '' AND t.numero_nota_fiscal IS NOT NULL)";
            }
            else if($nf_filtro == 'SEM_NF')
            {
                $sql_total_sub .= " AND (t.numero_nota_fiscal = '' AND t.numero_nota_fiscal IS NULL)";
            }
            // FIM SQL DO MES CORRENTE

            //echo $sql_total_sub . "<br><br>";

            $res_total_sub = mysql_query($sql_total_sub);
            $obj_total_sub = mysql_fetch_object($res_total_sub);
          
            echo '<tr bgcolor="#E5E5FF">';
            echo '<td style="padding-left: 40px;"><b>' . bd2texto($obj_subcat->titulos_subcategorias) . '</b></td>';
            echo '<td align="right"><b>' . bd2moeda(abs($obj_total_sub->total_2mes_antes)) . '</b></td>';
            echo '<td align="right"><b>' . bd2moeda(abs($obj_total_sub->total_1mes_antes)) . '</b></td>';
            echo '<td align="right"><b>' . bd2moeda(abs($obj_total_sub->total)) . '</b></td>';
            echo '</tr>';
            
            
            $total_2mes_antes -= $obj_total_sub->total_2mes_antes;         
            $total_1mes_antes -= $obj_total_sub->total_1mes_antes;
            $total -= $obj_total_sub->total;
            
            if ($detalhado)
            {
                $sql_titulos = "SELECT tp.*, t.* FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_titulos_subcategorias = ".$obj_subcat->cod_titulos_subcategorias." AND t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
                
                if ($tipo_data_filtro == 'VENC')
                {
                    $sql_titulos .= " AND tp.data_vencimento BETWEEN '" . ($data_inicial_filtro) . "' AND '" . ($data_final_filtro) . "'";
                }
                else if($tipo_data_filtro == 'REF')
                {
                    $sql_titulos .= " AND tp.mes_ref BETWEEN MONTH('" . ($data_inicial_filtro) . "') AND MONTH('" . ($data_final_filtro) . "') AND tp.ano_ref BETWEEN YEAR('" . ($data_inicial_filtro) . "') AND YEAR('" . ($data_final_filtro) . "')";
                }
                
                if ($tipo_titulo != "PAGAR_RECEBER")
                {
                    $sql_titulos .= " AND (t.tipo_titulo='" . $tipo_titulo . "')";
                }
                
                if($cod_pizzarias != 'TODOS')
                {
                    $sql_titulos .= " AND (t.cod_pizzarias='" . $cod_pizzarias . "')";
                }
                
                if($situacao_parcelas != 'PAGO_ABERTO')
                {
                    $sql_titulos .= " AND (tp.situacao='$situacao_parcelas')";
                }
                
                if($nf_filtro == 'NF')
                {
                    $sql_titulos .= " AND (t.numero_nota_fiscal <> '' AND t.numero_nota_fiscal IS NOT NULL)";
                }
                else if($nf_filtro == 'SEM_NF')
                {
                    $sql_titulos .= " AND (t.numero_nota_fiscal = '' AND t.numero_nota_fiscal IS NULL)";
                }
                
                $sql_titulos .= ' ORDER BY data_vencimento';
                
                $res_titulos = mysql_query($sql_titulos);
                $num_titulos = mysql_num_rows($res_titulos);
                
                while ($obj_titulos = mysql_fetch_object($res_titulos))
                {
                    echo '<tr>';
                    echo '<td style="padding-left: 80px;">';
                    
                    echo $obj_titulos->cod_titulos . ' - ';
                    
                    if($obj_titulos->tipo_cedente_sacado == 'FORNECEDOR')
                    {
                        $obj_buscar_fornecedor = executaBuscaSimples("SELECT * FROM ipi_fornecedores WHERE cod_fornecedores = '" . $obj_titulos->cod_fornecedores . "'", $conexao);
                        
                        echo bd2texto($obj_buscar_fornecedor->nome_fantasia);
                    }
                    else if($obj_titulos->tipo_cedente_sacado == 'COLABORADOR')
                    {
                        $obj_buscar_colaborador = executaBuscaSimples("SELECT * FROM ipi_colaboradores WHERE cod_colaboradores = '" . $obj_titulos->cod_colaboradores . "' AND cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")", $conexao);
    
                        echo bd2texto($obj_buscar_colaborador->nome);
                    }
                    else if(($obj_titulos->tipo_cedente_sacado == 'PROJETO') || ($obj_titulos->tipo_cedente_sacado == 'CLIENTE') || ($obj_titulos->tipo_cedente_sacado == 'PRODUTO'))
                    {
                        $obj_buscar_cliente = executaBuscaSimples("SELECT * FROM ipi_clientes WHERE cod_clientes = '" . $obj_titulos->cod_clientes . "'", $conexao);
    
                        echo bd2texto($obj_buscar_cliente->nome);
                    }
                    
                    echo ' - Venc.: ' . bd2data($obj_titulos->data_vencimento);
                    
                    echo ' - ' . bd2texto($obj_titulos->numero_parcela) . "/" . bd2texto($obj_titulos->total_parcelas);
                    
                    if (bd2texto($obj_titulos->mes_ref))
                    {
                        echo " - Ref.: " . bd2texto($obj_titulos->mes_ref)."/". bd2texto($obj_titulos->ano_ref);
                    }
                    
                    if(trim($obj_titulos->numero_nota_fiscal) != '')
                    {
                        echo " - NF: " . bd2texto($obj_titulos->numero_nota_fiscal);
                    }

                    echo '</td>';
                    echo '<td style="padding-left: 80px;" align="right">' . bd2moeda(abs($obj_titulos->valor)) . '</td>';
                    
                    echo '</tr>';
                }
            }
        }
        $subtotal = bd2moeda(abs($total_2mes_antes));
        echo '<tr>';
        echo '<td align="center"><b>SUBTOTAL</b></td>';
        echo '<td style="padding-left: 80px;" align="right"><b>' . bd2moeda(abs($total_2mes_antes))  . '</b></td>';
        //die();
        echo '<td style="padding-left: 80px;" align="right"><b>' . bd2moeda(abs($total_1mes_antes)) . '</b></td>';
        echo '<td style="padding-left: 80px;" align="right"><b>' . bd2moeda(abs($total)) . '</b></td>';
        echo '</tr>';
        

    }
     
        echo '<tr>';
        echo '<td align="center"><b>TOTAL</b></td>';
        echo '<td style="padding-left: 80px;" align="right"><b>' . bd2moeda(abs($total_2mes_antes)) . '</b></td>';
        echo '<td style="padding-left: 80px;" align="right"><b>' . bd2moeda(abs($total_1mes_antes)) . '</b></td>';
        echo '<td style="padding-left: 80px;" align="right"><b>' . bd2moeda(abs($total)) . '</b></td>';
        echo '</tr>';
    
    desconectabd($conexao);
    
    ?>
  
    </tbody>
</table>

<? endif; ?>

<? rodape(); ?>
