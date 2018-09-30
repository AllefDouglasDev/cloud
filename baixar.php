<?php 

$arquivo = $_GET["arquivo"]; 

$testa = substr($arquivo,-3); 
$bloqueados = array('php','tml','htm'); 
// caso a extensão seja diferente das citadas acima ele 
// executa normalmente o script 

if(!in_array($testa,$bloqueados)){ 
	if(isset($arquivo) && file_exists($arquivo)){ // faz o teste se a variavel não está vazia e se o arquivo realmente existe
		switch(strtolower(substr(strrchr(basename($arquivo),"."),1))){ // verifica a extensão do arquivo para pegar o tipo
			case "txt": $tipo="application/txt"; break;
			case "pdf": $tipo="application/pdf"; break;
			case "exe": $tipo="application/octet-stream"; break;
			case "zip": $tipo="application/zip"; break;
			case "rar": $tipo="application/rar"; break;
			case "doc": $tipo="application/msword"; break;
			case "xls": $tipo="application/vnd.ms-excel"; break;
			case "ppt": $tipo="application/vnd.ms-powerpoint"; break;
			case "gif": $tipo="image/gif"; break;
			case "png": $tipo="image/png"; break;
			case "jpg": $tipo="image/jpg"; break;
			case "mp3": $tipo="audio/mpeg"; break;
			case "php": // deixar vazio por seurança
			case "htm": // deixar vazio por seurança
			case "html": // deixar vazio por seurança
		}

		header("Content-Type: ".$tipo); // informa o tipo do arquivo ao navegador
		header("Content-Length: ".filesize($arquivo)); // informa o tamanho do arquivo ao navegador
		header("Content-Disposition: attachment; filename=".basename($arquivo)); // informa ao navegador que é tipo anexo e faz abrir a janela de download, tambem informa o nome do arquivo
		readfile($arquivo); // lê o arquivo
		exit; // aborta pós-ações
	}else{
		echo "Erro!";
		exit;
	} 
}
