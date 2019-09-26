<?
require_once 'ipi_req_carrinho_trava_meianoite.php';
require_once('ipi_req_carrinho_classe.php');
?>

<script src="js/mascara.js"></script>
<script src="js/creditcard.js"></script>

<?
if($_SESSION['ipi_cliente']['autenticado'] != true)
{
	echo '<script>location.href = "pedidos" </script>';
	die();
}

$habilitado_cupom = 0;
$verificou_cupom = false;
$habilitado_minimo = 0;
$habilitado_fidelidade = 0;
$valor_minimo_compra = 0;


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

require("pub_req_fuso_horario1.php");
$arr_funcionamento = array();
$sql_buscar_horarios = "SELECT horario_inicial as inicio,horario_final as fim from ipi_pizzarias_funcionamento where cod_pizzarias = '".$cod_pizzarias."' and dia_semana = '".date('w')."'  order by horario_inicial";//and horario_inicial >'02:00:00'
//echo $sql_buscar_horarios."<br/>".(explode(':',$obj_buscar_horarios->inicio))[0]."<br/>".date('H',$obj_buscar_horarios->fim);
$res_buscar_horarios = mysql_query($sql_buscar_horarios);
$hor = 0;
while($obj_buscar_horarios = mysql_fetch_object($res_buscar_horarios))
{
	$arr_funcionamento[$hor]["inicio"] = date('H',$obj_buscar_horarios->inicio);
	$arr_funcionamento[$hor]["fim"] = date('H',$obj_buscar_horarios->fim);
	$hor++;
}

$hora_inicial = ($hor!=0 ? $arr_funcionamento[0]["inicio"] : '23:59:59');
$hora_final = ($hor!=0 ? $arr_funcionamento[0]["fim"] : '23:59:59');


$con = conectabd();
$sqlCupom = "SELECT * FROM ipi_configuracoes WHERE chave='VALOR_MINIMO'";
$resCupom = mysql_query($sqlCupom);
$objCupom = mysql_fetch_object($resCupom);
$compra_minima = ($objCupom->valor);


// #### Validação de compra mínima em caso de FIDELIDADE
if ($_SESSION['ipi_carrinho']['fidelidade_pontos_gastos']!="")
{
  $habilitado_fidelidade = 1;
}
else
{
  $habilitado_fidelidade = 0;
}


// #### Validação de compra mínima em caso de CUPOM
if ($_SESSION['ipi_carrinho']['cupom']!="")
{
	$verificou_cupom = true;
  $sqlCupom = "SELECT * FROM ipi_cupons WHERE cupom = '".$_SESSION['ipi_carrinho']['cupom']."'";
  $resCupom = mysql_query($sqlCupom);
  $objCupom = mysql_fetch_object($resCupom);
  $valor_minimo_compra = $objCupom->valor_minimo_compra;
  if ($objCupom->necessita_compra=="0")
  {
      $habilitado_cupom = 1;
  }
  else
  {
  	if ($valor_minimo_compra==-1)
  	{
  	   $habilitado_cupom = 1;
  	}
  	else
		{
		if ($_SESSION['ipi_carrinho']['total_pedido']>=$valor_minimo_compra)
			{
			$habilitado_cupom = 1;
			}
		else
			{
			$habilitado_cupom = 0;
			}
		}
		
	}
}
else
{
    $habilitado_cupom=0;
}



// Se nenhuma condição acima foi apontada, verifica a compra minima
if ($valor_minimo_compra==0)   //verificar valor minimo da compra está vazio, pois é setado acima em caso de cupom com valor minimo de compra
{
    if ($_SESSION['ipi_carrinho']['total_pedido']>=$compra_minima)
    {
        $habilitado_minimo = 1;
    }
    else
    {
        $valor_minimo_compra = $compra_minima;
        $habilitado_minimo = 0;
    }
}

// Verificar se não existe combo incompleto (aberto) no carrinho
$combo_aberto = 0;
$indice_opcoes = -1;
$num_opcoes = count($_SESSION['ipi_carrinho']['combo']['produtos']);
for ($a=0; $a<$num_opcoes; $a++)
{
    if ($_SESSION['ipi_carrinho']['combo']['produtos'][$a]['foi_pedido']=='N')
    {
        $indice_opcoes = $a;
        break;
    }
}
if ($indice_opcoes==-1)
{
	$combo_aberto = 0;
}
else
{
	$combo_aberto = 1;
}

// Trava da Meia-Noite impedir fechamento de pedidos depois das 00h05
$bloquear_pedido_por_horario = 0;

/*
$hora_corte_inicio = "00:05:00";
$hora_corte_fim = "02:00:00";
*/
$hora_corte_inicio = $arr_funcionamento[$hor]["inicio"];
$hora_corte_fim = $arr_funcionamento[$hor]["fim"];

$hora_corte_inicio_convertida = strtotime($hora_corte_inicio);
$hora_corte_fim_convertida = strtotime($hora_corte_fim)+301;

if ( ($hora_atual_convertida > $hora_corte_inicio_convertida) && ($hora_atual_convertida < $hora_corte_fim_convertida) )
{
	$bloquear_pedido_por_horario = 1;
}

/*
echo "<Br>Atual: ".$hora_atual_convertida;
echo "<Br>Inicio: ".$hora_corte_inicio_convertida;
echo "<Br>Fim: ".$hora_corte_fim_convertida;
echo "<Br>Block: ".$bloquear_pedido_por_horario;
*/
$sqlEndereco = "SELECT * FROM ipi_enderecos WHERE cod_clientes = '".$_SESSION['ipi_cliente']['codigo']."'";
$resEndereco = mysql_query($sqlEndereco);
$numEndereco = mysql_num_rows($resEndereco);
desconectabd($con);
if($numEndereco>0)
{
	$tem_endereco = true;
}
else
{
	$tem_endereco = false;
}

