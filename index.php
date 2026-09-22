<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['history']) || !is_array($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

$history = array_reverse($_SESSION['history']);

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
<title>Калькулятор</title>
<style>
  :root{
    --bg-1:#0b0c14;
    --bg-2:#14162a;
    --panel: rgba(255,255,255,0.06);
    --panel-border: rgba(255,255,255,0.10);
    --text-primary:#f5f6fa;
    --text-secondary: rgba(245,246,250,0.55);
    --accent-1:#7b5cff;
    --accent-2:#22d3ee;
    --op-bg: rgba(123,92,255,0.16);
    --op-bg-active: rgba(123,92,255,0.35);
    --num-bg: rgba(255,255,255,0.055);
    --num-bg-active: rgba(255,255,255,0.14);
    --func-bg: rgba(255,255,255,0.09);
    --danger:#ff6b6b;
    --radius: 22px;
    --shadow-soft: 0 8px 30px rgba(0,0,0,0.35);
    font-family: -apple-system, "SF Pro Display", "SF Pro Text", "Inter", system-ui, "Segoe UI", Roboto, sans-serif;
  }

  *{box-sizing:border-box;}

  html,body{
    height:100%;
    margin:0;
    background:
      radial-gradient(1200px 600px at 15% -10%, rgba(123,92,255,0.25), transparent 60%),
      radial-gradient(900px 500px at 110% 10%, rgba(34,211,238,0.18), transparent 55%),
      linear-gradient(180deg, var(--bg-1), var(--bg-2));
    color:var(--text-primary);
    -webkit-font-smoothing:antialiased;
    overscroll-behavior:none;
  }

  .app{
    min-height:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    padding: 32px 16px calc(32px + env(safe-area-inset-bottom,0px));
    padding-top: calc(32px + env(safe-area-inset-top,0px));
    gap:28px;
  }

  .stage{
    display:flex;
    gap:28px;
    align-items:stretch;
    width:100%;
    max-width: 880px;
    justify-content:center;
  }

  /* ---------- Calculator card ---------- */
  .calc{
    width: 100%;
    max-width: 380px;
    background: var(--panel);
    border:1px solid var(--panel-border);
    border-radius: 32px;
    padding: 22px;
    backdrop-filter: blur(30px) saturate(160%);
    -webkit-backdrop-filter: blur(30px) saturate(160%);
    box-shadow: var(--shadow-soft), inset 0 1px 0 rgba(255,255,255,0.06);
    display:flex;
    flex-direction:column;
    gap:18px;
    position:relative;
  }

  .calc-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding: 0 4px;
  }

  .brand{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13px;
    letter-spacing:0.06em;
    text-transform:uppercase;
    color:var(--text-secondary);
    font-weight:600;
  }
  .brand-dot{
    width:8px;height:8px;border-radius:50%;
    background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
    box-shadow: 0 0 12px rgba(123,92,255,0.8);
  }

  .icon-btn{
    width:34px;height:34px;
    border-radius:12px;
    border:1px solid var(--panel-border);
    background: rgba(255,255,255,0.05);
    color: var(--text-secondary);
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;
    transition: transform .15s ease, background .2s ease, color .2s ease;
  }
  .icon-btn:hover{ color: var(--text-primary); background: rgba(255,255,255,0.1); }
  .icon-btn:active{ transform: scale(0.9); }
  .icon-btn svg{ width:16px; height:16px; }

  .display{
    padding: 22px 10px 6px;
    text-align:right;
    min-height:108px;
    display:flex;
    flex-direction:column;
    justify-content:flex-end;
    gap:6px;
    overflow:hidden;
  }

  .display .expr{
    font-size:15px;
    color: var(--text-secondary);
    min-height:18px;
    white-space:nowrap;
    overflow-x:auto;
    scrollbar-width:none;
  }
  .display .expr::-webkit-scrollbar{ display:none; }

  .display .result{
    font-size: 52px;
    font-weight:600;
    letter-spacing: -0.02em;
    line-height:1.05;
    white-space:nowrap;
    overflow-x:auto;
    scrollbar-width:none;
    transform-origin: right center;
  }
  .display .result::-webkit-scrollbar{ display:none; }

  .display .result.pulse{
    animation: pulseIn .22s ease;
  }
  @keyframes pulseIn{
    0%{ transform: scale(0.9); opacity:0.4; }
    100%{ transform: scale(1); opacity:1; }
  }
  .display .result.error{ color: var(--danger); font-size:30px; }

  .pad{
    display:grid;
    grid-template-columns: repeat(4, 1fr);
    gap:12px;
  }

  button.key{
    position:relative;
    overflow:hidden;
    border:none;
    border-radius: 18px;
    height:66px;
    font-size:22px;
    font-weight:500;
    color: var(--text-primary);
    background: var(--num-bg);
    cursor:pointer;
    transition: transform .08s ease, background .15s ease, box-shadow .15s ease;
    -webkit-tap-highlight-color: transparent;
  }
  button.key:active{ transform: scale(0.93); }

  button.key.zero{ grid-column: span 2; border-radius:18px; }

  button.key.func{
    background: var(--func-bg);
    font-size:19px;
    color: var(--text-primary);
  }
  button.key.func.clear{ color: var(--danger); }

  button.key.op{
    background: var(--op-bg);
    color: #cdbdff;
    font-weight:600;
  }
  button.key.op.active-op{
    background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
    color:#0b0c14;
    box-shadow: 0 6px 18px rgba(123,92,255,0.45);
  }

  button.key.equals{
    background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
    color:#0b0c14;
    font-weight:700;
    box-shadow: 0 8px 22px rgba(123,92,255,0.4);
  }
  button.key.equals:active{ box-shadow: 0 4px 12px rgba(123,92,255,0.35); }

  /* ripple */
  .ripple{
    position:absolute;
    border-radius:50%;
    transform:scale(0);
    background: rgba(255,255,255,0.35);
    animation: rippleAnim .5s ease-out forwards;
    pointer-events:none;
  }
  @keyframes rippleAnim{
    to{ transform: scale(2.6); opacity:0; }
  }

  /* ---------- History panel ---------- */
  .history{
    width: 300px;
    background: var(--panel);
    border:1px solid var(--panel-border);
    border-radius: 32px;
    padding: 20px;
    backdrop-filter: blur(30px) saturate(160%);
    -webkit-backdrop-filter: blur(30px) saturate(160%);
    box-shadow: var(--shadow-soft), inset 0 1px 0 rgba(255,255,255,0.06);
    display:flex;
    flex-direction:column;
    gap:12px;
    max-height: 560px;
    transition: transform .35s cubic-bezier(.2,.8,.2,1), opacity .35s ease;
  }

  .history-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
  }
  .history-head h2{
    font-size:15px;
    margin:0;
    font-weight:600;
    letter-spacing:0.02em;
  }
  .clear-history{
    font-size:12px;
    color: var(--text-secondary);
    background:none;
    border:none;
    cursor:pointer;
    padding:6px 10px;
    border-radius:10px;
    transition: background .2s ease, color .2s ease;
  }
  .clear-history:hover{ background: rgba(255,255,255,0.08); color: var(--danger); }

  .history-list{
    overflow-y:auto;
    display:flex;
    flex-direction:column;
    gap:10px;
    padding-right:4px;
  }
  .history-list::-webkit-scrollbar{ width:5px; }
  .history-list::-webkit-scrollbar-thumb{ background: rgba(255,255,255,0.15); border-radius:10px; }

  .history-empty{
    color: var(--text-secondary);
    font-size:13px;
    text-align:center;
    padding: 30px 10px;
  }

  .history-item{
    background: rgba(255,255,255,0.04);
    border:1px solid rgba(255,255,255,0.06);
    border-radius:14px;
    padding:10px 12px;
    cursor:pointer;
    transition: background .18s ease, transform .12s ease;
    animation: itemIn .3s ease;
  }
  .history-item:hover{ background: rgba(255,255,255,0.09); }
  .history-item:active{ transform: scale(0.97); }
  .history-item .h-expr{
    font-size:12px;
    color: var(--text-secondary);
    margin-bottom:2px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }
  .history-item .h-result{
    font-size:18px;
    font-weight:600;
  }
  .history-item .h-time{
    float:right;
    font-size:10px;
    color: var(--text-secondary);
    font-weight:400;
  }

  @keyframes itemIn{
    from{ opacity:0; transform: translateY(-6px); }
    to{ opacity:1; transform: translateY(0); }
  }

  /* mobile: history as bottom sheet */
  @media (max-width: 760px){
    .stage{ flex-direction:column; align-items:center; }
    .history{
      position:fixed;
      left:0; right:0; bottom:0;
      width:auto;
      max-height: 60vh;
      border-radius: 26px 26px 0 0;
      transform: translateY(110%);
      opacity:0;
      pointer-events:none;
      z-index:20;
      padding-bottom: calc(20px + env(safe-area-inset-bottom,0px));
    }
    .history.open{
      transform: translateY(0);
      opacity:1;
      pointer-events:auto;
    }
    .backdrop{
      position:fixed; inset:0;
      background: rgba(0,0,0,0.45);
      opacity:0;
      pointer-events:none;
      transition: opacity .3s ease;
      z-index:15;
    }
    .backdrop.show{ opacity:1; pointer-events:auto; }
  }

  @media (min-width: 761px){
    .history{ display:flex; }
  }
  @media (max-width: 760px){
    .history{ display:flex; }
  }

  @media (max-width:400px){
    button.key{ height:58px; font-size:20px; }
    .display .result{ font-size:42px; }
  }
