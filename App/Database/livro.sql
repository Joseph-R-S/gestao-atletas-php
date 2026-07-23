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

-- 2. OBJETIVOS
CREATE TABLE objetivos (
    id SERIAL PRIMARY KEY,
    descricao VARCHAR(50) NOT NULL UNIQUE 
);

-- 2.1. OBJETIVO - ATLETA 
CREATE TABLE objetivo_atleta (
    id SERIAL PRIMARY KEY,
    atleta_id INT NOT NULL,
    objetivo_id INT NOT NULL,
    descricao TEXT,
    prazo_meses INT,
    motivo TEXT,
    data_objetivo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
    CONSTRAINT fk_atleta FOREIGN KEY (atleta_id) REFERENCES atletas(id) ON DELETE CASCADE,
    CONSTRAINT fk_objetivo FOREIGN KEY (objetivo_id) REFERENCES objetivos(id) ON DELETE RESTRICT
);

-- 3. MEDIDAS CORPORAIS
CREATE TABLE perfil_atleta (
    id SERIAL PRIMARY KEY,
    atleta_id INT NOT NULL,
    data_medicao DATE NOT NULL DEFAULT CURRENT_DATE,
    peso DECIMAL(5,2),
    altura DECIMAL(4,2),    
    peito DECIMAL(5,2),
    cintura DECIMAL(5,2),
    quadril DECIMAL(5,2),    
    braco DECIMAL(5,2),
    coxa DECIMAL(5,2),       
    panturrilha DECIMAL(5,2),
    factor_actividad DECIMAL(5,2),
    data_perfil TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_medidas_atleta FOREIGN KEY (atleta_id) REFERENCES atletas(id) ON DELETE CASCADE
);

CREATE TABLE alimentos (
    id SERIAL PRIMARY KEY,
    tipo CHAR(1),
    nome VARCHAR(50) NOT NULL,
    porcao VARCHAR(2),
    calorias DECIMAL(5,2),
    proteinas DECIMAL(5,2),
    gorduras DECIMAL(5,2),
    carbohidratos DECIMAL(5,2),
    fibra DECIMAL(5,2)
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE alimentos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    -- Define se a porcao g/ml/unid
    porcao CHAR(3),
    -- Define a quantidade 100/100/1
    quantidade INT,
    -- Definimos que los macros son por cada 100g de referencia
    calorias DECIMAL(6,2) NOT NULL,
    proteinas DECIMAL(5,2) NOT NULL,
    gorduras DECIMAL(5,2) NOT NULL,
    carbohidratos DECIMAL(5,2) NOT NULL,
    fibra DECIMAL(5,2),
    -- Tipo: (Proteína), (Carbohidrato), (Grasa), (Proteina Vegetal), (Otro)
    tipo CHAR(20), 
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



