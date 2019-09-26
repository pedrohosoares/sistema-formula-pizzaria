<?
require_once 'bd.php';
require_once 'ipi_req_carrinho_classe.php';
require_once 'sys/lib/php/formulario.php';
require_once 'pub_req_promocoes.php';
$cod_combos = validaVarGet('cod_combos');

$conexao = conectabd ();

if( (!$_SESSION['ipi_carrinho']['cep_visitante']) && (!$_SESSION['ipi_carrinho']['buscar_balcao']) )
{
  echo "<script>window.location='pedidos'</script>"; 
}

if ( (!$_SESSION['ipi_carrinho']['combo']) && (!$cod_combos) )
{
    echo "<script>window.location='cardapio'</script>";    
}

if ( ($cod_combos!="") && (!$_SESSION['ipi_carrinho']['combo']) )
{
        
    if ($_SESSION['ipi_carrinho']['id_combo_atual'])
    {
        $_SESSION['ipi_carrinho']['id_combo_atual'] = $_SESSION['ipi_carrinho']['id_combo_atual'] + 1;
    }
    else
    {
        $_SESSION['ipi_carrinho']['id_combo_atual'] = 1;
    }
    
    
    if ( (!$_SESSION['ipi_carrinho']['combo']['cod_combos']) && ($cod_combos) )
    {
        $_SESSION['ipi_carrinho']['combo']['id_combo'] = $_SESSION['ipi_carrinho']['id_combo_atual'];
        $_SESSION['ipi_carrinho']['combo']['cod_combos'] = $cod_combos;
    }
    
    $sql_combos = "SELECT * FROM ipi_combos_produtos WHERE cod_combos=".$_SESSION['ipi_carrinho']['combo']['cod_combos'];
    $res_combos = mysql_query( $sql_combos );
    $num_combos = mysql_num_rows( $res_combos );
    for ($a=0; $a<$num_combos; $a++)
    {
        $obj_combos = mysql_fetch_object( $res_combos );
        if ($obj_combos->tipo!="BORDA")
        {
                             // BEBIDAS ESPECIFICAS \\
                  $arr_cod_conteudos = array();
                  $sql_produtos_combos = "SELECT * FROM ipi_combos_produtos_bebidas WHERE cod_combos_produtos=".$obj_combos->cod_combos_produtos."";
                  $res_produtos_combos = mysql_query( $sql_produtos_combos );
                  $num_produtos_combos = mysql_num_rows( $res_produtos_combos );
                  if ($num_produtos_combos>0)
                  {
                    while ( $obj_produtos_combos = mysql_fetch_object( $res_produtos_combos ) )
                    {
                      $arr_cod_conteudos[] = $obj_produtos_combos->cod_bebidas_ipi_conteudos;
                    }
                  }

                  // PIZZAS ESPECIFICAS \\
                  $arr_cod_pizzas = array();
                  $sql_produtos_combos = "SELECT * FROM ipi_combos_produtos_pizzas WHERE cod_combos_produtos=".$obj_combos->cod_combos_produtos."";
                  $res_produtos_combos = mysql_query( $sql_produtos_combos );
                  $num_produtos_combos = mysql_num_rows( $res_produtos_combos );

                  if ($num_produtos_combos>0)
                  {
                    while ( $obj_produtos_combos = mysql_fetch_object( $res_produtos_combos ) )
                    {
                      if ($obj_produtos_combos->selecionar_produto == "POR_CODIGO")
                      {
                        $arr_cod_pizzas[] = $obj_produtos_combos->cod_pizzas;
                      }

                      elseif ($obj_produtos_combos->selecionar_produto == "PIZZA_SEMANA")
                      {
                        $cod_tamanhos = $obj_combos->cod_tamanhos;

                      }
                    }
                  }

             $_SESSION['ipi_carrinho']['combo']['produtos'][$a]['cod_conteudos'] = implode(",", $arr_cod_conteudos);
            $_SESSION['ipi_carrinho']['combo']['produtos'][$a]['cod_tamanhos']=$obj_combos->cod_tamanhos;
            $_SESSION['ipi_carrinho']['combo']['produtos'][$a]['qualidade']=$obj_combos->qualidade;
            $_SESSION['ipi_carrinho']['combo']['produtos'][$a]['preco']=$obj_combos->preco;
            $_SESSION['ipi_carrinho']['combo']['produtos'][$a]['tipo']=$obj_combos->tipo;
            $_SESSION['ipi_carrinho']['combo']['produtos'][$a]['sabor']=$obj_combos->sabor; 
            $_SESSION['ipi_carrinho']['combo']['produtos'][$a]['cod_pizzas_combo']= implode(",", $arr_cod_pizzas); 
            $_SESSION['ipi_carrinho']['combo']['produtos'][$a]['foi_pedido']='N';
        }
        else
        {
            
            if ($_SESSION['ipi_carrinho']['combo']['qtde_bordas'])
            {
                $_SESSION['ipi_carrinho']['combo']['qtde_bordas'] = $_SESSION['ipi_carrinho']['combo']['qtde_bordas'] + 1;
            }
            else
            {
                $_SESSION['ipi_carrinho']['combo']['qtde_bordas'] = 1;
            }
            
        }
    }
    
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
    echo "<script>window.location='pedidos'</script>";    
}
else
{
    if ($_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['tipo']=='BEBIDA')
    {
        echo "<script>window.location='bebidas_combo'</script>";    
    }
}
$sql_combos = "SELECT * FROM ipi_combos WHERE cod_combos=".$_SESSION['ipi_carrinho']['combo']['cod_combos'];
$res_combos = mysql_query( $sql_combos );
$obj_combos = mysql_fetch_object( $res_combos );
?>

