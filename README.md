# NextSI - API de Usuários em PHP

API feita em PHP para autenticação e gerenciamento de usuários.

O projeto foi organizado para rodar localmente com mais facilidade no XAMPP, usando MySQL, autenticação por token e documentação com Swagger. Também possui controle de permissão para que apenas usuários administradores possam criar, editar, excluir e restaurar usuários.

---

## O que este projeto faz

Esta API permite:

- verificar se a aplicação está online
- fazer login
- listar usuários ativos
- buscar usuário por ID
- criar usuário
- editar usuário
- excluir usuário com soft delete
- consultar usuário excluído
- restaurar usuário excluído

As rotas atuais incluem `/health`, `/auth/login`, `/users`, `/users/{id}`, `/users/deleted/{id}` e `/users/restore/{id}`. 

---

## Tecnologias usadas

- PHP
- MySQL
- XAMPP
- Swagger UI para documentação da API

O projeto também usa `.env` para configuração local e possui um `bootstrap.php` para carregar as classes e variáveis de ambiente.

---

## Como rodar no XAMPP

1. Coloque a pasta do projeto dentro de `htdocs`.
2. Abra o XAMPP.
3. Ligue o **Apache** e o **MySQL**.
4. Abra o phpMyAdmin.
5. Importe o arquivo:

```text
database/setup.sql