</style>
</head>
<body>

<div class="app">
  <div class="stage">

    <div class="calc" id="calc">
      <div class="calc-top">
        <div class="brand"><span class="brand-dot"></span>Calc</div>
        <button class="icon-btn" id="historyToggle" title="История" aria-label="История">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        </button>
      </div>

      <div class="display">
        <div class="expr" id="exprLine">&nbsp;</div>
        <div class="result" id="resultLine">0</div>
      </div>

      <div class="pad" id="pad">
        <button class="key func clear" data-action="clear">AC</button>
        <button class="key func" data-action="sign">±</button>
        <button class="key func" data-action="percent">%</button>
        <button class="key op" data-op="/">÷</button>

        <button class="key" data-digit="7">7</button>
        <button class="key" data-digit="8">8</button>
        <button class="key" data-digit="9">9</button>
        <button class="key op" data-op="*">×</button>

        <button class="key" data-digit="4">4</button>
        <button class="key" data-digit="5">5</button>
        <button class="key" data-digit="6">6</button>
        <button class="key op" data-op="-">−</button>

        <button class="key" data-digit="1">1</button>
        <button class="key" data-digit="2">2</button>
        <button class="key" data-digit="3">3</button>
        <button class="key op" data-op="+">+</button>

        <button class="key zero" data-digit="0">0</button>
        <button class="key" data-action="dot">,</button>
        <button class="key equals" data-action="equals">=</button>
      </div>
    </div>

    <aside class="history" id="historyPanel">
      <div class="history-head">
        <h2>История</h2>
        <button class="clear-history" id="clearHistoryBtn">Очистить</button>
      </div>
      <div class="history-list" id="historyList">
        <?php if (empty($history)): ?>
          <div class="history-empty" id="historyEmpty">Пока пусто —<br>первый расчёт впереди</div>
        <?php else: ?>
          <?php foreach ($history as $item): ?>
            <div class="history-item" data-result="<?= e($item['result']) ?>">
              <div class="h-expr"><span class="h-time"><?= e($item['time']) ?></span><?= e($item['expression']) ?></div>
              <div class="h-result"><?= e($item['result']) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </aside>

  </div>