<!-- <div style="background-image: url('upload/combos/<? echo $obj_combos->imagem_fundo; ?>'); width: 630px;"> -->

<style type="text/css">
#DivDetalhes
{
    text-align: left;
    color: #FFF;
    height: 90px;
    position: absolute;
    width: 250px;
    border: 1px solid black;
    padding: 2px;
    background-color: #EB891A;
    visibility: hidden;
    z-index: 100;
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
    /*filter: progid: DXImageTransform.Microsoft.Shadow(color=gray,direction=135);*/
}
</style>

<script type="text/javascript" src="js/detalhes.js"></script>

<div id="DivDetalhes">
</div>
<script type="text/javascript">
$(function() {
  $('.nyroModal').nyroModal();
});
function fazer_scroll(obj)
{
  var node = $(obj);
  var t = 0;
  var found = false;
  //var tName = 'a[name='+nome+']';
  var tId = '#'+$(node).attr('id');
  if (!!$(node).length){
    t = $(node).offset().top;
    if ($(node).text() == ""){
      t = $(node).parent().offset().top;
    }
    found = true;
  } else if(!!$(tId).length){
    t = $(tId).offset().top;
    found = true;
  }
  if (found){
    $("body, html").animate({scrollTop: t}, 200);
  }
}
jQuery.fn.smoothScroll = function(){
  $(this).each(function(){
    var node = $(this);
    $(node).click(function(e){
      //var nome = $(this).attr('name');
      //anchor = anchor.split("#");
      //anchor = anchor[1];
      var t = 0;
      var found = false;
      //var tName = 'a[name='+nome+']';
      var tId = '#'+$(node).attr('id');
      if (!!$(node).length){
        t = $(node).offset().top;
        if ($(node).text() == ""){
          t = $(node).parent().offset().top;
        }
        found = true;
      } else if(!!$(tId).length){
        t = $(tId).offset().top;
        found = true;
      }
      if (found){
        $("body, html").animate({scrollTop: t}, 200);
      }
      //e.preventDefault();
    });
  });

  var lAnchor = location.hash;
  if (lAnchor.length > 0){
    lAnchor = lAnchor.split("#");
    lAnchor = lAnchor[1];
    if (lAnchor.length > 0){
      $("body, html").scrollTop(0);
      var lt = 0;
      var lfound = false;
      var ltName = 'a[name='+lAnchor+']';
      var ltId = '#'+lAnchor;
      if (!!$(ltName).length){
        lt = $(ltName).offset().top;
        if ($(ltName).text() == ""){
          lt = $(ltName).parent().offset().top;
        }
        lfound = true;
      } else if(!!$(ltId).length){
        lt = $(ltId).offset().top;
        lfound = true;
      }
      if (lfound){
        $("body, html").animate({scrollTop: lt}, 200);
      }
    }
  }
}

function mostrar_detalhes(cod,acao,id)
{
	//$('#teste').nmCall(); nyroModalLoad
	//$('#teste').nm().nmCall()
	$('#pizza_detalhada').html('');
	$('#pizza_detalhada').addClass('nyroModalLoad');
	$.nmManual('#nyromodal_cardapio',{showCloseButton: false});
	$.ajax({
		url: 'ipi_req_carrinho_pedido_ajax.php',
		data: 'cod='+cod+'&tipo='+acao+'&id='+id,
		dataType: 'html',
		type: 'post',
		success: function(dados)
		{
			$('#pizza_detalhada').removeClass('nyroModalLoad');
			$('#pizza_detalhada').html(dados);
		
		}
	});
}
	
