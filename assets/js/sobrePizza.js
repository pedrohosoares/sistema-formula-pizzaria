function mostrarSabor(valor) {
    $(document).ready(function () {
        $('#pizzaSabor01').hide();
        $('#pizzaSabor02').hide();
        $('#pizzaSabor03').hide();
        $('#pizzaSabor04').hide();

        $('#detalhesPizzaSabor01').hide();
        $('#detalhesPizzaSabor02').hide();
        $('#detalhesPizzaSabor03').hide();
        $('#detalhesPizzaSabor04').hide();

        switch (valor) {
            case 1: 
                $('#pizzaSabor01').show();
                $('#detalhesPizzaSabor01').show();
                break;
            case 2: 
                $('#pizzaSabor02').show();
                $('#detalhesPizzaSabor02').show();
                break;
            case 3: 
                $('#pizzaSabor03').show();
                $('#detalhesPizzaSabor03').show();
                break;
            case 4: 
                $('#pizzaSabor04').show();
                $('#detalhesPizzaSabor04').show();
                break;
        };
    });
}