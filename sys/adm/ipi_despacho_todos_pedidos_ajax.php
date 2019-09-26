<?php

/**
 * ipi_rel_historico_pedidos.php: Histórico de Pedidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../traducoes.php';


$acao = validaVarPost('acao');

$codigo_usuario = $_SESSION['usuario']['codigo'];

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 135;
if($acao==''):
$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 

$pedido = (validaVarPost('pedido', '/[0-9]+/')) ? (int) validaVarPost('pedido', '/[0-9]+/') : (validaVarGet('p', '/[0-9]+/') ? (int) validaVarGet('p', '/[0-9]+/') : '');

// $cod_pizzarias = validaVarPost('cod_pizzarias');
$cod_pizzarias = $_SESSION['usuario']['cod_pizzarias'][0];

?>

<?
  $con = conectabd();
	require ("../../pub_req_fuso_horario1.php");
	// echo'Horario: '. date('Y-m-d H:i:s');
	// echo '<br/>date_default_timezone_set: ' . date_default_timezone_get() . '<br />';

### PEDIDOS SEM AGENDAMENTO ###
  $SqlBuscaRegistros = "SELECT p.*, c.*, pi.nome AS pi_nome, p.situacao AS pedidos_situacao,p.data_hora_pedido as horario_pedido FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.cod_pizzarias IN (" . $_SESSION['usuario']['cod_pizzarias'][0] . ") ";
   
  $SqlBuscaRegistros .= " AND p.situacao = 'IMPRESSO' AND p.tipo_entrega = 'Entrega' AND p.agendado = 0  AND (p.arquivo_json IS NULL OR p.arquivo_json = '') ";//$situacao
  

  $SqlBuscaRegistros .= ' ORDER BY cod_pedidos LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
  $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
  $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);

### PEDIDOS COM AGENDAMENTO ###
 $SqlBuscaRegistrosAgendados = "SELECT p.*, CONCAT( DATE( p.data_hora_pedido ) ,  ' ', horario_agendamento ) AS data_hora_pedido,  c.*, pi.nome AS pi_nome, p.situacao AS pedidos_situacao, CONCAT(DATE(data_hora_pedido), ' ', horario_agendamento) ,(SELECT (DATE_ADD(CONCAT(DATE(data_hora_pedido), ' ', horario_agendamento), INTERVAL ".(defined('TEMPO_ENTREGA')? TEMPO_ENTREGA : 30)." MINUTE)) ) AS tempo_entrega FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.cod_pizzarias IN (" . $_SESSION['usuario']['cod_pizzarias'][0] . ") ";
   
  $SqlBuscaRegistrosAgendados .= " AND p.situacao = 'IMPRESSO' AND p.tipo_entrega = 'Entrega' AND p.agendado = 1 AND (p.arquivo_json IS NULL OR p.arquivo_json = '') ";//$situacao
     $SqlBuscaRegistrosAgendados .= " AND NOW() >=(DATE_ADD(CONCAT(DATE(data_hora_pedido), ' ', horario_agendamento), INTERVAL -60 MINUTE))";
// echo $SqlBuscaRegistrosAgendados;
  $SqlBuscaRegistrosAgendados .= ' ORDER BY tempo_entrega LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
  $resBuscaRegistrosAgendados = mysql_query($SqlBuscaRegistrosAgendados);
  $linhasBuscaRegistrosAgendados = mysql_num_rows($resBuscaRegistrosAgendados);



  $SqlBuscaRegistrosBalcao = "SELECT p.*, c.*, pi.nome AS pi_nome, p.situacao AS pedidos_situacao,p.data_hora_pedido as horario_pedido,c.nome as nome_cliente FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.cod_pizzarias IN (" . $_SESSION['usuario']['cod_pizzarias'][0] . ") ";
   
  $SqlBuscaRegistrosBalcao .= " AND p.situacao = 'IMPRESSO' AND (p.tipo_entrega = 'Balcão' or p.tipo_entrega='Mesa' or p.tipo_entrega= 'Balcão a Distancia')  AND p.arquivo_json IS NULL ";//$situacao
  

  $SqlBuscaRegistrosBalcao .= ' ORDER BY cod_pedidos LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
  $resBuscaRegistrosBalcao = mysql_query($SqlBuscaRegistrosBalcao);
  // echo $SqlBuscaRegistrosBalcao;
  $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistrosBalcao);

  //echo $SqlBuscaRegistros;


?>
    <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" width="100%">
		<thead>
    <tr>
      <td  width="25%" style='background-color: #E5EFFD'><b>Entregador</b>   
	      <div style='float:right'>
						 <div align="center"><input style='height:40px;width:120px' class="botaoAzul" type="button" value="Editar Lista" onclick="editar_entregadores()"/></div>
				</div>
			</td>
      <td  width="50%" style='background-color: #E5EFFD'><input type="checkbox" id='marcar_todos' onclick="marcaTodos('marcarp');">&nbsp;<b onclick="checkar('marcar_todosp');marcaTodos('marcarp');">Pedidos Entrega</b>
      <td  width="25%" style='background-color: #E5EFFD'><input type="checkbox" id='marcar_todosb' onclick="marcaTodos('marcarb');">&nbsp;<b onclick="checkar('marcar_todosb');marcaTodos('marcarb');">Pedidos <? echo utf8_encode('Balcão') ?> </b>
      <div style='float:right'>
					 <div align="center"><input style='height:40px;width:120px' class="botaoAzul despacharBtn" type="button" value="Despachar Pedidos" onclick="despachar()"> </div>
	</td>		</div>
</td>
		</tr>
		</thead>
		</table>
		
		<table class="listaEdicao" cellpadding="0" cellspacing="0" width="100%">
		<tbody>
		<tr>
		 <td name="td_entregador" width="25%" valign='top' style='vertical-align:top'>
			 <table class="listaEdicao" cellpadding="0" cellspacing="0" width="100%">
				<?
				if(!isset($_SESSION['ipi_despacho']['entregadores']) || $_SESSION['ipi_despacho']['entregadores'] =='')
				{
					$cod_pizzarias_sessao =  implode("," , $_SESSION['usuario']['cod_pizzarias']);
					$SqlBuscaEntregadores = "SELECT * FROM ipi_entregadores WHERE cod_pizzarias IN ($cod_pizzarias_sessao) and situacao='ATIVO' ORDER BY nome";
					$resBuscaEntregadores = mysql_query($SqlBuscaEntregadores);
					//echo $SqlBuscaEntregadores;
					$i_e = 0;
					/*echo 'bd';*/
					while($objBuscaEntregadores = mysql_fetch_object($resBuscaEntregadores)) 
					{ 
						if(!isset($_SESSION['ipi_despacho']['entregadores'][$objBuscaEntregadores->cod_entregadores]))
						{
							$_SESSION['ipi_despacho']['entregadores'][$objBuscaEntregadores->cod_entregadores] = $objBuscaEntregadores->nome;
						}

						if($i_e % 2 == 0)
						{
							$cor = ";background-color:#CCCCCC";
						}
						else
						{
							$cor = "";
						}

						?>
					 <tr>
						 <td style="height:35px<? echo $cor ?>" onclick="checkar('entregador_<? echo $objBuscaEntregadores->cod_entregadores ?>')">
									<input type="radio"  onclick="checkar('entregador_<? echo $cod_e ?>')"id="entregador_<? echo $objBuscaEntregadores->cod_entregadores ?>" name="cod_entregador[]"  value="<? echo $objBuscaEntregadores->cod_entregadores ?>"> <? echo utf8_encode($objBuscaEntregadores->nome)  ?></input>
							</td>
						</tr>
							<?  $i_e++;
						} ?>
					</table>
	      </td>   
	      <?
	    }
	    else
	    {
	    			
		    		$i_e = 0;
						foreach ($_SESSION['ipi_despacho']['entregadores'] as $cod_e => $nome_e) 
						{

						if($i_e % 2 == 0)
						{
							$cor = ";background-color:#CCCCCC";
						}
						else
						{
							$cor = "";
						}

						?>
					 	<tr>
						 <td  style="height:35px<? echo $cor ?>" onclick="checkar('entregador_<? echo $cod_e ?>')">
									<input type="radio" id="entregador_<? echo $cod_e ?>" name="cod_entregador[]"  value="<? echo $cod_e ?>" onclick="checkar('entregador_<? echo $cod_e ?>')"> <? echo utf8_encode($nome_e)  ?></input>
							</td>
						</tr>
							<?  $i_e++;
						} ?>
					</table>
	      </td>   
	      <?
	    }
  	?>

