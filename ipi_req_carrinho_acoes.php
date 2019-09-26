<?
session_start();

require_once 'ipi_req_carrinho_classe.php';
require_once 'sys/lib/php/formulario.php';

$carrinho = new ipi_carrinho();
$acao = validaVarPost('acao');

$sugestao = validaVarPost('sugestao');
if($sugestao != '') {
  $_SESSION['ipi_carrinho']['sugestao'] = $sugestao;
}

if ($acao=="limpar")
{
	$carrinho->apagar_pedido();
}

if ($acao=="creditar_fidelidade")
{
  $cbPontos = validaVarPost('cbPontos');
  //echo "<br>acao: ".$acao."<br>";
  //print_r($cbPontos);
  $cod_pizzarias = $carrinho->retornar_codigo_pizzaria();
  $con = conectar_bd();
  
  if ($cbPontos>0)
  {
    $num_elementos = count($cbPontos);
    for ($a=0; $a<$num_elementos; $a++)
    {
      list($fidel_codigo, $fidel_tipo, $fidel_indice_ses) = split (",", $cbPontos[$a]);
      //echo "<br>Var: " ." - " . $fidel_codigo ." - ". $fidel_tipo ." - ". $fidel_indice_ses;
      //$carrinho->pontos_fidelidade()

      if ($fidel_tipo=="PIZZA")
      {
        $num_fracoes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao']);
        $preco_pizza = 0;
        for ($b = 0; $b < $num_fracoes; $b++)
        {
          $cod_pizzas = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
          $num_fracao = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['num_fracao'];
          $sqlAux = "SELECT pontos_fidelidade FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $_SESSION['ipi_carrinho']['pedido'][$fidel_indice_ses]['cod_tamanhos']." AND cod_pizzarias = '".$cod_pizzarias."'";
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          $preco_fracao = ceil($objAux->pontos_fidelidade / $num_fracoes) / 1000;
          $preco_pizza += ($preco_fracao);

        }
        //echo "<br/>".$preco_pizza."->".$_SESSION['ipi_carrinho']['pedido'][$fidel_indice_ses]['cod_tamanhos']."-".$num_fracoes."<br/>";

        if($carrinho->pontos_fidelidade() >= ($preco_pizza*1000))
        {
          //die();
          $_SESSION['ipi_carrinho']['pedido'][$fidel_indice_ses]['pizza_fidelidade']='1';
        }
      }
      elseif ($fidel_tipo=="BORDA")
      {
        $sql_buscar_fidelidade_borda = "SELECT pontos_fidelidade FROM ipi_tamanhos_ipi_bordas WHERE cod_tamanhos = '".$_SESSION['ipi_carrinho']['pedido'][$fidel_indice_ses]['cod_tamanhos']."' AND cod_pizzarias = '".$cod_pizzarias."' ";
        $res_buscar_fidelidade_borda = mysql_query($sql_buscar_fidelidade_borda);
        $obj_buscar_fidelidade_borda = mysql_fetch_object($res_buscar_fidelidade_borda);

        if($carrinho->pontos_fidelidade() >= $obj_buscar_fidelidade_borda->pontos_fidelidade)
        {
          $_SESSION['ipi_carrinho']['pedido'][$fidel_indice_ses]['borda_fidelidade']='1';
        }
      }
      elseif ($fidel_tipo=="BEBIDA")
      {
        $sql_buscar_fidelidade_bebida = "SELECT pontos_fidelidade FROM ipi_conteudos_pizzarias WHERE cod_bebidas_ipi_conteudos = '".$_SESSION['ipi_carrinho']['bebida'][$fidel_indice_ses]['cod_bebidas_ipi_conteudos']."' AND cod_pizzarias = '".$cod_pizzarias."' ";
        $res_buscar_fidelidade_bebida = mysql_query($sql_buscar_fidelidade_bebida);
        $obj_buscar_fidelidade_bebida = mysql_fetch_object($res_buscar_fidelidade_bebida);

        if($carrinho->pontos_fidelidade() >= $obj_buscar_fidelidade_bebida->pontos_fidelidade)
        {
          $_SESSION['ipi_carrinho']['bebida'][$fidel_indice_ses]['bebida_fidelidade']='1';
        }
      } 
    }   
  }

  //die();
  $acao="pagamentos";
    //echo "<br>acao: ".$acao;
  desconectar_bd($con);
  
  $carrinho->calcular_desconto_fidelidade();
}

if (($acao=="adicionar_bebidas")||($acao=="adicionar_verificar_login"))
{
	$cod_bebidas_conteudos = validaVarPost('cod_bebidas_conteudos');
	$quantidades = validaVarPost('quantidades');
	
	$num_bebida = count ($cod_bebidas_conteudos);
	for ($a=0; $a<$num_bebida; $a++)
	{
		if ($quantidades[$a]!="0")
			$carrinho->adicionar_bebida($cod_bebidas_conteudos[$a],$quantidades[$a]);
	}

	
}


if ($acao=="adicionar_bebida_promocional")
{
	$cod_bebidas_conteudos = validaVarPost('cod_bebidas_conteudos');
	$quantidades = validaVarPost('quantidades');
	
	$num_bebida = count ($cod_bebidas_conteudos);
	for ($a=0; $a<$num_bebida; $a++)
	{
		if ($quantidades[$a]!="0")
			$carrinho->adicionar_bebida_promocional($cod_bebidas_conteudos[$a],validaVarPost('nc'));
	}

	
}

if($acao=='excluir_combo')
{
  $id_combo = validaVarPost('id_combo', "/[0-9]+/");
  $carrinho->excluir_combo($id_combo);

  $cont_pizza = count($_SESSION['ipi_carrinho']['pedido']);

  if($cont_pizza>0)
  {
    $acao = 'algo_mais';
  }else
    $acao = 'pedidos';

}


if ($acao=="adicionar_bebida_combo")
{
    
    $cod_bebidas_conteudos = validaVarPost('cod_bebidas_conteudos');
    $quantidades = validaVarPost('quantidades');
    $indice_atual_combo = validaVarPost('indice_atual_combo');
    $id_combo = validaVarPost('id_combo');
    $cod_combos = validaVarPost('cod_combos');
    
    if($_SESSION['ipi_carrinho']['combo']['produtos'][$indice_atual_combo]['foi_pedido']=='N')
    {
      $num_bebida = count ($cod_bebidas_conteudos);
      for ($a=0; $a<$num_bebida; $a++)
      {
          if ($quantidades[$a]!="0")
              $carrinho->adicionar_bebida($cod_bebidas_conteudos[$a],$quantidades[$a], $id_combo, $cod_combos);
      }

      $_SESSION['ipi_carrinho']['combo']['produtos'][$indice_atual_combo]['foi_pedido']='S';
    }

    
    
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
        unset($_SESSION['ipi_carrinho']['combo']);
        $acao = "combo_redirecionar_bebidas_pedido";
    }
    else
    {
        if ($_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['tipo']=='PIZZA')
        {
            $acao = "combo_redirecionar_pizza";
        }
        elseif ($_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['tipo']=='BEBIDA')
        {
            $acao = "combo_redirecionar_bebida";
        }
    }
    
}


