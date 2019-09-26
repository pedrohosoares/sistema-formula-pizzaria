<?php
/**
 * ipi_caixa.php: Sitema de Caixa
 * 
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once 'ipi_caixa_classe.php';
$executa = validaVarGet('executa');
if($executa and $executa == 'refresh'){
    
  unset($_SESSION['ipi_caixa']);
  unset($_SESSION['refIfood']);
  unset($_SESSION['pIfood']);
  unset($_SESSION['pedido_ifood_json']);
}
$acao = validaVarPost('acao');
/*$hora_inicial = microtime(true);
echo "<br>Hora Inicial: ".date("d/m/Y H:i:s:u", $hora_inicial);
echo "<script>console.log('".date("d/m/Y H:i:s:u", $hora_inicial)."')</script>"; */
if(!$acao)
{
    $acao = validaVarGet('acao');
}

if(!isset($_SESSION['ipi_caixa']['entregac']))
{
    $_SESSION['ipi_caixa']['entregac'] = "Entrega";
}

if(!isset($_SESSION['ipi_caixa']['pizzaria_atual']))
{
    $_SESSION['ipi_caixa']['pizzaria_atual'] = $_SESSION['usuario']['cod_pizzarias'][0];
    $cod_pizzarias = $_SESSION['usuario']['cod_pizzarias'][0];
}



//if(!isset($_SESSION['usuario']['ddd_pizzaria']))
{
  $conexao = conectabd();

  $sql_buscar_dd = "SELECT telefone_1,cidade,estado from ipi_pizzarias where cod_pizzarias = '$cod_pizzarias'";
  $res_buscar_dd = mysql_query($sql_buscar_dd);
  $obj_buscar_dd = mysql_fetch_object($res_buscar_dd);

  if($obj_buscar_dd->telefone_1 != "")
  {
    $ddd = explode(")",$obj_buscar_dd->telefone_1);
    $_SESSION['usuario']['ddd_pizzaria'] = $ddd[0].")";
    $_SESSION['usuario']['cidade_pizzaria'] = $obj_buscar_dd->cidade;
    $_SESSION['usuario']['estado_pizzaria'] = $obj_buscar_dd->estado;
}
desconectabd($conexao);
}

if($acao=="comprar")
{

    $conexao = conectabd();
    
    $cod_combos = validaVarGet('cd');
    // print_r($_SESSION['ipi_caixa']['combo']);
    // die();
    if ( ($cod_combos!="") && (!$_SESSION['ipi_caixa']['combo']) )
    {


        if ($_SESSION['ipi_caixa']['id_combo_atual'])
        {
            $_SESSION['ipi_caixa']['id_combo_atual'] = $_SESSION['ipi_caixa']['id_combo_atual'] + 1;
        }
        else
        {
            $_SESSION['ipi_caixa']['id_combo_atual'] = 1;
        }
        
        if ( (!$_SESSION['ipi_caixa']['combo']['cod_combos']) && ($cod_combos) )
        {
            $_SESSION['ipi_caixa']['combo']['id_combo'] = $_SESSION['ipi_caixa']['id_combo_atual'];
            $_SESSION['ipi_caixa']['combo']['cod_combos'] = $cod_combos;
        }
        
        $sql_combos = "SELECT * FROM ipi_combos_produtos WHERE cod_combos=".$_SESSION['ipi_caixa']['combo']['cod_combos']." ORDER BY tipo";
        $res_combos = mysql_query( $sql_combos );
        $num_combos = mysql_num_rows( $res_combos );
        //echo "<br>1: ".$sql_combos;
        
        for ($a=0; $a<$num_combos; $a++)
        {
            $obj_combos = mysql_fetch_object( $res_combos );
            if ($obj_combos->tipo!="BORDA")
            {
                $arr_cod_conteudos = array();
                $sql_produtos_combos = "SELECT * FROM ipi_combos_produtos_bebidas WHERE cod_combos_produtos=".$obj_combos->cod_combos_produtos."";
                $res_produtos_combos = mysql_query( $sql_produtos_combos );
                $num_produtos_combos = mysql_num_rows( $res_produtos_combos );
                if ($num_produtos_combos>0)
                {
                    while ( $obj_produtos_combos = mysql_fetch_object( $res_produtos_combos ) )
                    {
                      $arr_cod_conteudos[] = $obj_produtos_combos->cod_bebidas_ipi_conteudos;
                  }
              }

              $_SESSION['ipi_caixa']['combo']['produtos'][$a]['cod_conteudos'] = implode(",", $arr_cod_conteudos);

              $_SESSION['ipi_caixa']['combo']['produtos'][$a]['cod_tamanhos']=$obj_combos->cod_tamanhos;
              $_SESSION['ipi_caixa']['combo']['produtos'][$a]['qualidade']=$obj_combos->qualidade;
              $_SESSION['ipi_caixa']['combo']['produtos'][$a]['preco']=$obj_combos->preco;
              $_SESSION['ipi_caixa']['combo']['produtos'][$a]['tipo']=$obj_combos->tipo;

              $arr_cod_pizzas = array();
              $sql_produtos_combos = "SELECT * FROM ipi_combos_produtos_pizzas WHERE cod_combos_produtos=".$obj_combos->cod_combos_produtos."";
              $res_produtos_combos = mysql_query( $sql_produtos_combos );
              $num_produtos_combos = mysql_num_rows( $res_produtos_combos );
              if ($num_produtos_combos>0)
              {
                while ( $obj_produtos_combos = mysql_fetch_object( $res_produtos_combos ) )
                {
                  if ($obj_produtos_combos->selecionar_produto == "POR_CODIGO")
                  {
                    $arr_cod_pizzas[] = $obj_produtos_combos->cod_pizzas;
                }
                elseif ($obj_produtos_combos->selecionar_produto == "PIZZA_SEMANA")
                {
                    $cod_tamanhos = $obj_combos->cod_tamanhos;

                        // PENSAR SE PRECISA USAR O TAMANHO
                        //$sql_verifica_pizza_semana="SELECT * FROM ipi_pizzas_ipi_tamanhos WHERE cod_pizzarias = '".$cod_pizzarias."' AND cod_tamanhos = '".$cod_tamanhos."' AND pizza_semana = 1";
                    $sql_verifica_pizza_semana = "SELECT * FROM ipi_pizzas_ipi_tamanhos WHERE cod_pizzarias = '".$cod_pizzarias."' AND pizza_semana = 1";
                        //echo "<Br>sql_verifica_se_tem_atual: ".$sql_verifica_pizza_semana;
                    $res_verifica_pizza_semana = mysql_query($sql_verifica_pizza_semana);
                    $num_verifica_pizza_semana = mysql_num_rows($res_verifica_pizza_semana);
                    if($num_verifica_pizza_semana>0)
                    {
                      while($obj_verifica_pizza_semana = mysql_fetch_object($res_verifica_pizza_semana))
                      {
                        $arr_cod_pizzas[] = $obj_verifica_pizza_semana->cod_pizzas;
                    }
                }

            }
        }
        /*
              echo "xxxx-xxxx<pre>";
              print_r ($arr_cod_pizzas);
              echo "</pre>";
        */
          }      

                $_SESSION['ipi_caixa']['combo']['produtos'][$a]['sabor'] = implode(",", $arr_cod_pizzas); // ESTE CLIENTE ESPECIFICA QUAIS SABORES NO CADASTRO
                //$_SESSION['ipi_caixa']['combo']['produtos'][$a]['sabor']=$obj_combos->sabor;

                $_SESSION['ipi_caixa']['combo']['produtos'][$a]['foi_pedido']='N';
            }
            else
            {
                if ($_SESSION['ipi_caixa']['combo']['qtde_bordas'])
                {
                    $_SESSION['ipi_caixa']['combo']['qtde_bordas'] = $_SESSION['ipi_caixa']['combo']['qtde_bordas'] + 1;
                }
                else
                {
                    $_SESSION['ipi_caixa']['combo']['qtde_bordas'] = 1;
                }
            }
        }
    }
    
    desconectabd($conexao);
    
}
elseif($acao=="converter_combo")
{

    $conexao = conectabd();

    $cod_tamanho_quadradinha = 4; // Variável presetada na mão com o cod_tamanhos para a quadradinha        
    $cod_tamanho_quadrada = 3; // Variável presetada na mão com o cod_tamanhos para a quadrada
    $cod_conteudos_lata = 2; // Variável presetada na mão com o cod_conteudos para a lata
    $cod_conteudos_2litros = 1; // Variável presetada na mão com o cod_conteudos para a 2 litros
    
    $cod_combos = validaVarPost('cod_combos');
    $numero_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
    if ($numero_pizzas > 0)
    {

        $cont_pizza_quadrada_doce = 0;
        $cont_pizza_quadrada_salgada = 0;
        $cont_pizza_quadradinha_doce = 0;
        $cont_pizza_quadradinha_salgada = 0;
        $cont_bordas = 0;
        
        for ($a = 0; $a < $numero_pizzas; $a++)
        {
          if ($cod_combos==1)
          {
            $pizza_quadrada_doce = 0;
            $pizza_quadrada_salgada = 0;
            $pizza_quadradinha_doce = 0;
            $pizza_quadradinha_salgada = 1;
            $bordas = 1;

            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] != "1") && ( (int)$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] == (int)$cod_tamanho_quadradinha) && ($pizza_quadradinha_salgada > $cont_pizza_quadradinha_salgada) )
            {
                $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos'] = $cod_combos; 
                $_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] = "1"; 
                $cont_pizza_quadradinha_salgada++;
            }
            
            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['borda_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['borda_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['borda_combo'] != "1") && ($bordas > $cont_bordas))
            {
                $_SESSION['ipi_caixa']['pedido'][$a]['borda_combo'] = "1";
                $cont_bordas++; 
            }
        }







        if ($cod_combos==2)
        {

            $pizza_quadrada_doce = 0;
            $pizza_quadrada_salgada = 1;
            $pizza_quadradinha_doce = 1;
            $pizza_quadradinha_salgada = 0;
            $bordas = 0;

            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] != "1") && ((int)$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] == (int)$cod_tamanho_quadradinha) && ($pizza_quadradinha_doce > $cont_pizza_quadradinha_doce)  )
            {
                $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos'] = $cod_combos; 
                $_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] = "1"; 
                $cont_pizza_quadradinha_doce++;
            }

            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] != "1") && ((int)$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] == (int)$cod_tamanho_quadrada)  && ($pizza_quadrada_salgada > $cont_pizza_quadrada_salgada) )
            {
                $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos'] = $cod_combos; 
                $_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] = "1"; 
                $cont_pizza_quadrada_salgada++;
            }

        }





        if ($cod_combos==3)
        {

            $pizza_quadrada_doce = 0;
            $pizza_quadrada_salgada = 4;
            $pizza_quadradinha_doce = 2;
            $pizza_quadradinha_salgada = 0;
            $bordas = 0;

            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] != "1") && ((int)$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] == (int)$cod_tamanho_quadradinha) && ($pizza_quadradinha_doce > $cont_pizza_quadradinha_doce)  )
            {
                $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos'] = $cod_combos; 
                $_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] = "1"; 
                $cont_pizza_quadradinha_doce++;
            }

            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] != "1") && ((int)$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] == (int)$cod_tamanho_quadrada)  && ($pizza_quadrada_salgada > $cont_pizza_quadrada_salgada) )
            {
                $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos'] = $cod_combos; 
                $_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] = "1"; 
                $cont_pizza_quadrada_salgada++;
            }

        }






        if ($cod_combos==4)
        {

            $pizza_quadrada_doce = 0;
            $pizza_quadrada_salgada = 2;
            $pizza_quadradinha_doce = 1;
            $pizza_quadradinha_salgada = 0;
            $bordas = 0;

            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] != "1") && ((int)$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] == (int)$cod_tamanho_quadradinha) && ($pizza_quadradinha_doce > $cont_pizza_quadradinha_doce)  )
            {
                $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos'] = $cod_combos; 
                $_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] = "1"; 
                $cont_pizza_quadradinha_doce++;
            }

            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] != "1") && ((int)$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] == (int)$cod_tamanho_quadrada)  && ($pizza_quadrada_salgada > $cont_pizza_quadrada_salgada) )
            {
                $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos'] = $cod_combos; 
                $_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] = "1"; 
                $cont_pizza_quadrada_salgada++;
            }

        }









    }



    $numero_bebidas = isset($_SESSION['ipi_caixa']['bebida']) ? count($_SESSION['ipi_caixa']['bebida']) : 0;
    if ($numero_bebidas > 0)
    {
        $cont_refri_lata = 0;
        $cont_refri_2litros = 0;

        for ($a = 0; $a < $numero_bebidas; $a++)
        {
            $sqlAux = "SELECT c.cod_conteudos FROM ipi_bebidas b INNER JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $_SESSION['ipi_caixa']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);

            if ($cod_combos==1)
            {
                $refri_lata = 1;
                $refri_2litros = 0;

                if ( ($objAux->cod_conteudos == $cod_conteudos_lata) && ($refri_lata > $cont_refri_lata) )
                {
                    $_SESSION['ipi_caixa']['bebida'][$a]['bebida_combo'] = '1';
                    $_SESSION['ipi_caixa']['bebida'][$a]['id_combo'] = $id_combo;
                    $_SESSION['ipi_caixa']['bebida'][$a]['cod_combos'] = $cod_combos;
                    $cont_refri_lata++;
                }
            }


            if ($cod_combos==2)
            {
                $refri_lata = 0;
                $refri_2litros = 1;

                if ( ($objAux->cod_conteudos == $cod_conteudos_2litros) && ($refri_2litros > $cont_refri_2litros) )
                {
                    $_SESSION['ipi_caixa']['bebida'][$a]['bebida_combo'] = '1';
                    $_SESSION['ipi_caixa']['bebida'][$a]['id_combo'] = $id_combo;
                    $_SESSION['ipi_caixa']['bebida'][$a]['cod_combos'] = $cod_combos;
                    $cont_refri_2litros++;
                }
            }




            if ($cod_combos==3)
            {
                $refri_lata = 0;
                $refri_2litros = 2;

                if ( ($objAux->cod_conteudos == $cod_conteudos_2litros) && ($refri_2litros > $cont_refri_2litros) )
                {
                    $_SESSION['ipi_caixa']['bebida'][$a]['bebida_combo'] = '1';
                    $_SESSION['ipi_caixa']['bebida'][$a]['id_combo'] = $id_combo;
                    $_SESSION['ipi_caixa']['bebida'][$a]['cod_combos'] = $cod_combos;
                    $cont_refri_2litros++;
                }
            }




            if ($cod_combos==4)
            {
                $refri_lata = 0;
                $refri_2litros = 1;

                if ( ($objAux->cod_conteudos == $cod_conteudos_2litros) && ($refri_2litros > $cont_refri_2litros) )
                {
                    $_SESSION['ipi_caixa']['bebida'][$a]['bebida_combo'] = '1';
                    $_SESSION['ipi_caixa']['bebida'][$a]['id_combo'] = $id_combo;
                    $_SESSION['ipi_caixa']['bebida'][$a]['cod_combos'] = $cod_combos;
                    $cont_refri_2litros++;
                }
            }



        }
    }


}

desconectabd($conexao);

}

