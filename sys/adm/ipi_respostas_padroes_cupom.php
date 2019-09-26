<?php

/**
 * Cadastro de Precupoms utilizados na resposta padrao.
 *
 * @version 1.0
 * @package iti
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       31/10/2012   Filipe         Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Precadastro de Cupoms para Respostas');

$acao = validaVarPost('acao');

$tabela = 'ipi_respostas_cupom';
$chave_primaria = 'cod_respostas_cupom';
$quant_pagina = 80;

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indices_sql = implode(',', $excluir);
    
    $conexao = conectabd();
    
    $sql_del = "UPDATE $tabela SET situacao='EXCLUIDO' WHERE $chave_primaria IN ($indices_sql)";
    
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
    $data_validade = data2bd(validaVarPost('data_validade'));
    $quantidade = validaVarPost('quantidade');
    
    $produto = validaVarPost('produto');
    $cod_produtos = validaVarPost('cod_produtos');
    $cod_tamanhos = validaVarPost('cod_tamanhos');

    $nome_cupom = validaVarPost('nome_cupom');

    $cod_pizzarias = validaVarPost('cod_pizzarias');

    $obs_cupom = validaVarPost('obs_cupom');

    $dias_validos = validaVarPost('dias_validos');

    $promocao = (validaVarPost('reutilizavel') == 'on') ? 1 : 0; 

    $necessita_compra = (validaVarPost('necessita_compra') == 'on') ? 1 : 0; 
    
    $tipo_cupom = validaVarPost('tipo_cupom');
    
    $valor_minimo_compra = moeda2bd(validaVarPost('valor_minimo'));
    
    $generico = ($tipo_cupom == 'GENERICO') ? 1 : 0;
    $situacao = validaVarPost('situacao');
    $situacao = "ATIVO";
        $con = conectabd();
    if($tipo_cupom == 'GENERICO') {
      $cod_produtos = 0;
    }


    if($nome_cupom=="")
    {
      $nome_cupom = $produto;

      if($cod_tamanhos)
      {
        $sql_pega_tamanho = "SELECT * from ipi_tamanhos ";
        $res_pega_tamanho = mysql_query($sql_pega_tamanho);
        while($obj_pega_tamanho = mysql_fetch_object($res_pega_tamanho))
        {
          $cod_tamanhos_sql = $obj_pega_tamanho->cod_tamanhos;
          $nome_tamanho = explode('(',$obj_pega_tamanho->tamanho);
          $nome_tamanho = $nome_tamanho[0];
          $arr_tamanhos[$cod_tamanhos_sql] = $nome_tamanho;
        }
        $nome_cupom .= ' '.strtoupper($arr_tamanhos[$cod_tamanhos]);
      }

      if(!$cod_produtos) 
      {
        $nome_cupom .= ' GENERICA';
      }
      else
      {
        if($cod_produtos)
        {
          if ($produto == 'PIZZA')
          {
              $sqlBuscaPizza = "SELECT * FROM ipi_pizzas WHERE cod_pizzas = ".$cod_produtos." ORDER BY pizza";
              $resBuscaPizza = mysql_query($sqlBuscaPizza);
              $objBuscaPizza = mysql_fetch_object($resBuscaPizza);
              $nome_cupom .= ' '.strtoupper(bd2texto($objBuscaPizza->pizza));
          }
          else if ($produto == 'BORDA')
          {
              $sqlBuscaBorda = "SELECT * FROM ipi_bordas WHERE cod_bordas = ".$cod_produtos." ORDER BY borda";
              $resBuscaBorda = mysql_query($sqlBuscaBorda);
              $objBuscaBorda = mysql_fetch_object($resBuscaBorda);
              $nome_cupom .= ' '.strtoupper(bd2texto($objBuscaBorda->borda));
          }
          else if ($produto == 'BEBIDA')
          {
              $sqlBuscaBebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_conteudos_pizzarias cp on cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos WHERE bc.cod_bebidas_ipi_conteudos = ".$cod_produtos." GROUP BY b.bebida ORDER BY b.bebida";
              //echo $sqlBuscaBebidas;
              $resBuscaBebidas = mysql_query($sqlBuscaBebidas);
              $objBuscaBebidas = mysql_fetch_object($resBuscaBebidas);
              $nome_cupom .=  ' '.strtoupper(bd2texto($objBuscaBebidas->bebida . ' ' . $objBuscaBebidas->conteudo));
          }
        }else
        $nome_cupom .= ' GENERICA';
      }
      if($necessita_compra)
      {
         $nome_cupom .= ' - C/ COMPRA R$ '.bd2moeda($valor_minimo_compra);
      }
      else
      {
        $nome_cupom .= ' - S/ COMPRA';
      }
    }


    if($codigo <=0)
    {
      $sql_inserir = "INSERT into ipi_respostas_cupom(nome_cupom,produto,cod_produtos,cod_tamanhos,dias_validos,necessita_compra,valor_minimo_compra,generico,situacao) values ('".$nome_cupom."','".$produto."',".$cod_produtos.",'".$cod_tamanhos."','".$dias_validos."','".$necessita_compra."','".$valor_minimo_compra."','".$generico."','".$situacao."')";
      //echo $sql_inserir."<br/>";
      $res_inserir = mysql_query($sql_inserir);
    }else
    {
      $sql_inserir = sprintf("UPDATE ipi_respostas_cupom set nome_cupom = '%s', produto = '%s' , cod_produtos = %d ,cod_tamanhos = %d , dias_validos = %d , necessita_compra = %d, valor_minimo_compra = '%s', generico = %d , situacao = '%s' where $chave_primaria = $codigo",$nome_cupom,$produto,$cod_produtos,$cod_tamanhos,$dias_validos,$necessita_compra,$valor_minimo_compra,$generico,$situacao);
      $res_inserir = mysql_query($sql_inserir);
    }
    
    
    if($res_inserir)
    {
      mensagemOk('Cupons gerados com êxito!');
    }
    else
    {
      mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
      
    desconectabd($con);
  break;
}

?>
<script type="text/javascript" src="../lib/js/Picker.js" /></script>
<script type="text/javascript" src="../lib/js/Picker.Attach.js" ></script>
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

function carregaProdutos(cod_produtos) 
{
    var url = 'acao=carregar_produto';
    
    var produto = $('produto').getProperty('value');
    url += '&produto=' + produto;

    cod_produtos = (cod_produtos ? cod_produtos : '');
    if(cod_produtos)
    {
      url += '&cod_produtos=' + cod_produtos;
    }
    $('carregando_produtos').set('text', 'Carregando...');
    
    new Request.HTML({
    url: 'ipi_respostas_pradoes_cupom_ajax.php',
    update: $('cod_produtos'),
    onComplete: function() {
          $('carregando_produtos').set('text', '');
          
          if((document.frmIncluir.tipo_cupom.value == 'GENERICO') && (document.frmIncluir.produto.value != 'BEBIDA')) 
          {
              carregaTamanhos();
          }
          else
          {      
              if(document.frmIncluir.produto.value == 'BEBIDA') 
              {
                  document.frmIncluir.cod_tamanhos.value = '';
                  document.frmIncluir.cod_tamanhos.disabled = 'disabled';
              }
              else 
              {
                  document.frmIncluir.cod_tamanhos.disabled = '';
              }
          }
        }
    }).send(url);
}

function carregaTamanhos(cod_tamanhos) {
  var url = 'acao=carregar_tamanho';
  
  var produto = $('produto').getProperty('value');
  url += '&produto=' + produto;
  
  var cod_produtos = $('cod_produtos').getProperty('value');
  url += '&cod_produtos=' + cod_produtos;

  cod_tamanhos = (cod_tamanhos ? cod_tamanhos : '');
  if(cod_tamanhos)
  {
    url += '&cod_tamanhos='+cod_tamanhos;
  }
  $('carregando_tamanhos').set('text', 'Carregando...');
  
  new Request.HTML({
    url: 'ipi_respostas_pradoes_cupom_ajax.php',
    update: $('cod_tamanhos'),
    onComplete: function() {
      $('carregando_tamanhos').set('text', '');
    }
  }).send(url);
}

function habilitar_tipo_produto(tipo) {
    if(tipo == 'GENERICO')
    {
        $('tipo_produto_legenda').setStyle('display', 'block');
        $('tipo_produto_campo').setStyle('display', 'block');
        $('tipo_produto_campo').addClass('sep');
        
        $('produto_legenda').setStyle('display', 'none');
        $('produto_campo').setStyle('display', 'none');
        
        $('tamanho_legenda').setStyle('display', 'block');
        $('tamanho_campo').setStyle('display', 'block');
    }
    else if(tipo == 'ESPECIFICO')
    {
        $('tipo_produto_legenda').setStyle('display', 'block');
        $('tipo_produto_campo').setStyle('display', 'block');
        $('tipo_produto_campo').removeClass('sep');
        
        $('produto_legenda').setStyle('display', 'block');
        $('produto_campo').setStyle('display', 'block');
        
        $('tamanho_legenda').setStyle('display', 'block');
        $('tamanho_campo').setStyle('display', 'block');
    }
    else
    {
        $('tipo_produto_legenda').setStyle('display', 'none');
        $('tipo_produto_campo').setStyle('display', 'none');
        
        $('produto_legenda').setStyle('display', 'none');
        $('produto_campo').setStyle('display', 'none');
        
        $('tamanho_legenda').setStyle('display', 'none');
        $('tamanho_campo').setStyle('display', 'none');
    }

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

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs');
  
  //new vlaDatePicker('data_validade', {prefillDate: false});
  new vlaDatePicker('data_validade', {openWith: 'botao_data_validade', prefillDate: false});
  
  if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) {
    <? if ($acao == '') echo 'tabs.irpara(1);'; ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.<? echo $chave_primaria ?>.value = '';
      document.frmIncluir.data_validade.value = '';
      document.frmIncluir.produto.value = '';
      document.frmIncluir.cod_produtos.value = '';
      document.frmIncluir.cod_tamanhos.value = '';
      //document.frmIncluir.promocao.checked = true;
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
});

</script>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a href="javascript:;">Listar</a></li>
       <li><a href="javascript:;">Gerar</a></li>
    </ul>
  </div>
    
  <!-- Tab Listar -->
  <div class="painelTab">
    <table align="center" ><tr>
  
    <!-- Conteúdo -->
    <td class="conteudo">
        
    <?
    $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 
    $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : 'nome';
    $filtro = validaVarPost('filtro');
    $tipo_cupom = validaVarPost('tipo_cupom');
    $situacao = (validaVarPost('situacao') ? validaVarPost('situacao') : 'Todos' );
    $necessita = validaVarPost('necessita');
    
    
    ?>        

    <form name="frmFiltro" method="post">
      <table align="center" class="caixa" cellpadding="0" cellspacing="0">
      
      <!-- 
      <tr>
        <td class="legenda tdbl tdbt" align="right">
          <select name="opcoes">
            <option value="cupom" <? if($opcoes == 'cupom') echo 'selected' ?>>Cupom</option>
          </select>
        </td>
        <td class="tdbt">&nbsp;</td>
        <td class="tdbt tdbr">
          <input type="text" name="filtro" size="60" value="<? echo $filtro ?>">
        </td>
      </tr>
       -->
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
       -->
      <tr>
        <td class="legenda tdbl tdbt sep" align="right"><label for="necessita">Necessita compra?</label></td>
        <td class="sep tdbt ">&nbsp;</td>
        <td class="tdbr sep tdbt ">
          <select name="necessita">
            <option value="Todos" <? if($necessita == 'Todos') echo 'selected' ?>>Todos</option>
            <option value="Sim" <? if($necessita == 'Sim') echo 'selected' ?>>Sim</option>
            <option value="Não" <? if($necessita == 'Não') echo 'selected' ?>>Não</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="legenda tdbl sep" align="right"><label for="tipo_cupom">Tipo Cupom:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
          <select name="tipo_cupom">
            <option value="Todos" <? if($tipo_cupom == 'Todos') echo 'selected' ?>>Todos</option>
            <option value="Especifico" <? if($tipo_cupom == 'Especifico') echo 'selected' ?>>Específico</option>
            <option value="Generico" <? if($tipo_cupom == 'Generico') echo 'selected' ?>>Genérico</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="legenda tdbl sep" align="right"><label for="situacao">Situação:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
          <select name="situacao">
            <option value="Todos" <? if($situacao == 'Todos') echo 'selected' ?>>Todos</option>
            <option value="ATIVO" <? if($situacao == 'ATIVO') echo 'selected' ?>>Ativos</option>
            <option value="EXCLUIDO" <? if($situacao == 'EXCLUIDO') echo 'selected' ?>>Excluidos</option>
          </select>
        </td>
      </tr>
      <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
      
      </table>
      
      <input type="hidden" name="acao" value="buscar">
    </form>
    
    <br>    
    
        <?
        
        
        $con = conectabd();
        
        $SqlBuscaRegistros = "SELECT * FROM $tabela cup WHERE cup.cod_respostas_cupom > 0 ";
        
        if ($necessita!="Todos")
        {
            if ($necessita=="Não")
                $SqlBuscaRegistros .= " AND cup.necessita_compra = 0";
            if ($necessita=="Sim")
                $SqlBuscaRegistros .= " AND cup.necessita_compra = 1";
        }
                
        if ($tipo_cupom!="Todos")
        {
            if ($tipo_cupom=="Especifico")
                $SqlBuscaRegistros .= " AND cup.generico=0 ";
            if ($tipo_cupom=="Generico")
                $SqlBuscaRegistros .= " AND cup.generico=1 ";
        }

        if ($situacao!="Todos")
        {
          $SqlBuscaRegistros .= " AND cup.situacao='".$situacao."' ";
        }

        $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
        $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);
      

        $SqlBuscaRegistros .= ' ORDER BY cup.cod_respostas_cupom DESC LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
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
          echo '<input type="hidden" name="reutilizavel" value="'.$reutilizavel.'">';
          echo '<input type="hidden" name="tipo_cupom" value="'.$tipo_cupom.'">';
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
    
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
          <tr>
            <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
              <td align="center">Cupom</td>
              <td align="center" width="100">Tipo de Cupom</td>
              <td align="center" width="100">Tipo de Produto</td>
              <td align="center" width="40">Situação</td>
            </tr>
          </thead>
          <tbody>
          
          <?

          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';

            $nome_cupom = 'Cupom #'.bd2texto($objBuscaRegistros->$chave_primaria);
            $nome_cupom .= ' - '.bd2texto($objBuscaRegistros->nome_cupom);

            
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.$nome_cupom.'</a></td>';
            //echo '<td align="center">'.bd2texto($objBuscaRegistros->$chave_primaria).'</td>';
            
            if($objBuscaRegistros->generico) {
              echo '<td align="center">Genérico</td>';
            }
            else {
              echo '<td align="center">Específico</td>';
            }
            
            echo '<td align="center">'.bd2texto($objBuscaRegistros->produto).'</td>';
            
            if($objBuscaRegistros->situacao=="ATIVO")
              echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
            else
              echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
              
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
          <li><a href="ipi_respostas_padroes_respostas.php">Respostas</a></li>
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
    
    if($codigo > 0) {
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    } 
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0" width="350">

      <!-- 
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_pizzarias">Pizzaria</label></td></tr>
    <tr><td class="tdbl tdbr sep">

    <?
    $conexao = conectabd();
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao='ATIVO' ORDER BY nome";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $num_buscar_pizzarias = mysql_num_rows($res_buscar_pizzarias);
    $metade = $num_buscar_pizzarias/2;
    $e = 0;

    echo '<table align="center" border="0"><tr><td>';
    while ($obj_buscar_pizzarias[$e] = mysql_fetch_object($res_buscar_pizzarias))
    {
        echo '<input type="checkbox" name="cod_pizzarias[]" class="noborder" align="absbottom" id="cod_pizzarias[]" value=' . $obj_buscar_pizzarias[$e]->cod_pizzarias . '>';
        echo $obj_buscar_pizzarias[$e]->nome.'<br />';
        if ( $metade == ($e+1) )
        {
			      echo "</td><td>";        	
        }
        
        $e +=1;
    }
    echo "</td></tr>";
    echo '</table>';
    desconectabd($conexao);
    ?>
    -->

    </td></tr>

       
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="dias_validos">Dias validos</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    <input class="requerido" type="text" name="dias_validos" id="dias_validos" maxlength="10" size="14" value="<? echo $objBusca->dias_validos ?>" onkeypress="return ApenasNumeros(this,event);"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="tipo_cupom">Tipo de Cupom</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select class="requerido" name="tipo_cupom" id="tipo_cupom" onchange="habilitar_tipo_produto(this.value)">
        <option value=""></option>
        <option value="GENERICO" <? if($objBusca->generico) echo "selected" ?>>Genérico</option>
        <option value="ESPECIFICO" <? if((!$objBusca->generico) && ($objBusca->cod_produtos!="")) echo "selected" ?>>Específico</option>
      </select>
    </td></tr>
    <tr><td id="tipo_produto_legenda" style="display: none" class="legenda tdbl tdbr"><label for="produto">Tipo de Produto</label></td></tr>
    <tr><td id="tipo_produto_campo" style="display: none" class="tdbl tdbr">
      <select name="produto" id="produto" onchange="carregaProdutos()">
        <option value=""></option>
        <option value="PIZZA" <? if($objBusca->produto == 'PIZZA') echo 'selected' ?>>Pizza</option>
        <option value="BORDA" <? if($objBusca->produto == 'BORDA') echo 'selected' ?>>Borda</option>
        <option value="BEBIDA" <? if($objBusca->produto == 'BEBIDA') echo 'selected' ?>>Bebida</option>
      </select>
      &nbsp;
      <small id="carregando_produtos" style="color: red;"></small>
    </td></tr>
    <tr><td id="produto_legenda" style="display: none" class="legenda tdbl tdbr"><label for="cod_produtos">Produto</label></td></tr>
    <tr><td id="produto_campo" style="display: none" class="tdbl tdbr">
      <select name="cod_produtos" id="cod_produtos" onchange="carregaTamanhos()">
        <option value=""></option>
      </select>
      &nbsp;
      <small id="carregando_tamanhos" style="color: red;"></small>
    </td></tr>
    <? if($objBusca->cod_produtos!="")
      {
        echo "<script>carregaProdutos('".$objBusca->cod_produtos."')</script>";
      }
    ?> 
    <tr><td id="tamanho_legenda" style="display: none" class="legenda tdbl tdbr"><label for="cod_tamanhos">Tamanho</label></td></tr>
    <tr><td id="tamanho_campo" style="display: none" class="tdbl tdbr sep">
      <select name="cod_tamanhos" id="cod_tamanhos">
        <option value=""></option>
      </select>
    </td></tr>
    <? if($objBusca->cod_tamanhos!="")
      {
        echo "<script>carregaTamanhos('".$objBusca->cod_tamanhos."')</script>";
      }
    ?> 
    <? if($objBusca->generico!="")
      {
        echo "<script>habilitar_tipo_produto($('tipo_cupom').value)</script>";
      }
    ?>  
    <tr>
    <td class="legenda tdbl tdbr sep">
      <input type="checkbox" name="necessita_compra" id="necessita_compra" <? if($objBusca->necessita_compra) echo 'checked' ?> onclick="habilitar_valor_minimo(this.checked)">
      &nbsp;
      <label for="necessita_compra">Necessita de Compra</label>
      </td>
    </tr>
    <tr><td id="valor_minimo_legenda" style="display: none" class="legenda tdbl tdbr"><label for="valor_minimo">Valor Mínimo de Compra</label></td></tr>
    <tr><td id="valor_minimo_campo" style="display: none" class="tdbl tdbr sep"><input type="text" name="valor_minimo" id="valor_minimo" maxlength="10" size="14" value="<? echo bd2moeda($objBusca->valor_minimo_compra) ?> " onkeypress="return formataMoeda(this, '.', ',', event)"></td></tr>
    <? if($objBusca->necessita_compra)
    {
      echo "<script>habilitar_valor_minimo(true)</script>";
    }
    ?>
    <tr>
      <td class="legenda tdbl tdbr">
        <label for="nome_cupom">Nome do cupom</label>
      </td>
    </tr>
    <tr>
      <td class="tdbl tdbr sep">
        <input type="text" name="nome_cupom" id="nome_cupom" size='35' maxlength='100' value="<? echo $objBusca->nome_cupom ?>" />
      </td>
    </tr>
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Gerar -->
    
 </div>

<? rodape(); ?>
