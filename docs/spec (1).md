# Spec Inicial — Achaê

**Sistema de Achados e Perdidos do Campus**

---

## 1. Objetivo

O **Achaê** é um sistema web de achados e perdidos destinado a alunos, professores e servidores do campus. O sistema permitirá cadastrar, consultar e gerenciar objetos perdidos ou encontrados, facilitando a comunicação entre quem perdeu um pertence e quem o encontrou.

O sistema terá cadastro e login de usuários, publicação de objetos, pesquisa e filtros, além de um fluxo de solicitação de devolução.

---

## 2. Usuários

### 2.1 Usuário comum

O usuário comum poderá ser aluno, professor ou servidor do campus.

**Permissões:**
- Criar uma conta;
- Realizar login e logout;
- Editar seus dados básicos;
- Publicar objetos perdidos;
- Publicar objetos encontrados;
- Consultar objetos publicados;
- Pesquisar e filtrar objetos;
- Visualizar detalhes de uma publicação;
- Solicitar a devolução de um objeto;
- Consultar o status de suas solicitações;
- Marcar uma publicação como resolvida após a devolução do objeto.

### 2.2 Administrador

O administrador será responsável pela manutenção e moderação do sistema.

**Permissões:**
- Realizar login administrativo;
- Consultar usuários cadastrados;
- Consultar todas as publicações;
- Editar ou remover publicações inadequadas;
- Bloquear ou desativar usuários;
- Consultar solicitações de devolução;
- Gerenciar categorias de objetos;
- Visualizar denúncias realizadas pelos usuários.

---

## 3. Casos de uso principais

### UC01 — Cadastrar usuário

Permite que uma pessoa crie uma conta no sistema informando seus dados básicos.

### UC02 — Realizar login

Permite que um usuário cadastrado entre no sistema utilizando e-mail e senha.

### UC03 — Publicar objeto

Permite que um usuário autenticado cadastre um objeto como **perdido** ou **encontrado**, informando título, descrição, categoria, local e data.

### UC04 — Consultar objetos

Permite que usuários consultem os objetos cadastrados no sistema.

### UC05 — Pesquisar e filtrar objetos

Permite localizar publicações utilizando termos de pesquisa, categoria, tipo, local e status.

### UC06 — Visualizar detalhes do objeto

Permite consultar todas as informações disponíveis de uma publicação específica.

### UC07 — Solicitar devolução

Permite que um usuário interessado entre em contato com o responsável por uma publicação para tentar confirmar a propriedade ou realizar a devolução do objeto.

### UC08 — Gerenciar publicação

Permite que o usuário responsável por uma publicação altere suas informações ou marque o objeto como resolvido.

### UC09 — Administrar usuários e publicações

Permite que o administrador consulte, edite, bloqueie ou remova registros quando necessário.

---

## 4. Requisitos funcionais (RF)

### RF01 — Cadastro de usuário

O sistema deve permitir que uma pessoa crie uma conta informando nome, e-mail, senha e tipo de usuário.

### RF02 — Login

O sistema deve permitir que usuários cadastrados realizem login utilizando e-mail e senha válidos.

### RF03 — Logout

O sistema deve permitir que um usuário autenticado encerre sua sessão.

### RF04 — Publicação de objeto

O sistema deve permitir que usuários autenticados cadastrem um objeto perdido ou encontrado, informando título, descrição, categoria, local, data e tipo da ocorrência.

### RF05 — Consulta de objetos

O sistema deve permitir que usuários visualizem uma lista de objetos cadastrados no sistema.

### RF06 — Pesquisa e filtros

O sistema deve permitir pesquisar objetos por texto e filtrar as publicações por categoria, tipo, local e status.

### RF07 — Visualização de detalhes

O sistema deve permitir que o usuário visualize os detalhes de uma publicação selecionada.

### RF08 — Solicitação de devolução

O sistema deve permitir que um usuário autenticado envie uma solicitação relacionada a um objeto publicado.

