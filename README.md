**Desenvolvimento de Sistema para Automatização do Fluxo de Mensagens entre Cliente e Assistência Técnica**

  Este projeto foi desenvolvido dentro da empresa, visando automatizar o fluxo de mensagens entre clientes e a equipe de assistência técnica.
  A solução utiliza APIs dos Correios e WhatsApp para facilitar o envio automático de informações relevantes, como códigos de rastreamento e detalhes sobre os produtos, diretamente aos clientes.


**Descrição do Projeto:**

  O objetivo deste projeto foi criar uma solução eficiente para melhorar a comunicação entre a assistência técnica e os clientes, proporcionando um atendimento mais ágil e transparente. A automação do processo de envio de informações, utilizando as APIs dos Correios e WhatsApp, permite que os clientes recebam atualizações em tempo real sobre o status de seus pedidos e informações detalhadas sobre o transporte de seus produtos.


**Realização do Projeto:**

  Para a realização deste projeto, foi adotada uma abordagem abrangente, utilizando diversas ferramentas e tecnologias:


**Integração com APIs**
  Utilizando a linguagem PHP, o sistema realiza a integração com diversas APIs dos Correios e WhatsApp:

  Autenticação: Inicialmente, é feita a autenticação com a API dos Correios para obter um token de acesso.

  Pré-Postagem: A extração dos dados relevantes é realizada diretamente através da API PMA (Pré Postagem) dos Correios, que fornece todos os códigos de rastreamento da empresa.

  Rastreamento: O código de rastreamento é enviado para a API de rastreamento dos Correios, que retorna informações sobre a localização do objeto.

  Validação de Números de WhatsApp: Implementamos uma função que utiliza uma API do WhatsApp para validar e corrigir números de telefone dos clientes, retornando o número correto com ou sem o dígito 9.

  Envio de Mensagens: Após obter as informações de rastreamento, uma mensagem personalizada é enviada ao cliente via WhatsApp, contendo o código de rastreamento e as informações de localização.



**Controle de Mensagens:**

  Para evitar sobrecarregar o cliente com mensagens desnecessárias, o sistema foi projetado para enviar apenas duas mensagens: uma informando a saída do objeto e outra informando a chegada.




**Tecnologias Utilizadas:**

  Linguagem de Programação: PHP
  
  Banco de Dados: MySQL
  
  Integração de APIs: Correios (para rastreamento) e WhatsApp (para envio de mensagens)
  



**Status do Projeto:**

  O projeto foi implementado com sucesso e está em operação dentro da empresa. A automação do fluxo de mensagens proporcionou uma significativa melhoria na eficiência do atendimento ao cliente,
  reduzindo o tempo de resposta e aumentando a satisfação do cliente. Futuras atualizações e aprimoramentos estão sendo considerados para agregar novas funcionalidades e melhorias ao sistema.