if ($acao=="excluir_pizza")
{	
	$ind_ses = validaVarPost('ind_ses');

  if($_SESSION['ipi_carrinho']['id_combo_atual']!="")
  {
    $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;

    if ($numero_pizzas > 0)
    {
        for ($a = 0; $a < $numero_pizzas; $a++)
        {
            if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_id_sessao']==$ind_ses)
            {
                $id_sessao_exclusao = $a;
                break;
            }
        }
        
    }
    if($_SESSION['ipi_carrinho']['pedido'][$id_sessao_exclusao]['id_combo']!="")
    {
      $carrinho->excluir_combo($_SESSION['ipi_carrinho']['pedido'][$id_sessao_exclusao]['id_combo']);
    }else
    {
      $carrinho->remover_pizza($ind_ses);
    }
  }
  else
  {
	 $carrinho->remover_pizza($ind_ses);
  }
  if(count($_SESSION['ipi_carrinho']['pedido'])>1)
  {
    if($_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"]==1)
    {
      $acao="ir_promocao";
      $promocao_cod = 12;
    }
    else
    {
  	  $acao="algo_mais";
    }
	}else
    $acao="pedidos";
}

if ($acao=="excluir_bebida")
{
	$ind_ses = validaVarPost('ind_ses');
	//echo 'Aki: '.$ind_ses;
  if($_SESSION['ipi_carrinho']['bebida'][$ind_ses]['id_combo']!="")
  {
    $carrinho->excluir_combo($_SESSION['ipi_carrinho']['bebida'][$ind_ses]['id_combo']);
  }
  else
  {
	  $carrinho->remover_bebida($ind_ses);
	}
  $acao="algo_mais";
}

if ($acao=="confirmar_pagamento")
{

	$forma_pagamento = validaVarPost('forma_pagamento');
	$troco = validaVarPost('troco');
	$necessita_troco = validaVarPost('necessita_troco');
	$cpf_nota_paulista = validaVarPost('cpf_nota_paulista');
  $cod_enderecos = validaVarPost('cod_enderecos');
  $cod_enderecos = validaVarPost('cod_enderecos');
  $agendar = validaVarPost('agendar');
  $horario = validaVarPost('horario');

  $bandeira_cc = validaVarPost('bandeira_cc');
  $txt_nc = validaVarPost('txt_nc');
  $txt_cc = validaVarPost('txt_cc');
  $txt_cs = validaVarPost('txt_cs');
  $txt_dva = validaVarPost('txt_dva');
  $txt_dvm = validaVarPost('txt_dvm');
  $cartao_compacto = compactar_valores($bandeira_cc.$txt_cc);

  $num_bloqueio = 0;
  $bloqueio_erro = array();

  // AGENDAMENTO //
  if($agendar == "Sim")
  {
    $_SESSION['ipi_carrinho']['agendar'] = $agendar;
    $_SESSION['ipi_carrinho']['horario'] = $horario;
  }
  else
  {
    $_SESSION['ipi_carrinho']['agendar'] = "Não";
  }

  
  $conexao = conectabd();

  $sql_bloqueio_email = "SELECT * FROM ipi_clientes_bloqueio cb WHERE cb.email='" . $_SESSION['ipi_cliente']['email'] . "' AND cb.situacao='BLOQUEADO' AND cb.email<>''";
  $res_bloqueio_email = mysql_query($sql_bloqueio_email);
  $num_bloqueio_email = mysql_num_rows($res_bloqueio_email);
  if ($num_bloqueio_email>0)
  {
    $obj_bloqueio_email = mysql_fetch_object($res_bloqueio_email);
    $bloqueio_erro[] = "em";
    $sql_log_bloqueio = "INSERT INTO ipi_clientes_bloqueio_log (cod_clientes, cod_clientes_bloqueio, data_hora_bloqueio) VALUES( '".$_SESSION['ipi_cliente']['codigo']."', '".$obj_bloqueio_email->cod_clientes_bloqueio."', '".date("Y-m-d H:i:s")."');";
    $res_log_bloqueio = mysql_query($sql_log_bloqueio);
  } 
  $num_bloqueio += $num_bloqueio_email;


  $sql_bloqueio_cpf = "SELECT * FROM ipi_clientes_bloqueio cb WHERE cb.cpf='" . $_SESSION['ipi_cliente']['cpf'] . "' AND cb.situacao='BLOQUEADO' AND cb.cpf<>''";
  $res_bloqueio_cpf = mysql_query($sql_bloqueio_cpf);
  $num_bloqueio_cpf = mysql_num_rows($res_bloqueio_cpf);
  if ($num_bloqueio_cpf>0)
  {
    $obj_bloqueio_cpf = mysql_fetch_object($res_bloqueio_cpf);
    $bloqueio_erro[] = "cp";
    $sql_log_bloqueio = "INSERT INTO ipi_clientes_bloqueio_log (cod_clientes, cod_clientes_bloqueio, data_hora_bloqueio) VALUES( '".$_SESSION['ipi_cliente']['codigo']."', '".$obj_bloqueio_cpf->cod_clientes_bloqueio."', '".date("Y-m-d H:i:s")."');";
    $res_log_bloqueio = mysql_query($sql_log_bloqueio);
  }
  $num_bloqueio += $num_bloqueio_cpf;

  $sql_bloqueio_cartao = "SELECT * FROM ipi_clientes_bloqueio cb WHERE cb.cartao_compacto='" . $cartao_compacto . "' AND cb.situacao='BLOQUEADO' AND cb.cartao_compacto<>''";
  $res_bloqueio_cartao = mysql_query($sql_bloqueio_cartao);
  $num_bloqueio_cartao = mysql_num_rows($res_bloqueio_cartao);
  if ($num_bloqueio_cartao>0)
  {
    $obj_bloqueio_cartao = mysql_fetch_object($res_bloqueio_cartao);
    $bloqueio_erro[] = "cc";
    $sql_log_bloqueio = "INSERT INTO ipi_clientes_bloqueio_log (cod_clientes, cod_clientes_bloqueio, data_hora_bloqueio) VALUES( '".$_SESSION['ipi_cliente']['codigo']."', '".$obj_bloqueio_cartao->cod_clientes_bloqueio."', '".date("Y-m-d H:i:s")."');";
    $res_log_bloqueio = mysql_query($sql_log_bloqueio);
  }
  $num_bloqueio += $num_bloqueio_cartao;

  if ($_SESSION["ipi_carrinho"]["buscar_balcao"]!="Balcão")
  {
    if ($_SESSION['ipi_carrinho']['frete_gratis_fidelidade']==true)
    {
          $valor_frete = "0";
          $comissao_frete = "0";
    }
    else
    {
      $sql_endereco = "SELECT * FROM ipi_clientes c INNER JOIN ipi_enderecos e ON (e.cod_clientes=c.cod_clientes) WHERE c.cod_clientes=" . $_SESSION['ipi_cliente']['codigo'] . " AND e.cod_enderecos=" . $cod_enderecos;
    //echo "<br>sql end: ".$sql_endereco;
    //die($_SESSION["ipi_carrinho"]["buscar_balcao"]);
      $res_endereco = mysql_query($sql_endereco);
      $num_endereco = mysql_num_rows($res_endereco);
      $obj_endereco = mysql_fetch_object($res_endereco);
    //die();
      $endereco_compacto = compactar_valores($obj_endereco->endereco.$obj_endereco->numero.$obj_endereco->complemento.$obj_endereco->bairro.$obj_endereco->cidade.$obj_endereco->estado.$obj_endereco->cep);

      $sql_busca_frete = "SELECT tx.valor_frete as frete,tx.valor_comissao_frete from ipi_cep c inner join ipi_taxa_frete tx on tx.cod_taxa_frete = c.cod_taxa_frete where c.cep_inicial <='".str_replace('-','',$obj_endereco->cep)."' and c.cep_final >='".str_replace('-','',$obj_endereco->cep)."'";
      $res_busca_frete = mysql_query($sql_busca_frete);
      $obj_busca_frete = mysql_fetch_object($res_busca_frete);
      $valor_frete = ($obj_busca_frete->frete ?  $obj_busca_frete->frete :'0');
      $comissao_frete = ($obj_busca_frete->valor_comissao_frete ?  $obj_busca_frete->valor_comissao_frete :'0');
      //echo "<br/>sql_frete".$sql_busca_frete;
     //die();
      $sql_bloqueio_endereco = "SELECT * FROM ipi_clientes_bloqueio cb WHERE cb.endereco_compacto='" . $endereco_compacto . "' AND cb.situacao='BLOQUEADO' AND cb.endereco_compacto<>''";
      $res_bloqueio_endereco = mysql_query($sql_bloqueio_endereco);
      $num_bloqueio_endereco = mysql_num_rows($res_bloqueio_endereco);
      if ($num_bloqueio_endereco>0)
      {
        $obj_bloqueio_endereco = mysql_fetch_object($res_bloqueio_endereco);
        $bloqueio_erro[] = "en";
        $sql_log_bloqueio = "INSERT INTO ipi_clientes_bloqueio_log (cod_clientes, cod_clientes_bloqueio, data_hora_bloqueio) VALUES( '".$_SESSION['ipi_cliente']['codigo']."', '".$obj_bloqueio_endereco->cod_clientes_bloqueio."', '".date("Y-m-d H:i:s")."');";
        $res_log_bloqueio = mysql_query($sql_log_bloqueio);
      }
      $num_bloqueio += $num_bloqueio_endereco;
      /*
      echo "<br />sql_bloqueio_endereco: ".$sql_bloqueio_endereco;
      echo "<br />sql_bloqueio_cartao: ".$sql_bloqueio_cartao;
      echo "<br />sql_bloqueio_cpf: ".$sql_bloqueio_cpf;
      echo "<br />sql_bloqueio_email: ".$sql_bloqueio_email;
      die();
      */
    }
  }
  else
  {
    $valor_frete = "0";
    $comissao_frete = "0";
  }

  if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
  {
    $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
  }
  else
  {
    
    $sql_cod_pizzarias = "SELECT cep FROM ipi_enderecos WHERE cod_enderecos = '$cod_enderecos'";
    $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
    $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
    $cep = $obj_cod_pizzarias->cep;
    $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep));
    $sql_pizzarias = "SELECT p.* FROM ipi_pizzarias p INNER JOIN ipi_cep c ON (p.cod_pizzarias=c.cod_pizzarias) WHERE c.cep_inicial<=" . $cep_limpo . " AND c.cep_final>=" . $cep_limpo . " GROUP BY p.cod_pizzarias";
    $res_pizzarias = mysql_query($sql_pizzarias);
    $obj_pizzarias = mysql_fetch_object($res_pizzarias);

    $cod_pizzarias = $obj_pizzarias->cod_pizzarias;
  }

  require("pub_req_fuso_horario1.php");

  $arr_funcionamento = array();
  $sql_buscar_horarios = "SELECT * FROM ipi_pizzarias_funcionamento WHERE cod_pizzarias = '".$cod_pizzarias."' AND dia_semana='".date('w')."' and CURTIME() < ADDTIME(horario_final, '00:05:01')";;//and horario_inicial >'02:00:00'
 
  $res_buscar_horarios = mysql_query($sql_buscar_horarios);
  $hor = -1;
  $num_horarios = mysql_num_rows($res_buscar_horarios);
  while($obj_buscar_horarios = mysql_fetch_object($res_buscar_horarios))
  {
    $hor++;
    /*$arr_funcionamento[$hor]["inicio"] = $obj_buscar_horarios->inicio;
    $arr_funcionamento[$hor]["fim"] = $obj_buscar_horarios->fim;*/
    
  }
  //echo $sql_cod_pizzarias;
  //echo "<br/>".$sql_pizzarias;
  if ( $num_horarios>0 )
  {
    $bloquear_pedido_por_horario = 0;
  }
  else
  {
    $bloquear_pedido_por_horario = 1;
  }

  if($bloquear_pedido_por_horario == 1)
  {
    $num_bloqueio += 60;
  }

  if ($num_bloqueio == 0)
  {
    //die("PASSO");
	  if ($forma_pagamento=="DINHEIRO")
	  {
		  $carrinho->pagamento_dinheiro($forma_pagamento, $troco, $cod_enderecos,$valor_frete,$comissao_frete, $cpf_nota_paulista);
		  $acao="compra_finalizada";
	  }

    else if ($forma_pagamento=="LEVAR A MAQUINA DE CARTAO" || $forma_pagamento=="BEBLUE - CRÉDITO" || $forma_pagamento=="BEBLUE - DÉBITO" || $forma_pagamento=="BEBLUE - SALDO BEBLUE"  || $forma_pagamento=="EKKO - CRÉDITO" || $forma_pagamento=="EKKO - DÉBITO" || $forma_pagamento=="EKKO - DINHEIRO")
    {

      $carrinho->pagamento_dinheiro($forma_pagamento, 0, $cod_enderecos,$valor_frete,$comissao_frete, $cpf_nota_paulista);
      $acao="compra_finalizada";
    }
    else if ($forma_pagamento=="MAQUINA DE CARTAO DEBITO")
    {
      $carrinho->pagamento_dinheiro($forma_pagamento, 0, $cod_enderecos,$valor_frete,$comissao_frete, $cpf_nota_paulista);
      $acao="compra_finalizada";
    }
    else if ( ($forma_pagamento=="AMERICAN") || ($forma_pagamento=="ALELO REFEIÇÃO") || ($forma_pagamento=="ELO CRÉDITO") || ($forma_pagamento=="ELO DÉBITO") || ($forma_pagamento=="MASTER CRÉDITO") || ($forma_pagamento=="MASTER DÉBITO") || ($forma_pagamento=="VISA CRÉDITO") || ($forma_pagamento=="VISA DÉBITO") || ($forma_pagamento=="TICKET RESTAURANTE") )
    {
      $carrinho->pagamento_dinheiro($forma_pagamento, 0, $cod_enderecos,$valor_frete,$comissao_frete, $cpf_nota_paulista);
      $acao="compra_finalizada";
    }
	  else if ($forma_pagamento=="VISANET1")
	  {
        require_once('geraTIDVBV.php');
    		$numero_pedido_visa = $_SESSION['ipi_cliente']['codigo'].date("dmYHi");
		    $carrinho->pagamento_cartao($forma_pagamento, $cod_enderecos,$valor_frete,$comissao_frete, $numero_pedido_visa, $cpf_nota_paulista);
        $total_visa = str_replace(",","", str_replace(".","", sprintf("%s",$carrinho->exibir_total()) ));   
		    ?>
          <html>
          <body>
	        <form name="visaVBV" id="visaVBV" method="POST" action="https://comercio.locaweb.com.br/comercio.comp">
	        <!-- Par\E2metros obrigat\F3rios -->
	        <input type="hidden" name="identificacao" value="2432533">
	        <input type="hidden" name="ambiente" value="producao">
	        <input type="hidden" name="modulo" value="VISAVBV">
	        <input type="hidden" name="operacao" value="Pagamento">
	        <input type="hidden" name="tid" value="<? echo GerarTid( '1023727614' , '1001'); ?>">
	        <input type="hidden" name="orderid" value="<? echo $numero_pedido_visa; ?>">
	        <input type="hidden" name="order" value="<? echo "C\F3digo do Cliente: ".$_SESSION['ipi_cliente']['codigo']."   Nome: ".$_SESSION['ipi_cliente']['nome']."   Email: ".$_SESSION['ipi_cliente']['email']."   CPF: ".$_SESSION['ipi_cliente']['cpf']; ?>">
	        <input type="hidden" name="price" value="<? echo $total_visa; ?>">
          <input type="hidden" name="damount" value="R$ <? $carrinho = new ipi_carrinho();    echo sprintf("%s",$carrinho->exibir_total()); ?>">
	        <!-- Par\E2metros adicionais -->
	        <input type="hidden" name="visa_antipopup" value="0">
	        <input type="hidden" name="authenttype" value="1">
	        <input type="hidden" name="free" value="">
	        <input type="hidden" name="language" value="pt">
	        <input type="hidden" name="bin" value="">
	        </form>
          <script>
          	document.visaVBV.submit();
          </script>
          </body>
          </html>
		    <?
		    die();
	  }
    else if ( ($forma_pagamento=="VISANET-CIELO") || ($forma_pagamento=="MASTERCARDNET-CIELO") || ($forma_pagamento=="AMEXNET-CIELO") || ($forma_pagamento=="ELONET-CIELO") || ($forma_pagamento=="DINERSNET-CIELO") || ($forma_pagamento=="DISCOVERNET-CIELO") || ($forma_pagamento=="AURANET-CIELO") || ($forma_pagamento=="JCBNET-CIELO") )
    {
        require_once("classe/cielo/include.php");

        $file_temp = 'log/cielo/debug_cielo.log';
        $texto = var_export($_POST,true);
        file_put_contents($file_temp,"\n \n \n \n POST1 \n".$texto, FILE_APPEND);
        $texto = var_export($_GET,true);
        file_put_contents($file_temp,"\n \n \n \n GET1 \n".$texto, FILE_APPEND);
        $texto = var_export($_SESSION,true);
        file_put_contents($file_temp,"\n \n \n \n SESSION1 \n".$texto, FILE_APPEND);        


        if ($bandeira_cc=="Visa") 
        {
          $bandeira = 'visa';
          $produto = '1';
        }
        else if($bandeira_cc=="Master Card") 
        {
          $bandeira = 'mastercard';
          $produto = '1';
        }
        else if($bandeira_cc=="American Express") 
        {
          $bandeira = 'amex';
          $produto = '1';
        }
        else if($bandeira_cc=="ELO") 
        {
          $bandeira = 'elo';
          $produto = '1';
        }
        else if($bandeira_cc=="Diners Club") 
        {
          $bandeira = 'diners';
          $produto = '1';
        }
        else if($bandeira_cc=="Discover") 
        {
          $bandeira = 'discover';
          $produto = '1';
        }
        else if($bandeira_cc=="Aura") 
        {
          $bandeira = 'aura';
          $produto = '1';
        }
        else if($bandeira_cc=="JCB") 
        {
          $bandeira = 'jcb';
          $produto = '1';
        }

        $conexao = conectabd();

        if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
        {
            $sql_pizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias=" . $_SESSION['ipi_carrinho']['cod_pizzarias'];
            $res_pizzarias = mysql_query($sql_pizzarias);
            $obj_pizzarias = mysql_fetch_object($res_pizzarias);

            $num_gateway_pagamentos = $obj_pizzarias->num_afiliacao_cartao;
            $num_chave_cielo = $obj_pizzarias->chave_cielo;
            $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
        }
        else
        {
            $sqlCliente = "SELECT * FROM ipi_clientes c INNER JOIN ipi_enderecos e ON (e.cod_clientes=c.cod_clientes) WHERE c.cod_clientes=" . $_SESSION['ipi_cliente']['codigo'] . " AND e.cod_enderecos='" . $cod_enderecos . "'";
            $resCliente = mysql_query($sqlCliente);
            $objCliente = mysql_fetch_object($resCliente);
            //echo "<br>:: ".$sqlCliente;
            
            $sql_pizzarias = "SELECT p.* FROM ipi_pizzarias p INNER JOIN ipi_cep c ON (p.cod_pizzarias=c.cod_pizzarias) WHERE c.cep_inicial<=" . str_replace(".", "", str_replace("-", "", $objCliente->cep)) . " AND c.cep_final>=" . str_replace(".", "", str_replace("-", "", $objCliente->cep)) . " GROUP BY p.cod_pizzarias";
            $res_pizzarias = mysql_query($sql_pizzarias);
            $obj_pizzarias = mysql_fetch_object($res_pizzarias);

            $num_gateway_pagamentos = $obj_pizzarias->num_afiliacao_cartao;
            $num_chave_cielo = $obj_pizzarias->chave_cielo;
            $cod_pizzarias = $obj_pizzarias->cod_pizzarias;
            //echo "<br>::: ".$sqlPizzaria;
        }

        if(!isset($valor_frete))
        {
          $valor_frete="0";
        }

        $numero_pedido_visa = $_SESSION['ipi_cliente']['codigo'].date("dmYHi");
        $carrinho->pagamento_cartao($forma_pagamento, $cod_enderecos,$valor_frete,$comissao_frete, $numero_pedido_visa, $cpf_nota_paulista);

        // Comentado pra simular erro online
        $texto = var_export($_POST,true);
        file_put_contents($file_temp,"\n \n \n \n POST2 \n".$texto, FILE_APPEND);
        $texto = var_export($_GET,true);
        file_put_contents($file_temp,"\n \n \n \n GET2 \n".$texto, FILE_APPEND);
        $texto = var_export($_SESSION,true);
        file_put_contents($file_temp,"\n \n \n \n SESSION2 \n".$texto, FILE_APPEND);        

        $total_visa = "";

        $texto = $total_visa;
        file_put_contents($file_temp,"\n \n \n \n TOTAL VISA ANTES \n".$texto, FILE_APPEND);

        $total_visa = str_replace(",","", str_replace(".","", sprintf("%s",$carrinho->exibir_total() ) ) );   
        //$total_visa = ""; // Qdo publicar comentar esta linha, com ela provoca o mesmo erro online

        $texto = $total_visa;
        file_put_contents($file_temp,"\n \n \n \n TOTAL VISA DEPOIS \n".$texto, FILE_APPEND);

        // - dados da Loja
        $numero_afiliacao = $num_gateway_pagamentos;
        $numero_chave_cielo = $num_chave_cielo;

//  echo "<br>numero_afiliacao: ".$numero_afiliacao;
//  echo "<br>numero_chave_cielo: ".$numero_chave_cielo;
//  die();

        // - dados do cartao
        $nome_portador_cartao = $txt_nc;
        $numero_cartao = $txt_cc;
        $validade_cartao = $txt_dva.$txt_dvm;
        $indicador_cartao = '1';
        $codigo_seguranca_cartao = $txt_cs;

        // - dados do pedido
        $valor = $total_visa;
        $pedido = $numero_pedido_visa;
        $moeda = '986';
        $data_hora_pedido = date("Y-m-d H:i:s:u (T)");
        $descricao_pedido = 'Pedido de Pizza';  // Completar aqui!
        $idioma = 'PT';

        // - dados do pagamento
        $parcelas = '1';
        $forma_pagamento = '1';
        $autorizar = '3';
        $capturar = 'false';
        $campo_livre = '';

        $tentar_autenticar = "nao";
        $bin = '455187';

	      $Pedido = new Pedido();
	
	      // Lê dados do $_POST
	      $Pedido->formaPagamentoBandeira = $bandeira; 
	      $Pedido->formaPagamentoProduto = $forma_pagamento;
	      $Pedido->formaPagamentoParcelas = $parcelas;
	
	      $Pedido->dadosEcNumero = $numero_afiliacao;
	      $Pedido->dadosEcChave = $numero_chave_cielo;
	
	      $Pedido->capturar = $capturar;	
	      $Pedido->autorizar = $autorizar;
	
	      $Pedido->dadosPortadorNumero = $numero_cartao;
	      $Pedido->dadosPortadorVal = $validade_cartao;
	      // Verifica se Código de Segurança foi informado e ajusta o indicador corretamente
	      if ($codigo_seguranca_cartao == null || $codigo_seguranca_cartao == "")
	      {
		      $Pedido->dadosPortadorInd = "0";
	      }
	      else if ($Pedido->formaPagamentoBandeira == "mastercard")
	      {
		      $Pedido->dadosPortadorInd = "1";
	      }
	      else 
	      {
		      $Pedido->dadosPortadorInd = "1";
	      }
	      $Pedido->dadosPortadorCodSeg = $codigo_seguranca_cartao;
	
	      $Pedido->dadosPedidoNumero = $numero_pedido_visa; 
	      $Pedido->dadosPedidoValor = $total_visa;
	
	      $Pedido->urlRetorno = ReturnURL();
	//echo "<br>aki-3 ".ReturnURL();

	      // ENVIA REQUISIÇÃO SITE CIELO
	      if($tentar_autenticar == "sim") // TRANSAÇÃO
	      {
        	//echo "<br>aki-5 ";
		      $objResposta = $Pedido->RequisicaoTransacao(true);
	      }
	      else // AUTORIZAÇÃO DIRETA 
	      {
        	//echo "<br>aki-6 ";
		      $objResposta = $Pedido->RequisicaoTid();
        	//echo "<br>aki-7 ";
		
		      $Pedido->tid = $objResposta->tid;
        	//echo "<br>aki-8 ";
		      $Pedido->pan = $objResposta->pan;
        	//echo "<br>aki-9 ";
		      $Pedido->status = $objResposta->status;
        	//echo "<br>aki-10 ";
		
		      $objResposta = $Pedido->RequisicaoAutorizacaoPortador();
	      }
	      //echo "<br>aki-4";
		
	      $Pedido->tid = $objResposta->tid;
	      $Pedido->pan = $objResposta->pan;
	      $Pedido->status = $objResposta->status;
	
	      $urlAutenticacao = "url-autenticacao";
	      $Pedido->urlAutenticacao = $objResposta->$urlAutenticacao;

	      //echo "<br>aki-2";

	      // Serializa Pedido e guarda na SESSION
	      $StrPedido = $Pedido->ToString();
      	$_SESSION["pedidos"]->append($StrPedido);


        $PedidoConsulta = new Pedido();
	      $ultimoPedido = $_SESSION["pedidos"]->count();
        //echo "<Br>Y7: ".$_SESSION["pedidos"]->count();
	      $ultimoPedido -= 1;
      	$PedidoConsulta->FromString($_SESSION["pedidos"]->offsetGet($ultimoPedido));
      	$objResposta = $PedidoConsulta->RequisicaoConsulta();

/*      
				echo "<Br>1: ".$PedidoConsulta->dadosPedidoNumero;
				echo "<Br>3: ".$PedidoConsulta->tid;
				echo "<Br>4: ".$PedidoConsulta->getStatus();
        echo "<Br>5: ".htmlentities($objResposta->asXML());
				echo "<Br>6: ".$objResposta->status;
				echo "<Br>7: ".$PedidoConsulta->status;
        die("TesteXxX");
*/

      	if($objResposta->status == '4' || $objResposta->status == '6')  // 4 e 6 são aprovados consultar exemplo loja php veio junto com manual da cielo
  	    {

          $conexao = conectar_bd();

          foreach ($objResposta as $resposta => $valor)
          {
            if ( count($valor)>0 )
            {
              foreach ($valor as $subresposta => $subvalor)
              {
        				//echo "<Br><Br>Y: ".$subresposta." - ".utf8_decode($subvalor);
                $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$numero_pedido_visa."','".$subresposta."','".utf8_decode($subvalor)."', NOW() )";
                $res_ret_visa =  mysql_query($sql_ret_visa);
         				//echo "<Br>SQL: ".$sql_ret_visa;
              }
            }
            else
            {
      				//echo "<Br><Br>X: ".$resposta." - ".$valor;
              $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$numero_pedido_visa."','".$resposta."','".utf8_decode($valor)."', NOW() )";
              $res_ret_visa =  mysql_query($sql_ret_visa);
       				//echo "<Br>SQL: ".$sql_ret_visa;
            }
          }
          $acao = "visa_ok";
        }
        else
        {

          $conexao = conectar_bd();
          $retorno_lr_autorizacao = "";
          $retorno_tid = "";
          $retorno_mensagem_autorizacao = "";
          foreach ($objResposta as $resposta => $valor)
          {
            if ( count($valor)>0 )
            {
              foreach ($valor as $subresposta => $subvalor)
              {
        				//echo "<Br><Br>Y: ".$subresposta." - ".utf8_decode($subvalor);
                if ($subresposta == "mensagem")
                {
                  $retorno_mensagem_autorizacao = utf8_decode($subvalor);
                }
                else if ($subresposta == "lr")
                {
                  $retorno_lr_autorizacao = utf8_decode($subvalor);
                }
              }
            }
            else
            {
              if ($resposta == "tid")
              {
                $retorno_tid = utf8_decode($valor);
              }
      				//echo "<Br><Br>X: ".$resposta." - ".$valor;
            }
          }

          $_SESSION['ipi_carrinho']['visa_temp']['erro'] = $retorno_lr_autorizacao;
          $_SESSION['ipi_carrinho']['visa_temp']['tid_erro'] = $retorno_tid;
          $_SESSION['ipi_carrinho']['visa_temp']['origem_erro'] = $retorno_mensagem_autorizacao;

          $acao = "visa_erro";
        }

        /*
        echo "<br>Ses lr: ".$_SESSION['ipi_carrinho']['visa_temp']['erro'];
        echo "<br>Ses tid: ".$_SESSION['ipi_carrinho']['visa_temp']['tid_erro'];
        echo "<br>Ses mensagem_autorizacao: ".$_SESSION['ipi_carrinho']['visa_temp']['origem_erro'];

        echo "<br>StrPedido: ".$StrPedido;
        echo "<br>acao: ".$acao;

		    die("Serah!?");
        echo "<pre>";
        print_r($_SESSION["pedidos"]);
        echo "</pre>";
        */

	  }
    else if ( ($forma_pagamento=="VISANET") || ($forma_pagamento=="MASTERCARDNET") )
    {

        $numero_pedido_visa = $_SESSION['ipi_cliente']['codigo'].date("dmYHi");
        $carrinho->pagamento_cartao($forma_pagamento, $cod_enderecos,$valor_frete,$comissao_frete, $numero_pedido_visa, $cpf_nota_paulista);
        $total_visa = str_replace(",","", str_replace(".","", sprintf("%s",$carrinho->exibir_total() ) ) );   

        $conexao = conectabd();

        if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
        {
            $sql_pizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias=" . $_SESSION['ipi_carrinho']['cod_pizzarias'];
            $res_pizzarias = mysql_query($sql_pizzarias);
            $obj_pizzarias = mysql_fetch_object($res_pizzarias);

            $num_gateway_pagamentos = $obj_pizzarias->num_gateway_pagamento;
            $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
        }
        else
        {
            $sqlCliente = "SELECT * FROM ipi_clientes c INNER JOIN ipi_enderecos e ON (e.cod_clientes=c.cod_clientes) WHERE c.cod_clientes=" . $_SESSION['ipi_cliente']['codigo'] . " AND e.cod_enderecos=" . $_SESSION['ipi_carrinho']['pagamento']['cod_enderecos'];
            $resCliente = mysql_query($sqlCliente);
            $objCliente = mysql_fetch_object($resCliente);
            //echo "<br>:: ".$sqlCliente;
            
            $sql_pizzarias = "SELECT p.* FROM ipi_pizzarias p INNER JOIN ipi_cep c ON (p.cod_pizzarias=c.cod_pizzarias) WHERE c.cep_inicial<=" . str_replace(".", "", str_replace("-", "", $objCliente->cep)) . " AND c.cep_final>=" . str_replace(".", "", str_replace("-", "", $objCliente->cep)) . " GROUP BY p.cod_pizzarias";
            $res_pizzarias = mysql_query($sql_pizzarias);
            $obj_pizzarias = mysql_fetch_object($res_pizzarias);

            $cod_pizzarias = $obj_pizzarias->cod_pizzarias;
            $num_gateway_pagamentos = $obj_pizzarias->num_gateway_pagamento;
            //echo "<br>::: ".$sqlPizzaria;
        }



        // ############ Cielo Commerce ###############
        ini_set('allow_url_fopen', 1); // Ativa a diretiva 'allow_url_fopen'
        function getURL($txt_nc, $txt_cc, $txt_cs, $txt_dva, $txt_dvm, $total_visa, $numero_pedido_visa, $bandeira_forma_pagamento, $num_gateway_pagamentos)
        {
            // Dados obtidos da loja para a transa\E7\E3o

            // - dados do processo
            $identificacao = $num_gateway_pagamentos;
            $modulo = 'CIELO';
            $operacao = 'Autorizacao-Direta';
            $ambiente = 'PRODUCAO';

            // - dados do cart\E3o
            $nome_portador_cartao = $txt_nc;
            $numero_cartao = $txt_cc;
            $validade_cartao = $txt_dva.$txt_dvm;
            $indicador_cartao = '1';
            $codigo_seguranca_cartao = $txt_cs;

            // - dados do pedido
            $idioma = 'PT';
            $valor = $total_visa;
            $pedido = $numero_pedido_visa;
            $descricao = "C\F3digo do Cliente: ".$_SESSION['ipi_cliente']['codigo']."   Nome: ".$_SESSION['ipi_cliente']['nome']."   Email: ".$_SESSION['ipi_cliente']['email']."   CPF: ".$_SESSION['ipi_cliente']['cpf']."    Valor: ".$total_visa;

            // - dados do pagamento
            if ($bandeira_forma_pagamento=="VISANET") 
            {
                $bandeira = 'visa';
            }
            else if($bandeira_forma_pagamento=="MASTERCARDNET") 
            {
                $bandeira = 'mastercard';
            }
            $forma_pagamento = '1';
            $parcelas = '1';
            $autorizar = '2';
            $capturar = 'false';

            // - dados adicionais
            $campo_livre = '';

            // Monta a vari\E1vel com os dados para postagem
            $request = 'identificacao=' . $identificacao;
            $request .= '&modulo=' . $modulo;
            $request .= '&operacao=' . $operacao;
            $request .= '&ambiente=' . $ambiente;

            $request .= '&nome_portador_cartao=' . $nome_portador_cartao;
            $request .= '&numero_cartao=' . $numero_cartao;
            $request .= '&validade_cartao=' . $validade_cartao;
            $request .= '&indicador_cartao=' . $indicador_cartao;
            $request .= '&codigo_seguranca_cartao=' . $codigo_seguranca_cartao;

            $request .= '&idioma=' . $idioma;
            $request .= '&valor=' . $valor;
            $request .= '&pedido=' . $pedido;
            $request .= '&descricao=' . $descricao;

            $request .= '&bandeira=' . $bandeira;
            $request .= '&forma_pagamento=' . $forma_pagamento;
            $request .= '&parcelas=' . $parcelas;
            $request .= '&autorizar=' . $autorizar;
            $request .= '&capturar=' . $capturar;

            $request .= '&campo_livre=' . $campo_livre;

  //          echo "<br>request: ".$request;
  //          echo "<pre>";
  //          print_r($_SESSION);
  //          echo "</pre>";
  //          die();

            // Faz a postagem para a Cielo
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://comercio.locaweb.com.br/comercio.comp');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            if($errno = curl_errno($ch)) 
            {
                trigger_error("cURL error!!! Rotina Pagamento Cartão de Crédito, numero do erro cUrl: ({$errno})", E_USER_ERROR);
            }            
            curl_close($ch);
            return $response;
        }
        
        $XMLtransacao = GetURL($txt_nc, $txt_cc, $txt_cs, $txt_dva, $txt_dvm, $total_visa, $numero_pedido_visa, $forma_pagamento, $num_gateway_pagamentos);

        $file_temp = 'log/cielo/debug_cielo.log';
        $texto = var_export($XMLtransacao,true);
        file_put_contents($file_temp,"\n \n \n \n XML2 \n".$texto, FILE_APPEND);

        if ( ($XMLtransacao != "") && ($response !== false) )
        {
          $retorno_lr_autorizacao = -1; //Iniciada a variavel com -1, pois o valor default da aprovação cielo sem erro é "0" e o gateway esta retornando algumas coisas fora do padrão tipo (<font face="Arial" size=2><p>msxml3.dll</font>) e a variável está assumindo zero e passando como aprovado!
          
          // Carrega o XML
          $objDom = new DomDocument();
          $loadDom = $objDom->loadXML($XMLtransacao);

          $nodeErro = $objDom->getElementsByTagName('erro')->item(0);
          if ($nodeErro != '') 
          {
              $nodeCodigoErro = $nodeErro->getElementsByTagName('codigo')->item(0);
              $retorno_codigo_erro = $nodeCodigoErro->nodeValue;

              $nodeMensagemErro = $nodeErro->getElementsByTagName('mensagem')->item(0);
              $retorno_mensagem_erro = $nodeMensagemErro->nodeValue;
          }

          $nodeTransacao = $objDom->getElementsByTagName('transacao')->item(0);
          if ($nodeTransacao != '') 
          {
              $nodeTID = $nodeTransacao->getElementsByTagName('tid')->item(0);
              $retorno_tid = $nodeTID->nodeValue;

              $nodePAN = $nodeTransacao->getElementsByTagName('pan')->item(0);
              $retorno_pan = $nodePAN->nodeValue;

              $nodeDadosPedido = $nodeTransacao->getElementsByTagName('dados-pedido')->item(0);
              if ($nodeTransacao != '') 
              {
                  $nodeNumero = $nodeDadosPedido->getElementsByTagName('numero')->item(0);
                  $retorno_pedido = $nodeNumero->nodeValue;

                  $nodeValor = $nodeDadosPedido->getElementsByTagName('valor')->item(0);
                  $retorno_valor = $nodeValor->nodeValue;

                  $nodeMoeda = $nodeDadosPedido->getElementsByTagName('moeda')->item(0);
                  $retorno_moeda = $nodeMoeda->nodeValue;

                  $nodeDataHora = $nodeDadosPedido->getElementsByTagName('data-hora')->item(0);
                  $retorno_data_hora = $nodeDataHora->nodeValue;

                  $nodeDescricao = $nodeDadosPedido->getElementsByTagName('descricao')->item(0);
                  $retorno_descricao = utf8_decode(utf8_decode($nodeDescricao->nodeValue));

                  $nodeIdioma = $nodeDadosPedido->getElementsByTagName('idioma')->item(0);
                  $retorno_idioma = $nodeIdioma->nodeValue;
              }

              $nodeFormaPagamento = $nodeTransacao->getElementsByTagName('forma-pagamento')->item(0);
              if ($nodeFormaPagamento != '') 
              {
                  $nodeBandeira = $nodeFormaPagamento->getElementsByTagName('bandeira')->item(0);
                  $retorno_bandeira = $nodeBandeira->nodeValue;

                  $nodeProduto = $nodeFormaPagamento->getElementsByTagName('produto')->item(0);
                  $retorno_produto = $nodeProduto->nodeValue;

                  $nodeParcelas = $nodeFormaPagamento->getElementsByTagName('parcelas')->item(0);
                  $retorno_parcelas = $nodeParcelas->nodeValue;
              }

              $nodeStatus = $nodeTransacao->getElementsByTagName('status')->item(0);
              $retorno_status = $nodeStatus->nodeValue;

              $nodeAutenticacao = $nodeTransacao->getElementsByTagName('autenticacao')->item(0);
              if ($nodeAutenticacao != '') 
              {
                  $nodeCodigoAutenticacao = $nodeAutenticacao->getElementsByTagName('codigo')->item(0);
                  $retorno_codigo_autenticacao = $nodeCodigoAutenticacao->nodeValue;

                  $nodeMensagemAutenticacao = $nodeAutenticacao->getElementsByTagName('mensagem')->item(0);
                  $retorno_mensagem_autenticacao = $nodeMensagemAutenticacao->nodeValue;

                  $nodeDataHoraAutenticacao = $nodeAutenticacao->getElementsByTagName('data-hora')->item(0);
                  $retorno_data_hora_autenticacao = $nodeDataHoraAutenticacao->nodeValue;

                  $nodeValorAutenticacao = $nodeAutenticacao->getElementsByTagName('valor')->item(0);
                  $retorno_valor_autenticacao = $nodeValorAutenticacao->nodeValue;

                  $nodeECIAutenticacao = $nodeAutenticacao->getElementsByTagName('eci')->item(0);
                  $retorno_eci_autenticacao = $nodeECIAutenticacao->nodeValue;
              }

              $nodeAutorizacao = $nodeTransacao->getElementsByTagName('autorizacao')->item(0);
              if ($nodeAutorizacao != '') 
              {
                  $nodeCodigoAutorizacao = $nodeAutorizacao->getElementsByTagName('codigo')->item(0);
                  $retorno_codigo_autorizacao = $nodeCodigoAutorizacao->nodeValue;

                  $nodeMensagemAutorizacao = $nodeAutorizacao->getElementsByTagName('mensagem')->item(0);
                  $retorno_mensagem_autorizacao = utf8_decode(utf8_decode($nodeMensagemAutorizacao->nodeValue));

                  $nodeDataHoraAutorizacao = $nodeAutorizacao->getElementsByTagName('data-hora')->item(0);
                  $retorno_data_hora_autorizacao = $nodeDataHoraAutorizacao->nodeValue;

                  $nodeValorAutorizacao = $nodeAutorizacao->getElementsByTagName('valor')->item(0);
                  $retorno_valor_autorizacao = $nodeValorAutorizacao->nodeValue;

                  $nodeLRAutorizacao = $nodeAutorizacao->getElementsByTagName('lr')->item(0);
                  $retorno_lr_autorizacao = (int) $nodeLRAutorizacao->nodeValue;

                  $nodeARPAutorizacao = $nodeAutorizacao->getElementsByTagName('arp')->item(0);
                  $retorno_arp_autorizacao = $nodeARPAutorizacao->nodeValue;
              }

              $nodeURLAutenticacao = $nodeTransacao->getElementsByTagName('url-autenticacao')->item(0);
              $retorno_url_autenticacao = $nodeURLAutenticacao->nodeValue;
          }

          /* Tempor\E1rio para localizaer o erro do cart\E3o, assim que corrigir apagar */
          $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$numero_pedido_visa."','TEMP_retorno_codigo_erro','".$retorno_codigo_erro."', NOW() )";
          $res_ret_visa =  mysql_query($sql_ret_visa);

          $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$numero_pedido_visa."','TEMP_retorno_lr_autorizacao','".$retorno_lr_autorizacao."', NOW() )";
          $res_ret_visa =  mysql_query($sql_ret_visa);

          $file_temp = 'log/cielo/debug_cielo.log';
          $texto = var_export($_POST,true);
          file_put_contents($file_temp,"\n \n \n \n POST2 \n".$texto, FILE_APPEND);
          $texto = var_export($_GET,true);
          file_put_contents($file_temp,"\n \n \n \n GET2 \n".$texto, FILE_APPEND);
          $texto = var_export($_SESSION,true);
          file_put_contents($file_temp,"\n \n \n \n SESSION2 \n".$texto, FILE_APPEND);        
  
          file_put_contents($file_temp,"\n \n  SQL  PedVisa: ".$numero_pedido_visa." \n".$sql_ret_visa, FILE_APPEND);

          $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$numero_pedido_visa."','TEMP_retorno_mensagem_autorizacao','".$retorno_mensagem_autorizacao."', NOW() )";
          $res_ret_visa =  mysql_query($sql_ret_visa);

          $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$numero_pedido_visa."','TEMP_retorno_tid','".$retorno_tid."', NOW() )";
          $res_ret_visa =  mysql_query($sql_ret_visa);

          $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$numero_pedido_visa."','TEMP_retorno_mensagem_erro','".$retorno_mensagem_erro."', NOW() )";
          $res_ret_visa =  mysql_query($sql_ret_visa);
          /* Tempor\E1rio para localizaer o erro do cart\E3o, assim que corrigir apagar */



          // Se n\E3o ocorreu erro exibe par\E2metros
          if ($retorno_codigo_erro == '') 
          {

              if ($retorno_lr_autorizacao==0)
              {
                  file_put_contents($file_temp,"\n  GatewayLocaweb, Aprovado  \n", FILE_APPEND);

                  /*
                  echo '<b> TRANSA\C7\C3O </b><br />';
                  echo '<b>C\F3digo de identifica\E7\E3o do pedido (TID): </b>' . $retorno_tid . '<br />';
                  echo '<b>PAN do pedido (pan): </b>' . $retorno_pan . '<br />';

                  echo '<b>N\FAmero do pedido (numero): </b>' . $retorno_pedido . '<br />';
                  echo '<b>Valor do pedido (valor): </b>' . $retorno_valor . '<br />';
                  echo '<b>Moeda do pedido (moeda): </b>' . $retorno_moeda . '<br />';
                  echo '<b>Data e hora do pedido (data-hora): </b>' . $retorno_data_hora . '<br />';
                  echo '<b>Descri\E7\E3o do pedido (descricao): </b>' . $retorno_descricao . '<br />';
                  echo '<b>Idioma do pedido (idioma): </b>' . $retorno_idioma . '<br />';

                  echo '<b>Bandeira (bandeira): </b>' . $retorno_bandeira . '<br />';
                  echo '<b>Forma de pagamento (produto): </b>' . $retorno_produto . '<br />';
                  echo '<b>N\FAmero de parcelas (parcelas): </b>' . $retorno_parcelas . '<br />';

                  echo '<b>Status do pedido (status): </b>' . $retorno_status . '<br />';

                  echo '<b>URL para autentica\E7\E3o (url-autenticacao): </b>' . $retorno_url_autenticacao . '<br /><br />';

                  echo '<b> AUTENTICA\C7\C3O </b><br />';
                  echo '<b>C\F3digo da autentica\E7\E3o (codigo): </b>' . $retorno_codigo_autenticacao . '<br />';
                  echo '<b>Mensagem da autentica\E7\E3o (mensagem): </b>' . $retorno_mensagem_autenticacao . '<br />';
                  echo '<b>Data e hora da autentica\E7\E3o (data-hora): </b>' . $retorno_data_hora_autenticacao . '<br />';
                  echo '<b>Valor da autentica\E7\E3o (valor): </b>' . $retorno_valor_autenticacao . '<br />';
                  echo '<b>ECI da autentica\E7\E3o (eci): </b>' . $retorno_eci_autenticacao . '<br /><br />';

                  echo '<b> AUTORIZA\C7\C3O </b><br />';
                  echo '<b>C\F3digo da autoriza\E7\E3o (codigo): </b>' . $retorno_codigo_autorizacao . '<br />';
                  echo '<b>Mensagem da autoriza\E7\E3o (mensagem): </b>' . $retorno_mensagem_autorizacao . '<br />';
                  echo '<b>Data e hora da autoriza\E7\E3o (data-hora): </b>' . $retorno_data_hora_autorizacao . '<br />';
                  echo '<b>Valor da autoriza\E7\E3o (valor): </b>' . $retorno_valor_autorizacao . '<br />';
                  echo '<b>LR da autoriza\E7\E3o (LR): </b>' . $retorno_lr_autorizacao . '<br />';
                  echo '<b>ARP da autoriza\E7\E3o (ARP): </b>' . $retorno_arp_autorizacao . '<br /><br />';
                  */

                  if ($forma_pagamento=="VISANET") 
                      $bandeira = 'visa';
                  else if($forma_pagamento=="MASTERCARDNET") 
                      $bandeira = 'mastercard';

                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','bandeira','".$bandeira."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);

                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','digitos_cc','".$txt_cc."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);

                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_tid','".$retorno_tid."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_pan','".$retorno_pan."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_pedido','".$retorno_pedido."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_valor','".$retorno_valor."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_moeda','".$retorno_moeda."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_data_hora','".$retorno_data_hora."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_descricao','".$retorno_descricao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_idioma','".$retorno_idioma."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_bandeira','".$retorno_bandeira."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_produto','".$retorno_produto."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_parcelas','".$retorno_parcelas."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_status','".$retorno_status."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_codigo_autenticacao','".$retorno_codigo_autenticacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_mensagem_autenticacao','".$retorno_mensagem_autenticacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_data_hora_autenticacao','".$retorno_data_hora_autenticacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_valor_autenticacao','".$retorno_valor_autenticacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_eci_autenticacao','".$retorno_eci_autenticacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_codigo_autorizacao','".$retorno_codigo_autorizacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_mensagem_autorizacao','".$retorno_mensagem_autorizacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_data_hora_autorizacao','".$retorno_data_hora_autorizacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_valor_autorizacao','".$retorno_valor_autorizacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_lr_autorizacao','".$retorno_lr_autorizacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);
                  $sql_ret_visa = "INSERT INTO ipi_pedidos_pag_temp (cod_pedido_operadora, chave, valor, data_hora_gravacao) VALUES ('".$retorno_pedido."','retorno_arp_autorizacao','".$retorno_arp_autorizacao."', NOW() )";
                  $res_ret_visa =  mysql_query($sql_ret_visa);

                  if ( isset($_SESSION['ipi_carrinho']['visa_temp']['erro']))
                  {
                      unset($_SESSION['ipi_carrinho']['visa_temp']);
                  }
                  $acao = "visa_ok";
              }
              else 
              {
                  $_SESSION['ipi_carrinho']['visa_temp']['erro'] = $retorno_lr_autorizacao;
                  $_SESSION['ipi_carrinho']['visa_temp']['tid_erro'] = $retorno_tid;
                  $_SESSION['ipi_carrinho']['visa_temp']['origem_erro'] = $retorno_mensagem_autorizacao;
                  $acao = "visa_erro";
              }
          }
          else 
          {
              $_SESSION['ipi_carrinho']['visa_temp']['erro'] = $retorno_codigo_erro;
              $_SESSION['ipi_carrinho']['visa_temp']['origem_erro'] = utf8_decode($retorno_mensagem_erro);
              $acao = "visa_erro";
          }
        }
        else 
        {
            $_SESSION['ipi_carrinho']['visa_temp']['erro'] = "-1";
            $_SESSION['ipi_carrinho']['visa_temp']['origem_erro'] = "XML resposta vazio!";
            $acao = "visa_erro";
        }
        
        file_put_contents($file_temp,"\n  Retorno Visa: ".$acao."  \n", FILE_APPEND);
        // ############ Fim Cielo Commerce ###############

	  }  // Fim VISANET
  }
  else
  {
    if($bloquear_pedido_por_horario==1)
    {
      $acao = "fora_horario";
    }
    else
    {
      $acao = "cliente_bloqueado";
      $erros = implode(",", $bloqueio_erro);
    }

  }

}




