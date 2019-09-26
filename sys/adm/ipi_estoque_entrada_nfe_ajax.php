<?php

/**
 * Cadastro de Notas Fiscais de Entrada (ajax).
 *
 * @version 1.0
 * @package ipizza
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       17/03/2010   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

switch ($acao)
{
    case 'buscar_ingredientes':
        $busca_ingrediente = utf8_decode(validaVarPost('busca_ingrediente'));
        
        $conexao = conectabd();
        
        $obj_cont_ingredientes = executaBuscaSimples("SELECT COUNT(*) AS quantidade FROM ipi_ingredientes i WHERE ingrediente LIKE '%" . texto2bd($busca_ingrediente) . "%'  AND i.cod_ingredientes = i.cod_ingredientes_baixa AND ativo = 1", $conexao);
        
        $sql_buscar_ingredientes = "SELECT * FROM ipi_ingredientes i LEFT JOIN ipi_unidade_padrao up ON (i.cod_unidade_padrao = up.cod_unidade_padrao) WHERE ingrediente LIKE '%" . texto2bd($busca_ingrediente) . "%'   AND i.cod_ingredientes = i.cod_ingredientes_baixa AND ativo = 1 ORDER BY ingrediente_marca LIMIT 10";
        $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
        
        //echo "$sql_buscar_ingredientes";
        
        ?>
        
        <br><center><b><? echo $obj_cont_ingredientes->quantidade ?></b> ingrediente(s) encontrado(s).</center><br>
        
        <script>
            // A customized tip for all <span class='custom'> elements
            new FloatingTips('img.tips', {
            
                // Content can also be a function of the target element!
                content: function(e) { return e.alt; },
                
                html: true,
                position: 'bottom', // Bottom positioned
                center: false,      // Place the tip aligned with target
                arrowSize: 6,      // A bigger arrow!
                
            });
        </script>

        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="500">
		<thead>
			<tr>
				<td align="center"><label>Busca</label></td>
				<td align="center"><label><? echo utf8_encode('Marca'); ?></label></td>
				<td align="center" width="80"><label>Quantidade</label></td>
                <td align="center" width="80"><label><? echo utf8_encode('Quantidade na Embalagem'); ?></label></td>
				<!-- <td align="center" width="80"><label><? echo utf8_encode('Preço Unit.'); ?></label></td> -->
                <td align="center" width="80"><label><? echo utf8_encode('Preço Total'); ?></label></td>
				<td align="center" width="80"><label>Adicionar</label></td>
			</tr>
		</thead>
		<tbody>

		
		<?


		while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
		{
	        echo '<tr>';

            $abreviatura = ($obj_buscar_ingredientes->abreviatura ? ' (em '.$obj_buscar_ingredientes->abreviatura.')' : '');
            $divisor_comum = ($obj_buscar_ingredientes->divisor_comum ? $obj_buscar_ingredientes->divisor_comum : 1);
            
			echo '<td> '.($obj_buscar_ingredientes->instrucao_entrada ? '<img class="tips" src="../lib/img/principal/icon_help.gif" alt="'.nl2br(utf8_encode($obj_buscar_ingredientes->instrucao_entrada)).'"/>' : "").' ' . utf8_encode(bd2texto($obj_buscar_ingredientes->ingrediente).$abreviatura) . '</td>';
			
			echo '<td>' . utf8_encode(bd2texto($obj_buscar_ingredientes->ingrediente_marca)) . '</td>';

			echo '<td align="center"><input type="text" name="quantidade_ingredientes_adicionar[]" id="quantidade_ingredientes_adicionar_' . $obj_buscar_ingredientes->cod_ingredientes . '" size="8" maxsize="3" onkeypress="return ApenasNumero(event);" ></td>';
            //onkeyup="formataMoeda3casas(this, 0)"

			echo '<td align="center"><input type="text" name="quantidade_ingredientes_adicionar_embalagem[]" id="quantidade_ingredientes_adicionar_embalagem_' . $obj_buscar_ingredientes->cod_ingredientes . '" size="8" maxsize="3" value="' . str_replace(".",",",$obj_buscar_ingredientes->quantidade/$divisor_comum) . '"  onkeypress="formataMoeda3casas(this, 2)"></td>';
			
			/*echo '<td align="center"><input type="text" name="preco_ingredientes_adicionar[]" id="preco_ingredientes_adicionar_' . $obj_buscar_ingredientes->cod_ingredientes_marcas . '" size="8" maxsize="3" onkeypress="return formataMoeda(this, \'.\', \',\', event)"></td>';*/

            echo '<td align="center"><input type="text" name="preco_total_ingredientes_adicionar[]" id="preco_total_ingredientes_adicionar_' . $obj_buscar_ingredientes->cod_ingredientes . '" size="8" maxsize="3" onkeypress="return formataMoeda(this, \'.\', \',\', event)"></td>';

            if($obj_buscar_ingredientes->cod_unidade_padrao == 0)
            {
                echo '<td align="center" style="color: #F00;">'.utf8_encode('Sem Und.Padrão cadastrada').'</td>';
            }
            else
            {
    			echo '<td align="center"><input type="button" class="botaoAzul" value="Adicionar" onclick="adicionar_ingrediente(' . $obj_buscar_ingredientes->cod_ingredientes . ', ' . $obj_buscar_ingredientes->entrada_estoque_minima . ', ' . $obj_buscar_ingredientes->entrada_estoque_maxima . ')">';
                echo '<input type="hidden" name="tipo_unidade" id="tipo_unidade_' . $obj_buscar_ingredientes->cod_ingredientes . '" value="'.$obj_buscar_ingredientes->abreviatura.'" />
                <input type="hidden" name="divisor_comum" id="divisor_comum_' . $obj_buscar_ingredientes->cod_ingredientes . '" value="'.$divisor_comum.'" /></td>';           
            }
		    echo '</tr>';
		}
		
		?>
        
        </tbody>
        </table>
        
        <?

        desconectabd($conexao);
        
        break;
    case 'buscar_bebidas':
        $busca_bebida = utf8_decode(validaVarPost('busca_bebida'));
        
        $conexao = conectabd();
        $obj_cont_bebidas = executaBuscaSimples("SELECT COUNT(*) AS quantidade FROM cod_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) inner join ipi_conteudos_pizzarias cp on cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos WHERE bebida LIKE '%" . texto2bd($busca_bebida) . "%' AND cp.cod_pizzarias IN (" .$_SESSION['usuario']['cod_pizzarias'][0] . ") AND cp.situacao='ATIVO'", $conexao);
        
        $sql_buscar_bebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) inner join ipi_conteudos_pizzarias cp on cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos LEFT JOIN ipi_unidade_padrao iup ON (iup.cod_unidade_padrao = bc.cod_unidade_padrao) WHERE bebida LIKE '%" . texto2bd($busca_bebida) . "%' AND cp.cod_pizzarias IN (" .$_SESSION['usuario']['cod_pizzarias'][0] . ") AND cp.situacao='ATIVO' ORDER BY bebida, conteudo LIMIT 10";
        $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);

        ?>
        
        <br><center><b><? echo $obj_cont_bebidas->quantidade ?></b> bebidas(s) encontrada(s).</center><br>
        
        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="500">
		<thead>
			<tr>
				<td align="center"><label>Busca</label></td>
                <td align="center" width="80"><label>Quantidade de<br/>Embalagens</label></td>
                <td align="center" width="80"><label>Quantidade<br/>Embalagem</label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço<br />Embalagem'); ?></label></td>
				<td align="center" width="80"><label>Adicionar</label></td>
			</tr>
		</thead>
		<tbody>
		
		<?
		
		while($obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas))
		{
	        echo '<tr>';
			echo '<td>' . utf8_encode(bd2texto($obj_buscar_bebidas->bebida . ' - ' . $obj_buscar_bebidas->conteudo)) . '</td>';
			echo '<td align="center"><input type="text" name="quantidade_bebidas_adicionar[]" id="quantidade_bebidas_adicionar_' . $obj_buscar_bebidas->cod_bebidas_ipi_conteudos . '" size="8" maxsize="3" onkeypress="return ApenasNumero(event);"></td>';
            echo '<td align="center"><input type="text" name="quantidade_embalagem_bebidas_adicionar[]" id="quantidade_embalagem_bebidas_adicionar_' . $obj_buscar_bebidas->cod_bebidas_ipi_conteudos . '" size="8" maxsize="3" onkeypress="return ApenasNumero(event);" value="'.$obj_buscar_bebidas->quantidade_embalagem.'" /></td>';
			echo '<td align="center"><input type="text" name="preco_bebidas_adicionar[]" id="preco_bebidas_adicionar_' . $obj_buscar_bebidas->cod_bebidas_ipi_conteudos . '" size="8" maxsize="3" onkeypress="return formataMoeda(this, \'.\', \',\', event)"></td>';
			echo '<td align="center"><input type="button" class="botaoAzul" value="Adicionar" onclick="adicionar_bebida(' . $obj_buscar_bebidas->cod_bebidas_ipi_conteudos . ')"></td>';
		    echo '</tr>';
		}
		
		?>
        
        </tbody>
        </table>
        
        <?

        desconectabd($conexao);
        
        break;
    case 'adicionar_ingrediente':
        $cod_ingredientes = validaVarPost('cod_ingredientes');
        $quantidade = validaVarPost('quantidade');
        $quantidade_embalagem = validaVarPost('quantidade_embalagem');
        $preco = moeda2bd(validaVarPost('preco'));
        $preco_total = moeda2bd(validaVarPost('preco_total'));
        $preco = $preco_total/$quantidade;
        $unidade = validaVarPost('unidade');       
        $divisor_comum = validaVarPost('divisor_comum');        
        $liberacao = validaVarPost("liberacao");
        $cean_trib = validaVarPost("cean_trib");
        $unidade_trib = validaVarPost("unidade_trib");
        $cod_fornecedores_item = validaVarPost("cod_fornecedores_item");
        require_once '../../classe/estoque.php';
        
        try
        {
          $estoque = new Estoque();
          $estoque->definir_id_sessao('estoque_entrada_nfe');
          $indice = $estoque->adicionar_entrada_item($cod_ingredientes, 0, $quantidade, $quantidade_embalagem, $preco,$preco_total,$unidade, $divisor_comum,'0',$liberacao);
          $estoque->adicionar_item_fornecedor($indice,$cod_fornecedores_item,$cod_ingredientes, 0, $quantidade_embalagem, $unidade_trib,$cean_trib);

          $arr_retorno = array('resposta' => 'OK', 'mensagem' => '');    
        }
        catch (Exception $ex)
        {
          $arr_retorno = array('resposta' => 'ERRO', 'mensagem' => $ex);               
        }
        
        echo json_encode($arr_retorno);
        
        break;
    case 'adicionar_bebida':
        $cod_bebidas_ipi_conteudos = validaVarPost('cod_bebidas_ipi_conteudos');
        $quantidade = validaVarPost('quantidade');
        $preco = moeda2bd(validaVarPost('preco'));
        $preco_total = $quantidade*$preco;
        $qtd_embalagem = moeda2bd(validaVarPost('qtd_embalagem'));
        
        require_once '../../classe/estoque.php';
        
        try
        {
          $estoque = new Estoque();
          $estoque->definir_id_sessao('estoque_entrada_nfe');
          $estoque->adicionar_entrada_item(0, $cod_bebidas_ipi_conteudos, $quantidade, $qtd_embalagem, $preco,$preco_total,'ml', 1);
          
          $arr_retorno = array('resposta' => 'OK', 'mensagem' => '');    
        }
        catch (Exception $ex)
        {
          $arr_retorno = array('resposta' => 'ERRO', 'mensagem' => $ex);               
        }
        
        echo json_encode($arr_retorno);
        
        break;
    case 'alterar_ingrediente':
        $cod_ingredientes = validaVarPost('cod_ingredientes');
        $quantidade = validaVarPost('quantidade');
        $quantidade_embalagem = validaVarPost('quantidade_embalagem');
        $preco = moeda2bd(validaVarPost('preco'));
        
        require_once '../../classe/estoque.php';
        
        try
        {
            $estoque = new Estoque();
            $estoque->definir_id_sessao('estoque_entrada_nfe');
            $estoque->alterar_entrada_item($cod_ingredientes, 0, $quantidade, $quantidade_embalagem, $preco);
            
            $arr_retorno = array('resposta' => 'OK', 'mensagem' => '');    
        }
        catch (Exception $ex)
        {
            $arr_retorno = array('resposta' => 'ERRO', 'mensagem' => $ex);               
        }
        
        echo json_encode($arr_retorno);
        
        break;
    case 'alterar_bebida':
        $cod_bebidas_ipi_conteudos = validaVarPost('cod_bebidas_ipi_conteudos');
        $quantidade = validaVarPost('quantidade');
        $preco = moeda2bd(validaVarPost('preco'));
        
        require_once '../../classe/estoque.php';
        
        try
        {
            $estoque = new Estoque();
            $estoque->definir_id_sessao('estoque_entrada_nfe');
            $estoque->alterar_entrada_item(0, $cod_bebidas_ipi_conteudos, $quantidade, $preco);
            
            $arr_retorno = array('resposta' => 'OK', 'mensagem' => '');    
        }
        catch (Exception $ex)
        {
            $arr_retorno = array('resposta' => 'ERRO', 'mensagem' => $ex);               
        }
        
        echo json_encode($arr_retorno);
        
        break;
    case 'excluir_ingrediente':
        $posicao = validaVarPost('posicao');
        
        require_once '../../classe/estoque.php';
        
        try
        {
            $estoque = new Estoque();
            $estoque->definir_id_sessao('estoque_entrada_nfe');

            $estoque->excluir_entrada_temp_item($posicao, true, false);
            
            $arr_retorno = array('resposta' => 'OK', 'mensagem' => '');    
        }
        catch (Exception $ex)
        {
            $arr_retorno = array('resposta' => 'ERRO', 'mensagem' => $ex);               
        }
        
        echo json_encode($arr_retorno);
        
        break;
    case 'excluir_bebida':
        $posicao = validaVarPost('posicao');
        
        require_once '../../classe/estoque.php';
        
        try
        {
            $estoque = new Estoque();
            $estoque->definir_id_sessao('estoque_entrada_nfe');

            $estoque->excluir_entrada_temp_item($posicao, false, true);
            
            $arr_retorno = array('resposta' => 'OK', 'mensagem' => '');    
        }
        catch (Exception $ex)
        {
            $arr_retorno = array('resposta' => 'ERRO', 'mensagem' => $ex);               
        }
        
        echo json_encode($arr_retorno);
        
        break;
    case 'exibir_ingredientes_adicionados':
        require_once '../../classe/estoque.php';
        
        $estoque = new Estoque();
        $estoque->definir_id_sessao('estoque_entrada_nfe');
        $arr_ingredientes = $estoque->listar_entrada_ingredientes_temporarios();
        
        ?>
        
        <br><center><? echo utf8_encode("<b>" . count($arr_ingredientes) . "</b> ingrediente(s) na lista de inclusão."); ?></center><br>
        
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" width="600">
            <tr>
                <td><input class="botaoAzul" type="button"
                    value="Excluir Todos" onclick="limpar_ingredientes();"></td>
            </tr>
        </table>
        
        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
		<thead>
			<tr>
				<td align="center"><label>Ingrediente</label></td>
				<td align="center"><label><? echo utf8_encode('Marca'); ?></label></td>
                <td align="center" width="80"><label>Quantidade</label></td>
                <td align="center" width="80"><label><? echo utf8_encode('Quantidade na Embalagem'); ?></label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço Unit.'); ?></label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço Total'); ?></label></td>
				<td align="center" width="80"><label>Excluir</label></td>
			</tr>
		</thead>
		<tbody>
		
		<?

		$conexao = conectabd();
		
		$preco_total = 0;
		
		for($i = 0; $i < count($arr_ingredientes); $i++)
		{
		    $cod_ingredientes = $arr_ingredientes[$i]['cod_ingredientes'];
		    $quantidade = $arr_ingredientes[$i]['quantidade'];
            $quantidade_embalagem = $arr_ingredientes[$i]['quantidade_embalagem'];
            $preco = $arr_ingredientes[$i]['preco'];
            $unidade = $arr_ingredientes[$i]['unidade'];
        
        
		    
		    //$obj_buscar_ingrediente = executaBuscaSimples("SELECT * FROM ipi_ingredientes_marcas WHERE cod_ingredientes_marcas = '$cod_ingredientes_marcas' LIMIT 1", $conexao);
		    //$cod_ingredientes = $obj_buscar_ingrediente->cod_ingredientes;
		    
		    $preco_total += $quantidade * $preco;
		    
		    $obj_buscar_ingredientes_marcas = executaBuscaSimples("SELECT * FROM ipi_ingredientes i INNER JOIN ipi_unidade_padrao up ON (up.cod_unidade_padrao = i.cod_unidade_padrao) WHERE i.cod_ingredientes = '$cod_ingredientes' LIMIT 1", $conexao);

		    echo '<tr>';
		    echo '<td> ' . utf8_encode(bd2texto($obj_buscar_ingredientes_marcas->ingrediente).' (em '.$unidade.')') . '</td>';
		    echo '<td>' . utf8_encode(bd2texto($obj_buscar_ingredientes_marcas->ingrediente_marca  )) . '</td>';

            echo '<td align="center"><input type="text" name="quantidade_ingredientes_alterar[]" id="quantidade_ingredientes_alterar_' . $cod_ingredientes . '" value="' . $quantidade . '" size="8" maxsize="3" readonly="readonly" ></td>';//onkeypress="return ApenasNumero(event);
            
            echo '<td align="center"><input type="text" name="quantidade_ingredientes_alterar[]" id="quantidade_ingredientes_alterar_embalagem_' . $cod_ingredientes . '" value="' . $quantidade_embalagem . '" size="8" maxsize="3" readonly="readonly"></td>';

            echo '<td align="center"><input type="text" name="preco_ingredientes_alterar[]" id="preco_ingredientes_alterar_' . $cod_ingredientes . '" value="' . bd2moeda($preco) . '" size="8" maxsize="3" readonly="readonly"></td>';
            
            echo '<td align="center">' . bd2moeda($quantidade * $preco) . '</td>';
		    echo '<td align="center"><input type="button" value="Excluir" class="botaoAzul" onclick="if(confirm(\'Deseja EXCLUIR este ingrediente?\')) {  excluir_ingrediente(' . $i . '); calcular_valor_final();} "></td>';
		    echo '</tr>';
		}
		
		desconectabd($conexao);
		
		echo '<tr>';
	    echo '<td colspan="5" align="center"><b>Total</b></td>';
	    echo '<td align="center"><input id="total_ingredientes" type="text" readonly="readonly" style="border:0;text-align:right;font-weight:bold;width=100px" value="' . bd2moeda($preco_total) . '"</td>';
	    echo '<td align="center">&nbsp;</td>';
        echo '<input type="hidden" name="total_ingrediente" id="total_ingrediente" value='.$preco_total.'/>';
	    echo '</tr>';
