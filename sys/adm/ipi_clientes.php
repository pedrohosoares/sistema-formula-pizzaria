<?php

/**
 * Tela de consulta e alteração de dados de clientes.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       24/05/2010   FELIPE        Criado.
 * 1.0       17/09/2012   PEDRO H       Adicionado 'quicklink' pro perfil do FB, se vinculado.
 *
 */
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once 'ipi_req_quant_vendidas.php';

cabecalho('Cadastro de Clientes');

$tabela = 'ipi_clientes';
$chave_primaria = 'cod_clientes';
$quant_pagina = 50;
$cod_enquetes = 1; // Forçado para a enquete mail

$acao = validaVarPost('acao');

$cc = validaVarGet('cc');
if ($cc)
{
  $codigo = $cc;
  $acao = "detalhes";
}

switch($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indicesSql = implode(',',$excluir);
        
        $con = conectabd();
        
        $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
        
        if(mysql_query($SqlDel))
        {
            mensagemOk('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemErro('Erro ao excluir os registros','Por favor, verifique se o cliente não está vinculado a algum pedido.');
        }
        
        desconectabd($con);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
        $observacao = texto2bd(validaVarPost('observacao'));
        $situacao = texto2bd(validaVarPost('situacao'));
        $nova_senha = texto2bd(validaVarPost('nova_senha'));
        
        if($codigo > 0)
        {
            $con = conectabd();
            
            if(trim($nova_senha) != '')
            {
                $update_senha = ", senha = MD5('" . texto2bd($nova_senha) . "')";
            }
            
            $SqlUpdate = sprintf("UPDATE $tabela SET observacao = '%s', situacao = '%s' $update_senha WHERE $chave_primaria = $codigo",$observacao,$situacao);
            
            if(mysql_query($SqlUpdate))
            {
                mensagemOk('Os registros selecionados foram alterados com sucesso!');
            }
            else
            {
                mensagemErro('Erro ao alterar os registros','Por favor, contacte a equipe de suporte informando o cliente.');
            }
            
            desconectabd($con);
        }
        break;
    case 'detalhes':
      if (!$cc)
      {
        $codigo = validaVarPost($chave_primaria);
      }
      $news = validaVarPost('news');
      $vip = validaVarPost('vip');
      if ($news !="")
      {

            $conexao = conectar_bd();
            $codigo = validaVarPost($chave_primaria);
            $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

            $observacao = validaVarPost('obs_newsletter');

            $obj_buscar_clientes = executaBuscaSimples("SELECT * FROM ipi_clientes c WHERE $chave_primaria = '$codigo' AND c.cod_clientes IN (SELECT p.cod_clientes FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes AND p.cod_pizzarias IN(".$cod_pizzarias_usuario."))", $conexao);
            $sql_buscar_newsletter = "SELECT ativo FROM ine_emails_cadastro WHERE email = '".$obj_buscar_clientes->email."'";
            $res_buscar_newsletter = mysql_query($sql_buscar_newsletter);
            $num_buscar_newsletter = mysql_num_rows($res_buscar_newsletter);

            if($num_buscar_newsletter > 0)
            {
                $obj_buscar_newsletter = mysql_fetch_object($res_buscar_newsletter);
                $sql_atualizar_newsletter = "UPDATE ine_emails_cadastro SET ativo = '".($obj_buscar_newsletter->ativo == 1 ? 0 : 1)."' WHERE email = '".$obj_buscar_clientes->email."'";
                mysql_query($sql_atualizar_newsletter);
                echo ' <script> alert("Operação concluída com sucesso!"); </script>';
            }
            else
            {
                if ($obj_buscar_clientes->email !="")
                {   
                    $sql_cadastrar_newsletter = "INSERT INTO ine_emails_cadastro(email, ativo, cod_ligacao) VALUES ('".$obj_buscar_clientes->email."', '1', '".$obj_buscar_clientes->cod_clientes."')";
                    mysql_query($sql_cadastrar_newsletter);
                    echo ' <script> alert("Operação concluída com sucesso!"); </script>';
                }
                else
                {
                    echo '<script>alert("Cliente não possui email cadastrado!")</script>';
                }
                
            }

            desconectar_bd($conexao);

        //     $acao = '';
        // break;
      }

      if ($vip!="")
      {
            $conexao = conectar_bd();
            $codigo = validaVarPost($chave_primaria);
            $vip = validaVarPost("vip");
            $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

            $sql_atualizar_cliente = "UPDATE ipi_clientes c set c.cod_vip = '$vip' WHERE $chave_primaria = '$codigo' AND c.cod_clientes IN (SELECT p.cod_clientes FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes AND p.cod_pizzarias IN(".$cod_pizzarias_usuario."))";
            // echo $sql_atualizar_cliente;
            $res_atualizar_cliente = mysql_query($sql_atualizar_cliente);

            $sql_inserir_clientes_log = sprintf("INSERT INTO ipi_clientes_log (cod_clientes,cod_usuarios_alteracao,tipo_alteracao,valor_novo,data_hora_alteracao) values (%d,%d,'%s','%s',NOW())",$codigo,$_SESSION['usuario']['codigo'],'CLASSIFICACAO_VIP',$vip);
            $res_inserir_clientes_log = mysql_query($sql_inserir_clientes_log);

            desconectar_bd($conexao);
            ?>
                <script> alert("Operação concluída com sucesso!"); </script>
            <?
            // $acao = '';

      }
    
    	$conexao = conectabd();
    
    	$obj_buscar_clientes = executaBuscaSimples("SELECT * FROM ipi_clientes c WHERE $chave_primaria = '$codigo'", $conexao);
    	$obj_buscar_total = executaBuscaSimples("SELECT SUM(valor_total) AS total FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (c.cod_clientes = p.cod_clientes) WHERE c.$chave_primaria = '$codigo' AND p.situacao = 'BAIXADO'", $conexao);
    	$obj_buscar_ticket_total = executaBuscaSimples("SELECT COUNT(*) AS total FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (c.cod_clientes = p.cod_clientes) WHERE c.$chave_primaria = '$codigo' AND p.situacao = 'BAIXADO'", $conexao);
    	$obj_buscar_ticket_medio = executaBuscaSimples("SELECT AVG(valor_total) AS total FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (c.cod_clientes = p.cod_clientes) WHERE c.$chave_primaria = '$codigo' AND p.situacao = 'BAIXADO'", $conexao);
    	?>
    	
    	<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_interna.css" />
    	<script type="text/javascript" src="../lib/js/tabs_interna.js"></script>
    	<script type="text/javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>
    	
    	<script type="text/javascript">
        	function trocar(id_trocar)
        	{
        	    if (document.getElementById(id_trocar).style.display == 'none')
        	    {
        	        document.getElementById(id_trocar).style.display = 'block';
        	    }
        	    else
        	    {
        	        document.getElementById(id_trocar).style.display = 'none';
        	    }
        	}
    	
        	function detalhes_pedido(cod_pedidos)
        	{
        		var iebody = (document.compatMode && document.compatMode != "BackCompat") ? document.documentElement : document.body;
        	    var dsoctop = document.all ? iebody.scrollTop : pageYOffset;
        	    
        	    var divFundo = new Element('div', 
        	    {
        	        'id': 'divFundo',
        	        'styles': 
        	        {
        	            'position': 'absolute',
        	            'top': 0,
        	            'left': 0,
        	            'background-color': '#ffffff',
        	            'height': document.documentElement.clientHeight + dsoctop,
        	            'width':  document.documentElement.clientWidth,
        	            'z-index': 99998,
        	            'background-color': '#FFFFFF'
        	        }
        	    });
        	    
        	    var divMsg = new Element('div', 
        	    {
        	        'id': 'divMsg',
        	        'styles': 
        	        {
        	            'position': 'absolute',
        	            'left': (document.body.clientWidth - 800) / 2,
        	            'background-color': '#ffffff',
        	            'border': '2px solid #D44E08',
        	            'width' : 800,
        	            'height': 500,
        	            'padding': 20,
        	            'z-index': 99999,
        	            'overflow': 'auto'
        	        }
        	    });
        	    
        	    var win = window;
        	    var middle = win.getScrollTop() + (win.getHeight() / 2);
        	    var top = Math.max(0, middle - (500 / 2));
        	              
        	    divMsg.setStyle('top', top);
        	    
        	    var url = 'acao=detalhes_pedido&cod_pedidos=' + cod_pedidos;
        	    divMsg.set("html","<center>Aguarde, Carregando...</center>");
                divFundo.setStyle('opacity', 0.7);
                
                $(document.body).adopt(divMsg);
                $(document.body).adopt(divFundo);
        	    new Request.HTML(
        	    {
        	        url: 'ipi_clientes_ajax.php',
        	        update: divMsg,
        	        onComplete: function()
        	        {
        				        	
        	        }
        	    }).send(url);
        	}

        	function fechar_detalhes_pedidos()
        	{
				$('divMsg').destroy();
				$('divFundo').destroy();
        	}
    	
    		window.addEvent('domready', function()
    		{
        		var tabs_internas = new TabsInterna('tabs_internas');

        		//var score = new FusionCharts('../lib/swf/fusioncharts/angulargauge.swf', 'score', 350, 203, 0, 0, 'ffffff', 0);
        		var score = new FusionCharts('../lib/swf/fusioncharts/hbullet.swf', 'score', 380, 70, 0, 0, 'ffffff', 0);
        		score.setDataURL('ipi_clientes_dados.php?param=1,<? echo $codigo; ?>');
        		score.render('score');
        		
        		var ticket = new FusionCharts('../lib/swf/fusioncharts/scrollcolumn2d.swf', 'grafico_ticket', 900, 250, 0, 0, 'ffffff', 0);
        		ticket.setDataURL('ipi_clientes_dados.php?param=2,<? echo $codigo; ?>');
        		ticket.render('grafico_ticket');
        		
        		var sabores = new FusionCharts('../lib/swf/fusioncharts/doughnut2d.swf', 'sabores', 300, 300, 0, 0, 'ffffff', 0);
        		sabores.setDataURL('ipi_clientes_dados.php?param=5,<? echo $codigo; ?>');
        		sabores.render('grafico_sabores');
        		
        		var preferencia_dia = new FusionCharts('../lib/swf/fusioncharts/msline.swf', 'preferencia_dia', 450, 300, 0, 0, 'ffffff', 0);
        		preferencia_dia.setDataURL('ipi_clientes_dados.php?param=6,<? echo $codigo; ?>');
        		preferencia_dia.render('grafico_preferencia_dia');
        		
        		var preferencia_hora = new FusionCharts('../lib/swf/fusioncharts/msline.swf', 'preferencia_hora', 450, 300, 0, 0, 'ffffff', 0);
        		preferencia_hora.setDataURL('ipi_clientes_dados.php?param=7,<? echo $codigo; ?>');
        		preferencia_hora.render('grafico_preferencia_hora');
        		
        		var forma_pedido = new FusionCharts('../lib/swf/fusioncharts/stackedbar2d.swf', 'forma_pedido', 450, 300, 0, 0, 'ffffff', 0);
        		forma_pedido.setDataURL('ipi_clientes_dados.php?param=8,<? echo $codigo; ?>');
        		forma_pedido.render('grafico_forma_pedido');
        		
        		var tamanhos = new FusionCharts('../lib/swf/fusioncharts/column3d.swf', 'tamanhos', 444, 300, 0, 0, 'ffffff', 0);
        		tamanhos.setDataURL('ipi_clientes_dados.php?param=9,<? echo $codigo; ?>');
        		tamanhos.render('grafico_tamanhos');

        		var adicionais = new FusionCharts('../lib/swf/fusioncharts/doughnut2d.swf', 'grafico_sabores', 300, 300, 0, 0, 'ffffff', 0);
        		adicionais.setDataURL('ipi_clientes_dados.php?param=3,<? echo $codigo; ?>');
        		adicionais.render('grafico_adicionais');
        		
/*        		var bordas = new FusionCharts('../lib/swf/fusioncharts/doughnut2d.swf', 'grafico_bordas', 300, 300, 0, 0, 'ffffff', 0);
        		bordas.setDataURL('ipi_clientes_dados.php?param=4,<? echo $codigo; ?>');
        		bordas.render('grafico_bordas');*/
        		
    		});
    	</script>
    	
    	<table width="100%" border="0">
    		<tr>
    			<td valign="top" width="10%" align="center">
            <table>                        
              <?
                $sql_cadastro_social = "SELECT url_cliente_site FROM ipi_clientes_redes_sociais WHERE cod_clientes = '".$codigo."' AND status='ATIVO'";
                $res_cadastro_social = mysql_query($sql_cadastro_social);
                $obj_cadastro_social = mysql_fetch_object($res_cadastro_social);
                echo '<tr><td align="left"><img src="'.($obj_cadastro_social->url_cliente_site ? 'http://graph.facebook.com/'.$obj_cadastro_social->url_cliente_site.'/picture' : '../../sys/lib/img/principal/blank_avatar.gif').'" height="50" width="50" /></td></tr>';
                echo '<tr><td>&nbsp;</td></tr>';
                echo '<tr><td>&nbsp;</td></tr>';
                if($obj_cadastro_social->url_cliente_site)
                {
                  echo '<tr><td align="left"><a href="http://www.facebook.com.br/'.$obj_cadastro_social->url_cliente_site.'" title="Ver perfil desse usuário" target="_blank"><img src="../../img/pc/home_facebook_curta.png" alt="Ver perfil desse usuário" /></a></td></tr>';
                }
              ?>
              </table>
          </td>

          <td valign="top" width="40%" align="center">

            <table border="0" width="500">
              <?php if ($obj_buscar_clientes->nome) { ?>
                <tr><td colspan="2" align="center" style="padding-bottom: 20px; color: #E26A11;"><h1><?php echo bd2texto($obj_buscar_clientes->nome) ?></h1></td></tr><?php } ?>
              <?php if ($obj_buscar_clientes->email) { ?>
                <tr><td align="right"><label>E-mail: </label></td><td align="left"> <?php echo bd2texto($obj_buscar_clientes->email) ?></td></tr><?php } ?>
              <?php if ($obj_buscar_clientes->cpf) { ?>
                <tr><td align="right"><label>CPF: </label></td><td align="left"> <?php echo bd2texto($obj_buscar_clientes->cpf) ?></td></tr><?php } ?>
              <?php if ($obj_buscar_clientes->celular) { ?>
                <tr><td align="right"><label>Celular: </label></td><td align="left"> <?php echo bd2texto($obj_buscar_clientes->celular) ?></td></tr><?php } ?>
              <?php if ( ($obj_buscar_clientes->nascimento) && ($obj_buscar_clientes->nascimento!="0000-00-00")) { ?>
                <tr><td align="right"><label>Data Nascimento: </label></td><td align="left"> <?php echo bd2data($obj_buscar_clientes->nascimento) ?></td></tr><?php } ?>
              <?php if ($obj_buscar_clientes->ultimo_acesso) { ?>
                <tr><td align="right"><label>Último Acesso: </label></td><td align="left"> <?php echo bd2datahora($obj_buscar_clientes->ultimo_acesso) ?></td></tr><?php } ?>
              <?php if ($obj_buscar_clientes->situacao) { ?>
                <tr><td align="right"><label>Situação: </label></td><td align="left"> <?php echo bd2texto($obj_buscar_clientes->situacao) ?></td></tr><?php } ?>
              <?php if ($obj_buscar_clientes->origem_cliente) { ?>
                <tr><td align="right"><label>Cadastro feito via: </label></td><td align="left"> <?php echo bd2texto($obj_buscar_clientes->origem_cliente) ?></td></tr><?php } ?>
              <?php if ($obj_buscar_clientes->observacao) { ?>
                <tr><td align="right"><label>Observações: </label></td><td align="left"> <?php echo bd2texto($obj_buscar_clientes->observacao) ?></td></tr><?php } ?>
              <tr><td colspan="2" align="center"><form style="float: left;"><br /><input type="submit" value="&lt;&lt; Outro Cliente" class="botaoAzul" style="width: 110px;"></form></td></tr>
            </table>

    			</td>

    			<td id="score" valign="top" width="30%" align="center"></td>

    			<td width="20%" valign="top" align="center">
    				<table cellspacing="10">
    					<tr><td style=" padding: 5px; border: 2px solid #6F2700; background-color: #E26A11; color: white;"><p style="color: white;"><em>Ticket Total:</em></p><h1><?php echo bd2moeda($obj_buscar_total->total) ?></h1></td></tr>
    					<tr><td style=" padding: 5px; border: 2px solid #6F2700; background-color: #E26A11; color: white;"><p style="color: white;"><em>Ticket Médio:</em></p><h1><?php echo bd2moeda($obj_buscar_ticket_medio->total) ?></h1></td></tr>
    					<tr><td style=" padding: 5px; border: 2px solid #6F2700; background-color: #E26A11; color: white;"><p style="color: white;"><em>Quant. Pedidos<br/>Concluidos:</em></p><h1><?php echo $obj_buscar_ticket_total->total ?></h1></td></tr>
					</table>
    			</td>
    			
    		</tr>
    	</table>
    	
    	<div id="tabs_internas" style="width: 100%; min-width: 700px;">
    		<div class="menuTabInterno">
    			<ul>
    				<li><a href="javascript:;">Indicadores</a></li>
    				<li><a href="javascript:;">Pedidos</a></li>
    				<li><a href="javascript:;">Pontos Fidelidade</a></li>
    				<li><a href="javascript:;">Enquete</a></li>
            <li><a href="javascript:;">Endereços</a></li>
            <li><a href="javascript:;">Cupons</a></li>
            <li><a href="javascript:;">Ações</a></li>
    			</ul>
    		</div>
    		
    		<div class="painelTabInterno" align="center">

				<table>
					<tr>
						<td colspan="3" id="grafico_ticket"></td>
					</tr>
					<tr>
						<td id="grafico_adicionais"></td>
						<td id="grafico_bordas"></td>
						<td id="grafico_sabores"></td>
					</tr>
				</table>	
				<table>	
					<tr>
						<td id="grafico_preferencia_dia"></td>
						<td colspan="2" id="grafico_preferencia_hora"></td>
					</tr>
					<tr>
						<td colspan="2" id="grafico_forma_pedido"></td>
						<td id="grafico_tamanhos" style="border: 1px solid #CCCCCC;"></td>
					</tr>
				</table>
				
    		</div>
    		
    		<div class="painelTabInterno">
                <? 
                    $sql_buscar_qtd_pedidos = "SELECT 
                    ( SELECT count(p.cod_pedidos) FROM ipi_pedidos p LEFT JOIN ipi_entregadores e ON (p.cod_entregadores = e.cod_entregadores) INNER JOIN ipi_pizzarias pi ON(p.cod_pizzarias=pi.cod_pizzarias) WHERE cod_clientes = '$codigo') qtd_totais,
                    ( SELECT count(p.cod_pedidos)FROM ipi_pedidos p LEFT JOIN ipi_entregadores e ON (p.cod_entregadores = e.cod_entregadores) INNER JOIN ipi_pizzarias pi ON(p.cod_pizzarias=pi.cod_pizzarias) WHERE cod_clientes = '$codigo' AND p.situacao='BAIXADO') qtd_finalizados,
                    ( SELECT count(p.cod_pedidos)FROM ipi_pedidos p LEFT JOIN ipi_entregadores e ON (p.cod_entregadores = e.cod_entregadores) INNER JOIN ipi_pizzarias pi ON(p.cod_pizzarias=pi.cod_pizzarias) WHERE cod_clientes = '$codigo' AND p.situacao='CANCELADO') qtd_cancelados,
                    ( SELECT count(p.cod_pedidos)FROM ipi_pedidos p LEFT JOIN ipi_entregadores e ON (p.cod_entregadores = e.cod_entregadores) INNER JOIN ipi_pizzarias pi ON(p.cod_pizzarias=pi.cod_pizzarias) WHERE cod_clientes = '$codigo' AND p.situacao!='BAIXADO' AND p.situacao!='CANCELADO') qtd_outros";

                    $res_buscar_qtd_pedidos = mysql_query($sql_buscar_qtd_pedidos);
                    $obj_buscar_qtd_pedidos = mysql_fetch_object($res_buscar_qtd_pedidos);

                    $qtd_pedidos = $obj_buscar_qtd_pedidos->qtd_totais;
                    $qtd_pedidos_finalizados = $obj_buscar_qtd_pedidos->qtd_finalizados;
                    $qtd_pedidos_cancelados = $obj_buscar_qtd_pedidos->qtd_cancelados;
                    $qtd_pedidos_outros = $obj_buscar_qtd_pedidos->qtd_outros;
                
                ?>
                <table class="listaEdicao" align="center" cellpadding="0" style='width:600px' cellspacing="0">
                    <thead>
                        <tr>
                            <td align="center" style='width:150px'>Pedidos Totais</td>
                            <td align="center" style='width:150px'>Pedidos Concluidos</td>
                            <td align="center" style='width:150px'>Pedidos Cancelados</td>
                            <td align="center" style='width:150px'>Pedidos em andamento</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td align="center" ><? echo $qtd_pedidos ; ?></td>
                            <td align="center" ><? echo $qtd_pedidos_finalizados ; ?></td>
                            <td align="center" ><? echo $qtd_pedidos_cancelados ; ?></td>
                            <td align="center" ><? echo $qtd_pedidos_outros ; ?></td>
                        </tr>
                    </tbody>
                </table>
                <br/>
    			<table class="listaEdicao" cellpadding="0" cellspacing="0">
                	<thead>
                		<tr>
                			<td align="center" width="80">Pedido</td>
                			<td align="center">Detalhes</td>
                			<td align="center" width="100">Pizzaria</td>
                			<td align="center" width="100">Entregador</td>
                			<td align="center" width="100">Data e Hora</td>
                			<td align="center" width="100">Total</td>
                			<td align="center" width="100">Forma Pgto</td>
                			<td align="center" width="50">Origem</td>
                			<td align="center" width="100">Situação</td>
                		</tr>
                	</thead>
                	<tbody>
                		<?php 
                		$sql_buscar_pedidos = "SELECT p.*, pi.nome AS nome_pizzaria, e.nome AS entregadores_nome FROM ipi_pedidos p LEFT JOIN ipi_entregadores e ON (p.cod_entregadores = e.cod_entregadores) INNER JOIN ipi_pizzarias pi ON(p.cod_pizzarias=pi.cod_pizzarias) WHERE cod_clientes = '$codigo'  ORDER BY cod_pedidos DESC";
                		$res_buscar_pedidos = mysql_query($sql_buscar_pedidos);
                		
                		while($obj_buscar_pedidos = mysql_fetch_object($res_buscar_pedidos))
                		{
                			echo '<tr>';
                			echo '<td align="center"><b><a href="javascript:;" onclick="detalhes_pedido(' . $obj_buscar_pedidos->cod_pedidos . ')">' . sprintf('%08d', $obj_buscar_pedidos->cod_pedidos) . '</a></b></td>';
                			
                			echo '<td>';
                			
                			$sql_buscar_quant_pizzas = "SELECT COUNT(*) AS quant FROM ipi_pedidos_pizzas pp WHERE pp.cod_pedidos = '$obj_buscar_pedidos->cod_pedidos'";
                			$res_buscar_quant_pizzas = mysql_query($sql_buscar_quant_pizzas);
                			$obj_buscar_quant_pizzas = mysql_fetch_object($res_buscar_quant_pizzas);
                			
                			$sql_buscar_quant_bebidas = "SELECT COUNT(*) AS quant FROM ipi_pedidos_bebidas pb WHERE pb.cod_pedidos = '$obj_buscar_pedidos->cod_pedidos'";
                			$res_buscar_quant_bebidas = mysql_query($sql_buscar_quant_bebidas);
                			$obj_buscar_quant_bebidas = mysql_fetch_object($res_buscar_quant_bebidas);
                			
                			echo '<b>Quantidade de Pizzas: </b>' . $obj_buscar_quant_pizzas->quant . '<br>';
                			echo '<b>Quantidade de Bebidas: </b>' . $obj_buscar_quant_bebidas->quant;
                			
                			echo '</td>';
                			
                			echo '<td align="center"><b>' . bd2texto($obj_buscar_pedidos->nome_pizzaria) . '</b></td>';
                			
                			if($obj_buscar_pedidos->tipo_entrega == 'Entrega')
                			{
                				echo '<td align="center"><b>' . bd2texto($obj_buscar_pedidos->entregadores_nome) . '</b></td>';
                			}
                			else
                			{
                				echo '<td align="center"><b>Balcão</b></td>';
                			}
                			
                			echo '<td align="center"><b>' . bd2datahora($obj_buscar_pedidos->data_hora_pedido) . '</b></td>';
                			echo '<td align="center"><b>' . bd2moeda($obj_buscar_pedidos->valor_total) . '</b></td>';
                			echo '<td align="center"><b>' . bd2texto($obj_buscar_pedidos->forma_pg) . '</b></td>';
                			echo '<td align="center"><b>' . bd2texto($obj_buscar_pedidos->origem_pedido) . '</b></td>';
                			echo '<td align="center"><b>' . bd2texto($obj_buscar_pedidos->situacao) . '</b></td>';
                			echo '</tr>';	
                		}
                		
                		?>
                	</tbody>
            	</table>
    		</div>
    		
    		<div class="painelTabInterno">
    			<?php 
    			
    			//$obj_buscar_fidelidade = executaBuscaSimples("SELECT SUM(pontos) AS soma_pontos FROM ipi_fidelidade_clientes WHERE cod_clientes = $codigo AND (data_validade > NOW() OR data_validade = '0000-00-00' OR data_validade IS NULL) ORDER BY data_hora_fidelidade DESC", $conexao);
                $obj_buscar_fidelidade = executaBuscaSimples("SELECT SUM(pontos) AS soma_pontos FROM ipi_fidelidade_clientes WHERE cod_clientes = $codigo ORDER BY data_hora_fidelidade DESC", $conexao);
    			
				$soma = ($obj_buscar_fidelidade->soma_pontos > 0) ? $obj_buscar_fidelidade->soma_pontos : 0;
    			
				echo '<center><h1>Total: ' . $soma . '</h1></center>';
				
    			?>
    			<br>
    			<table class="listaEdicao" cellpadding="0" cellspacing="0" style="margin: 0px auto; width: 600px;">
                	<thead>
                		<tr>
                			<td align="center">Pontos</td>
                			<td align="center">Data e Hora</td>
                			<td align="center">Validade</td>
                			<td align="center">Pedido</td>
                		</tr>
                	</thead>
                	<tbody>
                		<?php 
                		
                		$sql_buscar_pontos = "SELECT * FROM ipi_fidelidade_clientes WHERE cod_clientes = '$codigo' AND pontos <> 0 ORDER BY data_hora_fidelidade DESC";
                		$res_buscar_pontos = mysql_query($sql_buscar_pontos);
                		
                		while($obj_buscar_pontos = mysql_fetch_object($res_buscar_pontos))
                		{
                			echo '<tr>';
                    		echo '<td align="center"><b>' . $obj_buscar_pontos->pontos . '</b></td>';
                    		echo '<td align="center">' . bd2datahora($obj_buscar_pontos->data_hora_fidelidade) . '</td>';
                    		echo '<td align="center">' . bd2data($obj_buscar_pontos->data_validade) . '</td>';
                    		echo '<td align="center">' . sprintf('%08d', $obj_buscar_pontos->cod_pedidos) . '</td>';
                    		echo '</tr>';
                		}
                		
                		?>
                	</tbody>
            	</table>
    		</div>
    		<div class="painelTabInterno">
    			<?php
    			
    			$sql_buscar_perguntas = "SELECT * FROM ipi_enquete_perguntas WHERE cod_enquetes = '$cod_enquetes' ORDER BY cod_enquete_perguntas";
                $res_buscar_perguntas = mysql_query($sql_buscar_perguntas);
                
                $num_grafico = 0;
                
                while ($obj_buscar_perguntas = mysql_fetch_object($res_buscar_perguntas)):
                	
            	$sql_buscar_respostas = "SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas = '$obj_buscar_perguntas->cod_enquete_perguntas'";
                $res_buscar_respostas = mysql_query($sql_buscar_respostas);
                
                echo '<table class="listaEnquete" border="0" cellspacing="0">';
                
                echo '<tr>';
                echo '<td align="center" colspan="2"><b>' . bd2texto($obj_buscar_perguntas->pergunta) . '</b></td>';
                echo '</tr>';
                
                echo '<tr>';
                echo '<td width="40" align="center"><b>Votos</b></td>';
                echo '<td align="center"><b>Resposta</b></td>';
                echo '</tr>';
                
                while ($obj_buscar_respostas = mysql_fetch_object($res_buscar_respostas))
                {
                    $sql_buscar_numero_respostas = "SELECT COUNT(cod_enquete_respostas) quantidade FROM ipi_clientes_ipi_enquete_respostas cer LEFT JOIN ipi_pedidos p ON (cer.cod_pedidos=p.cod_pedidos) WHERE cer.cod_clientes = '$codigo' AND cer.cod_enquete_respostas = '$obj_buscar_respostas->cod_enquete_respostas'";
                    $obj_buscar_numero_respostas = executaBuscaSimples($sql_buscar_numero_respostas, $conexao);
                    
                    $quantidade = ($obj_buscar_numero_respostas->quantidade > 0) ? $obj_buscar_numero_respostas->quantidade : 0;
                    
                    echo '<tr>';
                    echo '<td align="right">' . $quantidade . '</td>';
                    echo '<td>';
                    
                    if ($obj_buscar_respostas->justifica)
                    {
                        echo "<a href='javascript:trocar(\"tabRespJus" . $obj_buscar_respostas->cod_enquete_respostas . "\");' title='Clique para as justificativas'>" . $obj_buscar_respostas->resposta . "</a>";
                    }
                    else
                    {
                        echo $obj_buscar_respostas->resposta;
                    }
                    
                    if ($obj_buscar_respostas->justifica)
                    {
                        echo '<div id="tabRespJus' . $obj_buscar_respostas->cod_enquete_respostas . '" style="display: none;"><br>';
                        
                        $sql_buscar_detalhes = "SELECT cer.*, c.nome, p.*, e.nome AS entregadores_nome FROM ipi_clientes_ipi_enquete_respostas cer INNER JOIN ipi_clientes c ON (c.cod_clientes=cer.cod_clientes) INNER JOIN ipi_pedidos p ON (cer.cod_pedidos = p.cod_pedidos) LEFT JOIN ipi_entregadores e ON (p.cod_entregadores = e.cod_entregadores) WHERE cer.cod_enquete_respostas = '$obj_buscar_respostas->cod_enquete_respostas' AND cer.cod_clientes = '$codigo'";
                        $res_buscar_detalhes = mysql_query($sql_buscar_detalhes);
                        $num_buscar_detalhes = mysql_num_rows($res_buscar_detalhes);
                        
                        if ($num_buscar_detalhes > 0)
                        {
                            echo '<table class="listaEnquete" cellspacing="0" align="center" style="margin: 0px auto;">';
                            
                            echo '<thead>';
                            echo '<tr>';
                            echo '<td align="center"><b>Justificativa</b></td>';
                            echo '<td width="80" align="center"><b>Data Resposta</b></td>';
                            echo '<td width="80" align="center"><b>Data do Pedido</b></td>';
                            echo '<td width="80" align="center"><b>Num. Pedido</b></td>';
                            echo '<td width="160" align="center"><b>Bairro</b></td>';
                            echo '<td width="80" align="center"><b>Entregador</b></td>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            
                            while ($obj_buscar_detalhes = mysql_fetch_object($res_buscar_detalhes))
                            {
                                echo '<tr>';
                                echo '<td>' . bd2texto($obj_buscar_detalhes->justificativa) . '&nbsp;</td>';
                                echo '<td align="center">' . bd2datahora($obj_buscar_detalhes->data_hora_resposta) . '&nbsp;</td>';
                                echo '<td align="center">' . bd2datahora($obj_buscar_detalhes->data_hora_pedido) . '&nbsp;</td>';
                                echo '<td align="center">' . sprintf("%08d", $obj_buscar_detalhes->cod_pedidos) . '&nbsp;</td>';
                                echo '<td align="center">' . bd2texto($obj_buscar_detalhes->bairro) . '&nbsp;</td>';
                                echo '<td align="center">' . bd2texto($obj_buscar_detalhes->entregadores_nome) . '&nbsp;</td>';
                                echo '</tr>';
                            }
                            
                            echo '</tbody>';
                            echo '</table>';
                        }
                        else
                        {
                            echo "<center>Nenhum voto nesta resposta!</center><br>";
                        }
                        
                        echo '<br></div>';
                    }
                    
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</table><br><br>';
                
            	$num_grafico++;
            	
            	endwhile;
            	?>
    		</div>

            <div class="painelTabInterno">
                
                
                <table class="listaEdicao" cellpadding="0" cellspacing="0" style="margin: 0px auto; width: 950px">
                    <thead>
                        <tr>
                            <td align="center" width="150">Apelido</td>
                            <td align="center" width="600">Endereço</td>
                            <td align="center" width="100">Telefone1</td>
                            <td align="center" width="100">Telefone2</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        
                        $sql_enderecos = "SELECT e.* FROM ipi_enderecos e WHERE e.cod_clientes = '$codigo'";
                        $res_enderecos = mysql_query($sql_enderecos);
                        while($obj_enderecos = mysql_fetch_object($res_enderecos))
                        {
                            echo '<tr>';
                            echo '<td align="center">' . $obj_enderecos->apelido . '</td>';
                            echo '<td align="center">' . $obj_enderecos->endereco.', '.$obj_enderecos->numero.' - '.$obj_enderecos->complemento.' - '.$obj_enderecos->edificio.'<br>'.$obj_enderecos->bairro.', '.$obj_enderecos->cidade.' - '.$obj_enderecos->estado. ' - ' .$obj_enderecos->cep.'</td>';
                            echo '<td align="center">' . $obj_enderecos->telefone_1 . '</td>';
                            echo '<td align="center">' . $obj_enderecos->telefone_2 . '</td>';
                            echo '</tr>';
                        }
                        
                        ?>
                    </tbody>
                </table>
                
            </div>
            
            <div class="painelTabInterno" align="center">

<strong>Cupons Utilizados:</strong>
				<table class="listaEdicao" cellpadding="0" cellspacing="0" style="margin: 0px auto; width: 950px">
        <thead>
            <tr>
                <td align="center" width="50">Cont.</td>
                <td align="center" width="100">Cupom</td>
                <td align="center" width="100">Produto</td>
                <td align="center" width="100">Data Validade</td>
                <td align="center" width="100">Válido</td>
            </tr>
        </thead>
        <tbody>
            <?php 
						$sql_buscar_cupons = "SELECT * FROM ipi_pedidos p INNER JOIN ipi_pedidos_ipi_cupons pc ON(p.cod_pedidos=pc.cod_pedidos) INNER JOIN ipi_cupons c ON(pc.cod_cupons=c.cod_cupons) WHERE p.cod_clientes = '$codigo'";
						//$sql_buscar_cupons = "SELECT * FROM ipi_cupons c LEFT JOIN ipi_pedidos_ipi_cupons pc ON(c.cod_cupons=pc.cod_cupons) LEFT JOIN ipi_pedidos p ON(pc.cod_pedidos=p.cod_pedidos) WHERE c.cod_clientes = '$codigo'";
						$res_buscar_cupons = mysql_query($sql_buscar_cupons);
						while ($obj_buscar_cupons = mysql_fetch_object($res_buscar_cupons))
						{
							echo '<tr>';
							echo '<td align="center" width="50">' . (++$cont) . '</td>';
							echo '<td align="center">' . bd2texto($obj_buscar_cupons->cupom) . '</td>';
              echo '<td align="center">'; 
              echo bd2texto($obj_buscar_cupons->produto);
              if ($obj_buscar_cupons->generico=="1")
                  echo " - Genérico";
              else
                  echo " - Específico";
              if ($obj_buscar_cupons->usuario_criacao)
                  echo " - ".bd2texto($obj_buscar_cupons->usuario_criacao);
              if ($obj_buscar_cupons->obs_cupom)
                  echo "<br>".bd2texto($obj_buscar_cupons->obs_cupom);
              '</td>';
							echo '<td align="center">' . bd2data($obj_buscar_cupons->data_validade) . '</td>';
                            
              if ($obj_buscar_cupons->valido)
              {
	            	echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
              }	
	            else
	            {
	              	echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
	            }
	            
              echo '</tr>';
						}
						
						?>
					</tbody>
        </table>	

<br /><br /><strong>Cupons Gerados pra este cliente:</strong>

				<table class="listaEdicao" cellpadding="0" cellspacing="0" style="margin: 0px auto; width: 950px">
        <thead>
            <tr>
                <td align="center" width="50">Cont.</td>
                <td align="center" width="100">Cupom</td>
                <td align="center" width="100">Produto</td>
                <td align="center" width="100">Data Validade</td>
                <td align="center" width="100">Válido</td>
            </tr>
        </thead>
        <tbody>
            <?php 
						//$sql_buscar_cupons = "SELECT * FROM ipi_pedidos p INNER JOIN ipi_pedidos_ipi_cupons pc ON(p.cod_pedidos=pc.cod_pedidos) INNER JOIN ipi_cupons c ON(pc.cod_cupons=c.cod_cupons) WHERE p.cod_clientes = '$codigo'";
						$sql_buscar_cupons = "SELECT * FROM ipi_cupons c LEFT JOIN ipi_pedidos_ipi_cupons pc ON(c.cod_cupons=pc.cod_cupons) LEFT JOIN ipi_pedidos p ON(pc.cod_pedidos=p.cod_pedidos) WHERE c.cod_clientes = '$codigo'";
						$res_buscar_cupons = mysql_query($sql_buscar_cupons);
            $cont = 0;
						while ($obj_buscar_cupons = mysql_fetch_object($res_buscar_cupons))
						{
							echo '<tr>';
							echo '<td align="center" width="50">' . (++$cont) . '</td>';
							echo '<td align="center">' . bd2texto($obj_buscar_cupons->cupom) . '</td>';
              echo '<td align="center">'; 
              echo bd2texto($obj_buscar_cupons->produto);
              if ($obj_buscar_cupons->generico=="1")
                  echo " - Genérico";
              else
                  echo " - Específico";
              if ($obj_buscar_cupons->usuario_criacao)
                  echo " - ".bd2texto($obj_buscar_cupons->usuario_criacao);
              if ($obj_buscar_cupons->obs_cupom)
                  echo "<br>".bd2texto($obj_buscar_cupons->obs_cupom);
              '</td>';
							echo '<td align="center">' . bd2data($obj_buscar_cupons->data_validade) . '</td>';
                            
              if ($obj_buscar_cupons->valido)
              {
	            	echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
              }	
	            else
	            {
	              	echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
	            }
	            
              echo '</tr>';
						}
						
						?>
					</tbody>
        </table>	
				
    		</div>

			<div class="painelTabInterno" align="center">
				
				<script>
				
				function resetar_senha(cod_usuario)
	    		{
    			  if (confirm('Deseja realmente trocar a resetar a senha?'))
		        {
	        	  document.getElementById('div_carregando').style.display="block";
						  var url = 'acao=resetar_senha&cod_usuario=' + cod_usuario;
        	    new Request.HTML(
        	    {
        	        url: 'ipi_clientes_ajax.php',
        	        update: $('divReset'),
        	        onComplete: function(){ document.getElementById('div_carregando').style.display="none" }
        	    }).send(url);
		        }
		        else
		        {
						  return false;
		        }
	    		}
				</script>
				
				<table width="800" align="center">
					<tr><td><hr /><br /></td></tr>
					<tr>
						<td><h3>Trocar Senha</h3></td>
					</tr>
					<tr><td align="center"><div id="div_carregando" style="display: none;"><img src="../../img/ajax_loader2.gif"><br />Aguarde ...</div></td></tr>
					<tr>
						<td align="center">
                            <br />Para trocar a senha do cliente clique no botão abaixo, que será exibida a nova senha e enviada por e-mail.<br /><br /><div id="divReset"><input type="button" class="botaoAzul" name="resetar" value="Trocar Senha" onclick="javascript: resetar_senha(<? echo $codigo; ?>);"></div></td>
					</tr>				
					<tr><td><br /><br /><hr /></td></tr>
                    <tr><td><br /></td></tr>
                    <tr>
                        <td><h3>Receber Newsletter</h3></td>
                    </tr>
                    <tr><td align="center"><div id="div_carregando" style="display: none;"><img src="../../img/ajax_loader2.gif"><br />Aguarde ...</div></td></tr>
                    <tr>
                        <td align="center">
                            <br />Utilize o formulário abaixo para ativar ou desativar o envio de Newsletter para este cliente.<br /><br />
                            <form id="formNewsletter" method="post">
                                <?
                                    $obj_buscar_newsletter = executar_busca_simples("SELECT ativo FROM ine_emails_cadastro WHERE email = '".$obj_buscar_clientes->email."'", $conexao);
                                ?>

                                <label title="Situacão da newsletter: <? echo ($obj_buscar_newsletter->ativo == 1 ? 'Recebe' : 'Não recebe')?>" style='text-align: left;'>Situacão da newsletter:</label> <? echo ($obj_buscar_newsletter->ativo == 1 ? 'Recebe' : 'Não recebe')?><br /><br />

                                <!--<label for="obs_newsletter" title="Observação" style='text-align: left;'>Observação:</label><br />
                                <textarea id="obs_newsletter" name="obs_newsletter" cols="110" rows="4"> </textarea><br /><br />-->
                                
                                <input type="submit" class="botaoAzul" name="enviar" value="&nbsp;<? echo ($obj_buscar_newsletter->ativo == 1 ? 'Desativar envio' : 'Ativar envio');?>&nbsp;" />
                                <input type="hidden" name="news" value="alterar_newsletter" />
                                <input type="hidden" name="<? echo $chave_primaria; ?>" value="<? echo $codigo; ?>" />
                            </form>
                        </td>
                    </tr>               
                    <tr><td><br /><br /><hr /></td></tr>
                    <tr><td><br /></td></tr>
                    <tr>
                        <td><h3>Cliente VIP</h3></td>
                    </tr>
                    <tr><td align="center"><div id="div_carregando" style="display: none;"><img src="../../img/ajax_loader2.gif"><br />Aguarde ...</div></td></tr>
                    <tr>
                        <td align="center">
                            <br />Utilize o formulário abaixo para ativar ou desativar a classificação vip para este cliente.<br /><br />
                            <form id="formNewsletter" method="post">
                                <?
                                    //$obj_buscar_vip = executar_busca_simples("SELECT cod_vips FROM ipi_clientes WHERE cod_clientes = '".$obj_buscar_clientes->cod_clientes."'", $conexao);

                                ?>
                                <? //echo ($obj_buscar_newsletter->ativo == 1 ? 'Recebe' : 'Não recebe')?>
                                <label title="Cliente VIP:" style='text-align: left;'>Cliente VIP:</label> 
                                <select name='vip' id='vip'>
                                    <option value="0">Não VIP</option>
                                <?
                                  $sql_buscar_niveis_vip = "SELECT * FROM ipi_vip WHERE situacao_vip = 'ATIVO'";
                                  $res_buscar_niveis_vip = mysql_query($sql_buscar_niveis_vip);
                                  while($obj_buscar_niveis_vip = mysql_fetch_object($res_buscar_niveis_vip))
                                  {
                                    echo "<option value='".$obj_buscar_niveis_vip->cod_vip."'";
                                    if($obj_buscar_niveis_vip->cod_vip==$obj_buscar_clientes->cod_vip)
                                    {
                                      echo " selected='SELECTED' ";
                                    }
                                    echo ">".$obj_buscar_niveis_vip->classificacao_vip."</option>";
                                  }
                                ?>
                                </select><br /><br />

                                <!--<label for="obs_newsletter" title="Observação" style='text-align: left;'>Observação:</label><br />
                                <textarea id="obs_newsletter" name="obs_newsletter" cols="110" rows="4"> </textarea><br /><br />-->
                                
                                <input type="submit" class="botaoAzul" name="enviar" value="&nbsp;Alterar Nível VIP&nbsp;" />
                                <input type="hidden" name="acao" value="alterar_nivel_vip" />
                                <input type="hidden" name="<? echo $chave_primaria; ?>" value="<? echo $codigo; ?>" />
                            </form>
                        </td>
                    </tr>               
                    <tr><td><br /><br /><hr /></td></tr>
                </table>
                
            </div>


        </div>
        
        <?php
        
        desconectabd($conexao);
        
        break;

        
}

if(($acao == '') || ($acao == 'buscar') || ($acao == 'editar')):

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<script>

function verificaCheckbox(form) 
{
    var cInput = 0;
    var checkBox = form.getElementsByTagName('input');

    for (var i = 0; i < checkBox.length; i++)
    {
        if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true)))
        { 
        	cInput++; 
        }
    }
   
    if(cInput > 0)
    {
        if (confirm('Deseja excluir os registros selecionados?'))
        {
			return true;
        }
        else
        {
			return false;
        }
    }
    else
    {
        alert('Por favor, selecione os itens que deseja excluir.');
         
        return false;
    }
}

