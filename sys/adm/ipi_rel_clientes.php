<?php

/**
 * Tela de consulta e alteração de dados de clientes.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       24/05/2010   FELIPE        Criado.
 * 1.0       17/09/2012   PEDRO H       Adicionado 'quicklink' pro perfil do FB, se vinculado.
 *
 */
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once 'ipi_req_quant_vendidas.php';

cabecalho('Relatório de Clientes');

$tabela = 'ipi_clientes';
$chave_primaria = 'cod_clientes';
$quant_pagina = 250;
$cod_enquetes = 1; // Forçado para a enquete mail

/*echo "<pre>";
print_r($_SESSION);
echo "</pre>";
die();*/
$acao = validaVarPost('acao');

$faturamento_inicial_filtro = (validaVarPost('faturamento_inicial_filtro')) ? validaVarPost('faturamento_inicial_filtro') : '';
$faturamento_final_filtro = (validaVarPost('faturamento_final_filtro')) ? validaVarPost('faturamento_final_filtro') : '';

     if(($faturamento_inicial_filtro!="") && ($faturamento_final_filtro!=""))
    {
       $faturamento_inicial_sql = data2bd($faturamento_inicial_filtro); 
        $faturamento_final_sql = data2bd($faturamento_final_filtro);
        $filtro_faturamento .= " AND p.data_hora_pedido BETWEEN  '$faturamento_inicial_sql 00:00:00' AND  '$faturamento_final_sql 23:59:59' ";
    }
// die($filtro_faturamento);
//Array para a relação dos campos da tabela cliente
// arr[NOME_DO_CAMPO_NA_TABELA] = "NOME DO CAMPO PARA O CLIENTE VER"
$arr_campos = array();

$arr_campos['cliente'][0]['campo'] = 'nome';
$arr_campos['cliente'][0]['nome_exibir'] = 'Nome';

$arr_campos['cliente'][1]['campo'] = 'email';
$arr_campos['cliente'][1]['nome_exibir'] = 'E-mail';

$arr_campos['cliente'][2]['campo'] = 'cpf';
$arr_campos['cliente'][2]['nome_exibir'] = 'CPF';

$arr_campos['cliente'][3]['campo'] = 'celular';
$arr_campos['cliente'][3]['nome_exibir'] = 'Celular';

$arr_campos['cliente'][4]['campo'] = 'ultimo_acesso';
$arr_campos['cliente'][4]['nome_exibir'] = 'Último Acesso';
/*$arr_campos['cliente'][0]['indicador_recebeu_pontos'] = '';*/
/*$arr_campos['cliente'][0]['cod_clientes_indicador'] = '';*/
$arr_campos['cliente'][5]['campo'] = 'observacao';
$arr_campos['cliente'][5]['nome_exibir'] = 'Observação';

$arr_campos['cliente'][6]['campo'] = 'nascimento';
$arr_campos['cliente'][6]['nome_exibir'] = 'Nascimento';
$arr_campos['cliente'][6]['formatacao'] = 'data';

$arr_campos['cliente'][7]['campo'] = 'sexo';
$arr_campos['cliente'][7]['nome_exibir'] = 'Sexo';

$arr_campos['cliente'][8]['campo'] = 'origem_cliente';
$arr_campos['cliente'][8]['nome_exibir'] = 'Origem do Cliente';

$arr_campos['cliente'][9]['campo'] = 'data_hora_cadastro';
$arr_campos['cliente'][9]['nome_exibir'] = 'Data Cadastro';
$arr_campos['cliente'][9]['formatacao'] = 'datahora';

$arr_campos['cliente'][10]['campo'] = 'situacao';
$arr_campos['cliente'][10]['nome_exibir'] = 'Situação';

$arr_campos['endereco'][0]['campo'] = 'estado';
$arr_campos['endereco'][0]['nome_exibir'] = 'Estado';

$arr_campos['endereco'][1]['campo'] = 'cidade';
$arr_campos['endereco'][1]['nome_exibir'] = 'Cidade';

$arr_campos['endereco'][2]['campo'] = 'bairro';
$arr_campos['endereco'][2]['nome_exibir'] = 'Bairro';

$arr_campos['endereco'][3]['campo'] = 'endereco';
$arr_campos['endereco'][3]['nome_exibir'] = 'Endereço';

$arr_campos['endereco'][4]['campo'] = 'numero';
$arr_campos['endereco'][4]['nome_exibir'] = 'Número';

$arr_campos['endereco'][5]['campo'] = 'complemento';
$arr_campos['endereco'][5]['nome_exibir'] = 'Complemento';

$arr_campos['endereco'][6]['campo'] = 'cep';
$arr_campos['endereco'][6]['nome_exibir'] = 'Cep';

$arr_campos['endereco'][7]['campo'] = 'telefone_1';
$arr_campos['endereco'][7]['nome_exibir'] = 'Telefone 1';

$arr_campos['endereco'][8]['campo'] = 'telefone_2';
$arr_campos['endereco'][8]['nome_exibir'] = 'Telefone 2';

$arr_campos['endereco'][9]['campo'] = 'edificio';
$arr_campos['endereco'][9]['nome_exibir'] = 'Edificio';

$arr_campos['endereco'][10]['campo'] = 'referencia_endereco';
$arr_campos['endereco'][10]['nome_exibir'] = 'Ref. Cliente';

$arr_campos['endereco'][11]['campo'] = 'referencia_cliente';
$arr_campos['endereco'][11]['nome_exibir'] = 'Ref. Endereço';

$arr_campos['endereco'][12]['campo'] = 'apelido';
$arr_campos['endereco'][12]['nome_exibir'] = 'Apelido End.';

$arr_campos['calculados'][0]['campo'] = "num_pedidos";
$arr_campos['calculados'][0]['nome_exibir'] = "Total de Pedidos";
$arr_campos['calculados'][0]['query'] = "(SELECT count(cod_pedidos) FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes AND situacao='BAIXADO' $filtro_faturamento) num_pedidos";

