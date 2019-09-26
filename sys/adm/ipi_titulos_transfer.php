<?php

/**
 * Cadastro Títulos de Transferencia entre Contas.
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

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Transferência entre Bancos/Caixa');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_titulos';
$tabela = 'ipi_titulos';

switch ($acao)
{
    case 'editar':
        $cod_titulos_subcategorias = validaVarPost('cod_titulos_subcategorias');
        $descricao = validaVarPost('descricao');
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $cod_bancos_origem = validaVarPost('cod_bancos_origem');
        $cod_bancos_destino = validaVarPost('cod_bancos_destino');
        $vencimento = validaVarPost('vencimento');
        $valor = validaVarPost('valor');
        $mes_ref_entrada = validaVarPost('mes_ref');
        
        $conexao = conectabd();
        
        $res_transfer = true;
        
        // Origem
        if($res_transfer)
        {
            $sql_edicao = sprintf("INSERT INTO ipi_titulos (cod_pizzarias, cod_titulos_subcategorias, cod_colaboradores, cod_fornecedores, cod_entregadores, total_parcelas, tipo_titulo, descricao) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', 'TRANSFER', '%s')", 
                                $cod_pizzarias, $cod_titulos_subcategorias, 0, 0, 0, 1, $descricao);

            $res_transfer &= mysql_query($sql_edicao);

            if($res_transfer)
            {
                $codigo = mysql_insert_id();
                
                preg_match('/([0-9]{2})\/([0-9]{4})/', $mes_ref_entrada, $arr_match_data);
                
                $mes_ref = $arr_match_data[1];
                $ano_ref = $arr_match_data[2];
                
                $sql_edicao_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos, data_vencimento, data_pagamento, valor, juros, valor_total, numero_parcela, mes_ref, ano_ref, situacao, cod_bancos_destino) VALUES ('%s', '%s', NOW(), '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                                        $codigo, data2bd($vencimento), (moeda2bd($valor) * -1), 0, (moeda2bd($valor) * -1), 1, $mes_ref, $ano_ref, 'PAGO', $cod_bancos_origem);

                $res_transfer &= mysql_query($sql_edicao_parcelas);
            }
        }
        
        // Destino
        if($res_transfer)
        {
            $sql_edicao = sprintf("INSERT INTO ipi_titulos (cod_pizzarias, cod_titulos_subcategorias, cod_colaboradores, cod_fornecedores, cod_entregadores, total_parcelas, tipo_titulo, descricao) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', 'TRANSFER', '%s')", 
                                $cod_pizzarias, $cod_titulos_subcategorias, 0, 0, 0, 1, $descricao);

            $res_transfer &= mysql_query($sql_edicao);

            if($res_transfer)
            {
                $codigo = mysql_insert_id();
                
                preg_match('/([0-9]{2})\/([0-9]{4})/', $mes_ref_entrada, $arr_match_data);
                
                $mes_ref = $arr_match_data[1];
                $ano_ref = $arr_match_data[2];
                
                $sql_edicao_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos, data_vencimento, data_pagamento, valor, juros, valor_total, numero_parcela, mes_ref, ano_ref, situacao, cod_bancos_destino) VALUES ('%s', '%s', NOW(), '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                                        $codigo, data2bd($vencimento), moeda2bd($valor), 0, moeda2bd($valor), 1, $mes_ref, $ano_ref, 'PAGO', $cod_bancos_destino);

                $res_transfer &= mysql_query($sql_edicao_parcelas);
            }
        }
        
        if($res_transfer)
        {
            mensagemOk('Registro adicionado com êxito!');
        }
        else
        {
            mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        
        desconectabd($conexao);
        
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />
<script src="../lib/js/calendario.js" type="text/javascript"></script>

<script>

window.addEvent('domready', function()
{
    // DatePick
    new vlaDatePicker('vencimento', {openWith: 'botao_vencimento', prefillDate: false});
});

</script>
    
<form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr>
        <td class="legenda tdbl tdbt tdbr">
        	<label for="cod_titulos_subcategorias" class="requerido">Lançamento</label>
        </td>
    </tr>
   	<tr>
		<td class="tdbl tdbr sep">
        	<select name="cod_titulos_subcategorias" id="cod_titulos_subcategorias" class="requerido" style="width: 300px;">
        		<option value=""></option>
        		
        		<?

        		$conexao = conectabd();
        		
                $sql_buscar_categorias = "SELECT * FROM ipi_titulos_categorias WHERE cod_titulos_categorias IN (SELECT cod_titulos_categorias FROM ipi_titulos_subcategorias WHERE tipo_titulo = 'TRANSFER') ORDER BY titulos_categoria";
                $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                
                while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                {
                    echo '<optgroup label="' . bd2texto($obj_buscar_categorias->titulos_categoria) . '">';
                    
                    $sql_buscar_subcategorias = "SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = '" . $obj_buscar_categorias->cod_titulos_categorias . "' AND tipo_titulo = 'TRANSFER' ORDER BY titulos_subcategorias";
                    $res_buscar_subcategorias = mysql_query($sql_buscar_subcategorias);
                    
                    while($obj_buscar_subcategorias = mysql_fetch_object($res_buscar_subcategorias))
                    {
                        echo '<option value="' . $obj_buscar_subcategorias->cod_titulos_subcategorias . '">' . bd2texto($obj_buscar_subcategorias->titulos_subcategorias) . '</option>';
                    }
                    
                    echo '</optgroup>';
                }
        		
        		desconectabd($conexao);
        		
        		?>
        	</select>
    	</td>
	</tr>
	
    <tr>
        <td class="tdbr tdbl legenda">
        	<label for="descricao" class="requerido">Descrição</label>
    	</td>
    </tr>
	<tr>
        <td class="tdbr tdbl sep">
        	<input type="text" name="descricao" id="descricao" style="width: 295px;" class="requerido">
		</tr>            
	</td>
    
    <tr>
        <td class="legenda tdbl tdbr">
        	<label for="cod_pizzarias" class="requerido"><? echo ucfirst(TIPO_EMPRESA)?></label>
        </td>
    </tr>
   	<tr>
		<td class="tdbl tdbr sep">
        	<select name="cod_pizzarias" id="cod_pizzarias" class="requerido" style="width: 300px;">
        		<option value=""></option>
        		
        		<?

        		$conexao = conectabd();
        		
                $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";
                $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
                
                while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
                {
                    echo '<option value="' . $obj_buscar_pizzarias->cod_pizzarias . '">' . bd2texto($obj_buscar_pizzarias->nome) . '</option>';
                }
        		
        		desconectabd($conexao);
        		
        		?>
        	</select>
    	</td>
	</tr>
    
    <tr>
    	<td class="tdbr tdbl legenda"><label for="cod_bancos_origem" class="requerido">Banco/Caixa Origem</label></td>
    </tr>
    <tr>
        <td class="tdbr tdbl sep">
            <select name="cod_bancos_origem" id="cod_bancos_origem" class="requerido" style="width: 300px;">
            	<option value=""></option>
            	
            	<?

            	$conexao = conectabd();
            	
            	$sql_buscar_bancos = "SELECT * FROM ipi_bancos WHERE cod_bancos IN (SELECT cod_bancos FROM ipi_bancos_ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")) ORDER BY banco";
            	$res_buscar_bancos = mysql_query($sql_buscar_bancos);
            	
            	while($obj_buscar_bancos = mysql_fetch_object($res_buscar_bancos))
            	{
                    echo '<option value="' . $obj_buscar_bancos->cod_bancos . '">';
                    
                	echo bd2texto($obj_buscar_bancos->banco); 
                	
                	if(!$obj_buscar_bancos->caixa)
                	{
                		echo ' - AG: ' . bd2texto($obj_buscar_bancos->agencia)  . ' - C/C: ' . bd2texto($obj_buscar_bancos->conta_corrente);
                	}
                    
                    echo '</option>';                	       
            	}
            	
            	desconectabd();
            	
            	?>
            	
            </select>
        </td>
    </tr>
    
    <tr>
    	<td class="tdbr tdbl legenda"><label for="cod_bancos_destino" class="requerido">Banco/Caixa Destino</label></td>
    </tr>
    <tr>
        <td class="tdbr tdbl sep">
            <select name="cod_bancos_destino" id="cod_bancos_destino" class="requerido" style="width: 300px;">
            	<option value=""></option>
            	
            	<?

            	$conexao = conectabd();
            	
            	$sql_buscar_bancos = "SELECT * FROM ipi_bancos WHERE cod_bancos IN (SELECT cod_bancos FROM ipi_bancos_ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")) ORDER BY banco";
            	$res_buscar_bancos = mysql_query($sql_buscar_bancos);
            	
            	while($obj_buscar_bancos = mysql_fetch_object($res_buscar_bancos))
            	{
                    echo '<option value="' . $obj_buscar_bancos->cod_bancos . '">';

                	echo bd2texto($obj_buscar_bancos->banco); 
                	
                	if(!$obj_buscar_bancos->caixa)
                	{
                		echo ' - AG: ' . bd2texto($obj_buscar_bancos->agencia)  . ' - C/C: ' . bd2texto($obj_buscar_bancos->conta_corrente);
                	}
                    
                    echo '</option>';                	       
            	}
            	
            	desconectabd();
            	
            	?>
            	
            </select>
        </td>
    </tr>
    
    <tr>
        <td class="tdbl tdbr legenda">
        	<label for="vencimento" class="requerido"><? echo utf8_encode('Data de Vencimento') ?></label>
        	<label for="valor" class="requerido" style="margin-left: 40px;">Valor</label>
        	<label for="mes_ref" class="requerido" style="margin-left: 87px;">Ref. (MM/AAAA)</label>
    	</td>
    </tr>
	<tr>
        <td class="tdbl tdbr sep">
        	<input type="text" name="vencimento" id="vencimento" maxlength="10" size="16" class="requerido" value="<? echo date('d/m/Y'); ?>" onkeypress="return MascaraData(this, event)">
        	&nbsp;
        	<a href="javascript:;" id="botao_vencimento"><img src="../lib/img/principal/botao-data.gif"></a>
        	
        	<input type="text" name="valor" id="valor" size="15" style="margin-left: 10px;" class="requerido" onkeypress="return formataMoeda(this, '.', ',', event)">
        	<input type="text" name="mes_ref" id="mes_ref" size="11" style="margin-left: 10px;" class="requerido" value="<?  echo date('m/Y'); ?>" onkeypress="return Mascara(this, event, '##/####')">
        </td>
    </tr>
    
    <tr>
        <td align="center" class="tdbl tdbb tdbr">
            <input name="botao_submit" class="botao" type="submit" value="Cadastrar">
        </td>
    </tr>
    
    </table>

<input type="hidden" name="acao" value="editar"> 
</form>

<?
rodape();
?>
