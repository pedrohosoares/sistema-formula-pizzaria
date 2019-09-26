<?php

/**
 * Resultados das Enquetes (ajax).
 *
 * @version 1.0
 * @package clientes
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       12/05/2010   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';
require_once '../../classe/pedido.php';
$acao = validaVarPost('acao');

switch($acao)
{
    case 'completar_bairro':
        $cidade = validaVarPost('cidade');
        $bairro = validaVarPost('bairro');
        echo '<option value="TODOS">Todos</option>';

        $conexao = conectabd();
        $sql_bairros = "SELECT DISTINCT(bairro) FROM ipi_enderecos WHERE cidade='".utf8_decode($cidade)."' ORDER BY bairro";
        $res_bairros = mysql_query($sql_bairros);
        while($obj_bairros = mysql_fetch_object($res_bairros))
        {
            echo '<option value="' . utf8_encode($obj_bairros->bairro) . '" ';
            if( utf8_decode($bairro) == $obj_bairros->bairro)
                echo 'selected';
            echo '>' . utf8_encode(bd2texto($obj_bairros->bairro)) . '</option>';
        }

    break;
    case 'detalhes_pedido':
        $cod_pedidos = validaVarPost('cod_pedidos');
        $pedido = new Pedido();
        echo $pedido->retornar_resumo_pedido_sys($cod_pedidos);
        echo '<br><br><center>';
        echo '<input type="button" class="botao" value="Fechar" onclick="fechar_detalhes_pedidos()">';
        echo '</center>';
        
        break;
    case 'enviar_resposta':
    
    	$cod_pedidos = validaVarPost('cod_pedidos');
    	$resposta = validaVarPost('resposta');
    	$cod_clientes_ipi_enquete_respostas = validaVarPost('cod_clientes_ipi_enquete_respostas');
    	
    	$conexao = conectabd();
    	
    	$res_inserir_reposta_pizzaria = true;
    	$enviar_email = false;
    	
    	for($i = 0; $i < count($cod_clientes_ipi_enquete_respostas); $i++)
    	{
    		if($resposta[$i] != '')
    		{
    			$enviar_email = true;
    		}
    	
    		$sql_inserir_reposta_pizzaria = sprintf("UPDATE ipi_clientes_ipi_enquete_respostas SET cod_usuarios = '%s', data_hora_resposta_pizzaria = NOW(), resposta_pizzaria = '%s', respondida_pizzaria = 1 WHERE cod_clientes_ipi_enquete_respostas = '"  . $cod_clientes_ipi_enquete_respostas[$i] . "'",
												$_SESSION['usuario']['codigo'], texto2bd($resposta[$i]));
    	
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

    
    	if($enviar_email)
    	{
    		require_once('../../ipi_email.php');
    		
    		$email = '<p>Nós agradecemos sua sugestão/reclamação realizada na enquete do pedido ' . sprintf('%08d', $cod_pedidos) . '. Seguem nossos comentários:</p><br><br>';
    		
			$sql_buscar_resposta_pizzaria = "SELECT * FROM ipi_enquete_perguntas p INNER JOIN ipi_enquete_respostas r ON (p.cod_enquete_perguntas = r.cod_enquete_perguntas) INNER JOIN ipi_clientes_ipi_enquete_respostas cr ON (r.cod_enquete_respostas = cr.cod_enquete_respostas) WHERE cr.cod_clientes_ipi_enquete_respostas IN (" . implode(',', $cod_clientes_ipi_enquete_respostas) . ")";
			$res_buscar_resposta_pizzaria = mysql_query($sql_buscar_resposta_pizzaria);
			
			while($obj_buscar_resposta_pizzaria = mysql_fetch_object($res_buscar_resposta_pizzaria))
			{
				$email .= '<p style="color: #D44E08;"><b>' . bd2texto($obj_buscar_resposta_pizzaria->pergunta) . '</b></p>';
				$email .= '<p><b>Sua opinião:</b> ' . bd2texto($obj_buscar_resposta_pizzaria->resposta) . '</p>';
				
				if($obj_buscar_resposta_pizzaria->justificativa != '')
				{
					$email .= '<p><b>Seu comentário:</b> ' . bd2texto($obj_buscar_resposta_pizzaria->justificativa) . '</p>';
				}
				
				$email .= '<p><b>Nossa resposta:</b> ' . nl2br(bd2texto($obj_buscar_resposta_pizzaria->resposta_pizzaria)) . '</p><br>';
			}
    		
    		$email .= '<p>Agradecemos sua participação na nossa enquete!</p>';
    		
    		$obj_clientes = executaBuscaSimples("SELECT * FROM ipi_pedidos p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) WHERE cod_pedidos = '$cod_pedidos'", $conexao);

    		$arr_aux = array();
            $arr_aux['cod_pedidos'] = $cod_pedidos;
            $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
            $arr_aux['cod_clientes'] = $sql_buscar_resposta_pizzaria->cod_clientes;
            $arr_aux['cod_pizzarias'] = 0;
            $arr_aux['tipo'] = 'ENQUETE_RESPOSTA';
    		enviar_email (EMAIL_PRINCIPAL, $obj_clientes->email, 'Sua enquete foi respondida!', $email, 'enquete_respondida');
    	}
    	
    	desconectabd($conexao);
    break;	
    	
    case 'resetar_senha':

    	$cod_usuario = validaVarPost('cod_usuario');
    	
    	$senha_reset = rand(100000, 999999);
    	
    	$conexao = conectabd();
		$sql_trocar_senha = "UPDATE ipi_clientes SET senha=MD5('".$senha_reset."') WHERE cod_clientes = '$cod_usuario'";
		if (mysql_query($sql_trocar_senha))
		{
			require_once '../../config.php';
			require_once '../../ipi_email.php';

			$obj_clientes = executaBuscaSimples("SELECT * FROM ipi_clientes WHERE cod_clientes = '$cod_usuario'", $conexao);
			
			
			$email_origem = EMAIL_PRINCIPAL;
			$email_destino = $obj_clientes->email;
            $assunto = NOME_FANTASIA." - Sua nova senha";
			  
			$texto .= '<br><br>Sua nova senha de acesso ao site <a href="http://'.HOST.'/" target="_blank">'.HOST.'</a> é: <b>'.$senha_reset.'</b>';
			$texto .= "<br><br>Não esqueça de troca-la no seu próximo acesso.";
			
            $arr_aux = array();
            $arr_aux['cod_pedidos'] = 0;
            $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
            $arr_aux['cod_clientes'] = $cod_usuario;
            $arr_aux['cod_pizzarias'] = 0;
            $arr_aux['tipo'] = 'SENHA_RESET';
			if (!enviar_email($email_origem, $email_destino, $assunto, $texto, 'nova_senha'))
			{
			  	echo 'Erro ao enviar E-mail!';
			}
			else 
			{
				echo utf8_encode('Senha resetada com Sucesso, a nova senha é: <font size="+1">'.$senha_reset.'</font>');	
			}  	
		}
		else
		{
			echo utf8_encode('Erro ao resetar senha!');
		}
		
		echo '<br /><br /><input type="button" name="resetar" class="botaoAzul" value="Trocar Senha" onclick="javascript: resetar_senha('.$cod_usuario.');">';
		
    	desconectabd($conexao);
    	
	break;
}

?>
