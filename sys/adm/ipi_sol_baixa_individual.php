<?php

/**
 * ipi_bebida.php: Cadastro Bebidas
 * 
 * Índice: cod_bebidas
 * Tabela: ipi_bebidas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Baixa de Pedidos Individuais');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$cupom_natal = 'LY6PXW12FR';

$cod_pizzarias_sessao =  implode("," , $_SESSION['usuario']['cod_pizzarias']);

switch($acao) 
{
  case 'baixar':

  $email_origem = EMAIL_PRINCIPAL;
  
  $con = conectar_bd();
  
  $cod_pedidos = validaVarPost('cod_pedidos');
  $forma_pg = validaVarPost('forma_pg');
  $cod_entregador = validaVarPost('cod_entregador');
  
  
  $objBuscaPedido = executaBuscaSimples("SELECT *, (SELECT c.cupom FROM ipi_cupons c INNER JOIN ipi_pedidos_ipi_cupons pp ON (pp.cod_cupons = c.cod_cupons)  WHERE pp.cod_pedidos = p.cod_pedidos) cupom FROM ipi_pedidos p WHERE p.cod_pedidos = $cod_pedidos", $con);
    //echo "<br>1: "."SELECT *, (SELECT c.cupom FROM ipi_cupons c INNER JOIN ipi_pedidos_ipi_cupons pp ON (pp.cod_cupons = c.cod_cupons)  WHERE pp.cod_pedidos = p.cod_pedidos) cupom FROM ipi_pedidos p WHERE p.cod_pedidos = $cod_pedidos";
   /* if($forma_pg)
   {*/

    $cod_pizzarias = $objBuscaPedido->cod_pizzarias;  

    if($objBuscaPedido->situacao!='BAIXADO')
    {
      require_once("../../pub_req_fuso_horario1.php");

      $sql_buscar_forma_pg = "SELECT * FROM ipi_formas_pg ifp INNER JOIN ipi_formas_pg_pizzarias ifpp ON (ifp.cod_formas_pg = ifpp.cod_formas_pg) WHERE ifp.forma_pg = '$forma_pg' AND ifpp.cod_pizzarias = ".$objBuscaPedido->cod_pizzarias;
      $res_buscar_forma_pg = mysql_query($sql_buscar_forma_pg);
      $obj_buscar_forma_pg = mysql_fetch_object($res_buscar_forma_pg);
     /* }else
      {
        $sql_buscar_forma_pg = "SELECT * FROM ipi_formas_pg ifp INNER JOIN ipi_formas_pg_pizzarias ifpp ON (ifp.cod_formas_pg = ifpp.cod_formas_pg) WHERE ifp.forma_pg = '".$objBuscaPedido->forma_pg."' AND ifpp.cod_pizzarias = ".$objBuscaPedido->cod_pizzarias;
        $res_buscar_forma_pg = mysql_query($sql_buscar_forma_pg);
        $obj_buscar_forma_pg = mysql_fetch_object($res_buscar_forma_pg);
      }
      $cod_titulos_subcategorias_taxa = $obj_buscar_forma_pg->cod_titulos_subcategorias_taxa;
      $cod_titulos_subcategorias = $obj_buscar_forma_pg->cod_titulos_subcategorias;
      $cod_bancos_destino = $obj_buscar_forma_pg->cod_bancos;

      */
      if(($obj_buscar_forma_pg->prazo=="")||($obj_buscar_forma_pg->cod_titulos_subcategorias_taxa<=0 && $obj_buscar_forma_pg->taxa>0)||($obj_buscar_forma_pg->cod_titulos_subcategorias<=0)||($obj_buscar_forma_pg->cod_bancos<=0))
      {
        echo "<h2 style='color:red'><b>NÃO FOI POSSIVEL REALIZAR A BAIXA, A FORMA DE PAGAMENTO CADASTRADA ESTÁ COM O CADASTRO INCOMPLETO (".$obj_buscar_forma_pg->forma_pg.")</b></h2>";
      }else
      {
        //die();
        $sqlPegaPedido = "SELECT ifood_polling FROM ipi_pedidos WHERE cod_pedidos='".$cod_pedidos."'";
        $sqlQueryPegaPedido = mysql_query($sqlPegaPedido);
        $sqlQueryPegaPedido = mysql_fetch_object($sqlQueryPegaPedido);
        if(!empty($sqlQueryPegaPedido->ifood_polling)){
          file_get_contents("https://formulasys.encontresuafranquia.com.br/ifood.php?acao=delivery&chave=165117047d56ce2487aa718bd8d6c5b7&cod_pedido=".$sqlQueryPegaPedido->ifood_polling);
        }
        $SqlUpdate = "UPDATE $tabela SET situacao = 'BAIXADO' , cod_entregadores = $cod_entregador,";//
        if ($forma_pg)
          $SqlUpdate .= " forma_pg = '$forma_pg', ";
        $SqlUpdate .= " data_hora_baixa = NOW() WHERE $chave_primaria = $cod_pedidos";
        
        $resUpdate = mysql_query($SqlUpdate);
        //echo "<br>1: ".$SqlUpdate;
        
        
        if ($resUpdate) 
        {
          $obj_buscar_clientes = executaBuscaSimples("SELECT c.cod_clientes, c.cod_clientes_indicador, c.indicador_recebeu_pontos, c.nome, c.email, p.data_hora_pedido,p.valor_total as valor FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (c.cod_clientes = p.cod_clientes) WHERE p.cod_pedidos = $cod_pedidos", $con);


          require_once '../../config.php';
          require_once '../../ipi_email.php';

          if ( ($objBuscaPedido->origem_pedido == 'NET') || (PONTOS_FIDELIDADE == "TODOS_PEDIDOS") )
          {
              //Distribuindo os pontos de fidelidade 1 -> 1
            $SqlInsertPontos = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, pontos, data_validade, cod_pedidos) (SELECT cod_clientes, NOW(), FLOOR(valor_total), DATE_ADD(NOW(), INTERVAL 1 YEAR), cod_pedidos FROM $tabela WHERE $chave_primaria = $cod_pedidos)";
            $resInsertPontos = mysql_query($SqlInsertPontos);
              //echo "<br>SqlInsertPontos: ".$SqlInsertPontos;
            
            if($resInsertPontos)
            {

                //echo "<br>Z: "."SELECT c.cod_clientes, c.cod_clientes_indicador, c.indicador_recebeu_pontos, c.nome, c.email, p.data_hora_pedido FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (c.cod_clientes = p.cod_clientes) WHERE p.cod_pedidos = $cod_pedidos";
              
                // Dando o ponto de fidelidade se quem foi indicado comprou (baixou)
                // if($obj_buscar_clientes->indicador_recebeu_pontos == 0) 

                //Promo Natal
              if($obj_buscar_clientes->indicador_recebeu_pontos == 0)
              {

                  /*if($objBuscaPedido->cupom)
                  {
                      $obj_email_cliente = executaBuscaSimples("SELECT c.email, p.valor_total FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (c.cod_clientes = p.cod_clientes) WHERE p.cod_pedidos = $cod_pedidos", $con);
                      //echo "<br>J: "."SELECT * FROM ipi_indicacoes WHERE email = '".$obj_email_cliente->email."' AND data_hora_indicacao >= '2012-11-30'";


                      $obj_buscar_clientes = executaBuscaSimples("SELECT *,(SELECT p.valor_total FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (c.cod_clientes = p.cod_clientes) WHERE p.cod_pedidos = $cod_pedidos) valor FROM ipi_indicacoes WHERE email = '".$obj_email_cliente->email."' AND data_hora_indicacao >= '2012-11-30'", $con);
                      //echo "<br>H: "."SELECT *,(SELECT c.email, p.valor_total FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (c.cod_clientes = p.cod_clientes) WHERE p.cod_pedidos = $cod_pedidos) valor FROM ipi_indicacoes WHERE email = '".$obj_email_cliente->email."' AND data_hora_indicacao >= '2012-11-30'";
                    }*/

                    if($obj_buscar_clientes->cod_clientes_indicador > 0) 
                    {
                      $SqlInsertPontosIndica = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, pontos, data_validade, obs) VALUES ('".$obj_buscar_clientes->cod_clientes_indicador."', NOW(), '".(int)$obj_buscar_clientes->valor."', DATE_ADD(NOW(), INTERVAL 1 YEAR), 'Indicação pelo pedido $cod_pedidos')";
                      $resInsertPontosIndica = mysql_query($SqlInsertPontosIndica);
                    //echo "<br>SqlInsertPontosIndica: ".$SqlInsertPontosIndica;
                      
                      if(!$resInsertPontosIndica) 
                      {
                        mensagemErro('Erro ao adicionar pontos de fidelidade por indicação', 'Por favor, verifique as configurações do cliente: '.bd2texto($obj_buscar_clientes->nome).'.');
                      }
                      else 
                      {
                        $SqlUpdatePontosIndica = "UPDATE ipi_clientes SET indicador_recebeu_pontos = 1 WHERE cod_clientes = '".$obj_buscar_clientes->cod_clientes."'";
                        $resUpdatePontosIndica = mysql_query($SqlUpdatePontosIndica);

                    //echo "<br>SqlUpdatePontosIndica: ".$SqlUpdatePontosIndica;

                        
                        $obj_buscar_clientesIndicador = executaBuscaSimples('SELECT * FROM ipi_clientes WHERE cod_clientes = '.$obj_buscar_clientes->cod_clientes_indicador, $con);
                        
                      // Envia o email para quem indicou informando que ganhou os pontos.
                        $email_destino = $obj_buscar_clientesIndicador->email;

                    //echo "<br>clientesIndicador: ".'SELECT * FROM ipi_clientes WHERE cod_clientes = '.$obj_buscar_clientes->cod_clientes_indicador;
                        
                        $assunto = NOME_SITE . " - Você ganhou ".(int)$obj_buscar_clientes->valor." pontos por indicar!";
                        
                        $texto = "<br><br>Parabéns ".bd2texto($obj_buscar_clientesIndicador->nome)." pela indicação!";
                        $texto .= "<br><br><br>Seu amigo(a) ".bd2texto($obj_buscar_clientes->nome)." indicado por você comprou e você ganhou ".(int)$obj_buscar_clientes->valor." PONTOS DE FIDELIDADE!";
                        $texto .= "<br><br>Indique quantas pessoas quiser e ganhe ainda mais pontos.";

                        
                        
                        $arr_aux['cod_pedidos'] = 0;
                        $arr_aux['cod_usuarios'] = 0;
                        $arr_aux['cod_clientes'] = $cod_clientes;
                        $arr_aux['cod_pizzarias'] = 0;
                        $arr_aux['tipo'] = 'INDICACAO_FIDELIDADE';
                      /*if(!enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'fidelidade'))
                      mensagemErro('Erro ao ENVIAR e-mail de indicação', 'Por favor, verifique as configurações do cliente: '.bd2texto($obj_buscar_clientesIndicador->nome).'.');*/
                      $con = conectar_bd();
                    }
                  }
                }
              }
            }
            
            if(ENQUETE_ENVIAR == 1)
            {
              
              //Foi tirado do laço de Cima e passado pra cá pra enviar para qualquer cliente que tenha EMAIL
              if ($obj_buscar_clientes->email!="")
              {
                //if (($obj_buscar_clientes->email!="")&&($obj_buscar_clientes->origem_pedido=="NET"))
                $nome_produto_visual = ENQUETE_NOME_PRODUTO;
                // Calculando o checksum
                $cod_enquetes = 1; ////////////////////// ENQUETE FIXADA!
                $cod_clientes = $obj_buscar_clientes->cod_clientes;
                
                $checksum = base64_encode("${cod_enquetes}_${cod_clientes}_${cod_pedidos}");
                
                $email_destino = $obj_buscar_clientes->email;
                //$email_destino = "filipegranato@internetsistemas.com.br";
                $assunto = NOME_SITE . " - Responda a nossa ENQUETE e ganhe um(a) ".$nome_produto_visual."!";
                
                $texto = "<br><br>Obrigado ".bd2texto($obj_buscar_clientes->nome)." pela compra, agora só falta você responder nossa enquete e ganhar um  ".$nome_produto_visual."* na próxima compra!";
                $texto .= "<br><br>Para responder acesse o site <a href=\"http://".HOST."/enquete&checksum=$checksum\">http://".HOST."/enquete&checksum=$checksum</a>.";
                $texto .= "<br><br><br><b>* Cupom válido até o dia ".date("d/m/Y", strtotime("+15 days", strtotime($obj_buscar_clientes->data_hora_pedido)) ).".</b>";
                $texto .= "<br><b>** Você pode responder está enquete também até o dia: ".date("d/m/Y H:i:s", strtotime("+15 days", strtotime($obj_buscar_clientes->data_hora_pedido)) )."</b>";
                
                //echo "<br>email: ".$texto;
                
                $sql_buscar_pizzaria = "SELECT cod_pizzarias FROM ipi_pedidos WHERE cod_pedidos = '$cod_pedidos'";
                $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
                $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);


                $arr_aux['cod_pedidos'] = $cod_pedidos;
                $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
                $arr_aux['cod_clientes'] = $cod_clientes;
                $arr_aux['cod_pizzarias'] = $obj_buscar_pizzaria->cod_pizzarias;
                $arr_aux['tipo'] = 'ENQUETE_ENVIADA';
                if(!enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'enquete'))
                  mensagemErro('Erro ao ENVIAR enquete', 'Por favor, verifique as configurações do cliente: '.bd2texto($obj_buscar_clientes->nome).'.');
                $con = conectar_bd();
                $sql_log_email = "INSERT INTO ipi_email_automatico (cod_clientes, tipo_email, data_hora_envio) VALUES( '".$cod_clientes."', 'AUMENTAR_FIDELIDADE', '".date("Y-m-d H:i:s")."')";
                $res_log_email = mysql_query($sql_log_email);
              }
            }


            // Mandar email Explicando como aumentar pontos Fidelidade
            $con = conectar_bd();
            $sql_email_fidelidade = "SELECT * FROM ipi_fidelidade_clientes WHERE cod_pedidos = '".$cod_pedidos."' AND pontos < 0";
            //echo "<Br>sql_email_fidelidade: ".$sql_email_fidelidade;
            $res_email_fidelidade = mysql_query($sql_email_fidelidade);
            $num_email_fidelidade = mysql_num_rows($res_email_fidelidade);
            //echo "<Br>num_email_fidelidade: ".$num_email_fidelidade;
            //echo "<Br>Email: ".$obj_buscar_clientes->email;

            if ( ($num_email_fidelidade>0)&&($obj_buscar_clientes->email!="") )
            {
              $email_destino = $obj_buscar_clientes->email;
              $assunto = NOME_SITE . " - Aumente seus pontos de FIDELIDADE!";
              
              $nome = explode(' ', $obj_buscar_clientes->nome);

              $texto = "<br /><strong>".primeira_maiuscula($nome[0])."</strong>, gostou de usar os Pontos Fidelidade Muzza? Legal, né?";
              $texto .= "<br />Então, você sabia que indicar nosso site para os amigos te dá direito";
              $texto .= "<br />a mais pontos?";
              $texto .= "<br />É, basta o seu indicado fazer uma compra que os pontos do primeiro";
              $texto .= "<br />pedido dele também vão pra você!";
              $texto .= "<br /><br />Não perca tempo e fale pra todo mundo como é legal pedir Pizza";
              $texto .= "<br />Quadrada dos Muzza pela internet!";
              $texto .= "<br /><br />Acesse www.osmuzzarellas.com.br/indique e veja como é fácil ganhar";
              $texto .= "<br />mais Pontos Fidelidade Muzza com a ajuda dos amigos.";
              $texto .= "<br /><br />Boa pontuação pra você!";
              $texto .= "<br /><br />Equipe Muzza";
                //echo "<br>email: ".$texto;
              
              $sql_buscar_pizzaria = "SELECT cod_pizzarias FROM ipi_pedidos WHERE cod_pedidos = '$cod_pedidos'";
              $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
              $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
              
              $arr_aux['cod_pedidos'] = $cod_pedidos;
              $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
              $arr_aux['cod_clientes'] = $cod_clientes;
              $arr_aux['cod_pizzarias'] = $obj_buscar_pizzaria->cod_pizzarias;
              $arr_aux['tipo'] = 'INDIQUE';
               /* if(!enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'fidelidade'))
                {
                  mensagemErro('Erro ao ENVIAR enquete', 'Por favor, verifique as configurações do cliente: '.bd2texto($obj_buscar_clientes->nome).'.');
                }*/
                $con = conectar_bd();
              }



            //Inserindo no fluxo de caixa.

            // Lançando considerando a forma de pagamento
              $sql_buscar_forma_pg = "SELECT ifpp.cod_bancos, ifpp.cod_titulos_subcategorias, ifpp.cod_titulos_subcategorias_taxa, ifpp.taxa, ifpp.prazo,ifp.cod_formas_pg  FROM ipi_formas_pg ifp INNER JOIN ipi_formas_pg_pizzarias ifpp ON (ifp.cod_formas_pg = ifpp.cod_formas_pg) WHERE ifp.forma_pg = '$forma_pg' AND ifpp.cod_pizzarias = ".$objBuscaPedido->cod_pizzarias;
            //echo "<Br>sql_buscar_forma_pg:      ".$sql_buscar_forma_pg;
              $res_buscar_forma_pg = mysql_query($sql_buscar_forma_pg);
              $obj_buscar_forma_pg = mysql_fetch_object($res_buscar_forma_pg);
              
              $cod_titulos_subcategorias_taxa = $obj_buscar_forma_pg->cod_titulos_subcategorias_taxa;
              $cod_titulos_subcategorias = $obj_buscar_forma_pg->cod_titulos_subcategorias;
              $cod_bancos_destino = $obj_buscar_forma_pg->cod_bancos;
              $dias_soma = $obj_buscar_forma_pg->prazo;
              $cod_formas_pg = $obj_buscar_forma_pg->cod_formas_pg;

            //echo "<br>Taxa: ".$cod_titulos_subcategorias_taxa;
            //echo "<br>Credito: ".$cod_titulos_subcategorias;

            // Insere os créditos

              
              $sql_inserir_titulos = sprintf("INSERT INTO ipi_titulos (cod_pedidos, cod_clientes, cod_pizzarias, cod_titulos_subcategorias, descricao, tipo_cedente_sacado, tipo_titulo, total_parcelas, data_hora_criacao) VALUES ('%s', '%s', '%s', '%s', '%s', 'CLIENTE', 'RECEBER', 1, NOW())",
                $cod_pedidos, $objBuscaPedido->cod_clientes, $objBuscaPedido->cod_pizzarias, $cod_titulos_subcategorias, 'Recebimento ref. pedido ' . $cod_pedidos . ' em ' . date('d/m/Y'));
              $res_inserir_titulos = mysql_query($sql_inserir_titulos);
            //echo "<Br>sql_inserir_titulos:      ".$sql_inserir_titulos;
              $cod_titulos = mysql_insert_id();
              
              
              if($dias_soma>0)
              {
                $sql_inserir_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos,cod_formas_pg, cod_bancos_destino, data_vencimento, data_pagamento, mes_ref, ano_ref, valor, juros, valor_total, numero_parcela, forma_pagamento, recebido_enviado, situacao, data_hora_criacao, data_emissao) VALUES ('%s', '%s','%s', DATE_ADD('%s', INTERVAL '%s' DAY), DATE_ADD('%s', INTERVAL '%s' DAY), MONTH('%s'), YEAR('%s'), '%s', '%s', '%s', 1, '%s', 1, 'ABERTO',NOW(),'%s')",
                  $cod_titulos,$cod_formas_pg, $cod_bancos_destino, $objBuscaPedido->data_hora_pedido,$dias_soma,$objBuscaPedido->data_hora_pedido,$dias_soma,$objBuscaPedido->data_hora_pedido,$objBuscaPedido->data_hora_pedido, $objBuscaPedido->valor_total, 0, $objBuscaPedido->valor_total, $forma_pg, $objBuscaPedido->data_hora_pedido);
                $res_inserir_parcelas = mysql_query($sql_inserir_parcelas);
              }
              else
              {
                $sql_inserir_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos,cod_formas_pg, cod_bancos_destino, data_vencimento, data_pagamento, mes_ref, ano_ref, valor, juros, valor_total, numero_parcela, forma_pagamento, recebido_enviado, situacao, data_hora_criacao, data_emissao) VALUES ('%s','%s','%s', DATE_ADD('%s', INTERVAL '%s' DAY), DATE_ADD('%s', INTERVAL '%s' DAY), MONTH('%s'), YEAR('%s'), '%s', '%s', '%s', 1, '%s', 1, 'PAGO',NOW(),'%s')",
                  $cod_titulos,$cod_formas_pg, $cod_bancos_destino,$objBuscaPedido->data_hora_pedido, $dias_soma,$objBuscaPedido->data_hora_pedido,$dias_soma,$objBuscaPedido->data_hora_pedido,$objBuscaPedido->data_hora_pedido, $objBuscaPedido->valor_total, 0, $objBuscaPedido->valor_total, $forma_pg, $objBuscaPedido->data_hora_pedido);
                $res_inserir_parcelas = mysql_query($sql_inserir_parcelas);
              }
            // echo "<Br>sql_inserir_parcelas:      ".$sql_inserir_parcelas;
            // die();
              

            // Insere os débitos de taxas
              if($obj_buscar_forma_pg->taxa > 0)
              {
                $valor_taxa = ($obj_buscar_forma_pg->taxa * $objBuscaPedido->valor_total * -1) / 100;

                $sql_inserir_titulos = sprintf("INSERT INTO ipi_titulos (cod_pedidos, cod_clientes, cod_pizzarias, cod_titulos_subcategorias, descricao, tipo_cedente_sacado, tipo_titulo, total_parcelas, data_hora_criacao) VALUES ('%s', '%s', '%s', '%s', '%s', 'CLIENTE', 'PAGAR', 1, NOW())",
                  $cod_pedidos, $objBuscaPedido->cod_clientes, $objBuscaPedido->cod_pizzarias, $cod_titulos_subcategorias_taxa, 'Pagamento taxa ref. pedido ' . $cod_pedidos . ' em ' . date('d/m/Y'));
                $res_inserir_titulos = mysql_query($sql_inserir_titulos);
                $cod_titulos = mysql_insert_id();
              //echo "<Br>sql_inserir_titulos:      ".$sql_inserir_titulos;
                if($dias_soma>0)
                {
                  $sql_inserir_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos,cod_formas_pg, cod_bancos_destino, data_vencimento, data_pagamento, mes_ref, ano_ref, valor, juros, valor_total, numero_parcela, forma_pagamento, recebido_enviado, situacao, data_hora_criacao, data_emissao) VALUES ('%s','%s', '%s', DATE_ADD('%s', INTERVAL '%s' DAY), DATE_ADD('%s', INTERVAL '%s' DAY), MONTH('%s'), YEAR('%s'),'%s', '%s', '%s', 1, '%s', 1, 'ABERTO',NOW(), '%s')",
                    $cod_titulos,$cod_formas_pg, $cod_bancos_destino,$objBuscaPedido->data_hora_pedido, $dias_soma,$objBuscaPedido->data_hora_pedido,$dias_soma,$objBuscaPedido->data_hora_pedido,$objBuscaPedido->data_hora_pedido, $valor_taxa, 0, $valor_taxa, $forma_pg, $objBuscaPedido->data_hora_pedido);
                  $res_inserir_parcelas = mysql_query($sql_inserir_parcelas);
              //echo "<Br>sql_inserir_parcelas:      ".$sql_inserir_parcelas;
                }
                else
                {
                  $sql_inserir_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos,cod_formas_pg, cod_bancos_destino, data_vencimento, data_pagamento, mes_ref, ano_ref, valor, juros, valor_total, numero_parcela, forma_pagamento, recebido_enviado, situacao, data_hora_criacao, data_emissao) VALUES ('%s', '%s', '%s', NOW(), DATE_ADD('%s', INTERVAL '%s' DAY), MONTH('%s'), YEAR('%s'),'%s', '%s', '%s', 1, '%s', 1, 'PAGO', NOW(), '%s')",
                    $cod_titulos,$cod_formas_pg, $cod_bancos_destino, $objBuscaPedido->data_hora_pedido,$dias_soma,$objBuscaPedido->data_hora_pedido,$objBuscaPedido->data_hora_pedido, $valor_taxa, 0, $valor_taxa, $forma_pg, $objBuscaPedido->data_hora_pedido);
                  $res_inserir_parcelas = mysql_query($sql_inserir_parcelas);
                }
              // echo "<Br>sql_inserir_parcelas:      ".$sql_inserir_parcelas;
              }


            // Baixando do estoque
              require_once '../../classe/estoque.php';
              $estoque = new Estoque();
              $estoque->lancar_estoque_consumo_pedido($cod_pedidos);
            }
            else 
            {
              mensagemErro('Erro ao BAIXAR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
            }
            
            mensagemOk('O pedido '.$cod_pedidos.' foi BAIXADO com sucesso!');

          }
        }
        else
        {
          mensagemErro('Erro ao BAIXAR o pedido', 'Pedido ja foi baixado.');
        }

        desconectabd($con);
        break;
      }

      $num_pedido = validaVarPost('num_pedido');

      ?>
      <!-- Tab Editar -->
      <script>
        function init()
        {
          $('num_pedido').focus();
        }
        window.addEvent('domready', init);
      </script>

      <table align="center" border="0" width="500" style="margin: 0 auto">
       <tr>
        <td align="right" width="150"><b>Numero do pedido:</b></td>
        <td width="350">
          <form name="frmBuscar" method="post"><input type="text"
           name="num_pedido" id="num_pedido" value="<? echo $num_pedido; ?>"> <input
           type="submit" name="bt_buscar" id="bt_buscar" value="Buscar"
           class="botao"> <input type="hidden" name="acao" value="buscar"></form>

         </td>
       </tr>
       <?
       if ($acao == "buscar")
       {
        $int_num_pedido = (int) $num_pedido;
        $con = conectabd();
        $sql_buscar = "SELECT p.*, c.nome, pi.nome nome_pizzaria,pi.cod_pizzarias FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes=c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias=pi.cod_pizzarias) WHERE p.cod_pedidos = '$int_num_pedido' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
        $res_buscar = mysql_query($sql_buscar);
        $num_buscar = mysql_num_rows($res_buscar);
        $obj_buscar = mysql_fetch_object($res_buscar);
    //echo "<br>1: ".$sql_buscar;
        desconectabd($con);
        
        if ($num_buscar > 0)
        {
          if ( ( ( ( $obj_buscar->situacao=="ENVIADO") || ( ( $obj_buscar->situacao=="IMPRESSO")  && $obj_buscar->tipo_entrega == "Balcão") )&& ($obj_buscar->forma_pg!="VISANET") && ($obj_buscar->forma_pg!="MASTERCARDNET") && ($obj_buscar->forma_pg!="VISANET-CIELO") && ($obj_buscar->forma_pg!="MASTERCARDNET-CIELO"))  || ($obj_buscar->situacao=="CAPTURADO") )
          {
            if (in_array($obj_buscar->cod_pizzarias,$_SESSION['usuario']['cod_pizzarias']))
            {
              ?>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              
              <tr>
                <td align="right"><b>Pizzaria:</b></td>
                <td>
                  <? echo $obj_buscar->nome_pizzaria; ?>
                </td>
              </tr>
              <tr>
                <td align="right"><b>Num. do Pedido:</b></td>
                <td>
                  <? echo $obj_buscar->cod_pedidos; ?>
                </td>
              </tr>
              <tr>
                <td align="right"><b>Nome do Cliente:</b></td>
                <td>
                  <? echo $obj_buscar->nome; ?>
                </td>
              </tr>
              <tr>
                <td align="right"><b>Bairro:</b></td>
                <td>
                  <? echo $obj_buscar->bairro; ?>
                </td>
              </tr>
              <tr>
                <td align="right"><b>Forma de pagamento:</b></td>
                <td>
                  <? echo ($obj_buscar->forma_pg); ?>
                </td>
              </tr>
              <tr>
                <td align="right"><b>Total do Pedido:</b></td>
                <td>
                  R$ <? echo bd2moeda($obj_buscar->valor_total); ?>
                </td>
              </tr>
              <tr>
                <td align="right"><b>Data e Hora do Pedido:</b></td>
                <td>
                  <? echo bd2datahora($obj_buscar->data_hora_pedido); ?>
                </td>
              </tr>

              <script>
                function validar_baixa(frm)
                {
                  if (frm.cod_entregador.value=="")
                  {
                    alert("Selecione um Entregador!");
                    frm.cod_entregador.focus();
                    return false;
                  }
                  
                  if (frm.forma_pg.value=="")
                  {
                    alert("Selecione uma Forma de Pagamento!");
                    frm.forma_pg.focus();
                    return false;
                  }

                  return true;
                }
              </script>
              
              <form name="frm_baixar" method="post"	onsubmit="return validar_baixa(this);">
               <tr>
                <td align="right"><b>Entregador:</b></td>
                <td>
                  <select name="cod_entregador" id="cod_entregador"	style="width: 240px">
                   <option value=""></option>
                   <?
                   $con = conectabd();
                   $SqlBuscaEntregadores = "SELECT * FROM ipi_entregadores WHERE cod_pizzarias IN ($cod_pizzarias_sessao) ORDER BY nome";
                   $resBuscaEntregadores = mysql_query($SqlBuscaEntregadores);
                   while($objBuscaEntregadores = mysql_fetch_object($resBuscaEntregadores)) 
                   {
                    echo '<option value="'.$objBuscaEntregadores->cod_entregadores.'" '.($obj_buscar->cod_entregadores==$objBuscaEntregadores->cod_entregadores?" selected='selected' ":"").'>'.$objBuscaEntregadores->nome.'</option>';
                  }
                  desconectabd($con);
                  ?>
                </select>
              </td>
            </tr> 
            
            <tr>
              <td align="right"><b>Forma de Pagamento:</b></td>
              <td><select name="forma_pg" id="forma_pg" style="width: 240px">
               <option value=""></option>
               <?
               $con = conectabd();
               $SqlBuscaFormaPg = "SELECT fp.* FROM ipi_formas_pg fp inner join ipi_formas_pg_pizzarias fpp on fpp.cod_formas_pg = fp.cod_formas_pg WHERE fpp.cod_pizzarias = ".$obj_buscar->cod_pizzarias." ORDER BY fp.forma_pg";
               $resBuscaFormaPg = mysql_query($SqlBuscaFormaPg);
               while($objBuscaFormaPg = mysql_fetch_object($resBuscaFormaPg)) 
               {
                echo '<option value="'.$objBuscaFormaPg->forma_pg.'" '.($obj_buscar->forma_pg==$objBuscaFormaPg->forma_pg?" selected='selected' ":"").'>'.$objBuscaFormaPg->forma_pg.'</option>';
              }
              desconectabd($con);
              ?>
            </select>
          </td>
        </tr>
        
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        
        <tr>
          <td align="center" colspan="2">
            <input type="submit" name="bt_baixar" id="bt_baixar" value="Baixar" class="botao"> 
            <input type="hidden" name="acao" value="baixar"> 
            <input type="hidden" name="cod_pedidos"	value="<? echo $obj_buscar->cod_pedidos; ?>"></td>
          </tr>
          
        </form>
        
        <?
      }
      else
      {
        ?>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td align="right"><b>Pizzaria:</b></td>
          <td>
            <? echo $obj_buscar->nome_pizzaria; ?>
          </td>
        </tr>
        <tr>
          <td align="right"><b>Num. do Pedido:</b></td>
          <td>
            <? echo $obj_buscar->cod_pedidos; ?>
          </td>
        </tr>
        <tr>
          <td align="right"><b>Nome do Cliente:</b></td>
          <td>
            <? echo $obj_buscar->nome; ?>
          </td>
        </tr>
        <tr>
          <td align="right"><b>Data e Hora do Pedido:</b></td>
          <td>
            <? echo bd2datahora($obj_buscar->data_hora_pedido); ?>
          </td>
        </tr>
        <tr>
          <td align="right"><b>Forma de pagamento:</b></td>
          <td>
            <? echo ($obj_buscar->forma_pg); ?>
          </td>
        </tr>
        <tr>
          <td align="right"><b>Total do Pedido:</b></td>
          <td>
            R$ <? echo bd2moeda($obj_buscar->valor_total); ?>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">Esse pedido não pode ser baixado pois <font color="#FF0000"><b>pertence a outra pizzaria (<? echo $obj_buscar->nome_pizzaria; ?>).</b></font>
          </td>
        </tr>
        <?
      }   
    }
    else
    {
      ?>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td align="right"><b>Pizzaria:</b></td>
        <td>
          <? echo $obj_buscar->nome_pizzaria; ?>
        </td>
      </tr>
      <tr>
        <td align="right"><b>Num. do Pedido:</b></td>
        <td>
          <? echo $obj_buscar->cod_pedidos; ?>
        </td>
      </tr>
      <tr>
        <td align="right"><b>Nome do Cliente:</b></td>
        <td>
          <? echo $obj_buscar->nome; ?>
        </td>
      </tr>
      <tr>
        <td align="right"><b>Data e Hora do Pedido:</b></td>
        <td>
          <? echo bd2datahora($obj_buscar->data_hora_pedido); ?>
        </td>
      </tr>
      <tr>
        <td align="right"><b>Forma de pagamento:</b></td>
        <td>
          <? echo ($obj_buscar->forma_pg); ?>
        </td>
      </tr>
      <tr>
        <td align="right"><b>Total do Pedido:</b></td>
        <td>
          R$ <? echo bd2moeda($obj_buscar->valor_total); ?>
        </td>
      </tr>
      <tr>
        <td colspan="2" align="center">
          <? 
          if ($obj_buscar->situacao == "BAIXADO")
          {
            ?>
            <br>Esse pedido já foi <font color="#FF0000"><b>BAIXADO</b></font>!
            <?        
          }
          else if($obj_buscar->situacao == "IMPRESSO")
          {
            ?>
            Esse pedido não pode ser baixado pois está na <br/>
            situação: <font color="#FF0000"><b><? echo $obj_buscar->situacao; ?></b></font> e deve ser Expedido.
            <?  
          }
          else if($obj_buscar->situacao == "NOVO")
          {
            ?>
            Esse pedido não pode ser baixado pois está na <br/>
            situação: <font color="#FF0000"><b><? echo $obj_buscar->situacao; ?></b></font> e deve ser IMPRESSO.
            <?  
          }
          else
          {
            ?>
            Esse pedido não pode ser baixado pois é Cartão de Crédito NET <br />e 
            está na situação: <font color="#FF0000"><b><? echo $obj_buscar->situacao; ?></b></font> e deve ser Capturado.
            <?
          }
          ?>
        </td>
      </tr>
      <?
    }
  }
  else 
  {
    ?>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2" align="center">Nenhum pedido encontrado para a busca:
        <b><? echo $num_pedido; ?></b></td>
      </tr>
      <?
    }
  }
  ?>
</table>

<? rodape(); ?>
