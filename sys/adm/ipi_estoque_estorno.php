<?php

/**
 * Cadastro de estornos.
 *
 * @version 1.0
 * @package ipizza
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       17/03/2010   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Estono');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_estoque_entrada';
$tabela = 'ipi_estoque_entrada';

switch ($acao)
{
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $numero_nota_fiscal = validaVarPost('numero_nota_fiscal');
        $cod_fornecedores = validaVarPost('cod_fornecedores');
        $num_parcelas = validaVarPost('num_parcelas');
        $cod_titulos_subcategorias = validaVarPost('cod_titulos_subcategorias');
        
        $vencimento = validaVarPost('vencimento');
        $valor = validaVarPost('valor');
        $mes_ref = validaVarPost('mes_ref');
        
        require_once '../../classe/estoque.php';
        
        $estoque = new Estoque();
                
        if ($estoque->gravar_entrada_itens_temporarios($cod_pizzarias, $numero_nota_fiscal, $cod_titulos_subcategorias, $cod_fornecedores, $num_parcelas, $vencimento, $valor, $mes_ref))
        {
            mensagemOK('Registro adicionado com êxito!');
        }
        else
        {
            mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        
        break;
}

?>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<script>

function bloquear_enter(field, event)
{
	var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
   
  	if (keyCode == 13)
  	{
  		buscar_ingredientes();
  		
    	return false;
    }
   	else
   	{
    	return true;
   	}
}

function bloquear_enter_bebida(field, event)
{
	var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
   
  	if (keyCode == 13)
  	{
  		buscar_bebidas();
  		
    	return false;
    }
   	else
   	{
    	return true;
   	}
}

function buscar_ingredientes()
{
	var busca_ingrediente = $('busca_ingrediente').getProperty('value');
	var url = 'acao=buscar_ingredientes&busca_ingrediente=' + busca_ingrediente;
	
	new Request.HTML({
		url: 'ipi_estoque_estorno_ajax.php',
		update: $('resultado_busca_ingrediente')
	}).send(url);
}

function buscar_bebidas()
{
	var busca_bebida = $('busca_bebida').getProperty('value');
	var url = 'acao=buscar_bebidas&busca_bebida=' + busca_bebida;
	
	new Request.HTML({
		url: 'ipi_estoque_estorno_ajax.php',
		update: $('resultado_busca_bebida')
	}).send(url);
}

function adicionar_ingrediente(cod_ingredientes_marcas)
{
	var quantidade = parseInt($('quantidade_ingredientes_adicionar_' + cod_ingredientes_marcas).getProperty('value'));
	var preco = $('preco_ingredientes_adicionar_' + cod_ingredientes_marcas).getProperty('value');
	
	if(quantidade > 0)
	{
    	var url = 'acao=adicionar_ingrediente&cod_ingredientes_marcas=' + cod_ingredientes_marcas + '&quantidade=' + quantidade + '&preco=' + preco; 
    	
    	new Request.JSON({
    		url: 'ipi_estoque_estorno_ajax.php',
    		onSuccess: function(retorno)
    		{
                if(retorno.resposta == 'OK')
                {
                	exibir_ingredientes_adicionados();
                }
                else
                {
                	alert(retorno.mensagem);
                }
        	}
    	}).send(url);
	}
	else
	{
		alert('Digite a quantidade antes de adicionar.');
	}
}

function adicionar_bebida(cod_bebidas_ipi_conteudos)
{
	var quantidade = parseInt($('quantidade_bebidas_adicionar_' + cod_bebidas_ipi_conteudos).getProperty('value'));
	var preco = $('preco_bebidas_adicionar_' + cod_bebidas_ipi_conteudos).getProperty('value');
	
	if(quantidade > 0)
	{
    	var url = 'acao=adicionar_bebida&cod_bebidas_ipi_conteudos=' + cod_bebidas_ipi_conteudos + '&quantidade=' + quantidade + '&preco=' + preco; 
    	
    	new Request.JSON({
    		url: 'ipi_estoque_estorno_ajax.php',
    		onSuccess: function(retorno)
    		{
                if(retorno.resposta == 'OK')
                {
                	exibir_bebidas_adicionados();
                }
                else
                {
                	alert(retorno.mensagem);
                }
        	}
    	}).send(url);
	}
	else
	{
		alert('Digite a quantidade antes de adicionar.');
	}
}

function alterar_ingrediente(cod_ingredientes_marcas)
{
	var quantidade = parseInt($('quantidade_ingredientes_alterar_' + cod_ingredientes_marcas).getProperty('value'));
	var preco = $('preco_ingredientes_alterar_' + cod_ingredientes_marcas).getProperty('value');
	
	if(quantidade > 0)
	{
    	var url = 'acao=alterar_ingrediente&cod_ingredientes_marcas=' + cod_ingredientes_marcas + '&quantidade=' + quantidade + '&preco=' + preco; 
    	
    	new Request.JSON({
    		url: 'ipi_estoque_estorno_ajax.php',
    		onSuccess: function(retorno)
    		{
                if(retorno.resposta == 'OK')
                {
                	exibir_ingredientes_adicionados();
                }
                else
                {
                	alert(retorno.mensagem);
                }
        	}
    	}).send(url);
	}
	else
	{
		alert('Digite a quantidade antes de adicionar.');
	}
}

function alterar_bebida(cod_bebidas_ipi_conteudos)
{
	var quantidade = parseInt($('quantidade_bebidas_alterar_' + cod_bebidas_ipi_conteudos).getProperty('value'));
	var preco = $('preco_bebidas_alterar_' + cod_bebidas_ipi_conteudos).getProperty('value');
	
	if(quantidade > 0)
	{
    	var url = 'acao=alterar_bebida&cod_bebidas_ipi_conteudos=' + cod_bebidas_ipi_conteudos + '&quantidade=' + quantidade + '&preco=' + preco; 
    	
    	new Request.JSON({
    		url: 'ipi_estoque_estorno_ajax.php',
    		onSuccess: function(retorno)
    		{
                if(retorno.resposta == 'OK')
                {
                	exibir_bebidas_adicionados();
                }
                else
                {
                	alert(retorno.mensagem);
                }
        	}
    	}).send(url);
	}
	else
	{
		alert('Digite a quantidade antes de adicionar.');
	}
}

function excluir_ingrediente(cod_ingredientes_marcas)
{
	var url = 'acao=excluir_ingrediente&cod_ingredientes_marcas=' + cod_ingredientes_marcas; 
	
	new Request.JSON({
		url: 'ipi_estoque_estorno_ajax.php',
		onSuccess: function(retorno)
		{
            if(retorno.resposta == 'OK')
            {
            	exibir_ingredientes_adicionados();
            }
            else
            {
            	alert(retorno.mensagem);
            }
    	}
	}).send(url);
}

function excluir_bebida(cod_bebidas_ipi_conteudos)
{
	var url = 'acao=excluir_bebida&cod_bebidas_ipi_conteudos=' + cod_bebidas_ipi_conteudos; 
	
	new Request.JSON({
		url: 'ipi_estoque_estorno_ajax.php',
		onSuccess: function(retorno)
		{
            if(retorno.resposta == 'OK')
            {
            	exibir_bebidas_adicionados();
            }
            else
            {
            	alert(retorno.mensagem);
            }
    	}
	}).send(url);
}

function exibir_ingredientes_adicionados()
{
	var url = 'acao=exibir_ingredientes_adicionados';
	
	new Request.HTML({
		url: 'ipi_estoque_estorno_ajax.php',
		update: $('resultado_ingrediente_adicionado')
	}).send(url);
}

function exibir_bebidas_adicionados()
{
	var url = 'acao=exibir_bebidas_adicionados';
	
	new Request.HTML({
		url: 'ipi_estoque_estorno_ajax.php',
		update: $('resultado_bebida_adicionado')
	}).send(url);
}

window.addEvent('domready', function()
{
    var tabs = new Tabs('tabs'); 
  
    if (document.frmIncluir.<?
    echo $chave_primaria?>.value > 0) 
    {
        <?
        if ($acao == '')
        {
            echo 'tabs.irpara(1);';
        }
        ?>
    
        document.frmIncluir.botao_submit.value = 'Alterar';
    }
    else 
    {
        document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  
    tabs.addEvent('change', function(indice)
    {
        if(indice == 1)
        {
            document.frmIncluir.<? echo $chave_primaria ?>.value = '';

            exibir_ingredientes_adicionados();
            exibir_bebidas_adicionados();
            
            document.frmIncluir.botao_submit.value = 'Cadastrar';
        }
    });
});

</script>

    <?
    
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    
    if ($codigo > 0)
    {
        $obj_editar = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    }
    
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return ((validaRequeridos(this)) && (validar_pizzarias(this)))">

	<table align="center" class="caixa" cellpadding="0" cellspacing="0" width="700">

	<tr>
        <td class="legenda tdbl tdbr tdbt">
            <label for="cod_pizzarias" class="requerido">Pizzaria</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <select name="cod_pizzarias" id="cod_pizzarias" class="requerido" style="width: 230px;">
            	<option value=""></option>
            	
            	<?

            	$conexao = conectabd();
            	
            	$sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";
            	$res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
            	
            	while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
            	{
            	    echo '<option value="' . $obj_buscar_pizzarias->cod_pizzarias . '" ';
            	    
            	    if($obj_editar->cod_pizzarias == $obj_buscar_pizzarias->cod_pizzarias)
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
        <td class="legenda tdbl tdbr sep">
        	<label>Ingredientes</label>
        	<hr size="1" noshade="noshade" color="#1A498F">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr sep">
            <input type="text" name="busca_ingrediente" id="busca_ingrediente" maxlength="60" size="40" onkeypress="return bloquear_enter(this, event)">
            &nbsp;
            <input type="button" class="botaoAzul" value="Buscar" onclick="buscar_ingredientes()">
        </td>
    </tr>
    
    <tr><td class="tdbl tdbr sep" id="resultado_busca_ingrediente"></td></tr>
    
    <tr><td class="tdbl tdbr sep" id="resultado_ingrediente_adicionado"></td></tr>
    
    
    <tr>
        <td class="legenda tdbl tdbr sep">
        	<label>Bebidas</label>
        	<hr size="1" noshade="noshade" color="#1A498F">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr sep">
            <input type="text" name="busca_bebida" id="busca_bebida" maxlength="60" size="40" onkeypress="return bloquear_enter_bebida(this, event)">
            &nbsp;
            <input type="button" class="botaoAzul" value="Buscar" onclick="buscar_bebidas()">
        </td>
    </tr>
    
    <tr><td class="tdbl tdbr sep" id="resultado_busca_bebida"></td></tr>
    
    <tr><td class="tdbl tdbr sep" id="resultado_bebida_adicionado"></td></tr>

    <tr>
        <td align="center" class="tdbl tdbb tdbr">
        	<input name="botao_submit" class="botao" type="submit" value="Cadastrar">
    	</td>
    </tr>
</table>

<input type="hidden" name="acao" value="editar"> <input type="hidden"
    name="<?
    echo $chave_primaria?>" value="<?
    echo $codigo?>"></form>


<?
rodape();
?>
