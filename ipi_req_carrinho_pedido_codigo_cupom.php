<?
require_once 'bd.php';
require_once 'ipi_req_carrinho_classe.php';
require_once 'sys/lib/php/formulario.php';


$nao_tenho = validar_var_post('nao_tenho');

if($nao_tenho)
{
  //require_once 'ipi_req_carrinho_pedido.php';
  $_SESSION['ipi_carrinho']['pergunta_cupom'] = "Respondida";
  echo '<script>window.location = "pedidos"</script>';
}
else
{
  $txtNumeroCupom = validaVarPost('txtNumeroCupom');
  $validar_cupom=0;
  if ($txtNumeroCupom)
  {
    if ($_SESSION['ipi_carrinho']['cupom']!="")
    {
	    echo "<script>alert('Você já utilizou um cupom neste pedido! Somente um por pedido!');</script>";
    }
    else
    {
    	$conexao = conectabd();
    	$sqlCupom = "SELECT * FROM ipi_cupons  WHERE cupom = '".$txtNumeroCupom ."'";
    	$resCupom =  mysql_query($sqlCupom);
    	$linCupom =  mysql_num_rows($resCupom);
    	$objCupom = mysql_fetch_object($resCupom);
    	//echo "<script>alert('".$objCupom->generico."');</script>";
	    if ($linCupom == 0)
	    {
		    echo "<script>alert('Número de cupom inválido ($txtNumeroCupom)!')</script>";
	    }
      else if (date("Y-m-d", strtotime($objCupom->data_inicio))>date("Y-m-d"))
      {
        echo "<script>alert('Este cupom só pode ser utilizado apartir de: ".bd2data($objCupom->data_inicio)."');</script>";
      }
	    else if (date("Y-m-d", strtotime($objCupom->data_validade))<date("Y-m-d"))
	    {
		    echo "<script>alert('Este cupom venceu em: ".bd2data($objCupom->data_validade)."');</script>";
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

        $sql_verifica_pizzaria = "SELECT pc.cod_cupons,p.cidade,p.bairro,pc.cod_pizzarias,(SELECT COUNT(*) from ipi_pizzarias_cupons where cod_cupons = c.cod_cupons and cod_pizzarias in ($cod_pizzarias)) as pizzaria_aprovada,(SELECT COUNT(*) from ipi_pizzarias where situacao='ATIVO') as total_pizzarias FROM ipi_pizzarias_cupons pc inner join ipi_cupons c on c.cod_cupons = pc.cod_cupons inner join ipi_pizzarias p on p.cod_pizzarias = pc.cod_pizzarias WHERE c.cupom = '$txtNumeroCupom'";// and pc.cod_pizzarias in($cod_pizzarias)

        //$arr_json['query'] =  utf8_encode($sql_verifica_pizzaria);
        //die();
        $res_verifica_pizzaria = mysql_query($sql_verifica_pizzaria);
        $num_registros = mysql_num_rows($res_verifica_pizzaria);
        $obj_verifica_pizzaria = mysql_fetch_object($res_verifica_pizzaria);  

        $arr_pizzarias[] = $obj_verifica_pizzaria->cod_pizzarias;
        while($obj_verifica_pizzaria = mysql_fetch_object($res_verifica_pizzaria))
        {
          $arr_pizzarias[] = $obj_verifica_pizzaria->cod_pizzarias;
        }

        $_SESSION['ipi_carrinho']['cupom_pizzarias'] = $arr_pizzarias;

        /*if($num_registros==$obj_verifica_pizzaria->total_pizzarias)
        {
          $cupom_valido = "validou";
        }
        else//$obj_verifica_pizzaria->pizzaria_aprovada && 
        {
          if($obj_verifica_pizzaria->pizzaria_aprovada==1)//$obj_verifica_pizzaria->pizzaria_aprovada && 
          {
            $cupom_valido = "validou";
            $motivo = "";//"Este cupom só pode ser utilizado na pizzaria: ".$obj_verifica_pizzaria->cidade." - ".$obj_verifica_pizzaria->bairro;
            $confirmar_acao = true;
          }
          else
          {
            
            $cupom_valido = "nao_validou";
            $motivo = "Este cupom (<b>$cupom</b>) só pode ser utilizado nas pizzarias: ".$str_pizzarias;
            $motivo = "Este cupom ($cupom) não pode ser utilizado na pizzaria $nome_pizzaria_atual , que é a pizzaria que atende seu endereço.";
            $confirmar_acao = true;
          }
        }*/

		    if ($objCupom->promocao == 1)
		    {
			    $carrinho = new ipi_carrinho();
			    if ($objCupom->produto=="PIZZA")
			    {
		        if($objCupom->generico)
		        {
	            $_SESSION['ipi_carrinho']['cupom'] = $txtNumeroCupom;
              $_SESSION['ipi_carrinho']['pergunta_cupom'] = "Respondida";
              echo '<script>window.location = "pedido_cupom&cc='.$objCupom->cod_cupons.'"</script>';
		        }
		        else
		        {
	            // Cupom específico
			        $validar_cupom=0;
			        $carrinho->adicionar_pizza_promocional($objCupom->cod_tamanhos, $objCupom->cod_produtos, $txtNumeroCupom);
			        echo '<script>window.location = "pedidos"</script>';
		        }
			    }
			    elseif ($objCupom->produto=="BORDA")
			    {
				    $validar_cupom=1;
				    echo "<script>alert('Na hora de escolher a borda recheada é só clicar no sabor. \\nEla é de graça nesta pizza!');</script>";
	          $_SESSION['ipi_carrinho']['cupom'] = $txtNumeroCupom;
            $_SESSION['ipi_carrinho']['pergunta_cupom'] = "Respondida";
            $codigo_borda = $objCupom->cod_produtos;
				   	require_once 'ipi_req_carrinho_pedido.php';
				   	$fez_require = true;
			    }
			    elseif ($objCupom->produto=="BEBIDA")
			    {
				    $validar_cupom=0;
				    if($objCupom->generico)
				    {
			    	 	$validar_cupom=1;
            	$codigo_bebida = $objCupom->cod_produtos;
				   		require_once 'ipi_req_carrinho_bebidas_cupom.php';
				   			$fez_require = true;
				    }
				    else
				    {
				  	  $carrinho->adicionar_bebida_promocional($objCupom->cod_produtos, $txtNumeroCupom);
				  	  	echo '<script>window.location = "pedidos"</script>';
				    }
				    
			    }
		    }
		    else 
		    {
    			if ($objCupom->valido==0)
    			{
    				echo "<script>alert('Cupom já utilizado ($txtNumeroCupom)!');</script>";
    			}
    			else 
			    {
				    $carrinho = new ipi_carrinho();
				
				    if ($objCupom->produto=="PIZZA")
				    {
    					if($objCupom->generico)
  				    {
			          $_SESSION['ipi_carrinho']['cupom'] = $txtNumeroCupom;
                $_SESSION['ipi_carrinho']['pergunta_cupom'] = "Respondida";
                echo '<script>window.location = "pedido_cupom&cc='.$objCupom->cod_cupons.'"</script>';
  				    }
  				    else
  				    {
  				        // Cupom específico
  					    $validar_cupom=0;
  					    $carrinho->adicionar_pizza_promocional($objCupom->cod_tamanhos, $objCupom->cod_produtos, $txtNumeroCupom);
  					    echo '<script>window.location = "pedidos"</script>';
  				    }
				    }
				    elseif ($objCupom->produto=="BORDA")
				    {
					    $validar_cupom=1;
					    echo "<script>alert('Na hora de escolher a borda recheada é só clicar no sabor. \\nEla é de graça nesta pizza!');</script>";
		          $_SESSION['ipi_carrinho']['cupom'] = $txtNumeroCupom;
              $_SESSION['ipi_carrinho']['pergunta_cupom'] = "Respondida";
              $codigo_borda = $objCupom->cod_produtos;
					 	  require_once 'ipi_req_carrinho_pedido.php';
					 	  $fez_require = true;
				    }
				    elseif ($objCupom->produto=="BEBIDA")
				    {
					    $validar_cupom=0;
					    if($objCupom->generico)
  				    {
				    	 	$validar_cupom=1;
            		$codigo_bebida = $objCupom->cod_produtos;
				   			require_once 'ipi_req_carrinho_bebidas_cupom.php';
				   			$fez_require = true;
  				    }
  				    else
  				    {
					    	$carrinho->adicionar_bebida_promocional($objCupom->cod_produtos, $txtNumeroCupom);
					    	echo '<script>window.location = "pedidos"</script>';
					    }
				    }
			    }
		    }
        
	    }
    }
  }
}
?>
<?
if(!$fez_require): ?>
<script type="text/javascript">
  function validar_cupom_ajax(frm)
  {
   /* if (typeof(cod) =="undefined")
      cod = 0;*/
    var retorno = false;
    acao = 'validar_cupom';
    $.ajax({
          url: 'ipi_req_carrinho_pedido_ajax.php',
          data: 'cupom='+frm.txtNumeroCupom.value+'&tipo='+acao,
          dataType: 'json',
          type: 'post',
          async: false,
          success: function(dados)
          {
            retorno = validar_cupom(frm);
            if(retorno)
            {
              if(dados.valido=="validou")
              {
               // $("frmCupom").submit();
                retorno =  true;
              }
              else
              {
                alert(dados["motivo"]);
                //$('#txtNumeroCupom').focus();
                //$('#txtNumeroCupom').value = "";
                retorno =  false;
              }
            }
          }
        });
    return retorno;
  }

