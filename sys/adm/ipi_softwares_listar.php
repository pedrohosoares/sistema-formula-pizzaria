<?php

/**
 * Downloads.
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       23/04/2012   Pedro H.      Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Downloads');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_softwares';
$tabela = 'ipi_softwares';
$campo_ordenacao = 'software';
$campo_filtro_padrao = 'software';
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
$quant_pagina = 50;
$exibir_barra_lateral = false;
?>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<!-- Tab Editar -->

<? if ($exibir_barra_lateral): ?>

<table>
    <tr>

        <!-- Conteúdo -->
        <td class="conteudo">
        

        <? endif; ?>
        <!--
        
        <?
        $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0;
        $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : $campo_filtro_padrao;
        $filtro = validaVarPost('filtro');
        ?>
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

            <tr>
                <td class="legenda tdbl tdbt" align="right">
                Software: 
                </td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text"
                    name="filtro" size="60" value="<?
                    echo $filtro?>"></td>
            </tr>

            <tr>
                <td align="right" class="tdbl tdbb tdbr" colspan="3">
                  <input class="botaoAzul" type="submit" value="Buscar">
                </td>
            </tr>

        </table>

        <input type="hidden" name="acao" value="buscar"></form>
        
        -->


        <?
        
        $conexao = conectabd();
        
        $sql_buscar_registros = "SELECT * FROM $tabela s WHERE s.cod_softwares IN (SELECT ips.cod_softwares FROM ipi_softwares ips INNER JOIN ipi_software_permissoes isp ON (isp.$chave_primaria = ips.$chave_primaria) WHERE ips.situacao = 'ATIVO' AND ((isp.todas_pizzarias = 1) OR (isp.cod_pizzarias IN ($cod_pizzarias_usuario))))";
        $res_buscar_registros = mysql_query($sql_buscar_registros);    
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);            
        ?>
        <style>
          .arquivo_content{
            border: 1px solid #C96800;
            margin-bottom: 30px;
            margin-top: 10px;
            margin-left: 20px;
            _height: 71px;
            min-height: 71px;
            padding: 5px;
            padding-top: 20px;
          }
          
          .arquivo_content .name{
            float: left;
            margin-top: -37px;
            margin-left: -15px;
            font-family:Arial;
            font-size: 18px;
            background-color: white;
            color: #EB8612;
            padding: 3px;
            font-weight: bold;
          }
          
          .arquivo_content .name span{
            font-family:Arial;
            font-size: 13px;
            padding: 3px;
          }
          
          .arquivo_content .download{
            margin-top: -8px;
            float: right;
          }
          
          .arquivo_content .compatibility{
            margin-top: -8px;
            float: left;
            font-family:Arial;
            font-size: 10px;
            margin-top: -10px;
            margin-left: 10px;
          }
          
          .arquivo_content .description{
            border: 1px solid #C96800;
            margin-bottom: 5px;
            margin-top: 23px;
            margin-left: 15px;
            _height: 25px;
            min-height: 25px;
            padding: 5px;
            padding-top: 11px;
            margin-right: 100px;
            font-family:Arial;
            font-size: 14px;
          }
          
          .arquivo_content .description .desc_name{
            float: left;
            margin-top: -26px;
            margin-left: -15px;
            font-family:Arial;
            font-size: 16px;
            background-color: white;
            color: #EB8612;
            padding: 3px;
            font-weight: bold;
          }
        </style>
        
        <?
          function tamanho_arquivo($arq)
          {
            $tamanho = filesize($arq);
            $show_tamanho = '';
            if($tamanho < 1024)
            {
              $show_tamanho = bd2moeda($tamanho).' Bytes';
            }
            else
            {
              if($tamanho < 1048576)
              {
                $show_tamanho = bd2moeda($tamanho/1024).' KB';
              }
              else
              {
                if($tamanho < 1073741824)
                {
                  $show_tamanho = bd2moeda($tamanho/1048576).' MB';
                }
                else
                {
                  $show_tamanho = bd2moeda($tamanho/1073741824).' GB';
                }
              }
            }
            return $show_tamanho;
          }
          
          if($num_buscar_registros > 0)
          {
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
              $arquivo = '../../../softwares/'.$obj_buscar_registros->arquivo;
              echo "<div id='arquivo_". bd2texto($obj_buscar_registros->cod_softwares) ."' class='arquivo_content'>";
              echo "  <div class='name'>". bd2texto($obj_buscar_registros->software) ."</div>";
              echo "    <div class='compatibility'> <b>Compatibilidade:</b> ". bd2texto($obj_buscar_registros->compatibilidade) ." </div>";
              echo "    <div class='' style='margin-left: 10px;'><br /><b>Arquivo</b><br /><a href='".bd2texto($obj_buscar_registros->arquivo)."' target='_blank'>". bd2texto($obj_buscar_registros->arquivo) ."</a> </div>";
              echo "    <div class='description'> <div class='desc_name'> Descrição </div>";
              echo bd2texto($obj_buscar_registros->descricao);
              echo "    </div>";
              echo "</div>";
            }
          }
          else
          {
            echo "<div style='font-family:Arial; color: #EB8612; font-size: 18px; text-align: center; border: 2px solid #C96800; padding: 10px; margin-left: 35%; margin-right: 35%;'> Nenhum registro encontrado. </div>";
          }
        ?>
        

        <!--<?
          while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
          {
              echo '<td align="center">'. bd2texto($obj_buscar_registros->software) . '</a></td>';
              echo '<td align="center"><a href="'.bd2texto($obj_buscar_registros->arquivo).'" href="_blank">'. bd2texto($obj_buscar_registros->arquivo) .'</a></td>';
              echo '<td align="center">'. bd2texto($obj_buscar_registros->descricao) .'</td>';
              echo '<td align="center">'. bd2texto($obj_buscar_registros->compatibilidade) .'</td>';
              echo '<td align="center"><a href="ipi_download.php?cod_softwares='.base64_encode($obj_buscar_registros->$chave_primaria).'" class="botao" />Download</a></td>';
          }
            desconectabd($conexao);
         ?>

        <style>
          table.listaEdicao tr:hover {
            background-color: #FFE8C9;
          }
          
          table.listaEdicao thead td {
            background-color: #FF8000; 
            font-weight: bold; 
            color: #fff;
          }
        </style>-->
        

<!-- Tab Editar -->
</td>
</tr>
</table>
</div>

<?
rodape();
?>
