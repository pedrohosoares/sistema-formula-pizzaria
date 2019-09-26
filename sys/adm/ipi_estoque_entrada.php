<?php

/**
 * Cadastro de Notas Fiscais de Entrada.
 *
 * @version 1.0
 * @package ipizza
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       17/03/2010   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Nota Fiscal de Entrada');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_estoque_entrada';
$tabela = 'ipi_estoque_entrada';
$campo_ordenacao = 'data_hota_entrada_estoque';
$campo_filtro_padrao = 'data_hota_entrada_estoque';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $numero_nota_fiscal = validaVarPost('numero_nota_fiscal');
        $cod_fornecedores = validaVarPost('cod_fornecedores');
        $financeiro_descricao = validaVarPost('financeiro_descricao');
        $num_parcelas = validaVarPost('num_parcelas');
        $cod_titulos_subcategorias = validaVarPost('cod_titulos_subcategorias');
        $preco_total_nota  = validaVarPost('preco_total_nota');
        $vencimento = validaVarPost('vencimento');
        $emissao = validaVarPost('emissao');
        $valor = validaVarPost('valor');
        $mes_ref = validaVarPost('mes_ref');
        $ipi = validaVarPost('total_ipi');
        $icms = validaVarPost('total_icms');
        $outras = validaVarPost('outras_despesas');
        $desconto_total = validaVarPost('desconto_total');

      $conexao = conectabd();
          require_once '../../classe/estoque.php';
          $estoque = new Estoque();
           /*echo "<pre>";
           print_r($_POST);
           echo "</pre>";
           echo "<br/><br/>";    */ 
          if ($estoque->gravar_entrada_itens_temporarios($cod_pizzarias, $numero_nota_fiscal, $cod_titulos_subcategorias, $cod_fornecedores, $num_parcelas, $vencimento,$emissao, $valor, $mes_ref,$ipi,$icms,$outras,$desconto_total,$financeiro_descricao))
          {
              mensagemOK('Registro adicionado com êxito!');
          }
          else
          {
              mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          }
        

        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />
<link rel="stylesheet" type="text/css" media="screen" href="../lib/js/datepicker_vista/datepicker_vista.css" />


<script src="../lib/js/calendario.js" type="text/javascript"></script>
<script type="text/javascript" src="../lib/js/FloatingTips.js"></script>
<script type="text/javascript" src="../lib/js/mensagem.js"></script>
<script>
function criar_formulario_pagamento(cod_titulos_subcategorias)
{
  var url = "acao=criar_formulario_pagamento&cod_titulos_subcategorias=" + cod_titulos_subcategorias;
    
    new Request.HTML(
    {
        url: 'ipi_estoque_entrada_ajax.php',
        update: $('formulario_pagamento'),
        onComplete: function()
        {
          criar_parcelas(document.frmIncluir.num_parcelas.value);
        }
    }).send(url);
}

function criar_parcelas(num_parcelas)
{
  var url = "acao=criar_parcelas&num_parcelas=" + num_parcelas;
    
    new Request.HTML(
    {
        url: 'ipi_estoque_entrada_ajax.php',
        update: $('criar_parcelas')
    }).send(url);
}

function bloquear_enter(field, event)
{
  var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
   
    if (keyCode == 13)
    {
      buscar_ingredientes();
      
      return false;
    }
    else
    {
      return true;
    }
}

function bloquear_enter_bebida(field, event)
{
  var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
   
    if (keyCode == 13)
    {
      buscar_bebidas();
      
      return false;
    }
    else
    {
      return true;
    }
}

function buscar_ingredientes()
{
  var busca_ingrediente = $('busca_ingrediente').getProperty('value');
  var url = 'acao=buscar_ingredientes&busca_ingrediente=' + busca_ingrediente;
  
  new Request.HTML({
    url: 'ipi_estoque_entrada_ajax.php',
    update: $('resultado_busca_ingrediente')
  }).send(url);
}

function buscar_bebidas()
{
  var busca_bebida = $('busca_bebida').getProperty('value');
  var url = 'acao=buscar_bebidas&busca_bebida=' + busca_bebida;
  
  new Request.HTML({
    url: 'ipi_estoque_entrada_ajax.php',
    update: $('resultado_busca_bebida')
  }).send(url);
}

