<script type="text/javascript">
$(function() {
  $('.nyroModal').nyroModal();
});
</script>
<link rel="stylesheet" type="text/css" href="plugins/nyromodal/styles/nyroModal.css">
<?
require_once 'bd.php';
require_once ("sys/lib/php/nusoap/nusoap.php");
//session_start();
if(validar_var_get("tipo") != "")
{
  $tipo = validar_var_get("tipo");
}
else
{
  $tipo = validar_var_post("tipo");
}
$arr_pizzas = array();
$arr_bordas = array();
$cod_pizzarias = $_SESSION['ipi_carrinho']['pizzaria_cardapio'];

if($_SESSION['ipi_carrinho']['cep_visitante']!="" && $tipo == "combos")
{
  $_SESSION['ipi_carrinho']['cep_cardapio'] = $_SESSION['ipi_carrinho']['cep_visitante'];
}


if( (($_SESSION['ipi_carrinho']['cep_cardapio']=="") && ($cod_pizzarias=="")))
{
	$tipo="";
}
if($tipo=="")
{
// 		echo "
//   <div id='div_tabela_nutricional'><a href='upload/tabela_nutricional.pdf' target='_blank' title='Tabela Nutricional'><img src='img/pc/tabela_nutricional.png' border='0' /></a></div>
//   <div id='div_map_cardapio'> 
//   <img src='img/pc/fundo_cardapio_map.gif' width='1024' height='640' alt='Cardápio' usemap='#map_cardapio' />
//   <map id='map_cardapio' name='map_cardapio'>

//    <img src='img/pc/fundo_cardapio_map.gif' width='1024' height='640' alt='Cardápio' usemap='#map_cardapio' />
//   <map id='map_cardapio' name='map_cardapio'>

//    <area shape ='poly' coords ='0,11,339,322,36,644,0,644' href='javascript:void(0);' onclick='enviar_form(\"Doce\",\"laranja\")' alt='Pizzas Doces' />

//    <area shape ='poly' coords ='68,0,470,365,805,0' href='javascript:void(0);' onclick='enviar_form(\"Salgado\",\"laranja\")' alt='Pizzas Salgadas' />

//    <area shape ='poly' coords ='874,0,600,340,940,644,1024,644,1024,0' href='javascript:void(0);' onclick='enviar_form(\"Fit\",\"fit\")' alt='Pizzas Fit' />
//   </map>
// </div>";

  echo '
  <nav style="text-align: center; width:100%;"> <ul >
                    <li>
                        <a href="#" onclick=\'enviar_form("Salgado","laranja")\'>
                            Salgadas
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick=\'enviar_form("Doce","laranja")\'>
                            Doces
                        </a>
                    </li>
                     <li>
                        <a href="#" onclick=\'enviar_form("Calzone","laranja")\'>
                            Calzone
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick=\'enviar_form("bebidas","laranja")\'>
                            Bebidas
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick=\'enviar_form("combos","laranja")\'>
                            Combos
                        </a>
                    </li>
                </ul></nav>';
}
else
{
	if ( (($_SESSION['ipi_carrinho']['cep_cardapio']!="") && ($cod_pizzarias) ) && $tipo!="")
	{
            if( $tipo=="Salgado")
            {
              buscaPizza(1,$cod_pizzarias, $tipo); 
            }
            if( $tipo=="Doce")
            {
              buscaPizza(4,$cod_pizzarias, $tipo); 
            }
		if( $tipo=="Calzone")
		{
			buscaPizza(5,$cod_pizzarias, $tipo); 
		}

            if($tipo=="bebidas")
            {
              buscaBebida($cod_pizzarias);
            }
			

            if ($tipo=="combos") 
            {
                         if($_SESSION['ipi_carrinho']['combo'])
                        {
                          erro_combo_aberto($cod_pizzarias);
                        } 
                        else
                        {
                          buscaCombo($tipo,$cod_pizzarias) ;
                        }
                
            }


       

	}
	elseif($_SESSION['ipi_carrinho']['cep_cardapio']!="" && $tipo!="")
	{
	  $cep_visitante = $_SESSION['ipi_carrinho']['cep_cardapio'];
	  $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
	  $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
		$res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
		$obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
		$cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
	  //echo 'alert("'.$cod_pizzarias.'");';
	
            if( $tipo=="Salgado")
            {
              buscaPizza(1,$cod_pizzarias,$tipo); 
            }
            if( $tipo=="Doce")
            {
              buscaPizza(4,$cod_pizzarias,$tipo); 
            }
            if( $tipo=="Calzone")
            {
              buscaPizza(5,$cod_pizzarias,$tipo); 
            }
		else
		{
			switch($tipo)
			{
				case 'bebidas' :
						buscaBebida($cod_pizzarias);
					break;
				case 'combos' :
            if($_SESSION['ipi_carrinho']['combo'])
            {
              erro_combo_aberto($cod_pizzarias);
            } 
            else
						buscaCombo($tipo,$cod_pizzarias) ;
					break;				
				case 'borda':
						buscaBordas ($tipo,$cod_pizzarias);
					break;
			}
		}
	}
}


