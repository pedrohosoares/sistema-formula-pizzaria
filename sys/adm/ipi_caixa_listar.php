<?
require_once 'ipi_caixa_classe.php';
?>
<html>
<style>
body {
    font-family: Arial;
    font-size: 11px;
}

h6 {
    font-family: Arial;
    font-size: 11px;
}
</style>
<body>
<?
$caixa = new ipi_caixa();
$caixa->exibir_pedido();
?>
</body>
</html>
