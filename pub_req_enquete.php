<?
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';

function geraCupom($tam = 10) {
  $cupom = "";
  $caracteres = "123456789ABCDEFGHIJKLMNPQRSTUWXYZ";
  
  $i = 0;
  
  while ( $i < $tam ) {
    $char = substr($caracteres, mt_rand(0, strlen($caracteres) - 1 ), 1);
    
    if (! strstr ( $cupom, $char )) {
      $cupom .= $char;
      $i ++;
    }
  }
  
  return $cupom;
}

$acao = validaVarPost('acao');
$cod_enquete_entrega = 4;//codigo da pergunta sobre a entrega
$cod_enquete_sistema = 5;//codigo da pergunta sobre sistema
//$checksum = '1_10_1';
//echo base64_encode($checksum);

$checksum = base64_decode(validaVarGet('checksum'));

if($checksum == '')
  $checksum = validaVarPost('checksum');

$arrCheckSum = explode('_', $checksum);

if(is_array($arrCheckSum)) {
  $cod_enquetes = $arrCheckSum[0];
  $cod_clientes = $arrCheckSum[1];
  $cod_pedidos  = $arrCheckSum[2];
}

$objBuscaPedido = executaBuscaSimples("SELECT * FROM ipi_pedidos WHERE cod_pedidos = '$cod_pedidos'");

$data_hora_pedido = strtotime($objBuscaPedido->data_hora_pedido);
$data_hora_pedido_15dias = strtotime("+15 days", $data_hora_pedido);   
$data_hora_hoje = strtotime(date("Y-m-d H:i:s"));

$nome_produto_visual = ENQUETE_NOME_PRODUTO;


