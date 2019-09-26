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
require_once '../lib/php/mensagem.php';
require_once '../../classe/canal_comunicacao.php';

cabecalho('Tickets');

$acao = validaVarPost('acao');

$cod_situacoes_resolvido = 5;
$cod_situacoes_aguardando = 4;
$cod_situacoes_andamento = 3;
$chave_primaria = 'cod_tickets';
$tabela = 'ipi_comunicacao_tickets';
$campo_ordenacao = 'titulo_ticket';
$campo_filtro_padrao = 'titulo_ticket';
$quant_pagina = 50;
$exibir_barra_lateral = true;
$codigo_usuario = $_SESSION['usuario']['codigo'];
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
switch ($acao)
{
  case 'excluir':
      $excluir = validaVarPost('excluir');
      $indices_sql = implode(',', $excluir);
      
      $conexao = conectabd();
      
      $sql_del = "UPDATE $tabela SET status='EXCLUIDO' WHERE $chave_primaria IN ($indices_sql)";
      
      if (mysql_query($sql_del))
      {
          mensagemok('Os registros selecionados foram excluídos com sucesso!');
      }
      else
      {
          mensagemerro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
      }
      
      desconectabd($conexao);
      break;
  case 'editar':
      $codigo = validaVarPost($chave_primaria);
      $novo = $codigo;
      $nome_ticket = validaVarPost('nome_ticket');
      $categoria_ticket = validaVarPost('categoria_ticket');
      $mensagem_ticket = validaVarPost('mensagem_ticket');
      $arquivos = validaVarFiles('arq');
      $arquivos_descricao = validaVarPost('desc_arq');
      $pizzarias = validaVarPost('cod_pizzarias');
      $status = validaVarPost('sel_status');
      $comentario = validaVarPost('comentario');
      $data_prevista = validaVarPost('data_prevista');
      $data_prevista_analise = validaVarPost('data_prevista_analise');
      $obs_franqueadora = validaVarPost('obs_franqueadora');
      $tempo_desenvolvimento = moeda2bd(validaVarPost("tempo_desenvolvimento"));
      $tempo_trabalhado = moeda2bd(validaVarPost("tempo_trabalhado"));
      $cod_prioridade = validaVarPost("cod_prioridade");
      /*echo "<pre>";
      print_r($_POST);
      echo "////////////";
      print_r($arquivos);
      echo "///////////";
      echo $arquivos;
      echo "</pre><br><br/>";
      die();*/
      $canal_ticket = new CanalDeComunicacao_ticket();
      $resultado = true;
      $con = conectar_bd();
      if ($codigo <= 0)
      {
          $resultado = $canal_ticket->cadastrar_novo($codigo_usuario,$_SESSION['usuario']['cod_pizzarias'][0],$categoria_ticket,$nome_ticket,$mensagem_ticket,$cod_prioridade,$arquivos,$pizzarias);
      }
      else
      {
        $resultado &= $canal_ticket->editar_ticket($codigo,$codigo_usuario,$comentario,$data_prevista,$data_prevista_analise,$status,$categoria_ticket,$tempo_desenvolvimento,$tempo_trabalhado,$cod_prioridade,$obs_franqueadora,$pizzarias,$arquivos);
      }
      
      if (($codigo>0 && $resultado) || ($codigo<=0 && $resultado >0))
      {
        mensagemok('Registro alterado com êxito!');
      }  
      else
      {
        mensagemerro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
      }        
      desconectar_bd($con);
      break;
  case 'enviar_comentario':
    $comentario = validaVarPost('comentario');
    $cod_tickets = validaVarPost('cod_tickets');
    $arquivos = validaVarFiles('arq');
    $canal_ticket = new CanalDeComunicacao_ticket();
    /*echo "<pre>";
    print_r($_POST);
    echo "////////////";
    print_r($arquivos);
    echo "///////////";
    echo $arquivos;
    echo "</pre><br><br/>";
    die();*/
    $con = conectar_bd();
    if($comentario!="")
    {
      $comentou = $canal_ticket->comentar($cod_tickets,$codigo_usuario,$comentario,true,$arquivos);
      if($comentou)
      {
          mensagemok('Comentario Cadastrado com sucesso');
      }
      else
      {
          mensagemerro('Erro ao enviar comentario','Verifique se o comentario não ficou em branco');
      }
    }
    desconectar_bd($con);
  break;
  case 'fechar_ticket':
    $canal_ticket = new CanalDeComunicacao_ticket();
    $cod_tickets = validaVarPost('cod_tickets');
    $con = conectar_bd();
    $fechou = $canal_ticket->fechar_ticket($cod_tickets,$codigo_usuario);
    
    if($fechou)
    {
        mensagemok('Ticket Fechado com sucesso');
    }
    else
    {
        mensagemerro('Erro ao fechar ticket','Verifique ');
    }
    desconectar_bd($con);
  break;
}

function get_date_diff($date1, $date2) {
  $holidays = 0;
  for ($day = $date2; $day < $date1; $day += 24 * 3600) {
    $day_of_week = date('N', $day);
    if($day_of_week > 5) {
      $holidays++;
    }
  }
  return $date1 - $date2 - $holidays * 24 * 3600;
}

function diferenca_datas($data1,$data2)
{
  return get_date_diff(date_create($data1),date_create($data2));
}



?>
<link  href="../lib/js/moodialog/css/MooDialog.css" rel="stylesheet" type="text/css" media="screen" />
<script src="../lib/js/moodialog/MooDialog.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/Overlay.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Fx.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Alert.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Request.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Confirm.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Prompt.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Error.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />
<script type="text/javascript" src="../lib/js/calendario.js"></script>
<script src="../../sys/lib/js/mascara.js" type="text/javascript"></script>
<!--<script type="text/javascript" src="../../sys/lib/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">

tinyMCE.init({
  mode : "textareas",
  theme : "advanced",
  skin : "o2k7",
  language : "pt",
  plugins: "inlinepopups,fullscreen,media, table", 
  theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,|,bullist,numlist,|,link,unlink,|,fullscreen,code,media,table",
  theme_advanced_buttons2 : "",
  theme_advanced_buttons3 : "",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_statusbar_location : "bottom",
  theme_advanced_resizing : true,
  auto_reset_designmode : true,
  force_br_newlines : true,
  force_p_newlines : false,
  entity_encoding : "raw"
});


</script>-->

<script>
 function add_arq(div_alterar)
  {
    var div_arquivo = new Element('div', {
        name: 'arquivo'
    });

    var label_arquivo = new Element('label', {
        html: 'Arquivo :'
    });

    var arquivo = new Element('input', {
        type: 'file',
        name: 'arq[]'
    });

    //new Element('span#another')]
    div_arquivo.adopt(new Element('<br/>'));
    div_arquivo.adopt(label_arquivo);
    div_arquivo.adopt(new Element('<br/>'));
    div_arquivo.adopt(arquivo);
    div_arquivo.adopt(new Element('<br/>'));
    $(div_alterar).adopt(div_arquivo);
  }
