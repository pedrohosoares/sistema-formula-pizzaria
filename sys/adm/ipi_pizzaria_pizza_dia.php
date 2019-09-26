<?

/**
 * ipi_ingrediente.php: Cadastro de Pizzas do dia
 * 
 * Índice: ''
 * Tabela: ipi_pizzaria_pizza_semana
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de '.TIPO_PRODUTOS.' do Dia');

$cod_pizzarias = validaVarPost('cod_pizzarias');

$acao = validaVarPost('acao');
$codigo_matriz = 1;
$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab');
$dia_semana_completo = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');
switch ($acao) {
	case 'adicionar':
		$con = conectar_bd();
		$ordem = validaVarPost('txt_ordem');
		$cod_pizzas = validaVarPost('sel_pizza');
		$dia_semana = validaVarPost("dia_semana");
		$cod_tamanhos = validaVarPost('sel_tamanho');
		$preco = moeda2bd(validaVarPost('txt_preco'));

		$ordem = 0;

		$sql_verifica_pizza = "select * from ipi_pizzaria_pizza_semana where dia_semana = '$dia_semana' and cod_pizzarias ='$cod_pizzarias' and status='DIA'";
		$res_verifica_pizza = mysql_query($sql_verifica_pizza);
		$num_verifica = mysql_num_rows($res_verifica_pizza);
		//if($num_verifica>0)
		//{
		//echo "<script>alert('Este dia ja esta contem um ".TIPO_PRODUTO."');</script>";
		//}
		//else
		{
			//$sql_atualiza_ordems = "update ipi_pizzaria_pizza_semana set ordem=ordem+1 where cod_pizzarias = '$cod_pizzarias' and ordem >= '$ordem'";
			//$res_atualiza_ordems = mysql_query($sql_atualiza_ordems);
			
			$sql_inserir = sprintf("insert into ipi_pizzaria_pizza_semana(cod_pizzarias,cod_pizzas,cod_tamanhos,ordem,preco_pizza_semana,dia_semana,status) values(%d,%d,%d,%d,'%s',%d,'%s')",$cod_pizzarias,$cod_pizzas,$cod_tamanhos,$ordem,$preco,$dia_semana,'DIA');
			$res_inserir = mysql_query($sql_inserir);

		    if($res_inserir)
		    {
		    	 mensagemOK(TIPO_PRODUTO." do Dia agendada com sucesso");
		    }
		    else
		    {
		      mensagemErro("Erro");
		    }
	  }
		desconectar_bd($con);
		break;
	case 'excluir':
		 $dia_semana = validaVarPost('dia_semana');
		 $cod_pizzarias = validaVarPost('cod_pizzarias');
		 $cod_pizzas = validaVarPost('cod_pizzas');
		 $cod_tamanhos = validaVarPost('cod_tamanhos');

		 if($cod_pizzas)
		 {
 			$con = conectar_bd();

 			$sql_verifica_se_tem_atual = "select * from ipi_pizzas_ipi_tamanhos where cod_pizzarias = '".$cod_pizzarias."' and cod_tamanhos = '".$cod_tamanhos."' and cod_pizzas = '".$cod_pizzas."' and pizza_dia = 1";// and dia_semana = '$dia_semana'
 			$res_verifica_se_tem_atual = mysql_query($sql_verifica_se_tem_atual);
 			$num_verifica_se_tem_atual = mysql_num_rows($res_verifica_se_tem_atual);
 			//echo "<br>1: ".$sql_verifica_se_tem_atual;

 			if($num_verifica_se_tem_atual>0)
 			{
 				while($obj_verifica_se_tem_atual = mysql_fetch_object($res_verifica_se_tem_atual))
 				{
 					$sql_pega_preco_antigo = "select preco_antigo from ipi_pizzaria_pizza_semana where cod_pizzarias = '".$cod_pizzarias."' and cod_pizzas = ".$cod_pizzas." and dia_semana = '".$dia_semana."' and cod_tamanhos = '".$cod_tamanhos."' and status='DIA'";
		 			//echo "<br>2: ".$sql_pega_preco_antigo;
 					$res_pega_preco_antigo = mysql_query($sql_pega_preco_antigo);
 					$obj_pega_preco_antigo = mysql_fetch_object($res_pega_preco_antigo);
 					
 					$sql_atualiza_preco_antigo = "update ipi_pizzas_ipi_tamanhos set preco =".$obj_pega_preco_antigo->preco_antigo." where cod_pizzarias = '".$cod_pizzarias."' and cod_pizzas = ".$obj_verifica_se_tem_atual->cod_pizzas." and cod_tamanhos = '".$cod_tamanhos."'";
		 			//echo "<br>3: ".$sql_atualiza_preco_antigo;
 					$res_atualizar_preco_antigo = mysql_query($sql_atualiza_preco_antigo);


 					$sql_remover_da_semana = "update ipi_pizzas_ipi_tamanhos set pizza_dia=0 where cod_pizzarias = '".$cod_pizzarias."' and cod_pizzas = ".$cod_pizzas." and cod_tamanhos = '".$cod_tamanhos."'";
		 			//echo "<br>4: ".$sql_remover_da_semana;
	  			$sql_remover_da_semana = mysql_query($sql_remover_da_semana);
	  		}
 			}


 			$sql_excluir = "delete from ipi_pizzaria_pizza_semana where cod_pizzas = '".$cod_pizzas."' AND dia_semana = '".$dia_semana."' and cod_pizzarias = '".$cod_pizzarias."' and status='DIA'";
 			//echo "<br>x: ".$sql_excluir;
 			$res_excluir = mysql_query($sql_excluir);

			if($res_excluir)
	    {
	      mensagemOK(TIPO_PRODUTO." do dia Removida com sucesso");
   			//$sql_atualiza_ordems = "update ipi_pizzaria_pizza_semana set ordem = ordem-1 where ordem >= '$ord_e' and cod_pizzarias = '$cod_pizzarias'";
 				//$sql_atualiza_ordems = mysql_query($sql_atualiza_ordems);
				/*$sql_corrigir_ordem = "select * from ipi_pizzaria_pizza_semana where cod_pizzarias = '$cod_pizzarias'  and status='DIA' order by ordem";
				$res_corrigir_ordem = mysql_query($sql_corrigir_ordem);
				$i = 1;
				while($obj_corrigir_ordem = mysql_fetch_object($res_corrigir_ordem))
				{
					$sql_setar_ordem = "update ipi_pizzaria_pizza_semana set ordem = '$i' where cod_pizzarias='".$obj_corrigir_ordem->cod_pizzarias."' and cod_pizzas = '".$obj_corrigir_ordem->cod_pizzas."' and status='DIA'";  
					$res_setar_ordem = mysql_query($sql_setar_ordem);
					$i++;
				}*/
	    }
	    else
	    {
	      mensagemErro("Erro");
	    }

		 	desconectar_bd($con);
		 }

		break;
	case 'copiar_precos':
 			$con = conectar_bd();

 			$sql_excluir = "delete from ipi_pizzaria_pizza_semana where cod_pizzarias ='$cod_pizzarias' and status='DIA'";
 			$res_excluir = mysql_query($sql_excluir);
			if($res_excluir)
	    {
	    	$sql_copiar = "insert into ipi_pizzaria_pizza_semana(cod_pizzarias,cod_pizzas,cod_tamanhos,ordem,preco_pizza_semana,dia_semana,status) (select $cod_pizzarias,cod_pizzas,cod_tamanhos,ordem,preco_pizza_semana,dia_semana,status from ipi_pizzaria_pizza_semana where cod_pizzarias='$codigo_matriz' and status='DIA')";
	    	$res_copiar = mysql_query($sql_copiar);

	    	$sql_busca_atual = "select * from ipi_pizzas_ipi_tamanhos where cod_pizzarias = $codigo_matriz and cod_tamanhos = 3 and pizza_dia = 1 ";
	    	$res_busca_atual = mysql_query($sql_busca_atual);
	    	$obj_busca_atual = mysql_fetch_object($res_busca_atual);

	    	if($obj_busca_atual->cod_pizzas>0)
	    	{
		    	$sql_remover_da_semana = "update ipi_pizzas_ipi_tamanhos set pizza_dia =0  where cod_pizzarias = '$cod_pizzarias' ";
		    	$sql_remover_da_semana = mysql_query($sql_remover_da_semana);
		    	$sql_atualizar = "update ipi_pizzas_ipi_tamanhos set pizza_dia =1  where cod_pizzarias = '$cod_pizzarias' and cod_pizzas = '".$obj_busca_atual->cod_pizzas."' and cod_tamanhos = 3 ";
		    	//echo $sql_atualizar ;
		    	$res_atualizar = mysql_query($sql_atualizar);

					$sql_preco_atual = "select preco from ipi_pizzas_ipi_tamanhos where cod_pizzas='".$obj_busca_atual->cod_pizzas."' and cod_pizzarias='".$cod_pizzarias."' and cod_tamanhos='3' "; //pega o preco atual
					$res_preco_atual = mysql_query($sql_preco_atual);
					$obj_preco_atual = mysql_fetch_object($res_preco_atual);

						//joga o preco atual como preco antigo na tabela pizzaria_pizza_semana
					$sql_atualizar_preco_antigo_tabela_semana = "update ipi_pizzaria_pizza_semana set preco_antigo = ".$obj_preco_atual->preco."  where cod_pizzarias=".$cod_pizzarias." and cod_pizzas='".$obj_busca_atual->cod_pizzas."' and cod_tamanhos='3' and status='DIA'";
					$res_atualizar_preco_antigo_tabela_semana = mysql_query($sql_atualizar_preco_antigo_tabela_semana);

					$sql_atualiza_preco_semana = "update ipi_pizzas_ipi_tamanhos set preco='".$obj_busca_atual->preco."' where cod_pizzarias = ".$cod_pizzarias." and cod_pizzas ='".$obj_busca_atual->cod_pizzas."' and cod_tamanhos='3'";
					$res_atualiza_preco_semana = mysql_query($sql_atualiza_preco_semana);
		    }

				if($res_copiar)
		    {
		      mensagemOK("Pizzas do Dia Copiadas com sucesso");
		    }
		    else
		    {
		      mensagemErro("Erro");
	    	}
		 	}
		 	desconectar_bd($con);
		break;
	case 'setar_atual':
			$cod_pizzarias = validaVarPost('cod_pizzarias');
			$pizza_setar = validaVarPost('pizza_setar');
			$preco_setar = validaVarPost('preco_setar');
			$cod_tamanhos = validaVarPost('cod_tamanhos_setar');

 			$con = conectar_bd();

			/*
 			$sql_verifica_se_tem_atual = "select * from ipi_pizzas_ipi_tamanhos where cod_pizzarias = '$cod_pizzarias' and cod_tamanhos = 3 and pizza_semana = 1";
 			$res_verifica_se_tem_atual = mysql_query($sql_verifica_se_tem_atual);
 			$num_verifica_se_tem_atual = mysql_num_rows($res_verifica_se_tem_atual);
 			if($num_verifica_se_tem_atual>0)
 			{
 				while($obj_verifica_se_tem_atual = mysql_fetch_object($res_verifica_se_tem_atual))
 				{
 					$sql_pega_preco_antigo = "select preco_antigo from ipi_pizzaria_pizza_semana where cod_pizzarias = '$cod_pizzarias' and cod_pizzas = ".$obj_verifica_se_tem_atual->cod_pizzas." and cod_tamanhos = 3 and status='DIA'";
 					$res_pega_preco_antigo = mysql_query($sql_pega_preco_antigo);
 					$obj_pega_preco_antigo = mysql_fetch_object($res_pega_preco_antigo);
 					
 					$sql_atualiza_preco_antigo = "update ipi_pizzas_ipi_tamanhos set preco =".$obj_pega_preco_antigo->preco_antigo." where cod_pizzarias = '$cod_pizzarias' and cod_pizzas = ".$obj_verifica_se_tem_atual->cod_pizzas." and cod_tamanhos = 3";
 					$res_atualizar_preco_antigo = mysql_query($sql_atualiza_preco_antigo);


 					$sql_remover_da_semana = "update ipi_pizzas_ipi_tamanhos set pizza_semana =0 where cod_pizzarias = '$cod_pizzarias' and cod_pizzas = ".$obj_verifica_se_tem_atual->cod_pizzas." and cod_tamanhos = 3";
	  			$sql_remover_da_semana = mysql_query($sql_remover_da_semana);

	  		}
 			}
 			*/

	  	$sql_atualizar = "update ipi_pizzas_ipi_tamanhos set pizza_dia = 1  where cod_pizzarias = '".$cod_pizzarias."' and cod_pizzas = '".$pizza_setar."' and cod_tamanhos = '".$cod_tamanhos."' ";
	  	$res_atualizar = mysql_query($sql_atualizar);
	  	//echo "<br>1: ".$sql_atualizar;

			$sql_preco_atual = "select preco from ipi_pizzas_ipi_tamanhos where cod_pizzas=".$pizza_setar." and cod_pizzarias=".$cod_pizzarias." and cod_tamanhos = '".$cod_tamanhos."' "; //pega o preco atual
	  	//echo "<br>2: ".$sql_preco_atual;
			$res_preco_atual = mysql_query($sql_preco_atual);
			$obj_preco_atual = mysql_fetch_object($res_preco_atual);

				//joga o preco atual como preco antigo na tabela pizzaria_pizza_semana
			$sql_atualizar_preco_antigo_tabela_semana = "update ipi_pizzaria_pizza_semana set preco_antigo = ".$obj_preco_atual->preco."  where cod_pizzarias=".$cod_pizzarias." and cod_pizzas=".$pizza_setar." and cod_tamanhos = '".$cod_tamanhos."' and status='DIA'";
	  	//echo "<br>3: ".$sql_atualizar_preco_antigo_tabela_semana;
			$res_atualizar_preco_antigo_tabela_semana = mysql_query($sql_atualizar_preco_antigo_tabela_semana);

			$sql_atualiza_preco_semana = "update ipi_pizzas_ipi_tamanhos set preco='".$preco_setar."' where cod_pizzarias = ".$cod_pizzarias." and cod_pizzas ='".$pizza_setar."' and cod_tamanhos = '".$cod_tamanhos."'";
	  	//echo "<br>4: ".$sql_atualiza_preco_semana;
			$res_atualiza_preco_semana = mysql_query($sql_atualiza_preco_semana);


			if($res_atualizar)
	    {
	      mensagemOK(TIPO_PRODUTO." da dia definida com sucesso");
	    }
	    else
	    {
	      mensagemErro("Erro");
    	}
		 	desconectar_bd($con);
		break;
	case 'remover_atual':
			$cod_pizzarias = validaVarPost('cod_pizzarias');
			$pizza_setar = validaVarPost('pizza_setar');
			$cod_tamanhos = validaVarPost('cod_tamanhos_setar');
			$dia_semana = validaVarPost('dia_semana_setar');

 			$con = conectar_bd();

 			$sql_verifica_se_tem_atual = "select * from ipi_pizzas_ipi_tamanhos where cod_pizzarias = '".$cod_pizzarias."' and cod_tamanhos = '".$cod_tamanhos."' and cod_pizzas = '".$pizza_setar."' and pizza_dia = 1";// and dia_semana = '$dia_semana'
 			//echo "<Br>1: ".$sql_verifica_se_tem_atual;
 			$res_verifica_se_tem_atual = mysql_query($sql_verifica_se_tem_atual);
 			$num_verifica_se_tem_atual = mysql_num_rows($res_verifica_se_tem_atual);
 			if($num_verifica_se_tem_atual>0)
 			{
 				while($obj_verifica_se_tem_atual = mysql_fetch_object($res_verifica_se_tem_atual))
 				{
 					$sql_pega_preco_antigo = "select preco_antigo from ipi_pizzaria_pizza_semana where cod_pizzarias = '".$cod_pizzarias."' and cod_pizzas = ".$pizza_setar." and dia_semana = '".$dia_semana."' and cod_tamanhos = '".$cod_tamanhos."' and status='DIA'";
 			//echo "<Br>2: ".$sql_pega_preco_antigo;
 					$res_pega_preco_antigo = mysql_query($sql_pega_preco_antigo);
 					$obj_pega_preco_antigo = mysql_fetch_object($res_pega_preco_antigo);
 					
 					$sql_atualiza_preco_antigo = "update ipi_pizzas_ipi_tamanhos set preco =".$obj_pega_preco_antigo->preco_antigo." where cod_pizzarias = '".$cod_pizzarias."' and cod_pizzas = ".$pizza_setar." and cod_tamanhos = '".$cod_tamanhos."'";
 			//echo "<Br>3: ".$sql_atualiza_preco_antigo;
 					$res_atualizar_preco_antigo = mysql_query($sql_atualiza_preco_antigo);


 					$sql_remover_da_semana = "update ipi_pizzas_ipi_tamanhos set pizza_dia=0 where cod_pizzarias = '".$cod_pizzarias."' and cod_pizzas = ".$pizza_setar." and cod_tamanhos = '".$cod_tamanhos."'";
 			//echo "<Br>4: ".$sql_remover_da_semana;
	  			$sql_remover_da_semana = mysql_query($sql_remover_da_semana);
	  		}
 			}





	  	/*$sql_atualizar = "update ipi_pizzas_ipi_tamanhos set pizza_semana =1  where cod_pizzarias = '$cod_pizzarias' and cod_pizzas = ".$pizza_setar." and cod_tamanhos = 3 ";
	  	$res_atualizar = mysql_query($sql_atualizar);


			$sql_preco_atual = "select preco from ipi_pizzas_ipi_tamanhos where cod_pizzas=".$pizza_setar." and cod_pizzarias=".$cod_pizzarias." and cod_tamanhos='3' "; //pega o preco atual
			$res_preco_atual = mysql_query($sql_preco_atual);
			$obj_preco_atual = mysql_fetch_object($res_preco_atual);

				//joga o preco atual como preco antigo na tabela pizzaria_pizza_semana
			$sql_atualizar_preco_antigo_tabela_semana = "update ipi_pizzaria_pizza_semana set preco_antigo = ".$obj_preco_atual->preco."  where cod_pizzarias=".$cod_pizzarias." and cod_pizzas=".$pizza_setar." and cod_tamanhos='3'";
			$res_atualizar_preco_antigo_tabela_semana = mysql_query($sql_atualizar_preco_antigo_tabela_semana);

			$sql_atualiza_preco_semana = "update ipi_pizzas_ipi_tamanhos set preco='".$preco_setar."' where cod_pizzarias = ".$cod_pizzarias." and cod_pizzas ='".$pizza_setar."' and cod_tamanhos='3'";
			$res_atualiza_preco_semana = mysql_query($sql_atualiza_preco_semana);*/


			if($sql_remover_da_semana && $res_verifica_se_tem_atual)
	    {
	      mensagemOK(TIPO_PRODUTO."do dia definida com sucesso");
	    }
	    else
	    {
	      mensagemErro("Erro");
    	}
		 	desconectar_bd($con);
		break;
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