$produto_combo = 0;
if ($_SESSION['ipi_caixa']['combo'])
{
    $indice_produto_atual_combo = -1;
    $num_opcoes = count($_SESSION['ipi_caixa']['combo']['produtos']);
    for ($a=0; $a<$num_opcoes; $a++)
    {
        if ($_SESSION['ipi_caixa']['combo']['produtos'][$a]['foi_pedido']=='N')
        {
            $indice_produto_atual_combo = $a;
            break;
        }
    }


    for ($a=0; $a<$num_opcoes; $a++)
    {
        if ($_SESSION['ipi_caixa']['combo']['produtos'][$a]['foi_pedido']=='N')
        {
            if ( ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']=='PIZZA') || ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']=='BEBIDA') )
            {
                $produto_combo = 1;
            }
        }
    }
    
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <title><? echo NOME_SITE; ?> - Caixa</title>

    <style>
        * {
         margin: 0px;
         padding: 0px;
     }

     body,a,td,input,textarea,select {
         font-family: Arial, sans-serif;
         font-size: 12px;
         color: #424242;
     }

     input:focus,textarea:focus,select:focus,label:focus {
        font-family: Arial, sans-serif;
        font-size: 12px;
        font-weight:bold;
        background-color: #F7F413;
    }

    body {
     background-color: #84471a;
 }

 label {
     font-weight: bold;
     color: #EF6B00;
 }

 select,input,textarea {
     border: 1px solid #FE7300;
     margin-top: 3px;
     margin-bottom: 3px;
     padding: 2px;
 }

 select {
     border-collapse: collapse;
 }

 hr {
     color: #FE7300;
     size: 1px;
 }

 #principal {
     width: 750px;
     margin: 20px auto;
     background-color: #FFFFFF;

 }

 #miolo {
     padding: 5px;
 }

 #comanda {
     border: 1px solid #777;
     width: 180px;
     margin-top: 13px;
 }

 #comanda h2 {
     background-color: #777;
     font-size: 13px;
     padding: 5px;
     text-align: center;
     text-transform: uppercase;
     color: #ffffff;
 }

 #conteudo_comanda {
     font-size: 10px;
     padding: 15px;
 }

 .msgboxerro{
    border: 1px solid #9F221C; 
    padding: 5px;
    background-color: #F0DBDA; 
    font-size: 11px; 
    font-weight: bold;
    font-family: tahoma,sans-serif;
    text-align: center;
}

.msgboxok {
    border: 1px solid #003399; 
    padding: 5px;
    background-color: #ECF5FF; 
    font-size: 11px; 
    font-weight: bold;
    font-family: tahoma,sans-serif;
    text-align: center;
}

</style>

<link type="text/css" rel="stylesheet" href="../../css/autocompleter.css">

<script type="text/javascript" src="../lib/js/mascara.js"></script>
<script type="text/javascript" src="../lib/js/mootools-1.2-core.js"></script>
<script type="text/javascript" src="../lib/js/tabs.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs.css" />

<script type="text/javascript" src="../../js/autocompleter.js"></script>
<script type="text/javascript" src="../../js/autocompleter.request.js"></script>
<script type="text/javascript" src="../../js/observer.js"></script>

<link  href="../lib/js/moodialog/css/MooDialog.css" rel="stylesheet" type="text/css" media="screen" />
<script src="../lib/js/moodialog/MooDialog.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/Overlay.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Fx.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Alert.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Request.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Confirm.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Prompt.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Error.js" type="text/javascript"></script>
<script type="text/javascript">
    var tabs;

    function formatar_moeda(objTextBox, SeparadorMilesimo, SeparadorDecimal, e)
    {
        var sep = 0;
        var key = '';
        var i = j = 0;
        var len = len2 = 0;
        var strCheck = '0123456789';
        var aux = aux2 = '';

        var whichCode = (e.which) ? e.which : e.keyCode;
    //var whichCode = (window.Event) ? e.which : e.keyCode;

    // 13=enter, 8=backspace, 9=tab as demais retornam 0(zero)
    // whichCode==0 faz com que seja possivel usar todas as teclas como delete, setas, etc    
    //alert(whichCode +', '+ objTextBox.name);
    if ((objTextBox.name == "desconto")&&(whichCode == 9)) //Anular a tecla Tab caso seja no campo desconto
    {
        if (!calcular_total_com_desconto())
            return false;
        else
            return true;
    }
    else
    {
        if ( (( (whichCode == 13) || (whichCode == 9) ||(whichCode == 0) || (whichCode == 8) )  ) )
            return true;
    }

    key = String.fromCharCode(whichCode); // Valor para o código da Chave

    if (strCheck.indexOf(key) == -1) 
        return false; // Chave inválida
    len = objTextBox.value.length;
    
    for(i = 0; i < len; i++)
        if ((objTextBox.value.charAt(i) != '0') && (objTextBox.value.charAt(i) != SeparadorDecimal)) 
            break;
        aux = '';
        for(; i < len; i++)
            if (strCheck.indexOf(objTextBox.value.charAt(i))!=-1) 
                aux += objTextBox.value.charAt(i);
            aux += key;
            len = aux.length;
            if (len == 0) 
                objTextBox.value = '';
            if (len == 1) 
                objTextBox.value = '0'+ SeparadorDecimal + '0' + aux;
            if (len == 2) 
                objTextBox.value = '0'+ SeparadorDecimal + aux;
            if (len > 2) {
                aux2 = '';
                for (j = 0, i = len - 3; i >= 0; i--) {
                    if (j == 3) {
                        aux2 += SeparadorMilesimo;
                        j = 0;
                    }
                    aux2 += aux.charAt(i);
                    j++;
                }
                objTextBox.value = '';
                len2 = aux2.length;
                for (i = len2 - 1; i >= 0; i--)
                    objTextBox.value += aux2.charAt(i);
                objTextBox.value += SeparadorDecimal + aux.substr(len - 2, len);
            }
            return false;
        }


        function validar_numeros(e)
        {
            if (document.all)
                var tecla = event.keyCode;
            else 
                if(document.layers)
                    var tecla = e.which;
                if ((tecla > 47 && tecla < 58))
                    return true;
                else
                {
                    if (tecla != 8)
                        event.keyCode = 0;
                    else
                        return true;
                }
            }

            function revisar_pedido()
            {
                var var_url='acao=revisar_pedido';
    //alert( var_url );
    new Request.HTML({
      url: 'ipi_caixa_ajax.php',
      method: 'post',
      update: $('revisao_pedido')
  }).send(var_url);
}

function atualizar_total_pedido()
{
    var var_url='acao=atualizar_total';
    //alert( var_url );
    new Request.JSON({
      url: 'ipi_caixa_ajax.php',
      method: 'post',
      onComplete: function(retorno) {
        $('txt_total_pedido').setProperty('value', retorno.total);
        $('txt_total_formas').setProperty('value', retorno.total);
        $('txt_valor_formas_1').setProperty('value', retorno.total);
        
        if(retorno.cod_clientes!="null")
            $('cod_clientes_fechar').setProperty('value', retorno.cod_clientes);
        
        if(retorno.tipo_cliente!="null")
            $('tipo_cliente_fechar').setProperty('value', retorno.tipo_cliente);
        
        calcular_total_com_desconto();
    }
}).send(var_url);
}

function verificar_pizzaria_existe()
{
    var validar = false;
    var var_url='acao=verificar_pizzaria_existe&bairro='+document.frmCadastro.bairro.value+"&cep="+document.frmCadastro.cep.value;
    //alert( var_url );
    new Request.JSON({
      url: 'ipi_caixa_ajax.php',
      method: 'post',
      async: false,
      onComplete: function(retorno) {
        if(retorno.cod_pizzarias!="n")
        {
            ajustar_pizzaria(retorno.cod_pizzarias);
            //alert("achou"+retorno.cod_pizzarias);
            validar = true;
        }
        else
        {

           // alert("nachou");
           validar = false;
       }
   }
}).send(var_url);
    return validar;
    //alert("afd");
}

function ccbox(eu,id)
{

  //if($(id)==null)
  //{
    id = document.getElementById(id);
    //eu = document.getElementById(eu);
  //}
  var tipo = id.get('type');
  //alert(tipo);
  //alert(eu.get('type'));
  if(id.get("type")=="checkbox" && eu.get('type')=="checkbox")
  {
    if(eu.get('checked')==true)
    {
      id.set("checked",false);
  } 
}
}

function tabela_frete()
{
    var opcoes = "method:'post'";//method:'post'  
    var variaveis = 'acao=exibir_tabela_frete';
    var reqDialog = new MooDialog.Request('ipi_caixa_ajax.php',variaveis,opcoes, {
        'class': 'MooDialog',
        autoOpen: false,
        title: "Tabela de Frete",
        size: {
           width: 500,
           height: 300
       }
   });
    // You want the request dialog instance to set the onRequest message, so you have to do it in two steps.
    reqDialog.setRequestOptions({
        onRequest: function(){
            reqDialog.setContent('Carregando...')
        }
    }).open();
}

function carregar_frete(bairro)
{
  if(bairro.length>=2)
  {
      var url = "acao=exibir_tabela_frete_bairros&tbairro="+bairro;
      new Request.HTML(
      {
          url: 'ipi_caixa_ajax.php',
          update: 'tabela_frete_div',
          method:'post'
      }).send(url);
  }
}
function carregar_ingredientes(num_fracao) 
{
    var var_url='acao=carregar_ingredientes&cod_pizzas='+document.getElementById('cod_pizzas_'+num_fracao).value+'&cod_tamanhos='+document.getElementById('cod_tamanhos').value+'&num_fracao='+num_fracao+'&num_sabores='+document.getElementById('num_sabores').value;
    //alert( var_url );
    new Request.HTML({
      url: 'ipi_caixa_ajax.php',
      method: 'post',
      update: 'ingredientes_'+num_fracao,
      onComplete: function() {
        //$('ingredientes'+num_sabor).setStyle('padding-top', 10);
        //$('ingredientes'+num_sabor).setStyle('background', 'none');
    }
}).send(var_url);
}

function carregar_adicionais(num_fracao) 
{
    var var_url='acao=carregar_adicionais&cod_tamanhos='+document.getElementById('cod_tamanhos').value+'&num_fracao='+num_fracao+'&num_sabores='+document.getElementById('num_sabores').value;
    //alert( var_url );
    new Request.HTML({
      url: 'ipi_caixa_ajax.php',
      method: 'post',
      update: 'adicionais_'+num_fracao,
      onComplete: function() {
        //$('ingredientes'+num_sabor).setStyle('padding-top', 10);
        //$('ingredientes'+num_sabor).setStyle('background', 'none');
    }
}).send(var_url);
}

function validar_numeros_enter(event)
{
    var tecla = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    if ((tecla > 47 && tecla < 58)||(tecla == 13))
        return true;
    else
    {
        if (tecla != 8)
            return 0;
        else
            return true;
    }
}


function definir_sabores()
{
    carregar_opcoes();
    qtde_sabores = parseInt(document.getElementById('num_sabores').value);
    for (a=1; a<=4; a++)
    {
        if(a>qtde_sabores)
        {
            obj = document.getElementById('cod_pizzas_'+a);
            obj.disabled = true;
            //obj.setAttribute('style','display: none');
            obj = document.getElementById('cod_pizzas_digito_'+a);
            obj.disabled = true;
            //obj.setAttribute('style','display: none');
            obj = document.getElementById('observacao_'+a);
            obj.disabled = true;
            //obj.setAttribute('style','display: none');
            obj = document.getElementById('ingredientes_'+a);
            obj.innerHTML = "";
            //obj.setAttribute('style','display: none');

            /*obj = document.getElementById('lbl_sabor_'+a);
            obj.setAttribute('style','display: none');
            obj = document.getElementById('lbl_obs_'+a);
            obj.setAttribute('style','display: none');*/

            
            obj = document.getElementById('sabor_'+a);
            obj.setAttribute('style','display: none');
        }
        else
        {
            obj = document.getElementById('cod_pizzas_'+a);
            obj.disabled = false;
            //obj.setAttribute('style','');
            obj = document.getElementById('cod_pizzas_digito_'+a);
            obj.disabled = false;
            //obj.setAttribute('style','');
            obj = document.getElementById('observacao_'+a);
            obj.disabled = false;
           // obj.setAttribute('style','');

            //obj = document.getElementById('lbl_sabor_'+a);
            //obj.setAttribute('style','');
            //obj = document.getElementById('lbl_obs_'+a);
            //obj.setAttribute('style','');

            obj = document.getElementById('sabor_'+a);
            obj.setAttribute('style','');
        }
    }

}


function proximo_campo(field, event, classe)
{
    var erro=-1;
    var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    // Bloqueando o LR do scanner
    // console.log(keyCode);
    //alert(keyCode);
    
    if ((keyCode == 13)||(keyCode == 9)) 
    {
        var i;
        
        for (i = 0; i < field.form.elements.length; i++)
        {
            if (field == field.form.elements[i])
            {
                break;
            }
        }
        i = (i + 1) % field.form.elements.length;
        
        if(i==1)
            window.scrollTo(0, 300);
        if(i==5)
            window.scrollTo(0, 600);
        
        if (field.form.elements[i].type == "select-one")
        {
            field.form.elements[i].value = field.value;
            
            //alert(field.form.elements[i].name);
            
            if (field.form.elements[i].name=="num_sabores")
            {
                //definir_sabores();
            }

            if (field.form.elements[i].name=="cod_pizzas_1")
            {
                //carregar_ingredientes('1');
                window.scrollTo(0, 800);
            }
            
            if (field.form.elements[i].name=="cod_pizzas_2")
            {
                //carregar_ingredientes('2');
                window.scrollTo(0, 1200);
            }
            if (field.form.elements[i].name=="cod_pizzas_3")
            {
                //carregar_ingredientes('3');
                window.scrollTo(0, 1600);
            }
            if (field.form.elements[i].name=="cod_pizzas_4")
            {
                //carregar_ingredientes('4');
                window.scrollTo(0, 1800);
            }
            
            erro=0;  // variavel para não mudar de foco caso código não exista
            if (field.form.elements[i].value == "")
            {
                //alert("Código inválido!!!");
                erro=1;
                field.form.elements[i].focus();
                field.form.elements[i].select();
            }
            
        }
        else if (field.form.elements[i].type=="checkbox")
        {
            if ((field.form.elements[i].name=="pizza_promocional")||(field.form.elements[i].name=="borda_promocional"))
            {
                field.form.elements[i+1].value = field.value;
                if (field.form.elements[i+1].value == "")
                {
                    //alert("Código inválido!");
                    erro=1;
                    field.form.elements[i].focus();
                    field.form.elements[i].select();
                }
            }
            else

            {
                var arr_ingredientes = document.getElementsByName(field.form.elements[i].name);
                //alert(field.form.elements[i-1].value + ' - ' + field.form.elements[i].value + ' - ' + field.form.elements[i+1].value + ' - ' + arr_ingredientes[0].value);
                total = arr_ingredientes.length;
                for (a=0; a<total; a++)
                {
                    if (arr_ingredientes[a].value==field.form.elements[i-1].value)
                    {
                        arr_ingredientes[a].checked = ! arr_ingredientes[a].checked;
                    }
                }
            }
        }

        if ((erro==0)||(classe = "proximo_cliente")||(classe = "proximo"))
        {
            var c=0;
            for (c = i; c < field.form.elements.length; c++)
            {
                if((field.form.elements[c].className == classe)&&(!field.form.elements[c].disabled))
                {
                    // selecionando o elemento
                    field.form.elements[c].focus();
                    field.form.elements[c].select();
                    
                    return false;
                }    
            }
        }
    }
    else
    {
        return true;
    }
}

function carregar_formas_pg(cod_pizzarias)
{
    var var_url;
    var_url='acao=carregar_formas_pagamento&codp='+cod_pizzarias<? if(validaVarPost('cod_formas_pg')) echo '&codfp='.validaVarPost('cod_formas_pg') ?>;
    //alert(var_url);
    new Request.HTML({
      url: 'ipi_caixa_ajax.php',
      method: 'post',
      update: 'forma_pg',
      async: false
  }).send(var_url);
    return true;
}
function carregarCombo(idCombo)
{
    var var_url;

    var_url='acao=carregarCombo&cod='+idCombo+'&tamanho_pizza='+document.getElementById('cod_tamanhos').value + '&sabor=<? echo $_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['sabor']; ?>';
    //alert(var_url);
    new Request.HTML({
      url: 'ipi_caixa_ajax.php',
      method: 'post',
      update: idCombo,
      async: false,
      onComplete: function(retorno)
      {
        if ($('num_sabores').getProperty('value')!='' && idCombo=='num_sabores')
        {
            //alert('acho');
            document.getElementById('num_sabores_digito').value=$('num_sabores').getProperty('value');
            definir_sabores();
        }
    }
}).send(var_url);
    return true;


}

