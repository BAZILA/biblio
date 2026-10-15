/* Administration : petits comportements sans dépendance. */
(function () {
  'use strict';

  // Menu latéral sur mobile
  var burger = document.querySelector('.adm-burger');
  if (burger) {
    burger.addEventListener('click', function () {
      document.querySelector('.adm-nav').classList.toggle('ouvert');
    });
  }

  // Confirmation avant les actions destructrices : <form data-confirmer="Message ?">
  document.addEventListener('submit', function (ev) {
    var msg = ev.target.getAttribute('data-confirmer');
    if (msg && !window.confirm(msg)) { ev.preventDefault(); }
  });

  // Adresse (slug) générée à partir du titre tant qu'elle n'a pas été modifiée à la main
  function slugifier(t) {
    return t.normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[’']/g, '-')
      .replace(/œ/gi, 'oe').replace(/æ/gi, 'ae').toLowerCase()
      .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }
  document.querySelectorAll('input[data-slug-de]').forEach(function (slug) {
    var source = document.querySelector('[name="' + slug.getAttribute('data-slug-de') + '"]');
    if (!source) { return; }
    var manuel = slug.value !== '';
    slug.addEventListener('input', function () { manuel = slug.value !== ''; });
    source.addEventListener('input', function () {
      if (!manuel) { slug.value = (slug.getAttribute('data-prefixe') || '') + slugifier(source.value); }
    });
  });

  // Aperçu d'une image choisie avant envoi
  document.querySelectorAll('input[type=file][data-apercu]').forEach(function (input) {
    input.addEventListener('change', function () {
      var img = document.getElementById(input.getAttribute('data-apercu'));
      if (img && input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
      }
    });
  });

  // Choix d'un lecteur par recherche (guichet de prêt)
  document.querySelectorAll('.choix-lecteur').forEach(function (bloc) {
    var saisie = bloc.querySelector('input[type=search]');
    var cache = bloc.querySelector('input[type=hidden]');
    var liste = bloc.querySelector('.suggestions');
    var choisi = bloc.parentNode.querySelector('.lecteur-choisi');
    var minuterie;

    function choisir(l) {
      cache.value = l.id;
      saisie.value = l.nom;
      liste.classList.remove('ouvert');
      if (choisi) {
        choisi.innerHTML = '';
        var titre = document.createElement('strong');
        titre.textContent = l.nom;
        choisi.appendChild(titre);
        var detail = document.createElement('div');
        detail.className = 'doux';
        detail.textContent = l.detail;
        choisi.appendChild(detail);
        choisi.classList.add('ouvert');
      }
      var suivant = document.querySelector(bloc.getAttribute('data-focus-ensuite'));
      if (suivant) { suivant.focus(); }
    }

    saisie.addEventListener('input', function () {
      cache.value = '';
      if (choisi) { choisi.classList.remove('ouvert'); }
      clearTimeout(minuterie);
      var q = saisie.value.trim();
      if (q.length < 2) { liste.classList.remove('ouvert'); return; }
      minuterie = setTimeout(function () {
        fetch(bloc.getAttribute('data-url') + '?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (lecteurs) {
            liste.innerHTML = '';
            if (!lecteurs.length) {
              var vide = document.createElement('div');
              vide.className = 'vide';
              vide.textContent = 'Aucun lecteur actif trouvé.';
              liste.appendChild(vide);
            }
            lecteurs.forEach(function (l) {
              var b = document.createElement('button');
              b.type = 'button';
              b.innerHTML = '<strong></strong><br><small></small>';
              b.querySelector('strong').textContent = l.nom;
              b.querySelector('small').textContent = l.detail;
              b.addEventListener('click', function () { choisir(l); });
              liste.appendChild(b);
            });
            liste.classList.add('ouvert');
          });
      }, 200);
    });

    document.addEventListener('click', function (ev) {
      if (!bloc.contains(ev.target)) { liste.classList.remove('ouvert'); }
    });
  });
})();
