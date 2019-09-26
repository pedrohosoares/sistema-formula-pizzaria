function Mascara(objeto, evt, mask) 
	{
	var LetrasU = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var LetrasL = 'abcdefghijklmnopqrstuvwxyz';
	var Letras  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	var Numeros = '0123456789';
	var Fixos  = '().-:/ '; 
	var Charset = " !\"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_/`abcdefghijklmnopqrstuvwxyz{|}~";

	evt = (evt) ? evt : (window.event) ? window.event : "";
	var value = objeto.value;
	if (evt) 
		{
		var ntecla = (evt.which) ? evt.which : evt.keyCode;
		tecla = Charset.substr(ntecla - 32, 1);
		if (ntecla < 32) 
		 	return true;

		var tamanho = value.length;
		if (tamanho >= mask.length) 
			return false;

		var pos = mask.substr(tamanho,1); 
 		while (Fixos.indexOf(pos) != -1) 
			{
			value += pos;
			tamanho = value.length;
			if (tamanho >= mask.length) 
				return false;
			pos = mask.substr(tamanho,1);
			}

		 switch (pos) 
		 	{
		   	case '#' : if (Numeros.indexOf(tecla) == -1) return false; break;
		   	case 'A' : if (LetrasU.indexOf(tecla) == -1) return false; break;
		   	case 'a' : if (LetrasL.indexOf(tecla) == -1) return false; break;
		   	case 'Z' : if (Letras.indexOf(tecla) == -1) return false; break;
		   	case '*' : objeto.value = value; return true; break;
		   	default : return false; break;
		 	}
		}
	objeto.value = value; 
	return true;
	}

/*
"#" - Numeros
"A" - Letras UpperCase
"a" - Letras LowerCase
"Z" - Letras
"*" - Qualquer Caracter
"/", ".", "-", " ", ":" - Caracteres Fixos
onkeypress="return MaskCPF(this, event)" 
onkeypress="return MaskTelefone(this, event)" 
*/

function MascaraCEP(objeto, evt) 
	{ 
	return Mascara(objeto, evt, '#####-###');
	}

function MascaraTelefone(objeto, evt) 
{ 
	var tel_limpo = objeto.value.replace('(','').replace(')','').replace(' ','').replace('-','');
	if(tel_limpo.length > 3)
	{
	  if ( parseInt(tel_limpo[2]) > 5) 
	  {
	    return Mascara(objeto, evt, '(##) #####-####');
	  }
	  else
	  {	
	    return Mascara(objeto, evt, '(##) ####-####');
	  }
	}
	else
	{
	  return Mascara(objeto, evt, '(##) ####-####');
	}
}

function MascaraCPF(objeto, evt) 
	{ 
	return Mascara(objeto, evt, '###.###.###-##');
	}
	
function MascaraCPFNumero(objeto, evt) 
  { 
  return Mascara(objeto, evt, '###########');
  }

function MascaraCNPJ(objeto, evt) 
	{ 
	return Mascara(objeto, evt, '##.###.###/####-##');
	}

function MascaraCNPJNumero(objeto, evt) 
  { 
  return Mascara(objeto, evt, '##############');
  }

function MascaraInsEst(objeto, evt) 
	{ 
	return Mascara(objeto, evt, '###.###.###.###');
	}

function MascaraData(objeto, evt) 
	{ 
	return Mascara(objeto, evt, '##/##/####');
	}

function MascaraDataTraco(objeto, evt) 
	{ 
	return Mascara(objeto, evt, '##-##-####');
	}

function MascaraHora(objeto, evt) 
	{ 
	return Mascara(objeto, evt, '##:##');
	}

function MascaraDataHora(objeto, evt) 
  { 
  return Mascara(objeto, evt, '##/##/#### ##:##');
  }

function MascaraDataHoraTraco(objeto, evt) 
	{ 
	return Mascara(objeto, evt, '##-##-#### ##:##');
	}

function MascaraValidadeCartao(objeto, evt) 
	{ 
	return Mascara(objeto, evt, '##/####');
	}

function ValidarMoeda(e)
	{
	if (document.all)
		var tecla = event.keyCode;
	else 
	if(document.layers)
		var tecla = e.which;
	if ((tecla > 47 && tecla < 58)||(tecla==44))
		return true;
	else
		{
		if (tecla != 8)
			event.keyCode = 0;
		else
			return true;
		}
	}

function ValidarNumeros(e)
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

function ValidarCNPJ(CNPJ) 
	{
	erro = new String;
	if (CNPJ.length < 18) erro += "E' necessarios preencher corretamente o numero do CNPJ! \n\n";
	if ((CNPJ.charAt(2) != ".") || (CNPJ.charAt(6) != ".") || (CNPJ.charAt(10) != "/") || (CNPJ.charAt(15) != "-"))
		{
		if (erro.length == 0) 
			erro += "E' necessarios preencher corretamente o numero do CNPJ! \n\n";
		}
	if(document.layers && parseInt(navigator.appVersion) == 4)
		{
		x = CNPJ.substring(0,2);
		x += CNPJ.substring(3,6);
		x += CNPJ.substring(7,10);
		x += CNPJ.substring(11,15);
		x += CNPJ.substring(16,18);
		CNPJ = x;	
		} 
	else 
		{
		CNPJ = CNPJ.replace(".","");
		CNPJ = CNPJ.replace(".","");
		CNPJ = CNPJ.replace("-","");
		CNPJ = CNPJ.replace("/","");
		}
	var nonNumbers = /\D/;
	if (nonNumbers.test(CNPJ)) 
		erro += "A verificacao de CNPJ suporta apenas numeros! \n\n";	
	var a = [];
	var b = new Number;
	var c = [6,5,4,3,2,9,8,7,6,5,4,3,2];
	for (i=0; i<12; i++)
		{
		a[i] = CNPJ.charAt(i);
		b += a[i] * c[i+1];
		}
	if ((x = b % 11) < 2) 
		{ 
		a[12] = 0 
		} 
	else 
		{ 
		a[12] = 11-x 
		}
	b = 0;
	for (y=0; y<13; y++) 
		{
		b += (a[y] * c[y]); 
		}
	if ((x = b % 11) < 2) 
		{ 
		a[13] = 0; 
		} 
	else 
		{ 
		a[13] = 11-x; 
		}
	if ((CNPJ.charAt(12) != a[12]) || (CNPJ.charAt(13) != a[13]))
		{
		erro +="Digito verificador com problema!";
		}
	if (erro.length > 0)
		{
		//alert(erro);
		return false;
		} 
	return true;
	}
	
