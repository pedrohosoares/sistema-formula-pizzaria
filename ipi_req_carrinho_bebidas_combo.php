<?
require_once 'ipi_req_carrinho_trava_meianoite.php';
require_once('ipi_req_carrinho_classe.php');

require_once 'bd.php';

if ( (!$_SESSION['ipi_carrinho']['combo']['cod_combos']) )
{
    echo "<script>window.location='cardapio'</script>";    
}

$conexao = conectabd ();

$sql_combos = "SELECT * FROM ipi_combos WHERE cod_combos=".$_SESSION['ipi_carrinho']['combo']['cod_combos'];
//echo "<br>1: ".$sql_combos;
$res_combos = mysql_query( $sql_combos );
$obj_combos = mysql_fetch_object( $res_combos );


$indice_opcoes = -1;
$num_opcoes = count($_SESSION['ipi_carrinho']['combo']['produtos']);

for ($a=0; $a<$num_opcoes; $a++)
{
    if ( ($_SESSION['ipi_carrinho']['combo']['produtos'][$a]['foi_pedido']=='N') && ($_SESSION['ipi_carrinho']['combo']['produtos'][$a]['tipo']=='BEBIDA') )
    {    
        $indice_opcoes = $a;
        break;
    }
}
?>
<!-- <div style="background-image: url('upload/combos/<? echo $obj_combos->imagem_fundo; ?>'); width: 630px; height: 330px;"> -->
<script>
function ValidarBebidas(frm)
{
	objQtde = document.getElementsByName('quantidades[]');
	num_elementos = objQtde.length;
	selecao = 0;
	selecao = 0;
	total_bebidas = 0;
	for (a=0; a<num_elementos; a++)
	{
		if (objQtde[a].value=="")
			objQtde[a].value="0";
		
		if (objQtde[a].value=="0")
			selecao++;
		  total_bebidas += parseInt(objQtde[a].value);
	}
		
	if (num_elementos==selecao)
	{
		alert("Você deve selecionar a quantidade antes de adicionar no carrinho!");
		objQtde[0].focus();
		return false;
	}
	
  if (total_bebidas>1)
  {
		alert("Nos combos, você pode selecionar somente uma bebida (quantidade) de cada vez!\n\nRevise seu pedido!");
		objQtde[0].focus();
		return false;
  }
		
		
	return true;
}
</script>
<?

// Trava da Meia-Noite impedir fechamento de pedidos depois das 00h05
$bloquear_pedido_por_horario = 0;

$hora_corte_inicio = "00:05:00";
$hora_corte_fim = "02:00:00";

$hora_corte_inicio_convertida = strtotime($hora_corte_inicio);
$hora_corte_fim_convertida = strtotime($hora_corte_fim);

if ( ($hora_atual_convertida > $hora_corte_inicio_convertida) && ($hora_atual_convertida < $hora_corte_fim_convertida) )
{
	$bloquear_pedido_por_horario = 1;
}


