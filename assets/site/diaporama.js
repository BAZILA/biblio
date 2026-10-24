/*
 * Diaporama de l'accueil (views/site/_diaporama.php) : défilement automatique avec barre de progression,
 * flèches, points, clavier, glissement au doigt, pause au survol et bouton pause, vidéo jouée seulement
 * sur la diapositive visible. Respecte « réduire les animations » du système.
 */
(function () {
  'use strict';
  var racine = document.querySelector('.vit-diapo');
  if (!racine) { return; }
  var slides = racine.querySelectorAll('.vit-diapo-slide');
  var points = racine.querySelectorAll('.vit-diapo-point');
  var compteur = racine.querySelector('.vit-diapo-compteur strong');
  var boutonPause = racine.querySelector('.vit-diapo-pause');
  var duree = parseInt(racine.getAttribute('data-duree'), 10) || 7000;
  var calme = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var courant = 0, minuterie = null, enPause = false, survol = false, debut = 0, reste = duree;

  racine.style.setProperty('--duree', duree + 'ms');
  racine.classList.add('est-pret');
  if (calme) { racine.classList.add('sans-mouvement'); }

  function videos(slide, jouer) {
    slide.querySelectorAll('video').forEach(function (v) {
      if (jouer) {
        v.preload = 'auto';
        var p = v.play();
        if (p && p.catch) { p.catch(function () {}); }
      } else {
        v.pause();
      }
    });
  }

  function montrer(n, sens) {
    if (n === courant || !slides.length) { return; }
    var ancien = slides[courant];
    courant = (n + slides.length) % slides.length;
    var nouveau = slides[courant];
    racine.setAttribute('data-sens', sens || 'suivant');
    ancien.classList.remove('est-active');
    ancien.classList.add('est-sortante');
    ancien.setAttribute('aria-hidden', 'true');
    setTimeout(function () { ancien.classList.remove('est-sortante'); videos(ancien, false); }, 1200);
    // Relance les animations de la diapositive entrante
    nouveau.classList.remove('est-active');
    void nouveau.offsetWidth;
    nouveau.classList.add('est-active');
    nouveau.removeAttribute('aria-hidden');
    slides.forEach(function (s, i) {
      s.querySelectorAll('a').forEach(function (a) { if (i === courant) { a.removeAttribute('tabindex'); } else { a.setAttribute('tabindex', '-1'); } });
    });
    videos(nouveau, true);
    points.forEach(function (p, i) {
      p.classList.toggle('est-active', i === courant);
      p.setAttribute('aria-selected', i === courant ? 'true' : 'false');
    });
    if (compteur) { compteur.textContent = (courant < 9 ? '0' : '') + (courant + 1); }
    demarrer();
  }

  // Nouvelle diapositive : la barre de progression repart de zéro
  function demarrer() {
    clearTimeout(minuterie);
    minuterie = null;
    reste = duree;
    racine.classList.remove('progression');
    void racine.offsetWidth;
    if (slides.length > 1) { racine.classList.add('progression'); }
    relancer();
  }

  // Reprend le défilement là où il s'était arrêté (la barre continue sans repartir)
  function relancer() {
    clearTimeout(minuterie);
    minuterie = null;
    if (slides.length < 2 || enPause || survol) {
      racine.classList.add('est-en-pause');
      return;
    }
    racine.classList.remove('est-en-pause');
    debut = Date.now();
    minuterie = setTimeout(function () { montrer(courant + 1, 'suivant'); }, reste);
  }

  function suspendre() {
    if (minuterie) { reste = Math.max(300, reste - (Date.now() - debut)); }
    clearTimeout(minuterie);
    minuterie = null;
    racine.classList.add('est-en-pause');
  }

  var prec = racine.querySelector('.vit-diapo-prec');
  var suiv = racine.querySelector('.vit-diapo-suiv');
  if (prec) { prec.addEventListener('click', function () { montrer(courant - 1, 'precedent'); }); }
  if (suiv) { suiv.addEventListener('click', function () { montrer(courant + 1, 'suivant'); }); }
  points.forEach(function (p, i) { p.addEventListener('click', function () { montrer(i, i < courant ? 'precedent' : 'suivant'); }); });
  if (boutonPause) {
    boutonPause.addEventListener('click', function () {
      enPause = !enPause;
      boutonPause.setAttribute('aria-pressed', enPause ? 'true' : 'false');
      boutonPause.setAttribute('aria-label', enPause ? 'Reprendre le défilement' : 'Mettre en pause le défilement');
      boutonPause.innerHTML = '<i class="fa ' + (enPause ? 'fa-play' : 'fa-pause') + '" aria-hidden="true"></i>';
      if (enPause) { suspendre(); } else { relancer(); }
    });
  }
  racine.addEventListener('mouseenter', function () { survol = true; suspendre(); });
  racine.addEventListener('mouseleave', function () { survol = false; if (!enPause) { relancer(); } });
  racine.addEventListener('focusin', function () { survol = true; suspendre(); });
  racine.addEventListener('focusout', function (ev) { if (!racine.contains(ev.relatedTarget)) { survol = false; if (!enPause) { relancer(); } } });
  racine.addEventListener('keydown', function (ev) {
    if (ev.key === 'ArrowLeft') { montrer(courant - 1, 'precedent'); }
    if (ev.key === 'ArrowRight') { montrer(courant + 1, 'suivant'); }
  });
  // Glissement au doigt
  var x0 = null;
  racine.addEventListener('touchstart', function (ev) { x0 = ev.touches[0].clientX; }, { passive: true });
  racine.addEventListener('touchend', function (ev) {
    if (x0 === null) { return; }
    var dx = ev.changedTouches[0].clientX - x0;
    if (Math.abs(dx) > 45) { montrer(courant + (dx < 0 ? 1 : -1), dx < 0 ? 'suivant' : 'precedent'); }
    x0 = null;
  }, { passive: true });
  // Onglet caché : on arrête (et les vidéos aussi)
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) { suspendre(); videos(slides[courant], false); } else { videos(slides[courant], true); if (!enPause && !survol) { relancer(); } }
  });

  videos(slides[0], true);
  demarrer();
})();