function sabor_click(num,s) 
{
		if(typeof(s) =="undefined")
		{
			$("div.sabor"+num+"_conteudo").hide();
			document.getElementById('sabor'+num+'_pizza').value = "0";
			document.getElementById('botoes_pedido').style.display ="none";
			$('#carrinho_ingredientes_texto' + num).children().find('div.texto_opcao').html('');
			$('#carrinho_ingredientes_texto' + num).children().find('a[name="btn_trocar"]').css("display","none");
			$('#carrinho_ingredientes_texto' + num).children().find('a[name="btn_escolher"]').css("display","block");
			$('input[name=\'ingredientes_adicionais'+num+'[]\']').attr('checked', false);
      fazer_scroll('#carrinho_ingredientes_texto' + num);
			$('#sabores'+num).trigger('click');

		}
		else
		{
      fazer_scroll('#carrinho_ingredientes_texto' + num);
			$('#titulo_sabor_vazio').trigger('click');
			//$('#carrinho_ingredientes_texto' + num).trigger('click');
		}
		
}

$(document).ready(function() {
		 
function atualizarIngredientes(num_sabor) {
	document.getElementById('ingrediente'+num_sabor).style.display = "block";
	
	$('ingredientes'+num_sabor).setStyle('width', '100%');
	$('ingredientes'+num_sabor).setStyle('background', 'transparent url(img/ajax_loader2.gif) no-repeat scroll 200px 20px');
	$('ingredientes'+num_sabor).setStyle('padding-top', 50);
	$('ingredientes'+num_sabor).set('html', '<center>Carregando, aguarde...</center>');
	
    // FIXME Adicionar isso novamente, porem, analizar porque com 2 sabores fica com href="javascript:;"
	//var href_botao_adicionar_pedido = $('botao_adicionar_pedido').getProperty('href');
    //$('botao_adicionar_pedido').setProperty('href', 'javascript:;');
	
	//var href_botao_adicionar_bebida = $('botao_adicionar_bebida').getProperty('href');
	//$('botao_adicionar_bebida').setProperty('href', 'javascript:;');
	
	var var_url='tipo=carregarIngredientes&cod='+document.getElementById('sabor'+num_sabor+'_pizza').value+'&tamanho_pizza='+document.getElementById('tam_pizza').value+'&num_sabor='+num_sabor+'&qtde_sabor='+document.getElementById('num_sabores').value;
	
	jQuery.ajax({
	  url: 'ipi_req_carrinho_pedido_ajax.php',
    type: "POST",
    data: var_url,
    success: function(res)
    {
      $("#ingredientes"+num_sabor).html(res);
      $('#ingredientes'+num_sabor).css('padding-top', 10);
	    $('#ingredientes'+num_sabor).css('background', 'none');
	    //$('botao_adicionar_pedido').setProperty('href', href_botao_adicionar_pedido);
	    //$('botao_adicionar_bebida').setProperty('href', href_botao_adicionar_bebida);
	  }
	});
	
	}
	
	<?
	for ($j=1; $j<=4; $j++)
	{
	?>
	$('#sabor<? echo $j; ?>_pizza').bind('change', function(e) {
		new Event(e).stop();
		atualizarIngredientes(<? echo $j; ?>);
	});
	<?
	} 
	?>
	
 
	//ACCORDION BUTTON ACTION	
	$('div.sabores_titulo').click(function() {
		$('div.sabores_conteudo').slideUp('normal');	
		$(this).next().slideDown('normal');
	});
 
	//HIDE THE DIVS ON PAGE LOAD	
	$("div.sabores_conteudo").hide();
	$("div.sabor1_conteudo").hide();
	$("div.sabor2_conteudo").hide();
	$("div.sabor3_conteudo").hide();
	$("div.sabor4_conteudo").hide();	
	
	/*
  function teste() {
    $('div.sabor1_conteudo').slideUp('normal');
		$(this).next().slideDown('normal');
  };

  $('div.sabor1_titulo').click(teste);

  */

	$('div.sabor1_titulo').click(function() {
		$('div.sabor1_conteudo').slideUp('fast');	
		$(this).next().slideDown('fast');
		
	});
	
	$('div.sabor2_titulo').click(function() {
		$('div.sabor2_conteudo').slideUp('fast');	
		$(this).next().slideDown('fast');
		
	});
	
	$('div.sabor3_titulo').click(function() {
		$('div.sabor3_conteudo').slideUp('fast');	
		$(this).next().slideDown('fast');
		
	});
	
	$('div.sabor4_titulo').click(function() {
		$('div.sabor4_conteudo').slideUp('fast');	
		$(this).next().slideDown('fast');
		
	});

	/*$('img[name="btn_trocar"]').click(function ()
	{
	   $('div.sabor4_conteudo').slideUp('normal');	
	});*/
	
 
});

