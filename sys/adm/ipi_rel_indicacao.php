<?php

/**
 * ipi_rel_indicacao.php: Relatório de indicação de clientes
 * 
 * Índice: cod_indicacoes
 * Tabela: ipi_indicacoes
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Indicação de Clientes');

?>

<table class="listaEdicao" style="width: 900px !important; margin: 0px auto;" align="center" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <td align="center">Cliente</td>
            <td align="center" width="100">Quantidade de Indicações</td>
            <td align="center" width="100">Total de Compra</td>
        </tr>
    </thead>
    <tbody>
  
    <?
    
    function imprimir_tabela_indicacao ($cod, $espaco)
    {
        $tabela_cor = array ('', '#FAFFCF', '#EFF6FF', '#CFFFD1');
        
        $SqlBuscaPaginas = "SELECT * FROM ipi_clientes WHERE cod_clientes_indicador = $cod ORDER BY nome";
        $resBuscaPaginas = mysql_query($SqlBuscaPaginas);
        $numBuscaPaginas = mysql_num_rows($resBuscaPaginas);
        
        if (($numBuscaPaginas > 0) && ($cod > 0))
        {
            $espaco += 25;
        }
        
        while ($objBuscaPaginas = mysql_fetch_object($resBuscaPaginas))
        {
            $sql_quantidade_total_indicacoes = 'SELECT COUNT(*) AS total FROM ipi_indicacoes WHERE cod_clientes_indicador = ' . $objBuscaPaginas->cod_clientes;
            $res_quantidade_total_indicacoes = mysql_query($sql_quantidade_total_indicacoes);
            $obj_quantidade_total_indicacoes = mysql_fetch_object($res_quantidade_total_indicacoes);
            
            if (($obj_quantidade_total_indicacoes->total > 0) || ($objBuscaPaginas->cod_clientes_indicador > 0))
            {
                $sql_buscar_total_compra = "SELECT SUM(valor_total) AS valor_total FROM ipi_pedidos WHERE cod_clientes = '" . $objBuscaPaginas->cod_clientes . "' AND situacao = 'BAIXADO'";
                $res_buscar_total_compra = mysql_query($sql_buscar_total_compra);
                $obj_buscar_total_compra = mysql_fetch_object($res_buscar_total_compra);
                
                $cor = $tabela_cor[$espaco / 25];
                
                echo '<tr>';
                
                echo '<td align="left" style="padding-left: ' . $espaco . 'px; background-color: ' . $cor . ';">' . bd2texto($objBuscaPaginas->nome) . '</td>';
                echo '<td align="center" style="background-color: ' . $cor . ';">' . $obj_quantidade_total_indicacoes->total . '</td>';
                echo '<td align="center" style="background-color: ' . $cor . ';">' . $obj_buscar_total_compra->valor_total . '</td>';
                
                echo '</tr>';
            }
            
            imprimir_tabela_indicacao($objBuscaPaginas->cod_clientes, $espaco);
        }
    }
    
    $conexao = conectabd();
    imprimir_tabela_indicacao(0, 3);
    desconectabd($conexao);
    
    ?>
  
  </tbody>
</table>

<?
rodape();
?>