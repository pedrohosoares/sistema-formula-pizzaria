<?php

/**
 * Saldo banco (ajax).
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       28/06/2010   Felipe		Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$acao = validaVarPost('acao');

switch($acao)
{
    case 'carregar_relatorio':
        $data_inicial = data2bd(validaVarPost('data_inicial'));
        $data_final = data2bd(validaVarPost('data_final'));
        $cod_pizzarias = texto2bd(validaVarPost('cod_pizzarias'));

        ?>
        
        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="<? echo LARGURA_PADRAO ?>">
          <thead>
            <tr>
              <td align="center" width="90"><? echo utf8_encode('Banco') ?></td>
              <td align="center" width="80"><? echo utf8_encode('Crédito') ?></td>
              <td align="center" width="80"><? echo utf8_encode('Débito') ?></td>
              <td align="center" width="80"><? echo utf8_encode('Saldo') ?></td>
            </tr>
          </thead>
          <tbody>
          <?
          $conexao = conectabd();
        
	        $sql_buscar_registros = "SELECT * FROM ipi_bancos b INNER JOIN ipi_bancos_ipi_pizzarias bp ON (b.cod_bancos=bp.cod_bancos) WHERE b.situacao='ATIVO' AND bp.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
			    if ( ($cod_pizzarias!="") && ($cod_pizzarias!="TODOS") )
		      {
            $sql_buscar_registros .= " AND bp.cod_pizzarias = '".$cod_pizzarias."'";
          }
	        $res_buscar_registros = mysql_query($sql_buscar_registros);
	        
	        //echo $sql_buscar_registros;

	        $total = 0;
	        while ($obj_buscar_registros=mysql_fetch_object($res_buscar_registros))
	        {
              // Soma tudo até a data final para dar o Saldo!
	            $obj_buscar_credito = executaBuscaSimples("SELECT SUM(valor_total) AS saldo FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE  t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND tp.cod_bancos_destino=".$obj_buscar_registros->cod_bancos." AND data_pagamento <= '$data_final' AND tipo_titulo = 'RECEBER' AND tp.situacao = 'PAGO'", $conexao);
	            $obj_buscar_debito = executaBuscaSimples("SELECT SUM(valor_total) AS saldo FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND tp.cod_bancos_destino=".$obj_buscar_registros->cod_bancos." AND data_pagamento <= '$data_final' AND tipo_titulo = 'PAGAR' AND tp.situacao = 'PAGO'", $conexao);
	            
	            $obj_buscar_transfer_credito = executaBuscaSimples("SELECT SUM(valor_total) AS saldo FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND tp.cod_bancos_destino=".$obj_buscar_registros->cod_bancos." AND data_pagamento <= '$data_final' AND tipo_titulo = 'TRANSFER' AND tp.valor_total > 0 AND tp.situacao = 'PAGO'", $conexao);
	            $obj_buscar_transfer_debito = executaBuscaSimples("SELECT SUM(valor_total) AS saldo FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND tp.cod_bancos_destino=".$obj_buscar_registros->cod_bancos." AND data_pagamento <= '$data_final' AND tipo_titulo = 'TRANSFER' AND tp.valor_total < 0 AND tp.situacao = 'PAGO'", $conexao);
	            
	            $salto_real = (($obj_buscar_credito->saldo) + ($obj_buscar_debito->saldo) + ($obj_buscar_transfer_credito->saldo) + ($obj_buscar_transfer_debito->saldo));
	            
	            echo '<tr>';
	            echo '<td align="center">' . bd2texto(utf8_encode($obj_buscar_registros->banco.' - '.$obj_buscar_registros->agencia)) . '</td>';
	            echo '<td align="center"><b><font color="green">' . bd2moeda($obj_buscar_credito->saldo + $obj_buscar_transfer_credito->saldo) . '</font></b></td>';
	            echo '<td align="center"><b><font color="red">' . bd2moeda(($obj_buscar_debito->saldo + $obj_buscar_transfer_debito->saldo)*-1) . '</font></b></td>';
	            if (bd2moeda($salto_real) > 0)
	            {
	            	echo '<td align="center"><b><font color="blue">'.bd2moeda($salto_real).'</font></b></td>';
	            }
	            else 
	            {
	            	echo '<td align="center"><b><font color="red">'.bd2moeda($salto_real).'</font></b></td>';
	            }
	            echo '</tr>';
	            
	            $total += $salto_real;
    			}
            
            echo '<tr>';
            
            echo '<td colspan="3" align="center" style="background-color: rgb(239, 239, 239);"><b>Saldo</b></td>';
            
			if (bd2moeda($total)>0)
            {
            	echo '<td align="center" style="background-color: rgb(239, 239, 239);"><b><font color="blue">'.bd2moeda($total).'</font></b></td>';
            }
            else 
            {
            	echo '<td align="center" style="background-color: rgb(239, 239, 239);"><b><font color="red">'.bd2moeda($total).'</font></b></td>';
            }
            echo '</tr>';
            ?>
          
            </tbody>
        </table>
        
        <?
        
        desconectabd($conexao);
        
	break;
}

?>
