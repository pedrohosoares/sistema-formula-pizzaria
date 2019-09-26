<?php

/**
 * ipi_bebida_conteudo.php: Cadastro Conteúdo da Bebida
 * 
 * Índice: cod_bebidas_ipi_conteudos
 * Tabela: ipi_bebidas_ipi_conteudos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Preços de Bebidas por '.TIPO_EMPRESA);

$acao = validaVarPost('acao');

$tabela = 'ipi_bebidas_ipi_conteudos';
$chave_primaria = 'cod_bebidas_ipi_conteudos';
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

switch($acao) {
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $cod_bebidas = validaVarPost('cod_bebidas');
    $cod_conteudos = validaVarPost('cod_conteudos');
    $preco = moeda2bd(validaVarPost('preco'));
    $valor_imposto = moeda2bd(validaVarPost('valor_imposto'));
    $fidelidade = validaVarPost('fidelidade');
    
    $situacao = validaVarPost('situacao');
    $venda_net = validaVarPost('venda_net');
    
    $quantidade_minima = validaVarPost('quantidade_minima');
    $quantidade_maxima = validaVarPost('quantidade_maxima');
    $quantidade_perda = validaVarPost('quantidade_perda');
    

    
   $con = conectabd();//`ipi_conteudos_pizzarias
    
    $sql_confere_precos = "select * from ipi_conteudos_pizzarias where cod_bebidas_ipi_conteudos = $codigo and cod_pizzarias = $cod_pizzaria_filt";
    $res_confere_precos = mysql_query($sql_confere_precos);
    
    if(mysql_num_rows($res_confere_precos) <= 0) 
    {
      $SqlEdicao = sprintf("INSERT INTO ipi_conteudos_pizzarias (cod_pizzarias,cod_bebidas_ipi_conteudos, preco, pontos_fidelidade, situacao, venda_net, valor_imposto) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
                           $cod_pizzaria_filt, $codigo, $preco, $fidelidade, $situacao, $venda_net, $valor_imposto);
      $res_edicao = mysql_query($SqlEdicao);
      $codigo = mysql_insert_id();
      $resEdicaoTamanhoIngrediente = true;
        
      if($resEdicaoTamanhoIngrediente)
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE ipi_conteudos_pizzarias SET preco = '%s', pontos_fidelidade = '%s', situacao = '%s', venda_net = %d, valor_imposto = '%s' WHERE cod_pizzarias = $cod_pizzaria_filt and cod_bebidas_ipi_conteudos = $codigo ", 
                           $preco, $fidelidade, $situacao, $venda_net, $valor_imposto);
      //echo  "ALTERAR ".$SqlEdicao;
      $res_edicao = mysql_query($SqlEdicao);
        
      if($res_edicao)
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        //echo $SqlEdicao."AAA".$situacao;
      
    }
    
    desconectabd($con);
  break;
  case 'copiar_precos':
      $cod_matriz = validaVarPost('cod_matriz');
      $cod_copiar = validaVarPost('cod_filt');
        
      $con = conectar_bd(); 
      $sql_deletar = "delete from ipi_conteudos_pizzarias where cod_pizzarias = $cod_pizzaria_filt";
      $res_deletar = mysql_query($sql_deletar);
      
      if($res_deletar)
      {
        $sql_copiar = "insert into ipi_conteudos_pizzarias(cod_pizzarias,cod_bebidas_ipi_conteudos, preco, pontos_fidelidade, situacao, venda_net, quantidade_minima, quantidade_maxima, quantidade_perda, valor_imposto) (select $cod_copiar,cod_bebidas_ipi_conteudos, preco, pontos_fidelidade, situacao, venda_net, quantidade_minima, quantidade_maxima, quantidade_perda, valor_imposto from ipi_conteudos_pizzarias where cod_pizzarias = $cod_matriz)";
        $res_copiar = mysql_query($sql_copiar);
        
        if($res_copiar)
        {
             mensagemOk('Preços copiados com êxito!');
        }
        else
        {
            mensagemErro('Erro ao copiar preços', 'Por favor, verifique se a '.ucfirst(TIPO_EMPRESA).' está selecionada e filtrada.');
        }
      
      
      }
      else
      {
        mensagemErro('Erro aao copiar preços', 'Por favor, verifique se a '.ucfirst(TIPO_EMPRESA).' está selecionada e filtrada.');
      }
      
      desconectar_bd($con);
  break;
  case 'alterar_impressora':
    $excluir = validaVarPost('excluir');
    $cod_impressoras = validaVarPost("cod_impressoras");
    $indices_sql = implode(',', $excluir);
    
    $conexao = conectabd();
    
    $sql_del = "UPDATE ipi_conteudos_pizzarias set cod_impressoras = '$cod_impressoras' WHERE $chave_primaria IN ($indices_sql) and cod_pizzarias = '$cod_pizzaria_filt'";
    /*echo $sql_del;*/
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

          case 'excluir_impressora':
          $excluir = validaVarPost('excluir');
          $cod_impressoras = validaVarPost("cod_impressoras");
          $indices_sql = implode(',', $excluir);
          
          $conexao = conectabd();
          
          $sql_del = "UPDATE ipi_conteudos_pizzarias set cod_impressoras = NULL WHERE $chave_primaria IN ($indices_sql) and cod_pizzarias = '$cod_pizzaria_filt'";
          /*echo $sql_del;*/
          // die($sql_del);
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
}
      //echo "X: ".$SqlEdicao;

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