<!--       <td name="td_entregador" width="25%" valign='top' style='vertical-align:top'>
			 <table class="listaEdicao" cellpadding="0" cellspacing="0" width="100%">
				<?
				$cod_pizzarias_sessao =  implode("," , $_SESSION['usuario']['cod_pizzarias']);
				$SqlBuscaEntregadores = "SELECT * FROM ipi_colaboradores WHERE cod_pizzarias IN ($cod_pizzarias_sessao) and situacao='ATIVO' and cod_tipo_colaboradores = '2' ORDER BY nome";
				$resBuscaEntregadores = mysql_query($SqlBuscaEntregadores);
				//echo $SqlBuscaEntregadores;
				$i_e = 0;
				while($objBuscaEntregadores = mysql_fetch_object($resBuscaEntregadores)) 
				{ 
					if($i_e % 2 == 0)
					{
						$cor = ";background-color:#CCCCCC";
					}
					else
					{
						$cor = "";
					}

					?>
				 <tr>
					 <td style="height:35px<? echo $cor ?>" onclick="checkar('chapeiro_<? echo $objBuscaEntregadores->cod_colaboradores ?>')">
								<input type="radio" id="chapeiro_<? echo $objBuscaEntregadores->cod_colaboradores ?>" name="cod_chapeiro[]"  value="<? echo $objBuscaEntregadores->cod_colaboradores ?>"> <? echo utf8_encode($objBuscaEntregadores->nome)  ?></input>
						</td>
					</tr>
	<?  $i_e++;} ?>
				</table>
      </td>    --> 
  
		
			<td width="50%" valign='top' style='vertical-align:top'>
			<table class="listaEdicao" cellpadding="0" cellspacing="0" width="100%"><? //echo strtotime('now') ?>
