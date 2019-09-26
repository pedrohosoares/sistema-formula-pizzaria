<?php

/**
 * ipi_bebida.php: Cadastro Bebidas
 * 
 * Índice: cod_bebidas
 * Tabela: ipi_bebidas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Liberação manual - Captura');
$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';

$cod_pizzarias_sessao =  implode("," , $_SESSION['usuario']['cod_pizzarias']);

switch($acao) 
{
  case 'capturar':
      
    $con = conectabd();
    
    $cod_pedidos = validaVarPost('cod_pedidos');
    $cod_usuario = validaVarPost('cod_usuario');
    
    $SqlUpdate = "UPDATE $tabela SET situacao = 'CAPTURADO', cod_usuarios_liberacao_captura = $cod_usuario, data_captura_manual = NOW() WHERE $chave_primaria = $cod_pedidos";
    
    $resUpdate = mysql_query($SqlUpdate);
    //echo "<br>1: ".$SqlUpdate;
        
    if($resUpdate)
    {  
      mensagemOk('O pedido '.$cod_pedidos.' foi CAPTURADO com sucesso!');
    }
    else
    {
      mensagemErro('Erro ao CAPTURAR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
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
		<form name="frmBuscar" method="post"><input type="text"
			name="num_pedido" id="num_pedido" value="<? echo $num_pedido; ?>"> <input
			type="submit" name="bt_buscar" id="bt_buscar" value="Buscar"
			class="botao"> <input type="hidden" name="acao" value="buscar"></form>

		</td>
	</tr>
<?
if ($acao == "buscar")
{
    $int_num_pedido = (int) $num_pedido;
    $con = conectabd();
    $sql_buscar = "SELECT p.*, c.nome, pi.nome nome_pizzaria FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes=c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias=pi.cod_pizzarias) WHERE p.cod_pedidos = '$int_num_pedido' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
    $res_buscar = mysql_query($sql_buscar);
    $num_buscar = mysql_num_rows($res_buscar);
    $obj_buscar = mysql_fetch_object($res_buscar);
    //echo "<br>1: ".$sql_buscar;
    desconectabd($con);
    
    if ($num_buscar > 0)
    {

        if ( ( ($obj_buscar->situacao=="IMPRESSO") || ($obj_buscar->situacao=="ENVIADO")) && ( ($obj_buscar->forma_pg=="VISANET")||($obj_buscar->forma_pg=="MASTERCARDNET")||($obj_buscar->forma_pg=="VISANET-CIELO")||($obj_buscar->forma_pg=="MASTERCARDNET-CIELO")||($obj_buscar->forma_pg=="ELONET-CIELO")||($obj_buscar->forma_pg=="DISCOVERNET-CIELO")||($obj_buscar->forma_pg=="DINERSNET-CIELO")||($obj_buscar->forma_pg=="AMEXNET-CIELO") ) )
        {

            if (in_array($obj_buscar->cod_pizzarias,$_SESSION['usuario']['cod_pizzarias']))
            {
            ?>
              <tr>
    		      <td colspan="2">&nbsp;</td>
            	</tr>
            	<tr>
            		<td colspan="2">&nbsp;</td>
            	</tr>
            
            	<tr>
            		<td align="right"><b>Pizzaria:</b></td>
            		<td>
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
            	
    	<form name="frm_baixar" method="post"
    		onsubmit="return validar_baixa(this);">
    
      	<tr>
      		<td colspan="2">&nbsp;</td>
      	</tr>
      
      	<tr>
      		<td align="center" colspan="2">
      		<input type="submit" name="bt_baixar" id="bt_baixar" value="Capturar" class="botao"> 
      		<input type="hidden" name="acao" value="capturar"> 
      		<input type="hidden" name="cod_pedidos"	value="<? echo $obj_buscar->cod_pedidos; ?>"></td>
      		<input type="hidden" name="cod_usuario"	value="<? echo $_SESSION['usuario']['codigo'] ?>"></td>
      	</tr>
    
    	</form>
              
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
                <td align="right"><b>Pizzaria:</b></td>
                <td>
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
                <td align="right"><b>Data e Hora do Pedido:</b></td>
                <td>
                        <? echo bd2datahora($obj_buscar->data_hora_pedido); ?>
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
                <td colspan="2" align="center">Esse pedido não pode ser capturado pois <font color="#FF0000"><b>pertence a outra pizzaria (<? echo $obj_buscar->nome_pizzaria; ?>).</b></font>
                </td>
            </tr>
            <?
            }   
        
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
    		<td align="right"><b>Pizzaria:</b></td>
    		<td>
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
    		<td align="right"><b>Data e Hora do Pedido:</b></td>
    		<td>
                    <? echo bd2datahora($obj_buscar->data_hora_pedido); ?>
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
    		<td colspan="2" align="center">
        <? 
        if ($obj_buscar->situacao == "CAPTURADO")
        {
          ?>
          <br>Esse pedido já foi <font color="#FF0000"><b>CAPTURADO</b></font>!
          <?        
        }
        elseif ($obj_buscar->situacao == "BAIXADO")
        {
          ?>
          <br>Esse pedido já foi <font color="#FF0000"><b>BAIXADO</b></font>!
          <?
        }
        else
        {
          ?>
          Esse pedido não pode ser capturado pois é Cartão de Crédito NET <br />e 
      		está na situação: <font color="#FF0000"><b><? echo $obj_buscar->situacao; ?></b></font>.
          <?
        }
        ?>
    		</td>
    	</tr>
        <?
        }
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
		<td colspan="2" align="center">Nenhum pedido encontrado para a busca:
		<b><? echo $num_pedido; ?></b></td>
	</tr>
    <?
    }
}
?>
</table>

<? rodape(); ?>