function carregar_adicionais(num_sabor)
{
	var var_url='tipo=carregarAdicionais&tamanho_pizza='+document.getElementById('tam_pizza').value+'&num_sabor='+num_sabor+'&qtde_sabor='+document.getElementById('num_sabores').value;
  jQuery.ajax({
	  url: 'ipi_req_carrinho_pedido_ajax.php',
    type: "POST",
    data: var_url,
    success: function(res)
    {
      $("#adicionais"+num_sabor).html(res);
	  }
	});
}

function ccbox(eu,id)
{
  if($(id).is(':checkbox')==false)
  {
    id = $('#'+id);
  }
  if($(id).is(':checkbox') && $(eu).is(':checkbox'))
  {
    if($(eu).is(':checked'))
    {
      $(id).attr("checked",false);
    } 
  }
}


function carregar_ingredientes(num_sabor,cod_pizzas,nome_pizza)
{
	$('#carrinho_ingredientes_texto' + num_sabor).children().find('div.texto_opcao').html(nome_pizza);
	$('#carrinho_ingredientes_texto' + num_sabor).children().find('a[name="btn_trocar"]').css("display","block");
	$('#carrinho_ingredientes_texto' + num_sabor).children().find('a[name="btn_escolher"]').css("display","none");
  fazer_scroll('#carrinho_ingredientes_texto' + num_sabor);
	//$('#sabor'+num_sabor).focus();
	var center = $(window).height()/2;
  var top = $('#carrinho_ingredientes_texto' + num_sabor).offset().top ;
	if (top > center)
	{
    $('html,body').scrollTop(top-center);
  }
	document.getElementById('sabor'+num_sabor+'_conteudo').style.display = "block";
	document.getElementById('sabor'+num_sabor+'_pizza').value = cod_pizzas;
	var cont = 0; 
	if($('#sabor1_pizza').val()!="0") cont++;
  if($('#sabor2_pizza').val()!="0") cont++;
  if($('#sabor3_pizza').val()!="0") cont++;
  if($('#sabor4_pizza').val()!="0") cont++;   

  if (cont==document.getElementById('num_sabores').value)
  {
    document.getElementById('botoes_pedido').style.display ="block";
  }
  else
  {
    document.getElementById('botoes_pedido').style.display ="none";       
  }
  
	var var_url='tipo=carregarIngredientes&cod='+cod_pizzas+'&tamanho_pizza='+document.getElementById('tam_pizza').value+'&num_sabor='+num_sabor+'&qtde_sabor='+document.getElementById('num_sabores').value;
  jQuery.ajax({
	  url: 'ipi_req_carrinho_pedido_ajax.php',
    type: "POST",
    data: var_url,
    success: function(res)
    {
      $("#ingredientes"+num_sabor).html(res);
      $("#ingredientes_adicionais"+num_sabor).trigger('click');
	  }
	});
}
	

function carregarCombo(idCombo)
{
    $('#'+idCombo).addClass("carregando_formulario");
    $('#'+idCombo).empty().append('<option value="">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Carregando...<\/option>');

	var var_url;
	if (idCombo=="borda")
		var_url='tipo=carregarCombo&cod='+idCombo+'&tamanho_pizza='+document.getElementById('tam_pizza').value+'&vc='+document.getElementById('vc').value+"&sabor=<? echo $_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['sabor']; ?>" ;
	else
		var_url='tipo=carregarCombo&cod='+idCombo+'&tamanho_pizza='+document.getElementById('tam_pizza').value + "&promo=nao&sabor=<? echo $_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['sabor']; ?>" ;
		
  jQuery.ajax({
    type: "POST", 
    url: "ipi_req_carrinho_pedido_ajax.php",
    data: var_url,
    success: function(res)
    {
      $('#'+idCombo).removeClass("carregando_formulario");	
      $("#"+idCombo).html(res);
    }
  });
}


