$('#btnCriarPasta').click(function(e){
    e.preventDefault();
    
    if(compartilhado == 1){ // Caso o usuário esteja em uma pasta compartilhada
        criarPastaComp();
    } else { // Caso o usuário esteja em uma pasta do seu drive
        criarPasta();
    }
});

let compartilhado = 0; // Identificador para saber se o usuário está em uma pasta compartilhada ou não
                       // Além de controlar o fim de uma listagem do caminho percorrido em pastas compartilhadas

function criarPasta() { // Esse método cria uma pasta
    usuarioDeslagado();
    
    var nomePasta = $('#txtNome').val();

    if(nomePasta == ''){
        alert("Não é possível criar pasta sem nome!");
    } else {
        $.ajax({
            url: 'app/api.php',
            type: 'post',
            dataType: 'json',
            async: false,
            data: {
                's': 7,
                'u': getToken(),
                'p1': localStorage.getItem('tokenPastaAtual'),
                'p2': nomePasta
            },
            success: function(retorno){
                if(retorno.erro == undefined){
                    listarConteudo(retorno);
                    $('#txtNome').val('');
                } else {
                    alert('Pasta com o nome escolhido já existe!');
                }  
            },
            error: function(){
                console.log('Not working: Criar Pasta');
            }
        });
    }
}

function editarPasta(id) { // Esse método edita uma pasta do drive do usuário
    usuarioDeslagado();
    var idDivCol = "#divCol"+id; // Pega o elemento html para substituir por campo de texto
    
    var t = "<input type='text' id='"+id+"' class='form-control editarPasta' onblur='editar(0)' autofocus>" // Campo de texto para edição
    $(idDivCol).html(t);
}
function editarPastaComp(id, tipo) { // Esse método edita uma pasta compartilhada
    usuarioDeslagado();
    var tdComp = "#tdComp"+id; // Pega o elemento html para substituir por campo de texto
    
    var t = "<input type='text' id='"+id+"' class='form-control editarPasta' onblur='editar("+tipo+")' autofocus>" // Campo de texto para edição
    $(tdComp).html(t);
}
function editar(tipo) { // Esse método edita uma pasta
    usuarioDeslagado();
    var ep = $('.editarPasta');
    if(ep.val()){
        $.ajax({
            url: 'app/api.php',
            type: 'post',
            dataType: 'json',
            async: false,
            data: {
                's': 6,
                'u': getToken(),
                'p1': ep.attr('id'),
                'p2': ep.val(),
                'p3': localStorage.getItem('tokenPastaAtual')
            },
            success: function(data){
                if(data.resultado == 'false'){
                    alert('Pasta com esse nome já existe');
                    listarConteudo(localStorage.getItem('tokenPastaAtual'));
                    
                } else {
                    if(tipo == 0){
                        listarConteudo(localStorage.getItem('tokenPastaAtual'));
                    } else if(tipo == 1) {
                        listarCompartilhado();
                    } else {
                        listarConteudoPastaComp(localStorage.getItem('tokenPastaAtual'));
                    }
                }
            }, 
            error: function() {
                console.log("Not working: editar pasta");
            }
        }); 
    } else {
        listarConteudo(localStorage.getItem('tokenPastaAtual'));
    }
}

function displayCriarUpload() { // Esse método libera os botões de criar parta e fazer upload
    $("#btn-m-criar-p").css({
        'display':"inline"
    });
    $("#btn-m-fazer-up").css({
        'display':"inline"
    });
}

function travarDisplayCriarUpload() { // Esse método trava os botões de criar parta e fazer upload
    $("#btn-m-criar-p").css({
        'display':"none"
    });
    $("#btn-m-fazer-up").css({
        'display':"none"
    });
}

