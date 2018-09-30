$('#btnCadastrarUsuario').click(function(e){
    e.preventDefault();
    const nome = $("#nome").val(),
        login = $("#login").val(),
        senha = $("#senha").val();
    if(nome != '' && login != '' && senha != '') { // Testa para null, undefined, vazio, etc
        cadastrar(nome, login, senha);
    }
});

function cadastrar(nome, email, senha) { // Esse método cadastra um novo usuário
    if(validaEmail(email)){ // Testando email válido
        $.ajax({
            url: 'app/api.php',
            type: 'post',
            dataType: 'json',
            data: {
                's': 11,
                'p1': nome,
                'p2': email,
                'p3': senha
            },
            success: function(data){
                if(data.erro != undefined){
                    $('#resposta').addClass('alert alert-danger');
                    $('#resposta').html(data.erro);
                } else {
                    location.href = 'index.html';
                }
            }, 
            error: function() {
                console.log("Not working: Cadastro");
            }
        });
    } else {
        $('#resposta').addClass('alert alert-danger');
        $('#resposta').html("Por favor, insira um email válido.");
    } 
}

function validaEmail(email) { // Esse método valida email, retornando true ou false
    // filtros
    var emailFilter=/^.+@.+\..{2,}$/;
    var illegalChars= /[\(\)\<\>\,\;\:\\\/\"\[\]]/
    // condição
    return (emailFilter.test(email)) || email.match(illegalChars);
}

