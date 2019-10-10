<?php
#slim
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
#csv
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\LexerConfig;

require 'vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('views', []);
    return $view;
};

$container['dbconn'] = function ($container) {
  global $conn;
  
  if ($conn == null) {
	$conn = new \PDO('sqlsrv:SERVER=DESKTOP-7P45RR1\SQLEXPRESS;DATABASE=DB_CSV', 'sa', 'Bqnepc40');
	$conn->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );
  }
  return $conn;
};


$app->get('/salvar', function (Request $request, Response $response, array $args) {
	return $this->view->render ($response, 'salvar.html', []);
});

$app->post('/upload', function (Request $request, Response $response, array $args) {
	#limpar banco de dados 
	$conn = $this->dbconn;
	$stmt = $conn->prepare ("delete from [dbo].[dados_auditoria_csv]");
	$stmt->execute ();
	#salvar arquivo
	$files = $request->getUploadedFiles();
	$file = $files["dados_documento"];
	if (!($file->getError() === UPLOAD_ERR_OK)) {
		throw new \Exception ("Upload não aconteceu.");
	}
	$data_csv = $file->moveTo('auditoria.csv');
	/*
	$array_dados_csv = str_getcsv ( $data_csv , ";");
	
	*/
	#global $arrayDadosCSV;
	global $str_sql_incluir;
	
	#$arrayDadosCSV = [];
	
	#parse file 
	$lexerConfig = new LexerConfig ();
	$lexerConfig->setDelimiter (",");
	$lexer = new Lexer ($lexerConfig);
	$interpreter = new Interpreter ();
	#$interpreter->unstrict();
	global $quantidadeRegistros;
	global $arrayDadosCSV;
	global $funcSendBD;
	global $funcBDProcessBatch;
	$quantidadeRegistros = 0;
	$arrayDadosCSV = [];
	$funcSendBD = function () {
		global $arrayDadosCSV;
		global $quantidadeRegistros;
	  
	  if ($quantidadeRegistros == 0) return;
	  $conn = $this->dbconn;
	  $str_insert_command = "
	     INSERT INTO [dbo].[dados_auditoria_csv]
           ([VP_CX]
           ,[DI]
           ,[Cd_Grupo_Auditoria]
           ,[Nm_Trabalho_Plano]
           ,[AUDIR]
           ,[RA]
           ,[Cd_Apontamento]
           ,[Nr_Apontamento]
           ,[Nm_Apontamento]
           ,[Dc_Apontamento]
           ,[Criticidade_Apontamento]
           ,[Abrangência_Apontamento]
           ,[Cd_Recomendacao]
           ,[Nr_Recomendacao]
           ,[Dc_Recomendacao]
           ,[Cd_Homologador]
           ,[Sg_Homologador]
           ,[Cd_SeqGestor]
           ,[Cd_Gestor]
           ,[Sg_Gestor]
           ,[Situação_Plano]
           ,[Dt_Fim_Preenchimento]
           ,[Dt_Preenchimento]
           ,[Dt_Fim_Homologacao]
           ,[Dt_Homologacao]
           ,[Data_Fim_Parecer]
           ,[Cd_Acao]
           ,[Nr_Acao]
           ,[Dc_Acao]
           ,[Dt_Fim_Acao]
           ,[Dt_Regul_Acao]
           ,[Dt_Fim_PA]
           ,[Dt_Regularizada]
           ,[prazo_parecer]
           ,[data_parecer]
           ,[Dc_Regularizacao])
     VALUES
	 ";
	 $str_insert_command .= "('" .$arrayDadosCSV [0][0] ."', '" .
								  $arrayDadosCSV [0][1] ."', '" .
								  $arrayDadosCSV [0][2] ."', '" .
								  $arrayDadosCSV [0][3] ."', '" .
								  $arrayDadosCSV [0][4] ."', '" .
								  $arrayDadosCSV [0][5] ."', '" .
								  $arrayDadosCSV [0][6] ."', '" .
								  $arrayDadosCSV [0][7] ."', '" .
								  $arrayDadosCSV [0][8] ."', '" .
								  $arrayDadosCSV [0][9] ."', '" .
								  $arrayDadosCSV [0][10] ."', '" .
								  $arrayDadosCSV [0][11] ."', '" .
								  $arrayDadosCSV [0][12] ."', '" .
								  $arrayDadosCSV [0][13] ."', '" .
								  $arrayDadosCSV [0][14] ."', '" .
								  $arrayDadosCSV [0][15] ."', '" .
								  $arrayDadosCSV [0][16] ."', '" .
								  $arrayDadosCSV [0][17] ."', '" .
								  $arrayDadosCSV [0][18] ."', '" .
								  $arrayDadosCSV [0][19] ."', '" .
								  $arrayDadosCSV [0][20] ."', " .
								  " convert (date, '" .$arrayDadosCSV [0][21] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [0][22] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [0][23] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [0][24] ."', 103) , " .
								  " convert (datetime, '" .$arrayDadosCSV [0][25] ."', 103) , '" .
								  $arrayDadosCSV [0][26] ."', '" .
								  $arrayDadosCSV [0][27] ."', '" .
								  $arrayDadosCSV [0][28] ."', " .
								  " convert (date, '" .$arrayDadosCSV [0][29] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [0][30] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [0][31] ."', 103) , " .
								  " convert (datetime, '" .$arrayDadosCSV [0][32] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [0][33] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [0][34] ."', 103) , '" .
								  $arrayDadosCSV [0][35] ."' )";
      for ($cont = 1; $cont < $quantidadeRegistros; $cont++)
        $str_insert_command .= ",('" .$arrayDadosCSV [$cont][0] ."', '" .
								  $arrayDadosCSV [$cont][1] ."', '" .
								  $arrayDadosCSV [$cont][2] ."', '" .
								  $arrayDadosCSV [$cont][3] ."', '" .
								  $arrayDadosCSV [$cont][4] ."', '" .
								  $arrayDadosCSV [$cont][5] ."', '" .
								  $arrayDadosCSV [$cont][6] ."', '" .
								  $arrayDadosCSV [$cont][7] ."', '" .
								  $arrayDadosCSV [$cont][8] ."', '" .
								  $arrayDadosCSV [$cont][9] ."', '" .
								  $arrayDadosCSV [$cont][10] ."', '" .
								  $arrayDadosCSV [$cont][11] ."', '" .
								  $arrayDadosCSV [$cont][12] ."', '" .
								  $arrayDadosCSV [$cont][13] ."', '" .
								  $arrayDadosCSV [$cont][14] ."', '" .
								  $arrayDadosCSV [$cont][15] ."', '" .
								  $arrayDadosCSV [$cont][16] ."', '" .
								  $arrayDadosCSV [$cont][17] ."', '" .
								  $arrayDadosCSV [$cont][18] ."', '" .
								  $arrayDadosCSV [$cont][19] ."', '" .
								  $arrayDadosCSV [$cont][20] ."', " .
								  " convert (date, '" .$arrayDadosCSV [$cont][21] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [$cont][22] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [$cont][23] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [$cont][24] ."', 103) , " .
								  " convert (datetime, '" .$arrayDadosCSV [$cont][25] ."', 103) , '" .
								  $arrayDadosCSV [$cont][26] ."', '" .
								  $arrayDadosCSV [$cont][27] ."', '" .
								  $arrayDadosCSV [$cont][28] ."', " .
								  " convert (date, '" .$arrayDadosCSV [$cont][29] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [$cont][30] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [$cont][31] ."', 103) , " .
								  " convert (datetime, '" .$arrayDadosCSV [$cont][32] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [$cont][33] ."', 103) , " .
								  " convert (date, '" .$arrayDadosCSV [$cont][34] ."', 103) , '" .
								  $arrayDadosCSV [$cont][35] ."' )";
      #die (var_dump ($str_insert_command)); 
	  $stmt = $conn->prepare ($str_insert_command );
	  $stmt->execute();
      	  
	};
	$funcBDProcessBatch = function () {
	  global $quantidadeRegistros;
	  global $arrayDadosCSV;
	  global $funcSendBD;
	  
	  $funcSendBD ();
	  $quantidadeRegistros = 0;
	  $arrayDadosCSV = [];
	};
	$interpreter->addObserver (function (array $row) {
	  global $arrayDadosCSV;
	  global $quantidadeRegistros;
	  global $funcBDProcessBatch;
	  
	  #array_push ($arrayDadosCSV, $row);
	  if ($row[1] != 'DI') {
		array_push ($arrayDadosCSV, $row);
		$quantidadeRegistros++;
	  }
	  if ($quantidadeRegistros == 25) {
		$funcBDProcessBatch ();
	  }
	});
	$lexer->parse ('auditoria.csv', $interpreter);
	$funcSendBD ();
	#exibir na tela
	#return $this->view->render ($response, 'conteudo_csv.html', [
	#  'dados_csv' => $arrayDadosCSV
	#]);	
	#gerar comando sql 
	#save file
	return $response->withRedirect ('visualizarBD', 301);
});

$app->get('/visualizarBD', function (Request $request, Response $response, array $args) {
	$conn = $this->dbconn;
	$stmt = $conn->prepare ("select * from [dbo].[dados_auditoria_csv]");
	$stmt->execute ();
	$arrayDadosCSV = $stmt->fetchAll (PDO::FETCH_ASSOC);
	return $this->view->render ($response, 'conteudo_csv.html', [
	  'dados_csv' => $arrayDadosCSV
	]);
});


$app->run();
?>