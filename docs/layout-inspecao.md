## Inspeção visual — 2026-09-21

A página `pages/Apresentação.html` corresponde ao screenshot enviado. A renderização imediata mostra o `#splash-screen` cobrindo a página com `img/logo.png`, explicando o grande logo visível durante a captura. O CSS da apresentação também contém seletores inválidos (`.fundo-hero:` e `.content-section:not(.hero-moderna):`), além de `<br>` redundantes no hero e regras duplicadas ao final do arquivo.

O screenshot do usuário mostra o hero principal em escala muito pequena, com título, descrição e botões comprimidos no canto superior. A correção deve preservar a identidade visual escura, remover a dependência de quebras manuais de linha, garantir uma grade hero responsiva e evitar que a splash screen permaneça durante a navegação.
## Causa raiz

A página referenciava `../css/Apresentação.css`, mas o arquivo real se chama `css/apresentação.css`. Em Linux, a diferença entre `A` e `a` causa 404; com o CSS específico ausente, os logos da página ficaram sem dimensionamento e o hero não recebeu seu layout.

## Correções aplicadas

- Corrigida a referência do CSS para respeitar a capitalização real do arquivo.
- Corrigidos seletores pseudo-elemento inválidos (`::before`).
- Removidas quebras `<br>` redundantes no hero.
- Reorganizada a seção `Tudo em um só lugar` para manter recursos e mensagem alinhados em desktop e empilhar no mobile.
- Removida a splash screen bloqueadora; a apresentação abre diretamente no conteúdo.
- Mantido fallback visual para que elementos `.reveal` permaneçam visíveis mesmo se o JavaScript de animação falhar.
## Ajuste adicional

A seção `#sobre-projeto` foi reorganizada em desktop para posicionar o texto e a linha do tempo à esquerda, enquanto o título “Feito para quem joga.” fica à direita, como na referência enviada. Em telas pequenas, os dois blocos voltam ao empilhamento vertical original para evitar overflow.
## Correção responsiva adicional

Foi adicionada uma regra específica para `max-width: 768px`, forçando a seção `#sobre-projeto` a usar uma única coluna. O título permanece no primeiro bloco, seguido pelo texto, “Nossa História” e a linha do tempo, evitando que os elementos fiquem comprimidos lado a lado em celulares.
## Composição institucional final

A seção institucional foi reestruturada conforme a especificação recebida: o bloco superior utiliza duas colunas, com o marcador e o título “Feito para quem joga.” à esquerda e o parágrafo descritivo à direita. A seção “Nossa História” e a linha divisória ocupam toda a largura abaixo, com os quatro marcos distribuídos horizontalmente. Em dispositivos móveis, os blocos são empilhados em uma coluna.
## Timeline horizontal conectada

A cronologia agora possui uma linha guia contínua entre os quatro marcos, com um nó circular destacado sobre cada número. O espaçamento superior foi reduzido para aproximar a timeline do título “Nossa História”, eliminando o vácuo visual entre o cabeçalho e as etapas.
