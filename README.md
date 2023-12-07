# Aparda Auto-Suggestion

Este projeto implementa um serviço de auto-sugestão para o Aparda.com, integrando sugestões de pesquisa de diferentes fontes e mantendo uma base de dados local para melhorar o desempenho.

## Funcionalidades

- Gera sugestões de pesquisa com base em consultas do usuário.
- Utiliza três fontes diferentes para sugestões: Ecosia, Brave e DuckDuckGo.
- Mantém uma base de dados local para armazenar sugestões e melhorar o desempenho.

## Tecnologias Utilizadas

- PHP
- MySQL
- cURL

## Instalação

1. Clone o repositório: `git clone https://github.com/apardabr/php-auto-suggestion.git`
2. Configure o ambiente PHP e MySQL.
3. Execute o comando para criar o banco de dados:

```sql
CREATE TABLE suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    query VARCHAR(255) NOT NULL,
    suggestions_json TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_query (query)
);
```

## Configuração

- Configure as credenciais do banco de dados em `database.php`.
- Certifique-se de que o servidor web está configurado corretamente para a aplicação.

## Utilização

- Acesse a API para obter sugestões de pesquisa: `https://ac.aparda.com/api?q={sua-consulta}`.
- As sugestões são mescladas a partir de Ecosia, Brave e DuckDuckGo.
- Os resultados são armazenados localmente para melhorar o desempenho.

## Contribuição

Sinta-se à vontade para contribuir.

## Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

---

**Desenvolvido por Matheus M Caetano | Aparda.com**