$arr_campos['calculados'][1]['campo'] = "total_pedidos";
$arr_campos['calculados'][1]['nome_exibir'] = "Total Gasto (R$)";
$arr_campos['calculados'][1]['query'] = "(SELECT sum(valor_total) FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes AND situacao='BAIXADO' $filtro_faturamento) total_pedidos";
$arr_campos['calculados'][1]['formatacao'] = "moeda";

$arr_campos['calculados'][2]['campo'] = "data_ultimo_pedido";
$arr_campos['calculados'][2]['nome_exibir'] = "Data do Último Pedido";
$arr_campos['calculados'][2]['query'] = "(SELECT data_hora_pedido FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes $filtro_faturamento ORDER BY cod_pedidos DESC LIMIT 1) data_ultimo_pedido";
$arr_campos['calculados'][2]['formatacao'] = "datahora";

$arr_campos['calculados'][3]['campo'] = "ticket_medio";
$arr_campos['calculados'][3]['nome_exibir'] = "Ticket Médio (R$)";
$arr_campos['calculados'][3]['query'] = "(SELECT avg(valor_total) FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes AND situacao='BAIXADO' $filtro_faturamento) ticket_medio";
$arr_campos['calculados'][3]['formatacao'] = "moeda";

$arr_campos['calculados'][4]['campo'] = "onde_conheceu";
$arr_campos['calculados'][4]['nome_exibir'] = "Como Conheceu";
$arr_campos['calculados'][4]['query'] = "(SELECT onde_conheceu FROM ipi_onde_conheceu oc WHERE oc.cod_onde_conheceu=c.cod_onde_conheceu LIMIT 1) onde_conheceu";

$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

switch($acao)
{
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',',$excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if(mysql_query($SqlDel))
    {
        mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    }
    else
    {
        mensagemErro('Erro ao excluir os registros','Por favor, verifique se o cliente não está vinculado a algum pedido.');
    }
    
    desconectabd($con);
    break;
  case 'editar':
    $codigo = validaVarPost($chave_primaria);
    $observacao = texto2bd(validaVarPost('observacao'));
    $situacao = texto2bd(validaVarPost('situacao'));
    $nova_senha = texto2bd(validaVarPost('nova_senha'));
    
    if($codigo > 0)
    {
        $con = conectabd();
        
        if(trim($nova_senha) != '')
        {
            $update_senha = ", senha = MD5('" . texto2bd($nova_senha) . "')";
        }
        
        $SqlUpdate = sprintf("UPDATE $tabela SET observacao = '%s', situacao = '%s' $update_senha WHERE $chave_primaria = $codigo",$observacao,$situacao);
        
        if(mysql_query($SqlUpdate))
        {
            mensagemOk('Os registros selecionados foram alterados com sucesso!');
        }
        else
        {
            mensagemErro('Erro ao alterar os registros','Por favor, contacte a equipe de suporte informando o cliente.');
        }
        
        desconectabd($con);
    }
    break;
  case 'detalhes':
    $codigo = validaVarPost($chave_primaria);
  
    $conexao = conectabd();
  
      
      desconectabd($conexao);
      
      break;

      case 'alterar_newsletter':
        $conexao = conectar_bd();
        $codigo = validaVarPost($chave_primaria);
        $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

        $observacao = validaVarPost('obs_newsletter');

        $obj_buscar_clientes = executaBuscaSimples("SELECT * FROM ipi_clientes c WHERE $chave_primaria = '$codigo' ", $conexao);//AND c.cod_clientes IN (SELECT p.cod_clientes FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes AND p.cod_pizzarias IN(".$cod_pizzarias_usuario."))
        $sql_buscar_newsletter = "SELECT ativo FROM ine_emails_cadastro WHERE email = '".$obj_buscar_clientes->email."'";
        $res_buscar_newsletter = mysql_query($sql_buscar_newsletter);
        $num_buscar_newsletter = mysql_num_rows($res_buscar_newsletter);

        if($num_buscar_newsletter > 0)
        {
            $obj_buscar_newsletter = mysql_fetch_object($res_buscar_newsletter);
            $sql_atualizar_newsletter = "UPDATE ine_emails_cadastro SET ativo = '".($obj_buscar_newsletter->ativo == 1 ? 0 : 1)."' WHERE email = '".$obj_buscar_clientes->email."'";
            mysql_query($sql_atualizar_newsletter);
        }
        else
        {
            $sql_cadastrar_newsletter = "INSERT INTO ine_emails_cadastro(email, ativo, cod_ligacao) VALUES ('".$obj_buscar_clientes->email."', '1', '".$obj_buscar_clientes->cod_clientes."')";
            mysql_query($sql_cadastrar_newsletter);
        }

        desconectar_bd($conexao);
        ?>
            <script> alert("Operação concluída com sucesso!"); </script>
        <?
        $acao = '';
      break;
}

function aplicar_formatacao($valor,$formatacao = '')
{
  switch ($formatacao) {
    case 'moeda':
      return bd2moeda($valor);
      break;
    case 'data':
      return bd2data($valor);
      break;
    case 'datahora':
      return bd2datahora($valor);
      break;
    default:
       return $valor;
      break;
  }

}

if(($acao == '') || ($acao == 'buscar') || ($acao == 'editar')):

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<script>

function verificaCheckbox(form) 
{
    var cInput = 0;
    var checkBox = form.getElementsByTagName('input');

    for (var i = 0; i < checkBox.length; i++)
    {
        if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true)))
        { 
          cInput++; 
        }
    }
   
    if(cInput > 0)
    {
        if (confirm('Deseja excluir os registros selecionados?'))
        {
      return true;
        }
        else
        {
      return false;
        }
    }
    else
    {
        alert('Por favor, selecione os itens que deseja excluir.');
         
        return false;
    }
}