function novo_pedido()
{
    var var_url;
    $('desconto').setProperty('value','0,00');
    calcular_total_com_desconto();
    var_url='acao=limpar_pedido';
    new Request.HTML({
      url: 'ipi_caixa_acoes.php',
      method: 'post',
      onComplete: atualizar_comanda
  }).send(var_url);
}

function atualizar_comanda()
{
    document.getElementById('frame_conteudo_comanda').src="ipi_caixa_listar.php";
    document.window.location='ipi_caixa.php';
}

function calcular_troco()
{
  var var_url='acao=calcular_troco&total_geral='+$('total_geral').getProperty('value')+'&troco='+$('troco').getProperty('value');
  new Request.JSON({
    method: "POST", 
    url: "ipi_caixa_ajax.php",
    //data: var_url,
    onComplete: function(data)
    {
      if (data.ok=="S")
      {
        $('troco_cliente').setProperty('value', data.troco);
    }
}
}).send(var_url);
}

function definir_cliente(cod_cliente, cod_endereco)
{
    var var_url;
    var_url='acao=definir_cliente&cod='+cod_cliente+'&cod_enderecos='+cod_endereco;
    new Request.JSON({
        url: 'ipi_caixa_ajax.php',
        method: 'post',
        onComplete: function(retorno)
        {

            if (retorno.situacao=="ATIVO")
            {
                $('alerta_situacao_cliente').style.display='none';
            }
            else
            {
                $('alerta_situacao_cliente').style.display='block';
            }

            $('situacao_cliente').setProperty('value', retorno.situacao);
            $('nome').setProperty('value', retorno.nome);
            $('cod_onde_conheceu').setProperty('value', retorno.cod_onde_conheceu);
            $('email').setProperty('value', retorno.email);
            $('cpf').setProperty('value', retorno.cpf);
            $('nascimento').setProperty('value', retorno.nascimento);
            //$('celular').setProperty('value', retorno.celular);
            $('telefone_1').setProperty('value', retorno.telefone_1);
            $('telefone_2').setProperty('value', retorno.telefone_2);
            $('cep').setProperty('value', retorno.cep);
            $('endereco').setProperty('value', retorno.endereco);
            $('numero').setProperty('value', retorno.numero);
            $('complemento').setProperty('value', retorno.complemento);
            $('edificio').setProperty('value', retorno.edificio);
            $('bairro').setProperty('value', retorno.bairro);
            $('cidade').setProperty('value', retorno.cidade);
            $('estado').setProperty('value', retorno.estado);
            $('sexo').setProperty('value', retorno.sexo);
            $('cod_clientes').setProperty('value', retorno.cod_clientes);
            $('cod_enderecos').setProperty('value', retorno.cod_enderecos);
            $('bt_cadastrar').setProperty('value', 'Confirmar');
            $('tipo_cliente').setProperty('value', 'ANTIGO');
            $('obs_cliente').setProperty('value', retorno.observacao);
            $('ref_cliente').setProperty('value', retorno.referencia_cliente);
            $('ref_endereco').setProperty('value', retorno.referencia_endereco);
            if($('tipo_entrega_editar').value=="Entrega")
            {
                $('cod_pizzarias_editar').setProperty('value', retorno.cod_pizzarias);
                ajustar_pizzaria(retorno.cod_pizzarias);
            }
            ocultar_exibir('inserir_cliente')
            //atualizar_comanda(); tabs.irpara(1); 
        }
    }).send(var_url);
}

function limpar_formulario_cadastro()
{
    $('alerta_situacao_cliente').style.display='none';
    $('situacao_cliente').setProperty('value', 'ATIVO');
    $('nome').setProperty('value', '');
    $('cod_onde_conheceu').setProperty('value', '');
    $('email').setProperty('value', '');
    //$('cpf').setProperty('value', '');
    $('nascimento').setProperty('value', '');
    //$('celular').setProperty('value', '<? echo $_SESSION['usuario']['ddd_pizzaria'] ?>');
    $('telefone_1').setProperty('value', '<? echo $_SESSION['usuario']['ddd_pizzaria'] ?>');
    $('telefone_2').setProperty('value', '<? echo $_SESSION['usuario']['ddd_pizzaria'] ?>');
    $('cep').setProperty('value', '');
    $('endereco').setProperty('value', '');
    $('numero').setProperty('value', '');
    $('complemento').setProperty('value', '');
    $('edificio').setProperty('value', '');
    $('bairro').setProperty('value', '');
    $('cidade').setProperty('value', '<? echo $_SESSION['usuario']['cidade_pizzaria'] ?>');
    $('estado').setProperty('value', '<? echo $_SESSION['usuario']['estado_pizzaria'] ?>');
    $('cod_clientes').setProperty('value', '');
    $('cod_enderecos').setProperty('value', '');
    $('bt_cadastrar').setProperty('value', 'Confirmar');
    $('tipo_cliente').setProperty('value', 'NOVO');
    $('obs_cliente').setProperty('value', '');
    $('ref_cliente').setProperty('value', '');
    $('ref_endereco').setProperty('value', '');
    $('revisao_pedido').setProperty('value', 'Carregando pedido, aguarde...');
}


function ocultar_exibir(acao)
{
    if (acao=="inserir_cliente")
    {
        $('div_inserir_cliente').setStyle('display','block');
        $('div_buscar_cliente').setStyle('display','none');
        $('nome').focus();
    }
    else if (acao=="buscar_cliente")
    {
        $('div_inserir_cliente').setStyle('display','none');
        $('div_buscar_cliente').setStyle('display','block');
        $('buscar_cliente').focus();
    }
    
}

function carregar_opcoes()
{
  // if (carregarCombo('cod_tipo_massa'))//
  {
    if (carregarCombo('cod_opcoes_corte'))
    {
      if (carregarCombo('cod_bordas'))
      {
        if (true)//carregarCombo('cod_adicionais')
        {
          if (carregarCombo('cod_pizzas_1'))
          {
            if (carregarCombo('cod_pizzas_2'))
            {
              if (carregarCombo('cod_pizzas_3'))
              {
                if(carregarCombo('cod_pizzas_4'))
                {
                    if($('cod_opcoes_corte').getProperty('value')!='')
                    {
                        document.getElementById('cod_opcoes_corte_digito').value=$('cod_opcoes_corte').getProperty('value');
                    }
                }
            }
        }
    }
}
}
}
}
}


function tamanho_pizza(obj)
{
  $('cod_tamanhos').setProperty('value', obj.value);
  if ($('cod_tamanhos').getProperty('value')!='')
  {
    carregarCombo('num_sabores');
}
}




function calcular_total_com_desconto()
{
    var var_total_com_desconto = 0;
    var var_total_desconto = 0;
    var var_total_pedido = 0;
    
    var_total_pedido = $('txt_total_pedido').getProperty('value');
    var_total_desconto = $('desconto').getProperty('value');
    if (var_total_pedido=="")
        var_total_pedido = 0;

    if(var_total_desconto=="")
        var_total_desconto = 0;

    if (parseFloat(var_total_pedido.toString().replace(',','.')) < parseFloat(var_total_desconto.toString().replace(',','.')) )
    {
        alert('ATENÇÃO, valor do desconto (R$ ' + $('desconto').getProperty('value') + ') maior do que o valor do pedido!');
        $('desconto').setProperty('value','0,00');
        calcular_total_com_frete();
        $('desconto').focus();
        return(false);
    }
    
    var_total_com_desconto = parseFloat(var_total_pedido.toString().replace(',','.')) - parseFloat(var_total_desconto.toString().replace(',','.'));
    
    $('total_com_desconto').setProperty('value', var_total_com_desconto.toFixed(2).toString().replace('.',','));

    calcular_total_com_frete();

    return(true);
    
}

function calcular_total_com_frete()
{
    var var_total_com_desconto = 0;
    var frete = 0;
    var var_total_pedido = 0;
    
    var_total_pedido = $('total_com_desconto').getProperty('value');
    frete = $('frete').getProperty('value');

    if (var_total_pedido=="")
        var_total_pedido = 0;

    if(frete=="")
        frete = 0;   
    var_total_com_desconto = parseFloat(var_total_pedido.toString().replace(',','.')) + parseFloat(frete.toString().replace(',','.'));
    
    $('total_geral').setProperty('value', var_total_com_desconto.toFixed(2).toString().replace('.',','));
    $('txt_valor_formas_1').setProperty('value', var_total_com_desconto.toFixed(2).toString().replace('.',','));

    calcular_total_das_formas_pgto();

    return(true);
    
}

function ajustar_frete_tipo_entrega(entrega)
{
    var frete_padrao = '<? echo bd2moeda($_SESSION['ipi_caixa']['cliente']['preco_frete']); ?>';
    <? 
        /*$n_pizza = 0;
        $n_acai = 0;
        $numero_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
        for($i = 0;$i< $numero_pizzas;$i++)
        {
            echo "var lanche_$i = '".$_SESSION['ipi_caixa']['pedido'][$i]['fracao']['0']['cod_pizzas']."';";
            if($_SESSION['ipi_caixa']['pedido'][$i]['fracao']['0']['cod_pizzas']!='57')
            {
                $n_pizza ++;
            }else
            {
                $n_acai ++;
            }
        }
        
        $desconto = $n_pizza*1 + $n_acai*0.5;
        $desconto = bd2moeda($desconto);*/
        ?>

        if (frete_padrao=="")
            frete_padrao = 0;

        if(entrega=="Entrega")
        {
            $('frete').setProperty('value', frete_padrao);
        }
        else if(entrega!="")
        {
            $('frete').setProperty('value', '0,00');
        }else
        {
            $('frete').setProperty('value', frete_padrao);
            $('desconto').setProperty('value', '0,00');
        }
        calcular_total_com_desconto();
    }

    function atalhos(event)
    {
        if (event.key == '0' && event.control) 
        {
            limpar_formulario_pizza(document.frm_pedido_pizzas);
            limpar_formulario_cadastro();        
            novo_pedido();
            document.getElementById('telemarketing').innerHTML = '';

        }

        if (event.key == '1' && event.control) 
        {
            tabs.irpara(0);
        } 
        if (event.key == '2' && event.control) 
        {
            tabs.irpara(1);
        } 
        if (event.key == '3' && event.control) 
        {
            tabs.irpara(2);
        } 
        if (event.key == '4' && event.control) 
        {
            tabs.irpara(3);
            $('quantidades0').focus();
        } 
        if (event.key == '5' && event.control) 
        {
            tabs.irpara(4);
        //revisar_pedido();
    } 
    if (event.key == '6' && event.control) 
    {
        tabs.irpara(5);
        atualizar_total_pedido();
        $('cod_pizzarias').focus();
        
    } 
    if (event.key == '7' && event.control) 
    {
        ocultar_exibir('inserir_cliente');
        limpar_formulario_cadastro();        
        $('nome').focus();
        
    } 
    if (event.key == '8' && event.control) 
    {
        ocultar_exibir('buscar_cliente');
        $('buscar_cliente').focus();
    } 
}

function buscar_clientes()
{
    var var_url;
    $('resultado_busca_cliente').setProperty('value','Buscando... aguarde!');
    var_url='acao=buscar_clientes&busca_cliente='+$('buscar_cliente').getProperty('value');
    new Request.HTML({
      url: 'ipi_caixa_ajax.php',
      method: 'post',
      update: $('resultado_busca_cliente')
  }).send(var_url);
}

function validacao_pizza(frm)
{
    if (frm.cod_tamanhos.value=="")
    {
        alert("Tamanho da pizza não foi selecionado!");
        frm.cod_tamanhos.focus();
        return false;
    }
    <?
    if ($produto_combo!=1)
    {
        ?>
        if (frm.pizza_promocional.checked==true)
        {
            if (frm.cod_motivo_promocoes_pizza.value=="")
            {
                alert("Motivo da Pizza Promocional não foi selecionado!");
                frm.cod_motivo_promocoes_pizza.focus();
                return false;
            }
        }
        <?
    }
    ?>
    
    if (frm.num_sabores.value=="")
    {
        alert("Número de Sabores não foi selecionado!");
        frm.num_sabores.focus();
        return false;
    }
    // if (frm.cod_tipo_massa.value=="")
    // {
    //     alert("Tipo de Massa não foi selecionado!");
    //     frm.cod_tipo_massa.focus();
    //     return false;
    // }
    if (frm.cod_opcoes_corte.value=="")
    {
        alert("O Corte não foi selecionado!");
        frm.cod_opcoes_corte.focus();
        return false;
    }

    if (frm.cod_bordas.value=="")
    {
        alert("Borda não foi selecionado!");
        frm.cod_bordas.focus();
        return false;
    }
    <?
/*    if ($produto_combo!=1)
    {
    ?>
    if (frm.borda_promocional.checked==true)
    {
        if (frm.cod_motivo_promocoes_borda.value=="")
        {
            alert("Motivo da Borda Promocional não foi selecionado!");
            frm.cod_motivo_promocoes_borda.focus();
            return false;
        }
    }
    <?
}*/
?>

/*    if (frm.cod_adicionais.value=="")
    {
        alert("Gergelim não foi selecionado!");
        frm.cod_adicionais.focus();
        return false;
    }*/
    var int_num_sabores;
    int_num_sabores = parseInt(frm.num_sabores.value);
    for (a=1; a<=int_num_sabores; a++)
    {
        campo1 = "cod_pizzas_"+a;    
        if (document.getElementById(campo1).value=="")
        {
            alert("O "+a+"º sabor não foi selecionado!");
            document.getElementById(campo1).focus();
            return false;
        }
        campo2 = "cod_ingredientes_"+a+"_digito";    
        if (document.getElementById(campo2)==null)
        {
            alert("Os ingredientes do "+a+"º sabor não foram carregados! Selecione o sabor novamente!");
            document.getElementById(campo1).focus();
            return false;
        }


    }
    
    return true;
}


function ajustar_tipo_entrega(entrega)
{
    $('tipo_entrega_editar').value = entrega;
    $('tipo_entrega_buscar').value = entrega;

    if(entrega=="Balcão")
    {
        $('cod_pizzarias_editar').setStyle('display','block');
        $('label_editar_pizzaria').setStyle('display','block');
        $('div_buscar_pizzaria').setStyle('display','block');
    }
    else
    {
        $('cod_pizzarias_editar').setStyle('display','none');
        $('label_editar_pizzaria').setStyle('display','none');
        $('div_buscar_pizzaria').setStyle('display','none');
    }
}

function ajustar_pizzaria(cod_pizzarias)
{
    $('cod_pizzarias_editar').value = cod_pizzarias;
    $('cod_pizzarias_buscar').value = cod_pizzarias;
}

