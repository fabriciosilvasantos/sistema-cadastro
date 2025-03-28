# Sistema de Cadastro de Usuários

Sistema web desenvolvido em PHP para cadastro e gerenciamento de usuários, com dashboard e relatórios.

## Funcionalidades

- Dashboard com estatísticas em tempo real
- Cadastro de usuários com validação
- Lista de usuários cadastrados
- Relatórios e gráficos
- Interface responsiva com Bootstrap 5
- Validação de formulários no cliente e servidor
- Proteção contra SQL Injection
- Senhas criptografadas

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP:
  - PDO
  - PDO_MySQL
  - mbstring
  - json

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/fabriciosilvasantos/sistema-cadastro.git
```

2. Configure seu servidor web para apontar para o diretório do projeto

3. Importe o arquivo `database.sql` para seu banco de dados MySQL

4. Configure as credenciais do banco de dados no arquivo `config/database.php`

5. Acesse o sistema através do navegador

## Estrutura do Projeto

```
cadastro/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── menu.php
│   └── functions.php
├── index.php
├── cadastro.php
├── lista.php
└── README.md
```

## Tecnologias Utilizadas

- PHP 7.4+
- MySQL
- Bootstrap 5
- Chart.js
- Font Awesome
- Bootstrap Icons

## Contribuição

1. Faça um Fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## Contato

Fabricio Silva Santos - fabricio.silvasantos@gmail.com

Link do Projeto: [https://github.com/fabriciosilvasantos/sistema-cadastro](https://github.com/fabriciosilvasantos/sistema-cadastro) 