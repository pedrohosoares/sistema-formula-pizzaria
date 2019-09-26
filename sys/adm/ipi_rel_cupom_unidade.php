<?php

/**
 * ipi_cupom.php: Cadastro de Cupom
 * 
 * Índice: cod_cupons
 * Tabela: ipi_cupons
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Utilização dos Cupons');

$acao = validaVarPost('acao');


$tabela = 'ipi_cupons';
$chave_primaria = 'cod_cupons';
$quant_pagina = 70;
?>

<script>
function verificar(form) {
  if(form.txtNumCupomFiltro.value==""){
    alert("Digite o número do Cupom!");
    form.txtNumCupomFiltro.focus();
    return false;
  }
  
  return true;
}

function trocar(cod_cupons,cod_pizzarias,situacao) 
{
  var idTrocar = 'result_'+cod_cupons+'_'+cod_pizzarias;
  var controle = 'controle_'+cod_cupons+'_'+cod_pizzarias;
  if (document.getElementById(idTrocar).style.display=="none")
  {
    if(document.getElementById(controle).value==0)
    {
      carregaDetalhes(cod_cupons,cod_pizzarias,situacao);
      document.getElementById(controle).value = 1
    }
    document.getElementById(idTrocar).style.display='block';
  }
  else
  {
    document.getElementById(idTrocar).style.display='none';
  }
}

function carregaDetalhes(cod_cupons,cod_pizzarias,situacao) 
{
  var url = 'acao=carregar_detalhes';
  
  url += '&c=' + cod_cupons+'&p=' + cod_pizzarias+'&s='+situacao;
  
  $('tbody_'+cod_cupons+'_'+cod_pizzarias).set('text', 'Carregando...');
  
  new Request.HTML({
  url: 'ipi_rel_cupom_unidade_ajax.php',
  update: $('tbody_'+cod_cupons+'_'+cod_pizzarias)
   }).send(url);
}


</script>

<div id="tabs">
  <!-- Tab Listar -->
  <div class="painelTab" align="center">
    <table><tr>
  
    <!-- Conteúdo -->
    <td class="conteudo">
  <?
    $cod_pizzarias = validaVarPost('cod_pizzarias');
    $cliente = validaVarPost("cliente");
    $situacao = validaVarPost("situacao");
    $txtNumCupomFiltro = validaVarPost('txtNumCupomFiltro');
     if ($txtNumCupomFiltro=="*")
          $txtNumCupomFiltro="";



  ?>
  <form name="frmFiltro" method="post">
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    <tr>
      <td class="legenda tdbl tdbt" align="right"><label for="pedido">Código do cupom:</label></td>
      <td class="tdbt">&nbsp;</td>
      <td class="tdbt tdbr"><input class="requerido" type="text" name="txtNumCupomFiltro" id="txtNumCupomFiltro" size="60" value="<? echo $txtNumCupomFiltro ?>" ></td>
    </tr>
    
<!--     <tr>
      <td class="legenda tdbl" align="right"><label for="cliente">Cliente:</label></td>
      <td class="">&nbsp;</td>
      <td class="tdbr"><input class="requerido" type="text" name="cliente" id="cliente" size="60" value="<? echo $cliente ?>"></td>
    </tr> -->
    
<!--     <tr>
      <td class="legenda tdbl" align="right"><label for="data_inicial">Data Inicial:</label></td>
      <td>&nbsp;</td>
      <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo $data_inicial ?>" onkeypress="return MascaraData(this, event)">
      &nbsp;
      <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
      </td>
    </tr>
    
    <tr>
      <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
      <td>&nbsp;</td>
      <td class="tdbr">
      <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo $data_final ?>" onkeypress="return MascaraData(this, event)">
      &nbsp;
      <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
      </td>
    </tr> -->

    <!-- <tr>
      <td class="legenda tdbl" align="right"><label for="cod_pizzarias">Pizzaria:</label></td>
      <td>&nbsp;</td>
      <td class="tdbr">
        <select name="cod_pizzarias" id="cod_pizzarias">
          <option value="">Todas as Pizzarias</option>
          <?
          $con = conectabd();
          
          $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY nome";
          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
          while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
            echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
            
            if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
              echo 'selected';
            
            echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
          }
          
          desconectabd($con);
          ?>
        </select>
      </td>
    </tr> -->

<!--     <tr>
      <td class="legenda tdbl sep" align="right"><label for="entrega">Entrega:</label></td>
      <td class="sep">&nbsp;</td>
      <td class="tdbr sep">
        <select name="entrega" id="entrega">
          <option value="TODOS" <? if($entrega == 'TODOS') echo 'selected' ?>>Todas</option>
          <option value="Entrega" <? if($entrega == 'Entrega') echo 'selected' ?>>Entrega</option>
          <option value="Balcão" <? if($entrega == 'Balcão') echo 'selected' ?>>Balcão</option>
        </select>
      </td>
    </tr> -->


    <tr>
      <td class="legenda tdbl" align="right"><label for="situacao">Situação do pedido:</label></td>
      <td class="">&nbsp;</td>
      <td class="tdbr">
        <select name="situacao" id="situacao">
         <option value="BAIXADO" <? if($situacao == 'BAIXADO') echo 'selected' ?>>Baixado</option>
          <option value="TODOS" <? if($situacao == 'TODOS') echo 'selected' ?>>Todas</option>
          <option value="NOVO" <? if($situacao == 'NOVO') echo 'selected' ?>>Novo</option>
          <option value="IMPRESSO" <? if($situacao == 'IMPRESSO') echo 'selected' ?>>Impresso</option>
          <option value="CANCELADO" <? if($situacao == 'CANCELADO') echo 'selected' ?>>Cancelado</option>
        </select>
      </td>
    </tr>

    <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="buscar">
  </form>

    <? if ($acao=='buscar'): ?>
      <?
      if($txtNumCupomFiltro!="")
        {
          $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 
          $con = conectabd();
          //if($cod_pizzarias=="")
          //{
            $cod_pizzarias = implode(',', $_SESSION['usuario']['cod_pizzarias']);
          //}
          
          $filtro_clientes =  "";
          $$filtro_situacao= "";
          if($cliente)
          {
            $arr_clientes = array();
            $sql_buscar_cods_clientes = "SELECT cod_clientes from ipi_clientes where nome like '%".$cliente."%'";
           // echo $sql_buscar_cods_clientes."<br/><br/>";
            $res_buscar_cods_clientes = mysql_query($sql_buscar_cods_clientes);
            $qtd_clientes = mysql_num_rows($res_buscar_cods_clientes);

            if($qtd_clientes>0)
            {
              while($obj_buscar_cods_clientes = mysql_fetch_object($res_buscar_cods_clientes))
              {
                $arr_clientes[] = $obj_buscar_cods_clientes->cod_clientes;
              }
              $filtro_clientes = "and p.cod_clientes in (".implode(',',$arr_clientes).") ";
            }
            else
            {
              $filtro_clientes =  "and p.cod_clientes != p.cod_clientes";//se caso não encontrou um cliente, não realizar a busca (vai retornar nada)
            }
          }
          
          if($situacao!="TODOS")
          {  
            $filtro_situacao = " and p.situacao='".$situacao."'";
          }

          
          $SqlBuscaRegistros = "SELECT cp.*,usu.nome,piz.nome,piz.cod_pizzarias as piz_code, (SELECT count(pc.cod_pedidos) FROM ipi_pedidos_ipi_cupons pc INNER JOIN ipi_pedidos p ON (pc.cod_pedidos=p.cod_pedidos) INNER JOIN ipi_cupons ic ON (pc.cod_cupons=ic.cod_cupons) WHERE ic.cupom=cp.cupom and pic.cod_pizzarias = p.cod_pizzarias $filtro_situacao $filtro_clientes) num_utilizacoes FROM $tabela cp inner join nuc_usuarios usu on usu.cod_usuarios = cp.usuario_criacao INNER JOIN ipi_pizzarias_cupons pic ON (pic.cod_cupons = cp.cod_cupons) INNER JOIN ipi_pizzarias piz on piz.cod_pizzarias = pic.cod_pizzarias WHERE pic.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ";///p.cod_pizzarias IN (" . $cod_pizzarias . " ) AND p.cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).")
          
          if ($txtNumCupomFiltro)
              $SqlBuscaRegistros .= " AND cp.cupom='$txtNumCupomFiltro' ";
              
          $SqlBuscaRegistros .= " having num_utilizacoes > 0 ";
          //echo $SqlBuscaRegistros;
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

          $SqlBuscaRegistros .= " ORDER BY cod_cupons DESC LIMIT ".($quant_pagina * $pagina).", ".$quant_pagina;
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);

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
        <br/>
        <form name="frmRelCupons" method="post" onsubmit="return verificar(this)">
      
          <!-- <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
            <tr>
              <td>
    		Número do Cupom:
    		<input type="text" name="txtNumCupomFiltro" id="txtNumCupomFiltro" size="12" value="<? echo $txtNumCupomFiltro; ?>">
    		<input class="botaoAzul" name="btFiltrar" type="submit" value="Filtrar"> * Para todos
              </td>
            </tr>
          </table> -->
        
          <table class="listaEdicao" cellpadding="0" cellspacing="0">
            <thead>
              <tr>
                <td align="center">Cupom</td>
                <td align="center" width="100">Data de Validade</td>
                <td align="center" width="100">Tipo</td>
                <td align="center" width="100">Quantidade Utilizações</td>
                <td align="center" width="100">Pizzaria</td>
              </tr>
            </thead>
            <tbody>
            
            <?

            $arr_pedidos = array();
            while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
              if($objBuscaRegistros->num_utilizacoes > 0) {
                echo '<tr>';
                
                echo '<td align="center" align="left" style="padding-left: 10px;"><a href="javascript:;" onclick="trocar(\''.$objBuscaRegistros->cod_cupons.'\',\''.$objBuscaRegistros->piz_code.'\',\''.$situacao.'\')">'.bd2texto($objBuscaRegistros->cupom).'</a><br/>('.$objBuscaRegistros->obs_cupom.') </td>';//\''.'result_'.$objBuscaRegistros->cod_cupons.'_'.$objBuscaRegistros->piz_code.'\')">'.bd2texto($objBuscaRegistros->cupom).'</a><br/>('.$objBuscaRegistros->obs_cupom.'
                echo '<td align="center">'.bd2data($objBuscaRegistros->data_validade).'</td>';
                echo '<td align="center">'.bd2texto($objBuscaRegistros->produto).'</td>';

                echo '<td align="center">'.bd2texto($objBuscaRegistros->num_utilizacoes).'</td>';
                echo '<td align="center">'.bd2texto($objBuscaRegistros->nome).'</td>';
                
                echo '</tr>';
                

                //$SqlBuscaPedidos = "SELECT pc.cod_pedidos,p.data_hora_pedido,c.nome,c.cod_clientes FROM ipi_pedidos_ipi_cupons pc INNER JOIN ipi_pedidos p ON (pc.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.cod_pizzarias = ".$objBuscaRegistros->piz_code." AND p.cod_pizzarias IN (".$cod_pizzarias.") AND cod_cupons = ".$objBuscaRegistros->cod_cupons." $filtro_situacao $filtro_clientes ORDER BY pc.cod_pedidos";
                //$resBuscaPedidos = mysql_query($SqlBuscaPedidos);
                
                //echo $SqlBuscaPedidos;
                
                echo '<tr style="display: none;" id="result_'.$objBuscaRegistros->cod_cupons.'_'.$objBuscaRegistros->piz_code.'">';
                echo '<td colspan="5" align="left" style="padding: 20px;">';
                  echo '<input type="hidden" id="controle_'.$objBuscaRegistros->cod_cupons.'_'.$objBuscaRegistros->piz_code.'" value="0"/>';
                echo '<table class="listaEdicao" cellpadding="0" cellspacing="0">';
                echo '<thead>';
                echo '<tr><td align="center">Pedido</td><td align="center">Data e Hora do Pedido</td><td align="center">Cliente</td></tr>';
                echo '</thead>';
                echo '<tbody id="tbody_'.$objBuscaRegistros->cod_cupons.'_'.$objBuscaRegistros->piz_code.'">';
                
                
                /*while($objBuscaPedidos = mysql_fetch_object($resBuscaPedidos)) {
                  echo '<tr>';
                  echo '<td align="center"><a href="ipi_rel_historico_pedidos.php?p='.$objBuscaPedidos->cod_pedidos.'">'.sprintf('%08d', $objBuscaPedidos->cod_pedidos).'</a></td>';
                  echo '<td align="center">'.bd2datahora($objBuscaPedidos->data_hora_pedido).'</td>';
                  echo '<td align="center"><a href="ipi_clientes_franquia.php?cc='.$objBuscaPedidos->cod_clientes.'">'.bd2texto($objBuscaPedidos->nome).'</a></td>';
                  echo '</tr>';
                  $arr_pedidos[] = $objBuscaPedidos->cod_pedidos;
                }*/
                echo '</tbody>';
                echo '</table>';
                
                echo '</td>';
                echo '</tr>';
              }
            }
            //echo "<br/><br/><br/>i: (".implode(",",$arr_pedidos).")";
            
            desconectabd($con);
            
            ?>
            
            </tbody>
          </table>
        
        </form>
      <?
      }
      else
      {
        echo "digite um cupom";
      }
      ?>
    <? endif; ?>
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
    <!-- <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="#">Atalho 1</a></li>
        </ul>
      </div>
    </td> -->
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Listar -->
    
 </div>

<? rodape(); ?>
