<?
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';

cabecalho('Painel de Administração');
?>

<script type="text/javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>

<script>
window.addEvent('domready', function()
{
    var resumo = new FusionCharts('../lib/swf/fusioncharts/sparkcolumn.swf', 'resumo', 250, 109, 0, 0, 'ffffff', 0);
    resumo.setDataURL('index_dados.php?param=1');
    resumo.render('resumo');
});
</script>

<script>
function passar_codigo_franqueados(codigo, form)
{   
    document.frm_franqueados.codigo.value = codigo;
    document.frm_franqueados.acao.value = 'exibir';
    document.frm_franqueados.submit();   
}
function passar_codigo_noticia(codigo, form)
{   
    document.frm_noticia.codigo.value = codigo;
    document.frm_noticia.acao.value = 'exibir';
    document.frm_noticia.submit();   
}
function passar_codigo_sistemas(codigo)
{   
    document.frm_sistemas.codigo.value = codigo;
    document.frm_sistemas.acao.value = 'exibir';
    document.frm_sistemas.submit();   
}
</script>

<table width="900" align="center" style="margin: 0px auto;" cellspacing="10">

    <tr>
        <td colspan="2" valign="top" width="66%">
            <!-- BOTÕES -->
            
            <table align="center" style="margin: 0px auto;" cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td valign="top" width="33%" align="center"><a href="ipi_rel_historico_pedidos.php" target="_blank"><img src="../lib/img/principal/botao_historico.jpg"></a></td>
                    <td valign="top" width="33%" align="center"><a href="ipi_caixa.php?executa=refresh" target="_blank"><img src="../lib/img/principal/botao_frente_caixa.jpg"></a></td>
                    <td valign="top" width="33%" align="center"><a href="ipi_despacho_pedidos.php" target="_blank"><img src="../lib/img/principal/botao_expedicao.jpg"></a></td>
                </tr>
            </table>
        </td>
        
        <td valign="top">
            <div class="blocoNavegacao" style="background: transparent url(../lib/img/principal/usuario.png) scroll no-repeat right bottom; width: 100%; margin: 0px;">
                <h4>Bem-vindo</h4>
                <p align="left">Olá <b><? echo strtoupper($_SESSION['usuario']['usuario']) ?></b>!</p>
                <p align="left"><? if($_SESSION['usuario']['ultimo_acesso'] != NULL) echo 'Seu último login foi em ' . $_SESSION['usuario']['ultimo_acesso'] . '.' ?></p>
                <p align="left"><a href="nuc_alterar_senha.php">Alterar Senha</a></p>
                <p align="left"><a href="nuc_logout.php">Sair</a></p>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" valign="top" width="66%">
            <!-- ACESSO RAPIDO -->
            
            <div class="blocoLateral" style="width: 100%; margin: 0px; height: 213px;">
                <h4>Acesso Rápido</h4>
                <br>
                <table width="100%">
                    <tr>
                        <td width="33%">
                            
                            <div class="blocoMenuGrupo" style="background-image: url(../lib/img/principal/acesso_rapido_gerenciamento.png); margin: 0px;">
                                <h2>Gerenciamento</h2>
                                <ul>
                                    <li><a href="ipi_estoque_entrada.php">Entrada</a></li>
                                    <li><a href="ipi_rel_titulos_fluxo_caixa.php">Fluxo de Caixa</a></li>
                                    <li><a href="ipi_titulos_pagar.php">Contas a Pagar</a></li>
                                    <li><a href="ipi_titulos_receber.php">Contas a Receber</a></li>
                                </ul>
                            </div>
                            
                        </td>
                        
                        <td width="33%">

                            <div class="blocoMenuGrupo" style="background-image: url(../lib/img/principal/acesso_rapido_operacoes.png); margin: 0px;">
                                <h2>Operações</h2>
                                <ul>
                                    <li><a href="<? if ( in_array("ipi_caixa_fechamento_v1_1.php", $_SESSION['usuario']['paginas'])){echo 'ipi_caixa_fechamento_v1_1.php'; }else{ echo 'ipi_caixa_fechamento.php';}?>">Fechamento de Caixa</a></li>
                                    <li><a href="<? if ( in_array("ipi_sol_baixa_individual_v2.php", $_SESSION['usuario']['paginas'])){echo 'ipi_sol_baixa_individual_v2.php'; }else{ echo 'ipi_sol_baixa_individual.php';}?>">Baixa de Pedidos</a></li>
                                    <li><a href="ipi_sol_entregas_avulsas.php">Entrega Avulsa</a></li>
                                    <li><a href="ipi_sol_impressao_relatorio.php">Impressão de Relatórios</a></li>
                                    <li><a href="ipi_sol_captura2.php">Confirmação de Cartão de Crédito</a></li>
                                </ul>
                            </div>
                            
                        </td>
                        
                        <td>
                            
                            <div class="blocoMenuGrupo" style="background-image: url(../lib/img/principal/acesso_rapido_relatorios.png); margin: 0px;">
                                <h2>Relatórios</h2>
                                <ul>
                                    <li><a href="ipi_rel_forma_pagamentos.php">Formas de Pagamento</a></li>
                                    <li><a href="ipi_rel_historico_pedidos.php">Histórico de Pedidos</a></li>
                                    <li><a href="ipi_rel_quant_vendidas.php">Quantidade Vendidas</a></li>
                                    <li><a href="ipi_rel_ger_volume_vendas.php">Volume de Vendas</a></li>
                                    <li><a href="ipi_rel_entregadores.php">Pedidos - Entregas</a></li>
                                </ul>
                            </div>
                            
                        </td>
                    </tr>
                </table>
                <br>
            </div>
            
        </td>
        <td valign="top" rowspan="4" >
            <div>
                <?
                $meses_ano = array(1 => "Janeiro", 2 => "Fevereiro", 3 => "Março", 4 => "Abril", 5 => "Maio", 6 => "Junho", 7 => "Julho", 8 => "Agosto", 9 => "Setembro", 10 => "Outubro", 11 => "Novembro", 12 => "Dezembro");
                
                $conexao = conectabd();
    
                $sql_buscar_registros = "SELECT * FROM ipi_colaboradores WHERE MONTH(data_nascimento) = MONTH(CURDATE()) AND situacao='ATIVO' AND cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY DAY(data_nascimento)";
                $res_buscar_registros = mysql_query($sql_buscar_registros);
                $num_buscar_registros = mysql_num_rows($res_buscar_registros);
                
                echo '<div class="blocoLateral" style="width: 100%; margin: 0px; height: 272px;">';
                
                $mes = (int)(date('m'));
                echo '<h4>Aniversariantes de '.$meses_ano[$mes].'</h4>';
                echo '<ul>';
                if ($num_buscar_registros>0)
                {
                    while ($obj_buscar_registros=mysql_fetch_object($res_buscar_registros))
                    {
                        $dia_aniversairo = explode('-',$obj_buscar_registros->data_nascimento);
                        $nome_aniversariante = explode(' ',bd2texto($obj_buscar_registros->nome));
                        if ( date('d') == $dia_aniversairo[2])
                        {
                            echo '<li><font color="#CC0000"><b>Dia '.$dia_aniversairo[2].' - '.$nome_aniversariante[0].' '.$nome_aniversariante[1].'</b></font></li>';
                        }
                        else 
                        {
                            echo '<li>Dia '.$dia_aniversairo[2].' - '.$nome_aniversariante[0].' '.$nome_aniversariante[1].'</li>';  
                        }
                    }
                }
                else 
                {
                    echo '<li>Nenhum Aniversariante</li>';  
                }
                echo '</ul>';
                echo '</div>';
                
                desconectabd($conexao);
                
                ?>
            </div>
        </td>
        
