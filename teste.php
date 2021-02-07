<!--

    Para o nosso teste de aptidão, utilizando os dados contidos em nosso repositório (https://drive.google.com/file/d/1hLpSH-2wYjNIekQUSls4fkoYE8c97JR2/view), 
    desenvolva uma aplicação utilizando o PHP 7.x que atenda os seguintes requisitos:
    
    obs: Dúvidas referentes ao conteúdo do teste, métodos e lógicas não serão respondidos pois a interpretação do texto e a capacidade de resolução são critérios de avaliação.

1 - Carregar todas as publicações em memória;
2 - Identificar as publicações pertencentes a 4ª Vara da Família e Sucessões;
3 - Identificar as publicações referentes as ações de Alimentos, Divórcio, Investigação de Paternidade, Inventário, Outros;
3 - Cada documento deverá receber um atributo contendo o número do processo da publicação extraído do texto;
4 - Cada documento deverá receber um atributo contendo o nome do juiz responsável pelo processo extraído do texto;
5 - Como arquivo de saída, deverão ser criados dois conjuntos de arquivos json:
5.1 - Arquivos separados por cada tipo de ação, tendo como critério de ordenação o nome do juiz responsável;
5.2 - Arquivos separados por cada tipo de ação e Juiz responsável, tendo como critério de ordenação o número do processo;
5.3 - Arquivo contendo as publicações não pertencentes a 4a Vara da Família e Sucessões.


-->

<html>
 <head>
  <title>Teste PHP</title>
 </head>
 <body>
 <?php

$sqlite = "sqlite:pdo.db";
  
// conexão ao sqlite
$pdo = new PDO($sqlite);

//Como o .sql não contém as informações do banco, criei tabela e coluna para o conteudo
$execucao = $pdo->prepare("CREATE TABLE IF NOT EXISTS publicacoes_fila_2020_08_02 (  ra_conteudo TEXT    NOT NULL);");
$execucao->execute();

//Ler arquivo .sql e executa no sqlite
$banco = file_get_contents("teste.sql");
$qr = $pdo->exec($banco);

//Verificação de contagem de registros
$execucao = $pdo->prepare("select COUNT(\"ra_conteudo\") from publicacoes_fila_2020_08_02");
$execucao->execute();
while ($row = $execucao->fetch()) {
    $conteudo = $row['COUNT("ra_conteudo")'];
    echo "O numero total de registros e ". $conteudo . "<br>"; 
} 



//Pegando todas as publicações sem usar filtros SQL
$execucao = $pdo->prepare("select ra_conteudo from publicacoes_fila_2020_08_02");
$execucao->execute();


// Arrays para JSON
$arr4vara = array();
$arrAlimentos = array();
$arrDivorcio = array();
$arrPaternidade = array();
$arrOutros = array();


$position = 0;
while ($row = $execucao->fetch()) {

    $conteudo = $row['ra_conteudo'];
    
    //Separação de arquivos através de expressão regular
    $is4vara = preg_match("/4ª vara da família/i", $conteudo);  //requisitos de identificação
    $isAlimentos = preg_match("/alimentos/i", $conteudo); 
    $isDivorcio = preg_match("/divórcio/i", $conteudo); 
    $isPaternidade = preg_match("/paternidade/i", $conteudo); 
    $isOutros = 0;
    if ($is4vara == 0 && $isAlimentos == 0 && $isDivorcio == 0 && $isPaternidade == 0)
    {
        // Se não for nenhum, é pertencente a outros
        $isOutros = 1;
    }

    //obter numero do processo através do padrão de expressão regular
    preg_match('/\d{7}-\d{2}\.\d{4}\.\d{1}\.\d{2}\.\d{4}/i', $conteudo, $processoNumero);

    //Pegar nome do juiz
    preg_match('/Magistrado\(a\)|Relator.*?-/i', $conteudo, $nomeJuiz);


    //Adicionando nos respectivos arrays
    if($is4vara == 1)
    {
        $arr4vara[] = $processoNumero;
    }
    if($isAlimentos == 1)
    {
        $arrAlimentos[] = $processoNumero;
    }
    if($isDivorcio == 1)
    {
        $arrDivorcio[] = $processoNumero;
    }
    if($isPaternidade == 1)
    {
        $arrPaternidade[] = $processoNumero;
    }
    if($isOutros == 1)
    {
        $arrOutros[] = $processoNumero;
    }

    //echo $position . ": " . $nomeJuiz[0] . "<br>"; 

    $position =  $position + 1;
} 


//Criando o texto em JSON usando os arrays
$json4vara = json_encode($arr4vara);
$jsonAlimentos = json_encode($arrAlimentos);
$jsonDivorcio = json_encode($arrDivorcio);
$jsonPaternidade = json_encode($arrPaternidade);
$jsonOutros = json_encode($arrOutros);



//Gravando os arquivos JSON
$fp = fopen('4vara.json', 'w');
fwrite($fp, $json4vara);
fclose($fp);

$fp = fopen('alimentos.json', 'w');
fwrite($fp, $jsonAlimentos);
fclose($fp);

$fp = fopen('divorcio.json', 'w');
fwrite($fp, $jsonDivorcio);
fclose($fp);

$fp = fopen('paternidade.json', 'w');
fwrite($fp, $jsonPaternidade);
fclose($fp);

$fp = fopen('outros.json', 'w');
fwrite($fp, $jsonOutros);
fclose($fp);


echo "Terminado!";


?>
 </body>
</html>




