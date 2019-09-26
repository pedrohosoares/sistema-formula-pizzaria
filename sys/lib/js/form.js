// Valida o preenchimento dos campos

var divAvisoObrigatorio = null; // Guarda o elemento DOM que comtem a div de aviso de campo obrigatorio

function validaRequeridos(formId) {
  // É necessário para atualizar o valor do textarea associado ao tinyMCE
  if (typeof tinyMCE != 'undefined') {
    tinyMCE.triggerSave(true, true);
  }
  
  // Verificando os campos requeridos
  var achouRequerido = false;
  var coordenadaPrimeiroCampo = null;
  var nomesCampos = '';
  //var camposAberto = '';
  var camposAberto = new Array(); 

  Array.each($$('input, select, textarea'), function(obj, index) {
    if ((obj.getProperty('type') != 'button') && (obj.getProperty('type') != 'submit') && (obj.getProperty('type') != 'reset')) 
    {
      if(obj.getProperty('class')!=null)
      {
          if ((obj.getProperty('class').match('requerido')) && (obj.getProperty('value').trim() == '')) 
          {
            // Ver o arquivo validacao.css
            obj.addClass('requeridoEfeito');
            
            if(coordenadaPrimeiroCampo == null) {
              coordenadaPrimeiroCampo = obj.getCoordinates();
            }
            
            camposAberto.include(obj.getProperty('id'));
            achouRequerido = true;
          }
          else 
          {
            obj.removeClass('requeridoEfeito');
            
            if(divAvisoObrigatorio != null) 
            {
              divAvisoObrigatorio.dispose();
            }
          }
        }
      }

  });
  if(achouRequerido) {
    var nomesCampos = '';
    //var 
    Array.each($$('label'), function(obj, index) {
      if(obj.getProperty('class')!=null)
      {
        if ((obj.getProperty('class').match('requerido')) && (camposAberto.contains(obj.getProperty('for')))) {
          nomesCampos = nomesCampos + ' <li>' + obj.get('text').replace(':', '') + '</li>';
          //nomesCampos = nomesCampos ;
        }
      }
	  });
    var top = coordenadaPrimeiroCampo.top;
    if(top<0)
      top = top*-1;
    
	  divAvisoObrigatorio = new Element('div', {
      'html': '<b>Campos requeridos:</b><br><ul>' + nomesCampos + '</ul>',
      'class': 'dicaObrigatorio',
      'styles': {
        'top': top,
        'left': coordenadaPrimeiroCampo.right + 10
      }
    });

	  $(document.body).adopt(divAvisoObrigatorio);
	  
    /*
    $(document.body).adopt(new Element('div', {
      'id': obj.getProperty('id') + 'balao',
      'class': 'dicaObrigatorio',
      'styles': {
        'top': obj.getCoordinates().top - 25,
        'left': obj.getCoordinates().right + 5
      }
    }));
    
    var divObrigatorio = new Element('div', {
      'class': 'dicaObrigatorio',
      'styles': {
        'top': 240,
        'left': 656
      }
    });
    
    $(document.body).adopt(divObrigatorio);
    */
  
		/*
		var divFundo = new Element('div', {
		  'styles': {
		    'position': 'absolute',
        'top': 0,
        'left': 0,
        'height': document.documentElement.clientHeight,
        'width':  document.documentElement.clientWidth,
        'background': "transparent url('../lib/img/principal/fundo-preto-trans.png') scroll repeat top left",
        'z-index': 99
		  }
		});
		
		var divMsg = new Element('div', {
		  'html': 'Teste',
		  'styles': {
		    'position': 'absolute',
		    'top': (document.body.clientHeight - 50) / 2,
		    'right': (document.body.clientWidth - 40) / 2,
		    'border': '3px solid #7b0101',
		    'background-color': '#be0000',
		    'color': '#fff',
		    'padding': 20
		  },
		  'events': {
		    'click': function() {
		      this.fade('hide');
		      divFundo.fade('hide');
		    }
		  }
		});
		
		divMsg.inject(divFundo);
		$(document.body).adopt(divFundo);
		*/
    
    return false;
  }
  
  return !achouRequerido;
}

