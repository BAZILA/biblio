<?php
/**
 * Fiche d'un ouvrage, avec le panneau de réservation de l'espace lecteur.
 * @var array       $o
 * @var string|null $precedent, $suivant
 * @var array|null  $maReservation  Réservation active du lecteur connecté pour cet ouvrage
 * @var bool        $monPret        Le lecteur connecté a déjà cet ouvrage en prêt
 * @var int         $fileAttente    Nombre de réservations en attente
 */
$lecteur = user();
?>
<div class="item-page" itemscope itemtype="https://schema.org/Article">

	<!-- Article -->
	<article itemscope itemtype="http://schema.org/Article">
		<meta itemprop="inLanguage" content="fr-FR" />

<header class="article-header clearfix">
	<h1 class="article-title" itemprop="headline">
					<a href="<?= e($o['url']) ?>" itemprop="url" title="<?= e($o['titre']) ?>">
				<?= e($o['titre']) ?></a>
			</h1>
			</header>

				<aside class="article-aside clearfix">
							<dl class="article-info  muted">
			<dt class="article-info-term">
													Détails							</dt>
<?php if ($o['auteurs_txt'] !== ''): ?>
<dd class="createdby hasTooltip" itemprop="author" title="Auteur(s) : ">
	<i class="fa fa-user"></i>
			<span itemprop="name"><?php foreach ($o['auteurs'] as $i => $a): ?><?= $i ? ', ' : '' ?><a href="<?= e(url('auteurs/' . $a['slug'])) ?>"><?= e($a['nom']) ?></a><?php endforeach; ?></span>
</dd>
<?php endif; ?>
										<dd class="category-name hasTooltip" title="Domaine : ">
				<i class="fa fa-folder-open"></i>
									<a href="<?= e(url('domaines/' . $o['domaine_slug'])) ?>" ><span itemprop="genre"><?= e($o['domaine_nom']) ?></span></a>							</dd>
						</dl>
		</aside>

<?= render(__DIR__ . '/_champs.php', ['o' => $o]) ?>

<?php if ($o['image']): ?>
					<figure class="pull-left item-image">
    <img src="<?= e(media($o['image'])) ?>" alt="Couverture : <?= e($o['titre']) ?>">    </figure>
<?php endif; ?>

			<section class="article-content clearfix" itemprop="articleBody">
				<?= $o['resume'] ?>
				<?= $o['description'] ?>
			</section>

			<section class="biblio-pret box-shadow">
				<h3><i class="fa fa-book" aria-hidden="true"></i> Emprunter cet ouvrage</h3>
<?php if ($o['consultation_sur_place']): ?>
				<p>Cet ouvrage se consulte <strong>uniquement sur place</strong>, en salle de lecture.</p>
<?php else: ?>
				<p class="biblio-pret-etat">
					<?php if ($o['nb_dispo'] > 0): ?>
						<span class="biblio-dispo biblio-dispo-disponible"><?= e(pluriel($o['nb_dispo'], 'exemplaire disponible', 'exemplaires disponibles')) ?></span> sur <?= (int) $o['nb_ex'] ?>.
					<?php elseif ($o['nb_ex'] > 0): ?>
						<span class="biblio-dispo biblio-dispo-emprunte">Tous les exemplaires sont empruntés</span><?= $fileAttente ? ' — ' . e(pluriel($fileAttente, 'lecteur')) . ' en attente.' : '.' ?>
					<?php else: ?>
						Aucun exemplaire n’est actuellement en circulation.
					<?php endif; ?>
				</p>
	<?php if ($monPret): ?>
				<p>Vous avez cet ouvrage en prêt. <a href="<?= e(url('compte')) ?>">Voir mes prêts</a></p>
	<?php elseif ($maReservation): ?>
				<p>
					<?php if ($maReservation['statut'] === 'disponible'): ?>
						Votre exemplaire vous attend à l’accueil jusqu’au <strong><?= e(date_fr($maReservation['expire_le'])) ?></strong>.
					<?php else: ?>
						Vous avez réservé cet ouvrage : vous êtes <strong><?= (int) $maReservation['rang'] ?><?= (int) $maReservation['rang'] === 1 ? 'er' : 'e' ?></strong> dans la file d’attente.
					<?php endif; ?>
					<a href="<?= e(url('compte')) ?>">Gérer mes réservations</a>
				</p>
	<?php elseif ($o['nb_ex'] > 0): ?>
		<?php if ($lecteur && $lecteur['role'] === 'lecteur'): ?>
				<form method="post" action="<?= e(url('compte/reserver')) ?>">
					<?= csrf_field() ?>
					<input type="hidden" name="ouvrage_id" value="<?= (int) $o['id'] ?>">
					<button type="submit" class="btn btn-primary"><i class="fa fa-bookmark" aria-hidden="true"></i> <?= $o['nb_dispo'] > 0 ? 'Réserver un exemplaire' : 'Rejoindre la file d’attente' ?></button>
				</form>
				<p class="biblio-aide">L’exemplaire est mis de côté <?= reglage_int('reservation_delai_jours', 3) ?> jours à l’accueil ; le prêt est enregistré au retrait.</p>
		<?php elseif (!$lecteur): ?>
				<p><a class="btn btn-primary" href="<?= e(url('compte/connexion', ['retour' => $o['url']])) ?>">Se connecter pour réserver</a>
				&nbsp; Pas encore de compte ? <a href="<?= e(url('compte/inscription')) ?>">Créer un compte lecteur</a></p>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
			</section>
	</article>
	<!-- //Article -->

<?php if ($precedent || $suivant): ?>
<nav class="pagenavigation">
    <span class="pagination ms-0">
<?php if ($precedent): ?>
                <a class="btn btn-sm btn-secondary previous" href="<?= e($precedent) ?>" rel="prev">
            <span class="icon-chevron-left" aria-hidden="true"></span> <span aria-hidden="true">Précédent</span>            </a>
<?php endif; ?>
<?php if ($suivant): ?>
                    <a class="btn btn-sm btn-secondary next" href="<?= e($suivant) ?>" rel="next">
            <span aria-hidden="true">Suivant</span> <span class="icon-chevron-right" aria-hidden="true"></span>            </a>
<?php endif; ?>
        </span>
</nav>
<?php endif; ?>
		</div>
