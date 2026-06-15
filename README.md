# Clínica Odontológica - Prev Dentistas

Este é um sistema web MVC em PHP desenvolvido para gerenciamento de uma clínica odontológica.

## 🚀 Requisitos

- PHP 8.0 ou superior (Recomendado usar XAMPP ou WAMP)
- MySQL / MariaDB
- Apache

## ⚙️ Como Instalar e Rodar o Projeto

1. **Clone ou mova os arquivos**
   Coloque a pasta do projeto (`ProjetoIntegrado2`) dentro da pasta pública do seu servidor local:
   - Se for XAMPP: `C:\xampp\htdocs\ProjetoIntegrado2`
   - Se for WAMP: `C:\wamp\www\ProjetoIntegrado2`

2. **Inicie os serviços**
   Abra o painel de controle do XAMPP/WAMP e inicie os módulos **Apache** e **MySQL**.

3. **Configure o Banco de Dados**
   - Acesse o phpMyAdmin: `http://localhost/phpmyadmin/`
   - Crie um novo banco de dados chamado: `clinica_prev_dentistas`
   - Na coluna esquerda, **clique em cima do banco** `clinica_prev_dentistas` para selecioná-lo.
   - Vá na aba **Importar**, clique em "Escolher arquivo" e selecione o arquivo que está em `database/clinica_prev_dentistas.sql`.
   - Clique em "Executar" para finalizar a importação.

4. **Acesse o sistema**
   Abra o seu navegador e acesse a URL:
   `http://localhost/ProjetoIntegrado2/`

## 🔐 Dados de Acesso (Login para Testes)

Para acessar o sistema e avaliar as funcionalidades, utilize as seguintes credenciais de teste:

- **Usuário:** `admin`
- **Senha:** `123`
