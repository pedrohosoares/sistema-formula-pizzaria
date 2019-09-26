<?php

/**
 * ipi_borda.php: Cadastro de Bordas
 * 
 * Índice: cod_bordas
 * Tabela: ipi_bordas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Bordas');

$acao = validaVarPost('acao');

$tabela = 'ipi_bordas';
$chave_primaria = 'cod_bordas';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel1 = "DELETE FROM ipi_tamanhos_ipi_bordas WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel2 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    $resDel1 = mysql_query($SqlDel1);
    $resDel2 = mysql_query($SqlDel2);
    
    if ($resDel1 && $resDel2)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $borda = validaVarPost('borda');
    $cod_ingredientes = validaVarPost('cod_ingredientes');
    $tamanho = validaVarPost('tamanho');
    $tamanho_checkbox = validaVarPost('tamanho_checkbox');
    $novidade = (validaVarPost('novidade') == 'on') ? 1 : 0;
    $estoque = validaVarPost('estoque');

    
    $foto_p = validaVarFiles('foto_p');
    $foto_g = validaVarFiles('foto_g');
    
    
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (borda, cod_ingredientes, novidade) VALUES ('%s', '%s', '%s')", 
                           $borda, $cod_ingredientes, $novidade);
    $resEdicaoImagem = true;
          
    if(mysql_query($SqlEdicao)) {
        $codigo = mysql_insert_id();
        // Alterando as Imagens pequenas
        if(count($foto_p['name']) > 0) {     
          if(trim($foto_p['name']) != '') {
            $arq_info = pathinfo($foto_p['name']);
            $arq_ext = $arq_info['extension'];
            if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $foto_p["type"])) {
              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
            }
            else {                
              $resEdicaoImagem &= move_uploaded_file($foto_p['tmp_name'], UPLOAD_DIR."/bordas/${codigo}_bordas_p.${arq_ext}");
                                      
              $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_pequena = '%s' WHERE $chave_primaria = $codigo", 
                         texto2bd("${codigo}_bordas_p.${arq_ext}"));
              
              $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
              }
            } 
          } 
          
          // Alterando as Imagens grandes
          if(count($foto_g['name']) > 0) {     
            if(trim($foto_g['name']) != '') {
              $arq_info = pathinfo($foto_g['name']);
              $arq_ext = $arq_info['extension'];
              if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $foto_g["type"])) {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
              }
              else {                
                $resEdicaoImagem &= move_uploaded_file($foto_g['tmp_name'], UPLOAD_DIR."/bordas/${codigo}_bordas_g.${arq_ext}");
                                        
                $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_grande = '%s' WHERE $chave_primaria = $codigo", 
                           texto2bd("${codigo}_bordas_g.${arq_ext}"));
                
                $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
              }
            }  
          }
          
        $resEdicaoTamanho = true;
        if (is_array($tamanho_checkbox))
        {
          for($t = 0; $t < count($tamanho); $t++)
          {
            if (in_array($tamanho[$t], $tamanho_checkbox))
            {
              $cor_estoque = ($estoque[$t] > 0) ? $estoque[$t] : 0;
              
              $SqlEdicaoTamanho = sprintf("INSERT INTO ipi_tamanhos_ipi_bordas_estoque (cod_bordas, cod_tamanhos, quantidade_estoque) VALUES (%d, %d, %d)", $codigo, $tamanho[$t], $cor_estoque);
              // echo "<br />G: ".$SqlEdicaoTamanho;
              $resEdicaoTamanho &= mysql_query($SqlEdicaoTamanho);
              // echo $SqlEdicaoTamanho."<br>";
            }
          }
        }
        
        if($resEdicaoImagem && $resEdicaoTamanho) {
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
      $SqlEdicao = sprintf("UPDATE $tabela SET borda = '%s', cod_ingredientes = '%s', novidade = '%s' WHERE $chave_primaria = $codigo", 
                           $borda, $cod_ingredientes, $novidade);
      $resEdicaoImagem = true;
      // Alterando as Imagens pequenas
      if(count($foto_p['name']) > 0) {     
        if(trim($foto_p['name']) != '') {
          $arq_info = pathinfo($foto_p['name']);
          $arq_ext = $arq_info['extension'];
          if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $foto_p["type"])) {
            mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
          }
          else {                
            $resEdicaoImagem &= move_uploaded_file($foto_p['tmp_name'], UPLOAD_DIR."/bordas/${codigo}_bordas_p.${arq_ext}");
                                    
            $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_pequena = '%s' WHERE $chave_primaria = $codigo", 
                       texto2bd("${codigo}_bordas_p.${arq_ext}"));
            
            $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
            }
          } 
        } 
        
        // Alterando as Imagens grandes
        if(count($foto_g['name']) > 0) {     
          if(trim($foto_g['name']) != '') {
            $arq_info = pathinfo($foto_g['name']);
            $arq_ext = $arq_info['extension'];
            if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $foto_g["type"])) {
              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
            }
            else {              
              $resEdicaoImagem &= move_uploaded_file($foto_g['tmp_name'], UPLOAD_DIR."/bordas/${codigo}_bordas_g.${arq_ext}");
                                      
              $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_grande = '%s' WHERE $chave_primaria = $codigo", 
                         texto2bd("${codigo}_bordas_g.${arq_ext}"));
              
              $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
            }
          }  
        }
      
      if(mysql_query($SqlEdicao)) {

          $resEdicaoTamanho = true;
          if (is_array($tamanho_checkbox))
          {
            for($t = 0; $t < count($tamanho); $t++)
            {
              if (in_array($tamanho[$t], $tamanho_checkbox))
              {
                $cor_estoque = ($estoque[$t] > 0) ? $estoque[$t] : 0;
                $sql_contar_registros = "SELECT * FROM ipi_tamanhos_ipi_bordas_estoque WHERE cod_tamanhos = '".$tamanho[$t]."' AND cod_bordas = '".$codigo."'";
                $res_contar_registros = mysql_query($sql_contar_registros);
                $num_contar_registros = mysql_num_rows($res_contar_registros);

                if(!$num_contar_registros)
                {
                  $SqlEdicaoTamanho = sprintf("INSERT INTO ipi_tamanhos_ipi_bordas_estoque (cod_bordas, cod_tamanhos, quantidade_estoque) VALUES (%d, %d, %d)", $codigo, $tamanho[$t], $cor_estoque);
                  // echo "<br />G: ".$SqlEdicaoTamanho;
                  $resEdicaoTamanho &= mysql_query($SqlEdicaoTamanho);
                  // echo $SqlEdicaoTamanho."<br>";
                }
                else
                {
                  $SqlEdicaoTamanho = sprintf("UPDATE ipi_tamanhos_ipi_bordas_estoque SET quantidade_estoque = '%d' WHERE cod_bordas = '%s' AND cod_tamanhos = '%d'", $cor_estoque, $codigo, $tamanho[$t]);
                  // echo "<br />G: ".$SqlEdicaoTamanho;
                  $resEdicaoTamanho &= mysql_query($SqlEdicaoTamanho);
                  // echo $SqlEdicaoTamanho."<br>";
                }
              }
            }
          }

          if($resEdicaoTamanho)
          {
            mensagemOk('Registro alterar com êxito!');
          } 
          else 
          {
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

function excluirImagem_pequena(cod) {
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
    var acao = 'excluir_imagem_pequena';
    var cod_bordas = cod;
    
    if(cod_bordas > 0) {
      var url = 'acao=' + acao + '&cod_bordas=' + cod_bordas;
      
      new Request.JSON({url: 'ipi_bordas_ajax.php', onComplete: function(retorno) {
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
}

function excluirImagem(cod) {
  if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
    var acao = 'excluir_imagem';
    var cod_bordas = cod;
    
    if(cod_bordas > 0) {
      var url = 'acao=' + acao + '&cod_bordas=' + cod_bordas;
            
      new Request.JSON({url: 'ipi_bordas_ajax.php', onComplete: function(retorno) {
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
      document.frmIncluir.borda.value = '';
      document.frmIncluir.cod_ingredientes.value = '';
      
      marcaTodosEstado('marcar_tamanho', false);
      
      // Limpando todos os campos input para Preço e Fidelidade
      var input = document.getElementsByTagName('input');
      for (var i = 0; i < input.length; i++) {
        if(input[i].name.match('preco')) { 
          input[i].value = ''; 
        }
        else if(input[i].name.match('fidelidade')) { 
          input[i].value = ''; 
        }
        else if(input[i].name.match('qtde_consumo_estoque')) { 
          input[i].value = ''; 
        }
      }
      
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
              <td align="center">Cod Borda</td>
              <td align="center">Borda</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaBordas = "SELECT * FROM $tabela ORDER BY borda";
          $resBuscaBordas = mysql_query($SqlBuscaBordas);
          
          while ($objBuscaBordas = mysql_fetch_object($resBuscaBordas)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaBordas->$chave_primaria.'"></td>';
            echo '<td align="center">'.bd2texto($objBuscaBordas->cod_bordas).'</td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaBordas->$chave_primaria.')">'.bd2texto($objBuscaBordas->borda).'</a></td>';
            
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
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)" enctype="multipart/form-data">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="borda">Borda</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="borda" id="borda" maxlength="45" size="58" value="<? echo texto2bd($objBusca->borda) ?>"></td></tr>
    
    <tr>
        <td class="tdbl tdbr sep"><input type="checkbox" name="novidade"
            <? if ($objBusca->novidade) echo 'checked'?>>&nbsp;<label for="novidade">Novidade</label></td>
    </tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="cod_ingredientes">Ingrediente</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    	<select name="cod_ingredientes" id="cod_ingredientes" class="requerido">
    		<option value=""></option>
    		
    		<?

    		$con = conectabd();
    		
    		$SqlBuscaIngredientes = "SELECT * FROM ipi_ingredientes WHERE ativo = 1 ORDER BY ingrediente";
    		$ResBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
    		
    		while($ObjBuscaIngredientes = mysql_fetch_object($ResBuscaIngredientes))
    		{
    		    echo '<option value="'.$ObjBuscaIngredientes->cod_ingredientes.'" ';
    		    
    		    if($ObjBuscaIngredientes->cod_ingredientes == $objBusca->cod_ingredientes)
    		    {
    		        echo 'selected';
    		    }
    		    
    		    echo '>'.bd2texto($ObjBuscaIngredientes->ingrediente).'</option>';
    		}
    		
    		
    		
    		?>
    		
    	</select>
    </td></tr>
    
    <tr>
		<td class="legenda tdbl tdbr"><label for="foto_g">Imagem grande (*.png, *.jpg)</label></td>
	</tr>
        
    <?
    if (is_file(UPLOAD_DIR . '/bordas/' . $objBusca->foto_grande))
    {
        echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_g_figura" style="padding: 15px;">';
        
        echo '<img height="100" src="' . UPLOAD_DIR . '/bordas/' . $objBusca->foto_grande . '">';
        
        echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem(' . $objBusca->$chave_primaria . ');"></td></tr>';
    }
    ?>  
    <tr>
		<td class="sep tdbl tdbr sep">
		<input type="file" name="foto_g" id="foto_g" height="100"></td>
	</tr>
     
     
	<tr>
		<td class="legenda tdbl tdbr"><label for="foto_p">Imagem pequena(*.png, *.jpg)</label></td>
	</tr>
     
    <?
    if (is_file(UPLOAD_DIR . '/bordas/' . $objBusca->foto_pequena))
    {
        echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_p_figura" style="padding: 15px;">';
        
        echo '<img height="50" src="' . UPLOAD_DIR . '/bordas/' . $objBusca->foto_pequena . '">';
        
        echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem_pequena(' . $objBusca->$chave_primaria . ');"></td></tr>';
    }
    ?>
    <tr>
		<td class="sep tdbl tdbr sep">
		<input type="file" name="foto_p" id="foto_p" height="100"></td>
	</tr>  
    
    
<tr><td colspan="2" align="center" class="tdbl tdbb tdbr">
    <table class="listaEdicao" cellpadding="0" cellspacing="0">
      <thead>
        <tr>
          <td align="center" width="20"><input type="checkbox"
            class="marcar_tamanho"
            onclick="marcaTodosEstado('marcar_tamanho', this.checked);"></td>
          <td align="center"><label>Tamanho</label></td>
          <td align="center"><label>Quantidade <br /> de <br /> consumo (gramas)</label></td>
          
        </tr>
      </thead>
      <tbody>
      <? 

        $SqlBuscaTamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
        $resBuscaTamanhos = mysql_query($SqlBuscaTamanhos);
        
        while ( $objBuscaTamanhos = mysql_fetch_object($resBuscaTamanhos) )
        {
          if ($codigo > 0)
            $objBuscaPrecosTamanho = executaBuscaSimples(sprintf("SELECT * FROM ipi_tamanhos_ipi_bordas_estoque WHERE cod_bordas = %d AND cod_tamanhos = %d order by cod_tamanhos", $codigo, $objBuscaTamanhos->cod_tamanhos), $con);
          else
            $objBuscaPrecosTamanho = null;

          echo '<tr>';
          echo '<input type="hidden" name="tamanho[]" value="' . $objBuscaTamanhos->cod_tamanhos . '">';
            
          if ($objBuscaPrecosTamanho)
            echo '<td align="center"><input type="checkbox" class="marcar_tamanho" checked="checked" name="tamanho_checkbox[]" value="' . $objBuscaTamanhos->cod_tamanhos . '" onclick="limpaPrecoFidelidade(' . $objBuscaTamanhos->cod_tamanhos . ')"></td>';
          else
            echo '<td align="center"><input type="checkbox" class="marcar_tamanho" name="tamanho_checkbox[]" value="' . $objBuscaTamanhos->cod_tamanhos . '" onclick="limpaPrecoFidelidade(' . $objBuscaTamanhos->cod_tamanhos . ')"></td>';

          echo '  <td><label>' . $objBuscaTamanhos->tamanho . '</label></td>';
          echo '  <td align="center"><input type="text" name="estoque[]" id="estoque_' . $objBuscaTamanhos->cod_tamanhos . '" maxsize="5" size="3" value="' . ($objBuscaPrecosTamanho->quantidade_estoque ? $objBuscaPrecosTamanho->quantidade_estoque : 0) . '" onKeyPress="return ApenasNumero(event)"></td>';
          echo '</tr>';
        }
        desconectabd($con);
      ?>
      </tbody>
    </table>
    </td></tr>


    <tr><td class="tdbl tdbr sep">
    </td></tr>


    <tr><td colspan="2" align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>

    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