</div>

<div class="backdrop" id="backdrop"></div>

<script>
(function(){
  const exprLine = document.getElementById('exprLine');
  const resultLine = document.getElementById('resultLine');
  const pad = document.getElementById('pad');
  const historyList = document.getElementById('historyList');
  const historyPanel = document.getElementById('historyPanel');
  const historyToggle = document.getElementById('historyToggle');
  const backdrop = document.getElementById('backdrop');
  const clearHistoryBtn = document.getElementById('clearHistoryBtn');

  let expression = '';      // строка, которая уйдёт на сервер
  let displayBuffer = '0';  // то, что видит пользователь как текущее число
  let justEvaluated = false;

  function isMobile(){ return window.matchMedia('(max-width: 760px)').matches; }

  function renderExpr(){
    exprLine.textContent = expression || '\u00A0';
  }

  function renderResult(text, isError){
    resultLine.textContent = text;
    resultLine.classList.toggle('error', !!isError);
    resultLine.classList.remove('pulse');
    void resultLine.offsetWidth; // reflow, чтобы анимация перезапустилась
    resultLine.classList.add('pulse');
    autoShrink();
  }

  function autoShrink(){
    // лёгкая подгонка размера шрифта под длину числа, чтобы не вылезало
    const len = resultLine.textContent.length;
    let size = 52;
    if (len > 9) size = 40;
    if (len > 12) size = 32;
    if (len > 16) size = 24;
    resultLine.style.fontSize = size + 'px';
  }

  function lastCharIsOperator(){
    return /[+\-*/%]$/.test(expression);
  }

  function highlightActiveOp(opChar){
    document.querySelectorAll('.key.op').forEach(b => b.classList.remove('active-op'));
    if (!opChar) return;
    const map = {'+':'+','-':'-','*':'*','/':'/'};
    const btn = document.querySelector('.key.op[data-op="'+opChar+'"]');
    if (btn) btn.classList.add('active-op');
  }

  function appendDigit(d){
    if (justEvaluated){
      expression = '';
      justEvaluated = false;
    }
    expression += d;
    renderExpr();
    resultLine.textContent = currentNumberTail() || '0';
    autoShrink();
    highlightActiveOp(null);
  }

  function currentNumberTail(){
    const m = expression.match(/(-?\d*\.?\d*)$/);
    return m ? m[1] : '';
  }

  function appendOp(op){
    if (expression === '' && op === '-'){
      expression = '-';
      renderExpr();
      return;
    }
    if (expression === '') return;
    if (lastCharIsOperator()){
      expression = expression.slice(0, -1) + op;
    } else {
      expression += op;
    }
    justEvaluated = false;
    renderExpr();
    highlightActiveOp(op);
  }

  function appendDot(){
    const tail = currentNumberTail();
    if (tail.includes('.')) return;
    if (tail === '' || tail === '-'){
      expression += (justEvaluated ? '' : '') + (tail === '' ? '0.' : '.');
    } else {
      expression += '.';
    }
    justEvaluated = false;
    renderExpr();
    resultLine.textContent = currentNumberTail();
  }

  function toggleSign(){
    // меняем знак у последнего числа в выражении
    const m = expression.match(/(-?\d*\.?\d+)$/);
    if (!m){ return; }
    const num = m[1];
    const start = expression.length - num.length;
    const flipped = num.startsWith('-') ? num.slice(1) : '-' + num;
    expression = expression.slice(0, start) + flipped;
    renderExpr();
    resultLine.textContent = currentNumberTail();
  }

  function applyPercent(){
    const m = expression.match(/(-?\d*\.?\d+)$/);
    if (!m) return;
    const num = parseFloat(m[1]);
    if (isNaN(num)) return;
    const start = expression.length - m[1].length;
    const pct = (num / 100).toString();
    expression = expression.slice(0, start) + pct;
    renderExpr();
    resultLine.textContent = currentNumberTail();
  }

  function clearAll(){
    expression = '';
    justEvaluated = false;
    highlightActiveOp(null);
    renderExpr();
    renderResult('0', false);
  }

  async function evaluate(){
    if (expression === '' || lastCharIsOperator()){
      return;
    }
    try{
      const res = await fetch('calculate.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: 'expression=' + encodeURIComponent(expression)
      });
      const data = await res.json();
      if (data.ok){
        renderResult(data.result, false);
        expression = data.result;
        justEvaluated = true;
        highlightActiveOp(null);
        renderHistory(data.history);
      } else {
        renderResult(data.error || 'Ошибка', true);
        justEvaluated = true;
      }
    } catch(e){
      renderResult('Нет связи', true);
    }
  }

  function renderHistory(items){
    historyList.innerHTML = '';
    if (!items || items.length === 0){
      const empty = document.createElement('div');
      empty.className = 'history-empty';
      empty.innerHTML = 'Пока пусто —<br>первый расчёт впереди';
      historyList.appendChild(empty);
      return;
    }
    items.forEach(item => {
      const div = document.createElement('div');
      div.className = 'history-item';
      div.dataset.result = item.result;
      div.innerHTML =
        '<div class="h-expr"><span class="h-time">'+escapeHtml(item.time)+'</span>'+escapeHtml(item.expression)+'</div>'+
        '<div class="h-result">'+escapeHtml(item.result)+'</div>';
      historyList.appendChild(div);
    });
  }

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  // клик по элементу истории — подставить результат в текущее выражение
  historyList.addEventListener('click', (e) => {
    const item = e.target.closest('.history-item');
    if (!item) return;
    expression = item.dataset.result;
    justEvaluated = true;
    renderExpr();
    resultLine.textContent = expression;
    autoShrink();
    if (isMobile()) closeHistory();
  });

  clearHistoryBtn.addEventListener('click', async () => {
    try{
      await fetch('clear_history.php', { method:'POST' });
    } catch(e){}
    renderHistory([]);
  });

  function openHistory(){
    historyPanel.classList.add('open');
    backdrop.classList.add('show');
  }
  function closeHistory(){
    historyPanel.classList.remove('open');
    backdrop.classList.remove('show');
  }
  historyToggle.addEventListener('click', () => {
    if (!isMobile()) return; // на десктопе панель всегда видна
    historyPanel.classList.contains('open') ? closeHistory() : openHistory();
  });
  backdrop.addEventListener('click', closeHistory);

  // ripple эффект на кнопках
  function addRipple(btn, e){
    const rect = btn.getBoundingClientRect();
    const circle = document.createElement('span');
    const size = Math.max(rect.width, rect.height);
    circle.className = 'ripple';
    circle.style.width = circle.style.height = size + 'px';
    const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left - size/2;
    const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top - size/2;
    circle.style.left = x + 'px';
    circle.style.top = y + 'px';
    btn.appendChild(circle);
    setTimeout(() => circle.remove(), 500);
  }

  pad.addEventListener('click', (e) => {
    const btn = e.target.closest('button.key');
    if (!btn) return;
    addRipple(btn, e);

    if (btn.dataset.digit !== undefined){
      appendDigit(btn.dataset.digit);
      return;
    }
    if (btn.dataset.op !== undefined){
      appendOp(btn.dataset.op);
      return;
    }
    switch(btn.dataset.action){
      case 'clear': clearAll(); break;
      case 'sign': toggleSign(); break;
      case 'percent': applyPercent(); break;
      case 'dot': appendDot(); break;
      case 'equals': evaluate(); break;
    }
  });

  // поддержка клавиатуры
  window.addEventListener('keydown', (e) => {
    if (e.key >= '0' && e.key <= '9'){ appendDigit(e.key); return; }
    if (['+','-','*','/'].includes(e.key)){ appendOp(e.key); return; }
    if (e.key === '.' || e.key === ','){ appendDot(); return; }
    if (e.key === 'Enter' || e.key === '='){ e.preventDefault(); evaluate(); return; }
    if (e.key === 'Backspace'){ expression = expression.slice(0, -1); renderExpr(); resultLine.textContent = currentNumberTail() || '0'; return; }
    if (e.key === 'Escape'){ clearAll(); return; }
    if (e.key === '%'){ applyPercent(); return; }
  });

  renderExpr();
})();
</script>

</body>
</html>