function filtrar_caracteres($mensagem)
{
    $mensagem = str_replace("\r", '', $mensagem);
    $mensagem = str_replace("\n", '', $mensagem);
    
    $mensagem = str_replace('&', '', $mensagem);
    $mensagem = str_replace('<', '', $mensagem);
    $mensagem = str_replace('<', '', $mensagem);
    
    return $mensagem;
}
  function erro_combo_aberto($cod_pizzarias)
  {
		$conteudo = '<div style="text-align:center"></br></br><br/>';
    $con = conectar_bd();
    $sql_busca_combos_aberto = "SELECT * FROM ipi_combos c inner join ipi_combos_pizzarias cop on cop.cod_combos = c.cod_combos where c.situacao='ATIVO' and cop.cod_pizzarias = '".$cod_pizzarias."' and c.cod_combos = ".$_SESSION['ipi_carrinho']['combo']['cod_combos']." order by ordem_combo";  
    //echo $sql_busca_combos_aberto;  
    $res_busca_combos_aberto = mysql_query($sql_busca_combos_aberto);
    $obj_busca_combos_aberto = mysql_fetch_object($res_busca_combos_aberto);
    $conteudo .= '<form id="frmDesistir" method="post" action="ipi_req_carrinho_acoes.php" >
   <input type="hidden" name="acao" value="excluir_combo">
   <input type="hidden" name="id_combo" value='.$_SESSION["ipi_carrinho"]["combo"]["id_combo"].'></form>';

    $conteudo .= '<p class="fonte_muzza1_negrito fonte16 cor_marrom2">Existe um '.$obj_busca_combos_aberto->nome_combo.' Aberto</p>';
    $conteudo .= '<span>Deseja cancelar ou continuar pedindo?<br/><p>&nbsp;</p><div align="center" ><a href="#" onclick="$(\'#frmDesistir\').submit();" title="desistir do combo"><img src="img/pc/btn_cancelar_combo.png" alt="Cancelar Combo"/></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="pedidos" title="Continuar Pedindo"><img src="img/pc/btn_continuar.png" alt="Continuar Pedindo"/></a></div></span>';
    $conteudo .= "<p>&nbsp;</p><p>&nbsp;</p><img class='imagem_combo' alt='Imagem do ".$obj_busca_combos_aberto->nome_combo."' src='upload/combos/".$obj_busca_combos_aberto->imagem_final."'></img>";

    $conteudo .= '<div class="clear"></div></div>';
    echo $conteudo;
    desconectar_bd($con);
  }
  function buscaPizza ($cod_tipo_pizza, $cod_pizzarias, $tipo)
  {
    $arr_pizzas = array();
    $con = conectabd();
    $conteudo="";
    // $conteudo .= '<div >Veja Aqui:</p><a href="cardapio_lista&amp;f=laranja" title="Veja o cardapio no formato de lista" class="float_right"> <img src="img/pc/btn_cardapio_antigo.png" alt="Veja o cardapio no formato de lista" /> </a></div>';

    $conteudo .= '<h1> '. ucfirst($tipo) . '</h1>';
    $conteudo .= '<br />';

  if ($cod_tipo_pizza == 1)
  {
      $SqBuscalPizzas = "SELECT * FROM ipi_pizzas p inner join ipi_pizzas_ipi_tamanhos pt on pt.cod_pizzas = p.cod_pizzas inner join ipi_tipo_pizza tp on tp.cod_tipo_pizza = p.cod_tipo_pizza WHERE pt.cod_pizzarias = '$cod_pizzarias' and tp.cod_tipo_pizza in (1,2,3) AND p.venda_online = 1 group by p.pizza ORDER BY p.pizza "; 
  }
  else
  {
      $SqBuscalPizzas = "SELECT * FROM ipi_pizzas p inner join ipi_pizzas_ipi_tamanhos pt on pt.cod_pizzas = p.cod_pizzas inner join ipi_tipo_pizza tp on tp.cod_tipo_pizza = p.cod_tipo_pizza WHERE pt.cod_pizzarias = '$cod_pizzarias' and tp.cod_tipo_pizza='$cod_tipo_pizza' AND p.venda_online = 1 group by p.pizza ORDER BY p.pizza ";
  }

    // echo "<br>SqBuscalPizzas: ".$SqBuscalPizzas;
    //echo $SqBuscalPizzas;
    $resBuscaPizzas = mysql_query($SqBuscalPizzas);
    //echo "SQLSLQLSL".$SqBuscalPizzas;
    $conteudo .="<div id='cardapio_tudo'>";
    while ($objBuscaPizzas = mysql_fetch_object($resBuscaPizzas))
    {
    	  $arr_pizzas[] = $objBuscaPizzas->cod_pizzas;
        if ($objBuscaPizzas->pizza_semana==1)  //pizza_gostosa_p_03.png.jpg
        {
        	$conteudo .= "<div align='center' class='cardapio_pizzas'>";
       		$conteudo .="<div class='texto_pizza_semana'><a href='javascript:void(0)' onClick='mostrar_detalhes(".$objBuscaPizzas->cod_pizzas.",\"mostrar_detalhes\")' ><img src='img/pc/pizza_semana_texto.png' alt='Esta pizza esta na promoção da pizza da semana' ></img></a></div>";
        }
        else if ($objBuscaPizzas->pizza_dia==1)  //pizza_gostosa_p_03.png.jpg
        {
          $conteudo .= "<div align='center' class='cardapio_pizzas'>";
          $conteudo .="<div class='texto_pizza_semana'><a href='javascript:void(0)' onClick='mostrar_detalhes(".$objBuscaPizzas->cod_pizzas.",\"mostrar_detalhes\")' ><img src='img/pc/pizza_dia_texto.png' alt='Esta pizza esta na promoção da pizza da semana' ></img></a></div>";
        }
        else if ($objBuscaPizzas->novidade==1) 
        {
          $conteudo .= "<div align='center' class='cardapio_pizzas'>";
          $conteudo .="<div class='texto_pizza_semana'><a href='javascript:void(0)' onClick='mostrar_detalhes(".$objBuscaPizzas->cod_pizzas.",\"mostrar_detalhes\")' ><img src='img/pc/produto_novidade.png' alt='Esta pizza esta na promoção da pizza da semana' ></img></a></div>";
        }
        else
        {
        	$conteudo .= "<div align='center' class='cardapio_pizzas".($tipo=="Doce" ? "_doce" : ($tipo=="Fit" ? "_fit" : ""))."'>";
        }
      //   $conteudo .= "<div align='center' class='cardapio_pizzas'>";
		       $conteudo .= "<div class='div_pizza' style='float:left; margin-left: 40px; width: 180px; height: 210px; */border:1px solid*/; margin-top: 15px;'>";

						   $conteudo .= '<a href="javascript:void(0)" onClick="mostrar_detalhes('.$objBuscaPizzas->cod_pizzas.',\'mostrar_detalhes\')" ><img class="cardapio_miniatura" width="170" id="pizzas_'.$objBuscaPizzas->cod_pizzas.'" '.($objBuscaPizzas->foto_pequena ? "src=\"upload/pizzas/".$objBuscaPizzas->foto_pequena."\"" :"src=\"upload/pizzas/pizza_temp.jpg\"" ).' /></a>'; //cardapio_menu_doces.png
						   
						   $conteudo .= "<p class='center'>".mb_strtoupper($objBuscaPizzas->pizza)."</p>"; //upload/pizzas/
		       $conteudo .= "</div>";
         $conteudo .= "</div>";
    }
    echo "<script  type='text/javascript'>var arr_cods = new Array(".implode($arr_pizzas,',').");</script>";
    $conteudo .= "</div>";  
    echo $conteudo;
    desconectabd($con);
  }

  function buscaCombo ($tipo,$cod_pizzaria)
  {
		$con = conectabd();
    $sql_busca_combos = "SELECT * FROM ipi_combos c inner join ipi_combos_pizzarias cop on cop.cod_combos = c.cod_combos where c.situacao='ATIVO' and cop.cod_pizzarias = '$cod_pizzaria' order by ordem_combo DESC";
    //echo $sql_busca_combos;
    echo '<h1>'. ucfirst($tipo) . '</h1>';		
    $res_busca_combos = mysql_query($sql_busca_combos);
    echo "<div id='cardapio_tudo' align='center' class='cardapio_fundo_branco'>"; //pedido_combo&cod_combos=2
    while ($obj_busca_combos = mysql_fetch_object($res_busca_combos))
    {
    	echo "<div id='div_combo".$obj_busca_combos->cod_combos."'>";
      //echo "<a class='botao_pedir_combo' title='Pedir o  combo ".$obj_busca_combos->nome_combo."' href='pedido_combo&cod_combos=".$obj_busca_combos->cod_combos."' ><img alt='Pedir esse combo' src='img/pc/btn_pedir_g.png'/></a>";

      echo "<a class='botao_pedir_combo' title='Pedir o  combo ".$obj_busca_combos->nome_combo."' href='pedido_combo&cod_combos=".$obj_busca_combos->cod_combos."' >";

			echo "<img class='imagem_combo' alt='Imagem do ".$obj_busca_combos->nome_combo."' src='upload/combos/".$obj_busca_combos->imagem_final."?".(date("His"))."'></img>";
      echo "</a>";
  		echo "</div>";
    }
    echo "<div class='clear'>";
    echo "<br />* Valor referente ao desconto calculado na compra das pizzas e bordas de maior valor.";
    echo "<br />** Os combos não são válidos para os sabores Muçarela e Calabresa.";
    echo "<br />*** Os combos não são cumulativos com outros promoções.";
    echo "</div></div>";
    desconectabd($con);
		
  } 
  
  function buscaBordas ($tipo,$cod_pizzarias)
  {
    $con = conectabd();
    $conteudo="";
    $conteudo .= '<div class="float_right div_btn_troca_vizualizacao"><p class="fonte_muzza1 cor_marrom2 fonte18 float_left">Veja Aqui:</p><a href="cardapio_lista&amp;f=laranja" title="Veja o cardapio no formato de lista" class="float_right"> <img src="img/pc/btn_cardapio_antigo.png" alt="Veja o cardapio no formato de lista" /> </a></div>';
    $conteudo .= '<h1 >'. ucfirst($tipo) . 'S</h1>';
    
    $conteudo .= '<br />';
    $SqBuscalPizzas = "SELECT b.* FROM ipi_bordas b inner join ipi_tamanhos_ipi_bordas tb on tb.cod_bordas = b.cod_bordas  and tb.cod_pizzarias = $cod_pizzarias group by b.borda ORDER BY b.borda";

    //echo "<br>SqBuscalPizzas: ".$SqBuscalPizzas;
    $resBuscaPizzas = mysql_query($SqBuscalPizzas);
    //echo "SQLSLQLSL".$SqBuscalPizzas;
    $conteudo .="<div id='cardapio_tudo'>";
    while ($objBuscaPizzas = mysql_fetch_object($resBuscaPizzas))
    {
    	  $arr_bordas[] = $objBuscaPizzas->cod_bordas;
        	$conteudo .= "<div align='center' class='cardapio_pizzas_borda'>";
      //   $conteudo .= "<div align='center' class='cardapio_pizzas'>";
		       $conteudo .= "<div class='div_pizza'>";
		       
		         $sql_busca_preco = "SELECT * FROM ipi_tamanhos_ipi_bordas tb INNER JOIN ipi_tamanhos t ON (tb.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_bordas b ON (tb.cod_bordas = b.cod_bordas) WHERE b.cod_bordas = " . $objBuscaPizzas->cod_bordas . " and tb.cod_pizzarias = $cod_pizzarias ORDER BY tamanho";
		         //echo $sql_busca_preco;
             $res_busca_preco = mysql_query($sql_busca_preco);
		       
		           $conteudo .= "<div class='hover_bebidas_bordas'>";  
		           while ($obj_busca_preco = mysql_fetch_object($res_busca_preco))
		           {
		             $arr_tamanho = explode('(', $obj_busca_preco->tamanho);
		             $conteudo .= '<p class="cor_branco">'.$arr_tamanho[0].'</p><strong class="cor_branco">R$ '.bd2moeda($obj_busca_preco->preco).'</strong><br/>';
		           }
		           $conteudo .= "</div>";


		           $sql_busca_foto = "SELECT * FROM ipi_tamanhos_ipi_bordas tb INNER JOIN ipi_bordas b ON (tb.cod_bordas = b.cod_bordas) WHERE b.cod_bordas = " . $objBuscaPizzas->cod_bordas . " and tb.cod_pizzarias = $cod_pizzarias";
		           //echo $sql_busca_foto;
               $res_busca_foto = mysql_query($sql_busca_foto);
               $obj_busca_foto = mysql_fetch_object($res_busca_foto);

						   $conteudo .= '<a href="javascript:void(0)" onClick="mostrar_detalhes('.$obj_busca_foto->cod_bordas.',\'mostrar_detalhes_borda\')" ><img class="cardapio_miniatura_borda '.((mb_strtoupper($obj_busca_foto->borda) == "CATUPIRY ORIGINAL" || mb_strtoupper($obj_busca_foto->borda) == "CATUPIRY LIGHT") ? "catupiry" : "").'"  id="borda_'.$obj_busca_foto->cod_bordas.'" src="upload/bordas/'.$obj_busca_foto->foto_pequena.'" /></a>'; //cardapio_menu_doces.png
						   $conteudo .= "<p class='cor_marrom2 cardapio_pizza_nome_fonte'>".mb_strtoupper($obj_busca_foto->borda)."</p>"; //upload/pizzas/
						   
		       $conteudo .= "</div>";
         $conteudo .= "</div>";
    }
    echo "<script  type='text/javascript'>var arr_cods = new Array(".implode($arr_bordas,',').");</script>";
    $conteudo .= "</div>";  
    echo $conteudo;
    desconectabd($con);
  }

  function buscaBebida ($cod_pizzarias)
  {
    $con = conectabd();
    $conteudo="";

    $conteudo .= '<h1>Bebidas</h1>';
    
    $conteudo .= '<br />';
		$SqBuscalPizzas = "SELECT * FROM ipi_bebidas_ipi_conteudos cb inner join ipi_conteudos_pizzarias p on p.cod_bebidas_ipi_conteudos = cb.cod_bebidas_ipi_conteudos INNER JOIN ipi_bebidas b ON (b.cod_bebidas = cb.cod_bebidas) inner join ipi_conteudos c on c.cod_conteudos = cb.cod_conteudos WHERE p.cod_pizzarias = $cod_pizzarias AND p.venda_net=1 ORDER BY b.bebida";//

   // echo "<br>SqBuscalPizzas: ".$SqBuscalPizzas;
    $resBuscaPizzas = mysql_query($SqBuscalPizzas);
    //echo "SQLSLQLSL".$SqBuscalPizzas;
    $conteudo .="<div id='cardapio_tudo'>";
    while ($objBuscaPizzas = mysql_fetch_object($resBuscaPizzas))
    {
    	 // $arr_pizzas[] = $objBuscaPizzas->cod_bebi;
         $conteudo .= "<div align='center' style=\"float:left; margin: 40px;\">";
		       

               $conteudo .= "<div class='cardapio_miniatura_bebida_imagem'>";

                                        if ($objBuscaPizzas->foto_pequena)
                                        {
                                           $conteudo .= '<img   height="100" width="50" id="bebida_'.$objBuscaPizzas->cod_bebidas.'" src="upload/bebidas/'.$objBuscaPizzas->foto_pequena.'" alt='.$objBuscaPizzas->bebida.' '.($objBuscaPizzas->conteudo=="Lata" ? 'em ' : '').''.$objBuscaPizzas->bebida.'/>'; 
                                        }
                                        else
                                        {
                                           $conteudo .= '<img height="100" width="50" src="img/cocapet.png" />';
                                        }
						  
						   $conteudo .= "</div>";

               // echo '<div class="fonte11"><strong>'.$objBebidas->bebida.'</strong><br/><span class="fonte10">'.$objBebidas->conteudo.'<br/>(R$ '.bd2moeda($objBebidas->preco).')</span></div>';
               $conteudo .= "<div style='font-size:11px;'><strong>".mb_strtoupper($objBuscaPizzas->bebida)."</strong><br/><span  style='font-size:10px;'>(R$".bd2moeda($objBuscaPizzas->preco).")<br/></span></div>"; 
		      
         $conteudo .= "</div>";
    }
   // echo "<script>var arr_pizzas = new Array(".implode($arr_pizzas,',').");</script>";
    $conteudo .= "</div>";  
    echo $conteudo;
    desconectabd($con);
  }



  $encontrou=false;
  $cep_visitante = validaVarPost('cep_visitante');
  //echo "SESSAO".$_SESSION['ipi_carrinho']['cep_visitante'];
  if ( (($_SESSION['ipi_carrinho']['cep_cardapio']!="")) || ($_SESSION['ipi_carrinho']['pizzaria_cardapio']!=""))
  {
    if ($_SESSION['ipi_carrinho']['cep_cardapio'])
      $cep_visitante = $_SESSION['ipi_carrinho']['cep_cardapio'];

    if ($_SESSION['ipi_carrinho']['pizzaria_cardapio'])
      $cps = $_SESSION['ipi_carrinho']['pizzaria_cardapio'];

    //$cod_ingredientes = validaVarPost('cod_ingredientes');
	  $encontrou=false;
	  $con = conectabd();
	  $contagem = 0;
	  $arr_cod_pizzarias = array();

    $cps = $_SESSION['ipi_carrinho']['pizzaria_cardapio'];
    if($cps)
    {
      $cod_pizzarias = $cps;
      $encontrou=true;  
    }
		if($cod_pizzarias=="")
		{
			if($cep_visitante)
			{
				$cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
				$objCep = executaBuscaSimples("SELECT COUNT(*) AS contagem FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo", $con);
				$contagem = $objCep->contagem; 
				$con = conectar_bd();
				$sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
				$res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
		    $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
		    $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
		   // echo $sql_cod_pizzarias;
				while($obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias))
				{
					$arr_cod_pizzarias[]['cod_pizzarias'] = $obj_cod_pizzarias->cod_pizzarias;
					//echo "</br>CCCCCCCCCCCCccc ".$cod_pizzarias."cccccCC";
				}
			}
		}
  }


	
  if ($cod_pizzarias=="")
  {
    $encontrou=false;
  }
  else
  {
		$encontrou=true;
  	?>
  	<script type="text/javascript">
  	function mostrar_detalhes(cod,acao)
  	{
  		//$('#teste').nmCall(); nyroModalLoad
  		//$('#teste').nm().nmCall()
  		$('#pizza_detalhada').html('');
  		$('#pizza_detalhada').addClass('nyroModalLoad');
			$.nmManual('#nyromodal_cardapio',{showCloseButton: false});
  		$.ajax({
				url: 'ipi_req_cardapio_ajax.php',
				data: 'cod='+cod+'&acao='+acao+'&arr_cods='+arr_cods+'&cod_pizzarias=<?
				echo $cod_pizzarias ;?> ',
				dataType: 'html',
				type: 'post',
				success: function(dados)
				{
					$('#pizza_detalhada').removeClass('nyroModalLoad');
					$('#pizza_detalhada').html(dados);
				
				}
			});
  	}
  	
  	function mostrar_detalhes_sem_nodal(cod,acao)
  	{
  		//$('#teste').nmCall();
  		//$('#teste').nm().nmCall()
  			$('#pizza_detalhada').html('');
  			$('#pizza_detalhada').addClass('nyroModalLoad');
  		$.ajax({
				url: 'ipi_req_cardapio_ajax.php',
				data: 'cod='+cod+'&acao='+acao+'&arr_cods='+arr_cods+'&cod_pizzarias=<?
				echo $cod_pizzarias ;?> ',
				dataType: 'html',
				type: 'post',
				success: function(dados)
				{
						$('#pizza_detalhada').removeClass('nyroModalLoad');
					$('#pizza_detalhada').html(dados);
				}
			});
  	}
  	
  	
  	function enviar_form(tipo,fundo)
  	{
			/*var form = new Element('form', {
				'action': '<?
				echo $PHP_SELF; ?>',
				'method': 'post'
			});
				document.createElement('div'),
			var input = new Element('input', {
				'type': 'hidden',
				'name': 'tipo',
				'value': tipo
			});
			
			var input2 = new Element('input', {
				'type': 'hidden',
				'name': 'f',
				'value': 'laranja'
			});
			input.inject(form);
			input2.inject(form);
			$(document.body).adopt(form);
			*/
			
			var form = $('<form />').attr({'action': '<? echo validar_var_get('pagina'); ?>','method': 'post','method': 'post'});
			var input =$('<input type="text">').attr({'name': 'tipo','value': tipo});
			var input2= $('<input type="text">').attr({'name': 'f','value': fundo});
			<?
				if($cod_pizzarias!="")
					echo "var input3= $('<input type=\"text\">').attr({'name': 'pizzaria','value': ".$cod_pizzarias."});form.append(input3);";
			
			?>
			form.append(input);
			form.append(input2);
			$(document.body).append(form);
			form.submit();
		}
  	
  	</script>
  	<div style="display: none;">
			<div id="nyromodal_cardapio" >
				<div id="container_pizza" class="pizza_detalhes_div" style="height: 500px; width: 1000px; border:1px solid; background-color: white; border-radius: 4px">				<div id='cardapio_fechar' class='cardapio_botao_fechar'><button  onclick='$.nmTop().close()' type="button" class="fechar_modal" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></div>
					<div id="pizza_detalhada" class="pizza_detalhes_conteudo" style="padding: 10px;"></div>
				</div>
			</div>
		</div>
		<?
  
}

