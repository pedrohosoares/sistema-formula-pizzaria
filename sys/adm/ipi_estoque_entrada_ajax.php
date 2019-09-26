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
        
        $obj_cont_ingredientes = executaBuscaSimples("SELECT COUNT(*) AS quantidade FROM ipi_ingredientes i  WHERE ingrediente LIKE '%" . texto2bd($busca_ingrediente) . "%'  AND i.cod_ingredientes = i.cod_ingredientes_baixa AND ativo = 1", $conexao);
        
        $sql_buscar_ingredientes = "SELECT * FROM ipi_ingredientes i LEFT JOIN ipi_unidade_padrao up ON (i.cod_unidade_padrao = up.cod_unidade_padrao) WHERE ingrediente LIKE '%" . texto2bd($busca_ingrediente) . "%'  AND i.cod_ingredientes = i.cod_ingredientes_baixa AND ativo = 1 ORDER BY ingrediente_marca LIMIT 10";
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


            echo '<td align="center"><input type="text" name="quantidade_ingredientes_adicionar_embalagem[]" id="quantidade_ingredientes_adicionar_embalagem_' . $obj_buscar_ingredientes->cod_ingredientes . '" size="8" maxsize="3" value="' . str_replace(".",",",$obj_buscar_ingredientes->quantidade/$divisor_comum) . '"  onkeypress="formataMoeda3casas(this, 3)"></td>';
            
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
        require_once '../../classe/estoque.php';
        
        try
        {
            $estoque = new Estoque();
            $estoque->adicionar_entrada_item($cod_ingredientes, 0, $quantidade, $quantidade_embalagem, $preco,$preco_total,$unidade, $divisor_comum,'0',$liberacao);
            
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
            
            $obj_buscar_ingredientes_marcas = executaBuscaSimples("SELECT * FROM ipi_ingredientes i  INNER JOIN ipi_unidade_padrao up ON (up.cod_unidade_padrao = i.cod_unidade_padrao) WHERE i.cod_ingredientes = '$cod_ingredientes'  LIMIT 1", $conexao);
            echo '<tr>';
            echo '<td>' . utf8_encode(bd2texto($obj_buscar_ingredientes_marcas->ingrediente).' (em '.$unidade.')') . '</td>';
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
}

?>
