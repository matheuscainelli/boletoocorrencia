Dado('Que eu acesso a pagina de cadastro de ocorrências') do
    visit 'http://localhost/boletoocorrencia/login.php'
    find('input[name=login]').set "admin"
    find('#password').set "admin1234"
    sleep(1)
    click_button 'Entrar'  
    click_link 'Tarefas' 
    sleep(1)
    find("a[href='ocorrencia.php']").click 
    click_link 'Novo registro'
  end
  
  Quando('eu seleciono o tipo de ocorrencia {string}') do |ocorrencia|
    select ocorrencia, :from => "IDTIPOOCORRENCIA"
  end
  
  Quando('seleciono o vigilante {string}') do |vigitante|
    select vigitante, :from => "IDVIGILANTE"
  end
  
  Quando('Posto\/Área {string}') do |posto|
    select posto, :from => "IDPOSTOAREA"
  end
  
  Quando('com a descrição {string}') do |descricao|
    find('.note-editable').set descricao
    sleep(1)
  end
  
  Quando('clico no botão Incluir') do 
    click_button 'Incluir' 
    sleep(1)
  end
  
  Então('devo ver {string}') do |mensagem|
    expect(page).to have_content mensagem
  end