<script>
window.addEvent('domready', function()
{
	var tabs = new Tabs('tabs'); 
});

function remover_atual()
{
	var form = new Element('form', {
    'action': '<?
    echo $_SERVER['PHP_SELF']?>',
    'method': 'post'
  });

  var input3 = new Element('input', {
    'type': 'hidden',
    'name': 'acao',
    'value': 'remover_atual'
  });

  var input1 = new Element('input', {
    'type': 'hidden',
    'name': 'cod_pizzarias',
    'value': '<? echo $cod_pizzarias ?>'
  });
	input3.inject(form);
	input1.inject(form);
  $(document.body).adopt(form);
  form.submit();	
}

function copiar_precos()
{
	var form = new Element('form', {
    'action': '<?
    echo $_SERVER['PHP_SELF']?>',
    'method': 'post'
  });

  var input3 = new Element('input', {
    'type': 'hidden',
    'name': 'acao',
    'value': 'copiar_precos'
  });

  var input1 = new Element('input', {
    'type': 'hidden',
    'name': 'cod_pizzarias',
    'value': '<? echo $cod_pizzarias ?>'
  });
	input3.inject(form);
	input1.inject(form);
  $(document.body).adopt(form);
  form.submit();	
}

</script>
<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a href="javascript:;"><?php echo TIPO_PRODUTO; ?> do Dia</a></li>
    </ul>
  </div>
    
  <!-- Tab Editar -->
  <div class="painelTab">
  	<form name='frmPizzaria' method='post'>
		  <table align="center" class="caixa" cellpadding="0" cellspacing="0" >
		    <tr>
		      <td class="legenda tdbl tdbt sep" align="right"><label for="cod_pizzarias"><?php echo TIPO_EMPRESA; ?>:</label></td>
		      <td class="tdbt sep">&nbsp;</td>
		      <td class="tdbr tdbt sep">
		        <select name="cod_pizzarias" id="cod_pizzarias">
		          <?
			      	$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

		          $cone = conectabd();
		          $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
		          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
		          
		          while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
			        {
				        echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
				        if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
				        {
					        echo 'selected';
				        }
				        echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
			        }
		          desconectabd($cone);
		          ?>
		        </select>
		      </td>
		    </tr> 
		    <tr>
		    	<td class='tdbl tdbb sep'>&nbsp;</td>
		    	<td class='tdbb sep'>&nbsp;</td>
		    	<td class='tdbr tdbb sep'><input type='submit' value='Buscar' class='botao'/></td>
		    </tr>
		  </table>
		</form>
    <!-- Conteúdo -->
    <td class="conteudo" align="center" >
    	<? if($cod_pizzarias): ?>
    		<br/><br/>
	    	<div id="conteudodiv" align="center" >
	    		<table>
			    	<form name='frmAdicionar' method='post' onsubmit='return validaRequeridos(this)'>
			    		<tr>
					        <td class="legenda" align="right"><label class=" requerido" for="dia_semana">Dia da Semana</label></td>
					        <td >
					            <select class="requerido" name="dia_semana" id="dia_semana">
					              <option value=""></option>
					                <?
					                for($i = 0; $i<=6 ; $i++) 
					                {
					                    echo '<option value="'. $i . '"';
					                    if("$i" === $dia_filtro)
					                    {
					                        echo " SELECTED ";
					                    }
					                    echo '>' .$dia_semana_completo[($i)] . '</option>';
					                }
					                
					                ?>
					            </select>
					        </td>
					    </tr> 

			    		<tr><td><label for='sel_pizza' class='requerido'><?php echo TIPO_PRODUTO; ?></label></td>
			    		<td><select name='sel_pizza' class='requerido'>
			    		<option value=''></option>
			    		<?
						    $con = conectabd();
		    		    $sql_busca_pizza = "select * from ipi_pizzas piz inner join ipi_pizzas_ipi_tamanhos pt on pt.cod_pizzas = piz.cod_pizzas inner join ipi_pizzarias pizr on pt.cod_pizzarias = pizr.cod_pizzarias where pt.cod_pizzarias='".$cod_pizzarias."' group by piz.pizza ORDER BY pizza"; 
		    		    $res_busca_pizza = mysql_query($sql_busca_pizza);
		    		    while($obj_busca_pizza = mysql_fetch_object($res_busca_pizza))
				        {
				          echo '<option value='.$obj_busca_pizza->cod_pizzas.'>'.$obj_busca_pizza->pizza.'</option>';
				        }

				      ?>
			    		</select></td></tr>

			    		<tr><td><label for='sel_tamanho' class='requerido'>Tamanho</label></td>
			    		<td><select name='sel_tamanho' class='requerido'>
			    		<option value=''></option>
			    		<?
						    $con = conectabd();
		    		    $sql_tamanho = "SELECT * FROM ipi_tamanhos WHERE situacao = 'ATIVO'"; 
		    		    $res_tamanho = mysql_query($sql_tamanho);
		    		    while($obj_tamanho = mysql_fetch_object($res_tamanho))
				        {
				          echo '<option value='.$obj_tamanho->cod_tamanhos.'>'.$obj_tamanho->tamanho.'</option>';
				        }
				      ?>
			    		</select></td></tr>

			    		<tr><td><label for='txt_preco' name="Preco" class='requerido'>Preço</label></td>
			    		<td><input type='text' size='8' max-size='3' class='requerido' onkeypress="return formataMoeda(this, '.', ',', event)" name='txt_preco'/></td></tr>
			    		<tr><td><input type='hidden' name='acao' value='adicionar'/><input type='hidden' name='cod_pizzarias' value='<? echo $cod_pizzarias ?>'/></td><td><input type='submit' value='Adicionar' class='botao' /></td></tr>
			    	</form>
		    	</table>
	    	</div>
	    	<div id="pizzas_adicionadas" align="center">
	    	<br/><br/><br/>


	    	<?
				    $con = conectabd();


				    $sql_pizza_semana = "select ips.*,piz.pizza,piz.cod_pizzas,t.tamanho,ips.cod_tamanhos from ipi_pizzaria_pizza_semana ips inner join ipi_pizzarias pizr on pizr.cod_pizzarias=ips.cod_pizzarias inner join ipi_pizzas piz on piz.cod_pizzas = ips.cod_pizzas inner join ipi_tamanhos t on t.cod_tamanhos = ips.cod_tamanhos where ips.cod_pizzarias ='$cod_pizzarias' and ips.status='DIA' order by dia_semana,piz.pizza ";
				    $res_pizza_semana = mysql_query($sql_pizza_semana);
				    $num_pizza_semana = mysql_num_rows($res_pizza_semana);
				    echo '<table class="cabecalhoEdicao" style="width:900px;" cellspacing="0" cellpadding="0">';
				    echo '<tbody><tr><td style="color:white;">Agenda de '.TIPO_PRODUTOS.'</td><td>'.($num_pizza_semana<=0 ? '<input type="button" value="Copiar Agenda da '.TIPO_EMPRESA.' matriz" class="botao" onclick="copiar_precos()"/>' : '').'</td></tr></tbody>';//<input type="button" value="Desativar Pizza atual" class="botao" onclick="remover_atual()"/>
				    echo '</table>';
				    echo '<table class="listaEdicao" style="width: 900px;">';
				    echo '<thead><tr>';
				    echo '<td>Atual?</td>';
				    echo '<td>Dia</td>';
				    echo '<td>Pizza</td>';
				    echo '<td>Tamanho</td>';
				    echo '<td>Preço Promocional</td>';
				    echo '<td>Preço fora<br/> da promoção</td>';
				    echo '<td>Preço no<br/> cardapio</td>';
				    echo '<td>Excluir da Lista?</td>';	
				    echo '</tr></thead>';
				    echo '<tbody>';
				    while($obj_pizza_semana = mysql_fetch_object($res_pizza_semana))
				    {
					    $sql_busca_atual = "select cod_pizzas,preco,pizza_dia from ipi_pizzas_ipi_tamanhos where cod_pizzarias = '".$cod_pizzarias."' and cod_pizzas = '".$obj_pizza_semana->cod_pizzas."'";
					    $res_busca_atual = mysql_query($sql_busca_atual);
					    $obj_busca_atual = mysql_fetch_object($res_busca_atual);
				    	if($obj_busca_atual->pizza_dia==1)
				    	{
				    		$cor = 'background-color:orange';
				    	}else
				    	{
				    		$cor = '';
				    	}

				    	echo '<tr>';
				    	echo '<td align="center" style="'.$cor.'">'.'<form method="post">
				    		<input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'"/>
				    		<input type="hidden" name="acao" value="'.($obj_busca_atual->pizza_dia==1?'remover_atual':'setar_atual').'"/>
				    		<input type="hidden" name="pizza_setar" value="'.$obj_pizza_semana->cod_pizzas.'"/>
				    		<input type="hidden" name="preco_setar" value="'.$obj_pizza_semana->preco_pizza_semana.'"/>
				    		<input type="hidden" name="dia_semana_setar" value="'.$obj_pizza_semana->dia_semana.'"/>
				    		<input type="hidden" name="cod_tamanhos_setar" value="'.$obj_pizza_semana->cod_tamanhos.'"/>
				    		<input type="submit" class="botao" value="'.($obj_busca_atual->pizza_dia==1?'Remover Atual':'Definir Atual').'"/></form>'.'</td>';

				    	echo '<td align="center" style="'.$cor.'">'.$dia_semana_completo[$obj_pizza_semana->dia_semana].'</td>';
				    	echo '<td align="center" style="'.$cor.'">'.$obj_pizza_semana->pizza.'</td>';
				    	echo '<td align="center" style="'.$cor.'">'.$obj_pizza_semana->tamanho.'</td>';
				    	echo '<td align="center" style="'.$cor.'">R$ '.bd2moeda($obj_pizza_semana->preco_pizza_semana).'</td>';
				    	echo '<td align="center" style="'.$cor.'">'.($obj_pizza_semana->preco_antigo? ' R$ '.bd2moeda($obj_pizza_semana->preco_antigo) : '').'</td>';
				    	echo '<td align="center" style="'.$cor.'"> R$ '.bd2moeda($obj_busca_atual->preco).'</td>';
				    	echo '<td align="center" style="'.$cor.'">';
				    	echo '<form method="post">
							    	<input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'"/>
							    	<input type="hidden" name="cod_pizzas" value="'.$obj_pizza_semana->cod_pizzas.'"/>
							    	<input type="hidden" name="cod_tamanhos" value="'.$obj_pizza_semana->cod_tamanhos.'"/>
							    	<input type="hidden" name="dia_semana" value="'.$obj_pizza_semana->dia_semana.'"/>
							    	<input type="hidden" name="acao" value="excluir"/>
							    	<input type="submit" class="botao" value="Excluir"/>
							    	</form>';
				    	echo '</td>';
				    	//echo '<td align="center" style='.$cor.'>'.($cor ? '' : '<form method="post"><input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'"/><input type="hidden" name="acao" value="excluir"/><input type="hidden" name="ord_excluir" value="'.$obj_pizza_semana->ordem.'"/><input type="submit" class="botao" value="Remover"/></form>' ).'</td>';
				    	echo '</tr>';
				    }
				    echo '</tbody></table>';
				    desconectabd($con);
	    	?>
	    	</div>
   		<? endif; ?>
    </td>
    </tr>
  </table>
 </div>   	
</div> 	





<? rodape(); ?>