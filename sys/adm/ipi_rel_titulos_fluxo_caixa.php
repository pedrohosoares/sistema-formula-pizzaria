<?php

/**
 * Relatório de Fluxo de Caixa.
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
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Fluxo de Caixa');

$acao = validaVarPost('acao');

$exibir_barra_lateral = false;

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />
<script src="../lib/js/calendario.js" type="text/javascript"></script>

<script>

function editar(cod) 
{
    var form = new Element('form', 
    {
        'action': '<?echo $_SERVER['PHP_SELF']?>',
        'method': 'post'
    });
  
    var input = new Element('input', 
    {
        'type': 'hidden',
        'name': '<?echo $chave_primaria?>',
        'value': cod
    });
  
    input.inject(form);
    $(document.body).adopt(form);
  
    form.submit();
}

function carregar_relatorio()
{
	var data_inicial = document.frmFiltro.data_inicial_filtro.value;
	var data_final = document.frmFiltro.data_final_filtro.value;
	var situacao = document.frmFiltro.situacao_filtro.value;
	var filtrar_por = document.frmFiltro.filtrar_filtro.value;

	var cod_titulos_subcategorias_filtro = document.getElementById('cod_titulos_subcategorias_filtro');
    var cod_titulos_subcategorias_filtro_a = new Array();
    //alert(cod_titulos_subcategorias_filtro.options.length);
   // alert(cod_titulos_subcategorias_filtro.ite);
    var todos = false;
    for(var c=0;c<cod_titulos_subcategorias_filtro.options.length;c++)
    {
        if(cod_titulos_subcategorias_filtro.item(c).selected)
        {
            cod_titulos_subcategorias_filtro_a.push(cod_titulos_subcategorias_filtro.item(c).value);
            if(cod_titulos_subcategorias_filtro.item(c).value=="TODOS")
            {
                todos = true;
            }
        }
    }  
    if(todos)
    {
        var todos_a = new Array();
        todos_a.push("TODOS");
        cod_titulos_subcategorias_filtro_a = todos_a;
    }
	var cod_bancos_filtro = document.frmFiltro.cod_bancos_filtro.value;
	//alert(cod_titulos_subcategorias_filtro);
	var pagamentos_filtro = document.frmFiltro.pagamentos_filtro.checked;
	var recebimentos_filtro = document.frmFiltro.recebimentos_filtro.checked;
	var transfer_filtro = document.frmFiltro.transfer_filtro.checked;
	
	var cod_pizzarias = document.frmFiltro.cod_pizzarias.value;
	//alert(cod_titulos_subcategorias_filtro_a);
    if(cod_titulos_subcategorias_filtro_a!="")
    {
    	var url = "acao=carregar_relatorio&data_inicial=" + data_inicial + "&data_final=" + data_final + "&situacao=" + situacao + "&pagamentos_filtro=" + pagamentos_filtro + "&recebimentos_filtro=" + recebimentos_filtro + "&transfer_filtro=" + transfer_filtro + "&cod_titulos_subcategorias_filtro=" + cod_titulos_subcategorias_filtro_a + "&cod_bancos_filtro=" + cod_bancos_filtro + "&cod_pizzarias=" + cod_pizzarias + "&filtrar_por="+filtrar_por;
        //alert(url);
        $('relatorio').innerHTML = "<div align='center'>Carregando...<br / ><img src='../lib/img/principal/ajax_loader_barra.gif' style='width:200;height:20' ></img></div>"; //sys/lib/img/principal/ajax_loader_barrra.gif
        new Request.HTML(
        {
            url: 'ipi_rel_titulos_fluxo_caixa_ajax.php',
            update: $('relatorio')
        }).send(url);
    }
    else
    {
        alert("Por favor selecione no minimo uma subcategoria");
    }
}

function exibir_baixar_parcela(cod_titulos_parcelas)
{
	var iebody = (document.compatMode && document.compatMode != "BackCompat") ? document.documentElement : document.body;
    var dsoctop = document.all ? iebody.scrollTop : pageYOffset;
    
    var divFundo = new Element('div', 
    {
        'id': 'divFundo',
        'styles': 
        {
            'position': 'absolute',
            'top': 0,
            'left': 0,
            'height': document.documentElement.clientHeight + dsoctop,
            'width':  document.documentElement.clientWidth,
            'z-index': 1,
            'background-color': '#FFFFFF'
        }
    });
    
    var divMsg = new Element('div', 
    {
        'id': 'divMsg',
        'styles': 
        {
            'position': 'absolute',
            'left': (document.body.clientWidth - 300) / 2,
            'background-color': '#ffffff',
            'border': '2px solid #1A498F',
            'width' : 300,
            'height': 460,
            'padding': 20,
            'z-index': 2,
            'overflow': 'hidden'
        }
    });
    
    var win = window;
    var middle = win.getScrollTop() + (win.getHeight() / 2);
    var top = Math.max(0, middle - (450 / 2));
              
    divMsg.setStyle('top', top);
    
    var url = "acao=exibir_baixar_parcela&cod_titulos_parcelas=" + cod_titulos_parcelas;
    
    new Request.HTML(
    {
        url: 'ipi_rel_titulos_fluxo_caixa_ajax.php',
        update: divMsg
    }).send(url);
    
    divFundo.setStyle('opacity', 0.7);
    
    $(document.body).adopt(divMsg);
    $(document.body).adopt(divFundo);
}

function baixar(cod_titulos_parcelas)
{
	var pagamento = $('pagamento').getProperty('value');
	var juros = $('juros').getProperty('value');
	var forma_pagamento = $('forma_pagamento').getProperty('value');
	var documento_numero = $('documento_numero').getProperty('value');
	var cheque_numero = $('cheque_numero').getProperty('value');
	var cheque_favorecido = $('cheque_favorecido').getProperty('value');
	var cod_bancos = $('cod_bancos').getProperty('value');
	
	var url = 'acao=baixar&cod_titulos_parcelas=' + cod_titulos_parcelas + '&pagamento=' + pagamento + '&juros=' + juros + '&forma_pagamento=' + forma_pagamento + '&documento_numero=' + documento_numero + '&cheque_numero=' + cheque_numero + '&cheque_favorecido=' + cheque_favorecido + '&cod_bancos=' + cod_bancos;

	new Request.JSON({
		url: 'ipi_rel_titulos_fluxo_caixa_ajax.php', 
		onSuccess: function(retorno) 
		{
			if(retorno.resposta == 'OK')
			{
    			carregar_relatorio();
    			$('divMsg').destroy();
        		$('divFundo').destroy();
    		}
    		else
    		{
    			alert('Erro ao baixar a parcela: ' + cod_titulos_parcelas + '.');
    		}
        }
    }).send(url);
}

function exibir_forma_pagamento_baixa(forma_pagamento)
{
	switch(forma_pagamento)
	{
		case 'DINHEIRO':
			
			$('documento_numero_label_tr').setStyle('display', 'none');
    		$('documento_numero_tr').setStyle('display', 'none');
    		
    		$('cheque_numero_label_tr').setStyle('display', 'none');
    		$('cheque_numero_tr').setStyle('display', 'none');
    		
    		$('cheque_favorecido_label_tr').setStyle('display', 'none');
    		$('cheque_favorecido_tr').setStyle('display', 'none');
    		
			break;
		case 'BOLETO':
		case 'DEPOSITO':
			
			$('documento_numero_label_tr').setStyle('display', 'block');
    		$('documento_numero_tr').setStyle('display', 'block');
    		
    		$('cheque_numero_label_tr').setStyle('display', 'none');
    		$('cheque_numero_tr').setStyle('display', 'none');
    		
    		$('cheque_favorecido_label_tr').setStyle('display', 'none');
    		$('cheque_favorecido_tr').setStyle('display', 'none');
    		
			break;
		case 'CHEQUE':
			
			$('documento_numero_label_tr').setStyle('display', 'none');
    		$('documento_numero_tr').setStyle('display', 'none');
    		
    		$('cheque_numero_label_tr').setStyle('display', 'block');
    		$('cheque_numero_tr').setStyle('display', 'block');
    		
    		$('cheque_favorecido_label_tr').setStyle('display', 'block');
    		$('cheque_favorecido_tr').setStyle('display', 'block');
    		
			break;
	}
}

function cancelar()
{
    $('divMsg').destroy();
    $('divFundo').destroy();
}

window.addEvent('domready', function()
{
	// DatePick
    new vlaDatePicker('data_inicial_filtro', {openWith: 'botao_data_inicial_filtro', prefillDate: false});
    new vlaDatePicker('data_final_filtro', {openWith: 'botao_data_final_filtro', prefillDate: false});
    
    
    // O fluxo de caixa no momento do carregamento fica muito pesado.
	//carregar_relatorio();
});

</script>

<? if ($exibir_barra_lateral): ?>

<table>
    <tr>
        <!-- Conteúdo -->
        <td class="conteudo">

		<? endif; ?>

        <?
        
        $data_inicial_filtro = (validaVarPost('data_inicial_filtro')) ? validaVarPost('data_inicial_filtro') : date("01/m/Y");
        $data_final_filtro = (validaVarPost('data_final_filtro')) ? validaVarPost('data_final_filtro') : date("t/m/Y", mktime(0, 0, 0, date('m'), 1, date('Y')));
        $filtrar_por = validaVarPost("filtrar_filtro");
        
        ?>
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

            <tr>
                <td class="legenda tdbl tdbt" align="right"><label for="data_inicial_filtro">Data Inicial:</label></td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr">
                	<input class="requerido" type="text" name="data_inicial_filtro" id="data_inicial_filtro" size="10" value="<? echo $data_inicial_filtro ?>" onkeypress="return MascaraData(this, event)">
                	&nbsp;<a href="javascript:;" id="botao_data_inicial_filtro"><img src="../lib/img/principal/botao-data.gif"></a>
            	</td>
            </tr>
            
            <tr>
                <td class="legenda tdbl " align="right"><label for="data_final_filtro">Data Final:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr ">
                	<input class="requerido" type="text" name="data_final_filtro" id="data_final_filtro" size="10" value="<? echo $data_final_filtro ?>" onkeypress="return MascaraData(this, event)">
                	&nbsp;
                	<a href="javascript:;" id="botao_data_final_filtro"><img src="../lib/img/principal/botao-data.gif"></a>
            	</td>
            </tr>
            
            <!-- 
            
            <tr>
                <td class="legenda tdbl" align="right"><label for="cod_clientes_filtro">Cliente:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr">
                	<select name="cod_clientes_filtro" style="width: 250px;">
                    	<option value=""></option>
                    	
                    	<?
    /*
                    	$conexao = conectabd();
                    	
                    	$sql_buscar_clientes = "SELECT * FROM ipi_clientes ORDER BY nome_fantasia";
                    	$res_buscar_clientes = mysql_query($sql_buscar_clientes);
                    	
                    	while($obj_buscar_clientes = mysql_fetch_object($res_buscar_clientes))
                    	{
                    	    echo '<option value="' . $obj_buscar_clientes->cod_clientes . '">' . bd2texto($obj_buscar_clientes->nome_fantasia) . '</option>';
                    	}
                    	
                    	desconectabd($conexao);*/
                    	
                    	?>
                    	
                	</select>
            	</td>
            </tr>
            
            <tr>
                <td class="legenda tdbl" align="right"><label for="cod_colaboradores_filtro">Colaborador:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr">
                	<select name="cod_colaboradores_filtro" style="width: 250px;">
                    	<option value=""></option>
                    	
                    	<?
    /*
                    	$conexao = conectabd();
                    	
                    	$sql_buscar_colaboradores = "SELECT * FROM ipi_colaboradores ORDER BY nome";
                    	$res_buscar_colaboradores = mysql_query($sql_buscar_colaboradores);
                    	
                    	while($obj_buscar_colaboradores = mysql_fetch_object($res_buscar_colaboradores))
                    	{
                    	    echo '<option value="' . $obj_buscar_colaboradores->cod_colaboradores . '">' . bd2texto($obj_buscar_colaboradores->nome) . '</option>';
                    	}
                    	
                    	desconectabd($conexao);*/
                    	
                    	?>
                    	
                	</select>
            	</td>
            </tr>
            
            <tr>
                <td class="legenda tdbl sep" align="right"><label for="cod_fornecedores_filtro">Fornecedor:</label></td>
                <td class="sep">&nbsp;</td>
                <td class="tdbr sep">
                	<select name="cod_fornecedores_filtro" style="width: 250px;">
                    	<option value=""></option>
                    	
                    	<?
    /*
                    	$conexao = conectabd();
                    	
                    	$sql_buscar_fornecedores = "SELECT * FROM ipi_fornecedores ORDER BY nome_fantasia";
                    	$res_buscar_fornecedores = mysql_query($sql_buscar_fornecedores);
                    	
                    	while($obj_buscar_fornecedores = mysql_fetch_object($res_buscar_fornecedores))
                    	{
                    	    echo '<option value="' . $obj_buscar_fornecedores->cod_fornecedores . '">' . bd2texto($obj_buscar_fornecedores->nome_fantasia) . '</option>';
                    	}
                    	
                    	desconectabd($conexao);*/
                    	
                    	?>
                    	
                	</select>
            	</td>
            </tr>

		 	-->

			<tr>
                <td class="legenda tdbl " align="right"><label for="cod_titulos_subcategorias_filtro">Subcategoria:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr ">
                	<select name="cod_titulos_subcategorias_filtro[]" id="cod_titulos_subcategorias_filtro" style="width: 250px;height:auto" multiple="multiple" size="10" >
                		<option value="TODOS" selected='selected'>Todas</option>
                		
                		<?

                        if (defined('COD_SUBCATEGORIAS_COMISSAO_CARTAO'))
                        {
                            echo '<option value="TODOS_'.COD_SUBCATEGORIAS_COMISSAO_CARTAO.'">Todas sem a comissão dos cartões</option>';
                        }
    
                		$conexao = conectabd();
                		
                        $sql_buscar_categorias = "SELECT * FROM ipi_titulos_categorias WHERE cod_titulos_categorias IN (SELECT cod_titulos_categorias FROM ipi_titulos_subcategorias) ORDER BY titulos_categoria";
                        $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                        
                        while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                        {
                            echo '<optgroup label="' . bd2texto($obj_buscar_categorias->titulos_categoria) . '">';
                            
                            $sql_buscar_subcategorias = "SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = '" . $obj_buscar_categorias->cod_titulos_categorias . "' ORDER BY titulos_subcategorias";
                            $res_buscar_subcategorias = mysql_query($sql_buscar_subcategorias);
                            
                            while($obj_buscar_subcategorias = mysql_fetch_object($res_buscar_subcategorias))
                            {
                                echo '<option value="' . $obj_buscar_subcategorias->cod_titulos_subcategorias . '"';
                                
                                if($obj_editar->cod_titulos_subcategorias == $obj_buscar_subcategorias->cod_titulos_subcategorias)
                                {
                                    echo 'selected';    
                                }
                            
                                echo '>' . bd2texto($obj_buscar_subcategorias->titulos_subcategorias) . '</option>';
                            }
                            
                            echo '</optgroup>';
                        }
                		
                		desconectabd($conexao);
                		
                		?>
                	</select><br/>(para selecionar mais de uma categoria segure ctrl e clique)
            	</td>
        	</tr>
        	
        	<tr>
                <td class="legenda tdbl " align="right"><label for="cod_bancos_filtro">Banco/Caixa:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr ">
                	<select name="cod_bancos_filtro" id="cod_bancos_filtro" style="width: 250px;">
                		<option value="TODOS">Todos</option>
                		
                		<?
    
                		$conexao = conectabd();
                		
                        $sql_buscar_bancos = "SELECT * FROM ipi_bancos b INNER JOIN ipi_bancos_ipi_pizzarias bp ON (b.cod_bancos=bp.cod_bancos) WHERE bp.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND b.situacao = 'ATIVO' ORDER BY b.banco";
                    	$res_buscar_bancos = mysql_query($sql_buscar_bancos);
                    	
                    	while($obj_buscar_bancos = mysql_fetch_object($res_buscar_bancos))
                    	{
                            echo '<option value="' . $obj_buscar_bancos->cod_bancos . '">';
    
                        	echo bd2texto($obj_buscar_bancos->banco); 
                        	
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
                <td class="legenda tdbl " align="right"><label for="situacao_filtro">Situação:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr ">
                	<select name="situacao_filtro" style="width: 250px;">
                    	<option value="TODOS">Todas</option>
                    	<option value="ABERTO">Abertos</option>
                    	<option value="PAGO">Pagos</option>
                	</select>
            	</td>
            </tr>

            <tr>
                <td class="legenda tdbl" align="right"><label for="filtrar_filtro">Filtrar por:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr ">
                    <select name="filtrar_filtro" style="width: 250px;">
                        <option value="MES_REFERENCIA" <? if($filtrar_por=="MES_REFERENCIA") echo "SELECTED='SELECTED'"; ?>>Mês de referencia</option>
                        <option value="DATA_VENCIMENTO" <? if($filtrar_por=="DATA_VENCIMENTO") echo "SELECTED='SELECTED'"; ?>>Data de Vencimento</option>
                        <option value="DATA_PAGAMENTO" <? if($filtrar_por=="DATA_PAGAMENTO") echo "SELECTED='SELECTED'"; ?>>Data de Pagamento</option>
                        <option value="DATA_EMISSAO" <? if($filtrar_por=="DATA_EMISSAO") echo "SELECTED='SELECTED'"; ?>>Data de Emissão</option>
                        <option value="DATA_CRIADA" <? if($filtrar_por=="DATA_CRIADA") echo "SELECTED='SELECTED'"; ?>>Data de Criação</option>
                    </select>
                </td>
            </tr>
            	   <tr>
 						     <td class="legenda tdbl " align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
			     		   <td class="">&nbsp;</td>
                <td class="tdbr " ><select name="cod_pizzarias" id="cod_pizzarias" style="width: 250px;">
											<?
											$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

											$con = conectabd();
											$SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao!='INATIVO' AND cod_pizzarias in(".$cod_pizzarias_usuario.") ORDER BY nome";//cod_pizzarias IN ($cod_pizzarias_usuario)
											$resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
								
											while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
											{
												echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
												if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
												{
													echo 'selected';
												}
												echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
											}
											?>
    						  </select></td>
            </tr>
            
            <tr>
                <td class="tdbl" align="right"><input type="checkbox" name="pagamentos_filtro" id="pagamentos_filtro" checked="checked"></td>
                <td>&nbsp;</td>
                <td class="legenda tdbr"><label for="pagamentos_filtro">Pagamentos</label></td>
            </tr>
            
            <tr>
                <td class="tdbl" align="right"><input type="checkbox" name="recebimentos_filtro" id="recebimentos_filtro"></td>
                <td>&nbsp;</td>
                <td class="legenda tdbr"><label for="recebimentos_filtro">Recebimentos</label></td>
            </tr>
            
            <tr>
                <td class="tdbl sep" align="right"><input type="checkbox" name="transfer_filtro" id="transfer_filtro"></td>
                <td class="sep">&nbsp;</td>
                <td class="legenda tdbr sep"><label for="transfer_filtro">Transferências</label></td>
            </tr>
            
            <tr>
                <td align="right" class="tdbl tdbb tdbr" colspan="3">
                    <input class="botaoAzul" type="button" value="Buscar" onclick="carregar_relatorio();">
                </td>
            </tr>

        </table>

        <input type="hidden" name="acao" value="buscar"></form>

        <br><br>
        
        <div id="relatorio"></div>
        
        <br>

<? if ($exibir_barra_lateral): ?>

        </td>
        <!-- Conteúdo -->

        <!-- Barra Lateral -->
        <td class="lateral">
        <div class="blocoNavegacao">
        <ul>
            <li><a href="#">Atalho 1</a></li>
            <li><a href="#">Atalho 2</a></li>
        </ul>
        </div>
        </td>
        <!-- Barra Lateral -->

    </tr>
</table>

<? endif;?>

<? rodape(); ?>
