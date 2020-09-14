<?php
    require 'conexao.php';

    //query to check if initial city exists
    $sql = $pdo->prepare(
        "SELECT 
            COUNT(*) AS qtd
        FROM cidades 
        WHERE del=0 AND NOME = ?"
    );

    $sql->execute([$_REQUEST['cid_ini']]);

    $result = $sql->fetch(PDO::FETCH_ASSOC);

    $json = '';

    if($result['qtd'] <= 0) {
        $json = [
            'erro' => 1, 
            'msg' => 'Cidade inicial inválida, por favor, digite uma cidade inicial cadastrada no sistema'
        ];
    } else {

        //query to check if final city exists
        $sql = $pdo->prepare(
            "SELECT 
                COUNT(*) AS qtd
            FROM cidades 
            WHERE del=0 AND NOME = ?"
        );

        $sql->execute([$_REQUEST['cid_fin']]);

        $result = $sql->fetch(PDO::FETCH_ASSOC);

        if($result['qtd'] <= 0) {
            $json = [
                'erro' => 1, 
                'msg' => 'Cidade final inválida, por favor, digite uma cidade final cadastrada no sistema'
            ];
        }
    }

    //JSON to show messages in JS
    if(!empty($json)) {
        echo json_encode($json);
        return;
    }

    //read the ligacoes.txt
    $arquivo = fopen ('../ligacoes.txt', 'r');

    $sql = $pdo->prepare(
        "SELECT 
            * 
        FROM cidades 
        WHERE del=0 "
    );
    $sql->execute([]);

    $result = $sql->fetchAll();

    $arr = [];

    //check the cities in ligacoes.txt with table "cidades" from database
    while(!feof($arquivo)) {
        
        $linha = fgets($arquivo, 1024);

        //explode the lines in "ligacoes.txt"
        $arrlinha = explode(",", $linha);

        foreach ($result as $key => $value) {
            if($value['NOME'] == $arrlinha[0]) {
                $lat1 = $value['LATITUDE'];
                $lon1 = $value['LONGITUDE'];
            //trim to deal with blank spaces
            } else if($value['NOME'] == trim($arrlinha[1])) {
                $lat2 = $value['LATITUDE'];
                $lon2 = $value['LONGITUDE'];
            }
        }

        //create a new array with the cities in "ligacoes.txt" with their distance calculated
        $arr[] = ['cidades' => trim($linha), 'distancia' => distancia($lat1, $lon1, $lat2, $lon2)];
    }
    fclose($arquivo);

    $novacidade = [];

    //initial city in var "$ponto"
    $ponto = $_REQUEST['cid_ini'];

    try {
        //checking all the possibilities with "ligacoes.txt" between initial and final cities
        while(true) {
            $reset = false;
            
            $novoarr = [];
            foreach ($arr as $key => $value) {
                
                //explode the cities
                $arrcidades = explode(",", $value['cidades']);
                
                //check if var "$ponto" exists in array of cities
                if(in_array($ponto, $arrcidades)) {
                    $novoarr[] = [
                        'cidade1' => $arrcidades[0],
                        'cidade2' => $arrcidades[1], 
                        'distancia' => $value['distancia']
                    ];
                    
                    //remove all possibilities that have "$ponto"
                    unset($arr[$key]);
                }
            }
            
            //organize the new array according with the lowest distance
            usort(
                $novoarr,
                function( $a, $b ) {
                    if( $a["distancia"] == $b["distancia"] ) 
                        return 0;
                    
                    return ( ( $a["distancia"] < $b["distancia"] ) ? -1 : 1 );
                }
            );
            
            //check if it's already in final city, if it's not, continue the while
            if($novoarr[0]['cidade1'] != $_REQUEST['cid_fin'] && $novoarr[0]['cidade2'] != $_REQUEST['cid_fin']) {
                $reset = true;
                
                //set the new "$ponto" with the other city that is different from the previous city checked
                if($novoarr[0]['cidade1'] != $ponto) {
                    $ponto = $novoarr[0]['cidade1'];
                } else if($novoarr[0]['cidade2'] != $ponto) {
                    $ponto = $novoarr[0]['cidade2'];
                }
            }
            
            //new array to store the first city
            $novacidade[] = $novoarr[0];
    
            if(!$reset) {
                break;
            }
        }

        //JSON with only the traced route
        echo json_encode($novacidade);
    } catch (Exception $e) {
        $json = [
            'erro' => 1,
            'msg' => 'Não foi possivel calcular o percurso dessas cidades, por favor, tente com outras cidades'
        ];

        //if something happens in try/catch
        //show message in JS
        echo json_encode($json);
    }
    
    //algorithm of Haversine to calculate the distance between two points with their latitudes and longitudes
    function distancia($lat1, $lon1, $lat2, $lon2) {

        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $lon1 = deg2rad($lon1);
        $lon2 = deg2rad($lon2);
        
        $dist = (6371 * acos( cos( $lat1 ) * cos( $lat2 ) * cos( $lon2 - $lon1 ) + sin( $lat1 ) * sin($lat2) ) );
        return $dist;
    }
?>