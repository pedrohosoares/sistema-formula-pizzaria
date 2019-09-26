<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once 'ipi_controle_mesas_classe.php';

$conexao = conectar_bd();
$controle_mesas = new ipi_controle_mesas();

$acao = validar_var_post('acao');
$cod_pizzarias_usuario = $_SESSION["usuario"]["cod_pizzarias"][0];

$tp="";

if ($acao == 'adicionar_taxas')
{
  $tp="t";

  $cod_colaboradores = validar_var_post('cod_colaboradores');
  $cod_mesas_taxas = validar_var_post('cod_mesas_taxas');
  $quantidades_taxas = validar_var_post('quantidades_taxas');
/*
  echo "<pre>";
  print_r($_POST);
  echo "</pre>";
  die();
*/
  //$quantidades = validar_var_post('quantidades');
  $num_taxas = count($cod_mesas_taxas);
  for ($a = 0; $a < $num_taxas; $a++)
  {
    if ($quantidades_taxas[$a] != '0')
    {
      $controle_mesas->adicionar_taxa($cod_colaboradores, $cod_pizzarias_usuario, $cod_mesas_taxas[$a], $quantidades_taxas[$a]);
    }
  }
  
}
elseif ($acao == 'transferir_produtos')
{
  $tp="r";
  $cod_pedidos_pizzas = validar_var_post('cod_pedidos_pizzas');
  $cod_pedidos_bebidas = validar_var_post('cod_pedidos_bebidas');
  $cod_pedidos_taxas = validar_var_post('cod_pedidos_taxas');
  $cod_colaboradores = validar_var_post('cod_colaboradores');

  $cod_pedidos_transferir = validar_var_post('cod_pedidos_transferir');
/*
  echo "<pre>";
  print_r($_POST);
  echo "<Br>" ;
  print_r($_SESSION['ipi_mesas']);
  echo "</pre>";
//  die();
*/
  if ( isset($_POST['cod_pedidos_bebidas']) == true)
  {
    $num_pedidos_bebidas = count($cod_pedidos_bebidas);
    for ($a = 0; $a < $num_pedidos_bebidas; $a++)
    {
      $controle_mesas->transferir_de_mesa_bebida($cod_colaboradores, $cod_pedidos_bebidas[$a], $cod_pedidos_transferir);
    }
  }

  if ( isset($_POST['cod_pedidos_pizzas']) == true)
  {
    $num_pedidos_pizzas = count($cod_pedidos_pizzas);
    for ($a = 0; $a < $num_pedidos_pizzas; $a++)
    {
      $controle_mesas->transferir_de_mesa_pizza($cod_colaboradores, $cod_pedidos_pizzas[$a], $cod_pedidos_transferir);
    }
  }

  if ( isset($_POST['cod_pedidos_taxas']) == true)
  {
    $num_pedidos_taxas = count($cod_pedidos_taxas);
    for ($a = 0; $a < $num_pedidos_taxas; $a++)
    {
      $controle_mesas->transferir_de_mesa_taxa($cod_colaboradores, $cod_pedidos_taxas[$a], $cod_pedidos_transferir);
    }
  }
//die();
}
elseif ($acao == 'cancelar_produtos')
{
  $tp="r";
  $cod_pedidos_pizzas = validar_var_post('cod_pedidos_pizzas');
  $cod_pedidos_bebidas = validar_var_post('cod_pedidos_bebidas');
  $cod_pedidos_taxas = validar_var_post('cod_pedidos_taxas');
  $cod_colaboradores = validar_var_post('cod_colaboradores');
  $usuario_revisar = validar_var_post('usuario_revisar');
  $senha_revisar = validar_var_post('senha_revisar');

/*
  echo "<pre>";
  print_r($_POST);
  echo "</pre>";
  die;
*/

  $sql_buscar_usuario = "SELECT * FROM nuc_usuarios WHERE usuario = '$usuario_revisar' AND senha = MD5('$senha_revisar') AND situacao='ATIVO' AND cod_perfis IN (1,2)";
  $res_buscar_usuario = mysql_query($sql_buscar_usuario);
  //echo $sql_buscar_usuario;
  //die();
  $num_buscar_usuario = mysql_num_rows($res_buscar_usuario);
  if ($num_buscar_usuario > 0)
  {

    if ( isset($_POST['cod_pedidos_pizzas']) == true)
    {
      $num_pedidos_pizzas = count($cod_pedidos_pizzas);
      for ($a = 0; $a < $num_pedidos_pizzas; $a++)
      {
        $controle_mesas->cancelar_pizza($cod_colaboradores, $cod_pedidos_pizzas[$a]);
      }
    }

    if ( isset($_POST['cod_pedidos_bebidas']) == true)
    {
      $num_pedidos_bebidas = count($cod_pedidos_bebidas);
      for ($a = 0; $a < $num_pedidos_bebidas; $a++)
      {
        $controle_mesas->cancelar_bebida($cod_colaboradores, $cod_pedidos_bebidas[$a]);
      }
    }

    if ( isset($_POST['cod_pedidos_taxas']) == true)
    {
      $num_pedidos_taxas = count($cod_pedidos_taxas);
      for ($a = 0; $a < $num_pedidos_taxas; $a++)
      {
        $controle_mesas->cancelar_taxa($cod_colaboradores, $cod_pedidos_taxas[$a]);
      }
    }

  }
  else
  {
    $tp="r&e=1";
    // Deu erro de autenticação, retorna sem fazer nada
  }


}
elseif ($acao == 'adicionar_bebidas')
{
  $tp="b";
  $cod_colaboradores = validar_var_post('cod_colaboradores');

  $quantidade_adicionar_bebida = validar_var_post('quantidade_adicionar_bebida');
  $bebida_promocional = validar_var_post('bebida_promocional');
  $cod_motivo_promocoes_bebida = validar_var_post('cod_motivo_promocoes_bebida');
  $cod_bebidas_conteudos = validar_var_post('cod_bebidas_conteudos');

/*
  echo "<pre>";
  print_r($_POST);
  echo "</pre>";
  die;
*/

  $controle_mesas->adicionar_bebida($cod_colaboradores, $cod_pizzarias_usuario, $cod_bebidas_conteudos, $quantidade_adicionar_bebida, $bebida_promocional, $cod_motivo_promocoes_bebida, '');
}
else if ($acao == 'adicionar_pizza')
{
  $tp="p";
/*
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';
    die();
*/
    $cod_pedidos = $controle_mesas->localizar_pedido();
    
    //echo "<br>ped: ".$cod_pedidos;

    if($_SESSION['ipi_caixa']['data_hora_inicial']=='')
    {
     $_SESSION['ipi_caixa']['data_hora_inicial'] = date("Y-m-d H:i:s");
    }

    $cod_colaboradores = validar_var_post('cod_colaboradores');
    $cod_adicionais = validaVarPost('cod_adicionais');
    $cod_tipo_massa = validaVarPost('cod_tipo_massa');
    $cod_opcoes_corte = validaVarPost('cod_opcoes_corte');
    $cod_bordas = validaVarPost('cod_bordas');
    $num_sabores = validaVarPost('num_sabores');
    $cod_tamanhos = validaVarPost('cod_tamanhos');
    $pizza_promocional = validaVarPost('pizza_promocional');
    $borda_promocional = validaVarPost('borda_promocional');
    $num_fracao = validaVarPost('num_fracao');
    
    $cod_motivo_promocoes_pizza = validaVarPost('cod_motivo_promocoes_pizza');
    $cod_motivo_promocoes_borda = validaVarPost('cod_motivo_promocoes_borda');
    
    $cod_pizzas_1 = validaVarPost('cod_pizzas_1');
    $cod_pizzas_2 = validaVarPost('cod_pizzas_2');
    $cod_pizzas_3 = validaVarPost('cod_pizzas_3');
    $cod_pizzas_4 = validaVarPost('cod_pizzas_4');
    
    $ingredientes1 = validaVarPost('ingredientes1');
    $ingredientes2 = validaVarPost('ingredientes2');
    $ingredientes3 = validaVarPost('ingredientes3');
    $ingredientes4 = validaVarPost('ingredientes4');
    
    $ingredientes_adicionais1 = validaVarPost('ingredientes_adicionais1');
    $ingredientes_adicionais2 = validaVarPost('ingredientes_adicionais2');
    $ingredientes_adicionais3 = validaVarPost('ingredientes_adicionais3');
    $ingredientes_adicionais4 = validaVarPost('ingredientes_adicionais4');
    
    $observacao_1 = validaVarPost('observacao_1');

    $observacao_2 = validaVarPost('observacao_2');
    $observacao_3 = validaVarPost('observacao_3');
    $observacao_4 = validaVarPost('observacao_4');
    $quantidade_add = validaVarPost('quantidade_adicionar');

    if ($quantidade_add=="" || $quantidade_add<=0)
    {
        $quantidade_add = 1;
    }

    for ($n=0;$n<$quantidade_add;$n++)
        {

        $indice_pizza = $controle_mesas->adicionar_pizza($cod_colaboradores, $cod_pizzarias_usuario, $cod_pizzas_1, $cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $cod_opcoes_corte, $num_sabores, $pizza_promocional, $borda_promocional, $cod_motivo_promocoes_pizza, $cod_motivo_promocoes_borda, '');
            
        //echo "<Br>cod_pizzas_1: ".$cod_pizzas_1;
        //echo "<Br>num_fracao: ".$num_fracao;
        if (($cod_pizzas_1 != '0') && ($num_fracao >= 1))
        {
            //echo "<Br>XXX ";
            $indice_fracao1 = $controle_mesas->adicionar_fracao($cod_pizzarias_usuario, $cod_pedidos, $indice_pizza, $cod_pizzas_1, $num_sabores, $cod_tamanhos, $num_fracao[0], $observacao_1);
            $num_ingredientes = count($ingredientes1);
            //echo "<Br>num_ingredientes ".$num_ingredientes;

            for ($a = 0; $a < $num_ingredientes; $a++)
            {
                if ($ingredientes1[$a] != '')
                {
                    $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao1, $ingredientes1[$a], $cod_tamanhos, $num_sabores, true);
                }
            }
            
            $num_ingredientes_adicionais = count($ingredientes_adicionais1);
            //echo "<Br>num_ingredientes_adicionais: ".$num_ingredientes_adicionais;
            for ($a = 0; $a < $num_ingredientes_adicionais; $a++)
            {

                if ($ingredientes_adicionais1[$a]!="")
                {
                    $arr_ingrediente = explode("###",$ingredientes_adicionais1[$a]);

                    $cod_ingredientes = $arr_ingrediente[1];
                    $tipo_ingrediente = $arr_ingrediente[0];
                    $cod_codigo_ingre_troca = $arr_ingrediente[2];
                    //echo "<br>TIPO: ".$tipo_ingrediente . " VVV ".$ingredientes_adicionais1[$a];

                    if($tipo_ingrediente!="TROCA")
                    {
                        $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao1, $ingredientes_adicionais1[$a], $cod_tamanhos, $num_sabores, false);
                    }
                    else
                    {
                        $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao1, $cod_ingredientes, $cod_tamanhos, $num_sabores, false,true,$cod_codigo_ingre_troca);
                    }
                }
            }
        }
        
        if (($cod_pizzas_2 != '') && ($num_fracao >= 2))
        {
            $indice_fracao2 = $controle_mesas->adicionar_fracao($cod_pizzarias_usuario, $cod_pedidos, $indice_pizza, $cod_pizzas_2, $num_sabores, $cod_tamanhos, $num_fracao[1], $observacao_2);
            $num_ingredientes = count($ingredientes2);
            
            for ($a = 0; $a < $num_ingredientes; $a++)
            {
                if ($ingredientes2[$a] != '')
                {
                    $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao2, $ingredientes2[$a], $cod_tamanhos, $num_sabores, true);
                }
            }
            
            $num_ingredientes_adicionais = count($ingredientes_adicionais2);
            
            for ($a = 0; $a < $num_ingredientes_adicionais; $a++)
            {
                if ($ingredientes_adicionais2[$a]!="")
                {
                    $arr_ingrediente = explode("###",$ingredientes_adicionais2[$a]);
                    $cod_ingredientes = $arr_ingrediente[1];
                    $tipo_ingrediente = $arr_ingrediente[0];
                    $cod_codigo_ingre_troca = $arr_ingrediente[2];
                    if($tipo_ingrediente!="TROCA")
                    {
                        $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao2, $ingredientes_adicionais2[$a], $cod_tamanhos, $num_sabores, false);
                    }
                    else
                    {
                        $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao2, $cod_ingredientes, $cod_tamanhos, $num_sabores, false,true,$cod_codigo_ingre_troca);
                    }
                } 
            }
        }

        
        
        if (($cod_pizzas_3 != '') && ($num_fracao >= 3))
        {
            $indice_fracao3 = $controle_mesas->adicionar_fracao($cod_pizzarias_usuario, $cod_pedidos, $indice_pizza, $cod_pizzas_3, $num_sabores, $cod_tamanhos, $num_fracao[2], $observacao_3);
            $num_ingredientes = count($ingredientes3);
            
            for ($a = 0; $a < $num_ingredientes; $a++)
            {
                if ($ingredientes3[$a] != '')
                {
                    $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao3, $ingredientes3[$a], $cod_tamanhos, $num_sabores, true);
                }
            }
            
            $num_ingredientes_adicionais = count($ingredientes_adicionais3);
            
            for ($a = 0; $a < $num_ingredientes_adicionais; $a++)
            {
                if ($ingredientes_adicionais3[$a]!="")
                {
                    $arr_ingrediente = explode("###",$ingredientes_adicionais3[$a]);
                    $cod_ingredientes = $arr_ingrediente[1];
                    $tipo_ingrediente = $arr_ingrediente[0];
                    $cod_codigo_ingre_troca = $arr_ingrediente[2];
                    if($tipo_ingrediente!="TROCA")
                    {
                        $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao3, $ingredientes_adicionais3[$a], $cod_tamanhos, $num_sabores, false);
                    }
                    else
                    {
                        $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao3, $cod_ingredientes, $cod_tamanhos, $num_sabores, false,true,$cod_codigo_ingre_troca);
                    }
                }
            }
        }
        
        
        
        if (($cod_pizzas_4 != '') && ($num_fracao >= 4))
        {
            $indice_fracao4 = $controle_mesas->adicionar_fracao($cod_pizzarias_usuario, $cod_pedidos, $indice_pizza, $cod_pizzas_4, $num_sabores, $cod_tamanhos, $num_fracao[3], $observacao_4);
            $num_ingredientes = count($ingredientes4);
            for ($a = 0; $a < $num_ingredientes; $a++)
            {
                if ($ingredientes4[$a] != '')
                {
                    $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao4, $ingredientes4[$a], $cod_tamanhos, $num_sabores, true);
                }
            }
            
            $num_ingredientes_adicionais = count($ingredientes_adicionais4);
            for ($a = 0; $a < $num_ingredientes_adicionais; $a++)
            {

                if ($ingredientes_adicionais4[$a]!="")
                {
                    $arr_ingrediente = explode("###",$ingredientes_adicionais4[$a]);
                    $cod_ingredientes = $arr_ingrediente[1];
                    $tipo_ingrediente = $arr_ingrediente[0];
                    $cod_codigo_ingre_troca = $arr_ingrediente[2];
                    if($tipo_ingrediente!="TROCA")
                    {
                        $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao4, $ingredientes_adicionais4[$a], $cod_tamanhos, $num_sabores, false);
                    }
                    else
                    {
                        $controle_mesas->adicionar_ingrediente($cod_pedidos, $indice_pizza, $indice_fracao4, $cod_ingredientes, $cod_tamanhos, $num_sabores, false,true,$cod_codigo_ingre_troca);
                    }
                }    
            }
        }
    }
    
    
}
header('Location: ipi_controle_mesas.php?tp='.$tp);
die();
?>