<?
	$i_p = 0;
	while($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
		echo '<tr>';
			if($i_p % 2 == 0)
			{
				$cor = ";background-color:#CCCCCC";
			}
			else
			{
				$cor = "";
			}
			// echo '<td>'.(strtotime(date('Y-m-d H:i:s'))+1800)."</td>";
			if(strtotime(date('Y-m-d H:i:s'))>=(strtotime($objBuscaRegistros->horario_pedido)+((defined('TEMPO_ENTREGA')? TEMPO_ENTREGA : 30)*60)))
			{
				$cor = ";background-color:red";
			}
			if(strtotime(date('Y-m-d H:i:s'))>=(strtotime($objBuscaRegistros->horario_pedido)+(((defined('TEMPO_ENTREGA')? TEMPO_ENTREGA : 30)-10)*60)) && strtotime(date('Y-m-d H:i:s'))<=(strtotime($objBuscaRegistros->horario_pedido)+(defined('TEMPO_ENTREGA')? TEMPO_ENTREGA : 30)*60))
			{
				$cor = ";background-color:orange";
			}
			//echo "<br/>tm=".strtotime(date('Y-m-d H:i:s'));
			//echo "<br/>tm2=".(strtotime($objBuscaRegistros->horario_pedido)-600);
		echo utf8_encode('<td style="height:35px'.$cor.'" align="" ><input type="checkbox" id="pedido_'.$objBuscaRegistros->$chave_primaria.'" class="marcarp situacao" name="'.$chave_primaria.'[]" value="'.$objBuscaRegistros->$chave_primaria.'_'.$objBuscaRegistros->cod_pizzarias.' onclick="checkar(\'pedido_'.$objBuscaRegistrosAgendados->$chave_primaria.'\')"">&nbsp;<b>'.sprintf('%06d', $objBuscaRegistros->$chave_primaria).'</b>, '.date("H:i",strtotime($objBuscaRegistros->data_hora_pedido)).' , R$ '.bd2moeda($objBuscaRegistros->valor_total).' - '.($objBuscaRegistros->edificio !='' ? $objBuscaRegistros->edificio .' - ' : '' ).$objBuscaRegistros->endereco.', n '.$objBuscaRegistros->numero.', '.$objBuscaRegistros->bairro.'</td>');//.' - '.$objBuscaRegistros->horario_pedido.' - '.strtotime($objBuscaRegistros->horario_pedido).' - '.(strtotime($objBuscaRegistros->tempo_entrega)-600)
		
	
		echo '</tr>';
		$i_p++;
	}

	while($objBuscaRegistrosAgendados = mysql_fetch_object($resBuscaRegistrosAgendados)) {
		echo '<tr>';
			if($i_p % 2 == 0)
			{
				$cor = ";background-color:#CCCCCC";
			}
			else
			{
				$cor = "";
			}
			echo '<!-- Date: '.date('Y-m-d H:i:s').' - TemEnt: '.$objBuscaRegistrosAgendados->tempo_entrega.' - ForTemEnt: '.date('Y-m-d H:i:s',strtotime($objBuscaRegistrosAgendados->tempo_entrega)).' -->';
			// echo '<td>'.strtotime(date('Y-m-d H:i:s')).'</td>';
			// echo '<td>'.(strtotime($objBuscaRegistrosAgendados->tempo_entrega)-600).'</td>';
			if(strtotime(date('Y-m-d H:i:s'))>=strtotime($objBuscaRegistrosAgendados->tempo_entrega))
			{
				$cor = ";background-color:red";
			}
			if((strtotime(date('Y-m-d H:i:s'))>=(strtotime($objBuscaRegistrosAgendados->tempo_entrega)-600)) && (strtotime(date('Y-m-d H:i:s'))<=strtotime($objBuscaRegistrosAgendados->tempo_entrega)))
			{
				$cor = ";background-color:orange";
			}
			//echo "<br/>tm=".strtotime(date('Y-m-d H:i:s'));
			//echo "<br/>tm2=".(strtotime($objBuscaRegistros->tempo_entrega)-600);

		echo utf8_encode('<td style="height:35px'.$cor.'" align="" ><input type="checkbox" id="pedido_'.$objBuscaRegistrosAgendados->$chave_primaria.'" class="marcarp situacao" name="'.$chave_primaria.'[]" value="'.$objBuscaRegistrosAgendados->$chave_primaria.'_'.$objBuscaRegistrosAgendados->cod_pizzarias.' onclick="checkar(\'pedido_'.$objBuscaRegistrosAgendados->$chave_primaria.'\')"">&nbsp;<b>'.sprintf('%06d', $objBuscaRegistrosAgendados->$chave_primaria).'</b>, '.date("H:i",strtotime($objBuscaRegistrosAgendados->data_hora_pedido)).', R$ '.bd2moeda($objBuscaRegistrosAgendados->valor_total).' - '.($objBuscaRegistrosAgendados->edificio !='' ? $objBuscaRegistrosAgendados->edificio.' - ' : '' ).$objBuscaRegistrosAgendados->endereco.', n '.$objBuscaRegistrosAgendados->numero.', '.$objBuscaRegistrosAgendados->bairro. ($objBuscaRegistrosAgendados->complemento !='' ? ', '.$objBuscaRegistrosAgendados->complemento : '' ).'</td>');//.' - '.$objBuscaRegistros->tempo_entrega.' - 

		
		echo '</tr>';
		$i_p++;
	}