function adicionar_ingrediente(cod_ingredientes, valor_minimo, valor_maximo)
{

  var quantidade = $('quantidade_ingredientes_adicionar_' + cod_ingredientes).getProperty('value');
  quantidade = quantidade.replace(",",".");
  var quantidade_embalagem = $('quantidade_ingredientes_adicionar_embalagem_' + cod_ingredientes).getProperty('value');
  quantidade_embalagem = quantidade_embalagem.replace(",",".");
  /*var preco = $('preco_ingredientes_adicionar_' + cod_ingredientes_marcas).getProperty('value');*/
  var preco_total = $('preco_total_ingredientes_adicionar_' + cod_ingredientes).getProperty('value');
  var unidade = $('tipo_unidade_' + cod_ingredientes).getProperty('value');
  var divisor_comum = $('divisor_comum_' + cod_ingredientes).getProperty('value');
  var total_entrada = parseFloat(quantidade) * parseFloat(quantidade_embalagem);
  var liberacao = 0;


    /*if(unidade=="Kg ou L")
    {
      quantidade_embalagem=quantidade_embalagem*1000;
      
    }*/
    if(quantidade_embalagem != 0)
    {
      if(quantidade > 0)
      {
        if(preco_total != 0)
        {


         // alert(total_entrada*divisor_comum);
          if (total_entrada*divisor_comum < valor_minimo) 
          {

            if ( confirm("Quantidade total da entrada abaixo do mínimo do ingrediente!\nAceitar entrada assim mesmo?\n\n('Cancelar' para corrigir ou 'Ok' para continuar)") )
            {
              liberacao = 2;
            }
            else
            {
              liberacao = 0;
            }

          }
          else if (total_entrada *divisor_comum> valor_maximo)
          {

            if ( confirm("Quantidade total da entrada acima do máximo do ingrediente!\nAceitar entrada assim mesmo?\n\n('Cancelar' para corrigir ou 'Ok' para continuar)") )
            {
              liberacao = 2;
            }
            else
            {
              liberacao = 0;
            }

          }
          else
          {
            liberacao = 1;
          }

          if (liberacao>0)
          {
            var url = 'acao=adicionar_ingrediente&cod_ingredientes=' + cod_ingredientes + '&quantidade=' + quantidade + '&quantidade_embalagem=' + quantidade_embalagem + '&unidade=' + unidade +'&divisor_comum='+divisor_comum+'&liberacao='+liberacao + '&preco_total=' + preco_total; //'&preco=' + preco + 
          
            new Request.JSON({
              url: 'ipi_estoque_entrada_ajax.php',
              onSuccess: function(retorno)
              {
                if(retorno.resposta == 'OK')
                {
                  exibir_ingredientes_adicionados();
                  mensagemOk('Ingrediente adicionado com sucesso!', '');
                }
                else
                {
                  alert(retorno.mensagem);
                }
              }
            }).send(url);
          }

        }
        else
        {
          alert('Digite o preço antes de adicionar.');
          $('preco_ingredientes_adicionar_' + cod_ingredientes).focus();
        }
      }
      else
      {
        alert('Digite a quantidade antes de adicionar.');
        $('quantidade_ingredientes_adicionar_' + cod_ingredientes).focus();
      }
    }
    else
    {
      alert('Digite a quantidade da embalagem antes de adicionar.');
      $('quantidade_ingredientes_adicionar_embalagem_' + cod_ingredientes).focus();
    }
  
}

function adicionar_bebida(cod_bebidas_ipi_conteudos)
{
  var quantidade = parseInt($('quantidade_bebidas_adicionar_' + cod_bebidas_ipi_conteudos).getProperty('value'));
  var preco = $('preco_bebidas_adicionar_' + cod_bebidas_ipi_conteudos).getProperty('value');
  var quantidade_embalagem = $('quantidade_embalagem_bebidas_adicionar_' + cod_bebidas_ipi_conteudos).getProperty('value');
  
  if(quantidade > 0)
  {
      var url = 'acao=adicionar_bebida&cod_bebidas_ipi_conteudos=' + cod_bebidas_ipi_conteudos + '&quantidade=' + quantidade + '&preco=' + preco + '&qtd_embalagem=' + quantidade_embalagem; 
      
      new Request.JSON({
        url: 'ipi_estoque_entrada_ajax.php',
        onSuccess: function(retorno)
        {
                if(retorno.resposta == 'OK')
                {
                  exibir_bebidas_adicionados();
                }
                else
                {
                  alert(retorno.mensagem);
                }
          }
      }).send(url);
  }
  else
  {
    alert('Digite a quantidade antes de adicionar.');
  }
}

