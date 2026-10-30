document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-toggle-password]').forEach(button => {
    button.addEventListener('click', () => {
      const input = document.getElementById(button.dataset.togglePassword);
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.textContent = show ? 'Ocultar' : 'Mostrar';
      button.setAttribute('aria-pressed', String(show));
    });
  });
  const role = document.getElementById('rol');
  const academic = document.getElementById('academicFields');
  const career = document.getElementById('carrera_id');
  const module = document.getElementById('modulo_num');
  const cycle = document.getElementById('ciclo_num');
  function updateAcademicFields() {
    if (!role || !academic) return;
    const isStudent = role.value === 'alumno';
    academic.hidden = !isStudent;
    [career, module, cycle].filter(Boolean).forEach(el => { el.required = isStudent; });
  }
  if (role) { role.addEventListener('change', updateAcademicFields); updateAcademicFields(); }
  const form = document.getElementById('registerForm');
  if (form) form.addEventListener('submit', e => {
    const p = form.querySelector('[name="contrasena"]');
    const c = form.querySelector('[name="confirmar"]');
    if (p && c && p.value !== c.value) { e.preventDefault(); c.setCustomValidity('Las contraseñas no coinciden.'); c.reportValidity(); }
  });
  document.querySelectorAll('[data-confirm]').forEach(button => button.addEventListener('click', e => {
    if (!confirm(button.dataset.confirm || '¿Confirmas esta acción?')) e.preventDefault();
  }));
  let fontSize = Number(localStorage.getItem('cc-font-size') || 16);
  function setFontSize(size) { fontSize = Math.max(14, Math.min(22, size)); document.body.style.fontSize = fontSize + 'px'; localStorage.setItem('cc-font-size', fontSize); }
  setFontSize(fontSize);
  document.querySelectorAll('[data-font]').forEach(btn => btn.addEventListener('click', () => {
    const action = btn.dataset.font; setFontSize(action === 'up' ? fontSize + 1 : action === 'down' ? fontSize - 1 : 16);
  }));
  const speak = document.getElementById('speak'), stop = document.getElementById('stopSpeak');
  if (speak && 'speechSynthesis' in window) speak.addEventListener('click', () => {
    window.speechSynthesis.cancel();
    const text = document.querySelector('main')?.innerText || document.body.innerText;
    const utterance = new SpeechSynthesisUtterance(text); utterance.lang = 'es-PE'; window.speechSynthesis.speak(utterance);
  }); else if (speak) { speak.disabled = true; speak.title = 'La síntesis de voz no está disponible en este navegador.'; }
  if (stop && 'speechSynthesis' in window) stop.addEventListener('click', () => window.speechSynthesis.cancel());
});
