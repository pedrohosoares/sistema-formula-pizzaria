<?php

/**
 * ipi_ingrediente.php: Cadastro de Ingrediente
 * 
 * Índice: cod_ingredientes
 * Tabela: ipi_ingredientes
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro Ingredientes');

$acao = validaVarPost('acao');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel1 = "DELETE FROM ipi_ingredientes_ipi_tamanhos WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel2 = "DELETE FROM ipi_ingredientes_ipi_pizzas WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel3 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    $resDel1 = mysql_query($SqlDel1);
    $resDel2 = mysql_query($SqlDel2);
    $resDel3 = mysql_query($SqlDel3);
    
    if ($resDel1 && $resDel2 && $resDel3)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $cod_ingredientes_troca = validaVarPost('cod_ingredientes_troca');
    $cod_ingredientes_baixa = validaVarPost('cod_ingredientes_baixa');
    $ingrediente = validaVarPost('ingrediente');
    $ingrediente_abreviado = validaVarPost('ingrediente_abreviado');
    $tipo = validaVarPost('tipo');
    $tamanho = validaVarPost('tamanho');
    $tamanho_checkbox = validaVarPost('tamanho_checkbox');
    $unidade_padrao = validaVarPost('unidade_padrao');
    $instrucao_entrada = validar_var_post('instrucao_entrada');
    $cod_titulos_subcategorias = validaVarPost('titulos_subcategorias');
    $indice_perda = validaVarPost('indice_perda');
    $entrada_estoque_maxima = (validaVarPost('entrada_estoque_maxima'));
    $entrada_estoque_minima = (validaVarPost('entrada_estoque_minima'));

    $ingrediente_marca = validaVarPost("ingrediente_marca");
    $quantidade_marca = validaVarPost("quantidade_marca");

    $divisor_comum = validaVarPost("divisor_comum");
/*    $entrada_estoque_maxima = $entrada_estoque_maxima * $divisor_comum;
    $entrada_estoque_minima = $entrada_estoque_minima * $divisor_comum;
    $quantidade_marca = $quantidade_marca* $divisor_comum;*/

    $adicional = (validaVarPost('adicional') == 'on') ? 1 : 0;
    $consumo = (validaVarPost('consumo') == 'on') ? 1 : 0;
    $ativo = (validaVarPost('ativo') == 'on') ? 1 : 0;
    $destaque = (validaVarPost('destaque') == 'on') ? 1 : 0;
    $considerar_cmv = (validaVarPost('considerar_cmv') == 'on') ? 1 : 0;

    $imagem_g = validaVarFiles('foto_g');
    $imagem_p = validaVarFiles('foto_p');
    
    $con = conectabd();
    
    if($codigo <= 0) 
    {
      $SqlEdicao = sprintf("INSERT INTO $tabela (cod_ingredientes_troca, cod_ingredientes_baixa,cod_titulos_subcategorias, ingrediente, ingrediente_abreviado, tipo, adicional, consumo, ativo, destaque, cod_unidade_padrao, instrucao_entrada, indice_perda, entrada_estoque_maxima, entrada_estoque_minima, considerar_cmv,ingrediente_marca,quantidade) VALUES (%d, %d, %d, '%s', '%s', '%s', %d, %d, %d, %d, '%s','%s','%s','%s','%s', %d,'%s','%s')", 
                           $cod_ingredientes_troca, $cod_ingredientes_baixa,$cod_titulos_subcategorias, $ingrediente, $ingrediente_abreviado, $tipo, $adicional, $consumo, $ativo, $destaque, $unidade_padrao, $instrucao_entrada, moeda2bd($indice_perda), ($entrada_estoque_maxima), ($entrada_estoque_minima), $considerar_cmv,$ingrediente_marca,$quantidade_marca);
     // echo "insert  ".$SqlEdicao;
      $res_edicao = mysql_query($SqlEdicao);
      if($res_edicao) 
      {
        $codigo = mysql_insert_id();
        
        $resEdicaoTamanhoIngrediente = true;
        
        // Inserindo as Imagens grandes
        $resEdicaoImagem = true;
        if(count($imagem_g['name']) > 0) {     
          if(trim($imagem_g['name']) != '') {
            $arq_info = pathinfo($imagem_g['name']);
            $arq_ext = $arq_info['extension'];
            if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_g["type"])) {
              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
            }
            else {                
              $resEdicaoImagem &= move_uploaded_file($imagem_g['tmp_name'], UPLOAD_DIR."/ingredientes/${codigo}_ing_g.${arq_ext}");
                                      
              $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_grande = '%s' WHERE $chave_primaria = $codigo", 
                         texto2bd("${codigo}_ing_g.${arq_ext}"));
              
              $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
            }
          }          
        }
        
        // Inserindo as Imagens pequenas
        $resEdicaoImagem = true;
        if(count($imagem_p['name']) > 0) {     
          if(trim($imagem_p['name']) != '') {
            $arq_info = pathinfo($imagem_p['name']);
            $arq_ext = $arq_info['extension'];
            if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_p["type"])) {
              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
            }
            else {                
              $resEdicaoImagem &= move_uploaded_file($imagem_p['tmp_name'], UPLOAD_DIR."/ingredientes/${codigo}_ing_p.${arq_ext}");
                                      
              $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_pequena = '%s' WHERE $chave_primaria = $codigo", 
                         texto2bd("${codigo}_ing_p.${arq_ext}"));
              
              $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
            }
          }          
        }
        
        
        if($resEdicaoImagem) {
          mensagemOk('Registro adicionado com êxito!');
        }
        else {
          mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        
      }
      else {
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
      }
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET cod_ingredientes_troca = '%s', cod_ingredientes_baixa = '%s', cod_titulos_subcategorias = %d, ingrediente = '%s', ingrediente_abreviado = '%s', tipo = '%s', adicional = %d, consumo = %d, ativo = %d, destaque = %d, cod_unidade_padrao = '%s', instrucao_entrada = '%s', indice_perda = '%s', entrada_estoque_maxima = '%s', entrada_estoque_minima = '%s', considerar_cmv = %d, ingrediente_marca = '%s', quantidade = '%s' WHERE $chave_primaria = '$codigo'", 
                           $cod_ingredientes_troca, $cod_ingredientes_baixa,$cod_titulos_subcategorias, $ingrediente, $ingrediente_abreviado, $tipo, $adicional, $consumo, $ativo, $destaque, $unidade_padrao, $instrucao_entrada,moeda2bd($indice_perda), ($entrada_estoque_maxima), ($entrada_estoque_minima), $considerar_cmv, $ingrediente_marca,$quantidade_marca);
        //echo  "update  ".$SqlEdicao;
                           
       if(mysql_query($SqlEdicao)) {
        $resEdicaoTamanhoIngrediente = true;
        
        $resEdicaoTamanhoIngrediente = true;
        
        // Alterando as Imagens grandes
        $resEdicaoImagem = true;
        if(count($imagem_g['name']) > 0) {     
          if(trim($imagem_g['name']) != '') {
            $arq_info = pathinfo($imagem_g['name']);
            $arq_ext = $arq_info['extension'];
            if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_g["type"])) {
              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
            }
            else {                
              $resEdicaoImagem &= move_uploaded_file($imagem_g['tmp_name'], UPLOAD_DIR."/ingredientes/${codigo}_ing_g.${arq_ext}");
                                      
              $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_grande = '%s' WHERE $chave_primaria = $codigo", 
                         texto2bd("${codigo}_ing_g.${arq_ext}"));
              
              $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
            }
          }          
         }
         
        // Alterando as Imagens pequenas
        if(count($imagem_p['name']) > 0) {     
          if(trim($imagem_p['name']) != '') {
            $arq_info = pathinfo($imagem_p['name']);
            $arq_ext = $arq_info['extension'];
            if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_p["type"])) {
              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
            }
            else {                
              $resEdicaoImagem &= move_uploaded_file($imagem_p['tmp_name'], UPLOAD_DIR."/ingredientes/${codigo}_ing_p.${arq_ext}");
                                      
              $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_pequena = '%s' WHERE $chave_primaria = $codigo", 
                         texto2bd("${codigo}_ing_p.${arq_ext}"));
              
              $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
            }
          }          
         }
               
          if($resEdicaoImagem) {
            mensagemOk('Registro alterado com êxito!');
          }
          else {
            mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          }
          
          
        }
        else {
          mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
    }
    
    desconectabd($con);
  break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

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