function editar(cod)
{
    var form = new Element('form',
    {
        'action': '<? echo $_SERVER['PHP_SELF'] ?>',
        'method': 'post'
    });
    
    var input1 = new Element('input',
    {
        'type': 'hidden',
        'name': '<? echo $chave_primaria ?>',
        'value': cod
    });
    
    var input2 = new Element('input',
    {
        'type': 'hidden',
        'name': 'acao',
        'value': 'detalhes'
    });
    
    input1.inject(form);
    input2.inject(form);
    $(document.body).adopt(form);
    
    form.submit();
}

</script>

<?

    $pagina = (validaVarPost('pagina','/[0-9]+/')) ? validaVarPost('pagina','/[0-9]+/') : 0;
    $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : 'nome';
    $filtro = validaVarPost('filtro');
    
    if ( $acao=='buscar' )
    {
        $_SESSION['usuario']['filtro_busca'] = $filtro;
        $_SESSION['usuario']['opcao_busca'] = $opcoes;
    }
    else
    {
        $filtro = $_SESSION['usuario']['filtro_busca'];
        $_SESSION['usuario']['opcao_busca'] ? $opcoes = $_SESSION['usuario']['opcao_busca'] : '';
    }
    


    $cidade = (validaVarPost('cidade'));
    if (validaVarPost('cidade'))
    {
    	$_SESSION['usuario']['cidade'] = $cidade;
    }
    else 
    {
    	if ($_SESSION['usuario']['cidade']=='')
    	{
    		$cidade = 'TODOS';
    	}
    	else 
    	{
			$cidade = $_SESSION['usuario']['cidade'];
    	}	
    }


    $bairro = (validaVarPost('bairro'));
    if (validaVarPost('bairro'))
    {
    	$_SESSION['usuario']['bairro'] = $bairro;
    }
    else 
    {
    	if ($_SESSION['usuario']['bairro']=='')
    	{
    		$bairro = 'TODOS';
    	}
    	else 
    	{
			$bairro = $_SESSION['usuario']['bairro'];
    	}	
    }
    

    $situacao_filtro = (validaVarPost('situacao_filtro')) ? validaVarPost('situacao_filtro') : 'TODOS';
    if (validaVarPost('situacao_filtro'))
    {
    	$_SESSION['usuario']['situacao_filtro'] = $situacao_filtro;
    }
    else 
    {
    	$situacao_filtro = $_SESSION['usuario']['situacao_filtro'];
    }
    
    $origem_cliente = (validaVarPost('origem_cliente')) ? validaVarPost('origem_cliente') : 'TODOS';
    if (validaVarPost('origem_cliente'))
    {
    	$_SESSION['usuario']['origem_cliente'] = $origem_cliente;
    }
    else 
    {
    	$origem_cliente = $_SESSION['usuario']['origem_cliente'];
    }

    $filtro_facebook = (validaVarPost('filtro_facebook')) ? validaVarPost('filtro_facebook') : 0;
    $_SESSION['usuario']['filtro_facebook'] = $filtro_facebook;

    $filtro_tel1 = (validaVarPost('filtro_tel1')) ? validaVarPost('filtro_tel1') : '';
    $_SESSION['usuario']['filtro_tel1'] = $filtro_tel1;

    $filtro_tel2 = (validaVarPost('filtro_tel2')) ? validaVarPost('filtro_tel2') : '';
    $_SESSION['usuario']['filtro_tel2'] = $filtro_tel2;

    $filtro_celular = (validaVarPost('filtro_celular')) ? validaVarPost('filtro_celular') : '';
    $_SESSION['usuario']['filtro_celular'] = $filtro_celular;

    // die($filtro_tel1);