function validar_cadastro(form) 
{

    if(form.email.value != '') 
    {
        if(!validarEmail(form.email.value)) 
        {
            alert('O campo e-mail não é válido ou não foi digitado corretamente.');
            form.email.focus();
            return false;
        }
    }

    if(form.nome.value == '') 
    {
        alert('Campo nome obrigatório.');
        form.nome.focus();
        return false;
    }

/*      if(form.cpf.value != '') 
      {
          if(!ValidarCPF(form.cpf.value)) {
            alert('O campo CPF não é válido ou não foi digitado corretamente.');
            return false;
          }
      }*/
      
/*      if(form.celular.value != '' && !(/^\(([1-9][1-9])\)$/.test(form.celular.value))) {
        if(!validarTelefone(form.celular.value)) {
          alert('O campo celular não é válido ou não foi digitado corretamente - (xx) xxxx-xxxx.');
          form.celular.focus();
          return false;
        }
      }
      */
      if(form.telefone_1.value == '' && !(/^\(([1-9][1-9])\)$/.test(form.telefone_1.value))) {
        alert('Campo telefone local 1 obrigatório.');
        form.telefone_1.focus();
        return false;
    }

    if(!validarTelefone(form.telefone_1.value) ) {
        alert('O campo telefone local 1 não é válido ou não foi digitado corretamente - (xx) xxxx-xxxx.');
        form.telefone_1.focus();
        return false;
    }

    if(form.telefone_2.value != ''  && !(/^\(([1-9][1-9])\)$/.test(form.telefone_2.value))) {
        if(!validarTelefone(form.telefone_2.value)) {
          alert('O campo telefone local 2 não é válido ou não foi digitado corretamente.');
          form.telefone_2.focus();
          return false;
      }
  }




  if(form.tipo_entrega.value=="")
  {
      alert("Selecione um tipo de Entrega!");
      form.tipo_entrega.focus();
      return(false);
  }

  if(form.tipo_entrega.value=="Entrega")
  {
            //alert(verificar_pizzaria_existe());
            if(verificar_pizzaria_existe()==false)
            {
                alert("Endereço fora da área de cobertura");
                form.bairro.focus();
                return(false);
            }
        }
        
        if(form.cod_pizzarias.value=="")
        {
          alert("Endereço fora da área de cobertura!");
          form.cod_pizzarias.focus();
          return(false);
      }
      /*
      if(form.cep.value == '') {
        alert('Campo CEP obrigatório.');
        form.cep.focus();
        return false;
      }
      
      if(form.endereco.value == '') {
        alert('Campo endereço obrigatório.');
        form.endereco.focus();
        return false;
      }
      
      if(form.numero.value == '') {
        alert('Campo número obrigatório.');
        form.numero.focus();
        return false;
      }
      
      if(form.bairro.value == '') {
        alert('Campo bairro obrigatório.');
        form.bairro.focus();
        return false;
      }
      
      if(form.cidade.value == '') {
        alert('Campo cidade obrigatório.');
        form.cidade.focus();
        return false;
      }
      
      if(form.estado.value == '') {
        alert('Campo estado obrigatório.');
        form.estado.focus();
        return false;
      }
      */
      
      if(form.cod_onde_conheceu.value == '') {
        alert('O Campo "Onde Conheceu" é obrigatório!');
        form.cod_onde_conheceu.focus();
        return false;
    }
    return true;
}

function completar_endereco() {
  var cep = document.frmCadastro.cep.value;
  var url = 'acao=completar_cep&cep=' + cep;
  
  if(cep != '') {
    new Request.JSON({url: 'ipi_caixa_ajax.php', onComplete: function(retorno) {
      if(retorno.status == 'OK') {

        document.frmCadastro.endereco.value = retorno.endereco;
        document.frmCadastro.bairro.value = retorno.bairro;
        document.frmCadastro.cidade.value = retorno.cidade;
        document.frmCadastro.estado.value = retorno.estado;
        document.frmCadastro.numero.focus();
    }
    else {
        alert('Erro ao completar CEP: ' + retorno.mensagem);
    }
}}).send(url); 
}
else {
    alert('Para completar o endereço o campo CEP deverá ter um valor válido.');
}
}


function init()
{
    new Autocompleter.Request.JSON('bairro', '../../ipi_auto_completar.php', { 'postVar': 'bairro', postData:{ acao:'bairro',pizzaria:'<? echo $_SESSION['ipi_caixa']['pizzaria_atual'] ?>'} } );
    new Autocompleter.Request.JSON('cidade', '../../ipi_auto_completar.php', { 'postVar': 'cidade', postData:{ acao:'cidade',pizzaria:'<? echo $_SESSION['ipi_caixa']['pizzaria_atual'] ?>'} } );

    tabs = new Tabs('tabs'); 
    tabs.addEvent('change', function(indice)
    {
        if(indice == 0) 
        {
            limpar_formulario_cadastro();
            ocultar_exibir("buscar_cliente");
            $('buscar_cliente').focus();
        }
        if(indice == 1) 
        {
            $('cod_tamanhos_digito').focus();
        }
        if (indice == 3) //3
        {
            $('quantidades0').focus();
        } 
        if (indice == 4) //4
        {
            revisar_pedido();
        } 
        if (indice == 5) //5
        {
            atualizar_total_pedido();
            $('cod_pizzarias').focus();
        } 
    });
    tabs.irpara(0);
    <? if(validaVarPost("redirecionar")!="")
    {
        echo "tabs.irpara(".validaVarPost("redirecionar").");";
    }
    ?>
    $('bt_buscar_cliente').addEvent('click', buscar_clientes);
    
    //Fazer uma melhoria para identificar o navegador
    window.addEvent('keydown', atalhos); //registrar de atalhos a função no Firefox
    $('pagina_inteira').addEvent('keydown', atalhos);  //registrar de atalhos a função no Internet Explorer

    
}

window.addEvent('domready', init);
<? if(!isset($_SESSION['ipi_caixa']['pizzaria_atual'])): ?>
    window.addEvent('domready', carregar_formas_pg(<? echo $_SESSION['ipi_caixa']['pizzaria_atual'] ?>));
<? endif; ?>
function baixar_telemarketing(cod_tel)
{
    var var_url;
    var_url='acao=baixar_telemarketing&cod='+cod_tel;
    new Request.HTML({
      url: 'ipi_caixa_ajax.php',
      method: 'post',
      onComplete: function() { $("telemarketing_"+cod_tel).destroy(); }
  }).send(var_url);
}


</script>


</head>
<body id="pagina_inteira">

    <div id="principal">
        <div id="cabecalho"><img src="../../img/cab_mascara.jpg"></div>
        <p style="padding-left: 25px;">0 - Limpar Carrinho</p>
        <?php 
        function filterGet($i){
            return preg_replace('/[^[:alnum:]_]/', '',$i);
        }
        function verificaSessaoTeste(){
          $result = false;
          $session = $_SESSION['usuario']['cod_pizzarias'];
          foreach($session as $i=>$v){
            if($v == 24){
              $result =  true;
          }
      }
      return $result;
  }
  $sessaoTeste = verificaSessaoTeste();
  if(true){
    if(
        (isset($_GET['ref_ifood']) and isset($_GET['p'])) 
        or 
        (isset($_SESSION['refIfood']) and isset($_SESSION['pIfood']))
    ){
        if((isset($_GET['ref_ifood']) and isset($_GET['p']))){
            $ref = filterGet($_GET['ref_ifood']);
            $p = filterGet($_GET['p']);
            $_SESSION['refIfood'] = $ref;
            $_SESSION['pIfood'] = $p;
        }
        $conexaoPedido = conectabd();
        $pedi = mysql_query("SELECT * FROM ipi_pedidos INNER JOIN ipi_clientes ON (ipi_pedidos.cod_clientes = ipi_clientes.cod_clientes) INNER JOIN ipi_enderecos ON (ipi_clientes.cod_clientes = ipi_enderecos.cod_clientes) WHERE ipi_pedidos.cod_pedidos='".$_SESSION['pIfood']."'");
        $numPedi = mysql_num_rows($pedi);
        if($numPedi >0){
            $pedi = mysql_fetch_object($pedi);
            $_SESSION['refIfood'] = $pedi->ifood_polling; 
            $nascimento = explode('-', $pedi->nascimento);
            $nascimento = $nascimento[2].'/'.$nascimento[1].'/'.$nascimento[0];
            $_SESSION['pedido_ifood_json'] = $pedi->pedido_ifood_json;
            $_SESSION['ipi_caixa']['tipo_cliente'] = 'ANTIGO';
            $_SESSION['ipi_caixa']['cliente'] = array(
                'cod_clientes'=>$pedi->cod_clientes,
                'cod_enderecos'=>$pedi->cod_enderecos,
                'confirmado'=>'1',
                'nome'=>$pedi->nome,
                'cod_onde_conheceu'=>$pedi->cod_onde_conheceu,
                'email'=>$pedi->email,
                'cpf'=>$pedi->cpf,
                'sexo'=>$pedi->sexo,
                'nascimento'=>$nascimento,
                'celular'=>$pedi->celular,
                'telefone_1'=>$pedi->telefone_1,
                'telefone_2'=>$pedi->telefone_2,
                'cep'=>$pedi->cep,
                'endereco'=>$pedi->endereco,
                'numero'=>$pedi->numero,
                'complemento'=>$pedi->complemento,
                'edificio'=>$pedi->edificio,
                'bairro'=>$pedi->bairro,
                'cidade'=>$pedi->cidade,
                'estado'=>$pedi->estado,
                'obs_cliente'=>$pedi->obs_cliente,
                'ref_endereco'=>$pedi->referencia_endereco,
                'ref_cliente'=>$pedi->referencia_cliente
            );
            #echo "<pre>";
            #var_dump($_SESSION['ipi_caixa']['cliente']);
            #echo "</pre>";
            $_SESSION['codClienteIfood'] = isset($pedi->cod_clientes)?$pedi->cod_clientes:"";
            desconectabd($conexaoPedido);
            echo '<div class="caixarepedido" style="width: 89%;display: block;margin-left: 26px;margin-top: 10px;background: yellow;padding: 16px;padding-left: 0px;border: 3px dotted red;font-size: 18px;font-weight: 600;font-family: sans-serif;">RE-CADASTRAMENTO DE PEDIDOS DO IFOOD';
            echo '<br />';
            echo 'N° PEDIDO: '.$_SESSION['pIfood'];
            echo '<br />REF IFOOD: '.$_SESSION['refIfood'];
            echo '</div>';
        }
    }
}
?>
<div id="miolo">






    <?



    if ($acao == "fechar_pedido")
    {

    //require_once 'ipi_caixa_classe.php';

    $caixa = new ipi_caixa();    
    $obs_pedido = validaVarPost('obs_pedido');
    $cod_pizzarias = validaVarPost('cod_pizzarias');//$_SESSION['ipi_caixa']['pizzaria_atual'];
    $cpf_nota_fiscal = validaVarPost('cpf_nota_fiscal');
    $forma_pg = validaVarPost('forma_pg');
    $txt_valor_formas = validaVarPost('txt_valor_formas');
    $tipo_entrega = $_SESSION['ipi_caixa']['entregac'];//validaVarPost('tipo_entrega');
    $desconto = validaVarPost('desconto');
    $horario_agendamento = validaVarPost('horario_agendamento');
    $troco = validaVarPost('troco');
    $frete = validaVarPost('frete');
    $comissao_frete = (validaVarPost('comissao_frete')!="" ? validaVarPost('comissao_frete')  : 0 );
    if ($caixa->existe_pedido())
    {   
        $num_pedido = $caixa->finalizar_pedido($cod_pizzarias, $obs_pedido, $cpf_nota_fiscal, $forma_pg, $txt_valor_formas, $tipo_entrega, $horario_agendamento, $troco, $desconto,moeda2bd($frete),moeda2bd($comissao_frete));
        $caixa->apagar_pedido();
        echo "<script>alert('Pedido: ".sprintf("%08d", $num_pedido)." efetuado com sucesso!')</script>";

    }
} 




//echo '<div id="mensagem_direta" style="display: none">';
echo '<div id="mensagem_direta" class="msgboxerro" style="display: none">';
echo '<div style="text-align: center;"><u>MENSAGEM DIRETA</u></div>';
echo '<br /><div id="texto_mensagem_direta"></div>';
echo '<br /><input type="button" name="bt_mensagem_direta" value="Mensagem Lida" onClick="javascript:document.getElementById(\'mensagem_direta\').style.display=\'none\'">';
echo '</div><br />';

$caixa = new ipi_caixa();    
$retorno_sugestao_combo = $caixa->sugerir_combo();
if ($retorno_sugestao_combo!=false)
{
    echo '<div id="sugestao_combo" class="msgboxok">';
    echo '<form name="frm_converter_combo" method="post">';
    echo '<div style="text-align: center;"><u>SUGESTÃO DE COMBO</u></div>';
    echo '<br /><div id="texto_sugestao_combo">'.$retorno_sugestao_combo.'</div>';
    echo '<br /><input type="button" name="bt_sugestao_combo" value="Converter em Combo" onClick="javascript:document.frm_converter_combo.submit()">';
    echo '<input type="hidden" name="acao" value="converter_combo">';
    echo '</form>';
    echo '</div><br />';
}

$conexao = conectabd();
echo '<div id="telemarketing">';
if ($_SESSION['ipi_caixa']['cliente']['cod_clientes'])
{
    $sqlAux = "SELECT * FROM ipi_telemarketing_ativo WHERE "; 
    if (count($_SESSION['usuario']['cod_pizzarias'])>0)
        $sqlAux .= " cod_pizzarias IN (".implode(",", $_SESSION['usuario']['cod_pizzarias']).") AND ";
    $sqlAux .= "situacao='ATIVO' AND ( (data_inicial_prog = '0000-00-00' AND data_final_prog = '0000-00-00') OR (data_inicial_prog IS NULL AND data_final_prog IS NULL) OR (data_inicial_prog < '".date("Y-m-d")." 00:00:00' AND data_final_prog > '".date("Y-m-d")." 23:59:59')) AND cod_telemarketing_ativo NOT IN (SELECT cod_telemarketing_ativo FROM ipi_telemarketing_ativo_respostas WHERE cod_clientes='".$_SESSION['ipi_caixa']['cliente']['cod_clientes']."')";
    //echo "<br>".$sqlAux;
    $resAux = mysql_query($sqlAux);
    $numAux = mysql_num_rows($resAux);
    if ($numAux>0)
    {
        echo "<div style='border: 1px solid #F37300; padding: 5px;'>";
        echo "<div style='color: #F37300; text-align: center; font-weight: bold; height:20px;'>Oferecer as seguintes promoções para este cliente:</div>";
        while ($objAux = mysql_fetch_object($resAux))
        {
            echo "<div id='telemarketing_".$objAux->cod_telemarketing_ativo."'><hr noshade='noshade' size='1'>";
            echo "<table width='650' border='0' cellpadding='10' cellspacing='0'>"; 
            echo "<tr height='28'>"; 
            echo "<td width='80' align='center'>"; 
            if ($objAux->mensagem_obrigatoria==0) 
            {    
                echo "<input type='button' value='Já ofereci' name='bt_baixa' onclick='javascript:baixar_telemarketing(".$objAux->cod_telemarketing_ativo.")' >";
            }
            else 
            {
                echo "<span style='font-weight: bold; color: #AAAAAA;'>Sempre<br />Oferecer</span>";
            }
            echo "</td>"; 
            echo "<td width='570'>"; 
            echo "&nbsp;&nbsp;".$objAux->mensagem;
            echo "</td>"; 
            echo "</table>"; 
            echo "</div>";
        }
        echo "</div>";
    }
    
}
echo '</div>';

?>

