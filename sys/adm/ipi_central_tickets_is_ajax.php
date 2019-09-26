<?php

/**
 * Cadastro e Exibição de Tickets.
 *
 * @version 1.0
 * @package iti
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       06/09/2012   Filipe         Criado.
 *
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../../classe/canal_comunicacao.php';


$acao = validaVarPost('acao');

$cod_situacoes_resolvido = 5;
$cod_situacoes_aguardando = 4;
$cod_situacoes_andamento = 3;

$chave_primaria = 'cod_tickets';
$tabela = 'ipi_comunicacao_tickets';

$codigo_usuario = $_SESSION['usuario']['codigo'];
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
switch ($acao)
{
  case 'carregar_tickets':
        $con = conectar_bd();
        $pagina = validaVarPost('pagina');
        $tipo = validaVarPost("tipo");
        if(!is_numeric($pagina))
          $pagina = 1;

       $sql_buscar_registros = "SELECT t.*,usu.nome,situ.nome_situacao,cca.nome_categoria,ccs.nome_subcategoria,(SELECT data_hora_comentario from ipi_comunicacao_tickets_comentarios where cod_tickets = t.cod_tickets order by data_hora_comentario DESC LIMIT 1) ultimo_coment,(select concat(pizza.cidade,'<br/>',pizza.bairro) from ipi_comunicacao_tickets_ipi_pizzarias pzt inner join ipi_pizzarias pizza on pizza.cod_pizzarias = pzt.cod_pizzarias where pzt.cod_tickets = t.cod_tickets limit 1) as nome_pizzarias FROM $tabela t inner join nuc_usuarios usu on usu.cod_usuarios = t.cod_usuarios inner join ipi_comunicacao_tickets_ipi_pizzarias ctp on ctp.cod_tickets = t.cod_tickets inner join ipi_comunicacao_subcategorias ccs on ccs.cod_ticket_subcategorias = t.cod_ticket_subcategorias inner join ipi_comunicacao_situacoes situ on situ.cod_situacoes = t.cod_situacoes inner join ipi_comunicacao_categorias cca on cca.cod_categorias = ccs.cod_categorias WHERE ctp.cod_pizzarias in(".$cod_pizzarias_usuario.",0) ";
       //AND (t.titulo_ticket LIKE '%$filtro%' or t.mensagem_ticket LIKE '%$filtro%' or t.cod_tickets LIKE '%$filtro%' or usu.nome LIKE '%$filtro%') 
        if($tipo!="tickets")
        {
          $sql_buscar_registros .=" and cca.cod_categorias = 6";
        }
        else
        {
          $sql_buscar_registros .=" and cca.cod_categorias not in (6)";
        }

      //  if(!$fechados)
            $sql_buscar_registros .=" and t.cod_situacoes not in(2)";

       // if($filt_categorias)
       //     $sql_buscar_registros .=" and ccs.cod_ticket_subcategorias ='".$filt_categorias."'";

       // if($autor)
        //    $sql_buscar_registros .=" and usu.nome like '%".$autor."%'";

        //if($situacao)
         //   $sql_buscar_registros .=" and t.cod_situacoes in(".$situacao.")";
            $qtd_por_pagina = 10;
        $sql_buscar_registros .="  GROUP BY t.cod_tickets";
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        //echo "</br>".$sql_buscar_registros."<br/><br/><br/><br/><br/><br/><br/>";
        $sql_buscar_registros .= ' ORDER BY t.data_hora_ticket DESC LIMIT ' . ($qtd_por_pagina * ($pagina-1)) . ', ' . $qtd_por_pagina;//ultimo_coment desc,
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        //echo "</br>".$sql_buscar_registros."<br/>";
        $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        //echo $sql_buscar_registros;

        //echo "<center><b>" . $num_buscar_registros . " registro(s) encontrado(s)</b></center>";
        echo utf8_encode("<center><b>Sugestões / Melhorias </b> ( ".$num_buscar_registros." registro(s) )</center>");
        
        echo '<center>';
        
        $num_pags = ceil($num_buscar_registros / $qtd_por_pagina);
        
         
        for($i = 1;$i<=$num_pags ; $i++)
        {
          if($i==$pagina)
          {
            echo "<b>".$i."</b>";
          }
          else
          {
            if($tipo=="tickets")
            {
              echo "<a href='javascript:void(0)' onclick='carregar_tickets(".$i.")'>".$i."</a>";
            }
            else
            {
              echo "<a href='javascript:void(0)' onclick='carregar_tickets(".$i.",\"tickets_sug\")'>".$i."</a>";
            }
          } 
             echo "&nbsp &nbsp";
        }
        echo '</center>';
        
        ?>

        <br>

        <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">

        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <tr>
                <td></td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <td align="center"><? echo utf8_encode("Título Ticket") ?></td>
                    <td align="center">Autor</td>
                    <td align="center"><? echo ucfirst(TIPO_EMPRESA)?></td>
                    <td align="center"><? echo utf8_encode("Último Comentário") ?></td>
                    <td align="center">Respostas</td>
                    <td align="center"><? echo utf8_encode("Situação / Data prevista") ?></td>
                    <td align="center">KPI 1</td>
                </tr>
            </thead>
            <tbody>
            <?
            $a = 0;
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                $cod_fundo_comentario = ($a%2 == 0) ? '#FFF' : '#EFEFEF' ;
                if($obj_buscar_registros->cod_situacoes==$cod_situacoes_andamento)
                {
                  $cod_fundo_comentario = '#A3C8D9';//azul claro
                }
                if($obj_buscar_registros->data_prevista <= date("Y-m-d")  && $obj_buscar_registros->data_prevista !="")
                {
                  $cod_fundo_comentario = '#F77C7E';//vermeçho
                }
                if($obj_buscar_registros->cod_situacoes==$cod_situacoes_resolvido)
                {
                  $cod_fundo_comentario = '#D5EDA4';//verde
                }
                if($obj_buscar_registros->cod_situacoes==$cod_situacoes_aguardando)
                {
                  $cod_fundo_comentario = '#82A1E8';//azul#4A7AE8'
                }

                echo '<tr>';
                
                $sql_buscar_comentarios_ticket = "select tc.cod_comentarios,tc.data_hora_comentario as data,usu.nome from ipi_comunicacao_tickets_comentarios tc inner join nuc_usuarios usu on usu.cod_usuarios = tc.cod_usuarios where tc.cod_tickets = ".$obj_buscar_registros->cod_tickets." order by data DESC";

                $res_buscar_comentarios_ticket = mysql_query($sql_buscar_comentarios_ticket);
                $quantidade = 0;
                $quantidade = mysql_num_rows($res_buscar_comentarios_ticket);
                $obj_buscar_comentarios_ticket = mysql_fetch_object($res_buscar_comentarios_ticket);
                //echo "<br/>".$sql_buscar_comentarios_ticket."</br>";
                //echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';                    <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
                //onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')
                echo utf8_encode('<td align="center" style="background-color:'.$cod_fundo_comentario.'" ><a href="?cc='.$obj_buscar_registros->cod_tickets.' ">' .sprintf("%04d",$obj_buscar_registros->cod_tickets)."-". bd2texto($obj_buscar_registros->titulo_ticket) . '</a><br/><span style="font-size:10px; ;">Categoria:'.$obj_buscar_registros->nome_categoria.' - '.$obj_buscar_registros->nome_subcategoria.'</span></td>');

                echo utf8_encode('<td align="center" style="background-color:'.$cod_fundo_comentario.'">'. bd2texto($obj_buscar_registros->nome).'<br/><b><small>em '. date("d/m/Y",strtotime($obj_buscar_registros->data_hora_ticket)).' as '. date("H:i",strtotime($obj_buscar_registros->data_hora_ticket)).'</small><b/></td>');
                echo utf8_encode('<td align="center" style="background-color:'.$cod_fundo_comentario.'">'. bd2texto(($obj_buscar_registros->nome_pizzarias 
                  ? $obj_buscar_registros->nome_pizzarias : 'Público')).'</td>');
                if($quantidade>0)
                {
                  echo utf8_encode("<td align='center' style='background-color:".$cod_fundo_comentario."'>" . $obj_buscar_comentarios_ticket->nome . "</br> <b><small>em ".bd2data(date('Y-m-d',strtotime($obj_buscar_comentarios_ticket->data)))." as ".date('H:i',strtotime($obj_buscar_comentarios_ticket->data))."</small></b></td>");
                }else
                {
                  echo utf8_encode('<td align="center" style="background-color:'.$cod_fundo_comentario.'">Nenhum</td>');
                }
 
                echo utf8_encode('<td align="center" style="background-color:'.$cod_fundo_comentario.'">'.$quantidade.'</td>');
                echo utf8_encode('<td align="center" style="background-color:'.$cod_fundo_comentario.'">'.$obj_buscar_registros->nome_situacao.''.($obj_buscar_registros->data_prevista ? '<br/><small>'.date('d/m/Y',strtotime($obj_buscar_registros->data_prevista)): '' ).'</td>');

                $canal_ticket = new CanalDeComunicacao_ticket();

                echo utf8_encode('<td align="center" style="background-color:'.$cod_fundo_comentario.'">'.$canal_ticket->calcular_kpi($obj_buscar_registros->cod_tickets,"1").'</td>');

                echo '</tr>';
                $a++;
            }
            
            ?>
            </tbody>
        </table>
<?
      desconectabd($con);
      break;
  case 'calcular_kpi1':
          
          $con = conectar_bd();
            $mes = validaVarPost("mes");
            $ano = validaVarPost("ano");

            if($mes=="undefined" || $mes=="") $mes = date("m");
            if($ano=="undefined" || $ano=="") $ano = date("Y");
            echo "<h2>KPI 1 ($mes/$ano)</h2> ";
            $canal_ticket_kpi = new CanalDeComunicacao_ticket();

            $sql_selecionar_ticketes = "SELECT cod_tickets from ipi_comunicacao_tickets where cod_ticket_subcategorias not in (1) AND cod_situacoes != 1 AND month(data_hora_ticket)='$mes' and year(data_hora_ticket) = '$ano' and cod_usuarios not in(1)";
            //echo "<br/>".$sql_selecionar_ticketes."<br/>";
            $res_selecionar_tickets = mysql_query($sql_selecionar_ticketes);
            $cont_sucesso = 0;
            $cont_fail = 0;
            $total = 0;
            $cods_fail = array();
            $z = 0;
            while($obj_selecionar_tickets = mysql_fetch_object($res_selecionar_tickets))
            {
              //if($obj_selecionar_tickets->cod_tickets==224)
              //  echo "<br/><br/><br/><br/><br/>";
              //echo "<br/>-----$obj_selecionar_tickets->cod_tickets-----";
              $tempo = $canal_ticket_kpi->calcular_kpi($obj_selecionar_tickets->cod_tickets,"1");
              if($tempo<=10.5)
              {
                $cont_sucesso++;
              }else
              {
                $cont_fail++;
                $z++;
                $cods_fail[] = $obj_selecionar_tickets->cod_tickets;
              }
              if($z>5)
              {
                $cods_fail[] = "<br/>";
                $z = 0 ;
              }
              $total++;
            }
            if($total==0)
            {
              echo "% dentro do prazo : 0%";
            }
            else
            {
              echo "% dentro do prazo : ".(($cont_sucesso/$total)*100)."%";
            }
            echo "<br/>Ticket sucesso : ".$cont_sucesso;
            echo "<br/>Ticket fail : ".$cont_fail;
            echo "<br/>Totais : ".$total;
            echo "</br><br/> Cods fail:".implode($cods_fail,', ');
            desconectar_bd($con);
      break;
  case 'calcular_kpi2':
      
      $con = conectar_bd();
      $mes = validaVarPost("mes");
      $ano = validaVarPost("ano");

      if($mes=="undefined" || $mes=="") $mes = date("m");
      if($ano=="undefined" || $ano=="") $ano = date("Y");
      echo "<h2>KPI 2 ($mes/$ano)</h2> ";
      $canal_ticket_kpi = new CanalDeComunicacao_ticket();

      $sql_selecionar_ticketes = "SELECT cod_tickets from ipi_comunicacao_tickets where cod_ticket_subcategorias not in (1) AND cod_situacoes != 1 AND month(data_hora_ticket)='$mes' and year(data_hora_ticket) = '$ano' AND data_prevista!=''";
      //echo "<br/>".$sql_selecionar_ticketes."<br/>";
      $res_selecionar_tickets = mysql_query($sql_selecionar_ticketes);
      $cont_sucesso = 0;
      $cont_fail = 0;
      $total = 0;
      $cods_fail = array();
      $z = 0;
      while($obj_selecionar_tickets = mysql_fetch_object($res_selecionar_tickets))
      {
        //if($obj_selecionar_tickets->cod_tickets==224)
        //  echo "<br/><br/><br/><br/><br/>";
        //echo "<br/>-----$obj_selecionar_tickets->cod_tickets-----";
        $tempo = $canal_ticket_kpi->calcular_kpi($obj_selecionar_tickets->cod_tickets,"2");
        if($tempo==1)
        {
          $cont_sucesso++;
        }else
        {
          $cont_fail++;
          $z++;
          $cods_fail[] = $obj_selecionar_tickets->cod_tickets;
        }
        if($z>5)
        {
          $cods_fail[] = "<br/>";
          $z = 0 ;
        }
        $total++;
      }
      if($total==0)
      {
        echo "% dentro do prazo : 0%";
      }
      else
      {
        echo "% dentro do prazo : ".(($cont_sucesso/$total)*100)."%";
      }
      echo "<br/>Ticket sucesso : ".$cont_sucesso;
      echo "<br/>Ticket fail : ".$cont_fail;
      echo "<br/>Totais : ".$total;
      echo "</br><br/> Cods fail:".implode($cods_fail,', ');
      desconectar_bd($con);
    break;
  case 'enviar_comentario':
   
  break;
  case 'fechar_ticket':
    
  break;
}
