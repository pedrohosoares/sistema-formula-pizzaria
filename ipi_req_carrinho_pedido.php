<?
require_once 'bd.php';
require_once 'ipi_req_carrinho_classe.php';
require_once 'sys/lib/php/formulario.php';

$conexao = conectabd ();

$cpp = '';
if(isset($_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]))
{
  $cpro = $_SESSION["ipi_carrinho"]['promocao']["cod_promocao"];
  /*$cpp = $_SESSION["ipi_carrinho"]['promocao']["pizza_promocional_pai"];*/
}

if ($_SESSION['ipi_carrinho']['promocao']['promocao12_ativa']==1)
{
  echo '<script>location.href = "promocao&p=12" </script>';
}

require_once 'pub_req_promocoes.php';

?>

<script type="text/javascript">
$(function() 
{
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
      // var center = $(window).height()/2;
      // var top = $('#carrinho_ingredientes_texto' + num).offset().top ;
        //if (top > center)
        //{
            //if($.browser.safari) bodyelem = $("body");
             // else bodyelem = $("html,body");
           // if($.browser.chrome) bodyelem = $("body");
            //  else bodyelem = $("html,body");

           // bodyelem.scrollTop(top-center);
        //}
        //$('#carrinho_ingredientes_texto'+num).trigger('click');
      $('#sabores'+num).trigger('click');

    }
    else
    {
      fazer_scroll('#carrinho_ingredientes_texto' + (num-1));
      $('#titulo_sabor_vazio').trigger('click');

     //var center = $(window).height()/2;
     // var top = $('#carrinho_ingredientes_texto' + (num-1)).offset().top ;
         // if (top > center){
            //i//f($.browser.safari) bodyelem = $("body");
             // else bodyelem = $("html,body");
           // if($.browser.chrome) bodyelem = $("body");
           //   else bodyelem = $("html,body");

           // bodyelem.scrollTop(top-center);
         // }
      //$('#carrinho_ingredientes_texto' + num).trigger('click');
    }
    
}
$(document).ready(function() 
{
  <? 

$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
 //  if($_SESSION['ipi_carrinho']['borda_recheada'] != 'sim')
 //  {
		
	//   if($dia_semana[date("w")]=='Ter') 
	//   {
	// 			echo  'verificar_promocoes_sozinhas(2);';
	// 			$_SESSION['ipi_carrinho']['borda_recheada'] = 'sim';
	// 	}
	// }
  if(!isset($_SESSION['ipi_carrinho']['promocao']['promocao_1'] ))
  {
    if($dia_semana[date("w")]=='Seg' || $dia_semana[date("w")]=='Ter' || $dia_semana[date("w")]=='Qua' || $dia_semana[date("w")]=='Qui') 
      {

          echo  'verificar_promocoes_sozinhas(1);';
          // $_SESSION['ipi_carrinho']['borda_recheada'] = 'sim';
      }
  }

    if(!isset($_SESSION['ipi_carrinho']['promocao']['promocao_2']))
  {
    if($dia_semana[date("w")]=='Sex' || $dia_semana[date("w")]=='Sáb' || $dia_semana[date("w")]=='Dom') 
      {
          echo  'verificar_promocoes_sozinhas(2);';
          // $_SESSION['ipi_carrinho']['borda_recheada'] = 'sim';
      }
  }

  if($_SESSION['ipi_carrinho']['bacon_gratis'] != 'sim')
  {
    // $dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
    if($dia_semana[date("w")]=='Qua') 
    {
        echo  'verificar_promocoes_sozinhas(16);';
        $_SESSION['ipi_carrinho']['bacon_gratis'] = 'sim';
    }
  }


  if($_SESSION['ipi_carrinho']['desconto_balcao'] != 'sim')
  {
    ////////////////BUSCANDO CODIGO DAS PIZZARIAS//////////
    if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
    {
      $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];

      $sql_buscar_promocao = "select * from ipi_promocoes pr inner join ipi_promocoes_ipi_pizzarias pp on pp.cod_promocoes = pr.cod_promocoes where pp.cod_pizzarias = '$cod_pizzarias' and pr.cod_promocoes = '10' and pp.situacao='ATIVO'";
      //die($sql_buscar_promocao);
      $res_buscar_promocoes = mysql_query($sql_buscar_promocao);
      if(mysql_num_rows($res_buscar_promocoes)>0)
      {

        $dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');
        if($dia_semana[date("w")]=='Qua')
        {
          echo  'verificar_promocoes_sozinhas(10);';
          $_SESSION['ipi_carrinho']['desconto_balcao'] = 'sim';
        }
      }
    }
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
		$('div.sabor1_conteudo').slideUp('normal');	
		$(this).next().slideDown('normal');
		
	});
	
	$('div.sabor2_titulo').click(function() {
		$('div.sabor2_conteudo').slideUp('normal');	
		$(this).next().slideDown('normal');
		
	});
	
	$('div.sabor3_titulo').click(function() {
		$('div.sabor3_conteudo').slideUp('normal');	
		$(this).next().slideDown('normal');
		
	});
	
	$('div.sabor4_titulo').click(function() {
		$('div.sabor4_conteudo').slideUp('normal');	
		$(this).next().slideDown('normal');
		
	});
	
 
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

  $('#carrinho_ingredientes_texto' + num_sabor).children().find('div.texto_opcao').html("<br/><h1 style='text-align:center'>"+nome_pizza+"</h1>");
  $('#carrinho_ingredientes_texto' + num_sabor).children().find('a[name="btn_trocar"]').css("display","block");
  $('#carrinho_ingredientes_texto' + num_sabor).children().find('a[name="btn_escolher"]').css("display","none");
  fazer_scroll('#carrinho_ingredientes_texto' + num_sabor);
  //$('#sabor'+num_sabor).focus();
  var top = $('#carrinho_ingredientes_texto' + num_sabor).offset().top ;

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

  if(idCombo=="borda")
  {

	  var var_url='tipo=carregarCombo&cod='+idCombo+'&tamanho_pizza='+document.getElementById('tam_pizza').value + '&promo=nao&vc='+document.getElementById('vc').value+'&cpr='+document.getElementById('cpr').value;
  }
	else
  {
		var var_url='tipo=carregarCombo&cod='+idCombo+'&tamanho_pizza='+document.getElementById('tam_pizza').value + '&promo=nao';
  }
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

	var var_url='tipo=carregarPizzas&tamanho_pizza='+document.getElementById('tam_pizza').value+'&id_pizza='+id+'&qtde_sabores='+document.getElementById('num_sabores').value;
  jQuery.ajax({
    type: "POST",
    url: "ipi_req_carrinho_pedido_ajax.php",
    data: var_url,
    success: function(res)
    {
      $("#sabor_pizza_"+id).html(res);
      
      //parent().find
      
      /*
      $('img.pedido_pizza_lupa').hover(
					function() 
					{
						//		$(this).children().next(".texto_pizza_pedido_botoes").css("display","block");
								$(this).parent().children(':first').css("display","block");
								//$(this).parent().children('.pedido_pizza_lupa').css("display","none");
								$(this).parent().children('.cor_marrom2.pedido_pizza_preco_fonte').css("display","none");
					},function()
					{
					}
				);
      
      $('div.pedido_pizzas_salgada').hover(
					function() 
					{
					},function()
					{
								$(this).children(':first').css("display","none");
								//$(this).children('.pedido_pizza_lupa').css("display","inline");
								$(this).children('.cor_marrom2.pedido_pizza_preco_fonte').css("display","inline");
					}
				);
				
				$('div.pedido_pizzas_doce').hover(
					function() 
					{
					},function()
					{
								$(this).children(':first').css("display","none");
								//$(this).children('.pedido_pizza_lupa').css("display","inline");
								$(this).children('.cor_marrom2.pedido_pizza_preco_fonte').css("display","inline");
					}
				);
				
				$('div.pedido_pizzas_fit').hover(
					function() 
					{
					},function()
					{
								$(this).children(':first').css("display","none");
								//$(this).children('.pedido_pizza_lupa').css("display","inline");
								$(this).children('.cor_marrom2.pedido_pizza_preco_fonte').css("display","inline");
					}
				);
        */
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
	carregarCombo('num_sabores', false);
	carregarCombo('borda', false);
	carregarCombo('gergelim');
  // carregarCombo('tipo_massa');
  carregarCombo('corte');
	//carregarCombo('sabor1_pizza');
	//carregarCombo('sabor2_pizza');
	//carregarCombo('sabor3_pizza');
	//carregarCombo('sabor4_pizza');
	
	/*carregar_ingredientes(1);
	carregar_ingredientes(2);
	carregar_ingredientes(3);
	carregar_ingredientes(4);*/
}


function verificar_num_sabores(num_sabores)
{

  document.getElementById('td_imagem_divisoes').style.display = "none";
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
    document.getElementById('sabor'+a+'_pizza').value = "0";
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
    document.getElementById('imagem_divisoes').src = "img/pc/pizza_qda_div_1.png";
    document.getElementById('td_borda').style.display = "none";
    document.getElementById('borda').value = "0";
    document.getElementById('gergelim').value = "0";
    document.getElementById('num_sabores').value = "0";
  }
  else
  {
	  document.getElementById('imagem_divisoes').src = "img/pc/pizza_qda_div_"+num_sabores.value+".png";
	  document.getElementById('imagem_divisoes').style.display = "inline";
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
 // var center = $(window).height()/2;
 // var top = $('#carrinho_ingredientes_texto1').smoothScroll(); ;
  //if (top > center)
  //{
           // if($.browser.safari) bodyelem = $("body")
           //   else bodyelem = $("html,body")
            //if($.browser.chrome) bodyelem = $("body")
             // else bodyelem = $("html,body")

          //  bodyelem.scrollTop(top-center);
  //}
    
 // $('#carrinho_ingredientes_texto1').trigger('click');
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
        fazer_scroll('#sabor'+a+'_pizza');
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

<form id="frmCarrinho" method="post" action="ipi_req_carrinho_acoes.php" onSubmit="return validar_pizza(this);">
<input type="hidden" name="vc" id="vc" value="<? echo $validar_cupom; ?>" />
<input type="hidden" name="nc" id="nc" value="<? echo $txtNumeroCupom; ?>" />
<input type="hidden" name="cpr" id="cpr" value="<? echo $codigo_borda; ?>" />
<input type="hidden" name="cpp" id="cpp" value="<? echo $cpp; ?>" />
<input type="hidden" name="cpro" id="cpro" value="<? echo $cpro; ?>" />
<!-- <input type="hidden" name="cpp" id="cpp" value="<? echo $cpp; ?>" /> -->
<input type="hidden" name="trocar" id="trocar" value='0' />
  <div id="carrinho_fundo_pedido" class="box_pedido">

    <div id="td_imagem_divisoes" style="display: none;">
	    <div id="td_imagem_divisoes_txt" class="cor_amarelo1 fonte12">SUA PIZZA</div>
	    <div id="td_imagem_divisoes_img"><img name="imagem_divisoes" src="img/pc/pizza_qda_div_1.png" id="imagem_divisoes" /></div>
    </div>

    <div id="carrinho_selecao_opcoes" class="box_pizza">
  
      <div class="fonte22 negrito">
      <? if(count($_SESSION["ipi_carrinho"]["pedido"])>0)
      {
      	echo "QUER PEDIR MAIS UMA PIZZA?";
      }
      else
      {
       	echo "COMO VOCÊ QUER SUA PIZZA?";
      }
      ?>
      </div>
      <div id="carrinho_tam_pizza">
        <label >
  	      Tamanho da Pizza?
          <select name="tam_pizza" id="tam_pizza" onchange="javascript:tamanho_pizza(this);" style="width: 220px;">
            <option value="0">Selecione</option>
  				  <?
  				  $sqlBordas = "SELECT * FROM ipi_tamanhos t ORDER BY t.tamanho";
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
        </label>
      </div>

      <div id="numero_sabores" style="display: none;">
        <label >
          Quantos Sabores?
          <select name="num_sabores" id="num_sabores" onchange="javascript:verificar_num_sabores(this);" style="width: 220px" class="carregando_formulario">
            <option value="0">Selecione</option>
          </select>
        </label>
      </div>

      <div id="td_borda" style="display: none;">
        <label >
  	      Borda Recheada?
          <select name="borda" id="borda" onchange="javascript:exibir_cortes();" style="width: 220px">
            <option value="0">Selecione</option>
          </select>
        </label>
      </div>

<!--       <div id="td_gergelim" style="display: none;">
        <label >
      		Gergelim na Borda?
  	      <select name="gergelim" id="gergelim" onchange="javascript:exibir_tipo_massa();" style="width: 220px">
            <option value="0">Selecione</option>
          </select>
        </label>
      </div> -->
      <input type="hidden" name="gergelim" id="gergelim" value="N">

<!--       <div id="td_tipo_massa" style="display: none">
        <label >
          Tipo da Massa?
          <select name="tipo_massa" id="tipo_massa" onchange="javascript:exibir_cortes();" style="width: 220px">
            <option value="0">Selecione</option>
          </select>
        </label>
      </div> -->
      <input type="hidden" name="tipo_massa" id="tipo_massa" value="1">
      <div id="td_corte" style="display: none">
        <label >
  	      Cortar Como?
          <select name="corte" id="corte" onchange="javascript:exibir_sabores();" style="width: 220px">
            <option value="0">Selecione</option>
          </select>
        </label>
      </div>

      <div id="carrinho_combo_pedido_banner" class="clear fonte22 negrito tipo_letra1 cor_marrom2">
        <div id="carrinho_combo_banner_txt"></div>
        <div id="carrinho_combo_banner_img"><a href="cardapio&amp;tipo=combos&amp;f=laranja"><img src="imgs/peca_combo.png"></a> </div>
      </div>

    </div>

    
  
  </div>

  <div id='div_carrinho_ingredientes' class="box_sabor">
    <table border="0" width="620">
      <tr id="titulos" style="display: none">
        <td></td>
      </tr>
      <tr>
      <td>
     <!-- <div id='sabores_indice' style="display: none"> onChange="javascript:carregar_ingredientes(<? echo $j; ?>);" -->
     <div id='titulo_sabor_vazio' class='sabores_titulo ' ></div>
        <?
        for($j=1; $j<=4; $j++)
        {
          ?>
          <div id="sabor<? echo $j; ?>" style="display: none;/*width:1010px;*/width:750px; border: 1px solid lightgray; border-radius: 4px; padding-top:20px; padding-left:35px;" class="box_sabor" >
            
          <div id="carrinho_ingredientes_texto<? echo $j; ?>" class="carrinho_ingredientes_texto sabores_titulo">
                <div id="titulo_sabor<? echo $j; ?>" class="cor_marrom2 sabores_pedido_meio">

                    <div class='sabores_pedido_top'></div>

                    <div name='div_num_sabor' class='fonte20 txt_pizza_ingredientes ' style="width:300px; float:left">
                         <? echo $j; ?>º SABOR
                    </div>

                    <div name='texto_opcao' class='texto_opcao'>
                    </div>

                    <div name='imagem_botao' style="width:220px; float:right; text-align:right">
                      <a href='javascript:void(0)' name='btn_trocar' class='sabores_pedido_botao btn btn-secondary' style='display:none; padding: 10px 38px;' onclick='sabor_click("<? echo $j ; ?>")' >Trocar Sabor</a>
                      <a href='javascript:void(0)' name='btn_escolher' class='btn btn-secondary'>Escolher Sabor</a>
                     <!-- <img class='sabores_pedido_botao' name='btn_escolher' src='img/pc/btn_escolher.png' alt='Escolher sabor'/> -->
                    </div>

                    <div class='sabores_pedido_bottom'></div>
                    <br /><br />
                  </div>
                </div>  
            
            <div class="sabores_conteudo">
              
                <input type="hidden" name="num_fracao[]" value="<? echo $j; ?>" />
                <input type="hidden" class='form_text4' id="sabor<? echo $j; ?>_pizza" name="sabor<? echo $j; ?>_pizza" />
                
                <div id="sabores<? echo $j; ?>" class="sabor<? echo $j; ?>_titulo"></div>
                   <div id="sabor_pizza_<? echo $j; ?>" class="sabor<? echo $j; ?>_conteudo" >
                    <!-- por os sabores aqui -->
                    </div>
                <div id="ingredientes_adicionais<? echo $j; ?>" class="sabor<? echo $j; ?>_titulo"></div>
                    <div id="sabor<? echo $j; ?>_conteudo" class="sabor<? echo $j; ?>_conteudo" style="display: none">
                    <!-- por os ingredientes aqui -->
                      <div id="ingredientes<? echo $j; ?>" class='div_ingredientes'></div>
                      <div id="adicionais<? echo $j; ?>" >  </div>
                    </div>
                    
                  
                    
                    
              </div>
          </div>
          <?
        }
        ?>
      <!--  </div> -->
      </td>
      </tr>
      </table>
     
  </div>


  <div class='div_botoes_pedido'>
    <div id='botoes_pedido' style='display:none; margin-top: 20px;'>
      <a  href="javascript:void(0)" onClick="validar_pizza_promocao()" class="btn btn-secondary">Adicionar ao Pedido</a>
    </div>   
    <!--<a href="javascript:void(0);" onClick="javascript:verificar_pedido_atual('bebidas')"><img src="img/pc/btn_pedir_bebida.png" alt="Valida e adiciona esta pizza a seu pedido!" /></a>-->
    <br/><br/>
  </div>
	
	
	
 <input type="hidden" name="acao" id="acao" value="adicionar" />
 <div style="display: none;">
			<div id="nyromodal_cardapio">
				<div id="container_pizza" class="pizza_detalhes_div">				<div id='cardapio_fechar' class='cardapio_botao_fechar'><a title='Fechar' href='javascript:void(0)' onclick='$.nmTop().close()' > <img src='img/pc/btn_fechar.png' ></img></a></div>
					<div id="pizza_detalhada" class="pizza_detalhes_conteudo"></div>
				</div>
			</div>
		</div>
</form>
<!--
    <a href="javascript:void(0);" onClick="javascript:abrir_promocao()"><img src="img/pc/btn_mais_detalhes.png" alt="Valida e adiciona esta pizza a seu pedido!" /></a>
    <a href="javascript:void(0);" onClick="javascript:abrir_sugestao()"><img src="img/pc/btn_mais_detalhes.png" alt="Valida e adiciona esta pizza a seu pedido!" /></a>
-->    
<?
desconectabd ($conexao);
?>