function alterar_ingrediente(cod_ingredientes)
{
  var quantidade = parseInt($('quantidade_ingredientes_alterar_' + cod_ingredientes).getProperty('value'));
    var quantidade_embalagem = $('quantidade_ingredientes_alterar_embalagem_' + cod_ingredientes).getProperty('value');
    var preco = $('preco_ingredientes_alterar_' + cod_ingredientes).getProperty('value');
  
  if(quantidade > 0)
  {
      var url = 'acao=alterar_ingrediente&cod_ingredientes=' + cod_ingredientes + '&quantidade=' + quantidade + '&preco=' + preco + '&quantidade_embalagem=' + quantidade_embalagem; 
      
      new Request.JSON({
        url: 'ipi_estoque_entrada_ajax.php',
        onSuccess: function(retorno)
        {
                if(retorno.resposta == 'OK')
                {
                    alert('Ingrediente alterada com sucesso.');
                    exibir_ingredientes_adicionados();
                }
                else
                {
                  alert(retorno.mensagem);
                }
          }
      }).send(url);
  }
  else
  {
    alert('Digite a quantidade antes de alterar.');
  }
}

function alterar_bebida(cod_bebidas_ipi_conteudos)
{
  var quantidade = parseInt($('quantidade_bebidas_alterar_' + cod_bebidas_ipi_conteudos).getProperty('value'));
  var preco = $('preco_bebidas_alterar_' + cod_bebidas_ipi_conteudos).getProperty('value');
  
  if(quantidade > 0)
  {
      var url = 'acao=alterar_bebida&cod_bebidas_ipi_conteudos=' + cod_bebidas_ipi_conteudos + '&quantidade=' + quantidade + '&preco=' + preco; 
      
      new Request.JSON({
        url: 'ipi_estoque_entrada_ajax.php',
        onSuccess: function(retorno)
        {
                if(retorno.resposta == 'OK')

                {
                    exibir_bebidas_adicionados();
                    alert('Bebida alterada com sucesso.');
                }
                else
                {
                  alert(retorno.mensagem);
                }
          }
      }).send(url);
  }
  else
  {
    alert('Digite a quantidade antes de adicionar.');
  }
}

function excluir_ingrediente(posicao)
{
  var url = 'acao=excluir_ingrediente&posicao=' + posicao; 
  
  new Request.JSON({
    url: 'ipi_estoque_entrada_ajax.php',
    onSuccess: function(retorno)
    {
            if(retorno.resposta == 'OK')
            {
              exibir_ingredientes_adicionados();
            }
            else
            {
              alert(retorno.mensagem);
            }
      }
  }).send(url);
}

function excluir_bebida(posicao)
{
  var url = 'acao=excluir_bebida&posicao=' + posicao; 
  
  new Request.JSON({
    url: 'ipi_estoque_entrada_ajax.php',
    onSuccess: function(retorno)
    {
            if(retorno.resposta == 'OK')
            {
              exibir_bebidas_adicionados();
            }
            else
            {
              alert(retorno.mensagem);
            }
      }
  }).send(url);
}

function exibir_ingredientes_adicionados()
{
  var url = 'acao=exibir_ingredientes_adicionados';
  
  new Request.HTML({
    url: 'ipi_estoque_entrada_ajax.php',
    update: $('resultado_ingrediente_adicionado')
  }).send(url);


}

function exibir_bebidas_adicionados()
{
  var url = 'acao=exibir_bebidas_adicionados';
  
  new Request.HTML({
    url: 'ipi_estoque_entrada_ajax.php',
    update: $('resultado_bebida_adicionado')
  }).send(url);
}

// function alterar_unidade(unidade,id)
// {
//   var razao = 1;
//    $("quantidade_ingredientes_adicionar_embalagem_"+id).removeEvents('keypress');

//   if(unidade=="Kg ou L")    
//   {
//     razao = 0.001
//   }
//   else
//   {
//     razao = 1000
//   }  

//  $("quantidade_ingredientes_adicionar_embalagem_"+id).value = $("quantidade_ingredientes_adicionar_embalagem_"+id).value.replace(",",".");
//  $("quantidade_ingredientes_adicionar_embalagem_"+id).value = Math.round($("quantidade_ingredientes_adicionar_embalagem_"+id).value * razao*1000)/1000;
//  $("quantidade_ingredientes_adicionar_embalagem_"+id).value = $("quantidade_ingredientes_adicionar_embalagem_"+id).value.replace(".",",");
// }

function validar_nota()
{
  var vl = 0;
  
  $$("input[name='valor[]']").each(function(valor) {    
      
    vl = vl + parseFloat(valor.value.replace('.','').replace(',','.'));
    });
  vl = Math.round(vl*100)/100;
  var vl_nota =  parseFloat($("preco_total_nota").value.replace('.','').replace(',','.'));

  if(vl==vl_nota)
  {
    return true;
  }
  else
  {
    alert("Valor informado para a nota não coincide com o valor total calculado");
    return false;
  }
}