<script type="text/javascript">
    function verificar_mensagem()
    {

        var url = 'acao=mensagem_direta';
        new Request.JSON({url: 'ipi_caixa_ajax.php', onComplete: function(retorno) 
        {
            if(retorno.status == 'OK') 
            {

                document.getElementById('texto_mensagem_direta').innerHTML=retorno.mensagem;
                document.getElementById('mensagem_direta').style.display='block';
        /*

        //alert(retorno.mensagem);
        var iebody = (document.compatMode && document.compatMode != "BackCompat") ? document.documentElement : document.body;
        var dsoctop = document.all? iebody.scrollTop : pageYOffset;
        
        var divFundo = new Element('div', {
        'styles': {
          'position': 'absolute',
          'top': 0,
          'left': 0,
          'height': document.documentElement.clientHeight + dsoctop,
          'width':  document.documentElement.clientWidth,
          //'background': "transparent url('sys/lib/img/principal/fundo-preto-trans.png') scroll repeat top left",
          'z-index': 99,
          'background-color': '#000'
        }
        });
        
        var divMsg = new Element('div', {
        'styles': {
          'position': 'absolute',
          //'top': 50,
          'border' : '4px solid #FE7300',
          'left': (document.body.clientWidth - 400) / 2,
          'background-color': '#ffffff',
          'width' : 400,
          'height': 380,
          'padding': 20,
          'z-index': 9999,
          'overflow': 'hidden'
        }
        });
        
        var win = window;
        var mensagem = "";
        var middle = win.getScrollTop() + (win.getHeight() / 2);
        var top = Math.max(0, middle - (400 / 2));
                  
        divMsg.setStyle('position', 'absolute');
        divMsg.setStyle('top', top);
        
        divMsg.onclick = function() {
            divMsg.destroy();
            divFundo.destroy();
        }
               
        mensagem = "<center><b>MENSAGEM DIRETA</b></center><br /><br />";
        mensagem += (retorno.mensagem);
        mensagem += "<br /><br /><center><input type='button' value='Ok' name='bt_ok'></center>";
        divMsg.innerHTML = mensagem;
        
        divFundo.setStyle('opacity', 0.5);
        
        $(document.body).adopt(divMsg);
        $(document.body).adopt(divFundo);
        */
    }
}}).send(url);

    }
    window.setInterval(verificar_mensagem, 10000);
</script>




<table border="0" cellspacing="0" cellpadding="0" width="740">
	<tr>
		<td valign="top" style="padding-right: 10px;" width="580">





          <div id="tabs">

              <div class="menuTab">
                <ul>
                 <li><a href="javascript:;">1 - Cliente</a></li>
                 <li><a href="javascript:;">2 - Pizza</a></li>
                 <li><a href="javascript:;">3 - Combo</a></li>
                 <li><a href="javascript:;">4 - Bebida</a></li>
                 <li><a href="javascript:;">5 - Revisar</a></li>
                 <li><a href="javascript:;">6 - Fechar</a></li>
             </ul>
         </div>


         <!-- Tab Clientes -->
         <div class="painelTab">

          <div id="div_inserir_cliente" style="display: none">
            <a href="javascript:ocultar_exibir('buscar_cliente')">8 - Buscar Cliente</a>&nbsp;&nbsp;<a href="javascript:tabela_frete();">Tabela de Frete</a>
            <br><br>


            <!-- Inicio Cadastro --> 
            <form name="frmCadastro" method="post" action="ipi_caixa_acoes.php">
              <table cellpadding="1" cellspacing="0" align="center">

                  <tr>
                    <td align="left"> <b>Tipo de Entrega:</b> </td>
                </tr>
                <tr>
                    <td>  

                      <select name="tipo_entrega" id="tipo_entrega_editar" onchange="ajustar_tipo_entrega(this.value)" style="width: 200px;">
                        <option value=""></option>
                        <option value="Balcão">Balcão</option>
                        <option value="Entrega">Entregar</option>
                    </select>     
                    <?php
