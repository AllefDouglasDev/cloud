var app = app || {};

(function(o){ // Esse método faz upload dos arquivos

	'use strict';

	//Private methods

	var ajax, getFormData, setProgress;

	ajax = function(data) {
		var xmlhttp = new XMLHttpRequest(), uploaded;

		// Retorna os arquivos enviados e os que falharam
		xmlhttp.addEventListener('readystatechange', function(){
			if(this.readyState === 4) {
				if(this.status === 200){
					try{
						uploaded = JSON.parse(this.responseText);
					} catch(ex) {
						uploaded = {"failed": {"name":"Arquivo muito grande.</br>"}};
					}

					if(typeof o.options.finished === 'function') {
						o.options.finished(uploaded);
					}
				} else {
					if(typeof o.options.error === 'function') {
						o.options.error();
					}
				}
			} 
		});

		// Monitora o progresso e chama método para alteração do status da barra de progresso
		xmlhttp.upload.addEventListener('progress', function(event){
			var percent;

			if(event.lengthComputable === true) {
				percent = Math.round((event.loaded / event.total) * 100); // Cálculo de porcentagem da barra de progresso
				setProgress(percent);
			}
		});

		// Envia para o arquivo php
		xmlhttp.open('post', o.options.processor);
		xmlhttp.send(data);
	};

	// Pega os valores dos arquivos e retorna um formData com os arquivos
	getFormData = function(source, funcao, token, idPasta){
		var data = new FormData();

		for(let i = 0; i < source.length; i++) {
			data.append('file[]', source[i]);
		}

		data.append('s', funcao); 
		data.append('ajax', true);
		data.append('u', token);
		data.append('p1', idPasta);
		
		return data;
	};

	// Altera o valor da barar de progresso
	setProgress = function(value){
		if(o.options.progressBar !== undefined) {
			o.options.progressBar.style.width = value ? value + '%' : 0;
		}

		if(o.options.progressText !== undefined) {
			o.options.progressText.innerText = value ? value + '%' : '';
		}
	};

	// Define as opções do objeto 
	o.uploader = function(options) {
		o.options = options; 
		if(o.options.files !== undefined) {
			ajax(getFormData(o.options.files.files, o.options.funcao, o.options.token, o.options.idPasta));
		}
	}
}(app));

document.getElementById('submit').addEventListener('click', function(e) {
	e.preventDefault();
	usuarioDeslagado();

	$(this).attr('disabled', 'true'); // Desabilita botão
	$("#labelArquivos").attr('disabled', 'true'); // Desabilita botão

	uploadFile();
}); //submit

function uploadFile() { // Esse método configura e recebe retorno do upload dos arquivos
	var url = 'app/api.php';

	var f = document.getElementById('file');
	var pb = document.getElementById('pb');
	var pt = document.getElementById('pt');

	app.uploader({
		files: f,
		progressBar: pb,
		progressText: pt,
		processor: url,
		funcao: 1,
		idPasta: localStorage.getItem('tokenPastaAtual'),
		token: getToken(),

		finished: function(data) {
			var uploads = document.getElementById('uploads'),
				failed = document.createElement('div'),
				x;

			if(data.failed.length) { // Caso algum arquivo tenha falhado 
				failed.innerHTML = '<p>Infelizmente, os arquivos a seguir falharam:</p>';
			}

			uploads.innerText = '';
			
			// Lista os arquivos que falharam
			for(x = 0; x < data.failed.length; x++) {
				span = document.createElement('span');
				span.innerText = data.failed[x].name;

				failed.appendChild(span);
			}

			uploads.appendChild(failed); // Adiciona os que falharam à div
			
			if(compartilhado == 1) {
				listarConteudoPastaComp(localStorage.getItem('tokenPastaAtual'));
			} else {
				listarConteudo(localStorage.getItem('tokenPastaAtual'));
			}

			zerarBarraCarregamento();
		},

		error: function() {
			failed.innerHTML = '<p>Infelizmente, os arquivos falharam</p>';
			uploads.appendChild(failed);
			zerarBarraCarregamento();
			console.log('Not working: upload de arquivos');
		}
	}); //app.uploader
}

function sleep(milliseconds) {
	var start = new Date().getTime();
	for (var i = 0; i < 1e7; i++) {
	  if ((new Date().getTime() - start) > milliseconds){
		break;
	  }
	}
}

function zerarBarraCarregamento(){ // Esse método zera a barra de carregamento e reabilita os botões
	// Tempo de 1,5 segundos
	setTimeout(function(){
		var pb = document.getElementById('pb');
		var pt = document.getElementById('pt');
		pb.style.width =  '0%';
		pt.innerText = '0%';
		$(".btn-start").removeAttr("disabled"); // Abilita botão
		$("#labelArquivos").removeAttr("disabled"); // Abilita botão
	},1500);
}