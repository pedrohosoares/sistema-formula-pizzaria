<?php

/**
 * ipi_cupom_ajax.php: Cadastro de Cupom (Ajax)
 * 
 * Índice: cod_cupons
 * Tabela: ipi_cupons
 */

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

switch ($acao)
{
    case 'carregar_produto':
        $con = conectabd();
        
        echo '<option value=""></option>';
        
        $produto = validaVarPost('produto');
        
        if ($produto == 'PIZZA')
        {
            $sqlBuscaPizza = "SELECT * FROM ipi_pizzas ORDER BY pizza";
            $resBuscaPizza = mysql_query($sqlBuscaPizza);
            
            while ($objBuscaPizza = mysql_fetch_object($resBuscaPizza))
            {
                echo '<option value="' . $objBuscaPizza->cod_pizzas . '">' . utf8_encode(bd2texto($objBuscaPizza->pizza)) . '</option>';
            }
        }
        else if ($produto == 'BORDA')
        {
            $sqlBuscaBorda = "SELECT * FROM ipi_bordas ORDER BY borda";
            $resBuscaBorda = mysql_query($sqlBuscaBorda);
            
            while ($objBuscaBorda = mysql_fetch_object($resBuscaBorda))
            {
                echo '<option value="' . $objBuscaBorda->cod_bordas . '">' . utf8_encode(bd2texto($objBuscaBorda->borda)) . '</option>';
            }
        }
        else if ($produto == 'BEBIDA')
        {
            //$sqlBuscaBebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) ORDER BY b.bebida";
            $sqlBuscaBebidas = "SELECT * FROM ipi_conteudos ORDER BY conteudo";
            $resBuscaBebidas = mysql_query($sqlBuscaBebidas);
            
            while ($objBuscaBebidas = mysql_fetch_object($resBuscaBebidas))
            {
                echo '<option value="' . $objBuscaBebidas->cod_conteudos . '">' . $objBuscaBebidas->conteudo . '</option>';
            }
        }
        
        desconectabd($con);
        break;
    case 'carregar_tamanho':
        $con = conectabd();
        
        echo '<option value=""></option>';
        
        $produto = validaVarPost('produto');
        $cod_produtos = (validaVarPost('cod_produtos')) ? validaVarPost('cod_produtos') : 0;
        
        if ($cod_produtos <= 0)
        {
            $sqlBuscaTamanho = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
            $resBuscaTamanho = mysql_query($sqlBuscaTamanho);
            
            while ($objBuscaTamanho = mysql_fetch_object($resBuscaTamanho))
            {
                echo '<option value="' . $objBuscaTamanho->cod_tamanhos . '">' . utf8_encode(bd2texto($objBuscaTamanho->tamanho)) . '</option>';
            }
        }
        else if ($produto == 'PIZZA')
        {
            $sqlBuscaTamanho = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt INNER JOIN ipi_pizzas p ON (pt.cod_pizzas = p.cod_pizzas) INNER JOIN ipi_tamanhos t ON (pt.cod_tamanhos = t.cod_tamanhos) WHERE p.cod_pizzas = $cod_produtos ORDER BY pizza";
            $resBuscaTamanho = mysql_query($sqlBuscaTamanho);
            
            while ($objBuscaTamanho = mysql_fetch_object($resBuscaTamanho))
            {
                echo '<option value="' . $objBuscaTamanho->cod_tamanhos . '">' . utf8_encode(bd2texto($objBuscaTamanho->tamanho)) . '</option>';
            }
        }
        else if ($produto == 'BORDA')
        {
            $sqlBuscaTamanho = "SELECT * FROM ipi_tamanhos_ipi_bordas bt INNER JOIN ipi_bordas b ON (bt.cod_bordas = b.cod_bordas) INNER JOIN ipi_tamanhos t ON (bt.cod_tamanhos = t.cod_tamanhos) WHERE b.cod_bordas = $cod_produtos ORDER BY borda";
            $resBuscaTamanho = mysql_query($sqlBuscaTamanho);
            
            while ($objBuscaTamanho = mysql_fetch_object($resBuscaTamanho))
            {
                echo '<option value="' . $objBuscaTamanho->cod_tamanhos . '">' . utf8_encode(bd2texto($objBuscaTamanho->tamanho)) . '</option>';
            }
        }
        
        desconectabd($con);
        break;
    case 'gerar_imagem':
	  		header("Content-type: image/png");

			  $con = conectabd();
				$cod_combos = validaVarPost('combo');
				$sql_busca_combos = "SELECT * FROM ipi_combos c where c.situacao='ATIVO' AND c.cod_combos = $cod_combos order by ordem_combo";
			
				$res_busca_combos = mysql_query($sql_busca_combos);
				while ($obj_busca_combos = mysql_fetch_object($res_busca_combos))
				{

					$sql_busca_produtos = 'Select cp.preco,cp.tipo,cp.sabor,t.tamanho,cp.cod_tamanhos,cp.quantidade,co.conteudo,co.cod_conteudos from ipi_combos_produtos cp left join ipi_tamanhos t on t.cod_tamanhos = cp.cod_tamanhos left join ipi_conteudos co on co.cod_conteudos = cp.cod_conteudos where cp.cod_combos='.$obj_busca_combos->cod_combos." order by cp.cod_combos_produtos";
					$res_busca_produtos = mysql_query($sql_busca_produtos);
					$bordas = "";
					$bebidas = "";
					$de=0;
					$ec = 0;
					$arr_tamanhos_sabor = array();
					$arr_conteudo_bebida= array();
					$bordas= array();
					$linha=0;
					while($obj_busca_produtos = mysql_fetch_object($res_busca_produtos))
					{
			
						if($obj_busca_produtos->tipo=="PIZZA")
						{
							$arr_tamanhos_sabor[$obj_busca_produtos->tamanho][] = $obj_busca_produtos->sabor;
							$sql_busca_maior_preco = "select preco from ipi_pizzas_ipi_tamanhos where cod_tamanhos = ".$obj_busca_produtos->cod_tamanhos." order by preco DESC LIMIT 1";
							$res_busca_maior_preco = mysql_query($sql_busca_maior_preco);
							$obj_busca_maior_preco = mysql_fetch_object($res_busca_maior_preco);
			
							$de += $obj_busca_maior_preco->preco;
							//$conteudo .= $obj_busca_produtos->quantidade." ".$tamanho_pizza." ".$obj_busca_produtos->sabor;
						}
			
						if($obj_busca_produtos->tipo=="BEBIDA")
						{
							$arr_conteudo_bebida[$obj_busca_produtos->conteudo] ++; //`
							$sql_busca_maior_preco = "select cp.preco from ipi_bebidas_ipi_conteudos bc inner join ipi_conteudos_pizzarias cp on cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos where bc.cod_conteudos = ".$obj_busca_produtos->cod_conteudos." order by cp.preco DESC LIMIT 1";
							$res_busca_maior_preco = mysql_query($sql_busca_maior_preco);
							$obj_busca_maior_preco = mysql_fetch_object($res_busca_maior_preco);
							$de += $obj_busca_maior_preco->preco;
				
						}
			
						if($obj_busca_produtos->tipo=="BORDA")
						{
							$bordas[] .= $obj_busca_produtos->quantidade." Borda recheada";
							$sql_busca_maior_preco = "select preco from ipi_tamanhos_ipi_bordas where cod_tamanhos = 3 order by preco DESC LIMIT 1";
							$res_busca_maior_preco = mysql_query($sql_busca_maior_preco);
							$obj_busca_maior_preco = mysql_fetch_object($res_busca_maior_preco);
							$de += $obj_busca_maior_preco->preco;
				
						}
			
					}
					$im_o= imagecreatefrompng(UPLOAD_DIR."/combos/".$obj_busca_combos->imagem_p);
					$h = imagesy($im_o);
					$w = imagesx($im_o);
					$im = imagecreatetruecolor($w,$h);
					imagecopyresampled($im,$im_o,0,0,0,0, $w,$h,$w,$h);

					
					
					//////////////////FONTE//////////////////////
					$audimat = '../../fontes/AUdimat-Regular.ttf';//MyriadPro-Bold.otf    MyriadPro-Regular.otf
					$audimat_negrito = '../../fontes/AUdimat-Bold.ttf';
					$myriad = '../../fontes/MyriadPro-Regular.otf';
					$myriad_negrito = '../../fontes/MyriadPro-Bold.otf';
					$tamanho_fonte = 14;
					$tamanho_fonte2 = 22;
					$tamanho_fonte3 = 24;
					///////////////////////////////////////////////
					
					//////////////////CORES/////////////////
					$white = imagecolorallocate($im, 255, 255, 255);	
					$orange = imagecolorallocate($im, 255, 169, 0);
					$orange = '0x00ffc500';
			  	////////////////////////////////////////////
			  	$x = 0;
					foreach($arr_tamanhos_sabor as $tamanho)
					{
						$cont_salg =0;
						$cont_doce =0;
				
						foreach ($tamanho as $i => $sabor) 
						{
							if($sabor=="Salgado")
								$cont_salg++;
					
							if($sabor=="Doce")
								$cont_doce++;
						}
						$arr_teste =  array_keys($arr_tamanhos_sabor);
					 // echo $arr_teste[0];
					 // echo "<br /> <pre>".print_r($arr_tamanhos_sabor)." </pre><br />";
						$tamanho_pizza_arr = explode('(',$arr_teste[$x]);
		//380 50
						imagefttext($im, 20, 0, 380, 100, $white, $audimat_negrito, strtoupper($obj_busca_combos->nome_combo));
						
						$tamanho_pizza = $tamanho_pizza_arr[0];  
						if($cont_salg>0)
						{
							if($cont_salg>=2)
							{
								$texto = $cont_salg." ".trim($tamanho_pizza)."s Salgada";
							}else
							{
								$texto = $cont_salg." ".trim($tamanho_pizza)." Salgada";
							}
							//imagestring($im, $fonte1, 380, (100 +$linhas*imagefontheight($fonte1)) , $texto, $white);
							imagefttext($im, $tamanho_fonte, 0, 380, (130 +$linhas*($tamanho_fonte*1.5)), $white, $myriad, $texto);
							$linhas++;
						}
		
						if($cont_doce>0)
						{
							
							if($cont_doce>=2)
							{
								$texto = $cont_doce." ".trim($tamanho_pizza)."s Doce";
							}
							else
							{
								$texto = $cont_doce." ".trim($tamanho_pizza)." Doce";
							}
							
							imagefttext($im, $tamanho_fonte, 0,  380, (130 +$linhas*($tamanho_fonte*1.5)), $white, $myriad, $texto);
							$linhas++;
						}
						$x++;
			 		 }
			 		 
			 		foreach($arr_conteudo_bebida as $cont => $qtd)
					{
						if($qtd>=2)
						{
			 				$texto = $qtd." Refrigerantes ".$cont;
			 			}
			 			else
			 			{
			 				$texto = $qtd." Refrigerante ".$cont;
			 			}
			 			imagefttext($im, $tamanho_fonte, 0,  380,( 130 +$linhas*($tamanho_fonte*1.5)), $white, $myriad, $texto);
						$linhas++;
			 		}
			 		
			 		foreach($bordas as $cont)
					{
			 			$texto = $cont;
			 			imagefttext($im, $tamanho_fonte, 0,  380,( 130 +$linhas*($tamanho_fonte*1.5)), $white, $myriad, $texto);
						$linhas++;
			 		}
			 		
			 		$orange = imagecolorallocate($im, 255, 197, 0);
					$linhast = 0;
					$texto =  "de R$".bd2moeda($de);
					imagefttext($im, $tamanho_fonte2, 0,  630, (65 +$linhast*($tamanho_fonte2*1.5)), $white, $myriad, $texto);
					$linhast++;
					$texto =  "por"; 
					imagefttext($im, $tamanho_fonte2, 0,  630, (65 +$linhast*($tamanho_fonte2*1.5)), $white, $myriad_negrito, $texto);
					$texto = "R$ ".bd2moeda($obj_busca_combos->preco);
					imagefttext($im, $tamanho_fonte2, 0,  680, (65 +$linhast*($tamanho_fonte2*1.5)), $orange, $myriad_negrito, $texto);
					$linhast++;
					//$linhast++;
					$texto = "Economize ate' ";
					imagefttext($im, 16, 0,  630, (75 +$linhast*($tamanho_fonte2*1.5)), $white, $myriad, $texto);
					$linhast++;
					$ec = $de - $obj_busca_combos->preco;
					$texto = "R$";
					imagefttext($im, $tamanho_fonte3, 0, 630, (75 +$linhast*($tamanho_fonte2*1.5)), $white, $myriad_negrito, $texto);
					$texto = bd2moeda($ec)."*";
					imagefttext($im, $tamanho_fonte3, 0, 680, (75 +$linhast*($tamanho_fonte2*1.5)), $orange, $myriad_negrito, $texto);
					$linhast++;
					$orange = imagecolorallocate($im, 255, 197, 0);
					imageline($im, 600, 65, 600, 190, $orange);
					imageline($im, 601, 65, 601, 190, $orange);
					imageline($im, 602, 65, 602, 190, $orange);
					imagepng($im,UPLOAD_DIR."/combos/".$obj_busca_combos->cod_combos."_combo_final.png",0);
					imagedestroy($im);
					
					$sql_atualiza = "UPDATE ipi_combos set imagem_final = '".$obj_busca_combos->cod_combos."_combo_final.png' where cod_combos=".$obj_busca_combos->cod_combos;
					$res_atualiza = mysql_query($sql_atualiza);
					desconectabd($con);
					
				}

    		echo "OK";
    		break;
}
?>