function salvaDadosForm(formId, msgId, redirect) {
  var url = '';

  // É necessário para atualizar o valor do textarea associado ao tinyMCE
  if (typeof tinyMCE != 'undefined') {
    tinyMCE.triggerSave(true, true);
  }
  
  // Verificando os campos requeridos
  validaRequeridos(formId, msgId);
  
  /*
  var achouRequerido = false;
  $each($$('input', 'select', 'textarea'), function(obj, index) {
    if ((obj.getProperty('type') != 'button') && (obj.getProperty('type') != 'submit') && (obj.getProperty('type') != 'reset')) {
      if ((obj.getProperty('class').match('requerido')) && (obj.getProperty('value').trim() == '')) {
        new Fx.Tween(obj).start('background-color', '#ffbcbc');
        achouRequerido = true;
      }
      else {
        // Definindo os campos pintados para fundo branco.
        // TODO definir o fundo apenas dos que estão em vermelho.
        new Fx.Tween(obj).start('background-color', '#fff');
      }
    }
  });
  
  if(achouRequerido) {
    $(msgId).set('text', 'Todos os campos em vermelho são requeridos. Os dados não foram salvos.');
    $(msgId).fade('in');
    //new Fx.Tween($(msgId)).start('background-color', '#ff2a2a');
    (function(){ $(msgId).fade('out'); }).delay(5000);
    
    
    // Dando foco para o primeiro input requerido.
    $each($$('input', 'select', 'textarea'), function(obj, index) {
      if ((obj.getProperty('type') != 'button') && (obj.getProperty('type') != 'submit') && (obj.getProperty('type') != 'reset')) {
        if (obj.getProperty('class').match('requerido')) {
          obj.focus();
          
          return;
        }
      }
    });
    
    return;  
  }
  */
  
  // Montando toda a url de envio
  Object.each($$('input', 'select', 'textarea'), function(obj, index) {
    if ((obj.getProperty('type') != 'button') && (obj.getProperty('type') != 'submit') && (obj.getProperty('type') != 'reset')) {
      if (index > 0) url += '&';
       
      if (obj.getProperty('type') == 'checkbox') {
        if (obj.getProperty('checked'))
          url += obj.getProperty('name') + '=' + obj.getProperty('value');
      }
      else if (obj.getProperty('type') == 'textarea') {
        url += obj.getProperty('name') + '=' + obj.get('html');
      }
      else
        url += obj.getProperty('name') + '=' + obj.getProperty('value');
    }
  });
  
  if ((waindice != '') && (wavalor != '')) {
    if (url != '') url += '&';
    
    url += waindice + '=' + wavalor;
  }
  
  new Request.JSON({
    url: $(formId).getProperty('action'),
    method: $(formId).getProperty('method'),
    onRequest: function() {
      $(msgId).set('text', 'Salvando dados...');
      $(msgId).fade('in');
      new Fx.Tween($(msgId)).start('background-color', '#072f60');
    },
    onComplete: function(resposta) {
      $(msgId).set('text', resposta.mensagem);
      
      if(resposta.status == 'OK') {
        waindice = resposta.waindice;
        wavalor = resposta.wavalor;
        
        new Fx.Tween($(msgId)).start('background-color', '#0f6607');
        (function(){ $(msgId).fade('out'); }).delay(3000);
      }
      else {
        new Fx.Tween($(msgId)).start('background-color', '#ff2a2a');
        (function(){ $(msgId).fade('out'); }).delay(10000);
      }
    },
    onFailure: function() {
      $(msgId).set('text', 'Erro de comunicação com o servidor, por favor, tente novamente.');
      new Fx.Tween($(msgId)).start('background-color', '#ff2a2a');

      (function(){ $(msgId).fade('out'); }).delay(5000);
    }
  }).send(url);
}

// Marca todos os checkboxes
function marcaTodos(classe) {
  var checkBox = document.getElementsByTagName('input');
  
  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match(classe)) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true)) { 
      checkBox[i].checked = !checkBox[i].checked; 
    }
  }
}

function marcaTodosEstado(classe, estado) {
  var checkBox = document.getElementsByTagName('input');
  
  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match(classe)) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true)) { 
      checkBox[i].checked = estado; 
    }
  }
}