if($bloquear_pedido_por_horario==0)
{
	?>
	<form id='frmDesistir' method='post' action="ipi_req_carrinho_acoes.php" >
	 <input type="hidden" name="acao" value="excluir_combo">
	 <input type="hidden" name="id_combo" value="<? echo $_SESSION['ipi_carrinho']['combo']['id_combo']; ?>">
</form>   
	<form name="frmBebidas" method="post" action="ipi_req_carrinho_acoes.php" onsubmit="return ValidarBebidas(this);">

	<div class="tipo_letra1 cor_marrom2">

		<div class="fonte22 negrito">QUER PEDIR ALGUMA BEBIDA?</div>
		<div align='center'>

    <br /><br />É necessário pedir uma bebida de cada vez.
    <br/><br/><a href='#' onclick='$("#frmDesistir").submit();' title='desistir do combo' class="btn btn-secondary">Cancelar Combo</a><br />

		</div>
		<div id="carrinho_escolha_bebidas">
		  <div class="bebidas_topo_branco"></div>
		  <div class="bebidas_meio_branco">
		  <?
		  $conexao = conectabd ();

		  if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
		  {
		    $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
		  }
		  else
		  {
		    $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
		    $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
		    $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
		    $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
		    $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
		    $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
		  }

		   $sqlBebidas = "SELECT * FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas=b.cod_bebidas) WHERE cp.situacao = 'ATIVO' AND bc.cod_bebidas_ipi_conteudos in(".$_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['cod_conteudos'].") AND cp.venda_net = 1 ORDER BY bebida,conteudo";
		  $resBebidas = mysql_query ( $sqlBebidas );
		  $linBebidas = mysql_num_rows ( $resBebidas );
		  //echo "<br>1: ".$sqlBebidas;
		  if ($linBebidas > 0) 
		  {
		    for($a = 0; $a < $linBebidas; $a ++) 
		    {
			    $objBebidas = mysql_fetch_object ( $resBebidas );
		      echo '<div style="float:left; margin: 40px;">';
			    echo '<input name="cod_bebidas_conteudos[]" type="hidden" size="3" value="'.$objBebidas->cod_bebidas_ipi_conteudos.'">';

		    	echo '<div class="carrinho_foto_bebida">';
			    
			    if ($objBebidas->foto_pequena)
			    {
			    	echo '<img width="50" height="100" src="upload/bebidas/'.$objBebidas->foto_pequena.'"/>';
			    }
			    else
			    {
			    	echo '<img width="50" height="100" src="img/cocapet.png" />';

			    }
			    echo '</div>';

			    echo '<div class="carrinho_opcoes_bebida">';
			    echo '<select name="quantidades[]">';
			    echo '<option value="0" selected>0</option>';
			    echo '<option value="1">1</option>';
			    echo '</select><br />';
			    //echo 'R$ '.bd2moeda($objBebidas->preco).'';
			    echo '(Combo)';
		      echo '</div>';

		      echo '</div>';
		    }
		  }
		  desconectabd ($conexao);
		   echo '<div style="clear:both"></div>';
		  ?>
		  
		  </div>

		  <div class="bebidas_rodape_branco"></div>
		</div>


		<br /><br />

		<a href="javascript:if(ValidarBebidas(document.frmBebidas)){document.frmBebidas.submit();}" class="btn btn-secondary">
		  	Incluir
		</a>
		&nbsp;&nbsp;
		<a href="javascript:document.frmBebidas.acao.value='verificar_login';document.frmBebidas.submit();" class="btn btn-secondary">
		  	Cancelar
		</a>


		<!--   td align="center" width="140">
		<a href="#" onclick="javascript:{document.frmBebidas.acao.value='adicionar_verificar_login';document.frmBebidas.submit();}">
		<img src="img/btn_fecharpedido.gif" border="0">
		</a>
		</td-->



		</div>
		
	 <input type="hidden" name="acao" value="adicionar_bebida_combo">
	 <input type="hidden" name="indice_atual_combo" value="<? echo $indice_opcoes; ?>">
	 <input type="hidden" name="id_combo" value="<? echo $_SESSION['ipi_carrinho']['combo']['id_combo']; ?>">
	 <input type="hidden" name="cod_combos" value="<? echo $_SESSION['ipi_carrinho']['combo']['cod_combos']; ?>">
	 </form>
	 <?
}
else
{	
	?>
	<table border="0" cellspacing="0" cellpadding="2" width="630">
	  <tr>
	    <td valign="top" align="center">
	    <br><br>

		<font color="#FF0000">
		<b>Não é possível concluir a compra!</b><Br>
		O horário limite para fechar a compra é até a <b><? echo date("H:i", strtotime($hora_corte_inicio)); ?></b>!<br> 
		Próximo horario para fazer pedidos agendados é as <b><? echo date("H:i", strtotime($hora_corte_fim)); ?></b>.
		</font>

	    <br><br>
	    </td>
	   </tr>
	 </table>
	<?
}
?>