function carregarPizzas(id)
{

	var var_url;
	//if (id=="borda")
	//	var_url='tipo=carregarPizzas&cod='+id+'&tamanho_pizza='+document.getElementById('tam_pizza').value+'&vc='+document.getElementById('vc').value+"&sabor=<? echo $_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['sabor']; ?>" ;
	//else
		var_url='tipo=carregarPizzas&id_pizza='+id+'&qtde_sabores='+document.getElementById('num_sabores').value+'&tamanho_pizza='+document.getElementById('tam_pizza').value + "&promo=nao&sabor=<? echo $_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['sabor']; ?>&combo=sim" ;

	//var var_url='tipo=carregarPizzas&tamanho='+document.getElementById('tam_pizza').value+'&id_pizza='+id;

  jQuery.ajax({
    type: "POST",
    url: "ipi_req_carrinho_pedido_ajax.php",
    data: var_url,
    success: function(res)
    {

      $("#sabor_pizza_"+id).html(res);
    //    $('img.pedido_pizza_lupa').hover(
				// 	function() 
				// 	{
				// 		//		$(this).children().next(".texto_pizza_pedido_botoes").css("display","block");
				// 				$(this).parent().children(':first').css("display","block");
				// 				//$(this).parent().children('.pedido_pizza_lupa').css("display","none");
				// 				$(this).parent().children('.cor_marrom2.pedido_pizza_preco_fonte').css("display","none");
				// 	},function()
				// 	{
				// 	}
				// );
      
    //   $('div.pedido_pizzas_salgada').hover(
				// 	function() 
				// 	{
				// 	},function()
				// 	{
				// 				$(this).children(':first').css("display","none");
				// 				//$(this).children('.pedido_pizza_lupa').css("display","inline");
				// 				$(this).children('.cor_marrom2.pedido_pizza_preco_fonte').css("display","inline");
				// 	}
				// );
				
				// $('div.pedido_pizzas_doce').hover(
				// 	function() 
				// 	{
				// 	},function()
				// 	{
				// 				$(this).children(':first').css("display","none");
				// 				//$(this).children('.pedido_pizza_lupa').css("display","inline");
				// 				$(this).children('.cor_marrom2.pedido_pizza_preco_fonte').css("display","inline");
				// 	}
				// );
				
				// $('div.pedido_pizzas_fit').hover(
				// 	function() 
				// 	{
				// 	},function()
				// 	{
				// 				$(this).children(':first').css("display","none");
				// 				//$(this).children('.pedido_pizza_lupa').css("display","inline");
				// 				$(this).children('.cor_marrom2.pedido_pizza_preco_fonte').css("display","inline");
				// 	}
				// );
    }
  });
}
function tamanho_pizza(obj)
{
	if (obj.value!="0")
	{
		document.getElementById('numero_sabores').style.display = "block";
	}
	else
	{
		document.getElementById('numero_sabores').style.display = "none";
	}

	$("#num_sabores").val("0");
    verificar_num_sabores( document.getElementById('num_sabores') );
	carregarCombo('num_sabores');
	carregarCombo('borda');
	// carregarCombo('gergelim');
  // carregarCombo('tipo_massa');
  carregarCombo('corte');
	//carregarCombo('sabor1_pizza');
	//carregarCombo('sabor2_pizza');
	//carregarCombo('sabor3_pizza');
	//carregarCombo('sabor4_pizza');
	// carregarPizzas(1);
	// carregarPizzas(2);
	// carregarPizzas(3);
	// carregarPizzas(4);
}


function verificar_num_sabores(num_sabores)
{
  
  // document.getElementById('td_imagem_divisoes').style.display = "none";
  document.getElementById('titulos').style.display = "none";
  document.getElementById('sabor1').style.display = "none";
  document.getElementById('td_borda').style.display = "none";
  // document.getElementById('td_gergelim').style.display = "none";
  // document.getElementById('td_tipo_massa').style.display = "none";
  document.getElementById('td_corte').style.display = "none";
  document.getElementById('botoes_pedido').style.display ="none";

  document.getElementById('borda').value = "0";
  document.getElementById('gergelim').value = "0";
  document.getElementById('tipo_massa').value = "1";
  document.getElementById('corte').value = "0";
  document.getElementById('sabor1_pizza').value = "0";
  document.getElementById('sabor2_pizza').value = "0";
  document.getElementById('sabor3_pizza').value = "0";
  document.getElementById('sabor4_pizza').value = "0";
  for (a=1; a<=4; a++)
  {
	  obj = document.getElementById('sabor'+a);
	  obj.style.display = "none";
	  obj = document.getElementById('sabor'+a+'_conteudo');
	  obj.style.display = "none";
	  $('#carrinho_ingredientes_texto' + a).children().find('div.texto_opcao').html('')
      $('#carrinho_ingredientes_texto' + a).children().find('a[name="btn_trocar"]').css("display","none");
      $('#carrinho_ingredientes_texto' + a).children().find('a[name="btn_escolher"]').css("display","block");
  }
  carregarPizzas(1);
  carregarPizzas(2);
  carregarPizzas(3);
  carregarPizzas(4);

  carregar_adicionais(1);
  carregar_adicionais(2);	
  carregar_adicionais(3);
  carregar_adicionais(4);

  if ((num_sabores.value=="0") || (num_sabores.value==""))
  {
    // document.getElementById('imagem_divisoes').src = "img/pc/pizza_qda_div_1.png";
    document.getElementById('td_borda').style.display = "none";
    document.getElementById('borda').value = "0";
    document.getElementById('gergelim').value = "0";
    document.getElementById('num_sabores').value = "0";
  }
  else
  {
	  // document.getElementById('imagem_divisoes').src = "img/pc/pizza_qda_div_"+num_sabores.value+".png";
	  // document.getElementById('imagem_divisoes').style.display = "inline";
	  // document.getElementById('td_imagem_divisoes').style.display = "block";
	  document.getElementById('td_borda').style.display = "block";
  }
  
  return(true);
}