echo "</table></td>";
?>
			<td width="25%" valign='top' style='vertical-align:top'>
			<table class="listaEdicao" cellpadding="0" cellspacing="0" width="100%">
<?
	$i_p = 0;
	while($objBuscaRegistrosBalcao = mysql_fetch_object($resBuscaRegistrosBalcao)) {
		echo '<tr>';
			if($i_p % 2 == 0)
			{
				$cor = ";background-color:#CCCCCC";
			}
			else
			{
				$cor = "";
			}

			if(strtotime(date('Y-m-d H:i:s'))>=(strtotime($objBuscaRegistrosBalcao->horario_pedido)+((defined('TEMPO_ENTREGA')? TEMPO_ENTREGA : 30)*60)))
			{
				$cor = ";background-color:red";
			}
			// if(strtotime(date('Y-m-d H:i:s'))>=(strtotime($objBuscaRegistrosBalcao->horario_pedido)+((-10)*60)) && strtotime(date('Y-m-d H:i:s'))<=(strtotime($objBuscaRegistrosBalcao->horario_pedido)+((defined('TEMPO_ENTREGA')? TEMPO_ENTREGA : 30)*60)))
				
			if(strtotime(date('Y-m-d H:i:s'))>=(strtotime($objBuscaRegistrosBalcao->horario_pedido)+(((defined('TEMPO_ENTREGA')? TEMPO_ENTREGA : 30)-10)*60)) && strtotime(date('Y-m-d H:i:s'))<=(strtotime($objBuscaRegistrosBalcao->horario_pedido)+(defined('TEMPO_ENTREGA')? TEMPO_ENTREGA : 30)*60))
			{
				$cor = ";background-color:orange";
			}
			//echo "<br/>tm=".strtotime(date('Y-m-d H:i:s'));
			//echo "<br/>tm2=".(strtotime($objBuscaRegistrosBalcao->horario_pedido)-600);


		echo utf8_encode('<td style="height:35px'.$cor.'" align="" ><input type="checkbox" id="pedido_'.$objBuscaRegistrosBalcao->$chave_primaria.'" class="marcarb situacao" name="'.$chave_primaria.'[]" value="'.$objBuscaRegistrosBalcao->$chave_primaria.'_'.$objBuscaRegistrosBalcao->cod_pizzarias.' onclick="checkar(\'pedido_'.$objBuscaRegistrosBalcao->$chave_primaria.'\')"">&nbsp;<b>'.sprintf('%06d', $objBuscaRegistrosBalcao->$chave_primaria).'</b>, '.date("H:i",strtotime($objBuscaRegistrosBalcao->data_hora_pedido)).', '.traduzir_tipo_entrega($objBuscaRegistrosBalcao->tipo_entrega).' </td>');//.' - '.$objBuscaRegistros->horario_pedido.' - '.strtotime(
		
		echo '</tr>';
		$i_p++;
	}
