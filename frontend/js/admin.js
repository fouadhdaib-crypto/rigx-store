/* Minimal progressive enhancement; core navigation and forms work without JavaScript. */
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('nav-toggle');
  if (!toggle) return;
  const periodPicker = document.querySelector('.period-picker');
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      if (toggle.checked) toggle.checked = false;
      if (periodPicker?.open) { periodPicker.open = false; periodPicker.querySelector('summary')?.focus(); }
    }
  });





  document.addEventListener('click', (event) => {
    if (periodPicker?.open && !periodPicker.contains(event.target)) periodPicker.open = false;
  });


  
  document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', () => { toggle.checked = false; });
  });
});
