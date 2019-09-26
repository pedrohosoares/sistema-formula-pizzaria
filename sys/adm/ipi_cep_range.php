<?php

/**
 * ipi_cep_range.php: Consulta de Range de Ceps
 * 
 * Índice: cod_cep, cod_cep_aprovacao
 * Tabela: ipi_cep, ipi_cep_aprovacao
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Consulta de CEP de Entrega');

$acao = validaVarPost('acao');

$tabela = 'ipi_cep';
$chave_primaria = 'cod_cep';
$quant_pagina = 120;

$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 
$cep = validaVarPost('cep');
?>

<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  
  <tr>
    <td class="tdbt tdbl sep"><label>CEP:</label></td>
    <td class="tdbt sep">&nbsp;</td>
    <td class="tdbt tdbr sep">
       <input type="text" name="cep" size="10" value="<? echo $filtro ?>" onkeypress="return MascaraCEP(this, event)">
    </td>
  </tr>
  
  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
</form>

<br>

<?
$con = conectabd();

$cep_limpo = str_replace ("-", "", str_replace('.', '', $cep));

$SqlBuscaRegistrosPizzarias = "SELECT c.*, p.nome FROM ipi_cep c INNER JOIN ipi_pizzarias p ON (c.cod_pizzarias = p.cod_pizzarias) WHERE c.cep_inicial <= '$cep_limpo' AND '$cep_limpo' <= c.cep_final ORDER BY c.bairro, c.rua, c.regiao, c.cidade, c.estado";
$resBuscaRegistrosPizzarias = mysql_query($SqlBuscaRegistrosPizzarias);

$SqlBuscaRegistrosAprovacao = "SELECT * FROM ipi_cep_aprovacao WHERE cep_inicial <= '$cep_limpo' AND '$cep_limpo' <= cep_final ORDER BY bairro, rua, regiao, cidade, estado";
$resBuscaRegistrosAprovacao = mysql_query($SqlBuscaRegistrosAprovacao);

?>

<br>

<table><tr>

<!-- Conteúdo -->
<td class="conteudo">

  <form name="frmExcluir" method="post">
    <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
      <tr>
        <td><b>CEPs Aprovados (<? echo $cep ?>)</b></td>
      </tr>
    </table>
  
    <table class="listaEdicao" cellpadding="0" cellspacing="0">
      <thead>
        <tr>
          <td align="center"><? echo ucfirst(TIPO_EMPRESA) ?></td>
          <td align="center">Rua</td>
          <td align="center">Bairro</td>
          <td align="center">Região</td>
          <td align="center">Cidade</td>
          <td align="center">Estado</td>
          <td align="center" width="80">CEP Inicial</td>
          <td align="center" width="80">CEP Final</td>
        </tr>
      </thead>
      <tbody>
      
      <?
      
      $con = conectabd();
      
      while ($objBuscaRegistrosPizzarias = mysql_fetch_object($resBuscaRegistrosPizzarias)) {
        echo '<tr>';
        
        $cep_inicial_inicio = substr($objBuscaRegistrosPizzarias->cep_inicial, 0, 5); 
        $cep_inicial_final = substr($objBuscaRegistrosPizzarias->cep_inicial, +5, 8);
        $cep_final_inicio = substr($objBuscaRegistrosPizzarias->cep_final, 0, 5); 
        $cep_final_final = substr($objBuscaRegistrosPizzarias->cep_final, +5, 8); 
        
        echo '<td align="center">'.bd2texto($objBuscaRegistrosPizzarias->nome).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistrosPizzarias->rua).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistrosPizzarias->bairro).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistrosPizzarias->regiao).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistrosPizzarias->cidade).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistrosPizzarias->estado).'</td>';
        echo '<td align="center">'.$cep_inicial_inicio .'-'.$cep_inicial_final.'</td>';
        echo '<td align="center">'.$cep_final_inicio .'-'.$cep_final_final.'</td>';
        
        echo '</tr>';
      }
      
      desconectabd($con);
      
      ?>
      
      </tbody>
    </table>
    
    <br><br><br>
    
    <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
      <tr>
        <td><b>CEPs para Aprovação (<? echo $cep ?>)</b></td>
      </tr>
    </table>
  
    <table class="listaEdicao" cellpadding="0" cellspacing="0">
      <thead>
        <tr>
          <td align="center">Rua</td>
          <td align="center">Bairro</td>
          <td align="center">Região</td>
          <td align="center">Cidade</td>
          <td align="center">Estado</td>
          <td align="center" width="80">CEP Inicial</td>
          <td align="center" width="80">CEP Final</td>
        </tr>
      </thead>
      <tbody>
      
      <?
      
      $con = conectabd();
      
      while ($objBuscaRegistrosAprovacao = mysql_fetch_object($resBuscaRegistrosAprovacao)) {
        echo '<tr>';
        
        $cep_inicial_inicio = substr($objBuscaRegistrosAprovacao->cep_inicial, 0, 5); 
        $cep_inicial_final = substr($objBuscaRegistrosAprovacao->cep_inicial, +5, 8);
        $cep_final_inicio = substr($objBuscaRegistrosAprovacao->cep_final, 0, 5); 
        $cep_final_final = substr($objBuscaRegistrosAprovacao->cep_final, +5, 8); 
        
        echo '<td align="center">'.bd2texto($objBuscaRegistrosAprovacao->rua).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistrosAprovacao->bairro).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistrosAprovacao->regiao).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistrosAprovacao->cidade).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistrosAprovacao->estado).'</td>';
        echo '<td align="center">'.$cep_inicial_inicio .'-'.$cep_inicial_final.'</td>';
        echo '<td align="center">'.$cep_final_inicio .'-'.$cep_final_final.'</td>';
        
        echo '</tr>';
      }
      
      desconectabd($con);
      
      ?>
      
      </tbody>
    </table>
  
    <input type="hidden" name="acao" value="desassociar">
  </form>

</td>
<!-- Conteúdo -->

<!-- Barra Lateral -->
<td class="lateral">
  <div class="blocoNavegacao">
    <ul>
      <li><a href="ipi_pizzaria.php"><? echo ucfirst(TIPO_EMPRESAS) ?></a></li>
      <li><a href="ipi_entregador.php">Entregadores</a></li>
    </ul>
  </div>
</td>
<!-- Barra Lateral -->

</tr></table>

<? rodape(); ?>
