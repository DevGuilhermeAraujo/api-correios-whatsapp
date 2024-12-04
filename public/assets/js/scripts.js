function chamarEndpoint(url, sucesso, erro) {
    return fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erro na API: ${response.status} ${response.statusText}`);
            }
            return response.text();
        })
        .then(data => {
            console.log(`[${new Date().toISOString()}] ${sucesso}`, data);
        })
        .catch(error => {
            console.error(`[${new Date().toISOString()}] ${erro}`, error);
        });
}

function estaNoIntervalo(hora, inicio, fim) {
    return hora >= inicio && hora < fim;
}

function chamarApi() {
    const agora = new Date();
    const hora = agora.getHours();

    if (estaNoIntervalo(hora, 5, 19)) {
        chamarEndpoint(
            'apiCall.php',
            'API chamada executada:',
            'Erro ao chamar API:'
        );
    } else {
        console.log(`[${new Date().toISOString()}] API não chamada: horário fora do intervalo permitido (${hora}h).`);
    }

    if (estaNoIntervalo(hora, 15, 17)) {
        chamarEndpoint(
            'enviarDados.php',
            'Dados enviados:',
            'Erro ao chamar enviarDados:'
        );
    } else {
        console.log(`[${new Date().toISOString()}] Dados não enviados: horário fora do intervalo permitido (${hora}h).`);
    }
}

// Executa imediatamente e a cada 15 minutos
chamarApi();
//setInterval(chamarApi, 15 * 60 * 1000);