function calcular_valor_final()
{
//isNaN(value)
var total_ingrediente = 0;
var total_bebida = 0;
var total_ipi = 0;
var total_icms = 0;
var outras_despesas = 0;
var desconto_total = 0;

if(document.getElementById("total_ingrediente"))
 {
  if(!isNaN(parseFloat($("total_ingrediente").value)))
  {
    total_ingrediente= parseFloat($("total_ingrediente").value);
  }
}

if(document.getElementById("total_bebida"))
{
  if(!isNaN(parseFloat($("total_bebida").value)))
  {
    total_bebida = parseFloat($("total_bebida").value);
  }
}

if(document.getElementById("total_ipi"))
{
  if(!isNaN(parseFloat($("total_ipi").value)))
  {
  total_ipi = parseFloat($("total_ipi").value.replace('.','').replace(',','.'));
  }
}

if(document.getElementById("desconto_total"))
{
  if(!isNaN(parseFloat($("desconto_total").value)))
  {
  desconto_total = parseFloat($("desconto_total").value.replace('.','').replace(',','.'));
  }
}

if(document.getElementById("total_icms"))
{
  if(!isNaN(parseFloat($("total_icms").value)))
  {
  total_icms = parseFloat($("total_icms").value.replace('.','').replace(',','.'));
  }
}

if(document.getElementById("outras_despesas"))
{
  if(!isNaN(parseFloat($("outras_despesas").value)))
  {
  outras_despesas = parseFloat($("outras_despesas").value.replace('.','').replace(',','.'));
  }
}

var total = total_ingrediente+total_bebida+total_ipi+total_icms+outras_despesas-desconto_total;

total = Math.round((total*100))/100;


$("preco_total_nota").value =  bd2moeda(total.toString());

}

window.addEvent('domready', function()
{
  exibir_ingredientes_adicionados();
  exibir_bebidas_adicionados();
});
      

//(validar_pizzarias(this)) &&
</script>
   