// function exibir_gergelim()
// {
// 	if (document.getElementById('borda').value!='0')
// 		document.getElementById('td_gergelim').style.display = "block";
// 	else
// 		document.getElementById('td_gergelim').style.display = "none";
// }
    
function exibir_tipo_massa()
{
  // if (document.getElementById('gergelim').value!='0')
    // document.getElementById('td_tipo_massa').style.display = "block";
  // else
  //   document.getElementById('td_tipo_massa').style.display = "none";
}

function exibir_cortes()
{
  // if (document.getElementById('tipo_massa').value!='0')
    document.getElementById('td_corte').style.display = "block";
  // else
  //   document.getElementById('td_corte').style.display = "none";
}


function exibir_sabores()
{
  $('div.sabores_titulo').smoothScroll();
  num_sabores = document.getElementById('num_sabores');
  document.getElementById('titulos').style.display = "block";
  for (a=1; a<=4; a++)
  {
    //alert(num_sabores.value);
    if(document.getElementById('tipo_massa').value!='0')
    {
    	//obj = document.getElementById('sabores_indice');
     // obj.style.display = "block";
      if (a<=parseInt(num_sabores.value))
      {
        obj = document.getElementById('sabor'+a);
        obj.style.display = "block";
        $('#sabores'+a).trigger('click');
        

      }
      else
      {
        obj = document.getElementById('sabor'+a);
        obj.style.display = "none";
      }
    }
    else
    {
      document.getElementById('titulos').style.display = "none";
      for (a=1; a<=4; a++)
      {
        obj = document.getElementById('sabor'+a);
        obj.style.display = "none";
        obj = document.getElementById('sabor'+a+'_conteudo');
        obj.style.display = "none";
      }
    }
  }
  fazer_scroll('#carrinho_ingredientes_texto1');
}

function verificar_pedido_atual(var_acao)
{
  if (document.getElementById("tam_pizza").value!='0')
  {
    if (validar_pizza(document.getElementById("frmCarrinho")))
    {
      document.getElementById("acao").value=var_acao;
      document.getElementById("frmCarrinho").submit();
    }
  }
  else
  {
    document.getElementById("acao").value='bebidas';
    document.getElementById("frmCarrinho").submit();
  }
}

function validar_pizza(frm)
{

  if (frm.tam_pizza.value=="0")
  {
    alert("Seleção do Tamanho é obrigatória!");
    frm.tam_pizza.focus();
    return false;
  }

  if (frm.num_sabores.value=="0")
  {
    alert("Seleção do Número de Sabores é obrigatória!");
    frm.num_sabores.focus();
    return false;
  }

  if (frm.borda.value=="0")
  {
    alert("Seleção da borda é obrigatória!\n\nSe não deseja borda selecione 'Não'!");
    frm.borda.focus();
    return false;
  }

  // if (frm.gergelim.value=="0")
  // {
  //   alert("Seleção do Gergelim é obrigatória!\n\nSe não deseja Gergelim selecione 'Não'!");
  //   frm.gergelim.focus();
  //   return false;
  // }
      
  // if (frm.tipo_massa.value=="0")
  // {
  //   alert("Seleção do Tipo de Massa é obrigatória!");
  //   frm.tipo_massa.focus();
  //   return false;
  // }

  if (frm.corte.value=="0")
  {
    alert("Seleção do Corte é obrigatória!");
    frm.corte.focus();
    return false;
  }

  for (a=1; a<=4; a++)
  {
    if (a<=parseInt(frm.num_sabores.value))
	  {
  	  obj = document.getElementById('sabor'+a+'_pizza');
  	  if (obj.value == "0")
		  {
  		  alert("Selecione o sabor da "+a+"ª parte!");
  		  obj.focus();
  		  return false;
		  }

      ingr_p = document.getElementsByName('ingredientes'+a+'[]');
      if(ingr_p.length<=0)
      {
        alert("Problema de conexão, por favor clique no botão trocar\ne escolha a pizza do "+a+"º Sabor novamente!");
        fazer_scroll('#carrinho_ingredientes_texto' +a);//obj.focus();
        return false;
      }
	  }
  }
  return true;
}

</script>

<form id='frmDesistir' method='post' action="ipi_req_carrinho_acoes.php" >
	 <input type="hidden" name="acao" value="excluir_combo">
	 <input type="hidden" name="id_combo" value="<? echo $_SESSION['ipi_carrinho']['combo']['id_combo']; ?>">