?>

<script type="text/javascript">
function buscar_bairros()
{
    var url = 'acao=completar_bairro&cidade=' + $('cidade').value+ '&bairro=' + $('bairro').value;
    new Request.HTML(
    {
        url: 'ipi_clientes_ajax.php',
        update: $('bairro')
    }).send(url);
}

<?
if ($cidade)
{
  ?>
  window.addEvent('domready', function()
  {
    var url = 'acao=completar_bairro&cidade=<? echo $cidade; ?>&bairro=<? echo $bairro; ?>';
    new Request.HTML(
    {
        url: 'ipi_clientes_ajax.php',
        update: $('bairro')
    }).send(url);
  });
  <?
}
?>
</script>

<form name="frmFiltro" method="post">
<table align="center" class="caixa" cellpadding="0" cellspacing="0">

	<tr>
		<td class="legenda tdbl tdbt" align="right">
      <select name="opcoes">
  			<option value="nome"
				<?
        if($opcoes == 'nome')
          echo 'selected'?>>Nome</option>
  			<option value="email"
				<? if($opcoes == 'email') echo 'selected'?>>E-mail</option>
            <option value="cpf"
                <? if($opcoes == 'cpf') echo 'selected'?>>CPF</option>
  		</select>
    </td>
		<td class="tdbt">&nbsp;</td>
		<td class="tdbt tdbr"><input class="requerido" type="text"
			name="filtro" size="60" value="<? echo $filtro ?>"></td>
	</tr>

	<tr>
		<td class="legenda tdbl" align="right"><label for="cidade">Cidade:</label></td>
		<td>&nbsp;</td>
		<td class="tdbr">
      <select name="cidade" id="cidade" onChange="javascript:buscar_bairros();">
  			<option value="TODOS" <? if($cidade == 'TODOS') echo 'selected'?>>Todas as Cidades</option>
          <?
          $con = conectabd();
          $sql_cidades = "SELECT DISTINCT(cidade) FROM ipi_enderecos ORDER BY cidade";
          $res_cidades = mysql_query($sql_cidades);
          while($obj_cidades = mysql_fetch_object($res_cidades))
          {
              echo '<option value="' . $obj_cidades->cidade . '" ';
              if($cidade == $obj_cidades->cidade)
                  echo 'selected';
              echo '>' . bd2texto($obj_cidades->cidade) . '</option>';
          }
          desconectabd($con);
          ?>
      </select>
    </td>
	</tr>

	<tr>
		<td class="legenda tdbl" align="right"><label for="bairro">Bairro:</label></td>
		<td>&nbsp;</td>
		<td class="tdbr">
      <select name="bairro" id="bairro">
  			<option value="TODOS">Selecione a Cidade</option>
      </select>
    </td>
	</tr>

	<tr>
		<td class="legenda tdbl" align="right"><label for="origem_cliente">Origem do Cliente:</label></td>
		<td class="sep">&nbsp;</td>
		<td class="tdbr"><select name="origem_cliente">
			<option value="TODOS"
				<? if($origem_cliente == 'TODOS') echo 'selected'?>>Todos</option>
			<option value="NET"
				<? if($origem_cliente == 'NET') echo 'selected'?>>Net</option>
			<option value="TEL"
				<? if($origem_cliente == 'TEL') echo 'selected'?>>Tel</option>
		</select></td>
	</tr>

	<tr>
		<td class="legenda tdbl" align="right"><label for="situacao_filtro">Situação:</label></td>
		<td class="">&nbsp;</td>
		<td class="tdbr">
    <select name="situacao_filtro">
			<option value="TODOS"
				<? if($situacao_filtro == 'TODOS') echo 'selected'?>>Todas as Situações</option>
			<option value="ATIVO"
				<? if($situacao_filtro == 'ATIVO') echo 'selected'?>>Ativo</option>
			<option value="INATIVO"
				<? if($situacao_filtro == 'INATIVO') echo 'selected'?>>Inativo</option>
		</select></td>
	</tr>

    <tr>
      <td class="legenda tdbl" align="right"> 
        <label for="filtro_facebook">Apenas do Facebook:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr"> 
        <select name="filtro_facebook">
			    <option value=""	<? if($filtro_facebook == '') echo 'selected="selected"'?>>Não</option>
			    <option value="1"	<? if($filtro_facebook == '1') echo 'selected="selected"'?>>Sim</option>
		    </select>
      </td>
    </tr>

      <tr>
      <td class="legenda tdbl" align="right"> 
        <label for="filtro_celular">Celular:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr"> 
                <input class="requerido" type="text" name="filtro_celular" size="15" value="<? echo $filtro_celular ?>">

      </td>
    </tr>

        <tr>
      <td class="legenda tdbl" align="right"> 
        <label for="filtro_tel1">Tel 1:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr"> 
                <input class="requerido" type="text" name="filtro_tel1" size="15" value="<? echo $filtro_tel1 ?>">

      </td>
    </tr>
            <tr>
      <td class="legenda tdbl" align="right"> 
        <label for="filtro_tel2">Tel 2:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr"> 
                <input class="requerido" type="text" name="filtro_tel2" size="15" value="<? echo $filtro_tel2 ?>">

      </td>
    </tr>

	<tr>
		<td align="right" class="tdbl tdbb tdbr" colspan="3"><input
			class="botaoAzul" type="submit" value="Buscar"></td>
	</tr>

