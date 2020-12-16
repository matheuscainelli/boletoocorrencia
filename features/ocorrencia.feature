#language:pt

Funcionalidade: Cadastro de uma Ocorrencia
Estória do cadastro de uma ocorrencia
Para que eu possa cadastrar uma nova ocorrencia

    Contexto: Adição de uma ocorrencia com todos os parametros corretos
        Dado Que eu acesso a pagina de cadastro de ocorrências
        
    Cenário: Ocorrencia Cadaasrada com os dados corretos 
        Quando eu seleciono o tipo de ocorrencia "Comportamento Curioso"
        E seleciono o vigilante "teste"
        E Posto/Área "A12 - Educação Física"
        E com a descrição "Comportamento suspeito"
        E clico no botão Incluir
        Então devo ver "Mostrando de"
    
    Cenário: Ocorrencia Cadaasrada com os dados incorretos 
    
        Quando eu seleciono o tipo de ocorrencia "Comportamento Curioso"
        E seleciono o vigilante "Selecione:"
        E Posto/Área "A12 - Educação Física"
        E com a descrição "Comportamento Suspeito"
        E clico no botão Incluir
        Então devo ver "Campos de preenchimento obrigatório"
