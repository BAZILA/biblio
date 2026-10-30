<?php /** @var array $liste @var bool $traitees */
$badges = ['en_attente' => '', 'acceptee' => 'vert', 'refusee' => 'rouge', 'annulee' => ''];
?>
<div class="entete"><h1>Demandes de prolongation</h1></div>

<nav class="onglets">
	<a href="<?= e(admin_url('prets/demandes')) ?>" class="<?= $traitees ? '' : 'actif' ?>">À traiter</a>
	<a href="<?= e(admin_url('prets/demandes', ['vue' => 'traitees'])) ?>" class="<?= $traitees ? 'actif' : '' ?>">Déjà traitées</a>
</nav>

<?php if (!$traitees): ?>
<p class="doux">Les lecteurs ne prolongent pas eux-mêmes : ils en font la demande depuis leur espace. Vous acceptez (en ajustant la date si besoin) ou vous refusez en indiquant le motif ; le lecteur voit la réponse dans son espace et la reçoit par e-mail.</p>
<?php endif; ?>

<div class="carte" style="padding:0">
<?php if (!$liste): ?>
	<div class="vide"><?= $traitees ? 'Aucune demande traitée.' : 'Aucune demande en attente.' ?></div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Lecteur</th><th>Ouvrage</th><th>Échéance actuelle</th><th>Demande</th><th><?= $traitees ? 'Décision' : 'Points d’attention' ?></th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $d): ?>
			<tr>
				<td><a href="<?= e(admin_url('lecteurs/voir/' . $d['lecteur_id'])) ?>"><?= e(trim($d['prenom'] . ' ' . $d['nom'])) ?></a><?= $d['matricule'] ? '<br><small>' . e($d['matricule']) . '</small>' : '' ?></td>
				<td><?= e($d['titre']) ?><br><small><code><?= e($d['exemplaire_code']) ?></code> · emprunté le <?= e(date_fr($d['date_pret'])) ?></small></td>
				<td><?= e(date_fr($d['date_retour_prevue'])) ?><br><small><?= (int) $d['prolongations'] ? 'déjà prolongé ' . (int) $d['prolongations'] . ' fois' : 'jamais prolongé' ?></small></td>
				<td><?= e(date_fr($d['demandee_le'], true)) ?><?= $d['motif'] !== '' ? '<br><em>« ' . e($d['motif']) . ' »</em>' : '' ?></td>
<?php if ($traitees): ?>
				<td><span class="badge <?= $badges[$d['statut']] ?>"><?= e(LIBELLES_DEMANDE[$d['statut']]) ?></span>
					<?= $d['nouvelle_date'] ? '<br><small>jusqu’au ' . e(date_fr($d['nouvelle_date'])) . '</small>' : '' ?>
					<?= $d['reponse'] !== '' ? '<br><small>' . e($d['reponse']) . '</small>' : '' ?></td>
				<td><small><?= $d['traitee_le'] ? e(date_fr($d['traitee_le'], true)) : '' ?><?= $d['agent_id'] ? '<br>par ' . e(nom_acteur((int) $d['agent_id'])) : '' ?></small></td>
<?php else: ?>
				<td><?php if ($d['obstacles']): ?><?php foreach ($d['obstacles'] as $o): ?><span class="badge orange"><?= e($o) ?></span><br><?php endforeach; ?><?php else: ?><span class="badge vert">Aucun</span><?php endif; ?></td>
				<td class="actions"><?= !peut('demandes.traiter') ? '' : bouton_modale('modale-demande', '<i class="fa-solid fa-gavel"></i> Traiter', 'btn petit', [
                    'action'    => admin_url('prets/demande/' . $d['id']),
                    'lecteur'   => trim($d['prenom'] . ' ' . $d['nom']),
                    'titre'     => $d['titre'],
                    'echeance'  => date_fr($d['date_retour_prevue']),
                    'motif'     => $d['motif'] !== '' ? $d['motif'] : '(aucun motif donné)',
                    'date'      => $d['proposee'],
                    'obstacles' => implode(' ', $d['obstacles']),
                ]) ?></td>
<?php endif; ?>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>

<dialog class="modale" id="modale-demande" aria-labelledby="titre-demande">
	<form method="post" data-remplir-action>
		<?= csrf_field() ?>
		<header><h2 id="titre-demande"><i class="fa-solid fa-gavel"></i> Demande de prolongation</h2><button type="button" class="fermer" data-fermer aria-label="Fermer">&times;</button></header>
		<div class="modale-corps">
			<dl class="recap">
				<dt>Lecteur</dt><dd data-remplir="lecteur"></dd>
				<dt>Ouvrage</dt><dd data-remplir="titre"></dd>
				<dt>Échéance actuelle</dt><dd data-remplir="echeance"></dd>
				<dt>Motif du lecteur</dt><dd><em data-remplir="motif"></em></dd>
			</dl>
			<div class="flash erreur" data-remplir="obstacles" data-masquer-si-vide></div>
			<div class="champ">
				<label for="demande-date">Nouvelle date de retour (si vous acceptez)</label>
				<input type="date" id="demande-date" name="date" data-remplir="date" min="<?= e(plus_jours(aujourdhui(), 1)) ?>" max="<?= e(plus_jours(aujourdhui(), 365)) ?>">
			</div>
			<div class="champ">
				<label for="demande-reponse">Message au lecteur <small class="doux">(obligatoire en cas de refus)</small></label>
				<textarea id="demande-reponse" name="reponse" rows="2" maxlength="255" placeholder="Ex. ouvrage réservé par un autre lecteur, merci de le rapporter à la date prévue."></textarea>
			</div>
		</div>
		<footer>
			<button type="button" class="btn secondaire" data-fermer>Fermer</button>
			<button class="btn danger" type="submit" name="decision" value="refuser" data-exige="#demande-reponse"><i class="fa-solid fa-xmark"></i> Refuser</button>
			<button class="btn vert" type="submit" name="decision" value="accepter" data-exige="#demande-date"><i class="fa-solid fa-check"></i> Accepter</button>
		</footer>
	</form>
</dialog>