<!--        <td valign="top">
            <div class="blocoLateral" style="width: 100%; margin: 0px;">
                <h4>Resumo do Dia</h4>
                
                <table align="center" width="98%" style="margin: 3px;">
                    <tr>
                        <td align="left">Faturamento</td>
                        <td align="right"><b>0,00</b></td>
                    </tr>
                    <tr>
                        <td align="left">Ticket Total</td>
                        <td align="right"><b>0</b></td>
                    </tr>
                    <tr>
                        <td align="left">Ticket Médio</td>
                        <td align="right"><b>0</b></td>
                    </tr>
                </table>
                
                <div id="resumo" style="margin: 10px 0px;"></div>
            </div>
        </td> -->
    </tr>
    <tr>
        <td valign="top" width="33%">
            <?
            $data_atual = date('Y-m-d');
            
            //echo $data_atual;
            
            $conexao = conectabd();

            $sql_buscar_registros = "SELECT * FROM iti_textos t INNER JOIN iti_secoes s ON(t.cod_secoes=s.cod_secoes) WHERE t.situacao='ATIVO' AND data_inicio_exibicao<='".$data_atual."' AND t.cod_secoes=4 ORDER BY data_texto DESC LIMIT 4";
            $res_buscar_registros = mysql_query($sql_buscar_registros);
            
            echo '<div class="blocoLateral" style="width: 100%; margin: 0px;">';
            echo '<h4>Mensagem aos Franqueados</h4>';
            echo '<ul>';
            while ($obj_buscar_registros=mysql_fetch_object($res_buscar_registros))
            {
                echo '<li><a href="javascript: passar_codigo_franqueados('.$obj_buscar_registros->cod_textos.');">'.$obj_buscar_registros->titulo.'</a></li>';
            }
            echo '<li><b><a href="javascript: document.frm_franqueados.submit();">Veja Todas</a></b></li>'; 
            echo '</ul>';
            echo '<form name="frm_franqueados" method="post" action="iti_listar_textos.php">';
            echo '<input type="hidden" name="cod_secoes" value="4">';
            echo '<input type="hidden" name="codigo" value="">';
            echo '<input type="hidden" name="acao" value="">';
            echo '<input type="hidden" name="cabecalho" value="Mensagem aos Franqueados">';
            echo '</form>';
            echo '</div>';