if ($acao=="adicionar_pizza_combo")
{
    $cod_adicionais = validaVarPost('gergelim');
    $cod_tipo_massa = validaVarPost('tipo_massa');
    $cod_bordas = validaVarPost('borda');
    $quant_fracao = validaVarPost('num_sabores');
    $cod_tamanhos = validaVarPost('tam_pizza');
	  $cod_opcoes_corte = validaVarPost('corte');
    
    $indice_atual_combo = validaVarPost('indice_atual_combo');
    $id_combo = validaVarPost('id_combo');
    
   //Trava para evitar que a mesma pizza seja adicionada duas vezes 
  if($_SESSION['ipi_carrinho']['combo']['produtos'][$indice_atual_combo]['foi_pedido']=='N')
  {
      // Confirmar que essa pizza do combo foi pedida na sess\E3o, para continuar da pr\F3xima pizza
      $_SESSION['ipi_carrinho']['combo']['produtos'][$indice_atual_combo]['foi_pedido']='S';
      
      $num_fracao = validaVarPost('num_fracao');
      $sabor1_pizza = validaVarPost('sabor1_pizza');
      $sabor2_pizza = validaVarPost('sabor2_pizza');
      $sabor3_pizza = validaVarPost('sabor3_pizza');
      $sabor4_pizza = validaVarPost('sabor4_pizza');
      
      $ingredientes1 = validaVarPost('ingredientes1');
      $ingredientes2 = validaVarPost('ingredientes2');
      $ingredientes3 = validaVarPost('ingredientes3');
      $ingredientes4 = validaVarPost('ingredientes4');
                                                 
      $ingredientes_adicionais1 = validaVarPost('ingredientes_adicionais1');
      $ingredientes_adicionais2 = validaVarPost('ingredientes_adicionais2');
      $ingredientes_adicionais3 = validaVarPost('ingredientes_adicionais3');
      $ingredientes_adicionais4 = validaVarPost('ingredientes_adicionais4');
      
      $indice_pizza = $carrinho->adicionar_pizza($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $quant_fracao, $cod_opcoes_corte, $vc, $nc, $id_combo);
      
      //Encontrar o id_sessao do m\E9todo de exclus\E3o
      //$id_sessao_pai = $_SESSION['ipi_carrinho']['pedido'][$indice_pizza]['pizza_id_sessao'];
      
      
     $conexao = conectar_bd();
    $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
    $cod_pizzarias = 0;
    if($cep_visitante)
    {
      //echo "<br />1";
      $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));

      $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo";
      //echo $sql_cod_pizzarias;
      $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
      $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
      $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
    }

    if ($sabor1_pizza!="0")
    {
      $indice_fracao1 = $carrinho->adicionar_fracao($indice_pizza, $sabor1_pizza, $num_fracao[0]);
      $num_ingredientes = (is_array($ingredientes1) ? count($ingredientes1) : 0);  
      $conexao = conectar_bd();

      $pizza_semana = 0;
      $sql_pizza_semana1 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor1_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
      //echo $sql_pizza_semana1;
      $res_pizza_semana1 = mysql_query($sql_pizza_semana1);   
      $res_aux = mysql_query($sql_pizza_semana1);
      $obj_aux = mysql_fetch_object($res_aux);
      $pizza_semana = $obj_aux->pizza_semana;
      if(($pizza_semana == '1') || ($num_ingredientes == 0))
      {
        while ($obj_pizza_semana1 = mysql_fetch_object($res_pizza_semana1))
        {
          $tipo_ingrediente = 'NORMAL';
          $cod_ingredientes_troca = '';
          $cod_ingredientes = $obj_pizza_semana1->cod_ingredientes;
          $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
          //echo $indice_pizza.' '; 
          //echo $indice_fracao1.' '; 
          //echo $cod_ingredientes.' '; 
          //echo $tipo_ingrediente_bool.' '; 
          //echo $cod_ingredientes_troca.'<br />';
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
        } 
        //die();
      }
      else
      {
        for ($a=0; $a<$num_ingredientes; $a++)
        {
          if ($ingredientes1[$a]!="")
          {
                    list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca ) = split("###", $ingredientes1[$a]);
                    $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
              $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
              
              //echo "$tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca<br>";
          }
        }
      }
      
      
      $num_ingredientes_adicionais = count ($ingredientes_adicionais1);
      for ($a=0; $a<$num_ingredientes_adicionais; $a++)
      {
        if ($ingredientes_adicionais1[$a]!="")
        {
                  list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais1[$a]);
                  $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
            $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes_adicionais, false, $tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
        }
      }
    }
    
    if ($sabor2_pizza!="0")
    {
      $indice_fracao2 = $carrinho->adicionar_fracao($indice_pizza, $sabor2_pizza, $num_fracao[1]);
      $num_ingredientes = (is_array($ingredientes2) ? count($ingredientes2) : 0);
      
      $conexao = conectar_bd();
      $pizza_semana = 0;
      $sql_pizza_semana2 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor2_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
      $res_pizza_semana2 = mysql_query($sql_pizza_semana2);   
      $sql_pizza_semana2 .= " GROUP BY ip.cod_pizzas";
      $res_aux = mysql_query($sql_pizza_semana2);
      $obj_aux = mysql_fetch_object($res_aux);
      $pizza_semana = $obj_aux->pizza_semana;
      if(($pizza_semana == '1') || ($num_ingredientes == 0))
      {
        while ($obj_pizza_semana2 = mysql_fetch_object($res_pizza_semana2))
        {
          $tipo_ingrediente = 'NORMAL';
          $cod_ingredientes_troca = '';
          $cod_ingredientes = $obj_pizza_semana2->cod_ingredientes;
          $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
        } 
      }
      else
      {
        for ($a=0; $a<$num_ingredientes; $a++)
        {
          if ($ingredientes2[$a]!="")
          {
              list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca) = split("###", $ingredientes2[$a]);
              $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
              $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
          }
        }
      }
      
      
      $num_ingredientes_adicionais = count ($ingredientes_adicionais2);
      for ($a=0; $a<$num_ingredientes_adicionais; $a++)
      {
        if ($ingredientes_adicionais2[$a]!="")
        {
            list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais2[$a]);
            $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
            $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes_adicionais, false, $tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
        }
      }
    }
    
    if ($sabor3_pizza!="0")
    {
      $indice_fracao3 = $carrinho->adicionar_fracao($indice_pizza, $sabor3_pizza, $num_fracao[2]);
      $num_ingredientes = (is_array($ingredientes3) ? count($ingredientes3) : 0);
      
      $conexao = conectar_bd();
      $pizza_semana = 0;
      $sql_pizza_semana3 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor3_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
      $res_pizza_semana3 = mysql_query($sql_pizza_semana3);   
      $sql_pizza_semana3 .= " GROUP BY ip.cod_pizzas";
      $res_aux = mysql_query($sql_pizza_semana3);
      $obj_aux = mysql_fetch_object($res_aux);
      $pizza_semana = $obj_aux->pizza_semana;
      if(($pizza_semana == '1') || ($num_ingredientes == 0))
      {
        while ($obj_pizza_semana3 = mysql_fetch_object($res_pizza_semana3))
        {
          $tipo_ingrediente = 'NORMAL';
          $cod_ingredientes_troca = '';
          $cod_ingredientes = $obj_pizza_semana3->cod_ingredientes;
          $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
        }
      }
      else
      {
        for ($a=0; $a<$num_ingredientes; $a++)
        {
          if ($ingredientes3[$a]!="")
          {
            list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca) = split("###", $ingredientes3[$a]);
            $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
            $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
          }
        }
      }
      
      $num_ingredientes_adicionais = count ($ingredientes_adicionais3);
      for ($a=0; $a<$num_ingredientes_adicionais; $a++)
      {
        if ($ingredientes_adicionais3[$a]!="")
        {
          list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais3[$a]);
          $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes_adicionais, false, $tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
        }
      }
    }

    if ($sabor4_pizza!="0")
    {
      $indice_fracao4 = $carrinho->adicionar_fracao($indice_pizza, $sabor4_pizza, $num_fracao[3]);
      $num_ingredientes = (is_array($ingredientes4) ? count($ingredientes4) : 0);
      
      $conexao = conectar_bd();
      $pizza_semana = 0;
      $sql_pizza_semana4 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor4_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
      $res_pizza_semana4 = mysql_query($sql_pizza_semana4);   
      $sql_pizza_semana4 .= " GROUP BY ip.cod_pizzas";
      $res_aux = mysql_query($sql_pizza_semana4);
      $obj_aux = mysql_fetch_object($res_aux);
      $pizza_semana = $obj_aux->pizza_semana;
      if(($pizza_semana == '1') || ($num_ingredientes == 0))
      {
        while ($obj_pizza_semana4 = mysql_fetch_object($res_pizza_semana4))
        {
          $tipo_ingrediente = 'NORMAL';
          $cod_ingredientes_troca = '';
          $cod_ingredientes = $obj_pizza_semana4->cod_ingredientes;
          $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
        } 
      }
      else
      {
        for ($a=0; $a<$num_ingredientes; $a++)
        {
          if ($ingredientes4[$a]!="")
          {
              list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca) = split("###", $ingredientes4[$a]);
              $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
              $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
          }
        }
      }
      
      $num_ingredientes_adicionais = count ($ingredientes_adicionais4);
      for ($a=0; $a<$num_ingredientes_adicionais; $a++)
      {
        if ($ingredientes_adicionais4[$a]!="")
        {
            list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais4[$a]);
            $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
            $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes_adicionais, false,$tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
        }
      }
    }   
  }

    
    
    $deb=0;
    $indice_opcoes = -1;
    if ($deb==1)  echo "<br>indice_opcoes1: ".$indice_opcoes;
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
        unset($_SESSION['ipi_carrinho']['combo']);
        $acao = "combo_redirecionar_pedido";
    }
    else
    {
        if ($_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['tipo']=='PIZZA')
        {
            $acao = "combo_redirecionar_pizza";
        }
        elseif ($_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['tipo']=='BEBIDA')
        {
            $acao = "combo_redirecionar_bebida";
        }
    }
        if ($deb==1) echo "<br>indice_opcoes2: ".$indice_opcoes;
    
    
        if ($deb==1) echo "<Br>Acao: ".$acao;
    
}



