# teste-de-software-tp

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