/*
          <select name="tipo_entrega" id="tipo_entrega_editar" onchange="ajustar_tipo_entrega(this.value)" style="width: 200px;">
            <option value=""></option>
            <option value="Balcão" <? if($_SESSION['ipi_caixa']['entregac'] == "Balcão") echo "selected='selected'" ?> >Balcão</option>
            <option value="Entrega" <? if($_SESSION['ipi_caixa']['entregac'] == "Entrega") echo "selected='selected'" ?>>Entregar</option>
          </select>     
*/
          ?>      
      </td>
  </tr>
            <!-- <tr>
                <td>&nbsp;</td>
            </tr> -->

            <tr>
                <td align="left" id="label_editar_pizzaria" style='display:none'> <b>Pizzaria:</b> </td>
            </tr>
            <tr>
                <td> 
                  <select name="cod_pizzarias" id="cod_pizzarias_editar" onchange='ajustar_pizzaria(this.value)'style="width: 200px;display:none">
                    <option value="">Selecione a Pizzaria</option>
                    <?

                    $SqlBuscaPizzarias = "SELECT cod_pizzarias,nome FROM ipi_pizzarias WHERE situacao = 'ATIVO' ORDER BY nome";
                    $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
                    while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
                    {
                        echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
                        if($_SESSION['ipi_caixa']['pizzaria_atual'] == $objBuscaPizzarias->cod_pizzarias)
                            echo 'selected="selected"';
                        echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <td align="center"><br><b>Dados Pessoais</b></td>
        </tr>

        <tr>
            <td>* Situação:</td>
        </tr>
        <tr>
            <td>
                <table cellpadding="0" cellspacing="4" border="0">
                    <tr>
                        <td>                
                            <select name="situacao_cliente" id="situacao_cliente" size="1" style="text-align: left;">
                                <option value=""></option>
                                <option value="ATIVO">Ativo</option>
                                <option value="INATIVO">Inativo</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="alerta_situacao_cliente" id="alerta_situacao_cliente" value="ATENÇÃO! CLIENTE INATIVO" size="30" style="border: 0px; color: #FF0000; font-size: 20px">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>             

            <!-- <tr>
                <td>&nbsp;</td>
            </tr> -->
            <tr>
                <td>* Nome Completo:</td>
            </tr>
            <tr>
                <td><input name="nome" id="nome" type="text" style="width: 365px"></td>
            </tr>			
<!-- 			<tr>
				<td>&nbsp;</td>
			</tr> -->

			<tr>
				<td>E-mail:</td>
			</tr>
			<tr>
				<td><input name="email" id="email" type="text" style="width: 365px"></td>
			</tr>


            <tr>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td>CPF:</td>
            </tr>
            <tr>
                <td><input name="cpf" id="cpf" type="text" style="width: 100px"
                    maxlength="14" onkeypress="return MascaraCPF(this, event);"></td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                </tr> 
                <tr><td>
                  <table>
                    <tr>
                        <td>Sexo:</td>
                        <td>&nbsp;</td>
                        <td>Data de Nascimento:</td>
                    </tr>
                    <tr>
                        <td>
                            <select name="sexo" id="sexo" style="width: 100px">
                              <option value=""></option>
                              <option value="M" <? echo ($objBusca->sexo == "M" ? "selected = selected" : ""); ?>>Masculino</option>
                              <option value="F" <? echo ($objBusca->sexo == "F" ? "selected = selected" : ""); ?>>Feminino</option>
                          </select>
                      </td>
                      <td>&nbsp;</td>
                      <td><input name="nascimento" id="nascimento" type="text" style="width: 140px"
                          maxlength="14" onkeypress="return MascaraData(this, event);">&nbsp;<small>Responda
                          e concorra a promoções.</small></td>
                      </tr>
                  </table></td></tr>

<!--             <tr>
                <td>&nbsp;</td>
            </tr> -->


            <tr>
                <td>&nbsp;</td>
            </tr> 

<!-- 			<tr>
				<td>Celular:</td>
			</tr>
			<tr>
				<td><input name="celular" id="celular" type="text" style="width: 177px"
					onkeypress="return MascaraTelefone(this, event);">&nbsp;<small>(xx)
				xxxx-xxxx</small></td>
			</tr> -->



<!-- 			<tr>
				<td>&nbsp;</td>
			</tr> -->
			<tr>
				<td align="center"><b>Endereço de Entrega</b></td>
			</tr>
<!-- 			<tr>
				<td>&nbsp;</td>
			</tr> -->

<!--                 <tr>
                <td>&nbsp;</td>
            </tr> -->
            <tr>
                <td>
                    <table cellpadding="1" cellspacing="0">
                     <tr>
                       <td>* Telefone Local 1:<small>(xx)
                       xxxx-xxxx</small></td>
                       <td>&nbsp;</td>
                       <td>Telefone Local 2:<small>(xx)
                       xxxx-xxxx</small></td>
                   </tr>
                   <tr>
                      <td><input name="telefone_1" id="telefone_1" type="text" style="width: 140px"
                        value="" onkeypress="return MascaraTelefone(this, event);">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><input name="telefone_2" id="telefone_2" type="text" style="width: 140px"
                           onkeypress="return MascaraTelefone(this, event);"></td>
                       </tr>
                   </table>
               </td>
           </tr>


<!-- 			<tr>
				<td>&nbsp;</td>
			</tr> -->
			<tr>
				<td align="center"><b>Para finalizar digite seu CEP e clique em
				completar endereço</b></td>
			</tr>
<!-- 			<tr>
				<td>&nbsp;</td>
			</tr> -->

            <tr id="cadBairro">
                <td>

                    <table cellpadding="1" cellspacing="0">
                        <tr>
                            <td>Bairro:</td>
                        </tr>
                        <tr>
                            <td><input name="bairro" id="bairro" type="text" style="width: 365px"></td>
                        </tr>
                    </table>

                </td>
            </tr>
            <tr>
                <td>CEP:</td>
            </tr>
            <tr>
                <td><input name="cep" id="cep" type="text" style="width: 75px"
                 onkeypress="return MascaraCEP(this, event);">&nbsp;<input
                 type="button" value="Completar Endereço" style="width: 150px;"
                 onclick="completar_endereco()">  <div id="cep_fora_cobertura" name="cep_fora_cobertura"></div>
             </td>
         </tr>

<!-- 			<tr>
				<td>&nbsp;</td>
			</tr> -->

			<tr id="cadEndereco">
				<td>
                    <table cellpadding="1" cellspacing="0">
                     <tr>
                      <td>Endereço:</td>
                      <td>&nbsp;</td>
                      <td>Número:</td>
                  </tr>
                  <tr>
                      <td><input name="endereco" id="endereco" type="text" style="width: 290px"></td>
                      <td>&nbsp;</td>
                      <td><input name="numero" id="numero" type="text" style="width: 65px"></td>
                  </tr>
              </table>
          </td>
      </tr>

      <tr>
        <td>Ponto de referencia da casa do Cliente:</td>
    </tr>
    <tr>
      <td><input name="ref_cliente" id="ref_cliente" type="text" style="width: 365px"><input name="ref_endereco" id="ref_endereco" type="hidden" style="width: 365px"></td>
  </tr>
<!-- 


          <tr>
              <td>&nbsp;</td>
          </tr> -->
            <!--<tr>
              <td>Ponto de referencia da rua:</td>
          </tr>
          <tr>
                  <td><input name="ref_endereco" id="ref_endereco" type="text" style="width: 365px"></td>
              </tr>-->
              <tr id="cadComplemento">
                <td>
                    <table cellpadding="1" cellspacing="0">
                     <tr>
                      <td>Complemento:</td>
                      <td>&nbsp;</td>
                      <td>Edifício:</td>

                  </tr>
                  <tr>
                      <td><input name="complemento" id="complemento" type="text" style="width: 165px"></td>
                      <td>&nbsp;</td>
                      <td><input name="edificio" id="edificio" type="text" style="width: 190px"></td>
                  </tr>
              </table>
          </td>
      </tr>



      <tr>
        <td>
            <table cellpadding="1" cellspacing="0">
             <tr>
              <td>Cidade:</td>
              <td>&nbsp;</td>
              <td>Estado:</td>
          </tr>
          <tr>
              <td><input name="cidade" id="cidade" type="text" style="width: 301px"></td>
              <td>&nbsp;</td>
              <td><select name="estado" id="estado" style="width: 58px">
               <option value=""></option>
               <option value="AC">AC</option>
               <option value="AL">AL</option>
               <option value="AP">AP</option>
               <option value="AM">AM</option>
               <option value="BA">BA</option>
               <option value="CE">CE</option>
               <option value="DF">DF</option>
               <option value="ES">ES</option>
               <option value="GO">GO</option>
               <option value="MA">MA</option>
               <option value="MT">MT</option>
               <option value="MS">MS</option>
               <option value="MG">MG</option>
               <option value="PA">PA</option>
               <option value="PB">PB</option>
               <option value="PR">PR</option>
               <option value="PE">PE</option>
               <option value="PI">PI</option>
               <option value="RJ">RJ</option>
               <option value="RN">RN</option>
               <option value="RS">RS</option>
               <option value="RO">RO</option>
               <option value="RR">RR</option>
               <option value="SC">SC</option>
               <option value="SP">SP</option>
               <option value="SE">SE</option>
               <option value="TO">TO</option>
           </select></td>
       </tr>
   </table>
</td>
</tr>			
<tr>
  <td>&nbsp;</td>
</tr>

<tr>
  <td>Observações do Cliente:</td>
</tr>
<tr>
  <td>
      <textarea name="obs_cliente" id="obs_cliente" cols="65" rows="4"></textarea>
  </td>
</tr>

<tr>
    <td> *Onde conheceu a <?php echo NOME_SITE; ?>:</td>
</tr>
<tr>
  <td>           
    <select name="cod_onde_conheceu" id="cod_onde_conheceu" size="1" style="text-align: left;">
      <option value=""></option>
      <?
      $conexao = conectabd();

      $sql_busca_rapida ="SELECT cod_onde_conheceu, onde_conheceu FROM ipi_onde_conheceu WHERE situacao = 'ATIVO' ORDER BY onde_conheceu"; 
      $res_buscar_rapida = mysql_query($sql_busca_rapida);
      echo $sql_busca_rapida;

      while($obj_busca_rapida = mysql_fetch_object($res_buscar_rapida))
      {
          echo("<option value='".$obj_busca_rapida->cod_onde_conheceu ."' > ". $obj_busca_rapida->onde_conheceu." </option>");

      }        
      ?>
  </select>
</td>
</tr>

<tr>
    <td>&nbsp;</td>
</tr>
<tr id="cadBotao">
    <td align="center"><input type="button" value="Cadastrar" name="bt_cadastrar" id="bt_cadastrar" onclick="javscript: if (validar_cadastro(document.frmCadastro)) { document.frmCadastro.submit(); }"></td>
</tr>
</table>

<input type="hidden" name="cod_clientes" id="cod_clientes" value="">
<input type="hidden" name="cod_enderecos" id="cod_enderecos" value="">
<input type="hidden" name="tipo_cliente" id="tipo_cliente" value="NOVO">
<input type="hidden" name="acao" value="adicionar_cliente">

</form>

<!-- Fim Cadastro -->

</div>

<div id="div_buscar_cliente">
  <form method="post" name="frm_buscar_cliente">
      <a href="javascript:ocultar_exibir('inserir_cliente'); limpar_formulario_cadastro();">7 - Novo Cliente</a>&nbsp;&nbsp;<a href="javascript:tabela_frete();">Tabela de Frete</a><br>

      <br/>
      <b>Tipo de Entrega:</b>
      <br/>

      <select name="tipo_entrega" id="tipo_entrega_buscar" onchange="ajustar_tipo_entrega(this.value)" style="width: 200px;">
        <option value=""></option>
        <option value="Balcão" >Balcão</option>
        <option value="Entrega" >Entregar</option>
    </select>     

    <?php
/*
              <select name="tipo_entrega" id="tipo_entrega_buscar" onchange="ajustar_tipo_entrega(this.value)" style="width: 200px;">
                <option value=""></option>
                <option value="Balcão" <? if($_SESSION['ipi_caixa']['entregac'] == "Balcão") echo "selected='selected'" ?> >Balcão</option>
                <option value="Entrega" <? if($_SESSION['ipi_caixa']['entregac'] == "Entrega") echo "selected='selected'" ?>>Entregar</option>
              </select>     
*/
              ?>

              <br/>

              <div id="div_buscar_pizzaria" style='display:none'>
                  <br/>
                  <b>Pizzaria:</b>
                  <br/>

                  <select name="cod_pizzarias" id="cod_pizzarias_buscar" onchange='ajustar_pizzaria(this.value)'style="width: 200px;">
                    <option value="">Selecione a Pizzaria</option>
                    <?

                    $SqlBuscaPizzarias = "SELECT cod_pizzarias,nome FROM ipi_pizzarias WHERE situacao = 'ATIVO' ORDER BY nome";
                    $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
                    while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
                    {
                        echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
                        if($_SESSION['ipi_caixa']['pizzaria_atual'] == $objBuscaPizzarias->cod_pizzarias)
                            echo 'selected="selected"';
                        echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
                    }
                    ?>
                </select>
            </div>
            <br/><br/>

            Nome, email, telefone ou cpf: <input type="text" id="buscar_cliente" name="buscar_cliente" value="" class="proximo_cliente" onkeypress="return proximo_campo(this, event, 'proximo_cliente')"> 
            <input type="button" name="bt_buscar_cliente" id="bt_buscar_cliente" value="Buscar Cliente" class="proximo_cliente" />
            <div id="resultado_busca_cliente"></div>
        </form>
    </div>

</div>



<!-- Tab Pizzas -->
<div class="painelTab">

  <script type="text/javascript">

      function limpar_formulario_pizza(frm)
      {
          <?
          if ($produto_combo!=1)
          {
            ?>
            frm.pizza_promocional.checked = false;
        //frm.borda_promocional.checked = false;
        <?
    }
    ?>
    frm.cod_tamanhos_digito.value = '';
    frm.cod_tamanhos.selectedIndex = -1;
    frm.num_sabores_digito.value = '';
    frm.num_sabores.selectedIndex = -1;
    frm.cod_tipo_massas_digito.value = '';
    frm.cod_tipo_massa.selectedIndex = -1;
    frm.cod_opcoes_corte_digito.value = '';
    frm.cod_opcoes_corte.selectedIndex = -1;
    frm.cod_bordas_digito.value = '';
    frm.cod_bordas.selectedIndex = -1;
      //frm.cod_adicionais_digito.value = '';
      //frm.cod_adicionais.selectedIndex = -1;
      frm.cod_pizzas_digito_1.value = '';
      frm.cod_pizzas_1.selectedIndex = -1;
  }

  function selecionar_codigo(txt_codigo, textarea_lista)
  {
      if (txt_codigo.name=="cod_tamanhos_digito")
      {
        textarea_lista.value = txt_codigo.value;
        if (textarea_lista.value)
        {
            tamanho_pizza(textarea_lista);
        }
    }
    else
    {
        textarea_lista.value = txt_codigo.value;
    }            
}

function selecionar_box(event, txt_codigo, cb_ingrediente)
{
  var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
  if (keyCode == 13)
  { 
    var arr_ingredientes = document.getElementsByName(cb_ingrediente);
        //alert(field.form.elements[i-1].value + ' - ' + field.form.elements[i].value + ' - ' + field.form.elements[i+1].value + ' - ' + arr_ingredientes[0].value);
        total = arr_ingredientes.length;
        for (a=0; a<total; a++)
        {
          if (arr_ingredientes[a].value==txt_codigo.value)
          {
              arr_ingredientes[a].checked = ! arr_ingredientes[a].checked;
          }
      }
  }
}
</script>

<?
if (($produto_combo==1)&&($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']=="PIZZA"))
{
  $sql_combos = "SELECT imagem_fundo FROM ipi_combos c WHERE c.cod_combos='".$_SESSION['ipi_caixa']['combo']['cod_combos']."'";
  $res_combos = mysql_query( $sql_combos );
  $obj_combos = mysql_fetch_object( $res_combos );
  ?>
  <div style="background-image: url('../../upload/combos/<? echo $obj_combos->imagem_fundo; ?>');">
      <?
  }
  else
  {
      ?>
      <div>
          <?
      }
      ?>

      <form method="post" name="frm_pedido_pizzas" action="ipi_caixa_acoes.php">

          <label>Pizza</label>
          <hr noshade="noshade" size="1">

          <br>
          <br>
          <br>

          <table border="0" align="center" width="500" cellpadding="0"
          cellspacing="0">

          <tr>
            <td width="250">

                <div style="float: left; width: 180px">
                    <label>Tamanho da Pizza:</label> 
                    <input align="top" type="text"
                    name="cod_tamanhos_digito" id="cod_tamanhos_digito" maxlength="4"
                    style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_tamanhos);"> 
                </div>
                
                <?
                if ( ( ($produto_combo==1) && ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']!='PIZZA') )|| ($produto_combo==0))
                {
                    ?>
                    <div id="fundo_pizza_promocional" style="width: 55px; float: left; height: 30px;">
                        <input type="checkbox" name="pizza_promocional" id="pizza_promocional" value="1" onfocus="javascript:document.getElementById('fundo_pizza_promocional').style.backgroundColor='#F7F413';" onBlur="javascript:document.getElementById('fundo_pizza_promocional').style.backgroundColor='#FFFFFF';" onclick="javascript:if(this.checked){document.getElementById('cod_motivo_promocoes_pizza').style.display='block';}else{document.getElementById('cod_motivo_promocoes_pizza').style.display='none';}" ><b>Promo</b>
                    </div>

                    <div style="width: 55px; float: left">
                        <select name="cod_motivo_promocoes_pizza" id="cod_motivo_promocoes_pizza" size="1" style="width: 230px; display:none;">
                            <?
                            $sql_promocoes = "SELECT * FROM ipi_motivo_promocoes WHERE situacao='ATIVO' ORDER BY motivo_promocao";
                            $res_promocoes = mysql_query($sql_promocoes);
                            echo '<option value=""></option>';
                            while($obj_promocoes = mysql_fetch_object($res_promocoes))
                            {
                                echo '<option value="' . $obj_promocoes->cod_motivo_promocoes . '">' . bd2texto($obj_promocoes->motivo_promocao) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <?
                } 
                ?>


                <br />
                <select name="cod_tamanhos" id="cod_tamanhos" size="6" onchange="javascript:document.getElementById('cod_tamanhos_digito').value=this.value;"
                style="width: 230px;" onblur="javascript:tamanho_pizza(this);">
                <?
                
                if ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']=="PIZZA")
                {
                    $sql_buscar_tamanhos = "SELECT t.cod_tamanhos,t.tamanho FROM ipi_tamanhos t WHERE t.cod_tamanhos = '" . $_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['cod_tamanhos'] . "' ORDER BY t.cod_tamanhos";
                }
                else 
                {
                    $sql_buscar_tamanhos = "SELECT cod_tamanhos,tamanho FROM ipi_tamanhos ORDER BY cod_tamanhos";
                }
                $res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);
                
                while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
                {
                    echo '<option value="' . $obj_buscar_tamanhos->cod_tamanhos . '">' . bd2texto($obj_buscar_tamanhos->cod_tamanhos . ' - ' . $obj_buscar_tamanhos->tamanho) . '</option>';
                }
                ?>
            </select>
            <? //echo "aki: ".$sql_buscar_tamanhos; ?>
        </td>

        <td width="10">&nbsp;</td>

        <td width="240"><label>Quantos Sabores:</label> <input type="text"
         name="num_sabores_digito" id="num_sabores_digito" class="proximo"
         style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.num_sabores);"> <br>
         <select name="num_sabores" id="num_sabores" size="6"
         style="width: 230px;" onchange="javascript:document.getElementById('num_sabores_digito').value=this.value;" onblur="javascript:definir_sabores();">
     </select></td>
 </tr>

 <tr>
    <td colspan="3" style="height: 20px;">&nbsp;</td>
</tr>

<tr>




    <td width="240" ><label>Corte:</label> 
        <input type="text" name="cod_opcoes_corte_digito" id="cod_opcoes_corte_digito" class="proximo"	style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_opcoes_corte);"> <br/>
        <select name="cod_opcoes_corte" id="cod_opcoes_corte" size="4" style="width: 230px;" onchange="javascript:document.getElementById('cod_opcoes_corte_digito').value=this.value;" >
        </select><input type="hidden" name="cod_adicionais" id='cod_adicionais' value='0' />

        <td width="10">&nbsp;</td>

<!--                     <td><label>Tipo de Massa:</label> <input type="text"
                    name="cod_tipo_massas_digito" id="cod_tipo_massas_digito"
                class="proximo" style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_tipo_massa);"> <br>
                <select name="cod_tipo_massa" id="cod_tipo_massa" size="4"
                    style="width: 230px;" onchange="javascript:document.getElementById('cod_tipo_massas_digito').value=this.value;">
                </select></td> -->
                <input type="hidden"
                name="cod_tipo_massas_digito" id="cod_tipo_massas_digito"
                value="1">
                <input type="hidden" value="1" name="cod_tipo_massa" id="cod_tipo_massa">
                <td>
                 <div style="float: left; width: 180px; ">
                    <label>Borda Recheada:</label> 
                    <input type="text"  name="cod_bordas_digito" id="cod_bordas_digito" class="proximo" style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_bordas);">  <br/>
                </div>

                <?
                if ( ( ($produto_combo==1) && ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']!='PIZZA') )|| ($produto_combo==0))
                {
                    ?>
                    <div id="fundo_borda_promocional" style="width: 55px; float: left; height: 30px; ">
                        <input type="checkbox" name="borda_promocional" id="borda_promocional" value="1" onfocus="javascript:document.getElementById('fundo_borda_promocional').style.backgroundColor='#F7F413';" onBlur="javascript:document.getElementById('fundo_borda_promocional').style.backgroundColor='#FFFFFF';" onclick="javascript:if(this.checked){document.getElementById('cod_motivo_promocoes_borda').style.display='block';}else{document.getElementById('cod_motivo_promocoes_borda').style.display='none';}"><b>Promo</b>
                    </div>

                    <div id="fundo_borda_promocional" style="width: 155px; float: left;">
                        <select name="cod_motivo_promocoes_borda" id="cod_motivo_promocoes_borda" size="1" style="width: 230px; display:none;">
                            <?
                            $sql_promocoes = "SELECT * FROM ipi_motivo_promocoes WHERE situacao='ATIVO' ORDER BY motivo_promocao";
                            $res_promocoes = mysql_query($sql_promocoes);
                            echo '<option value=""></option>';
                            while($obj_promocoes = mysql_fetch_object($res_promocoes))
                            {
                                echo '<option value="' . $obj_promocoes->cod_motivo_promocoes . '">' . bd2texto($obj_promocoes->motivo_promocao) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <?
                }
                ?>
                <br />
                <select name="cod_bordas" id="cod_bordas" size="5" style="width: 230px;" onchange="javascript:document.getElementById('cod_bordas_digito').value=this.value;">
                </select> <br>
            </td>
        </td>

    </tr>

<!-- 			<tr>
				<td colspan="3" style="height: 20px;">&nbsp;</td>
			</tr> -->

			<!-- <tr> -->
<!-- 				<td>
				
             
				</td>

				<td width="10">&nbsp;</td>

				<td> -->
<!--
                <label>Gergelim:</label> <input type="text"
					name="cod_adicionais_digito" id="cod_adicionais_digito"
					class="proximo" style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_adicionais);"
					> <br>
				<select name="cod_adicionais" id="cod_adicionais" size="5"
					style="width: 230px;" onchange="javascript:document.getElementById('cod_adicionais_digito').value=this.value;">
				</select> <br>
            -->
<!-- 				</td>
</tr> --> 

</table>

<br>
<br>
<br>

<table border="0" align="center" width="500" cellpadding="0" cellspacing="0">
 <tr>
    <td id="sabor_1">


      <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">	
       <tr>
        <td style="height: 30px;" colspan="4">
            <hr noshade="noshade" size="1">
        </td>
    </tr>
    <tr>
        <td><label  id='lbl_sabor_1'>1º Sabor:</label> 
            <input type="text" name="cod_pizzas_digito_1" id="cod_pizzas_digito_1" class="proximo"
            style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_pizzas_1);" > 
            <br>

            <select name="cod_pizzas_1" id="cod_pizzas_1" size="10" style="width: 230px;" onchange="javascript:document.getElementById('cod_pizzas_digito_1').value=this.value;" onblur="javascript:carregar_ingredientes('1');">
            </select>

            <br>
            <input type="hidden" name="num_fracao[]" value="1">

        </td>

        <td width="30">&nbsp;</td>

        <td><label id='lbl_obs_1'> Observações:</label><br>
            <textarea name="observacao_1" id="observacao_1" rows="9" cols="34" class="proximo" ></textarea>
        </td>
    </tr>
    <tr>
        <td style="height: 30px;" colspan="4" id="ingredientes_1"></td>
    </tr>
    <tr>
        <td style="height: 30px;" colspan="4" id="adicionais_1"></td>
    </tr>
</table>


</td>
</tr>




<tr>
   <td id="sabor_2">

    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">   
       <tr>
        <td style="height: 30px;" colspan="4">
            <hr noshade="noshade" size="1">
        </td>
    </tr>
    <tr>
        <td><label id='lbl_sabor_2'>2° Sabor:</label> <input type="text"
         name="cod_pizzas_digito_2" id="cod_pizzas_digito_2" class="proximo"
         style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_pizzas_2);"> <br>

         <select name="cod_pizzas_2" id="cod_pizzas_2" size="10" style="width: 230px;" onchange="javascript:document.getElementById('cod_pizzas_digito_2').value=this.value;" onblur="javascript:carregar_ingredientes('2');">
         </select>

         <br>
         <input type="hidden" name="num_fracao[]" value="2">

     </td>

     <td width="30">&nbsp;</td>

     <td> <label id='lbl_obs_2'>Observações:</label><br>
        <textarea name="observacao_2" id="observacao_2" rows="9" cols="34" class="proximo" ></textarea>
    </td>
    
</tr>
<tr>
    <td style="height: 30px;" colspan="4" id="ingredientes_2"></td>
</tr>
<tr>
    <td style="height: 30px;" colspan="4" id="adicionais_2"></td>
</tr>
</table>

</td>
</tr>


<tr>
   <td id="sabor_3">

    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">   
       <tr>
        <td style="height: 30px;" colspan="4">
            <hr noshade="noshade" size="1">
        </td>
    </tr>
    <tr>
        <td><label id='lbl_sabor_3'>3º Sabor:</label> <input type="text"
         name="cod_pizzas_digito_3" id="cod_pizzas_digito_3" class="proximo"
         style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_pizzas_3);"> <br>

         <select name="cod_pizzas_3" id="cod_pizzas_3" size="10" style="width: 230px;" onchange="javascript:document.getElementById('cod_pizzas_digito_3').value=this.value;" onblur="javascript:carregar_ingredientes('3');">
         </select>
         <br>
         <input type="hidden" name="num_fracao[]" value="3">

     </td>

     <td width="30">&nbsp;</td>

     <td><label id='lbl_obs_3'> Observações:</label><br>
        <textarea name="observacao_3" id="observacao_3" rows="9" cols="34" class="proximo" ></textarea>
    </td>
</tr>
<tr>
    <td style="height: 30px;" colspan="4" id="ingredientes_3"></td>
</tr>
<tr>
    <td style="height: 30px;" colspan="4" id="adicionais_3"></td>
</tr>
</table>

</td>
</tr>



<tr>
   <td id="sabor_4">

    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">   
       <tr>
        <td style="height: 30px;" colspan="4">
            <hr noshade="noshade" size="1">
        </td>
    </tr>
    
    <tr>
        <td><label id='lbl_sabor_4'>4º Sabor:</label> <input type="text"
         name="cod_pizzas_digito_4" id="cod_pizzas_digito_4" class="proximo" style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_pizzas_4);" > <br>

         <select name="cod_pizzas_4" id="cod_pizzas_4" size="10" style="width: 230px;"  onchange="javascript:document.getElementById('cod_pizzas_digito_4').value=this.value;" onblur="javascript:carregar_ingredientes('4');">
         </select>
         <br>
         <input type="hidden" name="num_fracao[]" value="4">

     </td>

     <td width="30">&nbsp;</td>

     <td> <label id='lbl_obs_4'>Observações:</label><br>
        <textarea name="observacao_4" id="observacao_4" rows="9" cols="34" class="proximo" ></textarea>
    </td>
    
