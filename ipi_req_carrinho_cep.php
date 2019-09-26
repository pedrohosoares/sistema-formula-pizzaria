<?
session_start();
require_once 'sys/lib/php/formulario.php';
require_once 'bd.php';

if (($cep_visitante)||($_SESSION['ipi_cliente']['autenticado'] == true)) 
{
 
  $con = conectabd();
  $contagem = 0;

  $arr_cod_pizzarias = array();

  if($cep_visitante)
  {
    //echo "<br />1";
    $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
    $objCep = executaBuscaSimples("SELECT COUNT(*) AS contagem FROM ipi_cep c inner join ipi_pizzarias p on p.cod_pizzarias = c.cod_pizzarias WHERE c.cep_inicial <= $cep_limpo AND c.cep_final >= $cep_limpo AND p.situacao !='INATIVO'", $con);
    $contagem = $objCep->contagem; 

    $sql_cod_pizzarias = "SELECT c.cod_pizzarias FROM ipi_cep c inner join ipi_pizzarias p on p.cod_pizzarias = c.cod_pizzarias WHERE c.cep_inicial <= $cep_limpo AND c.cep_final >= $cep_limpo AND p.situacao !='INATIVO'";
    $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
    while($obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias))
    {
      $arr_cod_pizzarias[]['cod_pizzarias'] = $obj_cod_pizzarias->cod_pizzarias;
    }
  }


  if($_SESSION['ipi_cliente']['autenticado'] == true)
  {
    //echo "<br />2";
    $SqlEnderecos = 'SELECT * FROM ipi_enderecos WHERE cod_clientes="'.$_SESSION['ipi_cliente']['codigo'].'"';
    $resEnderecos = mysql_query ($SqlEnderecos);
    while($objEnderecos = mysql_fetch_object($resEnderecos))
    {
      $cep_limpo = str_replace ( "-", "", str_replace('.', '', $objEnderecos->cep));
      $objCep = executaBuscaSimples("SELECT COUNT(*) AS contagem FROM ipi_cep c inner join ipi_pizzarias p on p.cod_pizzarias = c.cod_pizzarias WHERE c.cep_inicial <= $cep_limpo AND c.cep_final >= $cep_limpo AND p.situacao !='INATIVO'", $con);
      $contagem += $objCep->contagem; 

      $sql_cod_pizzarias = "SELECT c.cod_pizzarias FROM ipi_cep c inner join ipi_pizzarias p on p.cod_pizzarias = c.cod_pizzarias WHERE c.cep_inicial <= $cep_limpo AND c.cep_final >= $cep_limpo AND p.situacao !='INATIVO'";
      $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
      $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
      $num_cod_pizzarias = mysql_num_rows($res_cod_pizzarias);
      $arr_cod_pizzarias[]['cod_pizzarias'] = $obj_cod_pizzarias->cod_pizzarias;

      if ((!isset($_SESSION['ipi_carrinho']['cep_visitante']))&&($num_cod_pizzarias>0))
      {
        $_SESSION['ipi_carrinho']['cep_visitante'] = $objEnderecos->cep;
        $_SESSION['ipi_carrinho']['cod_pizzarias'] = $obj_cod_pizzarias->cod_pizzarias;
      }
    }
  }
  require("pub_req_fuso_horario1.php");
  $contagem_abertas = 0;
  if (count($arr_cod_pizzarias))
  {
    $arr_dias_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab');
    $dia_semana_hoje = $arr_dias_semana[date('w')]; 
    for ($a=0; $a<count($arr_cod_pizzarias); $a++)
    {
      $sql_funcionamento = "SELECT * FROM ipi_pizzarias_funcionamento WHERE cod_pizzarias = '".$arr_cod_pizzarias[$a]['cod_pizzarias']."' AND dia_semana=".date('w')." and horario_final > CURTIME()";//FILIPE -> antes o dia_semana era 'Dom','Seg', etc... ;; (08/02/2013) agora é o numero mesmo
      //echo "<br/>1: ".$sql_funcionamento;
      $res_funcionamento = mysql_query($sql_funcionamento);
      $num_funcionamento = mysql_num_rows($res_funcionamento);
      if ($num_funcionamento>0)
      {
        $arr_cod_pizzarias[$a]['funcionamento'] = 'Aberta';
        $contagem_abertas++;
      }
      else
      {
        $arr_cod_pizzarias[$a]['funcionamento'] = 'Fechada';
      }
    }
  }