function editar(cod)
{
    var form = new Element('form',
    {
        'action': '<? echo $_SERVER['PHP_SELF'] ?>',
        'method': 'post'
    });
    
    var input1 = new Element('input',
    {
        'type': 'hidden',
        'name': '<? echo $chave_primaria ?>',
        'value': cod
    });
    
    var input2 = new Element('input',
    {
        'type': 'hidden',
        'name': 'acao',
        'value': 'detalhes'
    });
    
    input1.inject(form);
    input2.inject(form);
    $(document.body).adopt(form);
    
    form.submit();
}

function validacaoForm (frm) {

  if (frm.nascimento_inicial_filtro.value != "" && frm.nascimento_final_filtro.value =="") 
  {
    alert("É necessário informar o período completo!");
    frm.nascimento_final_filtro.focus();
    return false;
  }
  if (frm.nascimento_final_filtro.value != "" && frm.nascimento_inicial_filtro.value =="")
  {
    alert("É necessário informar o período completo!");
    frm.nascimento_inicial_filtro.focus();
    return false;
  }
  return true;
}

</script>

<?

    $pagina = (validaVarPost('pagina','/[0-9]+/')) ? validaVarPost('pagina','/[0-9]+/') : 0;
    $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : 'nome';
    $filtro = validaVarPost('filtro');
    


    if ( $acao=='buscar' )
    {
        $_SESSION['rel_cli']['filtro_busca'] = $filtro;
        $_SESSION['rel_cli']['opcao_busca'] = $opcoes;
    }
    else
    {
        $filtro = $_SESSION['rel_cli']['filtro_busca'];
        $_SESSION['rel_cli']['opcao_busca'] ? $opcoes = $_SESSION['rel_cli']['opcao_busca'] : '';
    }

    $qtd_pagina = validaVarPost("qtd_pagina");
    if(validaVarPost("qtd_pagina"))
    {
      $_SESSION['rel_cli']['qtd_pagina'] = $qtd_pagina;
    }
    else
    {
      if($_SESSION['rel_cli']['qtd_pagina']!="")
      {
        $qtd_pagina = $_SESSION['rel_cli']['qtd_pagina'];
      }
      else
      $qtd_pagina = $quant_pagina;
    }

    
    $campo_cli = array();
    if (validaVarPost('campo_cli') || validaVarPost('filtro'))
    {
      if(validaVarPost('campo_cli'))
      {
        $campo_cli = validaVarPost('campo_cli');
        $_SESSION['rel_cli']['campo_cli'] = $campo_cli;
      }
      else
      {
        $campo_end = array();
      }
    }
    else 
    {
      if ($_SESSION['rel_cli']['campo_cli']!='')
      {
        $campo_cli = $_SESSION['rel_cli']['campo_cli'];
      } 
    }

    $campo_end = array();
    if (validaVarPost('campo_end') || validaVarPost('filtro'))
    {
      if(validaVarPost('campo_end'))
      {
        $campo_end = validaVarPost('campo_end');
        $_SESSION['rel_cli']['campo_end'] = $campo_end;
      }
      else
      {
        $campo_end = array();
      }
    }
    else 
    {
      if ($_SESSION['rel_cli']['campo_end']!='')
      {
        $campo_end = $_SESSION['rel_cli']['campo_end'];
      } 
    }

    $campo_calc = array();
    if (validaVarPost('campo_calc') || validaVarPost('filtro'))
    {
      if(validaVarPost('campo_calc'))
      {
        $campo_calc = validaVarPost('campo_calc');
        $_SESSION['rel_cli']['campo_calc'] = $campo_calc;
      }
      else
      {
        $campo_calc = array();
      }
    }
    else 
    {
      if ($_SESSION['rel_cli']['campo_calc']!='')
      {
/*        $campo_calc[] = '';
      }
      else 
      {*/
        $campo_calc = $_SESSION['rel_cli']['campo_calc'];
      } 
    }

    $cidade = (validaVarPost('cidade'));
    if (validaVarPost('cidade'))
    {
      $_SESSION['rel_cli']['cidade'] = $cidade;
    }
    else 
    {
      if ($_SESSION['rel_cli']['cidade']=='')
      {
        $cidade = 'TODOS';
      }
      else 
      {
        $cidade = $_SESSION['rel_cli']['cidade'];
      } 
    }


    $bairro = (validaVarPost('bairro'));
    if (validaVarPost('bairro'))
    {
      $_SESSION['rel_cli']['bairro'] = $bairro;
    }
    else 
    {
      if ($_SESSION['rel_cli']['bairro']=='')
      {
        $bairro = 'TODOS';
      }
      else 
      {
        $bairro = $_SESSION['rel_cli']['bairro'];
      } 
    }
    

    $situacao_filtro = (validaVarPost('situacao_filtro')) ? validaVarPost('situacao_filtro') : 'TODOS';
    if (validaVarPost('situacao_filtro'))
    {
      $_SESSION['rel_cli']['situacao_filtro'] = $situacao_filtro;
    }
    else 
    {
      $situacao_filtro = $_SESSION['rel_cli']['situacao_filtro'];
    }

    $nascimento_inicial_filtro = (validaVarPost('nascimento_inicial_filtro')) ? validaVarPost('nascimento_inicial_filtro') : '';
    if (validaVarPost('nascimento_inicial_filtro'))
    {
      $_SESSION['rel_cli']['nascimento_inicial_filtro'] = $nascimento_inicial_filtro;
    }
    // else 
    // {
    //   $nascimento_inicial_filtro = $_SESSION['rel_cli']['nascimento_inicial_filtro'];
    // }

    $nascimento_final_filtro = (validaVarPost('nascimento_final_filtro')) ? validaVarPost('nascimento_final_filtro') : '';
    if (validaVarPost('nascimento_final_filtro'))
    {
      $_SESSION['rel_cli']['nascimento_final_filtro'] = $nascimento_final_filtro;
    }
    // else 
    // {
    //   $nascimento_final_filtro = $_SESSION['rel_cli']['nascimento_final_filtro'];
    // }


    // if (validaVarPost('faturamento_inicial_filtro'))
    // {
    //   $_SESSION['rel_cli']['faturamento_inicial_filtro'] = $faturamento_inicial_filtro;
    // }


    // if (validaVarPost('faturamento_final_filtro'))
    // {
    //   $_SESSION['rel_cli']['faturamento_final_filtro'] = $faturamento_final_filtro;
    // }

    $origem_cliente = (validaVarPost('origem_cliente')) ? validaVarPost('origem_cliente') : 'TODOS';
    if (validaVarPost('origem_cliente'))
    {
      $_SESSION['rel_cli']['origem_cliente'] = $origem_cliente;
    }
    else 
    {
      $origem_cliente = $_SESSION['rel_cli']['origem_cliente'];
    }

    $filtro_conheceu = (validaVarPost('filtro_conheceu')) ? validaVarPost('filtro_conheceu') : 'TODOS';
    if (validaVarPost('filtro_conheceu'))
    {
      $_SESSION['rel_cli']['filtro_conheceu'] = $filtro_conheceu;
    }
    else 
    {
      if ($_SESSION['rel_cli']['filtro_conheceu']=='')
      {
        $filtro_conheceu = 'TODOS';
      }
      else 
      {
        $filtro_conheceu = $_SESSION['rel_cli']['filtro_conheceu'];
      }
    }

    $filtro_facebook = (validaVarPost('filtro_facebook')) ? validaVarPost('filtro_facebook') : 0;
    $_SESSION['rel_cli']['filtro_facebook'] = $filtro_facebook;
