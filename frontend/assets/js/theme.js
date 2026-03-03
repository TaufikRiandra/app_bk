// Simple theme toggle (light/dark) - defaults to light
(function(){
  const btn = document.getElementById('theme-toggle');
  if(!btn) return;
  const root = document.documentElement;
  const apply = (mode)=>{
    if(mode==='dark'){
      root.style.setProperty('--bg','#0f172a');
      root.style.setProperty('--card','#0b1220');
      root.style.setProperty('--text','#e6eef8');
      root.style.setProperty('--muted','#9aa4b2');
    } else {
      root.style.removeProperty('--bg');
      root.style.removeProperty('--card');
      root.style.removeProperty('--text');
      root.style.removeProperty('--muted');
    }
  }
  const saved = localStorage.getItem('theme') || 'light';
  apply(saved);
  btn.addEventListener('click',()=>{
    const next = (localStorage.getItem('theme')==='dark')? 'light':'dark';
    localStorage.setItem('theme',next);
    apply(next);
    btn.textContent = next==='dark'? '🌙':'☀️';
  });
})();