/*
  echo "<pre>";
  print_r($arr_cod_pizzarias);
  echo "</pre>";

  echo "<br>contagem: ".$contagem;
  echo "<br>contagem_abertas: ".$contagem_abertas;
*/
  $liberado_compra = 0;
  $logar_cep = 0;
  $fora_cobertura = 0;

  if( $contagem > 0 ) 
  {
    $exibir_lista_pizzarias = 0;
    if ($contagem_abertas)
    {
      $liberado_compra = 1;
    }
    else
    {
      ?>
		  <div class="pedido_minimo_caixa fonte22">
        Atenção<br /><br />
        Hoje nossas pizzarias estão fechadas!
        <br />Amanhã aguardamos seu pedido!
		  </div>
      <?
      $liberado_compra = 0;
    }
  }
  else
  {
    $liberado_compra = 1;
    $fora_cobertura = 1;
    if($_SESSION['ipi_cliente']['autenticado'] == true)
    {
      //echo "Seus Endereços estão fora da área de cobertura!";
    }
    else
    {
      //echo "CEP fora da nossa área de cobertura!";
      $logar_cep = 1;
    }
  }

  if ( (date("d")==24) && (date("m")==12) ) 
  {
    $liberado_compra = 0;
    ?>
		<div class="pedido_minimo_caixa fonte22">
      Atenção<br />
      <span class="fonte18">Hoje nossas pizzarias estão fechadas!
      <br />Amanhã aguardamos seu pedido!</span>
      <br /><br /><?php echo NOME_SITE; ?> te desejam um <br />Feliz Natal!
		</div>
    <?
  }

  if ( (date("d")==31) && (date("m")==12) )
  {
    $liberado_compra = 0;
    ?>
		<div class="pedido_minimo_caixa fonte22">
      Atenção<br />
      <span class="fonte18">Hoje nossas pizzarias estão fechadas!
      <br />Amanhã aguardamos seu pedido!</span>
      <br /><br /><?php echo NOME_SITE; ?> te desejam um <br />Feliz Ano Novo!
		</div>
    <?
  }


  if ($liberado_compra == 1)
  {
    ?>
    <script type="text/javascript">
	  function verificar_pizzaria(obj)
	  {
	    if (obj.value=="Balcão")
		  {
		    document.getElementById('qual_pizzaria').style.display="block";
        document.getElementById('qual_horario').style.display="none";
        document.getElementById('bt_continuar').style.display="none";

		  }
	    else if (obj.value=="Entrega")
		  {
		    document.getElementById('qual_pizzaria').style.display="none";
		    document.getElementById('qual_horario').style.display="block";
        document.getElementById('bt_continuar').style.display="block";
		  }
	    else
		  {
		    document.getElementById('qual_pizzaria').style.display="none";
		    document.getElementById('qual_horario').style.display="none";
        document.getElementById('bt_continuar').style.display="none";
		  }
	  }

	  function validar_entrega(frm)
	  {
		  if (frm.buscar_balcao.value=="")
		  {
			  alert("Responda a pergunta, se você gostaria de buscar no balcão!");
			  frm.buscar_balcao.focus();
			  return false
		  }

		  if (frm.buscar_balcao.value=="Balcão")
		  {
		    if (frm.cod_pizzarias.value=="")
			  {
			    alert("Selecione em qual pizzaria você irá retirar!");
			    frm.cod_pizzarias.focus();
			    return false
			  }
		  }

      /*if (frm.horario.value=="")
      {
        alert("Selecione qual horário que você irá retirar!");
        frm.horario.focus();
        return false
      }*/

		  return true;
	  }


    function liberar_enviar(obj)
    {
      if (obj.value!="def")
      {
        document.getElementById('bt_continuar').style.display="block";
      }
      else
      {
        document.getElementById('bt_continuar').style.display="none";
      }
    }
          
    function verificar_horario(obj)
    {

      if (obj.value!="")
      {
        document.getElementById('qual_horario').style.display="block";
        document.getElementById('bt_continuar').style.display="block";        
      }
      else
      {
        document.getElementById('qual_horario').style.display="none";
      }

        <?
        /*
          if((date('w')==1))
          {
            echo 'if(obj.value=="14")if(confirm("Hoje a nossa UNIDADE SJCAMPOS - AQUARIUS não está atendendo balcão,\nseu pedido deverá ser retirado na UNIDADE SJCAMPOS - VILA ADYANNA")){document.getElementById("bt_continuar").style.display="block";  }else{document.getElementById("bt_continuar").style.display="none";};';
          }
        */
        ?>
    }	
    </script>
    <div class="box_pedido">
    <div id="carrinho_opcoes_pedido" class="box_pizza">
    <div id="carrinho_opcoes_pedido_txt1" class="">VAMOS COMEÇAR PELA ENTREGA!</div>
    <!-- <a href="javascript: if(validar_entrega(document.frmRetirar))document.frmRetirar.submit();"> -->

    <form name="frmRetirar" method="post" action="<? echo $PHP_SELF; ?>" onSubmit="return validar_entrega(document.frmRetirar);">

      <? 
      if ($fora_cobertura == 0) 
      {
        ?>
        <br />
        Você gostaria de receber ou retirar seu pedido?
        <br />
        <select name="buscar_balcao" id="buscar_balcao" onchange="javascript:verificar_pizzaria(this);">
          <option value="">&nbsp;</option>
          <option value="Entrega">Receber</option>
          <option value="Balcão">Retirar na pizzaria</option>
        </select>
        <?
      }
      else
      {
        ?>
        <br />
        Seu endereço está fora da cobertura, mas você pode retirar no balcão!
        <br />
        <input type="hidden" name="buscar_balcao" value="Balcão" />
        <?
      }
      ?>

      <div id="qual_pizzaria" class="" style="display: <? echo ($fora_cobertura == 0) ? "none" : "block" ?>">
        <label class="campo">
        <br /><span>Retirar no balcão de qual pizzaria?</span>
  		    <select name="cod_pizzarias" id="cod_pizzarias" onchange="javascript:verificar_horario(this);">
  			    <option value="">Selecione</option>
  	        <?
                require 'pub_req_fuso_horario1.php';

                 // if (defined('HORARIO_INICIO_CENTRAL_PIZZARIA')){
                 //            $tempoInicio = HORARIO_INICIO_CENTRAL_PIZZARIA;
                 //        }
                 //        else{
                 //                $tempoInicio = '08:00:00'; //valor default
                 //        }

                        // if (defined('HORARIO_FIM_CENTRAL_PIZZARIA')){
                        //     $tempoFim = HORARIO_FIM_CENTRAL_PIZZARIA;
                        // }
                        // else{
                        //         $tempoFim = '17:00:00'; //valor default
                        // }


                 //        $dateTime = new DateTime($tempoInicio);
                 //        $dateTime2 = new DateTime($tempoFim);
                 // if (($dateTime->diff(new DateTime)->format('%R') == '+') && ($dateTime2->diff(new DateTime)->format('%R')== '-')) {


                // if (defined('CODIGO_PIZZARIA_CENTRAL')){
                // $codPizzarias = CODIGO_PIZZARIA_CENTRAL;
                // }
                // else{
                // $codPizzarias = 1; //valor default
                // }

              //       $SqlPizzarias = 'SELECT * FROM ipi_pizzarias WHERE situacao="ATIVO" AND cod_pizzarias = '.$codPizzarias.' ORDER BY nome';
              //       $resPizzarias = mysql_query ($SqlPizzarias);
              //       $objPizzarias = mysql_fetch_object($resPizzarias);
              //       echo "<option value='".$objPizzarias->cod_pizzarias."'>".$objPizzarias->nome."</option>"; 
              // }
              // else{
                                $SqlPizzarias = 'SELECT * FROM ipi_pizzarias WHERE situacao="ATIVO" ORDER BY nome';
                                $resPizzarias = mysql_query ($SqlPizzarias);
                                while($objPizzarias = mysql_fetch_object($resPizzarias)) 
                                {
                                  echo "<option value='".$objPizzarias->cod_pizzarias."'>".$objPizzarias->nome."</option>"; 
                                }
              // }


  			    ?>			
  		    </select>
        </label>
	   	</div>

	    	   
      <!-- AGENDA PEDIDOS BALCAO SE FOR DENTRO DO HORÁRIO 
      <div id="qual_horario" style="display: none">
        <br />
        Por volta de que horas deseja agendar?
        <br />
        <select name="horario" id="horario" onchange="javascript:liberar_enviar(this);">
          <option value="def">&nbsp;</option>
          <?if(date("H")>=18)
          {
            echo '<option value="N">Entregar no próximo horário</option>';
          }
          
          $hora_inicial = 18;
          $hora_final = 23;
          $minutos_adicionais = 46;
          $hora_habil = false;
          $data_hora_corte = mktime(date("H"),date("i") + $minutos_adicionais,date("s"), date("m"),date("d"),date("Y"));
          //echo $data_hora_corte;
          for($h = $hora_inicial; $h <= $hora_final; $h++)
          {
            if($h == $hora_inicial)
            {
              $m_inicial = 30;
            }
            else
            {
              $m_inicial = 0;
            }
            
            for($m = $m_inicial; $m < 60; $m += 15)
            {
              if(mktime($h,$m,00, date("m"),date("d"),date("Y")) > $data_hora_corte)
              {
                echo '<option value="'.sprintf('%02d', $h).':'.sprintf('%02d', $m).'">'.sprintf('%02d', $h).':'.sprintf('%02d', $m).'</option>';
                $hora_habil = true;
              }
            }
          }

          if(!$hora_habil) 
          {
            //echo '<option value="">Horário fora da capacidade de entrega da pizzaria</option>';
            echo '<option value="">Nenhum horário disponível para agendar</option>';
          }
          ?>
        </select>
      </div>-->
      
      <div id="qual_horario" style="display: none">
      </div>


      <div id="bt_continuar" style="display: none">
        <input style="margin-top:10px;" type="submit" value="Continuar" class="btn btn-secondary" />
      </div>
	     
	    <input type="hidden" name="cep_visitante" value="<? echo $cep_visitante; ?>" />
	    <input type="hidden" name="registrar_entrega" value="ok" />
	  </form>
    </div> 
    </div>

    <?
  }

}
else
{
  // TRAVA PARA NÃO FAZER PEDIDOS DIA 24/12 E 31/12 (PIZZARIA FICA FECHADA NESSES DIAS)
  if ( ( (date("d")==24) && (date("m")==12) ) || ( (date("d")==31) && (date("m")==12) ) )
  {

    if ( (date("d")==24) && (date("m")==12) ) 
    {
      $liberado_compra = 0;
      ?>
      <div class="pedido_minimo_caixa fonte22">
        Atenção<br />
        <span class="fonte18">Hoje nossas pizzarias estão fechadas!
        <br />Amanhã aguardamos seu pedido!</span>
        <br /><br /><?php echo NOME_SITE; ?> te desejam um <br />Feliz Natal!
      </div>
      <?
    }

    if ( (date("d")==31) && (date("m")==12) )
    {
      $liberado_compra = 0;
      ?>
      <div class="pedido_minimo_caixa fonte22">
        Atenção<br />
        <span class="fonte18">Hoje nossas pizzarias estão fechadas!
        <br />Amanhã aguardamos seu pedido!</span>
        <br /><br /><?php echo NOME_SITE; ?> te desejam um <br />Feliz Ano Novo!
      </div>
      <?
    }

  }
  else
  {
    ?>
    <script type="text/javascript" src="js/mascara.js"></script>
    <script type="text/javascript">
    function validar_cep(frm)  
    {
      if(frm.cep_visitante.value == '') 
      {
        alert("CEP obrigatório!");
        frm.cep_visitante.focus();
        return false;
      }

      if(frm.cep_visitante.value.length == 8) 
      {
        if (!(/\d{8}/.test(frm.cep_visitante.value)))
        {
          alert("CEP inválido1!");
          frm.cep_visitante.focus();
          return false;
        }
      }
      else if(frm.cep_visitante.value.length == 9) 
      {
        if (!(/\d{5}\-\d{3}/.test(frm.cep_visitante.value)))
        {
          alert("CEP inválido2!");
          frm.cep_visitante.focus();
          return false;
        }
      }
      else
      {
        alert("CEP inválido!");
        frm.cep_visitante.focus();
        return false;
      }
      return true;
    }
    </script>

    <div>
      <strong>Seja bem-vindo ao sistema de pedidos online! <br />
      Iremos verificar a disponibilidade da entrega no seu bairro: </strong>
    </div>

    <div class="box_pedido">
      <form method="post" action="<? echo $PHP_SELF; ?>" id="frmCEP" onsubmit="return validar_cep(this)">
      <div id="fundo_verificar_cep" class="box_pizza">

          <div>
            SAIBA SE ENTREGAMOS EM SUA CIDADE
            <p>
            
            <label for="cep_visitante"> Digite seu CEP:</label><br />
            <input type="text" name="cep_visitante" id="cep_visitante" onkeypress="return MascaraCEP(this, event);" title="Digite seu CEP para verificarmos a disponibilidade de entrega!" class="form_text1" value="" />&nbsp; <span class="aviso">* Ex.: 30840-470</span> &nbsp;
            <input type="submit" name="btVerificarCep" alt="Continuar e selecionar os sabores!" class="btn btn-secondary" value="Continuar" />
            
            </p>
          </div> 
      </div>
      </form>
    </div>
    <?
  }
}
?>