if ($data_hora_pedido_15dias < $data_hora_hoje)
{
    echo "<center><br />A enquete referente ao pedido <b>$cod_pedidos</b> do dia <b>".date("d/m/Y H:i:s", $data_hora_pedido)."</b> venceu.";
    echo "<br />Você tem <b>15 dias</b> para responder a enquete!</center>";
}
else
{    
    $data_pedido_15dias_bd =  date("Y-m-d H:i:s",$data_hora_pedido_15dias);
    //echo $data_pedido_15dias_bd;
    $objBuscaEnqueteCliente = executaBuscaSimples("SELECT COUNT(*) AS contagem FROM ipi_pedidos_ipi_enquetes WHERE cod_pedidos = '$cod_pedidos' AND cod_enquetes = '$cod_enquetes'");
    
    if(($objBuscaEnqueteCliente->contagem <= 0) && ($cod_enquetes > 0) && ($cod_clientes > 0) && ($cod_pedidos > 0)):
      
      if($acao == 'resposta'):
        $pergunta = validaVarPost('pergunta');
        
        $resInsert = true;
        
        $quantidade = 1;                   // Quantidade de sorteios por vez
        $produto = 'BORDA';               // Tipo do premio (PIZZA/BORDA/BEBIDA)
        $cod_produtos = array(1,2); // Todos os cod_bordas permitidos no sorteio
        $cod_tamanhos = 9;                 // Código do tamanho
        
        $con = conectabd();
        
        $resInsert = true;
        
        foreach($pergunta as $cor_p) 
        {
          $resposta = validaVarPost('resposta_'.$cor_p);
          $justifica = validaVarPost('justifica_'.$resposta);
          
          $SqlInsert = sprintf("INSERT INTO ipi_clientes_ipi_enquete_respostas (cod_clientes, cod_enquete_respostas, justificativa, data_hora_resposta, cod_pedidos) VALUES (%d, %d, '%s', NOW(), %d)", 
                               $cod_clientes, $resposta, texto2bd($justifica), $cod_pedidos);
          
          $resInsert &= mysql_query($SqlInsert);
        }
        
        if($resInsert) {
          $resEdicao = true;
          
          for($i = 1; $i <= $quantidade; $i++) {
            do {
              $cupom = geraCupom();
                
              $objContaCupom = executaBuscaSimples("SELECT COUNT(*) AS contagem FROM ipi_cupons WHERE cupom = '$cupom'", $con);
              
              if($objContaCupom->contagem > 0)
                $achou = true;
              else
                $achou = false;
                
            } while($achou);
            
            $cod_bordas = $cod_produtos[mt_rand(0, count($cod_produtos) - 1)];

            $cod_bordas = 0; // Forçado para zero para liberar escolha ao cliente, sorteio era o método antigo.
            
            $SqlEdicao = sprintf("INSERT INTO ipi_cupons (cupom, data_validade, produto, cod_produtos, cod_tamanhos, valido, cod_clientes,generico) VALUES ('%s', '%s', '%s', %d, %d, 1, %d,1)", 
                                 $cupom, $data_pedido_15dias_bd, $produto, $cod_bordas, $cod_tamanhos, $cod_clientes);
            $resEdicao &= mysql_query($SqlEdicao);
            $cod_cupons = mysql_insert_id();

            $sql_cupom_pizzaria = sprintf("INSERT INTO ipi_pizzarias_cupons (cod_cupons, cod_pizzarias) VALUES ('%s', '%s')", $cod_cupons, $objBuscaPedido->cod_pizzarias);
            $res_cupom_pizzaria = mysql_query($sql_cupom_pizzaria);
            //echo "<br>1: ".$sql_cupom_pizzaria;

          }
          
          $SqlInsertEnquete = sprintf("INSERT INTO ipi_pedidos_ipi_enquetes (cod_pedidos, cod_enquetes, data_hora_gravacao) VALUES (%d, %d, NOW())",
                                      $cod_pedidos, $cod_enquetes);
          
          $resInsertEnquete = mysql_query($SqlInsertEnquete);
          
          if($resEdicao && $resInsertEnquete) {
            $objBuscaBorda = executaBuscaSimples("SELECT * FROM ipi_bordas WHERE cod_bordas = $cod_bordas", $con);
            $objBuscaCliente = executaBuscaSimples("SELECT * FROM ipi_clientes WHERE cod_clientes = $cod_clientes", $con);
            
            require_once 'config.php';
            require_once 'ipi_email.php';
            
            $email_origem = EMAIL_PRINCIPAL;
            $email_destino = $objBuscaCliente->email;
            $assunto = NOME_SITE . " - Você ganhou um ".$nome_produto_visual."!";
            
            $texto .= "<br><br>Parabéns <b>".bd2texto($objBuscaCliente->nome)."</b>, você acaba de ganhar um cupom com direito a um ".$nome_produto_visual."* na sua próxima compra.";
            $texto .= "<br><br>Para utiliza-lo acesse o site <a href=\"http://".HOST."\">http://".HOST."</a> e digite o código <b>$cupom</b> no carrinho.";
            $texto .= "<br><br><br><b>* Cupom válido até ".date("d/m/Y",$data_hora_pedido_15dias)." às ".date("H:m:s",$data_hora_pedido_15dias).".</b>";
            
            $arr_aux = array();
            $arr_aux['cod_pedidos'] = $cod_pedidos;
            $arr_aux['cod_usuarios'] = 0;
            $arr_aux['cod_clientes'] = $cod_clientes;
            $arr_aux['cod_pizzarias'] = $objBuscaPedido->cod_pizzarias;
            $arr_aux['tipo'] = 'ENQUETE';
            if(enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'enquete')) {
              echo '<h1>Obrigado por sua resposta!</h1>';
              echo '<br>';
              echo '<p>O cupom <b>'.$cupom.'</b> vale um '.$nome_produto_visual.'* na sua próxima compra. O mesmo já foi enviado para o seu e-mail.</p>';
              echo '<p>* Cupom válido até '.date("d/m/Y",$data_hora_pedido_15dias)." às ".date("H:m:s",$data_hora_pedido_15dias).'.</p>';
            }
            else {
              echo '<script> alert("Erro ao processar a resposta da enquete. Infelizmente o prêmio não foi gerado!")</script>';
            }
          }
          else {
            echo '<script> alert("Erro ao processar a resposta da enquete. Infelizmente o prêmio não foi gerado!")</script>';
          } 
        }
        else {
          echo '<script> alert("Erro ao processar a resposta da enquete. Infelizmente o prêmio não foi gerado!")</script>';
        }
        
      else:
      ?>
      
      <script>

      
      function verificarfilhas(cod,perg) 
      {
          var acao = 'verificar_filhas';
          var url = 'acao=' + acao + '&cod_resposta=' + cod+ '&cod_pergunta=' + perg;
          var perg = perg;
          new Request.JSON({url: 'pub_req_enquete_ajax.php', onComplete: function(retorno) {
            if(retorno.status == 'sim') 
            {
              chamarfilhas(cod,perg);
            }
            else
            {
              
            }
          }}).send(url); 
        
      }

      function chamarfilhas(cod,perg) 
      {
       
          var acao = 'chamar_filhas';
          var perg = perg;
          var url = 'acao=' + acao + '&cod_resposta=' + cod;
            
            new Request.HTML({
              url: 'pub_req_enquete_ajax.php',
              update: $('div_'+perg)
            }).send(url);
            
          
        
      }

      function validaForm(form) {
        var radioBox = form.getElementsByTagName('input');
      
        // Utilizando o hash do mootools
        // Alterado para array pois o sistema agora usa jquery
        var radios ={};
      
        for (var r = 0; r < radioBox.length; r++) {
          if((radioBox[r].type == 'radio') && (radioBox[r].name.match('resposta_'))) {
            //alert(radios.indexOf(radioBox[r].name));
            if((radios[radioBox[r].name] == false) || (!radios.hasOwnProperty(radioBox[r].name))) {
              radios[radioBox[r].name] = radioBox[r].checked;
            }
          }
        }
        var retorno = true;
        
        // Verificando se todas as perguntas estão respondidas...
        $.each(radios,function(chave, valor){
          if(!valor) {
            var arrSplit = chave.split('_');
            
            alert('A pergunta "' + $('pergunta_' + arrSplit[1]).text() + '" deve ser respondida.');
            
            retorno = false;
          }
          else {
            // Verificando se todos os campos justificativa estão respondidos...
            
            var arrSplit = chave.split('_');
            var resposta = document.getElementsByName(chave);
            var value_resposta;
            
            for(var r = 0; r < resposta.length; r++) {
              if(resposta[r].checked) {
                value_resposta = resposta[r].value;
              }
            }
            
            for (var r = 0; r < radioBox.length; r++) {
              if((radioBox[r].type == 'radio') && (radioBox[r].name.match('resposta_'))) {
              
                if(($('#'+radioBox[r].className)) && (radioBox[r].className == 'justifica_' + value_resposta)) {
                  if($('#'+radioBox[r].className) != null) {
                    //alert($('#'+radioBox[r].className).attr('value'))
                    if($('#'+radioBox[r].className).attr('value') == '') {
                      alert('A resposta "' + $('#legenda_' + radioBox[r].value).text()+ '" da pergunta "' + $('#pergunta_' + arrSplit[1]).text() + '" deve ser justificada.');
                      retorno = false;
                    }
                  }
                }
              }
            }
          }
          
        });
        
        return retorno;  
      }
      </script>
      <div class="areaTextoRound fundoBranco espacoTopo" style="display: table;width: 400px;">
  <article>
      <header class="tituloRound fundoLaranjaClaro" style="width: 400px;">
      <h1 class="fundoAmarelo" style="width: 120px;">Responder enquete</h1>

      
      <form method="post" onsubmit="return validaForm(this)">
      <span class="spanFull corTextoMarrom" >
          <span class="spanAE espacoRodape" style='width: 75%;'>
      <br><br><br>
      Responda a enquete e ganhe um *<?php echo $nome_produto_visual; ?>!
      <br><br><br>
        <?
        echo '<div class="caixa_enquete">';
        
        $con = conectabd();
        
        $SqlBuscaPerguntas = "SELECT * FROM ipi_enquete_perguntas WHERE cod_enquetes = $cod_enquetes AND pergunta_pessoal = 0 ORDER BY cod_enquete_perguntas and cod_enquete_perguntas_pai!=0";
        $resBuscaPerguntas = mysql_query($SqlBuscaPerguntas);

        //echo $objBuscaPedido->cod_pedidos;

        while($objBuscaPerguntas = mysql_fetch_object($resBuscaPerguntas)) 
        {
          echo '<span id="pergunta_'.$objBuscaPerguntas->cod_enquete_perguntas.'"><strong>'.bd2texto($objBuscaPerguntas->pergunta).'</strong></span>';
          
          echo '<input type="hidden" name="pergunta[]" value="'.$objBuscaPerguntas->cod_enquete_perguntas.'">';
          echo '<br />';
          
          $SqlBuscaRespostas = "SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas = ".$objBuscaPerguntas->cod_enquete_perguntas." ORDER BY  cod_enquete_respostas";
          $resBuscaRespostas = mysql_query($SqlBuscaRespostas);


          if($objBuscaPedido->tipo_entrega=="Balcão" && $objBuscaPerguntas->cod_enquete_perguntas==$cod_enquete_entrega)
          {
              $objBuscaRespostas = mysql_fetch_object($resBuscaRespostas);
              echo "Seu pedido foi retirado no balcão, portanto não é necessario avaliar a entrega";
              echo '<input type="hidden" name="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.'" id="resposta_'.$objBuscaRespostas->cod_enquete_respostas.'" class="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" value="'.$objBuscaRespostas->cod_enquete_respostas.'">';
              echo '<input type="hidden" id="justifica_opcional_'.$objBuscaRespostas->cod_enquete_respostas.'" name="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" class="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.'" value="Respondido automaticamente pois o pedido foi retirado no balcão">';
            }
            else
            {
              if($objBuscaPedido->origem_pedido=="TEL" && $objBuscaPerguntas->cod_enquete_perguntas==$cod_enquete_sistema)
              {
                $objBuscaRespostas = mysql_fetch_object($resBuscaRespostas);
                echo "Seu pedido foi feito pelo telefone, portanto não é necessario avaliar o sistema";
                echo '<input type="hidden" name="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.'" id="resposta_'.$objBuscaRespostas->cod_enquete_respostas.'" class="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" value="'.$objBuscaRespostas->cod_enquete_respostas.'">';
                echo '<input type="hidden" id="justifica_opcional_'.$objBuscaRespostas->cod_enquete_respostas.'" name="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" class="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.'" value="Respondido automaticamente pois o pedido foi feito pelo telefone">';
              }
              else
              {
                while($objBuscaRespostas = mysql_fetch_object($resBuscaRespostas)) 
                {
                  if($objBuscaPedido->tipo_entrega=="Balcão" && $objBuscaPerguntas->cod_enquete_perguntas==$cod_enquete_entrega)
                  {
                    echo "Seu pedido foi retirado no balcão, portanto não é necessario avaliar a entrega";
                    echo '<input type="hidden" name="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.'" id="resposta_'.$objBuscaRespostas->cod_enquete_respostas.'" class="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" value="'.$objBuscaRespostas->cod_enquete_respostas.'">';
                  }else
                  {
                    echo '<div class="respostas"><input type="radio" style="background: none; border: none;" name="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.'" id="resposta_'.$objBuscaRespostas->cod_enquete_respostas.'" class="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" value="'.$objBuscaRespostas->cod_enquete_respostas.'">&nbsp;<label id="legenda_'.$objBuscaRespostas->cod_enquete_respostas.'" for="resposta_'.$objBuscaRespostas->cod_enquete_respostas.'">'.bd2texto($objBuscaRespostas->resposta.'</label></div>');
                    
                    if (($objBuscaRespostas->justifica) && (!$objBuscaRespostas->justifica_opcional)) {
                      echo '&nbsp;<input type="text" id="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" name="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" class="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.' form_text6" maxsize="1000">&nbsp;<span class="fonte12">(justifique)</span>';
                    }
                    else if (($objBuscaRespostas->justifica) && ($objBuscaRespostas->justifica_opcional)) {
                      echo '&nbsp;<input type="text" id="justifica_opcional_'.$objBuscaRespostas->cod_enquete_respostas.'" name="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" class="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.' form_text6" maxsize="1000">&nbsp;<span class="fonte12">(comente)</span>';
                    }
                    
                    
                  }          
                }
              }
            }
            echo '<br />';  
            echo '<br />';
        }
          ////////////////////////////////////////////////////////////////////////////////////////////////////////////
          
        //echo '</div>';
        
          
         /* $SqlBuscaPerguntas = "SELECT * FROM ipi_enquete_perguntas en WHERE en.cod_enquetes = $cod_enquetes AND en.cod_enquete_perguntas_pai=0 AND en.pergunta_pessoal = 1  and en.cod_enquete_perguntas not in(SELECT res.cod_enquete_perguntas from ipi_enquete_respostas res inner join ipi_clientes_ipi_enquete_respostas ipi on ipi.cod_enquete_respostas = res.cod_enquete_respostas WHERE res.cod_enquete_perguntas = en.cod_enquete_perguntas AND ipi.cod_clientes = $cod_clientes ) ORDER BY rand() LIMIT 5 ";
          //echo $SqlBuscaPerguntas;
          $resBuscaPerguntas = mysql_query($SqlBuscaPerguntas);
          if(mysql_num_rows($resBuscaPerguntas)>0)
          {
            echo "<span> <strong>Fale sobre você </strong> </span>";
            echo '<br />';
          }
          echo '<div class="caixa_enquete">';
          while($objBuscaPerguntas = mysql_fetch_object($resBuscaPerguntas)) 
          {
          echo '<span id="pergunta_'.$objBuscaPerguntas->cod_enquete_perguntas.'"><strong>'.bd2texto($objBuscaPerguntas->pergunta).'</strong></span>';
          
          echo '<input type="hidden" name="pergunta[]" value="'.$objBuscaPerguntas->cod_enquete_perguntas.'">';
          echo '<br />';
          $SqlBuscaRespostas = "SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas = ".$objBuscaPerguntas->cod_enquete_perguntas." ORDER BY  cod_enquete_respostas";
          $resBuscaRespostas = mysql_query($SqlBuscaRespostas);
          
          while($objBuscaRespostas = mysql_fetch_object($resBuscaRespostas)) 
          {
            echo '<div class="respostas_pessoais"><input type="radio" style="background: none; border: none;" name="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.'" id="resposta_'.$objBuscaRespostas->cod_enquete_respostas.'" class="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" value="'.$objBuscaRespostas->cod_enquete_respostas.'" onClick="verificarfilhas(this.value,'.$objBuscaPerguntas->cod_enquete_perguntas.')" >&nbsp;<label for="resposta_'.$objBuscaRespostas->cod_enquete_respostas.'" id="legenda_'.$objBuscaRespostas->cod_enquete_respostas.'">'.bd2texto($objBuscaRespostas->resposta.'</label></div>');
            
            if (($objBuscaRespostas->justifica) && (!$objBuscaRespostas->justifica_opcional)) 
            {
              echo '<br />';
              echo '&nbsp;<input type="text" id="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" name="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" class="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.' form_text6 input_pergunta_pessoal" maxsize="1000">&nbsp;<span class="fonte12">(justifique)</span>';
            }
            else if (($objBuscaRespostas->justifica) && ($objBuscaRespostas->justifica_opcional)) 
            {
              echo '<br />';
              echo '&nbsp;<input type="text" id="justifica_opcional_'.$objBuscaRespostas->cod_enquete_respostas.'" name="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" class="resposta_'.$objBuscaPerguntas->cod_enquete_perguntas.' form_text6 input_pergunta_pessoal" maxsize="1000">&nbsp;<span class="fonte12">(comente)</span>';
            }
            
            echo '<br />';
          }
          
          echo '<div id="div_'.$objBuscaPerguntas->cod_enquete_perguntas.'" name="div_'.$objBuscaPerguntas->cod_enquete_perguntas.'" ></div>';
          echo '<br />';
         
        }*/
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        desconectabd($con);
        echo '<input type="hidden" name="acao" value="resposta">';
        echo '<input type="hidden" name="checksum" value="'.$checksum.'">';
        echo '<div align="center"><input class="botaoPadrao fundoLaranjaBotao" type="submit" value="Responder" /></div>';
        
        echo '</div>';
        
        ?>
        <br>
      <small>* O prêmio para resposta dá direito a um cupom para ser descontado em uma compra.</small>
        </span>
        </span>
      </form>
      
      
      </header>
      </article>
    </div>
      <? endif; ?>
      
    <? else: ?>
    <p>Esta enquete já foi respondida.</p>
    <? 
    endif; 
}
?>
