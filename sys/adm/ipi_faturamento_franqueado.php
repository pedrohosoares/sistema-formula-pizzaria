<?php

/**
 * Tela para listar e exibir o ganho da Franquadora com as franquias.
 *
 * @version 2.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       08/10/2012   PEDRO H       Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Faturamento com as franquias');

$tabela = 'ipi_pizzarias';
$chave_primaria = 'cod_pizzarias';

?>
<script type="text/javascript">

</script>


<div class="painelTab">
<!--barralateralaqui-->

<br>

    <?

        $taxa_carencia = 280;
        $taxa_normal = 280;
        $pizzaria_ref = 0;
        $con = conectar_bd();
        $meses = array('Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez');
        $mtz_valores_meses = array();

        $sql_pizzarias = "SELECT nome, MONTH(data_inauguracao) as month, YEAR(data_inauguracao) as year, data_inauguracao FROM $tabela WHERE situacao = 'ATIVO' ORDER BY data_inauguracao";
        $res_pizzarias = mysql_query($sql_pizzarias);

        $aux = 0;
        while($obj_pizzarias = mysql_fetch_object($res_pizzarias))
        {
       
            $mtz_valores_meses[$aux]['nome'] = $obj_pizzarias->nome;
            if($obj_pizzarias->year && $obj_pizzarias->month)
            {
              $data_inicial = $obj_pizzarias->year.'-'.$obj_pizzarias->month.'-01';
              $data_limite = ( strtotime('+12 months', time() ) );
              $i = 0;

              while ( ( strtotime($data_inicial.'+'.$i.' month') < $data_limite ) )
              {
                //echo $i.'<br>';
                //echo ( strtotime($data.'+'.$i.' month') ).'<br>';
                //echo date('y-m-d', ( strtotime($data.'+'.$i.' month') ) ).'<br>';
                $data_pronta = $meses[ ( date('m', ( strtotime($data_inicial.'+'.$i.' month') ) ) - 1) ].'/'.date('y', ( strtotime($data_inicial.'+'.$i.' month') ) );

                $mtz_valores_meses[$aux][$data_pronta] = ($i < 7 ? $taxa_carencia : $taxa_normal);
                $i++;
              }
            }
            else
            {
              $pizzaria_ref++;
            }
            $aux++;

        }
        ?>

        <style type="text/css">
          .tabela_meses
          {
            overflow: auto;
            width: 100%;
            margin: 0 auto;
            padding: 10px 0;
          }

          .listaEdicao tbody tr td.destaque
          {
            background-color: #FAD7AF;
          }

          .listaEdicao tbody tr td.total 
          {
            background-color: #E5E5E5;
            font-weight: bold;
          }
        </style>

        <div class='tabela_meses'>
          <table class="listaEdicao" cellpadding="0" cellspacing="0">
            <thead>    
              <tr>
                <td align='center' width='200'><? echo ucfirst(TIPO_EMPRESA)?></td>

                <?     
                  foreach ($mtz_valores_meses[$pizzaria_ref] as $mes => $valor) 
                  {
                      echo ($mes != "nome" ? '<td align="center" width="50">'. $mes .'</td>': "");
                  }
                ?>

              </tr>
            </thead>
            <tbody>  

              <?
                $arr_total_meses = array();
                $a=0;
                foreach ($mtz_valores_meses as $pizzaria => $conteudo) 
                {       
                  echo '<tr>';
                  $cod_fundo_comentario = ($a%2 == 0) ? '#FFF' : '#EFEFEF' ;

                    foreach ($mtz_valores_meses[$pizzaria_ref] as $mes_ref => $valor_ref) 
                    {
                      $data = $mes_ref;
                      if($conteudo[$data])
                      {
                        if($conteudo[$data] == $taxa_carencia || $conteudo[$data] == $taxa_normal)
                          $arr_total_meses[$data] += $conteudo[$data];
                        else
                          $arr_total_meses[$data]  = "Total";
                        echo '  <td align="center" '.( $conteudo[$data] == $taxa_carencia ? "class='destaque'" : "style=\"background-color:".$cod_fundo_comentario."\"" ).'>'.$conteudo[$data].'</td>';
                      }
                      else
                      {
                        echo '  <td align="center" style="background-color:'.$cod_fundo_comentario.'"> - </td>';
                      }

                    }
                  echo '</tr>';
                  $a++;
                }
                echo '<tr>';
                foreach ($arr_total_meses as $data => $valor) 
                {
                  echo '  <td align="center" class="total">'.$valor.'</td>';
                }

                echo '</tr>';
              ?>

            </tbody>
          </table>
        </div>

    <?
        //echo '<pre>';
        //print_r($arr_total_meses);
        //echo '</pre>';

        desconectar_bd($con);
    ?>

<br>

<!--barralateralaqui-->
</div>


<? rodape(); ?>