</tr>
<tr>
    <td style="height: 30px;" colspan="4" id="ingredientes_4"></td>
</tr>
<tr>
    <td style="height: 30px;" colspan="4" id="adicionais_4"></td>
</tr>
</table>

</td>
</tr>




</table>

<br>
<center>
  <input type="button" name="br_fechar_pedido" onclick="javascript:if(validacao_pizza(document.frm_pedido_pizzas)){document.frm_pedido_pizzas.submit();}" value="Adicionar ao Pedido" class="proximo">
</center>
<?
if ($produto_combo==1)
{
    ?>
    <input type="hidden" name="acao" value="adicionar_pizza_combo">
    <input type="hidden" name="id_combo" value="<? echo $_SESSION['ipi_caixa']['combo']['id_combo']; ?>">
    <input type="hidden" name="indice_atual_combo" value="<? echo $indice_produto_atual_combo; ?>">
    <?
}
else if ($produto_combo==0)
{
    ?>
    <input type="hidden" name="acao" value="adicionar_pizza">
    <?
}
?>
</form>
</div>

</div>
<!-- Tab Editar --> 






<!-- Tab Combos ############################################# -->
<div class="painelTab" style="text-align: center;">
    <?
    $cod_pizzarias = $_SESSION["usuario"]["cod_pizzarias"][0];
        /*echo "<pre>";
        print_r($_SESSION['ipi_caixa']);
        echo "</pre>";*/
        if ($produto_combo!=1)
        {

            $sql_combos = "SELECT c.cod_combos,cp.imagem_final from ipi_combos c INNER JOIN ipi_combos_pizzarias cp ON (c.cod_combos = cp.cod_combos) WHERE c.situacao='ATIVO' AND cp.cod_pizzarias = '".$cod_pizzarias."' and cp.preco>0 order by c.ordem_combo";
            $res_combos = mysql_query($sql_combos);
            $num_combos = mysql_num_rows($res_combos);
            //echo "<br /><p style='color: #fff; text-align: left;'>&nbsp;&nbsp;&nbsp;&nbsp;NOVIDADE nos Muzzarellas!! Nossos combos</p> <br />";
            $z =1;
            while($obj_combos = mysql_fetch_object($res_combos))
            {   
                echo "<a href='ipi_caixa.php?cd=".$obj_combos->cod_combos."&acao=comprar'><img src='../../upload/combos/".$obj_combos->imagem_final."?".(date("His")).""."' border='1' width='500'></a>";
                if ($z == 2)
                    echo "<br />";

                $z++;
            }
        }
        else
        {
            echo "Já existe um combo aberto para compra!";
        }
        ?>
    </div>		



    <!-- Tab Bebidas -->
    <div class="painelTab">


        <script type="text/javascript">
            function validacao_bebida(frm)
            {
              <?
              if ($produto_combo!=1)
              {
                ?>
                if (frm.bebida_promocional.checked==true)
                {
                    if (frm.cod_motivo_promocoes_bebida.value=="")
                    {
                        alert("Motivo da Bebida Promocional não foi selecionado!");
                        frm.cod_motivo_promocoes_bebida.focus();
                        return false;
                    }
                }
                <?
            }
            ?>


            var arr = new Array();
            arr = document.getElementsByName("quantidades[]");
            for(var i = 0; i < arr.length; i++)
            {
                var obj = document.getElementsByName("quantidades[]").item(i);
                if(obj.value=="")
                {
                    alert("O campo quantidade não pode estar vazio!");
                    obj.focus();
                    return false;
                }
            }

            return true;

        }
    </script>

    <?
    if (($produto_combo==1)&&($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']=="BEBIDA"))
    {
        $sql_combos = "SELECT imagem_fundo FROM ipi_combos WHERE cod_combos='".$_SESSION['ipi_caixa']['combo']['cod_combos']."'";
        $res_combos = mysql_query( $sql_combos );
        $obj_combos = mysql_fetch_object( $res_combos );
        ?>
        <div style="background-image: url('../../upload/combos/<? echo $obj_combos->imagem_fundo; ?>');">
            <?
        }
        else
        {
            ?>
            <div>
                <?
            }
            ?>

            <form method="post" name="frm_pedido_bebidas" action="ipi_caixa_acoes.php">
              <table border="1" cellspacing="0" cellpadding="2" class="tabela" align="center" style="margin: 0px auto">
                <?
        $cod_pizzarias = $_SESSION['ipi_caixa']['pizzaria_atual']; //TODO: hoje está pegando a primeira pizzaria do usuario, verificar como serah feito isso

        if ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['cod_conteudos'])
        {
            $sqlBebidas = "SELECT cp.preco,c.conteudo,b.bebida,bc.cod_bebidas_ipi_conteudos FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas=b.cod_bebidas) WHERE c.cod_conteudos='" . $_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['cod_conteudos'] . "' AND cp.situacao = 'ATIVO' ORDER BY conteudo DESC, bebida";
        }
        else
        {
            $sqlBebidas = "SELECT cp.preco,c.conteudo,b.bebida,bc.cod_bebidas_ipi_conteudos FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas=b.cod_bebidas) WHERE cp.situacao = 'ATIVO' ORDER BY conteudo DESC, bebida";
        }
        $resBebidas = mysql_query ( $sqlBebidas );
        $linBebidas = mysql_num_rows ( $resBebidas );
        //echo "<br>Bebi: ".$sqlBebidas;

        if ($linBebidas > 0) {
            echo '<thead>';
            echo '<tr>';
            echo '  <td width="40" align="center" style="padding:10px">';
            echo '<b>Qtde</b>';
            echo '  </td>';
            echo '  <td width="130" align="center">';
            echo '<b>Bebida</b>';
            echo '  </td>';
            echo '  <td width="75" align="center">';
            echo '<b>Tamanho</b>';
            echo '  </td>';
            echo '  <td width="60" align="center">';
            echo '<b>Preço</b>';
            echo '  </td>';
            echo '</tr>';
            echo '</thead>';

            echo '<tbody>';
            for($a = 0; $a < $linBebidas; $a ++) {
                $objBebidas = mysql_fetch_object ( $resBebidas );
                echo '<tr>';
                echo '  <td align="center">';
                echo '  <input name="cod_bebidas_conteudos[]" type="hidden" value="'.$objBebidas->cod_bebidas_ipi_conteudos.'">';
                echo '  <input name="quantidades[]" id="quantidades'.$a.'" class="proxima_bebida" type="text" size="3" maxlength="2" value="0" onClick="javascript: this.select();" onkeypress="return ((ApenasNumero(event))&&(proximo_campo(this, event, \'proxima_bebida\')))">';
                echo '  </td>';
                echo '  <td style="padding:3px">';
                echo    $objBebidas->bebida;
                echo '  </td>';
                echo '  <td align="center">';
                echo    $objBebidas->conteudo;
                echo '  </td>';
                echo '  <td align="center">';
                echo    bd2moeda($objBebidas->preco);
                echo '  </td>';
                echo '</tr>';
            }
            echo '</tbody>';
        }
        ?>
    </table>
    <?
    if ($produto_combo!=1)
    {
        ?>
        
        <br>
        <center>
            <div id="fundo_bebida_promocional" align="center" style="width: 100px">
                <input type="checkbox" name="bebida_promocional" id="bebida_promocional" value="1" onfocus="javascript:document.getElementById('fundo_bebida_promocional').style.backgroundColor='#F7F413';" onBlur="javascript:document.getElementById('fundo_bebida_promocional').style.backgroundColor='#FFFFFF';" onclick="javascript:if(this.checked){document.getElementById('cod_motivo_promocoes_bebida').style.display='block';}else{document.getElementById('cod_motivo_promocoes_bebida').style.display='none';}"><b>Promocional</b>
            </div>
            <select name="cod_motivo_promocoes_bebida" id="cod_motivo_promocoes_bebida" size="1" style="width: 230px; display:none;">
              <?
              $sql_promocoes = "SELECT * FROM ipi_motivo_promocoes WHERE situacao='ATIVO' ORDER BY motivo_promocao";
              $res_promocoes = mysql_query($sql_promocoes);
              echo '<option value=""></option>';
              while($obj_promocoes = mysql_fetch_object($res_promocoes))
              {
                  echo '<option value="' . $obj_promocoes->cod_motivo_promocoes . '">' . bd2texto($obj_promocoes->motivo_promocao) . '</option>';
              }
              ?>
          </select>        
      </center>
      <?
  }
  ?>

  <br>
  <center>
      <input type="button" name="br_fechar_pedido" onclick="javascript:if(validacao_bebida(document.frm_pedido_bebidas)){document.frm_pedido_bebidas.submit();}" value="Adicionar ao Pedido">
  </center>


  <?
  if ($produto_combo==1)
  {
    ?>
    <input type="hidden" name="acao" value="adicionar_bebidas_combo">
    <input type="hidden" name="id_combo" value="<? echo $_SESSION['ipi_caixa']['combo']['id_combo']; ?>">
    <input type="hidden" name="indice_atual_combo" value="<? echo $indice_produto_atual_combo; ?>">
    <?
}
else if ($produto_combo==0)
{
    ?>
    <input type="hidden" name="acao" value="adicionar_bebidas">
    <?
}
?>


</form>
</div>


</div>


<!-- Tab Revisar -->
<div class="painelTab">

    <div id="revisao_pedido">
        Carregando pedido, aguarde...
    </div>
</div>


<!-- Tab Pagamentos -->
<div class="painelTab">
        <!-- 
        Escolha a forma de pagamento:<br>
        <br>
		<input type="checkbox" name="formas_pagamento[]" value="Dinheiro">Dinheiro,
		Valor:<input type="text" name="valor" value="" size="10">, Troco: <input
			type="text" name="valor" value="" size="10"> <br>
		<input type="checkbox" name="formas_pagamento[]" value="Cheque">Cheque:
		Valor: <input type="text" name="valor" value="" size="10">, Banco: <input
			type="text" name="valor" value="" size="10">, Num. Cheque: <input
			type="text" name="valor" value="" size="10"> <br>
		<input type="checkbox" name="formas_pagamento[]"
			value="Cartao Credito Visa">Cartão Crédito Visa, Valor:<input
			type="text" name="valor" value="" size="10"> <br>
		<input type="checkbox" name="formas_pagamento[]" value="Cheque">Cartão
		Debito Visa, Valor:<input type="text" name="valor" value="" size="10">
		<br>
		<input type="checkbox" name="formas_pagamento[]"
			value="Cartao Crédito Mastercard">MasterCard, Valor:<input
			type="text" name="valor" value="" size="10"> <br>
		<br>
  -->

  <script type="text/javascript">
      function validar_fechar_pedido(frm)
      {
        var pizzarias_sessao = new Array(-1, <? echo implode(",", $_SESSION['usuario']['cod_pizzarias']) ?>);	      

        var confirmado = "<? echo $_SESSION['ipi_caixa']['cliente']['confirmado']; ?>";
        
        if ( ((frm.cod_clientes_fechar.value=="")&&(frm.tipo_cliente_fechar.value=="ANTIGO")) || (frm.tipo_cliente_fechar.value=="") )
        {
          alert("Nenhum cliente foi selecionado!");
          return(false);
      }

      if (confirmado == "")
      {
          alert("O cadastro do cliente não foi confirmado!\nVolte na tela de cadastro de cliente e clique no botão confirmar!");
          return(false);
      }


      if (frm.cod_pizzarias.value=="")
      {
          alert("Pizzaria inválida! Selecione uma pizzaria!");
          frm.cod_pizzarias.focus();
          return(false);
      }

        //alert(pizzarias_sessao + " -- " +frm.cod_pizzarias.value + frm.cod_pizzarias.options[frm.cod_pizzarias.selectedIndex].text);

        var verificar=0;
        for (a=0; a<pizzarias_sessao.length; a++)
        {
          if (pizzarias_sessao[a]==frm.cod_pizzarias.value)
          {
            verificar=1;
        }
    }

    if (verificar==0)
    {
      if(!confirm("ATENÇÃO!!\n\nVocê está enviando este pedido para outra pizzaria: " + frm.cod_pizzarias.options[frm.cod_pizzarias.selectedIndex].text + " \n\nDeseja realmente continuar?"))
      {
        frm.cod_pizzarias.focus();
        return(false);
    }
}

        /*if (frm.cod_pizzarias.value=="")
        {
          alert("Pizzaria inválida! Selecione uma pizzaria!");
          frm.cod_pizzarias.focus();
          return(false);
      }*/

      if(frm.cpf_nota_fiscal.value!="")
      {
        if ((frm.cmb_tipo_cliente.value=="PF")&&(frm.cpf_nota_fiscal.value!="000.000.000-00"))
        {
         if (!ValidarCPF(frm.cpf_nota_fiscal.value))
         {
            alert("CPF digitado inválido!");
            frm.cpf_nota_fiscal.focus();
            return(false);
        }
    }
    else if ((frm.cmb_tipo_cliente.value=="PJ")&&(frm.cpf_nota_fiscal.value!="00.000.000.0000/00"))
    {
     if (!ValidarCNPJ(frm.cpf_nota_fiscal.value))
     {
        alert("CNPJ digitado inválido!");
        frm.cpf_nota_fiscal.focus();
        return(false);
    }
}
}


var txt_valor = document.getElementsByName("txt_valor_formas[]");
var cmb_forma_pg = document.getElementsByName("forma_pg[]");
var i;
for (i = 0; i < txt_valor.length; i++) 
{
  if (txt_valor[i].value == "") 
  {
    alert("Valor da forma de pagamento não foi digitada!");
    txt_valor[i].focus();
    return(false);
}
if (cmb_forma_pg[i].value == "") 
{
    alert("Não foi selecionada a forma de pagamento!");
    cmb_forma_pg[i].focus();
    return(false);
}

}

var vlor_total = parseFloat(frm.total_geral.value.replace(',', '.'));
var troco = parseFloat(frm.troco.value.replace(',', '.'));
if(troco < vlor_total)
{
  alert("Valor para troco não pode ser menor que o valor do pedido!");
  return(false);
} 

var vlor_total_formas = parseFloat(frm.txt_total_formas.value.replace(',', '.'));
if(vlor_total_formas != vlor_total)
{
  alert("O Valor total das parcelas, não confere com o valor total dos pedidos!");
  return(false);
}


/*            if (frm.tipo_entrega.value=="")
            {
                alert("Tipo de Entrega Inválido!");
                frm.tipo_entrega.focus();
                return(false);
            }*/
            /*
            if (frm.forma_pg.value=="")
            {
                alert("Forma de Pagamento Inválida!");
                frm.forma_pg.focus();
                return(false);
            }
             var forma_pg = frm.forma_pg.value.toUpperCase();

            if (forma_pg=="DINHEIRO")
            {
               if ((frm.troco.value!="0") && (frm.troco.value!= '') && (frm.troco.value!="0,00"))
                {
                  if ((frm.troco.value=="0") || (frm.troco.value== ''))
                  {
                    alert("De quanto você necessita de troco?");
                    frm.troco.focus();
                    return (false);
                  }
                  var troco_str = frm.troco.value;
                  var valor_total_str = frm.total_geral.value;

                  troco_str = troco_str.replace('.', '');
                  troco_str = troco_str.replace(',', '.');

                  //valor_total_str = valor_total_str.replace('.', '');
                  valor_total_str = valor_total_str.replace(',', '.');

                  var troco_num = parseFloat(troco_str);
                  var valor_total_num = parseFloat(valor_total_str);

                  if(troco_num < valor_total_num) 
                  {
                    alert("Ops, o valor para troco não deve ser menor que o valor total do pedido!\nVocê digitou R$ "+frm.troco.value+" e o valor de seu pedido é R$ "+frm.total_geral.value+".\nPor favor, digite novamente.");
                    frm.troco.focus();
                    frm.troco.value = "";
                    return (false);
                  }
                } 
                
                
            }
            */
            
            return (true);
        }

        function ultimaCasaPart(){
            var valorDesconto = document.querySelector('input[name="desconto"]').value;
            var ultimoValor = valorDesconto.substring(valorDesconto.length,valorDesconto.length-1);
            if(ultimoValor%2 != 0){
                ultimoValor = ultimoValor-1;
                let novoValor = valorDesconto.substring(0,valorDesconto.length-1)+""+ultimoValor;
                document.querySelector('input[name="desconto"]').value = novoValor;
                document.querySelector('input[name="desconto"]').focus();
            }
        }

        function alterar_tipo_cliente(cmb)
        {
           $('cpf_nota_fiscal').value="";
           if(cmb.value=="PF")
           {
            $('lbl_cpf_nota_fiscal').value="CPF Nota:";
            $('cpf_nota_fiscal').onkeypress = function (event) 
            {
               return MascaraCPF(this, event);
           }
       }
       else if(cmb.value=="PJ")
       {
        $('lbl_cpf_nota_fiscal').value="CNPJ Nota:";
        $('cpf_nota_fiscal').onkeypress = function (event) 
        {
           return MascaraCNPJ(this, event);
       }
   }
}


function adicionar_forma_pagamento()
{
  var newdiv = document.createElement('div');

  var contador_divs = parseInt(document.getElementById("contador_divs").value);
  contador_divs++;
  document.getElementById("contador_divs").value = contador_divs;
  div_id = "div_formas_pagtos_"+contador_divs.toString();
  newdiv.id = div_id;
  var html_txt = "";
          //newdiv.innerHTML = " <br><input type='text' name='myInputs[]'>";


          html_txt = '<input type="text" name="txt_valor_formas[]" id="txt_valor_formas_'+contador_divs+'" class="proximo" style="width: 60px;" onkeypress="return formatar_moeda(this, \'.\', \',\', event);" onblur="calcular_total_das_formas_pgto()">';
          html_txt += '<select name="forma_pg[]" style="width: 200px; margin-left: 4px;">';
          html_txt += '<option value=""></option>';
          <?php
          $sql_formas_pg = "SELECT fp.forma_pg,fp.cod_formas_pg FROM ipi_formas_pg fp INNER JOIN ipi_formas_pg_pizzarias fpp on fpp.cod_formas_pg = fp.cod_formas_pg WHERE fpp.cod_pizzarias = '$cod_pizzarias' ORDER BY forma_pg";
          $res_formas_pg = mysql_query($sql_formas_pg);
          while($obj_formas_pg = mysql_fetch_object($res_formas_pg)) 
          {
            echo 'html_txt += \'<option value="'.$obj_formas_pg->cod_formas_pg.'" \';';
            if("DINHEIRO" == $obj_formas_pg->forma_pg)
              echo 'html_txt += \'selected="selected"\';';
          echo 'html_txt += \'>'.bd2texto($obj_formas_pg->forma_pg).'</option>\';';
      }
      ?>
      html_txt += '</select>';
      html_txt += '<input type="button" value="+" onclick="javascript:adicionar_forma_pagamento()" style="width:20px; margin-left: 11px;">';
      html_txt += '<input type="button" value="-" onclick="javascript:remover_forma_pagamento(\''+div_id+'\')" style="width:20px; margin-left: 5px;">';

      newdiv.innerHTML = html_txt;

      document.getElementById("area_formas_pagamento").appendChild(newdiv);
  }

  function remover_forma_pagamento(div_id)
  {
      document.getElementById(div_id).remove();
      calcular_total_das_formas_pgto();
  }    

  function calcular_total_das_formas_pgto()
  {

      var objTxt = document.getElementsByName("txt_valor_formas[]");

      var cnt = objTxt.length;
      var subtotal_formas_pg = 0;
      var txt='';
      for (a=0; a<cnt; a++)
      {
        txt = document.getElementsByName("txt_valor_formas[]")[a].value;
        if (txt != "")
        {
          subtotal_formas_pg += parseFloat(txt.toString().replace('.','').replace(',','.')) 
      }
  }
  $('txt_total_formas').setProperty('value', subtotal_formas_pg.toFixed(2).toString().replace('.',','));
}     
</script>

<form method="post" name="frm_fechar_pedido" action="ipi_caixa.php">

    <table border="0">

      <tr>
        <td align="right"> <b>Pizzaria:</b> </td>
        <td> 
          <select name="cod_pizzarias" id="cod_pizzarias" onchange='carregar_formas_pg(this.value)'style="width: 200px;">
            <option value="">Selecione a Pizzaria</option>
            <?
                  //$cod_pizzarias = $_SESSION['usuario']['cod_pizzarias'][0];

            require '../../pub_req_fuso_horario1.php';

                      // if (defined('HORARIO_INICIO_CENTRAL_PIZZARIA')){
                      //       $tempoInicio = HORARIO_INICIO_CENTRAL_PIZZARIA;
                      //   }
                      //   else{
                      //           $tempoInicio = '08:00:00'; //valor default
                      //   }

                      //   if (defined('HORARIO_FIM_CENTRAL_PIZZARIA')){
                      //       $tempoFim = HORARIO_FIM_CENTRAL_PIZZARIA;
                      //   }
                      //   else{
                      //           $tempoFim = '17:00:00'; //valor default
                      //   }


                        // $dateTime = new DateTime($tempoInicio);
                        // $dateTime2 = new DateTime($tempoFim);

                        // if (($dateTime->diff(new DateTime)->format('%R') == '+') && ($dateTime2->diff(new DateTime)->format('%R')== '-')) {

                        //     if (defined('CODIGO_PIZZARIA_CENTRAL')){
                        //         $codPizzarias = CODIGO_PIZZARIA_CENTRAL;
                        //     }
                        //     else{
                        //         $codPizzarias = 1; //valor default
                        //     }

                        //      $SqlBuscaPizzarias = "SELECT cod_pizzarias,nome FROM ipi_pizzarias WHERE situacao = 'ATIVO' AND cod_pizzarias = $codPizzarias ";
                        //      // echo $SqlBuscaPizzarias;
                        //      $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
                        //      $objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias);
                        //                echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';                                         
                        //                      echo 'selected="selected"';
                        //                 echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
                        // }
                  // else{
            $SqlBuscaPizzarias = "SELECT cod_pizzarias,nome FROM ipi_pizzarias WHERE situacao = 'ATIVO' ORDER BY nome";
            $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
            while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
            {
                echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
                if($_SESSION['ipi_caixa']['pizzaria_atual'] == $objBuscaPizzarias->cod_pizzarias)
                 echo 'selected="selected"';
             echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
         }
                  // }

         ?>
     </select>
 </td>
</tr> 


<tr>
    <td align="right"> <b>Tipo de cliente:</b> </td>
    <td> 
      <select name="cmb_tipo_cliente" id="cmb_tipo_cliente" style="width: 200px;" onChange="alterar_tipo_cliente(this)">
        <option value="PF">Pessoa Física</option>
        <option value="PJ">Pessoa Jurídica</option>
    </select>        
</td>
</tr>


<tr>
    <td align="right" width="150"> <b> <input type="text" value="CPF Nota:" name="lbl_cpf_nota_fiscal" id="lbl_cpf_nota_fiscal" style="border: 0px; text-align: right; font-weight: bold;" size="15" /></b> </td>
    <td width="350"> <input type="text" name="cpf_nota_fiscal" id="cpf_nota_fiscal" class="proximo" style="width: 120px;" onkeypress="return MascaraCPF(this, event);"> </td>
</tr> 

<tr>
    <td align="right"> <b>Horário Agendamento:</b> </td>
    <td> <input type="text" name="horario_agendamento" id="horario_agendamento" class="proximo" style="width: 60px;" onkeypress="return MascaraHora(this, event);"> </td>
</tr>

<!--           <tr>
            <td align="right"> <b>Tipo de Entrega:</b> </td>
            <td>  

              <select name="tipo_entrega" id="tipo_entrega" style="width: 200px;" onblur="ajustar_frete_tipo_entrega(this.value)">
                <option value=""></option>
                <option value="Balcão" >Balcão</option>
                <option value="Entrega" selected='selected'>Entregar</option>
              </select>     
            
            </td>
        </tr> -->

        <tr>
            <td align="right"> <b>Total do Produtos:</b> </td>
            <td> <input type="text" name="txt_total_pedido" id="txt_total_pedido" class="proximo" style="width: 60px;" readonly="readonly" > </td>
        </tr>

        <tr>
            <td align="right"> <b>Desconto (O último número do desconto deve ser par):</b> </td>
            <td> 
                <input type="text" name="desconto" id="desconto" class="proximo" style="width: 60px;" value="0,00" onkeypress="return formatar_moeda(this, '.', ',', event);" onfocusout="return ultimaCasaPart()" onblur="calcular_total_com_desconto()"> 
            </td>
        </tr>

        <tr>
            <td align="right"> <b>SubTotal:</b> </td>
            <td> 
                <input type="text" name="total_com_desconto" id="total_com_desconto" style="width: 60px;" readonly="readonly" > 
            </td>
        </tr>

        <tr>
            <td align="right"> <b>Frete:</b> </td>
            <td> 
                <input type="text" name="frete" id="frete" style="width: 60px;" onkeypress="return formatar_moeda(this, '.', ',', event);" value="<? echo ($_SESSION['ipi_caixa']['cliente']['preco_frete']>0 ? bd2moeda($_SESSION['ipi_caixa']['cliente']['preco_frete']) : '0,00'); ?>" onblur="calcular_total_com_frete()"/><br/><input type='hidden' name="comissao_frete" id="comissao_frete" style="width: 60px;" onkeypress="return formatar_moeda(this, '.', ',', event);" value="<? echo ( $_SESSION['ipi_caixa']['cliente']['valor_comissao_frete'] > 0 ? bd2moeda($_SESSION['ipi_caixa']['cliente']['valor_comissao_frete']) : '0,00' ); ?>"/>
            </td>
        </tr>

        <tr>
            <td align="right"> <b>Total do Pedido:</b> </td>
            <td> 
                <input type="text" name="total_geral" id="total_geral" style="width: 60px;" readonly="readonly" > 
            </td>
        </tr>

        <tr>
            <td align="right"> <b>Forma de pagamento:</b> </td>
            <td> 
                <div id="area_formas_pagamento">

                  <div id="div_formas_pagtos_1">
                    <input type="text" name="txt_valor_formas[]" id="txt_valor_formas_1" class="proximo" style="width: 60px;" onkeypress="return formatar_moeda(this, '.', ',', event);" onblur="calcular_total_das_formas_pgto()">
                    <select name="forma_pg[]" style="width: 200px; margin-left: 1x;">
                      <option value=""></option>
                      <?
                      $sql_formas_pg = "SELECT fp.forma_pg,fp.cod_formas_pg FROM ipi_formas_pg fp INNER JOIN ipi_formas_pg_pizzarias fpp on fpp.cod_formas_pg = fp.cod_formas_pg WHERE fpp.cod_pizzarias = '$cod_pizzarias' ORDER BY forma_pg";
                      $res_formas_pg = mysql_query($sql_formas_pg);
                      while($obj_formas_pg = mysql_fetch_object($res_formas_pg)) 
                      {
                        echo '<option value="'.$obj_formas_pg->cod_formas_pg.'" ';
                        if("DINHEIRO" == $obj_formas_pg->forma_pg)
                            echo 'selected';
                        echo '>'.bd2texto($obj_formas_pg->forma_pg).'</option>';
                    }
                    ?>
                </select>
                <input type="button" value="+" onclick="javascript:adicionar_forma_pagamento()" style="width:20px; margin-left: 9px;">
                <!-- <input type="button" value="-" onclick="javascript:remover_forma_pagamento('div_formas_pagtos_1')" style="width:20px; margin-left: 2px;"> -->
            </div>

        </div>
        <input type="hidden" name="contador_divs" id="contador_divs" value="1" />
    </td>
</tr>

<tr>
    <td align="right"> <b>Total das Formas:</b> </td>
    <td> <input type="text" name="txt_total_formas" id="txt_total_formas" class="proximo" style="width: 60px;" readonly="readonly" > </td>
</tr>

<tr>
    <td align="right"> <b>Troco para:</b> </td>
    <td> <input type="text" name="troco" id="troco" class="proximo" style="width: 60px;" onkeypress="return formatar_moeda(this, '.', ',', event);" onBlur="javascript:calcular_troco()"> </td>
</tr>

<tr>
    <td align="right"> <b>Troco:</b> </td>
    <td> <input type="text" name="troco_cliente" id="troco_cliente" class="proximo" style="width: 60px;" readonly="readonly"> </td>
</tr>

<tr>
    <td align="right"> 
        <b>Observações Gerais:</b>
    </td>
    <td> 
        <textarea rows="6" cols="50" name="obs_pedido"></textarea>            
    </td>
</tr>

<tr>
    <td align="center" colspan="2"> 

        <input type="button" name="br_fechar_pedido" value="Fechar Pedido" onclick="javascript: if (validar_fechar_pedido(document.frm_fechar_pedido)) { document.frm_fechar_pedido.submit(); }">
        <input type="hidden" name="acao" value="fechar_pedido">
        <input type="hidden" name="cod_clientes_fechar" id="cod_clientes_fechar" value="">
        <input type="hidden" name="tipo_cliente_fechar" id="tipo_cliente_fechar" value="">
        <script> 
             //ajustar_frete_tipo_entrega("<? echo $_SESSION['ipi_caixa']['entregac'] ?>");
             //ajustar_tipo_entrega("<? echo $_SESSION['ipi_caixa']['entregac'] ?>");
         </script>
     </td>
 </tr>

</table>


</form>
</div>
</td>


<td valign="top" width="180">
  <div id="comanda">
    <h2>Comanda</h2>
    <div id="conteudo_comanda" style="padding: 0px;">
        <iframe	id="frame_conteudo_comanda" frameborder="0" width="180" height="500" src="ipi_caixa_listar.php"></iframe>
    </div>
</div>
</td>

</tr>
</table>

</div>




<div id="rodape"><img src="../../img/rod_mascara.jpg"></div>
</div>

<?
/*
 * 
 *  TABS não é um objeto dentro deste javascript. veirificar
if ($_SESSION['ipi_caixa']['combo'])
{
    if ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']=="PIZZA")
    {
        echo "<script>alert(tabs);//tabs.irpara(2);</script>";
    }
    else if($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']=="BEBIDA")
    {
        echo "<script>alert('teste2');tabs.irpara(3);</script>";
    }
}
*/

desconectabd($conexao);

$erro = validaVarPost('erro');
if ($erro)
{
    echo "<script>alert('".$erro."');</script>";
}
/*echo "<pre>";
print_r($_SESSION);
echo "</pre>";*/
/*$hora_final = microtime(true);
$diferenca_hora = $hora_final - $hora_inicial;
echo "<br>Hora Final: ".date("d/m/Y H:i:s:u", $hora_final);
echo "<script>console.log('".date("d/m/Y H:i:s:u", $hora_final)."')</script>"; 
echo "<br>Tempo Total: ". $diferenca_hora ." s";
echo "<script>console.log('".$diferenca_hora ."')</script>"; */
?>

</body>
</html>