</form>    
<form id="frmCarrinho" method="post" action="ipi_req_carrinho_acoes.php" onSubmit="return validar_pizza(this);">

<input type="hidden" name="vc" id="vc" value="<? echo $validar_cupom; ?>"/>
<input type="hidden" name="nc" id="nc" value="<? echo $txtNumeroCupom; ?>"/>
<input type="hidden" name="tc" id="tc" value=""/>
	<!-- <div id='combo_nome'><img src="img/pc/pedido_combos_<? echo $_SESSION['ipi_carrinho']['combo']['cod_combos']; ?>.png" /></div> -->
  <div id="carrinho_fundo_pedido" class="box_pedido">
		
    <!-- <div id="td_imagem_divisoes" style="display: none;">
	    <div id="td_imagem_divisoes_txt" class="cor_amarelo1 fonte12">SUA PIZZA</div>
	    <div id="td_imagem_divisoes_img"><img name="imagem_divisoes" src="img/pc/pizza_qda_div_1.png" id="imagem_divisoes"></div>
    </div>
 -->
    <div id="carrinho_selecao_opcoes" class="box_pizza">
  	
      <div class="fonte22 negrito">
        COMO VOCÊ QUER SUA PIZZA?
      </div>
      <br />
      <div id="carrinho_combo_infos" class="clear fonte42 tipo_letra1 cor_marrom2">
       Nos combos você pede uma pizza de cada vez, na hora de montar cada pizza coloque seus adicionais preferidos. <b>Todos os adicionais serão cobrados a parte.</b><br/><br/><div align='center' ><a href='#' onclick='$("#frmDesistir").submit();' title='desistir do combo' class="btn btn-secondary" style="font-size: 10pt;">Cancelar Combo</a></div>
    </div><br/>
      <div id="carrinho_tam_pizza">
	      <label class="campo">Tamanho da Pizza?</label><br />
        <select name="tam_pizza" id="tam_pizza" onchange="javascript:tamanho_pizza(this);"  class="combo_select" style="width: 170px;">
          <option value="0">Selecione</option>
				  <?
				  $sqlBordas = "SELECT * FROM ipi_tamanhos t WHERE t.cod_tamanhos = '" . $_SESSION['ipi_carrinho']['combo']['produtos'][$indice_opcoes]['cod_tamanhos'] . "' ORDER BY t.tamanho";
				  //echo $sqlBordas;
				  $resBordas = mysql_query ( $sqlBordas );
				  $linBordas = mysql_num_rows ( $resBordas );
				  if ($linBordas > 0) 
			    {
					  for($a = 0; $a < $linBordas; $a ++) 
				    {
						  $objBordas = mysql_fetch_object ( $resBordas );
						  echo '<option value="'.$objBordas->cod_tamanhos.'">'.$objBordas->tamanho.'</option>';
					  }
				  }
				  ?>
        </select>
      </div>

      <div id="numero_sabores" style="display: none;">
	      <label class="campo">Quantos Sabores?</label><br />
        <select name="num_sabores" id="num_sabores" class="combo_select" onchange="javascript:verificar_num_sabores(this);" style="width: 155px">
          <option value="0">Selecione</option>
        </select>
      </div>

      <div id="td_borda" style="display: none; margin-top:30px">
	      <label class="campo">Borda Recheada?</label><br />
        <select name="borda" id="borda" class="combo_select" onchange="javascript:exibir_cortes();" style="width: 170px">
          <option value="0">Selecione</option>
        </select>
      </div>

    <!--   <div id="td_gergelim" style="display: none; margin-top:30px">
    		<label class="campo">Gergelim na Borda?</label><br />
	      <select name="gergelim" id="gergelim" class="combo_select" onchange="javascript:exibir_tipo_massa();" style="width: 155px">
          <option value="0">Selecione</option>
        </select>
      </div> -->

            <input type="hidden" name="gergelim" id="gergelim" value="N">

      <!-- <div id="td_tipo_massa" style="display: none; margin-top:30px">
        <label class="campo">Tipo da Massa?</label><br />
        <select name="tipo_massa" id="tipo_massa" class="combo_select" onchange="javascript:exibir_cortes();" style="width: 170px">
          <option value="0">Selecione</option>
        </select>
      </div> -->
      <input type="hidden" name="tipo_massa" id="tipo_massa" value="1">
      <div id="td_corte" style="display: none; margin-top:30px">
	      <label class="campo">Cortar Como?</label><br />
        <select name="corte" id="corte" class="combo_select" onchange="javascript:exibir_sabores();" style="width: 155px">
          <option value="0">Selecione</option>
        </select>
      </div>

    </div>

    
    
  
  </div>



  <div class="box_sabor">
    <table border="0" width="620">
      <tr id="titulos" style="display: none">
      </tr>
      <tr>
      <td>
     <!-- <div id='sabores_indice' style="display: none"> onChange="javascript:carregar_ingredientes(<? echo $j; ?>);" -->
          <div id='titulo_sabor_vazio' class='sabores_titulo' ></div>
		    <?
		    for($j=1; $j<=4; $j++)
		    {
		      ?>
		  		<div id="sabor<? echo $j; ?>" style="display: none;/*width:1010px;*/width:750px; border: 1px solid lightgray; border-radius: 4px; padding-top:20px; padding-left:35px;min-height:65px">
	  			<div id="carrinho_ingredientes_texto<? echo $j; ?>" class="carrinho_ingredientes_texto sabores_titulo">
	              <div id="titulo_sabor<? echo $j; ?>" class="cor_marrom2 sabores_pedido_meio">
	                  <div class='sabores_pedido_top'></div>
		                  <div name='div_num_sabor' class='fonte20 txt_pizza_ingredientes' style="width:300px; float:left">
		                       <? echo $j; ?>º SABOR
		                  </div>
		                  <div name='texto_opcao' class='texto_opcao'>
		                    
		                  </div>
		                  <div name='imagem_botao' style="width:220px; float:right; text-align:right">
	                  	    <a href='javascript:void(0)' name='btn_trocar' class='sabores_pedido_botao btn btn-secondary' style='display:none; padding: 10px 38px; margin-top:-24px' onclick='sabor_click("<? echo $j ; ?>")' >Trocar Sabor</a>
                          <a href='javascript:void(0)' name='btn_escolher' class='btn btn-secondary'>Escolher Sabor</a>
		                    <!-- <img class='sabores_pedido_botao' name='btn_escolher' src='img/pc/btn_escolher.png' alt='Escolher sabor'/> -->
		                  </div>
	                  <div class='sabores_pedido_bottom'></div>
                  </div>
                </div>		
						<div class="sabores_conteudo">
				        <input type="hidden" name="num_fracao[]" value="<? echo $j; ?>">
				        <input type="hidden" id="sabor<? echo $j; ?>_pizza" name="sabor<? echo $j; ?>_pizza" >
				        
				        <div id="sabores<? echo $j; ?>" class="sabor<? echo $j; ?>_titulo"></div>

								   <div id="sabor_pizza_<? echo $j; ?>" class="sabor<? echo $j; ?>_conteudo" >
								    <!-- por os sabores aqui -->
							   		</div>
								<div id="ingredientes_adicionais<? echo $j; ?>" class="sabor<? echo $j; ?>_titulo"></div>
										<div id="sabor<? echo $j; ?>_conteudo" class="sabor<? echo $j; ?>_conteudo" style="display: none">
										<!-- por os ingredientes aqui -->
											<div width="400" id="ingredientes<? echo $j; ?>"></div>
											<div id="adicionais<? echo $j; ?>" >	</div>
										</div>
										
									
										
										
							</div>
		      </div>
		      <?
		    }
		    ?>
	    <!--	</div> -->
    	</td>
    	</tr>
		  </table>
		 
	</div>

 <div class='div_botoes_pedido'>
  <div id='botoes_pedido' style='display:none; margin-bottom: 30px;margin-top: 25px;'>
    <input type="submit" class="btn btn-secondary" value="Adicionar ao Pedido" />
  <!--  <a href="javascript:void(0);" onClick="javascript:verificar_pedido_atual('bebidas')"><img src="img/pc/btn_pedir_bebida.png" alt="Valida e adiciona esta pizza a seu pedido!" /></a> -->
  </div>
</div>

 <input type="hidden" name="acao" id="acao" value="adicionar" />
 <div style="display: none;">
			<div id="nyromodal_cardapio">
				<div id="container_pizza" class="pizza_detalhes_div">				<div id='cardapio_fechar' class='cardapio_botao_fechar'><a title='Fechar' href='javascript:void(0)' onclick='$.nmTop().close()' > <img src='img/pc/btn_fechar.png' ></img></a></div>
					<div id="pizza_detalhada" class="pizza_detalhes_conteudo"></div>
				</div>
			</div>
		</div>
		
		 
	 <input type="hidden" name="acao" value="adicionar_pizza_combo">
	 <input type="hidden" name="indice_atual_combo" value="<? echo $indice_opcoes; ?>">
	 <input type="hidden" name="id_combo" value="<? echo $_SESSION['ipi_carrinho']['combo']['id_combo']; ?>">
 
</form>
<?
desconectabd ($conexao);
?>
