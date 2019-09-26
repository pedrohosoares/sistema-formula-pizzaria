<?
session_start();
require_once 'ipi_req_carrinho_classe.php';
require_once 'bd.php';
require_once 'palavra_chave.php';
require_once 'classe/pontos_fidelidade.php';
$pontos_fidelidade = new PontosFidelidade();
$carrinho = new ipi_carrinho();
?>
<div class="divfidelidade">
<form id="frmFidelidade" method="post" action="ipi_req_carrinho_acoes.php">



<br /><br />
<div style="text-align: left; margin-right: 40px">
  <span class="fonte15">Você possui: <input type="text" name="pontos_fidelidade" id="pontos_fidelidade"	value="<? echo $carrinho->pontos_fidelidade(); ?>" readonly="readonly"	size="2" style="border: 0px; background: none; text-align: right; font-weight: bold;"> Pontos</span>
</div>

<script>
function desconta_fidelidade(cbFidelidade)
{
  if (cbFidelidade.checked==true)
  {
    var cbTemp = cbFidelidade.value;
    var cbTempString = cbFidelidade.value.split(",");
    pt = document.getElementById('pontos_fidelidade').value;
    if (parseInt(cbTempString[0])>parseInt(pt))
    {
      cbFidelidade.checked=false;
      alert('Seus pontos de fidelidade não são suficientes para comprar este item!');
    }
    else
    {
      document.getElementById('pontos_fidelidade').value = parseInt(pt) - parseInt(cbTempString[0]);
    }
  }
  else if (cbFidelidade.checked==false)
  {
    var cbTemp = cbFidelidade.value;
    var cbTempString = cbFidelidade.value.split(",");
    pt = document.getElementById('pontos_fidelidade').value;
    document.getElementById('pontos_fidelidade').value = parseInt(pt) + parseInt(cbTempString[0]);
  }
}
</script>
<?
echo $carrinho->exibir_pedido_fidelidade();
?>
<div>
	<div>
		<? //echo buscar_palavra_chave("Fidelidade Tabela"); ?>
      <br/>
      <br/>
      <br/>
      <br/>
      <!-- <span class="mintitulo"><strong>TABELA DE PONTOS FIDELIDADE</strong></span> -->
      <br/>
      <br/>
      <div class='div_tabela_fidelidade_dl'>
      <? echo $pontos_fidelidade->buscar_tabela_cep($_SESSION['ipi_carrinho']['cep_visitante']); ?>
    </div>
	</div>
</div>

 <div></div>


<br/>
<br/>
<table border="0" cellspacing="0" cellpadding="2" align="center">
	<tr>
		<td align="center" width="140">
      <a href="#" onclick="javascript:$('#frmFidelidade').submit();"  class="btn btn-secondary btn-small">Usar Fidelidade</a>
    </td>
    <td>
      <a href='pagamentos' title='voltar para pagamentos'  class="btn btn-secondary btn-small">Voltar para pagamentos </a>
    </td>
	</tr>
</table>

<input type="hidden" name="acao" value="creditar_fidelidade">
<br/>
</form>
</div>
<div class="bottom_div"></div>