<?php 
try {
	$pdo = new PDO("mysql:host=localhost; dbname=drive", "root", ""); // Banco local
	
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch(PDOException $e) {}

function login($email, $senha){ // Esse método faz login no sistema
	$vetor = null; // Inicia com o vetor null para verificação de Email caso não retorne nada do DB

	$erro = array(
		'resultado' => 'false',
		'erro'=> 'Login e/ou senha inválido(s)'
	);
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT senha, nome, id FROM usuario WHERE login = ? AND deletado = 0");
		$consulta->bindParam(1,	$email, PDO::PARAM_STR);
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	
	
		if($vetor !== null) {
			if($vetor[0]['senha'] === $senha){// Se a senha for igual ao informado
				$token = md5($email) . time();
				$vetor[0]['senha'] = $token;

				date_default_timezone_set('America/recife');

				$dataInit = date('Y-m-d H:i');
				if(inserirToken($token, $dataInit, $vetor[0]['id'])){ 
					return json_encode(array(
						'resultado' => 'true',
						'parametros' => $vetor));
				} else {
					return json_encode($erro);
				}
			} else { // Senha incorreta
				return json_encode($erro);
			}
		} else { // Email não existente ou deletado
			return json_encode($erro);
		}
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function esqueciSenha($email) { // Esse método insere o token para o usuário acessar a página de alterar senha
	$idUser = searchUserByEmail($email);

	if($idUser != null) {
		$token = md5($email) . time();

		try {   
			$conect = $GLOBALS['pdo'];
			$conect->beginTransaction(); 
						
			$consulta = $conect->prepare("INSERT INTO esqueci_senha (id_usuario, token) VALUES (?,?)");
			$consulta->bindParam(1,	$idUser, PDO::PARAM_INT);	
			$consulta->bindParam(2,	$token, PDO::PARAM_STR);
			$consulta->execute();		

			$conect->commit();	

			if(enviarEmail($email, $token)){
				return json_encode(array('resultado' => 'true'));
			} else {
				return json_encode(array('resultado' => 'false'));
			}
		} catch (Exception $e) {  		
			return json_encode(array('resultado' => 'false'));
		}
	} else {
		return json_encode(array('resultado' => 'false'));
	}
}
function enviarEmail($email, $token){ // Esse método envia um email para alteração de senha para o usuário
 
	ini_set('display_errors', 1);

	error_reporting(E_ALL);

	$from = "allefwalker73@gmail.com";

	$to = $email;

	$subject = "Esqueci minha senha";

	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= "De:". $from;

	$message = "<html><head><title>Esqueci minha senha</title></head><body><a href='localhost:8000/recuperarSenha.html?token=".$token."'>Clique para atualizar senha</a></body></html>";

	return mail($to, $subject, $message, $headers);
}
function alterarSenha($token, $senha) { // Esse método altera a senha do usuário no DB
	$idUsuario = buscarUsuarioEsqueciSenha($token);
	if($idUsuario != null){
		try {   
			$conect = $GLOBALS['pdo'];
			$conect->beginTransaction(); 
						
			$consulta = $conect->prepare("UPDATE usuario SET senha = ? WHERE id = ?");
			$consulta->bindParam(1,	$senha, PDO::PARAM_STR);
			$consulta->bindParam(2,	$idUsuario, PDO::PARAM_INT);
			$consulta->execute();		

			$conect->commit();
			return json_encode(array('resultado' => 'true'));
		} catch (Exception $e) {  		
			return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
		}
	} else {
		return json_encode(array('resultado' => 'false'));
	}
}

function verificaUsuarioLogado($token) { // Esse método valida o token do usuário
	$vetor = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT id_usuario, data_init FROM token WHERE token = ? AND deletado = 0");
		$consulta->bindParam(1,	$token, PDO::PARAM_STR);
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	
	
		if($vetor !== null) {// Caso o usuário esteja logado
			date_default_timezone_set('America/recife');

			$dataAtual = date('Y-m-d H:i');

			if(validaTempoUsuario($vetor[0]['data_init']) == 0){
				return 0;
			}
			
			return $vetor[0]['id_usuario'];
		} else { // Caso o usuário não esteja logado
			return 0;
		}
	} catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function validaTempoUsuario($data_init) { // Esse método verifica se o usuario está a mais de 20 min sem ação, e retorna 1 caso passe do tempo
    date_default_timezone_set('America/recife');
	$dataAtual = date('Y-m-d H:i');
	
    $dia = intval(dateDifference($data_init, $dataAtual, 'd'));
    $hora = dateDifference($data_init, $dataAtual, 'h');
    $min = dateDifference($data_init, $dataAtual, 'i');

    if($dia == 0) {
        if($hora > 0){
            return 0;
        } else {
            if($min >= 20){
                return 0;
            } else {
                return 1;
            }
        }
    } else {
        return 0;
    }
}
function verificaUsuarioExistente($login) { // Esse método verifica se o login inserido já existe
	$vetor = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT id FROM usuario WHERE login = ?");
		$consulta->bindParam(1,	$login, PDO::PARAM_INT);	
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	
		if($vetor != null){ // Caso o login já exista no sistema
			return true;
		} else {
			return false;
		}
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function receberArquivo($arquivo, $idUsuario, $idPasta){ // Esse método faz o upload dos arquivos enviados
	header('Content-type: application/json');

	$upload = [];
	$allowed = ['jpeg', 'png', 'jpg', 'txt', 'pdf', 'PDF', 'rar', 'zip']; // Extensão de arquivos suportados

	$succeeded = [];
	$failed = [];

	try{
		foreach ($arquivo['name'] as $key => $name) {
			if($arquivo['error'][$key] === 0) {

				$temp = $arquivo['tmp_name'][$key]; // Nome e localização temporária do arquivo
				
				$ext = explode('.', $name);

				$ext = strtolower(end($ext)); // Recebe o ultimo valor do array

				$file = md5_file($temp) . time() . '.' . $ext; // Configura um nome próprio para o arquivo
				
				// Envia arquivo para pasta selecionada
				if(in_array($ext, $allowed) === true && move_uploaded_file($temp, "../uploads/{$file}") === true) {
				
					date_default_timezone_set('America/recife');
	
					$dataCriacao = date('Y-m-d H:i');
					
					inserirArquivo($idUsuario, $idPasta, $file, $dataCriacao, $name);
	
					$succeeded[] = array(
						'name' => $name,
						'file' => $file
					);
				} else {
					$failed[] = array(
						'name' => $name
					);
				}
			}
		}
	} catch(Exception $e){
		$failed[] = array(
			'name' => 'Arquivos muito grandes ou extensões não permitidas.'
		);
	}
	

	if(!empty($_POST['ajax'])) {
		return json_encode(array(
			'succeeded' => $succeeded,
			'failed' => $failed
		));
	}
}	

function criarPasta($idUsuario, $idPastaOrigem, $descricao) { // Esse método cria uma pasta
	if($idPastaOrigem == null || $idPastaOrigem == 0){
		$idPastaOrigem = -1;
	}
	// Testa para saber se o nome da pasta já existe nesse diretório
	// Retorna null caso não exista
	if(buscarPastaPorNome($descricao, $idPastaOrigem, $idUsuario) == null){
		date_default_timezone_set('America/recife');

		$dataCriacao = date('Y-m-d H:i');
		try {   
			$conect = $GLOBALS['pdo'];
			$conect->beginTransaction(); 
							
			$consulta = $conect->prepare("INSERT INTO pasta (id_usuario_criador, id_pasta_origem, data_criacao, descricao, deletado, id_criador_comp) 
										VALUES (?,?,?,?,?,?)");		 	
			$consulta->bindParam(1,	$idUsuario);		 	
			$consulta->bindParam(2,	$idPastaOrigem);		 	
			$consulta->bindParam(3,	$dataCriacao);
			$consulta->bindParam(4,	$descricao);
			$consulta->bindValue(5,	0); // Valor 0 para indicar que não está deletado
			$consulta->bindParam(6,	$idUsuario);
			$consulta->execute();		
					
			$conect->commit();
			
			// Insere o usuário na tabela usuario_pasta com o ID da ultima pasta
			inserirUsuarioPasta($idUsuario, idPasta());
			return json_encode($idPastaOrigem);
		} catch (Exception $e) {  		
			return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
		}
	} else {
		$erro = array('erro' => 'true');
		return json_encode($erro);
	}
	
}

function inserirUsuario($nome, $login, $senha) { // Esse método cria um novo usuário
	$resultado = [];
	// Verifica se já existe um login igual ao que o usuário digitou
	// Caso o login não exista
	if(!verificaUsuarioExistente($login)){
		try {   
			$conect = $GLOBALS['pdo'];
			$conect->beginTransaction(); 
							
			$consulta = $conect->prepare("INSERT INTO usuario (login, senha, nome, deletado) VALUES (?,?,?,?)");			 	
			$consulta->bindParam(1,	$login, PDO::PARAM_STR);		 	
			$consulta->bindParam(2,	$senha, PDO::PARAM_STR);		 	
			$consulta->bindParam(3,	$nome, PDO::PARAM_STR);		 	
			$consulta->bindValue(4,	0, PDO::PARAM_STR); 	
			$consulta->execute();		
					
			$conect->commit();	

			$resultado = array(
				'resultado' => 'true'
			);
			return json_encode($resultado);
		}
		catch (Exception $e) {  		
			return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
		}
	} else { // Caso já exista um login
		$resultado = array(
			'resultado' => 'false',
			'erro' => 'O login já existe!'
		);
		return json_encode($resultado);
	}
}
function inserirArquivo($idUsuario, $idPasta, $file, $dataCriacao, $name) { // Esse método cadastra o arquivo enviado no DB
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
						
		$consulta = $conect->prepare("INSERT INTO arquivo (id_usuario_criador, id_pasta, descricao, data_criacao, nome_original, deletado)
											VALUES (?, ?, ?, ?, ?, ?)");			 	
		$consulta->bindParam(1,	$idUsuario, PDO::PARAM_INT);		 	
		$consulta->bindParam(2,	$idPasta, PDO::PARAM_INT);		 	
		$consulta->bindParam(3,	$file, PDO::PARAM_STR);		 	
		$consulta->bindParam(4,	$dataCriacao, PDO::PARAM_STR);		 	
		$consulta->bindParam(5,	$name, PDO::PARAM_STR);		
		$consulta->bindValue(6,	0, PDO::PARAM_INT);	 	
		$consulta->execute();		
				
		$conect->commit();	
		return '[{"result": "true","p1":""}]';
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function inserirUsuarioPasta($idUsuario, $idPasta) { // Esse método insere o usuario à tabela usuario_pasta, dando permissão a ela para acessar essa pasta
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
						
		$consulta = $conect->prepare("INSERT INTO usuario_pasta (id_usuario, id_pasta) VALUES (?, ?)");		 	
		$consulta->bindValue(1,	$idUsuario);		 	
		$consulta->bindValue(2,	$idPasta);
		$consulta->execute();		
				
		$conect->commit();
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
} 
function inserirToken($token, $dataInit, $idUsuario) { // Esse método insere o token no DB
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
						
		$consulta = $conect->prepare("INSERT INTO token (token, data_init, id_usuario, deletado) VALUES (?,?,?,?)");		 	
		$consulta->bindParam(1,	$token);		 	
		$consulta->bindParam(2,	$dataInit);		 	
		$consulta->bindParam(3,	$idUsuario);
		$consulta->bindValue(4,	0);
		$consulta->execute();		
				
		return $conect->commit();	
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}

function listarUsuarios($id) { // Esse método lista os usuários
	$vetor = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT id, nome, login 
									FROM `usuario` 
									WHERE id != ? AND deletado = 0 ORDER BY nome ASC");
		$consulta->bindParam(1,	$id);	
		$consulta->execute();
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}
		$conect->commit();	
		return json_encode($vetor);
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function listarDiretorio($idPasta, $idUsuario){ // Esse método lista as pasta do usuário logado, além de seus arquivos
	if($idPasta == '') {
		$idPasta = -1;	
	}
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
		
		$consulta = $conect->prepare("SELECT 0 as tipo, p.id_pasta, p.descricao as nome, p.id_pasta_origem as diretorio 
									FROM pasta p
									JOIN usuario_pasta up on up.id_pasta = p.id_pasta
									JOIN usuario u on u.id = up.id_usuario
									WHERE up.id_usuario = ? AND p.id_pasta_origem = ? AND p.id_usuario_criador = ?
									AND p.deletado = 0 
									UNION ALL
									SELECT 1 as tipo, a.id_arquivo, a.nome_original as nome,  a.descricao
									FROM arquivo a
									WHERE a.id_pasta = ? AND deletado = 0
									ORDER BY tipo, nome ASC");
		$consulta->bindParam(1,	$idUsuario, PDO::PARAM_INT);
		$consulta->bindParam(2,	$idPasta, PDO::PARAM_INT);
		$consulta->bindParam(3,	$idUsuario, PDO::PARAM_INT);
		$consulta->bindParam(4,	$idPasta, PDO::PARAM_INT);	
		$consulta->execute();
		$vetor = null;		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	
		if($vetor !== null){ // Caso a pasta esteja preenchida
			return json_encode($vetor);
		} else { // Caso a pasta esteja vazia
			$vetor = array(
				'idPasta'=> $idPasta
			);
			return json_encode($vetor);
		}
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function listarPastasAnteriores($idPastaAtual){ // Esse método lista o caminho percorrido pelo usuário
	if($idPastaAtual == null){
		$idPastaAtual = 1;
	}
	$contador = 0;
	$posicao = 0; // Não está sendo usado
	// Busca o id da pasta origem da pasta passada
	$resultado = buscarIdPastaAnterior($idPastaAtual);
	
	$aux = $resultado[0]['id_pasta_origem']; // Adiciona o primeiro elemento buscado para buscar o próximo futuramente
	$idPasta[$contador] = $resultado[0]['id_pasta']; 
	$descricao[$contador] = $resultado[0]['descricao'];
	while($aux != null) { // Caso ainda exista
		$contador++;
		$resultado = buscarIdPastaAnterior($aux); // Busca pelo próximo id do pai passado	
		$aux = $resultado[0]['id_pasta_origem']; // Atribui o resultado para aux
		$descricao[$contador] = $resultado[0]['descricao']; // Adiciona o nome no array na posição do contador
		$idPasta[$contador] = $resultado[0]['id_pasta']; // Adiciona o id no array na posição do contador
	}
	$retorno = array(
		'descricao' => $descricao,
		'idPasta' => $idPasta
	);
	return json_encode($retorno);	
}

function idPasta() { // Esse método retorna o id da ultima pasta criada no sistema
	$id = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT MAX(id_pasta) as id_pasta from pasta");
		$consulta->execute();
		$id = $consulta->fetch(PDO::FETCH_ASSOC)['id_pasta'];
		$conect->commit();	
		return $id;
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function idUltimoUsuario() { // Esse método retorna o id do ultimo usuário criado
	$id = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT MAX(id) as id_usuario from usuario");
		$consulta->execute();
		$id = $consulta->fetch(PDO::FETCH_ASSOC)['id_usuario'];
		$conect->commit();	
		return $id;
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}

function buscarUsuarioCriador($idPasta) { // Esse método retorna o id do usuario que criou a pasta
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT id_usuario_criador as id
									FROM pasta 
									WHERE id_pasta = ?");
		$consulta->bindParam(1,	$idPasta, PDO::PARAM_INT);	
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	

		return $vetor;
	} catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function buscaPastaPai($idPasta){ // Esse método retorna o id da pasta de origem da pasta passada por parâmetro
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT p.id_pasta_origem 
									FROM pasta p
									WHERE p.id_pasta = ?");
		$consulta->bindParam(1,	$idPasta, PDO::PARAM_INT);	
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	
	
		return json_encode($vetor);
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function buscarPastaPorNome($nome, $idPastaOrigem, $idUsuario){ // Esse método retorna o id e a descrição da pasta passada por parâmetro
	$vetor = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT id_pasta, descricao 
									FROM `pasta` 
									WHERE descricao = ? AND id_pasta_origem = ? 
														AND id_usuario_criador = ? 
														AND deletado = 0");
		$consulta->bindParam(1,	$nome, PDO::PARAM_STR);	
		$consulta->bindParam(2,	$idPastaOrigem, PDO::PARAM_INT);	
		$consulta->bindParam(3,	$idUsuario, PDO::PARAM_INT);
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	
	
		return $vetor;
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function buscarIdPastaAnterior($idPastaAtual) { // Esse método retorno o id_pasta_origem, descricao e id_pasta da pasta passada por parâmetro
	$vetor = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT id_pasta_origem, descricao, id_pasta FROM pasta WHERE id_pasta = ?");
		$consulta->bindValue(1,	$idPastaAtual, PDO::PARAM_INT);	
		$consulta->execute();

		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}
		$conect->commit();	
		return $vetor;
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function searchUserByEmail($email) { // Esse método o id do usuario baseado no login inserido
	$vetor = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT id FROM usuario WHERE login = ?");
		$consulta->bindParam(1,	$email, PDO::PARAM_INT);	
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	

		if($vetor != null){
			return $vetor[0]['id'];
		} else {
			return $vetor;
		}
		
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function buscarUsuarioEsqueciSenha($token) { // Esse método verifica se o token do url existe no DB e a que usuario ele pertence
	$vetor = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT id_usuario FROM esqueci_senha WHERE token = ?");
		$consulta->bindParam(1,	$token, PDO::PARAM_INT);	
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	

		if($vetor != null){
			return $vetor[0]['id_usuario'];
		} else {
			return $vetor;
		}
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}

function editarPasta($idPasta, $descricao, $idPastaOrigem, $idUsuario) { // Esse método edita uma pasta
	if(buscarPastaPorNome($descricao, $idPastaOrigem, $idUsuario) == null){
		try {   
			$conect = $GLOBALS['pdo'];
			$conect->beginTransaction(); 
						
			$consulta = $conect->prepare("UPDATE pasta SET descricao = ? WHERE id_pasta = ?");
			$consulta->bindParam(1,	$descricao, PDO::PARAM_INT);
			$consulta->bindParam(2,	$idPasta, PDO::PARAM_INT);
			$consulta->execute();		

			$conect->commit();
			return json_encode(array(
				'resultado' => 'true'
			));
		}
		catch (Exception $e) {  		
			return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
		}
	} else {
		return json_encode(array(
			'resultado' => 'false'
		));
	}
}
function editarDataHoraUsuario($token) { // Esse método atualiza a data e a hora do usuário para o valor atual
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 

		date_default_timezone_set('America/recife');

		$dataInit = date('Y-m-d H:i');
					
		$consulta = $conect->prepare("UPDATE token SET data_init = ? WHERE token = ?");
		$consulta->bindParam(1,	$dataInit, PDO::PARAM_STR);
		$consulta->bindParam(2,	$token, PDO::PARAM_STR);
		$consulta->execute();		

		$conect->commit();
		return json_encode(array(
			'resultado' => 'true'
		));
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}

function deletarPasta($idPasta, $idUsuario) { // Esse método deleta uma pasta (Deleção lógica. 0 ou 1)
	$buscaArray = json_decode(listarConteudoPastaComp($idPasta));
	
	if (array_key_exists("idPasta", $buscaArray)) {
		try {   
			$conect = $GLOBALS['pdo'];
			$conect->beginTransaction(); 
						
			$consulta = $conect->prepare("UPDATE pasta SET deletado = 1 WHERE id_pasta = ?");
			$consulta->bindParam(1,	$idPasta, PDO::PARAM_INT);
			$consulta->execute();		

			$conect->commit();
			return json_encode(array(
				'resultado' => 'true'
			));
		}
		catch (Exception $e) {  		
			return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
		}
	} else {
		return json_encode(array(
			'resultado' => 'false'
		));
	}
}
function deletarArquivo($arquivo) { // Esse método deleta um arquivo (Deleção lógica. 0 ou 1)
	$descricao = substr($arquivo, 11);
	
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("UPDATE arquivo SET deletado = 1 WHERE descricao = ?");
		$consulta->bindParam(1,	$descricao, PDO::PARAM_STR);
		$consulta->execute();		

		return $conect->commit();
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function deletaToken($token) { // Esse método deleta o token (Deleção lógica. 0 ou 1)
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("UPDATE token SET deletado = 1 WHERE token = ?");
		$consulta->bindParam(1,	$token, PDO::PARAM_STR);
		$consulta->execute();		

		return $conect->commit();
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}

function dateDifference($date_1, $date_2, $t){ // Esse método retorna o intervalo de tempo de acordo com o tipo escolhido. d=dia; h=hora...
	$differenceFormat = '%'.$t;
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);    
    $interval = date_diff($datetime1, $datetime2);    
    return $interval->format($differenceFormat);    
}

//Compartilhados
function verificaPermissaoComp($idUsuario, $idPasta) { // Esse método verifica se o usuário é o dono da pasta para evitar
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
		
		$consulta = $conect->prepare("SELECT p.descricao
										FROM pasta p
										WHERE p.id_usuario_criador = ? AND p.id_pasta = ?");
		$consulta->bindParam(1,	$idUsuario, PDO::PARAM_INT);
		$consulta->bindParam(2,	$idPasta, PDO::PARAM_INT);
		$consulta->execute();
		$vetor = null;		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	

		return $vetor;
	} catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}

function criarPastaComp($idUsuario, $idPastaOrigem, $descricao) { // Esse método cria uma pasta em uma pasta já compartilhada
	if($idPastaOrigem == null || $idPastaOrigem == 0){
		$idPastaOrigem = -1;
		return json_encode(array(
			'resultado' => 'false',
			'idPastaAtual' => $idPastaOrigem
		));
	}
	$idUsuarioCriador = buscarUsuarioCriador($idPastaOrigem)[0]['id'];
	date_default_timezone_set('America/recife');

	$dataCriacao = date('Y-m-d H:i');

	if(buscarPastaPorNome($descricao, $idPastaOrigem, $idUsuarioCriador) == null){
		try {   
			$conect = $GLOBALS['pdo'];
			$conect->beginTransaction(); 
							
			$consulta = $conect->prepare("INSERT INTO pasta (id_usuario_criador, id_pasta_origem, data_criacao, descricao, deletado, id_criador_comp) 
										VALUES (?,?,?,?,?,?)");		 	
			$consulta->bindParam(1,	$idUsuarioCriador);		 	
			$consulta->bindParam(2,	$idPastaOrigem);		 	
			$consulta->bindParam(3,	$dataCriacao);
			$consulta->bindParam(4,	$descricao);
			$consulta->bindValue(5,	0);
			$consulta->bindParam(6,	$idUsuario);
			$consulta->execute();		
					
			$conect->commit();

			inserirUsuarioPasta($idUsuarioCriador, idPasta());
			return json_encode(array(
				'resultado' => 'true',
				'idPastaAtual' => $idPastaOrigem
			));
		}
		catch (Exception $e) {  		
			return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
		}
	} else {
		$erro = array('resultado' => 'false');
		echo json_encode($erro);
	}
}

function compartilharPasta($idUsuario, $idPasta, $idUsuarioCriador) { // Esse método compartilha uma pasta
	if(verificaPermissaoComp($idUsuarioCriador, $idPasta) != null) { // Valida se o usuário é mesmo dono da pasta
		inserirUsuarioPasta($idUsuario, $idPasta); // Dando permissão para o usuário acessar a pasta
		return json_encode(array('resultado' => 'true'));
	} else {
		return json_encode(array('resultado' => 'false'));
	}
}
function compartilharArquivo($idUsuario, $idArquivo) { // Esse método compartilha um arquivo
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
						
		$consulta = $conect->prepare("INSERT INTO usuario_arquivo (id_arquivo, id_usuario)
											VALUES (?, ?)");			 	
		$consulta->bindParam(1,	$idArquivo, PDO::PARAM_INT);		 	
		$consulta->bindParam(2,	$idUsuario, PDO::PARAM_INT); 	
		$consulta->execute();		
				
		$conect->commit();	
		return '[{"result": "true","p1":""}]';
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}

function deletarCompartilharPasta($idUsuario, $idPasta) { // Esse método remove um usuario que compartilha a pasta
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("DELETE FROM usuario_pasta WHERE id_usuario = ? AND id_pasta = ?");
		$consulta->bindParam(1,	$idUsuario, PDO::PARAM_INT);
		$consulta->bindParam(2,	$idPasta, PDO::PARAM_INT);
		$consulta->execute();		

		$conect->commit();

		return json_encode(array(
			'resultado' => 'true'
		));
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function deletarCompartilharArquivo($idUsuario, $idArquivo) { // Esse método remove um usuario que compartilha o arquivo
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("DELETE FROM usuario_arquivo WHERE id_usuario = ? AND id_arquivo = ?");
		$consulta->bindParam(1,	$idUsuario, PDO::PARAM_INT);
		$consulta->bindParam(2,	$idArquivo, PDO::PARAM_INT);
		$consulta->execute();		

		$conect->commit();

		return json_encode(array(
			'resultado' => 'true'
		));
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}

function listarDiretorioCompartilhado($idUsuario) { // Esse método lista as pastas que são compartilhadas
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					 
		$consulta = $conect->prepare("SELECT 0 as tipo, p.id_pasta, p.descricao as nome, p.id_pasta_origem as diretorio 
									FROM pasta p 
									JOIN usuario_pasta up
									ON p.id_pasta = up.id_pasta
									WHERE up.id_usuario = ? AND p.id_usuario_criador != ? AND deletado = 0");
		$consulta->bindParam(1,	$idUsuario, PDO::PARAM_INT);
		$consulta->bindParam(2,	$idUsuario, PDO::PARAM_INT);	
		$consulta->execute();
		$vetor = null;		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	
		if($vetor !== null){
			return json_encode($vetor);
		} else {
			$vetor = array(
				'idPasta'=> '2'
			);
			return json_encode($vetor);
		}
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function listarConteudoPastaComp($idPasta) { // Esse método lsita o conteúdo de uma pasta compartilhada
	if($idPasta == '') {
		$idPasta = -1;	
	}
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT 0 as tipo, p.id_pasta, p.descricao as nome, p.id_pasta_origem as diretorio
									FROM pasta p
									WHERE p.id_pasta_origem = ? AND deletado = 0 
									UNION ALL
									SELECT 1 as tipo, a.id_arquivo, a.nome_original as nome,  a.descricao
									FROM arquivo a
									WHERE a.id_pasta = ? AND deletado = 0
									ORDER BY tipo, nome ASC");
		$consulta->bindParam(1,	$idPasta, PDO::PARAM_INT);
		$consulta->bindParam(2,	$idPasta, PDO::PARAM_INT);	
		$consulta->execute();
		$vetor = null;		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	
		if($vetor !== null){
			return json_encode($vetor);
		} else {
			$vetor = array(
				'idPasta'=> $idPasta
			);
			return json_encode($vetor);
		}
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function listarUsuariosComp($idPasta, $idUsuario) { // Esse método lista os usuários que compartilham a pasta passada por parâmetro
	$vetor = null; 
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT up.id_usuario, u.nome, u.login
									FROM usuario_pasta up
									JOIN pasta p 
									ON p.id_pasta = up.id_pasta
									JOIN usuario u 
									ON u.id = up.id_usuario
									WHERE up.id_pasta = ? AND up.id_usuario != ?
									ORDER BY u.nome ASC");	
		$consulta->bindParam(1,	$idPasta);
		$consulta->bindParam(2,	$idUsuario);
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	

		return json_encode($vetor);
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function listarPastaAnteriorComp($idPastaAtual, $idPastaLimite, $idUsuario) { // Esse método retorna o caminho percorrido pelo usuário
	$listaPastas[] = json_decode(listarPastasAnteriores($idPastaAtual));
	$count = count($listaPastas[0]->idPasta);

	$idPasta[] = null;
	$descricao[] = null;
	$contador = 0;

	for($i = 0; $i < $count; $i++){
		$idPasta[$contador] = $listaPastas[0]->idPasta[$i];
		$descricao[$contador] = $listaPastas[0]->descricao[$i];
		$contador++;
		if($idPastaLimite == $listaPastas[0]->idPasta[$i]){
			return json_encode(array(
				'descricao' => $descricao,
				'idPasta' => $idPasta
			));
		}	
	}
}
function listarArquivoCompartilhado($idUsuario) { // Esse método lista os arquivos compartilhados que não estão em uma pasta
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					 
		$consulta = $conect->prepare("SELECT a.id_pasta, a.id_arquivo, a.id_usuario_criador, a.descricao, a.nome_original
									FROM arquivo a
									JOIN usuario_arquivo ua
									ON a.id_arquivo = ua.id_arquivo
									WHERE ua.id_usuario = ? AND deletado = 0
									ORDER BY a.nome_original ASC");
		$consulta->bindParam(1,	$idUsuario, PDO::PARAM_INT);
		$consulta->execute();
		$vetor = null;		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	
		if($vetor !== null){
			return json_encode(array(
				'resultado' => 'true',
				'parametros' => $vetor
			));
		} else {
			return json_encode(array(
				'resultado'=> 'false'
			));
		}
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function listarUsuariosArquivosComp($idArquivo) { // Esse método lista os usuários que compartilham o arquivo passado por parâmentro
	$vetor = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT u.id, u.nome, u.login
									FROM arquivo a 
									JOIN usuario_arquivo ua 
									ON a.id_arquivo = ua.id_arquivo
									JOIN usuario u
									ON ua.id_usuario = u.id
									WHERE a.id_arquivo = ?
									ORDER BY u.nome ASC");	
		$consulta->bindParam(1,	$idArquivo);
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	

		if($vetor != null){
			return json_encode(array(
				'resultado' => 'true',
				'parametros' => $vetor
			));
		} else {
			return json_encode(array(
				'resultado' => 'false'
			));
		}
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}

function buscarUsuariosPastaComp($idPasta) { // Esse método lista os usuários que compartilham a pasta passada por parâmetro
	$vetor = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT up.id_usuario FROM  usuario_pasta up WHERE id_pasta = ?");	
		$consulta->bindParam(1,	$idPasta);
		$consulta->execute();		
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	

		return $vetor;
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function buscarPastaPaiComp($idPasta, $idUsuario) { // Esse método retorna o id_pasta_origem de uma pasta passada por parâmetro,
													//em um ambiente compartilhado, onde o usuário tem permissão
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT up.id_pasta
									FROM usuario_pasta up
									WHERE up.id_pasta in (SELECT p.id_pasta
                  										FROM pasta p
                  										WHERE p.id_pasta_origem = ?)
											AND up.id_usuario = ?");
		$consulta->bindParam(1,	$idPasta, PDO::PARAM_INT);	
		$consulta->bindParam(2,	$idUsuario, PDO::PARAM_INT);	
		$consulta->execute();	
		$vetor = null;	
		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}		
		$conect->commit();	
	
		return json_encode($vetor);
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}
function buscarIdPastaAnteriorComp($idPastaAtual, $idUsuario) { // Esse método todos os dados, em um ambiente compartilhado,
																// onde o usuário tem permissão, baseado na pasta atual
	$vetor = null;
	try {   
		$conect = $GLOBALS['pdo'];
		$conect->beginTransaction(); 
					
		$consulta = $conect->prepare("SELECT p.* 
									FROM pasta p 
									JOIN usuario_pasta up
									ON p.id_pasta_origem = up.id_pasta
									WHERE up.id_usuario = ? AND p.id_pasta = ?");
		$consulta->bindValue(1,	$idUsuario, PDO::PARAM_INT);	
		$consulta->bindValue(2,	$idPastaAtual, PDO::PARAM_INT);	
		$consulta->execute();

		while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){$vetor[] = ($linha);}
		$conect->commit();	
		return $vetor;
	}
	catch (Exception $e) {  		
		return '[{"result": "false","pe":"'.encode_texto($e->getMessage()).'"}]';
	}
}