CREATE TABLE rastreioApi(
    id INT PRIMARY KEY AUTO_INCREMENT,
	destinatario VARCHAR(50),
    codRastreio VARCHAR(13),
    numeroTelefone VARCHAR(14),
	nomeProduto VARCHAR(50),
    validate1 INT,
    validate2 INT,
    statusPostadoHora DATETIME,
    statusEntregaHora DATETIME,
    lastUpdate DATETIME
    )
	
CREATE TABLE correiosToken (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token TEXT NOT NULL,
    data_expiracao DATETIME NOT NULL
);

CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    mensagem TEXT NOT NULL,
	classe VARCHAR(50) NOT NULL,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);