### RF09 — Consulta de solicitações

O sistema deve permitir que o usuário visualize as solicitações de devolução relacionadas às suas publicações e às solicitações que realizou.

### RF10 — Atualização do status do objeto

O sistema deve permitir que o responsável pela publicação altere o status do objeto, incluindo a opção de marcar o caso como resolvido.

### RF11 — Gerenciamento administrativo

O sistema deve permitir que o administrador consulte e gerencie usuários, publicações e solicitações.

### RF12 — Persistência dos dados

O sistema deve armazenar no banco de dados os usuários, objetos publicados e solicitações de devolução, permitindo posteriormente consultar esses registros.

---

## 5. Requisitos não funcionais (RNF)

### RNF01 — Segurança das senhas

As senhas dos usuários **nunca devem ser armazenadas em texto puro**. O sistema deve utilizar uma função de hash segura para armazená-las no banco de dados.

### RNF02 — Validação no servidor

Todo formulário enviado pelo usuário deve possuir **validação no servidor**, mesmo que também exista validação no lado do cliente.

### RNF03 — Controle de acesso

O sistema deve verificar a autenticação e as permissões do usuário antes de permitir o acesso a funcionalidades restritas.

### RNF04 — Proteção contra SQL Injection

As consultas ao banco de dados devem utilizar mecanismos seguros, como consultas preparadas, evitando a inserção direta de dados fornecidos pelo usuário nas consultas SQL.

### RNF05 — Integridade dos dados

O sistema deve utilizar chaves primárias e estrangeiras para manter a relação e a integridade dos dados armazenados.

### RNF06 — Usabilidade

As telas devem possuir uma interface simples e organizada, permitindo que usuários encontrem as principais funcionalidades sem necessidade de treinamento.

### RNF07 — Compatibilidade

O sistema deve funcionar nos principais navegadores modernos, como Google Chrome, Microsoft Edge e Mozilla Firefox.

### RNF08 — Desempenho

As páginas principais do sistema devem responder em tempo adequado para operações comuns de consulta, cadastro e pesquisa, considerando a quantidade de dados prevista para o projeto.

---

## 6. Modelo de dados — DER simples

O banco de dados será composto pelas seguintes tabelas principais:

### USUARIO

- `id_usuario` — PK
- `nome`
- `email`
- `senha_hash`
- `tipo_usuario`
- `status`
- `data_cadastro`

### CATEGORIA

- `id_categoria` — PK
- `nome`

### OBJETO

- `id_objeto` — PK
- `id_usuario` — FK → `USUARIO.id_usuario`
- `id_categoria` — FK → `CATEGORIA.id_categoria`
- `tipo`
- `titulo`
- `descricao`
- `local`
- `data_ocorrencia`
- `foto`
- `status`
- `data_publicacao`

### SOLICITACAO

- `id_solicitacao` — PK
- `id_objeto` — FK → `OBJETO.id_objeto`
- `id_usuario` — FK → `USUARIO.id_usuario`
- `mensagem`
- `status`
- `data_solicitacao`

### Relacionamentos

- Um **USUARIO** pode criar vários **OBJETOS**.
- Uma **CATEGORIA** pode estar relacionada a vários **OBJETOS**.
- Um **OBJETO** pertence a uma **CATEGORIA** e foi publicado por um **USUARIO**.
- Um **OBJETO** pode possuir várias **SOLICITAÇÕES**.
- Um **USUARIO** pode realizar várias **SOLICITAÇÕES**.

### Representação simplificada

```text
USUARIO
  │
  │ 1:N
  ▼
OBJETO ───────── N:1 ───────► CATEGORIA
  │
  │ 1:N
  ▼
SOLICITACAO
  ▲
  │ N:1
  │
USUARIO
```

Esse modelo permite realizar o fluxo principal do sistema: um usuário cadastrado publica um objeto, os dados são gravados no banco e posteriormente outros usuários podem consultar a publicação e realizar uma solicitação de devolução.

