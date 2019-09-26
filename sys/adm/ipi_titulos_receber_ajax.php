<?php

/**
 * Cadastro Títulos a receber (ajax).
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       08/12/2009   FELIPE        Criado.
 *
 */

session_start();

require_once '../../bd.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

switch ($acao)
{
    case 'criar_formulario_edicao':
        $cod_titulos = validaVarPost('cod_titulos');
        $cod_titulos_subcategorias = validaVarPost('cod_titulos_subcategorias');
        
        $conexao = conectabd();
        
        if($cod_titulos > 0)
        {
            $obj_titulos = executaBuscaSimples("SELECT * FROM ipi_titulos WHERE cod_titulos = '$cod_titulos'", $conexao);
        }
        
        $obj_detalhes_subcategoria = executaBuscaSimples("SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_subcategorias = '" . $cod_titulos_subcategorias . "'", $conexao);
        
        desconectabd($conexao);
        
        ?>
        
        <table cellpadding="0" cellspacing="0">
        
        <? if ($obj_detalhes_subcategoria->tipo_cendente_sacado == 'CLIENTE'): ?>
        
        <tr>
            <td class="legenda">
            	<label for="cod_clientes">Cliente</label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<select name="cod_clientes" id="cod_clientes" style="width: 300px;">
                	<option value=""></option>
                	
                	<?
    
                	$conexao = conectabd();
                	
                	$sql_buscar_clientes = "SELECT * FROM ipi_clientes ORDER BY nome";
                	$res_buscar_clientes = mysql_query($sql_buscar_clientes);
                	
                	while($obj_buscar_clientes = mysql_fetch_object($res_buscar_clientes))
                	{
                	    echo '<option value="' . $obj_buscar_clientes->cod_clientes . '" ';
                	    
                	    if($obj_titulos->cod_clientes == $obj_buscar_clientes->cod_clientes)
                	    {
                	        echo 'selected';   
                	    }
                	    
                	    echo '>' . utf8_encode(bd2texto($obj_buscar_clientes->nome)) . '</option>';
                	}
                	
                	desconectabd();
                	
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
    
                	$conexao = conectabd();
                	
                	$sql_buscar_tipo_colaboradores = "SELECT * FROM ipi_tipo_colaboradores WHERE cod_tipo_colaboradores IN (SELECT cod_tipo_colaboradores FROM ipi_colaboradores WHERE situacao = 'ATIVO') ORDER BY tipo_colaboradores";
                	$res_buscar_tipo_colaboradores = mysql_query($sql_buscar_tipo_colaboradores);
                	
                	while($obj_buscar_tipo_colaboradores = mysql_fetch_object($res_buscar_tipo_colaboradores))
                	{
                	    echo '<optgroup label="' . utf8_encode(bd2texto($obj_buscar_tipo_colaboradores->tipo_colaboradores)) . '">';
                	    
                    	$sql_buscar_colaboradores = "SELECT * FROM ipi_colaboradores WHERE cod_tipo_colaboradores = '" . $obj_buscar_tipo_colaboradores->cod_tipo_colaboradores . "' AND situacao = 'ATIVO' ORDER BY nome";
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
                	
                	desconectabd();
                	
                	?>
            	
            	</select>
			</tr>            
		</td>
		
		<? elseif ($obj_detalhes_subcategoria->tipo_cendente_sacado == 'ENTREGADOR'): ?>
        
        <tr>
            <td class="legenda">
            	<label for="cod_entregadores">Entregadores</label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<select name="cod_entregadores" id="cod_entregadores" style="width: 300px;">
                	<option value=""></option>
                	
                	<?
    
                	$conexao = conectabd();
                	
                	$sql_buscar_entregadores = "SELECT * FROM ipi_entregadores WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";
                	$res_buscar_entregadores = mysql_query($sql_buscar_entregadores);
                	
                	while($obj_buscar_entregadores = mysql_fetch_object($res_buscar_entregadores))
                	{
                	    echo '<option value="' . $obj_buscar_entregadores->cod_entregadores . '" ';
                	    
                	    if($obj_titulos->cod_entregadores == $obj_buscar_entregadores->cod_entregadores)
                	    {
                	        echo 'selected';   
                	    }
                	    
                	    echo '>' . utf8_encode(bd2texto($obj_buscar_entregadores->nome)) . '</option>';
                	}
                	
                	desconectabd();
                	
                	?>
            	
            	</select>
			</tr>            
		</td>
        
        <? elseif ($obj_detalhes_subcategoria->tipo_cendente_sacado == 'FORNECEDOR'): ?>
        
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
    
                	$conexao = conectabd();
                	
                	$sql_buscar_fornecedores = "SELECT * FROM ipi_fornecedores WHERE situacao = 'ATIVO' ORDER BY nome_fantasia";
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
                    	
                	desconectabd();
                	
                	?>
            	
            	</select>
			</tr>            
		</td>
        
        <? endif; ?>
        
        <? 
        
        if ($obj_detalhes_subcategoria->num_parcelas_maximo == 1): 
        
        $obj_titulos_parcelas = executaBuscaSimples("SELECT * FROM ipi_titulos_parcelas WHERE cod_titulos = '" . $obj_titulos->cod_titulos . "'");
        
        ?>
        
        <tr>
            <td class="legenda">
                <label for="vencimento"><? echo utf8_encode('Data Vencimento') ?></label>
                <label for="emissao" style="margin-left: 28px;"><? echo utf8_encode('Data emissão') ?></label>
                <label for="valor" style="margin-left: 45px;"><? echo utf8_encode('Valor') ?></label>
                <label for="mes_ref" style="margin-left: 105px;"><? echo utf8_encode('Ref. (MM/AAAA)') ?></label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<input type="text" name="vencimento" id="vencimento" maxlength="10" size="16" value="<? echo ($obj_titulos_parcelas->cod_titulos_parcelas > 0) ? bd2data($obj_titulos_parcelas->data_vencimento) : date('d/m/Y'); ?>" onkeypress="return MascaraData(this, event)">
            	&nbsp;
            	<a href="javascript:;" id="botao_vencimento"><img src="../lib/img/principal/botao-data.gif"></a>
            	
                <input type="text" name="emissao" id="emissao" maxlength="10" size="10" style="margin-left: 10px;" value="<? echo ($obj_titulos_parcelas->cod_titulos_parcelas > 0) ? bd2data($obj_titulos_parcelas->data_emissao) : date('d/m/Y'); ?>" onkeypress="return MascaraData(this, event)">
                &nbsp;
                <a href="javascript:;" id="botao_emissao"><img src="../lib/img/principal/botao-data.gif"></a>

            	<input type="text" name="valor" id="valor" size="15" style="margin-left: 10px;" value="<? echo bd2moeda($obj_titulos_parcelas->valor) ?>" onkeypress="return formataMoeda(this, '.', ',', event)">
            	<input type="text" name="mes_ref" id="mes_ref" size="11" style="margin-left: 10px;" value="<?  echo (($obj_titulos_parcelas->mes_ref != '') && ($obj_titulos_parcelas->ano_ref != '')) ? sprintf('%02d', $obj_titulos_parcelas->mes_ref) . '/' . $obj_titulos_parcelas->ano_ref : date('m/Y'); ?>" onkeypress="return Mascara(this, event, '##/####')">
            </td>
        </tr>
        
        <script>new vlaDatePicker('vencimento', {openWith: 'botao_vencimento', prefillDate: false});
        new vlaDatePicker('emissao', {openWith: 'botao_emissao', prefillDate: false});
        </script>
        
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
        $cod_titulos = validaVarPost('cod_titulos');
        $num_parcelas = validaVarPost('num_parcelas');
        
        echo '<br>';
        
        for($i = 1; $i <= $num_parcelas; $i++):
        
        $obj_buscar_titulos_parcelas = executaBuscaSimples("SELECT * FROM ipi_titulos_parcelas WHERE cod_titulos = '$cod_titulos' AND numero_parcela = '$i'");
        
        ?>
        
        <br>
        
        <label>Parcela <? echo $i ?></label>
        <hr color="#1A498F" size="1" noshade="noshade">
        
        <table style="margin-top: 10px;" cellpadding="0" cellspacing="0">
        
        <tr>
            <td class="legenda">
                <label for="vencimento[]"><? echo utf8_encode('Data Vencimento') ?></label>
                <label for="emissao[]" style="margin-left: 28px;"><? echo utf8_encode('Data emissão') ?></label>
                <label for="valor[]" style="margin-left: 45px;"><? echo utf8_encode('Valor') ?></label>
                <label for="mes_ref[]" style="margin-left: 105px;"><? echo utf8_encode('Ref. (MM/AAAA)') ?></label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<input type="text" name="vencimento[]" id="vencimento<? echo $i ?>" maxlength="10" size="10" value="<? echo bd2data($obj_buscar_titulos_parcelas->data_vencimento); ?>" onkeypress="return MascaraData(this, event)">
            	&nbsp;
            	<a href="javascript:;" id="botao_vencimento<? echo $i ?>"><img src="../lib/img/principal/botao-data.gif"></a>
                
                <input type="text" name="emissao[]" id="emissao<? echo $i ?>" <? echo $readonly; ?> style="margin-left: 10px;" maxlength="10" size="10" value="<? echo bd2data($obj_buscar_titulos_parcelas->data_emissao); ?>" <? if($readonly =='') echo 'onkeypress="return MascaraData(this, event)"' ;?>>
                &nbsp;
                <a href="javascript:;" <? echo $readonly; ?> id="botao_emissao<? echo $i ?>"><img src="../lib/img/principal/botao-data.gif"></a>

            	<input type="text" name="valor[]" id="valor" size="15" style="margin-left: 10px;" value="<? echo bd2moeda($obj_buscar_titulos_parcelas->valor) ?>" onkeypress="return formataMoeda(this, '.', ',', event)">
            	<input type="text" name="mes_ref[]" id="mes_ref" size="11" style="margin-left: 10px;" value="<?  echo (($obj_buscar_titulos_parcelas->mes_ref != '') && ($obj_buscar_titulos_parcelas->ano_ref != '')) ? sprintf('%02d', $obj_buscar_titulos_parcelas->mes_ref) . '/' . $obj_buscar_titulos_parcelas->ano_ref : ''; ?>" onkeypress="return Mascara(this, event, '##/####')">
            </td>
        </tr>
        
        </table>
        
        <input type="hidden" name="cod_titulos_parcelas[]" value="<? echo $obj_buscar_titulos_parcelas->cod_titulos_parcelas; ?>">
        
        <?
        
        endfor;
        
        //echo "Num parcelas: $num_parcelas";
        
        echo "<script>for (x = 1; x <= " . $num_parcelas . "; x++) { new vlaDatePicker('vencimento' + x, {openWith: 'botao_vencimento' + x, prefillDate: false}); new vlaDatePicker('emissao' + x, {openWith: 'botao_emissao' + x, prefillDate: false}); }</script>";
        
        break;
}

?>
