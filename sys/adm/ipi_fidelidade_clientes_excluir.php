<?php

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Excluir Pontos de Fidelidade Manual');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';

$cod_usuarios = $_SESSION['usuario']['codigo'];

switch($acao) 
{
	case 'cadastrar':
      
    	$con = conectabd();
    
    	$pontos = -1*validaVarPost('pontos');
    	$observacao = validaVarPost('observacao');
    	$cod_clientes = validaVarPost('cod_clientes');
    	$cod_pedidos = validaVarPost('cod_pedidos');
    
    	$sql_inserir_pontos = sprintf("INSERT INTO ipi_fidelidade_clientes (cod_clientes, cod_pedidos, cod_usuarios, pontos, obs, data_hora_fidelidade, data_validade) VALUES ('%s', '%s', '%s', '%s', '%s', NOW(), NOW() )", $cod_clientes, $cod_pedidos, $cod_usuarios, $pontos, $observacao);
		$res_inserir_pontos = mysql_query($sql_inserir_pontos);
		//echo $sql_inserir_pontos;
		
		if($res_inserir_pontos) 
		{
          	mensagemOk('Registro adicionado com êxito!');
        }
        else 
        {
          	mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        
    	desconectabd($con);
  	break;
}

$num_pedido = validaVarPost('num_pedido');

?>
<!-- Tab Editar -->
<script>
function init()
{
    $('num_pedido').focus();
}
window.addEvent('domready', init);
</script>

<table align="center" border="0" width="500" style="margin: 0 auto">
	<tr>
		<td align="right" width="150"><b>Numero do pedido:</b></td>
		<td width="350">
		<form name="frmBuscar" method="post">
			<input type="text" name="num_pedido" id="num_pedido" value="<? echo $num_pedido; ?>"> 
			<input type="submit" name="bt_buscar" id="bt_buscar" value="Buscar" class="botao"> 
			<input type="hidden" name="acao" value="buscar">
		</form>

		</td>
	</tr>
<?
if ($acao == "buscar")
{
    $int_num_pedido = (int) $num_pedido;
    $con = conectabd();
    $sql_buscar = "SELECT p.*, c.cod_clientes, c.nome, pi.nome nome_pizzaria FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes=c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias=pi.cod_pizzarias) WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.cod_pedidos = '$int_num_pedido'";
    $res_buscar = mysql_query($sql_buscar);
    $num_buscar = mysql_num_rows($res_buscar);
    $obj_buscar = mysql_fetch_object($res_buscar);
    //echo "<br>1: ".$sql_buscar;
    desconectabd($con);
    
    if ($num_buscar > 0)
    {
		?>
		<tr>
			<td colspan="2"><br />
			
            <b>Dados do Pedido</b>
			<hr noshade="noshade" color="#D44E08" size="1">
				<table border="0" width="100%">
					<tr>
	            		<td width="40%" align="right"><b>Pizzaria:</b></td>
	            		<td width="60%">
	                            <? echo $obj_buscar->nome_pizzaria; ?>
	                        </td>
	            	</tr>
	            	<tr>
	            		<td align="right"><b>Num. do Pedido:</b></td>
	            		<td>
	                            <? echo $obj_buscar->cod_pedidos; ?>
	                        </td>
	            	</tr>
	            	<tr>
	            		<td align="right"><b>Nome do Cliente:</b></td>
	            		<td>
	                            <? echo $obj_buscar->nome; ?>
	                        </td>
	            	</tr>
	            	<tr>
	            		<td align="right"><b>Bairro:</b></td>
	            		<td>
	                            <? echo $obj_buscar->bairro; ?>
	                        </td>
	            	</tr>
	            	<tr>
	            		<td align="right"><b>Forma de pagamento:</b></td>
	            		<td>
	                            <? echo ($obj_buscar->forma_pg); ?>
	                        </td>
	            	</tr>
	            	<tr>
	            		<td align="right"><b>Total do Pedido:</b></td>
	            		<td>
	                            R$ <? echo bd2moeda($obj_buscar->valor_total); ?>
	                        </td>
	            	</tr>
	            	<tr>
	            		<td align="right"><b>Data e Hora do Pedido:</b></td>
	            		<td>
	                            <? echo bd2datahora($obj_buscar->data_hora_pedido); ?>
	                        </td>
	            	</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="left">
            <br /><br />	
            <b>Excluir Pontos</b>
            <hr noshade="noshade" color="#D44E08" size="1">
				<form name="frm_fidelidade" method="post">
				<table width="100%" border="0">
					<tr>
						<td width="40%" align="right"><label id="pontos">Pontos:</label></td>
						<td width="60%" align="left"><input type="text" name="pontos" id="pontos" value=""></td>
					</tr>
					<tr>
						<td align="right"><label id="pontos">Observação:</label></td>
						<td align="left"><textarea rows="5" cols="50" name="observacao" id="observacao"></textarea></td>
					</tr>
                    <tr>
                        <td align="right"><label id="usuario_inclusao">Usuário que está excluindo:</label></td>
                        <td align="left"><? echo $_SESSION['usuario']['usuario']; ?></td>
                    </tr>
                    <tr>
                        <td align="right"><label id="data_inclusao">Data Exclusão:</label></td>
                        <td align="left"><? echo date("d/m/Y"); ?></td>
                    </tr>
					<tr>
						<td colspan="2" align="center"><br /><input type="submit" name="bt_enviar" value="EXCLUIR Pontos" class="botao"></td>
					</tr>
				</table>
				<input type="hidden" name="acao" value="cadastrar">
				<input type="hidden" name="cod_pedidos" value="<? echo $obj_buscar->cod_pedidos;?>">
				<input type="hidden" name="cod_clientes" value="<? echo $obj_buscar->cod_clientes;?>">
				</form>
			</td>
		</tr>		
		<?		        
    }
    else 
    {
	    ?>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" align="center">Nenhum pedido encontrado para a busca:<b><? echo $num_pedido; ?></b></td>
		</tr>
	    <?
    }
}
?>
</table>

<? rodape(); ?>
