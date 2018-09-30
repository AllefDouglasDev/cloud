if(getToken() == null) {
    localStorage.setItem("tokenLogin", '1');
}
$.ajax({ // Esse método valida se o usuário está logado por tempo determinado
    url: 'app/api.php',
    type: 'post',
    dataType: 'json',
    async: false,
    data: {
        's': 4,
        'u': getToken()
    },
    success: function(data){
        if(data.resultado == 'false'){
            localStorage.removeItem("tokenLogin");
            location.href = 'index.html';
        } 
    },
    error: function(){
        console.log("Not working: Erro ao verificar usuário logado");
    }
});

function sair() { // Esse método sai do sistema
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'html',
        data: {
            's': 5,
            'u': getToken()
        },
        success: function(data){
            localStorage.removeItem("tokenLogin");
            location.href = 'index.html';
        }
    });
}

function getToken() { // Esse método retorna o token do usuário
    return localStorage.getItem("tokenLogin");
}

function usuarioDeslagado() { // Esse método valida se o usuário está logado por tempo determinado
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'json',
        async: false,
        data: {
            's': 4,
            'u': getToken()
        },
        success: function(data){
            if(data.resultado == 'false' && data.erro == 'Usuário não logado. Por favor, efetuar o login!'){
                localStorage.removeItem("tokenLogin");
                location.href = 'index.html';
            } 
        },
        error: function(){
            console.log("Not working: Erro ao verificar usuário logado");
        }
    });
}
