<?php

/**
 * ipi_preco_fracao.php: Cadastro de Preços de Fração
 * 
 * Índice: cod_fracoes, cod_tamanhos, cod_pizzarias
 * Tabela: ipi_tamanhos_ipi_fracoes
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Preços de frações por '.(TIPO_EMPRESA).'s');

$acao = validaVarPost('acao');

$tabela = 'ipi_fracoes';
$chave_primaria = 'cod_fracoes';
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
if(validaVarGet("cod_filt", '/[0-9]+/')!="")
{
  $cod_pizzaria_filt = validaVarGet("cod_filt", '/[0-9]+/');
}
if(validaVarPost("cod_filt", '/[0-9]+/')!="")
{
  $cod_pizzaria_filt = validaVarPost("cod_filt", '/[0-9]+/');
}
if(validaVarPost('cod_pizzarias_filt')!="")
{
  $cod_pizzaria_filt = validaVarPost('cod_pizzarias_filt');
}
//echo "FILT".$cod_pizzaria_filt."/FILT";
switch ($acao)
{
    case 'editar' :
        $codigo = validaVarPost($chave_primaria);
        $pizza = validaVarPost('pizza');
        $tamanho = validaVarPost('tamanho');
        $tamanho_checkbox = validaVarPost('tamanho_checkbox');
        $fidelidade = validaVarPost('fidelidade');
        $preco = validaVarPost('preco');
        $cod_pizzarias = validaVarPost('checkpizzaria');
        $selecao_obrigatoria = validaVarPost("selecao");
        $con = conectabd();
        
      
        if($cod_pizzaria_filt)
        {
          $cod_pizzaria = $cod_pizzaria_filt;
          $res_del_tamanho = true;
          if (is_array($tamanho))
          {
              $sql_del_tamanho = "DELETE FROM ipi_tamanhos_ipi_fracoes WHERE $chave_primaria = $codigo and cod_pizzarias = $cod_pizzaria";
              $res_del_tamanho &= mysql_query($sql_del_tamanho);
          }
          
          if ($res_del_tamanho)
          {
              $resEdicaoTamanho = TRUE;
              if (is_array($tamanho_checkbox))
              {
                  for($t = 0; $t < count($tamanho); $t++)
                  {
                      if (in_array($tamanho[$t], $tamanho_checkbox))
                      {
                          $cor_preco = ($preco[$t] > 0) ? moeda2bd($preco[$t]) : 0;
                          $cor_fidelidade = ($fidelidade[$t] > 0) ? $fidelidade[$t] : 0;
                          $cor_selecao = ($selecao_obrigatoria[$tamanho[$t]] > 0) ? $selecao_obrigatoria[$tamanho[$t]] : 0;
                          $SqlEdicaoTamanho = sprintf("INSERT INTO ipi_tamanhos_ipi_fracoes (cod_fracoes, cod_tamanhos, cod_pizzarias, preco, pontos_fidelidade, selecao_padrao_fracao) VALUES (%d, %d, %d, %s, %d, %d)", $codigo, $tamanho[$t],$cod_pizzaria, $cor_preco, $cor_fidelidade,$cor_selecao);
                         // echo "<br />G: ".$SqlEdicaoTamanho;
                          $resEdicaoTamanho &= mysql_query($SqlEdicaoTamanho);
                      //    echo $SqlEdicaoTamanho."<br>";
                      }
                  }
              }
              
              
              if ($resEdicaoTamanho)
              {
                  $resEdicao = true;
                  mensagemOk('Registro alterado com êxito!');
              }
              else
              {
                  $resEdicao = false;
                  mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
              }
            
        }
        else
        {
            $resEdicao = false;
            mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
      }
            
        
        desconectabd($con);
        break;
    case 'copiar_precos' :
        $cod_matriz = validaVarPost('cod_matriz');
        $cod_copiar = validaVarPost('cod_filt');
        
        $con = conectar_bd();
        $sql_del_tamanho = "DELETE FROM ipi_tamanhos_ipi_fracoes WHERE cod_pizzarias = $cod_copiar";
        
        $res_del_tamanho = mysql_query($sql_del_tamanho);
                      //echo "<br />D: ".$sql_del_tamanho;
        $res_adicionar_preco = TRUE;
        if($res_del_tamanho)
        {       
          $sql_pegar_precos = "SELECT * FROM ipi_tamanhos_ipi_fracoes WHERE cod_pizzarias = $cod_matriz";
          $res_pegar_precos = mysql_query($sql_pegar_precos);
          
          while($obj_pegar_precos = mysql_fetch_object($res_pegar_precos))
          {
              $sql_adicionar_preco = "insert into ipi_tamanhos_ipi_fracoes(cod_fracoes,cod_tamanhos,cod_pizzarias,preco,pontos_fidelidade, selecao_padrao_fracao) values (".$obj_pegar_precos->cod_fracoes.",".$obj_pegar_precos->cod_tamanhos.",".$cod_copiar.",".$obj_pegar_precos->preco.",".$obj_pegar_precos->pontos_fidelidade.",".$obj_pegar_precos->selecao_padrao_fracao.")";
              $res_adicionar_preco &= mysql_query($sql_adicionar_preco);
          }
          
          if ($res_adicionar_preco)
          {
              mensagemOk('Preços copiados com êxito!');
          }
          else
          {
              mensagemErro('Erro ao copiar preços', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          }
        
        }
        else
          {
              mensagemErro('Erro ao copiar preços', 'Por favor, verifique se a pizzaria tem algum preço cadastrado.');
          }
        desconectar_bd($con);
        break;    
}

?>

<link rel="stylesheet" type="text/css" media="screen"
  href="../lib/css/tabs_simples.css" />

<script>

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

  var input2 = new Element('input', {
    'type': 'hidden',
    'name': 'cod_filt',
    'value': <? echo "'$cod_pizzaria_filt'" ?>
  });
  
  input.inject(form);
  input2.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}


function copiar_precos()
{
  if(confirm('TEM CERTEZA QUE DESEJA COPIAR OS PREÇOS?\n(ESSE PROCESSO NÃO TERÁ VOLTA)\n')) 
  { 
    var cod_pizzarias_matriz = 1;
    var form = new Element('form', {
      'action': '<?
      echo $_SERVER['PHP_SELF']?>',
      'method': 'post'
    });
    var input = new Element('input', {
      'type': 'hidden',
      'name': 'cod_matriz',
      'value': cod_pizzarias_matriz
    });

    var input2 = new Element('input', {
      'type': 'hidden',
      'name': 'cod_filt',
      'value': <? echo "'$cod_pizzaria_filt'" ?>
    });
    
    var input3 = new Element('input', {
      'type': 'hidden',
      'name': 'acao',
      'value': 'copiar_precos'
    });
    
    input.inject(form);
    input2.inject(form);
    input3.inject(form);
    $(document.body).adopt(form);
    form.submit();  
  }
}

function limpaPrecoFidelidade(cod) {
  document.getElementById('preco_' + cod).value = '';
  document.getElementById('fidelidade_' + cod).value = '';
}

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.<?
echo $chave_primaria?>.value > 0) {
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
      document.frmIncluir.<?
      echo $chave_primaria?>.value = '';
      document.frmIncluir.pizza.value = '';
      document.frmIncluir.tipo.value = '';
      document.frmIncluir.sugestao.checked = false;
      document.frmIncluir.novidade.checked = false;
      
      marcaTodosEstado('marcar_tamanho', false);
      marcaTodosEstado('marcar_ingrediente', false);
      
      // Limpando todos os campos input para Preço e Fidelidade
      var input = document.getElementsByTagName('input');
      for (var i = 0; i < input.length; i++) {
        if(input[i].name.match('preco')) { 
          input[i].value = ''; 
        }
      }
      
      var input = document.getElementsByTagName('input');
      for (var i = 0; i < input.length; i++) {
        if(input[i].name.match('fidelidade')) { 
          input[i].value = ''; 
        }
      }
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
});

</script>
<form name="frmPizzariaFilt" method="post">

<? echo ucfirst(TIPO_EMPRESA); ?>: <select name="cod_pizzarias_filt" style="width: 300px;">
<option value=''>Selecione um(a) <? echo ucfirst(TIPO_EMPRESA); ?></option>
<?
$con = conectabd();

$SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
$resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
{
  echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
  if($cod_pizzaria_filt == $objBuscaPizzarias->cod_pizzarias)
    echo 'selected';
  echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
}
desconectabd($con);
?>
</select>
<input class="botaoAzul" type="submit" value="Filtrar">
</form>

<div id="tabs">
<div class="menuTab">
<ul>
  <li><a href="javascript:;">Editar</a></li>
  <li><a href="javascript:;">Incluir</a></li>
</ul>
</div>

<!-- Tab Editar -->
<div class="painelTab">
<table>
  <tr>

    <!-- Conteúdo -->
    <td class="conteudo">
    
      <div id='botao_copiar'></div>
    <form name="frmExcluir" method="post"
      onsubmit="return verificaCheckbox(this)">
<? if($cod_pizzaria_filt>0): ?>
<table class="listaEdicao" cellpadding="0" cellspacing="0">
      <thead>
        <tr>
          <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
          <td align="center">Frações</td>
          <?
           $con = conectabd();
            $sql_precos = "select tamanho,cod_tamanhos from ipi_tamanhos order by cod_tamanhos";
            $res_precos = mysql_query($sql_precos);
            while($obj_precos = mysql_fetch_object($res_precos))
            {
             echo '<td align="center">'.$obj_precos->tamanho.'</td>';
            }
            desconectar_bd($con);
          ?>
          <!-- <td align="center">Seleção Padrão</td> -->
        </tr>
      </thead>
      <tbody>
          <?
        
        $con = conectabd();
        
        $sql_buscar_fracao = "SELECT t.* FROM $tabela t ORDER BY fracoes ";// WHERE cod_pizzarias = ".validaVarPost('cod_pizzarias_filt')." 
        //echo $sql_buscar_fracao;
        $res_buscar_fracao = mysql_query($sql_buscar_fracao);
        $i=0;
        while ( $obj_buscar_fracao = mysql_fetch_object($res_buscar_fracao) )
        {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_fracao->$chave_primaria . '"></td>';
            
            echo '<td align="center"><a href="javascript:;" onclick="editar(' . $obj_buscar_fracao->$chave_primaria . ')">' . bd2texto($obj_buscar_fracao->fracoes) . '</a></td>';
            $sql_tam = "select tamanho,cod_tamanhos from ipi_tamanhos order by cod_tamanhos";
            $res_tam = mysql_query($sql_tam);
            while($obj_tam = mysql_fetch_object($res_tam))
            {
            
              $sql_precos = "select tp.preco,t.tamanho,t.cod_tamanhos,tp.selecao_padrao_fracao from ipi_tamanhos_ipi_fracoes tp inner join ipi_tamanhos t on t.cod_tamanhos = tp.cod_tamanhos where tp.cod_fracoes = ".$obj_buscar_fracao->cod_fracoes." and tp.cod_tamanhos = ".$obj_tam->cod_tamanhos." and tp.cod_pizzarias = '$cod_pizzaria_filt' order by cod_tamanhos";
              $res_precos = mysql_query($sql_precos);
              $obj_precos = mysql_fetch_object($res_precos);
              
              echo '<td align="center">'.($obj_precos->preco ?'R$' :'' ).' '.bd2moeda($obj_precos->preco).'&nbsp; '.($obj_precos->selecao_padrao_fracao ?' (Padrão)' : '' ).'</td>';

              if($obj_precos->preco!="") $i++;

            }
           // echo '<td align="center">'..'&nbsp; </td>';
            echo '</tr>';
        }
        if($cod_pizzaria_filt !="" && $cod_pizzaria_filt !="1")
        {
         // echo 'aaa<script>alert('.$i.');</script>';
         // if($i<=9)
         // {
            echo '<script> $("botao_copiar").innerHTML = "<input type=\'button\' class=\'botao\' id=\'botao_copiar\' onclick=\'copiar_precos()\' value=\'Copiar preços da pizzaria matriz\' />" ;</script>';
            
          //}
        }
        desconectabd($con);
        
        ?>
          
          </tbody>
    </table>
  <? endif;?>
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="cod_filt" value="<? echo $cod_pizzaria_filt ?>" ></form>
    </td>
    <!-- Conteúdo -->

    <!-- Barra Lateral -->
    <td class="lateral">
    <div class="blocoNavegacao">
    <ul>
      <li>Preços</li>   
      <li><a href="ipi_preco_adicionais.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Gergilim</a></li>
      <li><a href="ipi_preco_borda.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Bordas</a></li>
      <li><a href="ipi_preco_ingredientes.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Adicionais</a></li>
      <li><a href="ipi_preco_bebida.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Bebidas</a></li>
      <li><a href="ipi_preco_pizza.php?cod_filt=<? echo $cod_pizzaria_filt ;?>"><? echo ucfirst(TIPO_PRODUTOS)?></a></li>
    </ul>
    </div>
    </td>
    <!-- Barra Lateral -->

  </tr>
</table>
</div>
<!-- Tab Editar --> <!-- Tab Incluir -->
<div class="painelTab">
    <?
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    if ($codigo > 0)
    {
        $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");        
    }
    ?>
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data"
  onsubmit="return validaRequeridos(this)">

    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
  <tr>
    <td class="legenda tdbl tdbr tdbt"><label 
      for="fracoes">Nº frações: </label></td>
  </tr>
  <tr>
    <td class="tdbl tdbr"><input type="text"
      name="fracoes" id="fracoes" maxlength="45" size="45"
      value="<?echo texto2bd($objBusca->fracoes)?>" readonly></td>
  </tr>

  <tr>
    <td class="tdbl tdbr sep">
    <table class="listaEdicao" cellpadding="0" cellspacing="0">
      <thead>
        <tr>
          <td align="center" width="20"><input type="checkbox"
            class="marcar_tamanho"
            onclick="marcaTodosEstado('marcar_tamanho', this.checked);"></td>
          <td align="center"><label>Tamanho</label></td>
          <td align="center"><label>Preço</label></td>
          <td align="center"><label>Preço <br />Fidelidade</label></td>
          <td align="center"><label>Seleção<br /> Padrão</label></td>
        </tr>
      </thead>
      <tbody>

        <?
        $con = conectar_bd();
        $SqlBuscaTamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
        $resBuscaTamanhos = mysql_query($SqlBuscaTamanhos);
        
        while ( $objBuscaTamanhos = mysql_fetch_object($resBuscaTamanhos) )
        {
            echo '<tr>';
            
            if ($codigo > 0)
                $objBuscaPrecosTamanho = executaBuscaSimples(sprintf("SELECT * FROM ipi_tamanhos_ipi_fracoes WHERE cod_fracoes = %d AND cod_tamanhos = %d and cod_pizzarias= %d order by cod_tamanhos", $codigo, $objBuscaTamanhos->cod_tamanhos,$cod_pizzaria_filt), $con);
            else
                $objBuscaPrecosTamanho = null;
            
            echo '<input type="hidden" name="tamanho[]" value="' . $objBuscaTamanhos->cod_tamanhos . '">';
            
            if ($objBuscaPrecosTamanho)
                echo '<td align="center"><input type="checkbox" class="marcar_tamanho" checked="checked" name="tamanho_checkbox[]" value="' . $objBuscaTamanhos->cod_tamanhos . '" onclick="limpaPrecoFidelidade(' . $objBuscaTamanhos->cod_tamanhos . ')"></td>';
            else
                echo '<td align="center"><input type="checkbox" class="marcar_tamanho" name="tamanho_checkbox[]" value="' . $objBuscaTamanhos->cod_tamanhos . '" onclick="limpaPrecoFidelidade(' . $objBuscaTamanhos->cod_tamanhos . ')"></td>';
            
            echo '<td><label>' . $objBuscaTamanhos->tamanho . '</label></td>';
            echo '<td align="center"><input type="text" name="preco[]" id="preco_' . $objBuscaTamanhos->cod_tamanhos . '" maxsize="5" size="6" value="' . bd2moeda($objBuscaPrecosTamanho->preco) . '" onKeyPress="return formataMoeda(this, \'.\', \',\', event)"></td>';
            echo '<td align="center"><input type="text" name="fidelidade[]" id="fidelidade_' . $objBuscaTamanhos->cod_tamanhos . '" maxsize="5" size="3" value="' . $objBuscaPrecosTamanho->pontos_fidelidade . '" onKeyPress="return ApenasNumero(event)"> pontos</td>';

            echo '<td align="center"><input type="checkbox" name="selecao['.$objBuscaTamanhos->cod_tamanhos.']" id="selecao_' . $objBuscaTamanhos->cod_tamanhos . '" value="1" '.($objBuscaPrecosTamanho->selecao_padrao_fracao ? 'checked="checked"' : '').' ></td>';//' . $objBuscaPrecosTamanho->selecao_padrao_fracao . '
            
            echo '</tr>';
        }
        
        desconectabd($con);
        ?>
        
        </tbody>
    </table>
    </td>
  </tr>

  <tr>
    <td colspan="2" align="center" class="tdbl tdbb tdbr"><input
      name="botao_submit" class="botao" type="submit" value="Cadastrar"></td>
  </tr>

</table>

<input type="hidden" name="acao" value="editar"> <input type="hidden"
  name="<?
echo $chave_primaria?>" value="<?
echo $codigo?>">
<input type="hidden" name="cod_filt" value="<? echo $cod_pizzaria_filt ?>" >
</form>
</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>