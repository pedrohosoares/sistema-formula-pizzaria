<?php

/**
 * ipi_combos_produtos.php: Cadastro de Produtos no Combo
 * 
 * Índice: cod_combos_produtos
 * Tabela: ipi_combos_produtos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Produtos dos Combos Promocionais');

$acao = validaVarPost('acao');
$acao2 = validaVarPost('acao2');

if ($acao2)
{
  $acao = $acao2;
}

$tabela = 'ipi_combos_produtos';
$chave_primaria = 'cod_combos';
$quant_pagina = 80;

switch($acao) 
{
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM $tabela WHERE cod_combos_produtos IN ($indicesSql)";
    //echo $codigo." - ".$SqlDel;
    
    if (mysql_query($SqlDel))
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Produto já foi vendido e não pode ser excluído!');
    
    desconectabd($con);
  break;
  case 'cadastrar':
    
    $quantidade = validaVarPost('quantidade');
    $cod_tamanhos = validaVarPost('cod_tamanhos');
    $cod_conteudos = validaVarPost('cod_conteudos');
    $tipo_sabor = validaVarPost('tipo_sabor');
    $produto = validaVarPost('produto');
    $cod_combos = validaVarPost('cod_combos');
    
    $con = conectabd();

    $sql_inserir = sprintf("INSERT INTO $tabela (cod_conteudos, cod_tamanhos, cod_combos, quantidade, tipo, sabor) VALUES ('%d', '%d', '%d', '%d', '%s','%s')", $cod_conteudos, $cod_tamanhos, $cod_combos, $quantidade, $produto, $tipo_sabor);
    $res_inserir = mysql_query($sql_inserir);
    $cod_combos_produtos = mysql_insert_id();
    //echo "<br>1: ".$sql_inserir;

    if ($tipo_sabor == "-1")
    {
      $sql_inserir = sprintf("INSERT INTO ipi_combos_produtos_pizzas (cod_combos_produtos, selecionar_produto) VALUES ('%d', 'PIZZA_SEMANA')", $cod_combos_produtos);
      //echo "<br>2: ".$sql_inserir;
      $res_inserir = mysql_query($sql_inserir);

    }

    if ($res_inserir)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao incluir os registros', 'Erro no cadastro do combo!');
    
    desconectabd($con);
    
  break;

  case 'incluir_produto':
    $cod_pizzas = validar_var_post('add_cod_pizzas');
    $cod_bebidas_ipi_conteudos = validar_var_post('add_cod_bebidas_ipi_conteudos');
    $cod_combos_produtos = validar_var_post('add_cod_combos_produtos');
    
    $con = conectabd();

    $sql_inserir = sprintf("INSERT INTO ipi_combos_produtos_pizzas (cod_combos_produtos, cod_pizzas, selecionar_produto) VALUES ('%d', '%d', 'POR_CODIGO')", $cod_combos_produtos, $cod_pizzas);
    //echo "<br>1: ".$sql_inserir;
    if (mysql_query($sql_inserir))
      mensagemOk('Incluidos com sucesso!');
    else
      mensagemErro('Erro ao incluir os registros', 'Erro no cadastro do combo!');
    
    desconectabd($con);
  break;

  case 'incluir_bebida':
    $cod_pizzas = validar_var_post('add_cod_pizzas');
    $cod_bebidas_ipi_conteudos = validar_var_post('add_cod_bebidas_ipi_conteudos');
    $cod_combos_produtos = validar_var_post('add_cod_combos_produtos');
    
    $con = conectabd();

    $sql_inserir = sprintf("INSERT INTO ipi_combos_produtos_bebidas (cod_combos_produtos, cod_bebidas_ipi_conteudos) VALUES ('%d', '%d')", $cod_combos_produtos, $cod_bebidas_ipi_conteudos);
    //echo "<br>1: ".$sql_inserir;
    
    if (mysql_query($sql_inserir))
      mensagemOk('Incluidos com sucesso!');
    else
      mensagemErro('Erro ao incluir os registros', 'Erro no cadastro do combo!');
    
    desconectabd($con);
  break;

  case 'excluir_produto':
  echo '<input type="hidden" name="excluir_cod_pizzas" id="excluir_cod_pizzas" value="">';
  echo '<input type="hidden" name="excluir_cod_bebidas" id="excluir_cod_bebidas" value="">';

    $cod_combos_produtos_pizzas = validar_var_post('excluir_cod_pizzas');
    
    $con = conectabd();

    $sql_inserir = sprintf("DELETE FROM ipi_combos_produtos_pizzas WHERE  cod_combos_produtos_pizzas = '%d'", $cod_combos_produtos_pizzas);
    //echo "<br>1: ".$sql_inserir;
    if (mysql_query($sql_inserir))
      mensagemOk('Excluido com sucesso!');
    else
      mensagemErro('Erro ao incluir os registros', 'Erro no excluir produto do combo!');
    
    desconectabd($con);
  break;

  case 'excluir_bebida':

    $cod_combos_produtos_bebidas = validar_var_post('excluir_cod_bebidas');
    
    $con = conectabd();

    $sql_inserir = sprintf("DELETE FROM ipi_combos_produtos_bebidas WHERE  cod_combos_produtos_bebidas = '%d'", $cod_combos_produtos_bebidas);
    //echo "<br>1: ".$sql_inserir;
    if (mysql_query($sql_inserir))
      mensagemOk('Excluido com sucesso!');
    else
      mensagemErro('Erro ao incluir os registros', 'Erro no excluir produto do combo!');
    
    desconectabd($con);
  break;

}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>

<script type="text/javascript" src="../lib/js/calendario.js"></script>

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
    'action': '<? echo $_SERVER['PHP_SELF'] ?>',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': '<? echo $chave_primaria ?>',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}


function habilitar_campos(tipo) 
{

    if(tipo == 'PIZZA')
    {
        carregaTamanhos();
        $('tipo_sabor_legenda').setStyle('display', 'block');
        $('tipo_sabor_campo').setStyle('display', 'block');
        
        $('tamanho_legenda').setStyle('display', 'block');
        $('tamanho_campo').setStyle('display', 'block');
        
        $('cod_conteudos_legenda').setStyle('display', 'none');
        $('cod_conteudos_campo').setStyle('display', 'none');

        $('cod_conteudos').value = '';
    }
    else if(tipo == 'BEBIDA')
    {
        carregaConteudos();
        $('tipo_sabor_legenda').setStyle('display', 'none');
        $('tipo_sabor_campo').setStyle('display', 'none');
        
        $('tamanho_legenda').setStyle('display', 'none');
        $('tamanho_campo').setStyle('display', 'none');
        
        $('cod_conteudos_legenda').setStyle('display', 'block');
        $('cod_conteudos_campo').setStyle('display', 'block');

        $('tipo_sabor').value = '';
        $('cod_tamanhos').value = '';
    }
    else
    {
        $('cod_conteudos_legenda').setStyle('display', 'none');
        $('cod_conteudos_campo').setStyle('display', 'none');

        $('tipo_sabor_legenda').setStyle('display', 'none');
        $('tipo_sabor_campo').setStyle('display', 'none');

        $('tamanho_legenda').setStyle('display', 'none');
        $('tamanho_campo').setStyle('display', 'none');
    }

}

function carregaTamanhos() 
{
    var url = 'acao=carregar_tamanho';
  
    var produto = $('produto').getProperty('value');
    url += '&produto=' + produto;
  
    //var cod_produtos = $('cod_produtos').getProperty('value');
    //url += '&cod_produtos=' + cod_produtos;
  
    $('carregando_tamanhos').set('text', 'Carregando...');
  
    new Request.HTML({
        url: 'ipi_combos_produtos_ajax.php',
        update: $('cod_tamanhos'),
        onComplete: function() 
        {
            $('carregando_tamanhos').set('text', '');
        }
    }).send(url);
}


function carregaConteudos() 
{
    var url = 'acao=carregar_produto';
  
    var produto = $('produto').getProperty('value');
    url += '&produto=' + produto;
  
    //var cod_produtos = $('cod_produtos').getProperty('value');
    //url += '&cod_produtos=' + cod_produtos;
  
    $('carregando_tamanhos').set('text', 'Carregando...');
  
    new Request.HTML({
        url: 'ipi_combos_produtos_ajax.php',
        update: $('cod_conteudos'),
        onComplete: function() 
        {
            $('carregando_tamanhos').set('text', '');
        }
    }).send(url);
}

function habilitar_valor_minimo(tipo)
{
    if(tipo == true)
    {
        $('valor_minimo_legenda').setStyle('display', 'block');
        $('valor_minimo_campo').setStyle('display', 'block');
    }
    else
    {
        $('valor_minimo_legenda').setStyle('display', 'none');
        $('valor_minimo_campo').setStyle('display', 'none');
    }
}

window.addEvent('domready', function()
{
  var tabs = new Tabs('tabs');
  //new vlaDatePicker('data_validade', {prefillDate: false});
  if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) 
  {
    <? if (($acao == '')||($acao == 'cadastrar')||($acao == 'excluir')) echo 'tabs.irpara(1);'; ?>
    
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  else 
  {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  tabs.addEvent('change', function(indice)
  {
    var valor_chave='<? echo $chave_primaria ?>';
    if ( (indice == 1) && (valor_chave!='') ) 
    {
        tabs.irpara(0);
        alert("Você deve clicar em um combo para editar os produtos");
    }
    
  });
});

function gerar_imagem(combo)
{
		var url = 'acao=gerar_imagem';
  
    url += '&combo=' + combo;
  
    //var cod_produtos = $('cod_produtos').getProperty('value');
    //url += '&cod_produtos=' + cod_produtos;
  
    $('imagem_status').set('html', 'Carregando...');
  
    new Request.HTML({
        url: 'ipi_combos_produtos_ajax.php',
        update: $('imagem_Status'),
        onComplete: function() 
        {
            $('imagem_status').set('html', 'Imagem_Atualizada');
        }
    }).send(url);

}

</script>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a href="javascript:;">Listar</a></li>
       <li><a href="javascript:;">Editar</a></li>
    </ul>
  </div>
    
  <!-- Tab Listar -->
  <div class="painelTab">
    <table align="center" width="950"><tr>
  
    <!-- Conteúdo -->
    <td class="conteudo">
        
    <?
    $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 
    $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : 'nome';
    $filtro = validaVarPost('filtro');
    $tipo_criacao = validaVarPost('tipo_criacao');
    ?>        

    <form name="frmFiltro" method="post">
      <table align="center" class="caixa" cellpadding="0" cellspacing="0">
      
      <!-- 
      <tr>
        <td class="legenda tdbl" align="right"><label for="bairro">Bairro:</label></td>
        <td>&nbsp;</td>
        <td class="tdbr">
          <select name="bairro">
            <option value="TODOS" <? if($bairro == 'TODOS') echo 'selected' ?>>Todos os Bairros</option>
            <?
            $con = conectabd();
            
            $SqlBuscaBairros = "SELECT DISTINCT bairro FROM ipi_enderecos ORDER BY bairro";
            $resBuscaBairros = mysql_query($SqlBuscaBairros);
            
            while($objBuscaBairros = mysql_fetch_object($resBuscaBairros)) {
              echo '<option value="'.$objBuscaBairros->bairro.'" ';
              
              if($bairro == $objBuscaBairros->bairro)
                echo 'selected';
              
              echo '>'.bd2texto($objBuscaBairros->bairro).'</option>';
            }
            
            desconectabd($con);
            ?>
          </select>
        </td>
      </tr>
     
      <tr>
        <td class="legenda tdbl tdbt sep" align="right"><label for="tipo_criacao">Modo Criação:</label></td>
        <td class="sep tdbt">&nbsp;</td>
        <td class="tdbr sep tdbt">
          <select name="tipo_criacao">
            <option value="Todos" <? if($tipo_criacao == 'Todos') echo 'selected' ?>>Todos</option>
            <option value="Automatico" <? if($tipo_criacao == 'Automatico') echo 'selected' ?>>Automático</option>
            <option value="Manual" <? if($tipo_criacao == 'Manual') echo 'selected' ?>>Manual</option>
          </select>
        </td>
      </tr>

      <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
      
      </table>
      
       -->

      <input type="hidden" name="acao" value="buscar">
    </form>
    
    <br>    
    
        <?
        
        $con = conectabd();

        
        $SqlBuscaRegistros = "SELECT * FROM ipi_combos cmb ";
        $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
        $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);
        

        $SqlBuscaRegistros .= ' ORDER BY nome_combo LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
        $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
        $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);
        
        //echo $SqlBuscaRegistros;
        
        echo "<center><b>".$numBuscaRegistros." registro(s) encontrado(s)</center></b><br>";
        
        if ((($quant_pagina * $pagina) == $numBuscaRegistros) && ($pagina != 0) && ($acao == 'excluir')) $pagina--;
        
        echo '<center>';
        
        $numpag = ceil(((int) $numBuscaRegistros) / ((int) $quant_pagina));
        
        for ($b = 0; $b < $numpag; $b++) {
          echo '<form name="frmPaginacao'.$b.'" method="post">';
          echo '<input type="hidden" name="pagina" value="'.$b.'">';
          echo '<input type="hidden" name="filtro" value="'.$filtro.'">';
          echo '<input type="hidden" name="opcoes" value="'.$opcoes.'">';
          
          echo '<input type="hidden" name="bairro" value="'.$bairro.'">';
          echo '<input type="hidden" name="situacao_filtro" value="'.$situacao_filtro.'">';
          
          echo '<input type="hidden" name="acao" value="buscar">';
          echo "</form>";
        }
        
        if ($pagina != 0)
          echo '<a href="javascript:;" onclick="javascript:frmPaginacao'.($pagina - 1).'.submit();" style="margin-right: 5px;">&laquo;&nbsp;Anterior</a>';
        else
          echo '<span style="margin-right: 5px;">&laquo;&nbsp;Anterior</span>';
        
        for ($b = 0; $b < $numpag; $b++) {
          if ($b != 0)
            echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
          
          if ($pagina != $b)
            echo '<a href="javascript:;" onclick="javascript:frmPaginacao'.$b.'.submit();">'.($b + 1).'</a>';
          else
            echo '<span><b>'.($b + 1).'</b></span>';
        }
        
        if (($quant_pagina == $linhasBuscaRegistros) && ((($quant_pagina * $pagina) + $quant_pagina) != $numBuscaRegistros))
          echo '<a href="javascript:;" onclick="javascript:frmPaginacao'.($pagina + 1).'.submit();" style="margin-left: 5px;">Próxima&nbsp;&raquo;</a>';
        else
          echo '<span style="margin-left: 5px;">Próxima&nbsp;&raquo;</span>';
        
        echo '</center>';
        
        ?>    
    
    
      <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">
        
        <!--  
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
          <tr>
            <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
          </tr>
        </table>
         -->
         
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <!--  <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>  -->
              <td align="center">Nome do Combo</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) 
          {
                echo '<tr>';
                //echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
                echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->cod_combos.')">'.bd2texto($objBuscaRegistros->nome_combo).'</a></td>';
                echo '</tr>';
          }
          desconectabd($con);
          ?>
          
          </tbody>
        </table>
      
        <input type="hidden" name="acao" value="excluir">
      </form>
    
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
    
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="ipi_combos.php">Combos</a></li>
         </ul>
      </div>
    </td>
    
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Listar -->
  
  
  
  <!-- Tab Gerar -->
  <div class="painelTab">
    <? 
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    if (!$codigo)
        $codigo = validaVarPost('codigo');
    
    
    if($codigo > 0) {
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    } 
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0" width="350">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="quantidade">Quantidade:</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="quantidade" id="quantidade" maxlength="10" size="14" value="1" onkeypress="return ApenasNumero(event);"></td></tr>

    <tr><td id="tipo_produto_legenda" class="legenda tdbl tdbr"><label for="produto">Tipo de Produto</label></td></tr>
    <tr><td id="tipo_produto_campo" class="tdbl tdbr sep">
      <select name="produto" id="produto" onchange="habilitar_campos(this.value);">
        <option value=""></option>
        <option value="PIZZA" <? if($objBusca->produto == 'PIZZA') echo 'selected' ?>>Produto</option>
        <?php
        if (PRODUTO_USA_BORDA == 'S') 
        { 
          ?>
          <option value="BORDA" <? if($objBusca->produto == 'BORDA') echo 'selected' ?>>Borda</option>
          <?php
        }
        ?>
        <option value="BEBIDA" <? if($objBusca->produto == 'BEBIDA') echo 'selected' ?>>Bebida</option>
      </select>
      &nbsp;
      <!-- 
      <small id="carregando_produtos" style="color: red;"></small>
       -->
      <small id="carregando_tamanhos" style="color: red;"></small>
    </td></tr>
    
    <!-- 
    <tr><td id="produto_legenda" style="display: none" class="legenda tdbl tdbr"><label for="cod_produtos">Produto</label></td></tr>
    <tr><td id="produto_campo" style="display: none" class="tdbl tdbr">
      <select name="cod_produtos" id="cod_produtos" onchange="carregaTamanhos()">
        <option value=""></option>
      </select>
      &nbsp;
      <small id="carregando_tamanhos" style="color: red;"></small>
    </td></tr>
     -->
     
    <tr><td id="tamanho_legenda" style="display: none" class="legenda tdbl tdbr"><label for="cod_tamanhos">Tamanho</label></td></tr>
    <tr><td id="tamanho_campo" style="display: none" class="tdbl tdbr sep">
      <select name="cod_tamanhos" id="cod_tamanhos">
        <option value=""></option>
      </select>
    </td></tr>

    <tr><td id="tipo_sabor_legenda" style="display: none" class="legenda tdbl tdbr"><label for="tipo_sabor">Tipo</label></td></tr>
    <tr><td id="tipo_sabor_campo" style="display: none" class="tdbl tdbr sep">
      <select name="tipo_sabor" id="tipo_sabor">
        <option value=""></option>
        <?php
          $con = conectabd();
          $sql_buscar_tipos = "SELECT tipo_pizza, cod_tipo_pizza from ipi_tipo_pizza";
          $res_buscar_tipos = mysql_query($sql_buscar_tipos);

          echo "<option value='-1'>Pizza da Semana</option>";
          while($obj_buscar_tipos = mysql_fetch_object($res_buscar_tipos))
          {
            echo "<option value='".$obj_buscar_tipos->cod_tipo_pizza."'>".$obj_buscar_tipos->tipo_pizza."</option>";
          }
        ?>
<!--
          <option value="Salgado">Salgado</option>
          <option value="Doce">Doce</option> 
-->
      </select>
    </td></tr>
     
    <tr><td id="cod_conteudos_legenda" style="display: none" class="legenda tdbl tdbr"><label for="cod_conteudos">Conteudo</label></td></tr>
    <tr><td id="cod_conteudos_campo" style="display: none" class="tdbl tdbr sep">
      <select name="cod_conteudos" id="cod_conteudos">
        <option value=""></option>
      </select>
    </td></tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar">
    </td></tr>
    </table>
    
    <input type="hidden" name="acao" value="cadastrar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
    <!-- <input name="botao_imagem" class="botao" type="button" onclick="gerar_imagem(<?// echo $codigo ?> )" value="Gerar Imagem do Combo"><div id="imagem_status"></div>-->
    
    <br>
    
    <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">
      
      <center>

      <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" style="width: 900px">
        <tr>
          <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
        </tr>
      </table>
      
      <table class="listaEdicao" cellpadding="0" cellspacing="0" style="width: 900px">
        <thead>
          <tr>
            <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
            <td align="center">Quantidade</td>
            <td align="center">Tipo Produto</td>
            <td align="center">Tipo Sabor</td>
            <td align="center">Tamanhos</td>
            <td align="center">Lista de Produtos</td>
          </tr>
        </thead>
        <tbody>
        
        <?
        $SqlBuscaRegistros = "SELECT cp.*, t.tamanho tamanho_pizza, co.conteudo tamanho_bebida,tipo_pi.tipo_pizza FROM ipi_combos_produtos cp LEFT JOIN ipi_tamanhos t ON (cp.cod_tamanhos=t.cod_tamanhos) LEFT JOIN ipi_conteudos co ON (cp.cod_conteudos=co.cod_conteudos) LEFT JOIN ipi_tipo_pizza tipo_pi on tipo_pi.cod_tipo_pizza = cp.sabor WHERE cp.cod_combos=".$codigo." ORDER BY cod_combos_produtos";
        $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
        $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);
        //echo "<br>".$SqlBuscaRegistros;
        for ($a=0; $a<$numBuscaRegistros; $a++)
        {
          $objBuscaRegistros = mysql_fetch_object($resBuscaRegistros);
          echo '<tr>';
          echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->cod_combos_produtos.'"></td>';
          echo '<td align="center">'.bd2texto($objBuscaRegistros->quantidade).'</td>';
          echo '<td align="center">'.(bd2texto($objBuscaRegistros->tipo)=="PIZZA"?'PRODUTO':$objBuscaRegistros->tipo).'</td>';
          echo '<td align="center">'.bd2texto($objBuscaRegistros->tipo_pizza).'</td>';
          echo '<td align="center">'.bd2texto($objBuscaRegistros->tamanho_pizza.$objBuscaRegistros->tamanho_bebida).'</td>';
          echo '<td>';

          if ($objBuscaRegistros->tipo=="PIZZA")
          {
            $sql_produtos_pizzas= "SELECT cpp.selecionar_produto, cpp.cod_combos_produtos_pizzas, tp.tipo_pizza, p.pizza FROM ipi_combos_produtos_pizzas cpp LEFT JOIN ipi_pizzas p ON (p.cod_pizzas = cpp.cod_pizzas) LEFT JOIN ipi_tipo_pizza tp ON (tp.cod_tipo_pizza = p.cod_tipo_pizza) WHERE cod_combos_produtos = '".$objBuscaRegistros->cod_combos_produtos."'";
            $res_produtos_pizzas = mysql_query($sql_produtos_pizzas);
            while ($obj_produtos_pizzas = mysql_fetch_object($res_produtos_pizzas) )
            {
              echo ' <input type="button" class="botaoVermelho" name="bt_enviar" value="X" style="background-color: #FD9999;" onClick="excluir_produtos(\'PIZZA\', '.$obj_produtos_pizzas->cod_combos_produtos_pizzas.')"> ';
              if ($obj_produtos_pizzas->selecionar_produto == "POR_CODIGO")
              {
                echo $obj_produtos_pizzas->tipo_pizza." - ".$obj_produtos_pizzas->pizza."<br />";
              }
              elseif ($obj_produtos_pizzas->selecionar_produto == "PIZZA_SEMANA")
              {
                echo "Pizza da Semana<br />";
              }
            }

            $sqlPizzas= "SELECT p.cod_pizzas, p.cod_tipo_pizza, p.pizza, p.codigo_cliente_pizza, p.tipo, tp.tipo_pizza, p.codigo_cliente_pizza, p.venda_online  FROM ipi_pizzas p LEFT JOIN ipi_tipo_pizza tp ON (p.cod_tipo_pizza= tp.cod_tipo_pizza)";
            if ($sabor)
            {
              $sqlPizzas .= " AND p.cod_tipo_pizza='".$sabor."'";
            }
            if ($cod_tipo_pizza)
            {
              $sqlPizzas .= " AND p.cod_tipo_pizza = '".$cod_tipo_pizza."'";
            }
            $sqlPizzas .= " ORDER BY tp.tipo_pizza, p.pizza ASC, p.codigo_cliente_pizza ASC";
            //echo $sqlPizzas;
            $resPizzas = mysql_query($sqlPizzas);
            $linPizzas = mysql_num_rows($resPizzas);
            echo '<select name="cod_pizzas'.$a.'" id="cod_pizzas'.$a.'">';
            if ($linPizzas > 0)
            {
              $tipo="";
              for ($b = 0; $b < $linPizzas; $b++)
              {
                $objPizzas = mysql_fetch_object($resPizzas);
              if ($tipo != $objPizzas->tipo_pizza)
                {
                  if ($tipo!="") echo '</optgroup>';

                  echo '<optgroup label="'. $objPizzas->tipo_pizza .'">';
                  $tipo= $objPizzas->tipo_pizza;
                }                
                echo ('<option value="' . $objPizzas->cod_pizzas . '">' . $objPizzas->codigo_cliente_pizza . " - " . $objPizzas->pizza . '</option>');
              }
            }
            echo '</optgroup>';
            echo '</select>';

            echo ' <input type="button" class="botaoAzul" name="bt_enviar" value="+" onClick="adicionar_produtos(\'PIZZA\', '.$objBuscaRegistros->cod_combos_produtos.', \'cod_pizzas'.$a.'\')">';


          }
          else if ($objBuscaRegistros->tipo=="BEBIDA")
          {
            $sql_produtos_bebidas= "SELECT cpb.cod_combos_produtos_bebidas, b.bebida, c.conteudo FROM ipi_combos_produtos_bebidas cpb LEFT JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas_ipi_conteudos = cpb.cod_bebidas_ipi_conteudos) LEFT JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) LEFT JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) WHERE cod_combos_produtos = '".$objBuscaRegistros->cod_combos_produtos."'";
            //echo $sql_produtos_bebidas;
            $res_produtos_bebidas = mysql_query($sql_produtos_bebidas);
            while ($obj_produtos_bebidas = mysql_fetch_object($res_produtos_bebidas) )
            {
              echo ' <input type="button" class="botaoVermelho" name="bt_enviar" value="X" style="background-color: #FD9999;" onClick="excluir_produtos(\'BEBIDA\', '.$obj_produtos_bebidas->cod_combos_produtos_bebidas.')"> ';
              echo $obj_produtos_bebidas->bebida." - ".$obj_produtos_bebidas->conteudo."<br />";
            }

            $sql_bebidas = "SELECT bc.cod_bebidas_ipi_conteudos, bc.codigo_cliente_bebida, b.bebida, c.conteudo, bc.situacao, tb.tipo_bebida FROM ipi_bebidas_ipi_conteudos bc LEFT JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) LEFT JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) LEFT JOIN ipi_tipo_bebida tb ON (b.cod_tipo_bebida= tb.cod_tipo_bebida) ORDER BY bc.codigo_cliente_bebida ASC, tb.tipo_bebida, b.bebida, c.conteudo";
            $res_bebidas = mysql_query($sql_bebidas);
            //echo $sql_bebidas;
            echo '<select name="cod_bebidas_ipi_conteudos'.$a.'" id="cod_bebidas_ipi_conteudos'.$a.'">';
            $tipo = "";
            while ($obj_bebidas = mysql_fetch_object($res_bebidas))
            {
              if ($tipo != $obj_bebidas->tipo_bebida)
              {
                if ($tipo!="") echo '</optgroup>';

                echo '<optgroup label="'. $obj_bebidas->tipo_bebida .'">';
                $tipo= $obj_bebidas->tipo_bebida;
              }
              echo ('<option value="' . $obj_bebidas->cod_bebidas_ipi_conteudos . '">'  . $obj_bebidas->codigo_cliente_bebida . " - " . $obj_bebidas->bebida. " - ".$obj_bebidas->conteudo . '</option>');
              
            }
            echo '</optgroup>';
            echo '</select>';
            echo ' <input type="button" class="botaoAzul" name="bt_enviar" value="+" onClick="adicionar_produtos(\'BEBIDA\', '.$objBuscaRegistros->cod_combos_produtos.',\'cod_bebidas_ipi_conteudos'.$a.'\' )">';

          }
          else
          {
            //TODO
          }
          echo '</td>';
          echo '</tr>';
        }
        ?>
        
        </tbody>
      </table>
      </center>
      <input type="hidden" name="acao" value="excluir">
      <input type="hidden" name="codigo" value="<? echo $codigo ?>">
    </form>

<script>
function adicionar_produtos(tipo, cod_combos_produtos, id_produto)
{
  if ($(id_produto).value != "")
  {
    if (tipo == "BEBIDA")
    {
      $('acao').value = 'incluir_bebida';
      $('add_cod_bebidas_ipi_conteudos').value = $(id_produto).value;
    }
    else if (tipo == "PIZZA")
    {
      $('acao').value = 'incluir_produto';
      $('add_cod_pizzas').value = $(id_produto).value;
    }
    else
    {
      //TODO
    }
    $('add_cod_combos_produtos').value = cod_combos_produtos;

    document.frm_add_produtos.submit();
  }
  else
  {
    alert("Para adicionar selecione um produto!");
  }
}

function excluir_produtos(tipo, cod_produto)
{
  if ( confirm("Deseja realmente excluir este produto?") == true )
  {
    if (tipo == "BEBIDA")
    {
      $('acao2').value = 'excluir_bebida';
      $('excluir_cod_bebidas').value = cod_produto;
    }
    else if (tipo == "PIZZA")
    {
      $('acao2').value = 'excluir_produto';
      $('excluir_cod_pizzas').value = cod_produto;
    }
    else
    {
      //TODO
    }

    document.frm_excluir_produtos.submit();
  }
}
</script>

<?
echo '<form name="frm_excluir_produtos" method="post">';
echo '<input type="hidden" name="excluir_cod_pizzas" id="excluir_cod_pizzas" value="">';
echo '<input type="hidden" name="excluir_cod_bebidas" id="excluir_cod_bebidas" value="">';
echo '<input type="hidden" id="acao2" name="acao2" value="">';
echo '</form>';

echo '<form name="frm_add_produtos" method="post">';
echo '<input type="hidden" name="add_cod_pizzas" id="add_cod_pizzas" value="">';
echo '<input type="hidden" name="add_cod_bebidas_ipi_conteudos" id="add_cod_bebidas_ipi_conteudos" value="">';
echo '<input type="hidden" id="acao" name="acao" value="">';
echo '<input type="hidden" name="add_cod_combos_produtos" id="add_cod_combos_produtos" value="">';
echo '</form>';
desconectabd($con);
?>
    
  </div>
  <!-- Tab Gerar -->
    
 </div>

<? rodape(); ?>
