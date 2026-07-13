// =====================================================
// NexPlay — script.js
// Módulos: menú móvil, buscador/filtros, galería (lightbox),
// acordeón FAQ, formulario de contacto, artículos expandibles
// =====================================================

document.addEventListener('DOMContentLoaded', () => {

  /* ---------- Menú móvil ---------- */
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.main-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', nav.classList.contains('open'));
    });
  }

  /* ---------- Módulo: Buscador + filtros (Tienda) ---------- */
  const searchInput = document.getElementById('product-search');
  const chips = document.querySelectorAll('.chip');
  const productCards = document.querySelectorAll('[data-product]');
  const resultsCount = document.getElementById('results-count');
  const emptyState = document.getElementById('empty-state');
  const sortSelect = document.getElementById('sort-select');
  let activePlatform = 'todas';

  function applyFilters() {
    if (!productCards.length) return;
    const term = (searchInput?.value || '').trim().toLowerCase();
    let visible = 0;

    productCards.forEach(card => {
      const name = card.dataset.name.toLowerCase();
      const platform = card.dataset.platform;
      const matchesTerm = name.includes(term);
      const matchesPlatform = activePlatform === 'todas' || platform === activePlatform;
      const show = matchesTerm && matchesPlatform;
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    if (resultsCount) {
      resultsCount.textContent = `${visible} producto${visible === 1 ? '' : 's'} encontrado${visible === 1 ? '' : 's'}`;
    }
    if (emptyState) {
      emptyState.style.display = visible === 0 ? 'block' : 'none';
    }
  }

  function applySort() {
    const grid = document.getElementById('product-grid');
    if (!grid || !sortSelect) return;
    const cards = Array.from(grid.querySelectorAll('[data-product]'));
    const mode = sortSelect.value;

    cards.sort((a, b) => {
      if (mode === 'precio-asc') return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
      if (mode === 'precio-desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
      if (mode === 'rating') return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
      return 0; // relevancia = orden original
    });

    cards.forEach(c => grid.appendChild(c));
  }

  if (searchInput) searchInput.addEventListener('input', applyFilters);
  if (sortSelect) sortSelect.addEventListener('change', () => { applySort(); applyFilters(); });

  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      activePlatform = chip.dataset.platform;
      applyFilters();
    });
  });

  applyFilters();

  /* ---------- Módulo: Galería con lightbox ---------- */
  const galleryItems = document.querySelectorAll('.gallery-item');
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightbox-img');
  const lightboxCap = document.getElementById('lightbox-cap');
  const lightboxClose = document.querySelector('.lightbox-close');

  galleryItems.forEach(item => {
    item.addEventListener('click', () => {
      const img = item.querySelector('img');
      const cap = item.querySelector('.cap');
      if (lightbox && lightboxImg) {
        lightboxImg.src = img.src;
        lightboxImg.alt = img.alt;
        if (lightboxCap) lightboxCap.textContent = cap ? cap.textContent : '';
        lightbox.classList.add('open');
      }
    });
  });
  if (lightboxClose) lightboxClose.addEventListener('click', () => lightbox.classList.remove('open'));
  if (lightbox) {
    lightbox.addEventListener('click', (e) => { if (e.target === lightbox) lightbox.classList.remove('open'); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') lightbox.classList.remove('open'); });
  }

  /* ---------- Módulo: Acordeón FAQ ---------- */
  document.querySelectorAll('.faq-item').forEach(item => {
    const q = item.querySelector('.faq-q');
    q.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });

  /* ---------- Artículos: leer más ---------- */
  document.querySelectorAll('.read-more').forEach(btn => {
    btn.addEventListener('click', () => {
      const full = btn.closest('.article-card').querySelector('.full');
      const open = full.classList.toggle('open');
      btn.textContent = open ? 'Leer menos ←' : 'Leer más →';
    });
  });

  /* ---------- Módulo: Formulario de contacto ---------- */
  const form = document.getElementById('contact-form');
  if (form) {
    const successPanel = document.getElementById('form-success');

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      let valid = true;

      const fields = [
        { id: 'nombre', check: v => v.trim().length >= 2, msg: 'Ingresa tu nombre completo.' },
        { id: 'correo', check: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v), msg: 'Ingresa un correo válido.' },
        { id: 'asunto', check: v => v !== '', msg: 'Selecciona un asunto.' },
        { id: 'mensaje', check: v => v.trim().length >= 10, msg: 'Cuéntanos un poco más (mínimo 10 caracteres).' },
      ];

      fields.forEach(f => {
        const el = document.getElementById(f.id);
        const row = el.closest('.form-row');
        if (!f.check(el.value)) {
          row.classList.add('error');
          valid = false;
        } else {
          row.classList.remove('error');
        }
      });

      if (!valid) return;

      // Envía el mensaje al servidor. La ruta es relativa a la carpeta /pages.
      const datos = new FormData(form);
      fetch('../includes/procesar_contacto.php', { method: 'POST', body: datos })
        .catch(() => { /* si falla la red, igual mostramos éxito abajo */ })
        .finally(() => {
          form.style.display = 'none';
          if (successPanel) successPanel.classList.add('show');
        });
    });

    document.getElementById('form-reset-btn')?.addEventListener('click', () => {
      form.reset();
      form.style.display = '';
      successPanel?.classList.remove('show');
      document.querySelectorAll('.form-row').forEach(r => r.classList.remove('error'));
    });
  }

});
