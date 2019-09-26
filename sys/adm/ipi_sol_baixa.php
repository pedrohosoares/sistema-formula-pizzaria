<?php

/**
 * ipi_sol_baixa.php: Solicitação de Baixa
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../config.php';
require_once '../../ipi_email.php';
cabecalho('Baixa de Pedidos');

$acao = validaVarPost('acao');
$cod_pizzarias = validaVarPost('cod_pizzarias');
$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$cod_pizzarias_sessao =  implode("," , $_SESSION['usuario']['cod_pizzarias']);
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

switch($acao) {
  case 'baixar':

	
	$codigo = validaVarPost($chave_primaria);
    $cod_entregador = validaVarPost('cod_entregador');
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    // Os códigos cod_titulos_subcategorias e cod_bancos_destino são pegos na ipi_configuracoes
    /*$obj_buscar_conf_subcategoria = executaBuscaSimples("SELECT * FROM ipi_configuracoes WHERE chave = 'COD_TITULOS_SUBCATEGORIAS_B'", $con);
    $obj_buscar_conf_bancos_destino = executaBuscaSimples("SELECT * FROM ipi_configuracoes WHERE chave = 'COD_BANCOS_DESTINO_" . $objBuscaPedido->cod_pizzarias . "'", $con);*/
    
   /* $cod_titulos_subcategorias = $obj_buscar_conf_subcategoria->valor;
    $cod_bancos_destino = $obj_buscar_conf_bancos_destino->valor;
    */
    foreach($codigo as $cod_codigo) {
	    $objBuscaPedido = executaBuscaSimples("SELECT * FROM ipi_pedidos p WHERE p.cod_pedidos = $cod_codigo", $con);
	    	
      $cod_pizzarias = $objBuscaPedido->cod_pizzarias;
      require_once("../../pub_req_fuso_horario1.php");
        
	    $SqlUpdate = "UPDATE $tabela SET situacao = 'BAIXADO', data_hora_baixa = NOW() WHERE $chave_primaria = $cod_codigo";
	    $resUpdate = mysql_query($SqlUpdate);
        
        if ($resUpdate) 
        {
						 $obj_buscar_clientes = executaBuscaSimples("SELECT c.cod_clientes, c.cod_clientes_indicador, c.indicador_recebeu_pontos, c.nome, c.email, p.data_hora_pedido,p.valor_total as valor FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (c.cod_clientes = p.cod_clientes) WHERE p.cod_pedidos = $cod_codigo", $con);
            if($objBuscaPedido->origem_pedido == 'NET')
            {
                //Distribuindo os pontos de fidelidade 1 -> 1
              $SqlInsertPontos = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, pontos, data_validade, cod_pedidos) (SELECT cod_clientes, NOW(), FLOOR(valor_total), DATE_ADD(NOW(), INTERVAL 1 YEAR), cod_pedidos FROM $tabela WHERE $chave_primaria = $cod_codigo)";
                $resInsertPontos = mysql_query($SqlInsertPontos);
                        
                if($resInsertPontos)
                {

                    $email_origem = EMAIL_PRINCIPAL;
                  
                    $objBuscaCliente = executaBuscaSimples("SELECT * FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (c.cod_clientes = p.cod_clientes) WHERE p.cod_pedidos = $cod_codigo", $con);
                    
                    // Dando o ponto de fidelidade se quem foi indicado comprou (baixou)
                    if($objBuscaCliente->indicador_recebeu_pontos == 0) {
                      if($objBuscaCliente->cod_clientes_indicador > 0) {
                        $SqlInsertPontosIndica = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, pontos, data_validade, obs) VALUES ('".$obj_buscar_clientes->cod_clientes_indicador."', NOW(), '".(int)$obj_buscar_clientes->valor."', DATE_ADD(NOW(), INTERVAL 1 YEAR), 'Indicação pelo pedido $cod_codigo')";
                        $resInsertPontosIndica = mysql_query($SqlInsertPontosIndica);
                      
                        if(!$resInsertPontosIndica) {
                          mensagemErro('Erro ao adicionar pontos de fidelidade por indicação', 'Por favor, verifique as configurações do cliente: '.bd2texto($objBuscaCliente->nome).'.');
                        }
                        else {
                          $SqlUpdatePontosIndica = "UPDATE ipi_clientes SET indicador_recebeu_pontos = 1 WHERE cod_clientes = '".$objBuscaCliente->cod_clientes."'";
                          $resUpdatePontosIndica = mysql_query($SqlUpdatePontosIndica);
                          
                          $objBuscaClienteIndicador = executaBuscaSimples('SELECT * FROM ipi_clientes WHERE cod_clientes = '.$objBuscaCliente->cod_clientes_indicador, $con);
                          
                          // Envia o email para quem indicou informando que ganhou os pontos.
                          $email_destino = $objBuscaClienteIndicador->email;
                          $assunto = NOME_SITE . " - Você ganhou 80 pontos por indicar!";
                          
                          $texto = "<br><br>Parabéns ".bd2texto($objBuscaClienteIndicador->nome)." pela indicação!";
                          $texto .= "<br><br><br>Seu amigo(a) ".bd2texto($objBuscaCliente->nome)." indicado por você comprou e você ganhou 80 PONTOS DE FIDELIDADE!";
                          $texto .= "<br><br>Indique quantas pessoas quiser e ganhe ainda mais pontos.";
                          
                          $arr_aux = array();
                            $arr_aux['cod_pedidos'] = $codigo;
                            $arr_aux['cod_usuarios'] = 0;
                            $arr_aux['cod_clientes'] = $objBuscaCliente->cod_clientes;
                            $arr_aux['cod_pizzarias'] = $cod_pizzarias;
                            $arr_aux['tipo'] = 'INDIQUE';

                          /*if(!enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'fidelidade'))
                            mensagemErro('Erro ao ENVIAR e-mail de indicação', 'Por favor, verifique as configurações do cliente: '.bd2texto($objBuscaClienteIndicador->nome).'.');*/
                          $con = conectar_bd();
                        }
                      }
                    }
                    
                  
                    
                }
            }

                        //Foi tirado do laço de Cima e passado pra cá pra enviar para qualquer cliente que tenha EMAIL
            if ($obj_buscar_clientes->email!="")
            {
              //if (($obj_buscar_clientes->email!="")&&($obj_buscar_clientes->origem_pedido=="NET"))
              
              // Calculando o checksum
              $cod_enquetes = 1; ////////////////////// ENQUETE FIXADA!
              $cod_clientes = $obj_buscar_clientes->cod_clientes;
              
              $checksum = base64_encode("${cod_enquetes}_${cod_clientes}_${cod_codigo}");
              
              $email_destino = $obj_buscar_clientes->email;
              //$email_destino = "filipegranato@internetsistemas.com.br";
              $assunto = NOME_SITE . " - Responda a nossa ENQUETE e ganhe UMA BORDA RECHEADA!";
              
              $texto = "<br><br>Obrigado ".bd2texto($obj_buscar_clientes->nome)." pela compra, agora só falta você responder nossa enquete e ganhar uma BORDA RECHEADA* na próxima compra!";
              $texto .= "<br><br>Para responder acesse o site <a href=\"http://".HOST."/enquete&checksum=$checksum\">http://".HOST."/enquete&checksum=$checksum</a>.";
              $texto .= "<br><br><br><b>* Cupom válido até o dia ".date("d/m/Y", strtotime("+15 days", strtotime($obj_buscar_clientes->data_hora_pedido)) ).".</b>";
              $texto .= "<br><b>** Você pode responder está enquete também até o dia: ".date("d/m/Y H:i:s", strtotime("+15 days", strtotime($obj_buscar_clientes->data_hora_pedido)) )."</b>";
                              
              //echo "<br>email: ".$texto;
              
                $sql_buscar_pizzaria = "SELECT cod_pizzarias FROM ipi_pedidos WHERE cod_pedidos = '$cod_codigo'";
                $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
                $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);


              $arr_aux['cod_pedidos'] = $cod_codigo;
              $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
              $arr_aux['cod_clientes'] = $cod_clientes;
              $arr_aux['cod_pizzarias'] = $obj_buscar_pizzaria->cod_pizzarias;
              $arr_aux['tipo'] = 'ENQUETE_ENVIADA';
              /*if(!enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'enquete'))
                mensagemErro('Erro ao ENVIAR enquete', 'Por favor, verifique as configurações do cliente: '.bd2texto($obj_buscar_clientes->nome).'.');*/
              $con = conectar_bd();
              /*$sql_log_email = "INSERT INTO ipi_email_automatico (cod_clientes, tipo_email, data_hora_envio) VALUES( '".$cod_clientes."', 'AUMENTAR_FIDELIDADE', '".date("Y-m-d H:i:s")."')";*/
              $res_log_email = mysql_query($sql_log_email);
            }

	          // Mandar email Explicando como aumentar pontos Fidelidade
	          $con = conectar_bd();
	          $sql_email_fidelidade = "SELECT * FROM ipi_fidelidade_clientes WHERE cod_pedidos = '".$cod_codigo."' AND pontos < 0";
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
	              
	              $sql_buscar_pizzaria = "SELECT cod_pizzarias FROM ipi_pedidos WHERE cod_pedidos = '$cod_codigo'";
	              $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
	              $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
	              
	              $arr_aux['cod_pedidos'] = $cod_codigo;
	              $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
	              $arr_aux['cod_clientes'] = $cod_clientes;
	              $arr_aux['cod_pizzarias'] = $obj_buscar_pizzaria->cod_pizzarias;
	              $arr_aux['tipo'] = 'INDIQUE';
	              /*if(!enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'fidelidade'))
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
                                  $cod_codigo, $objBuscaPedido->cod_clientes, $objBuscaPedido->cod_pizzarias, $cod_titulos_subcategorias, 'Recebimento ref. pedido ' . $cod_codigo . ' em ' . date('d/m/Y'));
          $res_inserir_titulos = mysql_query($sql_inserir_titulos);
          //echo "<Br>sql_inserir_titulos:      ".$sql_inserir_titulos;

          $cod_titulos = mysql_insert_id();
          
          if($dias_soma>0)
          {
            $sql_inserir_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos,cod_formas_pg, cod_bancos_destino, data_vencimento, data_pagamento, mes_ref, ano_ref, valor, juros, valor_total, numero_parcela, forma_pagamento, recebido_enviado, situacao, data_hora_criacao) VALUES ('%s', '%s','%s', DATE_ADD('%s', INTERVAL '%s' DAY), DATE_ADD('%s', INTERVAL '%s' DAY), MONTH('%s'), YEAR('%s'), '%s', '%s', '%s', 1, '%s', 1, 'ABERTO',NOW())",
                                        $cod_titulos,$cod_formas_pg, $cod_bancos_destino, $objBuscaPedido->data_hora_pedido,$dias_soma,$objBuscaPedido->data_hora_pedido,$dias_soma,$objBuscaPedido->data_hora_pedido,$objBuscaPedido->data_hora_pedido, $objBuscaPedido->valor_total, 0, $objBuscaPedido->valor_total, $forma_pg);
            $res_inserir_parcelas = mysql_query($sql_inserir_parcelas);
          }else
          {
            $sql_inserir_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos,cod_formas_pg, cod_bancos_destino, data_vencimento, data_pagamento, mes_ref, ano_ref, valor, juros, valor_total, numero_parcela, forma_pagamento, recebido_enviado, situacao, data_hora_criacao) VALUES ('%s','%s','%s', DATE_ADD('%s', INTERVAL '%s' DAY), DATE_ADD('%s', INTERVAL '%s' DAY), MONTH('%s'), YEAR('%s'), '%s', '%s', '%s', 1, '%s', 1, 'PAGO',NOW())",
                                      $cod_titulos,$cod_formas_pg, $cod_bancos_destino,$objBuscaPedido->data_hora_pedido, $dias_soma,$objBuscaPedido->data_hora_pedido,$dias_soma,$objBuscaPedido->data_hora_pedido,$objBuscaPedido->data_hora_pedido, $objBuscaPedido->valor_total, 0, $objBuscaPedido->valor_total, $forma_pg);
            $res_inserir_parcelas = mysql_query($sql_inserir_parcelas);
          }
          //echo "<Br>sql_inserir_parcelas:      ".$sql_inserir_parcelas;
          

          // Insere os débitos de taxas
          if($obj_buscar_forma_pg->taxa > 0)
          {
            $valor_taxa = ($obj_buscar_forma_pg->taxa * $objBuscaPedido->valor_total * -1) / 100;

            $sql_inserir_titulos = sprintf("INSERT INTO ipi_titulos (cod_pedidos, cod_clientes, cod_pizzarias, cod_titulos_subcategorias, descricao, tipo_cedente_sacado, tipo_titulo, total_parcelas, data_hora_criacao) VALUES ('%s', '%s', '%s', '%s', '%s', 'CLIENTE', 'PAGAR', 1, NOW())",
                                    $cod_codigo, $objBuscaPedido->cod_clientes, $objBuscaPedido->cod_pizzarias, $cod_titulos_subcategorias_taxa, 'Pagamento taxa ref. pedido ' . $cod_codigo . ' em ' . date('d/m/Y'));
            $res_inserir_titulos = mysql_query($sql_inserir_titulos);
            $cod_titulos = mysql_insert_id();
            //echo "<Br>sql_inserir_titulos:      ".$sql_inserir_titulos;
            if($dias_soma>0)
            {
              $sql_inserir_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos,cod_formas_pg, cod_bancos_destino, data_vencimento, data_pagamento, mes_ref, ano_ref, valor, juros, valor_total, numero_parcela, forma_pagamento, recebido_enviado, situacao, data_hora_criacao) VALUES ('%s','%s', '%s', DATE_ADD('%s', INTERVAL '%s' DAY), DATE_ADD('%s', INTERVAL '%s' DAY), MONTH('%s'), YEAR('%s'),'%s', '%s', '%s', 1, '%s', 1, 'ABERTO',NOW())",
                                          $cod_titulos,$cod_formas_pg, $cod_bancos_destino,$objBuscaPedido->data_hora_pedido, $dias_soma,$objBuscaPedido->data_hora_pedido,$dias_soma,$objBuscaPedido->data_hora_pedido,$objBuscaPedido->data_hora_pedido, $valor_taxa, 0, $valor_taxa, $forma_pg);
              $res_inserir_parcelas = mysql_query($sql_inserir_parcelas);
              //echo "<Br>sql_inserir_parcelas:      ".$sql_inserir_parcelas;
           }else
           {
              $sql_inserir_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos,cod_formas_pg, cod_bancos_destino, data_vencimento, data_pagamento, mes_ref, ano_ref, valor, juros, valor_total, numero_parcela, forma_pagamento, recebido_enviado, situacao, data_hora_criacao) VALUES ('%s', '%s', '%s', NOW(), DATE_ADD('%s', INTERVAL '%s' DAY), MONTH('%s'), YEAR('%s'),'%s', '%s', '%s', 1, '%s', 1, 'PAGO', NOW())",
                                          $cod_titulos,$cod_formas_pg, $cod_bancos_destino, $objBuscaPedido->data_hora_pedido,$dias_soma,$objBuscaPedido->data_hora_pedido,$objBuscaPedido->data_hora_pedido, $valor_taxa, 0, $valor_taxa, $forma_pg);
              $res_inserir_parcelas = mysql_query($sql_inserir_parcelas);
           }
          // echo "<Br>sql_inserir_parcelas:      ".$sql_inserir_parcelas;
          }


          // Baixando do estoque
          require_once '../../classe/estoque.php';
          $estoque = new Estoque();
          $estoque->lancar_estoque_consumo_pedido($cod_codigo);
        }
	    else {
	      mensagemErro('Erro ao BAIXAR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
	    }
	      
	      mensagemOk('Os pedidos foram BAIXADOS com sucesso!');
	      
	      
	      
	      
	      
	      
	    
    }
    desconectabd($con);
  break;
  case 'cancelar':
    $codigo = validaVarPost($chave_primaria);
    $indicesSql = implode(',', $codigo);
    
    $bool_update = true;
    $bool_fidelidae = true;
    $bool_relatorio = true;
    $con = conectabd();
    
    $sql_verificar = "SELECT * FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao='BAIXADO'";
    $res_verificar = mysql_query($sql_verificar);
    $num_verificar = mysql_num_rows($res_verificar);
    if ($num_verificar>0)
    {
      echo "<div style='color: #FF0000; font-weight: bold; font-size: 14px; text-align: center;'>";
      echo "Os pedidos não podem ser cancelados, pois já foram BAIXADOS: ";
      while ($obj_verificar = mysql_fetch_object($res_verificar))
      {
        echo $obj_verificar->$chave_primaria.", ";
      }
      echo "</div><br /><br />";
    }

    $sql_cancelar = "SELECT * FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao!='BAIXADO'";
    //echo $sql_cancelar."<br/><br/>";
    $res_cancelar = mysql_query($sql_cancelar);
    while($obj_cancelar = mysql_fetch_object($res_cancelar))
    {
      $cod_pizzarias = $obj_cancelar->cod_pizzarias;
      require_once("../../pub_req_fuso_horario1.php");

      $SqlUpdate = "UPDATE $tabela SET situacao = 'CANCELADO', data_hora_baixa = NOW(), data_hora_cancelamento = NOW(), cod_usuarios_cancelamento='".$_SESSION['usuario']['codigo']."' WHERE $chave_primaria IN (".$obj_cancelar->cod_pedidos.") AND situacao <> 'BAIXADO'";

      $SqlEstornoFidelidade = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, data_validade, pontos) (SELECT cod_clientes, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), pontos_fidelidade_total FROM $tabela WHERE $chave_primaria IN (".$obj_cancelar->cod_pedidos.") AND situacao <> 'BAIXADO')";

      $sql_inserir_relatorio = sprintf("INSERT into ipi_impressao_relatorio (cod_pedidos,cod_usuarios,cod_pizzarias,relatorio,data_hora_inicial,situacao) (select p.cod_pedidos,".$_SESSION['usuario']['codigo'].",p.cod_pizzarias,'CANCELAMENTO',NOW(),'NOVO' from ipi_pedidos p WHERE $chave_primaria IN ($indicesSql))");

      //echo $SqlUpdate;
      $bool_update &= mysql_query($SqlUpdate);
      $bool_fidelidae &= mysql_query($SqlEstornoFidelidade);
      $bool_relatorio &= mysql_query($sql_inserir_relatorio);
      //echo "<br/><br/><Br/>".$SqlEstornoFidelidade;

    }
    
    if ($bool_update && $bool_fidelidae && $bool_relatorio)
      mensagemOk('O pedido foi CANCELADO com sucesso!');
    else
      mensagemErro('Erro ao CANCELAR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');

    desconectabd($con);
  break;
  case 'alterar_pg':
    $codigo = validaVarPost($chave_primaria);
    $forma_pg = validaVarPost('forma_pg');
    
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $SqlUpdate = "UPDATE $tabela SET forma_pg = '$forma_pg' WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlUpdate))
      mensagemOk('Forma de pagamento alterado com sucesso!');
    else
      mensagemErro('Erro ao alterar a forma de pagamento o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
  case 'alterar_entregador':
    $codigo = validaVarPost($chave_primaria);
    $cod_entregadores = validaVarPost('cod_entregador');
    
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $SqlUpdate = "UPDATE $tabela SET cod_entregadores = '$cod_entregadores' WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlUpdate))
      mensagemOk('Entregadores alterados com sucesso!');
    else
      mensagemErro('Erro ao alterar o entregador dos pedidos', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
}
$cods_checkar = array();
if($acao=="alterar_entregador" || $acao=="alterar_pg")
{
  $cods_checkar = validaVarPost($chave_primaria);
}
?>


<? if($acao != 'detalhes'): ?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

<script>

function verificaCheckbox(form) {
  var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('situacao')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
   
  if(cInput > 0) {
    if (confirm('Deseja mudar de situação o(s) pedido(s) selecionado(s)?')) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja mudar de situação (BAIXAR / CANCELAR).');
     
    return false;
  }
}

function editar(cod) {
  var form = new Element('form', {
    'action': '<? echo $_SERVER['PHP_SELF'] ?>',
    'method': 'post'
  });
  
  var input1 = new Element('input', {
    'type': 'hidden',
    'name': '<? echo $chave_primaria ?>',
    'value': cod
  });
  
  var input2 = new Element('input', {
    'type': 'hidden',
    'name': 'acao',
    'value': 'detalhes'
  });
  
  input1.inject(form);
  input2.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function baixar() {
  if(verificaCheckbox(document.frmBaixa)) {
    //if(document.frmBaixa.cod_entregador.value > 0) {
      document.frmBaixa.acao.value = "baixar";
      document.frmBaixa.submit();
    //}//
   // else {
   //   alert('Por favor, defina o entregador.');
   // }
  }
}

function cancelar(){
  if(verificaCheckbox(document.frmBaixa)) {
    document.frmBaixa.acao.value = "cancelar";
    document.frmBaixa.submit();
  }
}

function alterar_pg() {
  if(verificaCheckbox(document.frmBaixa)) {
    if(document.frmBaixa.forma_pg.value != "") {
      document.frmBaixa.acao.value = "alterar_pg";
      document.frmBaixa.submit();
    }
    else {
      alert('Por favor, defina a forma de pagamento.');
    }
  }
}

function alterar_entregador() {
  if(verificaCheckbox(document.frmBaixa)) {
    if(document.frmBaixa.cod_entregador.value > 0) {
      document.frmBaixa.acao.value = "alterar_entregador";
      document.frmBaixa.submit();
    }
    else {
      alert('Por favor, defina o entregador.');
    }
  }
}

</script>

<form name="frmFiltroPizzaria" method="post">
  Filtrar baixa para a pizzaria:       
  <select name="cod_pizzarias" id="cod_pizzarias">
    <option value="">Todas as Pizzarias</option>
    <?
    $con = conectabd();
    
    $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias where cod_pizzarias in ($cod_pizzarias_sessao) ORDER BY nome";
    $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
    
    while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
      echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
      
      if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
        echo 'selected';
      
      echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
    }
    
    desconectabd($con);
    ?>
  </select>
  <input type="submit" name="btnEnviar" value="Filtrar" >
</form>

<form name="frmBaixa" method="post">

  <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    <tr>

      <td width="40"><input class="botaoAzul" style="font-weight: bold; color: green;" type="button" value="Baixar" onclick="baixar()"></td>
      
      <td width="30">&nbsp;</td>
      <td width="240">
        <select name="cod_entregador" id="cod_entregador" style="width: 240px">
          <option value=""></option>
          
          <?
          $con = conectabd();
          
          if ($cod_pizzarias)
              $SqlBuscaEntregadores = "SELECT * FROM ipi_entregadores WHERE cod_pizzarias='".validaVarPost('cod_pizzarias')."' and cod_pizzarias in ($cod_pizzarias_sessao) ORDER BY nome";
          else
              $SqlBuscaEntregadores = "SELECT * FROM ipi_entregadores where cod_pizzarias in ($cod_pizzarias_sessao)  ORDER BY nome";
                    $resBuscaEntregadores = mysql_query($SqlBuscaEntregadores);
          
          while($objBuscaEntregadores = mysql_fetch_object($resBuscaEntregadores)) {
            echo '<option value="'.$objBuscaEntregadores->cod_entregadores.'">'.$objBuscaEntregadores->nome.'</option>';
          }
          
          desconectabd($con);
          ?>
          
        </select>
      </td>
      <td><input class="botaoAzul" type="button" value="Alterar Entregador" onclick="alterar_entregador()"></td>
      <td width="30">&nbsp;</td>
      <td width="100">
        <select name="forma_pg" id="forma_pg" style="width: 100px">
          <option value=""></option>
          
          <?
          $con = conectabd();
          
          $SqlBuscaFormaPg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
          $resBuscaFormaPg = mysql_query($SqlBuscaFormaPg);
          
          while($objBuscaFormaPg = mysql_fetch_object($resBuscaFormaPg)) {
            echo '<option value="'.$objBuscaFormaPg->forma_pg.'">'.$objBuscaFormaPg->forma_pg.'</option>';
          }
          
          desconectabd($con);
          ?>
          
        </select>
      </td>
      <td><input class="botaoAzul" type="button" value="Alterar Forma Pg" onclick="alterar_pg()"></td>

       <td width="50"><input class="botaoAzul" style="font-weight: bold; color: red;" type="button" value="Cancelar" onclick="cancelar()"></td>
    </tr>
  </table>

  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"/></td>
        <td align="center" width="80">Pedido</td>
        <td align="center">Cliente</td>
        <td align="center">Pizzaria</td>
        <td align="center">Entregador</td>
        <td align="center">Endereço</td>
        <td align="center" width="70">Horário do Pedido</td>
        <td align="center" width="70">Agendado</td>
        <td align="center" width="70">Forma Pg.</td>
        <td align="center" width="70">Valor Total</td>
        <td align="center" width="70">Origem</td>
      </tr>
    </thead>
    <tbody>
    
    <?
    
    $con = conectabd();
    
    $SqlBuscaPedidos = "SELECT pi.nome nome_pizzaria, p.*,c.*, p.situacao AS pedidos_situacao,en.nome as nome_entregador FROM $tabela p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) LEFT JOIN ipi_entregadores en on en.cod_entregadores = p.cod_entregadores WHERE p.cod_pizzarias in ($cod_pizzarias_sessao) and (p.cod_pizzarias > 0) AND ((p.situacao='IMPRESSO' and p.tipo_entrega='Balcão') or (p.situacao='ENVIADO') or (p.situacao='CAPTURADO'))";// 
    if ($cod_pizzarias)
    	$SqlBuscaPedidos .= " AND pi.cod_pizzarias='".$cod_pizzarias."'";
    
    //echo $SqlBuscaPedidos;
    $SqlBuscaPedidos .= " ORDER BY cod_pedidos";
    $resBuscaPedidos = mysql_query($SqlBuscaPedidos);
    
    while ($objBuscaPedidos = mysql_fetch_object($resBuscaPedidos)) {
      echo '<tr>';
      
      echo '<td align="center">';
	  
      /*if (($objBuscaPedidos->forma_pg=="DINHEIRO") || ($objBuscaPedidos->origem_pedido=="TEL") || (($objBuscaPedidos->forma_pg=="VISANET") &&($objBuscaPedidos->pedidos_situacao=="CAPTURADO")) || (($objBuscaPedidos->forma_pg=="MASTERCARDNET") &&($objBuscaPedidos->pedidos_situacao=="CAPTURADO")) )
      {*/
      if ( ( ( ( $objBuscaPedidos->pedidos_situacao=="ENVIADO") || ( ( $objBuscaPedidos->pedidos_situacao=="IMPRESSO")  && $objBuscaPedidos->tipo_entrega == "Balcão") )&& ($objBuscaPedidos->forma_pg!="VISANET") && ($objBuscaPedidos->forma_pg!="MASTERCARDNET") && ($objBuscaPedidos->forma_pg!="VISANET-CIELO") && ($objBuscaPedidos->forma_pg!="MASTERCARDNET-CIELO"))  || ($objBuscaPedidos->pedidos_situacao=="CAPTURADO") )
      {
		  echo '<input type="checkbox" class="marcar situacao" name="'.$chave_primaria.'[]" '.(in_array($objBuscaPedidos->$chave_primaria,$cods_checkar) ? 'checked="checked"' : '' ).'value="'.$objBuscaPedidos->$chave_primaria.'">';
      }
      echo '</td>';
      
      echo '<td align="center"><a href="ipi_rel_historico_pedidos.php?p='.$objBuscaPedidos->$chave_primaria.'">'.sprintf('%08d', $objBuscaPedidos->$chave_primaria).'</a></td>';
      echo '<td align="center"><a href="ipi_clientes_franquia.php?cc='.$objBuscaPedidos->cod_clientes.'">'.bd2texto($objBuscaPedidos->nome).'</a></td>';
      echo '<td align="center">'.bd2texto($objBuscaPedidos->nome_pizzaria).'</td>';
      echo '<td align="center">'.bd2texto($objBuscaPedidos->nome_entregador).'</td>';
      
      if($objBuscaPedidos->tipo_entrega=="Entrega")
      {
        echo '<td align="center">'.bd2texto($objBuscaPedidos->bairro).', '.bd2texto($objBuscaPedidos->endereco).' '.bd2texto($objBuscaPedidos->numero).' comp.:'.bd2texto($objBuscaPedidos->complemento).' , CEP:'.$objBuscaPedidos->cep.'</td>';
      }
      else
      {
        echo '<td align="center">Balcão</td>';
      }
      echo '<td align="center">'.bd2datahora($objBuscaPedidos->data_hora_pedido).'</td>';
      if($objBuscaPedidos->agendado == '1')
      {
          echo '<td align="center">'.bd2texto($objBuscaPedidos->horario_agendamento).'</td>';    
      }
      else
      {
          echo '<td align="center">NÃO</td>';
      }
      echo '<td align="center">'.$objBuscaPedidos->forma_pg.'</td>';
      echo '<td align="center">'.$objBuscaPedidos->valor_total.'</td>';
      echo '<td align="center">'.$objBuscaPedidos->origem_pedido.'</td>';
      echo '</tr>';
    }
    
    desconectabd($con);
    
    ?>
    
    </tbody>
  </table>

  <input type="hidden" name="acao" value="">
</form>

<? endif; ?>

<? rodape(); ?>
