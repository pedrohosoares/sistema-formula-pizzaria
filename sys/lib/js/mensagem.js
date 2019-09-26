var Mensagem = new Class({

  initialize: function(tipo, titulo, texto) {
    if(tipo == '') tipo = 'ok'
    
    if ((texto == '') || (texto == null))
      var html = '<h2>' + titulo + '</h2>';
    else
      var html = '<h2>' + titulo + '</h2><p>' + texto + '</p>';
    
    if(tipo == 'ok') {
      var divMsg = new Element('div', {
        'html': html,
        'class': 'mensagemOk',
        'styles': {
          'position': 'absolute',
          'top': 0,
          'right': 0,
          'background-color': '#ffffff'
        }
      });
    
      $(document.body).adopt(divMsg);
      (function(){ $(divMsg).fade('out'); }).delay(3000); 
      
    }
    else if (tipo == 'erro') {
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
    
      html += '<br><a style="text-align: right;" href="javascript:;">Fechar</a>';
    
      var divMsg = new Element('div', {
        'html': html,
        'class': 'mensagemErro',
        'styles': {
          'position': 'absolute',
          'top': (document.body.clientHeight - 50) / 2,
          'right': (document.body.clientWidth - 230) / 2,
          'background-color': '#ffffff'
        },
        'events': {
          'click': function() {
            this.destroy();
            divFundo.destroy();
          }
        }
      });
    
      divMsg.inject(divFundo);
      $(document.body).adopt(divFundo); 
    }
  }
});

function mensagemErro(titulo, texto) {
  window.addEvent('domready', function() { 
    new Mensagem('erro', titulo, texto);
  });
}

function mensagemOk(titulo, texto) {
  window.addEvent('domready', function() { 
    new Mensagem('ok', titulo, texto);
  });
}
