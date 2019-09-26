<? 
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';
require_once 'ipi_req_carrinho_classe.php';
$carrinho = new ipi_carrinho();



if ($_SESSION['ipi_carrinho']['pagamento']['tipo']=="VISANET1")
{
	$_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'];

	$conexao = conectabd();
	$sqlAux = "SELECT * FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='".$_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora']."'";		
	//echo $sqlAux;
	$resAux = mysql_query($sqlAux);
	$ret_operadora = array();
	while ($objAux = mysql_fetch_object($resAux))
		$ret_operadora[$objAux->chave]=$objAux->valor;
	$ret_operadora['lr']=(int)$ret_operadora['lr'];
	desconectabd($conexao);
}

if ($_SESSION['ipi_carrinho']['pagamento']['tipo']=="VISANET")
{
  $conexao = conectabd();
  $sqlAux = "SELECT * FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='".$_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora']."'";     
  //echo $sqlAux;
  $resAux = mysql_query($sqlAux);
  $ret_operadora = array();
  while ($objAux = mysql_fetch_object($resAux))
      $ret_operadora[$objAux->chave]=$objAux->valor;
  $ret_operadora['retorno_lr_autorizacao']=(int)$ret_operadora['retorno_lr_autorizacao'];
  desconectabd($conexao);
  
  echo "<br /><b>Pagamento com Cartão Crédito</b>";
  echo "<br /><b>ID Transação (TID)</b>: ".$ret_operadora['retorno_tid'];
  echo "<br /><b>Cod. Resposta (LR)</b>: ".$ret_operadora['retorno_lr_autorizacao'];
  echo "<br /><br />";
}

if ($ret_operadora['retorno_lr_autorizacao']!=0)
{
	echo "<center>Operação não autorizada pela operadora!<br>Lr: {$ret_operadora['retorno_lr_autorizacao']}<br>Tid: {$ret_operadora['retorno_tid']}<br>Arp: {$ret_operadora['retorno_arp_autorizacao']}<br><br><a href='pagamentos'>Voltar as formas de pagamento</a></center>";
}


