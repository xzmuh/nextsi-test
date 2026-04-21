# API CRUD de autenticação em PHP

Projeto ajustado para rodar com mais facilidade no XAMPP, sem depender de `composer install` para o autoload e para a leitura do `.env`.

## O que foi corrigido

- removida a dependência obrigatória de `vendor/autoload.php`
- adicionado `bootstrap.php` com autoload PSR-4 simples e leitura de `.env`
- corrigido o problema de rotas em subpasta do XAMPP (`/seu-projeto`, `/seu-projeto/public`)
- adicionados `.htaccess` na raiz e em `public/`
- corrigido o uso de banco inconsistente entre `.env.example`, `schema.sql` e `seed.sql`
- ajustado `swagger-initializer.js` para usar caminho relativo
- criado `database/setup.sql` para importar tudo de uma vez no phpMyAdmin
- criado `index.php` na raiz para facilitar acesso pelo Apache

## Como rodar no XAMPP

1. Coloque a pasta do projeto dentro de `htdocs`.
2. Inicie **Apache** e **MySQL** no XAMPP.
3. Importe `database/setup.sql` no phpMyAdmin.
4. Confira o arquivo `.env`:
   - `DB_HOST=localhost`
   - `DB_PORT=3306`
   - `DB_NAME=nextsi`
   - `DB_USER=root`
   - `DB_PASS=`
5. Acesse no navegador:
   - `http://localhost/NOME_DA_PASTA/health`
   - ou `http://localhost/NOME_DA_PASTA/public/health`

## Rotas principais

- `GET /health`
- `POST /auth/login`
- `GET /users`
- `GET /users/{id}`
- `POST /users`
- `PUT /users/{id}`
- `DELETE /users/{id}`

## Swagger

Se os assets do Swagger estiverem completos no projeto original, acesse:

- `http://localhost/NOME_DA_PASTA/public/swagger/`

## Login padrão

- **e-mail:** `admin@example.com`
- **senha:** `Admin@123`
