
CREATE DATABASE ArrumaJa;
USE ArrumaJa;
 drop database ArrumaJa;
-- ==============================
-- TABELA CLIENTE
-- ==============================
CREATE TABLE Cliente (
    idCliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(45) UNIQUE NOT NULL,
    telefone VARCHAR(150),
    email VARCHAR(150) UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- ==============================
-- TABELA ENDERECO
-- ==============================
CREATE TABLE Endereco (
    idEndereco INT AUTO_INCREMENT PRIMARY KEY,
    cep VARCHAR(45) NOT NULL,
    cidade VARCHAR(45) NOT NULL,
    estado VARCHAR(45) NOT NULL,
    numero VARCHAR(45) NOT NULL,
    complemento VARCHAR(45),
    logradouro VARCHAR(100) NOT NULL,
    bairro VARCHAR(45) NOT NULL
);

-- ==============================
-- RELAÇÃO CLIENTE x ENDERECO
-- ==============================
CREATE TABLE Cliente_has_Endereco (
    Cliente_idCliente INT NOT NULL,
    Endereco_idEndereco INT NOT NULL,
    PRIMARY KEY (Cliente_idCliente, Endereco_idEndereco),
    FOREIGN KEY (Cliente_idCliente) REFERENCES Cliente(idCliente) ON DELETE CASCADE,
    FOREIGN KEY (Endereco_idEndereco) REFERENCES Endereco(idEndereco) ON DELETE CASCADE
);

-- ==============================
-- TABELA FRETE
-- ==============================
CREATE TABLE Frete (
    idFrete INT AUTO_INCREMENT PRIMARY KEY,
    bairro VARCHAR(45) NOT NULL,
    valor DOUBLE NOT NULL,
    transportadora VARCHAR(45)
);

-- ==============================
-- TABELA CUPOM
-- ==============================
CREATE TABLE Cupom (
    idCupom INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(45),
    valor float not null,
    data_validade date not null,
    quantidade int not null
);

select * from Cupom;

-- ==============================
-- TABELA FORMAS DE PAGAMENTO
-- ==============================
CREATE TABLE Formas_Pagamento (
    idFormas_Pagamento INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(45)
);

-- ==============================
-- TABELA CATEGORIAS SERVIÇOS
-- ==============================
-- 1️⃣ Tabela de categorias (precisa existir primeiro)
CREATE TABLE Categorias_Servicos (
    idCategorias_Servicos INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT null,
    desconto DOUBLE
);

-- ==============================
-- TABELA MARIDO DE ALUGUEL
-- ==============================
CREATE TABLE Marido_Aluguel (
    idMarido_Aluguel INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(45) UNIQUE NOT NULL,
    telefone VARCHAR(150),
    email VARCHAR(150) UNIQUE,
    senha VARCHAR(12) NOT NULL
);

-- ==============================
-- TABELA SERVICOS
-- ==============================
-- 2️⃣ Tabela de serviços
CREATE TABLE Servicos (
    idServicos INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(45) NOT NULL,
    descricao TEXT NOT NULL,
    preco_servico DOUBLE NOT NULL,
    desconto DOUBLE,
    Categorias_Servicos_idCategorias_Servicos INT NOT NULL,
    FOREIGN KEY (Categorias_Servicos_idCategorias_Servicos)
        REFERENCES Categorias_Servicos(idCategorias_Servicos)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);
select * from Servicos;

-- ==============================
-- TABELA SERVICOS x MARIDO DE ALUGUEL
-- ==============================
CREATE TABLE Servicos_has_Marido_Aluguel (
    Servicos_idServicos INT NOT NULL,
    Marido_Aluguel_idMarido_Aluguel INT NOT NULL,
    PRIMARY KEY (Servicos_idServicos, Marido_Aluguel_idMarido_Aluguel),
    FOREIGN KEY (Servicos_idServicos) REFERENCES Servicos(idServicos) ON DELETE CASCADE,
    FOREIGN KEY (Marido_Aluguel_idMarido_Aluguel) REFERENCES Marido_Aluguel(idMarido_Aluguel) ON DELETE CASCADE
);

-- ==============================
-- TABELA HORARIOS_SERVICOS
-- ==============================
CREATE TABLE Horarios_Servicos (
    idHorarios_Servicos INT AUTO_INCREMENT PRIMARY KEY,
    hora DATETIME,
    data_entrega DATE,
    situacao VARCHAR(45),
    idServicos INT,
    FOREIGN KEY (idServicos) REFERENCES Servicos(idServicos)
);

-- ==============================
-- TABELA IMAGEM_SERVICOS
-- 3️⃣ Tabela de imagens (relacionada 1:N com serviços)
CREATE TABLE Imagem_Servicos (
    idImagem_Servicos INT AUTO_INCREMENT PRIMARY KEY,
    foto LONGBLOB NOT NULL,
    descricao VARCHAR(255),
    Servicos_idServicos INT NOT NULL,
    FOREIGN KEY (Servicos_idServicos)
        REFERENCES Servicos(idServicos)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);




-- ==============================
-- TABELA BANNERS
-- ==============================
CREATE TABLE Banners (
  idBanner INT AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(100) NOT NULL,
  link VARCHAR(150),
  categoria VARCHAR(100),
  validade DATE NOT NULL,
  imagem LONGBLOB not null
);
select * from Banners;


-- ==============================
-- TABELA EMPRESA
-- ==============================
CREATE TABLE Empresa (
    idEmpresa INT AUTO_INCREMENT PRIMARY KEY,
    nome_fantasia VARCHAR(100) NOT NULL,
    razao_social VARCHAR(100),
    cnpj VARCHAR(20),
    telefone VARCHAR(20),
    email VARCHAR(100),
    endereco VARCHAR(100),
    cidade VARCHAR(45),
    estado VARCHAR(45)
);

-- ==============================
-- TABELA CONTRATAR_SERVICOS
-- ==============================
CREATE TABLE Contratar_Servicos (
    idContrato INT AUTO_INCREMENT PRIMARY KEY,
    valorTotal DOUBLE NOT NULL,
    data_contrato DATE NOT NULL,
    hora_contrato DATETIME NOT NULL,

    -- RELAÇÕES
    prestador INT NOT NULL,        -- Marido_Aluguel
    cliente INT NOT NULL,          -- Cliente
    servico INT NOT NULL,          -- Servicos
    formapagamento INT NOT NULL,   -- Formas_Pagamento
    frete INT,                     -- Frete
    endereco INT NOT NULL,         -- Endereco

    FOREIGN KEY (prestador) REFERENCES Marido_Aluguel(idMarido_Aluguel),
    FOREIGN KEY (cliente) REFERENCES Cliente(idCliente),
    FOREIGN KEY (servico) REFERENCES Servicos(idServicos),
    FOREIGN KEY (formapagamento) REFERENCES Formas_Pagamento(idFormas_Pagamento),
    FOREIGN KEY (frete) REFERENCES Frete(idFrete),
    FOREIGN KEY (endereco) REFERENCES Endereco(idEndereco)
);


select * from Servicos;
select * from Categorias_Servicos;