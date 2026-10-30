<?php
/** Page contact : coordonnées + formulaire (messages consultables dans l'admin). @var array $saisie @var array $erreurs */
$details = [
    ['Adresse', 'contact-street', 'streetAddress', reglage('contact_adresse')],
    ['Ville', 'contact-suburb', 'addressLocality', reglage('contact_ville')],
    ['Pays', 'contact-country', 'addressCountry', reglage('contact_pays')],
];
$champ = function (string $nom, string $libelle, string $type = 'text') use ($saisie, $erreurs): string {
    $id = 'contact_' . $nom;
    $html = '<div class="control-group' . (isset($erreurs[$nom]) ? ' error' : '') . '">'
        . '<div class="control-label"><label for="' . $id . '" class="required">' . e($libelle) . '<span class="star" aria-hidden="true">&#160;*</span></label></div>'
        . '<div class="controls">';
    if ($type === 'textarea') {
        $html .= '<textarea name="' . $nom . '" id="' . $id . '" cols="50" rows="10" class="form-control required" required>' . e($saisie[$nom] ?? '') . '</textarea>';
    } else {
        $html .= '<input type="' . $type . '" name="' . $nom . '" id="' . $id . '" value="' . e($saisie[$nom] ?? '') . '" class="form-control required" required>';
    }
    if (isset($erreurs[$nom])) {
        $html .= '<span class="help-inline">' . e($erreurs[$nom]) . '</span>';
    }
    return $html . '</div></div>';
};
?>
<div class="com-contact contact exeltis" itemscope itemtype="https://schema.org/Person">
            <h1>
            Contacter la bibliothèque        </h1>

            <div class="page-header">
            <h2>
                                <span class="contact-name" itemprop="name"><?= e(reglage('contact_nom')) ?></span>
            </h2>
        </div>

    <div class="biblio-contact-grid">
        <div class="biblio-contact-info">
<?php if (reglage('contact_role') !== ''): ?>
                                    <p class="biblio-contact-role" itemprop="jobTitle">
                        <?= e(reglage('contact_role')) ?>                    </p>
<?php endif; ?>

                <dl class="com-contact__address contact-address biblio-contact-details" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
<?php foreach ($details as [$libelle, $classe, $prop, $valeur]): if ($valeur === '') continue; ?>
                        <dt><?= e($libelle) ?></dt>
            <dd>
                <span class="<?= $classe ?>" itemprop="<?= $prop ?>"><?= e($valeur) ?></span>
            </dd>
<?php endforeach; ?>
<?php if (reglage('contact_telephone') !== ''): ?>
            <dt>Téléphone</dt>
        <dd>
            <span class="contact-telephone" itemprop="telephone"><?= e(reglage('contact_telephone')) ?></span>
        </dd>
<?php endif; ?>
    </dl>

                            <div class="com-contact__miscinfo contact-miscinfo">
                    <div class="contact-misc">
                        <?= bloc('contact_infos') ?>                    </div>
                </div>
                    </div>

        <div class="biblio-contact-form-wrap">
                            <div class="com-contact__form contact-form exeltis biblio-contact-form box-shadow">
    <form id="contact-form" action="<?= e(url('contact')) ?>" method="post" class="form-validate well">
        <?= csrf_field() ?>
                                                            <fieldset class="m-0">
            <div class="control-group field-spacer">
            <div class="control-label"><span class="spacer"><span class="before"></span><span class="text"><label id="jform_spacer-lbl" class=""><strong class="red">*</strong> Champ requis</label></span><span class="after"></span></span></div>
        <div class="controls">
                     </div>
</div>
            <?= $champ('nom', 'Nom') ?>
            <?= $champ('email', 'E-mail', 'email') ?>
            <?= $champ('sujet', 'Sujet') ?>
            <?= $champ('message', 'Message', 'textarea') ?>
            <div class="biblio-hp" aria-hidden="true"><label>Ne pas remplir <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label></div>
                                    </fieldset>
        <div class="control-group">
            <div class="controls">
                <button class="btn btn-primary validate" type="submit">Envoyer</button>
            </div>
        </div>
    </form>
</div>
                    </div>
    </div>

    </div>