if (($acao=="adicionar")||($acao=="finalizar")||($acao=="adicionar_redirecionar_promocao")||($acao=="adicionar_redirecionar_promocao_bebidas")||($acao=="sugestao_verificar_login"))
{
	$cod_adicionais = validaVarPost('gergelim');
	$cod_bordas = validaVarPost('borda');
	$quant_fracao = validaVarPost('num_sabores');
	$cod_tamanhos = validaVarPost('tam_pizza');
	$cod_tipo_massa = validaVarPost('tipo_massa');
	$cod_opcoes_corte = validaVarPost('corte');

	$nc = validaVarPost('nc');	//nc = numero cupom
	$vc = validaVarPost('vc');  //vc = validar cupo

  $c_pizza_pai = validaVarPost("cpp");
	
	$trocar = validaVarPost('trocar');
		
	$num_fracao = validaVarPost('num_fracao');
	$sabor1_pizza = validaVarPost('sabor1_pizza');
	$sabor2_pizza = validaVarPost('sabor2_pizza');
	$sabor3_pizza = validaVarPost('sabor3_pizza');
	$sabor4_pizza = validaVarPost('sabor4_pizza');
	
	$ingredientes1 = validaVarPost('ingredientes1');
	$ingredientes2 = validaVarPost('ingredientes2');
	$ingredientes3 = validaVarPost('ingredientes3');
	$ingredientes4 = validaVarPost('ingredientes4');

	$ingredientes_adicionais1 = validaVarPost('ingredientes_adicionais1');
	$ingredientes_adicionais2 = validaVarPost('ingredientes_adicionais2');
	$ingredientes_adicionais3 = validaVarPost('ingredientes_adicionais3');
	$ingredientes_adicionais4 = validaVarPost('ingredientes_adicionais4');
	
  $promocao_cod = "";

  if($_SESSION['ipi_carrinho']['sugestao']==12 && $_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"]==1)
  {
    $indice_pizza = $carrinho->adicionar_pizza_promocional_escolha ($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $quant_fracao, $cod_opcoes_corte, '', '');
    $_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"]==0;
  }
  else
  {
  	$indice_pizza = $carrinho->adicionar_pizza($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $quant_fracao, $cod_opcoes_corte, $vc, $nc,'','',$c_pizza_pai);
  }
	//Encontrar o id_sessao do m\E9todo de exclus\E3o, usado no redirect l\E1 no fim da p\E1gina
	$id_sessao_pai = $_SESSION['ipi_carrinho']['pedido'][$indice_pizza]['pizza_id_sessao'];
  $fracao_doce = $carrinho->verificar_pizza_doce("inteira",$sabor1_pizza,$sabor2_pizza,$sabor3_pizza,$sabor4_pizza);

	$promocao9=false;
  $promocao17=false;
  $promocao19=false;
  $promocao13 =false;

  if($sugestao==13)
  {
    $promocao13 =true;
    $_SESSION['ipi_carrinho']['pedido'][$indice_pizza]['promocao13_ativa'] = 1;
  }

  if(isset($_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]))
  {
    if($fracao_doce==false && $cod_tamanhos==3 && ( ($_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]==9) || ($_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]==19 ) ) ) 
    {
      $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
      if ($numero_pizzas > 0)
      {
        for ($a = 0; $a < $numero_pizzas; $a++)
        {
          if ($_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_ativa']==1)//a variavel ativa, funciona como se fosse 'pendente', assim, depois que eu associar, eu removo-a para não deixala pendente
          {
            $indice_sessao_pai = $a;
            break;
          }
        }
          
      }
    
      $promocao9 = false;//flag para dizer q promo de carnaval esta ativa e nao vai conflitar - desativado para só a promo 19 (dia das maes) funcionar
      $promocao19 = true;//flag para dizer q promo de dia das maes esta ativa e nao vai conflitar

      unset($_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]);
      unset($_SESSION['ipi_carrinho']['pedido'][$indice_sessao_pai]['promocao9_ativa']);
      $_SESSION['ipi_carrinho']['pedido'][$indice_sessao_pai]['promocao9_indice'] = $_SESSION["ipi_carrinho"]['promocao']["promo9_indice"] ;
      $_SESSION['ipi_carrinho']['pedido'][$indice_pizza]['promocao9_indice'] = $_SESSION["ipi_carrinho"]['promocao']["promo9_indice"] ;
    }

    if($fracao_doce==true && $_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]==17)// && $cod_tamanhos==3 
    {
      $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
      if ($numero_pizzas > 0)
      {
        for ($a = 0; $a < $numero_pizzas; $a++)
        {
          if ($_SESSION['ipi_carrinho']['pedido'][$a]['promocao17_ativa']==1)//a variavel ativa, funciona como se fosse 'pendente', assim, depois que eu associar, eu removo-a para não deixala pendente
          {
            $indice_sessao_pai = $a;
            break;
          }
        }
          
      }
    
      $promocao17 = true;//flag para dizer q promo de carnaval esta ativa e nao vai conflitar

      unset($_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]);
      unset($_SESSION['ipi_carrinho']['pedido'][$indice_sessao_pai]['promocao17_ativa']);
      $_SESSION['ipi_carrinho']['pedido'][$indice_sessao_pai]['promocao17_indice'] = $_SESSION["ipi_carrinho"]['promocao']["promo17_indice"] ;
      $_SESSION['ipi_carrinho']['pedido'][$indice_pizza]['promocao17_indice'] = $_SESSION["ipi_carrinho"]['promocao']["promo17_indice"] ;
      $_SESSION['ipi_carrinho']['pedido'][$indice_pizza]['promocao17_doce'] = 's' ;
    }

  }
  $promo_pizza_doce = false;
	if($trocar!='n' && $promocao17==false)
	{
		$trocar_cod = validaVarPost('combo_bebida_trocar');
    $trocar = validaVarPost('trocar');
	/*	echo "<pre>";
		print_r($_POST);
		echo"</pre>";
		die();*/
		if($cod_tamanhos == 3)
		{
			if($trocar!='1')
			$trocar = '0';
			
			//echo '<br/>trocar1'.$trocar.'<br/>';
			$promo_pizza_doce = $carrinho->verificar_promocao_pizza_doce($indice_pizza,$trocar,$sabor1_pizza,$sabor2_pizza,$sabor3_pizza,$sabor4_pizza,$trocar_cod);
		}
		
	}
  $dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');
   $conexao = conectar_bd();
	////////////////BUSCANDO CODIGO DAS PIZZARIAS//////////

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
  
  $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '4' and pp.situacao='ATIVO'";
    //die($sql_buscar_promocao);
    $res_buscar_promocoes = mysql_query($sql_buscar_promocao);

    $sql_buscar_promocao_11 = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '11' and pp.situacao='ATIVO'";//PROMOÇÃO 11 EH A DE ARARAQUARA QUE TEM OS DIAS FIXOS PARA FUNCIONAR
    //die($sql_buscar_promocao);
    $res_buscar_promocoes_11 = mysql_query($sql_buscar_promocao_11);

