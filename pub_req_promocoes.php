<?
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';

?>

<script type="text/javascript">
	function verifica_sabor_doce(arr_sabores)
	{
		acao = 'verificar_sabor_doce';
		//alert(arr_sabores);
		$.ajax({
			url: 'pub_req_promocoes_ajax.php',
			data: 'arr='+arr_sabores+'&acao='+acao,
			dataType: 'json',
			 async: false,
			type: 'post',
			success: function(dados)
			{
				//alert(dados["resposta"]);
				if(dados["resposta"]=="sim")
				{
					verificar_promocoes(3);
				}
				else
				{
				  verificar_promocoes(7);
				}
			}
		});
	}
	
	function validar_pizza_promocao()
	{

		if(validar_pizza(document.getElementById('frmCarrinho')))
		{
			if($('#tam_pizza').val() == 3 && $('#cpro').val() =="")
			{
				arr_sabores = new Array();
				if($('#sabor1_pizza').val()!=0)
				arr_sabores.push($('#sabor1_pizza').val());
				
				if($('#sabor2_pizza').val()!=0)
				arr_sabores.push($('#sabor2_pizza').val());
				
				if($('#sabor3_pizza').val()!=0)
				arr_sabores.push($('#sabor3_pizza').val());
				
				if($('#sabor4_pizza').val()!=0)
				arr_sabores.push($('#sabor4_pizza').val());
				
				//var retorno = verifica_sabor_doce(arr_sabores);
				//alert('retorno  '+retorno);
				//verifica_sabor_doce(arr_sabores);
				verifica_sabor_doce(arr_sabores)
			}
			else
			{
				verificar_promocoes();
			}
		}
	}
	
	function checkar_trocar()
	{
		if($('#trocar').val()=='0')	
		{
			$('#trocar').val('1');
		}else
			$('#trocar').val('0');
	}
	
	function verificar_promocoes_sozinhas(cod,acao,pizza_pai)
	{
		if (typeof(cod) =="undefined")
			cod = 0;

		if (typeof(acao) =="undefined")
			acao = 'carregar_promocao';

		if (typeof(pizza_pai) =="undefined")
			pizza_pai = '';
		//acao = 'carregar_promocao';
		$.ajax({
					url: 'pub_req_promocoes_ajax.php',
					data: 'cod='+cod+'&acao='+acao+'&pizza_pai='+pizza_pai,
					dataType: 'json',
					type: 'post',
					success: function(dados)
					{
						if(dados["resposta"]=='achou')
						{
							$('#conteudoaqui').html(dados['conteudo']);
							abrir_modal();
							
						}else
						{
							
						}
					}
				});
	}
	
	function verificar_promocoes(cod)
	{
		if (typeof(cod) =="undefined")
			cod = 0;
			
		var tam =$('#tam_pizza').val();
			
			
		acao = 'carregar_promocao';
		$.ajax({
					url: 'pub_req_promocoes_ajax.php',
					data: 'cod='+cod+'&acao='+acao+'&tam='+tam,
					dataType: 'json',
					type: 'post',
					success: function(dados)
					{
						if(dados["resposta"]=='achou')
						{
							$('#conteudoaqui').html(dados['conteudo']);
							abrir_modal();
							
						}else
						{
							$('#frmCarrinho').submit();
						}
					}
				});
	}
	
	function finalizar_promocao(acao)
	{
		if (typeof(acao) =="undefined")
			acao = 0;		
				
		if(acao!=0)
		{
			$("#acao").val(acao);
		}
		
		if($('#trocar').val()!='n' && $('#trocar').val()!='0' && $('#cpp').val()=="")
		{
			if($('#combo_bebida_trocar').val()!="")
			{
				 cmb_trocar= document.getElementById('combo_bebida_trocar');
				$("#frmCarrinho").append(cmb_trocar); 
				 cmb_trocar.style.display='none';
				$("#frmCarrinho").submit();		
			}else
			{
				alert('Selecione uma bebida ou desmarque a opção de troca');
			}	
		
		}else
		{
			if($('#trocar').val()!='n' && $('#cpp').val()=="")
			{
				//alert(typeof($('#combo_bebida_trocar')));
				if($('#combo_bebida_trocar').val()!="" && $('#combo_bebida_trocar').length > 0)
				{
					alert("Selecione a opção de troca antes de escolher a bebida!");		
				}else
				{
					$("#frmCarrinho").submit();		
				}
			}else
				$("#frmCarrinho").submit();		
		}
	}
	
	function checkar_trocar()
	{
		if($('#combo_bebida_trocar').val()!="")
		{
			$('#trocar').val('1');
			$('#trocar_check').attr('checked', true);
		}else
		{
			$('#trocar_check').attr('checked', false);
			$('#trocar').val('0');
		}
		
	}
	
	function sugerir(cod,pizza_pai)
	{
		if (typeof(cod) =="undefined")
			cod = 0;

		if (typeof(pizza_pai) =="undefined")
			pizza_pai = '';

		acao = 'carregar_sugestao';
		$.ajax({
					url: 'pub_req_promocoes_ajax.php',
					data: 'cod='+cod+'&acao='+acao+'&pizza_pai='+pizza_pai,
					dataType: 'json',
					type: 'post',
					success: function(dados)
					{
						if(dados["resposta"]=='achou')
						{
							$('#conteudoaqui').html(dados['conteudo']);
							abrir_modal();
							
						}else
						{
							$('#frmFecharPedido').submit();
						}
					}
				});
	
	}

	function abrir_modal()//metodo que abre as sugestões
	{
		$.nmManual('#popup_modal',{showCloseButton: false,closeOnEscape: false, closeOnClick: false});
	}
</script>

	<div style="display: none; width:900px">
			<div id="popup_modal" style="overflow:hidden">
			  <div id='conteudoaqui'> 
					
			  </div>
			</div> 
	</div>