/*echo ($encontrou ? 'ENCONTRO' : "AAAAAAAAAA" );
echo "CP".$cp."   COD ".$cod_pizzarias;*/

if ($encontrou==false)
{
  ?>
  <script type="text/javascript">
  $(document).ready(function () 
  {
    $.nmManual('#nyromodal_cardapio',{showCloseButton: false});
  });
    
  function buscar_cep_1(id,acao)
	{
		cep = document.getElementById(id).value;
		buscar_cep(cep,acao)
	}  

  function salvar_cep(cod_pizzarias)
  {
    $.nmTop().close();
    location.reload(true);
  }

  function buscar_cep_2(id,acao)
  {
    //alert(cep);
    cp = document.getElementById(id).value;
    $('#cardapio_janela_cep_cont').html('');
    $('#cardapio_janela_cep_cont').addClass('nyroModalLoad');
    $.ajax({
      url: 'ipi_req_cardapio_ajax.php',
      data: 'cp='+cp+'&acao='+acao,
      dataType: 'html',
      type: 'post',
      success: function(dados)
      {
          $('#cardapio_janela_cep_cont').removeClass('nyroModalLoad');
        $('#cardapio_janela_cep_cont').html(dados);
      }
    });
  }  
	function buscar_cep(cep,acao)
	{
    //$('#teste').nmCall();
    //$('#teste').nm().nmCall()
		$('#cardapio_janela_cep_cont').html('');
		$('#cardapio_janela_cep_cont').addClass('nyroModalLoad');
		$.ajax({
			url: 'ipi_req_cardapio_ajax.php',
			data: 'cep_visitante='+cep+'&acao='+acao,
			dataType: 'html',
			type: 'post',
			success: function(dados)
			{
					$('#cardapio_janela_cep_cont').removeClass('nyroModalLoad');
				$('#cardapio_janela_cep_cont').html(dados);
			}
		});
	}

	function enviar_form(tipo,fundo)
	{
		var cod_pizzaria = document.getElementById('cod_pizzaria_cep')
		if(cod_pizzaria.value!="")
		{
			var form = $('<form />').attr({'action': '<? echo validar_var_get('pagina'); ?>','method': 'post','method': 'post'});
			var input =$('<input type="text">').attr({'name': 'tipo','value': tipo});
			var input2= $('<input type="text">').attr({'name': 'f','value': fundo});
			var input3= $('<input type="text">').attr({'name': 'pizzaria','value': cod_pizzaria.value});
			form.append(input3);
			form.append(input);
			form.append(input2);
			$(document.body).append(form);
			form.submit();
		}
		else
		{		
			tipo="semcep";
			var form = $('<form />').attr({'action': '<? echo validar_var_get('pagina'); ?>','method': 'post','method': 'post'});
			var input =$('<input type="text">').attr({'name': 'tipo','value': tipo});
			form.append(input);
			$(document.body).append(form);
			form.submit();
		}
	}
  </script>

  <div style="display: none;">
    <div id="nyromodal_cardapio">
      <div id="cardapio_janela_cep" class='cardapio_janela_cep' style="background-color: #FFCC08;border-radius:4px; text-align: left; "> 
        <div id='cardapio_janela_cep_cont' class='cardapio_janela_cep_cont'>
         <br/><br/><br/>Nosso cardápio é regionalizado, digite seu CEP e <br />iremos verificar a disponibilidade de entrega:<br />
          <h1>SAIBA SE ENTREGAMOS EM SUA CASA</h1>
          <div id="campo_cep">
          <label for="cep_visitante" class='cardapio_texto_digite_cep font_muzza1'>Digite seu CEP:</label>&nbsp;&nbsp;<input type="text" class='form_textCEP' name="cep_visitante" id="cep_visitante" value="<? echo $_SESSION['ipi_carrinho']['cep_visitante'] ;?>" size="10" onkeypress="return MascaraCEP(this, event);"/>&nbsp;
          &nbsp;<a href='javascript:void(0)' onclick='if($("#cep_visitante").val().length==9){buscar_cep_1("cep_visitante","buscar_cep")}else{alert("Formato de CEP Inválido")}' name="btEnviarCep" class="btn btn-success enviar_cep_modal">Verificar</a></div><br/><br/><br/>
        </div> 
        <!--<pre>
        <? //print_r($_SESSION); ?>
        </pre>-->
      </div>
    </div>
  </div>
  <input type="hidden" id="cod_pizzaria_cep" name="cod_pizzaria_cep" readonly="readonly" />			
  <?
}
?>
