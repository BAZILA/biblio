/*
 * Carrousels de l'accueil (Nouveautés, Coups de cœur, Citations).
 * Reprend le comportement des modules JT d'origine : Owl Carousel avec plusieurs
 * lignes par page sur petits écrans (les cartes sont regroupées en colonnes factices).
 */
(function ($) {
  'use strict';

  function largeurFenetre() {
    return window.innerWidth || document.documentElement.clientWidth;
  }

  window.biblioCarrousel = function (selecteur, responsive) {
    $(function () {
      var el = $(selecteur);
      if (!el.length) { return; }

      var options = {
        margin: 0, stagePadding: 0, center: false, loop: true, nav: true, dots: true,
        rtl: false, slideBy: 'page', autoplay: false, autoplaySpeed: 300, smartSpeed: 300,
        autoplayTimeout: 4000, autoplayHoverPause: true, mouseDrag: true, touchDrag: true,
        responsive: responsive
      };

      var paliers = Object.keys(responsive).map(Number).sort(function (a, b) { return b - a; });
      var slides = el.find('[data-slide-index]');
      var nbSlides = slides.length;
      var carrousel, lignesPrec, colsPrec;

      function miseAJour() {
        var l = largeurFenetre(), lignes = 1, cols = 1;
        for (var i = 0; i < paliers.length; i++) {
          if (l >= paliers[i] || i === paliers.length - 1) {
            lignes = responsive[paliers[i]].rows || 1;
            cols = responsive[paliers[i]].items;
            break;
          }
        }
        if (lignes === lignesPrec && cols === colsPrec) { return; }

        var reinit = false;
        if (carrousel) {
          carrousel.trigger('destroy.owl.carousel');
          carrousel = undefined;
          slides = el.find('[data-slide-index]').detach().appendTo(el);
          el.find('.fake-col-wrapper').remove();
          reinit = true;
        }

        // Répartit les cartes dans des colonnes contenant « lignes » cartes chacune
        var parPage = lignes * cols;
        var page = Math.floor(nbSlides / parPage);
        var nbCols = page * cols + (nbSlides >= (page * parPage + cols) ? cols : (nbSlides % cols));
        var compteur = 0;
        for (var c = 0; c < nbCols; c++) {
          var col = $('<div class="fake-col-wrapper"></div>').appendTo(el);
          for (var r = 0; r < lignes; r++) {
            var index = Math.floor(compteur / parPage) * parPage + (c % cols) + r * cols;
            if (index < nbSlides) {
              slides.filter('[data-slide-index=' + index + ']').detach().appendTo(col);
            }
            compteur++;
          }
        }
        lignesPrec = lignes;
        colsPrec = cols;
        if (reinit) { carrousel = el.owlCarousel(options); }
      }

      if (nbSlides > 0) {
        $(window).on('resize', miseAJour);
        miseAJour();
      }
      carrousel = el.owlCarousel(options);

      if (window.lightbox) {
        lightbox.option({ fadeDuration: 300, fitImagesInViewport: true, imageFadeDuration: 300, positionFromTop: 150, resizeDuration: 150 });
      }
    });
  };

  window.biblioCitations = function (selecteur) {
    $(function () {
      $(selecteur).owl2Carousel({
        margin: 10, nav: true, dots: true, slideBy: 'page',
        navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
        responsive: { 0: { items: 1 }, 768: { items: 2 }, 991: { items: 3 } }
      });
    });
  };
})(jQuery);