/*      echo "<tr><td colspan='7'><pre>";
      print_r($_SESSION['estoque_entrada_nfe']);
      echo "</pre></td></tr>";*/
		?>
		
		</tbody>
		</table>
        <script>calcular_valor_final();</script>
        <?
        
        break;
    case 'exibir_bebidas_adicionados':
        require_once '../../classe/estoque.php';
        
        $estoque = new Estoque();
        $arr_bebidas = $estoque->listar_entrada_bebidas_temporarios();
        
        ?>
        
        <br><center><? echo utf8_encode("<b>" . count($arr_bebidas) . "</b> bebidas(s) na lista de inclusão."); ?></center><br>
        
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" width="600">
            <tr>
                <td><input class="botaoAzul" type="button"
                    value="Excluir Todos" onclick="limpar_ingredientes();"></td>
            </tr>
        </table>
        
        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
		<thead>
			<tr>
				<td align="center"><label>Bebida</label></td>
                <td align="center" width="80"><label>Quantidade de<br />Embalagens</label></td>
                <td align="center" width="80"><label>Quantidade<br />Embalagem</label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço<br />Embalagem'); ?></label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço Total'); ?></label></td>
				<td align="center" width="80"><label>Excluir</label></td>
			</tr>
		</thead>
		<tbody>
		
		<?

		$conexao = conectabd();
		
		$preco_total = 0;
		
		for($i = 0; $i < count($arr_bebidas); $i++)
		{
		    $cod_bebidas_ipi_conteudos = $arr_bebidas[$i]['cod_bebidas_ipi_conteudos'];
		    $quantidade = $arr_bebidas[$i]['quantidade'];
            $preco = $arr_bebidas[$i]['preco'];
            $qtd_embalagem = $arr_bebidas[$i]['quantidade_embalagem'];
		    
		    $preco_total += $quantidade * $preco;
		    
		    $obj_buscar_bebidas = executaBuscaSimples("SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) WHERE cod_bebidas_ipi_conteudos = '$cod_bebidas_ipi_conteudos'", $conexao);

		    echo '<tr>';
		    echo '<td>' . utf8_encode(bd2texto($obj_buscar_bebidas->bebida . ' - ' . $obj_buscar_bebidas->conteudo)) . '</td>';
            echo '<td align="center"><input type="text" name="quantidade_bebidas_alterar[]" id="quantidade_bebidas_alterar_' . $obj_buscar_bebidas->cod_bebidas_ipi_conteudos . '" value="' . $quantidade . '" size="8" maxsize="3" onkeypress="return ApenasNumero(event);"></td>';
            echo '<td align="center"><input type="text" name="quantidade_embalagem_bebidas_alterar[]" id="quantidade_embalagem_bebidas_alterar_' . $obj_buscar_bebidas->cod_bebidas_ipi_conteudos . '" value="' . $qtd_embalagem . '" size="8" maxsize="3" onkeypress="return ApenasNumero(event);"></td>';
		    echo '<td align="center"><input type="text" name="preco_bebidas_alterar[]" id="preco_bebidas_alterar_' . $obj_buscar_bebidas->cod_bebidas_ipi_conteudos . '" value="' . bd2moeda($preco) . '" size="8" maxsize="3" onkeypress="return formataMoeda(this, \'.\', \',\', event)"></td>';
		    echo '<td align="center">' . bd2moeda($quantidade * $preco) . '</td>';
		    echo '<td align="center"><input type="button" value="Excluir" class="botaoAzul" onclick="if(confirm(\'Deseja EXCLUIR esta bebida?\')) {  excluir_bebida(' . $i . '); calcular_valor_final();} "></td>';
		    echo '</tr>';
		}
		
		desconectabd($conexao);
		
		echo '<tr>';
	    echo '<td colspan="4" align="center"><b>Total</b></td>';
	    echo '<td align="center"><input id="total_bebidas" type="text" readonly="readonly" style="border:0;text-align:right;font-weight:bold;width=100px" value="' . bd2moeda($preco_total) . '"</td>';
        echo '<input type="hidden" name="total_bebida" id="total_bebida" value='.$preco_total.'/>';
	    echo '<td align="center">&nbsp;</td>';
	    echo '</tr>';
		
		?>
		
		</tbody>
		</table>
        <script>calcular_valor_final();</script>
        <?
        
        break;
    case 'criar_formulario_pagamento':
        $cod_titulos_subcategorias = validaVarPost('cod_titulos_subcategorias');
        
        $conexao = conectabd();
        
        $obj_detalhes_subcategoria = executaBuscaSimples("SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_subcategorias = '" . $cod_titulos_subcategorias . "'", $conexao);
        
        desconectabd($conexao);
        
        ?>
        
        <table cellpadding="0" cellspacing="0">
        
        
        <tr>
            <td class="legenda">
            	<label for=cod_fornecedores class="requerido">Fornecedor</label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<select name="cod_fornecedores" id="cod_fornecedores" style="width: 300px;" class="requerido">
                	<option value=""></option>
                	
                	<?
    
                	$conexao = conectabd();
                	
                	$sql_buscar_fornecedores = "SELECT * FROM ipi_fornecedores f inner join ipi_titulos_subcategorias_ipi_fornecedores sf on f.cod_fornecedores = sf.cod_fornecedores where sf.cod_titulos_subcategorias = '$cod_titulos_subcategorias'  ORDER BY f.nome_fantasia";
                	$res_buscar_fornecedores = mysql_query($sql_buscar_fornecedores);
                	
                	while($obj_buscar_fornecedores = mysql_fetch_object($res_buscar_fornecedores))
                	{
                	    echo '<option value="' . $obj_buscar_fornecedores->cod_fornecedores . '" ';
                	    
                	    if($obj_titulos->cod_fornecedores == $obj_buscar_fornecedores->cod_fornecedores)
                	    {
                	        echo 'selected';   
                	    }
                	    
                	    echo '>' . utf8_encode(bd2texto($obj_buscar_fornecedores->nome_fantasia)) . '</option>';
                	}
                	
                	desconectabd($conexao);
                	
                	?>
            	
            	</select>
			</td> 
        </tr>

        <tr>
            <td class="legenda">
                <label for="financeiro_descricao"><? echo utf8_encode('Descrição') ?></label>
            </td>
        </tr>
        <tr>
            <td class="sep">
                <input type="text" name="financeiro_descricao" id="financeiro_descricao" maxlength="45" style="width: 295px;">
            </td>
        </tr>
        
        <? 
        
        if ($obj_detalhes_subcategoria->num_parcelas_maximo == 1): 
        
        ?>
        
        <tr>
            <td class="legenda">
            	<label class="requerido" for="vencimento[]"><? echo utf8_encode('Data de Vencimento') ?></label>
                <label class="requerido" for="emissao[]" style="margin-left: 40px;"><? echo utf8_encode('Data de Emissão') ?></label>
            	<label class="requerido" for="valor[]" style="margin-left: 40px;"><? echo utf8_encode('Valor') ?></label>
            	<label class="requerido" for="mes_ref[]" style="margin-left: 87px;"><? echo utf8_encode('Ref. (MM/AAAA)') ?></label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<input type="text" class="requerido" name="vencimento[]" id="vencimento" maxlength="10" size="16" onkeypress="return MascaraData(this, event)">
            	&nbsp;
            	<a href="javascript:;" id="botao_vencimento"><img src="../lib/img/principal/botao-data.gif"></a>
            	
                <input type="text"  style="margin-left: 10px;" class="requerido" name="emissao[]" id="emissao" maxlength="10" size="16" onkeypress="return MascaraData(this, event)">
                &nbsp;
                <a href="javascript:;" id="botao_emissao"><img src="../lib/img/principal/botao-data.gif"></a>

            	<input type="text" class="requerido" name="valor[]" id="valor" size="15" style="margin-left: 10px;" onkeypress="return formataMoeda(this, '.', ',', event)">
            	<input type="text" class="requerido" name="mes_ref[]" id="mes_ref" size="11" style="margin-left: 10px;" onkeypress="return Mascara(this, event, '##/####')">
            </td>
        </tr>
        
        <input type="hidden" name="num_parcelas" value="1">
        
        <? elseif ($obj_detalhes_subcategoria->num_parcelas_maximo > 1): ?>
        
        <tr>
            <td class="legenda">
            	<label for="num_parcelas"><? echo utf8_encode('Parcelas') ?></label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<select name="num_parcelas" id="num_parcelas" style="width: 120px;" onchange="criar_parcelas(this.value)">
            		<option value=""></option>
            		
            		<?

            		for($i = 1; $i <= $obj_detalhes_subcategoria->num_parcelas_maximo; $i++)
            		{
            	        echo '<option value="' . $i . '">' . $i . 'x</option>';
            		}
            		
            		?>
            		
            	</select>
            </td>
        </tr>
        
        <tr><td id="criar_parcelas"></td></tr>
        
        <? endif; ?>
        
        </table>
        <? 
        echo "<script> new vlaDatePicker('vencimento', {openWith: 'botao_vencimento'});
                new vlaDatePicker('emissao', {openWith: 'botao_emissao'});</script>";
         
    
        break;
    case 'criar_parcelas':
        $num_parcelas = validaVarPost('num_parcelas');
        
        echo '<br>';
        
        for($i = 1; $i <= $num_parcelas; $i++):
        
        ?>
        
        <br>
        
        <label>Parcela <? echo $i ?></label>
        <hr color="#1A498F" size="1" noshade="noshade">
        
        <table style="margin-top: 10px;" cellpadding="0" cellspacing="0">
        
        <tr>
            <td class="legenda">
            	<label class="requerido" for="vencimento[]"><? echo utf8_encode('Data de Vencimento') ?></label>
                <label class="requerido" for="emissao[]" style="margin-left: 21px;"><? echo utf8_encode('Data de Emissão') ?></label>
            	<label class="requerido" for="valor[]" style="margin-left: 50px;"><? echo utf8_encode('Valor') ?></label>
            	<label class="requerido" for="mes_ref[]" style="margin-left: 87px;"><? echo utf8_encode('Ref. (MM/AAAA)') ?></label>
        	</td>
        </tr>
    	<tr>
            <td class="sep">
            	<input type="text" class="requerido" name="vencimento[]" id="vencimento<? echo $i ?>" maxlength="10" size="15" onkeypress="return MascaraData(this, event)">
            	&nbsp;
            	<a href="javascript:;" id="botao_vencimento<? echo $i ?>"><img src="../lib/img/principal/botao-data.gif"></a>
            	
                <input type="text" class="requerido" name="emissao[]" id="emissao<? echo $i ?>" maxlength="10" size="15" onkeypress="return MascaraData(this, event)">
                &nbsp;
                <a href="javascript:;" id="botao_emissao<? echo $i ?>"><img src="../lib/img/principal/botao-data.gif"></a>

            	<input type="text" class="requerido" name="valor[]" id="valor" size="15" style="margin-left: 10px;" onkeypress="return formataMoeda(this, '.', ',', event)">
            	<input type="text" class="requerido" name="mes_ref[]" id="mes_ref" size="11" style="margin-left: 10px;" onkeypress="return Mascara(this, event, '##/####')">
            </td>
        </tr>
        
        </table>
        
        <?
        
        endfor;

        if ($num_parcelas>0)
        {
          echo "<script>for (x = 1; x <= " . $num_parcelas . "; x++) { new vlaDatePicker('vencimento' + x, {openWith: 'botao_vencimento' + x}); };
          for (x = 1; x <= " . $num_parcelas . "; x++) { new vlaDatePicker('emissao' + x, {openWith: 'botao_emissao' + x}); }</script>";
        }
        break;
        case 'carregar_ingredientes_marcas':
          $cod_ingredientes = validaVarPost("cim");

          $conexao = conectabd();

          $sql_buscar_ingredientes = "SELECT *,'achou' as achou FROM ipi_ingredientes i  LEFT JOIN ipi_unidade_padrao up ON (i.cod_unidade_padrao = up.cod_unidade_padrao) WHERE i.cod_ingredientes = '" . $cod_ingredientes . "'  AND ativo = 1 ORDER BY ingrediente_marca LIMIT 1";
          $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
          $obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes);

          $arr_json = array();
          $arr_json["marca"] = $obj_buscar_ingredientes->marca;

          

          $arr_json["ingrediente"] = $obj_buscar_ingredientes->ingrediente;
          $arr_json["abreviatura"] = $obj_buscar_ingredientes->abreviatura;
          $arr_json["divisor_comum"] = $obj_buscar_ingredientes->divisor_comum;
         // $arr_json["cod_ingredientes_marcas"] = $obj_buscar_ingredientes->cod_ingredientes_marcas;
          $arr_json["quantidade"] = $obj_buscar_ingredientes->quantidade;
          $arr_json["cod_unidade_padrao"] = $obj_buscar_ingredientes->cod_unidade_padrao;
          $arr_json["entrada_estoque_minima"] = $obj_buscar_ingredientes->entrada_estoque_minima;
          $arr_json["entrada_estoque_maxima"] = $obj_buscar_ingredientes->entrada_estoque_maxima;

          $quantidade_emb = str_replace(".",",",$obj_buscar_ingredientes->quantidade/$obj_buscar_ingredientes->divisor_comum);
          $arr_json["quantidade_emb"] = $quantidade_emb;
          $arr_json["status"] = "OK";
          //$arr_json["marca"] = ;
          desconectabd($conexao);
          echo json_encode($arr_json);
        break;
        case 'ler_produtos_nfe':
          //require_once '../lib/php/formatacao.php';
          $notaf_tmp = validaVarPost('notaf_tmp');
          $cod_fornecedores = validaVarPost("cod_forncedores_enviar");
          if (is_uploaded_file($_FILES['notaf']['tmp_name']))
          {
            $fileData = file_get_contents($_FILES['notaf']['tmp_name']);
             // echo 'foi';
            

            //$filename = 'nfe.xml';

            //echo "<br/><br/>";
            //echo $fileData;
            //echo "<br/><br/>";
            $xml_nfe = new DOMDocument( '1.0', 'UTF-8' );
            $xml_nfe->preserveWhiteSpace = false;
            $xml_nfe->load( $_FILES['notaf']['tmp_name'] );
            if ($xml_nfe->schemaValidate('../lib/xsd/validadores_nfe/PL_006s/procNFe_v2.00.xsd')) 
            { 
              $products = $xml_nfe->getElementsByTagName( 'prod' );

              ?>
              <html><body style='background:none'>
              <link rel="stylesheet" type="text/css" media="screen" href="../lib/css/principal.css" />
              <script src="../lib/js/mascara.js" type="text/javascript"></script>
                <script>
                    // A customized tip for all <span class='custom'> elements
/*                    new FloatingTips('img.tips', {
                    
                        // Content can also be a function of the target element!
                        content: function(e) { return e.alt; },
                        
                        html: true,
                        position: 'bottom', // Bottom positioned
                        center: false,      // Place the tip aligned with target
                        arrowSize: 6,      // A bigger arrow!
                        
                    });*/
                </script>
               <!--  <br><center><b><? echo $obj_cont_ingredientes->quantidade ?></b> ingrediente(s) encontrado(s).</center><br> -->
                <table class="listaEdicao"  cellpadding="0" cellspacing="0" width="670">
                <thead style='display: block'>
                  <tr>
                    <td align="center" width="159"><label>Busca</label></td>
                    <td align="center" width="290"><label>Encontrado</label></td>
                    <td align="center" width="82"><label>Quantidade</label></td>
                    <td align="center" width="20"><label><? echo utf8_encode('Un'); ?></label></td>
                    <td align="center" width="109"><label><? echo utf8_encode('Quantidade na Embalagem'); ?></label></td>
                    <!-- <td align="center" width="80"><label><? echo utf8_encode('Preço Unit.'); ?></label></td> -->
                    <td align="center" width="82"><label><? echo ('Preço Total'); ?></label></td>
                    <td align="center" width="56"><label>Conferência</label></td>
                  </tr>
                </thead>
                <tbody style="display: block;height: 360px;overflow: auto;">

            
                <?
                $i = 0;


                 $conexao = conectabd();
                $options = '<option value=""></option>';         
                $sql_buscar_categorias = "SELECT * FROM ipi_ingredientes i LEFT JOIN ipi_unidade_padrao up ON (i.cod_unidade_padrao = up.cod_unidade_padrao) WHERE ativo = 1 and i.cod_ingredientes = i.cod_ingredientes_baixa ORDER BY ingrediente,ingrediente_marca ";
                $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                {
                  $options.= '<option value="' . $obj_buscar_categorias->cod_ingredientes . '"';
                  $options.= '>' . bd2texto($obj_buscar_categorias->ingrediente_abreviado.' - '.$obj_buscar_categorias->ingrediente_marca) . '</option>';
                }
                                

              foreach( $products as $product )
              {
                $cor = " style='background-color:FAAFB0' ";

                $busca_ingrediente = utf8_decode(validaVarPost('busca_ingrediente'));
        
               
                
/*                $obj_cont_ingredientes = executaBuscaSimples("SELECT COUNT(*) AS quantidade FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_marcas m ON (i.cod_ingredientes = m.cod_ingredientes) WHERE ingrediente LIKE '%" . texto2bd($busca_ingrediente) . "%' AND ativo = 1", $conexao);*/
                $nome_produto = $product->getElementsByTagName( 'xProd' )->item( 0 )->nodeValue;
                $nome_produto = str_replace(" DE ", "", $nome_produto);
                $nome_produto = str_replace(" ", "|", $nome_produto );
                $nome_produto = str_replace("+", "", $nome_produto);
                $nome_produto = str_replace("-", "", $nome_produto);
                

                $sql_count_ingredientes = "SELECT (SELECT count(cod_ingredientes) FROM ipi_ingredientes i LEFT JOIN ipi_unidade_padrao up ON (i.cod_unidade_padrao = up.cod_unidade_padrao) WHERE ingrediente REGEXP '" . filtrar_caracteres_sql($nome_produto) . "'  AND ativo = 1 ORDER BY ingrediente_marca) as count_nome,(select count(quantidade_embalagem) from ipi_ingredientes_ipi_fornecedores where cod_fornecedores = '$cod_fornecedores' and cod_item_fornecedor = '".$product->getElementsByTagName( 'cProd' )->item( 0 )->nodeValue."') as count_cprod,(select count(quantidade_embalagem) from ipi_ingredientes_ipi_fornecedores where cean_trib = '".$product->getElementsByTagName( 'cEANTrib' )->item( 0 )->nodeValue."' and cean_trib!='') as count_ceantrib";
                //echo "$sql_count_ingredientes<br/><br/>";
                $res_count_ingredientes = mysql_query($sql_count_ingredientes);
                $obj_count_ingredientes = mysql_fetch_object($res_count_ingredientes);
                //echo "......".$obj_count_ingredientes->count_nome." - ".$obj_count_ingredientes->count_cprod." - ".$obj_count_ingredientes->count_ceantrib."<br/>";

                if($obj_count_ingredientes->count_cprod>0)
                {
                  $sql_buscar_ingredientes = "SELECT *,'achou' as achou ,inf.quantidade_embalagem as quantidade_emb FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_fornecedores inf on inf.cod_ingredientes = i.cod_ingredientes LEFT JOIN ipi_unidade_padrao up ON (i.cod_unidade_padrao = up.cod_unidade_padrao) WHERE inf.cod_fornecedores = '$cod_fornecedores' and inf.cod_item_fornecedor = '".$product->getElementsByTagName( 'cProd' )->item( 0 )->nodeValue."' AND i.ativo = 1 ORDER BY ingrediente_marca LIMIT 1";
                  $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
                 // echo "$sql_buscar_ingredientes<br/><br/>"; 
                  $obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes);
                }
                elseif($obj_count_ingredientes->count_ceantrib>0)
                {
                  $sql_buscar_ingredientes = "SELECT *,'achou' as achou,inf.quantidade_embalagem as quantidade_emb FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_fornecedores inf on inf.cod_ingredientes = i.cod_ingredientes LEFT JOIN ipi_unidade_padrao up ON (i.cod_unidade_padrao = up.cod_unidade_padrao) WHERE inf.cean_trib = '".$product->getElementsByTagName( 'cEANTrib' )->item( 0 )->nodeValue."' AND i.ativo = 1 ORDER BY i.ingrediente_marca LIMIT 1";
                  $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
                 // echo "$sql_buscar_ingredientes<br/><br/>";
                  $obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes);
                }
                elseif($obj_count_ingredientes->count_nome>0)
                {
                  $sql_buscar_ingredientes = "SELECT *,'achou' as achou,i.quantidade  as quantidade_emb  FROM ipi_ingredientes i LEFT JOIN ipi_unidade_padrao up ON (i.cod_unidade_padrao = up.cod_unidade_padrao) WHERE ingrediente REGEXP '" . filtrar_caracteres_sql($nome_produto) . "'  AND ativo = 1 ORDER BY ingrediente_marca LIMIT 1";
                  $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
                  //echo "$sql_buscar_ingredientes<br/><br/>";
                  $obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes);
                }
                else
                {
                  $obj_buscar_ingredientes = (object)'';
                }
                

                $achou = ($obj_buscar_ingredientes->achou=="achou"? true : false);

                $abreviatura = ($obj_buscar_ingredientes->abreviatura ? ' (em '.$obj_buscar_ingredientes->abreviatura.')' : '');
                $divisor_comum = ($obj_buscar_ingredientes->divisor_comum ? $obj_buscar_ingredientes->divisor_comum : 1);


                $quantidade_ing = $product->getElementsByTagName( 'qTrib' )->item( 0 )->nodeValue;
                $quantidade_emb = $obj_buscar_ingredientes->quantidade_emb/$divisor_comum;
                $unidade_compra = $product->getElementsByTagName( 'uTrib' )->item( 0 )->nodeValue;

                if($unidade_compra=="KG")
                {
                  //$quantidade_ing = $quantidade_ing;
                  $quantidade_emb = $quantidade_ing;
                  $quantidade_ing = 1;

                  if($obj_buscar_ingredientes->abreviatura=="g")
                  { 
                    $quantidade_emb = $quantidade_emb*1000;
                  }
/*                  if($obj_buscar_ingredientes->abreviatura=="kg")
                  {
                    $quantidade_ing = 1;
                  }
                  elseif($obj_buscar_ingredientes->abreviatura=="g")
                  { 
                    $quantidade_ing = 1000;
                  }*/


                }
                $quantidade_ing = str_replace(".",",",$quantidade_ing);
                $quantidade_emb = str_replace(".",",",$quantidade_emb);
               



                echo '<tr id="linha_'.$i.'">';
                  echo "<td $cor width='160'>".$product->getElementsByTagName( 'xProd' )->item( 0 )->nodeValue."</td>";      
                  