if ( ($_SESSION['ipi_carrinho']['pagamento']['tipo']=="DINHEIRO") || ($ret_operadora['retorno_lr_autorizacao']==0) )
{

  if ($carrinho->existe_carrinho())
  {

	  $num_pedido = $carrinho->finalizar_pedido();
	  if ($num_pedido>0)
	  {
      $cod_pizzarias_atendeu = $_SESSION['cod_pizzarias_atendeu'];
      $carrinho->apagar_pedido_logar($num_pedido);
      unset($_SESSION['cod_pizzarias_atendeu']);
      //die("Pizzaria Atendeu: ".$cod_pizzarias_atendeu);
      //echo ("Pizzaria Atendeu: ".$cod_pizzarias_atendeu);
		  $pizzaria = $carrinho->dados_pizzaria($num_pedido);
		  $email_pedido = $carrinho->email_pedido($num_pedido);
		  $tempo_entrega = $carrinho->tempo_entrega_pedido($num_pedido);
		  $total_pedido = $carrinho->exibir_total_pedido($num_pedido);
      ?>
     
      <script src="js/jquery.browser.min.js"></script>
      <script type="text/javascript">
        $(function() {
          $('.nyroModal').nyroModal();
        });

        $(document).ready(function () {
          $.nmManual('#nyromodal_pedido_finalizado',{showCloseButton: false});


          var var_url='acao=pedidos_log&nome='+$.browser.name+'&versao='+$.browser.version+'&idioma='+((navigator.language) ? navigator.language : navigator.userLanguage)+'&plat='+$.os.name+'&cod_pedidos=<? echo $num_pedido; ?>';
          jQuery.ajax({
            url: 'ipi_req_carrinho_fim_pedido_ajax.php',
            type: "POST",
            data: var_url,
            success: function(response) { }
          });

        });

        
        function atualizar_apelido() 
        {
          var var_url='acao=salvar_apelido&cod='+document.getElementById('cod_pedidos').value+'&apelido='+document.getElementById('txt_apelido').value;
          jQuery.ajax({
          url: 'ipi_req_carrinho_fim_pedido_ajax.php',
          type: "POST",
          data: var_url,
          success: function(response)
            {
              $("#div_apelido").fadeOut(100);
              $("#div_apelido_salvar h1").animate({
                fontSize: "70px"
              }, 100 );
              $('#div_apelido_salvar h1').html("APELIDO SALVO");
            }
          });
        }

        function log_share_fb(local)
        {
          var var_url='acao=log_share_fb&cod='+document.getElementById('cod_pedidos').value+'&local='+local;
          jQuery.ajax({
            url: 'ipi_req_carrinho_fim_pedido_ajax.php',
            type: "POST",
            data: var_url,
            success: function(response) { }
          });
        }
      </script>
      
      <div style="display: none;">
        <div id="nyromodal_pedido_finalizado">
          <!-- <div class='conteudo_modal_pedido_finalizado'> -->
          <div class='conteudo_modal_pedido_finalizado_natal'>
            <a class='aviso_botao_fechar' alt='Fechar' href='javascript:void(0)' onclick='$.nmTop().close()'><img width="40" src='imgs/btn_fechar.png' alt='Fechar'></img></a>
	          <div id="container_pedido_finalizado">	
	            <div> 
                <br/><br/><br/><br/><h1>Pedido concluído com sucesso!</h1>
                
                <br/><br/>Sua pizza já vai para o forno. Em breve, chegará à sua mesa!

                <br/><br/>Enquanto espera, mate seus amigos de inveja!
                <?
                //TODO: Texto para o share do Facebook da tela do Apelido.
                  $title=urlencode(utf8_encode('Pizza quadrada é mais pizza!'));
                  $url=urlencode('https://www.osmuzzarellas.com.br/index.php');
                  $summary=urlencode(utf8_encode('Acabei de pedir minha Quadrada dos Muzza. Peça já a sua! :D'));
                  $image = "http://osmuzzarellas.com.br/img/pc/logo_osmuzzarellas.png";
                  //<a href='presente'><img src="img/pc/fim_pedido_modal_natal.png"  alt="MUZZA ENTREGA! Sua pizza já já vai pro forno, em breve chegara à sua mesa!" class='float_right fim_pedido_modal_natal'/></a>
                ?>
                <!--
                <a class="btn_compartilhar_modal" onclick="window.open('http://www.facebook.com/sharer.php? s=100&amp;p[title]=<?php echo $title;?>&amp;p[summary]=<?php echo $summary;?>&amp;p[url]=<?php echo $url; ?>&amp;&amp;p[images][0]=<?php echo $image;?>','sharer','toolbar=0,status=0,width=548,height=325'); log_share_fb('MODAL');" href="javascript: void(0)" title="Compartilhe"> <img src='img/pc/btn_compartilhe.png' alt='Compartilhe' /> </a>
                -->
              </div>			
            </div>
          </div>
        </div>
      </div>      
      <!-- <div id='caixa_fim_pedido'> -->
      <div class='parabens float_left'>
        <?
          $arr_nome = explode(' ', $_SESSION[ipi_cliente][nome]);  
          echo "<h1 class='fonte16 cor_marrom2'> ".$arr_nome[0].", </h1>";
          //echo "<img src='img/pc/apelido_parabens.jpg' alt='Parabéns! Compra finalizada! Os Muzza agradecem a preferencia!'/>";
        ?>
      </div>
      
      <div class='float_right compartilhe_natal'>   
        <?
        //TODO: Texto para o share do Facebook da tela do Apelido.
		      // $title=urlencode(utf8_encode('Pizza quadrada é mais pizza!'));
		      // $url=urlencode('https://www.osmuzzarellas.com.br/index.php');
		      // $summary=urlencode(utf8_encode('Acabei de pedir minha Quadrada dos Muzza. Peça já a sua! :D'));
		      // $image = "http://osmuzzarellas.com.br/img/pc/logo_osmuzzarellas.png";
		    ?> 
        <!--
        <p class='cor_branco fonte14'>Quer matar os seus<br/>amigos de inveja?</p>     
        <a class="float_right" onclick="window.open('http://www.facebook.com/sharer.php? s=100&amp;p[title]=<?php echo $title;?>&amp;p[summary]=<?php echo $summary;?>&amp;p[url]=<?php echo $url; ?>&amp;&amp;p[images][0]=<?php echo $image;?>','sharer','toolbar=0,status=0,width=548,height=325');  log_share_fb('n');" href="javascript: void(0)" title="Compartilhe"> <img src='img/pc/btn_compartilhe.png' alt='Compartilhe' /> </a>
        -->
      </div>
      
      <div class='float_left filial_atender'>
        <h1 class='fonte22 cor_marrom2'> Pedido Nº<? echo sprintf("%08d", $num_pedido); ?> </h1>
        <p class='fonte12 cor_marrom2'> Seu pedido será atendido pela nossa filial:<br /><? echo $pizzaria; ?><br /><? echo $tempo_entrega; ?> </p>
      </div>  
      
      <div class='float_right' id='div_apelido_salvar'>     
        <p class='fonte14 cor_cinza1'> Dê um apelido para o seu pedido e da próxima vez, na sua<br />área restrita, basta buscar pelo apelido e clicar em refazer. </p>
        <h1 class='cor_marrom2 espacamento_dois'> APELIDO </h1>
        <div id='div_apelido'>
          <form method="post" id="frmApelido" action="<? echo $PHP_SELF; ?>" onsubmit="javascript:atualizarApelido()">
            <div>
              <input type="text" name="txt_apelido" id="txt_apelido" size="30" class='form_text4' title="Apelido do pedido" />
              <a href="#apelido_titulo" onclick="atualizar_apelido()" title="Clique aqui para salvar o apelido do seu pedido." class="btn btn-secondary">Salvar Apelido</a>
				      <input type="hidden" name="cod_pedidos" id="cod_pedidos" value="<? echo $num_pedido; ?>">
             </div>
          </form>
        </div>
      </div>
      <?

		  require_once 'config.php';
		  require_once 'ipi_email.php';

		  $email_origem = EMAIL_PRINCIPAL;
		  $email_destino = $_SESSION['ipi_cliente']['email'];

		  $assunto = NOME_SITE . " - Pedido de Pizza";

		  $msg_email = '<br><font face="arial narrow, arial" color="#eb891a" size="5">Seu pedido de pizza foi efetuado!</font>';
		  $msg_email .= "<br><br><b>Pedido número: ".sprintf("%08d", $num_pedido).'</b>';

		  $msg_email .= '<br><br><br><font face="arial narrow, arial" color="#eb891a" size="4">Resumo do Pedido</font>';
		  $msg_email .= "<br><br>".$email_pedido;

		  $msg_email .= "<br><br><br>".$tempo_entrega;
		  $msg_email .= "<br><br>Para mais detalhes, visite nosso site: ".HOST;

		  //echo "<br><br>".$msg_email;
      
      $obj_buscar_pizzaria = executar_busca_simples("SELECT cod_pizzarias FROM ipi_pedidos WHERE cod_pedidos = $num_pedido");
      $arr_aux = array();
      $arr_aux['cod_pedidos'] = $num_pedido;
      $arr_aux['cod_usuarios'] = 0;
      $arr_aux['cod_clientes'] = $_SESSION['ipi_cliente']['codigo'];
      $arr_aux['cod_pizzarias'] = $obj_buscar_pizzaria->cod_pizzarias;
      $arr_aux['tipo'] = 'PEDIDO_INSERIDO';
		  enviar_email($email_origem, $email_destino, $assunto, $msg_email, $arr_aux, 'resumo_pedido');
	  }
	  else
	  {
		  $saida_erro = date("m/d/Y H:i:s")."\n\r";
		  $saida_erro .= var_export($_SESSION, true);
		  $saida_erro .= "\n\r----------------------------------------------------\n\r";

		  file_put_contents("ipi_log_fim_pedido.txt", $saida_erro, FILE_APPEND);
		  ?>
		  <div id='caixa_fim_pedido_falha'>
		    <div align='center'>
		      <h1 class='cor_marrom2'>ERRO AO PROCESSAR O PEDIDO</h1>
		      <br />
		      <strong>ATENÇÃO!</strong> Seu pedido NÃO FOI efetuado!
		      <br />
		      Volte e tente novamente!
		      <br />
		      <a href='pagamentos'>&lt;&lt; Voltar ao Pagamento</a>
		    </div>
	    </div>	  
      <?
	  }
  }
}
?>  	  