?>

<script type="text/javascript">
function buscar_bairros()
{
    var url = 'acao=completar_bairro&cidade=' + $('cidade').value+ '&bairro=' + $('bairro').value;
    new Request.HTML(
    {
        url: 'ipi_clientes_ajax.php',
        update: $('bairro')
    }).send(url);
}

<?
if ($cidade)
{
/*  ?>
  window.addEvent('domready', function()
  {
    var url = 'acao=completar_bairro&cidade=<? echo $cidade; ?>&bairro=<? echo $bairro; ?>';
    new Request.HTML(
    {
        url: 'ipi_clientes_ajax.php',
        update: $('bairro')
    }).send(url);
  });
  <?*/
}
?>
</script>

<form name="frmFiltro" method="post" onsubmit="return validacaoForm(this)">
<table align="center" class="caixa" cellpadding="0" cellspacing="0" >

  <tr>
    <td class="legenda tdbl tdbt" align="right">
      <select name="opcoes">
        <option value="nome"
        <?
        if($opcoes == 'nome')
          echo 'selected'?>>Nome</option>
        <option value="email"
        <? if($opcoes == 'email') echo 'selected'?>>E-mail</option>
            <option value="cpf"
                <? if($opcoes == 'cpf') echo 'selected'?>>CPF</option>
      </select>
    </td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbt tdbr"><input class="requerido" type="text"
      name="filtro" size="60" value="<? echo $filtro ?>"></td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="cidade">Cidade:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cidade" id="cidade" onChange="javascript:buscar_bairros();">
        <option value="TODOS" <? if($cidade == 'TODOS') echo 'selected'?>>Todas as Cidades</option>
          <?
          $con = conectabd();
          $sql_cidades = "SELECT DISTINCT(cidade) FROM ipi_enderecos ORDER BY cidade";
          $res_cidades = mysql_query($sql_cidades);
          while($obj_cidades = mysql_fetch_object($res_cidades))
          {
              echo '<option value="' . $obj_cidades->cidade . '" ';
              if($cidade == $obj_cidades->cidade)
                  echo 'selected';
              echo '>' . bd2texto($obj_cidades->cidade) . '</option>';
          }
          desconectabd($con);
          ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="bairro">Bairro:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="bairro" id="bairro">
      <?
        if($cidade!="")
        {
          $conexao = conectabd();
          $sql_bairros = "SELECT DISTINCT(bairro) FROM ipi_enderecos WHERE cidade='".$cidade."' ORDER BY bairro";
          $res_bairros = mysql_query($sql_bairros);
          while($obj_bairros = mysql_fetch_object($res_bairros))
          {
              echo '<option value="' . $obj_bairros->bairro . '" ';
              if( ($bairro) == $obj_bairros->bairro)
                  echo 'selected';
              echo '>' . bd2texto($obj_bairros->bairro) . '</option>';
          }
          desconectar_bd($conexao);
        }
        else
          echo '<option value="TODOS">Selecione a Cidade</option>';
      ?>
        
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="origem_cliente">Origem do Cliente:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr"><select name="origem_cliente">
      <option value="TODOS"
        <? if($origem_cliente == 'TODOS') echo 'selected'?>>Todos</option>
      <option value="NET"
        <? if($origem_cliente == 'NET') echo 'selected'?>>Net</option>
      <option value="TEL"
        <? if($origem_cliente == 'TEL') echo 'selected'?>>Tel</option>
    </select></td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="situacao_filtro">Situação:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr">
    <select name="situacao_filtro">
      <option value="TODOS"
        <? if($situacao_filtro == 'TODOS') echo 'selected'?>>Todas as Situações</option>
      <option value="ATIVO"
        <? if($situacao_filtro == 'ATIVO') echo 'selected'?>>Ativo</option>
      <option value="INATIVO"
        <? if($situacao_filtro == 'INATIVO') echo 'selected'?>>Inativo</option>
    </select></td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right">
    <label for="nascimento_inicial_filtro">Data Nascimento De:</label></td> <td class="">&nbsp;</td>
    <td class="tdbr">
    <table>
      <tr>
      <td>
      <input class="requerido" type="text" name="nascimento_inicial_filtro" id="nascimento_inicial_filtro" size="12" value="<? echo $nascimento_inicial_filtro ?>" onkeypress="return MascaraData(this, event)">
      </td>
      <td class="legenda">
      &nbsp;<label for="nascimento_final_filtro">Até:</label>
      <input class="requerido" type="text" name="nascimento_final_filtro" id="nascimento_final_filtro" size="12" value="<? echo $nascimento_final_filtro ?>" onkeypress="return MascaraData(this, event)"></td>
      </tr>
    </table>    
      </td>
   
  </tr>

    <tr>
    <td class="legenda tdbl" align="right">
    <label for="faturamento_inicial_filtro">Data Faturamento De:</label></td> <td class="">&nbsp;</td>
    <td class="tdbr">
    <table>
      <tr>
      <td>
      <input class="requerido" type="text" name="faturamento_inicial_filtro" id="faturamento_inicial_filtro" size="12" value="<? echo $faturamento_inicial_filtro ?>" onkeypress="return MascaraData(this, event)">
      </td>
      <td class="legenda">
      &nbsp;<label for="faturamento_final_filtro">Até:</label>
      <input class="requerido" type="text" name="faturamento_final_filtro" id="faturamento_final_filtro" size="12" value="<? echo $faturamento_final_filtro ?>" onkeypress="return MascaraData(this, event)"></td>
      </tr>
    </table>    
      </td>
   
  </tr>


    <tr>
      <td class="legenda tdbl" align="right"> 
        <label for="filtro_conheceu">Como conheceu:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr"> 
        <select name="filtro_conheceu">
        <option value='TODOS'>Todos</option>
          <?
            $con = conectabd();
            $sql_buscar_onde_conheceu = "SELECT * from ipi_onde_conheceu where situacao='ATIVO' order by onde_conheceu";
            $res_buscar_onde_conheceu = mysql_query($sql_buscar_onde_conheceu);
            while($obj_buscar_onde_conheceu = mysql_fetch_object($res_buscar_onde_conheceu))
            {
              echo "<option value='".$obj_buscar_onde_conheceu->cod_onde_conheceu."'";

              if($filtro_conheceu == $obj_buscar_onde_conheceu->cod_onde_conheceu)
              {
                echo ' selected="selected" '; 
              }

              echo ">".$obj_buscar_onde_conheceu->onde_conheceu."</option>";
            }

            desconectabd();


          ?>
        </select>
      </td>
    </tr>

    <tr>
      <td class="legenda tdbl" align="right"> 
        <label for="filtro_facebook">Apenas do Facebook:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr"> 
        <select name="filtro_facebook">
          <option value=""  <? if($filtro_facebook == '') echo 'selected="selected"'?>>Não</option>
          <option value="1" <? if($filtro_facebook == '1') echo 'selected="selected"'?>>Sim</option>
        </select>
      </td>
    </tr>

    <tr>
      <td class="legenda tdbl sep" align="right"> 
        <label >Quantidade por página:</label>
      </td>
      <td class="sep">&nbsp;</td>
      <td class="tdbr sep"> 
        <select name="qtd_pagina">
          <option value="25"  <? if($qtd_pagina == '25') echo 'selected="selected"'?>>25</option>
          <option value="50"  <? if($qtd_pagina == '50') echo 'selected="selected"'?>>50</option>
          <option value="75"  <? if($qtd_pagina == '75') echo 'selected="selected"'?>>75</option>
          <option value="100"  <? if($qtd_pagina == '100') echo 'selected="selected"'?>>100</option>
          <option value="150"  <? if($qtd_pagina == '150') echo 'selected="selected"'?>>150</option>
          <option value="200"  <? if($qtd_pagina == '200') echo 'selected="selected"'?>>200</option>
          <option value="250"  <? if($qtd_pagina == '250') echo 'selected="selected"'?>>250</option>
          <option value="300"  <? if($qtd_pagina == '300') echo 'selected="selected"'?>>300</option>
          <option value="350"  <? if($qtd_pagina == '350') echo 'selected="selected"'?>>350</option>
          <option value="400"  <? if($qtd_pagina == '400') echo 'selected="selected"'?>>400</option>
          <option value="500"  <? if($qtd_pagina == '500') echo 'selected="selected"'?>>500</option>
        </select>
      </td>
    </tr>

    <tr>
