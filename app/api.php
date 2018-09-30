<?php
	header('Access-Control-Allow-Origin: *');	
 	
	include 'DAO.php';
	
	//$banco = $_POST["y"];

	$opcao = $_POST["s"];	
	$arrayValidacao = [3, 11, 24, 25, 26]; // Funcoes que não testarão se o usuario está logado

	if(!in_array($opcao, $arrayValidacao)){
		$user = $_POST["u"];
		$idUsuario = verificaUsuarioLogado($user);
		if($idUsuario == 0){ // Usuário não logado
			echo json_encode(array(
				'resultado' => 'false',
				'erro' => 'Usuário não logado. Por favor, efetuar o login!'
			));
			exit();
		} else {
			editarDataHoraUsuario($_POST['u']);
		}
	}
	
	switch ($opcao) {
		case "0": 	f0();
			break;
		case "1": 	f1();
			break;
		case "2": 	f2();			
			break;
		case "3":	f3();		
			break;
		case "4":	f4();		
			break;
		case "5":	f5();		
			break;
		case "6":	f6();		
			break;
		case "7":	f7();		
			break;
		case "8":	f8();		
			break;
		case "9":	f9();		
			break;
		case "10":	f10();		
			break;
		case "11":	f11();		
			break;
		case "12":	f12();		
			break;
		case "13":	f13();		
			break;
		case "14":	f14();		
			break;
		case "15":	f15();		
			break;
		case "16":	f16();		
			break;
		case "17":	f17();		
			break;
		case "18":	f18();		
			break;
		case "19":	f19();		
			break;
		case "20":	f20();		
			break;
		case "21":	f21();		
			break;	
		case "22":	f22();		
			break;
		case "23":	f23();		
			break;
		case "24":	f24();		
			break;
		case "25":	f25();		
			break;			
		case "26":	f26();		
			break;
		case "27":	f27();		
			break;
		case "28":	f28();		
			break;
		case "29":	f29();		
			break;
		case "30":	f30();		
			break;
		default:
			echo "erro: api nao localizada";
	}

function f1() { // Esse método faz upload de arquivos
	header('Content-type: application/json');
	if(!empty($_FILES['file'])){
		echo receberArquivo($_FILES['file'], $GLOBALS['idUsuario'], $_POST['p1']); // receberArquivo(arquivos, idUsuario, idPasta)
	}
}
function f2() { // Esse método lista as pastas do drive
	echo listarDiretorio($_POST['p1'], $GLOBALS['idUsuario']);
}
function f3() { // Esse método faz login no sistema
	echo login($_POST['p1'], $_POST['p2']);
}
function f4() { // Esse método retorna verdadeiro para usuário logado
	echo json_encode(array(
		'resultado' => 'true',
		'erro' => 'Usuário logado!'
	));
}
function f5() { // Esse método deleta um token do bd
	echo deletaToken($_POST["u"]);
}
function f6() { // Esse método edita o nome de uma pasta
	echo editarPasta($_POST['p1'], $_POST['p2'], $_POST['p3'], $GLOBALS['idUsuario']);
}
function f7() { // Esse método cria uma pasta 
	echo criarPasta($GLOBALS['idUsuario'], $_POST['p1'], $_POST['p2']);
}
function f8() { // Esse método lista o caminho percorrido pelo usuário
	echo listarPastasAnteriores($_POST['p1']);
}
function f9() { // Esse método deleta uma pasta
	echo deletarPasta($_POST['p1'], $GLOBALS['idUsuario']);
}
function f10() { // Esse método deleta um arquivo
	echo deletarArquivo($_POST['p1']);
}
function f11() { // Esse método cadastra um novo usuário
	echo inserirUsuario($_POST['p1'], $_POST['p2'], $_POST['p3']);
}
function f12() { // Esse método lista os usuários cadastrados, exceto o usuário logado
	echo listarUsuarios($GLOBALS['idUsuario']);
}
function f13() { // Esse método compartilha uma pasta
	echo compartilharPasta($_POST['p1'], $_POST['p2'], $GLOBALS['idUsuario']);
}
function f14() { // Esse método lista as pastas compartilhadas com o usuário logado
	echo listarDiretorioCompartilhado($GLOBALS['idUsuario']);
}
function f15() { // Esse método lista o conteúdos das pastas compartilhadas do usuário logado
	echo listarConteudoPastaComp($_POST['p1']);
}
function f16() { // Esse método compartilha um arquivo
	echo compartilharArquivo($_POST['p1'], $_POST['p2']); 
}
function f17() { // Esse método criar uma pasta dentro de uma pasta compartilhada
	echo criarPastaComp($GLOBALS['idUsuario'], $_POST['p1'], $_POST['p2']); 
} 
function f18() { // Esse método lista os arquivos compartilhados com o usuário logado
	echo listarArquivoCompartilhado($GLOBALS['idUsuario']);
}
function f19() { // Esse método lista o caminho percorrido pelo usuário dentro das pastas compartilhadas
	echo listarPastaAnteriorComp($_POST['p1'], $_POST['p2'], $GLOBALS['idUsuario']);
}
function f20() { // Esse método lista os usuários que compartilham esse pasta
	echo listarUsuariosComp($_POST['p1'], $GLOBALS['idUsuario']);
}
function f21() { // Esse método remove um usuário que compartilha essa pasta
	echo deletarCompartilharPasta($_POST['p1'], $_POST['p2']);
}
function f22() { // Esse método os usuários que compartilham esse arquivo
	echo listarUsuariosArquivosComp($_POST['p1']);
}
function f23() { // Esse método remove um usuário que compartilha esse arquivo
	echo deletarCompartilharArquivo($_POST['p1'], $_POST['p2']);
}
function f24() { // Esse método insere um token para liberação de renovação de senha
	echo esqueciSenha($_POST['p1']);
}
function f25() { // Esse método valida token para renovação de senha
	$id = buscarUsuarioEsqueciSenha($_POST['p1']);
	if($id != null){
		echo json_encode(array('resultado' => 'true'));
	} else {
		echo json_encode(array('resultado' => 'false'));
	}
}
function f26() { // Esse método altera uma senha
	echo alterarSenha($_POST['p1'], $_POST['p2']);
}