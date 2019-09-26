<?php

/**
 * ipi_central_comunicacao_ajax.php: Central de comunicação dos muzza AJAX AJAQUEZ AJAQUIX
 * 
 * Índice: 
 * Tabela: 
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');
$cod_usuario = $_SESSION['usuario']['codigo'];
$cod_situacoes_resolvido = 5;
$cod_situacoes_aguardando = 4;
$cod_situacoes_andamento = 3;
//$tabela = 'ipi_combos_produtos';
//$chave_primaria = 'cod_combos';
//$quant_pagina = 80;
//echo "saijdisaojdioasalskdjalkçsdjaçslkdjaslkçdjaskldjaksjdksaldjsalkdjaslkçdjsajdkl";
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
switch($acao) 
{
  case 'carregar_tickets':
    $con = conectar_bd();
    $pagina = validaVarPost('pagina');
    $tipo = "tickets";
    if(!is_numeric($pagina))
      $pagina = 1;


      ?>

      <table width="100%" align="center" class='listaEdicao'>
      <thead>
        <tr>
          <td align='center' colspan='5'>
            <? if($tipo=="tickets")
            {
              $sql_cont_novidades = "select ct.cod_tickets from ipi_comunicacao_tickets ct inner join ipi_comunicacao_tickets_ipi_pizzarias ctp on ctp.cod_tickets = ct.cod_tickets inner join ipi_comunicacao_situacoes situ on situ.cod_situacoes = ct.cod_situacoes inner join ipi_comunicacao_subcategorias cc on cc.cod_ticket_subcategorias = ct.cod_ticket_subcategorias where ct.cod_situacoes not in (2) and ctp.cod_pizzarias in(".$cod_pizzarias_usuario.",0) AND cc.cod_categorias not in (6) GROUP BY ct.cod_tickets";
              //echo "<br/>".$sql_cont_novidades."</br>";
              $res_cont_novidades = mysql_query($sql_cont_novidades);
              $num_cont_novidades = mysql_num_rows($res_cont_novidades);
              $qtd_por_pagina = 10;
              $num_pags = ceil($num_cont_novidades / $qtd_por_pagina);
              echo "<h1>Tickets</h1>";

            }
            else
            {
              $sql_cont_novidades = "select ct.cod_tickets from ipi_comunicacao_tickets ct inner join ipi_comunicacao_tickets_ipi_pizzarias ctp on ctp.cod_tickets = ct.cod_tickets inner join ipi_comunicacao_situacoes situ on situ.cod_situacoes = ct.cod_situacoes inner join ipi_comunicacao_subcategorias cc on cc.cod_ticket_subcategorias = ct.cod_ticket_subcategorias where ct.cod_situacoes not in (2) and ctp.cod_pizzarias in(".$cod_pizzarias_usuario.",0) AND cc.cod_categorias = 6 GROUP BY ct.cod_tickets";
              //echo "<br/>".$sql_cont_novidades."</br>";
              $res_cont_novidades = mysql_query($sql_cont_novidades);
              $num_cont_novidades = mysql_num_rows($res_cont_novidades);
              $qtd_por_pagina = 3;
              $num_pags = ceil($num_cont_novidades / $qtd_por_pagina);
              echo ("<h1>Melhorias/Sugestões</h1>");
            }

            ?>
            <input type="button" class="botao float_right" onclick="novo_ticket()" value="+ Novo Ticket" />
          </td>
        </tr>
      </thead>
      <tbody>
        <!--<tr class="sem_hover"><td colspan = '5' align="center" style="font-weight:bold;font-size:18px">
          Tickets Gerais </td></tr>-->
        <tr class='sem_hover'><td colspan = '5' align="center">
        <?
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

        echo "</td></tr>";//<td>Data Hora Criação</td>
        echo "<tr style='background-color: #E5E5E5;'>
        <td width='30%'><strong>Nome do Ticket</strong></td>
        <td width='18%'><strong>Autor</strong></td>
        <td width='18%' ><strong>Último Comentário</strong></td>
        <td width='10%'><strong>Situação / Data Prevista</strong></td>
        <td width='4%'><strong>Respostas</strong></td></tr>";

          if($pagina<1)
            $pagina = 1;
          $pagina = ($pagina-1) * $qtd_por_pagina;


          $sql_buscar_tickets = "SELECT t.*,usu.nome,ccp.nome_categoria,cc.nome_subcategoria,situ.nome_situacao,(SELECT data_hora_comentario from ipi_comunicacao_tickets_comentarios where cod_tickets = t.cod_tickets order by data_hora_comentario DESC LIMIT 1) ultimo_coment FROM ipi_comunicacao_tickets t inner join nuc_usuarios usu on usu.cod_usuarios = t.cod_usuarios inner join ipi_comunicacao_tickets_ipi_pizzarias ctp on ctp.cod_tickets = t.cod_tickets inner join ipi_comunicacao_subcategorias cc on cc.cod_ticket_subcategorias = t.cod_ticket_subcategorias inner join ipi_comunicacao_situacoes situ on situ.cod_situacoes = t.cod_situacoes inner join ipi_comunicacao_categorias ccp on ccp.cod_categorias = cc.cod_categorias where ctp.cod_pizzarias in(".$cod_pizzarias_usuario.",0) and t.cod_situacoes not in(2)";//ultimo_coment desc,

          if($tipo=="tickets")
          {
            $sql_buscar_tickets .= " AND cc.cod_categorias not in(6) ";
          }
          else
          {
            $sql_buscar_tickets .= " AND cc.cod_categorias = 6 ";
          }

          $sql_buscar_tickets .= "GROUP BY t.cod_tickets ORDER BY t.data_hora_ticket DESC LIMIT ".$pagina.",".$qtd_por_pagina;
          //echo $sql_buscar_tickets;
          $res_buscar_tickets = mysql_query($sql_buscar_tickets);
          //$obj_buscar_tickets = mysql_fetch_object($res_buscar_tickets);
          while($obj_buscar_tickets = mysql_fetch_object($res_buscar_tickets))
          //for($a=0;$a<=8;$a++)
          {
             $cor_fundo = '#FFFFFF';
            /*if($obj_buscar_tickets->cod_situacoes==$cod_situacoes_andamento)
            {
              $cor_fundo = '#A3C8D9';//azul claro
            }
            if($obj_buscar_tickets->data_prevista <= date("Y-m-d")  && $obj_buscar_tickets->data_prevista !="")
            {
              $cor_fundo = '#F77C7E';//vermeçho
            }*/
            if($obj_buscar_tickets->cod_situacoes==$cod_situacoes_resolvido)
            {
              $cor_fundo = '#D5EDA4';//verde
            }
            /*if($obj_buscar_tickets->cod_situacoes==$cod_situacoes_aguardando)
            {
              $cor_fundo = '#82A1E8';//azul#4A7AE8'
            }*/

            $sql_buscar_comentarios_ticket = "select tc.cod_comentarios,tc.data_hora_comentario as data,usu.nome from ipi_comunicacao_tickets_comentarios tc inner join nuc_usuarios usu on usu.cod_usuarios = tc.cod_usuarios where tc.cod_tickets = ".$obj_buscar_tickets->cod_tickets." order by data DESC";

            $res_buscar_comentarios_ticket = mysql_query($sql_buscar_comentarios_ticket);
            $quantidade = 0;
            $quantidade = mysql_num_rows($res_buscar_comentarios_ticket);
            $obj_buscar_comentarios_ticket = mysql_fetch_object($res_buscar_comentarios_ticket);

            echo "<tr>";
            //echo "<td>".$obj_buscar_tickets->cod_tickets."</td>" onclick='ler_ticket(".$obj_buscar_tickets->cod_tickets.")';
            echo utf8_encode("<td style='background-color:$cor_fundo'><a href='ipi_central_tickets.php?cc=".$obj_buscar_tickets->cod_tickets."'>".sprintf("%04d",$obj_buscar_tickets->cod_tickets)."-".$obj_buscar_tickets->titulo_ticket."</a><br /><span style='font-size:10px; ;'>Categoria: ".($tipo=="tickets" ? $obj_buscar_tickets->nome_categoria." - " : "")." ".$obj_buscar_tickets->nome_subcategoria."</span></td>");
            echo utf8_encode("<td style='height: 50px; color:grey;background-color:$cor_fundo'>".$obj_buscar_tickets->nome."<br/><b><small>em ". date("d/m/Y",strtotime($obj_buscar_tickets->data_hora_ticket)).' as '. date("H:i",strtotime($obj_buscar_tickets->data_hora_ticket))."</small><b/></td>");
            //echo "<td>".bd2data($obj_buscar_tickets->data_hora_ticket)."/<td>";
            if($quantidade>0)
            {
              echo utf8_encode("<td style=' color:grey;background-color:$cor_fundo'>".$obj_buscar_comentarios_ticket->nome."<br/><b><small> em ".bd2data(date('Y-m-d',strtotime($obj_buscar_comentarios_ticket->data)))." as ".date('H:i',strtotime($obj_buscar_comentarios_ticket->data))."</small></b></td>");
            }
            else
            {
              echo '<td align="center" style=" color:grey;background-color:'.$cor_fundo.'">Nenhuma</td>';
            }
            echo utf8_encode("<td style=' color:grey;background-color:$cor_fundo'>".$obj_buscar_tickets->nome_situacao."".($obj_buscar_tickets->data_prevista ? '<br/><small>'.date('d/m/Y',strtotime($obj_buscar_tickets->data_prevista)).'</small>' : '')."</td>");

            echo "<td style=' color:grey;background-color:$cor_fundo'>".$quantidade."</td>";
            echo "</tr>";
          }
      echo "</tbody>";
      echo "</table>";
    desconectar_bd($con);
  break;
  case 'carregar_novidades':
    $con = conectar_bd();
    $pagina = validaVarPost('pagina');
    if(!is_numeric($pagina))
      $pagina = 1;

      $sql_cont_novidades = "select cod_novidades from ipi_comunicacao_novidades where status = 'PUBLICADO'";
      $res_cont_novidades = mysql_query($sql_cont_novidades);
      $num_cont_novidades = mysql_num_rows($res_cont_novidades);
      $qtd_por_pagina = 5;
      $num_pags = ceil($num_cont_novidades / $qtd_por_pagina);

      echo '<style type="text/css">
        table.listaEdicao td a
        {
          font-weight: normal!important;
        }
        table.listaEdicao td a.destaque
        {
          font-weight: bold!important;
        }
        table.listaEdicao tr.destaque
        {
          background-color: #EBEBEB;
        }
      </style>';
      ?>
      <table width="100%" align="center" class='listaEdicao'>
      <thead>
        <tr>
          <td align='center' colspan='4'><h1>Novidades / Comunicados</h1></td>
        </tr>
      </thead>
      <tr class='sem_hover'><td colspan = '4' align="center">
      <?
      for($i = 1;$i<=$num_pags ; $i++)
      {
        if($i==$pagina)
        {
          echo "<b>".$i."</b>";
        }
        else
        {
          echo "<a href='javascript:void(0)' onclick='carregar_novidades(".$i.")'>".$i."</a>";
        } 
           echo "&nbsp &nbsp";
      }

      echo "</td></tr>";
      if($pagina<1)
        $pagina = 1;
        $pagina = ($pagina-1) * $qtd_por_pagina;

        $sql_buscar_novidades = "select cn.*,usu.nome,(SELECT CASE WHEN count(*)>0 THEN 1 ELSE 0 END from ipi_comunicacao_novidades_ipi_usuarios where cod_usuarios = $cod_usuario and cod_novidades = cn.cod_novidades) lido from ipi_comunicacao_novidades cn inner join nuc_usuarios usu on usu.cod_usuarios = cn.cod_usuarios where cn.status = 'PUBLICADO' order by lido,cn.data_novidade desc LIMIT ".$pagina.",".$qtd_por_pagina;
        //echo "<br/>".$sql_buscar_novidades."</br>";

        echo '<tr style="background-color: #E5E5E5;">';
        echo '<td width="10%"><strong>Status</strong></td>';
        echo '<td width="90%"><strong>Título</strong></td>';
        //echo '<td width="30%"><strong>Autor</strong></td></tr>';
        $res_buscar_novidades = mysql_query($sql_buscar_novidades);
        while($obj_buscar_novidades = mysql_fetch_object($res_buscar_novidades))
        {
          $lido = false;
          
          if($obj_buscar_novidades->lido>0)
          {
            $lido = true;
          }
           echo "<tr ".(!$lido ? "class='destaque'" : '')."><td align='center' >";

          if(!$lido)
          {
            if($obj_buscar_novidades->destaque)
            {
              echo "<img src='../lib/img/principal/icon_important.png' alt='Nova' />";
            }else
            echo "<img src='../lib/img/principal/icon_news.png' alt='Nova' />";
          }
          else
            echo "<img src='../lib/img/principal/icon_no_news.png' alt='Lido' />";

          echo "</td><td align='center' style='height: 35px;''>";
          echo utf8_encode("<a href='javascript:void(0)' onclick='abrir_novidade(".$obj_buscar_novidades->cod_novidades.",\"".$obj_buscar_novidades->titulo_novidade."\")' ".(!$lido ? "class='destaque'" : '')." >".$obj_buscar_novidades->titulo_novidade." </a>
            <br/><small>por ".$obj_buscar_novidades->nome." em ".bd2data($obj_buscar_novidades->data_novidade)."</small></td>");

          //echo "<td align='center' style='font-size:12px; color:grey;'>".(!$lido ? "<strong>" : '').$obj_buscar_novidades->nome."<br /><span style='font-size: 10px; color:grey;'>".(!$lido ? "<strong>" : '')."em ".bd2data($obj_buscar_novidades->data_novidade).(!$lido ? "<strong>" : '')."</span>".(!$lido ? "</strong>" : '')."</td>";
          echo "</tr>";
        }

      echo "</table>";
    desconectar_bd($con);
  break;
    case 'carregar_sistema':
    $con = conectar_bd();

      ?>
      <table width="100%" align="center" class='listaEdicao'>      
      <thead>
        <tr>
          <td align='center' colspan='4'><h1>Sistema</h1></td>
        </tr>
      </thead>
      <tr>
      <?
      echo "</td></tr>";
      /*
        $sql_buscar_cronogramas = "select cc.*,usu.nome from ipi_comunicacao_cronogramas cc inner join nuc_usuarios usu on usu.cod_usuarios = cc.cod_usuarios where cc.status = 'PUBLICADO' and data_prevista>now() order by cc.data_prevista desc LIMIT ".$pagina.",10";
        $res_buscar_cronogramas = mysql_query($sql_buscar_cronogramas);
        while($obj_buscar_cronogramas = mysql_fetch_object($res_buscar_cronogramas))
        {
           echo "<tr>";

          echo "<td align='center' style='height: 35px;''>";
          echo utf8_encode("<b><a href='javascript:void(0)' onclick='abrir_cronograma(".$obj_buscar_cronogramas->cod_cronogramas.",\"".$obj_buscar_cronogramas->titulo_cronograma."\")' >".$obj_buscar_cronogramas->titulo_cronograma." </a></b></td>");
          echo utf8_encode("<td align='center' style='font-size:10px;color:grey'>por ".$obj_buscar_cronogramas->nome."</td>");
          echo "<td align='center' style='font-size:10px;color:grey'>previsto para <b>".bd2data($obj_buscar_cronogramas->data_prevista)."</b></td>";
          echo "</tr>";
        }*/

      echo '<tr style="background-color: #E5E5E5;">';
      echo '<td width="30%"><strong>Site</strong></td>';
      echo '<td width="30%"><strong>Sistema</strong></td>';
      echo '<td width="30%"><strong>Envio de e-mail</strong></td></tr>';

      echo "<tr><td width='30%' style='color:green; font-weight:bold;'>ONLINE</td>";
      echo "<td width='30%' style='color:green; font-weight:bold;'>ONLINE</td>";
      echo "<td width='30%' style='color:green; font-weight:bold;'>ONLINE</td></tr>";
      echo "</table>";
    desconectar_bd($con);
  break;
  case 'carregar_cronogramas':
    $con = conectar_bd();
    $pagina = validaVarPost('pagina');
    if(!is_numeric($pagina))
      $pagina = 1;

      $sql_cont_cronograma = "select cod_cronogramas from ipi_comunicacao_cronogramas where status = 'PUBLICADO' and data_prevista>now() order by data_prevista desc";
      $res_cont_cronograma = mysql_query($sql_cont_cronograma);
      $num_cont_cronograma = mysql_num_rows($res_cont_cronograma);
      $qtd_por_pagina = 2;
      $num_pags = ceil($num_cont_cronograma / $qtd_por_pagina);

      ?>
      <table width="100%" align="center" class='listaEdicao'>
      <thead>
        <tr>
          <td align='center' colspan='4'><h1>Cronograma</h1></td>
        </tr>
      </thead>
      <tr class='sem_hover'><td colspan = '3' align="center">
      <?
      for($i = 1;$i<=$num_pags ; $i++)
      {
        if($i==$pagina)
        {
          echo "<b>".$i."</b>";
        }
        else
        {
          echo "<a href='javascript:void(0)' onclick='carregar_cronogramas(".$i.")'>".$i."</a>";
        } 
           echo "&nbsp &nbsp";
      }

      echo "</td></tr>";
      if($pagina<1)
        $pagina = 1;
        $pagina = ($pagina-1) * $qtd_por_pagina;

        $sql_buscar_cronogramas = "select cc.*,usu.nome from ipi_comunicacao_cronogramas cc inner join nuc_usuarios usu on usu.cod_usuarios = cc.cod_usuarios where cc.status = 'PUBLICADO' and data_prevista>now() order by cc.data_prevista desc LIMIT ".$pagina.",".$qtd_por_pagina;
        $res_buscar_cronogramas = mysql_query($sql_buscar_cronogramas);
      echo '<tr style="background-color: #E5E5E5;">';
      echo '<td width="70%"><strong>Título</strong></td>';
      echo '<td width="30%"><strong>Data prevista</strong></td></tr>';
        while($obj_buscar_cronogramas = mysql_fetch_object($res_buscar_cronogramas))
        {
           echo "<tr>";

          echo "<td align='center' style='height: 35px;''>";
          echo utf8_encode("<b><a href='javascript:void(0)' onclick='abrir_cronograma(".$obj_buscar_cronogramas->cod_cronogramas.",\"".$obj_buscar_cronogramas->titulo_cronograma."\")' >".$obj_buscar_cronogramas->titulo_cronograma." </a></b></td>");

          echo "<td align='center' style='font-size:12px; color:grey;'>".bd2data($obj_buscar_cronogramas->data_prevista)."</td>";
          echo "</tr>";
        }

      echo "</table>";
    desconectar_bd($con);
  break;
  case 'exibir_novidade_detalhada':
    $con = conectar_bd();
    $cod_novidade = validaVarPost("cod_novidades");

    $sql_buscar_novidades = "select n.* from ipi_comunicacao_novidades n where n.cod_novidades = '$cod_novidade'";
    $res_buscar_novidades = mysql_query($sql_buscar_novidades);
    $num_buscar_novidades = mysql_num_rows($res_buscar_novidades);
    if($num_buscar_novidades >0)
    {
      $obj_buscar_novidades = mysql_fetch_object($res_buscar_novidades);
      echo "<div style='width:600px;max-height:500px;_height:500px;overflow:auto;'>";
				echo "<div name='div_conteudo'>";
					echo utf8_encode($obj_buscar_novidades->novidade);

          $sql_buscar_uploads = "SELECT * from ipi_comunicacao_novidades_arquivos where cod_novidades = '$cod_novidade'";
          $res_buscar_uploads = mysql_query($sql_buscar_uploads);
          $num_uploads = mysql_num_rows($res_buscar_uploads);
          if($num_uploads>0)
          {
            echo "<br/>";
            echo '<div id="cont_anexos" style="width:100%">';
                echo "<h3>Anexos</h3>";
                echo "<br/>";
                echo "<table class='listaEdicao'>";
                echo "<thead><tr><td style='background-color:#EB8612';
    border: 1px solid #EB8612;'>Nome do Arquivo</td></tr></thead><tbody>";
                while($obj_buscar_uploads = mysql_fetch_object($res_buscar_uploads))
                {
                  echo "<tr>";
                  echo "<td ><a href='".UPLOAD_DIR."/comunicacao/novidades/".$obj_buscar_uploads->nome_arquivo."' target='_blank'>".$obj_buscar_uploads->nome_arquivo."</a></td>";
                }
                echo "</tbody></table></div><br/><br/>";
          }

          if($_SESSION['usuario']['perfil']==1 || $_SESSION['usuario']['perfil']==2 || $_SESSION['usuario']['perfil']==4)
          {
            $sql_buscar_leitores = "SELECT usu.nome,nusu.data_hora_leitura from nuc_usuarios usu inner join ipi_comunicacao_novidades_ipi_usuarios nusu on usu.cod_usuarios = nusu.cod_usuarios where cod_novidades = '$cod_novidades' GROUP by usu.nome ORDER BY nusu.data_hora_leitura ASC";
            $res_buscar_leitores = mysql_query($sql_buscar_leitores);
            $nomes = false;
            while ($obj_buscar_leitores = mysql_fetch_object($res_buscar_leitores))
            {
              $nomes .= (!$nomes? '' : '<br/>').$obj_buscar_leitores->nome." em ".date('d/m/Y \á\s H:i',strtotime($obj_buscar_leitores->data_hora_leitura));
            }

            if($nomes)
              echo "<br/><br/><small>Lido por: ".utf8_encode($nomes)."</small>";
          }
				echo "</div>";
			echo "</div>";

      $sql_atualiza = "insert into ipi_comunicacao_novidades_ipi_usuarios(cod_usuarios,cod_novidades,data_hora_leitura) values (".$cod_usuario.",".$cod_novidade.",NOW())";
      $res_atualiza = mysql_query($sql_atualiza);
      if($res_atualiza)
        echo "<script>carregar_novidades()</script>";
    }
    desconectar_bd($con);
  break;
  case 'exibir_form_cadastro_ticket':
    $con = conectar_bd(); 
      ?>
      <form name="cadastrar_ticket" method='post' action='' enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">
        <table width="450px" class='listaEdicao'>
          <thead>
            <tr>
              <td align='center'><h1>Cadastrar Novo Ticket</h1></td>
            </tr>
          </thead>

          <tr>
            <td>
              <label for="nome_ticket" class='requerido'>Nome do Ticket</label>
              <input type="text" name="nome_ticket" size=30 class='requerido'/>
            </td>
          </tr>
          <tr>
            <td>
              <label for="categoria_ticket" class='requerido'>Categoria</label>
                <select name="categoria_ticket" id="categoria_ticket" class='requerido'>
                  <?
                    echo "<option value=''></option>";
                    $sql_busca_categoria_pai = "SELECT cc.cod_categorias,cc.nome_categoria from ipi_comunicacao_categorias cc where cc.status='ATIVO' ";//,count(select* from ipi_comunicacao_subcategorias where situacao='ATIVO' and cod_categorias=cc.cod_categorias) as qtd_filha and qtd_filha>0
                    $res_busca_categoria_pai = mysql_query($sql_busca_categoria_pai);
                    while($obj_busca_categoria_pai = mysql_fetch_object($res_busca_categoria_pai))
                    {
                      echo utf8_encode("<optgroup label='".$obj_busca_categoria_pai->nome_categoria."'>");
                      $sql_busca_categorias = "select* from ipi_comunicacao_subcategorias where situacao='ATIVO' and cod_categorias='".$obj_busca_categoria_pai->cod_categorias."'";
                      $res_busca_categorias = mysql_query($sql_busca_categorias);
                      while($obj_busca_categorias = mysql_fetch_object($res_busca_categorias))
                      {
                        echo utf8_encode("<option value=".$obj_busca_categorias->cod_ticket_subcategorias."".($obj_editar->cod_ticket_subcategorias == $obj_busca_categorias->cod_ticket_subcategorias? " selected " : "" ).">".$obj_busca_categorias->nome_subcategoria."</option>");
                      }
                      echo "</optgroup>";
                    }
                  ?>
                </select>
            </td>
          </tr>
          <tr>
            <td>
              <label for="mensagem_ticket" class='requerido'>Mensagem</label>
            </br>
              <textarea rows="20" cols="50" class='requerido' name="mensagem_ticket"></textarea>
            </td>
          </tr>
          <tr>
            <td>
                <div id='upload_arquivos'>
                    <a href='javascript:void(0)' onclick='add_arq("upload_arquivos")'>Adicionar mais um arquivo </a>
                    <div name='arquivo'>
                        <label for='arq[]'>Arquivo :</label>&nbsp;<input name='arq[]' type='file' /><br/>
                    </div>
                </div>
            </td>
          </tr>
          <tr>
            <td>
              <input type="hidden" name="acao" value="cadastrar_ticket"/>
              <input type="submit" class="botao" value="Cadastrar"/>
              <input type="button" class="botao" value="Cancelar" onclick='javascript:carregar_tickets(1)'/>
            </td>
          </tr>
        </table>
      </form>
      <?
    desconectar_bd($con);
  break;
case 'exibir_cronograma_detalhado':
    $con = conectar_bd();
    $cod_crongrama = validaVarPost("cod_cronograma");

    $sql_buscar_cronograma = "select * from ipi_comunicacao_cronogramas where cod_cronogramas = '$cod_cronograma'";
    $res_buscar_cronograma = mysql_query($sql_buscar_cronograma);
    $num_buscar_cronograma = mysql_num_rows($res_buscar_cronograma);
    if($num_buscar_cronograma >0)
    {
      $obj_buscar_cronograma = mysql_fetch_object($res_buscar_cronograma);
      
      echo "<div style='width:600px;text-align:center'>";
        echo "<div name='div_conteudo'>";
          echo utf8_encode($obj_buscar_cronograma->mensagem_cronograma);
        echo "</div>";
      echo "</div>";
    }
    desconectar_bd($con);
  break;
}