<!--       <td class="legenda tdbl" align="right"> 
        <label>Campos para exibir:</label> 
      </td> -->
      <!-- <td class="">&nbsp;</td> -->
      <td class='tdbl tdbr' colspan="3">
        Campos do Cliente
        <hr/>
        <br/>
        <?php
        $i = 0;
        echo "<ul>";
        foreach ($arr_campos['cliente'] as $id_cli => $arr_dados) 
        {
          if($i==5)
          {
            echo "</ul><ul>";
            $i = 0;
          }
          echo "<li style='float:left;list-style: none;width:110px'>";
          echo '<input type="checkbox" value="'.$id_cli.'" ';

          if(in_array($id_cli, $campo_cli))
          {
            echo 'checked="checked"';
          }

          echo ' name="campo_cli[]"/>'.$arr_dados['nome_exibir']."&nbsp;&nbsp;&nbsp;";
          $i++;
          echo "</li>";
        }
        echo "<li style='clear:both;list-style: none'></li>";
        echo "</ul>";
        ?>
        
        <br/>
        Campos do Endereço
        <hr/>
        <br/>
        <?php
        $i = 0;
        echo "<ul>";
        foreach ($arr_campos['endereco'] as $id_end => $arr_dados) 
        {
          if($i==5)
          {
            echo "</ul><ul>";
            $i = 0;
          }
          echo "<li style='float:left;list-style: none;width:110px'>";
          echo '<input type="checkbox" value="'.$id_end.'" ';

          if(in_array($id_end, $campo_end))
          {
            echo 'checked="checked" ';
          }

          echo 'name="campo_end[]"/>'.$arr_dados['nome_exibir']."&nbsp;&nbsp;&nbsp;";
          $i++;
        }
        echo "<li style='clear:both;list-style: none'></li>";
        echo "</ul>";
        ?>
        <br/>
        <br/>
        Campos Calculados
        <hr/>
        <br/>
        <?php
        $i = 0;
        echo "<ul>";
        foreach ($arr_campos['calculados'] as $id_calc => $arr_dados) 
        {
          if($i==5)
          {
            echo "</ul><ul>";
            $i = 0;
          }
          echo "<li style='float:left;list-style: none;width:110px'>";
          echo '<input type="checkbox" value="'.$id_calc.'" ';

          if(in_array($id_calc, $campo_calc))
          {
            echo 'checked="checked" ';
          }

          echo 'name="campo_calc[]"/>'.$arr_dados['nome_exibir']."&nbsp;&nbsp;&nbsp;";
          $i++;
        }
        echo "<li style='clear:both;list-style: none'></li>";
        echo "</ul>";
        ?>
      </td>
    </tr>
  
    <tr>
    <td align="right" class="tdbl tdbb tdbr" colspan="3"><input
      class="botaoAzul" type="submit" value="Buscar"></td>
  </tr>