$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');
    if(mysql_num_rows($res_buscar_promocoes)>0 || (mysql_num_rows($res_buscar_promocoes_11)>0 && (($dia_semana[date("w")]=='Seg') || ($dia_semana[date("w")]=='Ter')  || ($dia_semana[date("w")]=='Qui')  || ($dia_semana[date("w")]=='Dom')  )  ))
    {
      if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
      {
        if(mysql_num_rows($res_buscar_promocoes)>0)
        {
          $promocao_cod = 4;
        }
        elseif(mysql_num_rows($res_buscar_promocoes_11)>0 && (($dia_semana[date("w")]=='Seg') || ($dia_semana[date("w")]=='Ter')  || ($dia_semana[date("w")]=='Qui')  || ($dia_semana[date("w")]=='Dom')))
        {
          $promocao_cod = 11;
        }
        else
        {
          $promocao_cod = 4;
        }

        if(!isset($_SESSION['ipi_carrinho']['promocao']['promocao_4'] ) && $promocao13==false)
        {
          $num_pizzas  = count($_SESSION['ipi_carrinho']['pedido']);
          $cont_g = 0;
          for($p = 0;$p<$num_pizzas ; $p++)
          {
            if($_SESSION['ipi_carrinho']['pedido'][$p]["cod_tamanhos"] == 3)
            $cont_g++;
          }

          if($promocao9==false && $promocao17==false && $promocao19==false) //se a pizza a ser adicionada for da promoção 9, oferece o refri balcão na segunda pizza (ja que a pergunta da primeira irá direto para adicionar a 2º com desconto)
          {
            if($cont_g==1)
            {
              $acao = 'ir_promocao';
              $pizza_pai = '';
            }
          }
          else
          {
            if($cont_g==2)
            {
              $acao = 'ir_promocao';
              $pizza_pai = '';
            }
          }
        }
      }
    }
  
    $promo_borda = false;
    if (date('w') == 2)
    {
      $sql_buscar_promocao_borda = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '2' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
      $res_buscar_promocao_borda = mysql_query($sql_buscar_promocao_borda);
      $promo_borda = true;
    }

   $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '8' and pp.situacao='ATIVO'";
    //die($sql_buscar_promocao);
    $res_buscar_promocoes = mysql_query($sql_buscar_promocao);
    if(mysql_num_rows($res_buscar_promocoes)>0)
    {

      //$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');
      //if($dia_semana[date("w")]=='Seg')
      //{
        if($cod_tamanhos==3)
        {
          $acao = 'ir_promocao'; 
          $promocao_cod = 8;
          $pizza_pai = $id_sessao_pai;
        }
      //}
    }
    
    $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '13' and pp.situacao='ATIVO'";
    //die($sql_buscar_promocao);
    $res_buscar_promocoes = mysql_query($sql_buscar_promocao);
    if(mysql_num_rows($res_buscar_promocoes)>0 && $promo_pizza_doce==false && !($cod_bordas!='N' && $promo_borda==true))
    {

      //$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');
      //if($dia_semana[date("w")]=='Seg')
      //{
        if($cod_tamanhos==3 || $cod_tamanhos==5)//Pode ser tanto Quadrada Grande quanto a Six
        {
          $acao = 'ir_promocao'; 
          $promocao_cod = 13;
          $pizza_pai = $id_sessao_pai;
          $promocao13 =true;
        }
      //}
    }

  if(($promocao9==false && $promocao17==false && $promocao19==false))//essas duas (9,17) sao promocoes que usam duas pizzas, entao essa validação esta aqui para que nenhuma promoção se ative na segunda pizza
  {
    $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '1' and pp.situacao='ATIVO'";
      //die($sql_buscar_promocao);
      $res_buscar_promocoes = mysql_query($sql_buscar_promocao);
      if(mysql_num_rows($res_buscar_promocoes)>0)
      {

        
      	if($dia_semana[date("w")]=='Seg')
        {
          if($cod_tamanhos==3)
          {
            $acao = 'ir_promocao'; 
            $promocao_cod = 1;
            $pizza_pai = $id_sessao_pai;
          }
        }
      }
  

    $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '18' and pp.situacao='ATIVO'";
      //die($sql_buscar_promocao);
      $res_buscar_promocoes = mysql_query($sql_buscar_promocao);
      if(mysql_num_rows($res_buscar_promocoes)>0)
      {

        
        //if($dia_semana[date("w")]=='Seg')
        {
          if($cod_tamanhos==3)
          {
            $acao = 'ir_promocao'; 
            $promocao_cod = 18;
            $pizza_pai = $id_sessao_pai;
          }
        }
      }

    if($fracao_doce==false && $cod_tamanhos==3 && $promocao13==false)
    {
      $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '9' and pp.situacao='ATIVO'";
      //die($sql_buscar_promocao);
      $res_buscar_promocoes = mysql_query($sql_buscar_promocao);
      if(mysql_num_rows($res_buscar_promocoes)>0)
      {
  
        //$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');
        //if($dia_semana[date("w")]=='Seg')
        //{
          if($cod_tamanhos==3)
          {
            $acao = 'ir_promocao'; 
            $promocao_cod = 9;
            $pizza_pai = $id_sessao_pai;
          }
        //}
      }


      $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '17' and pp.situacao='ATIVO'";
      //die($sql_buscar_promocao);
      $res_buscar_promocoes = mysql_query($sql_buscar_promocao);
      if(mysql_num_rows($res_buscar_promocoes)>0)
      {
  
        //$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');
        //if($dia_semana[date("w")]=='Seg')
        //{
          if($cod_tamanhos==3)
          {
            $acao = 'ir_promocao'; 
            $promocao_cod = 17;
            $pizza_pai = $id_sessao_pai;
          }
        //}
      }


    }

    if($cod_tamanhos==3)
    {
      $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '15' and pp.situacao='ATIVO'";
      //die($sql_buscar_promocao);
      $res_buscar_promocoes = mysql_query($sql_buscar_promocao);
      if(mysql_num_rows($res_buscar_promocoes)>0)
      {
        $acao = 'ir_promocao'; 
        $promocao_cod = 15;
        $pizza_pai = $id_sessao_pai;
      }
    }

    if($cod_tamanhos==3 && $promocao_cod!=1 && $promo_pizza_doce==false && $promocao13==false)//promo pascoa 2014
    {
      $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '12' and pp.situacao='ATIVO'";
      //die($sql_buscar_promocao);
      $res_buscar_promocoes = mysql_query($sql_buscar_promocao);

      

      if(mysql_num_rows($res_buscar_promocoes)>0 && !($cod_bordas!='N' && $promo_borda==true))
      {
  
        //$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');
        //if($dia_semana[date("w")]=='Seg')
        //{
          if($cod_tamanhos==3)
          {
            if(isset($_SESSION['ipi_carrinho']['promocao']['promocao12_cont']))
            {
              //$_SESSION['ipi_carrinho']['promocao']['promocao12_cont'] = $_SESSION['ipi_carrinho']['promocao']['promocao12_cont'] +1;
              //$_SESSION['ipi_carrinho']['promocao']['promocao12_id_2'] = $id_sessao_pai;
              $_SESSION['ipi_carrinho']['pedido'][$indice_pizza]['promocao12_ativa'] = 1;
              $_SESSION['ipi_carrinho']['promocao']['promocao12_cont'] = $_SESSION['ipi_carrinho']['promocao']['promocao12_cont'] +1;
              $_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"] = 1;
            }
            else
            {
              $_SESSION['ipi_carrinho']['promocao']['promocao12_cont'] = 1;
              $_SESSION['ipi_carrinho']['pedido'][$indice_pizza]['promocao12_ativa'] = 1;
              $_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"] = 1;
            }

            if($_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"]==1)
            {
              $acao = 'ir_promocao'; 
              $promocao_cod = 12;
              $pizza_pai = $id_sessao_pai;
            }
          }
        //}
      }
    }
  }



/*  $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
  $cod_pizzarias = 0;
  if($cep_visitante)
  {
    //echo "<br />1";
    $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));

    $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo";
    //echo $sql_cod_pizzarias;
    $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
    $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
    $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
  }*/

	
	if ($sabor1_pizza!="0")
	{
		$indice_fracao1 = $carrinho->adicionar_fracao($indice_pizza, $sabor1_pizza, $num_fracao[0]);
		$num_ingredientes = (is_array($ingredientes1) ? count($ingredientes1) : 0);  
    $conexao = conectar_bd();

		$pizza_semana = 0;
		$sql_pizza_semana1 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor1_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
    //echo $sql_pizza_semana1;
		$res_pizza_semana1 = mysql_query($sql_pizza_semana1);		
		$res_aux = mysql_query($sql_pizza_semana1);
		$obj_aux = mysql_fetch_object($res_aux);
		$pizza_semana = $obj_aux->pizza_semana;
		if(($pizza_semana == '1') || ($num_ingredientes == 0))
		{
		  while ($obj_pizza_semana1 = mysql_fetch_object($res_pizza_semana1))
		  {
	      $tipo_ingrediente = 'NORMAL';
        $cod_ingredientes_troca = '';
        $cod_ingredientes = $obj_pizza_semana1->cod_ingredientes;
        $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
        //echo $indice_pizza.' '; 
        //echo $indice_fracao1.' '; 
        //echo $cod_ingredientes.' '; 
        //echo $tipo_ingrediente_bool.' '; 
        //echo $cod_ingredientes_troca.'<br />';
	      $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
		  } 
      //die();
		}
		else
		{
		  for ($a=0; $a<$num_ingredientes; $a++)
		  {
			  if ($ingredientes1[$a]!="")
			  {
                  list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca ) = split("###", $ingredientes1[$a]);
                  $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
			      $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
			      
			      //echo "$tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca<br>";
			  }
		  }
		}
		
		
		$num_ingredientes_adicionais = count ($ingredientes_adicionais1);
		for ($a=0; $a<$num_ingredientes_adicionais; $a++)
		{
			if ($ingredientes_adicionais1[$a]!="")
			{
                list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais1[$a]);
                $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
			    $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes_adicionais, false, $tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
			}
		}
	}
	
	if ($sabor2_pizza!="0")
	{
		$indice_fracao2 = $carrinho->adicionar_fracao($indice_pizza, $sabor2_pizza, $num_fracao[1]);
		$num_ingredientes = (is_array($ingredientes2) ? count($ingredientes2) : 0);
		
    $conexao = conectar_bd();
		$pizza_semana = 0;
    $sql_pizza_semana2 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor2_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
		$res_pizza_semana2 = mysql_query($sql_pizza_semana2);		
		$sql_pizza_semana2 .= " GROUP BY ip.cod_pizzas";
		$res_aux = mysql_query($sql_pizza_semana2);
		$obj_aux = mysql_fetch_object($res_aux);
		$pizza_semana = $obj_aux->pizza_semana;
		if(($pizza_semana == '1') || ($num_ingredientes == 0))
		{
		  while ($obj_pizza_semana2 = mysql_fetch_object($res_pizza_semana2))
		  {
	      $tipo_ingrediente = 'NORMAL';
        $cod_ingredientes_troca = '';
        $cod_ingredientes = $obj_pizza_semana2->cod_ingredientes;
        $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
	      $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
		  } 
		}
		else
		{
		  for ($a=0; $a<$num_ingredientes; $a++)
		  {
			  if ($ingredientes2[$a]!="")
        {
            list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca) = split("###", $ingredientes2[$a]);
            $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
            $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
        }
		  }
		}
		
		
		$num_ingredientes_adicionais = count ($ingredientes_adicionais2);
		for ($a=0; $a<$num_ingredientes_adicionais; $a++)
		{
			if ($ingredientes_adicionais2[$a]!="")
      {
          list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais2[$a]);
          $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes_adicionais, false, $tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
      }
		}
	}
	
	if ($sabor3_pizza!="0")
	{
		$indice_fracao3 = $carrinho->adicionar_fracao($indice_pizza, $sabor3_pizza, $num_fracao[2]);
		$num_ingredientes = (is_array($ingredientes3) ? count($ingredientes3) : 0);
		
    $conexao = conectar_bd();
		$pizza_semana = 0;
    $sql_pizza_semana3 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor3_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
		$res_pizza_semana3 = mysql_query($sql_pizza_semana3);		
		$sql_pizza_semana3 .= " GROUP BY ip.cod_pizzas";
		$res_aux = mysql_query($sql_pizza_semana3);
		$obj_aux = mysql_fetch_object($res_aux);
		$pizza_semana = $obj_aux->pizza_semana;
		if(($pizza_semana == '1') || ($num_ingredientes == 0))
		{
		  while ($obj_pizza_semana3 = mysql_fetch_object($res_pizza_semana3))
		  {
	      $tipo_ingrediente = 'NORMAL';
        $cod_ingredientes_troca = '';
        $cod_ingredientes = $obj_pizza_semana3->cod_ingredientes;
        $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
	      $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
		  }
		}
		else
		{
		  for ($a=0; $a<$num_ingredientes; $a++)
		  {
			  if ($ingredientes3[$a]!="")
        {
          list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca) = split("###", $ingredientes3[$a]);
          $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
        }
		  }
		}
		
		$num_ingredientes_adicionais = count ($ingredientes_adicionais3);
		for ($a=0; $a<$num_ingredientes_adicionais; $a++)
		{
			if ($ingredientes_adicionais3[$a]!="")
      {
        list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais3[$a]);
        $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
        $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes_adicionais, false, $tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
      }
		}
	}
	
	if ($sabor4_pizza!="0")
	{
		$indice_fracao4 = $carrinho->adicionar_fracao($indice_pizza, $sabor4_pizza, $num_fracao[3]);
		$num_ingredientes = (is_array($ingredientes4) ? count($ingredientes4) : 0);
		
    $conexao = conectar_bd();
		$pizza_semana = 0;
    $sql_pizza_semana4 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor4_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
		$res_pizza_semana4 = mysql_query($sql_pizza_semana4);		
		$sql_pizza_semana4 .= " GROUP BY ip.cod_pizzas";
		$res_aux = mysql_query($sql_pizza_semana4);
		$obj_aux = mysql_fetch_object($res_aux);
		$pizza_semana = $obj_aux->pizza_semana;
		if(($pizza_semana == '1') || ($num_ingredientes == 0))
		{
		  while ($obj_pizza_semana4 = mysql_fetch_object($res_pizza_semana4))
		  {
	      $tipo_ingrediente = 'NORMAL';
        $cod_ingredientes_troca = '';
        $cod_ingredientes = $obj_pizza_semana4->cod_ingredientes;
        $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
	      $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
		  } 
		}
		else
		{
		  for ($a=0; $a<$num_ingredientes; $a++)
		  {
			  if ($ingredientes4[$a]!="")
        {
            list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca) = split("###", $ingredientes4[$a]);
            $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
            $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
        }
		  }
		}
		
		$num_ingredientes_adicionais = count ($ingredientes_adicionais4);
		for ($a=0; $a<$num_ingredientes_adicionais; $a++)
		{
			if ($ingredientes_adicionais4[$a]!="")
      {
          list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais4[$a]);
          $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes_adicionais, false,$tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
      }
		}
	}		
	desconectabd($conexao);
}


