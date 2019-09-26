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
        $busca_ingrediente = validaVarPost('busca_ingrediente');
        
        $conexao = conectabd();
        
        $obj_cont_ingredientes = executaBuscaSimples("SELECT COUNT(*) AS quantidade FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_marcas m ON (i.cod_ingredientes = m.cod_ingredientes) WHERE ingrediente LIKE '%" . texto2bd($busca_ingrediente) . "%' AND ativo = 1", $conexao);
        
        $sql_buscar_ingredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_marcas m ON (i.cod_ingredientes = m.cod_ingredientes) INNER JOIN ipi_ingredientes_unidade_padrao up ON (m.cod_ingredientes_unidade_padrao = up.cod_ingredientes_unidade_padrao) WHERE ingrediente LIKE '%" . texto2bd($busca_ingrediente) . "%'  AND ativo = 1 ORDER BY ingrediente_marca LIMIT 10";
        $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
        
        ?>
        
        <br><center><b><? echo $obj_cont_ingredientes->quantidade ?></b> ingrediente(s) encontrado(s).</center><br>
        
        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="500">
		<thead>
			<tr>
				<td align="center"><label>Busca</label></td>
				<td align="center"><label><? echo utf8_encode('Marca / Quantidade / Unidade Padrão'); ?></label></td>
				<td align="center" width="80"><label>Quantidade</label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço Unit.'); ?></label></td>
				<td align="center" width="80"><label>Adicionar</label></td>
			</tr>
		</thead>
		<tbody>
		
		<?
		
		while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
		{
	        echo '<tr>';
			echo '<td>' . utf8_encode(bd2texto($obj_buscar_ingredientes->ingrediente)) . '</td>';
			echo '<td>' . utf8_encode(bd2texto($obj_buscar_ingredientes->ingrediente_marca . ' (' . $obj_buscar_ingredientes->quantidade . ') ' . strtolower($obj_buscar_ingredientes->unidade_padrao))) . '</td>';
			echo '<td align="center"><input type="text" name="quantidade_ingredientes_adicionar[]" id="quantidade_ingredientes_adicionar_' . $obj_buscar_ingredientes->cod_ingredientes_marcas . '" size="8" maxsize="3" onkeypress="return ApenasNumero(event);"></td>';
			echo '<td align="center"><input type="text" name="preco_ingredientes_adicionar[]" id="preco_ingredientes_adicionar_' . $obj_buscar_ingredientes->cod_ingredientes_marcas . '" size="8" maxsize="3" onkeypress="return formataMoeda(this, \'.\', \',\', event)"></td>';
			echo '<td align="center"><input type="button" class="botaoAzul" value="Adicionar" onclick="adicionar_ingrediente(' . $obj_buscar_ingredientes->cod_ingredientes_marcas . ')"></td>';
		    echo '</tr>';
		}
		
		?>
        
        </tbody>
        </table>
        
        <?

        desconectabd($conexao);
        
        break;
    case 'buscar_bebidas':
        $busca_bebida = validaVarPost('busca_bebida');
        
        $conexao = conectabd();
        
        $obj_cont_bebidas = executaBuscaSimples("SELECT COUNT(*) AS quantidade FROM cod_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) WHERE bebida LIKE '%" . texto2bd($busca_bebida) . "%' AND situacao = 1", $conexao);
        
        $sql_buscar_bebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) WHERE bebida LIKE '%" . texto2bd($busca_bebida) . "%'  AND situacao = 1 ORDER BY bebida, conteudo LIMIT 10";
        $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
        
        ?>
        
        <br><center><b><? echo $obj_cont_bebidas->quantidade ?></b> bebidas(s) encontrada(s).</center><br>
        
        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="500">
		<thead>
			<tr>
				<td align="center"><label>Busca</label></td>
				<td align="center" width="80"><label>Quantidade</label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço Unit.'); ?></label></td>
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
        $cod_ingredientes_marcas = validaVarPost('cod_ingredientes_marcas');
        $quantidade = validaVarPost('quantidade');
        $preco = moeda2bd(validaVarPost('preco'));
        
        require_once '../../classe/estoque.php';
        
        try
        {
            $estoque = new Estoque();
            $estoque->adicionar_entrada_item($cod_ingredientes_marcas, 0, $quantidade, $preco);
            
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
        
        require_once '../../classe/estoque.php';
        
        try
        {
            $estoque = new Estoque();
            $estoque->adicionar_entrada_item(0, $cod_bebidas_ipi_conteudos, $quantidade, $preco);
            
            $arr_retorno = array('resposta' => 'OK', 'mensagem' => '');    
        }
        catch (Exception $ex)
        {
            $arr_retorno = array('resposta' => 'ERRO', 'mensagem' => $ex);               
        }
        
        echo json_encode($arr_retorno);
        
        break;
    case 'alterar_ingrediente':
        $cod_ingredientes_marcas = validaVarPost('cod_ingredientes_marcas');
        $quantidade = validaVarPost('quantidade');
        $preco = moeda2bd(validaVarPost('preco'));
        
        require_once '../../classe/estoque.php';
        
        try
        {
            $estoque = new Estoque();
            $estoque->alterar_entrada_item($cod_ingredientes_marcas, 0, $quantidade, $preco);
            
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
        $cod_ingredientes_marcas = validaVarPost('cod_ingredientes_marcas');
        
        require_once '../../classe/estoque.php';
        
        try
        {
            $estoque = new Estoque();
            $estoque->excluir_entrada_item($cod_ingredientes_marcas, 0);
            
            $arr_retorno = array('resposta' => 'OK', 'mensagem' => '');    
        }
        catch (Exception $ex)
        {
            $arr_retorno = array('resposta' => 'ERRO', 'mensagem' => $ex);               
        }
        
        echo json_encode($arr_retorno);
        
        break;
    case 'excluir_bebida':
        $cod_bebidas_ipi_conteudos = validaVarPost('cod_bebidas_ipi_conteudos');
        
        require_once '../../classe/estoque.php';
        
        try
        {
            $estoque = new Estoque();
            $estoque->excluir_entrada_item(0, $cod_bebidas_ipi_conteudos);
            
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
        $arr_ingredientes = $estoque->listar_entrada_ingredientes_temporarios();
        
        ?>
        
        <br><center><? echo utf8_encode("<b>" . count($arr_ingredientes) . "</b> ingrediente(s) na lista de inclusão."); ?></center><br>
        
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" width="500">
            <tr>
                <td><input class="botaoAzul" type="button"
                    value="Excluir Todos" onclick="limpar_ingredientes();"></td>
            </tr>
        </table>
        
        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="600">
		<thead>
			<tr>
				<td align="center"><label>Ingrediente</label></td>
				<td align="center"><label><? echo utf8_encode('Marca / Quantidade / Unidade Padrão'); ?></label></td>
				<td align="center" width="80"><label>Quantidade</label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço Unit.'); ?></label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço Total'); ?></label></td>
				<td align="center" width="80"><label>Alterar</label></td>
				<td align="center" width="80"><label>Excluir</label></td>
			</tr>
		</thead>
		<tbody>
		
		<?

		$conexao = conectabd();
		
		$preco_total = 0;
		
		for($i = 0; $i < count($arr_ingredientes); $i++)
		{
		    $cod_ingredientes_marcas = $arr_ingredientes[$i]['cod_ingredientes_marcas'];
		    $quantidade = $arr_ingredientes[$i]['quantidade'];
		    $preco = $arr_ingredientes[$i]['preco'];
		    
		    $obj_buscar_ingrediente = executaBuscaSimples("SELECT * FROM ipi_ingredientes_marcas WHERE cod_ingredientes_marcas = '$cod_ingredientes_marcas' LIMIT 1", $conexao);
		    $cod_ingredientes = $obj_buscar_ingrediente->cod_ingredientes;
		    
		    $preco_total += $quantidade * $preco;
		    
		    $obj_buscar_ingredientes_marcas = executaBuscaSimples("SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_marcas im ON (i.cod_ingredientes = im.cod_ingredientes) WHERE im.cod_ingredientes = '$cod_ingredientes' AND im.cod_ingredientes_marcas = '$cod_ingredientes_marcas' LIMIT 1", $conexao);

		    echo '<tr>';
		    echo '<td>' . utf8_encode(bd2texto($obj_buscar_ingredientes_marcas->ingrediente)) . '</td>';
		    echo '<td>' . utf8_encode(bd2texto($obj_buscar_ingredientes_marcas->ingrediente_marca . ' (' . $obj_buscar_ingredientes_marcas->quantidade . ') ' . strtolower($obj_buscar_ingredientes_marcas->unidade_padrao))) . '</td>';
		    echo '<td align="center"><input type="text" name="quantidade_ingredientes_alterar[]" id="quantidade_ingredientes_alterar_' . $cod_ingredientes_marcas . '" value="' . $quantidade . '" size="8" maxsize="3" onkeypress="return ApenasNumero(event);"></td>';
		    echo '<td align="center"><input type="text" name="preco_ingredientes_alterar[]" id="preco_ingredientes_alterar_' . $cod_ingredientes_marcas . '" value="' . bd2moeda($preco) . '" size="8" maxsize="3" onkeypress="return formataMoeda(this, \'.\', \',\', event)"></td>';
		    echo '<td align="center">' . bd2moeda($quantidade * $preco) . '</td>';
		    echo '<td align="center"><input type="button" value="Alterar" class="botaoAzul" onclick="alterar_ingrediente(' . $cod_ingredientes_marcas . ')"></td>';
		    echo '<td align="center"><input type="button" value="Excluir" class="botaoAzul" onclick="if(confirm(\'Deseja EXCLUIR este ingrediente?\')) {  excluir_ingrediente(' . $cod_ingredientes . '); } "></td>';
		    echo '</tr>';
		}
		
		desconectabd($conexao);
		
		echo '<tr>';
	    echo '<td colspan="4" align="center"><b>Total</b></td>';
	    echo '<td align="center"><b>' . bd2moeda($preco_total) . '</b></td>';
	    echo '<td align="center">&nbsp;</td>';
	    echo '<td align="center">&nbsp;</td>';
	    echo '</tr>';
		
		?>
		
		</tbody>
		</table>
        
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
        
        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="500">
		<thead>
			<tr>
				<td align="center"><label>Bebida</label></td>
				<td align="center" width="80"><label>Quantidade</label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço Unit.'); ?></label></td>
				<td align="center" width="80"><label><? echo utf8_encode('Preço Total'); ?></label></td>
				<td align="center" width="80"><label>Alterar</label></td>
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
		    
		    $preco_total += $quantidade * $preco;
		    
		    $obj_buscar_bebidas = executaBuscaSimples("SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) WHERE cod_bebidas_ipi_conteudos = '$cod_bebidas_ipi_conteudos'", $conexao);

		    echo '<tr>';
		    echo '<td>' . utf8_encode(bd2texto($obj_buscar_bebidas->bebida . ' - ' . $obj_buscar_bebidas->conteudo)) . '</td>';
		    echo '<td align="center"><input type="text" name="quantidade_bebidas_alterar[]" id="quantidade_bebidas_alterar_' . $obj_buscar_bebidas->cod_bebidas_ipi_conteudos . '" value="' . $quantidade . '" size="8" maxsize="3" onkeypress="return ApenasNumero(event);"></td>';
		    echo '<td align="center"><input type="text" name="preco_bebidas_alterar[]" id="preco_bebidas_alterar_' . $obj_buscar_bebidas->cod_bebidas_ipi_conteudos . '" value="' . bd2moeda($preco) . '" size="8" maxsize="3" onkeypress="return formataMoeda(this, \'.\', \',\', event)"></td>';
		    echo '<td align="center">' . bd2moeda($quantidade * $preco) . '</td>';
		    echo '<td align="center"><input type="button" value="Alterar" class="botaoAzul" onclick="alterar_bebida(' . $cod_bebidas_ipi_conteudos . ')"></td>';
		    echo '<td align="center"><input type="button" value="Excluir" class="botaoAzul" onclick="if(confirm(\'Deseja EXCLUIR esta bebida?\')) {  excluir_bebida(' . $cod_bebidas_ipi_conteudos . '); } "></td>';
		    echo '</tr>';
		}
		
		desconectabd($conexao);
		
		echo '<tr>';
	    echo '<td colspan="3" align="center"><b>Total</b></td>';
	    echo '<td align="center"><b>' . bd2moeda($preco_total) . '</b></td>';
	    echo '<td align="center">&nbsp;</td>';
	    echo '<td align="center">&nbsp;</td>';
	    echo '</tr>';
		
		?>
		
		</tbody>
		</table>
        
        <?
        
        break;
}

?>