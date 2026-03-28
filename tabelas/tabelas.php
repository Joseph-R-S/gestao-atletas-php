<?php
$host = 'localhost';
$db   = 'gestao_atletas';
$user = 'postgres';
$pass = 'admin123';
$port = '5432';
// antes de rodar é preciso criar o banco de dados
//CREATE DATABASE IF NOT EXISTS gestao_atletas;

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS niveis_experiencia (
        id SERIAL PRIMARY KEY,
        nome VARCHAR(20) NOT NULL,
        descricao TEXT NULL
    );
    
    CREATE TABLE IF NOT EXISTS objetivo (
        id SERIAL PRIMARY KEY,
        nome VARCHAR(255) NOT NULL
    );
    
    CREATE TABLE IF NOT EXISTS modalidades_esporte (
        id SERIAL PRIMARY KEY,
        nome VARCHAR(100) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS atletas (
        id SERIAL PRIMARY KEY,
        nome_completo VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        sexo CHAR(1) NOT NULL CHECK (sexo IN('M', 'F')),
        telefone VARCHAR(11) UNIQUE NOT NULL,
        data_nascimento DATE NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS avaliacoes (
        id SERIAL PRIMARY KEY,
        atleta_id INTEGER REFERENCES atletas(id),
        objetivo_id INTEGER REFERENCES objetivo(id),
        nivel_id INTEGER REFERENCES niveis_experiencia(id),
        tipo_esporte_id INTEGER REFERENCES  modalidades_esporte(id),
        foto_frente_path VARCHAR(255) NULL,
        foto_costa_path VARCHAR(255) NULL,
        foto_lado_path VARCHAR(255) NULL,
        meta_especifica TEXT NULL,
        peso DECIMAL(5,2) NOT NULL,
        altura DECIMAL(3,2) NOT NULL,
        data TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    $conn->exec($sql);
    echo "Estrutura do banco 'gestao_atletas' criada com sucesso!";

} catch (PDOException $e) {
    echo "Erro técnico: " . $e->getMessage();
} finally {
    $conn = null;
}
?>