/*

            $sql_buscar_enquetes_perguntas = "SELECT cod_enquete_perguntas, pergunta FROM ipi_enquete_perguntas WHERE pergunta_pessoal = 0";
            $res_buscar_enquetes_perguntas = mysql_query($sql_buscar_enquetes_perguntas);
            while($obj_buscar_enquetes_perguntas = mysql_fetch_object($res_buscar_enquetes_perguntas))
            {
                //echo $obj_buscar_enquetes_perguntas->cod_enquete_perguntas;

                $sql_buscar_enquetes = "SELECT COUNT(icier.cod_clientes_ipi_enquete_respostas) as qtd FROM ipi_enquete_perguntas iep INNER JOIN ipi_enquete_respostas ier ON (ier.cod_enquete_perguntas = iep.cod_enquete_perguntas) INNER JOIN ipi_clientes_ipi_enquete_respostas icier ON (icier.cod_enquete_respostas = ier.cod_enquete_respostas) INNER JOIN ipi_pedidos ip ON (ip.cod_pedidos = icier.cod_pedidos) WHERE ip.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND respondida_pizzaria = 0 AND respondida_pizzaria_tel = 0 AND conceito_baixo = 1 AND iep.cod_enquete_perguntas = '".$obj_buscar_enquetes_perguntas->cod_enquete_perguntas."'";

                $data_inicial = date('Y-m-d', strtotime('-15 day'));
                $data_final = date('Y-m-d');

                if (($data_inicial) && ($data_final))
                {
                    $data_inicial_sql = ($data_inicial) . ' 00:00:00';
                    $data_final_sql = ($data_final) . ' 23:59:59';
                    
                    $sql_buscar_enquetes .= " AND icier.data_hora_resposta >= '$data_inicial_sql' AND icier.data_hora_resposta <= '$data_final_sql'";
                }
                //echo $sql_buscar_enquetes;
                $res_buscar_enquetes = mysql_query($sql_buscar_enquetes);
                $obj_buscar_enquetes = mysql_fetch_object($res_buscar_enquetes);
                $arr_enquete = explode(' ', $obj_buscar_enquetes_perguntas->pergunta);

                if(in_array('produtos', $arr_enquete))
                    echo "Há <a href='ipi_enquete_resultado.php?acao=buscar&cod=0' target='_blank'> ".$obj_buscar_enquetes->qtd." enquete(s) sobre os produtos</a> marcadas com <strong>baixo conceito</strong> para ser(em) respondida(s). <br/>";
                elseif(in_array('entrega', $arr_enquete))
                    echo "Há <a href='ipi_enquete_resultado.php?acao=buscar&cod=1' target='_blank'> ".$obj_buscar_enquetes->qtd." enquete(s) sobre a entrega</a> marcadas com <strong>baixo conceito</strong> para ser(em) respondida(s).<br/>";
                elseif(in_array('sistema', $arr_enquete))
                    echo "Há <a href='ipi_enquete_resultado.php?acao=buscar&cod=2' target='_blank'> ".$obj_buscar_enquetes->qtd." enquete(s) sobre o sistema</a> marcadas com <strong>baixo conceito</strong> para ser(em) respondida(s).<br/>";


            }

            $sql_buscar_enquetes = "SELECT COUNT(*) as qtd FROM ipi_fale_conosco WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']).''.(in_array(1, $_SESSION['usuario']['cod_pizzarias']) ? ", 'Franqueadora'" : "") .") AND respondida = 0";
            //echo $sql_buscar_enquetes;
            $res_buscar_enquetes = mysql_query($sql_buscar_enquetes);
            $obj_buscar_enquetes = mysql_fetch_object($res_buscar_enquetes);
            echo "Há <a href='ipi_fale_conosco.php?resp=nao' target='_blank'>".$obj_buscar_enquetes->qtd." 'Fale Conosco'</a> para ser(em) respondido(s)";

*/
            
            desconectabd($conexao);
            
            ?>
        </td>
        
        <td valign="top" width="33%">
            <?
            $conexao = conectabd();

            $sql_buscar_registros = "SELECT * FROM iti_textos t INNER JOIN iti_secoes s ON(t.cod_secoes=s.cod_secoes) WHERE t.situacao='ATIVO' AND data_inicio_exibicao<='".$data_atual."' AND t.cod_secoes=5 ORDER BY data_texto DESC LIMIT 4";
            $res_buscar_registros = mysql_query($sql_buscar_registros);
            
            echo '<div class="blocoLateral" style="width: 100%; margin: 0px;">';
            echo '<h4>Notícias</h4>';
            echo '<ul>';
            while ($obj_buscar_registros=mysql_fetch_object($res_buscar_registros))
            {
                echo '<li><a href="javascript: passar_codigo_sistemas('.$obj_buscar_registros->cod_textos.');">'.bd2texto($obj_buscar_registros->titulo).'</a></li>';
            }
            echo '<li><b><a href="javascript: document.frm_noticias.submit();">Veja Todas</a></b></li>';    
            echo '</ul>';
            echo '<form name="frm_noticias" method="post" action="iti_listar_textos.php">';
            echo '<input type="hidden" name="cod_secoes" value="5">';
            echo '<input type="hidden" name="acao" value="">';
            echo '<input type="hidden" name="cabecalho" value="Notícias">';
            echo '<input type="hidden" name="codigo" value="">';
            echo '</form>';
            echo '</div>';
            
            desconectabd($conexao);
            
            ?>
        </td>
        <!-- 
        <td valign="top" width="33%">
            <?
            $conexao = conectabd();

            $sql_buscar_registros = "SELECT * FROM iti_textos t INNER JOIN iti_secoes s ON(t.cod_secoes=s.cod_secoes) WHERE t.situacao='ATIVO' AND data_inicio_exibicao<='".$data_atual."' AND t.cod_secoes=6 ORDER BY data_texto DESC LIMIT 4";
            $res_buscar_registros = mysql_query($sql_buscar_registros);
            
            echo '<div class="blocoLateral" style="width: 100%; margin: 0px;">';
            echo '<h4>Atualizações do Sistema</h4>';
            echo '<ul>';
            while ($obj_buscar_registros=mysql_fetch_object($res_buscar_registros))
            {
                echo '<li><a href="javascript: passar_codigo_sistemas('.$obj_buscar_registros->cod_textos.');">'.bd2texto($obj_buscar_registros->titulo).'</a></li>';
            }
            echo '<li><b><a href="javascript: document.frm_sistemas.submit();">Veja Todas</a></b></li>';
            echo '</ul>';
            echo '<form name="frm_sistemas" method="post" action="iti_listar_textos.php">';
            echo '<input type="hidden" name="cod_secoes" value="6">';
            echo '<input type="hidden" name="codigo" value="">';
            echo '<input type="hidden" name="acao" value="">';
            echo '<input type="hidden" name="cabecalho" value="Notícias de Atualização do Sistema">';
            echo '</form>';
            echo '</div>';
            
            desconectabd($conexao);
            
            ?>
        </td>
         -->

    </tr>

</table>

<? rodape(); ?>