/*                  if($achou)
                  {
                    echo '<td '.$cor.'> '.($obj_buscar_ingredientes->instrucao_entrada ? '<img class="tips" src="../lib/img/principal/icon_help.gif" alt="'.nl2br(utf8_encode($obj_buscar_ingredientes->instrucao_entrada)).'"/>' : "").' ' . (bd2texto($obj_buscar_ingredientes->ingrediente).$abreviatura) . '</td>';//utf8_encode
                  }
                  else*/
                  {
                    ?>
                        <td <? echo $cor?> width='290'>
                            <select name="ingrediente_filtro" id="ingrediente_filtro_<? echo $i?>" onchange="parent.carregar_ingrediente(this.value,'<? echo $i ?>')">
                            <? echo $options ?>
                            </select>
                            <script>document.getElementById("ingrediente_filtro_<? echo $i?>").value = <? echo "'$obj_buscar_ingredientes->cod_ingredientes'" ?></script>
                        </td>

                    <?

                  }

                  echo '<td '.$cor.' align="center" width="82"><input type="text" name="quantidade_ingredientes_adicionar[]" id="quantidade_ingredientes_adicionar_' . $i . '" size="8" maxsize="3" value='.$quantidade_ing.' onkeypress="return ApenasNumero(event);" ></td>';

                  echo '<td '.$cor.' id="marca_ing_'.$i.'" width="20">' . (bd2texto($unidade_compra)) . '<input type="hidden" id="unidade_compra_nota_'.$i.'" value="'.(bd2texto($unidade_compra)).'"/></td>';//utf8_encode
                        //onkeyup="formataMoeda3casas(this, 0)"

                  echo '<td '.$cor.' align="center" width="109"><input type="text" name="quantidade_ingredientes_adicionar_embalagem[]" id="quantidade_ingredientes_adicionar_embalagem_' . $i . '" size="5" maxsize="3" value="' . $quantidade_emb . '"  onkeypress="formataMoeda3casas(this, 2)"><div id="unidade_ing_sys_'.$i.'" style="display: inline;width:30px">'.($obj_buscar_ingredientes->abreviatura == ''? '&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp' : ' '.$obj_buscar_ingredientes->abreviatura).'</div></td>';
                  

                  echo '<td '.$cor.' align="center" width="82"><input type="text" name="preco_total_ingredientes_adicionar[]" id="preco_total_ingredientes_adicionar_' . $i . '" size="8" maxsize="3" value='.bd2moeda($product->getElementsByTagName( 'vProd' )->item( 0 )->nodeValue).' onkeypress="return formataMoeda(this, \'.\', \',\', event)"></td>';


                  echo '<td '.$cor.' align="center" style="color: #F00;" width="56"><div id="msg_erro_'.$i.'">';
                  
                  if($obj_buscar_ingredientes->cod_ingredientes == 0)
                  {
                    echo 'Selecione um Ing</div><div id="botao_add_'.$i.'" style="display:none">';
                  }
                  else
                  {
                    if($obj_buscar_ingredientes->cod_unidade_padrao == 0)
                    {
                      echo 'Sem Und.Padrão cadastrada</div><div id="botao_add_'.$i.'" style="display:none">';//utf8_encode
                    }
                    else
                    {
                      echo '</div><div id="botao_add_'.$i.'">';    
                    }

                    //controle de cor
                  }
                  echo '<input type="checkbox" value="OK" onclick="parent.confirmar_ingredientes(' . $i . ',this)">Ok</input></div>';//<input type="button" class="botaoAzul" value="Adicionar" onclick="parent.adicionar_ingrediente(' . $i . ')"/>
                  echo '<input type="hidden" name="entrada_estoque_minima" id="entrada_estoque_minima_' . $i . '" value="'.$obj_buscar_ingredientes->entrada_estoque_minima.'" /><input type="hidden" name="tipo_unidade" id="tipo_unidade_' . $i . '" value="'.$obj_buscar_ingredientes->abreviatura.'" /><input type="hidden" name="entrada_estoque_maxima" id="entrada_estoque_maxima_' . $i . '" value="'.$obj_buscar_ingredientes->entrada_estoque_maxima.'" />
                        <input type="hidden" name="divisor_comum" id="divisor_comum_' . $i . '" value="'.$divisor_comum.'" /><input type="hidden" name="cod_ingredientes" id="cod_ingredientes_' . $i . '" value="'.$obj_buscar_ingredientes->cod_ingredientes.'" /><input type="hidden" name="confirmacao" id="confirmacao_'.$i.'" value="0" /><input type="hidden" name="cean_trib" id="cean_trib_'.$i.'" value="'.$product->getElementsByTagName( 'cEANTrib' )->item( 0 )->nodeValue.'"/><input type="hidden" name="cod_fornecedores_item" id="cod_fornecedores_item_'.$i.'" value="'.$product->getElementsByTagName( 'cProd' )->item( 0 )->nodeValue.'"/></td>';       

                echo '</tr>';
                

        
