<?php

/**
 * Relatório de Fluxo de Caixa (ajax).
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       12/12/2009   FELIPE        Criado.
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
        
        $cod_titulos_subcategorias_filtro = validaVarPost('cod_titulos_subcategorias_filtro');
        $cod_bancos_filtro = validaVarPost('cod_bancos_filtro');
        
        $situacao = validaVarPost('situacao');
        $filtrar_por = validaVarPost('filtrar_por');

        $cod_pizzarias = validaVarPost('cod_pizzarias');
        
        $pagamentos_filtro = (validaVarPost('pagamentos_filtro') == 'true') ? true : false;
        $recebimentos_filtro = (validaVarPost('recebimentos_filtro') == 'true') ? true : false;
        $transfer_filtro = (validaVarPost('transfer_filtro') == 'true') ? true : false;
        
        $arr_tipo_titulo = array();
        
        if($pagamentos_filtro)
        {
            $arr_tipo_titulo[] = "'PAGAR'";
        }
        
        if($recebimentos_filtro)
        {
            $arr_tipo_titulo[] = "'RECEBER'";
        }
        
        if($transfer_filtro)
        {
            $arr_tipo_titulo[] = "'TRANSFER'";
        }
        
        if($filtrar_por =="MES_REFERENCIA")
        {
            $filtro_data = "mes_ref BETWEEN month('$data_inicial') AND month('$data_final') AND ano_ref BETWEEN year('$data_inicial') AND year('$data_final') ";
        }
        else if($filtrar_por =="DATA_PAGAMENTO")
        {
            $filtro_data =  "data_pagamento BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' ";
        }
        else if($filtrar_por =="DATA_CRIADA")
        {
            $filtro_data =  "tp.data_hora_criacao BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' ";
        }
        else if($filtrar_por=="DATA_EMISSAO")
        {
            $filtro_data =  "data_emissao BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' ";
        }else
        {
            $filtro_data =  "data_vencimento BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' ";
        }   



        $conexao = conectabd();
        
        $sql_buscar_registros = "SELECT t.*, tp.*, tc.titulos_categoria, ts.titulos_subcategorias,tp.data_hora_criacao as data_hora_parcela FROM ipi_titulos t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) INNER JOIN ipi_titulos_subcategorias ts ON (t.cod_titulos_subcategorias = ts.cod_titulos_subcategorias) INNER JOIN ipi_titulos_categorias tc ON (ts.cod_titulos_categorias = tc.cod_titulos_categorias) INNER JOIN ipi_pizzarias pizr ON (pizr.cod_pizzarias = t.cod_pizzarias) WHERE $filtro_data AND pizr.cod_pizzarias = $cod_pizzarias  AND t.tipo_titulo IN (" . implode(',', $arr_tipo_titulo) . ")";
        //echo "<br/>a-".$filtrar_por."-a</br></br>";
        //echo "<br>1: ".$sql_buscar_registros;
        //die();

        if($situacao != 'TODOS')
        {
            $sql_buscar_registros .= " AND tp.situacao = '$situacao'";
        }
        
        if($cod_titulos_subcategorias_filtro != 'TODOS')
        {
            $arr_opções = explode("_",$cod_titulos_subcategorias_filtro);
            if($arr_opções[0]=="TODOS")
            {
                $sql_buscar_registros .= " AND t.cod_titulos_subcategorias not in (".$arr_opções[1].")";
            }
            else
            {
                $sql_buscar_registros .= " AND t.cod_titulos_subcategorias in ($cod_titulos_subcategorias_filtro)";
            }

        }
        
    	if($cod_bancos_filtro != 'TODOS')
        {
        //    $sql_buscar_registros .= " AND tp.cod_bancos_destino = '$cod_bancos_filtro'";
        }
        
        $sql_buscar_registros .= " ORDER BY data_vencimento";
        //echo "<br>1: ".$sql_buscar_registros;
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        ?>
        
        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="<? echo LARGURA_PADRAO + 250 ?>">
            <thead>
                <tr>
                    <td align="center" width="70"><? echo utf8_encode('Lançamento') ?></td>
                    <td align="center" width="70"><? echo utf8_encode('Emissão') ?></td>
                    <td align="center" width="70"><? echo utf8_encode('Vencimento') ?></td>
                    <td align="center" width="70"><? echo utf8_encode('Pagamento') ?></td>
                    <td align="center"><? echo utf8_encode('Lançamento') ?></td>
                    <td align="center"><? echo utf8_encode('Descrição') ?></td>
                    <td align="center" width="40"><? echo utf8_encode('Parcela') ?></td>
                    <td align="center" width="120"><? echo utf8_encode('Cedente / Sacado') ?></td>
                    <td align="center" width="50"><? echo utf8_encode('Situação') ?></td>
                    <td align="center" width="90"><? echo utf8_encode('Banco / Caixa') ?></td>
                    <td align="center" width="80"><? echo utf8_encode('Débito') ?></td>
                    <td align="center" width="80"><? echo utf8_encode('Crédito') ?></td>
                    <td align="center" width="80"><? echo utf8_encode('Saldo Provisionado') ?></td>
                    <td align="center" width="80"><? echo utf8_encode('Saldo') ?></td>
                    <td align="center" width="50"><? echo utf8_encode('Baixa') ?></td>

                </tr>
            </thead>
            <tbody>
          
            <?
            
            if($filtrar_por =="MES_REFERENCIA")
            {
                $filtro_data = "tp.mes_ref < month('$data_inicial') AND tp.ano_ref < year('$data_inicial') ";
                $filtro_data_saldo_fluxo = "tp.mes_ref < month('$data_inicial') AND tp.ano_ref < year('$data_inicial')" ;
            }
            else if($filtrar_por =="DATA_PAGAMENTO")
            {
                $filtro_data = "tp.data_pagamento < '$data_inicial'";
                $filtro_data_saldo_fluxo = "tp.data_pagamento < '$data_inicial'";
            }
            else if($filtrar_por =="DATA_CRIADA")
            {
                //$filtro_data =  "data_emissao BETWEEN '$data_inicial' AND '$data_final' ";
                $filtro_data = "tp.data_hora_criacao < '$data_inicial'";
                $filtro_data_saldo_fluxo = "tp.data_hora_criacao < '$data_inicial'";
            }
            else if($filtrar_por=="DATA_EMISSAO")
            {
               // $filtro_data =  "data_hora_criacao BETWEEN '$data_inicial' AND '$data_final' ";
                $filtro_data = "tp.data_emissao < '$data_inicial'";
                $filtro_data_saldo_fluxo = "tp.data_emissao < '$data_inicial'";
            }
            else
            {
                $filtro_data = "tp.data_vencimento < '$data_inicial'";
                $filtro_data_saldo_fluxo = "tp.data_vecimento < '$data_inicial'";
            }   


            // Buscando o Saldo Inicial
            $obj_buscar_saldo_fluxo = executaBuscaSimples("SELECT SUM(tp.valor_total) AS saldo FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE $filtro_data_saldo_fluxo AND t.cod_pizzarias = $cod_pizzarias AND tp.situacao = 'PAGO'", $conexao);
            $saldo_real = $obj_buscar_saldo_fluxo->saldo;
            $sub_saldo_real = 0;
            
            $obj_buscar_credito = executaBuscaSimples("SELECT SUM(tp.valor_total) AS saldo FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE $filtro_data AND t.cod_pizzarias= $cod_pizzarias AND t.tipo_titulo = 'RECEBER'", $conexao);
            $credito_inicial = $obj_buscar_credito->saldo;
            
            $obj_buscar_debito = executaBuscaSimples("SELECT SUM(tp.valor_total) AS saldo FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE $filtro_data AND t.cod_pizzarias = $cod_pizzarias AND t.tipo_titulo = 'PAGAR'", $conexao);
            //echo "SELECT SUM(tp.valor_total) AS saldo FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE tp.data_vencimento < '$data_inicial' AND t.cod_pizzarias = $cod_pizzarias AND t.tipo_titulo = 'PAGAR'";
            $debito_inicial = $obj_buscar_debito->saldo;
            
            $obj_buscar_saldo_fluxo = executaBuscaSimples("SELECT SUM(tp.valor_total) AS saldo FROM ipi_titulos_parcelas tp INNER JOIN ipi_titulos t ON (tp.cod_titulos = t.cod_titulos) WHERE t.cod_pizzarias = $cod_pizzarias AND $filtro_data", $conexao);
            $saldo_simulado = $obj_buscar_saldo_fluxo->saldo;
            $sub_saldo_simulado = 0;
            
            echo '<tr>';
            
            echo '<td align="center" colspan="10" style="background-color: #EFEFEF;"><b>Transportado</b></td>';
            
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="red">' . bd2moeda($debito_inicial * -1) . '</font></b></td>';
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="green">' . bd2moeda($credito_inicial) . '</font></b></td>';
            
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="#DB00FF">' . bd2moeda($saldo_simulado) . '</font></b></td>';
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="blue">' . bd2moeda($saldo_real) . '</font></b></td>';
            
            echo '<td align="center" style="background-color: #EFEFEF;">&nbsp;</td>';

            
            echo '</tr>';
            
            $total_debito = $debito_inicial;
            $sub_total_debito = 0;
            $total_credito = $credito_inicial;
            $sub_total_credito = 0;
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                echo '<td align="center">' . ($obj_buscar_registros->data_hora_parcela!="" ? bd2data(date("Y-m-d",strtotime($obj_buscar_registros->data_hora_parcela))) : '') . '</td>';                
                echo '<td align="center">' . bd2data($obj_buscar_registros->data_emissao) . '</td>';
                echo '<td align="center">' . bd2data($obj_buscar_registros->data_vencimento) . '</td>';
                echo '<td align="center">' . bd2data($obj_buscar_registros->data_pagamento) . '</td>';


                echo '<td align="left">' . utf8_encode(bd2texto($obj_buscar_registros->titulos_categoria . '  &raquo; ' . $obj_buscar_registros->titulos_subcategorias)) . '</td>';
                echo '<td align="left">' . utf8_encode(bd2texto($obj_buscar_registros->descricao));

                if($obj_buscar_registros->documento_numero)
                {
                    echo '<br><small>Num. Doc.: </small>' . utf8_encode(bd2texto($obj_buscar_registros->documento_numero)) .'';
                }

                if($obj_buscar_registros->cheque_numero)
                {
                    echo '<br><small>Cheque: </small>' . utf8_encode(bd2texto($obj_buscar_registros->cheque_numero));
                    echo ($obj_buscar_registros->cheque_favorecido != "") ? ' - '. utf8_encode(bd2texto($obj_buscar_registros->cheque_favorecido)):'';
                }

                if($obj_buscar_registros->obs)
                {
                    echo '<br><small>Obs: </small>' . utf8_encode(bd2texto($obj_buscar_registros->obs)) .'';
                }

                $url = "";

                switch ($obj_buscar_registros->tipo_titulo) {
                    case 'PAGAR':
                         $url = "ipi_titulos_pagar.php?tp=";
                        break;

                    case 'RECEBER':
                        $url = "ipi_titulos_receber.php?tr=";
                        break;

                }
                if ($url!=""){
                    echo '<a href="'.$url.''.$obj_buscar_registros->cod_titulos_parcelas.'" target="_blank"><img style="width: 3%; vertical-align: bottom; float: right; margin-right: 0.6rem;" src="'.(is_file("../lib/img/principal/info.ico") ? "../lib/img/principal/info.ico" : "../lib/img/principal/detalhe.gif") .' " alt="Clique para mais detalhes" title="Clique para mais detalhes"/> </a>';
                }
               
                echo '</td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->numero_parcela . '/' . $obj_buscar_registros->total_parcelas) . '</td>';
                
                echo '<td align="center">';
                
                if($obj_buscar_registros->tipo_cedente_sacado == 'FORNECEDOR')
                {
                    $obj_buscar_fornecedor = executaBuscaSimples("SELECT * FROM ipi_fornecedores WHERE cod_fornecedores = '" . $obj_buscar_registros->cod_fornecedores . "'", $conexao);
                    
                    echo utf8_encode(bd2texto($obj_buscar_fornecedor->nome_fantasia));
                }
                else if($obj_buscar_registros->tipo_cedente_sacado == 'COLABORADOR')
                {
                	$obj_buscar_colaborador = executaBuscaSimples("SELECT * FROM ipi_colaboradores WHERE cod_colaboradores = '" . $obj_buscar_registros->cod_colaboradores . "'", $conexao);

                	echo utf8_encode(bd2texto($obj_buscar_colaborador->nome));
                }
                else if($obj_buscar_registros->tipo_cedente_sacado == 'ENTREGADOR')
                {
                	$obj_buscar_entregador = executaBuscaSimples("SELECT * FROM ipi_entregadores WHERE cod_entregadores = '" . $obj_buscar_registros->cod_entregadores . "'", $conexao);

                	echo utf8_encode(bd2texto($obj_buscar_entregador->nome));
                }
                else if(($obj_buscar_registros->tipo_cedente_sacado == 'PROJETO') || ($obj_buscar_registros->tipo_cedente_sacado == 'CLIENTE') || ($obj_buscar_registros->tipo_cedente_sacado == 'PRODUTO'))
                {
                	$obj_buscar_cliente = executaBuscaSimples("SELECT * FROM ipi_clientes WHERE cod_clientes = '" . $obj_buscar_registros->cod_clientes . "'", $conexao);

                	echo utf8_encode(bd2texto($obj_buscar_cliente->nome));
                }
                
                if($obj_buscar_registros->tipo_titulo == 'TRANSFER')
                {
                    echo 'TRANSFER'; 
                }
                
                echo '<br>';
                echo '<b><em><small>' . utf8_encode(bd2texto($obj_buscar_registros->tipo_cedente_sacado)) . '</small></em></b>';
                echo '</td>';
                
                echo '<td align="center">' . bd2texto($obj_buscar_registros->situacao) . '</td>';
                
                if($obj_buscar_registros->situacao == 'PAGO')
                {
                	// Buscar o banco/caixa

                	$obj_buscar_banco = executaBuscaSimples("SELECT * FROM ipi_bancos WHERE cod_bancos = '" . $obj_buscar_registros->cod_bancos_destino . "'", $conexao);
                
                	echo '<td align="center">';
                	
                	echo utf8_encode(bd2texto($obj_buscar_banco->banco)); 
                	
                	if(!$obj_buscar_banco->caixa)
                	{
                		echo '<br><small><b><em>AG: ' . utf8_encode(bd2texto($obj_buscar_banco->agencia))  . '<br>C/C: ' . utf8_encode(bd2texto($obj_buscar_banco->conta_corrente)) . '</em></b></small>';
                	}
                    
                	
                	echo '</td>';
                }
                else
                {
                	echo '<td align="center">&nbsp;</td>';	
                }
                
                if ($obj_buscar_registros->tipo_titulo == 'PAGAR')
                {
                    echo '<td align="center"><b><font color="red">' . bd2moeda($obj_buscar_registros->valor_total * -1) . '</font></b></td>';       
                    echo '<td align="center">&nbsp;</td>';
                    
                    $total_debito += $obj_buscar_registros->valor_total * -1;
                    $sub_total_debito += $obj_buscar_registros->valor_total * -1;
                }
                else if($obj_buscar_registros->tipo_titulo == 'RECEBER')
                {
                    echo '<td align="center">&nbsp;</td>';
                    echo '<td align="center"><b><font color="green">' . bd2moeda($obj_buscar_registros->valor_total) . '</font></b></td>';
                    
                    $total_credito += $obj_buscar_registros->valor_total;
                    $sub_total_credito += $obj_buscar_registros->valor_total;
                }
                else if ($obj_buscar_registros->tipo_titulo == 'TRANSFER')
                {
                    if($obj_buscar_registros->valor_total < 0)
                    {
                        echo '<td align="center"><b><font color="red">' . bd2moeda($obj_buscar_registros->valor_total * -1) . '</font></b></td>';       
                        echo '<td align="center">&nbsp;</td>';
                        
                        $total_debito += $obj_buscar_registros->valor_total * -1;
                        $sub_total_debito += $obj_buscar_registros->valor_total * -1;
                    }
                    else
                    {
                        echo '<td align="center">&nbsp;</td>';
                        echo '<td align="center"><b><font color="green">' . bd2moeda($obj_buscar_registros->valor_total) . '</font></b></td>';
                        
                        $total_credito += $obj_buscar_registros->valor_total;
                        $sub_total_credito += $obj_buscar_registros->valor_total;
                    }
                }
                
                $saldo_simulado += $obj_buscar_registros->valor_total;
                $sub_saldo_simulado += $obj_buscar_registros->valor_total;
                
                
                if($obj_buscar_registros->situacao == 'PAGO')
                {
                	$saldo_real += $obj_buscar_registros->valor_total;
                	$sub_saldo_real += $obj_buscar_registros->valor_total;
                }
                
                echo '<td align="center"><b><font color="#DB00FF">' . bd2moeda($saldo_simulado) . '</font></b></td>';
                echo '<td align="center"><b><font color="blue">' . bd2moeda($saldo_real) . '</font></b></td>';
                
                if($obj_buscar_registros->situacao != 'PAGO')
                {
                    echo '<td align="center"><input type="button" class="botaoVermelho" value="Baixar" onclick="exibir_baixar_parcela(' . $obj_buscar_registros->cod_titulos_parcelas . ')"></td>';
                   
                }
                else
                {
                    echo '<td align="center"></td>';

                }
                
                echo '</tr>';
            }

            echo '<tr>';
            echo '<td align="center" colspan="10" style="background-color: #EFEFEF;"><b>Subtotal</b></td>';
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="red">' . bd2moeda($sub_total_debito) . '</font></b></td>';
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="green">' . bd2moeda($sub_total_credito) . '</font></b></td>';
            
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="#DB00FF">' . bd2moeda($sub_saldo_simulado) . '</font></b></td>';
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="blue">' . bd2moeda($sub_saldo_real) . '</font></b></td>';
            
            echo '<td align="center" style="background-color: #EFEFEF;">&nbsp;</td>';

            echo '</tr>';
            
            echo '<tr>';
            echo '<td align="center" colspan="10" style="background-color: #EFEFEF;"><b>Saldo</b></td>';
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="red">' . bd2moeda($total_debito) . '</font></b></td>';
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="green">' . bd2moeda($total_credito) . '</font></b></td>';
            
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="#DB00FF">' . bd2moeda($saldo_simulado) . '</font></b></td>';
            echo '<td align="center" style="background-color: #EFEFEF;"><b><font color="blue">' . bd2moeda($saldo_real) . '</font></b></td>';
            
            echo '<td align="center" style="background-color: #EFEFEF;">&nbsp;</td>';

            echo '</tr>';
            
            ?>
          
            </tbody>
        </table>
        
        <?
        
        desconectabd($conexao);
        
        break;
        
    case 'exibir_baixar_parcela':
        $cod_titulos_parcelas = validaVarPost('cod_titulos_parcelas');

        $obj_titulos_parcelas = executaBuscaSimples("SELECT * FROM ipi_titulos t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) WHERE cod_titulos_parcelas = '$cod_titulos_parcelas'");
        
        ?>
        
        <h3>Baixar Parcela</h3>

        <br><br>
        
        <table>
        
        <tr>
        	<td class="legenda"><label for="pagamento"><? echo utf8_encode('Data de Pagamento'); ?></label></td>
        </tr>
        <tr>
            <td class="sep">
                <input type="text" name="pagamento" id="pagamento" maxlength="10" size="16" value="<? echo date('d/m/Y'); ?>" onkeypress="return MascaraData(this, event)">
            	&nbsp;
            	<a href="javascript:;" id="botao_pagamento_baixa"><img src="../lib/img/principal/botao-data.gif"></a>
            	
            	<script type="text/javascript">
            		new vlaDatePicker('pagamento', {openWith: 'botao_pagamento_baixa', prefillDate: false});
            	</script>
            </td>
        </tr>
        
        <tr>
        	<td class="legenda"><label for="juros"><? echo utf8_encode('Juros / Acréscimos'); ?></label></td>
        </tr>
        <tr>
            <td class="sep">
                <input type="text" name="juros" id="juros" maxlength="10" size="20" value="0,00" onkeypress="return formataMoeda(this, '.', ',', event)">
            </td>
        </tr>
        
        <tr>
        	<td class="legenda">
        		<label for="valor_total"><? echo utf8_encode('Valor Total'); ?></label>
    		</td>
        </tr>
        <tr>
            <td class="sep">
                <!-- <input type="text" name="valor_total" id="valor_total" maxlength="10" size="20" value="<? if($obj_titulos_parcelas->tipo_titulo == 'PAGAR') { echo bd2moeda($obj_titulos_parcelas->valor_total * -1); } else { echo bd2moeda($obj_titulos_parcelas->valor_total); } ?>" onkeypress="return formataMoeda(this, '.', ',', event)">  -->
                <? if($obj_titulos_parcelas->tipo_titulo == 'PAGAR') { echo bd2moeda($obj_titulos_parcelas->valor_total * -1); } else { echo bd2moeda($obj_titulos_parcelas->valor_total); } ?>
            </td>
        </tr>
        
        <tr>
        	<td class="legenda"><label for="cod_bancos"><? echo utf8_encode('Banco / Caixa'); ?></label></td>
        </tr>
        <tr>
            <td class="sep">
                <select name="cod_bancos" id="cod_bancos" style="width: 285px;">
                	<option value=""></option>
                	
                	<?

                	$conexao = conectabd();
                	
                	//$sql_buscar_bancos = "SELECT b.* FROM ipi_bancos b INNER JOIN ipi_bancos_ipi_pizzarias bp ON (b.cod_bancos = bp.cod_bancos) AND bp.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY banco";
                	$sql_buscar_bancos = "SELECT * FROM ipi_bancos WHERE cod_bancos IN (SELECT cod_bancos FROM ipi_bancos_ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")) ORDER BY banco";
                	$res_buscar_bancos = mysql_query($sql_buscar_bancos);
                	
                	while($obj_buscar_bancos = mysql_fetch_object($res_buscar_bancos))
                	{
                        echo '<option value="' . $obj_buscar_bancos->cod_bancos . '">';

                    	echo utf8_encode(bd2texto($obj_buscar_bancos->banco)); 
                    	
                    	if(!$obj_buscar_bancos->caixa)
                    	{
                    		echo ' - AG: ' . utf8_encode(bd2texto($obj_buscar_bancos->agencia))  . ' - C/C: ' . utf8_encode(bd2texto($obj_buscar_bancos->conta_corrente));
                    	}
                        
                        echo '</option>';                	       
                	}
                	
                	desconectabd($conexao);
                	
                	?>
                	
                </select>
            </td>
        </tr>
        
        <tr>
        	<td class="legenda"><label for="forma_pagamento"><? echo utf8_encode('Forma de Pagamento'); ?></label></td>
        </tr>
        <tr>
            <td class="sep">
                <select name="forma_pagamento" id="forma_pagamento" style="width: 285px;" onchange="exibir_forma_pagamento_baixa(this.value)">
                	<option value=""></option>
                	<option value="BOLETO"><? echo utf8_encode('Boleto') ?></option>
                	<option value="CHEQUE"><? echo utf8_encode('Cheque') ?></option>
                	<option value="DEPOSITO"><? echo utf8_encode('Depósito') ?></option>
                	<option value="DINHEIRO"><? echo utf8_encode('Dinheiro') ?></option>
                    <option value="CREDITO"><? echo utf8_encode('Crédito') ?></option>
                    <option value="DEBITO"><? echo utf8_encode('Débito') ?></option>
                </select>
            </td>
        </tr>
        
        <tr id="documento_numero_label_tr" style="display: none;">
        	<td class="legenda"><label for="documento_numero"><? echo utf8_encode('Número de Documento'); ?></label></td>
        </tr>
        <tr id="documento_numero_tr" style="display: none;">
            <td class="sep">
                <input type="text" name="documento_numero" id="documento_numero" maxlength="100" size="50">
            </td>
        </tr>
        
        <tr id="cheque_numero_label_tr" style="display: none;">
        	<td class="legenda"><label for="cheque_numero"><? echo utf8_encode('Número do Cheque'); ?></label></td>
        </tr>
        <tr id="cheque_numero_tr" style="display: none;">
            <td class="sep">
                <input type="text" name="cheque_numero" id="cheque_numero" maxlength="20" size="50">
            </td>
        </tr>
        
        <tr id="cheque_favorecido_label_tr" style="display: none;">
        	<td class="legenda"><label for="cheque_favorecido"><? echo utf8_encode('Favorecido'); ?></label></td>
        </tr>
        <tr id="cheque_favorecido_tr" style="display: none;">
            <td class="sep">
                <input type="text" name="cheque_favorecido" id="cheque_favorecido" maxlength="45" size="50">
            </td>
        </tr>
        
        <tr>
            <td align="center"><input name="botao_submit" class="botao"
                type="button" value="Baixar" onclick="javascript: baixar('<? echo $cod_titulos_parcelas; ?>');"> &nbsp;&nbsp;&nbsp;&nbsp; <input
                name="botao_fechar" class="botao" type="button" value="Cancelar"
                onclick="javascript: cancelar();"></td>
    	</tr>
        
        </table>
        
        <?
        
        break;
    case 'baixar':
        $file = 'log_alteracoes_fluxo_caixa.txt';
        $texto = var_export($_POST,true);

        //file_put_contents($file, "\n \n".$texto, FILE_APPEND);

        $cod_titulos_parcelas = validaVarPost('cod_titulos_parcelas');
        $pagamento = validaVarPost('pagamento');
        $juros = (moeda2bd(validaVarPost('juros')) > 0) ? moeda2bd(validaVarPost('juros')) : 0;
        $forma_pagamento = validaVarPost('forma_pagamento');
        $numero_documento = validaVarPost('numero_documento');
        $cheque_numero = validaVarPost('cheque_numero');
        $cheque_favorecido = validaVarPost('cheque_favorecido');
        $documento_numero = validaVarPost('documento_numero');
        $cod_bancos = validaVarPost('cod_bancos');
        
        if($cod_titulos_parcelas > 0)
        {
            $conexao = conectabd();
            
            $obj_buscar_titulos_parcelas = executaBuscaSimples("SELECT * FROM ipi_titulos t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) WHERE tp.cod_titulos_parcelas = '$cod_titulos_parcelas'", $conexao);
            
            $valor_total = ($obj_buscar_titulos_parcelas->tipo_titulo == 'PAGAR') ? ($juros + (($obj_buscar_titulos_parcelas->valor) * -1)) * -1 : $obj_buscar_titulos_parcelas->valor + $juros;
            
            $sql_edicao = sprintf("UPDATE ipi_titulos_parcelas SET data_pagamento = '%s', juros = '%s', valor_total = '%s', forma_pagamento = '%s', cheque_numero = '%s', cheque_favorecido = '%s', documento_numero = '%s', cod_bancos_destino = '%s', situacao = 'PAGO' WHERE cod_titulos_parcelas = '$cod_titulos_parcelas'", 
                            data2bd($pagamento), $juros, $valor_total, $forma_pagamento, $cheque_numero, $cheque_favorecido, $documento_numero, $cod_bancos);
            /*
            file_put_contents($file, "\n SQL SETAR PARCELA COMO PAGO \n".$sql_edicao, FILE_APPEND);
            if(mysql_error($conexao)!="")
            {
                file_put_contents($file, "ERRO ".mysql_errno($conexao) . ": " . mysql_error($conexao) . "\n",FILE_APPEND);
            }
            */
            
            if(mysql_query($sql_edicao))
            {
                $arr_retorno = array('resposta' => 'OK');
            }
            else
            {
                $arr_retorno = array('resposta' => 'ERRO', 'debug' => $sql_edicao);   
            }
            
            desconectabd($conexao);
            
            echo json_encode($arr_retorno);
        }
        
        break;
}

?>
