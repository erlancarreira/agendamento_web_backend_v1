# API Clínica

[![Coverage Status](https://coveralls.io/repos/github/slimphp/Slim-Skeleton/badge.svg?branch=master)](https://coveralls.io/github/slimphp/Slim-Skeleton?branch=master)

API para gerenciamento de clínicas médicas construída com Slim Framework 4. Teste

## Instalação

Você precisará do PHP 7.4 ou superior.

```bash
composer create-project slim/slim-skeleton [my-app-name]
```

Substitua `[my-app-name]` pelo nome desejado do diretório da aplicação. Você precisa:

* Apontar o document root do seu virtual host para o diretório `public/` da aplicação.
* Garantir que o diretório `logs/` tenha permissão de escrita.

## Executando a Aplicação

Para desenvolvimento, você pode usar os comandos:

```bash
cd [my-app-name]
composer start
```

Ou usar `docker-compose`:
```bash
cd [my-app-name]
docker-compose up -d
```
Depois, acesse `http://localhost:8080` no seu navegador.

Para executar os testes:
```bash
composer test
```

## Regras de Negócio

### Factories

#### PacienteFactory
- Cria instâncias de pacientes baseadas no cliente (ACOR ou Default)
- Implementa o padrão Factory Method para diferentes tipos de pacientes
- Regras específicas:
  - Para cliente ACOR: Utiliza AcorPaciente com regras específicas de convênio
  - Para outros clientes: Utiliza DefaultPaciente com regras padrão
  - Sempre inicializa com instâncias de Paciente e Convênio

#### PageFactory
- Gerencia a criação de páginas baseadas no cliente
- Regras específicas:
  - Cliente ACOR: Retorna PageAcor com layout e regras específicas
  - Outros clientes: Retorna PageDefault com configuração padrão
  - Ambos recebem uma instância de Paciente

#### ScheduleFilterFactory
- Cria filtros de agendamento com validações de tempo
- Regras específicas:
  - Utiliza UnixTimeProvider para validações de horário
  - Implementa interface ScheduleFilterInterface
  - Aplica regras de filtragem de horários disponíveis

### Implementações Específicas

#### DefaultPaciente
1. Método byCPF:
   - Verifica existência do paciente
   - Valida convênios associados
   - Regras de retorno:
     - Paciente não existe: Redireciona para cadastro particular
     - Paciente sem convênio: Redireciona para cadastro de convênio
     - Convênio bloqueado: Redireciona para central de agendamento
   - Verifica histórico de agendamentos
   - Atualiza estado com último agendamento se existente

2. Regras de Convênio:
   - Verifica convênios bloqueados
   - Permite cadastro como particular
   - Mantém histórico de convênios utilizados

#### AcorPaciente
1. Método byCPF:
   - Mesmas validações base do DefaultPaciente
   - Regras específicas ACOR:
     - Tratamento especial para pacientes sem convênio
     - Redirecionamento para central de agendamento em casos específicos
     - Validações adicionais de agendamentos

2. Regras Adicionais:
   - Método handleHasAgendamento para verificar agendamentos existentes
   - Método handleConfirmConvenio para validação específica de convênios
   - Tratamento específico para modal de fallback

### Endpoints e Métodos

#### Agenda `/agenda`
- GET: Retorna agendamentos
  - Filtra por data, profissional e especialidade
  - Valida disponibilidade em tempo real
- POST: Cria novo agendamento
  - Valida conflitos de horário
  - Verifica elegibilidade do convênio
  - Confirma disponibilidade do profissional

#### Consultas `/consultas`
- GET: Lista consultas
  - Filtra por período, status e paciente
  - Inclui histórico de alterações
- PUT: Atualiza consulta
  - Permite reagendamento com regras específicas
  - Valida status da consulta
  - Registra alterações

#### Convênios `/convenios`
- GET: Lista convênios
  - Filtra por status e tipo
  - Inclui regras de cobertura
- POST: Adiciona convênio ao paciente
  - Valida documentação necessária
  - Verifica elegibilidade
  - Registra histórico de alterações

### Validações

1. Validação de Pacientes:
   - CPF válido e cadastrado
   - Convênio ativo e elegível
   - Sem pendências financeiras
   - Documentação completa

2. Validação de Agendamentos:
   - Horário disponível
   - Profissional disponível
   - Convênio válido para especialidade
   - Intervalo mínimo entre consultas

3. Validação de Convênios:
   - Status ativo
   - Cobertura para especialidade
   - Carência cumprida
   - Documentação atualizada

4. Validação de Profissionais:
   - Cadastro ativo
   - Agenda disponível
   - Especialidades cadastradas
   - Convênios aceitos