function verificaCheckbox(form) {
  var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
   
  if(cInput > 0) {
    if (confirm('Deseja excluir os registros selecionados?')) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja excluir.');
     
    return false;
  }
}

function carregar_tickets(pagina,tipo)
{
  if(typeof(tipo)=="undefined")
  {
    tipo = "tickets";
  }
  var url = "acao=carregar_tickets&pagina="+pagina+"&tipo="+tipo;
  new Request.HTML(
  {
      url: 'ipi_central_tickets_is_ajax.php',
      update: tipo,
      method:'post'
  }).send(url);
}

function calcular_kpi()
{
  var mes = $("input_mes").value;
  var ano = $("input_ano").value;
  var url = "acao=calcular_kpi1&mes="+mes+"&ano="+ano;
  new Request.HTML(
  {
      url: 'ipi_central_tickets_is_ajax.php',
      update: "div_kpi1",
      method:'post'
  }).send(url);

  var url = "acao=calcular_kpi2&mes="+mes+"&ano="+ano;
  new Request.HTML(
  {
      url: 'ipi_central_tickets_is_ajax.php',
      update: "div_kpi2",
      method:'post'
  }).send(url);



}

function editar(cod) {
  var form = new Element('form', {
    'action': '<?
    echo $_SERVER['PHP_SELF']?>',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': '<?
    echo $chave_primaria?>',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

window.addEvent('domready', function(){
    carregar_tickets(1,'tickets_sug');
    calcular_kpi();
  var tabs = new Tabs('tabs'); 

    <? if($acao == 'enviar_comentario' || $acao == 'editar_ticket')
     echo 'tabs.irpara(1);'; ?>

  if ((document.frmIncluir.<? echo $chave_primaria?>.value > 0)) {
    <?
    if ($acao == '')
        echo 'tabs.irpara(1);';
    ?>
    
    if(typeof(document.frmIncluir.botao_submit)!='undefined')
    {
      document.frmIncluir.botao_submit.value = 'Alterar';
    }
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      	document.frmIncluir.<? echo $chave_primaria?>.value = '';
        document.frmIncluir.nome_ticket.value = '';
        document.frmIncluir.categoria_ticket.value = '';
        //document.frmIncluir.data_novidade.value = '';
        //document.frmIncluir.status.value = '';
        //tinyMCE.getInstanceById('mensagem_ticket').setContent('');
        document.frmIncluir.mensagem_ticket.value = '';
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
});

</script>

<div id="tabs">
<div class="menuTab">
<ul>
    <li><a href="javascript:;">Editar</a></li>
    <li><a href="javascript:;">Incluir</a></li>
</ul>
</div>

<!-- Tab Editar -->
<div class="painelTab">

<? if ($exibir_barra_lateral): ?>

<table>
    <tr>

        <!-- Conteúdo -->
        <td class="conteudo">
        

<? endif; ?>

        <?
        $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') :(validaVarGet('p', '/[0-9]+/') ? validaVarGet('p', '/[0-9]+/') : 0);
        $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : (validaVarGet('op') ? validaVarGet('op') : $campo_filtro_padrao);
        $filtro = (validaVarPost('filtro')!="" ? validaVarPost('filtro') : validaVarGet('fil'));
        $fechados = (validaVarPost('filt_fechados')!="" ? validaVarPost('filt_fechados') : validaVarGet('ff'));
        $situacao = (validaVarPost('filt_situacao')!="" ? validaVarPost('filt_situacao') : validaVarGet('fs'));
        $filt_categorias = (validaVarPost('filt_categorias')!=""  ? validaVarPost('filt_categorias') : validaVarGet('fc')) ;
        $autor = (validaVarPost('filt_autor')!=""  ? validaVarPost('filt_autor') : validaVarGet('fa') );
        $pizzaria = (validaVarPost('filt_pizzarias')!=""  ? validaVarPost('filt_pizzarias') : validaVarGet('fp') );

        $ordenacao =  (validaVarPost('ordenacao_lista')!=""  ? validaVarPost('ordenacao_lista') : validaVarGet('ord') );
        $ordenacao_cresc =  (validaVarPost('ordenacao_cresc')!=""  ? validaVarPost('ordenacao_cresc') : validaVarGet('ordc') );

        $filts_get = "";
        if($pagina!=0)
        $filts_get .="&p=$pagina";

       /* if($opcoes!="")
        $filts_get .="&op=$opcoes";*/

        if($filtro!="")
          $filts_get .="&fil=$filtro";

        if($situacao!="")
          $filts_get .="&fs=$situacao";
        
        if($fechados!="")
          $filts_get .="&ff=$fechados";

        if($filt_categorias!="")
          $filts_get .="&fc=$filt_categorias";

        if($filt_autor!="")
          $filts_get .="&autor=$filt_autor";

        if($pizzaria!="")
          $filts_get .="&fp=$pizzaria";


        if($ordenacao!="")
          $filts_get .="&ord=$ordenacao";

        if($ordenacao_cresc!="")
          $filts_get .="&ordc=$ordenacao_cresc";

        if($ordenacao=="")
        {
          $ordenacao = "datac";
        }

        if($ordenacao_cresc=="")
        {
          $ordenacao_cresc = "DESC";
        }
        ?>
                <script>

        function ordenar(tipo_ord)
        {
          var ordem_cresc;
          var input = new Element('input', {
            'type': 'hidden',
            'name': 'ordenacao_lista',
            'value': tipo_ord
          });
          if(tipo_ord==<? echo "'$ordenacao'" ?>)
          {
            if("DESC"==<? echo "'$ordenacao_cresc'" ?>)
            {
              ordem_cres = "ASC";
            }
            else
            {
              ordem_cres = "DESC";
            }
          }
          else
          {
            ordem_cres = "DESC";
          }
          var input2 = new Element('input', {
            'type': 'hidden',
            'name': 'ordenacao_cresc',
            'value': ordem_cres
          });

          input.inject($('frmFiltro'));
          input2.inject($('frmFiltro'));

          $('frmFiltro').submit();
        }

        </script>  
        <form name="frmFiltro" id="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

            <tr>
                <td class="legenda tdbl tdbt" align="right">
                	<!--<select name="opcoes">
                    	<option value="<? echo $campo_filtro_padrao ?>"<? if ($opcoes == $campo_filtro_padrao) {echo 'selected';}?>>Título</option>
                      <option value="mensagem_ticket"<? if ($opcoes == "mensagem_ticket") {echo 'selected';}?>>Conteudo</option>
                	</select>-->
                  <label for="filtro">Id,Autor,Conteudo ou Titulo</label>
                </td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text"
                    name="filtro" size="60" value="<?
                    echo $filtro?>"></td>
            </tr>
            <tr>
                <td class="legenda tdbl" align="right">
                    <label for="filt_autor">Autor</label>
                </td>
                <td class="">&nbsp;</td>
                <td class="tdbr"><input type="text"
                    name="filt_autor" size="60" value="<?
                    echo $autor?>"></td>
            </tr>
            <tr>
                <td class="legenda tdbl" align="right">
                    <label for="filt_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?> </label>
                </td>
                <td class="">&nbsp;</td>
                <td class="tdbr" align="left">
                  <select name='filt_pizzarias'>
                    <option value="">Todas</option>
                    <?
                     $conexao = conectabd();
                      $sql_selecionar_pizzarias = "SELECT cidade,bairro,cod_pizzarias from ipi_pizzarias where cod_pizzarias in($cod_pizzarias_usuario) order by cidade";
                      $res_selecionar_pizzarias = mysql_query($sql_selecionar_pizzarias);
                      while($obj_selecionar_pizzarias = mysql_fetch_object($res_selecionar_pizzarias))
                      {
                        echo "<option value='".$obj_selecionar_pizzarias->cod_pizzarias."'";
                        if($pizzaria == $obj_selecionar_pizzarias->cod_pizzarias)
                          echo  ' SELECTED '; 
                        echo ">".$obj_selecionar_pizzarias->cidade." - ".$obj_selecionar_pizzarias->bairro."</option>";
                      } ?>
                  </select>
                </td>
            </tr>
            <tr>
                <td class="legenda tdbl" align="right">
                    <label for="fechados">Exibir tickets fechados </label>
                </td>
                <td class="">&nbsp;</td>
                <td class="tdbr" align="left">
                  <select name='filt_fechados'>
                    <option value="">Não</option>
                    <option <? if($fechados) echo " SELECTED "; ?> value="1">Sim</option>
                  </select>
                </td>
            </tr>
            <tr>
                <td class="legenda tdbl" align="right">
                    <label for="filt_situacao">Situação </label>
                </td>
                <td class="">&nbsp;</td>
                <td class="tdbr" align="left">
                  <select name='filt_situacao'>
                    <option value="">TODOS</option>
                    <option <? if($situacao == '1,3,4,8') echo  ' SELECTED ' ?>; value="1,3,4,8">ABERTOS</option>
                    <?
                     $conexao = conectabd();
                      $sql_selecionar_situacoes = "SELECT situ.nome_situacao,situ.cod_situacoes from ipi_comunicacao_situacoes situ";
                      $res_selecionar_situacoes = mysql_query($sql_selecionar_situacoes);
                      while($obj_selecionar_situacoes = mysql_fetch_object($res_selecionar_situacoes))
                      {
                        echo "<option value='".$obj_selecionar_situacoes->cod_situacoes."'";
                         if($situacao == $obj_selecionar_situacoes->cod_situacoes)
                          echo  ' SELECTED '; 
                        echo ">".$obj_selecionar_situacoes->nome_situacao."</option>";
                      } ?>
                  </select>
                </td>
            </tr>
            <tr>
                <td class="legenda tdbl" align="right">
                    <label for="filt_categorias">Categoria</label>
                </td>
                <td class="">&nbsp;</td>
                <td class="tdbr" align="left">
                  <select name="filt_categorias" >
                    <?
                    echo "<option value=''>Todas</option>";
                      $sql_busca_categoria_pai = "SELECT cc.cod_categorias,cc.nome_categoria from ipi_comunicacao_categorias cc where cc.status='ATIVO' ";//,count(select* from ipi_comunicacao_subcategorias where situacao='ATIVO' and cod_categorias=cc.cod_categorias) as qtd_filha and qtd_filha>0
                      $res_busca_categoria_pai = mysql_query($sql_busca_categoria_pai);
                      while($obj_busca_categoria_pai = mysql_fetch_object($res_busca_categoria_pai))
                      {
                        echo "<optgroup label='".$obj_busca_categoria_pai->nome_categoria."'>";
                        $sql_busca_categorias = "select* from ipi_comunicacao_subcategorias where situacao='ATIVO' and cod_categorias='".$obj_busca_categoria_pai->cod_categorias."'";
                        $res_busca_categorias = mysql_query($sql_busca_categorias);
                        while($obj_busca_categorias = mysql_fetch_object($res_busca_categorias))
                        {
                          echo "<option value=".$obj_busca_categorias->cod_ticket_subcategorias."".($filt_categorias == $obj_busca_categorias->cod_ticket_subcategorias? " selected " : "" ).">".$obj_busca_categorias->nome_subcategoria."</option>";
                        }
                        echo "</optgroup>";
                      }
                    ?>
                    </select>
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
    
        
        $sql_buscar_registros = "SELECT t.*,usu.nome,situ.nome_situacao,cca.nome_categoria,ccs.nome_subcategoria,(SELECT data_hora_comentario from ipi_comunicacao_tickets_comentarios where cod_tickets = t.cod_tickets order by data_hora_comentario DESC LIMIT 1) ultimo_coment,(select concat(pizza.cidade,'<br/>',pizza.bairro) from ipi_comunicacao_tickets_ipi_pizzarias pzt inner join ipi_pizzarias pizza on pizza.cod_pizzarias = pzt.cod_pizzarias where pzt.cod_tickets = t.cod_tickets limit 1) as nome_pizzarias FROM $tabela t inner join nuc_usuarios usu on usu.cod_usuarios = t.cod_usuarios inner join ipi_comunicacao_tickets_ipi_pizzarias ctp on ctp.cod_tickets = t.cod_tickets inner join ipi_comunicacao_subcategorias ccs on ccs.cod_ticket_subcategorias = t.cod_ticket_subcategorias inner join ipi_comunicacao_situacoes situ on situ.cod_situacoes = t.cod_situacoes inner join ipi_comunicacao_categorias cca on cca.cod_categorias = ccs.cod_categorias WHERE ctp.cod_pizzarias in(".$cod_pizzarias_usuario.",0) AND (t.titulo_ticket LIKE '%$filtro%' or t.mensagem_ticket LIKE '%$filtro%' or t.cod_tickets LIKE '%$filtro%' or usu.nome LIKE '%$filtro%') ";
        if(!$fechados)
            $sql_buscar_registros .=" and t.cod_situacoes not in(2)";

        if($filt_categorias)
            $sql_buscar_registros .=" and ccs.cod_ticket_subcategorias ='".$filt_categorias."'";

        if($autor)
            $sql_buscar_registros .=" and usu.nome like '%".$autor."%'";

        if($situacao)
            $sql_buscar_registros .=" and t.cod_situacoes in(".$situacao.")";

          $sql_buscar_registros .=" and cca.cod_categorias not in (6)";//excluindo sugestoes 

        $sql_buscar_registros .="  GROUP BY t.cod_tickets";
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
       // if($ordenacao=="")
          $ordenacao_sql = "t.data_hora_ticket";


        if($ordenacao=="titulo")
          $ordenacao_sql = "t.titulo_ticket";

        if($ordenacao=="autor")
          $ordenacao_sql = "usu.nome";

        if($ordenacao=="pizzaria")
          $ordenacao_sql = "nome_pizzarias";

        //if($ordenacao=="resps")
       //   $ordenacao = "t.titulo_ticket";

        if($ordenacao=="situ")
          $ordenacao_sql = "situ.nome_situacao";

        if($ordenacao=="datac")
          $ordenacao_sql = "t.data_hora_ticket";

        if($ordenacao=="datap")
          $ordenacao_sql = "t.data_prevista";

        if($ordenacao=="prioridade")
          $ordenacao_sql = "t.cod_prioridades";

        $sql_buscar_registros .= " ORDER BY $ordenacao_sql $ordenacao_cresc LIMIT " . ($quant_pagina * $pagina) . ', ' . $quant_pagina;//ultimo_coment desc,
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        //echo "</br>".$sql_buscar_registros."<br/>";
        $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        //echo $sql_buscar_registros;

        echo "<center><b>" . $num_buscar_registros . " registro(s) encontrado(s)</b></center><br>";
        
        if ((($quant_pagina * $pagina) == $num_buscar_registros) && ($pagina != 0) && ($acao == 'excluir'))
        {
            $pagina--;
        }
        
        echo '<center>';
        
        $numpag = ceil(((int) $num_buscar_registros) / ((int) $quant_pagina));
        
        for ($b = 0; $b < $numpag; $b++)
        {
            echo '<form name="frmPaginacao' . $b . '" method="post">';
            echo '<input type="hidden" name="pagina" value="' . $b . '">';
            echo '<input type="hidden" name="filtro" value="' . $filtro . '">';
            echo '<input type="hidden" name="opcoes" value="' . $opcoes . '">';
            
            echo '<input type="hidden" name="acao" value="buscar">';
            echo "</form>";
        }
        
        if ($pagina != 0)
        {
            echo '<a href="#" onclick="javascript:frmPaginacao' . ($pagina - 1) . '.submit();" style="margin-right: 5px;">&laquo;&nbsp;Anterior</a>';
        }
        else
        {
            echo '<span style="margin-right: 5px;">&laquo;&nbsp;Anterior</span>';
        }
        
        for ($b = 0; $b < $numpag; $b++)
        {
            if ($b != 0)
            {
                echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
            }
            
            if ($pagina != $b)
            {
                echo '<a href="#" onclick="javascript:frmPaginacao' . $b . '.submit();">' . ($b + 1) . '</a>';
            }
            else
            {
                echo '<span><b>' . ($b + 1) . '</b></span>';
            }
        }
        
        if (($quant_pagina == $linhas_buscar_registros) && ((($quant_pagina * $pagina) + $quant_pagina) != $num_buscar_registros))
        {
            echo '<a href="#" onclick="javascript:frmPaginacao' . ($pagina + 1) . '.submit();" style="margin-left: 5px;">Próxima&nbsp;&raquo;</a>';
        }
        else
        {
            echo '<span style="margin-left: 5px;">Próxima&nbsp;&raquo;</span>';
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
                    <td align="center" width="25%" <? echo ($ordenacao=="titulo" ? ($ordenacao_cresc=="ASC" ? ' class="ordem_asc"' : ' class="ordem_desc"') : '') ?>><u onclick='javascript:ordenar("titulo")' style='cursor: pointer;'>Título Ticket</u></td>

                    <td align="center" width="10%" <? echo ($ordenacao=="autor" ? ($ordenacao_cresc=="ASC" ? ' class="ordem_asc"' : ' class="ordem_desc"') : '') ?>><u onclick='javascript:ordenar("autor")' style='cursor: pointer;'>Autor</u></td>

                    <td align="center" width="10%" <? echo ($ordenacao=="pizzaria" ? ($ordenacao_cresc=="ASC" ? ' class="ordem_asc"' : ' class="ordem_desc"') : '') ?>><u onclick='javascript:ordenar("pizzaria")' style='cursor: pointer;'><? echo ucfirst(TIPO_EMPRESA)?></u></td>

                    <td align="center" width="10%">Último Comentário</td>
                    <td align="center" width="2%">Respostas</td>

                    <td align="center" width="10%" <? echo ($ordenacao=="situ" ? ($ordenacao_cresc=="ASC" ? ' class="ordem_asc"' : ' class="ordem_desc"') : '') ?>><u  onclick='javascript:ordenar("situ")' style='cursor: pointer;'>Situação</u></td>

                    <td align="center" width="10%" <? echo ($ordenacao=="datac" ? ($ordenacao_cresc=="ASC" ? ' class="ordem_asc"' : ' class="ordem_desc"') : '') ?>><u  onclick='javascript:ordenar("datac")' style='cursor: pointer;'>Data de Criação</u></td>

                    <td align="center" width="10%" <? echo ($ordenacao=="datap" ? ($ordenacao_cresc=="ASC" ? ' class="ordem_asc"' : ' class="ordem_desc"') : '') ?>><u  onclick='javascript:ordenar("datap")' style='cursor: pointer;'>Data prevista</u></td>
                    <td align="center" width="3%" <? echo ($ordenacao=="prioridade" ? ($ordenacao_cresc=="ASC" ? ' class="ordem_asc"' : ' class="ordem_desc"') : '') ?>><u  onclick='javascript:ordenar("prioridade")' style='cursor: pointer;'>Prioridade</u></td>
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
                if($obj_buscar_registros->data_prevista <= date("Y-m-d")  && $obj_buscar_registros->data_prevista !="" && $obj_buscar_registros->cod_situacoes!=2)// 2 é o codigo da situacao fechado, n fiz variavel pois acho 
                                                        //q vou mudar isto FIXME
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
                echo '<td align="center" style="background-color:'.$cod_fundo_comentario.'" ><a href="?cc='.$obj_buscar_registros->cod_tickets.''.$filts_get.' ">' .sprintf("%04d",$obj_buscar_registros->cod_tickets)."-". bd2texto($obj_buscar_registros->titulo_ticket) . '</a><br/><span style="font-size:10px; ;">Categoria:'.$obj_buscar_registros->nome_categoria.' - '.$obj_buscar_registros->nome_subcategoria.'</span></td>';

                echo '<td align="center" style="background-color:'.$cod_fundo_comentario.'">'. bd2texto($obj_buscar_registros->nome).'<br/></td>';
                echo '<td align="center" style="background-color:'.$cod_fundo_comentario.'">'. bd2texto(($obj_buscar_registros->nome_pizzarias 
                  ? $obj_buscar_registros->nome_pizzarias : 'Público')).'</td>';
                if($quantidade>0)
                {
                  echo "<td align='center' style='background-color:".$cod_fundo_comentario."'>" . $obj_buscar_comentarios_ticket->nome . "</br> <b><small>em ".bd2data(date('Y-m-d',strtotime($obj_buscar_comentarios_ticket->data)))." as ".date('H:i',strtotime($obj_buscar_comentarios_ticket->data))."</small></b></td>";
                }else
                {
                  echo '<td align="center" style="background-color:'.$cod_fundo_comentario.'">Nenhum</td>';
                }
 
                echo '<td align="center" style="background-color:'.$cod_fundo_comentario.'">'.$quantidade.'</td>';
                echo '<td align="center" style="background-color:'.$cod_fundo_comentario.'">'.$obj_buscar_registros->nome_situacao.'</td>';

                echo '<td align="center" style="background-color:'.$cod_fundo_comentario.'">'. date("d/m/Y",strtotime($obj_buscar_registros->data_hora_ticket)).'<br/> as '. date("H:i",strtotime($obj_buscar_registros->data_hora_ticket)).'</td>';

                echo '<td align="center" style="background-color:'.$cod_fundo_comentario.'">'.($obj_buscar_registros->data_prevista ? ''.date('d/m/Y',strtotime($obj_buscar_registros->data_prevista)): '' ).'</td>';
               // $canal_ticket = new CanalDeComunicacao_ticket();

                echo '<td align="center" style="background-color:'.$cod_fundo_comentario.'">'.$obj_buscar_registros->cod_prioridades.'</td>';

                echo '</tr>';
                $a++;
            }
            desconectabd($conexao);
            ?>
            </tbody>
        </table>

        <input type="hidden" name="acao" value="excluir">
        </form>
        <br/>
        <div id="tickets_sug" style="width:1015px;height: 615px;" class='div_cont'>
      </div>

<?
if ($exibir_barra_lateral)
:
    ?>
        </td>
        <!-- Conteúdo -->

        <!-- Barra Lateral -->
        <td class="lateral">
        <div class="blocoNavegacao">
        <ul>
            <li><a href="ipi_central_comunicacao.php">Voltar o canal de comunicações</a></li>
            <!--<li><a href="ipi_central_tickets.php"></a></li>-->
        </ul>
        </div>
        <div id="botoes_kpi" style="border:1px solid black">
          <label for="input_mes">Mês para verificar</label>
          <input type="text" size="2" max-lenght = "2" name="input_mes" id="input_mes"/><br/>
          <label for="input_ano">Ano para verificar</label>
          <input type="text" size="4" max-lenght = "4" name="input_ano" id="input_ano"/>
          <input type="button" value="Calcular KPI" onclick="calcular_kpi()"/>
        </div>
        <div id="div_kpi1" style='border:1px solid black'>  
          
        </div>
        <div id="div_kpi2" style='border:1px solid black'>  
          
        </div>
        </td>
        <!-- Barra Lateral -->

</table>


<? endif;
?>

</div>

<!-- Tab Editar --> <!-- Tab Incluir -->
<div class="painelTab">

<?
$codigo = validaVarPost($chave_primaria, '/[0-9]+/');
$codigo = ($codigo ? $codigo : validaVarGet('cc', '/[0-9]+/'));
$editar = true;
$novo = true;
$pode_editar = false;
$pizzarias[] = 0;
if ($codigo > 0)
{
  $novo = false;
  $editar= false;
  $obj_editar = executaBuscaSimples("SELECT t.*,situ.nome_situacao FROM $tabela t inner join ipi_comunicacao_situacoes situ on situ.cod_situacoes = t.cod_situacoes WHERE t.$chave_primaria = $codigo");
  //echo "<br/>SELECT t.*,situ.nome_situacao FROM $tabela t inner join ipi_comunicacao_situacoes situ on situ.cod_situacoes = t.cod_situacoes WHERE t.$chave_primaria = $codigo</br>";
  $con = conectar_bd();

  if((($obj_editar->cod_usuarios==$codigo_usuario) || ($codigo_usuario==1)) && ($obj_editar->cod_situacoes!=2))
  {
    $pode_editar = true;
  }

  
  if($pode_editar)
  { 
    if($acao == 'editar_ticket')
    {
      $editar = true;
    }
  }
  $sql_buscar_pizzarias = "select p.cod_pizzarias from ipi_comunicacao_tickets_ipi_pizzarias p where p.cod_tickets = ".$obj_editar->cod_tickets;
    //echo "<br/>".$sql_buscar_pizzarias."</br>";
  $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
  $pizzarias = array();
  while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
  {
    $pizzarias[] = $obj_buscar_pizzarias->cod_pizzarias;
  }
  $str_pizzarias = implode(',',$pizzarias);
    
}
?>

<style type='text/css'>
  
  <? if(!$editar):?>

    label.requerido {
      background:none;
      padding-left:0px !important;
    }
  <? endif;?>
  .div_cont
  {
    border: 1px #EB8612 solid;
    font-size: 13pt;
    margin-bottom: 15px;
    margin: 0 auto; 
    width: 800px;'
  }
</style>
<? if($codigo>0 && $editar): ?>
<script>

window.addEvent('domready', function() 
{
    new vlaDatePicker('data_prevista', {openWith: 'botao_data_prevista'});
    new vlaDatePicker('data_prevista_analise', {openWith: 'botao_data_prevista_analise'});
});

</script>
<? endif; ?>

<script>

function validar_form_editar(form)
{
  if(!validaRequeridos(form))
  {
    return false;
  }
<? if($codigo>0 && $editar): ?>
  if(form.sel_status.value==8 && form.data_prevista_analise.value == "")
  {
    alert('O ticket não pode ser alterado para analise, sem uma data de fim de analise');
    return false;
  }

<? endif; ?>
  return true;
}



</script>
<div class='div_cont'>
  

  <form name="frmIncluir" method='post' action='' enctype="multipart/form-data" onsubmit="return validar_form_editar(this)">
    <table width="800px" align='center' class='detalhesTicket'>
      <thead>
        <tr>
        <td align='center'><h1><? if($obj_editar->titulo_ticket) echo "#".sprintf("%04d",$obj_editar->cod_tickets)." - ".$obj_editar->titulo_ticket ; else echo " Cadastrar Novo Ticket" ; ?></h1></td>
        </tr>
      </thead>
      <tr>
        <td>
            <?
            if($editar)
            {
              if(count($_SESSION['usuario']['cod_pizzarias'])==1)
              {
                echo "<input type='hidden' name='cod_pizzarias[]' value='".$_SESSION['usuario']['cod_pizzarias'][0]."'/>";
                echo "<input type='checkbox' name='cod_pizzarias[]' ".(in_array(0,$pizzarias) ? "checked='checked'" : "" )." value='0'>Ticket Público";
              }else
              {
                echo "<h3 style='color:#EB8612'>Ticket visivel para: </h3>";
                echo '</td></tr>';
                echo '<tr><td>';
                $con = conectar_bd();
                $sql_buscar_pizzarias = "select cod_pizzarias,nome from ipi_pizzarias where cod_pizzarias in(".$cod_pizzarias_usuario.")";
                $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
                echo "<ul class='lista_pizzarias'>";
                echo "<li><input id='cod0' type='checkbox' name='cod_pizzarias[]' ".(in_array(0,$pizzarias) ? "checked='checked'" : "" )." value='0'><label for='cod0'>&nbsp;&nbsp;Ticket Público</label></input><br /></li>";
                while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
                {
                    echo "<li><input type='checkbox' id='cod".$obj_buscar_pizzarias->cod_pizzarias."' name='cod_pizzarias[]' value='".$obj_buscar_pizzarias->cod_pizzarias."' ";
                    if(in_array($obj_buscar_pizzarias->cod_pizzarias,$pizzarias))//||in_array(0,$pizzarias)
                    {
                        echo "checked='checked'";//".(in_array(0,$pizzarias) ? "disabled='disabled'" : "")."
                    }
                    echo " /><label for='cod".$obj_buscar_pizzarias->cod_pizzarias."'>&nbsp;&nbsp;".$obj_buscar_pizzarias->nome."</label></li>";
                }
                echo "</ul>";
              }
            }else
            {
              echo "<h3 style='color:#EB8612'>Ticket visivel para: </h3>";
              echo "<ul style='list-style: none;padding-left: 20px;line-height: 20px;'>";
              if(in_array(0,$pizzarias))
              {
                echo "<li>Todas as pizzarias<br /></li>";
              }else
              {
                $sql_buscar_pizzarias = "select cod_pizzarias,nome from ipi_pizzarias where cod_pizzarias in(".implode(',',$pizzarias).")";
                $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);

                while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
                {
                  echo "<li>".$obj_buscar_pizzarias->nome."</li>";
                }
              }
            }

            ?>
        </td>
      </tr>
      <? if($codigo>0): ?>
        <tr>
        <td align='left'>
          <label >Dia de Criação</label><br />
          <div style='border:1px solid #D44E08; padding: 10px;'><? echo date("d/m/Y",strtotime($obj_editar->data_hora_ticket)) ;?></div>
          <?
            if(!$editar)
            {
                if($obj_editar->data_prevista_analise)
                {
                  echo "<br/><label >Data Prevista Análise</label><br />";
                  echo "<div style='border:1px solid #D44E08; padding: 10px;'>".date("d/m/Y",strtotime($obj_editar->data_prevista_analise))."</div>";
                }
                if($obj_editar->data_prevista)
                {
                  echo "<br/><label >Data Prevista</label><br />";
                  echo "<div style='border:1px solid #D44E08; padding: 10px;'>".date("d/m/Y",strtotime($obj_editar->data_prevista))."</div>";
                }
            }else
            {
              echo "<br/><label >Data Prevista para fim de análise</label><br />";
              echo '<input type="text"
                    name="data_prevista_analise" id="data_prevista_analise" size="8"
                    value="'.($obj_editar->data_prevista_analise ? date("d/m/Y",strtotime($obj_editar->data_prevista_analise)) : '').'"
                    onkeypress="return MascaraData(this, event)"> &nbsp; <a
                    href="javascript:void(0);" id="botao_data_prevista_analise"><img
                    src="../lib/img/principal/botao-data.gif"></a>';

              echo "<br/><br/><label >Data Prevista</label><br />";
              echo '<input type="text"
                    name="data_prevista" id="data_prevista" size="8"
                    value="'.($obj_editar->data_prevista ? date("d/m/Y",strtotime($obj_editar->data_prevista)) : '').'"
                    onkeypress="return MascaraData(this, event)"> &nbsp; <a
                    href="javascript:void(0);" id="botao_data_prevista"><img
                    src="../lib/img/principal/botao-data.gif"></a>';
            }
          ?>
          </td>
        </tr>
      <? endif; ?>
      <tr>
        <td align='left'>
          <label for="nome_ticket" class='requerido'>Nome do Ticket</label><br />
          <? if($novo):?>
          <input type="text" name="nome_ticket" size='60' class='requerido' value=""/>
          <? else: ?>
           <? echo "<div style='border:1px solid #D44E08; padding: 10px;'>".$obj_editar->titulo_ticket."</div>"; 

              $sql_buscar_nome_autor = "select * from nuc_usuarios where cod_usuarios = ".$obj_editar->cod_usuarios;
             // echo "<br/>".$sql_buscar_nome_autor."</br><pre>";
              //var_dump($obj_editar,true);
              //echo "</pre>";
              $res_buscar_nome_autor = mysql_query($sql_buscar_nome_autor);
              $obj_buscar_nome_autor = mysql_fetch_object($res_buscar_nome_autor);
              echo "<br/><label>Autor</label><br/>";
              echo "<div style='border:1px solid #D44E08; padding: 10px;'>".$obj_buscar_nome_autor->nome."</div>";
          ?>
          <? endif; ?>
        </td>
      </tr>
      <tr>
        <td align='left'>
            <label for="categoria_ticket" class='requerido'>Categoria</label><br /> 
            <? if($editar): ?>

          <select name="categoria_ticket" <? if (!$editar) echo "disabled" ?> class='requerido'>
            <?
              $sql_busca_categoria_pai = "SELECT cc.cod_categorias,cc.nome_categoria from ipi_comunicacao_categorias cc where cc.status='ATIVO' ";//,count(select* from ipi_comunicacao_subcategorias where situacao='ATIVO' and cod_categorias=cc.cod_categorias) as qtd_filha and qtd_filha>0
              $res_busca_categoria_pai = mysql_query($sql_busca_categoria_pai);
              while($obj_busca_categoria_pai = mysql_fetch_object($res_busca_categoria_pai))
              {
                echo "<optgroup label='".$obj_busca_categoria_pai->nome_categoria."'>";
                $sql_busca_categorias = "select* from ipi_comunicacao_subcategorias where situacao='ATIVO' and cod_categorias='".$obj_busca_categoria_pai->cod_categorias."'";
                $res_busca_categorias = mysql_query($sql_busca_categorias);
                while($obj_busca_categorias = mysql_fetch_object($res_busca_categorias))
                {
                  echo "<option value=".$obj_busca_categorias->cod_ticket_subcategorias."".($obj_editar->cod_ticket_subcategorias == $obj_busca_categorias->cod_ticket_subcategorias? " selected " : "" ).">".$obj_busca_categorias->nome_subcategoria."</option>";
                }
                echo "</optgroup>";
              }
            ?>
            </select>
            <? else: ?>
              <?
                $sql_busca_categorias = "select* from ipi_comunicacao_subcategorias where cod_ticket_subcategorias= ".$obj_editar->cod_ticket_subcategorias;
                $res_busca_categorias = mysql_query($sql_busca_categorias);
                $obj_busca_categorias = mysql_fetch_object($res_busca_categorias);
                echo "<div style='border:1px solid #D44E08; padding: 10px;'>".$obj_busca_categorias->nome_subcategoria."</div>";
              ?>

            <? endif; ?>
        </td>
      </tr>
      <tr>
          <td>
              <? if($editar && !$novo): ?>
              <label for="fechados">Situação</label><br/>
                <select name='sel_status'>
                  <?
                  echo "<option value='".$obj_editar->cod_situacoes."'>".$obj_editar->nome_situacao."</option>";
                  $sql_selecionar_situacoes = "SELECT css.*,situ.nome_situacao,situ.cod_situacoes from ipi_comunicacao_subcategorias_situacoes css inner join ipi_comunicacao_situacoes situ on situ.cod_situacoes = css.cod_situacoes_fim where css.cod_ticket_subcategorias = ".$obj_editar->cod_ticket_subcategorias." AND css.cod_situacoes_origem = ".$obj_editar->cod_situacoes;
                  $res_selecionar_situacoes = mysql_query($sql_selecionar_situacoes);
                  while($obj_selecionar_situacoes = mysql_fetch_object($res_selecionar_situacoes))
                  {
                    echo "<option value='".$obj_selecionar_situacoes->cod_situacoes."'";
                     if($obj_editar->cod_situacoes == $obj_selecionar_situacoes->cod_situacoes)
                      echo  ' SELECTED '; 
                    echo ">".$obj_selecionar_situacoes->nome_situacao."</option>";
                  }
                ?> 
               </select>
              <? elseif(!$novo): ?>
              <label for="fechados">Situação</label><br/>
                <div style='border:1px solid #D44E08; padding: 10px;'><? echo $obj_editar->nome_situacao; ?></div>
              <? endif; ?>
         
          </td>
      </tr>
      <tr>
        <td align='left'>
          <label for="mensagem_ticket" class='requerido'>Mensagem</label>
        
        <? if($novo)
        {
          echo '<textarea rows="15" cols="100" class="requerido" id="mensagem_ticket" name="mensagem_ticket">'.$obj_editar->mensagem_ticket.'</textarea>';
        }else
        {
          echo "<div style='border:1px solid #D44E08; padding: 10px;'>".nl2br($obj_editar->mensagem_ticket)."</div>";
        }
        ?>
        </td>
      </tr>
            <? if($codigo_usuario==1 || $codigo_perfil==4 || $codigo_perfil==2): ?>
      <tr>
        
        <? if($pode_editar && $editar)
        {
          echo "<td align='left'>";
          echo '<label for="obs_franqueadora">Comentário da Franqueadora</label><br/>';
          echo '<textarea rows="10" cols="100" id="obs_franqueadora" name="obs_franqueadora">'.$obj_editar->observacao_franqueadora.'</textarea>';
        }
        elseif($obj_editar->observacao_franqueadora!="")
        {
          echo "<td align='left'>";
          echo '<label for="obs_franqueadora">Comentário da Franqueadora</label><br/>';
          echo "<div style='border:1px solid #D44E08; padding: 10px;'>".nl2br($obj_editar->observacao_franqueadora)."</div>";
        }
        ?>
        </td>
      </tr>
      <tr>
      <? if($pode_editar && $editar)
          {
                      echo "<td align='left'>";
          echo '<label for="cod_prioridade">Prioridade</label><br/>';
            echo "<input type='text' size='3' maxlenght='1' name='cod_prioridade' id='cod_prioridade' value='".$obj_editar->cod_prioridades."'/>";
          }
          elseif($obj_editar->cod_prioridades!="")
          {
                      echo "<td align='left'>";
          echo '<label for="cod_prioridade">Prioridade</label><br/>';
            echo "<div style='border:1px solid #D44E08; padding: 10px;width:100px'>".nl2br($obj_editar->cod_prioridades)."</div>";
          }
          ?>
        </td>
      </tr>
      <tr>
          <? if($pode_editar && $editar)
          {
                      echo "<td align='left'>";
          echo '<label for="tempo_desenvolvimento">Tempo de desenvolvimento</label><br/>';
            echo "<input type='text' size='5' maxlenght='3' name='tempo_desenvolvimento' id='tempo_desenvolvimento' value='".bd2moeda($obj_editar->tempo_desenvolvimento)."'/>";
          }
          elseif($obj_editar->tempo_desenvolvimento!="")
          {
                      echo "<td align='left'>";
          echo '<label for="tempo_desenvolvimento">Tempo de desenvolvimento</label><br/>';
            echo "<div style='border:1px solid #D44E08; padding: 10px;width:100px'>".nl2br(bd2moeda($obj_editar->tempo_desenvolvimento))."</div>";
          }
          ?>
        </td>
      </tr>
            <tr>
              <td align='left'>
          <? if($pode_editar && $editar)
          {
          echo '<label for="tempo_trabalhado">Tempo Trabalhado</label><br/>';
            echo "<input type='text' size='5' maxlenght='3' name='tempo_trabalhado' id='tempo_trabalhado' value='".bd2moeda($obj_editar->tempo_trabalhado)."'/>";
          }
          elseif($obj_editar->tempo_trabalhado!="")
          {
          echo '<label for="tempo_trabalhado">Tempo Trabalhado</label><br/>';
            echo "<div style='border:1px solid #D44E08; padding: 10px;width:100px'>".nl2br(bd2moeda($obj_editar->tempo_trabalhado))."</div>";
          }
          ?>
        </td>
      </tr>
    <? endif; ?>
    <? if($novo): ?>
      <tr>
        <td>
            <div id='upload_arquivos'>
                <a href='javascript:void(0)' onclick='add_arq("upload_arquivos")'>+ Adicionar mais um arquivo </a>
                <div name='arquivo'>
                    <label for='arq[]'>Arquivo:<br /></label>&nbsp;<input name='arq[]' type='file' /><br/><br />
                   <!-- <label for='desc_arq[]'>Descrição Opicional:<br /></label>&nbsp;<input type='text' name='desc_arq[]' size='30'/>-->
                </div>
            </div>
        </td>
      </tr>
    <? endif; ?>  
      <tr>
        <td>
          <?  
            if($codigo > 0)
            {
              $sql_buscar_uploads = "select * from ipi_comunicacao_tickets_arquivos where cod_tickets='$codigo' and cod_comentarios ='0'";
              $res_buscar_uploads = mysql_query($sql_buscar_uploads);
              $num_buscar_uploads = mysql_num_rows($res_buscar_uploads);

              if($num_buscar_uploads>0)
              {
                echo '<div id="cont_anexos">';
                echo "<h3>Anexos</h1>";
                echo "<br/>";
                echo "<table class='listaEdicao'>";
                echo "<thead><tr><td>Nome do Arquivo</td></tr></thead><tbody>";
                while($obj_buscar_uploads = mysql_fetch_object($res_buscar_uploads))
                {
                  echo "<tr>";
                  echo "<td ><a href='".UPLOAD_DIR."/comunicacao/suporte/".$obj_buscar_uploads->nome_arquivo."' target='_blank'>".$obj_buscar_uploads->nome_arquivo."</a></td>";
                }
                echo "</tbody></table></div>";
              }
            }
          ?>
          <input type="hidden" name="<? echo $chave_primaria?>" value="<?  echo $codigo?>">
        </td>
      </tr>
      <? if($pode_editar && !$editar): ?>
        <tr>
          <td>
            <input type="hidden" name="acao" value="editar"/>
          </form>
            <h3 style='color:#EB8612'>Ações</h1>
            <br/>
            <form name="frmEditarTicket" action = '' method='post'>
              <input class="botao" type="submit" value="Editar este ticket" />
              <input type="hidden" name="acao" value="editar_ticket"/>
              <input type="hidden" name="cod_tickets" value="<?  echo $codigo?>"/>
            </form>
             
            <? 
            //$intervalo = diferenca_datas($obj_editar->data_hora_ticket,(date('Y-m-d'))); ?>
            <? if(($codigo_usuario==$obj_editar->cod_usuarios) ||  ( $pode_editar && $codigo_usuario==1)): ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <form name="frmFecharTicket" action = '' method='post'>
              <input class="botao" type="submit" value="Fechar este ticket" />
              <input type="hidden" name="acao" value="fechar_ticket"/>
              <input type="hidden" name="cod_tickets" value="<?  echo $codigo?>"/>
            </form>
            <? endif; ?>
          </td>
        </tr>
      <? else: ?>
      </form>

      <? endif; ?>
          <? if(($editar || $obj_editar->cod_situacoes==2) && $codigo<=0): ?>
                <tr>
            <td align='center'>
            <input type="hidden" name="<? echo $chave_primaria?>" value="<?  echo $codigo?>">
            <input type="hidden" name="acao" value="editar"/>
            <input type="submit" class="botao" name="botao_submit" value="Cadastrar"/>&nbsp;&nbsp;&nbsp;

            </form>
              </td>
             </tr>
          <? elseif($editar && $codigo>0): ?>
              <tr>
                <td align='center'>
                  <input type="hidden" name="<? echo $chave_primaria?>" value="<?  echo $codigo?>">                        
                  <div id='upload_arquivos_edicao'>
                    <a href='javascript:void(0)' onclick='add_arq("upload_arquivos_edicao")'>Adicionar mais um arquivo </a>
                    <div name='arquivo'>
                      <label for='arq[]'>Arquivo:<br /></label>&nbsp;<input name='arq[]' type='file' /><br/><br />
                    </div>
                  </div>
                </td>
              </tr> 
              <tr><td><h3 style='color:#EB8612'>Comentário:</h3></td></tr>
                <tr>
                    <td>
                        <textarea name='comentario' rows="8" cols="75"></textarea>
                    </td>
                </tr>
                <tr>
                  <td colspan='2' align="center">    
                    <input type="hidden" name="acao" value="editar"/>
                    <input type="submit" class="botao" name="botao_submit" value="Alterar"/>
                  </td>
                </tr>
              </form> 
          <? endif; ?>
  </table>

  <?  if ($codigo > 0 ): //&& !$editar?>
  <br/>
  <hr/>
  <br/>
  <div id="cont_coments">
  <h1>Comentários</h1>
  <!-- comentarios -->
  <?
  $sql_buscar_comentarios = "select * from ipi_comunicacao_tickets_comentarios ctc inner join nuc_usuarios usu on usu.cod_usuarios = ctc.cod_usuarios where ctc.cod_tickets ='$codigo' and ctc.status = 'ATIVO'";
  $res_buscar_comentarios = mysql_query($sql_buscar_comentarios);
  $num_coments = mysql_num_rows($res_buscar_comentarios);

  echo "<table align='center' class='caixa'>";

  if($num_coments>0)
  {
      $a = 0;
      while($obj_buscar_comentarios = mysql_fetch_object($res_buscar_comentarios))
      {
        $sql_busca_anexos = "select * from ipi_comunicacao_tickets_arquivos where cod_tickets = '$codigo' and cod_comentarios='".$obj_buscar_comentarios->cod_comentarios."'";
        //echo $sql_busca_anexos;
        $res_busca_anexos = mysql_query($sql_busca_anexos);
        $num_busca_anexos = mysql_num_rows($res_busca_anexos);

        $cod_fundo_comentario = ($a%2 == 0) ? '#FFF' : '#EFEFEF' ;
        echo "<tr>";
        echo "<td class='tdbt tdbl tdbr tdbb' align='center' style='background-color:".$cod_fundo_comentario."'><b>".$obj_buscar_comentarios->nome."</b><br/> em ".bd2data(date('Y-m-d',strtotime($obj_buscar_comentarios->data_hora_comentario)))." as ".date('H:i:s',strtotime($obj_buscar_comentarios->data_hora_comentario))."</td>";
        echo "<td class='tdbt tdbr tdbb' border='1' align='center' style='background-color:".$cod_fundo_comentario."'>".nl2br($obj_buscar_comentarios->comentario)."<br/><br/>";
        
        if($num_busca_anexos>0)
        {
          echo "<hr><h2>Anexos</h2>";
          echo "<div id=cont_anexos>";
          echo "<table class='detalhesTicket listaEdicao'>";
          echo "<thead><tr><td>Nome do Arquivo</td></tr></thead><tbody>";

          while($obj_busca_anexos = mysql_fetch_object($res_busca_anexos))
          {
            echo "<tr>";
            echo "<td ><a href='".UPLOAD_DIR."/comunicacao/suporte/".$obj_busca_anexos->nome_arquivo."' target='_blank'>".$obj_busca_anexos->nome_arquivo."</a></td>";
          }
          echo "</tbody></table></div>";
        }
        echo "</td>";
        echo "</tr>";
        $a++;
      }
  }
  else
  {
    echo "<tr><td align='center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td align='center'></br>Nenhum comentário</br></br></td></tr>";

  }

      if(($obj_editar->cod_situacoes!=2) && !$editar): ?>

      <?
      $sql_busca_usuario = "select nome from nuc_usuarios where cod_usuarios = '$codigo_usuario'";
      $res_busca_usuario = mysql_query($sql_busca_usuario);
      $obj_busca_usuario = mysql_fetch_object($res_busca_usuario);
      $nome_usuario = $obj_busca_usuario->nome;
      ?>
      <tr><td colspan='2' ><h1>Fazer um novo comentário</h1></br></td></tr>
      <form name="frmIncluirComentario" action='' method='post' enctype="multipart/form-data">
        <tr>
            <td align="center" class='tdbt tdbl tdbb tdbr'>
                <? echo $nome_usuario ?>
            </td>
            <td class='tdbt tdbr tdbb'>
                <textarea name='comentario' rows="8" cols="75"></textarea>
                <br/>
                <div id='upload_arquivos_comentario'>
                  <!--<a href='javascript:void(0)' onclick='add_arq("upload_arquivos_comentario")'>Adicionar mais um arquivo </a>-->
                  <div name='arquivo'>
                    <label for='arq[]'>Arquivo:<br /></label>&nbsp;<input name='arq[]' type='file' /><br/><br />
                  </div>
                </div>
            </td>
        </tr>
        <tr>
          <td colspan='2' align="center">    
          <input type="submit" class="botao" name="btnEnviarComentario" value="Comentar"/>
          </td>
        </tr>
          <input type="hidden" name="acao" value="enviar_comentario"/>
          <input type="hidden" name="<? echo $chave_primaria?>" value="<?  echo $codigo?>">
      </form> 
        <? desconectar_bd($con) ?>
    <? endif; ?>
     </table>
<? endif; ?>





</div>
</div>
</div>
<!-- Tab Incluir -->
</div>





<?
rodape();
?>