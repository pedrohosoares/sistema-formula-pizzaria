<?
require_once 'bd.php';
echo "<div>";
echo "<br /><strong>Informações:</strong>";
echo "<br /><Br /><strong>Código Erro (LR):</strong> ".$_SESSION['ipi_carrinho']['visa_temp']['erro'];
echo "<br /><strong>ID Transação (TID):</strong> ".$_SESSION['ipi_carrinho']['visa_temp']['tid_erro'];
echo "<br /><strong>Motivo:</strong> ".$_SESSION['ipi_carrinho']['visa_temp']['origem_erro'];
//echo "<br>Lr: {$ret_operadora['lr']}<br>Tid: {$ret_operadora['tid']}<br>Ars: {$ret_operadora['ars']}";
echo "</div>";
?>
<br /><br /><br /><a href="pagamentos">&lt;&lt; Voltar em PAGAMENTOS</a>