</table>

<input type="hidden" name="acao" value="buscar"></form>

<? if($acao=='buscar'): ?>
<br>

<?
    
    $campo_ordenacao = validaVarPost("campo_ordenacao");

    $con = conectabd();

    
    $filtrar_facebook = "";
    if($filtro_facebook)
    {
        $filtrar_facebook .= " INNER JOIN ipi_clientes_redes_sociais icrs ON (c.cod_clientes = icrs.cod_clientes) ";
    }


    $query_cli_end = '';
    $query_calculados = '';

    if(count($campo_cli)>0)
    {
      foreach ($campo_cli as $id_cli) 
      {
        if($query_cli_end!='') $query_cli_end .= ',';
        $query_cli_end .= 'c.'.$arr_campos['cliente'][$id_cli]['campo'];
      }
    }

    if(count($campo_end)>0)
    {
      foreach ($campo_end as $id_end) 
      {
        if($query_cli_end!='') $query_cli_end .= ',';
        $query_cli_end .= 'e.'.$arr_campos['endereco'][$id_end]['campo'];
      }
    }

    if(count($campo_calc)>0)
    {
      foreach ($campo_calc as $id_calc) 
      {
        if($query_calculados!='') $query_calculados .= ',';
        $query_calculados .= $arr_campos['calculados'][$id_calc]['query'];
      }
    }

    if($query_cli_end!='' && $query_calculados!='') $query_cli_end .= ',';
    //if($query_cli_end!='') $query_cli_end .= ',';

    $SqlBuscaRegistros = "SELECT $query_cli_end $query_calculados

      FROM $tabela c inner join ipi_enderecos e on e.cod_clientes = c.cod_clientes ".$filtrar_facebook;

    if(($faturamento_inicial_filtro!="") && ($faturamento_final_filtro!=""))
    {

      $SqlBuscaRegistros .= " INNER JOIN ipi_pedidos p ON (p.cod_clientes = c.cod_clientes) ";
    }

      $SqlBuscaRegistros .=" WHERE $opcoes LIKE '%$filtro%'  ";//AND c.cod_clientes IN (SELECT p.cod_clientes FROM ipi_pedidos p WHERE p.cod_clientes=c.cod_clientes AND p.cod_pizzarias IN(".$cod_pizzarias_usuario.")) 

     if(($faturamento_inicial_filtro!="") && ($faturamento_final_filtro!=""))
    {

        $SqlBuscaRegistros .= " AND p.situacao='BAIXADO' AND p.data_hora_pedido BETWEEN  '$faturamento_inicial_sql 00:00:00' AND  '$faturamento_final_sql 23:59:59' ";
    }
    
    if($situacao_filtro != 'TODOS')
    {
        $SqlBuscaRegistros .= "AND c.situacao = '$situacao_filtro'";
    }

    if(($nascimento_inicial_filtro!="") && ($nascimento_final_filtro!="")) 
  {
    $nascimento_inicial_sql = data2bd($nascimento_inicial_filtro); 
    $nascimento_final_sql = data2bd($nascimento_final_filtro);

    if(validar_hora($hora_inicial))
    {
      $data_inicial_sql .= ' '.$hora_inicial.':00'; 
    }
    else
    {
      $data_inicial_sql .= ' 00:00:00'; 
    }

    if(validar_hora($hora_final))
    {
      $data_final_sql .= ' '.$hora_final.':59'; 
    }
    else
    {
      $data_final_sql .= ' 23:59:59'; 
    }

    $SqlBuscaRegistros .= " AND c.nascimento BETWEEN '$nascimento_inicial_sql' AND '$nascimento_final_sql'";

  }
  
    
    if($origem_cliente != 'TODOS')
    {
        $SqlBuscaRegistros .= "AND c.origem_cliente = '$origem_cliente'";
    }

    //echo "<br />cidade: ".$cidade;
    //echo "<br />bairro: ".$bairro;
    if($cidade != 'TODOS')
    {
      $SqlBuscaRegistros .= " and e.cidade = '$cidade' ";

      if($bairro != 'TODOS')
      {
        $SqlBuscaRegistros .= " AND e.bairro = '$bairro' ";
      }
    }

    if($filtro_conheceu!= 'TODOS')
    {
      $SqlBuscaRegistros .= " AND c.cod_onde_conheceu = '$filtro_conheceu' ";
    }

    if($campo_ordenacao && $campo_ordenacao!='')
    {
      $SqlBuscaRegistros .= ' ORDER BY ' . $campo_ordenacao;
    }

         if(($faturamento_inicial_filtro!="") && ($faturamento_final_filtro!=""))
    {

    $SqlBuscaRegistros.=" GROUP BY p.cod_clientes";
    }
    


    $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
    $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);
    
    $SqlBuscaRegistros .= ' LIMIT ' . ($qtd_pagina * $pagina) . ', ' . $qtd_pagina;
    $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
    $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);
        
    echo "<center><b>" . $numBuscaRegistros . " registro(s) encontrado(s)</center></b><br>";

    
    if((($qtd_pagina * $pagina) == $numBuscaRegistros) && ($pagina != 0) && ($acao == 'excluir'))
    {
        $pagina--;
    }
    
    echo '<center>';
    
    $numpag = ceil(((int)$numBuscaRegistros) / ((int)$qtd_pagina));
    
    for($b = 0; $b < $numpag; $b++)
    {
        echo '<form name="frmPaginacao' . $b . '" method="post">';
        echo '<input type="hidden" name="pagina" value="' . $b . '">';
        echo '<input type="hidden" name="filtro" value="' . $filtro . '">';
        echo '<input type="hidden" name="opcoes" value="' . $opcoes . '">';
        
        echo '<input type="hidden" name="bairro" value="' . $bairro . '">';
        echo '<input type="hidden" name="situacao_filtro" value="' . $situacao_filtro . '">';
        echo '<input type="hidden" name="nascimento_inicial_filtro" value="' . $nascimento_inicial_filtro . '">';
        echo '<input type="hidden" name="nascimento_final_filtro" value="' . $nascimento_final_filtro . '">';

                echo '<input type="hidden" name="faturamento_inicial_filtro" value="' . $faturamento_inicial_filtro . '">';
        echo '<input type="hidden" name="faturamento_final_filtro" value="' . $faturamento_final_filtro . '">';

        echo '<input type="hidden" name="cidade"  value="'.$cidade.'"/>';
        echo '<input type="hidden" name="origem_cliente" value="'.$origem_cliente.'"/>';
        echo '<input type="hidden" name="filtro_conheceu" value="'.$filtro_conheceu.'"/>';
        echo '<input type="hidden" name="filtro_facebook" value="'.$filtro_facebook.'"/>';
        echo '<input type="hidden" name="qtd_pagina" value="'.$qtd_pagina.'"/>';

        echo '<input type="hidden" name="campo_ordenacao" value="'.$campo_ordenacao.'"/>'; 

        foreach ($campo_cli as $campo ) 
        {      
          echo '<input type="hidden" name="campo_cli[]" value="'.$campo.'"/>';
        }
        foreach ($campo_end as $campo ) 
        { 
          echo '<input type="hidden" name="campo_end[]" value="'.$campo.'"/>';
        }
        foreach ($campo_calc as $campo ) 
        { 
          echo '<input type="hidden" name="campo_calc[]" value="'.$campo.'"/>';
        }

        echo '<input type="hidden" name="acao" value="buscar">';
        echo "</form>";
    }
    
    if($pagina != 0)
    {
        echo '<a href="javascript:;" onclick="javascript:frmPaginacao' . ($pagina - 1) . '.submit();" style="margin-right: 5px;">&laquo;&nbsp;Anterior</a>';
    }
    else
    {
        echo '<span style="margin-right: 5px;">&laquo;&nbsp;Anterior</span>';
    }
    
    for($b = 0; $b < $numpag; $b++)
    {
        if($b != 0)
        {
            echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
        }
        
        if($pagina != $b)
        {
            echo '<a href="javascript:;" onclick="javascript:frmPaginacao' . $b . '.submit();">' . ($b + 1) . '</a>';
        }
        else
        {
            echo '<span><b>' . ($b + 1) . '</b></span>';
        }
    }
    
    if(($qtd_pagina == $linhasBuscaRegistros) && ((($qtd_pagina * $pagina) + $qtd_pagina) != $numBuscaRegistros))
    {
        echo '<a href="javascript:;" onclick="javascript:frmPaginacao' . ($pagina + 1) . '.submit();" style="margin-left: 5px;">Próxima&nbsp;&raquo;</a>';
    }
    else
    {
        echo '<span style="margin-left: 5px;">Próxima&nbsp;&raquo;</span>';
    }
    
    echo '</center>';
    
    ?>

