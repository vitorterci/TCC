# Relatório de auditoria e implementação — preços reais

## Arquitetura encontrada

- Home e biblioteca usam `js/precos/precos.js` para chamar `php/api/precos.php` e preencher preços comparados.
- A página de detalhes (`pages/pagina.html` + `js/detalhes-jogo.js`) consulta os dados do jogo via `php/api/jogo.php` e as ofertas via `php/api/precos.php`.
- A lista acompanhada em `pages/perfil.html` faz uma consulta por jogo ao endpoint principal e usa a URL e preço retornados.
- O endpoint de preços original consultava `simulados/_tcc_catalogo.php`, não consultava o provedor real nem o cache MySQL e devolvia `precos_simulados: true`.
- `php/api/user.php` e `php/api/jogo.php` tinham consultas diretas à tabela legada `precos`; recomendações/objetos de jogo podiam carregar esses valores legados. A implementação passa essas leituras para o cache real, apenas em BRL onde a resposta exige um único número.

## Problemas encontrados

1. O endpoint principal produzia três ofertas simuladas, com valores BRL constantes e caminhos `/tcc/simulados/...`.
2. O JavaScript de detalhes recriava URLs locais conforme o nome da loja, em vez de usar a URL recebida do provedor.
3. A formatação do frontend fixava BRL mesmo quando uma API poderia retornar outra moeda.
4. `LojaInterface.php`, `precos.js` e `detalhes-jogo.js` na raiz eram um adaptador alternativo órfão que requisitava `../config.php`, `../servicos/lojas/*.php` e `ProvedorPrecos.php`, arquivos sem correspondência no repositório; nenhum include/call ativo para esses arquivos foi encontrado.
5. `php/teste_conexao_ggdeals.php` e `php/api/teste_cheapshark.php` são scripts de teste, sem integração no endpoint de produção.
6. `tcc.sql` reúne versões concatenadas de dumps: há múltiplas declarações incompatíveis de `jogos`, `plataformas` e `precos`. A última declaração de `precos` usa `plataforma_id` e unicidade por jogo/plataforma; blocos anteriores usam outro modelo. Há dados e tabelas simuladas. Por isso, o dump e os dados legados não foram alterados nem removidos.

## Implementação

- Provedor principal: IsThereAnyDeal.
- Matching: título de `jogos.nome` → ID UUID de ITAD; o ID é guardado em `jogo_provedores`.
- Preços: `POST /games/prices/v3?country=BR`, com a lista de IDs externos.
- Chave: somente `getenv('ITAD_API_KEY')`, em header para o provedor; nunca no frontend nem nos logs.
- Cache: uma hora por padrão, configurável por `ITAD_CACHE_TTL`; também são cacheadas respostas vazias para evitar repetição de buscas sem oferta.
- Normalização: loja real, plataforma(s), valor e valor original, desconto, moeda, URL fornecida, disponibilidade, timestamp e `origem: api_real`.
- Política monetária: não converter valores. BRL é comparado primeiro; sem BRL, a melhor oferta só é definida se os resultados forem de uma única moeda. Assim valores de moedas distintas não são comparados como se fossem equivalentes.
- Nenhum dado antigo foi apagado. As tabelas simuladas podem ser removidas manualmente no futuro apenas após decisão separada e validação de dependências.

## SQL criado

`php/sql/itad_prices.sql` cria:

- `jogo_provedores`: `id`, `jogo_id`, `provedor`, `external_id`, `external_slug`, `precos_atualizados_em`, `data_atualizacao`; PK em `id`, UNIQUE `(jogo_id, provedor)` e `(provedor, external_id)`, índice no ID externo e FK para `jogos(id)` com `ON DELETE CASCADE ON UPDATE CASCADE`.
- `precos_cache_itad`: `id`, `jogo_id`, `loja_id`, `loja`, `plataforma`, `preco`, `preco_antigo`, `desconto`, `moeda`, `disponibilidade`, `disponivel`, `url`, `url_oferta`, `external_id`, `data_atualizacao`, `cache_atualizado_em`; PK em `id`, UNIQUE `(jogo_id, loja_id, plataforma, moeda)`, índices de TTL e disponibilidade/preço, FK para `jogos(id)` com cascade. `data_atualizacao` preserva a data da oferta enviada pelo provedor; `cache_atualizado_em` registra quando a consulta foi observada localmente e controla o TTL.

As tabelas `precos`, `gogfake_ofertas`, `steamfake_ofertas`, `epicfake_ofertas`, bem como outros simulados, foram preservadas. Recomenda-se executar a migração nova em um banco de teste/backup primeiro, porque o `tcc.sql` versionado contém definições repetidas e incompatíveis.

## Arquivos alterados

- `php/api/precos.php`
- `php/api/jogo.php`
- `php/api/user.php`
- `js/precos/precos.js`
- `js/detalhes-jogo.js`
- `pages/perfil.html`

## Arquivos criados

- `php/sql/itad_prices.sql`
- `docs/itad-configuracao.md`
- `docs/relatorio-auditoria-precos.md`

## Arquivos removidos por serem apenas adaptadores órfãos/incompletos

- `LojaInterface.php`
- `precos.js`
- `detalhes-jogo.js`

As remoções não afetam o fluxo ativo, que utiliza `js/precos/precos.js`, `js/detalhes-jogo.js` e `php/api/precos.php`.

## Arquivos auditados, sem alteração

`php/config.php`, `php/teste_conexao_ggdeals.php`, `php/api/teste_cheapshark.php`, `php/api/admin/precos.php`, `js/jogos/cards.js`, `js/jogos/cards-expansiveis.js`, `js/home.js`, `js/pages/biblioteca.js`, `pages/pagina.html`, `index.html`, `tcc.sql` e os diretórios simulados. Os consumidores de dados de jogo/recomendação foram alterados apenas onde consultavam `precos` como fonte de valores.

## Verificações executadas

- `php -l` aprovado nos três PHP alterados (`php/api/precos.php`, `php/api/jogo.php`, `php/api/user.php`).
- `node --check` aprovado nos dois JavaScript alterados (`js/precos/precos.js`, `js/detalhes-jogo.js`).
- Testes isolados em PHP cobriram URL real preservada, rejeição de preço zero e URL local, seleção de menor oferta BRL, proteção contra comparação entre moedas diferentes e resposta sem ofertas.
- Smoke tests em Node cobriram moeda do card, extração da melhor oferta, remoção de preços inválidos e ordenação de ofertas.
- `git diff --check` aprovado e nenhuma referência a lojas fake ou caminhos `/simulados/` permaneceu na API ou na renderização ativa de ofertas. Os três controles de preferência de loja foram preservados, mas agora apresentam os nomes reais Steam, Epic Games e GOG; `php/api/user.php` traduz valores legados já salvos para esses nomes, mantendo as preferências existentes.

A chave ITAD não está configurada e não há instância MySQL do projeto conectada neste ambiente; por isso, não foi possível executar chamadas remotas reais nem a integração ponta a ponta com o banco. Após configurar a chave e aplicar a migração no XAMPP, execute os testes documentados em `docs/itad-configuracao.md`.

## Arquivos auditados, sem alteração (complemento)

O `precos.php` da raiz também foi inspecionado: trata-se de uma cópia de perfil sem links de navegação encontrados no repositório e não foi alterada.