function validar_cupom(frm)
{
  if (frm.txtNumeroCupom.value=="")
  {
    alert("Digite seu numero de cupom!");
    frm.txtNumeroCupom.focus();
    return false;
  }

  if (frm.txtNumeroCupom.value.length!=10)
  {
    alert("Número de cupom inválido!");
    frm.txtNumeroCupom.focus();
    return false;
  }

  //return validar_cupom_ajax(frm.txtNumeroCupom.value);
  return true;
}

</script>

<div class="box_pedido" id="div_cupom">
  <div class="box_pizza">
    <div class="fonte20 cor_marrom2 titulo_cupom">
      POSSUI ALGUM CÓDIGO PROMOCIONAL?
    </div>
    <div class='caixa_cupom'>
      <form id="frmCupom" method="post" action="<? echo $PHP_SELF; ?>" onsubmit="return validar_cupom_ajax(this);">
        <br /><label for="txtNumeroCupom" class="cor_marrom2 fonte22"> DIGITE AQUI </label> <br/>
        <input type="text" id="txtNumeroCupom" name="txtNumeroCupom"  maxlength="10"/> <br />
        <input type="submit" value="Validar" id="btCupom" class="btn btn-success btn_cupom1" />
      </form>
      <form id="formVoltar" method="post" action="<? echo $PHP_SELF; ?>">
        <input type="hidden" name="nao_tenho" id="voltar" value="nao_tenho" />
        <input type="submit" value="Não tenho cupom" class="btn btn-secondary btn_cupom2" />
      </form>
    </div>
    <div style="clear:both"></div>
  </div>
  
  
  <br/><br/><br/><div class="texto_bottom_cupom">*É permitido apenas um cupom por pedido.
  <br />**Os cupons não são cumulativos com as promoções.</div>
</div>
<br/><br/>
<? endif;?>