</table>

<input type="hidden" name="acao" value="buscar"></form>

<br>

<?
    $ord_pedidos = validaVarGet('ord_pedidos');
    $ord_total = validaVarGet('ord_total');
    $ord_datacompra = validaVarGet('ord_datacompra');

    $con = conectabd();

    if($ord_pedidos == "pedidos2")
        $pedido_ordem = "num_pedidos ASC";
    else 
        if($ord_pedidos == "pedidos1")
            $pedido_ordem = "num_pedidos DESC";
    
    if($ord_total == "total2")
        $pedido_ordem = "total_pedidos ASC";
    else 
        if($ord_total == "total1")
            $pedido_ordem = "total_pedidos DESC";
    
    if($ord_datacompra == "compra2")
        $pedido_ordem = "data_ultimo_pedido ASC";
    else 
        if($ord_datacompra == "compra1")
            $pedido_ordem = "data_ultimo_pedido DESC";
    
    $filtrar_facebook = "";
    if($filtro_facebook)
    {
        $filtrar_facebook .= " INNER JOIN ipi_clientes_redes_sociais icrs ON (c.cod_clientes = icrs.cod_clientes) ";
    }

    $SqlBuscaRegistros = "SELECT c.*, e.telefone_1, e.telefone_2,
      (SELECT bairro FROM ipi_enderecos e WHERE e.cod_clientes=c.cod_clientes LIMIT 1) bairro, 
      (SELECT cidade FROM ipi_enderecos e WHERE e.cod_clientes=c.cod_clientes LIMIT 1) cidade, 
      (SELECT count(cod_pedidos) FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes AND situacao='BAIXADO') num_pedidos,
      (SELECT sum(valor_total) FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes AND situacao='BAIXADO') total_pedidos, 
      (SELECT data_hora_pedido FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes ORDER BY cod_pedidos DESC LIMIT 1) data_ultimo_pedido 
      FROM $tabela c ".$filtrar_facebook."  RIGHT JOIN ipi_enderecos e ON (c.cod_clientes=e.cod_clientes) WHERE $opcoes LIKE '%$filtro%' ";

    
    if($situacao_filtro != 'TODOS')
    {
        $SqlBuscaRegistros .= "AND c.situacao = '$situacao_filtro'";
    }
    
    if($origem_cliente != 'TODOS')
    {
        $SqlBuscaRegistros .= "AND c.origem_cliente = '$origem_cliente'";
    }

    //echo "<br />cidade: ".$cidade;
    //echo "<br />bairro: ".$bairro;
    if($cidade != 'TODOS')
    {
      $SqlBuscaRegistros .= " HAVING cidade = '$cidade' ";

      if($bairro != 'TODOS')
      {
        $SqlBuscaRegistros .= " AND bairro = '$bairro' ";
      }
    }
     if($filtro_tel1 != '')
      {
        $SqlBuscaRegistros .= " AND e.telefone_1 like '%$filtro_tel1%' ";
      }

    if($filtro_tel2 != '')
      {
        $SqlBuscaRegistros .= " AND e.telefone_2 like '%$filtro_tel2%' ";
      }

    if($filtro_celular != '')
      {
        $SqlBuscaRegistros .= " AND c.celular like '%$filtro_celular%' ";
      }

    if($pedido_ordem)
    {
        $SqlBuscaRegistros .= ' ORDER BY ' . $pedido_ordem;
    }
    
    // echo "<Br>1: ".$SqlBuscaRegistros;
    // die();

    $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
    $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);
    
    $SqlBuscaRegistros .= ' LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
    $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
    $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);

    if($ord_pedidos == "pedidos2")
    {
        $ord_pedidos = "pedidos1";
    }
    else
    { 
        if($ord_pedidos == "pedidos1")
        {
            $ord_pedidos = "pedidos2";
        }
        else
        {
            $ord_pedidos = "pedidos1";
        }
    }
    
    if($ord_total == "total2")
    {
        $ord_total = "total1";
    }
    else
    { 
        if($ord_total == "total1")
        {
            $ord_total = "total2";
        }
        else
        {
            $ord_total = "total1";
        }
    }
    
    if($ord_datacompra == "compra2")
    {
        $ord_datacompra = "compra1";
    }
    else
    { 
        if($ord_datacompra == "compra1")
        {
            $ord_datacompra = "compra2";
        }
        else
        {
            $ord_datacompra = "compra1";
        }
    }
        
    echo "<center><b>" . $numBuscaRegistros . " registro(s) encontrado(s)</center></b><br>";

    
    if((($quant_pagina * $pagina) == $numBuscaRegistros) && ($pagina != 0) && ($acao == 'excluir'))
    {
        $pagina--;
    }
    
    echo '<center>';
    
    $numpag = ceil(((int)$numBuscaRegistros) / ((int)$quant_pagina));
    
    for($b = 0; $b < $numpag; $b++)
    {
        echo '<form name="frmPaginacao' . $b . '" method="post">';
        echo '<input type="hidden" name="pagina" value="' . $b . '">';
        echo '<input type="hidden" name="filtro" value="' . $filtro . '">';
        echo '<input type="hidden" name="opcoes" value="' . $opcoes . '">';
        
        echo '<input type="hidden" name="bairro" value="' . $bairro . '">';
        echo '<input type="hidden" name="situacao_filtro" value="' . $situacao_filtro . '">';
        
        echo '<input type="hidden" name="acao" value="buscar">';
        echo "</form>";
    }
    
    if($pagina != 0)
    {
        echo '<a href="javascript:;" onclick="javascript:frmPaginacao' . ($pagina - 1) . '.submit();" style="margin-right: 5px;">&laquo;&nbsp;Anterior</a>';
    }
    else
    {
        echo '<span style="margin-right: 5px;">&laquo;&nbsp;Anterior</span>';
    }
    
    for($b = 0; $b < $numpag; $b++)
    {
        if($b != 0)
        {
            echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
        }
        
        if($pagina != $b)
        {
            echo '<a href="javascript:;" onclick="javascript:frmPaginacao' . $b . '.submit();">' . ($b + 1) . '</a>';
        }
        else
        {
            echo '<span><b>' . ($b + 1) . '</b></span>';
        }
    }
    
    if(($quant_pagina == $linhasBuscaRegistros) && ((($quant_pagina * $pagina) + $quant_pagina) != $numBuscaRegistros))
    {
        echo '<a href="javascript:;" onclick="javascript:frmPaginacao' . ($pagina + 1) . '.submit();" style="margin-left: 5px;">Próxima&nbsp;&raquo;</a>';
    }
    else
    {
        echo '<span style="margin-left: 5px;">Próxima&nbsp;&raquo;</span>';
    }
    
    echo '</center>';
    
    ?>

