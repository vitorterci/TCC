# Integração de preços reais com IsThereAnyDeal

## Arquitetura implementada

O endpoint `php/api/precos.php` identifica o jogo pela tabela `jogos`, localiza/memoriza o UUID do jogo na IsThereAnyDeal usando `GET /games/lookup/v1?title=...`, busca ofertas por `POST /games/prices/v3?country=BR`, normaliza os resultados e os armazena em cache. O navegador chama somente o endpoint PHP; a chave nunca é enviada ao frontend.

As ofertas atuais ficam em `precos_cache_itad`; o mapeamento do jogo local para o UUID do provedor fica em `jogo_provedores`. `data_atualizacao` conserva o timestamp informado pelo provedor, enquanto `cache_atualizado_em` registra quando a consulta foi observada e controla o TTL. A tabela legada `precos` e as tabelas dos catálogos simulados não são removidas nem usadas pelo endpoint novo.

## Pré-requisitos

- PHP 7.4 ou superior, extensão `mysqli` e extensão `curl` habilitadas.
- Banco MySQL/MariaDB do projeto acessível pelo `php/config.php`.
- Uma chave válida criada na conta da IsThereAnyDeal. O projeto não inclui chave real ou fictícia.

## Preparar o banco no XAMPP

1. Faça backup do banco antes de qualquer migração.
2. Selecione o banco `tcc` no phpMyAdmin.
3. Importe `php/sql/itad_prices.sql` uma única vez. O script cria somente `jogo_provedores` e `precos_cache_itad`; não apaga nem altera dados antigos.
4. Confirme que `jogos.id` existe como `INT UNSIGNED` (ou tipo compatível para chave estrangeira). O dump `tcc.sql` do repositório contém versões concatenadas de esquemas e formatos diferentes de `precos`; valide o esquema que está efetivamente instalado antes de importar ou substituir o banco.

## Configurar a chave no XAMPP (Windows)

1. Edite `C:\xampp\apache\conf\extra\httpd-xampp.conf` localmente (não inclua sua chave em arquivos versionados).
2. Dentro de um bloco `<IfModule env_module>`, configure:

   ```apache
   SetEnv ITAD_API_KEY "COLOQUE_A_CHAVE_REAL_AQUI"
   SetEnv ITAD_CACHE_TTL "3600"
   ```

3. Substitua o texto de exemplo pela chave real apenas no computador/servidor local. Não a envie em mensagens, não a grave no JavaScript/HTML e não faça commit desse arquivo.
4. Salve e reinicie o Apache no painel do XAMPP. Verifique nos logs do Apache se a configuração foi carregada.

A variável lida pelo PHP é `ITAD_API_KEY`. A variável opcional `ITAD_CACHE_TTL` define o tempo do cache em segundos; o padrão é 3600 (1 hora), com limites de 60 a 86400 segundos.

## País, moeda e comparação

A requisição envia `country=BR`. A moeda apresentada é a retornada pela API; nenhum câmbio é calculado. Ofertas BRL são ordenadas e comparadas entre si. Se não houver BRL, a menor oferta só é escolhida quando as ofertas comparáveis usam uma única moeda; valores em moedas distintas não são somados nem comparados artificialmente.

## Chamadas de teste

Com Apache, MySQL, migração e chave configurados:

```text
http://localhost/tcc/php/api/precos.php?acao=buscar&slug=elden-ring
http://localhost/tcc/php/api/precos.php?acao=buscar&slug=alan-wake-2
http://localhost/tcc/php/api/precos.php?acao=buscar&id=4
http://localhost/tcc/php/api/precos.php?acao=buscar&jogo=Elden%20Ring
http://localhost/tcc/php/api/precos.php?acao=buscar&slug=elden-ring&forcar=1
```

A resposta bem-sucedida mantém `success`, `sucesso`, `jogo`, `precos`, `total`, `menor_preco`, `menorPreco`, `melhor_oferta`, `precos_simulados: false`, `origem: "api_real"` e `cache`. Quando não há ofertas, retorna lista vazia e `menor_preco: null`. Se o serviço não estiver configurado ou indisponível, retorna erro HTTP e mensagem genérica sem expor chave ou detalhes internos.

## Testes manuais recomendados

- Consultar os cinco jogos definidos na tarefa: Elden Ring, Alan Wake 2, Cyberpunk 2077, Baldur's Gate 3 e Hollow Knight.
- Repetir a chamada sem `forcar=1` e verificar `cache: true` sem nova consulta remota.
- Usar `forcar=1` para invalidar o cache daquele jogo.
- Testar slug desconhecido e indisponibilidade de rede/chave inválida; o frontend deve continuar renderizando o estado sem ofertas.
- Conferir que `url`/`url_oferta` são links externos HTTPS/HTTP enviados pelo provedor e que nenhuma chave aparece nas ferramentas de rede do navegador.
