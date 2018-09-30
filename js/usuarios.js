function listarUsuarios(id){ // Esse método lista os usuários para compartilhamento
    usuarioDeslagado();
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'json',
        data: {
            's': 12,
            'u': getToken()
        },
        success: function(retorno){
            let i, table, tbody;
            $('#usuarios').html('');
            if(retorno != null){
                for(i = 0; i < retorno.length; i++){
                    tbody = "<tr><td><input type='radio' name='radioListaUsuario' id='"+retorno[i].id+"' value='"+retorno[i].id+"'/></td>";
                    tbody += "<td><label for='"+retorno[i].id+"'>"+retorno[i].nome+"</label></td>";
                    tbody += "<td><label for='"+retorno[i].id+"'>"+retorno[i].login+"</label></td></tr>";
                    $('#usuarios').append(tbody);
                }  
                $('.pastaCompartilhada').attr('value', id);
                listarUsuariosCompartilhados();
            }
        },
        error: function(){
            console.log('Not working: listar usuarios');
        }
    });   
}
function listarUsuariosCompartilhados(){ // Esse método lista os usuários que estão compartilhando da pasta escolhida
    usuarioDeslagado();
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'json',
        data: {
            's': 20,
            'u': getToken(),
            'p1': $('.pastaCompartilhada').val()
        },
        success: function(retorno){
            let i, tbody;
            $('#usuariosComp').html('');
            if(retorno != null){
                $('#removerComp').css({
                    'display': 'block'
                });
                for(i = 0; i < retorno.length; i++){
                    tbody = "<tr><td><input type='radio' name='radioListaUsuarioComp' id='"+retorno[i].id_usuario+"Comp' value='"+retorno[i].id_usuario+"'/></td>";
                    tbody += "<td><label for='"+retorno[i].id_usuario+"Comp' class='listaUsuarios'>"+retorno[i].nome+"</label></td>";
                    tbody += "<td><label for='"+retorno[i].id_usuario+"Comp' class='listaUsuarios'>"+retorno[i].login+"</label></td></tr>";
                    $('#usuariosComp').append(tbody);
                } 
            } else {
                $('#removerComp').css({
                    'display': 'none'
                });
            }
        },
        error: function(){
            console.log('Not working: listar usuarios compartilhados');
        }
    });
}

//Arquivos
function listarUsuariosArq(id){ // Esse método lista os usuários para compartilhamento
    usuarioDeslagado();
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'json',
        data: {
            's': 12,
            'u': getToken()
        },
        success: function(retorno){
            let i, tbody;
            $('#usuariosArq').html('');
            if(retorno != null){
                for(i = 0; i < retorno.length; i++){
                    tbody = "<tr><td><input type='radio' name='radioListaUsuarioArquivo' id='arq"+retorno[i].id+"' value='"+retorno[i].id+"'/></td>";
                    tbody += "<td><label for='arq"+retorno[i].id+"' class='listaUsuarios'>"+retorno[i].nome+"</label></td>";
                    tbody += "<td><label for='arq"+retorno[i].id+"' class='listaUsuarios'>"+retorno[i].login+"</label></td></tr>";
                    $('#usuariosArq').append(tbody);
                }  
                $('.arquivoCompartilhado').attr('value', id);
                listarArquivosUsuariosCompartilhados();
            }
        },
        error: function(){
            console.log('Not working: listar usuarios para compartilhar arquivos');
        }
    });   
}
function listarArquivosUsuariosCompartilhados(){ // Esse método lista os usuários que estão compartilhando o arquivo escolhido
    usuarioDeslagado();
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'json',
        data: {
            's': 22,
            'u': getToken(),
            'p1': $('.arquivoCompartilhado').val()
        },
        success: function(retorno){
            let i, tbody;
            $('#usuariosCompArq').html('');
            if(retorno.resultado == 'true'){
                $('#removerCompArq').css({
                    'display': 'block'
                });

                for(i = 0; i < retorno.parametros.length; i++){
                    tbody = "<tr><td><input type='radio' name='radioListaUsuarioCompArq' id='"+retorno.parametros[i].id+"CompArq' value='"+retorno.parametros[i].id+"'/></td>";
                    tbody += "<td><label for='"+retorno.parametros[i].id+"CompArq' class='listaUsuarios'>"+retorno.parametros[i].nome+"</label></td>";
                    tbody += "<td><label for='"+retorno.parametros[i].id+"CompArq' class='listaUsuarios'>"+retorno.parametros[i].login+"</label></td></tr>";
                    $('#usuariosCompArq').append(tbody);
                } 
            } else {
                $('#removerCompArq').css({
                    'display': 'none'
                });
            }
        },
        error: function(){
            console.log('Not working: lista de usuarios que compartilham arquivo');
        }
    }); 
}

