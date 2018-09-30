if(localStorage.getItem("tokenLogin") == null) {
    localStorage.setItem("tokenLogin", '1');
}
$.ajax({ // Esse método valida se o usuário está logado por tempo determinado
    url: 'app/api.php',
    type: 'post',
    dataType: 'json',
    async: false,
    data: {
        's': 4,
        'u': localStorage.getItem("tokenLogin")
    },
    success: function(data){
        console.log(data.resultado );
        if(data.resultado != 'false'){
            location.href = 'home.html';
        } 
    },
    error: function(){
        console.log("Not working: Erro ao verificar usuário logado");
    }
});

$('#btnEntrar').click(function(e){
    e.preventDefault();
    
    var login = $('#login').val(),
        senha = $('#senha').val();

    if(login && senha) {
        entrar(login, senha);
    }
});

function entrar(email, senha) { // Esse método faz login no sistema
    $.ajax({
        url: 'app/api.php',
        type: 'POST',
        dataType: "json",
        data: {
            's': 3,
           'p1': email,
           'p2': senha
        },
        success: function(data) {
            if(data.resultado == "false") {
                $('#retorno').addClass('alert alert-danger');
                $("#retorno").html(data.erro);
            } else {
                localStorage.setItem('tokenPasta', '-1');
                localStorage.setItem('tokenPastaAtual', '-1');
                localStorage.setItem("tokenLogin", data.parametros[0].senha);
                location.href = 'home.html';
            }
        },
        error: function() {
			console.log('Not working: Login');
		}
    });
}

$('#esqueciSenha').click(function(e){
    e.preventDefault();
    var email = $('#email').val();
    
    if(email) {
        $.ajax({
            url: 'app/api.php',
            type: 'POST',
            dataType: "json",
            data: {
                's': 24,
               'p1': email
            },
            success: function(data) {
                if(data.resultado == 'true'){
                    $(".my_form").html("<div class='alert alert-success'>Verifique o e-mail "+email.toUpperCase()+" para alterar a senha</div><br><a href='index.html'>Fazer login</a>");
                } else {
                    alert('O e-mail informado não está cadastrado no sistema');
                }
                
            },
            error: function() {
                console.log('Not working: Esqueci senha');
            }
        });
    } else {
        alert('Por favor, informe seu email.');
    }
});