function listarConteudo(id) { // Esse método lista o conteúdo da pasta passada por parâmetro pertencentes a um usuário
    usuarioDeslagado(); // Verificando o tempo de inatividade do usuário
    displayCriarUpload();
    if(id == null){
        id = -1;
    }
    
    localStorage.setItem('tokenPastaAtual', id); // Pasta atual que o usuário está
    var x, pasta = '', arquivo = '', img;

    $('#pasta').html('');
    $('#title-pastas').html('Meu Drive');

    $.ajax({
        url: 'app/api.php',
        type: 'POST',
        dataType: "json",
        data: {
            's': 2,
            'u': getToken(),
            'p1': id
        }, 
        error: function() {
            console.log("Not working: Listar conteúdo do drive [listarConteudo()]");
        }
    }).done(function(data){ // Mesma coisa do método success: function(data){}
        if(data.idPasta === undefined){ // Caso exista conteúdo na pasta
            for(let i = 0; i < data.length; i++) {
                if(data[i].tipo == 0){ // Listando Pastas
                    localStorage.setItem('tokenPasta', data[i].diretorio);
                    
                    pasta += "<tr class='ponto' ondblclick='listarConteudo("+data[i].id_pasta+")'>";
                    pasta += "<td id='divCol"+data[i].id_pasta+"'><img class='imgPasta' src='img/folder-icon.png'/>";
                    pasta += "<a class='ponto input-lg' onclick='listarConteudo("+data[i].id_pasta+")'>"+data[i].nome+"</a></td>";
                    pasta += "<td><a class='ponto' onclick='editarPasta("+data[i].id_pasta+")'>EDITAR</a></td>";
                    pasta += "<td><a class='ponto' onclick='listarUsuarios("+data[i].id_pasta+")' data-toggle='modal' data-target='#modal-mensagem'>COMPARTILHAR</a></td>";
                    pasta += "<td><a class='ponto' onclick='deletarPasta("+data[i].id_pasta+")'>DELETAR</a></td></tr>";
                } else { // Listando Arquivos
                    arquivo += "<tr class='linha-pt-arq ponto' ondblclick='abrirArquivo(`uploads/"+data[i].diretorio+"`)'>";
                    
                    var str = data[i].diretorio; // Nome do arquivo
                    var res = str.substring((data[i].diretorio.length-3), data[i].diretorio.length); // Pegando extensão
                    img = extImg(res); // Verificando qual a extenção e retornando o link para uma img da entensão
                    if(img == ''){ // Se for do tipo png, jpg ou jpeg, a imagem do arquivo original irá aparecer
                        arquivo += "<td><img class='imgPasta' src='uploads/"+data[i].diretorio+"'>";
                    } else {
                        arquivo += "<td><img class='imgPasta' src='"+img+"'>";
                    }
                    arquivo += "<a class='ponto input-lg' target='_blank' href='uploads/"+data[i].diretorio+"'>"+data[i].nome+"</a></td>";
                    arquivo += "<td><a href='baixar.php?arquivo=uploads/"+data[i].diretorio+"'><button class='btn btn-info input-sm'>Download</button></a></td>";
                    arquivo += "<td><a class='ponto' onclick='listarUsuariosArq("+data[i].id_pasta+")' data-toggle='modal' data-target='#modal-comp-arquivo'><button class='btn btn-success input-sm'>Compartilhar</button></a></td>";
                    arquivo += "<td><button onclick='deletarArquivo(`../uploads/"+data[i].diretorio+"`)' type='button' class='btn btn-danger input-sm'>Deletar</button></td></tr>";
                }
            }
            $('#pasta').append(pasta);
            $('#pasta').append(arquivo);
        } else { // Caso não exista conteúdo na pasta
            var label = "<tr class='text-center'><td><label class='input-lg'>Pasta vazia</label></td></tr>";
            $('#pasta').html(label);
        }

        listarCaminho(); // Lista o caminho que o usuario ja passou
    });
}
function listarCaminho() { // Esse método lista o caminho percorrido por um usuário dentro das pastas
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'json',
        async: false,
        data: {
            's': 8,
            'u': getToken(),
            'p1': localStorage.getItem('tokenPastaAtual')
        },
        success: function(resposta){
            $('#caminho').html('');
            for(let i = resposta.idPasta.length - 1; i >= 0; i--){
                if(resposta.descricao[i] != null){
                    if(i == 0){
                        $('#caminho').append('<label>'+ resposta.descricao[i] + "</label>");
                    } else {
                        $('#caminho').append('<a class="ponto" onclick="listarConteudo('+resposta.idPasta[i]+')">'+ resposta.descricao[i] + "</a> > ");
                    }
                }
            }  
        },
        error: function(){
            console.log('Not working: Listar caminho do drive [listarCaminho()]');
        }
    });
}

