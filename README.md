Projeto está em desenvolvimento ativo 

1) Gestão de Atletas
Sistema para gestão de atletas e avaliações físicas, focado e organização de treinos.

2) Sobre o Projeto
Este sistema esta sendo desenvolvido para facilitar o acompanhamento de atletas, permitindo o cadastro completo, gestão de medidas corporais e histórico de avaliações.

O projeto utiliza uma arquitetura MVC (Model-View-Controller) e implementa um ORM (Object-Relational Mapping) próprio baseado no padrão Active Record,
garantindo um código limpo, reutilizável e de fácil manutenção.

3) Tecnologias Utilizadas
PHP 8.x (Core do sistema)

PostgreSQL (Banco de dados relacional)

Twig (Motor de templates para a View)

Composer (Gerenciamento de dependências)

Git (Versionamento)

4) Arquitetura e Padrões de Projeto
O diferencial deste projeto é a abstração da camada de banco de dados:

Active Record: Implementação de uma classe abstrata Record que centraliza as operações de load, store, all e delete.

Transaction & Connection: Gerenciamento robusto de conexões com banco de dados e controle de transações (commit/rollback) para garantir a integridade dos dados.

Encapsulamento: Uso de métodos mágicos PHP (__set, __get) para manipulação dinâmica de propriedades dos modelos.

Logs do Sistema: Sistema de log automatizado para monitoramento de todas as consultas SQL executadas durante as transações.

5) Instalação
Clone o repositório:

Bash
git clone https://github.com/seu-usuario/gestao_atletas.git
Instale as dependências:

Bash
composer install
Configure o arquivo de conexão db.ini com as credenciais do seu banco de dados.

Execute as migrações/scripts SQL fornecidos na pasta database/.
