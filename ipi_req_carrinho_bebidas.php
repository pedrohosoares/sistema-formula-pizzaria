<?
require_once 'ipi_req_carrinho_trava_meianoite.php';
require_once('ipi_req_carrinho_classe.php');

require_once 'bd.php';
?>
<script type="text/javascript">
function ValidarBebidas(frm)
{
	objQtde = document.getElementsByName('quantidades[]');
	num_elementos = objQtde.length;
	selecao = 0;
	for (a=0; a<num_elementos; a++)
	{
		if (objQtde[a].value=="")
			objQtde[a].value="0";
		
		if (objQtde[a].value=="0")
			selecao++;
	}
		
	if (num_elementos==selecao)
	{
		alert("Você deve selecionar a quantidade antes de adicionar no carrinho!");
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
	// if(isset($_SESSION['ipi_carrinho']['cod_pizzarias'])) :
		
	?>
	<form name="frmBebidas" method="post" action="ipi_req_carrinho_acoes.php" onsubmit="return ValidarBebidas(this);">

	<div>

		<div id="carrinho_escolha_bebidas">
		  <div class="bebidas_topo_branco"></div>
		  <div class="bebidas_meio_branco">
	
		  	<br/>
		  <?
		  $conexao = conectabd ();

		  if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
		  {
		    $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
		  }
		  else if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Entrega")
		  {
		    $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
		    $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
		    $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
		    $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
		    $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
		    $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
		  }
		  else
		  {
		  	echo '<script>location.href="pedidos";</script>';
		  }

		  $sqlBebidas = "SELECT * FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas=b.cod_bebidas) WHERE cp.situacao = 'ATIVO' AND cp.venda_net = 1 ORDER BY bebida, conteudo";

		  $resBebidas = mysql_query ( $sqlBebidas );
		  $linBebidas = mysql_num_rows ( $resBebidas );
		  //echo "<br />1: ".$sqlBebidas;
		  if ($linBebidas > 0) 
		  {
		    for($a = 0; $a < $linBebidas; $a ++) 
		    {
			    $objBebidas = mysql_fetch_object ( $resBebidas );
		      echo '<div style="float:left; margin: 40px;">';
			    echo '<input name="cod_bebidas_conteudos[]" type="hidden" size="3" value="'.$objBebidas->cod_bebidas_ipi_conteudos.'" />';

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
			    	
			      
		      // if ($objBebidas->foto_pequena)
			      // echo '<div class="carrinho_foto_bebida"> <img src="./img/cocapet.png" /></div>';
			      
			      echo '<div class="fonte11"><strong>'.$objBebidas->bebida.'</strong><br/><span class="fonte10">'.$objBebidas->conteudo.'<br/>(R$ '.bd2moeda($objBebidas->preco).')</span></div>';

			    echo '<div >';
			    echo '<select name="quantidades[]">';
			    echo '<option value="0" selected>0</option>';
			    echo '<option value="1">1</option>';
			    echo '<option value="2">2</option>';
			    echo '<option value="3">3</option>';
			    echo '<option value="4">4</option>';
			    echo '<option value="5">5</option>';
			    echo '<option value="6">6</option>';
			    echo '<option value="7">7</option>';
			    echo '<option value="8">8</option>';
			    echo '<option value="9">9</option>';
			    echo '<option value="10">10</option>';
			    echo '</select>';
			   
		      echo '</div>';

		      echo '</div>';

		    }
		     echo '<div style="clear:both"></div>';
		  }else
		  {
		  	echo "Nenhum bebida cadastrada para a sua região";
		  }
		  desconectabd ($conexao);
		  ?>
		  <div class="clear"></div>
		  <div style="text-align:center;">
			<br/>
			<a href="javascript:if(ValidarBebidas(document.frmBebidas)){document.frmBebidas.acao.value='adicionar_bebidas';document.frmBebidas.submit();}" class="btn btn-primary">
			  Incluir
			</a>
&nbsp;&nbsp;
			<a href="javascript:document.frmBebidas.acao.value='adicionar_bebidas';document.frmBebidas.submit();" title='Não Obrigado' class="btn btn-secondary">
			  Cancelar
			</a>
		</div>
		  </div>

		  <div class="bebidas_rodape_branco"></div>
		</div>

	 <input type="hidden" name="acao" value="" />

		</div>
	 </form>
	 <?
	 // endif;
	 // else:
	 // 	echo '<script>location.href="pedidos";</script>';
	 // endif;
}
else
{	
	?>
	<table border="0" cellspacing="0" cellpadding="2" width="630">
	  <tr>
	    <td valign="top" align="center">
	    <br /><br />

		<font color="#FF0000">
		<strong>Não é possível concluir a compra!</strong><br />
		O horário limite para fechar a compra é até a <strong><? echo date("H:i", strtotime($hora_corte_inicio)); ?></strong>!<br /> 
		Próximo horario para fazer pedidos agendados é as <strong><? echo date("H:i", strtotime($hora_corte_fim)); ?></strong>.
		</font>

	    <br /><br />
	    </td>
	   </tr>
	 </table>
	<?
}
?>
