# movie-favorites

## 1. Membros do Grupo
- Cleifson da Silva Araujo  
- Iago da Silva  
- Ronald Rabelo  

## 2. Explicação do Sistema
O Projeto é uma aplicação web construída com *Laravel* no backend e *Vue.js* no frontend.  
O sistema permite que usuários interajam com uma lista de filmes favoritos, integrando-se à *API da TMDB* para obter informações atualizadas sobre filmes.  

Funcionalidades principais:  
- Cadastro e autenticação de usuários.
- Listar filmes, mostrar detalhes dos filmes.   
- CRUD de favoritos: adicionar, listar e remover favoritos.
- Integração com API externa (TMDB) para buscar informações de filmes.  
- Estrutura baseada em *Domain-Driven Design (DDD)*, separando responsabilidades entre camadas: Application, Domain e Infra.  

## 3. Tecnologias Utilizadas
- *Backend:* Laravel 10, PHP 8  
- *Frontend:* Vue.js 3  
- *Banco de Dados:* MySQL (via Docker)  
- *Containers:* Docker e Docker Compose  
- *API Externa:* TMDB (The Movie Database)  
- *Arquitetura:* Domain-Driven Design (DDD)  
- *Testes:* PHPUnit (backend) e Vue Test Utils (frontend)

## 4. Rodando projeto

A seguir, estão as instruções para rodar o projeto manualmente, mas recomendo o uso do script `setup.sh` e `migrate.sh` para automação do processo.

## 5. Pré-requisitos

Antes de rodar o projeto, é necessário ter as seguintes ferramentas instaladas:

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Observações

- O script `setup.sh` e `migrate.sh` automatiza o processo de configuração e execução dos containers, tornando o processo mais simples e rápido. 

## 6. Passo a Passo

### Clonando o Repositório
Primeiro, clone o repositório para sua máquina local:

```bash
  git clone https://github.com/Cleyfson/movie-favorites.git
  cd movie-favorites
```

### Execute o arquivo setup.sh e migrate.sh que está no root do projeto

```bash
./setup.sh
./migrate.sh
```

Caso você encontre problemas de permissão ao rodar os arquivos `.sh`, é possível que o script não tenha permissão de execução. Para corrigir isso, execute o comando abaixo antes de rodá-los:

```bash
chmod +x setup.sh
chmod +x migrate.sh
```

## 7. Passo a Passo sem setup.sh e migrate.sh

### Clonando o Repositório

Primeiro, clone o repositório para sua máquina local:

```bash
  git clone https://github.com/Cleyfson/movie-favorites.git
  cd movie-favorites
```

### Configuração da API (Backend)

Navegue até o diretório `api` e copie o .env-example:

```bash
cd api
cp .env.example .env
```

### Configuração do Frontend
Navegue até o diretório `frontend` e copie o .env-example:

```bash
cd fronted
cp .env.example .env
```

### Subindo os Containers

Execute o comando abaixo para iniciar os containers. O Docker irá construir as imagens e subir os containers em segundo plano:

```bash
docker-compose up --build -d
```

Isso vai iniciar os seguintes containers:

- Backend (`laravel_app`)
- Frontend (`vue_frontend`)
- Banco de Dados (`laravel_db`)
- Webserver (`laravel_webserver`)

### Configurando aplicação

Depois disso, execute os comandos abaixo para configurar a aplicação:

```bash
docker exec laravel_app php artisan key:generate
docker exec laravel_app php artisan jwt:secret
docker exec laravel_app php artisan migrate --seed
```

## 8. Como testar a aplicação

Após subir os containers e executar as migrações, você pode acessar a aplicação nos seguintes endereços:

Backend (Laravel): http://localhost:8000

Frontend (Vue.js): http://localhost:5173

## 9. Testes automatizados

Para rodar os testes automatizados execute o seguintes comandos no root do projeto
```bash
docker exec laravel_app php artisan test
```
