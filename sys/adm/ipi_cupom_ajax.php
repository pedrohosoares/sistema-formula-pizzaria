<?php

/**
 * ipi_cupom_ajax.php: Cadastro de Cupom (Ajax)
 * 
 * Índice: cod_cupons
 * Tabela: ipi_cupons
 */

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

switch ($acao)
{
    case 'carregar_produto':
        $con = conectabd();
        
        echo '<option value=""></option>';
        
        $produto = validaVarPost('produto');
        
        if ($produto == 'PIZZA')
        {
            $sqlBuscaPizza = "SELECT * FROM ipi_pizzas ORDER BY pizza";
            $resBuscaPizza = mysql_query($sqlBuscaPizza);
            
            while ($objBuscaPizza = mysql_fetch_object($resBuscaPizza))
            {
                echo '<option value="' . $objBuscaPizza->cod_pizzas . '">' . utf8_encode(bd2texto($objBuscaPizza->pizza)) . '</option>';
            }
        }
        else if ($produto == 'BORDA')
        {
            $sqlBuscaBorda = "SELECT * FROM ipi_bordas ORDER BY borda";
            $resBuscaBorda = mysql_query($sqlBuscaBorda);
            
            while ($objBuscaBorda = mysql_fetch_object($resBuscaBorda))
            {
                echo '<option value="' . $objBuscaBorda->cod_bordas . '">' . utf8_encode(bd2texto($objBuscaBorda->borda)) . '</option>';
            }
        }
        else if ($produto == 'BEBIDA')
        {
            $sqlBuscaBebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_conteudos_pizzarias cp on cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos GROUP BY b.bebida ORDER BY b.bebida";
            $resBuscaBebidas = mysql_query($sqlBuscaBebidas);
            
            while ($objBuscaBebidas = mysql_fetch_object($resBuscaBebidas))
            {
                echo '<option value="' . $objBuscaBebidas->cod_bebidas_ipi_conteudos . '">' . utf8_encode(bd2texto($objBuscaBebidas->bebida . ' ' . $objBuscaBebidas->conteudo)) . '</option>';
            }
        }
        
        desconectabd($con);
        break;
    case 'carregar_tamanho':
        $con = conectabd();
        
        echo '<option value=""></option>';
        
        $produto = validaVarPost('produto');
        $cod_produtos = (validaVarPost('cod_produtos')) ? validaVarPost('cod_produtos') : 0;
        
        if ($cod_produtos <= 0)
        {
            $sqlBuscaTamanho = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
            $resBuscaTamanho = mysql_query($sqlBuscaTamanho);
            
            while ($objBuscaTamanho = mysql_fetch_object($resBuscaTamanho))
            {
                echo '<option value="' . $objBuscaTamanho->cod_tamanhos . '">' . utf8_encode(bd2texto($objBuscaTamanho->tamanho)) . '</option>';
            }
        }
        else if ($produto == 'PIZZA')
        {
            $sqlBuscaTamanho = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt INNER JOIN ipi_pizzas p ON (pt.cod_pizzas = p.cod_pizzas) INNER JOIN ipi_tamanhos t ON (pt.cod_tamanhos = t.cod_tamanhos) WHERE p.cod_pizzas = $cod_produtos GROUP BY t.tamanho ORDER BY pizza ";
            $resBuscaTamanho = mysql_query($sqlBuscaTamanho);
            
            while ($objBuscaTamanho = mysql_fetch_object($resBuscaTamanho))
            {
                echo '<option value="' . $objBuscaTamanho->cod_tamanhos . '">' . utf8_encode(bd2texto($objBuscaTamanho->tamanho)) . '</option>';
            }
        }
        else if ($produto == 'BORDA')
        {
            $sqlBuscaTamanho = "SELECT * FROM ipi_tamanhos_ipi_bordas bt INNER JOIN ipi_bordas b ON (bt.cod_bordas = b.cod_bordas) INNER JOIN ipi_tamanhos t ON (bt.cod_tamanhos = t.cod_tamanhos) WHERE b.cod_bordas = $cod_produtos GROUP BY t.tamanho ORDER BY borda";
            $resBuscaTamanho = mysql_query($sqlBuscaTamanho);
            
            while ($objBuscaTamanho = mysql_fetch_object($resBuscaTamanho))
            {
                echo '<option value="' . $objBuscaTamanho->cod_tamanhos . '">' . utf8_encode(bd2texto($objBuscaTamanho->tamanho)) . '</option>';
            }
        }
        
        desconectabd($con);
        break;
}
?>
