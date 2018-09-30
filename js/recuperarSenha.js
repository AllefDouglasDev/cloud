$.ajax({ // Esse códito testa se o token do url é válido
    url: 'app/api.php',
    type: 'POST',
    dataType: "json",
    data: {
        's': 25,
        'p1': get_url('token')
    },
    success: function(data) {
    	if(data.resultado == 'false') {
    		location.href = 'index.html';
    	}
    },
    error: function() {
        console.log('Not working: validar token da url falhou');
    }
});

$('#esqueciSenha').click(function(e){
    e.preventDefault();
    validarSenha();
});

function validarSenha() { // Esse método valida e altera a senha do usuário
    var senha = $('#senha1').val(),
        confSenha = $('#senha2').val();

    if(senha == confSenha && senha){
        $.ajax({
            url: 'app/api.php',
            type: 'POST',
            dataType: "json",
            data: {
                's': 26,
                'p1': get_url('token'),
                'p2': senha
            },
            success: function(data) {
                location.href = 'index.html';
            },
            error: function() {
                console.log('Not working: validar senha');
            }
        });
    } else {
        $('#retorno').addClass('alert alert-danger');
        $('#retorno').html("<label for='senha1'>Por favor, insira valor de senha válido.</label>");
    }
}

function get_url(n) { // Esse método pega um valor da url enviado por GET
    var r = location.search.substring(1, location.search.length),
        a = !1,
        t = r.split("&");
    for (i = 0; i < t.length; i++) param_name = t[i].substring(0, t[i].indexOf("=")), param_name == n && (a = t[i].substring(t[i].indexOf("=") + 1));
    return a ? a : !1
}