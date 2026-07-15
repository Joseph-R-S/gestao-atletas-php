CREATE TABLE atletas (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(20) NOT NULL,
    data_nascimento DATE,
    sexo CHAR(1),
    ocupacao VARCHAR(100),
    estado_civil VARCHAR(50),
    endereco VARCHAR(150),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