function limpaPreco(cod) {
  document.getElementById('preco_' + cod).value = '';
}

function excluirImagem(cod) {
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
    var acao = 'excluir_imagem';
    var url = 'acao=' + acao + '&cod_ingredientes=' + cod;
    
    new Request.JSON({url: 'ipi_ingrediente_ajax.php', onComplete: function(retorno) {
      if(retorno.status != 'OK') {
        alert('Erro ao excluir esta imagem.');
      }
      else {
        if($('foto_g_figura')) {
          $('foto_g_figura').destroy();
        }
      }
    }}).send(url); 
  } 
}

function excluirImagem_pequena(cod) {
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
    var acao = 'excluir_imagem_pequena';
    var url = 'acao=' + acao + '&cod_ingredientes=' + cod;
    
    new Request.JSON({url: 'ipi_ingrediente_ajax.php', onComplete: function(retorno) {
      if(retorno.status != 'OK') {
        alert('Erro ao excluir esta imagem.');
      }
      else {
        if($('foto_p_figura')) {
          $('foto_p_figura').destroy();
        }
      }
    }}).send(url); 
  } 
}

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
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
      document.frmIncluir.ingrediente.value = '';
      document.frmIncluir.ingrediente_abreviado.value = '';
      document.frmIncluir.cod_ingredientes_baixa.value = '';
      document.frmIncluir.unidade_padrao.value = '';
      document.frmIncluir.quantidade_marca.value = '';
      document.frmIncluir.ingrediente_marca.value = '';
      document.frmIncluir.instrucao_entrada.value = '';
      document.frmIncluir.titulos_subcategorias.value = '';
      document.frmIncluir.cod_ingredientes_troca.value = '';
      document.frmIncluir.entrada_estoque_minima.value = '';
      document.frmIncluir.entrada_estoque_maxima.value = '';
      document.frmIncluir.indice_perda.value = '0,00';
      //document.frmIncluir.tipo.value = '';
      document.frmIncluir.adicional.checked = false;
      document.frmIncluir.ativo.checked = true;
      document.frmIncluir.considerar_cmv.checked = true;
      document.frmIncluir.adicional.checked = false;
      document.frmIncluir.consumo.checked = false;
      document.frmIncluir.destaque.checked = false;

      
     /* marcaTodosEstado('marcar_tamanho', false);*/
      
      // Limpando todos os campos input para Preço
      // var input = document.getElementsByTagName('input');
      // for (var i = 0; i < input.length; i++) {
      //   if((input[i].name.match('preco')) || (input[i].name.match('quantidade_estoque_extra'))) { 
      //     input[i].value = ''; 
      //   }
      // }
      
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
    <table><tr>
  
    <!-- Conteúdo -->
    <td class="conteudo">
    
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
              <td align="center">COD Ingrediente</td>
              <td align="center">Ingrediente</td>
              <td align="center">Ingrediente Abreviado</td>
              <td align="center">Ingrediente Adicional</td>
              <td align="center">Ingrediente Destaque</td>
            </tr>
          </thead>
          <tbody>
          <?
          $con = conectabd();
          $SqlBuscaIngredientes = "SELECT * FROM $tabela ORDER BY ingrediente";
          $resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
          while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) 
          {
            echo '<tr>';
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaIngredientes->$chave_primaria.'"></td>';
            echo '<td align="center">'.bd2texto($objBuscaIngredientes->cod_ingredientes).'</td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaIngredientes->$chave_primaria.')">'.bd2texto($objBuscaIngredientes->ingrediente).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaIngredientes->$chave_primaria.')">'.bd2texto($objBuscaIngredientes->ingrediente_abreviado).'</a></td>';
            
            if ($objBuscaIngredientes->adicional==1)
            {
              echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
            }
            else
            {
               echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
            }

            if ($objBuscaIngredientes->destaque==1)
            {
              echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
            }
            else
            {
               echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
            }

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
          <li><a href="ipi_adicional.php">Adicionais</a></li>
          <li><a href="ipi_borda.php">Bordas</a></li>
          <li><a href="ipi_pizza.php"><? echo ucfirst(TIPO_PRODUTOS)?></a></li>
          <li><a href="ipi_tamanho.php">Tamanhos</a></li>
          <li><a href="ipi_unidade_padrao.php">Unidade Padrão</a></li>
          <li><a href="ipi_ingrediente_marcas.php">Ingredientes - Marcas</a></li>
        </ul>
      </div>
    </td>
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Editar -->
  
  
  
  <!-- Tab Incluir -->
  <div class="painelTab">
    <? 
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    
    if($codigo > 0) {
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    } 
    ?>
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="ingrediente">Ingrediente</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="ingrediente" id="ingrediente" maxlength="45" size="45" value="<? echo texto2bd($objBusca->ingrediente) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="ingrediente_abreviado">Ingrediente Abreviado</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="ingrediente_abreviado" id="ingrediente_abreviado" maxlength="45" size="45" value="<? echo texto2bd($objBusca->ingrediente_abreviado) ?>"></td></tr>

    
    <? $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo"); ?>
    <tr>
    <td class="legenda tdbl tdbr"><label for="foto_g">Imagem grande (*.png, *.jpg)</label></td>
  </tr>
        
    <?
    if (is_file(UPLOAD_DIR . '/ingredientes/' . $objBusca->foto_grande))
    {
        echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_g_figura" style="padding: 15px;">';
        
        echo '<img height="100" src="' . UPLOAD_DIR . '/ingredientes/' . $objBusca->foto_grande . '">';
        
        echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem(' . $objBusca->$chave_primaria . ');"></td></tr>';
    }
    ?>
    
    <tr>
    <td class="sep tdbl tdbr sep"><input type="file" name="foto_g"
      id="foto_g" size="40"></td>
  </tr>
  
  <tr>
    <td class="legenda tdbl tdbr"><label for="foto_p">Imagem pequena (*.png, *.jpg)</label></td>
  </tr>
        
    <?
    if (is_file(UPLOAD_DIR . '/ingredientes/' . $objBusca->foto_pequena))
    {
        echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_p_figura" style="padding: 15px;">';
        
        echo '<img src="' . UPLOAD_DIR . '/ingredientes/' . $objBusca->foto_pequena . '">';
        
        echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem_pequena(' . $objBusca->$chave_primaria . ');"></td></tr>';
    }
    ?>
    
    <tr>
    <td class="sep tdbl tdbr sep"><input type="file" name="foto_p"
      id="foto_p" size="40"></td>
  </tr>
  
    
 <tr>
        <td class="legenda tdbl tdbr"><label for="titulos_subcategorias">Subcategoria do Ingrediente</label></td>
    </tr>   
    <tr>
        <td class="tdbl tdbr sep">
            <select name="titulos_subcategorias" id="titulos_subcategorias">
              <option value=""></option>
                <?
                $con = conectabd();
                
                $sql_buscar_categorias = "SELECT * FROM ipi_titulos_categorias WHERE cod_titulos_categorias IN (SELECT cod_titulos_categorias FROM ipi_titulos_subcategorias WHERE tipo_titulo = 'PAGAR' AND tipo_cendente_sacado = 'FORNECEDOR') ORDER BY titulos_categoria";
                $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                
                while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                {
                    echo '<optgroup label="' . bd2texto($obj_buscar_categorias->titulos_categoria) . '">';
                    
                    $sql_buscar_subcategorias = "SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = '" . $obj_buscar_categorias->cod_titulos_categorias . "' AND tipo_titulo = 'PAGAR' AND tipo_cendente_sacado = 'FORNECEDOR' ORDER BY titulos_subcategorias";
                    $res_buscar_subcategorias = mysql_query($sql_buscar_subcategorias);
                    
                    while($obj_buscar_subcategorias = mysql_fetch_object($res_buscar_subcategorias))
                    {
                        echo '<option value="' . $obj_buscar_subcategorias->cod_titulos_subcategorias . '"';
                        if($obj_buscar_subcategorias->cod_titulos_subcategorias == $objBusca->cod_titulos_subcategorias)
                        {
                            echo " SELECTED ";
                        }
                        echo '>' . bd2texto($obj_buscar_subcategorias->titulos_subcategorias) . '</option>';
                    }
                    
                    echo '</optgroup>';
                }
                
                
                
                ?>
            </select>
        </td>
    </tr>  

    <tr><td class="legenda tdbl tdbr"><label for="cod_ingredientes_baixa">Ingrediente para Baixa no Estoque</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    
    <select name="cod_ingredientes_baixa" id="cod_ingredientes_baixa">
      <option></option>
      <?
        $con = conectabd();
        $sql_ingredientes = 'SELECT * FROM ipi_ingredientes ORDER BY ingrediente';
        $res_ingredientes = mysql_query($sql_ingredientes);
        while ($obj_ingredientes = mysql_fetch_object($res_ingredientes)) 
        {
          echo '<option value="'.$obj_ingredientes->cod_ingredientes.'" ';
          if($obj_ingredientes->cod_ingredientes == $objBusca->cod_ingredientes_baixa)
            echo 'SELECTED';
          echo '>'.$obj_ingredientes->ingrediente.'</option>';
        }
        desconectabd($con);
      ?>
    </select>
    </td></tr>
  
    
    <tr><td class="legenda tdbl tdbr"><label for="ingrediente_abreviado">Ingrediente de Troca</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    
    <select name="cod_ingredientes_troca" id="cod_perfis">
      <option></option>
      <?
        $con = conectabd();
        $sql_ingredientes = 'SELECT * FROM ipi_ingredientes ORDER BY ingrediente';
        $res_ingredientes = mysql_query($sql_ingredientes);
        while ($obj_ingredientes = mysql_fetch_object($res_ingredientes)) 
        {
          echo '<option value="'.$obj_ingredientes->cod_ingredientes.'" ';
          if($obj_ingredientes->cod_ingredientes == $objBusca->cod_ingredientes_troca)
            echo 'SELECTED';
          echo '>'.$obj_ingredientes->ingrediente.'</option>';
        }
        desconectabd($con);
      ?>
    </select>
    </td></tr>
  
    
    <tr><td class="legenda tdbl tdbr"><label for="unidade_padrao">Unidade padrão</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    
    <select name="unidade_padrao" id="unidade_padrao">
      <option></option>
      <?
        $con = conectabd();
        $sql_unidades = 'SELECT * FROM ipi_unidade_padrao ORDER BY unidade';
        $res_unidades = mysql_query($sql_unidades);
        while ($obj_unidades = mysql_fetch_object($res_unidades)) 
        {
          echo '<option value="'.$obj_unidades->cod_unidade_padrao.'" ';
          if($obj_unidades->cod_unidade_padrao == $objBusca->cod_unidade_padrao)
          {

            echo 'SELECTED';
           // $unidade_estoque = $obj_unidades->abreviatura;
           // $divisor = $obj_unidades->divisor_comum;
          }
            
          echo '>'.$obj_unidades->abreviatura.'</option>';
        }

        desconectabd($con);
      ?>
    </select>
    </td></tr>

    <tr><td class="legenda tdbl tdbr"><label for="indice_perda">Indice de Perda</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    
      <input type="text" name="indice_perda" size="5" onclick="javascript: if(this.value=='0'){this.value=''; }" onblur="javascript: if(this.value==''){this.value='0';}" onkeypress="return formataMoeda(this, '.', ',' , event)" value="<? echo bd2moeda($objBusca->indice_perda) ?>">&nbsp; %</td></tr>
    <tr><td class="legenda tdbl tdbr"><label for="entrada_estoque_minimo">Quantidade de Entrada Minima no Estoque (em g ou ml)</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    
      <input type="text" name="entrada_estoque_minima" size="5" onclick="javascript: if(this.value=='0'){this.value=''; }" onblur="javascript: if(this.value==''){this.value='0';}" value="<? echo ($objBusca->entrada_estoque_minima) ?>">&nbsp;

    </td></tr>


    </td></tr>

    <tr><td class="legenda tdbl tdbr"><label for="entrada_estoque_maxima">Quantidade de Entrada Máxima no Estoque (em g ou ml)</label></td></tr>
    <tr><td class="tdbl tdbr sep">
   
      <input type="text" name="entrada_estoque_maxima" size="5" onclick="javascript: if(this.value=='0'){this.value=''; }" onblur="javascript: if(this.value==''){this.value='0';}" value="<? echo ($objBusca->entrada_estoque_maxima) ?>">&nbsp; <!-- onkeypress="return formataMoeda(this, '.', ',' , event)" -->

    </td></tr>

   

    <!--  
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="tipo">Tipo</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select name="tipo" id="tipo" class="requerido">
        <option value=""></option>
        <option value="Salgado" <? if($objBusca->tipo == 'Salgado') echo 'selected' ?>>Salgado</option>
        <option value="Doce" <? if($objBusca->tipo == 'Doce') echo 'selected' ?>>Doce</option>
      </select>
    </td></tr>
    -->
    <tr><td class="tdbl tdbr"><input type="checkbox" name="considerar_cmv" <? if($objBusca->considerar_cmv) echo 'checked' ?>>&nbsp;<label for="considerar_cmv">Considerar no cálculo do CMV</label></td></tr>
    
    <tr><td class="tdbl tdbr"><input type="checkbox" name="adicional" <? if($objBusca->adicional) echo 'checked' ?>>&nbsp;<label for="adicional">Ingrediente Adicional</label></td></tr>
    <tr><td class="tdbl tdbr"><input type="checkbox" name="consumo" <? if($objBusca->consumo) echo 'checked' ?>>&nbsp;<label for="adicional">Ingrediente de Consumo</label></td></tr>
    <tr><td class="tdbl tdbr"><input type="checkbox" name="destaque" <? if($objBusca->destaque) echo 'checked' ?>>&nbsp;<label for="destaque">Destaque</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="checkbox" name="ativo" <? if($objBusca->ativo) echo 'checked' ?>>&nbsp;<label for="ativo">Habilitado</label></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="ingrediente">Marca do Ingrediente</label></td></tr>
    <tr><td class="tdbl tdbr"><input type="text" name="ingrediente_marca" id="ingrediente_marca" maxlength="45" size="45" value="<? echo texto2bd($objBusca->ingrediente_marca) ?>"></td></tr>

    <tr><td class="legenda tdbl tdbr"><label for="ingrediente">Quantidade padrão da marca</label> <span id='unidade_marca'>(em g ou ml)</span></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="text" name="quantidade_marca" id="quantidade_marca" maxlength="10" size="12" onkeypress="formataMoeda3casas(this, 3)" value="<? echo texto2bd($objBusca->quantidade) ?>"></td></tr>


    <tr><td class="legenda tdbl tdbr"><label for="instrucao_lancamento">Instruções para Entrada de estoque</label></td></tr>
    <tr><td class="tdbl tdbr sep"><textarea type="text" name="instrucao_entrada" id="instrucao_entrada" cols="50" rows="5"><? echo texto2bd($objBusca->instrucao_entrada) ?></textarea></td></tr>
    
    <tr><td class="tdbl tdbr">
    <tr><td colspan="2" align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