<br />
<?

/*echo "<pre>";
print_r($_POST);
echo "</pre>";
echo "<br/><br/><br/><pre>";
print_r($_SESSION);
echo "</pre>";
*/

?>
       <form id="ordem_campo_form" action="" method="post">
       <input type="hidden" name="campo_ordenacao" id="campo_ordenacao" value=""/>
       <?
        
        echo '<input type="hidden" name="pagina" value="' . $pagina . '">';
        echo '<input type="hidden" name="filtro" value="' . $filtro . '">';
        echo '<input type="hidden" name="opcoes" value="' . $opcoes . '">';
        
        echo '<input type="hidden" name="bairro" value="' . $bairro . '">';
        echo '<input type="hidden" name="situacao_filtro" value="' . $situacao_filtro . '">';

        echo '<input type="hidden" name="nascimento_inicial_filtro" value="' . $nascimento_inicial_filtro . '">';
        echo '<input type="hidden" name="nascimento_final_filtro" value="' . $nascimento_final_filtro . '">';

        echo '<input type="hidden" name="cidade"  value="'.$cidade.'"/>';
        echo '<input type="hidden" name="origem_cliente" value="'.$origem_cliente.'"/>';
        echo '<input type="hidden" name="filtro_conheceu" value="'.$filtro_conheceu.'"/>';
        echo '<input type="hidden" name="filtro_facebook" value="'.$filtro_facebook.'"/>';
        echo '<input type="hidden" name="qtd_pagina" value="'.$qtd_pagina.'"/>';

        foreach ($campo_cli as $campo ) 
        {      
          echo '<input type="hidden" name="campo_cli[]" value="'.$campo.'"/>';
        }
        foreach ($campo_end as $campo ) 
        { 
          echo '<input type="hidden" name="campo_end[]" value="'.$campo.'"/>';
        }
        foreach ($campo_calc as $campo ) 
        { 
          echo '<input type="hidden" name="campo_calc[]" value="'.$campo.'"/>';
        }
        echo '<input type="hidden" name="acao" value="buscar">';

        ?>
       </form>
