// Função para chamar a página apiCall.php e enviarDados.php
function chamarApi() {
    // Obtém a hora atual do servidor
    const agora = new Date(); // Assume que a hora do servidor está sincronizada
    const hora = agora.getHours(); // Obtém a hora atual (0-23)

    // Verifica se está entre 5h e 19h para chamar apiCall.php
    if (hora >= 5 && hora < 19) {
        fetch('apiCall.php')
            .then(response => response.text()) // Assume que a resposta seja texto
            .then(data => {
                console.log('API chamada executada: ', data); // Exibe a resposta no console
            })
            .catch(error => {
                console.error('Erro ao chamar API:', error);
            });
    } else {
        console.log(`API não chamada: horário fora do intervalo permitido (${hora}h).`);
    }

    // Verifica se está entre 15h e 17h para chamar enviarDados.php
    if (hora >= 15 && hora < 17) {
        fetch('enviarDados.php')
            .then(response => response.text()) // Assume que a resposta seja texto
            .then(data => {
                console.log('Dados enviados: ', data); // Exibe a resposta no console
            })
            .catch(error => {
                console.error('Erro ao chamar enviarDados:', error);
            });
    } else {
        console.log(`Dados não enviados: horário fora do intervalo permitido (${hora}h).`);
    }
}

// Chama a API imediatamente quando a página carrega
chamarApi();

// Chama a API a cada 15 minutos (900000 ms)
setInterval(chamarApi, 900000); // 900000 ms = 15 minutos
