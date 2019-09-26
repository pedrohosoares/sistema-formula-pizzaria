
<?php

/**
 * Tela para enviar email para teste de compatibilidade.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       15/08/2012   FILIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Teste de Compatibilidade');


$acao = validaVarPost("acao");
$quant_pagina = 50;
$campo_filtro_padrao = 'email_enviar';

switch ($acao) {
	case 'enviar_email':
    $email_origem = EMAIL_PRINCIPAL;
    //require_once '../../config.php';
    require_once '../../ipi_email.php';
		$con = conectar_bd();
		$email = validaVarPost('text_email');
		$reclamacao = validaVarPost('text_rec');
		$sql_procura_cliente = "select * from ipi_clientes where email= '$email'";
		$res_procura_cliente = mysql_query($sql_procura_cliente);
		$obj_procura_clientes = mysql_fetch_object($res_procura_cliente);
		$sql_inserir_dados = "insert into ipi_clientes_informacao (email_enviar,cod_clientes,tipo_log,problema_reclamado, data_envio) values('".$email."','".$obj_procura_clientes->cod_clientes."','COMPATIBILIDADE','".$text_rec."', NOW())";
		$res_inserir_dados = mysql_query($sql_inserir_dados);
		if($res_inserir_dados)
		{
			$cod_informacao = mysql_insert_id();
			$checksum = base64_encode($cod_informacao);
      
      $email_destino = $email;
      $assunto = NOME_SITE . " - Email para teste de Compatibilidade!";
      //$texto = 
      $texto .= "Olá,<br/><br/>Conforme seu comentario do problema abaixo:<br/><br/>Seu comentario: '".$reclamacao."'<br/><br/> Estamos enviando este email para fazer um teste de compatibilidade entre seu computador e o sistema de pedidos online.<br/><br/>Para continuar clique no link abaixo: <br/><a href=\"http://".HOST."/compatibilidade&checksum=".$checksum."\">http://".HOST."/compatiblidade&checksum=".$checksum."</a>";
                      
      //echo "<br>email: ".$texto;
      
      if(!enviar_email($email_origem, $email_destino, $assunto, $texto,array('cod_pedidos' =>'0','cod_usuarios' => '0','cod_clientes' => '0','cod_pizzarias'=> '0', 'tipo'=>'COMPATIBILIDADE'), 'neutro'))
        mensagemErro('Erro ao ENVIAR email', 'Por favor, reverifique o email: '.$email.'.');
      else        
        mensagemOk('E-mail enviado com êxito!');
		}
		else
		{
			echo 'Erro ao enviar email para'.$email;
			echo '<scipt>alert("erro ao enviar email")</script>';

		}

		break;
}
 //echo base64_encode(5);
?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />
<script type="text/javascript" src="../lib/js/calendario.js"></script>

<script>
function editar(cod) 
{
    var form = new Element('form', 
    {
        'action': '<?
        echo $_SERVER['PHP_SELF']?>',
        'method': 'post'
    });
  
    var input = new Element('input', 
    {
        'type': 'hidden',
        'name': 'cod_informacao',
        'value': cod
    });
  
    var input2 = new Element('input', 
    {
        'type': 'hidden',
        'name': 'acao',
        'value': 'detalhes'
    });
  
    input.inject(form);
    input2.inject(form);
    $(document.body).adopt(form);
  
    form.submit();
}

window.addEvent('domready', function()
{
    var tabs = new Tabs('tabs'); 
    <?
    if ($acao == '')
    {
        echo 'tabs.irpara(0);';
    }
    elseif ($acao == 'detalhes')
    {
        echo 'tabs.irpara(2);';
    }
    ?>
  
    tabs.addEvent('change', function(indice)
    { });
});
</script>

<div id="tabs">
<div class="menuTab">
<ul>
    <li><a href="javascript:;">Lista</a></li>
    <li><a href="javascript:;">Enviar email de teste</a></li>
    <li><a href="javascript:;">Detalhes</a></li>
</ul>
</div>

<div class="painelTab">
<?
  $pagina = (validar_var_post('pagina', '/[0-9]+/')) ? validar_var_post('pagina', '/[0-9]+/') : 0;
  $opcoes = (validar_var_post('opcoes')) ? validar_var_post('opcoes') : $campo_filtro_padrao;
  $filtro = validar_var_post('filtro');
?>

<form name="frmFiltro" method="post">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
    <tr>
        <td class="legenda tdbl tdbt" align="right"><select
        name="opcoes">
        <option value="<? echo $campo_filtro_padrao ?>"
            <?
            if ($opcoes == $campo_filtro_padrao)
            {
                echo 'selected';
            }
            ?>>E-mail</option>
    </select></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbt tdbr"><input type="text"
        name="filtro" size="60" value="<?
        echo $filtro?>"></td>
    </tr>

    <tr>
        <td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Filtrar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="buscar">

</form>

  <br>

  <?
  
  $conexao = conectar_bd();
  
  $sql_buscar_registros = "SELECT * FROM ipi_clientes_informacao ici LEFT JOIN ipi_clientes ic ON (ic.cod_clientes = ici.cod_clientes) WHERE ici.$opcoes LIKE '%$filtro%' AND tipo_log != 'PEDIDOS_LOG'";
  
  $res_buscar_registros = mysql_query($sql_buscar_registros);
  $num_buscar_registros = mysql_num_rows($res_buscar_registros);
  
  $sql_buscar_registros .= ' ORDER BY ic.nome LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
  $res_buscar_registros = mysql_query($sql_buscar_registros);
  $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
  
  //echo $sql_buscar_registros;

  echo "<center><b>" . $num_buscar_registros . " registro(s) encontrado(s)</center></b><br>";
  
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
      echo '<input type="hidden" name="data_inicial" value="' . $data_inicial . '">';
      echo '<input type="hidden" name="data_final" value="' . $data_final . '">';
      
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

  <form name="frmExcluir" method="post" onsubmit="return verificar_checkbox(this)">

  <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0"
      width="<?
      echo LARGURA_PADRAO?>">
      <tr>
          <!--<td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>-->
      </tr>
  </table>

  <table class="listaEdicao" cellpadding="0" cellspacing="0"
      width="<?
      echo LARGURA_PADRAO?>">
      <thead>
          <tr>
              <!--<td align="center" width="20"><input type="checkbox"
                  onclick="marcaTodos('marcar');"></td>-->
              <td align="center">Email</td>
              <td align="center">Nome</td>
              <td align="center">Reclamação</td>
              <td align="center">Respondido</td>
          </tr>
      </thead>
      <tbody>
    
      <?
      
      while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
      {
          echo '<tr>';
          
          //echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
          if($obj_buscar_registros->data_resposta)
          {
            echo '<td align="center" width="30%"><a href="javascript:;" onclick="editar('.$obj_buscar_registros->cod_informacao.')">' . bd2texto($obj_buscar_registros->email_enviar) . '</a></td>';
          }
          else
          {
            echo '<td align="center" width="30%">' . bd2texto($obj_buscar_registros->email_enviar) . '</td>';
          }
          if($obj_buscar_registros->nome)
          {
            echo '<td align="center" width="30%">' . bd2texto($obj_buscar_registros->nome) . '</td>';
          }
          else
          {
            echo '<td align="center" width="30%">Não é cliente</td>';
          }
          echo '<td align="center" width="30%">' . bd2texto($obj_buscar_registros->problema_reclamado) . '</td>';
          echo '<td align="center" width="10%">' . ($obj_buscar_registros->data_resposta ? '<img src="../lib/img/principal/ok.gif">' : '<img src="../lib/img/principal/erro.gif">') . '</td>';
          
          echo '</tr>';
      }
      
      desconectar_bd($conexao);
      
      ?>
    
      </tbody>
  </table>  
</div>


<!-- Tab Editar --> <!-- Tab Incluir -->
<div class="painelTab">
  <div style='padding: 10px 15px; margin: 0 auto;' align='center'>
    <form action='' method='post' name='frmEnviar'>
    <label for='text_email' name='Email'>Email: </label><br/>
    <input type='text' size='50' name='text_email'/><br/>
    <label for='text_rec' name='Reclamacao'>Reclamação do cliente: </label><br/>
    <textarea type='text' cols='50' rows='6' name='text_rec'></textarea><br/>
    <input type='hidden' name='acao' value='enviar_email'/><br/>
    <input type='submit' class='botao' name='btnEnviar' value='Enviar'/>
    </form>
  </div>
</div>

<!-- Tab Incluir --> <!-- Tab Detalhes -->
  <div class="painelTab">

<?
  $cod_informacao = validar_var_post("cod_informacao", '/[0-9]+/');
  if($cod_informacao):
  $obj_detalhes_compatibilidade = executar_busca_simples("SELECT * FROM ipi_clientes_informacao ici LEFT JOIN ipi_clientes ic ON (ic.cod_clientes = ici.cod_clientes) WHERE cod_informacao = '$cod_informacao'");
?>

    <div style='padding: 10px 15px; margin: 0 auto;' align='center'>
      <table class="listaEdicao" cellpadding="0" cellspacing="0" width="<? echo LARGURA_PADRAO?>">
        <thead>
          <tr>
            <td align='center' colspan='2'>
              Informações do cliente
            </td>
          </tr>
        </thead> 
        <tbody>
          <? if($obj_detalhes_compatibilidade->cod_clientes): ?>
          <tr>
            <td width='10%' style='font-weight: bold;' align='right'>
              Nome:
            </td>
            <td>
              <? echo $obj_detalhes_compatibilidade->nome ?>
            </td>
          </tr> 
          <tr>
            <td style='font-weight: bold;' align='right'>
              E-mail:
            </td>
            <td>
              <? echo $obj_detalhes_compatibilidade->email ?>
            </td>
          </tr> 
          <tr>
            <td style='font-weight: bold;' align='right'>
              Telefone:
            </td>
            <td>
              <? echo $obj_detalhes_compatibilidade->celular ?>
            </td>
          </tr> 
        <? else: ?>
          <tr>
            <td colspan='2' style='font-weight: bold;' align='right'>
              Não é cliente.
            </td>
          </tr> 
          <tr>
            <td style='font-weight: bold;' align='right'>
              E-mail:
            </td>
            <td>
              <? echo $obj_detalhes_compatibilidade->email_enviar ?>
            </td>
          </tr> 
        <? endif; ?>
        </tbody>      
        <thead>
          <tr>
            <td align='center' colspan='2'>
              Informações do computador
            </td>
          </tr>
        </thead> 
        <tbody>
          <tr>
            <td width='10%' style='font-weight: bold;' align='right'>
              Sistema operacional:
            </td>
            <td>
              <? echo $obj_detalhes_compatibilidade->nome_plataforma ?>
            </td>
          </tr> 
          <tr>
            <td style='font-weight: bold;' align='right'>
              Navegador:
            </td>
            <td>
              <? echo $obj_detalhes_compatibilidade->nome_navegador.' - v.'.$obj_detalhes_compatibilidade->versao_navegador ?>
            </td>
          </tr> 
          <tr>
            <td style='font-weight: bold;' align='right'>
              Idioma:
            </td>
            <td>
              <? echo $obj_detalhes_compatibilidade->idioma ?>
            </td>
          </tr> 
          <tr>
            <td style='font-weight: bold;' align='right'>
              User-agent:
            </td>
            <td>
              <? echo $obj_detalhes_compatibilidade->user_agent ?>
            </td>
          </tr> 
          <tr>
            <td style='font-weight: bold;' align='right'>
              Informação extra:
            </td>
            <td>
              <? echo $obj_detalhes_compatibilidade->informacao_extra ?>
            </td>
          </tr> 
          <tr>
            <td style='font-weight: bold;' align='right'>
              Sessão:
            </td>
            <td>
              <? echo $obj_detalhes_compatibilidade->sessao ?>
            </td>
          </tr> 
          </tr>  
        </tbody>      
      </table>
    </div>
  <? else: ?>
    <div align='center'> Nenhuma informação disponível </div>
  <? endif; ?>  
  </div>
</div>
<?php rodape(); ?>
