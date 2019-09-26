<?
require_once 'ipi_req_carrinho_classe.php';
require_once 'sys/lib/php/formulario.php';
if (validaVarPost('tipo') == "formas_pg")
{
	?>
	<table border="0" cellspacing="0" cellpadding="2" class="tabela" align="center" width="310">
	  <thead>
		<?
		$con = conectabd();
		$cod_formas_pizzaria = array();
		if (validaVarPost('tf')=="Entregar")
		{
            $sqlCliente = "SELECT * FROM ipi_clientes c INNER JOIN ipi_enderecos e ON (e.cod_clientes=c.cod_clientes) WHERE c.cod_clientes=" . $_SESSION['ipi_cliente']['codigo'] . " AND e.cod_enderecos=" . validaVarPost('valor');
            $resCliente = mysql_query($sqlCliente);
            $objCliente = mysql_fetch_object($resCliente);

            $sqlPizzaria = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_cep c ON (p.cod_pizzarias=c.cod_pizzarias) WHERE c.cep_inicial<=" . str_replace(".", "", str_replace("-", "", $objCliente->cep)) . " AND c.cep_final>=" . str_replace(".", "", str_replace("-", "", $objCliente->cep)) . " GROUP BY p.cod_pizzarias";
            $resPizzaria = mysql_query($sqlPizzaria);
            $objPizzaria = mysql_fetch_object($resPizzaria);

			$sql_formas_pg = "SELECT * FROM ipi_formas_pg_pizzarias fpp INNER JOIN ipi_formas_pg fp ON (fpp.cod_formas_pg=fp.cod_formas_pg) WHERE fpp.cod_pizzarias = '".$objPizzaria->cod_pizzarias."' AND fpp.disponivel_ecommerce = 1";
		}
		else if (validaVarPost('tf')=="Balcao")
		{
			$sql_formas_pg = "SELECT * FROM ipi_formas_pg_pizzarias fpp INNER JOIN ipi_formas_pg fp ON (fpp.cod_formas_pg=fp.cod_formas_pg) WHERE fpp.cod_pizzarias = '".$_SESSION['ipi_carrinho']['cod_pizzarias']."' AND fpp.disponivel_ecommerce = 1";
		}
		//echo "<Br>sql_formas_pg: ".$sql_formas_pg;
		$res_formas_pg = mysql_query($sql_formas_pg);
		while ($obj_formas_pg = mysql_fetch_object($res_formas_pg))
		{
			$cod_formas_pizzaria[] = mb_strtoupper($obj_formas_pg->forma_pg);
		}
		desconectabd($con);
		/*
		echo "<pre>";
		print_r($cod_formas_pizzaria);
		echo "</pre>";
		*/
		if (count($cod_formas_pizzaria)==0)
			echo utf8_encode("<br /><p align='center'><strong>Forma de pagamento não cadastrada!</strong></p>");

		?>

		<?
		/*  
		As formas de pagamentos são:
		DINHEIRO - pagamentos em dinheiro
		VISANET1 - pagamentos com cartão visa MOSET
		VISANET - pagamentos com cartão visa gateway locaweb
		MASTERCARDNET - pagamentos com cartão master gateway locaweb
		VISANET-CIELO - pagamentos com cartão visa direto na Cielo
		MASTERCARDNET-CIELO - pagamentos com cartão master direto na Cielo
		AMEXNET-CIELO - pagamentos com cartão amex direto na Cielo
		ELONET-CIELO - pagamentos com cartão elo direto na Cielo
		DINERSNET-CIELO - pagamentos com cartão diners direto na Cielo
		DISCOVERNET-CIELO - pagamentos com cartão discover direto na Cielo
		BEBLUE - Crédito
		BEBLUE - Débito
		BEBLUE - Saldo Beblue
    EKKO - Crédito
    EKKO - Débito
    EKKO - Dinheiro
		*/
		?>


		<?
		if (in_array("VISANET", $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_visanet" value="VISANET" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_visanet'><? echo utf8_encode('Cartão de Crédito'); ?> <img src="img/logo_visa.jpg" align="absmiddle" alt ='Cartão VISA'/></label>
		 	</td>
		   </tr>
		<?
		}

		if (in_array("AMEXNET-CIELO", $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_amex" value="AMEXNET-CIELO" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_amex'><? echo utf8_encode('Cartão de Crédito'); ?> <img src="img/logo_amex.jpg" align="absmiddle" alt ='Cartão American Express'/></label>
		 	</td>
		   </tr>
		<?
		}

		if (in_array("ELONET-CIELO", $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_elo" value="ELONET-CIELO" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_elo'><? echo utf8_encode('Cartão de Crédito'); ?> <img src="img/logo_elo.jpg" align="absmiddle" alt ='Cartão ELO'/></label>
		 	</td>
		   </tr>
		<?
		}

		if (in_array("DINERSNET-CIELO", $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_diners" value="DINERSNET-CIELO" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_diners'><? echo utf8_encode('Cartão de Crédito'); ?> <img src="img/logo_diners.jpg" align="absmiddle" alt ='Cartão Diners Club'/></label>
		 	</td>
		   </tr>
		<?
		}

		if (in_array("DISCOVERNET-CIELO", $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_discover" value="DISCOVERNET-CIELO" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_discover'><? echo utf8_encode('Cartão de Crédito'); ?> <img src="img/logo_discover.jpg" align="absmiddle" alt ='Cartão Discover'/></label>
		 	</td>
		   </tr>
		<?
		}

		if (in_array("VISANET-CIELO", $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_cielonet" value="VISANET-CIELO" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_cielonet'><? echo utf8_encode('Cartão de Crédito'); ?> <img src="img/logo_visa.jpg" align="absmiddle" alt ='Cartão VISA'/></label>
		 	</td>
		   </tr>
		<?
		}

		if (in_array("MASTERCARDNET", $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id='forma_mastercard' value="MASTERCARDNET" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_mastercard'><? echo utf8_encode('Cartão de Crédito'); ?> <img src="img/logo_master.jpg" align="absmiddle"  alt ='Cartão MASTERCARD'/> </label>
			</td>
		   </tr>
		<?
		}

		if (in_array("MASTERCARDNET-CIELO", $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id='forma_mastercard' value="MASTERCARDNET-CIELO" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_mastercard'><? echo utf8_encode('Cartão de Crédito'); ?> <img src="img/logo_master.jpg" align="absmiddle"  alt ='Cartão MASTERCARD'/> </label>
			</td>
		   </tr>
		<?
		}

		if (in_array("LEVAR A MAQUINA DE CARTAO", $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_levar_maquina" value="LEVAR A MAQUINA DE CARTAO" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_levar_maquina'><? echo utf8_encode('Levar a máquina de Cartão'); ?></label>
		 	</td>
		   </tr>
		<?
		}

    if (in_array(("ALELO REFEIÇÃO"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_alelo_refeicao" value="<? echo utf8_encode('ALELO REFEIÇÃO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_alelo_refeicao'><? echo utf8_encode('ALELO REFEIÇÃO'); ?></label>
      </td>
       </tr>
    <?
    }

    if (in_array(("AMERICAN"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_american" value="AMERICAN" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_american'><? echo utf8_encode('AMERICAN'); ?></label>
      </td>
       </tr>
    <?
    }

    if (in_array(("ELO CRÉDITO"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_elo_credito" value="<? echo utf8_encode('ELO CRÉDITO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_elo_credito'><? echo utf8_encode('ELO CRÉDITO'); ?></label>
      </td>
       </tr>
    <?
    }
    if (in_array(("ELO DÉBITO"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_elo_debito" value="<? echo utf8_encode('ELO DÉBITO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_elo_debito'><? echo utf8_encode('ELO DÉBITO'); ?></label>
      </td>
       </tr>
    <?
    }

    if (in_array(("MASTER CRÉDITO"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_master_credito" value="<? echo utf8_encode('MASTER CRÉDITO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_master_credito'><? echo utf8_encode('MASTER CRÉDITO'); ?></label>
      </td>
       </tr>
    <?
    }
    if (in_array(("MASTER DÉBITO"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_master_debito" value="<? echo utf8_encode('MASTER DÉBITO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_master_debito'><? echo utf8_encode('MASTER DÉBITO'); ?></label>
      </td>
       </tr>
    <?
    }

    if (in_array(("VISA CRÉDITO"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_visa_credito" value="<? echo utf8_encode('VISA CRÉDITO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_visa_credito'><? echo utf8_encode('VISA CRÉDITO'); ?></label>
      </td>
       </tr>
    <?
    }
    if (in_array(("VISA DÉBITO"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_visa_debito" value="<? echo utf8_encode('VISA DÉBITO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_visa_debito'><? echo utf8_encode('VISA DÉBITO'); ?></label>
      </td>
       </tr>
    <?
    }

    if (in_array(utf8_decode("TICKET RESTAURANTE"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_ticket_rest_debito" value="<? echo ('TICKET RESTAURANTE'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_ticket_rest_debito'><? echo utf8_encode('TICKET RESTAURANTE'); ?></label>
      </td>
       </tr>
    <?
    }





    if (in_array("MAQUINA DE CARTAO DEBITO", $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_levar_maquina_debito" value="MAQUINA DE CARTAO DEBITO" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_levar_maquina_debito'><? echo utf8_encode('Levar a máquina de Cartão de Débito'); ?></label>
      </td>
       </tr>
    <?
    }    

		if (in_array(mb_strtoupper("DINHEIRO"), $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_dinheiro" value="DINHEIRO" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_dinheiro'>Dinheiro</label>
		 	</td>
		   </tr>
		<?
		}

		if (in_array(mb_strtoupper("BEBLUE - Crédito"), $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_beblue_credito" value="<? echo utf8_encode('BEBLUE - CRÉDITO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_beblue_credito'><? echo utf8_encode('BEBLUE - Crédito'); ?></label>
		 	</td>
		   </tr>
		<?
		}

		if (in_array(mb_strtoupper("BEBLUE - Débito"), $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_beblue_debito" value="<? echo utf8_encode('BEBLUE - DÉBITO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_beblue_debito'><? echo utf8_encode('BEBLUE - Débito'); ?></label>
		 	</td>
		   </tr>
		<?
		}

		if (in_array(mb_strtoupper("BEBLUE - Saldo Beblue"), $cod_formas_pizzaria))
		{
		?>
		   <tr>
			<td>
			<input type="radio" name="forma_pagamento" id="forma_beblue_saldo_beblue" value="<? echo utf8_encode('BEBLUE - SALDO BEBLUE'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
			<label for='forma_beblue_saldo_beblue'><? echo utf8_encode('BEBLUE - Saldo Beblue'); ?></label>
		 	</td>
		   </tr>
		<?
		}

    if (in_array(mb_strtoupper("EKKO - Crédito"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_ekko_credito" value="<? echo utf8_encode('EKKO - CRÉDITO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_ekko_credito'><? echo utf8_encode('EKKO - Crédito'); ?></label>
      </td>
       </tr>
    <?
    }

    if (in_array(mb_strtoupper("EKKO - Débito"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_ekko_debito" value="<? echo utf8_encode('EKKO - DÉBITO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_ekko_debito'><? echo utf8_encode('EKKO - Débito'); ?></label>
      </td>
       </tr>
    <?
    }

    if (in_array(mb_strtoupper("EKKO - Dinheiro"), $cod_formas_pizzaria))
    {
    ?>
       <tr>
      <td>
      <input type="radio" name="forma_pagamento" id="forma_ekko_dinheiro" value="<? echo utf8_encode('EKKO - DINHEIRO'); ?>" style="border: 0px; background: none;" onclick="javascript:verificar_forma_pagamento(this);" />
      <label for='forma_ekko_dinheiro'><? echo utf8_encode('EKKO - Dinheiro'); ?></label>
      </td>
       </tr>
    <?
    }



		?>
	  </thead>

	</table>
  
	<?
}



if (validaVarPost('tipo') == "horarios_agendamento")
{
	$con = conectar_bd();
	$cod_pizzarias = '';
	if (validaVarPost('tf')=="Entregar")
	{
    $sqlCliente = "SELECT * FROM ipi_clientes c INNER JOIN ipi_enderecos e ON (e.cod_clientes=c.cod_clientes) WHERE c.cod_clientes=" . $_SESSION['ipi_cliente']['codigo'] . " AND e.cod_enderecos=" . validaVarPost('valor');
    $resCliente = mysql_query($sqlCliente);
    $objCliente = mysql_fetch_object($resCliente);

    $sqlPizzaria = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_cep c ON (p.cod_pizzarias=c.cod_pizzarias) WHERE c.cep_inicial<=" . str_replace(".", "", str_replace("-", "", $objCliente->cep)) . " AND c.cep_final>=" . str_replace(".", "", str_replace("-", "", $objCliente->cep)) . " GROUP BY p.cod_pizzarias";
    $resPizzaria = mysql_query($sqlPizzaria);
    $objPizzaria = mysql_fetch_object($resPizzaria);
    $cod_pizzarias = $objPizzaria->cod_pizzarias;
	}
	else if (validaVarPost('tf')=="Balcao")
	{
		$cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
	}
  
  require("pub_req_fuso_horario1.php");

	$aberta_agora = false;
	$sql_buscar_horarios = "SELECT horario_inicial as inicio,horario_final as fim from ipi_pizzarias_funcionamento where cod_pizzarias = '".$cod_pizzarias."' and dia_semana = '".date('w')."' and CURTIME() < ADDTIME(horario_final, '00:05:01') order by horario_inicial";// and horario_inicial < CURTIME() 
	//echo $sql_buscar_horarios."<br/>";
	$res_buscar_horarios = mysql_query($sql_buscar_horarios);
	$a = -1;
	while($obj_buscar_horarios = mysql_fetch_object($res_buscar_horarios))
	{
		//echo "<br/><Br/>Agora::".date("H:i");
		//echo "<br/>inicio::".$obj_buscar_horarios->inicio;
    //echo "<Br/>fim::".date("H:i",strtotime($obj_buscar_horarios->fim));
    //echo "<Br/>fim+5::".date("H:i",strtotime($obj_buscar_horarios->fim)+301);
		// if(date("H:i")>= $obj_buscar_horarios->inicio && date("H:i")<= date("H:i",strtotime($obj_buscar_horarios->fim)+301) ) $aberta_agora = true;
		########### TIRADO O LIMITE DE 5MIN TEMPORARIAMENTE####################
		######### PARA FUNCIONAR A LOJA ABERTA ENTRE HORÁRIOS 23:30 À 00:00
		##### POIS SE FOR UM HORÁRIO EX.: 23:40 NUNCA VAI CAIR ABERTO
		##### 23:40 <= 00:05 ? não

		if ( date("H:i") >= date("H:i", strtotime($obj_buscar_horarios->inicio)) && date("H:i") <= date("H:i", strtotime($obj_buscar_horarios->fim) ) ) $aberta_agora = true;
    //if ( date("H:i") >= $obj_buscar_horarios->inicio && date("H:i") <= date("H:i", strtotime($obj_buscar_horarios->fim) ) ) $aberta_agora = true;

		$a++;
		$arr_funcionamento[$a]["inicio"] = $obj_buscar_horarios->inicio;
		$arr_funcionamento[$a]["fim"] = $obj_buscar_horarios->fim;

	}

	$hora_inicial = ($a>0 ? $arr_funcionamento[0]["inicio"] : '23:30:00');
	$hora_final = ($a>0 ? $arr_funcionamento[$a]["fim"] : '23:59:59');

	//echo $hora_final." am$a m".($a-1)."--".$arr_funcionamento[($a-1)]["fim"];
	$m_final = date('i',strtotime($hora_final)+301);
	$hora_inicial = date('H',strtotime($hora_inicial));
	$hora_final = date('H',strtotime($hora_final));

	$sql_selecionar_horario_entrega = "SELECT tempo_entrega from ipi_pizzarias_horarios where cod_pizzarias = '".$cod_pizzarias."' and dia_semana = '".date('w')."' and CURTIME() between horario_inicial_entrega and horario_final_entrega";
//	echo "<br/>".$sql_selecionar_horario_entrega."<br/>";
	$res_selecionar_horario_entrega = mysql_query($sql_selecionar_horario_entrega);
	$obj_selecionar_horario_entrega = mysql_fetch_object($res_selecionar_horario_entrega);
	if($obj_selecionar_horario_entrega->tempo_entrega !="")
	{
		$minutos_adicionais = $obj_selecionar_horario_entrega->tempo_entrega;
	}
	else
	{
		$sql_selecionar_horario_entrega = "SELECT tempo_entrega from ipi_pizzarias_horarios where cod_pizzarias = '".$cod_pizzarias."' and dia_semana = '".date('w')."' and horario_inicial_entrega > CURTIME() order by horario_inicial_entrega ASC LIMIT 1";
	//	echo "22<br/>".$sql_selecionar_horario_entrega."<br/>";
		$res_selecionar_horario_entrega = mysql_query($sql_selecionar_horario_entrega);
		$obj_selecionar_horario_entrega = mysql_fetch_object($res_selecionar_horario_entrega);
		if($obj_selecionar_horario_entrega->tempo_entrega !="")
		{
			$minutos_adicionais = $obj_selecionar_horario_entrega->tempo_entrega;
		}
		else
		{
			$minutos_adicionais = 46;
		}
	}
  $hora_habil = false;
  //$minutos_adicionais = 25;
  
  $data_hora_corte = strtotime("+ $minutos_adicionais minutes");
 // echo date("H:i:s",$data_hora_corte)." --==-- m=".$minutos_adicionais;
  
  ?>

    <h3 class=''>AGENDAMENTO</h3>
    <div id="deseja_agendar" style='display: none;'>
    	
      Deseja agendar?
      <select name="agendamento" id="agendamento" onchange="javascript:liberar_horario(this);" style='display: block; color:black'>
          <option value="">&nbsp;</option>
          <option value="Sim">Sim</option>
          <?
            if($aberta_agora)
            {
              echo utf8_encode('<option value="Não">Não</option>');
            }
          ?>
      </select>
      </br />
    </div>

    <div id="aviso_agendar" style='display: none;'>

      <? echo utf8_encode("Agora são ".date('H:i')." e nossas pizzarias ainda estão fechadas!!! <br/> Mas você pode agendar!"); ?>
      <br />
    </div>

    <div id="qual_horario" style='display: none;'>
    	  Por volta de que horas deseja agendar?
				  <select name="horario" id="horario" style="color:black">
				    <option value="">&nbsp;</option>
				    <?     /*<? echo "$hora_inicial -a= $obj_buscar_horarios->fim =a- $hora_final -- $m_final" ?> */
				    // perguntar sobre buraco entre horarios -> o proximo disponivel vira o de agendamento

				    // Pega o horário de corte 
				    // pois como foi liberado todos os horários de funcionamento das pizzarias
				    // liberou agendamentos também
				    // assim ele volta ao funcionamento normal 
				    // baseado na hora em que termina a centralização de pedidoss
				     if (defined("HORARIO_FIM_CENTRAL_PIZZARIA")){
				     	 $aux = explode(":", HORARIO_FIM_CENTRAL_PIZZARIA);
				     	$hora_inicial =  $aux[0];
				     }

				    
				    for($h = $hora_inicial; $h <= $hora_final; $h++)
				    {
				      if($h == $hora_inicial)
				      {
				        $m_inicial = 30;
				      }
				      else
				      {
				        $m_inicial = 0;
				      }
				      
				      for($m = $m_inicial; $m < 60; $m += 15)
				      {
				      	if($h == $hora_final && $m >= $m_final) break;;

				      	$permitido = false;
				      	for($z = 0;$z<=$a;$z++)
				      	{
				      		if((sprintf('%02d', $h).':'.sprintf('%02d', $m)).':00' >= $arr_funcionamento[$z]["inicio"] && (sprintf('%02d', $h).':'.sprintf('%02d', $m)).':00' <= $arr_funcionamento[$z]["fim"])
				      		{
				      			$permitido = true;
				      			break;;
				      		}
				     		}
				        
				        if(mktime($h,$m,00, date("m"),date("d"),date("Y")) > $data_hora_corte && $permitido)
				        {
				          echo '<option value="'.sprintf('%02d', $h).':'.sprintf('%02d', $m).'">'.sprintf('%02d', $h).':'.sprintf('%02d', $m).'</option>';
				          $hora_habil = true;
				        }
				      }
				    }

				    if(!$hora_habil) 
				    {
				      //echo '<option value="">Horário fora da capacidade de entrega da pizzaria</option>';
				      echo utf8_encode('<option value="">Nenhum horário disponível para agendar</option>');
				    }?>
				 
				  </select>
    </div>

             
  	<script>
      <?
        if(!$aberta_agora)
        {
          ?>
            document.getElementById("deseja_agendar").style.display = "none";
            document.getElementById("aviso_agendar").style.display = "block";
            document.getElementById('agendamento').value="Sim";   
            document.getElementById('agendar').value="Sim";   
            document.getElementById('qual_horario').style.display="block";
            document.getElementById('horario').value="def";                  
          <?
        }else
        {
        	?>
        	document.getElementById('deseja_agendar').style.display="block";
          //document.getElementById("aviso_agendar").style.display = "none";
        	<?
        }
      ?>
  	</script>
  <?

  desconectabd($con);
}

if(validaVarPost("tipo") == "validar_cupom")
{
	$con = conectabd();
	$cupom = validaVarPost('cupom');
	$arr_json = array();

	$cupom_valido = "validou";
	$motivo = "";
	$confirmar_acao = false;

	//VALIDAÇÃO SE CUPOM EXISTE
	$sql_contagem = "SELECT * FROM ipi_cupons WHERE cupom = '$cupom'";
  $res_contagem = mysql_query($sql_contagem);
  $qtd_cupoms = mysql_num_rows($res_contagem);
  $obj_contagem = mysql_fetch_object($res_contagem);

  if($qtd_cupoms > 0)
  {
    $cupom_valido = "validou";
  }
  else
  {
    $cupom_valido = "nao_validou";
    $motivo = "Número de cupom inválido ($cupom)!'";
  }


  //VALIDAÇÃO DA PIZZARIA
  if($cupom_valido=="validou")
  {
  	if($_SESSION['ipi_cliente']['autenticado'] == true && $_SESSION['ipi_carrinho']['buscar_balcao'] != "Balcão")
  	{
  		$arr_pizzarias = array();
  		$sql_buscar_pizzarias_enderecos = "SELECT cep.cod_pizzarias FROM ipi_cep cep join ipi_enderecos end WHERE cep.cep_inicial <= replace(end.cep,'-','') AND cep.cep_final >= replace(end.cep,'-','') and end.cod_clientes ='".$_SESSION['ipi_cliente']['codigo']."'";
  		$res_buscar_pizzarias_enderecos = mysql_query($sql_buscar_pizzarias_enderecos);
  		while($obj_buscar_pizzarias_enderecos = mysql_fetch_object($res_buscar_pizzarias_enderecos))
  		{
  			$arr_pizzarias[] = $obj_buscar_pizzarias_enderecos->cod_pizzarias;
  		}

  		$sql_buscar_nome_pizzaria = "SELECT cidade,bairro from ipi_pizzarias where cod_pizzarias = '".$_SESSION['ipi_carrinho']['cod_pizzarias']."'";
	    $res_buscar_nome_pizzaria = mysql_query($sql_buscar_nome_pizzaria);
	    $obj_buscar_nome_pizzaria = mysql_fetch_object($res_buscar_nome_pizzaria);

	    $nome_pizzaria_atual = $obj_buscar_nome_pizzaria->cidade." - ".$obj_buscar_nome_pizzaria->bairro;
	    $cod_pizzarias = implode(',',$arr_pizzarias);
  	}
  	else
  	{
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

	    $sql_buscar_nome_pizzaria = "SELECT cidade,bairro from ipi_pizzarias where cod_pizzarias = '$cod_pizzarias'";
	    $res_buscar_nome_pizzaria = mysql_query($sql_buscar_nome_pizzaria);
	    $obj_buscar_nome_pizzaria = mysql_fetch_object($res_buscar_nome_pizzaria);

	    $nome_pizzaria_atual = $obj_buscar_nome_pizzaria->cidade." - ".$obj_buscar_nome_pizzaria->bairro;
  	}
  	

  	{
	  	
	    $sql_verifica_pizzaria = "SELECT pc.cod_cupons,p.cidade,p.bairro,pc.cod_pizzarias,(SELECT COUNT(*) from ipi_pizzarias_cupons where cod_cupons = c.cod_cupons and cod_pizzarias in ($cod_pizzarias)) as pizzaria_aprovada,(SELECT COUNT(*) from ipi_pizzarias where situacao='ATIVO') as total_pizzarias FROM ipi_pizzarias_cupons pc inner join ipi_cupons c on c.cod_cupons = pc.cod_cupons inner join ipi_pizzarias p on p.cod_pizzarias = pc.cod_pizzarias WHERE c.cupom = '$cupom'";// and pc.cod_pizzarias in($cod_pizzarias)

	    //$arr_json['query'] =  utf8_encode($sql_verifica_pizzaria);
	    //die();
		  $res_verifica_pizzaria = mysql_query($sql_verifica_pizzaria);
		  $num_registros = mysql_num_rows($res_verifica_pizzaria);
		  $obj_verifica_pizzaria = mysql_fetch_object($res_verifica_pizzaria);	



		  if($num_registros==$obj_verifica_pizzaria->total_pizzarias)
		  {
		  	$cupom_valido = "validou";
		  }
		  else//$obj_verifica_pizzaria->pizzaria_aprovada && 
		  {
		  	if($obj_verifica_pizzaria->pizzaria_aprovada>=1)//$obj_verifica_pizzaria->pizzaria_aprovada && 
		  	{
			  	$cupom_valido = "validou";
			  	$motivo = "";//"Este cupom só pode ser utilizado na pizzaria: ".$obj_verifica_pizzaria->cidade." - ".$obj_verifica_pizzaria->bairro;
			  	$confirmar_acao = true;
			  }
			  else
			  {
			  	/*$str_pizzarias = $obj_verifica_pizzaria->cidade." - ".$obj_verifica_pizzaria->bairro;
			  	while($obj_verifica_pizzaria = mysql_fetch_object($res_verifica_pizzaria))
			  	{
			  		$str_pizzarias .= ", ".$obj_verifica_pizzaria->cidade." - ".$obj_verifica_pizzaria->bairro;
			  	}*/
			  	$cupom_valido = "nao_validou";
			  	$motivo = "Este cupom (<b>$cupom</b>) só pode ser utilizado nas pizzarias: ".$str_pizzarias;
			  	if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
			  	{
			  		$motivo = "Este cupom ($cupom) não pode ser utilizado na pizzaria $nome_pizzaria_atual , que é a pizzaria em que você ira retirar seu pedido.";
			  	}
			  	else
			  	{
			  		if($_SESSION['ipi_cliente']['autenticado'] == true)
			  		{
					  	$motivo = "Este cupom ($cupom) não pode ser utilizado nas pizzarias, quem atendem seus endereços.";
					  }
					  else
					  {
					  	$motivo = "Este cupom ($cupom) não pode ser utilizado na pizzaria $nome_pizzaria_atual , que é a pizzaria que atende seu endereço.";
					  }
				  }
			  	$confirmar_acao = true;
			  }
		  }
		}
  }

  //VALIDAÇÂO DA DATA DE INICIO E DATA DE VENCIMENTO
  if($cupom_valido=="validou")
  {	

  	if (date("Y-m-d", strtotime($obj_contagem->data_inicio))>date("Y-m-d"))
    {
    	$cupom_valido = "nao_validou";
      $motivo = 'Este cupom só pode ser utilizado apartir de: '.bd2data($obj_contagem->data_inicio);

    }
    else if (date("Y-m-d", strtotime($obj_contagem->data_validade))<date("Y-m-d"))
    {
    	$cupom_valido = "nao_validou";
      $motivo = "Este cupom venceu em: ".bd2data($obj_contagem->data_validade);
	    ////echo "<script>alert('Este cupom venceu em: ".bd2data($obj_contagem->data_validade)."');</script>";
    }
  }

  //VALIDACAO SE CUPOM JA FOI UTILIZADO
  if($cupom_valido=="validou")
  {
  	if ($obj_contagem->valido==0)
		{
			$cupom_valido = "nao_validou";
      $motivo = "Cupom já utilizado ($cupom)!";
			//echo "<script>alert('');</script>";
		}
	}

	$arr_json["valido"] = $cupom_valido;
	$arr_json["motivo"] = utf8_encode($motivo);
	
	echo json_encode($arr_json);
	desconectar_bd($con);
}

if (validaVarPost('tipo') == "carregarPizzas")
{
	 if (validaVarPost('tamanho_pizza'))
    {
			require_once 'bd.php';
      $conexao = conectabd();
				
			$tamanho = 	validaVarPost('tamanho_pizza', "/[0-9]+/");
			$id = validaVarPost('id_pizza',"/[0-9]+/");
			$qtde_sabores = validaVarPost('qtde_sabores',"/[0-9]+/");
			if($qtde_sabores=="" || ( $qtde_sabores!=1 && $qtde_sabores!=2 && $qtde_sabores!=3 && $qtde_sabores!=4) )
			{
				$qtde_sabores = 1;
			}
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
			
			$combo = validaVarPost('combo');
			if (validaVarPost('promo')!="sim")
		  {
				 $sabor = validaVarPost('sabor');
				 
				 if($combo=='sim')
				 	$preco = '"   (Combo)"';
				 else
				 	$preco = 'pt.preco';

		      if ($sabor)
		      {
		      	if ($combo=="sim")

			      	{

			      		   $sql_combos = "SELECT * FROM ipi_combos_produtos WHERE cod_combos=".$_SESSION['ipi_carrinho']['combo']['cod_combos'];
					    $res_combos = mysql_query( $sql_combos );
					    $num_combos = mysql_num_rows( $res_combos );
					    for ($a=0; $a<$num_combos; $a++)
					    {
					    	if ($_SESSION['ipi_carrinho']['combo']['produtos'][$a]['foi_pedido']=="N")
					    	{
					    		$sabores = $_SESSION['ipi_carrinho']['combo']['produtos'][$a]['cod_pizzas_combo'];
					    		break;
					    	}

					    }

			      		$sqlBuscaPizzas = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt INNER JOIN ipi_pizzas p on p.cod_pizzas = pt.cod_pizzas where pt.cod_pizzarias = $cod_pizzarias and pt.cod_tamanhos = $tamanho and pt.cod_pizzas in ($sabores) and p.venda_online = 1 ORDER BY p.pizza";
			      		// echo $sqlBuscaPizzas;
			      	}
			      	else{
		          $sqlBuscaPizzas = "SELECT *,".$preco." as precos FROM ipi_pizzas_ipi_tamanhos pt INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE pt.cod_pizzarias = '".$cod_pizzarias."' AND pt.cod_tamanhos=" . $tamanho . " AND p.cod_tipo_pizza='".$sabor."' and p.venda_online = 1 ORDER BY p.pizza";
		      }
		          // echo "<h1>".$sabor;
		          // die();
		      }

		      // nao mexer
		      else
		      {
		          $sqlBuscaPizzas = "SELECT *,".$preco." as precos FROM ipi_pizzas_ipi_tamanhos pt INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE pt.cod_pizzarias = '".$cod_pizzarias."' AND pt.cod_tamanhos=" . $tamanho . " and p.venda_online = 1 ORDER BY p.pizza";
		      }
			// echo $sqlBuscaPizzas;
			}
      else
			if(validaVarPost('promo')=="sim")
			{
				      $sqlBuscaPizzas = "SELECT *,'(Gratis)' as precos FROM ipi_pizzas_ipi_tamanhos pt INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE pt.cod_pizzarias = '".$cod_pizzarias."' AND pt.cod_tamanhos=" . $tamanho . " AND p.tipo='Doce' and p.venda_online = 1 ORDER BY p.pizza";
			
			}
			// $SqBuscalPizzas = "SELECT * FROM ipi_pizzas p inner join ipi_pizzas_ipi_tamanhos pt on pt.cod_pizzas = p.cod_pizzas WHERE pt.cod_pizzarias = $cod_pizzarias AND pt.cod_tamanhos = ".$tamanho." ORDER BY p.pizza";
			 
			//echo "<br>sqlBuscaPizzas: ".$sqlBuscaPizzas;
			//echo $sqlBuscaPizzas;
				$resBuscaPizzas = mysql_query($sqlBuscaPizzas);
				//echo "SQLSLQLSL".$SqBuscalPizzas;
				echo utf8_encode("<div id='cardapio_tudo_pedido' class='cardapio'>");
				//echo "ASDW".$sqlBuscaPizzas;
				//echo utf8_decode("<div class='legenda_pedido_pizza'> <img src='img/pc/legenda_pedido_pizza.png' alt='legenda das pizzas' /></div>");
				while ($objBuscaPizzas = mysql_fetch_object($resBuscaPizzas))
				{
					  $arr_pizzas[] = $objBuscaPizzas->cod_pizzas;
		      	echo  utf8_encode("<div align='center' class='pedido_pizzas".($objBuscaPizzas->pizza_fit ? "_fit" : ($objBuscaPizzas->tipo=="Doce" ? "_doce" : "_salgada" ) )." pizza'>");		    
		      	//echo utf8_encode("<div class='texto_pizza_pedido_botoes'> <a href='javascript:void(0)' onClick='mostrar_detalhes(".$objBuscaPizzas->cod_pizzas.",\"mostrar_sem_prox\",".$id.")' class='cor_marrom2 pedido_pizza_preco_fonte'> <span class='cor_amarelo1'>+</span> DETALHES  </a> </div>");
				  //   $conteudo .= "<div align='center' class='cardapio_pizzas'>";
				  		
						   echo  utf8_encode("<div class='div_pizza_pedido'>");
							 echo utf8_encode('<a href="javascript:void(0)" onClick="carregar_ingredientes('.$id.','.$objBuscaPizzas->cod_pizzas.',\''.mb_strtoupper($objBuscaPizzas->pizza).'\', \''.($objBuscaPizzas->foto_pequena ? "upload/pizzas/".$objBuscaPizzas->foto_pequena : "imgs/pizza_temp.jpg" ).'\')" title="'.$objBuscaPizzas->pizza.'"><img class="pedido_miniatura"  id="pizzas_'.$objBuscaPizzas->cod_pizzas.'" '.($objBuscaPizzas->foto_pequena ? "src=\"upload/pizzas/".$objBuscaPizzas->foto_pequena."\"" :"src=\"imgs/pizza_temp.jpg\"" ).' />'); //cardapio_menu_doces.png
							     	
		            if ($objBuscaPizzas->pizza_semana==1)  //pizza_gostosa_p_03.png.jpg
		            {
		           		echo utf8_encode("<div class='pedido_pizza_semana'> <img src='img/pc/pizza_semana_texto.png' alt='Pizza da semana' /> </div>");
		            }

		            if ($objBuscaPizzas->pizza_dia==1)  //pizza_gostosa_p_03.png.jpg
		            {
		           		echo utf8_encode("<div class='pedido_pizza_semana'> <img src='img/pc/pizza_dia_texto.png' alt='Pizza da semana' /> </div>");
		            }

                if ($objBuscaPizzas->novidade==1)  //pizza_gostosa_p_03.png.jpg
                {
                  echo utf8_encode("<div class='pedido_pizza_semana'> <img src='img/pc/produto_novidade.png' alt='Novidade!!!' /> </div>");
                }

							  echo  utf8_encode("<p class='center'>".mb_strtoupper($objBuscaPizzas->pizza)."</p></a>"); //upload/pizzas/
									 
					  echo  utf8_encode("</div>");
									 
						 if(validaVarPost('promo')!="sim"&&$combo!='sim')
						 {
						 	if($_SESSION['ipi_carrinho']['desconto_balcao'] == 'sim')
						 	{
						 		$objBuscaPizzas->precos = $objBuscaPizzas->precos*0.7;
						 	}

						   echo  utf8_encode("<span class='cor_marrom2 pedido_pizza_preco_fonte'>R$ ".bd2moeda($objBuscaPizzas->precos/$qtde_sabores)."</span>");
						 }
						 else
						 {
						   echo  utf8_encode("<span id='pedido_preco' class='cor_marrom2 pedido_pizza_preco_fonte'> ".$objBuscaPizzas->precos."</span>");
						 }
						 
						 //echo '<img src="img/pc/pedido_caixa_pizza_lupa.png" class="pedido_pizza_lupa" title="Mais detalhes" />';
									 
		     echo  utf8_encode("</div>");
				}
				echo utf8_encode("</div>");


			desconectar_bd($conexao);
		}
}

if(validaVarPost('tipo') == "mostrar_sem_prox")
{
		$con = conectabd();
		$cod = validaVarPost('cod',"/[0-9]+/");
		$id = validaVarPost('id',"/[0-9]+/");
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
		$sql_select_pizza = "select * from ipi_pizzas where cod_pizzas = ".$cod;
		$res_select_pizza = mysql_query($sql_select_pizza);
		$obj_select_pizza = mysql_fetch_object($res_select_pizza);
		//pizza_nutella_g.png
		if($obj_select_pizza->foto_grande)
		{
			echo "<div id='imagem_pizza' class='imagem_pizza' ><img class='tamanho_imagem_pizza' src='upload/pizzas/".($obj_select_pizza->foto_grande)."'/></div>";
		}else
		{
			echo "<div id='imagem_pizza' class='imagem_pizza' ><img class='tamanho_imagem_pizza' src='img/pc/pizza_nutella_g.png'/></div>";
		}
		$fit = false;//"pizza_nutella_g.png"
		if($obj_select_pizza->pizza_fit==1) $fit=true;
		echo "<div id='detalhes_pizza' align='left' class='detalhes_pizza'>";
			echo utf8_encode("<h2 class='".($fit ? 'cor_azulFIT' :'cor_marrom2' )." fonte22 negrito'>".mb_strtoupper($obj_select_pizza->pizza)."</h2>");
			echo '<br />';
        
        $ingredientes = array ();
        $SqlBuscaIngredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_pizzas = " . $obj_select_pizza->cod_pizzas." AND i.consumo = 0	";
        $resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
        while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes))
        {
            $ingredientes[] = $objBuscaIngredientes->ingrediente;
        }
        echo "<div id='cardapio_ingredientes' class='cardapio_texto_ingredientes' ><h2 class='cor_laranja2 fonte16 fonte_muzza1_negrito'> Ingredientes </h2><p class='cor_cinza1 fonte14 fonte_audimat'>".utf8_encode(implode(', ', $ingredientes))."</p></div>";
        
			echo "<br />";
		  $sql_busca_preco = "SELECT * FROM ipi_pizzas_ipi_tamanhos p INNER JOIN ipi_tamanhos t ON (p.cod_tamanhos = t.cod_tamanhos) WHERE p.cod_pizzas = " . $obj_select_pizza->cod_pizzas . " and cod_pizzarias = $cod_pizzarias ORDER BY tamanho";
      $res_busca_preco = mysql_query($sql_busca_preco);
      
        
			echo "<table ><tr>";
			echo "<td colspan='".mysql_num_rows($res_busca_preco)."' class='cor_marrom2 cardapio_texto_valores'>Valores</td></tr><tr>";
			while($obj_busca_preco = mysql_fetch_object($res_busca_preco))
			{
			  $arr_tamanho_aux = explode(')', utf8_encode($obj_busca_preco->tamanho));
				echo "<td width='80px'> <h2 class='fonte_muzza1_negrito fonte14 cor_laranja2'>".$arr_tamanho_aux[0].")</h2>";
				echo "<p width='80px' class='fonte_audimat fonte14 cor_cinza1'>".$arr_tamanho_aux[1]."</p></td>";
			}
			$res_busca_preco = mysql_query($sql_busca_preco);
			echo "</tr><tr >";
			while($obj_busca_preco = mysql_fetch_object($res_busca_preco))
			{
				echo "<td width='17px' class='fonte_audimat fonte14 cor_cinza1'><strong> R$ ".bd2moeda($obj_busca_preco->preco)."</strong></td>";
			}
			
			// onClick="mostrar_detalhes('.$objBuscaPizzas->cod_pizzas.',\'mostrar_detalhes\')
			echo "</table>";
			echo "<br>";
			//echo "<div id='tabela_nutricional' class='tabela_nutricional' ></div>";
		echo "</div>";

		echo "<div class='clear'></div>";
			echo "<div id='cardapio_botoes' class='cardapio_botoes'>";//btn_anterior_p.png btn_proximo_p.png   $.nmTop().close()
				//echo "<a href='javascript:void(0)' onClick='mostrar_detalhes_sem_nodal(".$cod_ant.",\"mostrar_detalhes\")' ><img alt='Anterior' src='img/pc/btn_anterior_p.png' ></img></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:void(0)' onClick='mostrar_detalhes_sem_nodal(".$cod_prox.",\"mostrar_detalhes\")' ><img alt='Proximo' src='img/pc/btn_proximo_p.png' ></img></a>";
				echo "<a href='javascript:void(0)' onclick='{ $.nmTop().close();carregar_ingredientes(".$id.",".$obj_select_pizza->cod_pizzas.",\"".mb_strtoupper($obj_select_pizza->pizza)."\") }'> <img alt='Pedir' class='cardapio_botao_pedir' src='img/pc/btn_pedir_g.png' ></a>";
			echo "</div>";

		desconectabd($con);

}

if(validaVarPost('tipo') == "carregarAdicionais")
{
		$conexao = conectar_bd();
		
		 $tamanho_pizza = validaVarPost('tamanho_pizza', "/[0-9]+/");
		  $num_sabor = validaVarPost('num_sabor', "/[0-9]+/");
		  $qtde_sabor = validaVarPost('qtde_sabor', "/[0-9]+/");

		  if($qtde_sabor=="" || ( $qtde_sabor!=1 && $qtde_sabor!=2 && $qtde_sabor!=3 && $qtde_sabor!=4) )
			{
				$qtde_sabor = 1;
			}
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
		  
			$carrinho = new ipi_carrinho();

			$sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '16' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
      $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
      //  $obj_buscar_promocoes = mysql_fetch_object($res_buscar_promocoes);
            

		?><div id="carrinho_ingredientes_clear<? echo $num_sabor ;?>" class="clear negrito fonte18 cor_marrom2 tipo_letra1"></div>
		
          <div id="carrinho_selecao_adicionais">
           <!-- <div class="ingredientes_adic_topo_branco">  </div>-->
           <br><br>
            <div class="ingredientes_adic_meio_branco cor_marrom2 fonte12 tipo_letra1" style="clear:both">
            		<br/>
	           <h3 class='texto_esquerda'>QUER ADICIONAR ALGO?</h3>
	           <br />
            	<?
              $sqlAdic = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it LEFT JOIN ipi_ingredientes i ON (i.cod_ingredientes=it.cod_ingredientes) WHERE i.adicional AND it.cod_tamanhos='" . $tamanho_pizza . "' AND i.ativo = 1 AND it.cod_pizzarias = '".$cod_pizzarias."' ORDER BY ingrediente";
              $resAdic = mysql_query($sqlAdic);
              $linAdic = mysql_num_rows($resAdic);
              //echo $sqlAdic;
              if ($linAdic > 0)
              {
                echo "<table cellspacing='5' cellpadding='0' border='0' width='750'>";
                echo "<tr><td valign='top' width='200'>";
                $divisor = floor($linAdic / 4);
                if (($linAdic % 4) != 0)
                {
                  $divisor++;
                }
                $arr_ingredientes_troca = array();
                for ($a = 0; $a < $linAdic; $a++)
                {
                  if ((($a % $divisor) == 0) && ($a != 0))
                  {
                      echo "</td><td valign='top' width='200'>";
                  }
                      
                  $objAdic = mysql_fetch_object($resAdic);
                  
                  if($objAdic->destaque != 1)
                  { 
                      $cor = 'cor_marrom2';
                  }

                  $dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
                  $stilo = '';
                  if(mysql_num_rows($res_buscar_promocoes)>0 &&  $objAdic->cod_ingredientes==$carrinho->cod_ingredientes_promocao_16 && $tamanho_pizza==3 && $dia_semana[date("w")]=='Qua')
			            {
			              $objAdic->preco = 0;
			              $stilo = 'style="font-weight:bold;background-color:#FFD200;"';
			            }
                  
                  echo "<div class='ingredientes' style='margin-bottom: 30px;'>";
                  if ($objAdic->foto_pequena)
                  {
                    echo utf8_encode("<input class='check_ingredientes_pedido' type='checkbox' id='adicionais" . $num_sabor."_".$a."' name='ingredientes_adicionais" . $num_sabor . "[]' value='NORMAL###" . $objAdic->cod_ingredientes . "###' style='border: 0; background: none;' /><label class='ingrediente' for='adicionais" . $num_sabor."_".$a."'><img src='upload/ingredientes/".$objAdic->foto_pequena."' width='45' />");
                  }
                  else
                  {
                    echo utf8_encode("<input type='checkbox' class='check_ingredientes_pedido' id='adicionais" . $num_sabor."_".$a."' name='ingredientes_adicionais" . $num_sabor . "[]' value='NORMAL###" . $objAdic->cod_ingredientes . "###' style='border: 0; background: none;' /><label class='ingrediente' for='adicionais" . $num_sabor."_".$a."'><img src='imgs/6_ing_p.png' width='45' />");
                  }

                  if($objAdic->preco>0)
                  {
                  	echo utf8_encode("<div class='item'><div class='infos'><span>".$objAdic->ingrediente_abreviado . "</span><br/><span class='preco'> (R$ " . bd2moeda(arredondar_preco_ingrediente($objAdic->preco, $qtde_sabor)) . ")</span></div></div></label>");
                	}
                	else
                	{
                		echo utf8_encode("<div class='infos'>".$objAdic->ingrediente_abreviado . " <br /> <span class='preco'> Grátis - Clique aqui para <br />adicionar ao seu pedido</span></div></label>");
                	}
                  //echo "<a href='javascript:;' onMouseover=\"Mostrar('<div style=\'float: left; margin-right: 5px;\'><img src=\'img/ing_mucarela.jpg\'></div><br><strong>".utf8_encode($objAdic->ingrediente_abreviado)."</strong><br><br>".utf8_encode('Descrição ou alguma dica sobre o ingrediente.')."<br><br>')\" onMouseout=\"Esconder()\">".utf8_encode($objAdic->ingrediente_abreviado) . " <font style='font-size:9px; '>(" . bd2moeda(arredondar_preco_ingrediente($objAdic->preco, $qtde_sabor)) . ")</font></a><br />";
                  echo "</div>";
                }
                echo "</td></tr>";
               // if($num_sabor<$qtde_sabor)
                //{
               	  echo"<tr><td colspan='5' align='center'><br/><a href='javascript:void(0)' onclick='sabor_click(\"".($num_sabor+1)."\",\"f\")' class='btn btn-secondary'>Proximo Sabor</a></tr></td>";
               // }
                echo "</table>";
              }
              ?><br/><br/></div>
            <div class='sabores_pedido_bottom'>
            </div>
          </div>

        </div><?	
		
		desconectar_bd($conexao);


}



if (validaVarPost('tipo') == "carregarIngredientes")
{
    if (validaVarPost('cod'))
    {
        require_once 'bd.php';
        $conexao = conectabd();

        $tamanho_pizza = validaVarPost('tamanho_pizza', "/[0-9]+/");
        $num_sabor = validaVarPost('num_sabor', "/[0-9]+/");
        $qtde_sabor = validaVarPost('qtde_sabor', "/[0-9]+/");

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

        $sqlIngre = "SELECT i.*, p.foto_pequena foto_pizza,pp.pizza_semana,pp.pizza_dia FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas p ON (ip.cod_pizzas = p.cod_pizzas) INNER JOIN ipi_pizzas_ipi_tamanhos pp on pp.cod_pizzas = ip.cod_pizzas WHERE ip.cod_pizzas='" . validaVarPost('cod','/[0-9]+/') . "' AND i.ativo = 1 AND i.consumo = 0 AND pp.cod_pizzarias = '".$cod_pizzarias."' AND pp.cod_tamanhos = '".$tamanho_pizza."' ORDER BY ingrediente";
        $resIngre = mysql_query($sqlIngre);
        $linIngre = mysql_num_rows($resIngre);

				$sql_pizza = "SELECT p.pizza,p.foto_pequena FROM ipi_pizzas p WHERE p.cod_pizzas='" . validaVarPost('cod','/[0-9]+/') . "' ";
				//echo "<br>".$sql_pizza."<br>";
				$res_pizza = mysql_query($sql_pizza);
				$obj_pizza = mysql_fetch_object($res_pizza);
        ?>
        <div id="carrinho_carregar_ingredientes">
         <!-- <div id="carrinho_ingredientes_texto1" class="fonte18 cor_marrom2 negrito tipo_letra1">QUAL VAI SER O 1 SABOR?</div> -->
          <div>
            
            <div id="carrinho_ingredientes">
              <div class="ingredientes_meio_branco cor_marrom2 fonte12 tipo_letra1 left">
              	<div id="carrinho_foto_pizza">
	              <div class="miniatura_foto_pizza">
	                <img <? echo ($obj_pizza->foto_pequena ? "src=\"upload/pizzas/".$obj_pizza->foto_pequena."\"" :"src=\"imgs/pizza_temp.jpg\"" )  ?> width="200">
	              </div>
	              <!--<div class="sabor_pizza_foto fonte10 cor_marrom2 tipo_letra1">
	               <? echo utf8_encode(mb_strtoupper($obj_pizza->pizza)); ?>
	              </div>
	              <a href="javascript:void(0);" onclick="sabor_click(<? echo $num_sabor ?>)" ><img src="img/pc/btn_trocar.png"></a>-->
	            </div>
	            <br />
                <h3 class="texto_esquerda">INGREDIENTES</h3>
                <br />
				<ul class="lista_infos3">
                <?
                if ($linIngre > 0)
                {
                  for ($a = 0; $a < $linIngre; $a++)
                  {

                    $objIngre = mysql_fetch_object($resIngre);
                    echo "<li class='cor_marrom2 menor'>";
                    if ($objIngre->cod_ingredientes_troca)
                    {
                    	$id_troca = "ingredientes" . $num_sabor."_t".$a;
                    	$id_normal = "ingredientes" . $num_sabor."_".$a;
                      if ($objIngre->foto_pequena)
                      {
                        echo utf8_encode("<input type='checkbox' onclick='ccbox(this,\"".$id_troca."\")' id='ingredientes" . $num_sabor."_".$a."' name='ingredientes" . $num_sabor . "[]' value='NORMAL###" . $objIngre->cod_ingredientes . "###' checked='checked' style='border: 0; background: none;' ".(($objIngre->pizza_semana == 1) ? 'onclick="this.checked=true"' : '')." /><label for='ingredientes" . $num_sabor."_".$a."'><img src='upload/ingredientes/".$objIngre->foto_pequena."' width='47' />");
                      }
                      else
                      {
                        echo utf8_encode("<input type='checkbox' onclick='ccbox(this,\"".$id_troca."\")' id='ingredientes" . $num_sabor."_".$a."' name='ingredientes" . $num_sabor . "[]' value='NORMAL###" . $objIngre->cod_ingredientes . "###' checked='checked' style='border: 0; background: none;' ".(($objIngre->pizza_semana == 1) ? 'onclick="this.checked=true"' : '')." /><label for='ingredientes" . $num_sabor."_".$a."'><img src='imgs/6_ing_p.png' width='47' />");
                      }
                      echo utf8_encode($objIngre->ingrediente."</label>");
                      
                      $sql_troca = "SELECT it.preco_troca, itroca.ingrediente, i.foto_pequena FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) INNER JOIN ipi_ingredientes itroca ON (i.cod_ingredientes_troca=itroca.cod_ingredientes) WHERE i.cod_ingredientes = ".$objIngre->cod_ingredientes." AND it.cod_tamanhos=" . $tamanho_pizza." and it.cod_pizzarias='".$cod_pizzarias."'";
                      //echo $sql_troca;
                      $res_troca = mysql_query($sql_troca);
                      $obj_troca = mysql_fetch_object($res_troca);
                      if ($obj_troca->foto_pequena)
                      {
                        echo utf8_encode("<br/> <input type='checkbox' onclick='ccbox(this,\"".$id_normal."\")' id='ingredientes" . $num_sabor."_t".$a."' name='ingredientes_adicionais" . $num_sabor . "[]' value='TROCA###" . $objIngre->cod_ingredientes_troca . "###" . $objIngre->cod_ingredientes . "' style='border: 0; background: none;' /><label for='ingredientes" . $num_sabor."_t".$a."' ><img src='upload/ingredientes/".$obj_troca->foto_pequena."' width='47' />");
                      }
                      else
                      {
                        echo utf8_encode("<br/> <input type='checkbox' onclick='ccbox(this,\"".$id_normal."\")' id='ingredientes" . $num_sabor."_t".$a."' name='ingredientes_adicionais" . $num_sabor . "[]' value='TROCA###" . $objIngre->cod_ingredientes_troca . "###" . $objIngre->cod_ingredientes . "' style='border: 0; background: none;' /><label for='ingredientes" . $num_sabor."_t".$a."' ><img src='imgs/6_ing_p.png' width='47' />");
                      }
                      echo "<div style='    font-size: 14px;
    position: relative;
    top: -26px;
    left: 65px;'>Trocar por ".$obj_troca->ingrediente." (".bd2moeda(arredondar_preco_ingrediente($obj_troca->preco_troca, $qtde_sabor)).") </div></label>";
                    }
                    else
                    {
                      if ($objIngre->foto_pequena)
                      {
                        echo utf8_encode("<input type='checkbox' id='ingredientes" . $num_sabor."_".$a."' name='ingredientes" . $num_sabor . "[]' value='NORMAL###" . $objIngre->cod_ingredientes . "###' checked='checked' style='border: 0; background: none;' ".(($objIngre->pizza_semana == 1) ? 'onclick="this.checked=true"' : '')." /><label for='ingredientes" . $num_sabor."_".$a."'' ><img src='upload/ingredientes/".$objIngre->foto_pequena."' width='47' />");
                      }
                      else
                      {
                        echo utf8_encode("<input type='checkbox' id='ingredientes" . $num_sabor."_".$a."' name='ingredientes" . $num_sabor . "[]' value='NORMAL###" . $objIngre->cod_ingredientes . "###' checked='checked' style='border: 0; background: none;' ".(($objIngre->pizza_semana == 1) ? 'onclick="this.checked=true"' : '')." /><label for='ingredientes" . $num_sabor."_".$a."'' ><img src='imgs/6_ing_p.png' width='47' />");
                      }
                      //echo "<a href='javascript:;' onMouseover=\"Mostrar('<div style=\'float: left; margin-right: 5px;\'><img src=\'img/ing_mucarela.jpg\'></div><br><strong>".utf8_encode($objIngre->ingrediente)."</strong><br><br>".utf8_encode('Descrição ou alguma dica sobre o ingrediente.')."<br><br>')\" onMouseout=\"Esconder()\">".utf8_encode($objIngre->ingrediente) . "</a><br />";
                      echo utf8_encode($objIngre->ingrediente."</label>");
                    }
                    echo "</li>";
                  }
                }
                ?>
							</ul>
							<div class='clear' ></div>
							<?  if($objIngre->pizza_semana == 1)
                      {
                        echo "<div class='centralizar' >".utf8_encode("*Obs: Não é permitido retirar ingredientes da pizza da semana.")."</div>";
                      }
                      ?>
                      <br />
              </div>	
              </div>
            </div>
          </div>
        <?
        desconectabd($conexao);
    }
}

if (validaVarPost('tipo') == "carregarCombo")
{
    require_once 'bd.php';
    $conexao = conectabd();



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

    //TODO: Se não encontrar o cod_pizzarias força para pizzaria da adyanna, isso acontece qdo clica no combo direto da home por exemplo
    if(!$cod_pizzarias)
    {
      $cod_pizzarias = 1;
    }



    $tamanho_pizza = validaVarPost('tamanho_pizza');
    $sabor = validaVarPost('sabor');
    
    echo utf8_encode("<option value='0'>Selecione</option>");
    
    if (validaVarPost('cod') == "borda")
    {
        echo utf8_encode("<option value='N'>Não</option>");
        if(validaVarPost('cpr') !="" && validaVarPost('cpr') !="0")
        {
        	$sqlBordas = "SELECT * FROM ipi_tamanhos t INNER JOIN ipi_tamanhos_ipi_bordas tb ON (tb.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_bordas b ON (tb.cod_bordas=b.cod_bordas) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND tb.cod_pizzarias= '".$cod_pizzarias."' and tb.cod_bordas = ".validaVarPost('cpr',"/[0-9]+/")." ORDER BY b.borda";
        }else
        {
        	$sqlBordas = "SELECT * FROM ipi_tamanhos t INNER JOIN ipi_tamanhos_ipi_bordas tb ON (tb.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_bordas b ON (tb.cod_bordas=b.cod_bordas) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND tb.cod_pizzarias= '".$cod_pizzarias."'  ORDER BY b.borda";
        }
        $resBordas = mysql_query($sqlBordas);
        $linBordas = mysql_num_rows($resBordas);
        
        if ($linBordas > 0)
        {
            for ($a = 0; $a < $linBordas; $a++)
            {
                $objBordas = mysql_fetch_object($resBordas);
                
                
                if (validaVarPost('vc') == "1")
                    $objBordas->preco = "0";
                
               //  if(!isset($_SESSION['ipi_carrinho']['combo']))
               //  {    
	              //   // PROMOCAO 3: Toda a terça-feira borda grátis
	              //   if (date('w') == 2)
	              //   {
	              //   	$sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '2' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
               //      $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
                        
               //      //  $obj_buscar_promocoes = mysql_fetch_object($res_buscar_promocoes);
               //      if(mysql_num_rows($res_buscar_promocoes)>0)
               //      {
		             //      $objBordas->preco = "0";
		             //    }
		             //  }
		             //  elseif($tamanho_pizza==3)
		             //  {
		             //  	$sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '14' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
               //      $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
                        
               //      //  $obj_buscar_promocoes = mysql_fetch_object($res_buscar_promocoes);
               //      if(mysql_num_rows($res_buscar_promocoes)>0)
               //      {
		             //      $objBordas->preco = "0";
		             //    }
		             //  }
	              // }
                
                if ($objBordas->preco != "0")
                    $preco_borda = bd2moeda($objBordas->preco);
                else
                    $preco_borda = "Grátis";
                
                if ($_SESSION['ipi_carrinho']['combo']['qtde_bordas']>0)                    
                {    
                  $preco_borda = "Combo";
                }
                
                echo utf8_encode('<option value="' . $objBordas->cod_bordas . '">' . $objBordas->borda . ' (' . $preco_borda . ')</option>');
            }
        }
    }
    
    if (validaVarPost('cod') == "num_sabores")
    {
        $sqlFracoes = "SELECT * FROM ipi_tamanhos_ipi_fracoes tf INNER JOIN ipi_fracoes f ON (tf.cod_fracoes=f.cod_fracoes) WHERE tf.cod_tamanhos='" . $tamanho_pizza . "' AND tf.cod_pizzarias = '".$cod_pizzarias."' ORDER BY f.fracoes";
        //echo $sqlFracoes;
        $resFracoes = mysql_query($sqlFracoes);
        $linFracoes = mysql_num_rows($resFracoes);
        if ($linFracoes > 0)
        {
            for ($a = 0; $a < $linFracoes; $a++)
            {
                
                $objFracoes = mysql_fetch_object($resFracoes);
                
                echo utf8_encode('<option value="' . $objFracoes->fracoes . '">' . $objFracoes->fracoes);
                
                if ($objFracoes->preco != "0.00")
                {
                    echo utf8_encode(' (+ R$' . bd2moeda($objFracoes->preco) . ')');
                }
                
                echo utf8_encode('</option>');
            }
        }
    }
    
    if (validaVarPost('cod') == "gergelim")
    {
        echo utf8_encode("<option value='N'>Não</option>");
        $sqlBordas = "SELECT * FROM ipi_tamanhos t INNER JOIN ipi_tamanhos_ipi_adicionais ta ON (ta.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_adicionais a ON (ta.cod_adicionais=a.cod_adicionais) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND ta.cod_pizzarias= '".$cod_pizzarias."' ORDER BY a.adicional";
        $resBordas = mysql_query($sqlBordas);
        $linBordas = mysql_num_rows($resBordas);
        if ($linBordas > 0)
        {
            for ($a = 0; $a < $linBordas; $a++)
            {
                $objBordas = mysql_fetch_object($resBordas);
                echo utf8_encode('<option value="' . $objBordas->cod_adicionais . '">' . $objBordas->adicional . ' (' . bd2moeda($objBordas->preco) . ')</option>');
            }
        }
    }
    
    if (validaVarPost('cod') == "tipo_massa")
    {
        $sql_pizza_fit = "SELECT * FROM ipi_pizzas p inner join ipi_pizzas_ipi_tamanhos pt on pt.cod_pizzas = p.cod_pizzas WHERE  pt.cod_pizzarias = '".$cod_pizzarias."' AND p.pizza_fit = 1 group by p.pizza";//não estamos considerando massa por pizzaria
        $res_pizza_fit = mysql_query($sql_pizza_fit);
        $num_pizza_fit = mysql_num_rows($res_pizza_fit);

        if ($num_pizza_fit>0)
        {
          $sqlBordas = "SELECT tm.cod_tipo_massa, tm.tipo_massa, ttm.preco FROM ipi_tipo_massa tm INNER JOIN ipi_tamanhos_ipi_tipo_massa ttm ON (ttm.cod_tipo_massa=tm.cod_tipo_massa)  WHERE ttm.cod_tamanhos=" . $tamanho_pizza . " ORDER BY tm.tipo_massa";
        }
        else
        {
          // CASO NÃO FOR INTEGRAL RETIRADO MANUALMENTE A MASSA TIPO INTEGRAL
          $sqlBordas = "SELECT tm.cod_tipo_massa, tm.tipo_massa, ttm.preco FROM ipi_tipo_massa tm INNER JOIN ipi_tamanhos_ipi_tipo_massa ttm ON (ttm.cod_tipo_massa=tm.cod_tipo_massa)  WHERE ttm.cod_tamanhos=" . $tamanho_pizza . " AND tm.cod_tipo_massa NOT IN (3) ORDER BY tm.tipo_massa";
        }
        $resBordas = mysql_query($sqlBordas);
        $linBordas = mysql_num_rows($resBordas);
        
        if ($linBordas > 0)
        {
            for ($a = 0; $a < $linBordas; $a++)
            {
                $objBordas = mysql_fetch_object($resBordas);
                
                if($objBordas->preco > 0)
                {
                    echo utf8_encode('<option value="' . $objBordas->cod_tipo_massa . '">' . $objBordas->tipo_massa . ' (' . bd2moeda($objBordas->preco) . ')</option>');    
                }
                else
                {
                		//echo utf8_encode('<option value="">'.$objBordas->cod_tipo_massa.'//'.$objBordas->tipo_massa.'</option>');
                    echo utf8_encode('<option value="' . $objBordas->cod_tipo_massa . '">' . $objBordas->tipo_massa . '</option>');
                }
            }
        }
    }
    
    
    if (validaVarPost('cod') == "corte")
    {
        $sqlBordas = "SELECT * FROM ipi_tamanhos t INNER JOIN ipi_tamanhos_ipi_opcoes_corte toc ON (toc.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_opcoes_corte oc ON (toc.cod_opcoes_corte=oc.cod_opcoes_corte) WHERE t.cod_tamanhos=" . $tamanho_pizza . " ORDER BY oc.opcao_corte";
        $resBordas = mysql_query($sqlBordas);
        $linBordas = mysql_num_rows($resBordas);
        
        if ($linBordas > 0)
        {
            for ($a = 0; $a < $linBordas; $a++)
            {
                $objBordas = mysql_fetch_object($resBordas);
                
                if($objBordas->preco > 0)
                {
                    echo utf8_encode('<option value="' . $objBordas->cod_opcoes_corte . '">' . $objBordas->opcao_corte . ')</option>');    
                }
                else
                {
                    echo utf8_encode('<option value="' . $objBordas->cod_opcoes_corte . '">' . $objBordas->opcao_corte . '</option>');
                }
            }
        }
    }


    if (((validaVarPost('cod') == "sabor1_pizza") || (validaVarPost('cod') == "sabor2_pizza") || (validaVarPost('cod') == "sabor3_pizza") || (validaVarPost('cod') == "sabor4_pizza")) && (validaVarPost('promo') == 'nao'))
    {
        if ($sabor)
        {
            $sqlPizzas = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE pt.cod_pizzarias = '".$cod_pizzarias."' AND pt.cod_tamanhos=" . $tamanho_pizza . " AND p.tipo='".$sabor."' and p.venda_online = 1 ORDER BY p.pizza";
        }
        else
        {
            $sqlPizzas = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE pt.cod_pizzarias = '".$cod_pizzarias."' AND pt.cod_tamanhos=" . $tamanho_pizza . " and p.venda_online = 1 ORDER BY p.pizza";
        }
        //echo "sqlPizzas: ".$sqlPizzas;
        $resPizzas = mysql_query($sqlPizzas);
        $linPizzas = mysql_num_rows($resPizzas);
        if ($linPizzas > 0)
        {
            for ($a = 0; $a < $linPizzas; $a++)
            {
                $objPizzas = mysql_fetch_object($resPizzas);
                echo utf8_encode('<option value="' . $objPizzas->cod_pizzas . '">' . $objPizzas->pizza . ' (' . bd2moeda($objPizzas->preco) . ')</option>');
            }
        }
    }
    
    if (((validaVarPost('cod') == "sabor1_pizza") || (validaVarPost('cod') == "sabor2_pizza") || (validaVarPost('cod') == "sabor3_pizza") || (validaVarPost('cod') == "sabor4_pizza")) && (validaVarPost('promo') == 'sim'))
    {
        //$sqlPizzas = "SELECT * FROM ipi_tamanhos t INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (pt.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND p.tipo='Doce' ORDER BY p.pizza";
        $sqlPizzas = "SELECT * FROM ipi_tamanhos t INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (pt.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE pt.cod_pizzarias = '".$cod_pizzarias."' AND t.cod_tamanhos=" . $tamanho_pizza . " AND p.tipo='Doce' and p.venda_online = 1 ORDER BY p.pizza";
        $resPizzas = mysql_query($sqlPizzas);
        $linPizzas = mysql_num_rows($resPizzas);
        if ($linPizzas > 0)
        {
            for ($a = 0; $a < $linPizzas; $a++)
            {
                $objPizzas = mysql_fetch_object($resPizzas);
                echo utf8_encode('<option value="' . $objPizzas->cod_pizzas . '">' . $objPizzas->pizza . ' (Grátis)</option>');
            }
        }
    }
    
    if (((validaVarPost('cod') == "sabor1_pizza") || (validaVarPost('cod') == "sabor2_pizza") || (validaVarPost('cod') == "sabor3_pizza") || (validaVarPost('cod') == "sabor4_pizza")) && (validaVarPost('cupom') == 'sim'))
    {
        $sqlPizzas = "SELECT * FROM ipi_tamanhos t INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (pt.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND pt.cod_pizzarias= '".$cod_pizzarias."' and p.venda_online = 1 ORDER BY p.pizza";
        $resPizzas = mysql_query($sqlPizzas);
        $linPizzas = mysql_num_rows($resPizzas);
        if ($linPizzas > 0)
        {
            for ($a = 0; $a < $linPizzas; $a++)
            {
                $objPizzas = mysql_fetch_object($resPizzas);
                echo utf8_encode('<option value="' . $objPizzas->cod_pizzas . '">' . $objPizzas->pizza . ' (Grátis)</option>');
            }
        }
    }
    
    desconectabd($conexao);
}

?>