if (($acao=="adicionar_promocao")||($acao=="adicionar_promocao_bebidas"))
{
	unset($_SESSION["ipi_carrinho"]["pizza_promocional"]);
	unset($_SESSION["ipi_carrinho"]["pizza_promocional_pai"] );
	
	$cod_adicionais = validaVarPost('gergelim');
	$cod_bordas = validaVarPost('borda');
	$quant_fracao = validaVarPost('num_sabores');
	$cod_tamanhos = validaVarPost('tam_pizza');
	$cod_tipo_massa = validaVarPost('tipo_massa');
	$cod_opcoes_corte = validaVarPost('corte');

	$cpp = validaVarPost('cpp');
	$cc = validaVarPost('cc');
		
	$num_fracao = validaVarPost('num_fracao');
	$sabor1_pizza = validaVarPost('sabor1_pizza');
	$sabor2_pizza = validaVarPost('sabor2_pizza');
	$sabor3_pizza = validaVarPost('sabor3_pizza');
	$sabor4_pizza = validaVarPost('sabor4_pizza');
	
	$ingredientes1 = validaVarPost('ingredientes1');
	$ingredientes2 = validaVarPost('ingredientes2');
	$ingredientes3 = validaVarPost('ingredientes3');
	$ingredientes4 = validaVarPost('ingredientes4');
	
	$ingredientes_adicionais1 = validaVarPost('ingredientes_adicionais1');
	$ingredientes_adicionais2 = validaVarPost('ingredientes_adicionais2');
	$ingredientes_adicionais3 = validaVarPost('ingredientes_adicionais3');
	$ingredientes_adicionais4 = validaVarPost('ingredientes_adicionais4');
		
	
	$indice_pizza = $carrinho->adicionar_pizza_promocional_escolha ($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $quant_fracao, $cod_opcoes_corte, $cpp, $cc);
  $trocar_cod = validaVarPost('combo_bebida_trocar');
  $trocar = validaVarPost('trocar');   

  $conexao = conectar_bd();

  ////////////////BUSCANDO CODIGO DAS PIZZARIAS//////////
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

$sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '4' and pp.situacao='ATIVO'";
    //die($sql_buscar_promocao);
    $res_buscar_promocoes = mysql_query($sql_buscar_promocao);

    $sql_buscar_promocao_11 = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '1' and pp.situacao='ATIVO'";//PROMOÇÃO 11 EH A DE ARARAQUARA QUE TEM OS DIAS FIXOS PARA FUNCIONAR
    //die($sql_buscar_promocao);
    $res_buscar_promocoes_11 = mysql_query($sql_buscar_promocao_11);


    if(mysql_num_rows($res_buscar_promocoes)>0 || (mysql_num_rows($res_buscar_promocoes_11)>0 && (($dia_semana[date("w")]=='Seg') || ($dia_semana[date("w")]=='Ter')  || ($dia_semana[date("w")]=='Qui')  || ($dia_semana[date("w")]=='Dom')  )  ))
    {
      if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
      {
        if(mysql_num_rows($res_buscar_promocoes)>0)
        {
          $promocao_cod = 4;
        }
        elseif(mysql_num_rows($res_buscar_promocoes_11)>0 && (($dia_semana[date("w")]=='Seg') || ($dia_semana[date("w")]=='Ter')  || ($dia_semana[date("w")]=='Qui')  || ($dia_semana[date("w")]=='Dom')))
        {
          $promocao_cod = 11;
        }
        else
        {
          $promocao_cod = 4;
        }
          
        if(!isset($_SESSION['ipi_carrinho']['promocao']['promocao_4'] ))
        {
          $num_pizzas  = count($_SESSION['ipi_carrinho']['pedido']);
          $cont_g = 0;
          for($p = 0;$p<$num_pizzas ; $p++)
          {
            if($_SESSION['ipi_carrinho']['pedido'][$p]["cod_tamanhos"] == 3)
            $cont_g++;
          }

          if($promocao9==false) //se a pizza a ser adicionada for da promoção 9, oferece o refri balcão na segunda pizza (ja que a pergunta da primeira irá direto para adicionar a 2º com desconto)
          {
            if($cont_g==1)
            {
              $acao = 'ir_promocao';
              $pizza_pai = '';
            }
          }
          elseif($promocao19==false) //se a pizza a ser adicionada for da promoção 9, oferece o refri balcão na segunda pizza (ja que a pergunta da primeira irá direto para adicionar a 2º com desconto)
          {
            if($cont_g==1)
            {
              $acao = 'ir_promocao';
              $pizza_pai = '';
            }
          }
          else
          {
            if($cont_g==2)
            {
              $acao = 'ir_promocao';
              $pizza_pai = '';
            }
          }
        }
      }
    }

  
  $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
  $cod_pizzarias = 0;
  if($cep_visitante)
  {
    //echo "<br />1";
    $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));

    $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo";
    //echo $sql_cod_pizzarias;
    $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
    $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
    $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
  }

  if ($sabor1_pizza!="0")
  {
    $indice_fracao1 = $carrinho->adicionar_fracao($indice_pizza, $sabor1_pizza, $num_fracao[0]);
    $num_ingredientes = (is_array($ingredientes1) ? count($ingredientes1) : 0);  
    $conexao = conectar_bd();

    $pizza_semana = 0;
    $sql_pizza_semana1 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor1_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
    //echo $sql_pizza_semana1;
    $res_pizza_semana1 = mysql_query($sql_pizza_semana1);   
    $res_aux = mysql_query($sql_pizza_semana1);
    $obj_aux = mysql_fetch_object($res_aux);
    $pizza_semana = $obj_aux->pizza_semana;
    if(($pizza_semana == '1') || ($num_ingredientes == 0))
    {
      while ($obj_pizza_semana1 = mysql_fetch_object($res_pizza_semana1))
      {
        $tipo_ingrediente = 'NORMAL';
        $cod_ingredientes_troca = '';
        $cod_ingredientes = $obj_pizza_semana1->cod_ingredientes;
        $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
        //echo $indice_pizza.' '; 
        //echo $indice_fracao1.' '; 
        //echo $cod_ingredientes.' '; 
        //echo $tipo_ingrediente_bool.' '; 
        //echo $cod_ingredientes_troca.'<br />';
        $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
      } 
      //die();
    }
    else
    {
      for ($a=0; $a<$num_ingredientes; $a++)
      {
        if ($ingredientes1[$a]!="")
        {
                  list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca ) = split("###", $ingredientes1[$a]);
                  $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
            $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
            
            //echo "$tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca<br>";
        }
      }
    }
    
    
    $num_ingredientes_adicionais = count ($ingredientes_adicionais1);
    for ($a=0; $a<$num_ingredientes_adicionais; $a++)
    {
      if ($ingredientes_adicionais1[$a]!="")
      {
                list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais1[$a]);
                $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao1, $cod_ingredientes_adicionais, false, $tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
      }
    }
  }
	
  if ($sabor2_pizza!="0")
  {
    $indice_fracao2 = $carrinho->adicionar_fracao($indice_pizza, $sabor2_pizza, $num_fracao[1]);
    $num_ingredientes = (is_array($ingredientes2) ? count($ingredientes2) : 0);
    
    $conexao = conectar_bd();
    $pizza_semana = 0;
    $sql_pizza_semana2 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor2_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
    $res_pizza_semana2 = mysql_query($sql_pizza_semana2);   
    $sql_pizza_semana2 .= " GROUP BY ip.cod_pizzas";
    $res_aux = mysql_query($sql_pizza_semana2);
    $obj_aux = mysql_fetch_object($res_aux);
    $pizza_semana = $obj_aux->pizza_semana;
    if(($pizza_semana == '1') || ($num_ingredientes == 0))
    {
      while ($obj_pizza_semana2 = mysql_fetch_object($res_pizza_semana2))
      {
        $tipo_ingrediente = 'NORMAL';
        $cod_ingredientes_troca = '';
        $cod_ingredientes = $obj_pizza_semana2->cod_ingredientes;
        $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
        $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
      } 
    }
    else
    {
      for ($a=0; $a<$num_ingredientes; $a++)
      {
        if ($ingredientes2[$a]!="")
        {
            list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca) = split("###", $ingredientes2[$a]);
            $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
            $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
        }
      }
    }
    
    
    $num_ingredientes_adicionais = count ($ingredientes_adicionais2);
    for ($a=0; $a<$num_ingredientes_adicionais; $a++)
    {
      if ($ingredientes_adicionais2[$a]!="")
      {
          list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais2[$a]);
          $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao2, $cod_ingredientes_adicionais, false, $tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
      }
    }
  }
	
  if ($sabor3_pizza!="0")
  {
    $indice_fracao3 = $carrinho->adicionar_fracao($indice_pizza, $sabor3_pizza, $num_fracao[2]);
    $num_ingredientes = (is_array($ingredientes3) ? count($ingredientes3) : 0);
    
    $conexao = conectar_bd();
    $pizza_semana = 0;
    $sql_pizza_semana3 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor3_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
    $res_pizza_semana3 = mysql_query($sql_pizza_semana3);   
    $sql_pizza_semana3 .= " GROUP BY ip.cod_pizzas";
    $res_aux = mysql_query($sql_pizza_semana3);
    $obj_aux = mysql_fetch_object($res_aux);
    $pizza_semana = $obj_aux->pizza_semana;
    if(($pizza_semana == '1') || ($num_ingredientes == 0))
    {
      while ($obj_pizza_semana3 = mysql_fetch_object($res_pizza_semana3))
      {
        $tipo_ingrediente = 'NORMAL';
        $cod_ingredientes_troca = '';
        $cod_ingredientes = $obj_pizza_semana3->cod_ingredientes;
        $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
        $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
      }
    }
    else
    {
      for ($a=0; $a<$num_ingredientes; $a++)
      {
        if ($ingredientes3[$a]!="")
        {
          list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca) = split("###", $ingredientes3[$a]);
          $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
        }
      }
    }
    
    $num_ingredientes_adicionais = count ($ingredientes_adicionais3);
    for ($a=0; $a<$num_ingredientes_adicionais; $a++)
    {
      if ($ingredientes_adicionais3[$a]!="")
      {
        list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais3[$a]);
        $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
        $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao3, $cod_ingredientes_adicionais, false, $tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
      }
    }
  }

  if ($sabor4_pizza!="0")
  {
    $indice_fracao4 = $carrinho->adicionar_fracao($indice_pizza, $sabor4_pizza, $num_fracao[3]);
    $num_ingredientes = (is_array($ingredientes4) ? count($ingredientes4) : 0);
    
    $conexao = conectar_bd();
    $pizza_semana = 0;
    $sql_pizza_semana4 = "SELECT p.pizza_semana, ip.cod_ingredientes, i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) LEFT JOIN ipi_pizzas_ipi_tamanhos p ON (ip.cod_pizzas = p.cod_pizzas) WHERE i.ativo = 1 AND i.consumo = 0 AND ip.cod_pizzas=$sabor4_pizza AND p.cod_pizzarias = '$cod_pizzarias' AND p.cod_tamanhos='$cod_tamanhos'";
    $res_pizza_semana4 = mysql_query($sql_pizza_semana4);   
    $sql_pizza_semana4 .= " GROUP BY ip.cod_pizzas";
    $res_aux = mysql_query($sql_pizza_semana4);
    $obj_aux = mysql_fetch_object($res_aux);
    $pizza_semana = $obj_aux->pizza_semana;
    if(($pizza_semana == '1') || ($num_ingredientes == 0))
    {
      while ($obj_pizza_semana4 = mysql_fetch_object($res_pizza_semana4))
      {
        $tipo_ingrediente = 'NORMAL';
        $cod_ingredientes_troca = '';
        $cod_ingredientes = $obj_pizza_semana4->cod_ingredientes;
        $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
        $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
      } 
    }
    else
    {
      for ($a=0; $a<$num_ingredientes; $a++)
      {
        if ($ingredientes4[$a]!="")
        {
            list($tipo_ingrediente, $cod_ingredientes, $cod_ingredientes_troca) = split("###", $ingredientes4[$a]);
            $tipo_ingrediente_bool = ( $tipo_ingrediente=="TROCA" ? true : false );
            $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes, true, $tipo_ingrediente_bool, $cod_ingredientes_troca);
        }
      }
    }
    
    $num_ingredientes_adicionais = count ($ingredientes_adicionais4);
    for ($a=0; $a<$num_ingredientes_adicionais; $a++)
    {
      if ($ingredientes_adicionais4[$a]!="")
      {
          list($tipo_ingrediente_adicional, $cod_ingredientes_adicionais, $cod_ingredientes_troca_adicionais) = split("###", $ingredientes_adicionais4[$a]);
          $tipo_ingrediente_adicional_bool = ( $tipo_ingrediente_adicional=="TROCA" ? true : false );
          $carrinho->adicionar_ingrediente($indice_pizza, $indice_fracao4, $cod_ingredientes_adicionais, false,$tipo_ingrediente_adicional_bool, $cod_ingredientes_troca_adicionais);
      }
    }
  }   

}

