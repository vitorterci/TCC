# Correção de Bugs do Carrossel - Game Search

## Data: 29 de maio de 2026
## Versão: 1.1.0

---

## Resumo das Correções

Este documento detalha os três bugs críticos identificados e corrigidos no carrossel de destaques do projeto Game Search.

---

## 🐛 Bug 1: Falta de Atributo de Segurança em Links Externos

### Problema
Links abertos com `target="_blank"` não incluíam o atributo `rel="noopener noreferrer"`, criando uma vulnerabilidade de segurança conhecida como **Tabnabbing**.

### Localização
- `index.html` - linha 182 (script inline)
- `js/principal.js` - linha 79

### Solução Implementada
Adicionado `rel="noopener noreferrer"` a todos os links do carrossel:

```html
<!-- Antes -->
<a href="${slide.link}" target="_blank">

<!-- Depois -->
<a href="${slide.link}" target="_blank" rel="noopener noreferrer">
```

### Impacto
✅ **Segurança**: Previne que a página aberta em nova aba acesse o objeto `window.opener`  
✅ **Performance**: `noopener` permite que o navegador otimize melhor a aba aberta

---

## 🐛 Bug 2: Estilos Inadequados nas Imagens do Carrossel

### Problema
As imagens do carrossel não possuíam estilos inline suficientes para garantir que preenchessem 100% do container, causando possíveis distorções visuais e comportamentos inconsistentes em diferentes navegadores.

### Localização
- `index.html` - linha 183 (script inline)
- `js/principal.js` - linha 80

### Solução Implementada
Adicionados estilos inline completos nas imagens e links:

```html
<!-- Antes -->
<a href="${slide.link}" target="_blank">
    <img src="${slide.src}" alt="Destaque ${index + 1}">
</a>

<!-- Depois -->
<a href="${slide.link}" target="_blank" rel="noopener noreferrer" style="display: block; width: 100%; height: 100%;">
    <img src="${slide.src}" alt="Destaque ${index + 1}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
</a>
```

### Impacto
✅ **Renderização**: Imagens ocupam 100% do espaço disponível  
✅ **Consistência**: Comportamento uniforme em todos os navegadores  
✅ **Responsividade**: Melhor adaptação em diferentes tamanhos de tela

---

## 🐛 Bug 3: Classe CSS Não Definida

### Problema
O HTML utilizava `<div class="carrossel__container">` mas o CSS não definia estilos para essa classe, deixando o comportamento do carrossel indefinido em relação ao posicionamento e overflow.

### Localização
- `index.html` - linha 52
- `css/estilo.css` - não existia

### Solução Implementada

**Opção 1**: Remover o container desnecessário do HTML (aplicado)
```html
<!-- Antes -->
<section class="carrossel" id="carrossel-destaque">
    <div class="carrossel__container">
        <div class="carrossel__trilho" id="carrossel-trilho">...</div>
        <div class="carrossel__indicadores" id="carrossel-indicadores">...</div>
    </div>
</section>

<!-- Depois -->
<section class="carrossel" id="carrossel-destaque">
    <div class="carrossel__trilho" id="carrossel-trilho">...</div>
    <div class="carrossel__indicadores" id="carrossel-indicadores">...</div>
</section>
```

**Opção 2**: Adicionar estilos CSS para compatibilidade com layouts anteriores
```css
.carrossel__container {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}
```

### Impacto
✅ **Estrutura**: HTML mais limpo e sem elementos redundantes  
✅ **Compatibilidade**: CSS preparado para futuras alterações  
✅ **Manutenibilidade**: Código mais fácil de entender e manter

---

## 📋 Arquivos Modificados

| Arquivo | Linhas | Alterações |
|---------|--------|-----------|
| `index.html` | 50-58, 177-185, 271-275 | Remoção de container desnecessário, adição de estilos e atributos de segurança |
| `js/principal.js` | 72-76, 79-87, 104 | Adição de validação de erro, estilos e segurança, log de sucesso |
| `css/estilo.css` | 131-137 | Adição de estilos para `.carrossel__container` |

---

## ✅ Testes Recomendados

1. **Segurança**: Verificar se links abrem corretamente em nova aba
2. **Renderização**: Validar se imagens preenchem 100% do carrossel
3. **Responsividade**: Testar em mobile, tablet e desktop
4. **Console**: Verificar logs de sucesso no console do navegador
5. **Compatibilidade**: Testar em Chrome, Firefox, Safari e Edge

---

## 🔄 Próximas Melhorias Sugeridas

- Adicionar controles de navegação (setas anterior/próximo)
- Implementar suporte a toque (swipe) em dispositivos móveis
- Adicionar transições mais suaves
- Implementar lazy loading para imagens
- Adicionar testes automatizados

---

**Desenvolvido em conformidade com as diretrizes do projeto TCC**  
**Nomenclatura em português brasileiro conforme especificado**  
**Código comentado e bem documentado**