if($bloquear_pedido_por_horario==0)
{

	if($combo_aberto==0)
	{
		if ( ($habilitado_minimo==1) || ( ($habilitado_cupom==1) || ($habilitado_fidelidade==1) ) )
		{
			if($tem_endereco)
			{
				if($_SESSION["ipi_carrinho"]["pizza_promocional"] != "sim")
					{
					
		
		
						if($_GET["erro_pag"]==1)
						{
							echo "<script>alert('Seu pedido não pode ser processado, porque a pizzaria fechou.')</script>";
						}
						?>

<div id='container_home_client'>
  <div class='top_box'>
    <?
      $carrinho = new ipi_carrinho();
      $arr_nome = explode(' ', $_SESSION[ipi_cliente][nome]);  
      echo "<h1> Olá, ".$arr_nome[0]."! </h1>";
      //echo "<img src='img/pc/home_cliente_bem_vindo.png' alt='Seja bem vindo!'/>";
    ?>
    <!-- <img class='float_right seta' src='img/pc/home_cliente_seta_pontos.png' alt='Pontos' /> -->
    <div class='box_dados_user'> 
    <?
      $obj_pontos = executar_busca_simples('SELECT SUM(pontos) as total FROM ipi_fidelidade_clientes where cod_clientes = '.$_SESSION['ipi_cliente']['codigo']);  
      if($carrinho->pontos_fidelidade() != NULL || $carrinho->pontos_fidelidade() != 0)
      {
        echo "<p class='num cor_marrom2'>".$carrinho->pontos_fidelidade()." PONTOS ";
        echo "<a href='usar_fidelidade' title='Use agora seus pontos!'>Usar Agora</a></p>";
      }
      else
      {
        echo "<p class='num cor_marrom2'>0 PONTOS</p>";
      }
    ?>
    </div>
  </div>

  <div class='box_sabor pagamento'>
      <div class="header"> 
        <?
        if($_SESSION['ipi_carrinho']['pedido'] || $_SESSION['ipi_carrinho']['bebida'])
        {
          echo "O pedido abaixo está só aguardando sua finalização.";        
          // RESUMO PEDIDO //
          $carrinho->exibir_resumo_pedido();

          echo "<div class='caixa_padrao_resumo'>";
          ?>

          <style type="text/css">
            #anchorTitle 
            {
              /* border radius */
              -moz-border-radius: 8px;
              -webkit-border-radius: 8px;
              border-radius: 8px;
              /* box shadow */
              -moz-box-shadow: 2px 2px 3px #e6e6e6;
              -webkit-box-shadow: 2px 2px 3px #e6e6e6;
              box-shadow: 2px 2px 3px #e6e6e6;
              /* other settings */
              background-color: #fff;
              border: solid 3px #d6d6d6;
              color: #333;
              display: none;
              font-family: Helvetica, Arial, sans-serif;
              font-size: 11px;
              line-height: 1.3;
              max-width: 200px;
              padding: 5px 7px;
              position: absolute;
              z-index: 1;
            }
            #resumo_pagamento_dados
            {
              position: relative;
            }
          </style>

          <script>
		      function verificar_forma_pagamento(objPgto)
		      {
			      if (objPgto.value=="DINHEIRO")
			      {
				      document.getElementById('necessita_troco').style.display="block";
				      document.getElementById('mensagem_visa').style.display="none";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DE PAGAMENTO";
			      }
			      else if (objPgto.value=="BEBLUE - CRÉDITO" || objPgto.value=="BEBLUE - DÉBITO" || objPgto.value=="BEBLUE - SALDO BEBLUE")
			      {
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('mensagem_visa').style.display="none";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      
			      }
            else if (objPgto.value=="EKKO - CRÉDITO" || objPgto.value=="EKKO - DÉBITO" || objPgto.value=="EKKO - DINHEIRO")
            {
              document.getElementById('necessita_troco').style.display="none";
              document.getElementById('mensagem_visa').style.display="none";
              document.getElementById('id_fechar_pedido').style.display="block";
              document.getElementById('nota_fiscal_paulista').style.display="block";
              
            }            
			      else if (objPgto.value=="LEVAR A MAQUINA DE CARTAO")
			      {
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('mensagem_visa').style.display="none";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DE PAGAMENTO";
			      }
            else if (objPgto.value=="MAQUINA DE CARTAO DEBITO")
            {
              document.getElementById('necessita_troco').style.display="none";
              document.getElementById('mensagem_visa').style.display="none";
              document.getElementById('id_fechar_pedido').style.display="block";
              document.getElementById('nota_fiscal_paulista').style.display="block";
              document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DE PAGAMENTO";
            }




            else if ((objPgto.value=="AMERICAN")||(objPgto.value=="ALELO REFEIÇÃO")||(objPgto.value=="ELO CRÉDITO")||(objPgto.value=="ELO DÉBITO")||(objPgto.value=="MASTER CRÉDITO")||(objPgto.value=="MASTER DÉBITO")||(objPgto.value=="VISA CRÉDITO")||(objPgto.value=="VISA DÉBITO")||(objPgto.value=="TICKET RESTAURANTE"))
            {
              document.getElementById('necessita_troco').style.display="none";
              document.getElementById('mensagem_visa').style.display="none";
              document.getElementById('id_fechar_pedido').style.display="block";
              document.getElementById('nota_fiscal_paulista').style.display="block";
              document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DE PAGAMENTO";
            }



			      else if (objPgto.value=="VISANET1")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
			      }
			      else if (objPgto.value=="VISANET-CIELO")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('logo_operadora_cc').src="img/logo_visa2.jpg";
				      document.getElementById('bandeira_cc').value="Visa";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
				      document.getElementById('txt_cs').maxLength=3;
				      document.getElementById('txt_cc').maxLength=16;
			      }
			      else if (objPgto.value=="AMEXNET-CIELO")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('logo_operadora_cc').src="img/logo_amex2.jpg";
				      document.getElementById('bandeira_cc').value="American Express";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
				      document.getElementById('txt_cs').maxLength=4;
				      document.getElementById('txt_cc').maxLength=15;
			      }
			      else if (objPgto.value=="AURANET-CIELO")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('logo_operadora_cc').src="img/logo_aura2.jpg";
				      document.getElementById('bandeira_cc').value="Aura";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
				      document.getElementById('txt_cs').maxLength=3;
				      document.getElementById('txt_cc').maxLength=19;
			      }
			      else if (objPgto.value=="JCBNET-CIELO")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('logo_operadora_cc').src="img/logo_jcb2.jpg";
				      document.getElementById('bandeira_cc').value="JCB";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
				      document.getElementById('txt_cs').maxLength=3;
				      document.getElementById('txt_cc').maxLength=16;
			      }
			      else if (objPgto.value=="DINERSNET-CIELO")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('logo_operadora_cc').src="img/logo_diners2.jpg";
				      document.getElementById('bandeira_cc').value="Diners Club";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
				      document.getElementById('txt_cs').maxLength=3;
				      document.getElementById('txt_cc').maxLength=14;
			      }
			      else if (objPgto.value=="ELONET-CIELO")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('logo_operadora_cc').src="img/logo_elo2.jpg";
				      document.getElementById('bandeira_cc').value="ELO";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
				      document.getElementById('txt_cs').maxLength=3;
				      document.getElementById('txt_cc').maxLength=16;
			      }
			      else if (objPgto.value=="DISCOVERNET-CIELO")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('logo_operadora_cc').src="img/logo_discover2.jpg";
				      document.getElementById('bandeira_cc').value="Discover";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
				      document.getElementById('txt_cs').maxLength=3;
				      document.getElementById('txt_cc').maxLength=16;
			      }
			      else if (objPgto.value=="VISANET")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('logo_operadora_cc').src="img/logo_visa2.jpg";
				      document.getElementById('bandeira_cc').value="Visa";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
			      }
			      else if (objPgto.value=="MASTERCARDNET")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('logo_operadora_cc').src="img/logo_master2.jpg";
				      document.getElementById('bandeira_cc').value="Master Card";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
			      }
			      else if (objPgto.value=="MASTERCARDNET-CIELO")
			      {
				      document.getElementById('mensagem_visa').style.display="block";
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('logo_operadora_cc').src="img/logo_master2.jpg";
				      document.getElementById('bandeira_cc').value="Master Card";
				      document.getElementById('id_fechar_pedido').style.display="block";
				      document.getElementById('nota_fiscal_paulista').style.display="block";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
				      document.getElementById('txt_cs').maxLength=3;
				      document.getElementById('txt_cc').maxLength=16;
			      }
			      else
			      {
				      document.getElementById('necessita_troco').style.display="none";
				      document.getElementById('troco_quanto').style.display="none";
				      document.getElementById('mensagem_visa').style.display="none";
				      document.getElementById('id_fechar_pedido').style.display="none";
				      document.getElementById('nota_fiscal_paulista').style.display="none";
				      document.getElementById('cartao_dinheiro').innerHTML = "OPÇÕES DO CARTÃO";
			      }
		      }


		      function verificar_troco(objTroco)
		      {
			      if (objTroco.value=="Sim")
			      {
				      document.getElementById('troco_quanto').style.display="block";
				      document.frmPagamento.troco.value='';
				      document.frmPagamento.troco.focus();
			      }
			      else
			      {
				      document.getElementById('troco_quanto').style.display="none";
				      document.frmPagamento.troco.value='';
			      }
		      }


		      function verificar_nota(objNota)
		      {
			      if (objNota.value=="Sim")
			      {
				      document.getElementById('qual_cpf').style.display="block";
				      document.frmPagamento.cpf_nota_paulista.value='<? echo $_SESSION['ipi_cliente']['cpf']; ?>';
				      //document.frmPagamento.cpf_nota_paulista.value='';
				      document.frmPagamento.cpf_nota_paulista.focus();
			      }
			      else
			      {
				      document.getElementById('qual_cpf').style.display="none";
			      }
		      }

		      function ValidarPagamento(frm)
		      {

            <?
            if ($_SESSION['ipi_carrinho']['buscar_balcao']=="Entrega")
            {
              ?>
              if(typeof(frm.cod_enderecos)!="undefined")
              {
	              if (!jQuery.isEmptyObject(frm.cod_enderecos[0]))
	                {
	                  selecionado = -1;

	                  for (i=0; i<$("input[name='cod_enderecos']").length; i++) 
	                  {
	                    if ($("input[name='cod_enderecos']")[i].checked)
	                    {
	                      selecionado = i
	                      resposta = $("input[name='cod_enderecos']")[i].value;
	                    }
	                  }
	                  if (selecionado == -1) 
	                  {
	                    alert("Epa, você esqueceu de selecionar um endereço de entrega.");
	                    //frm.cod_enderecos[0].focus();
	                    return false;
	                  } 
	                }
	              else
	                {
	                  if (frm.cod_enderecos.checked == false) 
	                  {
	                    alert("Epa, você esqueceu de selecionar um endereço de entrega.");
	                    frm.cod_enderecos.focus();
	                    return false;
	                  }
	                } 
	            }
	            else
	            {
                alert("Opa, você esqueceu de selecionar um endereço de entrega.");
                return false;
	            }
            <? 
            }
            ?>  

            if (frm.agendamento.value=="")
            {
              alert("Deseja agendar?\n\nCaso não deseje agendar, responda Não.");
              frm.necessita_troco.focus();
              return false;
            }

            if (frm.agendamento.value=="Sim" && (frm.horario.value=="" || (!(/^([0-9])([0-9])\:([0-9])([0-9])/.test(frm.horario.value))) ) )
            {
              alert("Opa, pra que horas deseja agendar?");
              frm.necessita_troco.focus();
              return false;
            }


			      selecionado = -1;
			      var obj=$('forma_pagamento');

			      for (i=0; i<$("input[name='forma_pagamento']").length; i++) 
		        {
			        if ($("input[name='forma_pagamento']")[i].checked) 
			        {
					      selecionado = i
					      resposta = $("input[name='forma_pagamento']")[i].value;
				      }
			      }
				      if (selecionado == -1) 
				      {
					      alert("Opa, você esqueceu de selecionar uma forma de pagamento!");
					      //frm.forma_pagamento[0].focus();
					      return false;
				      } 	
			
			        if (resposta == 'VISANET' || resposta == 'VISANET-CIELO' || resposta == 'MASTERCARDNET' || resposta == 'MASTERCARDNET-CIELO' || resposta == 'AMEXNET-CIELO' || resposta == 'AURANET-CIELO' || resposta == 'ELONET-CIELO' || resposta == 'JCBNET-CIELO' || resposta == 'DINERSNET-CIELO' || resposta == 'DISCOVERNET-CIELO')
			        {

				        if (frm.txt_nc.value == "")
				        {
				            alert("Opa, você esqueceu o nome no Cartão de Crédito!");
				            frm.txt_nc.focus();
				            return false;
				        }

			        
				        if (frm.txt_cc.value == "")
				        {
				            alert("Opa, você esqueceu o número do Cartão de Crédito!");
				            frm.txt_cc.focus();
				            return false;
				        }
				        else
				        {
			            <? // Validador: http://www.braemoor.co.uk/software/creditcard.shtml ?>
                  if( ((resposta == 'VISANET-CIELO') || (resposta == 'VISANET')) && (!checkCreditCard (frm.txt_cc.value, 'Visa')))
                  {
			        	      alert("Tem algo errado, o número do Cartão de Crédito está inválido.");
			                frm.txt_cc.focus();
			        	      return false;
			        	  }
                  else if( ((resposta == 'MASTERCARDNET-CIELO') || (resposta == 'MASTERCARDNET'))  && (!checkCreditCard (frm.txt_cc.value, 'MasterCard')))
                  {                      
                      alert("Tem algo errado, o número do Cartão de Crédito está inválido.");
                      frm.txt_cc.focus();
                      return false;
                  }

                  else if( ((resposta == 'AMEXNET-CIELO') || (resposta == 'AMEXNET'))  && (!checkCreditCard (frm.txt_cc.value, 'AmEx')))
                  {                      
                      alert("Tem algo errado, o número do Cartão de Crédito está inválido.");
                      frm.txt_cc.focus();
                      return false;
                  }
                  else if( ((resposta == 'ELONET-CIELO') || (resposta == 'ELONET'))  && (!checkCreditCard (frm.txt_cc.value, 'Elo')))
                  {                      
                      alert("Tem algo errado, o número do Cartão de Crédito está inválido.");
                      frm.txt_cc.focus();
                      return false;
                  }
                  else if( ((resposta == 'DINERSNET-CIELO') || (resposta == 'DINERSNET'))  && (!checkCreditCard (frm.txt_cc.value, 'DinersClub')))
                  {                      
                      alert("Tem algo errado, o número do Cartão de Crédito está inválido.");
                      frm.txt_cc.focus();
                      return false;
                  }
                  else if( ((resposta == 'DISCOVERNET-CIELO') || (resposta == 'DISCOVERNET'))  && (!checkCreditCard (frm.txt_cc.value, 'Discover')))
                  {                      
                      alert("Tem algo errado, o número do Cartão de Crédito está inválido.");
                      frm.txt_cc.focus();
                      return false;
                  }
                  else if( ((resposta == 'JCBNET-CIELO') || (resposta == 'JCBNET'))  && (!checkCreditCard (frm.txt_cc.value, 'JCB')))
                  {                      
                      alert("Tem algo errado, o número do Cartão de Crédito está inválido.");
                      frm.txt_cc.focus();
                      return false;
                  }
                  else if( ((resposta == 'AURANET-CIELO') || (resposta == 'AURANET'))  && (!checkCreditCard (frm.txt_cc.value, 'Aura')))
                  {                      
                      alert("Tem algo errado, o número do Cartão de Crédito está inválido.");
                      frm.txt_cc.focus();
                      return false;
                  }

				        }
				            
				        if (frm.txt_cs.value=="")
				        {
				            alert("Opa, você esqueceu o Código de Segurança!");
				            frm.txt_cs.focus();
				            return false;
				        }

                if(resposta == 'AMEXNET-CIELO')
                {
				          if (frm.txt_cs.value.length!=4)
				          {
				              alert("Ops, o Código de Segurança está inválido, digite os 4 números!");
				              frm.txt_cs.focus();
				              return false;
				          }
                }
                else
                {
				          if (frm.txt_cs.value.length!=3)
				          {
				              alert("Ops, o Código de Segurança está inválido, digite os 3 números!");
				              frm.txt_cs.focus();
				              return false;
				          }
                }
				            
				        if (frm.txt_dvm.value=="")
				        {
				            alert("Opa, você esqueceu o Mês de Validade do Cartão de Crédito!");
				            frm.txt_dvm.focus();
				            return false;
				        }
				            
				        if (frm.txt_dva.value=="")
				        {
				            alert("Opa, você esqueceu o Ano de Validade do Cartão de Crédito!");
				            frm.txt_dva.focus();
				            return false;
				        }
		
			        }

              if (resposta=="DINHEIRO")
              {

                if (frm.necessita_troco.value=="")
                {
                  alert("Necessita de Troco?\n\nCaso não necessite de troco responda Não.");
                  frm.necessita_troco.focus();
                  return false;
                }

                if (frm.necessita_troco.value=="Sim")
                {
                  if ((frm.troco.value=="0") || (frm.troco.value== ''))
                  {
                    alert("De quanto você necessita de troco?");
                    frm.troco.focus();
                    return false;
                  }
                  var troco_str = frm.troco.value;
                  var valor_total_str = frm.valor_total.value;

                  troco_str = troco_str.replace('.', '');
                  troco_str = troco_str.replace(',', '.');

                  //valor_total_str = valor_total_str.replace('.', '');
                  valor_total_str = valor_total_str.replace(',', '.');

                  var troco_num = parseFloat(troco_str);
                  var valor_total_num = parseFloat(valor_total_str);

                  if(troco_num < valor_total_num) 
                  {
                    alert("Ops, o valor para troco não deve ser menor que o valor total do pedido!\nVocê digitou R$ "+frm.troco.value+" e o valor de seu pedido é R$ "+frm.valor_total.value+".\nPor favor, digite novamente.");
                    frm.troco.focus();
                    frm.troco.value = "";
                    return false;
                  }
                }                  
              }	
              if (frm.nota_fiscal_paulista.value=="")
              {
                alert("CPF na nota?\n\nCaso não necessite responda Não.");
                frm.nota_fiscal_paulista.focus();
                return false;
              }
    
    
              if (frm.nota_fiscal_paulista.value=="Sim")
              {
                if (frm.cpf_nota_paulista.value=="")
                {
                  alert("Epa, você esqueceu de digitar o CPF!");
                  frm.cpf_nota_paulista.focus();
                  return false;
                }
                if (!ValidarCPF(frm.cpf_nota_paulista.value))
                {
                  alert("Tem algo errado, o CPF está inválido!");
                  frm.cpf_nota_paulista.focus();
                  return false;
                }
              } 
				      return true;
			      }


		     function formatar_numero(number, decimals, dec_point, thousands_sep) {
		          number = (number+'').replace(',', '').replace(' ', '');
		          var n = !isFinite(+number) ? 0 : +number,
		          prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		          sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		          s = '',
		          toFixedFix = function (n, prec) {
		              var k = Math.pow(10, prec);
		              return '' + Math.round(n * k) / k;
		          };
		          // Fix for IE parseFloat(0.55).toFixed(0) = 0;
		          s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		          if (s[0].length > 3) {
		              s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		          }
		          if ((s[1] || '').length < prec) {
		              s[1] = s[1] || '';
		              s[1] += new Array(prec - s[1].length + 1).join('0');
		          }
		          return s.join(dec);
		      }
					  
		      function carregar_pagamentos(tf, valor,frete)
		      {
            if(typeof(frete) != "undefined")
            {
		      	  frete = Number(frete.replace(/[^0-9\.]+/g,""));
  	      		var valor_novo = <? echo moeda2bd($carrinho->exibir_total()); ?> + frete;
  	      		$('#valor_total_h1').text('R$ '+formatar_numero(valor_novo, 2, ',', '.'));
  	      		$('#valor_total').val(valor_novo);
            }
		      	


			      var var_url;
			      var_url='tipo=formas_pg&valor='+valor+'&tf='+tf;
            jQuery.ajax({
			        url: 'ipi_req_carrinho_pedido_ajax.php',
              type: "POST",
              data: var_url,
              success: function(ret)
				      {
				        document.getElementById('formas_pagamentos').style.display="block";
                $("#formas_pagamentos").html(ret);
				      }
			      });

			      var var_url;
			      var_url='tipo=horarios_agendamento&valor='+valor+'&tf='+tf;
            jQuery.ajax({
			        url: 'ipi_req_carrinho_pedido_ajax.php',
              type: "POST",
              data: var_url,
              success: function(ret)
				      {
                $("#conteudo_agendamento").html(ret);
				        //document.getElementById('deseja_agendar').style.display="block";
				      }
			      });
		      }

          function showAnchorTitle(element, text) 
          {
            var offset = element.offset();

            $('#anchorTitle').css
            ({ 
              'top'  : '140px',
              'left' : '240px'
            })
            .html(text)
            .show();
          }

          function hideAnchorTitle() 
          {
            $('#anchorTitle').hide();
          }

          function liberar_horario(obj)
          {
            document.getElementById('agendar').value=obj.value;
            if (obj.value=="Sim")
            {
              document.getElementById('qual_horario').style.display="block";
              document.getElementById('horario').value="def";
            }
            else
            {
              document.getElementById('qual_horario').style.display="none";
              document.getElementById('horario').value="def";
            }
          } 

          $(document).ready(function() 
          {      
            var pagto;
            if(document.getElementById('ativar_pagamento'))
            {
              pagto = document.getElementById('ativar_pagamento').value;  
              frete = document.getElementById('valor_frete_pagamento').value;
            }  
            else
              pagto = 0;

            if(pagto != 0)
            {
              carregar_pagamentos('Entregar', pagto,frete);

            }

            $('#resumo_pagamento_dados').append('<div id="anchorTitle"></div>');

            $('#tip_credit_card').each(function() 
            {
              var a = $(this).after($('<span/>'));

              a.data('title', a.attr('title'))
              .removeAttr('title')
              .hover
              (
                function() { showAnchorTitle(a, a.data('title')); }, 
                function() { hideAnchorTitle(); }
              );
            });
            <?/*
              if(date("H")<$hora_inicial)
              {
                ?>
                  document.getElementById("deseja_agendar").style.display = "none";
                  document.getElementById("aviso_agendar").style.display = "block";
                  document.getElementById('agendamento').value="Sim";   
                  document.getElementById('agendar').value="Sim";   
                  document.getElementById('qual_horario').style.display="block";
                  document.getElementById('horario').value="def";                  
                <?
              }*/
            ?>
          });

		      </script>

		      <form name="frmPagamento" method="post" action="ipi_req_carrinho_acoes.php">

           
           <?
           	// ENDEREÇOS //
		        if ($_SESSION['ipi_carrinho']['buscar_balcao']=="Entrega")
		        {
		          echo '<div class="resumo_topo_laranja"></div>';
           		  echo '<div class="resumo_meio_laranja fonte12">';
          		  echo "<h3 class='fonte20 cor_marrom2'>ENDEREÇO DE ENTREGA</h3>";
		          $con = conectabd();
		          $sqlEnderecos = "SELECT * FROM ipi_enderecos e WHERE cod_clientes=".$_SESSION['ipi_cliente']['codigo'];
		          $resEnderecos = mysql_query ( $sqlEnderecos );
		          $linEnderecos = mysql_num_rows ( $resEnderecos );
		          
		          
		          if($linEnderecos <= 1)
			          echo '<h2 class="fonte16 cor_branco">Por favor, confira mais uma vez o seu endereço:</h2><br/>';
		          elseif($linEnderecos == 0)
                echo '<h2 class="fonte16 cor_branco">Opaa,</h2><br /> parece que você ainda não tem nenhum endereço cadastrado<br/><a href="enderecos" title="Cadastre um endereço">Clique aqui</a> e cadastre um endereço agora mesmo!';
              else
			          echo '<h2 class="fonte16">Selecione o Endereço de entrega!</h2><br/>';

		          //$carrinho = new ipi_carrinho();
		          echo $carrinho->exibir_enderecos_entrega($_SESSION['ipi_cliente']['codigo']);
          		  echo '</div>';
   		  		  echo '<div class="resumo_rodape_laranja"></div>';
		        }
		        elseif ($_SESSION['ipi_carrinho']['buscar_balcao']=="Balcão")
		        {
		        	echo '<div class="linha">';
           		  	echo '<div class="box_item">';
          		  	echo "<h3 class='fonte20 cor_marrom2'>ENDEREÇO DE RETIRADA</h3>";
           		  	echo '<table border="0" cellspacing="0" cellpadding="2" class="tabela">';
       		  		
		            echo '<tbody>';
		            $con = conectabd();

                if (isset($_SESSION['ipi_carrinho']['cod_pizzarias'])) 
                {
                  $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
                }
                require("pub_req_fuso_horario1.php");

      					$arr_dias_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab');
								$dia_semana_hoje = $arr_dias_semana[date('w')]; 
								$sql_funcionamento = "SELECT * FROM ipi_pizzarias_funcionamento WHERE cod_pizzarias = '".$_SESSION['ipi_carrinho']['cod_pizzarias']."' AND dia_semana=".date('w');
								$res_funcionamento = mysql_query($sql_funcionamento);         
								$num_funcionamento = mysql_num_rows($res_funcionamento);
								desconectabd($con);
		            $obj_endereco_pizzaria = executar_busca_simples('SELECT * FROM ipi_pizzarias WHERE cod_pizzarias='.$_SESSION['ipi_carrinho']['cod_pizzarias']);
		            if($obj_endereco_pizzaria->situacao!="INATIVO" && $num_funcionamento>0)
		            {
		            	if((isset($_SESSION['ipi_carrinho']['cupom']) && in_array($_SESSION['ipi_carrinho']['cod_pizzarias'], $_SESSION['ipi_carrinho']['cupom_pizzarias'])) || !isset($_SESSION['ipi_carrinho']['cupom']))
                  {
				            echo '<tr><td align="center" class="padding_15 cor_amarelo1 fonte14">Retirada no<br />balcão</td>';
			            	echo '<td class="padding_15 cor_branco">';
				            echo '<strong class="fonte16">'.$obj_endereco_pizzaria->nome.'</strong>';
				            echo '<br />'.$obj_endereco_pizzaria->endereco.', '.$obj_endereco_pizzaria->numero;
				            echo '<br />'.$obj_endereco_pizzaria->cidade.' | '.$obj_endereco_pizzaria->bairro;
				            echo '<br />'.str_replace('(','',str_replace(')',' ',$obj_endereco_pizzaria->telefone_1));
				            echo '</td></tr>';
				            echo "<script>carregar_pagamentos('Balcao', '".$_SESSION['ipi_carrinho']['cod_pizzarias']."')</script>";
				          }
				          else
				          {
				          	echo '<tr><td align="center" class="padding_15 cor_amarelo1 fonte14">A Pizzaria para retirada <br/>do seu pedido não participa <br />da promoção do cupom utilizado</td>';
			            	echo '<td class="padding_15 cor_branco">';
				            echo '<strong class="fonte16">'.$obj_endereco_pizzaria->nome.'</strong>';
				            echo '<br />'.$obj_endereco_pizzaria->endereco.', '.$obj_endereco_pizzaria->numero;
				            echo '<br />'.$obj_endereco_pizzaria->cidade.' | '.$obj_endereco_pizzaria->bairro;
				            echo '<br />'.str_replace('(','',str_replace(')',' ',$obj_endereco_pizzaria->telefone_1));
				            echo '</td></tr>';
				          }
			          }
			          else
			          {
			            echo '<tr><td align="center" class="padding_15 cor_amarelo1 fonte14">Pizzaria <br />fechada</td>';
		            	echo '<td class="padding_15 cor_branco">';
			            echo '<strong class="fonte16">'.$obj_endereco_pizzaria->nome.'</strong>';
			            echo '<br />'.$obj_endereco_pizzaria->endereco.', '.$obj_endereco_pizzaria->numero;
			            echo '<br />'.$obj_endereco_pizzaria->cidade.' | '.$obj_endereco_pizzaria->bairro;
			            echo '<br />'.str_replace('(','',str_replace(')',' ',$obj_endereco_pizzaria->telefone_1));
			            echo '</td></tr>';
			            //echo "<script>carregar_pagamentos('Balcao', '".$_SESSION['ipi_carrinho']['cod_pizzarias']."')</script>";
			          }
/*
			          if((date('w')==1) && ($_SESSION['ipi_carrinho']['cod_pizzarias']==14))
				        {
				          echo '<br /><h1>Hoje sua Unidade não está atendendo balcão,  seu pedido deverá ser retirado na unidade da <u><b>Vila Adyanna</b></u></h2>';
				        }
*/
		            echo '</tbody>';


		            echo '</table>';
         		  	echo '</div>';
              echo '</div>';
		        }

          // AGENDAMENTO //
          //if($_SESSION['ipi_carrinho']['buscar_balcao'] == "Entrega")
          //{
            echo '<br />';
            echo '<div class="linha">';
              echo '<div id="conteudo_agendamento" class="box_item">';
              $hora_habil = false;
              $data_hora_corte = mktime(date("H"),date("i") + $minutos_adicionais,date("s"), date("m"),date("d"),date("Y"));
              ?>
                <h3 class=''>AGENDAMENTO</h3>
                <span class=""  id="aviso_endereco"><br />Selecione um endereço para ver os horarios para a entrega!</span>
                <div id="deseja_agendar" style='display: none;'>
                	
                  <h2 class="fonte16 cor_branco" >Deseja agendar?</h2>
                  <select name="agendamento" id="agendamento" onchange="javascript:liberar_horario(this);" style='display: block; color:black'>
                      <option value="">&nbsp;</option>
                      <option value="Sim">Sim</option>
                      <?
                        if(date("H")>=$hora_inicial)
                        {
                          echo '<option value="Não">Não</option>';
                        }
                      ?>
                  </select>
                  </br />
                </div>

                <div id="aviso_agendar" style='display: none;'>

                  Agora são <? echo date('H:i'); ?> e nossas pizzarias ainda estão fechadas. <br/> Mas você pode agendar!
                  <br />
                </div>

                <div id="qual_horario" style='display: none;'>

                </div>

              <?

              echo '</div>';
              echo '</div>';
              echo '<input type="hidden" name="agendar" id="agendar" value="" />';
		      //}

          // MEUS PONTOS //
          echo "
          <div class='pedido_pontos_fidelidade'>
          	<ul class='lista_infos3'>
          		<li>
          			<p class='fonte18 cor_branco fonte_muzza1'><strong>Você tem</strong>: ".$carrinho->pontos_fidelidade()." pontos";
          if ($carrinho->pontos_fidelidade() != NULL || $carrinho->pontos_fidelidade() != 0)
          {
	          echo	"
	          			<a href='usar_fidelidade' alt='Use seus pontos'>Usar Pontos</a>
	          		";
          }
          echo	"</p></li></ul>
          </div>";
          ?>
          <!-- PAGAMENTO -->

          <div class='linha' style="min-height: 180px">
          <div class='box_item'>
          <div id="resumo_pagamento_forma">
            <h3>PAGAMENTO</h3>
		        <div id="formas_pagamentos">

			        <table border="0" cellspacing="0" cellpadding="2" class="tabela" align="center" width="310">
			          <thead>
			           <tr>
				          <td>
				          	<br />Selecione um endereço para ver<br />as formas de pagamento! <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
			           	</td>
			           </tr>
			          </thead>
			        </table>

		        </div>
          </div>

          <div id="resumo_pagamento_dados"> 
          	<h3 id='cartao_dinheiro'>&nbsp;</h3>
		      <table border="0" cellspacing="0" cellpadding="2" class="tabela" align="center" width="300">
		        <tr id="necessita_troco" style="display: none">
			      <td width="300">
				      <label for='necessita_troco'>Você vai precisar de troco?</label>
				      <select name="necessita_troco" id="necessita_troco" onchange="javascript:verificar_troco(this);" style="color:black">
					      <option value=""></option>
					      <option value="Não">Não</option>
					      <option value="Sim">Sim</option>
				      </select>
		       	</td>
		         </tr>


		        <tr>
			      <td width="300" id="troco_quanto" style="display: none">
			      <label for='troco'>Troco para quanto? </label> <input type="text" name="troco" id="troco" size="10" maxlength="8" value="0" onKeyPress="return formataMoeda(this, '.', ',', event)"/>
		       	</td>
		        </tr>
		        

		        <tr>
			      <td id="mensagem_visa" style="display: none" align="center">
				

				      <table border="0" cellspacing="0">
					      <tr>
						      <td style="border: none;" align="left"><img src="img/banner1_fundo.gif" width="100" height="40" id="logo_operadora_cc"/></td>
						      <td style="border: none;" align="left" class='padding_2'><input type="text" name="bandeira_cc" id="bandeira_cc" value="" size="19" readonly="readonly"/></td>
					      </tr>
					      <tr>
						      <td style="border: none;" align="left"><label for='txt_nc'>Nome no Cartão:</label></td>
						      <td style="border: none;" align="left" class='padding_2'><input type="text" name="txt_nc" id="txt_nc" value="" maxlength="50" class="form_text3"/></td>
					      </tr>
					      <tr>
						      <td style="border: none;" align="left"><label for='txt_cc'>Número do Cartão:</label></td>
						      <td style="border: none;" align="left" class='padding_2'><input type="text" name="txt_cc" id="txt_cc" value="" maxlength="16" onkeypress="return ApenasNumero(event)" class="form_text3"/></td>
					      </tr>
					      <tr>
						      <td style="border: none;" align="left"><label for='txt_cs'>Cod. Segurança:</label></td>
						      <td style="border: none;" align="left" class='padding_2' id='td_credit_card'><input type="text" name="txt_cs" id="txt_cs" value="" maxlength="3" onkeypress="return ApenasNumero(event)" class="form_text1"/>
						      <a href="#" onclick="return false;" class="Tips1" id="tip_credit_card" title="<center><b>Código de Segurança</b></center><Br>Na parte de trás do cartão, os 3 dígitos<Br>como mostra a figura abaixo: <br><br><center><img src='img/cod_seg_cc.gif'></center>"><b>O que é isto?</b></a>
						      </td>
					      </tr>
					      <tr>
						      <td style="border: none;" align="left">Data de Validade:</td><td style="border: none;" align="left" class='padding_2'>
						      <select name="txt_dvm" size="1" style="color:black">
						         <option value=""></option>
						         <option value="01">01</option>
						         <option value="02">02</option>
						         <option value="03">03</option>
						         <option value="04">04</option>
						         <option value="05">05</option>
						         <option value="06">06</option>
						         <option value="07">07</option>
						         <option value="08">08</option>
						         <option value="09">09</option>
						         <option value="10">10</option>
						         <option value="11">11</option>
						         <option value="12">12</option>
						      </select>
						      /
						      <select name="txt_dva" size="1" style="color:black">
						         <option value=""></option>
						         <?
						          $ano4 = date("Y");
						          $ano2 = date("y");
						          for ($a=0; $a<10; $a++)
						          {
						              echo '<option value="'.($ano4+$a).'">'.($ano4+$a).'</option>';
						          }
						         ?>
						      </select>
						      &nbsp;<small>MÊS/ANO</small>
						      </td>
					      </tr>
				      </table>


			      </td>
		        </tr>
		       </table>


		      <table border="0" cellspacing="0" cellpadding="2" class="tabela" align="center" width="300">
		      <thead>
		        <tr>
			      <td id="nota_fiscal_paulista" style="display: none" class='padding_2'>
				      <label for='nota_fiscal_paulista'>CPF na Nota Fiscal?</label>
				      <select name="nota_fiscal_paulista" id="nota_fiscal_paulista" onchange="javascript:verificar_nota(this);" style="color:black">
				          <option value="Não">Não</option>
				          <option value="Sim">Sim</option>
				      </select>
			      </td>
		         </tr>
		        </thead>
		        <tbody>
		        <tr>
			      <td id="qual_cpf" style="display: none" align='center' class='padding_2'>
			      <label for='cpf_nota_paulista'>Qual CPF?</label> <input type="text" name="cpf_nota_paulista" id="cpf_nota_paulista" size="15" value="" onKeyPress="return MascaraCPF(this, event)" class='form_text5'/>
			      </td>
		        </tr>
		        </tbody>
		      </table>

          </div>
          </div>


          <div id="resumo_pagamento_total">
            Valor Total do pedido: 
            <?
            $carrinho = new ipi_carrinho();
            
            echo "R$ ".($carrinho->exibir_total() == "" ?  '0,00': $carrinho->exibir_total());
            echo "<input type='hidden' name='valor_total' id='valor_total' value='".$carrinho->exibir_total()."' />";
            ?>
          </div>

		     
		      <br />






		        <input type="hidden" name="acao" value="confirmar_pagamento"/>
		       </form>
          
