# Decisões de Projeto — Achaê

Este documento registra as decisões tomadas durante o planejamento do sistema Achaê.
Serve como referência para o desenvolvimento e para futuras manutenções.

---

## 1. Tabela USUARIO

### 1.1 Campos de tipo de usuário separados

O spec original previa um único campo `tipo_usuario`. Decidiu-se separar em dois campos:

- **`vinculo`**: indica o vínculo institucional do usuário com o campus.
  - Valores possíveis: `aluno`, `professor`, `servidor`
- **`perfil`**: indica o nível de acesso dentro do sistema.
  - Valores possíveis: `usuario`, `admin`

**Motivo:** separa a identidade institucional (quem é a pessoa) do papel no sistema (o que pode fazer), tornando o controle de acesso mais claro e flexível.

### 1.2 Campo telefone (opcional)

Adicionado o campo `telefone` (texto, opcional) na tabela USUARIO.

**Motivo:** facilitar o contato entre as partes após a solicitação ser aceita.

### 1.3 Status do usuário

- **`ativo`**: usuário pode logar normalmente.
- **`bloqueado`**: usuário não consegue realizar login.

---

## 2. Tabela OBJETO

### 2.1 Campo tipo

- **`perdido`**: o publicante perdeu o objeto e está procurando.
- **`encontrado`**: o publicante encontrou o objeto e quer devolver.

### 2.2 Status do objeto

- **`aberto`**: o caso ainda não foi resolvido.
- **`resolvido`**: o objeto foi devolvido/encontrado pelo dono.
- **`removido`**: a publicação foi removida pelo admin (moderação).

---

## 3. Tabela SOLICITACAO

### 3.1 Status da solicitação

- **`pendente`**: o dono ainda não respondeu.
- **`aceita`**: o dono aceitou — contatos ficam visíveis para ambos.
- **`recusada`**: o dono recusou a solicitação.

### 3.2 Regras de negócio das solicitações

- Um usuário **não pode solicitar devolução do próprio objeto**.
- Um usuário **não pode ter duas solicitações pendentes para o mesmo objeto** (apenas uma por vez).
- O telefone e o e-mail do **solicitante** só aparecem para o dono após a solicitação ser aceita.
- O telefone e o e-mail do **dono** só aparecem para o solicitante após a solicitação ser aceita.
- A mensagem da solicitação é obrigatória (facilita a comunicação inicial).

---

## 4. Upload de fotos

- Extensões permitidas: `jpg`, `jpeg`, `png`, `webp`.
- Tamanho máximo: **2 MB**.
- O tipo MIME real deve ser verificado com `finfo` (não apenas a extensão).
- O arquivo é renomeado com nome aleatório (para evitar conflitos e colisões).
- Arquivos salvos na pasta `uploads/`.

---

## 5. Categorias iniciais

Cadastradas via `seed.sql` no banco de dados:

1. Documentos
2. Eletrônicos
3. Roupas e Acessórios
4. Chaves
5. Material Escolar
6. Garrafas e Utensílios
7. Outros

---

## 6. Segurança

- Senhas: `password_hash()` ao salvar, `password_verify()` ao conferir.
- SQL: somente consultas preparadas PDO. Nunca concatenar variáveis do usuário.
- Saída: todo dado exibido passa por `htmlspecialchars()`.
- CSRF: formulários POST têm token na sessão, conferido no servidor.
- Sessão: `session_regenerate_id(true)` após login; logout destrói a sessão completamente.
- Funções `exigir_login()` e `exigir_admin()` chamadas no topo de cada página restrita.
- Autorização por dono verificada no servidor (não apenas escondendo botões).

---

## 7. O que foi adiado para a versão 2

- **Denúncias**: o spec menciona denúncias nas permissões do admin, mas não haverá tabela nem telas de denúncia na v1.

---

## 8. Configuração e ambiente

- `config/database.php` **não vai para o Git** (listado no `.gitignore`).
- `config/database.example.php` contém valores fictícios como modelo.
- Campo `local` do objeto: texto livre na v1.
- Sistema rodando via XAMPP em `http://localhost/achae/`.