function ValidarCPF(cpf)
	{
	var i;
	
	cpf = cpf.replace(".", ""); 
	cpf = cpf.replace(".", ""); 
	cpf = cpf.replace("/", ""); 
	cpf = cpf.replace("-", ""); 
	
	var c = cpf.substr(0,9); 
	var dv = cpf.substr(9,2);
	 
	if ((cpf == "00000000000")||(cpf == "11111111111")||(cpf == "22222222222")||(cpf == "33333333333")||(cpf == "44444444444")||(cpf == "55555555555")||(cpf == "66666666666")||(cpf == "77777777777")||(cpf == "88888888888")||(cpf == "99999999999"))
		return false;
	  
	var d1 = 0; 
	for (i = 0; i < 9; i++) 
	{ 
	d1 += c.charAt(i)*(10-i); 
	} 
	d1 = 11 - (d1 % 11); 
	if (d1 > 9) d1 = 0; d2 = d1 * 2; 
	   for (i = 0; i < 9; i++) 
		 { 
		 d2 += c.charAt(i)*(11-i); 
		 } d2 = 11 - (d2 % 11); 
	if (d2 > 9) d2 = 0; 
		if (dv.charAt(0) != d1 || dv.charAt(1) != d2) 
		  { 
		  return false; 
		  } 
	return true; 
	}

function validarEmail(email) {
  if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email))) {
	return false;
  }
  
  return true;
}

function validarTelefone(telefone) {
  if ((!(/\(\d{2}\) \d{4}-\d{4}/.test(telefone))) && (!(/\(\d{2}\) \d{5}-\d{4}/.test(telefone)))) {
	return false;
  }
  
  return true;
}


function bd2moeda(num){
  x = 0;
  
  if(num < 0) { num = Math.abs(num);   x = 1; }
  
  if(isNaN(num)) num = "0";
  cents = Math.floor((num*100+0.5)%100);
    num = Math.floor((num*100+0.5)/100).toString();
 
  if(cents < 10) cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
  num = num.substring(0,num.length-(4*i+3))+'.'+num.substring(num.length-(4*i+3));
                 
  ret = num + ',' + cents; 
  if (x == 1) 
  ret = '-' + ret;
                
  return ret;
}

// TODO Adicionar a tecla TAB
function ApenasNumero(e) {
  navegador = /msie/i.test(navigator.userAgent);

  if (navegador)
    var tecla = event.keyCode;
  else
    var tecla = e.which;

  if(tecla > 47 && tecla < 58) // numeros de 0 a 9
    return true;
  else 
	{
    if ((tecla != 8)&&(tecla != 0)) // backspace
      return false;
    else
      return true;
  	}
}


//Formatação de moeda
//Utilizar: onKeyPress="return formataMoeda(this, '.', ',', event)"
function formataMoeda(objTextBox, SeparadorMilesimo, SeparadorDecimal, e)
{
    var sep = 0;
    var key = '';
    var i = j = 0;
    var len = len2 = 0;
    var strCheck = '0123456789';
    var aux = aux2 = '';
    
    var whichCode = (e.which) ? e.which : e.keyCode;
    //var whichCode = (window.Event) ? e.which : e.keyCode;
    
        
    // 13=enter, 8=backspace as demais retornam 0(zero)
    // whichCode==0 faz com que seja possivel usar todas as teclas como delete, setas, etc    
    if ((whichCode == 13) || (whichCode == 0) || (whichCode == 8) || (whichCode == 9))
    	return true;
    	
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


function formataMoeda3casas(txt, dec)
{
  var str = txt.value;   
  var len = txt.value.length;   
  var ldZeros=true;   
  var thischar;   
  for (var i=0; i<len; i++){
   thischar = str.charAt(i);
   var regmtch = new RegExp(/[0-9.]/);
   if ( thischar.match(regmtch)) 
   {
	  if (ldZeros && thischar=='0')
    {
	   str = str.substr(0,i)+str.substr(i+1); len--; i--;
	  }
	  if (thischar!='0')
    {
	   if (thischar==',')
     {
		  str = str.substr(0,i)+str.substr(i+1); len--; i--; 
	   } else ldZeros=false;
	   
	  }
   } else 
  {
	  str = str.substr(0,i)+str.substr(i+1); len--; i--;
   }
  }
  if (len>dec)
  {    
    var splitPos = len-dec;   
    txt.value = str.substr(0,splitPos)+','+str.substr(splitPos); 
   }
   else 
   {   
	   var left = dec-len;
     txt.value = ',' + Array(left+1).join('0') + str;  
   }
}