<script>

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
  
  var input2 = new Element('input', {
    'type': 'hidden',
    'name': 'cod_filt',
    'value': <? echo $cod_pizzaria_filt ?>
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
      'value': <? echo $cod_pizzaria_filt ?>
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
      document.frmIncluir.cod_bebidas.value = '';
      document.frmIncluir.cod_conteudos.value = '';
      document.frmIncluir.preco.value = '';
      //document.frmIncluir.quantidade_minima.value = '';
      //document.frmIncluir.quantidade_maxima.value = '';
      //document.frmIncluir.quantidade_perda.value = '';
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
});

function verificar_acao_impressora(acao, frm)
{
  if (acao=="excluir")
  {
    document.frmExcluir.acao.value = 'excluir_impressora';
    
  }
  else
  {
    document.frmExcluir.acao.value = 'alterar_impressora';

  }
  frm.submit();
}
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
    <table><tr>
  
    <!-- Conteúdo -->
    <td class="conteudo">
          <div id='botao_copiar'></div>
      <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">
    
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
          <tr>
          <td>
            Impressora: <select name="cod_impressoras" style="width: 150px;">
              <!-- <option value="TODOS">Todas <? echo ucfirst(TIPO_EMPRESA); ?>s</option> -->
              <?
              $con = conectabd();
              
              $SqlBuscaImpressoras = "SELECT * FROM ipi_impressoras ORDER BY nome_impressora";
              $resBuscaImpressoras = mysql_query($SqlBuscaImpressoras);
              
              while($objBuscaImpressoras = mysql_fetch_object($resBuscaImpressoras)) {
                echo '<option value="'.$objBuscaImpressoras->cod_impressoras.'" ';
                
                /*if($cod_Impressoras == $objBuscaImpressoras->cod_Impressoras)
                  echo 'selected';*/
                
                echo '>'.bd2texto($objBuscaImpressoras->nome_impressora).'</option>';
              }
              
              desconectabd($con);
              ?>
            </select>
                        <input class="botaoAzul" type="button" value="Alterar Selecionados" onclick="verificar_acao_impressora('alterar', frmExcluir)">
            <input class="botaoAzul" type="button" onclick="verificar_acao_impressora('excluir', frmExcluir)" value="Excluir Impressora">
            </td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
              <td align="center">Código</td>
              <td align="center">Tipo Bebida</td>
              <td align="center">Bebida</td>
              <td align="center">Conteúdo</td>
              <td align="center">Preço</td>
              <td align="center">Ativo</td>
              <td align="center">Venda Net</td>
              <td align="center" width="150">Impressora</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();

         
          $SqlBuscaRegistros = "SELECT * FROM $tabela bc LEFT JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) LEFT JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) LEFT JOIN ipi_tipo_bebida tb ON (b.cod_tipo_bebida= tb.cod_tipo_bebida) ORDER BY bc.codigo_cliente_bebida ASC, tb.tipo_bebida, b.bebida, c.conteudo";
          //echo $SqlBuscaRegistros;
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          $i=0;
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            $sql_busca_precos = "SELECT cp.*,ip.nome_impressora FROM ipi_conteudos_pizzarias cp left join ipi_impressoras ip on ip.cod_impressoras = cp.cod_impressoras where cp.cod_bebidas_ipi_conteudos = ".$objBuscaRegistros->cod_bebidas_ipi_conteudos." and cp.cod_pizzarias = $cod_pizzaria_filt";
            //echo $sql_busca_precos;
            $res_busca_precos = mysql_query($sql_busca_precos);
            $obj_busca_precos = mysql_fetch_object($res_busca_precos);
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$obj_busca_precos->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.$objBuscaRegistros->codigo_cliente_bebida.'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.$objBuscaRegistros->tipo_bebida.'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.$objBuscaRegistros->bebida.'</a></td>';
            echo '<td align="center">'.$objBuscaRegistros->conteudo.'</td>';
            echo '<td align="center">'.bd2moeda($obj_busca_precos->preco).'</td>';
            if($obj_busca_precos->preco!="")
            {
              $i++;
            }
            echo '<td align="center">';
            if($obj_busca_precos->situacao=='ATIVO')
        echo '<img src="../lib/img/principal/ok.gif">';
            else
        echo '<img src="../lib/img/principal/erro.gif">';
        
            echo '</td>';
            
            echo '<td align="center">';
            if($obj_busca_precos->venda_net=='1')
        echo '<img src="../lib/img/principal/ok.gif">';
            else
        echo '<img src="../lib/img/principal/erro.gif">';
        
            echo '</td>';

            echo '<td align="center">'.($obj_busca_precos->nome_impressora).'</td>';
            
            echo '</tr>';
          }
          
        if($cod_pizzaria_filt !=""  && $cod_pizzaria_filt !="1")
        {
         // echo 'aaa<script>alert('.$i.');</script>';
          //if($i<=2)
          //{
            echo '<script> $("botao_copiar").innerHTML = "<input type=\'button\' class=\'botao\' id=\'botao_copiar\' onclick=\'copiar_precos()\' value=\'Copiar preços da '.TIPO_EMPRESA.' matriz\' />" ;</script>';
            
         // }
        }
          
          desconectabd($con);
          
          ?>
          
          </tbody>
        </table>

        <input type="hidden" name="cod_pizzarias_filt" value="<? echo $cod_pizzaria_filt; ?>">
        <input type="hidden" name="acao" value="alterar_impressora">
      </form>
    
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li>Preços</li>
            <li><a href="ipi_preco_adicionais.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Preço adicionais</a></li>
            <li><a href="ipi_preco_borda.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Preço de Bordas</a></li>
            <li><a href="ipi_preco_ingredientes.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Preço de Ingredientes</a></li>
            <li><a href="ipi_preco_bebida.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Preço de Bebidas</a></li>
            <li><a href="ipi_preco_pizza.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Preço de <? echo ucfirst(TIPO_PRODUTOS)?></a></li>
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
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_bebidas">Bebidas</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select name="cod_bebidas"  disabled id ="cod_bebidas" style="width: 300px;">
        <option value=""></option>
        <?
        $con = conectabd();
        
        $SqlBuscaBebidas = "SELECT * FROM ipi_bebidas ORDER BY bebida";
        $resBuscaBebidas = mysql_query($SqlBuscaBebidas);
        
        while($objBuscaBebidas = mysql_fetch_object($resBuscaBebidas)) {
          echo '<option value="'.$objBuscaBebidas->cod_bebidas.'" ';
          
          if($objBuscaBebidas->cod_bebidas == $objBusca->cod_bebidas)
            echo 'selected';
            
          echo '>'.bd2texto($objBuscaBebidas->bebida).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="cod_conteudos">Conteúdos</label></td></tr>
    <tr><td class="tdbl tdbr sep">
      <select name="cod_conteudos" disabled  id="cod_conteudos" style="width: 300px;">
        <option value=""></option>
        <?
        $con = conectabd();
        
        $SqlBuscaConteudos = "SELECT * FROM ipi_conteudos ORDER BY conteudo";
        $resBuscaConteudos = mysql_query($SqlBuscaConteudos);
        
        while($objBuscaConteudos = mysql_fetch_object($resBuscaConteudos)) {
          echo '<option value="'.$objBuscaConteudos->cod_conteudos.'" ';
          
          if($objBuscaConteudos->cod_conteudos == $objBusca->cod_conteudos)
            echo 'selected';
            
          echo '>'.bd2texto($objBuscaConteudos->conteudo).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td></tr>
    
    <? $objBusca = executaBuscaSimples("SELECT * FROM ipi_conteudos_pizzarias WHERE $chave_primaria = $codigo and cod_pizzarias = $cod_pizzaria_filt");?>

     
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="preco">Preço</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="preco" id="preco" maxlength="10" size="3" value="<? echo bd2moeda($objBusca->preco) ?>" onKeyPress="return formataMoeda(this, '.', ',', event)"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="fidelidade">Fidelidade</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="fidelidade" id="fidelidade" maxlength="10" size="3" value="<? echo texto2bd($objBusca->pontos_fidelidade) ?>" onKeyPress="return ApenasNumero(event)"></td></tr>

    <tr><td class="legenda tdbl tdbr"><label for="valor_imposto">(%) Imposto</label></td></tr>
    <tr><td class="tdbl tdbr"><input type="text" name="valor_imposto" id="valor_imposto" maxlength="10" size="3" value="<? echo bd2moeda($objBusca->valor_imposto) ?>" onKeyPress="return formataMoeda(this, '.', ',', event)"></td></tr>
    
    <tr>
      <td class="legenda tdbl  tdbr">
        <label class="requerido" for="situacao">Situação</label>
      </td>
    </tr>
    <tr>
      <td class="tdbl tdbr sep">
      <select class="requerido" name="situacao" id="situacao">
        <option value=""></option>
        <option value="ATIVO" <? if( $objBusca->situacao == 'ATIVO') echo 'selected';?>>ATIVO</option>
        <option value="INATIVO" <? if( $objBusca->situacao == 'INATIVO') echo 'selected';?>>INATIVO </option>
      </select>
      </td>
    </tr>
    
     <tr>
      <td class="legenda tdbl  tdbr">
        <label class="requerido" for="venda_net">Vender no Site Público?</label>
      </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <select class="requerido" name="venda_net" id="venda_net">
            <option value=""></option>
            <option value="1" <? if( $objBusca->venda_net == '1') echo 'selected'; ?> value="1">SIM</option>
            <option value="0" <? if( $objBusca->venda_net == '0') echo 'selected'; ?> value="0">NÂO</option>
        </select>
        </td>
    </tr>

    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>


    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    <input type="hidden" name="cod_filt" value="<? echo $cod_pizzaria_filt ?>" >
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