//die("Y: ".$acao." X: ".$_SESSION['ipi_cliente']['autenticado']);

if ( ($acao=="adicionar_verificar_login")||($acao=="verificar_login"))
{
	if($_SESSION['ipi_cliente']['autenticado'] == 1)
	{
		$acao="pagamentos";
	}
	else
  {
		$acao="identifique";
  }
}

if(($acao=="sugestao_verificar_login") )
{
  if($_SESSION['ipi_cliente']['autenticado'] == 1)
  {
    $acao="pagamentos";
  }
  else
  {
    $acao="identifique";
  }
}
/*
if ($acao=="verificar_login")
{
  $acao="resumo_pedido";
}
*/

//echo "acao: ".$acao." - ".$erros;
//die;


if($acao=='adicionar_promocao_balcao')
{
  if(!isset($_SESSION['ipi_carrinho']['promocao']['promocao_4']))
  {
    $trocar_cod = validaVarPost('combo_bebida_trocar');
    $trocar = validaVarPost('trocar');            
    if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
    {
      $num_pizzas  = count($_SESSION['ipi_carrinho']['pedido']);
      $cont_g = 0;
      for($p = 0;$p<$num_pizzas ; $p++)
      {
        if($_SESSION['ipi_carrinho']['pedido'][$p]["cod_tamanhos"] == 3)
        $cont_g++;
      }

      if(($cont_g==1) || ($_SESSION['promocao']['promo9_indice']==0 & $cont_g==2) )
      {
        if($trocar!='n')
        {
          $trocar_cod = validaVarPost('combo_bebida_trocar');
          if($trocar==1)
          {
            $indice = $carrinho->adicionar_bebida_promocao_balcao($trocar_cod,5); 
            $_SESSION['ipi_carrinho']['promocao']['promocao_4'] = $indice;        
          }else
          {
            $indice = $carrinho->adicionar_bebida_promocao_balcao(5,5);
            $_SESSION['ipi_carrinho']['bebida'][$indice]['bebida_promocional'] = 1;
            $_SESSION['ipi_carrinho']['promocao']['promocao_4'] = $indice;        
          } 
        }

      }
    }
  }
}