<form name="frmIncluir" method="post" onsubmit="{return (validar_nota())&& (validaRequeridos(this))}">

  <table align="center" class="caixa" cellpadding="0" cellspacing="0" width="700">

  <tr>
        <td class="legenda tdbl tdbr tdbt">
            <label for="cod_pizzarias" class="requerido"><? echo ucfirst(TIPO_EMPRESA)?></label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <select name="cod_pizzarias" id="cod_pizzarias" class="requerido" style="width: 230px;">
              <option value=""></option>
              
              <?

              $conexao = conectabd();
              
              $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";
              $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
              
              while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
              {
                  echo '<option value="' . $obj_buscar_pizzarias->cod_pizzarias . '">' . bd2texto($obj_buscar_pizzarias->nome) . '</option>';
              }
              
              desconectabd($conexao);
              
              ?>
              
            </select>
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr">
            <label for="numero_nota_fiscal">Número da Nota Fiscal</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text" name="numero_nota_fiscal" id="numero_nota_fiscal" maxlength="60" size="40">
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr">
          <label for="cod_titulos_subcategorias" class="requerido">Grupo e Subgrupo</label>
        </td>
    </tr>
    <tr>
    <td class="tdbl tdbr sep">
          <select name="cod_titulos_subcategorias" id="cod_titulos_subcategorias" class="requerido" style="width: 230px;" onchange="criar_formulario_pagamento(this.value)">
            <option value=""></option>
            
            <?

            $conexao = conectabd();
            
                $sql_buscar_categorias = "SELECT * FROM ipi_titulos_categorias WHERE cod_titulos_categorias IN (SELECT cod_titulos_categorias FROM ipi_titulos_subcategorias WHERE tipo_titulo = 'PAGAR' AND tipo_cendente_sacado = 'FORNECEDOR') ORDER BY titulos_categoria";
                $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                
                while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                {
                    echo '<optgroup label="' . bd2texto($obj_buscar_categorias->titulos_categoria) . '">';
                    
                    $sql_buscar_subcategorias = "SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = '" . $obj_buscar_categorias->cod_titulos_categorias . "' AND tipo_titulo = 'PAGAR' AND tipo_cendente_sacado = 'FORNECEDOR' ORDER BY titulos_subcategorias";
                    $res_buscar_subcategorias = mysql_query($sql_buscar_subcategorias);
                    
                    while($obj_buscar_subcategorias = mysql_fetch_object($res_buscar_subcategorias))
                    {
                        echo '<option value="' . $obj_buscar_subcategorias->cod_titulos_subcategorias . '">' . bd2texto($obj_buscar_subcategorias->titulos_subcategorias) . '</option>';
                    }
                    
                    echo '</optgroup>';
                }
            
            desconectabd($conexao);
            
            ?>
          </select>
      </td>
  </tr>


    <tr><td id="formulario_pagamento" class="tdbl tdbr sep">&nbsp;</td></tr>

    <tr>
        <td class="legenda tdbl tdbr sep">
          <label>Ingredientes</label>
          <hr size="1" noshade="noshade" color="#1A498F">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr sep">
            <input type="text" name="busca_ingrediente" id="busca_ingrediente" maxlength="60" size="40" onkeypress="return bloquear_enter(this, event)">
            &nbsp;
            <input type="button" class="botaoAzul" value="Buscar" onclick="buscar_ingredientes()">
        </td>
    </tr>
    
    <tr><td class="tdbl tdbr sep" id="resultado_busca_ingrediente"></td></tr>
    
    <tr><td class="tdbl tdbr sep" id="resultado_ingrediente_adicionado"></td></tr>
    
    
    <tr>
        <td class="legenda tdbl tdbr sep">
          <label>Bebidas</label>
          <hr size="1" noshade="noshade" color="#1A498F">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr sep">
            <input type="text" name="busca_bebida" id="busca_bebida" maxlength="60" size="40" onkeypress="return bloquear_enter_bebida(this, event)">
            &nbsp;
            <input type="button" class="botaoAzul" value="Buscar" onclick="buscar_bebidas()">
        </td>
    </tr>
    
    <tr><td class="tdbl tdbr sep" id="resultado_busca_bebida"></td></tr>
    
    <tr><td class="tdbl tdbr sep" id="resultado_bebida_adicionado"></td></tr>
    
    <tr>
     &nbsp;
    <td class="legenda tdbl tdbr sep">
     <table class="tdbl tdbr cabecalhoEdicao" cellpadding="0" cellspacing="0" width="600" align="center">
            <tr>
                <td style="color:white"><b>Valor Total da Nota</b></td>
            </tr>
     </table>
    <table class="tdbl tdbr listaEdicao" cellpadding="0" cellspacing="0" width="400" align="center">
      <thead>
      <tr>
          <td class="legenda tdbl tdbr sep" align="center" width="20%">
            <label>ICMS Substituição</label>
          </td>
          <td class="legenda tdbl tdbr sep" align="center" width="20%">
            <label>Valor total do IPI</label>
          </td>
          <td class="legenda tdbl tdbr sep" align="center" width="20%">
            <label>Outras despesas Acessórias</label>
          </td>
          <td class="legenda tdbl tdbr sep" align="center" width="20%">
            <label>Desconto Total da Nota</label>
          </td>
          <td class="legenda tdbl tdbr sep" align="center" width="20%">
            <label>Preço Total Nota</label>
          </td>
      </tr>
      </thread>
      <tbody>
        <tr>
          <td align="center">
            <input type="text" name="total_icms" id="total_icms" onkeypress="return formataMoeda(this, '.', ',', event)" onKeyup="calcular_valor_final()" style="width:80"/>
          </td>
          <td align="center">
            <input type="text" name="total_ipi" id="total_ipi"  onkeypress="return formataMoeda(this, '.', ',', event)" onKeyup="return calcular_valor_final()" style="width:80"/>
          </td>
          <td align="center">
            <input type="text" name="outras_despesas" id="outras_despesas"onkeypress="return formataMoeda(this, '.', ',', event)" onKeyup="calcular_valor_final()" style="width:80" />
          </td>
          <td align="center">
            <input type="text" name="desconto_total" id="desconto_total"onkeypress="return formataMoeda(this, '.', ',', event)" onKeyup="calcular_valor_final()" style="width:80" />
          </td>
          <td >
            <input type="text" name="preco_total_nota" id="preco_total_nota" readonly="readonly" style="border:0;text-align:right;font-weight:bold;width=80"/>
          </td></tr>

    </tbody>
  </table></td></tr>

    
    <tr>
        <td align="center" class="tdbl tdbb tdbr">
          <input name="botao_submit" class="botao" type="submit" value="Cadastrar">
      </td>
    </tr>

</table>

<input type="hidden" name="acao" value="editar"> 
<input type="hidden" name="<? echo $chave_primaria?>" value="<? echo $codigo?>">

</form>

<?
/*
echo "<pre>";
print_r($_SESSION);
echo "</pre>";*/
rodape();
?>