function deletarPasta(id) { // Esse método deleta uma pasta
    usuarioDeslagado();
    
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'json',
        data: {
            's': 9,
            'u': getToken(),
            'p1': id
        },
        success: function(response){
            if(response.resultado == 'true'){
                listarConteudo(localStorage.getItem('tokenPastaAtual'));
            } else {
                alert("A pasta precisa estar vazia para ser deletada.");
            }
        },
        error: function(){
            console.log('Not working: deletar parta');
        }
    });
}
function deletarArquivo(arquivo) { // Esse método deleta um arquivo
    usuarioDeslagado();
    
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'html',
        async: false,
        data: {
            's': 10,
            'u': getToken(),
            'p1': arquivo
        },
        success: function(resposta){
            if(compartilhado == 1){
                listarConteudoPastaComp(localStorage.getItem('tokenPastaAtual'));
            } else {
                listarConteudo(localStorage.getItem('tokenPastaAtual'));
            }
        },
        error: function(){
            console.log('Not working: deletar arquivo');
        }
    });
}

function extImg(ext) { // Esse método retorna um link para um icon dependendo da extensão do arquivo
    if(ext == 'pdf' || ext == 'PDF') {
        return 'http://beaerospace.com/wp-content/uploads/2016/06/pdf-icon.png';
    } else if(ext == 'rar' || ext == 'zip') {
        return 'https://vignette.wikia.nocookie.net/300heroes/images/3/34/Icon_rar.png/revision/latest?cb=20160217042919';
    } else if(ext == 'txt') {
        return 'https://icon-icons.com/icons2/160/PNG/256/document_txt_22638.png';
    } 
    return '';
}

function abrirArquivo(url) { // Esse método abre um arquivo em outra aba
    window.open(url, '_blank');
}

//Pasta compartilhada
function criarPastaComp() { // Esse método cria uma pasta dentro de uma pasta na área de compartilhamento
    usuarioDeslagado();
    
    var nomePasta = $('#txtNome').val();

    if(nomePasta == ''){
        alert('Não é possível criar pasta sem nome!');
    } else {
        $.ajax({
            url: 'app/api.php',
            type: 'post',
            dataType: 'json',
            async: false,
            data: {
                's': 17,
                'u':getToken(),
                'p1': localStorage.getItem('tokenPastaAtual'),
                'p2': nomePasta
            },
            success: function(retorno){
                if(retorno.resultado == 'true'){
                    listarConteudoPastaComp(retorno.idPastaAtual);
                    $('#txtNome').val('');
                } else {
                    alert('Pasta com o nome escolhido já existe!');
                }
            },
            error: function(){
                console.log('Not working: criar pasta na area de compartilhamento');
            }
        });
    }
}

