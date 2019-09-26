<?
session_start();
//require_once 'ipi_req_carrinho_classe.php';

echo "<a href='temp_exibir_sessao.php'>Atualizar Carrinho</a>";
echo "<form name='frmReload' method='post' action='temp_exibir_sessao.php'>";
echo "<a href='javascript:document.frmReload.submit();'>Limpar Carrinho</a>";
echo "<input type='hidden' name='cod' value='1'>";
echo "</form>";


if ($_POST["cod"]=="1")
{
	//$carrinho = new ipi_carrinho();
	//$carrinho->apagar_pedido();
  unset($_SESSION['ipi_carrinho']);
  unset($_SESSION['ipi_cliente']);
  //unset($_SESSION['ipi_cliente']);
}


echo "<pre>";
print_r($_SESSION['ipi_carrinho']);
echo "</pre>";

echo "<br />-------------------------------------------------------------------------";
echo "<br />Sessão Cliente";

echo "<pre>";
print_r($_SESSION['ipi_cliente']);
echo "</pre>";

?>
