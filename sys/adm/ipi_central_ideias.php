<?php

/**
 * Cadastro de Secões.
 *
 * @version 1.0
 * @package iti
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       11/09/2009   ELIAS         Criado.
 *
 */

$cod_secoes = 5; //Seleciona o tipo de secao.

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/canal_comunicacao.php';

cabecalho('Cadastro de Idéias');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_ideias';
$tabela = 'ipi_ideias';
$campo_ordenacao = 'cod_tickets';
$campo_filtro_padrao = 'cod_tickets';
$quant_pagina = 50;
$exibir_barra_lateral = false;
$codigo_usuario = $_SESSION['usuario']['nome'];
$codigo_perfil = $_SESSION['usuario']['perfil'];
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

switch ($acao)
{
    case 'excluir':
      $con = conectar_bd();
        
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
         
        $sql_del = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";
        
        if (mysql_query($sql_del))
        {
            mensagemok('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemerro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        }
        
         desconectar_bd($con);
        break;
    case 'editar':
     $con = conectar_bd();
        $codigo = validaVarPost($chave_primaria);
        $titulo = validaVarPost('titulo');
        $cod_tickets = validaVarPost('cod_tickets');
        $titulo_ideia = (validaVarPost('titulo_ideia'));
        $autor_ideia = (validaVarPost('autor_ideia'));
        $publico = validaVarPost('publico');
        $mensagem_ideia = validaVarPost('mensagem_ideia');
        $situacao = validaVarPost('situacao');
        $tempo_desenvolvimento = validaVarPost('tempo_desenvolvimento');
        $data_hora_ideia = validaVarPost('data_hora_ideia');
        $data_prevista_analise = validaVarPost('data_prevista_analise');
        $data_prevista = validaVarPost('data_prevista');


        $cod_tickets=str_replace(".", "", $cod_tickets);
        $cod_tickets=str_replace(", ", " - ", $cod_tickets);


        
        
            
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (cod_tickets, titulo_ideia, autor_ideia, publico, mensagem_ideia, situacao, tempo_desenvolvimento, data_hora_ideia, data_prevista_analise, data_prevista) 
                VALUES ('%s', '%s', '%s', '%s', '%s', '%s','%s', NOW(), '%s', '%s')", $cod_tickets, $titulo_ideia,$codigo_usuario, $publico, $mensagem_ideia, $situacao, $tempo_desenvolvimento, data2bd($data_hora_ideia), data2bd($data_prevista_analise), data2bd($data_prevista) );
            $res_edicao = mysql_query($sql_edicao);
            //echo "cadastrado  ".$sql_edicao;
           
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET cod_tickets = '%s', titulo_ideia = '%s', autor_ideia = '%s', publico = '%s', mensagem_ideia = '%s', situacao = '%s', tempo_desenvolvimento = '%s', data_prevista_analise = '%s', data_prevista = '%s'WHERE $chave_primaria = $codigo", $cod_tickets, $titulo_ideia, $codigo_usuario, $publico, $mensagem_ideia, $situacao, $tempo_desenvolvimento, data2bd($data_prevista_analise), data2bd($data_prevista));
                   //echo "atualizado  ".$sql_edicao;
            $res_edicao = mysql_query($sql_edicao);
        }

      
        
        if ($res_edicao)
        {
            mensagemok('Registro alterado com êxito!');
        }  
        else
        {
            mensagemerro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }        
        
       desconectar_bd($con);
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<script src="../../sys/lib/js/mascara.js" type="text/javascript"></script>
<script type="text/javascript" src="../../sys/lib/js/tiny_mce/tiny_mce.js"></script>


<script>


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
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.<? echo $chave_primaria?>.value > 0) {
    <?
    if ($acao == '')
        echo 'tabs.irpara(1);';
    ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
        document.frmIncluir.<? echo $chave_primaria?>.value = '';
        document.frmIncluir.cod_tickets.value = '';
        document.frmIncluir.titulo_ideia.value = '';
        document.frmIncluir.autor_ideia.value = '';
        document.frmIncluir.publico.value = '';
        document.frmIncluir.mensagem_ideia.value = '';
        document.frmIncluir.tempo_desenvolvimento.value = '';
        document.frmIncluir.situacao.value = '';
         document.frmIncluir.data_hora_ideia.value = '';
          document.frmIncluir.data_prevista_analise.value = '';
           document.frmIncluir.data_prevista.value = '';
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
        $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0;
        $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : $campo_filtro_padrao;
        $filtro = validaVarPost('filtro');
        ?>
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

            <tr>
                <td class="legenda tdbl tdbt" align="right">
                    <select name="opcoes">
                        <option value="<? echo $campo_filtro_padrao ?>"<? if ($opcoes == $campo_filtro_padrao) {echo 'selected';}?>>Ticket</option>
                    </select>
                </td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text"
                    name="filtro" size="60" value="<?
                    echo $filtro?>"></td>
            </tr>

            <tr>
                <td align="right" class="tdbl tdbb tdbr" colspan="3"><input
                    class="botaoAzul" type="submit" value="Buscar"></td>
            </tr>

        </table>

        <input type="hidden" name="acao" value="buscar"></form>

        <br>

        <?
        
        $con = conectar_bd();
        
        $sql_buscar_registros = "SELECT * FROM ipi_ideias WHERE $opcoes LIKE '%$filtro%' $sessao ";
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY situacao Limit ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
       
        //echo $sql_buscar_registros;

        echo "<center><b>" . $num_buscar_registros . " registro(s) encontrado(s)</center></b><br>";
        
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
         desconectar_bd($con);
        
        ?>

        <br>

        <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">

        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <tr>
                <td><input class="botaoAzul" type="submit"
                    value="Excluir Selecionados"></td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
                    <td align="center" width="50">Título</td>
                    <td align="center" width="80">Autor</td>
                    <td align="center" width="80">Ticket</td>
                    <td align="center" width="30">Publico</td>
                    <td align="center" width="20">Tempo de Desenvolvimento (horas)</td>
                    <td align="center" width="80">Data de Criação</td>
                    <td align="center" width="80">Data Prevista para Analise</td>
                    <td align="center" width="80">Data Prevista</td>
                    <td align="center" width="30">Situação</td>
                </tr>
            </thead>
            <tbody>
            <?
                     $con = conectar_bd();

          
          //$sql_buscar_registros  = "SELECT * FROM $tabela";

          //$res_buscar_registros = mysql_query($sql_buscar_registros);

            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {


            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$obj_buscar_registros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$obj_buscar_registros->$chave_primaria.')">'.bd2texto($obj_buscar_registros->titulo_ideia).'</a></td>';
             echo '<td align="center"><a href="javascript:;" onclick="editar('.$obj_buscar_registros->$chave_primaria.')">'.bd2texto($obj_buscar_registros->autor_ideia).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$obj_buscar_registros->$chave_primaria.')">'.bd2texto($obj_buscar_registros->cod_tickets).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$obj_buscar_registros->$chave_primaria.')">'.bd2texto($obj_buscar_registros->publico).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$obj_buscar_registros->$chave_primaria.')">'.($obj_buscar_registros->tempo_desenvolvimento).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$obj_buscar_registros->$chave_primaria.')">'.bd2data($obj_buscar_registros->data_hora_ideia).'</a></td>';
                 
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$obj_buscar_registros->$chave_primaria.')">'.bd2data($obj_buscar_registros->data_prevista_analise).'</a></td>';
                 
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$obj_buscar_registros->$chave_primaria.')">'.bd2data($obj_buscar_registros->data_prevista).'</a></td>';
                 
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$obj_buscar_registros->$chave_primaria.')">'.bd2texto($obj_buscar_registros->situacao).'</a></td>';
                 

            
            echo '</tr>';
            }
             desconectar_bd($con);
            ?>
            </tbody>
        </table>

        <input type="hidden" name="acao" value="excluir">
        </form>

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
            <li><a href="#">Atalho 1</a></li>
            <li><a href="#">Atalho 2</a></li>
        </ul>
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
    
    if ($codigo > 0)
    {
        $obj_editar = executaBuscaSimples("SELECT * FROM $tabela  WHERE $chave_primaria = $codigo");
    }

    ?>
     
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this) " class= "listaEdicao">
 
<table align="center" class="caixa" cellpadding="0" cellspacing="0">


     <tr>         <td class="legenda tdbl tdbt tdbr" align="center" font-size="10px" 
bgcolor="#EB8612"><h1 >Cadastrar Nova Idéia</h1></td>
</tr>     <tr>

     <? if($codigo>0): ?>
      <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label for="autor_ideia" class="requerido">Autor</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text" name="autor_ideia" id="autor_ideia" maxlength="40" size="30"  value="<? echo ($obj_editar->autor_ideia)?>" disabled></td>
    </tr>
    <tr>
     <tr>
        <td align='left'>
          <label  for="data_hora_ideia" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dia de Criação</label><br /><tr/>
          <tr>
          <td class="tdbl tdbr sep"><input  type="text" name="data_hora_ideia" id="data_hora_ideia" maxlength="100" size="20" value="<? echo bd2data($obj_editar->data_hora_ideia) ;?>" disabled></td></tr>
        </td>
     </tr>
      <tr>
        <td align='left'>
          <label >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Data Prevista Análise</label><br /><tr/>
          <tr>
          <td class="legenda tdbl tdbr"><input  type="text" name="data_prevista_analise" id="data_prevista_analise" maxlength="100" size="20" onkeypress="return MascaraData(this, event)" value="<? echo bd2data($obj_editar->data_prevista_analise) ;?>"></td></tr>
        </td>
     </tr>
        <tr>
        <td align='left'>
          <label >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Data Prevista</label><br /><tr/>
          <tr>
          <td class="legenda tdbl tdbr"><input  type="text" name="data_prevista" id="data_prevista" maxlength="100" size="20" onkeypress="return MascaraData(this, event)"value="<? echo bd2data($obj_editar->data_prevista) ;?>"></td></tr>
        </td>
     </tr>
      <? endif;?>
  

    
    <tr>
        <td class="legenda tdbl tdbr"><label  for="cod_ticket">Ticket</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input  type="text" name="cod_tickets" id="cod_tickets" maxlength="100" size="45" value="<? echo ($obj_editar->cod_tickets)?>"></td>
    </tr>
    
      

    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label for="titulo_ideia" class="requerido">Titulo</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text" name="titulo_ideia" id="titulo_ideia" maxlength="100" size="30"  value="<? echo ($obj_editar->titulo_ideia)?>"></td>
    </tr>
    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label  for="mensagem_ideia" class="requerido">Descrição de Idéia</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep" colspan="2"><textarea class="requerido"   rows="10" cols="50"  name="mensagem_ideia" id="mensagem_ideia "> <? echo ($obj_editar->mensagem_ideia)?></textarea></td>
    </tr>    
    
    <tr>
        <td class="legenda tdbl tdbr"><label  for="tempo_desenvolvimento"  >Tempo</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input type="text" name="tempo_desenvolvimento" id="tempo_desenvolvimento"   maxlength="15" size="15"  value="<? echo ($obj_editar->tempo_desenvolvimento)?>"></td>
    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label for= "situacao" class="requerido"  >Situacao</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr" >

            <select name="situacao" class="requerido" id="situacao" >
                <option value="" ></option>
                <option value="NOVO" <? if($obj_editar->situacao == 'NOVO') echo 'SELECTED' ?>>NOVO</option>
                <option value="ANÁLISE"<? if($obj_editar->situacao == 'ANÁLISE') echo 'SELECTED' ?>>EM ANÁLISE</option>  
                <option value="EXECUÇÃO" <? if($obj_editar->situacao == 'EXECUÇÃO') echo 'SELECTED' ?>>EM EXECUÇÃO</option>  
                <option value="CONCLUÍDA"  <? if($obj_editar->situacao == 'CONCLUÍDA') echo 'SELECTED' ?>>CONCLUÍDA</option>
            </select>
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label for="publico" class="requerido"   >Publico</label></td>
    </tr>
    
        <td class="tdbl tdbr" colspan="2">
            <select   id="publico" name="publico" class="requerido"  >
                <option id="situacao" value="" ></option>
                <option value="SIM" <? if($obj_editar->publico== 'SIM') echo 'SELECTED' ?>>SIM</option>  
                <option value="NAO" <? if($obj_editar->publico == 'NAO') echo 'SELECTED' ?>>NÃO</option>
            </select>
        </td>
    </tr>
      <tr>
        <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
            class="botao" type="submit" value="Cadastrar"></td>
    </tr>
    <tr>
<td><span id="clock"></span>

  </td>
    </tr>


</table>

<input type="hidden" name="acao" value="editar"> 
<input type="hidden" name="<? echo $chave_primaria?>" value="<?  echo $codigo?>">

</form>


</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>