// =====================================================
// NexPlay — script.js
// Módulos: menú móvil, datos desde la API (productos/blog)
// con respaldo local, buscador/filtros, galería (lightbox),
// acordeón FAQ, formulario de contacto, artículos expandibles
// =====================================================

// Carpeta donde vive api/ respecto a la página actual.
// index.html está en la raíz -> "api"
// pages/*.html están un nivel abajo -> "../api"
const API_BASE = window.location.pathname.includes('/pages/') ? '../api' : 'api';

/* =====================================================
   Datos de respaldo (idénticos a nexplayn.sql)
   Se usan si no se puede llegar al servidor PHP/API,
   por ejemplo al abrir el HTML directamente sin XAMPP.
   ===================================================== */
const PRODUCTOS_RESPALDO = [
  { id:1,  nombre:'PlayStation 5 Slim',           slug:'ps5-slim',                 tipo:'Consola',          descripcion:'1TB SSD, control DualSense incluido.',           precio:11499, precio_anterior:null, calificacion:5, imagen:'assets/img/console-ps.svg',     categoria_slug:'ps5' },
  { id:2,  nombre:'Xbox Series X',                slug:'xbox-series-x',            tipo:'Consola',          descripcion:'1TB SSD, 4K nativo, retrocompatible.',           precio:11999, precio_anterior:null, calificacion:5, imagen:'assets/img/console-xbox.svg',   categoria_slug:'xbox' },
  { id:3,  nombre:'Nintendo Switch OLED',         slug:'switch-oled',              tipo:'Consola',          descripcion:'Pantalla OLED 7", edición estándar.',            precio:7799,  precio_anterior:8999, calificacion:4, imagen:'assets/img/console-switch.svg', categoria_slug:'switch' },
  { id:4,  nombre:'Cartucho Colección 16-bit',    slug:'cartucho-coleccion-16bit', tipo:'Retro',            descripcion:'Edición restaurada, caja e instructivo.',        precio:1299,  precio_anterior:null, calificacion:4, imagen:'assets/img/console-retro.svg',  categoria_slug:'retro' },
  { id:5,  nombre:'Headset Inalámbrico Pro',      slug:'headset-inalambrico-pro',  tipo:'Accesorio',        descripcion:'Sonido envolvente 7.1, batería 20h.',            precio:1899,  precio_anterior:null, calificacion:5, imagen:'assets/img/acc-headset.svg',    categoria_slug:'pc' },
  { id:6,  nombre:'Teclado Mecánico RGB',         slug:'teclado-mecanico-rgb',     tipo:'Accesorio',        descripcion:'Switches táctiles, reposamuñecas incluido.',     precio:1599,  precio_anterior:null, calificacion:4, imagen:'assets/img/acc-keyboard.svg',   categoria_slug:'pc' },
  { id:7,  nombre:'Control DualSense Extra',      slug:'control-dualsense-extra',  tipo:'Accesorio · PS5',  descripcion:'Vibración háptica, gatillos adaptativos.',       precio:1399,  precio_anterior:null, calificacion:5, imagen:'assets/img/acc-controller.svg', categoria_slug:'ps5' },
  { id:8,  nombre:'SSD NVMe 1TB Gaming',          slug:'ssd-nvme-1tb-gaming',      tipo:'Accesorio',        descripcion:'Lectura hasta 7000 MB/s, disipador incluido.',   precio:1699,  precio_anterior:null, calificacion:5, imagen:'assets/img/acc-storage.svg',    categoria_slug:'pc' },
  { id:9,  nombre:'Bundle Switch + Mario Kart',   slug:'bundle-switch-mariokart',  tipo:'Bundle',           descripcion:'Consola + juego físico, ahorro incluido.',       precio:8499,  precio_anterior:null, calificacion:5, imagen:'assets/img/acc-bundle.svg',     categoria_slug:'switch' },
  { id:10, nombre:'Galaxy Quest: Edición Deluxe', slug:'galaxy-quest-deluxe',      tipo:'Videojuego',       descripcion:'Incluye pase de temporada y skins exclusivas.',  precio:1199,  precio_anterior:null, calificacion:4, imagen:'assets/img/acc-game.svg',       categoria_slug:'xbox' },
  { id:11, nombre:'Handheld Retro Emulador',      slug:'handheld-retro-emulador',  tipo:'Retro',            descripcion:'Miles de títulos clásicos preinstalados.',       precio:2199,  precio_anterior:null, calificacion:3, imagen:'assets/img/console-retro.svg',  categoria_slug:'retro' },
  { id:12, nombre:'Control Elite Series 2',       slug:'control-elite-series-2',   tipo:'Accesorio · Xbox', descripcion:'Componentes intercambiables, grip texturizado.', precio:2599,  precio_anterior:null, calificacion:5, imagen:'assets/img/acc-controller.svg', categoria_slug:'xbox' },
];

