<?php

/*O objetivo desse codigo é ler um arquivo CSV, e popular o BD com os dados desse arquivo para que sejam manipulados via código. 

Após isso, será realizada uma limpeza de dados duplicados de todos os valores de 2021 que são repetidos em 2022 e apaga-los, mantendo os dados não duplicados de 2021. Depois disso, o código deve apagar todos os dados de 2021 para baixo com exceção dos não repetidos.

Depois de ter a planilha "limpa" será feito um preenchimento da tabela principal com uma segunda tabela com dados que já estão na tabela principal, mas precisam ser complementados, por isso, a segunda tabela será responsável pela complementação de dados com uma validação de nomes repetidos, para tratamento manual posterior. 

Para finalizar, as datas comemorativas que começarem com "Dia", em geral são feriados nacionais, por isso, o campo cidade e estado deve ser preenchido com "null", então será feito um filtro para preencher esse campo de forma automática.
*/


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define( 'MYSQL_HOST', 'localhost' );
define( 'MYSQL_USER', 'root' );
define( 'MYSQL_PASSWORD', 'root' );
define( 'MYSQL_DB', 'municipios' ); 

try
{
    $PDO = new PDO( 'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB , MYSQL_USER, MYSQL_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
}
catch ( PDOException $e )
{
    echo 'Erro ao conectar com o MySQL: ' . $e->getMessage();
}

$arquivo = fopen("eventos.csv","r"); //faz a leitura do CSV Principal
//O laço abaixo preenche o BD com os dados do CSV principal
while (($linha = fgetcsv($arquivo)) !== FALSE) {
    $query = 'INSERT INTO eventos (title,all_day,date_start,date_end,time_start,time_end,recurrence_id,category_id,category_name,segment_id,segment_name,nivel_id,nivel_name,country_id,country_name,state_id,state_name,city_id,city_name) VALUE (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
    $stmt = $PDO->prepare($query);
    for($i = 1; $i <= 19; $i++){
        $stmt->bindParam($i, $linha[$i-1]);
    }
    //$stmt->execute(); //descomentar para funcionar

    echo $linha[2]." - ".$linha[0]."<br>";
}

fclose();

$arquivo = fopen("aniversariantes.csv","r"); //faz a leitura do CSV complementar

//O laço abaixo preenche o BD com os dados do CSV complementar, incluindo uma concatenação de dados que será necessário para validar os nomes de cidades que forem repetidas.

while (($linha = fgetcsv($arquivo)) !== FALSE) {

    if ($linha[3]<10) {
        $linha[3] = '0'.$linha[3];
    }
    if ($linha[4]<10) {
        $linha[4] = '0'.$linha[4];
    }

    $linha[5] = '2022-'.$linha[3].'-'.$linha[4]; 
    
    $query = 'INSERT INTO municipios (nome, nome_estado, Dia, Mes, Data_Completa) 
    VALUE (:nome, :estado, :dia, :mes, :data_completa)';
    $stmt = $PDO->prepare($query);
    $stmt->bindParam(':nome', $linha[2]);
    $stmt->bindParam(':estado', $linha[0]);
    $stmt->bindParam(':dia', $linha[3]);
    $stmt->bindParam(':mes', $linha[4]);
    $stmt->bindParam(':data_completa', $linha[5]);
    //$stmt->execute();

    echo $linha[2]." - ".$linha[0]." - ".$linha[3]." - ".$linha[4]." - ".$linha[5]."<br>";
}

fclose();

//Bloco que limpa os dados duplicados de 2021 e 2022, primeiro selecionando os eventos que começam com 2021 e 20221, para tratar esse dados primeiro.

$queryData2021 = "SELECT title FROM eventos WHERE id > 1 AND date_start LIKE '2021%'";
$queryData2022 = "SELECT title FROM eventos WHERE id > 1 AND date_start LIKE '2022%'";
$stmt_data2021 = $PDO->prepare($queryData2021);
$stmt_data2022 = $PDO->prepare($queryData2022);
$stmt_data2021->execute();
$stmt_data2022->execute();


$arr2021 = $stmt_data2021->fetchAll(PDO::FETCH_ASSOC);
$arr2021 = array_map("tiraDaArray", $arr2021);

$arr2022 = $stmt_data2022->fetchAll(PDO::FETCH_ASSOC);
$arr2022 = array_map("tiraDaArray", $arr2022);

$datasDiferentes=array_diff($arr2021,$arr2022);

print_r($datasDiferentes);

foreach ($datasDiferentes as $evento) {
    $queryUpdate ="UPDATE eventos SET nao_deletar = 'n' WHERE title = :title";
    $stmtUpdate = $PDO->prepare($queryUpdate);
    $stmtUpdate->bindParam(':title', $evento);
    $stmtUpdate->execute();
}    

//Bloco que Deleta todos os registros de 2015 até 2021. 

$queryDeletar ="DELETE FROM eventos 
WHERE (date_start LIKE '2021%' 
OR date_start LIKE '2020%' 
OR date_start LIKE '2019%' 
OR date_start LIKE '2018%' 
OR date_start LIKE '2017%' 
OR date_start LIKE '2016%' 
OR date_start LIKE '2015%') 
AND nao_deletar <> 'n'"; 
$stmtDeletar = $PDO->prepare($queryDeletar);
$stmtDeletar->execute();

function tiraDaArray($a){
    return $a['title'];
}

//Caso o evento esteja dentro da condição de ser um aniversário de cidade, então ele preencherá na tabela principal a qual estado e cidade pertece a cidade aniversariante


$queryEvento = 'SELECT * FROM eventos WHERE id > 1';
$stmt_evento = $PDO->prepare($queryEvento);
$stmt_evento->bindParam(':nome', $final);
$stmt_evento->execute();

    while ($evento = $stmt_evento->fetch()) {
        $comeco = substr($evento["title"], 0,12);
        $comeco = strtolower(limpeza($comeco)); 
        
        if (($comeco == "aniversario")) {
           
            $final = substr ($evento["title"],16);
            $query = 'SELECT * FROM municipios WHERE nome = :nome AND Data_Completa = :datacomp';
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(':nome', $final);
            $stmt->bindParam(':datacomp', $evento["date_start"]);
            $stmt->execute(); //descomentar para funcionar

            echo $final."<br>";

            $municipio = $stmt->fetch();
            $qtd = $stmt->rowCount();
            if($qtd > 0){
                if($qtd > 1){
                    if ($evento["date_start"] == $municipio["Data_Completa"]){
                        $query3 = 'UPDATE eventos SET state_name = :estado, city_name = :cidade WHERE id = :id';
                        $stmt3 = $PDO->prepare($query3);
                        $stmt3->bindParam(':estado', $municipio["nome_estado"]);
                        $stmt3->bindParam(':cidade', $municipio["nome"]);
                        $stmt3->bindParam(':id', $evento["id"]);
                        $stmt3->execute(); 
                    }else {
                        $rep = "Repedido-Validar";
                        $query3 = 'UPDATE eventos SET state_name = :estado, city_name = :cidade WHERE id = :id';
                        $stmt3 = $PDO->prepare($query3);
                        $stmt3->bindParam(':estado', $rep);
                        $stmt3->bindParam(':cidade', $municipio["nome"]);
                        $stmt3->bindParam(':id', $evento["id"]);
                        $stmt3->execute();
                    }
                }else{
                    $query3 = 'UPDATE eventos SET state_name = :estado, city_name = :cidade WHERE id = :id';
                    $stmt3 = $PDO->prepare($query3);
                    $stmt3->bindParam(':estado', $municipio["nome_estado"]);
                    $stmt3->bindParam(':cidade', $municipio["nome"]);
                    $stmt3->bindParam(':id', $evento["id"]);
                    $stmt3->execute(); //descomentar para funcionar
                }
            }else{
               echo "nao achou cidade";
            }
        }
    }

//Bloco que preenche os dados de cidade e estado como "null" caso o nome do evento comece com "dia".    

$queryEvento = 'SELECT * FROM eventos WHERE id > 1';
$stmt_evento = $PDO->prepare($queryEvento);
$stmt_evento->bindParam(':nome', $final);
$stmt_evento->execute();

    while ($evento = $stmt_evento->fetch()) {
        $comeco = substr($evento["title"], 0,3);
        $comeco = strtolower(limpeza($comeco)); 
        
        if (($comeco == "dia")) {
            echo "Achou algo<br>";
            $query3 = 'UPDATE eventos SET state_name = "null", city_name = "null" WHERE id = :id';
            $stmt3 = $PDO->prepare($query3);
            $stmt3->bindParam(':id', $evento["id"]);
          //descomentar para funcionar  $stmt3->execute(); 
                
        }else{
            echo "Não começa com dia<br>";
        }
        
    }    

//Expressão regular para padronização de dados, assim fica mais assertivo o tratamento e comparação de dados entre as tabelas.

function limpeza ($texto){
    $texto = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$texto);
    return $texto;
}    
?>