---

## 7. Critérios de aceite

### RF01 — Cadastro

**Dado que** a pessoa ainda não possui uma conta, **quando** preencher corretamente os campos obrigatórios e enviar o formulário, **então** o sistema deve criar o usuário no banco de dados e informar que o cadastro foi realizado.

### RF02 — Login

**Dado que** o usuário possui uma conta cadastrada, **quando** informar e-mail e senha corretos, **então** o sistema deve iniciar sua sessão e permitir acesso às funcionalidades autenticadas.

### RF04 — Publicação de objeto

**Dado que** o usuário está autenticado, **quando** preencher corretamente os dados de um objeto e enviar o formulário, **então** o sistema deve gravar a publicação no banco de dados e exibi-la na lista de objetos.

### RF05 — Consulta de objetos

**Dado que** existem objetos cadastrados, **quando** o usuário acessar a área de objetos, **então** o sistema deve consultar o banco de dados e apresentar as publicações disponíveis.

### RF06 — Pesquisa e filtros

**Dado que** existem diferentes objetos cadastrados, **quando** o usuário realizar uma pesquisa ou aplicar um filtro, **então** o sistema deve apresentar somente as publicações compatíveis com os critérios informados.

### RF07 — Visualização de detalhes

**Dado que** existe uma publicação cadastrada, **quando** o usuário selecionar essa publicação, **então** o sistema deve apresentar seus dados detalhados.

### RF08 — Solicitação de devolução

**Dado que** o usuário está autenticado e visualizando uma publicação, **quando** enviar uma solicitação de devolução com uma mensagem válida, **então** o sistema deve gravar a solicitação no banco de dados e vinculá-la ao objeto e ao usuário responsável pela solicitação.

### RF09 — Consulta de solicitações

**Dado que** o usuário possui solicitações registradas, **quando** acessar sua área de solicitações, **então** o sistema deve consultar o banco de dados e apresentar as solicitações relacionadas a ele.

### RF10 — Atualização de status

**Dado que** o usuário é responsável por uma publicação, **quando** alterar o status do objeto para resolvido, **então** o sistema deve atualizar o status correspondente no banco de dados.

### RF11 — Gerenciamento administrativo

**Dado que** o usuário possui perfil de administrador, **quando** acessar o painel administrativo, **então** o sistema deve permitir consultar e gerenciar os registros autorizados.

### RF12 — Persistência

**Dado que** um usuário realize uma operação de cadastro de objeto ou solicitação, **quando** a operação for concluída com sucesso, **então** os dados devem permanecer armazenados no banco e poder ser recuperados posteriormente por meio de consultas.

---

## 8. Decisão de stack e deploy

### Stack escolhida

O sistema será desenvolvido utilizando:

- **HTML5** — estrutura das páginas;
- **CSS3** — estilização e responsividade;
- **JavaScript** — interações e validações no lado do cliente;
- **PHP** — desenvolvimento do back-end e regras de negócio;
- **MySQL** — armazenamento e consulta dos dados;
- **XAMPP** — ambiente de desenvolvimento local com servidor Apache, PHP e MySQL.

### Justificativa

A stack foi escolhida por ser adequada ao desenvolvimento de uma aplicação web com cadastro, autenticação, operações de banco de dados e gerenciamento de informações. Além disso, PHP e MySQL são tecnologias compatíveis com os conhecimentos adquiridos no curso e permitem desenvolver o projeto de forma gradual.

### Deploy

Durante o desenvolvimento e os testes iniciais, o sistema será executado localmente utilizando o **XAMPP**. Para uma versão de demonstração acessível pela internet, o sistema será publicado em uma hospedagem compatível com **PHP, Apache e MySQL**, mantendo a mesma estrutura utilizada no ambiente de desenvolvimento.

A escolha dessa infraestrutura evita a necessidade de alterar a arquitetura do sistema para realizar a publicação, pois o ambiente de produção será compatível com a stack utilizada no desenvolvimento.
