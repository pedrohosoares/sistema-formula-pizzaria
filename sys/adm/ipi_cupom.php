<?php

/**
 * ipi_cupom.php: Cadastro de Cupom
 * 
 * �ndice: cod_cupons
 * Tabela: ipi_cupons
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/cupom.php';

cabecalho('Cadastro de Cupons');

$acao = validaVarPost('acao');

$tabela = 'ipi_cupons';
$chave_primaria = 'cod_cupons';
$quant_pagina = 80;

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');

    $cupom = new Cupom();

    $con = conectabd();
    $resultado = 1;
	  foreach ($excluir as $cod_cupons)
	  {
      $resultado &= $cupom->excluir_cupom($cod_cupons);
	  }

    if ($resultado)
      mensagemOk('Os registros selecionados foram exclu�dos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, verifique se a pizzaria n�o est� repos�vel por algum bairro (cep).');
    
    desconectabd($con);
  break;
  case 'editar_pizzarias':
    $cod_cupons = validaVarPost("cod_cupons");
    $cod_pizzarias = validaVarPost('cod_pizzarias');

    $cupom = new Cupom();
    $con = conectabd();
    $resultado = false;

    if($cod_cupons!="")
    {
      $resultado = $cupom->alterar_pizzaria($cod_cupons,$cod_pizzarias);
    }

    if($resultado)
    {
      mensagemOk('Cupon alterado com �xito!');
    }
    else
    {
      mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o cupom foi selecionado.');
    }
    desconectabd($con);
  break;
  case 'editar':
    $data_validade = data2bd(validaVarPost('data_validade'));
    $data_inicio = data2bd(validaVarPost('data_inicio'));
    $quantidade = validaVarPost('quantidade');
    
    $produto = validaVarPost('produto');
    $cod_produtos = validaVarPost('cod_produtos');
    $cod_tamanhos = validaVarPost('cod_tamanhos');
    
    $cod_pizzarias = validaVarPost('cod_pizzarias');

    $obs_cupom = validaVarPost('obs_cupom');
    
    $promocao = (validaVarPost('reutilizavel') == 'on') ? 1 : 0; 
    $necessita_compra = (validaVarPost('necessita_compra') == 'on') ? 1 : 0; 
    
    $tipo_cupom = validaVarPost('tipo_cupom');
    
    $valor_minimo_compra = moeda2bd(validaVarPost('valor_minimo'));
    
    $generico = ($tipo_cupom == 'GENERICO') ? 1 : 0;
    
    if($tipo_cupom == 'GENERICO') {
      $cod_produtos = 0;
    }

    $con = conectabd();
    $cupom = new Cupom();


    $resEdicao = true;
    for($i = 1; $i <= $quantidade; $i++) 
    {
      $cod_cupons = $cupom->inserir_cupom($data_inicio, $data_validade, $produto, $cod_produtos, $cod_tamanhos, $promocao, $necessita_compra, $valor_minimo_compra, $cod_usuario, $generico, $obs_cupom, $cod_pizzarias);
    }
    
    if($resEdicao)
    {
      mensagemOk('Cupons gerados com �xito!');
    }
    else
    {
      mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro j� n�o se encontra cadastrado.');
    }
      
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

function carregaProdutos() 
{
    var url = 'acao=carregar_produto';
    
    var produto = $('produto').getProperty('value');
    url += '&produto=' + produto;
    
    $('carregando_produtos').set('text', 'Carregando...');
    
    new Request.HTML({
    url: 'ipi_cupom_ajax.php',
    update: $('cod_produtos'),
    onComplete: function() {
          $('carregando_produtos').set('text', '');
          
          if((document.frmIncluir.tipo_cupom.value == 'GENERICO') && (document.frmIncluir.produto.value != 'BEBIDA')) 
          {
               document.frmIncluir.cod_tamanhos.disabled = '';
              carregaTamanhos();
          }
          else
          {      
              if(document.frmIncluir.produto.value == 'BEBIDA') 
              {
                  document.frmIncluir.cod_tamanhos.value = '';
                  document.frmIncluir.cod_tamanhos.disabled = 'disabled';
                  $('cod_tam_label').removeClass("requerido");
                  $('cod_tamanhos').removeClass("requerido");
              }
              else 
              {
                  $('cod_tam_label').addClass("requerido");
                  $('cod_tamanhos').addClass("requerido");
                  document.frmIncluir.cod_tamanhos.disabled = '';
              }
          }
        }
    }).send(url);
}

function carregaTamanhos() {
  var url = 'acao=carregar_tamanho';
  
  var produto = $('produto').getProperty('value');
  url += '&produto=' + produto;
  
  var cod_produtos = $('cod_produtos').getProperty('value');
  url += '&cod_produtos=' + cod_produtos;
  
  $('carregando_tamanhos').set('text', 'Carregando...');
  
  new Request.HTML({
    url: 'ipi_cupom_ajax.php',
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

        $('cod_tam_label').removeClass("requerido");
        $('cod_tamanhos').removeClass("requerido");
    }
    else if(tipo == 'ESPECIFICO')
    {
        $('cod_tam_label').addClass("requerido");
        $('cod_tamanhos').addClass("requerido");

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

        $('cod_tam_label').removeClass("requerido");
        $('cod_tamanhos').removeClass("requerido");
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
  new vlaDatePicker('data_inicio', {openWith: 'botao_data_inicio', prefillDate: false});
  
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
      document.frmIncluir.data_inicio.value = '';
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
    <table align="center" width="950"><tr>
  
    <!-- Conte�do -->
    <td class="conteudo">
        
    <?
    $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 
    $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : 'nome';
    $filtro = validaVarPost('filtro');
    $tipo_criacao = validaVarPost('tipo_criacao');
    $tipo_cupom = validaVarPost('tipo_cupom');
    $reutilizavel = validaVarPost('reutilizavel');
    $descricao = validaVarPost("descricao");
    
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
        <td class="legenda tdbl tdbt sep" align="right">
          <select name="opcoes">
            <option value="descricao" <? if($opcoes == 'descricao') echo 'selected' ?>>Obs. Cupom</option>
          </select>
        </td>
        <td class="tdbt sep">&nbsp;</td>
        <td class="tdbt tdbr sep">
          <input type="text" name="filtro" size="60" value="<? echo $filtro ?>">
        </td>
      </tr>
      <?
        if (!$tipo_criacao)
            $tipo_criacao = 'Manual';
      ?>
      <tr>
        <td class="legenda tdbl  sep" align="right"><label for="tipo_criacao">Modo Cria��o:</label></td>
        <td class="sep ">&nbsp;</td>
        <td class="tdbr sep ">
          <select name="tipo_criacao">
            <option value="Todos" <? if($tipo_criacao == 'Todos') echo 'selected' ?>>Todos</option>
            <option value="Automatico" <? if($tipo_criacao == 'Automatico') echo 'selected' ?>>Autom�tico</option>
            <option value="Manual" <? if($tipo_criacao == 'Manual') echo 'selected' ?>>Manual</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="legenda tdbl sep" align="right"><label for="reutilizavel">Reutiliz�vel:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
          <select name="reutilizavel">
            <option value="Todos" <? if($reutilizavel == 'Todos') echo 'selected' ?>>Todos</option>
            <option value="Sim" <? if($reutilizavel == 'Sim') echo 'selected' ?>>Sim</option>
            <option value="N�o" <? if($reutilizavel == 'N�o') echo 'selected' ?>>N�o</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="legenda tdbl sep" align="right"><label for="tipo_cupom">Tipo Cupom:</label></td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
          <select name="tipo_cupom">
            <option value="Todos" <? if($tipo_cupom == 'Todos') echo 'selected' ?>>Todos</option>
            <option value="Especifico" <? if($tipo_cupom == 'Especifico') echo 'selected' ?>>Espec�fico</option>
            <option value="Generico" <? if($tipo_cupom == 'Generico') echo 'selected' ?>>Gen�rico</option>
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
        
        //$SqlBuscaRegistros = "SELECT cup.cupom, c.nome, cup.usuario_criacao, cup.data_validade, cup.valido, cup.produto FROM $tabela cup LEFT JOIN ipi_pedidos_ipi_cupons pc ON (cup.cod_cupons = pc.cod_cupons) LEFT JOIN ipi_pedidos ped ON (pc.cod_pedidos = ped.cod_pedidos) LEFT JOIN ipi_clientes c ON (ped.cod_clientes = c.cod_clientes) WHERE c.$opcoes LIKE '%$filtro%'";
        $SqlBuscaRegistros = "SELECT cup.cod_cupons, cup.cupom, cup.usuario_criacao, cup.data_validade,cup.data_inicio, cup.valido, cup.produto, cup.obs_cupom, cup.generico, (SELECT COUNT(c.cod_pedidos) FROM ipi_pedidos_ipi_cupons c INNER JOIN ipi_pedidos p ON(c.cod_pedidos=p.cod_pedidos) WHERE situacao='BAIXADO' AND c.cod_cupons=cup.cod_cupons ) AS numero_utilizacoes FROM $tabela cup INNER JOIN ipi_pizzarias_cupons pc ON (pc.cod_cupons = cup.cod_cupons) WHERE pc.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
        if ($filtro!="")
        {
          $SqlBuscaRegistros.= " AND cup.obs_cupom like '%".$filtro."%'";
        }
        if ($tipo_criacao!="Todos")
        {
            if ($tipo_criacao=="Automatico")
                $SqlBuscaRegistros .= " AND cup.usuario_criacao IS NULL ";
            if ($tipo_criacao=="Manual")
                $SqlBuscaRegistros .= " AND cup.usuario_criacao IS NOT NULL ";
        }
        
        if ($reutilizavel!="Todos")
        {
            if ($reutilizavel=="Sim")
                $SqlBuscaRegistros .= " AND cup.promocao=1 ";
            if ($reutilizavel=="N�o")
                $SqlBuscaRegistros .= " AND cup.promocao=0 ";
        }
        
        if ($tipo_cupom!="Todos")
        {
            if ($tipo_cupom=="Especifico")
                $SqlBuscaRegistros .= " AND cup.generico=0 ";
            if ($tipo_cupom=="Generico")
                $SqlBuscaRegistros .= " AND cup.generico=1 ";
        }
        
        $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
        $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);
        

        $SqlBuscaRegistros .= ' ORDER BY cup.cod_cupons DESC LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
        $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
        $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);
        
        // echo $SqlBuscaRegistros;
        
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
          echo '<a href="javascript:;" onclick="javascript:frmPaginacao'.($pagina + 1).'.submit();" style="margin-left: 5px;">Pr�xima&nbsp;&raquo;</a>';
        else
          echo '<span style="margin-left: 5px;">Pr�xima&nbsp;&raquo;</span>';
        
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
              <td align="center" width="100">Data de Inicio</td>
              <td align="center" width="100">Data de Validade</td>
              <td align="center" width="100">Tipo de Cupom</td>
              <td align="center" width="100">Tipo de Produto</td>
              <td align="center">Cliente</td>
              <td align="center">Usu�rio de Cria��o</td>
              <td align="center">Obs.</td>
              <td align="center">Util.</td>
              <td align="center" width="40">V�lido</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->cupom).'</a></td>';
            //echo '<td align="center">'.bd2texto($objBuscaRegistros->cupom).'</td>';
            echo '<td align="center">'.bd2data($objBuscaRegistros->data_inicio).'</td>';
            echo '<td align="center">'.bd2data($objBuscaRegistros->data_validade).'</td>';
            
            if($objBuscaRegistros->generico=='1') {
              echo '<td align="center">Gen�rico</td>';
            }
            else {
              echo '<td align="center">Espec�fico</td>';
            }
            
            echo '<td align="center">'.bd2texto($objBuscaRegistros->produto).'</td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->nome).'</td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->usuario_criacao).'</td>';
            echo '<td align="left">'.bd2texto($objBuscaRegistros->obs_cupom).'</td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->numero_utilizacoes).'</td>';
            
            if($objBuscaRegistros->valido)
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
    <!-- Conte�do -->
    
    <!-- Barra Lateral -->
    <!-- 
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="#">Atalho 1</a></li>
        </ul>
      </div>
    </td>
     -->
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Listar -->
  
  
  
  <!-- Tab Gerar -->
  <div class="painelTab">
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    
    <? 
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    

    if($codigo > 0) { ?>

    <?
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
      $conexao = conectabd();
      $arr_pizzarias = array();

      $sql_buscar_pizzarias_cupom = "SELECT * from ipi_pizzarias_cupons where cod_cupons = '$codigo'";
      $res_buscar_pizzarias_cupom = mysql_query($sql_buscar_pizzarias_cupom);
      while($obj_buscar_pizzarias_cupom = mysql_fetch_object($res_buscar_pizzarias_cupom))
      {
        $arr_pizzarias[] = $obj_buscar_pizzarias_cupom->cod_pizzarias;
      }

      if($objBusca->data_inicio)
      {
        $data_inicio = $objBusca->data_inicio;
      }


    ?>

    <table align="center" class="caixa" cellpadding="0" cellspacing="0" width="350">
    

    <tr><td class="legenda tdbl tdbt sep tdbr">Alterar Pizzarias do cupom: <b><? echo $objBusca->cupom ?></b> </td></tr>
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="cod_pizzarias">Pizzaria</label></td></tr>
    <tr><td class="tdbl tdbr sep">

    <?
    
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao='ATIVO' ORDER BY nome";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $num_buscar_pizzarias = mysql_num_rows($res_buscar_pizzarias);
    $metade = $num_buscar_pizzarias/2;
    $e = 0;

    echo '<table align="center" border="0"><tr><td>';
    while ($obj_buscar_pizzarias[$e] = mysql_fetch_object($res_buscar_pizzarias))
    {
        echo '<input type="checkbox" name="cod_pizzarias[]" class="noborder" align="absbottom" id="cod_pizzarias[]" ';
        if(in_array($obj_buscar_pizzarias[$e]->cod_pizzarias,$arr_pizzarias))
        {
          echo 'checked="checked"';
        }
        echo ' value=' . $obj_buscar_pizzarias[$e]->cod_pizzarias . '>';
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

<tr><input type="hidden" name="acao" value="editar_pizzarias"><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    </td></tr>
  </table>
    <? }
    else{ ?>



    <? //$data_inicio = date("Y-m-d"); ?>


    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0" width="350">
    

   
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_pizzarias"><?php echo TIPO_EMPRESA; ?></label></td></tr>
    <tr><td class="tdbl tdbr sep">

    <?
    $conexao = conectabd();
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao='ATIVO' ORDER BY nome";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $num_buscar_pizzarias = mysql_num_rows($res_buscar_pizzarias);
    if ($num_buscar_pizzarias>0)
    {
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
    }
    else
    {
      echo "Nenhuma pizzaria ativa no sistema!";
    }

    desconectabd($conexao);
    ?>


    </td></tr>

    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="quantidade">Quant. de Cupons</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="quantidade" id="quantidade" maxlength="10" size="14" value="1" onkeypress="return ApenasNumero(event);"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="data_inicio">Data de Inicio da Utiliza��o</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    <input class="requerido" type="text" name="data_inicio" id="data_inicio" maxlength="10" size="14" value="<? echo bd2data($data_inicio) ?>" onkeypress="return MascaraData(this,event);">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicio"><img src="../lib/img/principal/botao-data.gif"></a>
    </td></tr>


    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="data_validade">Data de Validade</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    <input class="requerido" type="text" name="data_validade" id="data_validade" maxlength="10" size="14" value="<? echo bd2data($objBusca->data_validade) ?>" onkeypress="return MascaraData(this,event);">
    &nbsp;
    <a href="javascript:;" id="botao_data_validade"><img src="../lib/img/principal/botao-data.gif"></a>
    </td></tr>

    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="tipo_cupom">Tipo de Cupom</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select class="requerido" name="tipo_cupom" id="tipo_cupom" onchange="habilitar_tipo_produto(this.value)">
        <option value=""></option>
        <option value="GENERICO">Gen�rico</option>
        <option value="ESPECIFICO">Espec�fico</option>
      </select>
    </td></tr>
    
    <tr><td id="tipo_produto_legenda" style="display: none" class="legenda tdbl tdbr"><label for="produto">Tipo de Produto</label></td></tr>
    <tr><td id="tipo_produto_campo" style="display: none" class="tdbl tdbr">
      <select name="produto" id="produto" onchange="carregaProdutos()">
        <option value=""></option>
        <option value="PIZZA" <? if($objBusca->produto == 'PIZZA') echo 'selected' ?>>Pizza</option>
        <?php if (PRODUTO_USA_BORDA == "S") { ?>
        <option value="BORDA" <? if($objBusca->produto == 'BORDA') echo 'selected' ?>>Borda</option>
        <?php } ?>
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
    
    <tr><td id="tamanho_legenda" style="display: none" class="legenda tdbl tdbr"><label name="cod_tam_label" id="cod_tam_label" for="cod_tamanhos">Tamanho</label></td></tr>
    <tr><td id="tamanho_campo" style="display: none" class="tdbl tdbr sep">
      <select name="cod_tamanhos" id="cod_tamanhos">
        <option value=""></option>
      </select>
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr">
      <input type="checkbox" name="reutilizavel" id="reutilizavel" <? if($objBusca->promocao) echo 'checked' ?>>
      &nbsp;
      <label for="reutilizavel">Reutiliz�vel</label>
    </td></tr>
    
    <tr>
    <td class="legenda tdbl tdbr sep">
      <input type="checkbox" name="necessita_compra" id="necessita_compra" <? if($objBusca->necessita_compra) echo 'checked' ?> onclick="habilitar_valor_minimo(this.checked)">
      &nbsp;
      <label for="necessita_compra">Necessita de Compra</label>
      </td>
    </tr>
    
    <tr><td id="valor_minimo_legenda" style="display: none" class="legenda tdbl tdbr"><label for="valor_minimo">Valor M�nimo de Compra</label></td></tr>
    <tr><td id="valor_minimo_campo" style="display: none" class="tdbl tdbr sep"><input type="text" name="valor_minimo" id="valor_minimo" maxlength="10" size="14" onkeypress="return formataMoeda(this, '.', ',', event)"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="obs_cupom">Obs.</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="obs_cupom" id="obs_cupom" maxlength="250" size="45" value="<? echo bd2texto($objBusca->obs_cupom) ?>"></td></tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    
    <?
  } 
    ?>
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    </form>
  </div>
  <!-- Tab Gerar -->
    
 </div>

<? rodape(); ?>