<!--
          <a class="float_right" href='pagamentos' title='Finalizar o seu pedido!'> <img src='img/pc/btn_finalizar_g.png' /> </a>
-->
              <div class='clear'></div>
            </div>
            <div class='resumo_rodape_marrom'></div>
          </div>

          <script type="text/javascript">
          function submit_pagamento()
          {
           $('#id_fechar_pedido').fadeOut(100, function () {
          	 $('#id_fechar_pedido').html("<img src='imgs/loader.gif' border='0' alt='Finalizando o seu pedido!'>");
           }); 
		   $('#id_fechar_pedido').fadeIn(100);
           document.frmPagamento.submit(); 
		  }
          </script>


                    


          <table border="0" cellspacing="0" cellpadding="2" align="center" id="botoes_bebidas" class="float_right">
            <tr>
              <td align="center"  id="id_fechar_pedido">
                <div class="box_footer_pgto">
                    <h2>ESQUECEU DE PEDIR ALGO MAIS?</h2>
                    <span>(Bebida, outra pizza ou quem sabe um Combo)</span>
                    <a href="algo_mais" class="btn btn-secondary btn-small">Pedir</a>
                </div>
                <div class="box_footer_pgto">
                  <a id="botao_fechar_pedido" href="javascript:void(0);" onclick="javascript: if(ValidarPagamento(document.frmPagamento)) { submit_pagamento() }" title="Finalizar o seu pedido!" class="btn btn-success">Finalizar</a>
                </div>
              </td>
            </tr>
          </table>
          <!--
          <div align='left' class='div_pedir_mais'>
        		<div class='float_right'><a href='algo_mais'><img class='botao_pedir_mais' src='img/pc/btn_pedir_algo_mais.png' alt='Pedir Algo Mais' ></a></div>
          	<p class='cor_amarelo1 fonte20 fonte_muzza1'>ESQUECEU DE PEDIR ALGO MAIS?</p>
          	<p class='cor_branco fonte_muzza1 fonte16'>(Bebida,outra pizza ou quem sabe um Combo)</p>
          	
          </div>
          -->
