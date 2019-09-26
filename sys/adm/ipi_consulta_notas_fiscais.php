<?php

/**
 * Consulta de Notas Fiscais.
 *
 * @version 1.0
 * @package iti
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       05/11/2012   PEDRO H       Criado.
 *
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Consulta de Notas Fiscais');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_titulos';
$tabela = 'ipi_titulos';
$campo_ordenacao = 'numero_nota_fiscal';
$campo_filtro_padrao = 'numero_nota_fiscal';
$quant_pagina = 50;
$exibir_barra_lateral = false;
$codigo_usuario = $_SESSION['usuario']['codigo'];
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

if($acao=="" && validaVarGet('ct', '/[0-9]+/') !="")
{
  $acao = "detalhes";
}
if($acao == 'editar_item')
{ 
  $cod_estoque_entrada_itens = validar_var_post('cod_estoque_entrada_itens');
  $tipo_item = validar_var_post('tipo_item');
  $cod_item = validar_var_post('cod_item');
  $divisor_comum = validar_var_post($tipo_item.'_divisor_'.$cod_item);
  $quantidade_embalagem_entrada = validar_var_post($tipo_item.'_txt_quantidade_embalagem_entrada_'.$cod_item);
  $quantidade_embalagem_entrada = str_replace(',', '.', $quantidade_embalagem_entrada);
  $quantidade_embalagem_entrada = $quantidade_embalagem_entrada*$divisor_comum;

  $quantidade_entrada = validar_var_post($tipo_item.'_v_quantidade_entrada_'.$cod_item);
  $quantidade_entrada = str_replace(',', '.', $quantidade_entrada);

  $con = conectar_bd();
  require_once '../../classe/estoque.php';
  $estoque = new Estoque();

  if ($estoque->alterar_nota_fiscal($cod_estoque_entrada_itens, $quantidade_entrada, $quantidade_embalagem_entrada))
  {
    mensagemOK('Registro atualizado com êxito!');
    $estoque->reprocessar_mapa_estoque($cod_estoque_entrada_itens);
  }
  else
  {
    mensagemErro('Erro ao atualizar o registro', 'Por favor, verifique os dados e tente novamente.');
  }

  desconectar_bd($con);
  $acao = 'detalhes';
}

?>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<script src="../../sys/lib/js/mascara.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />
<script src="../lib/js/calendario.js" type="text/javascript"></script>


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

  var input2 = new Element('input', {
    'type': 'hidden',
    'name': 'acao',
    'value': 'detalhes'
  });
  
  input.inject(form);
  input2.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function editarEntradaItem (cod, tipo, cod_est, divisor)
{
  var valor_quantidade_embalagem_entrada = $(tipo+'_quantidade_embalagem_entrada_'+cod).get('value').replace('.',',');
  var valor_quantidade_entrada = $(tipo+'_quantidade_entrada_'+cod).get('value').replace('.',',');
  
  var input = new Element('input', {
    'type': 'text',
    'name': tipo+'_txt_quantidade_embalagem_entrada_'+cod,
    'value': valor_quantidade_embalagem_entrada,
    'events': { 'keypress': 
                function(){
                  formataMoeda3casas(this, 2);
                }
              }
  });

  var input2 = new Element('input', {
    'type': 'hidden',
    'name': tipo+'_v_quantidade_entrada_'+cod,
    'value': valor_quantidade_entrada,
  });

  var input3 = new Element('input', {
    'type': 'submit',
    'name': tipo+'_btn_editar_'+cod,
    'class': 'botaoAzul',
    'value': 'OK',
  });

  var input4 = new Element('input', {
    'type': 'hidden',
    'name': 'cod_estoque_entrada_itens',
    'value': cod_est,
  });

  var input5 = new Element('input', {
    'type': 'hidden',
    'name': 'tipo_item',
    'value': tipo,
  });

  var input6 = new Element('input', {
    'type': 'hidden',
    'name': 'cod_item',
    'value': cod,
  });

  var input7 = new Element('input', {
    'type': 'hidden',
    'name': tipo+'_divisor_'+cod,
    'value': divisor,
  });
  
  $(tipo+'_td_quantidade_embalagem_entrada_'+cod).set({html: ''});
  $(tipo+'_editar_'+cod).set({html: ''});

  input.inject($(tipo+'_td_quantidade_embalagem_entrada_'+cod));
  input2.inject($(tipo+'_editar_'+cod));
  input3.inject($(tipo+'_editar_'+cod));
  input4.inject($(tipo+'_editar_'+cod));
  input5.inject($(tipo+'_editar_'+cod));
  input6.inject($(tipo+'_editar_'+cod));
  input7.inject($(tipo+'_editar_'+cod));
}

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
    <?
    if ($acao == 'detalhes')
        echo 'tabs.irpara(1);';
    ?>
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      
    }
  });
});

</script>

<div id="tabs">
<div class="menuTab">
<ul>
    <li><a href="javascript:;">Voltar lista</a></li>
    <li><a style='display:none' href="javascript:;">Detalhes</a></li>
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
  
        <script type="text/javascript">
        window.addEvent('domready', function()
        {
            new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
            new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});    
        });

        </script>

        <?
        $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0;
        $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : $campo_filtro_padrao;
        $filtro = validaVarPost('filtro');
        $data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
        $data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $filtro_limites = validaVarPost("filtro_limites");
        ?>
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

            <tr>
                <td class="legenda tdbl tdbt" align="right">
                  <select name="opcoes">
                        <option value="<? echo $campo_filtro_padrao ?>"<? if ($opcoes == $campo_filtro_padrao) {echo 'selected';}?>>Número NF</option>
                        <option value="nome_fantasia"<? if ($opcoes == "nome_fantasia") {echo 'selected';}?>>Fornecedor</option>
                  </select>
                </td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text"
                    name="filtro" size="60" value="<?
                    echo $filtro?>"></td>
            </tr>
            
            <tr>
              <td class="legenda tdbl " align="right"><label for="cod_pizzarias"><?php echo TIPO_EMPRESA ?>:</label></td>
              <td class="">&nbsp;</td>
              <td class="tdbr ">
                <select name="cod_pizzarias" id="cod_pizzarias">
                  <option value="">Todas as <?php echo TIPO_EMPRESAS ?></option>
                  <?
              $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

                  $con = conectabd();
                  $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
                  $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
                  
                  while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
              {
                echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
                if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
                {
                  echo 'selected';
                }
                echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
              }
                  ?>
                </select>
              </td>
            </tr>
            
            <tr>
              <td class="legenda tdbl" align="right"><label for="data_inicial">Data inicial:</label></td>
              <td class="">&nbsp;</td>
              <td class="tdbr">
                <input class="requerido" type="text" name="data_inicial" id="data_inicial" size="10" value="<? echo bd2data($data_inicial) ?>" onkeypress="return MascaraData(this, event)">
                &nbsp;<a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
              </td>
            </tr>

            <tr>
              <td class="legenda tdbl " align="right"><label for="data_final">Data final:</label></td>
              <td class="">&nbsp;</td>
              <td class="tdbr ">
                <input class="requerido" type="text" name="data_final" id="data_final" size="10" value="<? echo bd2data($data_final) ?>" onkeypress="return MascaraData(this, event)">
                &nbsp;<a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
              </td>
            </tr>

            <tr>
              <td class="legenda tdbl sep" align="right"><label for="filtro_limites">Somente notas com itens <br/>lançados fora dos limites?</label></td>
              <td class="">&nbsp;</td>
              <td class="tdbr sep">
                <select name="filtro_limites" id="filtro_limites">
                  <option value="">Não</option>
                  <option value="SIM" <? echo ($filtro_limites=="SIM" ? 'selected="selected"' : ''); ?> >Sim</option>
                </select>
              </td>
            </tr>
            <tr>
                <td align="right" class="tdbl tdbb tdbr" colspan="3"><input
                    class="botaoAzul" type="submit" value="Buscar"></td>
            </tr>

        </table>

        <input type="hidden" name="acao" value="buscar">
      </form>

        <br>

        <?

        if($acao == 'buscar')
        {
          $conexao = conectar_bd();

          $sql_buscar_registros = "SELECT t.numero_nota_fiscal, ifo.nome_fantasia, ifo.razao_social, (SELECT SUM(valor) FROM ipi_titulos_parcelas itp WHERE itp.cod_titulos = t.cod_titulos) as total,(SELECT count(cod_estoque_entrada_itens) from ipi_estoque_entrada_itens where cod_estoque_entrada = iee.cod_estoque_entrada and entrada_fora_limites = 2) as qtde_items_errados, t.cod_titulos FROM $tabela t inner join ipi_fornecedores ifo ON (ifo.cod_fornecedores = t.cod_fornecedores) INNER JOIN ipi_estoque_entrada iee ON (iee.cod_estoque_entrada = t.cod_estoque_entrada) WHERE ".($opcoes=='nome_fantasia' ? "ifo.$opcoes" : "t.$opcoes")." LIKE '%$filtro%' AND t.cod_pizzarias in(".$cod_pizzarias_usuario.",0) AND iee.data_hota_entrada_estoque BETWEEN '".$data_inicial." 00:00:00' AND '".$data_final." 23:59:59' ";

          if($cod_pizzarias > 0)
            $sql_buscar_registros .= "AND t.cod_pizzarias = '".$cod_pizzarias."' ";

	          
          if($filtro_limites=="SIM")
          {
             $sql_buscar_registros .= ' having qtde_items_errados >0 ';
          }
					
          $res_buscar_registros = mysql_query($sql_buscar_registros);
          $num_buscar_registros = mysql_num_rows($res_buscar_registros);


          $sql_buscar_registros .= ' ORDER BY t.numero_nota_fiscal DESC LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;//ultimo_coment desc,
          $res_buscar_registros = mysql_query($sql_buscar_registros);
          //echo $sql_buscar_registros."<br/>";
          $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
          
          //echo $sql_buscar_registros;

          echo "<center><b>" . $num_buscar_registros . " registro(s) encontrado(s)</b></center><br>";
          
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
              echo '<input type="hidden" name="data_inicial" value="' . bd2data($data_inicial) . '">';
              echo '<input type="hidden" name="data_final" value="' . bd2data($data_final) . '">';
              echo '<input type="hidden" name="cod_pizzarias" value="' . $cod_pizzarias . '">';
              
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
        ?>

        <br>

        <form name="frmExcluir" method="post">

        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <tr>
                <td></td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <td align="center">Número NF</td>
                    <td align="center">Fornecedor<br />Nome Fantasia</td>
                    <td align="center">Fornecedor<br />Razão Social</td>
                    <td align="center">Total da NF</td>
                    <td align="center">Itens lançados fora dos limites</td>
                </tr>
            </thead>
            <tbody>
            <?
            $a = 0;
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                //echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->cod_titulos . ')">' .($obj_buscar_registros->numero_nota_fiscal ? $obj_buscar_registros->numero_nota_fiscal : '0000') . '</a></td>';

                echo '<td align="center"><a href="ipi_consulta_notas_fiscais.php?ct='. $obj_buscar_registros->cod_titulos . '">' .($obj_buscar_registros->numero_nota_fiscal ? $obj_buscar_registros->numero_nota_fiscal : '0000') . '</a></td>';
                
                echo '<td align="center" >'.$obj_buscar_registros->nome_fantasia.'</td>';
                echo '<td align="center" >'.$obj_buscar_registros->razao_social.'</td>';
                echo '<td align="center" >R$ '.bd2moeda(abs($obj_buscar_registros->total)).'</td>';
                echo '<td align="center" >'.($obj_buscar_registros->qtde_items_errados > 0 ? '<b style="color:red">'.$obj_buscar_registros->qtde_items_errados : 0) .'</td>';
                

                echo '</tr>';
                $a++;
            }
            desconectabd($conexao);
            ?>
            </tbody> 
        </table>

        <input type="hidden" name="acao" value="excluir">
        </form>

<?
}
if ($exibir_barra_lateral)
:
    ?>

        </td>
        <!-- Conteúdo -->

        <!-- Barra Lateral -->
        <td class="lateral">
        <div class="blocoNavegacao">
        <ul>
            <li></li>
            <!--<li><a href="ipi_central_tickets.php"></a></li>-->
        </ul>
        </div>
        </td>
        <!-- Barra Lateral -->

</table>


<? endif; ?>

</div>

<!-- Tab Editar --> <!-- Tab Incluir -->
<div class="painelTab">
    <?
    $codigo = (validaVarPost($chave_primaria, '/[0-9]+/') != "" ? validaVarPost($chave_primaria, '/[0-9]+/') : validaVarGet('ct', '/[0-9]+/'));
    $conexao = conectar_bd();

    if ($codigo > 0)
    {
      $sql_buscar_nf = "SELECT  t.cod_pizzarias as codigo_pizzaria, t.numero_nota_fiscal, ifo.*,t.desconto, t.total_ipi, t.total_icms, t.outras_despesas, (SELECT SUM(valor) FROM ipi_titulos_parcelas itp WHERE itp.cod_titulos = t.cod_titulos) as total_nf, t.total_parcelas FROM $tabela t inner join ipi_fornecedores ifo ON (ifo.cod_fornecedores = t.cod_fornecedores) WHERE t.cod_titulos = '".$codigo."'";
      //echo $sql_buscar_nf;
      $res_buscar_nf = mysql_query($sql_buscar_nf);
      $obj_buscar_nf = mysql_fetch_object($res_buscar_nf);

      $sql_buscar_pizzaria_nf = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias = '".$obj_buscar_nf->codigo_pizzaria."'";
      $res_buscar_pizzaria_nf = mysql_query($sql_buscar_pizzaria_nf);
      $obj_buscar_pizzaria_nf = mysql_fetch_object($res_buscar_pizzaria_nf);

      $sql_buscar_dados_lancador = "SELECT ee.data_hota_entrada_estoque as data_lancamento,usu.nome FROM ipi_estoque_entrada ee inner join ipi_titulos t on t.cod_estoque_entrada = ee.cod_estoque_entrada inner join nuc_usuarios usu on usu.cod_usuarios = ee.cod_usuarios WHERE t.cod_titulos = '".$codigo."'";
      $res_buscar_dados_lancador = mysql_query($sql_buscar_dados_lancador);
      $obj_buscar_dados_lancador = mysql_fetch_object($res_buscar_dados_lancador);
    }
    ?>
      <div style='width:1000px; margin:0 auto;'>
        <h1> Nota Fiscal de número: <? echo ($obj_buscar_nf->numero_nota_fiscal ? $obj_buscar_nf->numero_nota_fiscal : '0000'); ?></h1>
        <br />
        <hr />
        <br/>
        Nota lançada por <b><? echo $obj_buscar_dados_lancador->nome ?></b> em <? echo date("d/m/Y",strtotime($obj_buscar_dados_lancador->data_lancamento)) ?> ás <? echo date("H:i",strtotime($obj_buscar_dados_lancador->data_lancamento)) ?>
        <br /><br />
        <hr />
        <br />
        <h3>Dados do Fornecedor</h3>
        <?
          echo "<p>".$obj_buscar_nf->nome_fantasia." - ".$obj_buscar_nf->razao_social." - CNPJ: ".$obj_buscar_nf->cnpj."</p>"; 
          echo "<p>".$obj_buscar_nf->endereco.", ".$obj_buscar_nf->numero.($obj_buscar_nf->complemento ? ' - '.$obj_buscar_nf->complemento : ' ').", ".$obj_buscar_nf->bairro."<br />".$obj_buscar_nf->cidade."-".$obj_buscar_nf->estado." CEP:".$obj_buscar_nf->cep."<br />Tel.:".$obj_buscar_nf->telefone."</p>"; 

        ?>
        
        <br />
        <hr />
        <br />

        <h3>Dados destinatário</h3>
          <?
            echo "<p>".$obj_buscar_pizzaria_nf->nome_fantasia." - ".$obj_buscar_pizzaria_nf->razao_social." - CNPJ: ".$obj_buscar_pizzaria_nf->cnpj."</p>"; 
            echo "<p>".$obj_buscar_pizzaria_nf->endereco.", ".$obj_buscar_pizzaria_nf->numero.($obj_buscar_pizzaria_nf->complemento ? ' - '.$obj_buscar_pizzaria_nf->complemento : ' ').", ".$obj_buscar_pizzaria_nf->bairro."<br />".$obj_buscar_pizzaria_nf->cidade."-".$obj_buscar_pizzaria_nf->estado." CEP:".$obj_buscar_pizzaria_nf->cep."<br />Tel.:".$obj_buscar_pizzaria_nf->telefone."</p>";      
          ?>   
        <br/>
        <hr/>
        <br/>

        <h3>Parcelas</h3><br />

        <table class="listaEdicao">
          <thead>
            <tr>
              <td width='250' align="center">Parcela</td>
              <td width='250' align="center">Data vencimento</td>
              <td width='250' align="center">Data pagamento</td>
              <td width='250' align="center">Valor</td>
            </tr>
          </thead>
        <? 
          $sql_buscar_parcelas_nf = "SELECT valor_total, numero_parcela, data_vencimento, data_pagamento FROM ipi_titulos_parcelas WHERE cod_titulos = '".$codigo."'";
          $res_buscar_parcelas_nf = mysql_query($sql_buscar_parcelas_nf);
          while($obj_buscar_parcelas_nf = mysql_fetch_object($res_buscar_parcelas_nf))
          {
            echo '<tr>';
            echo '  <td align="center">';
            echo $obj_buscar_parcelas_nf->numero_parcela.'/'.$obj_buscar_nf->total_parcelas;
            echo '  </td>';
            
            echo '  <td align="center">';
            echo bd2data($obj_buscar_parcelas_nf->data_vencimento);
            echo '  </td>';
            
            echo '  <td align="center">';
            echo ($obj_buscar_parcelas_nf->data_pagamento != '0000-00-00' ? bd2data($obj_buscar_parcelas_nf->data_pagamento) : '-');
            echo '  </td>';
            
            echo '  <td align="center">';
            echo 'R$ '.bd2moeda(abs($obj_buscar_parcelas_nf->valor_total));
            echo '  </td>';
            echo '</tr>';
          }

        ?>
        </table>

        <br />
        <hr />
        <br />

        <?
          $sql_buscar_itens_nf = "SELECT ieei.cod_estoque_entrada_itens,ieei.entrada_fora_limites, ieei.cod_bebidas_ipi_conteudos, ieei.cod_ingredientes, ieei.tipo_entrada_estoque, ieei.quantidade_entrada, ieei.quantidade_embalagem_entrada,ieei.preco_total_entrada, ieei.preco_unitario_entrada FROM ipi_titulos it INNER JOIN ipi_estoque_entrada iee ON (iee.cod_estoque_entrada = it.cod_estoque_entrada) INNER JOIN ipi_estoque_entrada_itens ieei ON (ieei.cod_estoque_entrada = iee.cod_estoque_entrada) WHERE it.cod_titulos = '".$codigo."'";
          $res_buscar_itens_nf = mysql_query($sql_buscar_itens_nf);
          //echo $sql_buscar_itens_nf.'<br />';
        ?>

        <h3>Dados dos produtos</h3><br />
         
        <form action='<?echo $_SERVER['PHP_SELF'];?>' method='post'>
        <table class='listaEdicao' width='1000'>
          <thead>
            <tr>
              <td width='500'> Descrição do Produto </td>
              <td width='100' align='center'> Quantidade </td>
              <td width='100' align='center'> Quantidade por Embalagem </td>
              <td width='100' align='center'> Valor Unit. </td>
              <td width='100' align='center'> Valor Total (calculado)</td>
              <td width='100' align='center'> Valor Total (digitado)</td>
              <td width='40' align='center'> &nbsp; </td>
            </tr>
          </thead>
          <tbody>
            <?
              while($obj_buscar_itens_nf = mysql_fetch_object($res_buscar_itens_nf))
              {
                echo "<tr >";
                if($obj_buscar_itens_nf->tipo_entrada_estoque == "BEBIDA")
                {
                  $sql_buscar_produtos = "SELECT ib.bebida, ic.conteudo, up.unidade, up.divisor_comum FROM ipi_bebidas_ipi_conteudos ibic LEFT JOIN ipi_conteudos ic ON (ic.cod_conteudos = ibic.cod_conteudos) LEFT JOIN ipi_bebidas ib ON (ib.cod_bebidas = ibic.cod_bebidas) LEFT JOIN ipi_unidade_padrao up ON (ibic.cod_unidade_padrao = up.cod_unidade_padrao) WHERE ibic.cod_bebidas_ipi_conteudos = '".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."'";
                  //echo $sql_buscar_produtos.'<br />';
                  $res_buscar_produtos = mysql_query($sql_buscar_produtos);
                  $obj_buscar_produtos = mysql_fetch_object($res_buscar_produtos);
                  $divisor_comum = ($obj_buscar_produtos->divisor_comum > 0 ? $obj_buscar_produtos->divisor_comum : 1);
                  echo "  <td id='bebida_conteudo_".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."'> ".$obj_buscar_produtos->bebida." ".$obj_buscar_produtos->conteudo." </td>"; 
                  
                  echo "<input type='hidden' id='bebida_quantidade_embalagem_entrada_".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."' value='".$obj_buscar_itens_nf->quantidade_embalagem_entrada."'/>";
                  echo "<input type='hidden' id='bebida_quantidade_entrada_".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."' value='".$obj_buscar_itens_nf->quantidade_entrada."'/>";

                  echo "  <td align='right' id='bebida_td_quantidade_entrada_".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."'> ".$obj_buscar_itens_nf->quantidade_entrada." </td>";
                  echo "  <td align='right' id='bebida_td_quantidade_embalagem_entrada_".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."'> ".($obj_buscar_itens_nf->quantidade_embalagem_entrada/$divisor_comum)." </td>"; 
                  echo "  <td align='right' id='bebida_preco_unitario_entrada".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."'> R$ ".bd2moeda($obj_buscar_itens_nf->preco_unitario_entrada)." </td>";
                  echo "  <td align='right' id='bebida_preco_total_".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."'> R$ ".bd2moeda($obj_buscar_itens_nf->preco_unitario_entrada * ($obj_buscar_itens_nf->quantidade_entrada) )." </td>";  

                  echo "  <td align='right' id='bebida_td_total_entrada_".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."'> ".$obj_buscar_itens_nf->preco_total_entrada." </td>";

                  echo "<td id='bebida_editar_".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."' align='center'>";
                  echo "<input class='botaoAzul' type='button' value='Editar' onclick='editarEntradaItem(".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos.", \"bebida\", ".$obj_buscar_itens_nf->cod_estoque_entrada_itens.", ".$divisor_comum.")'/>";
                  echo "</td>";   
                  echo "<input type='hidden' name='".$chave_primaria."' value='".$codigo."'/>";
                  echo "<input type='hidden' id='acao' name='acao' value='editar_item'/>"; 
                }

                
                elseif($obj_buscar_itens_nf->tipo_entrada_estoque == "INGREDIENTE")
                {
                  $sql_buscar_produtos = "SELECT ii.ingrediente, up.abreviatura, up.divisor_comum FROM ipi_ingredientes ii LEFT JOIN ipi_unidade_padrao up ON (ii.cod_unidade_padrao = up.cod_unidade_padrao)  WHERE ii.cod_ingredientes = '".$obj_buscar_itens_nf->cod_ingredientes."'";
                  $res_buscar_produtos = mysql_query($sql_buscar_produtos);
                  $obj_buscar_produtos = mysql_fetch_object($res_buscar_produtos);
                  $divisor_comum = ($obj_buscar_produtos->divisor_comum > 0 ? $obj_buscar_produtos->divisor_comum : 1);
                  $abreviatura = ($obj_buscar_produtos->abreviatura ? ' <b>(em '.$obj_buscar_produtos->abreviatura.')</b>' : '');
                  //echo $sql_buscar_produtos.'<br />';
                  echo "  <td id='ingrediente_".$obj_buscar_itens_nf->cod_ingredientes."' > ".$obj_buscar_produtos->ingrediente." - ".$obj_buscar_produtos->ingrediente_marca.$abreviatura.($obj_buscar_itens_nf->entrada_fora_limites == 2 ? '<i style="color:red"> (entrada com quantidade fora dos limites)</i>' : '')." </td>"; 

                  echo "<input type='hidden' id='ingrediente_quantidade_embalagem_entrada_".$obj_buscar_itens_nf->cod_ingredientes."' value='".($obj_buscar_itens_nf->quantidade_embalagem_entrada/$divisor_comum)."'/>";
                  echo "<input type='hidden' id='ingrediente_quantidade_entrada_".$obj_buscar_itens_nf->cod_ingredientes."' value='".$obj_buscar_itens_nf->quantidade_entrada."'/>";

                  echo "  <td align='right' id='ingrediente_td_quantidade_entrada_".$obj_buscar_itens_nf->cod_ingredientes."'> ".$obj_buscar_itens_nf->quantidade_entrada." </td>";
                  echo "  <td align='right' id='ingrediente_td_quantidade_embalagem_entrada_".$obj_buscar_itens_nf->cod_ingredientes."'> ".str_replace('.', ',', ($obj_buscar_itens_nf->quantidade_embalagem_entrada/$divisor_comum))." </td>";
                  echo "  <td align='right' id='ingrediente_preco_unitario_entrada".$obj_buscar_itens_nf->cod_ingredientes."'> R$ ".bd2moeda($obj_buscar_itens_nf->preco_unitario_entrada)." </td>";
                  echo "  <td align='right' id='ingrediente_preco_total_".$obj_buscar_itens_nf->cod_ingredientes."'> R$ ".bd2moeda($obj_buscar_itens_nf->preco_unitario_entrada * ($obj_buscar_itens_nf->quantidade_entrada) )." </td>";

                  echo "  <td align='right' id='ingrediente_aspreco_total_".$obj_buscar_itens_nf->cod_ingredientes."'> R$ ".bd2moeda($obj_buscar_itens_nf->preco_total_entrada)." </td>";

                   //echo "  <td align='right' id='bebida_td_total_entrada_".$obj_buscar_itens_nf->cod_bebidas_ipi_conteudos."'> ".$obj_buscar_itens_nf->preco_total_entrada." </td>";

                  echo "<td id='ingrediente_editar_".$obj_buscar_itens_nf->cod_ingredientes."' align='center'>";
                  echo "<input class='botaoAzul' type='button' value='Editar' onclick='editarEntradaItem(".$obj_buscar_itens_nf->cod_ingredientes.", \"ingrediente\", ".$obj_buscar_itens_nf->cod_estoque_entrada_itens.", ".$divisor_comum.")'/>";
                  echo "</td>"; 
                  echo "<input type='hidden' name='".$chave_primaria."' value='".$codigo."'/>";                  
                  echo "<input type='hidden' id='acao' name='acao' value='editar_item'/>";
                }
                
                echo "</tr>"; 
              }
            ?>
          </tbody>
        </table>
        </form>
        
        <br/>
        <hr/>
        <br/>

        <h3>Impostos</h3><br/>
        <table class='listaEdicao'>
          <thead>
            <tr>
              <td width='200'>ICMS Substituição</td>
              <td width='200'>Valor total do IPI</td>
              <td width='200'>Outras despesas Acessórias</td>
              <td width='200'>Total com impostos</td>
              <td width='200'>Desconto</td>
            </tr>
          </thead>  

          <tbody>
            <tr>
              <td align='right'><? echo 'R$ '.bd2moeda($obj_buscar_nf->total_ipi); ?></td>
              <td align='right'><? echo 'R$ '.bd2moeda($obj_buscar_nf->total_icms); ?></td>
              <td align='right'><? echo 'R$ '.bd2moeda($obj_buscar_nf->outras_despesas); ?></td>
              <? $total_impostos = bd2moeda($obj_buscar_nf->total_ipi + $obj_buscar_nf->total_icms + $obj_buscar_nf->outras_despesas); ?>
              <td align='right'><? echo 'R$ '.($total_impostos > 0 ? $total_impostos : '0,00'); ?></td>
              <td align='right'><? echo 'R$ '.bd2moeda($obj_buscar_nf->desconto); ?></td>
            </tr> 
          </tbody>
        </table>

        <br />
        <hr/>
        <br/>

        <h2 style='text-align:right;'>Total da nota: R$ <? echo bd2moeda(abs($obj_buscar_nf->total_nf)); ?></h2>
        

      </div>

    <? desconectar_bd($conexao); ?>
</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>