if($acao == "repetir_pedido")
{
  $cod_pedidos = validar_var_post('cod_pedidos');
  $carrinho->repetir_pedido($cod_pedidos);
}

if($acao == "aceitar_promocao")
{
  $trocar = validaVarPost('trocar');
  $cod_tamanhos = validaVarPost('tam_pizza');
  $c_pizza_pai = validaVarPost("cpp");
  $cod_promo = validaVarPost("cod_promo");
  if($trocar!='n')
  { 
    $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
    if ($numero_pizzas > 0)
    {
      for ($a = 0; $a < $numero_pizzas; $a++)
      {
        if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_id_sessao']==$c_pizza_pai)
        {
          $indice_sessao= $a;
          break;
        }
      }
        
    }

    //$_SESSION["ipi_carrinho"]["pizza_promocional"] = "sim";
    if($cod_promo==17)
    {
      $_SESSION["ipi_carrinho"]['pedido'][$indice_sessao]["promocao17_ativa"] = 1;
      $_SESSION["ipi_carrinho"]['promocao']["cod_promocao"] = 17;
      $_SESSION["ipi_carrinho"]['promocao']["promo17_indice"] = isset($_SESSION["ipi_carrinho"]['promocao']["promo17_indice"]) ? ($_SESSION["ipi_carrinho"]['promocao']["promo17_indice"]+1) : 0;
    }
    else
    {
      $_SESSION["ipi_carrinho"]['pedido'][$indice_sessao]["promocao9_ativa"] = 1;
      $_SESSION["ipi_carrinho"]['promocao']["cod_promocao"] = 9;
      $_SESSION["ipi_carrinho"]['promocao']["promo9_indice"] = isset($_SESSION["ipi_carrinho"]['promocao']["promo9_indice"]) ? ($_SESSION["ipi_carrinho"]['promocao']["promo9_indice"]+1) : 0;
    }
  }
  else
  {
    $conexao = conectar_bd(); 
            ////////////////BUSCANDO CODIGO DAS PIZZARIAS//////////
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
       
    $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '1' and pp.situacao='ATIVO'";
    //die($sql_buscar_promocao);
    $res_buscar_promocoes = mysql_query($sql_buscar_promocao);
    if(mysql_num_rows($res_buscar_promocoes)>0)
    {

      $dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');
      if($dia_semana[date("w")]=='Seg')
      {
        //TODO REMOVER ESTE FOR, ELE TA PARA TESTE (SUBSTITUIR POR ALGO MAIS EFICIENTE)
        // ELE VERIFICA SE A ULTIMA PIZZA NA SESSAO (CONSEQUENTEMENTE A ULTIMA ADD) EH DE TAMANHO 3 PARA OFERECER PROMO SEGUNDA
        $num_pizzas  = count($_SESSION['ipi_carrinho']['pedido']);
        $grande = false;
        for($p = 0;$p<$num_pizzas ; $p++)
        {
          if($_SESSION['ipi_carrinho']['pedido'][$p]["cod_tamanhos"] == 3)
          $grande = true;
        }
        if($grande)
        {
          $acao = 'ir_promocao'; 
          $promocao_cod = 1;
          $pizza_pai = $c_pizza_pai;
        }
        else
        {
          $acao = 'redirecionar_seg'; 
        }
      }
      else
        {
          $acao = 'redirecionar_seg'; 
        }
    }
    else
    {
      $acao = 'redirecionar_seg'; 
    }
    desconectabd($conexao);
  }
}

//die(  'promocao&p='.$promocao_cod.''.($pizza_pai ?'&pp='.$pizza_pai : '')  );
switch($acao)
{
  case 'redirecionar_seg':
  {
		  $trocar = validaVarPost('trocar');     
		  if($trocar!='n')
		  {   
      	$cpp = validaVarPost('cpp');
				$_SESSION["ipi_carrinho"]["pizza_promocional"] = "sim";
				$_SESSION["ipi_carrinho"]["pizza_promocional_pai"] = $cpp;
     	  header('Location: pedido_promocional&codPizzaPai='.$cpp);
      }else
      {
        ////////////////BUSCANDO CODIGO DAS PIZZARIAS//////////
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
        $conexao = conectar_bd();
        $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '4' and pp.situacao='ATIVO'";
          //die($sql_buscar_promocao);
        $res_buscar_promocoes = mysql_query($sql_buscar_promocao);

        $sql_buscar_promocao_11 = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '1' and pp.situacao='ATIVO'";//PROMOÇÃO 11 EH A DE ARARAQUARA QUE TEM OS DIAS FIXOS PARA FUNCIONAR
        //die($sql_buscar_promocao);
        $res_buscar_promocoes_11 = mysql_query($sql_buscar_promocao_11);

          $dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');
        if(mysql_num_rows($res_buscar_promocoes)>0 || (mysql_num_rows($res_buscar_promocoes_11)>0 && (($dia_semana[date("w")]=='Seg') || ($dia_semana[date("w")]=='Ter')  || ($dia_semana[date("w")]=='Qui')  || ($dia_semana[date("w")]=='Dom')  )  ))
        {

          if(mysql_num_rows($res_buscar_promocoes)>0)
          {
            $promocao_cod = 4;
          }
          elseif(mysql_num_rows($res_buscar_promocoes_11)>0 && (($dia_semana[date("w")]=='Seg') || ($dia_semana[date("w")]=='Ter')  || ($dia_semana[date("w")]=='Qui')  || ($dia_semana[date("w")]=='Dom')))
          {
            $promocao_cod = 11;
          }
          else
          {
            $promocao_cod = 4;
          }

          desconectar_bd($conexao);
          $num_pizzas  = count($_SESSION['ipi_carrinho']['pedido']);
          $cont_g = 0;
          for($p = 0;$p<$num_pizzas ; $p++)
          {
            if($_SESSION['ipi_carrinho']['pedido'][$p]["cod_tamanhos"] == 3)
            $cont_g++;
          }

          if($cont_g==1)
          {
            $acao = 'ir_promocao';
           
            header('Location: promocao&p='.$promocao_cod.''.($pizza_pai ?'&pp='.$pizza_pai : '') );
          }
          else
          {
            header('Location: algo_mais');
          }
        }
        else
        {
          desconectar_bd($conexao);
        	header('Location: algo_mais');
        }
      }
  }
    break;
  case 'limpar':
        header('Location: pedidos');
    break;
  case 'aceitar_promocao':
      header('Location: pedidos');
    break;
  case 'repetir_pedido':
    {
        header('Location: pagamentos');
    }
    break;
  case 'ir_promocao':
    {
        header('Location: promocao&p='.$promocao_cod.''.($pizza_pai ?'&pp='.$pizza_pai : '') );
    }
    break; 
  case 'resumo_pedido':
    {
        header('Location: resumo_pedido');
  	}
  	break;
  case 'cliente_bloqueado':
    {
        header('Location: problemas_no_cadastro&erro='.$erros);
  	}
  	break;
  case 'algo_mais':
    {
      if($sugestao==12)
      {
        $_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"]=0;
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        for ($v = 0; $v < $numero_pizzas; $v++)
        {
          if ($_SESSION['ipi_carrinho']['pedido'][$v]['promocao12_ativa']==1)
          {
            unset($_SESSION['ipi_carrinho']['pedido'][$v]['promocao12_ativa']);
          }
        }

        unset($_SESSION['ipi_carrinho']['promocao']['promocao12_cont']);
      }
      header('Location: algo_mais');
    }
    break;
  case 'pedidos':
    {
        header('Location: pedidos');
    }
    break;
  case 'usar_fidelidade':
    {
        header('Location: usar_fidelidade');
  	}
  	break;
  case 'descontar_fidelidade':
  	{
        header('Location: descontar_fidelidade');
  	}
  	break;
  case 'finalizar':
  	{
  	    header('Location: algo_mais');
  	}
  	break;
  case 'bebidas':
  	{
        header('Location: bebidas');
  	}
  	break;
  case 'reposta_pagamento':
  	{
        header('Location: reposta_pagamento');
  	}
  	break;
  case 'compra_finalizada':
  	{
        header('Location: compra_finalizada');
  	}
  	break;
  case 'pagamentos':
  	{
        header('Location: pagamentos');
  	}
  	break;
  case 'fora_horario':
    {
        header('Location: pagamentos&erro_pag=1');
    }
    break;
  case 'identifique':
  	{
        header('Location: identifique');
  	}
  	break;
  case 'adicionar_bebidas':
  	{
        header('Location: algo_mais');
  	}
  	break;
  case 'adicionar_redirecionar_promocao_bebidas':
  	{
				$_SESSION["ipi_carrinho"]["pizza_promocional"] = "sim";
				$_SESSION["ipi_carrinho"]["pizza_promocional_pai"] = $id_sessao_pai.'&proximo=b';
        header('Location: pedido_promocional&codPizzaPai='.$id_sessao_pai.'&proximo=b');
  	}
  	break;
  case 'adicionar_redirecionar_promocao':
  	{
				$_SESSION["ipi_carrinho"]["pizza_promocional"] = "sim";
				$_SESSION["ipi_carrinho"]["pizza_promocional_pai"] = $id_sessao_pai;
        // Mudan\E7a para o novo processo de exclus\E3o agora passa o id_sessao
        //header('Location: pedido_promocional&codPizzaPai='.$indice_pizza);
        header('Location: pedido_promocional&codPizzaPai='.$id_sessao_pai);
  	}
  	break;
  case 'adicionar':
  	{
        header('Location: algo_mais');
  	}
  	break;
  case 'combo_redirecionar_pedido':
    {
        header('Location: algo_mais');
    }
    break;
  case 'combo_redirecionar_bebidas_pedido':
    {
        header('Location: algo_mais');
    }
    break;
    case 'adicionar_promocao_bebidas':
  	{
        header('Location: bebidas');
  	}
  	break;
  case 'combo_redirecionar_pizza':
    {
        header('Location: pedido_combo');
    }
    break;
  case 'combo_redirecionar_bebida':
    {
        header('Location: bebidas_combo');
    }
    break;
  case 'visa_erro':
    {
        header('Location: erro_visa');
    }
    break;
  case 'visa_ok':
    {
        header('Location: compra_finalizada');
    }
    break;
  default:	
  	{
        header('Location: algo_mais');
  	}
}
?>