<!-- 
          <div>
            <p align='center'> Pedidos promocionais ou com gastos em pontos de fidelidade não podem ser refeitos!</p>
          </div>
 -->
          <div class='clear'></div>

          <?
        }
        else
        {
          ?>     
          Não há nenhum pedido em andamento.
          <a class='pedir_algo float_right' href='pedidos' title='Peça algo agora mesmo!'> <img src='img/pc/btn_pedir_algo.png' alt='Peça algo agora mesmo!' /> </a>
          <div class='clear'></div>
          <? 
        } 
        ?> &nbsp;
      </div>
    <div class='b'></div>
  </div>
<?


  /*
		$con = conectar_bd();
		$sql_busca_pedidos = "select cod_pedidos from ipi_pedidos where cod_clientes = ".$_SESSION['ipi_cliente']['codigo'];
		$res_busca_pedidos = mysql_query($sql_busca_pedidos);
		$arr_codigos_pedidos = array();
		$arr_codigos_pedidos_pizzas = array();
		$tem = false;
    $fidelidade = false;
    $promocional = false;
    $item_desativado = false;
		$pizza = "Você ainda não tem<br/> uma pizza <br/>favorita";//,<br/>faça um pedido agora
		if($_SESSION['ipi_cliente']['autenticado'] == true)
    {
			if(mysql_num_rows($res_busca_pedidos)>0)
				{
					while($obj_busca_pedidos = mysql_fetch_object($res_busca_pedidos))
					{
						$arr_codigos_pedidos[] = $obj_busca_pedidos->cod_pedidos;
					}
					$str_codigos = implode(',',$arr_codigos_pedidos);
				
					$sql_busca_pedidos_pizzas = "select cod_pedidos_pizzas from ipi_pedidos_pizzas where cod_pedidos in (".$str_codigos.")";
					$res_busca_pedidos_pizzas = mysql_query($sql_busca_pedidos_pizzas);
					while($obj_busca_pedidos_pizzas  = mysql_fetch_object($res_busca_pedidos_pizzas))
					{
						$arr_codigos_pedidos_pizzas[] = $obj_busca_pedidos_pizzas->cod_pedidos_pizzas;
					}
					$str_codigos_pizzas = implode(',',$arr_codigos_pedidos_pizzas);
				
					$sql_busca_favorita = "SELECT fr.cod_pizzas,p.pizza,count(fr.cod_pizzas) as qtd FROM ipi_pedidos_fracoes fr INNER JOIN ipi_pizzas p ON (fr.cod_pizzas = p.cod_pizzas) WHERE fr.cod_pedidos in (".$str_codigos.") AND fr.cod_pedidos_pizzas in (".$str_codigos_pizzas.") group BY fr.cod_pizzas order by qtd DESC limit 1";
					//echo $sql_busca_favorita;
					$res_busca_favorita = mysql_query($sql_busca_favorita);
					$obj_busca_favorita = mysql_fetch_object($res_busca_favorita);
					//while($obj_busca_favorita = mysql_fetch_object($res_busca_favorita))
					//{
					//	echo $obj_busca_favoria->fracao."  ".$obj_busca_favorita->qtd;
					//}
					$pizza = mb_strtoupper($obj_busca_favorita->pizza);
					$tem = true;
				}



      echo "<div class='bottom_box'>
        <div class='float_left favorita'>
        <p class='float_left cor_amarelo1 fonte18'> ".$pizza." </p>
        ".($tem? " <a class='pedir float_left' href='pedidos' title='Peça a sua pizza favorita!'> <img src='img/pc/btn_pedir_favorita.png' alt='Peça a sua pizza favorita!' />" : "" )." </a>
        </div>";	

        
			$sql_busca_pedidos = "select * from ipi_pedidos where cod_clientes = ".$_SESSION['ipi_cliente']['codigo']." order by data_hora_final DESC LIMIT 1";
        $res_busca_pedidos = mysql_query($sql_busca_pedidos);
        $obj_busca_pedidos = mysql_fetch_object($res_busca_pedidos);
      
      if(mysql_num_rows($res_busca_pedidos)>0)
      {
        $sql_busca_pedidos_pizzas = "select pp.*,t.* from ipi_pedidos_pizzas pp inner join ipi_tamanhos t on t.cod_tamanhos = pp.cod_tamanhos where cod_pedidos in (".$obj_busca_pedidos->cod_pedidos.")";
        //echo $sql_busca_pedidos_pizzas;
        $res_busca_pedidos_pizzas = mysql_query($sql_busca_pedidos_pizzas);
        $obj_busca_pedidos_pizzas = mysql_fetch_object($res_busca_pedidos_pizzas );
        $num_busca_pedidos_pizzas = mysql_num_rows($res_busca_pedidos_pizzas);
    
          if($obj_busca_pedidos_pizzas->fidelidade)
            $fidelidade = true;
          if($obj_busca_pedidos_pizzas->promocional)
            $promocional = true;



          if ($num_busca_pedidos_pizzas>0)
          {
          $sql_busca_pedidos_adicionais = "select pp.*,ta.preco as preco_gergi from ipi_pedidos_adicionais pp left join ipi_tamanhos_ipi_adicionais ta on ta.cod_tamanhos = '".$obj_busca_pedidos_pizzas->cod_tamanhos."' and ta.cod_pizzarias = '".$obj_busca_pedidos->cod_pizzarias."' and ta.cod_adicionais = pp.cod_adicionais where cod_pedidos in (".$obj_busca_pedidos->cod_pedidos.")";
          $res_busca_pedidos_adicionais = mysql_query($sql_busca_pedidos_adicionais);
    			//echo "<br/><br/>".$sql_busca_pedidos_adicionais;
          $gergilim = "Sem gergelim na borda";
          if(mysql_num_rows($res_busca_pedidos_adicionais)>0)
          {
            $obj_busca_pedidos_adicionais  = mysql_fetch_object($res_busca_pedidos_adicionais );
            $gergilim = "Com gergilim na borda";
            if($obj_busca_pedidos_adicionais->fidelidade)
              $fidelidade = true;
            if($obj_busca_pedidos_adicionais->promocional)
              $promocional = true;

            if($obj_busca_pedidos_adicionais->preco_gergi=="")
              $item_desativado = true;
          }
    	
          $sql_busca_pedidos_bordas = "select pp.*,tb.preco as preco_borda from ipi_pedidos_bordas pp LEFT join ipi_tamanhos_ipi_bordas tb on tb.cod_bordas = pp.cod_bordas and tb.cod_tamanhos = '".$obj_busca_pedidos_pizzas->cod_tamanhos."' and tb.cod_pizzarias = '".$obj_busca_pedidos->cod_pizzarias."' where cod_pedidos in (".$obj_busca_pedidos->cod_pedidos.")";
          $res_busca_pedidos_bordas = mysql_query($sql_busca_pedidos_bordas);
    	//echo "<br/><br/>".$sql_busca_pedidos_bordas;
          $borda = "Sem borda recheada";
          if(mysql_num_rows($res_busca_pedidos_bordas)>0)
          {
            $obj_busca_pedidos_bordas  = mysql_fetch_object($res_busca_pedidos_bordas);

            if($obj_busca_pedidos_bordas->fidelidade)
              $fidelidade = true;
            if($obj_busca_pedidos_bordas->promocional)
              $promocional = true;
      			
            if($obj_busca_pedidos_bordas->preco_borda=="")
            {
            	$item_desativado = true;
            }

            $sql_busca_pedidos_bordas = "select pp.* from ipi_bordas pp where cod_bordas in (".$obj_busca_pedidos_bordas->cod_bordas.")";
          //  echo $sql_busca_pedidos_bordas;
            $res_busca_pedidos_bordas = mysql_query($sql_busca_pedidos_bordas);
            $obj_busca_pedidos_bordas  = mysql_fetch_object($res_busca_pedidos_bordas);
            $borda = "Com borda de ".$obj_busca_pedidos_bordas->borda;
          }

            $sql_busca_pedidos_sabor = "SELECT p.pizza as qtd,pt.preco,fr.cod_pedidos_fracoes FROM ipi_pedidos_fracoes fr INNER JOIN ipi_pizzas p ON (fr.cod_pizzas = p.cod_pizzas) LEFT join ipi_pizzas_ipi_tamanhos pt on pt.cod_pizzas = p.cod_pizzas and pt.cod_tamanhos = '".$obj_busca_pedidos_pizzas->cod_tamanhos."' and pt.cod_pizzarias = '".$obj_busca_pedidos->cod_pizzarias."' WHERE fr.cod_pedidos = (".$obj_busca_pedidos->cod_pedidos.") AND fr.cod_pedidos_pizzas = (".$obj_busca_pedidos_pizzas->cod_pedidos_pizzas.") group BY fr.cod_pizzas order by qtd DESC";
          //echo "<br/><br/>".$sql_busca_pedidos_sabor;
          $res_busca_pedidos_sabor = mysql_query($sql_busca_pedidos_sabor);
          //$obj_busca_pedidos_sabor = mysql_fetch_object($res_busca_pedidos_sabor);
          $arr_info  = array();
           $tamanho = explode('(',$obj_busca_pedidos_pizzas->tamanho);
          $pizza = "1 ".$tamanho[0]." com ".$obj_busca_pedidos_pizzas->quant_fracao."".($obj_busca_pedidos_pizzas->quant_fracao >1 ? " Sabores" : " Sabor");
          $arr_info[] = $pizza; 
          while($obj_busca_pedidos_sabor = mysql_fetch_object($res_busca_pedidos_sabor))
          {

            $arr_info[] = $obj_busca_pedidos_sabor->qtd;

            if($obj_busca_pedidos_sabor->preco=="")
            {
            	$item_desativado = true;
            }

	          $sql_buscar_adicionais_removidos = "SELECT pi.cod_ingredientes,it.preco FROM ipi_pedidos_ingredientes pi LEFT JOIN ipi_ingredientes_ipi_tamanhos it on it.cod_ingredientes = pi.cod_ingredientes and it.cod_tamanhos = '".$obj_busca_pedidos_pizzas->cod_tamanhos."' and it.cod_pizzarias = '".$obj_busca_pedidos->cod_pizzarias."' WHERE pi.ingrediente_padrao = 0 AND pi.cod_pedidos_pizzas = ".$obj_busca_pedidos_pizzas->cod_pedidos_pizzas." AND pi.cod_pedidos = ".$obj_busca_pedidos->cod_pedidos." AND pi.cod_pedidos_fracoes = ".$obj_busca_pedidos_sabor->cod_pedidos_fracoes;
	          $res_buscar_adicionais_removidos = mysql_query($sql_buscar_adicionais_removidos);
						while($obj_buscar_adicionais_removidos = mysql_fetch_object($res_buscar_adicionais_removidos))
						{
							if($obj_buscar_adicionais_removidos->preco=="")
							{
								$item_desativado = true;
							}

						}
          }
         

          $sql_buscar_pedidos_bebidas = "SELECT *, p.preco AS pedidos_preco,pz.preco AS preco_bebida_atual FROM ipi_pedidos_bebidas p INNER JOIN ipi_bebidas_ipi_conteudos bc ON (p.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) LEFT JOIN ipi_conteudos_pizzarias pz on pz.cod_pizzarias = '".$obj_busca_pedidos->cod_pizzarias."' and pz.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos and pz.situacao='ATIVO' and pz.venda_net = '1' WHERE cod_pedidos = ".$obj_busca_pedidos->cod_pedidos;
				  $res_buscar_pedidos_bebidas = mysql_query($sql_buscar_pedidos_bebidas);
				  $rows_buscar_pedidos_bebidas = mysql_num_rows($res_buscar_pedidos_bebidas);
				  while($obj_buscar_pedidos_bebidas = mysql_fetch_object($res_buscar_pedidos_bebidas)) 
				  {

			      if($obj_buscar_pedidos_bebidas->preco_bebida_atual=="")
			      {
			        $item_desativado = true; 
			      }
				  }


          $sql_busca_pedidos_corte = "select * from ipi_opcoes_corte where cod_opcoes_corte = ".$obj_busca_pedidos_pizzas ->cod_opcoes_corte;
          $res_busca_pedidos_corte = mysql_query($sql_busca_pedidos_corte);
          $obj_busca_pedidos_corte = mysql_fetch_object($res_busca_pedidos_corte);
          $corte = "Corte ".$obj_busca_pedidos_corte->opcao_corte ;
    
          $sql_busca_pedidos_massa = "select * from ipi_tipo_massa where cod_tipo_massa = ".$obj_busca_pedidos_pizzas ->cod_tipo_massa;
          $res_busca_pedidos_massa = mysql_query($sql_busca_pedidos_massa);
          $obj_busca_pedidos_massa = mysql_fetch_object($res_busca_pedidos_massa);
          $massa = "Massa ".$obj_busca_pedidos_massa->tipo_massa; 
          
          $arr_info[] = $borda;
          $arr_info[] = $massa;
          if(count($arr_info)<7)
          {
           $arr_info[] = $corte;
          }
          $txt_infos = implode("<br/>",$arr_info);    

            //$pizza."<br />".$borda."<br />".$massa."<br />".$corte."<br />".$ultimo_sab
            echo "<div class='float_right ultima'>
            <h1 class='fonte22 cor_marrom2'> Seu último pedido foi: </h1>
            <img src='img/pc/pedido_ilust_six_".$obj_busca_pedidos_pizzas->quant_fracao.".png' alt='".$obj_busca_pedidos_pizzas->quant_fracao." Sabores' />
            <p class='fonte12 cor_branco float_right'>".$txt_infos."</p>
            <div class='botoes'>
              <form id='FrmVerPost' method='post' action='meus_pedidos'>
                <a class='float_right' href='#' onclick='$(\"#FrmVerPost\").submit()' title='Mais detalhes sobre esse pedido.'> Ver+ </a>
                <input type='hidden' name='acao' value='detalhes' />
                <input type='hidden' name='cod_pedidos' value='".$obj_busca_pedidos->cod_pedidos."' />
              </form>";

              // REPETIR PEDIDO
              echo '<form id="frmRepetir" method="post" action="meus_pedidos" class="repetir float_right">';
              echo '<input type="hidden" name="acao" id="acao" value="detalhes" />';
              echo '<input type="hidden" name="cod_pedidos" id="cod_pedidos" value="'.$obj_busca_pedidos->cod_pedidos.'" />';
              if($promocional == false && $fidelidade == false && $item_desativado == false)
                echo '<input type="image" src="img/pc/btn_repetir.png" alt="Clique para ver mais detalhes do pedido." value="Alterar" />';
              echo '</form>';

            echo "</div>
            <div class='clear'> </div>
          </div>";
        }
		    	
		    }
        else
		    {

          echo "<div class='float_right ultima'>
				    <h1 class='fonte22 cor_marrom2'> Seu último pedido foi: </h1>
				    <img src='img/pc/pedido_ilust_six_1.png' alt='Pizza 1 Sabor' />
				    <p class='fonte12 cor_branco float_right'>Você precisa estar logado<br />para ter um último pedido</p>
				    <div class='clear'> </div>
				  </div>";
     
		    }
      }
		desconectar_bd($con);
		*/
    ?>

  </div>
