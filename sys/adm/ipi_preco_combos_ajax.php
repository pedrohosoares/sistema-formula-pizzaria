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
$cod_combos = validaVarPost('combo');//AND c.cod_combos = $cod_combos
switch ($acao)
{
	case 'gerar_imagem':
		//header("Content-type: image/png");
/*
################### ATENÇÂO #######################
imagettftext <== FONTES TRUE TYPE COM ACENTO
imagefttext <== PROBLEMAS NO ACENTOS
################### ATENÇÂO #######################
*/
	  $con = conectabd();

		$sql_busca_combos = "SELECT * FROM ipi_combos c inner join ipi_combos_pizzarias cop on c.cod_combos = cop.cod_combos where c.situacao='ATIVO' order by ordem_combo";
		$res_busca_combos = mysql_query($sql_busca_combos);
		while ($obj_busca_combos = mysql_fetch_object($res_busca_combos))
		{
			$sql_busca_produtos = 'Select cp.preco,cp.tipo,tpi.tipo_pizza as sabor,t.tamanho,cp.cod_tamanhos,cp.quantidade,co.conteudo,co.cod_conteudos from ipi_combos_produtos cp left join ipi_tamanhos t on t.cod_tamanhos = cp.cod_tamanhos left join ipi_conteudos co on co.cod_conteudos = cp.cod_conteudos left join ipi_tipo_pizza tpi on tpi.cod_tipo_pizza = cp.sabor where cp.cod_combos='.$obj_busca_combos->cod_combos.' order by cp.cod_combos_produtos';
			//echo "\n\n".$sql_busca_produtos;
			$res_busca_produtos = mysql_query($sql_busca_produtos);
			$bordas = "";
			$bebidas = "";
			$de=0;
			$ec = 0;
			$arr_tamanhos_sabor = array();
			$arr_conteudo_bebida= array();
			$bordas= array();
			$linhas=0;
			while($obj_busca_produtos = mysql_fetch_object($res_busca_produtos))
			{

				if($obj_busca_produtos->tipo=="PIZZA")
				{
					$arr_tamanhos_sabor[$obj_busca_produtos->tamanho][] = $obj_busca_produtos->sabor;
					$sql_busca_maior_preco = "SELECT ipit.preco from ipi_pizzas_ipi_tamanhos ipit INNER JOIN ipi_pizzas ip ON (ip.cod_pizzas = ipit.cod_pizzas) where ipit.cod_pizzarias = ".$obj_busca_combos->cod_pizzarias." and ipit.cod_tamanhos = ".$obj_busca_produtos->cod_tamanhos." AND ip.cod_tipo_pizza = '".$obj_busca_produtos->sabor."' order by ipit.preco DESC LIMIT 1";
					$res_busca_maior_preco = mysql_query($sql_busca_maior_preco);
					$obj_busca_maior_preco = mysql_fetch_object($res_busca_maior_preco);

					$de += $obj_busca_maior_preco->preco;
	/*
					echo "\n\n".$sql_busca_maior_preco;
					echo "<pre>";
					print_r($arr_tamanhos_sabor);
					echo "</pre>";
					echo "\n\n".$conteudo;
	*/
					$conteudo .= $obj_busca_produtos->quantidade." ".$tamanho_pizza." ".$obj_busca_produtos->sabor;
				}

				if($obj_busca_produtos->tipo=="BEBIDA")
				{
					$arr_conteudo_bebida[$obj_busca_produtos->conteudo] ++; //`
					$sql_busca_maior_preco = "select cp.preco from ipi_bebidas_ipi_conteudos bc inner join ipi_conteudos_pizzarias cp on cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos where bc.cod_conteudos = ".$obj_busca_produtos->cod_conteudos." and cp.cod_pizzarias = ".$obj_busca_combos->cod_pizzarias." order by cp.preco DESC LIMIT 1";
					$res_busca_maior_preco = mysql_query($sql_busca_maior_preco);
					$obj_busca_maior_preco = mysql_fetch_object($res_busca_maior_preco);
					$de += $obj_busca_maior_preco->preco;
		
				}

				if($obj_busca_produtos->tipo=="BORDA")
				{
					$bordas[] .= $obj_busca_produtos->quantidade." Borda recheada";
					$sql_busca_maior_preco = "select preco from ipi_tamanhos_ipi_bordas where cod_tamanhos = 3 and cod_pizzarias = ".$obj_busca_combos->cod_pizzarias." order by preco DESC LIMIT 1";
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
			$cor1 = imagecolorallocate($im, 0, 0, 0);	
			$orange = imagecolorallocate($im, 140, 38, 38);
			$orange = '0x00ffc500';
	  	////////////////////////////////////////////
	  	$x = 0;
	  	// $arr_titulo = imagefttext($im, 20, 0, 20, 50, $cor1, $audimat_negrito, strtoupper($obj_busca_combos->nome_combo));

		  //echo "<br />XX <pre>".print_r($arr_tamanhos_sabor)." </pre><br />";
	/*
			foreach($arr_tamanhos_sabor as $tamanho)
			{
				$arr_teste =  array_keys($arr_tamanhos_sabor);
				$tamanho_pizza_arr = explode(',',$arr_teste[$x]);
				foreach ($tamanho as $i => $sabor) 
				{

					$sabor_tamanho = $sabor.$arr_teste[$x]; //o propio sabor mais o tamanho dele 
					$$sabor_tamanho++;
				}
				
				$tamanho_pizza = $tamanho_pizza_arr[0];  

				foreach ($tamanho as $i => $sabor) 
				{
					$sabor_tamanho = $sabor.$arr_teste[$x];
					if($$sabor_tamanho>0)
					{
						if($$sabor_tamanho>=2)
						{
							$texto = $$sabor_tamanho." ".trim($tamanho_pizza)."s ".$sabor;
						}
						else
						{
							$texto = $$sabor_tamanho." ".trim($tamanho_pizza)."s ".$sabor;
						}
						imagefttext($im, $tamanho_fonte, 0, 20, (90 +$linhas*($tamanho_fonte*1.5)), $cor1, $myriad, $texto);
						$linhas++;
					}
					$$sabor_tamanho = 0; //reseto o valor para o proximo combo
				}

				$x++;
	 		}
		  //echo "<br />YY <pre>".print_r($arr_conteudo_bebida)." </pre><br />";
	 		 
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
	 			imagefttext($im, $tamanho_fonte, 0,  20,( 90 +$linhas*($tamanho_fonte*1.5)), $cor1, $myriad, $texto);
				$linhas++;
	 		}
	 		
	 		foreach($bordas as $cont)
			{
	 			$texto = $cont;
	 			imagefttext($im, $tamanho_fonte, 0,  100,( 100 +$linhas*($tamanho_fonte*1.5)), $cor1, $myriad, $texto);
				$linhas++;
	 		}
	*/
 			// $texto = $obj_busca_combos->descricao_combo;
 			// imagettftext($im, $tamanho_fonte, 0,  20,( 90 +$linhas*($tamanho_fonte*1.5)), $cor1, $audimat, $texto);
	 		
	 		$orange = imagecolorallocate($im, 140, 38, 38);
			$linhast = 0;
			//$texto =  "de R$".bd2moeda($de);
			//imagefttext($im, $tamanho_fonte2, 0,  330, (65 +$linhast*($tamanho_fonte2*1.5)), $cor1, $myriad, $texto);
			//$linhast++;
			// $texto =  "por"; 
			// imagefttext($im, $tamanho_fonte, 0,  20, (165), $cor1, $myriad_negrito, $texto);// +$linhast*($tamanho_fonte2*1.5)
			$texto = "R$ ".bd2moeda($obj_busca_combos->preco);
			imagefttext($im, $tamanho_fonte3, 0,  235, (200), $cor1, $myriad_negrito, $texto);// +$linhast*($tamanho_fonte2*1.5)
			$linhast++;
			//$linhast++;
			//$texto = "Economize ate' ";
			//imagefttext($im, 16, 0,  330, (75 +$linhast*($tamanho_fonte2*1.5)), $cor1, $myriad, $texto);
			//$linhast++;
			//$ec = $de - $obj_busca_combos->preco;
			//$texto = "R$";
			//imagefttext($im, $tamanho_fonte3, 0, 330, (75 +$linhast*($tamanho_fonte2*1.5)), $cor1, $myriad_negrito, $texto);
			//$texto = bd2moeda($ec)."*";
			//imagefttext($im, $tamanho_fonte3, 0, 380, (75 +$linhast*($tamanho_fonte2*1.5)), $orange, $myriad_negrito, $texto);
			//$linhast++;
			$orange = imagecolorallocate($im, 140, 38, 38);

			//SUBLINHADO		// imageline($im, $arr_titulo[0], $arr_titulo[1], $arr_titulo[2], $arr_titulo[3], $orange);
			//SUBLINHADO		// imageline($im, ($arr_titulo[0]), $arr_titulo[1]+1, $arr_titulo[2], $arr_titulo[3]+1, $orange);
			//SUBLINHADO		// imageline($im, ($arr_titulo[0]), $arr_titulo[1]+2, ($arr_titulo[2]), $arr_titulo[3]+2, $orange);

			/*imageline($im, 300, 65, 300, 190, $orange);
			imageline($im, 301, 65, 301, 190, $orange);
			imageline($im, 302, 65, 302, 190, $orange);*/
			imagepng($im,UPLOAD_DIR."/combos/".$obj_busca_combos->cod_pizzarias."p_".$obj_busca_combos->cod_combos."_combo_final.png",0);
			imagedestroy($im);

			$sql_atualiza = "UPDATE ipi_combos_pizzarias set imagem_final = '".$obj_busca_combos->cod_pizzarias."p_".$obj_busca_combos->cod_combos."_combo_final.png' where cod_combos=".$obj_busca_combos->cod_combos." and cod_pizzarias = ".$obj_busca_combos->cod_pizzarias ;
			$res_atualiza = mysql_query($sql_atualiza);
			
			
		}
		desconectabd($con);
		echo "OK";
	break;
}
?>
