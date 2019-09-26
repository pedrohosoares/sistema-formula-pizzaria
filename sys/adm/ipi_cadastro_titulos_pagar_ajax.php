<?php

/**
 * Cadastro Títulos a pagar (ajax).
 *
 * @version 1.0
 * @package gerencial
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       08/12/2009   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formulario.php';

$acao = validar_var_post('acao');

switch ($acao)
{
    case 'criar_formulario_edicao':
        $cod_titulos = validar_var_post('cod_titulos');
        $cod_titulos_subcategorias = validar_var_post('cod_titulos_subcategorias');
        
        $conexao = conectar_bd();
        
        if($cod_titulos > 0)
        {
            $obj_titulos = executar_busca_simples("SELECT * FROM ger_titulos WHERE cod_titulos = '$cod_titulos'", $conexao);
        }
        
        $obj_detalhes_subcategoria = executar_busca_simples("SELECT * FROM ger_titulos_subcategorias WHERE cod_titulos_subcategorias = '" . $cod_titulos_subcategorias . "'", $conexao);
        
        desconectar_bd($conexao);
        
        ?>
        
        <table cellpadding="0" cellspacing="0">
        
        <? if ($obj_detalhes_subcategoria->tipo_cendente_sacado == 'FORNECEDOR'): ?>
        
        <tr>
            <td class="legenda">
            	<label for=cod_fornecedores>Fornecedor</label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<select name="cod_fornecedores" id="cod_fornecedores" style="width: 300px;">
                	<option value=""></option>
                	
                	<?
    
                	$conexao = conectar_bd();
                	
                	$sql_buscar_categorias_fornecedores = "SELECT * FROM ger_fornecedores_categorias WHERE cod_fornecedores_categorias IN (SELECT cod_fornecedores_categorias FROM ger_fornecedores) ORDER BY fornecedores_categoria";
                	$res_buscar_categorias_fornecedores = mysql_query($sql_buscar_categorias_fornecedores);
                	
                	while($obj_buscar_categorias_fornecedores = mysql_fetch_object($res_buscar_categorias_fornecedores))
                	{
                	    echo '<optgroup label="' . utf8_encode(bd2texto($obj_buscar_categorias_fornecedores->fornecedores_categoria)) . '">';
                	    
                    	$sql_buscar_fornecedores = "SELECT * FROM ger_fornecedores WHERE cod_fornecedores_categorias = '" . $obj_buscar_categorias_fornecedores->cod_fornecedores_categorias . "' ORDER BY nome_fantasia";
                    	$res_buscar_fornecedores = mysql_query($sql_buscar_fornecedores);
                    	
                    	while($obj_buscar_fornecedores = mysql_fetch_object($res_buscar_fornecedores))
                    	{
                    	    echo '<option value="' . $obj_buscar_fornecedores->cod_fornecedores . '" ';
                    	    
                    	    if($obj_titulos->cod_fornecedores == $obj_buscar_fornecedores->cod_fornecedores)
                    	    {
                    	        echo 'selected';   
                    	    }
                    	    
                    	    echo '>' . utf8_encode(bd2texto($obj_buscar_fornecedores->nome_fantasia)) . '</option>';
                    	}
                    	
                    	echo '</optgroup>';
                	}
                	
                	desconectar_bd();
                	
                	?>
            	
            	</select>
			</tr>            
		</td>
        
        <? elseif ($obj_detalhes_subcategoria->tipo_cendente_sacado == 'COLABORADOR'): ?>
        
        <tr>
            <td class="legenda">
            	<label for=cod_colaboradores>Colaboradores</label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<select name="cod_colaboradores" id="cod_colaboradores" style="width: 300px;">
                	<option value=""></option>
                	
                	<?
    
                	$conexao = conectar_bd();
                	
                	$sql_buscar_tipo_colaboradores = "SELECT * FROM ger_tipo_colaboradores WHERE cod_tipo_colaboradores IN (SELECT cod_tipo_colaboradores FROM ger_colaboradores) ORDER BY tipo_colaboradores";
                	$res_buscar_tipo_colaboradores = mysql_query($sql_buscar_tipo_colaboradores);
                	
                	while($obj_buscar_tipo_colaboradores = mysql_fetch_object($res_buscar_tipo_colaboradores))
                	{
                	    echo '<optgroup label="' . utf8_encode(bd2texto($obj_buscar_tipo_colaboradores->tipo_colaboradores)) . '">';
                	    
                    	$sql_buscar_colaboradores = "SELECT * FROM ger_colaboradores WHERE cod_tipo_colaboradores = '" . $obj_buscar_tipo_colaboradores->cod_tipo_colaboradores . "' ORDER BY nome";
                    	$res_buscar_colaboradores = mysql_query($sql_buscar_colaboradores);
                    	
                    	while($obj_buscar_colaboradores = mysql_fetch_object($res_buscar_colaboradores))
                    	{
                    	    echo '<option value="' . $obj_buscar_colaboradores->cod_colaboradores . '" ';
                    	    
                    	    if($obj_titulos->cod_colaboradores == $obj_buscar_colaboradores->cod_colaboradores)
                    	    {
                    	        echo 'selected';   
                    	    }
                    	    
                    	    echo '>' . utf8_encode(bd2texto($obj_buscar_colaboradores->nome)) . '</option>';
                    	}
                    	
                    	echo '</optgroup>';
                	}
                	
                	desconectar_bd();
                	
                	?>
            	
            	</select>
			</tr>            
		</td>
        
        <? endif; ?>
        
        <? 
        
        if ($obj_detalhes_subcategoria->num_parcelas_maximo == 1): 
        
        $obj_titulos_parcelas = executar_busca_simples("SELECT * FROM ger_titulos_parcelas WHERE cod_titulos = '" . $obj_titulos->cod_titulos . "'", $conexao);
        
        ?>
        
        <tr>
            <td class="legenda">
            	<label for="vencimento"><? echo utf8_encode('Data de Vencimento') ?></label>
            	<label for="valor" style="margin-left: 50px;"><? echo utf8_encode('Valor') ?></label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<input type="text" name="vencimento" id="vencimento" maxlength="10" size="16" value="<? echo ($obj_titulos_parcelas->cod_titulos_parcelas > 0) ? bd2data($obj_titulos_parcelas->data_vencimento) : date('d/m/Y'); ?>" onkeypress="return MascaraData(this, event)">
            	&nbsp;
            	<a href="javascript:;" id="botao_vencimento"><img src="../lib/img/principal/botao-data.gif"></a>
            	
            	<input type="text" name="valor" id="valor" size="20" style="margin-left: 25px;" value="<? echo bd2moeda($obj_titulos_parcelas->valor * -1) ?>" onkeypress="return formataMoeda(this, '.', ',', event)">
            </td>
        </tr>
        
        <input type="hidden" name="num_parcelas" value="1">
        
        <? elseif ($obj_detalhes_subcategoria->num_parcelas_maximo > 1): ?>
        
        <tr>
            <td class="legenda">
            	<label for="num_parcelas"><? echo utf8_encode('Parcelas') ?></label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<select name="num_parcelas" id="num_parcelas" style="width: 120px;" onchange="criar_parcelas(this.value, '<? echo $cod_titulos ?>')">
            		<option value=""></option>
            		
            		<?

            		for($i = 1; $i <= $obj_detalhes_subcategoria->num_parcelas_maximo; $i++)
            		{
            	        echo '<option value="' . $i . '" ';
            	        
            	        if($obj_titulos->total_parcelas == $i)
            	        {
            	            echo 'selected';   
            	        }
            	        
            	        echo '>' . $i . 'x</option>';
            		}
            		
            		?>
            		
            	</select>
            </td>
        </tr>
        
        <tr><td id="criar_parcelas"></td></tr>
        
        <? endif; ?>
        
        </table>
        
        <?
        
        break;
    case 'criar_parcelas':
        $cod_titulos = validar_var_post('cod_titulos');
        $num_parcelas = validar_var_post('num_parcelas');
        
        echo '<br>';
        
        for($i = 1; $i <= $num_parcelas; $i++):
        
        $obj_buscar_titulos_parcelas = executar_busca_simples("SELECT * FROM ger_titulos_parcelas WHERE cod_titulos = '$cod_titulos' AND numero_parcela = '$i'");
        
        ?>
        
        <br>
        
        <label>Parcela <? echo $i ?></label>
        <hr color="#1A498F" size="1" noshade="noshade">
        
        <table style="margin-top: 10px;" cellpadding="0" cellspacing="0">
        
        <tr>
            <td class="legenda">
            	<label for="vencimento"><? echo utf8_encode('Data de Vencimento') ?></label>
            	<label for="valor" style="margin-left: 75px;"><? echo utf8_encode('Valor') ?></label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<input type="text" name="vencimento[]" id="vencimento" maxlength="10" size="20" value="<? echo bd2data($obj_buscar_titulos_parcelas->data_vencimento); ?>" onkeypress="return MascaraData(this, event)">
            	&nbsp;
            	<a href="javascript:;" id="botao_vencimento"><img src="../lib/img/principal/botao-data.gif"></a>
            	
            	<input type="text" name="valor[]" id="valor" size="16" style="margin-left: 25px;" value="<? echo bd2moeda($obj_buscar_titulos_parcelas->valor * -1) ?>" onkeypress="return formataMoeda(this, '.', ',', event)">
            </td>
        </tr>
        
        </table>
        
        <input type="hidden" name="cod_titulos_parcelas[]" value="<? echo $obj_buscar_titulos_parcelas->cod_titulos_parcelas; ?>">
        
        <?
        
        endfor;
        
        break;
}

?>