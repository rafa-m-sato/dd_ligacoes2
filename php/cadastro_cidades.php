<?php
    require 'conexao.php';

    if($_REQUEST['tipo'] == 'Cadastrar') {
        //if it's to insert/update
        
        //treating float
        $latitude = str_replace(",", ".", $_REQUEST['latitude']);
        $longitude = str_replace(",", ".", $_REQUEST['longitude']);

        //if it has "id", so it's an update
        if(!empty($_REQUEST['id'])) {

            $sql = $pdo->prepare(
                "SELECT 
                    COUNT(*) AS qtd 
                FROM cidades 
                WHERE del=0 AND LATITUDE = ? AND LONGITUDE = ? AND ID <> ?"
            );
            $sql->execute([$latitude, $longitude, $_REQUEST['id']]);
    
            $verifica = $sql->fetch(PDO::FETCH_ASSOC);
            
            //checking if some other city has the same latidude and longitude
            if($verifica['qtd'] > 0) {
                $json = [
                    'erro' => 1, 
                    'msg' => 'Já existe uma cidade com essa latitude/longitude iguais, por favor, cadastre um diferente'
                ];
            } else {
                $sql = $pdo->prepare(
                    "UPDATE cidades SET 
                        nome = ?, 
                        latitude = ?, 
                        longitude = ? 
                    WHERE ID = ?"
                );
                $atualiza = $sql->execute([$_REQUEST['nome'], $latitude, $longitude, $_REQUEST['id']]);
    
                if(!$atualiza) {
                    $json = [
                        'erro' => 1, 
                        'msg' => 'Ocorreu algum erro, por favor, tente novamente'
                    ];
                } else {
                    $json = [
                        'erro' => 0, 
                        'msg' => 'Atualizado com sucesso!'
                    ];
                }
            }
        } else {
            //if it hasn't "id", so it's an insert
            $sql = $pdo->prepare(
                "SELECT 
                    COUNT(*) AS qtd 
                FROM cidades 
                WHERE del=0 AND LATITUDE = ? AND LONGITUDE = ?"
            );
            $sql->execute([$latitude, $longitude]);
    
            $verifica = $sql->fetch(PDO::FETCH_ASSOC);
            
            //checking if there's any city that has the same latidude and longitude
            if($verifica['qtd'] > 0) {
                $json = [
                    'erro' => 1, 
                    'msg' => 'Já existe uma cidade com essa latitude/longitude iguais, por favor, cadastre um diferente'
                ];
            } else {
    
                $sql = $pdo->prepare(
                    "INSERT INTO cidades (nome, latitude, longitude) 
                    VALUES (?, ?, ?)"
                );
                $insere = $sql->execute([$_REQUEST['nome'], $latitude, $longitude]);
    
                if(!$insere) {
                    $json = [
                        'erro' => 1, 
                        'msg' => 'Ocorreu algum erro, por favor, tente novamente'
                    ];
                } else {
                    $json = [
                        'erro' => 0, 
                        'msg' => 'Cadastrado com sucesso!'
                    ];
                }
            }
        }

        //JSON to show messages in JS
        echo json_encode($json);
    } else if ($_REQUEST['tipo'] == 'Mostrar') {
        //if it's to get a city

        $sql = $pdo->prepare(
            "SELECT 
                ID, LATITUDE, LONGITUDE
            FROM cidades 
            WHERE del=0 AND NOME = ?"
        );

        $sql->execute([$_REQUEST['cidade']]);

        $result = $sql->fetch(PDO::FETCH_ASSOC);

        //JSON to show messages in JS
        echo json_encode($result);
    } else {
        //if it's to "delete" a city

        $sql = $pdo->prepare(
            "UPDATE cidades SET 
                del = 1
            WHERE ID = ?"
        );
        $atualiza = $sql->execute([$_REQUEST['ID']]);

        if(!$atualiza) {
            $json = [
                'erro' => 1, 
                'msg' => 'Ocorreu algum erro, por favor, tente novamente'
            ];
        } else {
            $json = [
                'erro' => 0, 
                'msg' => 'Excluido com sucesso!'
            ];
        }

        //JSON to show messages in JS
        echo json_encode($json);
    }

    
?>