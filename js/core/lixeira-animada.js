(() => {
'use strict';
const CLASSES=['is-busy','is-shrink','is-open','is-drop','is-close','is-dot','is-check','is-out'];
const wait=ms=>new Promise(r=>setTimeout(r,ms));
const preparar=btn=>{
 if(!btn||btn.classList.contains('lixeira-animada')) return;
 btn.classList.remove('btn-acao','btn-acao-perigo');
 btn.classList.add('lixeira-animada');
 btn.setAttribute('aria-label',btn.dataset.ariaLabel||'Remover');
 btn.innerHTML=`<span class="paper" aria-hidden="true"></span><span class="content"><span class="trash" aria-hidden="true"><svg viewBox="0 0 20 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path class="body-fill" d="M3.5 8h13l-1 13.2a1.5 1.5 0 0 1-1.5 1.3H6a1.5 1.5 0 0 1-1.5-1.3z"/><g class="lid"><path d="M1.5 5h17M7.5 5V3.2A1.2 1.2 0 0 1 8.7 2h2.6A1.2 1.2 0 0 1 12.5 3.2V5"/></g><path d="M8 11.5v7M12 11.5v7" opacity=".55"/></svg></span><span class="label">${btn.dataset.text||'Remover'}</span></span><span class="status" aria-hidden="true"><svg viewBox="0 0 10 10"><path d="M1.8 5.2 4.2 7.6 8.4 2.6"/></svg></span>`;
};
async function executar(btn,onDelete){
 if(!btn||btn.disabled||btn.classList.contains('is-busy')) return false;
 preparar(btn); btn.disabled=true; btn.classList.add('is-busy','is-shrink'); btn.setAttribute('aria-label','Removendo, aguarde');
 await new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r)));
 try{
  btn.classList.add('is-open'); await wait(120); btn.classList.add('is-drop'); await wait(1080);
  btn.classList.remove('is-open'); btn.classList.add('is-close'); await wait(100); btn.classList.add('is-dot'); await wait(400); btn.classList.add('is-check');
  await onDelete(); await wait(350); btn.classList.add('is-out'); await wait(250); return true;
 }catch(e){CLASSES.forEach(c=>btn.classList.remove(c));btn.disabled=false;btn.setAttribute('aria-label','Remover');throw e}
}
window.LixeiraAnimada={preparar,executar};
})();