</div>





		<?
						}					
						else
						{
									?>
						<table border="0" cellspacing="0" cellpadding="2" width="630">
							<tr>
								<td valign="top">
									<div class='div_combo_incompleto fonte_muzza1 cor_marrom2'>
								<br><br>
									Não é possível concluir a compra!<Br>
									Existe uma pizza promocional pendente <br/>no seu carrinho!
									<?
									echo '<br><b><a href="pedido_promocional&codPizzaPai='.$_SESSION["ipi_carrinho"]["pizza_promocional_pai"].'">Clique aqui para voltar a pizza</a></b>';
									
									?>
								</div>
								</td>
							 </tr>
						 </table>
						<?
						}
			
			}
			else
			{
					?>
					<table border="0" cellspacing="0" cellpadding="2" width="630">
					  <tr>
					    <td valign="top" align="center">
					    <br /><br />

						    <font color="#FF0000">
						    <b>Você ainda não tem um endereço cadastrado!</b><Br>
						    <a href='meus_enderecos' title='Clique aqui e seja redirecionado para a pagina para cadastro de  enderecos'>Clique aqui e seja redirecionado para a pagina para cadastro de  enderecos</a>!<br> 
						    </font>

					    <br /><br />
					    </td>
					   </tr>
					 </table>
	 				<?
			}
		}
		else
		{
			if($habilitado_cupom==0 && $verificou_cupom)
			{
			 ?>
			<div class='fonte20 pedido_minimo_caixa'>
			<div class='fonte30 atencao_pedido_minimo'>Atenção!</div>
			 <br/> O valor pedido mínimo para este<br/>cupom é de (R$ <? echo $valor_minimo_compra; ?>).
		    <br /><br/><a href="pedidos" class="fonte 16negrito">Clique aqui para voltar ao carrinho</a>		
			</div>
			<?
			}
			else
			{
				?>
				<div class='fonte20 pedido_minimo_caixa'>
			<div class='fonte30 atencao_pedido_minimo'>Atenção!</div>
			 <br/> O valor pedido mínimo no site é <br/>de (R$ <? echo $valor_minimo_compra; ?>).
		    <br /><br/><a href="pedidos" class="fonte 16negrito">Clique aqui para voltar ao carrinho</a>		
			</div>
				<?
			}
		
		}
	}
	else
	{
		?>
		<table border="0" cellspacing="0" cellpadding="2" width="630">
		  <tr>
		    <td valign="top">
		    	<div class='div_combo_incompleto fonte_muzza1 cor_marrom2'>
		    <br><br>
			    Não é possível concluir a compra!<Br>
			    Existe um combo incompleto no seu carrinho!
			    <?
			    if ($_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['tipo']=='PIZZA')
			    {
				    echo '<br><b><a href="pedido_combo">Clique aqui para voltar ao combo</a></b>';
			    }
			    elseif ($_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['tipo']=='BEBIDA')
			    {
				    echo '<br><b><a href="bebidas_combo">Clique aqui para voltar ao combo</a></b>';
			    }
			    ?>
			  </div>
		    </td>
		   </tr>
		 </table>
		<?
	}
}
else
{	
	?>
	<table border="0" cellspacing="0" cellpadding="2" width="630">
	  <tr>
	    <td valign="top" align="center">
	    <br /><br />

		    <font color="#FF0000">
		    <b>Não é possível concluir a compra!</b><Br>
		    O horário limite para fechar a compra é até a <b><? echo date("H:i", strtotime($hora_corte_inicio)); ?></b>!<br> 
		    Próximo horario para fazer pedidos agendados é as <b><? echo date("H:i", strtotime($hora_corte_fim)); ?></b>.
		    </font>

	    <br /><br />
	    </td>
	   </tr>
	 </table>
	<?
}
?>
