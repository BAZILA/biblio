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

  // Champ à valeur unique avec suggestions sous le champ, même présentation que les auteurs
  // (collection, indice Dewey) : une puce pour la valeur choisie, le champ caché reste la valeur envoyée
  document.querySelectorAll('.champ-choix').forEach(function (bloc) {
    var cache = bloc.querySelector('input[type=hidden]');
    var nouveau = bloc.getAttribute('data-nouveau');          // libellé de la proposition « + Ajouter … »
    var mini = parseInt(bloc.getAttribute('data-min') || '2', 10);
    var valeur = cache.value.trim();
    var afficheDetail = cache.hasAttribute('data-detail');    // Dewey : la puce porte « 530 — Physique »
    var detail = cache.getAttribute('data-detail') || '';
    var connue = valeur !== '';                               // valeur déjà en base (puce bleue) ou nouvelle (puce orange)

    var puces = document.createElement('div');
    puces.className = 'puces';
    var saisie = document.createElement('input');
    saisie.type = 'text';
    saisie.id = cache.name + '-saisie';
    saisie.placeholder = bloc.getAttribute('data-placeholder') || 'Rechercher…';
    saisie.autocomplete = 'off';
    var liste = document.createElement('div');
    liste.className = 'suggestions';
    bloc.appendChild(puces);
    var minuterie, propositions = [], actif = -1;

    function dessiner() {
      puces.innerHTML = '';
      if (valeur) {
        var p = document.createElement('span');
        p.className = 'puce' + (connue ? '' : ' nouveau');
        p.title = connue ? '' : 'Sera créé avec l’ouvrage';
        p.appendChild(document.createTextNode(valeur + (afficheDetail && detail ? ' — ' + detail : '')));
        var x = document.createElement('button');
        x.type = 'button';
        x.setAttribute('aria-label', 'Retirer ' + valeur);
        x.textContent = '×';
        x.addEventListener('click', function () { choisir('', '', true); saisie.focus(); });
        p.appendChild(x);
        puces.appendChild(p);
      }
      saisie.placeholder = valeur ? 'Remplacer…' : (bloc.getAttribute('data-placeholder') || 'Rechercher…');
      puces.appendChild(saisie);
      puces.appendChild(liste);
    }
    function choisir(v, d, existe) {
      valeur = v.trim();
      detail = valeur ? (d || '') : '';
      connue = valeur === '' || existe;
      cache.value = valeur;
      saisie.value = '';
      liste.classList.remove('ouvert');
      dessiner();
    }
    function afficher(q, resultats) {
      liste.innerHTML = '';
      propositions = resultats.map(function (r) {
        var d = r.libelle || [r.editeur, r.nb ? r.nb + ' ouvrage(s)' : ''].filter(Boolean).join(' · ');
        return { nom: r.nom, detail: d, existe: true };
      });
      var exact = resultats.some(function (r) { return r.nom.toLowerCase() === q.toLowerCase(); });
      if (nouveau && !exact) { propositions.push({ nom: q, detail: nouveau, existe: false }); }
      propositions.forEach(function (p) {
        var b = document.createElement('button');
        b.type = 'button';
        if (!p.existe) { b.className = 'proposition-nouveau'; }
        b.innerHTML = '<strong></strong> <small></small>';
        b.querySelector('strong').textContent = p.existe ? p.nom : '+ Ajouter « ' + p.nom + ' »';
        b.querySelector('small').textContent = p.detail;
        b.addEventListener('mousedown', function (ev) { ev.preventDefault(); choisir(p.nom, p.existe ? p.detail : '', p.existe); });
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
      if (q.length < mini) { liste.classList.remove('ouvert'); return; }
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
        if (actif >= 0 && propositions[actif] && liste.classList.contains('ouvert')) {
          choisir(propositions[actif].nom, propositions[actif].existe ? propositions[actif].detail : '', propositions[actif].existe);
        } else if (saisie.value.trim()) {
          choisir(saisie.value, '', false);
        }
      } else if (ev.key === 'Backspace' && saisie.value === '' && valeur) {
        choisir('', '', true);
      } else if (ev.key === 'Escape') {
        liste.classList.remove('ouvert');
      }
    });
    // Texte tapé sans rien choisir : on le garde quand même (il sera créé à l'enregistrement)
    saisie.addEventListener('blur', function () {
      setTimeout(function () {
        liste.classList.remove('ouvert');
        if (saisie.value.trim()) { choisir(saisie.value, '', false); }
      }, 150);
    });
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
    function filtrer() {
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
    }
    // Guichet : les étudiants de la liste de l'université sans fiche lecteur sont cherchés sur le serveur
    var groupeEtudiants = liste.querySelector('optgroup[data-etudiants]');
    var urlEtudiants = liste.getAttribute('data-url-etudiants');
    var minuterie = null, derniere = '';
    function chercherEtudiants() {
      var q = filtre.value.trim();
      if (q === derniere) { return; }
      derniere = q;
      if (q.length < 2) { groupeEtudiants.innerHTML = ''; groupeEtudiants.hidden = true; filtrer(); return; }
      fetch(urlEtudiants + '?q=' + encodeURIComponent(q), { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (res) {
        if (q !== filtre.value.trim()) { return; } // une saisie plus récente est en cours
        groupeEtudiants.innerHTML = '';
        res.forEach(function (x) {
          var o = document.createElement('option');
          o.value = x.valeur;
          o.textContent = x.texte;
          o.setAttribute('data-recherche', q); // trouvé par le serveur (nom, prénoms ou n° de carte)
          groupeEtudiants.appendChild(o);
        });
        groupeEtudiants.hidden = !res.length;
        filtrer();
      });
    }
    filtre.addEventListener('input', function () {
      filtrer();
      if (groupeEtudiants && urlEtudiants) {
        clearTimeout(minuterie);
        minuterie = setTimeout(chercherEtudiants, 250);
      }
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
      // Étudiant de la liste sans fiche : la fiche lecteur est créée côté serveur, puis le guichet s'ouvre sur lui
      if (choixLecteur.value.indexOf('etu-') === 0) {
        var f = document.createElement('form');
        f.method = 'post';
        f.action = choixLecteur.getAttribute('data-url-etudiant') + '/' + choixLecteur.value.slice(4);
        [['_csrf', choixLecteur.form.querySelector('input[name="_csrf"]').value], ['ouvrage', choixOuvrage ? choixOuvrage.value : '']].forEach(function (c) {
          var i = document.createElement('input');
          i.type = 'hidden'; i.name = c[0]; i.value = c[1];
          f.appendChild(i);
        });
        document.body.appendChild(f);
        f.submit();
        return;
      }
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

  // Cases à cocher d'une liste : « tout cocher » (data-tout-cocher="nom[]") et boutons actifs seulement si une case est cochée
  document.querySelectorAll('input[data-tout-cocher]').forEach(function (tout) {
    var form = tout.closest('form');
    var nom = tout.getAttribute('data-tout-cocher');
    function cases() { return form.querySelectorAll('input[type=checkbox][name="' + nom + '"]'); }
    function maj() {
      var n = Array.prototype.filter.call(cases(), function (c) { return c.checked; }).length;
      tout.checked = n > 0 && n === cases().length;
      tout.indeterminate = n > 0 && n < cases().length;
      form.querySelectorAll('[data-exige-coche]').forEach(function (b) { b.disabled = n === 0; });
      var info = form.querySelector('[data-compte-coches]');
      if (info) { info.textContent = n ? n + ' étudiant(s) coché(s) :' : 'Cochez des étudiants, puis :'; }
    }
    tout.addEventListener('change', function () { cases().forEach(function (c) { c.checked = tout.checked; }); maj(); });
    form.addEventListener('change', function (ev) { if (ev.target !== tout && ev.target.name === nom) { maj(); } });
    maj();
  });
  // Fiche actualité : bloc « évènement » selon le type, aperçu de l'effet de diapositive
  document.querySelectorAll('input[name="type"]').forEach(function (r) {
    r.addEventListener('change', function () {
      document.querySelectorAll('[data-si-type]').forEach(function (b) { afficher(b, b.getAttribute('data-si-type') === r.value); });
    });
  });
  document.querySelectorAll('[data-si-type][hidden]').forEach(function (b) { afficher(b, false); });
  document.querySelectorAll('select[data-apercu-animation]').forEach(function (choix) {
    var apercu = document.querySelector(choix.getAttribute('data-apercu-animation'));
    function rejouer() {
      apercu.setAttribute('data-animation', choix.value);
      apercu.querySelectorAll('.fond, .texte').forEach(function (el) { el.style.animation = 'none'; void el.offsetWidth; el.style.animation = ''; });
    }
    choix.addEventListener('change', rejouer);
    apercu.addEventListener('click', rejouer);
    var titre = document.querySelector('input[name="titre"]');
    if (titre) { titre.addEventListener('input', function () { apercu.querySelector('strong').textContent = titre.value || 'Titre'; }); }
    var image = document.querySelector('input[type=file][name="image"]');
    if (image) { image.addEventListener('change', function () { if (image.files[0]) { apercu.querySelector('.fond').style.backgroundImage = 'url(' + URL.createObjectURL(image.files[0]) + ')'; rejouer(); } }); }
  });
  // Courbes : réticule et infobulle au survol ou au clavier (flèches), toutes les séries du mois pointé
  document.querySelectorAll('figure[data-courbe]').forEach(function (fig) {
    var d = JSON.parse(fig.getAttribute('data-courbe'));
    var svg = fig.querySelector('svg'), zone = fig.querySelector('.courbe-zone');
    var capture = fig.querySelector('.courbe-capture'), reticule = fig.querySelector('.courbe-reticule');
    var points = fig.querySelectorAll('.courbe-point'), bulle = fig.querySelector('.courbe-bulle');
    var actuel = -1;
    function montrer(i) {
      actuel = Math.max(0, Math.min(d.x.length - 1, i));
      var x = d.x[actuel];
      reticule.setAttribute('x1', x); reticule.setAttribute('x2', x); reticule.setAttribute('visibility', 'visible');
      d.series.forEach(function (s, k) {
        points[k].setAttribute('cx', x); points[k].setAttribute('cy', s.y[actuel]); points[k].setAttribute('visibility', 'visible');
      });
      bulle.textContent = '';
      var m = document.createElement('div'); m.className = 'mois'; m.textContent = d.mois[actuel]; bulle.appendChild(m);
      d.series.forEach(function (s) {
        var l = document.createElement('div'); l.className = 'ligne-serie';
        var cle = document.createElement('i'); cle.style.background = s.couleur;
        var v = document.createElement('strong'); v.textContent = s.valeurs[actuel];
        var n = document.createElement('span'); n.textContent = s.nom;
        l.appendChild(cle); l.appendChild(v); l.appendChild(n); bulle.appendChild(l);
      });
      bulle.hidden = false;
      var pct = x / d.largeur * 100;
      bulle.style.left = Math.max(12, Math.min(88, pct)) + '%';
    }
    function cacher() {
      reticule.setAttribute('visibility', 'hidden');
      points.forEach(function (p) { p.setAttribute('visibility', 'hidden'); });
      bulle.hidden = true;
    }
    function indice(ev) {
      var pt = svg.createSVGPoint(); pt.x = ev.clientX; pt.y = ev.clientY;
      var x = pt.matrixTransform(svg.getScreenCTM().inverse()).x, best = 0;
      d.x.forEach(function (v, i) { if (Math.abs(v - x) < Math.abs(d.x[best] - x)) { best = i; } });
      return best;
    }
    capture.addEventListener('pointermove', function (ev) { montrer(indice(ev)); });
    capture.addEventListener('pointerleave', cacher);
    capture.addEventListener('focus', function () { montrer(actuel < 0 ? d.x.length - 1 : actuel); });
    capture.addEventListener('blur', cacher);
    capture.addEventListener('keydown', function (ev) {
      if (ev.key === 'ArrowLeft') { ev.preventDefault(); montrer(actuel - 1); }
      if (ev.key === 'ArrowRight') { ev.preventDefault(); montrer(actuel + 1); }
    });
  });
  // Rôle (compte Système) : « Tous les droits » grise la grille ; décoché, chaque permission se choisit
  var tousDroits = document.getElementById('role-tous-droits');
  if (tousDroits) {
    var majTousDroits = function () {
      document.querySelectorAll('#form-role input[name="permissions[]"]').forEach(function (c) { c.disabled = tousDroits.checked; });
      var masque = document.getElementById('role-masque');
      if (masque) { masque.disabled = tousDroits.checked; if (tousDroits.checked) { masque.checked = false; } }
    };
    tousDroits.addEventListener('change', majTousDroits);
    majTousDroits();
  }
  // Fiche ouvrage : proposer la cote à partir de l'indice Dewey et du premier auteur (« 005.1 CAR »)
  document.querySelectorAll('[data-cote-proposer]').forEach(function (bouton) {
    var cote = document.querySelector('[data-cote]');
    var indice = document.querySelector('[data-dewey]');
    var auteurs = document.querySelector('.champ-auteurs textarea');
    bouton.addEventListener('click', function () {
      var nom = auteurs ? (auteurs.value.split(/\r?\n/)[0] || '').trim() : '';
      var mots = nom.split(/\s+/).filter(Boolean);
      var cle = (mots[mots.length - 1] || '').normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/[^A-Za-z]/g, '').toUpperCase().slice(0, 3);
      cote.value = [(indice ? indice.value.trim() : ''), cle].filter(Boolean).join(' ');
      cote.dispatchEvent(new Event('input', { bubbles: true })); // les boutons des exemplaires suivent la cote affichée
      cote.focus();
    });
  });

  // « Ajouter des exemplaires » et « Renuméroter » emportent la cote affichée : une cote proposée
  // mais pas encore enregistrée sert quand même de préfixe (elle est enregistrée au passage).
  document.querySelectorAll('form[data-cote-jointe]').forEach(function (form) {
    var cote = document.querySelector('[data-cote]');
    var champ = form.querySelector('input[name="cote"]');
    var modele = form.getAttribute('data-confirmer-modele');
    if (!cote || !champ) { return; }
    function majCote() {
      champ.value = cote.value.trim();
      if (modele) { form.setAttribute('data-confirmer', modele.replace(/\{cote\}/g, champ.value || '…')); }
    }
    cote.addEventListener('input', majCote);
    form.addEventListener('submit', majCote); // avant la demande de confirmation, posée sur document
    majCote();
  });

  // Exemplaires d'un ouvrage : chaque ligne s'enregistre toute seule dès qu'on la modifie
  document.querySelectorAll('tr[data-auto-enregistrer]').forEach(function (ligne) {
    var form = document.getElementById(ligne.getAttribute('data-auto-enregistrer'));
    var temoin = ligne.querySelector('.etat-enregistrement');
    if (!form || !temoin) { return; }
    ligne.querySelectorAll('.sans-auto').forEach(function (b) { b.remove(); }); // le bouton ✓ ne sert qu'en l'absence de JS
    var attente = null;
    var dernier = '';
    function etat(classe, texte) {
      temoin.className = 'etat-enregistrement ' + classe;
      temoin.textContent = texte;
    }
    function enregistrer() {
      var donnees = new FormData(form);
      var signature = new URLSearchParams(donnees).toString();
      if (signature === dernier) { return; }
      dernier = signature;
      etat('en-cours', 'Enregistrement…');
      fetch(form.action, { method: 'POST', body: donnees, credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.ok) {
            etat('ok', 'Enregistré');
            window.setTimeout(function () { etat('', ''); }, 2500);
          } else {
            dernier = '';
            etat('erreur', res.erreur || 'Non enregistré');
          }
        })
        .catch(function () { dernier = ''; etat('erreur', 'Non enregistré'); });
    }
    dernier = new URLSearchParams(new FormData(form)).toString();
    ligne.addEventListener('change', enregistrer);                       // listes déroulantes, et champs texte quittés
    ligne.addEventListener('input', function () {                        // saisie au clavier : on attend une pause
      window.clearTimeout(attente);
      attente = window.setTimeout(enregistrer, 900);
    });
    form.addEventListener('submit', function (ev) { ev.preventDefault(); enregistrer(); });
  });

  // Listes à ranger (tableau de bord) : flèches monter / descendre ; l'ordre envoyé suit l'ordre affiché
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-monter], [data-descendre]');
    if (!b || !b.closest('[data-ordonnable]')) { return; }
    var li = b.closest('li');
    if (b.hasAttribute('data-monter') && li.previousElementSibling) { li.parentNode.insertBefore(li, li.previousElementSibling); }
    if (b.hasAttribute('data-descendre') && li.nextElementSibling) { li.parentNode.insertBefore(li.nextElementSibling, li); }
    b.focus();
  });
})();