<br />

<form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">

<table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
	<tr>
		<td><input class="botaoAzul" type="submit"
			value="Excluir Selecionados"></td>
	</tr>
</table>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<td align="center" width="20"><input type="checkbox"
				onclick="marcaTodos('marcar');"></td>
			<td align="center">Nome</td>
			<td align="center">E-mail</td>
			<td align="center">Bairro</td>
			<td align="center">Cidade</td>
			<td align="center">CPF</td>
			<td align="center"><a href="ipi_clientes.php?ord_pedidos=<? echo $ord_pedidos; ?>">Num. Pedidos Concluidos</a></td>
			<td align="center"><a href="ipi_clientes.php?ord_total=<? echo $ord_total; ?>">Total Gasto</a></td>
			<td align="center"><a
				href="ipi_clientes.php?ord_datacompra=<? echo $ord_datacompra; ?>">Data Última Compra</a></td>
			<td align="center">Observação</td>
			<td align="center">Situação</td>
		</tr>
	</thead>
	<tbody>
    
    <?
    for($a = 0; $a < $linhasBuscaRegistros; $a++)
    {
        $objBuscaRegistros = mysql_fetch_object($resBuscaRegistros);
        echo '<tr>';
        
        echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $objBuscaRegistros->$chave_primaria . '"></td>';
        echo '<td align="center"><a href="ipi_clientes.php?cc='.$objBuscaRegistros->cod_clientes.'">' . bd2texto($objBuscaRegistros->nome) . '</a></td>';
        echo '<td align="center">' . bd2texto($objBuscaRegistros->email) . '</td>';
        echo '<td align="center">' . bd2texto($objBuscaRegistros->bairro) . '</td>';
        echo '<td align="center">' . bd2texto($objBuscaRegistros->cidade) . '</td>';
        echo '<td align="center">' . bd2texto($objBuscaRegistros->cpf) . '</td>';
        echo '<td align="center">' . bd2texto($objBuscaRegistros->num_pedidos).'</td>';
        echo '<td align="center">' . bd2moeda($objBuscaRegistros->total_pedidos) . '</td>';
        
        echo '<td align="center">';
        
        if($objBuscaRegistros->data_ultimo_pedido)
        {
            echo bd2datahora($objBuscaRegistros->data_ultimo_pedido);
        }
        
        echo '</td>';
        
        echo '<td align="center">' . bd2texto($objBuscaRegistros->observacao) . '</td>';
        echo '<td align="center">' . bd2texto($objBuscaRegistros->situacao) . '</td>';
        
        echo '</tr>';
    }
    
    desconectabd($con);
    ?>
    
    </tbody>
</table>

<input type="hidden" name="acao" value="excluir"></form>

<br>

<? 
endif;

rodape();
?>