const BLOG_RESPALDO = [
  { id:1, titulo:'Nova Ronin: el shooter que redefine el género este año', categoria:'Reseña', extracto:'Analizamos a fondo el título más comentado del trimestre: combate táctico, narrativa ramificada y un apartado técnico que exige hardware de última generación.', contenido:'Nova Ronin combina un sistema de coberturas dinámico con decisiones narrativas que alteran el tercio final de la campaña. El rendimiento en PS5 y Series X se mantiene estable en 60 fps con ray tracing activado, mientras que la versión de PC ofrece soporte nativo para ultrawide. El multijugador competitivo, aunque secundario, suma valor de rejugabilidad. Veredicto: sobresaliente en campaña, correcto en multijugador.', imagen:'assets/img/article-resena.svg', fecha_publicacion:'2026-07-08' },
  { id:2, titulo:'Primeras impresiones de Chrono Break: mundo abierto y viajes en el tiempo', categoria:'Avance', extracto:'Tuvimos acceso anticipado a dos horas de gameplay. Te contamos qué esperar del sistema de líneas temporales y su fecha de lanzamiento confirmada.', contenido:'El estudio confirmó que Chrono Break llegará a PS5, Xbox Series X|S y PC en el último trimestre del año. El sistema de "bifurcaciones" permite que decisiones tomadas en una época alteren directamente el escenario de otra, un mecanismo que hasta ahora se siente fresco y bien pulido. La demo mostrada no presentó caídas de frames, aunque el estudio aclaró que sigue en optimización.', imagen:'assets/img/article-avance.svg', fecha_publicacion:'2026-07-03' },
  { id:3, titulo:'Summer Game Fest 2026: los cinco anuncios más importantes', categoria:'Evento', extracto:'Desde nuevas entregas de sagas clásicas hasta sorpresas de estudios independientes: un resumen de lo más relevante del evento.', contenido:'Entre los anuncios destacó el regreso de una franquicia de rol táctico ausente desde hace ocho años, además de una oleada de estudios independientes mexicanos presentando proyectos con apoyo de publishers internacionales. También se confirmaron fechas de lanzamiento para dos de los títulos más esperados del próximo año, ambos con versión física confirmada para Latinoamérica.', imagen:'assets/img/article-evento.svg', fecha_publicacion:'2026-06-28' },
  { id:4, titulo:'¿Qué consola elegir en 2026 según tu presupuesto?', categoria:'Guía de compra', extracto:'Comparamos PS5, Xbox Series X|S, Switch OLED y opciones retro para cada perfil de comprador: casual, hardcore, regalo y estudiante.', contenido:'Si tu prioridad es exclusivos narrativos, PS5 sigue siendo la opción más sólida. Para quienes buscan retrocompatibilidad y Game Pass, Xbox Series S ofrece la mejor relación precio-beneficio de la generación. Si el uso es mixto entre sala y viajes, Switch OLED continúa siendo insustituible. Para presupuestos ajustados, un handheld retro con emulación cubre cientos de clásicos por una fracción del costo.', imagen:'assets/img/article-guia.svg', fecha_publicacion:'2026-06-20' },
  { id:5, titulo:'Así construye la comunidad NexPlay sus wikis colaborativas', categoria:'Comunidad', extracto:'Insignias, créditos y moderación por pares: el sistema gamificado que convierte a los jugadores en editores.', contenido:'Cada contribución verificada otorga créditos canjeables dentro de la tienda y suma progreso hacia insignias de perfil. La moderación combina revisión comunitaria con validadores de confianza, un modelo que ha reducido el vandalismo de contenido y mantenido actualizadas las guías de los títulos más jugados del catálogo.', imagen:'assets/img/gallery-wiki.svg', fecha_publicacion:'2026-06-14' },
  { id:6, titulo:'Torneo NexPlay de junio: así se vivió la gran final', categoria:'Esports', extracto:'Ocho equipos, un premio en crédito de tienda y una final que se definió en el último round.', contenido:'La final reunió a los dos equipos con mejor récord de la temporada regular en un formato al mejor de cinco. El equipo ganador recibió crédito de tienda canjeable en cualquier categoría del catálogo, además de una insignia exclusiva de "Campeón de Temporada" visible en su perfil de comunidad.', imagen:'assets/img/gallery-torneo.svg', fecha_publicacion:'2026-06-09' },
];

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

  /* ==================================================
     Módulo: Tienda — carga desde la API + filtros/orden
     ================================================== */
  const grid = document.getElementById('product-grid');
  const resultsCount = document.getElementById('results-count');
  const emptyState = document.getElementById('empty-state');
  const dataSourceTag = document.getElementById('data-source-tag');

  function money(n) {
    return '$' + Number(n).toLocaleString('es-MX', { minimumFractionDigits: 0 });
  }

  function stars(rating) {
    const r = Math.round(Number(rating));
    return '★★★★★☆☆☆☆☆'.slice(5 - r, 10 - r);
  }

  function productoCardHTML(p) {
    const precioHTML = p.precio_anterior
      ? `<span class="old">${money(p.precio_anterior)}</span>${money(p.precio)}`
      : money(p.precio);
    return `
      <div class="card" data-product data-name="${p.nombre}" data-platform="${p.categoria_slug}" data-price="${p.precio}" data-rating="${p.calificacion}">
        <div class="media"><img src="${p.imagen}" alt="${p.nombre}" loading="lazy"></div>
        <div class="body">
          <span class="tag">${p.tipo}</span>
          <h3>${p.nombre}</h3>
          <p class="desc">${p.descripcion}</p>
          <div class="meta"><span class="price">${precioHTML}</span><span class="stars">${stars(p.calificacion)}</span></div>
        </div>
      </div>`;
  }

  function renderProductos(productos) {
    if (!grid) return;
    grid.innerHTML = productos.length
      ? productos.map(productoCardHTML).join('')
      : '';
    applyFilters();
  }

  let activePlatform = 'todas';

  function applyFilters() {
    if (!grid) return;
    const cards = grid.querySelectorAll('[data-product]');
    const searchInput = document.getElementById('product-search');
    const term = (searchInput?.value || '').trim().toLowerCase();
    let visible = 0;

    cards.forEach(card => {
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
    const sortSelect = document.getElementById('sort-select');
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

  const searchInput = document.getElementById('product-search');
  const sortSelect = document.getElementById('sort-select');
  if (searchInput) searchInput.addEventListener('input', applyFilters);
  if (sortSelect) sortSelect.addEventListener('change', () => { applySort(); applyFilters(); });

  document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      activePlatform = chip.dataset.platform;
      applyFilters();
    });
  });

  async function cargarProductos() {
    if (!grid) return; // esta página no tiene tienda
    grid.innerHTML = '<p style="padding:20px; opacity:.7;">Cargando productos…</p>';
    try {
      const res = await fetch(`${API_BASE}/productos.php`, { headers: { Accept: 'application/json' } });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      const productos = (data.productos && data.productos.length) ? data.productos : PRODUCTOS_RESPALDO;
      renderProductos(productos);
      if (dataSourceTag) {
        dataSourceTag.textContent = data.fuente === 'db'
          ? 'Datos en vivo desde la base de datos'
          : 'La base de datos no tiene productos aún — mostrando catálogo de respaldo';
      }
    } catch (err) {
      console.warn('No se pudo conectar con la API, usando datos de respaldo.', err);
      renderProductos(PRODUCTOS_RESPALDO);
      if (dataSourceTag) {
        dataSourceTag.textContent = 'Sin conexión al servidor/base de datos — mostrando catálogo de respaldo';
      }
    }
  }

  cargarProductos();

  /* ==================================================
     Módulo: Blog — carga desde la API + "leer más"
     ================================================== */
  const articleList = document.getElementById('article-list');
  const blogSourceTag = document.getElementById('blog-source-tag');

  const BADGE_CLASS = {
    'Reseña': 'purple',
    'Guía de compra': 'purple',
    'Avance': 'cyan',
    'Comunidad': 'cyan',
    'Evento': '',
    'Esports': '',
  };

  function formatFecha(fechaISO) {
    const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    const [y, m, d] = fechaISO.split('-').map(Number);
    return `${d} ${meses[m - 1]} ${y}`;
  }

  function articleCardHTML(post) {
    const badgeClass = BADGE_CLASS[post.categoria] ?? '';
    return `
      <article class="article-card">
        <div class="media"><img src="${post.imagen}" alt="${post.titulo}" loading="lazy"></div>
        <div class="body">
          <span class="badge ${badgeClass}">${post.categoria}</span>
          <h3>${post.titulo}</h3>
          <p class="excerpt">${post.extracto}</p>
          <div class="full"><p>${post.contenido}</p></div>
          <div class="row-meta">
            <span class="date">${formatFecha(post.fecha_publicacion)}</span>
            <button class="read-more">Leer más →</button>
          </div>
        </div>
      </article>`;
  }

  function renderBlog(posts) {
    if (!articleList) return;
    articleList.innerHTML = posts.length ? posts.map(articleCardHTML).join('') : '<p>No hay artículos disponibles.</p>';
  }

  // Delegación de eventos: funciona aunque las tarjetas se generen dinámicamente
  if (articleList) {
    articleList.addEventListener('click', (e) => {
      const btn = e.target.closest('.read-more');
      if (!btn) return;
      const full = btn.closest('.article-card').querySelector('.full');
      const open = full.classList.toggle('open');
      btn.textContent = open ? 'Leer menos ←' : 'Leer más →';
    });
  }

  async function cargarBlog() {
    if (!articleList) return; // esta página no tiene blog
    articleList.innerHTML = '<p style="padding:20px; opacity:.7;">Cargando artículos…</p>';
    try {
      const res = await fetch(`${API_BASE}/blog.php`, { headers: { Accept: 'application/json' } });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      const posts = (data.posts && data.posts.length) ? data.posts : BLOG_RESPALDO;
      renderBlog(posts);
      if (blogSourceTag) {
        blogSourceTag.textContent = data.fuente === 'db'
          ? 'Datos en vivo desde la base de datos'
          : 'La base de datos no tiene artículos aún — mostrando contenido de respaldo';
      }
    } catch (err) {
      console.warn('No se pudo conectar con la API, usando datos de respaldo.', err);
      renderBlog(BLOG_RESPALDO);
      if (blogSourceTag) {
        blogSourceTag.textContent = 'Sin conexión al servidor/base de datos — mostrando contenido de respaldo';
      }
    }
  }

  cargarBlog();

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

      if (valid) {
        form.style.display = 'none';
        if (successPanel) successPanel.classList.add('show');
      }
    });

    document.getElementById('form-reset-btn')?.addEventListener('click', () => {
      form.reset();
      form.style.display = '';
      successPanel?.classList.remove('show');
      document.querySelectorAll('.form-row').forEach(r => r.classList.remove('error'));
    });
  }

});