/*                printf( '<strong>Produto:</strong> %s<br/>
                         <strong>Valor:</strong> %01.2f<br/>
                         <strong>Unidade:</strong> %s<br/>
                         <strong>Quantidade:</strong> %s<br/>
                         <strong>cEAN:</strong> %s<br/>
                         <strong>cEANTrib:</strong> %s<br/>
                         <strong>qTrib:</strong> %s<br/>
                         <strong>uTrib:</strong> %s<br/>
                         <strong>qCom:</strong> %s<br/>
                         <strong>uCom:</strong> %s<br/>', //%01.2f
                        $product->getElementsByTagName( 'xProd' )->item( 0 )->nodeValue,
                        $product->getElementsByTagName( 'vUnCom' )->item( 0 )->nodeValue,
                        $product->getElementsByTagName( 'uCom' )->item( 0 )->nodeValue,
                        $product->getElementsByTagName( 'qTrib' )->item( 0 )->nodeValue,
                        $product->getElementsByTagName( 'cEAN' )->item( 0 )->nodeValue,
                        $product->getElementsByTagName( 'cEANTrib' )->item( 0 )->nodeValue,
                        $product->getElementsByTagName( 'qTrib' )->item( 0 )->nodeValue,
                        $product->getElementsByTagName( 'uTrib' )->item( 0 )->nodeValue,
                        $product->getElementsByTagName( 'qCom' )->item( 0 )->nodeValue,
                        $product->getElementsByTagName( 'uCom' )->item( 0 )->nodeValue
                );*/
              
               // echo "<br/>";
                $i++;
              }

                ?>
                <tr><td colspan='5'></td><td colspan='2'><input type='hidden' name='quantidade_linhas' id='quantidade_linhas' value='<? echo $i ?>'> <input type='button' class='botaoAzul' value='Adicionar Todos Conferidos' onclick='parent.adicionar_todos()' /></td></tr>
                </tbody>
                </table>
                
                <?

                desconectabd($conexao);
            }
            else
            {
              echo "houve um erro com a validação da nfe";
              echo "<br/><a href='javascript:void(0)' onclick='window.history.back()'>Voltar</a>";
            }
          }
          else
          {
            echo "houve um erro com o envio da nfe";
            echo "<br/><a href='javascript:void(0)' onclick='window.history.back()'>Voltar</a>";
          }
          ?>
          </body></html>
          <?
        break;
        default:

        ?>
        <html><head>
        
        </head><body style='background:none'>  
        <link rel="stylesheet" type="text/css" media="screen" href="../lib/css/principal.css" />
        <table align="center" class="caixa" cellpadding="0" cellspacing="0" >
          <tr>
            <td>
              <form action='' method="post" onsubmit='return parent.validar_fornecedor()' enctype="multipart/form-data">
                <input type="hidden" name='acao' value='ler_produtos_nfe'>
                <input type="file" name='notaf'/>
                <input type="hidden" id='cod_forncedores_enviar' name='cod_forncedores_enviar' value=''>
                <input class='botao' type="submit" value='Enviar'>
              </form>
            </td>
          </tr>
        </table>
        </body></html>
        <?
       /* echo "nada aqui";*/
        break;
}

?>
