<?php

/**
 * Resultados das Enquetes (ajax).
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       12/05/2010   FELIPE        Criado.
 */

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';
require_once '../../classe/cupom.php';
$cod_enquetes = 1; // Forçado para a enquete mail

$acao = validaVarPost('acao');

switch($acao)
{
    case 'detalhes_pedido':
        $cod_clientes_ipi_enquete_respostas = validaVarPost('cod_clientes_ipi_enquete_respostas');
        $cod_pedidos = validaVarPost('cod_pedidos');
        $visualizacao = validaVarPost('visualizacao');
        
        $conexao = conectabd();
        
        echo '<h2>Resposta de Enquete - Pedido ' . sprintf('%08d',$cod_pedidos) . '</h2><br><br>';
        
        $sql_buscar_cliente = "SELECT p.*, e.nome AS e_nome, c.nome AS c_nome FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (p.cod_clientes = c.cod_clientes) LEFT JOIN ipi_entregadores e ON (p.cod_entregadores = e.cod_entregadores) WHERE p.cod_pedidos = '$cod_pedidos' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
        $res_buscar_cliente = mysql_query($sql_buscar_cliente);
        $obj_buscar_cliente = mysql_fetch_object($res_buscar_cliente);
        
        echo '<label>Cliente</label><br><hr noshadow="noshadow" size="1" color="#D44E08"><br>';
        
        echo '<table>';
        echo '<tr><td><label>Nome: ' . utf8_encode(bd2texto($obj_buscar_cliente->c_nome)) . '</label></td></tr>';
        echo '<tr><td><label>E-mail: ' . utf8_encode(bd2texto($obj_buscar_cliente->email)) . '</label></td></tr>';
        echo '<tr><td><label>Telefone: ' . utf8_encode(bd2texto($obj_buscar_cliente->telefone)) . '</label></td></tr>';
        echo '<tr><td><label>Celular: ' . utf8_encode(bd2texto($obj_buscar_cliente->celular)) . '</label></td></tr>';
        echo '<tr><td><label>' . utf8_encode('Endereço: ' . bd2texto($obj_buscar_cliente->endereco)) . '</label></td></tr>';
        echo '<tr><td><label>Num. : ' . utf8_encode(bd2texto($obj_buscar_cliente->numero)) . ' Comp.: ' . utf8_encode(bd2texto($obj_buscar_cliente->complemento)) . ' Edif.: ' . utf8_encode(bd2texto($obj_buscar_cliente->complemento)) . '</label></td></tr>';
        echo '<tr><td><label>Bairro: ' . utf8_encode(bd2texto($obj_buscar_cliente->bairro)) . '</label></td></tr>';
        echo '<tr><td><label>Cidade: ' . utf8_encode(bd2texto($obj_buscar_cliente->cidade)) . ' - ' . utf8_encode(bd2texto($obj_buscar_cliente->estado)) . '</label></td></tr>';
        echo '</table>';
        
        echo '<br><br><label>Pedido</label><br><hr noshadow="noshadow" size="1" color="#D44E08"><br>';
        
        echo '<table>';
        echo '<tr><td><label>Valor Total: ' . utf8_encode(bd2texto($obj_buscar_cliente->valor_total)) . '</label></td></tr>';
        echo '<tr><td><label>Forma de Pgto: ' . utf8_encode(bd2texto($obj_buscar_cliente->forma_pg)) . '</label></td></tr>';
        echo '<tr><td><label>Origem do Pedido: ' . utf8_encode(bd2texto($obj_buscar_cliente->origem_pedido)) . '</label></td></tr>';
        echo '<tr><td><label>Tipo de Entrega: ' . utf8_encode(bd2texto($obj_buscar_cliente->tipo_entrega)) . '</label></td></tr>';
        echo '<tr><td><label>Entregador: ' . utf8_encode(bd2texto($obj_buscar_cliente->e_nome)) . '</label></td></tr>';
        echo '<tr><td><label>Agendado: ' . utf8_encode((($obj_buscar_cliente->agendado) ? bd2texto($obj_buscar_cliente->horario_agendamento) : 'Não')) . '</label></td></tr>';
        echo '</table><br>';
        
        $sql_buscar_pizzas = "SELECT pp.*, pb.cod_bordas, b.borda, pa.cod_adicionais, a.adicional, tp.tipo_massa, t.tamanho FROM ipi_pedidos_pizzas pp INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_tipo_massa tp ON (pp.cod_tipo_massa = tp.cod_tipo_massa) LEFT JOIN ipi_pedidos_adicionais pa ON (pp.cod_pedidos = pa.cod_pedidos AND pp.cod_pedidos_pizzas = pa.cod_pedidos_pizzas) LEFT JOIN ipi_adicionais a ON (pa.cod_adicionais = a.cod_adicionais) LEFT JOIN ipi_pedidos_bordas pb ON (pp.cod_pedidos = pb.cod_pedidos AND pp.cod_pedidos_pizzas = pb.cod_pedidos_pizzas) LEFT JOIN ipi_bordas b ON (pb.cod_bordas = b.cod_bordas) WHERE pp.cod_pedidos = '$cod_pedidos'";
        $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
        $num_buscar_pizzas = mysql_num_rows($res_buscar_pizzas);
        
        for($p = 1; $p <= $num_buscar_pizzas; $p++)
        {
            $obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas);
            
            echo '<table>';
            echo "<tr><td colspan=\"9\" style=\"color: #D44E08;\"><b>${p}&ordf; Pizza</b></td></tr>";
            echo '<tr>';
            echo "<td><label>Tamanho: " . utf8_encode(bd2texto($obj_buscar_pizzas->tamanho)) . "</label></td>";
            echo '<td width="10">&nbsp;</td>';
            echo "<td><label>Quant. Sabores: " . utf8_encode(bd2texto($obj_buscar_pizzas->quant_fracao)) . "</label></td>";
            echo '<td width="10">&nbsp;</td>';
            echo "<td><label>Borda: " . (($obj_buscar_pizzas->cod_bordas > 0) ? utf8_encode(bd2texto($obj_buscar_pizzas->borda)) : utf8_encode('Não')) . "</label></td>";
            echo '<td width="10">&nbsp;</td>';
            echo "<td><label>Gergelim: " . (($obj_buscar_pizzas->cod_adicionais > 0) ? utf8_encode(bd2texto($obj_buscar_pizzas->adicional)) : utf8_encode('Não')) . "</label></td>";
            echo '<td width="10">&nbsp;</td>';
            echo "<td><label>Tipo de Massa: " . utf8_encode(bd2texto($obj_buscar_pizzas->tipo_massa)) . "</label></td>";
            echo '</tr>';
            echo "<tr><td colspan=\"9\">&nbsp;</td></tr>";
            echo '</table>';
            
            $sql_buscar_fracoes = "SELECT * FROM ipi_pedidos_pizzas pp INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas p ON (pf.cod_pizzas = p.cod_pizzas) WHERE pp.cod_pedidos = '$cod_pedidos' AND pp.cod_pedidos_pizzas = '" . $obj_buscar_pizzas->cod_pedidos_pizzas . "' ORDER BY pf.fracao";
            $res_buscar_fracoes = mysql_query($sql_buscar_fracoes);
            $num_buscar_fracoes = mysql_num_rows($res_buscar_fracoes);
            
            for($f = 1; $f <= $num_buscar_fracoes; $f++)
            {
                $obj_buscar_fracoes = mysql_fetch_object($res_buscar_fracoes);
                
                echo '<table>';
                
                echo "<tr><td><label>${f} &ordm; Sabor: " . utf8_encode(bd2texto($obj_buscar_fracoes->pizza)) . "</label></td></tr>";
                echo "<tr><td><label>Adicionais: ";
                
                $sql_buscar_ingredientes = "SELECT * FROM ipi_pedidos_pizzas pp INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) INNER JOIN ipi_ingredientes i ON (pi.cod_ingredientes = i.cod_ingredientes) WHERE pp.cod_pedidos = '$cod_pedidos' AND pp.cod_pedidos_pizzas = '" . $obj_buscar_pizzas->cod_pedidos_pizzas . "' AND pf.cod_pedidos_fracoes = '" . $obj_buscar_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 0 ORDER BY ingrediente";
                $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
                $num_buscar_ingredientes = mysql_num_rows($res_buscar_ingredientes);
                
                if($num_buscar_ingredientes == 0)
                {
                    echo utf8_encode('Não');
                }
                else
                {
                    while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
                    {
                        echo utf8_encode(bd2texto($obj_buscar_ingredientes->ingrediente)) . ', ';
                    }
                }
                
                echo '</label></td></tr><tr><td>&nbsp;</td></tr></table>';
            }
        }
        
        $sql_buscar_bebidas = "SELECT * FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) WHERE p.cod_pedidos = '$cod_pedidos' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
        $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
        $num_buscar_bebidas = mysql_num_rows($res_buscar_bebidas);
        
        if($num_buscar_bebidas == 0)
        {
            echo '<br><label>Sem Bebidas</label>';
        }
        else
        {
            echo '<br><table>';
            
            while($obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas))
            {
                echo '<tr><td><label>' . utf8_encode(bd2texto($obj_buscar_bebidas->bebida . ' ' . $obj_buscar_bebidas->conteudo)) . '</label></td></tr>';
            }
            
            echo '</table>';
        }
        
        echo '<br><br><label>Enquete</label><br><hr noshadow="noshadow" size="1" color="#D44E08"><br>';
        
        echo '<form id="frmEnviarRespostas" action="ipi_enquete_resultado_ajax.php">';
        
        $sql_buscar_perguntas = "SELECT * FROM ipi_enquete_perguntas WHERE pergunta_pessoal = 0 and cod_enquetes = '$cod_enquetes' ORDER BY cod_enquete_perguntas";
        $res_buscar_perguntas = mysql_query($sql_buscar_perguntas);
        
        echo '<table>';
        
        $sql_buscar_pedidos_info = "SELECT * FROM ipi_pedidos_info WHERE cod_pedidos = '$cod_pedidos' AND chave = 'CABECALHO_RESPOSTA_ENQUETE'";
        $res_buscar_pedidos_info = mysql_query($sql_buscar_pedidos_info);
        $obj_buscar_pedidos_info = mysql_fetch_object($res_buscar_pedidos_info);
        
        echo '<tr><td><label style="color: #D44E08;">' . utf8_encode('Mensagem de cabeçalho / Observações') . '</label></td></tr>';
        echo '<tr><td><textarea id="cabecalho_resp" name="cabecalho_resp" rows="4" cols="108">' . utf8_encode(bd2texto($obj_buscar_pedidos_info->conteudo)) . '</textarea></td></tr>';
        $i =0;
        while($obj_buscar_perguntas = mysql_fetch_object($res_buscar_perguntas))
        {
            echo '<tr><td>&nbsp;</td></tr><tr><td style="color: #D44E08;"><label>' . utf8_encode(bd2texto($obj_buscar_perguntas->pergunta)) . '</label></td></tr><tr><td>&nbsp;</td></tr>';
            
            $sql_buscar_respostas = "SELECT * FROM ipi_enquete_respostas er INNER JOIN ipi_clientes_ipi_enquete_respostas cer ON (er.cod_enquete_respostas = cer.cod_enquete_respostas) WHERE er.cod_enquete_perguntas = '$obj_buscar_perguntas->cod_enquete_perguntas' AND cer.cod_pedidos = '$cod_pedidos'";
            $res_buscar_respostas = mysql_query($sql_buscar_respostas);
            
            while($obj_buscar_respostas = mysql_fetch_object($res_buscar_respostas))
            {
                echo '<tr><td><label style="color: #D44E08;">' . utf8_encode(bd2texto($obj_buscar_respostas->resposta . ':</label> <label>' . (($obj_buscar_respostas->justificativa) ? $obj_buscar_respostas->justificativa : 'Cliente não comentou'))) . '</label></td></tr>';

                echo utf8_encode("<tr><td>Resposta padrão: <select onchange='adicionar_resposta_automatica($i,this.value)'>");
                $sql_buscar_respostas_padrao = "SELECT rp.* from ipi_respostas_resposta_padrao rp inner join ipi_respostas_categorias c on c.cod_respostas_categorias = rp.cod_respostas_categorias where rp.situacao='ATIVO' and c.cod_enquete_perguntas = '".$obj_buscar_perguntas->cod_enquete_perguntas."'";
                $res_buscar_respostas_padrao = mysql_query($sql_buscar_respostas_padrao);
                echo "<option value=''></option>";
                while($obj_buscar_respostas_padrao = mysql_fetch_object($res_buscar_respostas_padrao))
                {
                    echo "<option value='".$obj_buscar_respostas_padrao->cod_respostas."'>".utf8_encode(bd2texto($obj_buscar_respostas_padrao->nome_resposta))."</option>";
                }
                echo "</select></td></tr>";

                echo "<tr><td>";
                echo "Cupom a ser enviado: <select name='cod_respostas_cupom[]' id='cod_respostas_cupom_$i'>";
                echo "<option value=''></option>";
                $SqlBuscaCupom = "SELECT * FROM ipi_respostas_cupom WHERE situacao='ATIVO' ORDER BY cod_respostas_cupom ASC";
                $resBuscaCupom = mysql_query($SqlBuscaCupom);
                
                while($objBuscaCupom = mysql_fetch_object($resBuscaCupom)) 
                {
                  $nome_cupom = utf8_encode('Cupom #'.bd2texto($objBuscaCupom->cod_respostas_cupom).' - '.$objBuscaCupom->nome_cupom);
                  echo '<option value="'.$objBuscaCupom->cod_respostas_cupom.'">'.$nome_cupom.'</option>'; 
                } 

                echo "</select>";
                echo "</td></tr>";
                echo '<tr><td><textarea id="resposta_pizzaria_'.$i.'" name="resposta[]" rows="8" cols="108">' . utf8_encode(bd2texto($obj_buscar_respostas->resposta_pizzaria)) . '</textarea></td></tr>';
                //echo '<tr><td>&nbsp;</td></tr>';

                $sql_buscar_categorias = "SELECT * FROM ipi_categorias_comentarios WHERE tipo_categoria = 'ENQUETE' ORDER BY categoria_comentario";
                $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                $num_buscar_categorias = mysql_num_rows($res_buscar_categorias);
                
                echo '<tr><td><table><tr>';
                
                for($c = 0; $c < $num_buscar_categorias; $c++)
                {
                    $obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias);
                    
                    $sql_buscar_respostas_comentarios = "SELECT COUNT(*) AS quantidade FROM ipi_clientes_ipi_enquete_respostas_categorias_comentarios WHERE cod_categorias_comentarios = '$obj_buscar_categorias->cod_categorias_comentarios' AND cod_clientes_ipi_enquete_respostas = '$obj_buscar_respostas->cod_clientes_ipi_enquete_respostas'";
                    $res_buscar_respostas_comentarios = mysql_query($sql_buscar_respostas_comentarios);
                    $obj_buscar_respostas_comentarios = mysql_fetch_object($res_buscar_respostas_comentarios);
                    
                    echo '<td style="padding-right: 10px;"><input type="checkbox" name="cod_categorias_comentarios_' . $obj_buscar_respostas->cod_clientes_ipi_enquete_respostas . '[]" value="' . $obj_buscar_categorias->cod_categorias_comentarios . '" ' . (($obj_buscar_respostas_comentarios->quantidade > 0) ? 'checked' : '') . '>&nbsp;<label>' . utf8_encode(bd2texto($obj_buscar_categorias->categoria_comentario)) . '</label></td>';
                }
                
                echo '</tr></table></td></tr>';
                echo '<tr><td>&nbsp;</td></tr>';
                echo '<input type="hidden" name="cod_clientes_ipi_enquete_respostas[]" value="' . $obj_buscar_respostas->cod_clientes_ipi_enquete_respostas . '">';
                $i++;
            }
        }
        
        echo '</table>';
        
        echo '<br><br><center>';
        
        if($visualizacao)
        {
        	echo '<input type="button" class="botao" value="Fechar" onclick="cancelar_resposta()">';
        }
        else
        {
        	echo '<input type="button" class="botao" value="Enviar Resposta por E-mail" style="margin-right: 40px;" onclick="enviar_resposta(' . $cod_clientes_ipi_enquete_respostas . ', ' . $cod_pedidos . ', \'email\')">';
        	echo '<input type="button" class="botao" value="Registrar Resposta por Telefone" style="margin-right: 40px;" onclick="enviar_resposta(' . $cod_clientes_ipi_enquete_respostas . ', ' . $cod_pedidos . ', \'tel\')">';
        	echo '<input type="button" class="botao" value="Cancelar" onclick="cancelar_resposta()">';
        }
        
        echo '</center>';
        
        echo '<input type="hidden" name="cod_pedidos" value="' . $cod_pedidos . '">';
        echo '<input type="hidden" name="tipo_reposta" id="tipo_reposta" value="">';
        echo '<input type="hidden" name="acao" value="enviar_resposta">';
        
        echo '</form>';
        
        desconectabd($conexao);
        
        break;
    case 'resposta_padrao':
        $con = conectar_bd();
        $cod_respostas = validaVarPost('cod_respostas');
        $sql_busca = "select * from ipi_respostas_resposta_padrao where cod_respostas = '$cod_respostas'";
        $res_busca = mysql_query($sql_busca);
        $obj_busca = mysql_fetch_object($res_busca);
        echo utf8_encode(bd2texto($obj_busca->mensagem_resposta));
        desconectabd($con);    
        break;
    case 'verificar_cupom':
        $con = conectar_bd();
        $cod_respostas = validaVarPost('cod_respostas');
        $sql_busca = "select * from ipi_respostas_resposta_padrao where cod_respostas = '$cod_respostas'";
        $res_busca = mysql_query($sql_busca);
        $obj_busca = mysql_fetch_object($res_busca);
        if($obj_busca->cod_respostas_cupom)
        {
          $arr_json["cod"] = $obj_busca->cod_respostas_cupom;
        }else
        {
          $arr_json["cod"] = "Nenhum";
        }
        echo json_encode($arr_json);
        desconectabd($con);    
        break;
    case 'enviar_resposta':
    
    	$cod_pedidos = validaVarPost('cod_pedidos');
    	$cabecalho = validaVarPost('cabecalho');
    	$resposta = validaVarPost('resposta');
      $resposta = explode('@@=',$resposta);
    	$tipo_reposta = validaVarPost('tipo_reposta');
      $cod_respostas_cupom = validaVarPost('cupom');
      $cod_respostas_cupom = explode(',',$cod_respostas_cupom);
    	$cod_clientes_ipi_enquete_respostas = validaVarPost('cod_clientes_ipi_enquete_respostas');
      $cod_clientes_ipi_enquete_respostas = explode(',',$cod_clientes_ipi_enquete_respostas);
    	

      //print_r($resposta);
     // print_r($cod_clientes_ipi_enquete_respostas);
      $conexao = conectabd(); 	
    	
    	$res_inserir_reposta_pizzaria = true;
    	$enviar_email = false;
    	
    	//$sql_inserir_cabecalho = sprintf("INSERT INTO ipi_pedidos_info (cod_pedidos, chave, conteudo) VALUES ('%s', '%s', '%s')", 
    			//					$cod_pedidos, 'CABECALHO_RESPOSTA_ENQUETE', utf8_decode($cabecalho));
    								
		  //$res_inserir_reposta_pizzaria &= mysql_query($sql_inserir_cabecalho);
    	
    	for($i = 0; $i < count($cod_clientes_ipi_enquete_respostas); $i++)
    	{
        $data_validade = '';
        $hash_cupom = '';
    		if($resposta[$i] != '')
    		{
    			$enviar_email = true;
          //echo $resposta[$i]."<br/>".$cod_respostas_cupom[$i]."</br>";
          if($cod_respostas_cupom[$i]!="")
          {
            $sql_buscar_infos_cupom = "SELECT * from ipi_respostas_cupom where cod_respostas_cupom = '".$cod_respostas_cupom[$i]."'";
            $res_buscar_infos_cupom = mysql_query($sql_buscar_infos_cupom);
            $obj_buscar_infos_cupom = mysql_fetch_object($res_buscar_infos_cupom);

            $data_validade =  date_create();
            date_add($data_validade, date_interval_create_from_date_string($obj_buscar_infos_cupom->dias_validos.' days'));
            $data_validade = date_format($data_validade, 'Y-m-d');

            $sql_busca_cod_pizzarias = "SELECT cod_pizzarias from ipi_pizzarias where situacao='ATIVO'";
            $res_busca_cod_pizzarias = mysql_query($sql_busca_cod_pizzarias);
            while($obj_busca_cod_pizzarias = mysql_fetch_object($res_busca_cod_pizzarias))
            {
              $cod_pizzarias[] = $obj_busca_cod_pizzarias->cod_pizzarias;
            }
            //echo $sql_buscar_infos_cupom."<br/>";
            $cupom = new Cupom();
            $cod_cupons = $cupom->inserir_cupom($data_validade, $obj_buscar_infos_cupom->produto, $obj_buscar_infos_cupom->cod_produtos, $obj_buscar_infos_cupom->cod_tamanhos, '0', $obj_buscar_infos_cupom->necessita_compra, $obj_buscar_infos_cupom->valor_minimo_compra, $_SESSION['usuario']['cod_usuario'], $obj_buscar_infos_cupom->generico,'Cupom gerado automaticamente para ser usado em uma reclamação de enquete', $cod_pizzarias);
            $sql_busca_cupom = "SELECT cupom from ipi_cupons where cod_cupons = '$cod_cupons'";
            $res_busca_cupom = mysql_query($sql_busca_cupom);
            $obj_busca_cupom = mysql_fetch_object($res_busca_cupom);
            $hash_cupom = $obj_busca_cupom->cupom;
          }
          $sql_buscar_nome_cliente = "select c.nome from ipi_clientes c inner join ipi_pedidos p on p.cod_clientes = c.cod_clientes where p.cod_pedidos = '".$cod_pedidos."'";
          //echo $sql_buscar_nome_cliente;
          $res_buscar_nome_cliente = mysql_query($sql_buscar_nome_cliente);
          $obj_buscar_nome_cliente = mysql_fetch_object($res_buscar_nome_cliente);
          $nome_cliente = explode(' ',$obj_buscar_nome_cliente->nome);
          $nome_cliente = ucfirst(strtolower(trim($nome_cliente[0])));
          $nome_gerente = explode(' ',$_SESSION['usuario']['nome']);
          $nome_gerente = $nome_gerente[0];
          $placeholders = array('##NOME_CLIENTE##','##NOME_GERENTE##','##NUMERO_CUPOM##','##VALIDADE_CUPOM##');
          $texto_troca = array($nome_cliente,$nome_gerente,$hash_cupom,$data_validade);
          $resposta[$i] = str_replace($placeholders,$texto_troca,$resposta[$i]);
          //echo $resposta[$i];
          //die();
    		}
        
    		$respondida_pizzaria_tel = ($tipo_reposta == 'tel') ? 1 : 0;
    		
    		$sql_inserir_reposta_pizzaria = sprintf("UPDATE ipi_clientes_ipi_enquete_respostas SET cod_usuarios = '%s', data_hora_resposta_pizzaria = NOW(), resposta_pizzaria = '%s', respondida_pizzaria = 1, respondida_pizzaria_tel = '%s' WHERE cod_clientes_ipi_enquete_respostas = '"  . $cod_clientes_ipi_enquete_respostas[$i] . "'",
												$_SESSION['usuario']['codigo'], texto2bd(utf8_decode($resposta[$i])), $respondida_pizzaria_tel);
    	
    		$res_inserir_reposta_pizzaria &= mysql_query($sql_inserir_reposta_pizzaria);

    		$cod_categorias_comentarios = validaVarPost('cod_categorias_comentarios_' . $cod_clientes_ipi_enquete_respostas[$i]);
    		
    		if(is_array($cod_categorias_comentarios))
    		{
    			foreach($cod_categorias_comentarios as $cor_cod_categorias_comentarios)
    			{
    				$sql_inserir_categoria_repostas = sprintf("INSERT INTO ipi_clientes_ipi_enquete_respostas_categorias_comentarios (cod_clientes_ipi_enquete_respostas, cod_categorias_comentarios) VALUES ('%s', '%s')",
    														$cod_clientes_ipi_enquete_respostas[$i], $cor_cod_categorias_comentarios); 
    														
					$res_inserir_reposta_pizzaria &= mysql_query($sql_inserir_categoria_repostas);
    			}
    		}
    	}
    
    	if(($enviar_email) && ($tipo_reposta == 'email'))
    	{
    		require_once('../../ipi_email.php');
    		
    		//$email = '<p>Nós agradecemos sua sugestão/reclamação realizada na enquete do pedido ' . sprintf('%08d', $cod_pedidos) . '. Seguem nossos comentários:</p><br><br>';
    		
    		if($cabecalho != '')
    		{
    			$email = nl2br(utf8_decode($cabecalho)) . '<br><br>';
    		}
    		else
    		{
    			$email = '';
    		}
        //echo 'aaaa'.($cod_clientes_ipi_enquete_respostas);
        // (" . implode(',', $cod_clientes_ipi_enquete_respostas) . ")";
			  $sql_buscar_resposta_pizzaria = "SELECT * FROM ipi_enquete_perguntas p INNER JOIN ipi_enquete_respostas r ON (p.cod_enquete_perguntas = r.cod_enquete_perguntas) INNER JOIN ipi_clientes_ipi_enquete_respostas cr ON (r.cod_enquete_respostas = cr.cod_enquete_respostas) WHERE p.pergunta_pessoal = 0 and cr.cod_clientes_ipi_enquete_respostas IN (" . implode(',', $cod_clientes_ipi_enquete_respostas) . ")";
			$res_buscar_resposta_pizzaria = mysql_query($sql_buscar_resposta_pizzaria);
			 
			while($obj_buscar_resposta_pizzaria = mysql_fetch_object($res_buscar_resposta_pizzaria))
			{
				if(bd2texto($obj_buscar_resposta_pizzaria->resposta_pizzaria) != '')
				{
					$email .= '<p style="color: #D44E08;"><b>' . bd2texto($obj_buscar_resposta_pizzaria->pergunta) . '</b></p>';
					$email .= '<p><b>Sua opinião:</b> ' . bd2texto($obj_buscar_resposta_pizzaria->resposta) . '</p>';
					
					if($obj_buscar_resposta_pizzaria->justificativa != '')
					{
						$email .= '<p><b>Seu comentário:</b> ' . bd2texto($obj_buscar_resposta_pizzaria->justificativa) . '</p>';
					}
					
					$email .= '<p><b>Nossa resposta:</b> ' . nl2br(bd2texto($obj_buscar_resposta_pizzaria->resposta_pizzaria)) . '</p><br>';
				}
			}
    		
    		//$email .= '<p>Agradecemos sua participação na nossa enquete!</p>';
    		
    		$obj_clientes = executaBuscaSimples("SELECT * FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) WHERE cod_pedidos = '$cod_pedidos'", $conexao);

    		$arr_aux = array();
            $arr_aux['cod_pedidos'] = $cod_pedidos;
            $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
            $arr_aux['cod_clientes'] = $obj_clientes->cod_clientes;
            $arr_aux['cod_pizzarias'] = 0;
            $arr_aux['tipo'] = 'ENQUETE_RESPOSTA';
            //$obj_clientes->email
    		enviar_email (EMAIL_PRINCIPAL, $obj_clientes->email, 'Sua enquete do pedido ' . sprintf('%08d', $cod_pedidos) . ' foi respondida!', $email, $arr_aux, 'enquete_respondida');
    	}
    	
      $arr_json["status"] = "ok";
      echo json_encode($arr_json);
        
    break;
}

?>
