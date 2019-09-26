var TabsInterna = new Class({
  initialize: function(elemento) {
    this.menus   = $$('.menuTabInterno ul li');
    this.paineis = $$('.painelTabInterno');
    
    this.menus.each(function(item, index) {
      item.addEvent('click', function(){
        this.ativa(this.menus[index], this.paineis[index], index);
      }.bind(this));
    }.bind(this));
    
    // Ativando o primeiro
    this.ativa(this.menus[0], this.paineis[0], 0);
  },
  
  ativa: function(menu, painel, indice) {
    menu.addClass('ativo');
    painel.addClass('ativo');
    
    if(($type(this.menuAtivo) == 'element') && (this.menuAtivo != menu)) {
      this.menuAtivo.removeClass('ativo');
    }
    
    if(($type(this.painelAtivo) == 'element') && (this.painelAtivo != painel)) {
      this.painelAtivo.removeClass('ativo');
    }
    
    this.menuAtivo   = menu;
    this.painelAtivo = painel;
    this.fireEvent('change', indice, 10);
  },
  
  
  irpara: function(indice) {
    this.ativa(this.menus[indice], this.paineis[indice], indice);
  }
});

TabsInterna.implement(new Events);