function compartilhar(){ // Esse método compartilha uma pasta
    usuarioDeslagado();
    var idUsuario = $("input[name='radioListaUsuario']:checked").val(),
        idPasta = $('.pastaCompartilhada').val();
    if(idUsuario != undefined){
        $.ajax({
            url: 'app/api.php',
            type: 'post',
            dataType: 'json',
            data: {
                's': 13,
                'u': getToken(),
                'p1': idUsuario,
                'p2': idPasta
            },
            success: function(retorno){
                if(retorno.resultado == "true"){
                    alert("Pasta compartilhada com sucesso!");
                } else {
                    alert("Erro ao compartilhar a pasta!");
                }
            },
            error: function(){
                console.log('nNot working: compartilhar pasta');
            }
        });
            
    } else {
       alert('Nenhum usuário escolhido!');
    }
}
function compartilharArquivo(){ // Esse método compartilha um arquivo
    usuarioDeslagado();
    var idUsuario = $("input[name='radioListaUsuarioArquivo']:checked").val(),
        idArquivo = $('.arquivoCompartilhado').val();
    if(idUsuario != undefined){
        $.ajax({
            url: 'app/api.php',
            type: 'post',
            dataType: 'html',
            data: {
                's': 16,
                'u': getToken(),
                'p1': idUsuario,
                'p2': idArquivo
            },
            success: function(retorno){
                alert("Arquivo compartilhado com sucesso!");
            },
            error: function(){
                console.log('Not working: compartilhar arquivo');
            }
        });
            
    } else {
       alert('Nenhum usuário escolhido!');
    }
}

function removerCompartilhamento() { // Esse método remove um usuário compartilhado de uma pasta
    usuarioDeslagado();
    var idUsuario = $("input[name='radioListaUsuarioComp']:checked").val(),
        idPasta = $('.pastaCompartilhada').val();
    if(idUsuario != undefined){
        $.ajax({
            url: 'app/api.php',
            type: 'post',
            dataType: 'html',
            data: {
                's': 21,
                'u': getToken(),
                'p1': idUsuario,
                'p2': idPasta
            },
            success: function(retorno){
                alert("Usuário removido com sucesso!");
            },
            error: function(){
                console.log('Not working: remover compartilhamento');
            }
        });
            
    } else {
       alert('Nenhum usuário escolhido!');
    }
}
function removerCompartilhamentoArq() { // Esse método remove um usuário compartilhado de um arquivo
    usuarioDeslagado();
    var idUsuario = $("input[name='radioListaUsuarioCompArq']:checked").val(),
        idArquivo = $('.arquivoCompartilhado').val();
    if(idUsuario != undefined){
        $.ajax({
            url: 'app/api.php',
            type: 'post',
            dataType: 'html',
            data: {
                's': 23,
                'u': getToken(),
                'p1': idUsuario,
                'p2': idArquivo
            },
            success: function(retorno){
                alert("Usuário removido com sucesso!");
            },
            error: function(){
                console.log('Not working: remover compartilhamento de arquivo');
            }
        });
            
    } else {
       alert('Nenhum usuário escolhido!');
    }
}

