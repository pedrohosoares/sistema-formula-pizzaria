<?
require_once 'pub_req_promocoes.php';
require_once 'ipi_req_carrinho_trava_meianoite.php';
if ($_SESSION['ipi_carrinho']['promocao']['promocao12_ativa']==1)
{
  echo '<script>location.href = "promocao&p=12" </script>';
}
?>
<div class='caixa_que_fazer'>
	<div class='float_right'>
		<a href='pedidos' title='Pedir outra pizza' class='btn btn-secondary'>Pedir Outra Pizza</a>
		<br /><br />
		<a href='bebidas' title='Pedir bebida' class='btn btn-secondary'>Pedir Bebida</a>
		<br /><br />
		<form name="frmFecharPedido" method="post" action="ipi_req_carrinho_acoes.php">
			<!--<a href='#' onclick='sugerir();' title='Finalizar meu pedido'> <img src='img/pc/btn_finalizar_pedido.png' alt='Finalizar meu pedido' /> </a>-->
			<a href='#' onclick='document.frmFecharPedido.submit();' title='Finalizar meu pedido' class='btn btn-secondary'>Finalizar meu pedido</a>
      <input type='hidden' name="acao" value="verificar_login" />
		</form>
	</div>
</div>
<br/><br/>

