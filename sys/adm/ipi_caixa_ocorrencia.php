<?php

/**
 * ipi_fechamento_caixa.php: Fechamento de caixa
 * 
 * Índice: cod_bebidas
 * Tabela: ipi_bebidas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Ocorrência de Caixa');

$acao = validaVarPost('acao');

switch ($acao)
{
    case 'inserir_ocorrencia':
    	$cod_caixa_ocorrencias_tipo = validaVarPost('cod_caixa_ocorrencias_tipo');
        $cod_caixa = validaVarPost('cod_caixa');
        $atendente = validaVarPost('atendente');
        $ocorrencia = validaVarPost('ocorrencia');
        $cod_colaboradores = validaVarPost('cod_colaboradores');
        $cod_entregadores = validaVarPost('cod_entregadores');
        $cod_pedidos = validaVarPost('cod_pedidos');
        
        $conexao = conectabd();
        
        $sql_inserir_ocorrencia = sprintf();
        
      	$sql_inserir_ocorrencia = sprintf("INSERT INTO ipi_caixa_ocorrencias (cod_entregadores, cod_colaboradores, cod_caixa_ocorrencias_tipo, cod_usuarios_ocorrencia, cod_caixa, ocorrencia, atendente, cod_pedidos, data_hora_ocorrencia) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', NOW())", $cod_entregadores, $cod_colaboradores, $cod_caixa_ocorrencias_tipo, $usuario, $cod_caixa, $ocorrencia, $atendente, $cod_pedidos);
      	echo $sql_inserir_ocorrencia;
      	if(mysql_query($sql_inserir_ocorrencia))
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

if(($acao == '') || ($acao == 'escolher_pizzaria')):

$conexao = conectabd();

?>

<script>

function esconder_campos(input, value)
{
	if (value=="")
	{
		document.getElementById("lblPedido").style.display="block";
		document.getElementById("lblEntregadores").style.display="block";
		document.getElementById("lblColaboradores").style.display="block";
	
		document.getElementById("cod_colaboradores").style.display="block";
		document.getElementById("cod_entregadores").style.display="block";
		document.getElementById("cod_pedidos").style.display="block";
	}
	else
	{
		switch (input) 		
		{
			case 1 : {
				document.getElementById("lblPedido").style.display="none";
			 	document.getElementById("lblEntregadores").style.display="none";
			
				document.getElementById("cod_entregadores").style.display="none";
				document.getElementById("cod_pedidos").style.display="none";
			}break;
			
			case 2 : {
				document.getElementById("lblPedido").style.display="none";
			 	document.getElementById("lblColaboradores").style.display="none";
				
				document.getElementById("cod_colaboradores").style.display="none";
				document.getElementById("cod_pedidos").style.display="none";
			}break;
			
			case 3 : {
			 	document.getElementById("lblEntregadores").style.display="none";
			 	document.getElementById("lblColaboradores").style.display="none";
			 
				document.getElementById("cod_colaboradores").style.display="none";
				document.getElementById("cod_entregadores").style.display="none";
			}break;
		}
	}		
}

</script>
<form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
<table align="center" class="caixa" cellpadding="0" cellspacing="0">

<?

// Busca o último caixa aberto de acordo com as pizzarias do perfil

if (($acao == 'escolher_pizzaria') && (validaVarPost('cod_caixa') > 0))
{
    $cod_caixa = validaVarPost('cod_caixa');
    
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) WHERE c.cod_caixa = '$cod_caixa'";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
    
    echo '<tr><td class="tdbl tdbt tdbr sep"><font color="red"><b>'.ucfirst(TIPO_EMPRESA).': ' . $obj_buscar_pizzarias->cod_pizzarias . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</b></font></td></tr>';
    echo '<input type="hidden" name="cod_caixa" value="' . $cod_caixa . '">';
    
    $cod_pizzarias = $obj_buscar_pizzarias->cod_pizzarias;
    $data_inicial = $obj_buscar_pizzarias->data_hora_abertura;
    $data_final = date('Y-m-d H:i:s');
}
else if (count($_SESSION['usuario']['cod_pizzarias']) > 1)
{
    echo '<tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_caixa">'.ucfirst(TIPO_EMPRESAS).'</label></td></tr>';
    echo '<tr><td class="tdbl tdbr sep"><select class="requerido" name="cod_caixa" id="cod_caixa" onchange="javascript: document.frmIncluir.acao.value = \'escolher_pizzaria\'; document.frmIncluir.submit(); ">';
    
    echo '<option value="0">Escolha a '.TIPO_EMPRESA.'</option>';
    
    foreach ($_SESSION['usuario']['cod_pizzarias'] as $cod_pizzarias_valor)
    {
        $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) WHERE p.cod_pizzarias = '$cod_pizzarias_valor' AND c.situacao = 'ABERTO'";
        $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
        $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
        
        echo '<option value="' . $obj_buscar_pizzarias->cod_caixa . '">' . $cod_pizzarias_valor . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</option>';
    }
    
    echo '</select></td></tr>';
}
else
{
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) WHERE p.cod_pizzarias = '" . $_SESSION['usuario']['cod_pizzarias'][0] . "' AND c.situacao = 'ABERTO'";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
    
    echo '<tr><td class="tdbl tdbt tdbr sep"><font color="red"><b>Pizzaria: ' . $_SESSION['usuario']['cod_pizzarias'][0] . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</b></font></td></tr>';
    echo '<input type="hidden" name="cod_caixa" value="' . $obj_buscar_pizzarias->cod_caixa . '">';
    
    $cod_pizzarias = $_SESSION['usuario']['cod_pizzarias'][0];
    $data_inicial = $obj_buscar_pizzarias->data_hora_abertura;
    $data_final = date('Y-m-d H:i:s');
}

if($cod_pizzarias > 0):

?>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="cod_caixa_ocorrencias_tipo">Tipo de Ocorrência</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep">
    	<select name="cod_caixa_ocorrencias_tipo" id="cod_caixa_ocorrencias_tipo" class="requerido" style="width: 350px;">
    	<?php 
    	
    	$sql_buscar_tipo_ocorrencias = "SELECT * FROM ipi_caixa_ocorrencias_tipo WHERE situacao = 'ATIVO' ORDER BY tipo_ocorrencia";
    	$res_buscar_tipo_ocorrencias = mysql_query($sql_buscar_tipo_ocorrencias);
    	
    	while($obj_buscar_tipo_ocorrencias = mysql_fetch_object($res_buscar_tipo_ocorrencias))
    	{
    		echo '<option value="' . $obj_buscar_tipo_ocorrencias->cod_caixa_ocorrencias_tipo . '">' . bd2texto($obj_buscar_tipo_ocorrencias->tipo_ocorrencia) . '</option>';
    	}
    	
    	?>
    	</select>
	</td>
</tr>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="atendente">Atendente que está relatando</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep"><input type="text" name="atendente" id="atendente" maxlength="45" size="45" style="width: 350px;"></td>
</tr>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="cod_colaboradores" id="lblColaboradores">Colaboradores</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep">
    	<select name="cod_colaboradores" id="cod_colaboradores" class="requerido" style="width: 200px;" onchange="javascript: esconder_campos(1, this.value);">
    	<?php 
    	
    	$sql_buscar_colaboradores = "SELECT * FROM ipi_colaboradores WHERE situacao = 'ATIVO' AND cod_pizzarias = ".$cod_pizzarias." ORDER BY nome";
    	$res_buscar_colaboradores = mysql_query($sql_buscar_colaboradores);
    	
    	echo '<option value=""></option>';
    	while($obj_buscar_colaboradores = mysql_fetch_object($res_buscar_colaboradores))
    	{
    		echo '<option value="' . $obj_buscar_colaboradores->cod_colaboradores . '" >' . bd2texto($obj_buscar_colaboradores->nome) . '</option>';
    	}
    	
    	?>
    	</select>
	</td>
</tr>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="cod_entregadores" id="lblEntregadores">Entregadores</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep">
    	<select name="cod_entregadores" id="cod_entregadores" class="requerido" style="width: 200px;" onchange="javascript: esconder_campos(2, this.value);">
    	<?php 
    	
    	$sql_buscar_entregadores= "SELECT * FROM ipi_entregadores WHERE cod_pizzarias = ".$cod_pizzarias." ORDER BY nome";
    	$res_buscar_entregadores= mysql_query($sql_buscar_entregadores);
    	
    	echo '<option value=""></option>';
    	while($obj_buscar_entregadores= mysql_fetch_object($res_buscar_entregadores))
    	{
    		echo '<option value="' . $obj_buscar_entregadores->cod_entregadores . '">' . bd2texto($obj_buscar_entregadores->nome) . '</option>';
    	}
    	
    	?>
    	</select>
	</td>
</tr>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="cod_pedidos" id="lblPedido">Pedido</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep">
    	<select name="cod_pedidos" id="cod_pedidos" class="requerido" style="width: 200px;" onchange="javascript: esconder_campos(3, this.value);">
    	<?php 
    	
    	$sql_buscar_pedidos= "SELECT * FROM ipi_pedidos WHERE cod_pizzarias = ".$cod_pizzarias." ORDER BY cod_pedidos";
    	$res_buscar_pedidos= mysql_query($sql_buscar_pedidos);
    	
    	echo '<option value=""></option>';
    	while($obj_buscar_pedidos= mysql_fetch_object($res_buscar_pedidos))
    	{
    		echo '<option value="' . $obj_buscar_pedidos->cod_pedidos . '">' . bd2texto($obj_buscar_pedidos->cod_pedidos) . '</option>';
    	}
    	
    	?>
    	</select>
	</td>
</tr>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="ocorrencia">Ocorrência</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep"><textarea rows="10" cols="100" id="ocorrencia" name="ocorrencia"></textarea></td>
</tr>

<tr>
    <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
        class="botao" type="button" value="Relatar Ocorrência" onclick="javascript: if(confirm('TEM CERTEZA QUE DESEJA RELATAR OCORRÊNCIA?')) { document.frmIncluir.submit(); } ;"></td>
</tr>

<? else: ?>

<tr>
    <td align="center" class="tdbl tdbb tdbr">&nbsp;</td>
</tr>

<? endif; ?>

</table>

<input type="hidden" name="acao" value="inserir_ocorrencia"></form>

<? 

desconectabd($conexao);

endif;

rodape(); 

?>