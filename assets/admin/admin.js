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
      var nom = document.querySelector('.nom-fichier[data-pour="' + input.id + '"]');
      if (nom) { nom.textContent = input.files && input.files[0] ? 'Image choisie : ' + input.files[0].name : ''; }
      var etiquette = img && img.parentNode.querySelector('.etiquette-defaut');
      if (etiquette) { etiquette.remove(); }
    });
  });

  // Petit utilitaire : interroge une adresse JSON de l'admin
  function chercherJson(url, q) {
    return fetch(url + (url.indexOf('?') < 0 ? '?' : '&') + 'q=' + encodeURIComponent(q), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); });
  }

  // Auteurs d'un ouvrage : puces + recherche dans l'index des auteurs (le textarea caché reste la valeur envoyée)
  document.querySelectorAll('.champ-auteurs').forEach(function (bloc) {
    var zone = bloc.querySelector('textarea');
    var noms = zone.value.split(/\r?\n/).map(function (n) { return n.trim(); }).filter(Boolean);
    var connus = {};
    zone.style.display = 'none';

    var puces = document.createElement('div');
    puces.className = 'puces';
    var saisie = document.createElement('input');
    saisie.type = 'text';
    saisie.id = 'auteurs-saisie';
    saisie.placeholder = 'Rechercher ou ajouter un auteur…';
    saisie.autocomplete = 'off';
    var liste = document.createElement('div');
    liste.className = 'suggestions';
    bloc.appendChild(puces);
    var minuterie, propositions = [], actif = -1;

    function synchroniser() { zone.value = noms.join('\n'); }
    function dessiner() {
      puces.innerHTML = '';
      noms.forEach(function (nom, i) {
        var p = document.createElement('span');
        p.className = 'puce' + (connus[nom] === false ? ' nouveau' : '');
        p.title = connus[nom] === false ? 'Nouvel auteur : il sera ajouté à l’index' : '';
        p.appendChild(document.createTextNode(nom));
        var x = document.createElement('button');
        x.type = 'button';
        x.setAttribute('aria-label', 'Retirer ' + nom);
        x.textContent = '×';
        x.addEventListener('click', function () { noms.splice(i, 1); synchroniser(); dessiner(); saisie.focus(); });
        p.appendChild(x);
        puces.appendChild(p);
      });
      puces.appendChild(saisie);
      puces.appendChild(liste);
    }
    function ajouter(nom, existe) {
      nom = nom.trim();
      if (!nom) { return; }
      if (noms.some(function (n) { return n.toLowerCase() === nom.toLowerCase(); })) { return; }
      connus[nom] = existe;
      noms.push(nom);
      saisie.value = '';
      liste.classList.remove('ouvert');
      synchroniser();
      dessiner();
      saisie.focus();
    }
    function afficher(q, resultats) {
      liste.innerHTML = '';
      propositions = [];
      resultats.forEach(function (a) {
        if (noms.indexOf(a.nom) < 0) { propositions.push({ nom: a.nom, existe: true, detail: a.nb + ' ouvrage(s)' }); }
      });
      var exact = resultats.some(function (a) { return a.nom.toLowerCase() === q.toLowerCase(); });
      if (!exact) { propositions.push({ nom: q, existe: false, detail: 'nouvel auteur' }); }
      propositions.forEach(function (p, i) {
        var b = document.createElement('button');
        b.type = 'button';
        if (!p.existe) { b.className = 'proposition-nouveau'; }
        b.innerHTML = '<strong></strong> <small></small>';
        b.querySelector('strong').textContent = p.existe ? p.nom : '+ Ajouter « ' + p.nom + ' »';
        b.querySelector('small').textContent = p.detail;
        b.addEventListener('mousedown', function (ev) { ev.preventDefault(); ajouter(p.nom, p.existe); });
        liste.appendChild(b);
      });
      actif = propositions.length ? 0 : -1;
      surligner();
      liste.classList.toggle('ouvert', propositions.length > 0);
    }
    function surligner() {
      Array.prototype.forEach.call(liste.children, function (el, i) { el.classList.toggle('actif', i === actif); });
    }

    saisie.addEventListener('input', function () {
      clearTimeout(minuterie);
      var q = saisie.value.trim();
      if (q.length < 2) { liste.classList.remove('ouvert'); return; }
      minuterie = setTimeout(function () { chercherJson(bloc.getAttribute('data-url'), q).then(function (r) { afficher(q, r); }); }, 180);
    });
    saisie.addEventListener('keydown', function (ev) {
      if (ev.key === 'ArrowDown' || ev.key === 'ArrowUp') {
        if (!propositions.length) { return; }
        ev.preventDefault();
        actif = (actif + (ev.key === 'ArrowDown' ? 1 : propositions.length - 1)) % propositions.length;
        surligner();
      } else if (ev.key === 'Enter') {
        ev.preventDefault();
        if (actif >= 0 && propositions[actif] && liste.classList.contains('ouvert')) { ajouter(propositions[actif].nom, propositions[actif].existe); }
        else if (saisie.value.trim()) { ajouter(saisie.value, undefined); }
      } else if (ev.key === 'Backspace' && saisie.value === '' && noms.length) {
        noms.pop(); synchroniser(); dessiner();
      } else if (ev.key === 'Escape') {
        liste.classList.remove('ouvert');
      }
    });
    saisie.addEventListener('blur', function () { setTimeout(function () { liste.classList.remove('ouvert'); }, 150); });
    puces.addEventListener('click', function (ev) { if (ev.target === puces) { saisie.focus(); } });
    dessiner();
  });

  // Choix d'une icône dans une grille (domaines) : le champ texte reste la valeur envoyée
  document.querySelectorAll('.champ-icone').forEach(function (bloc) {
    var champ = bloc.querySelector('input[type=text]');
    var apercu = bloc.querySelector('.tuile .fa');
    var filtre = bloc.querySelector('input[type=search]');
    var boutons = bloc.querySelectorAll('.choix-icones button');
    function choisir(nom) {
      champ.value = nom;
      apercu.className = 'fa ' + nom;
      boutons.forEach(function (b) { b.classList.toggle('choisi', b.getAttribute('data-icone') === nom); });
    }
    boutons.forEach(function (b) {
      b.addEventListener('click', function () { choisir(b.getAttribute('data-icone')); });
    });
    champ.addEventListener('input', function () { apercu.className = 'fa ' + champ.value.trim(); });
    if (filtre) {
      filtre.addEventListener('input', function () {
        var q = filtre.value.trim().toLowerCase();
        boutons.forEach(function (b) { b.style.display = !q || b.getAttribute('data-mots').indexOf(q) >= 0 ? '' : 'none'; });
      });
    }
  });

  // Faculté / département : visibles seulement pour un étudiant
  document.querySelectorAll('select[data-montre-etudiant]').forEach(function (choix) {
    var formulaire = choix.closest('form');
    function maj() {
      formulaire.querySelectorAll('.bloc-etudiant').forEach(function (b) { b.style.display = choix.value === 'etudiant' ? '' : 'none'; });
    }
    choix.addEventListener('change', maj);
    maj();
  });

  // Listes de sélection : champ de filtre au-dessus (data-liste="#id-du-select")
  function sansAccents(t) { return t.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); }
  document.querySelectorAll('input.filtre-liste').forEach(function (filtre) {
    var liste = document.querySelector(filtre.getAttribute('data-liste'));
    if (!liste) { return; }
    // Pendant le filtrage, la liste se déplie et ne montre que les correspondances ; un clic choisit
    filtre.addEventListener('input', function () {
      var mots = sansAccents(filtre.value.trim()).split(/\s+/).filter(Boolean);
      var visibles = 0;
      Array.prototype.forEach.call(liste.options, function (o) {
        if (!o.value) { o.hidden = mots.length > 0; return; }
        var texte = sansAccents(o.textContent + ' ' + (o.getAttribute('data-recherche') || ''));
        o.hidden = !mots.every(function (m) { return texte.indexOf(m) >= 0; });
        if (!o.hidden) { visibles++; }
      });
      liste.size = mots.length ? Math.min(8, Math.max(2, visibles)) : 0;
      liste.classList.toggle('liste-depliee', mots.length > 0);
    });
    filtre.addEventListener('keydown', function (ev) { if (ev.key === 'Enter' || ev.key === 'ArrowDown') { ev.preventDefault(); liste.focus(); } });
    liste.addEventListener('change', function () {
      liste.size = 0;
      liste.classList.remove('liste-depliee');
      Array.prototype.forEach.call(liste.options, function (o) { o.hidden = false; });
      filtre.value = '';
    });
  });

  // Guichet : exemplaires de l'ouvrage choisi (ceux mis de côté pour le lecteur en premier)
  var choixOuvrage = document.getElementById('choix-ouvrage');
  var choixExemplaire = document.getElementById('choix-exemplaire');
  var choixLecteur = document.getElementById('choix-lecteur');
  function chargerExemplaires() {
    if (!choixOuvrage || !choixExemplaire) { return; }
    choixExemplaire.length = 1; // garde « Automatique »
    if (!choixOuvrage.value) { return; }
    var url = choixOuvrage.getAttribute('data-url-exemplaires') + '?ouvrage=' + choixOuvrage.value + '&lecteur=' + (choixLecteur ? choixLecteur.value : '');
    fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (liste) {
      var etats = { neuf: 'neuf', bon: 'bon état', use: 'usé', abime: 'abîmé' };
      liste.forEach(function (x) {
        var o = document.createElement('option');
        o.value = x.code;
        o.textContent = x.code + ' — ' + (etats[x.etat] || x.etat) + (Number(x.pour_lui) ? ' (mis de côté pour ce lecteur)' : '');
        choixExemplaire.appendChild(o);
      });
      if (!liste.length) {
        var o = document.createElement('option');
        o.disabled = true;
        o.textContent = 'Aucun exemplaire disponible';
        choixExemplaire.appendChild(o);
      }
    });
  }
  if (choixOuvrage) { choixOuvrage.addEventListener('change', chargerExemplaires); chargerExemplaires(); }
  if (choixLecteur) {
    // Changer de lecteur recharge la page : ses prêts, réservations et ouvrages mis de côté s'affichent
    choixLecteur.addEventListener('change', function () {
      if (!choixLecteur.value) { return; }
      var u = new URL(window.location.href);
      u.searchParams.set('lecteur', choixLecteur.value);
      if (choixOuvrage && choixOuvrage.value) { u.searchParams.set('ouvrage', choixOuvrage.value); }
      window.location.href = u.toString();
    });
  }

  // Masque un bloc et neutralise ses champs (un champ masqué n'est ni envoyé ni exigé)
  function afficher(bloc, oui) {
    bloc.hidden = !oui;
    bloc.querySelectorAll('input, select, textarea').forEach(function (c) { c.disabled = !oui; });
    if (bloc.matches('input, select, textarea')) { bloc.disabled = !oui; }
  }

  // Constat de retour : la note devient obligatoire pour « abîmé » ou « perdu »
  function majConstat(fs) {
    var choix = fs.querySelector('input[name="etat_retour"]:checked');
    var etat = choix ? choix.value : 'conforme';
    fs.querySelectorAll('[data-si-etat]').forEach(function (b) {
      afficher(b, b.getAttribute('data-si-etat').split(' ').indexOf(etat) !== -1);
    });
    var note = fs.querySelector('textarea[name="note_retour"]');
    if (note) { note.required = etat === 'abime' || etat === 'perdu'; }
  }
  document.querySelectorAll('[data-constat]').forEach(function (fs) {
    fs.addEventListener('change', function () { majConstat(fs); });
    majConstat(fs);
  });

  // Fenêtres (views/admin/_modales.php) : <button data-modale="#id" data-valeurs='{"clé": "valeur"}'>
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-modale]');
    if (!b) { return; }
    var d = document.querySelector(b.getAttribute('data-modale'));
    if (!d || typeof d.showModal !== 'function') { return; }
    var v = JSON.parse(b.getAttribute('data-valeurs') || '{}');
    var form = d.querySelector('form');
    form.reset();
    if (form.hasAttribute('data-remplir-action') && v.action) { form.action = v.action; }
    d.querySelectorAll('[data-remplir]').forEach(function (el) {
      var val = v[el.getAttribute('data-remplir')];
      val = val === undefined || val === null ? '' : String(val);
      if (el.matches('input, select, textarea')) { el.value = val; } else { el.textContent = val; }
    });
    // [data-masquer-si-vide] : masqué si son propre texte est vide, ou si la valeur nommée est vide
    d.querySelectorAll('[data-masquer-si-vide]').forEach(function (el) {
      var cle = el.getAttribute('data-masquer-si-vide');
      afficher(el, cle ? !!v[cle] : el.textContent.trim() !== '');
    });
    d.querySelectorAll('[data-constat]').forEach(majConstat);
    d.showModal();
    var premier = d.querySelector('.modale-corps input:not([type=hidden]):not([disabled]), .modale-corps select, .modale-corps textarea:not([disabled])');
    if (premier) { premier.focus(); }
  });
  document.addEventListener('click', function (ev) {
    if (ev.target.closest('[data-fermer]')) { ev.target.closest('dialog').close(); return; }
    // Clic sur le fond assombri : on ferme
    if (ev.target.tagName === 'DIALOG' && ev.target.classList.contains('modale')) { ev.target.close(); }
  });
  // Bouton qui exige un champ précis (ex. « Refuser » exige un message, « Accepter » une date)
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('button[data-exige]');
    if (!b) { return; }
    var form = b.form;
    form.querySelectorAll('[data-exige-actif]').forEach(function (c) { c.required = false; c.removeAttribute('data-exige-actif'); });
    var champ = form.querySelector(b.getAttribute('data-exige'));
    if (champ) { champ.required = true; champ.setAttribute('data-exige-actif', ''); }
  });

  // Grille des permissions d'un rôle (views/admin/role_form.php)
  var formRole = document.getElementById('form-role');
  if (formRole) {
    var cases = function (sel) { return Array.prototype.slice.call(formRole.querySelectorAll(sel)); };
    var droit = function (module, action) { return formRole.querySelector('input[data-module="' + module + '"][data-action="' + action + '"]'); };
    var cocher = function (c, oui) { if (c && !c.disabled) { c.checked = oui; } };
    var majLignes = function () {
      cases('input[data-tout-ligne]').forEach(function (t) {
        var ligne = cases('input[data-module="' + t.getAttribute('data-tout-ligne') + '"]');
        var n = ligne.filter(function (c) { return c.checked; }).length;
        t.checked = n === ligne.length && n > 0;
        t.indeterminate = n > 0 && n < ligne.length;
      });
      var compteur = document.getElementById('nb-droits');
      if (compteur) { compteur.textContent = cases('input[name="permissions[]"]:checked').length; }
    };
    // Une action implique « Voir » de sa rubrique ; les exemplaires se gèrent depuis la fiche ouvrage
    var dependances = function (c) {
      var m = c.getAttribute('data-module'), a = c.getAttribute('data-action');
      if (c.checked && a !== 'voir') { cocher(droit(m, 'voir'), true); }
      if (c.checked && m === 'exemplaires') { cocher(droit('ouvrages', 'voir'), true); }
      if (!c.checked && a === 'voir') {
        cases('input[data-module="' + m + '"]').forEach(function (x) { cocher(x, false); });
        if (m === 'ouvrages') { cases('input[data-module="exemplaires"]').forEach(function (x) { cocher(x, false); }); }
      }
    };
    formRole.addEventListener('change', function (ev) {
      var c = ev.target;
      if (c.hasAttribute('data-tout-ligne')) {
        cases('input[data-module="' + c.getAttribute('data-tout-ligne') + '"]').forEach(function (x) { cocher(x, c.checked); dependances(x); });
      } else if (c.hasAttribute('data-module')) {
        dependances(c);
      }
      majLignes();
    });
    formRole.addEventListener('click', function (ev) {
      var b = ev.target.closest('[data-droits]');
      if (!b) { return; }
      var mode = b.getAttribute('data-droits');
      cases('input[name="permissions[]"]').forEach(function (c) {
        cocher(c, mode === 'tout' || (mode === 'lecture' && c.getAttribute('data-action') === 'voir'));
      });
      majLignes();
    });
    majLignes();
  }

  // Page Système : masquer « Voir » masque toute la rubrique
  var formMasque = document.getElementById('form-masquage');
  if (formMasque) {
    var ligneDe = function (m) { return Array.prototype.slice.call(formMasque.querySelectorAll('input[data-module="' + m + '"]')); };
    var majMasque = function () {
      Array.prototype.forEach.call(formMasque.querySelectorAll('input[data-tout-ligne]'), function (t) {
        var l = ligneDe(t.getAttribute('data-tout-ligne'));
        var n = l.filter(function (c) { return c.checked; }).length;
        t.checked = n === l.length;
        t.indeterminate = n > 0 && n < l.length;
      });
      document.getElementById('nb-masquees').textContent = formMasque.querySelectorAll('input[name="masquees[]"]:checked').length;
    };
    formMasque.addEventListener('change', function (ev) {
      var c = ev.target;
      if (c.hasAttribute('data-tout-ligne')) {
        ligneDe(c.getAttribute('data-tout-ligne')).forEach(function (x) { x.checked = c.checked; });
      } else if (c.hasAttribute('data-module')) {
        var voir = formMasque.querySelector('input[data-module="' + c.getAttribute('data-module') + '"][data-action="voir"]');
        if (c.getAttribute('data-action') === 'voir' && c.checked) {
          ligneDe(c.getAttribute('data-module')).forEach(function (x) { x.checked = true; });
        } else if (!c.checked && voir && voir !== c) {
          voir.checked = false; // une action visible suppose que la rubrique l'est
        }
      }
      majMasque();
    });
    majMasque();
  }
})();