echo "</table></td>";

echo "</tr></table>";
//echo "<tr><td></td><td><small><div style='color:orange'>Maior que <b>20</b> minutos</div><div style='color:red'>Maior que <b>30</b> minutos</div></small></td></tr>style='padding-right:600px";
      echo "<div align='center'><div style='color:orange'>Maior que <b>".((defined('TEMPO_ENTREGA')? (TEMPO_ENTREGA-10) : 20))."</b> minutos</div><div style='color:red'>Maior que <b>".(defined('TEMPO_ENTREGA')? TEMPO_ENTREGA : 30)."</b> minutos</div></div>";

     // echo "<div align='center'><div style='color:orange;font: 12px \"Arial\",\"Lucida Grande\",\"Helvetica\",\"Verdana\",\"sans-serif\"'>Maior que <b>20</b> minutos</div><div style='color:red;font: 12px \"Arial\",\"Lucida Grande\",\"Helvetica\",\"Verdana\",\"sans-serif\"'>Maior que <b>30</b> minutos</div></div>";
    
desconectabd($con);

endif;

if($acao=='editar_entregadores')
{
	$con = conectar_bd();
	?>
    <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" width="100%">
		<thead>
    <tr>
      <td  width="50%" style='background-color: #E5EFFD' colspan='6'><b>Entregador:</b>   
	      <div style='float:right'>
						 <div align="center"><input style='height:40px;width:120px' class="botaoAzul" type="button" value="Salvar Lista" onclick="salvar_entregadores()"> </div>
				</div>
			</td>
		</tr>
		</thead>
		</table>
		
		<table class="listaEdicao" cellpadding="0" cellspacing="0" width="100%">
		<tbody>
		<?
			$cod_pizzarias_sessao =  implode("," , $_SESSION['usuario']['cod_pizzarias']);
			$SqlBuscaEntregadores = "SELECT * FROM ipi_entregadores WHERE cod_pizzarias IN ($cod_pizzarias_sessao) and situacao='ATIVO' ORDER BY nome";
			$resBuscaEntregadores = mysql_query($SqlBuscaEntregadores);
			$qtd_total_e = mysql_num_rows($resBuscaEntregadores);
			//echo $SqlBuscaEntregadores;
			$i_e = 0;
			$cont_e = 0;
			/*echo 'bd';*/
			while($objBuscaEntregadores = mysql_fetch_object($resBuscaEntregadores)) 
			{ 
				if($i_e==0)
				{
					echo '<tr>';
				}
					if(in_array($objBuscaEntregadores->cod_entregadores, array_keys($_SESSION['ipi_despacho']['entregadores'])))
					{
						$cor = ";background-color:lightgreen";
						$checked ='checked="checked"';
					}
					else
					{
						$cor = "";//;background-color:lightred
						$checked = '';
					}

					?>
					  <td style="height:35px<? echo $cor ?>" onclick="checkar_edit('edit_entregador_<? echo $objBuscaEntregadores->cod_entregadores ?>',this)">
								<input type="checkbox" id="edit_entregador_<? echo $objBuscaEntregadores->cod_entregadores ?>" name="edit_cod_entregador[]" <? echo $checked ?> value="<? echo $objBuscaEntregadores->cod_entregadores ?>"> <? echo utf8_encode($objBuscaEntregadores->nome)  ?></input><input type='hidden' name='nome_entregador_<? echo $objBuscaEntregadores->cod_entregadores ?>' value='<? echo utf8_encode($objBuscaEntregadores->nome)  ?>'/>
						</td>
					<?  
					$i_e++;
					$cont_e++;

					if($i_e==6 || $cont_e==$qtd_total_e)
					{
						echo '</tr>';
						$i_e = 0;
					}
			}

					echo '</table>';

	desconectar_bd($con);
}



?>