function listarCaminhoComp() { // Esse método lista o caminho feito em uma pasta compartilhada
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'json',
        async: false,
        data: {
            's': 19,
            'u': getToken(),
            'p1': localStorage.getItem('tokenPastaAtual'),
            'p2':  localStorage.getItem('compartilhado')
        },
        success: function(response){
            $('#caminho').html(''); // Caminho percorrido pelo usuário
            for(let i = response.idPasta.length - 1; i >= 0; i--){
                if(response.descricao[i] != null){
                    if(i == 0){
                        $('#caminho').append('<label>'+ response.descricao[i] + "</label>");
                    } else {
                        $('#caminho').append('<a class="ponto" onclick="listarConteudoPastaComp('+response.idPasta[i]+')">'+ response.descricao[i] + "</a> > ");
                    }
                }
            }
        },
        error: function(){
            console.log('Not working: listar caminho compartilhado');
        }
    });
}
function listarCompartilhado() { // Esse método lista as pastas que são compartilhadas, mas não seu conteúdo
    usuarioDeslagado();
    travarDisplayCriarUpload();

    if(compartilhado == 1){
        compartilhado = 0;
    }
    
    var x, pasta = '';

    $('#pasta').html('');
    $('#caminho').html('');
    $('#title-pastas').html('Compartilhados');
    
    $.ajax({
        url: 'app/api.php',
        type: 'POST',
        dataType: "json",
        async: false,
        data: {
            's': 14,
            'u': getToken()
        },
        error: function(){
            console.log('Not working: listar compartilhado');
        }
    }).done(function(data){
        if(data.idPasta === undefined){
            for(let i = 0; i < data.length; i++) {
                if(data[i].tipo == 0){ // Listando Pastas
                    localStorage.setItem('tokenPasta', data[i].diretorio);
                    pasta += "<tr class='linha-pt-arq ponto'  ondblclick='listarConteudoPastaComp("+data[i].id_pasta+")'><td id='tdComp"+data[i].id_pasta+"'><img class='imgPasta' src='img/folder-icon.png'/>";
                    pasta += "<a class='ponto input-lg' onclick='listarConteudoPastaComp("+data[i].id_pasta+")'>"+data[i].nome+"</a></td>";
                    pasta += "<td><a class='ponto' onclick='editarPastaComp("+data[i].id_pasta+", 1)'>EDITAR</a></td>";
                    pasta += "<td><a class='ponto' onclick='deletarPastaComp("+data[i].id_pasta+", 0)'>DELETAR</a></td></tr>";
                } 
            }
            $('#pasta').append(pasta);
        } 
        listarArquivoCompartilhado(function(response){ // Caso existam arquivos compartilhasdos, sem estarem em uma pasta
            if(response == 'false' && data.idPasta !== undefined){
                var label = "<tr class='text-center'><td><label class='input-lg'>Pasta vazia</label></td></tr>";
                $('#pasta').html(label);
            }
        });
    });
}
function listarConteudoPastaComp(id){ // Esse método lista o conteúdo de uma pasta compartilhada
    usuarioDeslagado();
    displayCriarUpload();

    localStorage.setItem('tokenPastaAtual', id);
    if(compartilhado == 0){
        localStorage.setItem('compartilhado', id);
        compartilhado = 1;
    }

    $('#pasta').html('');
    var x, pasta = '', arquivo = '', img;

    $.ajax({
        url: 'app/api.php',
        type: 'POST',
        dataType: "json",
        data: {
            's': 15,
            'u': getToken(),
            'p1': id
        },
        error: function(){
            console.log('Not working: listar conteudo pasta compartilhada');
        }
    }).done(function(data){
        if(data.idPasta === undefined){
            for(let i = 0; i < data.length; i++) {
                if(data[i].tipo == 0){ // Listando Pastas
                    localStorage.setItem('tokenPasta', data[i].diretorio);

                    pasta += "<tr class='ponto' ondblclick='listarConteudoPastaComp("+data[i].id_pasta+")'><td id='tdComp"+data[i].id_pasta+"'><img class='imgPasta' src='img/folder-icon.png'/>";
                    pasta += "<a class='ponto input-lg' onclick='listarConteudoPastaComp("+data[i].id_pasta+")'>"+data[i].nome+"</a></td>";
                    pasta += "<td><a class='ponto' onclick='editarPastaComp("+data[i].id_pasta+", 2)'>EDITAR</a></td>";
                    pasta += "<td><a class='ponto' onclick='deletarPastaComp("+data[i].id_pasta+", 1)'>DELETAR</a></td></tr>";
                } else { // Listando Arquivos
                    arquivo += "<tr class='ponto' ondblclick='abrirArquivo(`uploads/"+data[i].diretorio+"`)'>";

                    var str = data[i].diretorio;
                    var res = str.substring((data[i].diretorio.length-3), data[i].diretorio.length);
                    img = extImg(res);
                    if(img == ''){
                        arquivo += "<td><img class='imgPasta' src='uploads/"+data[i].diretorio+"'>";
                    } else {
                        arquivo += "<td><img class='imgPasta' src='"+img+"'>";
                    }
                    arquivo += "<a class='ponto input-lg' target='_blank' href='uploads/"+data[i].diretorio+"'>"+data[i].nome+"</a></td>";
                    arquivo += "<td><a href='baixar.php?arquivo=uploads/"+data[i].diretorio+"'><button class='btn btn-info input-sm'>Download</button></a></td>";
                    arquivo += "<td><button onclick='deletarArquivo(`../uploads/"+data[i].diretorio+"`)' type='button' class='btn btn-danger input-sm'>Deletar</button></td></tr>";
                }
            }
            $('#pasta').append(pasta);
            $('#pasta').append(arquivo);
        } else{
            var label = "<tr class='text-center'><td><label class='input-lg'>Pasta vazia</label></td></tr>";
            $('#pasta').html(label);
        }
        listarCaminhoComp();
    });
}
function listarArquivoCompartilhado(response) { // Esse método lista os arquivos que são compartilhadas
    usuarioDeslagado();    
    var i, arquivo = '', img;

    $.ajax({
        url: 'app/api.php',
        type: 'POST',
        dataType: "json",
        async: false,
        data: {
            's': 18,
            'u': getToken()
        },
        error: function(){
            console.log('Not working: listar arquivo compartilhado');
        }
    }).done(function(data){
        if(data.resultado == 'true'){
            for(i = 0; i < data.parametros.length; i++) {
                arquivo += "<tr class='linha-pt-arq ponto'  ondblclick='abrirArquivo(`uploads/"+data.parametros[i].descricao+"`)'>";

                var str = data.parametros[i].descricao;
                var res = str.substring((data.parametros[i].descricao.length-3), data.parametros[i].descricao.length);
                img = extImg(res);
                if(img == ''){
                    arquivo += "<td><img class='imgPasta' src='uploads/"+data.parametros[i].descricao+"'>";
                } else {
                    arquivo += "<td><img class='imgPasta' src='"+img+"'>";
                }
                arquivo += "<a class='input-lg' target='_blank' href='uploads/"+data.parametros[i].descricao+"'>"+data.parametros[i].nome_original+"</a></td>";
                arquivo += "<td><a href='baixar.php?arquivo=uploads/"+data.parametros[i].descricao+"'><button class='btn btn-info input-sm'>Download</button></a></td>";
                arquivo += "<td><button onclick='deletarArquivoComp(`../uploads/"+data.parametros[i].descricao+"`)' type='button' class='btn btn-danger input-sm'>Deletar</button></td></tr>";
            }
            $('#pasta').append(arquivo);
            response('true');
        }  else {
            response('false');
        }
    });
}

function deletarPastaComp(id, tipo) { // Esse método apaga uma pasta compartilhada
    usuarioDeslagado();
    
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'json',
        data: {
            's': 9,
            'u': getToken(),
            'p1': id
        },
        success: function(response){
           if(response.resultado == 'true'){
               if(tipo == 0) {
                    listarCompartilhado();
               } else {
                    listarConteudoPastaComp(localStorage.getItem('tokenPastaAtual'));
               }
            } else {
                alert("A parta precisa estar vazia para ser deletada!");
            }
        },
        error: function(){
            console.log('Not working: deletar pasta compartilhada');
        }
    });
		
}
function deletarArquivoComp(arquivo) { // Esse método deleta um arquivo compartilhado
    usuarioDeslagado();
    
    $.ajax({
        url: 'app/api.php',
        type: 'post',
        dataType: 'html',
        async: false,
        data: {
            's': 10,
            'u': getToken(),
            'p1': arquivo
        },
        success: function(resposta){
            listarCompartilhado();
        },
        error: function(){
            console.log('Not working: deletar arquivo compartilhado');
        }
    });
}