<form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">

<!-- <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
  <tr>
    <td><input class="botaoAzul" type="submit"
      value="Excluir Selecionados"></td>
  </tr>
</table>
 -->
<table class="listaEdicao" cellpadding="0" cellspacing="0">
  <thead>
    <tr>
<!--      <td align="center" width="20"><input type="checkbox"
        onclick="marcaTodos('marcar');"></td> -->

       <!-- <td align="center">Nome</td> -->

       <? 
        foreach ($campo_cli as $id_cli ) 
        {

          $ordem_crescente = 'c.'.$arr_campos['cliente'][$id_cli]['campo'].' ASC';

          if($campo_ordenacao==$ordem_crescente)
          {
            $ordem_crescente = 'c.'.$arr_campos['cliente'][$id_cli]['campo'].' DESC';
          }

          echo '<td align="center"><a href="javascript:void(0)" onclick="$(\'campo_ordenacao\').value=\''.$ordem_crescente.'\';$(\'ordem_campo_form\').submit();">'.$arr_campos['cliente'][$id_cli]['nome_exibir'].'</href></td>';
        }
        foreach ($campo_end as $id_end ) 
        {
          $ordem_crescente = 'e.'.$arr_campos['endereco'][$id_end]['campo'].' ASC';

          if($campo_ordenacao==$ordem_crescente)
          {
            $ordem_crescente = 'e.'.$arr_campos['endereco'][$id_end]['campo'].' DESC';
          }
          echo '<td align="center"><a href="javascript:void(0)" onclick="$(\'campo_ordenacao\').value=\''.$ordem_crescente.'\';$(\'ordem_campo_form\').submit();">'.$arr_campos['endereco'][$id_end]['nome_exibir'].'</href></td>';
        }

        if(count($campo_calc)>0)
        {
          foreach ($campo_calc as $id_calc) 
          {
            $ordem_crescente = $arr_campos['calculados'][$id_calc]['campo'].' ASC';

            if($campo_ordenacao==$ordem_crescente)
            {
              $ordem_crescente = $arr_campos['calculados'][$id_calc]['campo'].' DESC';
            }

            echo '<td align="center"><a href="javascript:void(0)" onclick="$(\'campo_ordenacao\').value=\''.$ordem_crescente.'\';$(\'ordem_campo_form\').submit();">'.$arr_campos['calculados'][$id_calc]['nome_exibir'].'</href></td>';
          }
        }
       ?>
    </tr>
  </thead>
  <tbody>
    
    <?
    for($a = 0; $a < $linhasBuscaRegistros; $a++)
    {
        $objBuscaRegistros = mysql_fetch_object($resBuscaRegistros);
        echo '<tr>';
        
        /*echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $objBuscaRegistros->$chave_primaria . '"></td>';*/
       // echo '<td align="center"><a href="javascript:;" onclick="editar(' . $objBuscaRegistros->$chave_primaria . ')">' . bd2texto($objBuscaRegistros->nome) . '</a></td>';

        foreach ($campo_cli as $id_cli ) 
        {
          echo '<td align="center">'.aplicar_formatacao($objBuscaRegistros->$arr_campos['cliente'][$id_cli]['campo'],$arr_campos['cliente'][$id_cli]['formatacao']).'</td>';
        }
        foreach ($campo_end as $id_end ) 
        {
          echo '<td align="center">'.aplicar_formatacao($objBuscaRegistros->$arr_campos['endereco'][$id_end]['campo'],$arr_campos['endereco'][$id_end]['formatacao']).'</td>';
        }

        if(count($campo_calc)>0)
        {
          foreach ($campo_calc as $id_calc) 
          {
            echo '<td align="center">'.aplicar_formatacao($objBuscaRegistros->$arr_campos['calculados'][$id_calc]['campo'],$arr_campos['calculados'][$id_calc]['formatacao']).'</td>';  
          }
        }
        // echo '<td align="center">' . bd2texto($objBuscaRegistros->email) . '</td>';
        // echo '<td align="center">' . bd2texto($objBuscaRegistros->bairro) . '</td>';
        // echo '<td align="center">' . bd2texto($objBuscaRegistros->cidade) . '</td>';
        // echo '<td align="center">' . bd2texto($objBuscaRegistros->cpf) . '</td>';
        // echo '<td align="center">' . bd2texto($objBuscaRegistros->num_pedidos).'</td>';
        // echo '<td align="center">' . bd2moeda($objBuscaRegistros->total_pedidos) . '</td>';
        
        // echo '<td align="center">';
        
        // if($objBuscaRegistros->data_ultimo_pedido)
        // {
        //     echo bd2datahora($objBuscaRegistros->data_ultimo_pedido);
        // }
        
        // echo '</td>';
        
        // echo '<td align="center">' . bd2texto($objBuscaRegistros->observacao) . '</td>';
        // echo '<td align="center">' . bd2texto($objBuscaRegistros->situacao) . '</td>';
        
        // echo '</tr>';

    }
    
    desconectabd($con);
    ?>
    
    </tbody>
</table>

<input type="hidden" name="acao" value="excluir"></form>

<? endif; ?>
<br>

<? 
endif;
/*echo "<pre>";
print_r($_POST);
echo "</pre>";
echo "<br/><br/><br/><pre>";
print_r($_SESSION);
echo "</pre>";*/

rodape();
?>
