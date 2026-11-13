<?php
/**
 * Page Contact / Devis.
 * Variables : $valeurs (champs saisis), $erreurs (clé => message), $succes (null ou ['ref', 'email', 'urgence']).
 */
$champErreur = function (string $nom) use ($erreurs): string {
    return isset($erreurs[$nom]) ? '<p class="champ__erreur" id="erreur-' . $nom . '">' . e($erreurs[$nom]) . '</p>' : '';
};
$attributs = function (string $nom, string $aide = '') use ($erreurs): string {
    $decritPar = trim(($aide !== '' ? $aide : '') . (isset($erreurs[$nom]) ? " erreur-$nom" : ''));
    return (isset($erreurs[$nom]) ? ' aria-invalid="true"' : '') . ($decritPar !== '' ? ' aria-describedby="' . $decritPar . '"' : '');
};
$carteRequete = (cfg('gps_lat') !== '' && cfg('gps_lng') !== '') ? cfg('gps_lat') . ',' . cfg('gps_lng') : cfg('carte_recherche');
$carteLien = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($carteRequete);
$carteIntegree = 'https://www.google.com/maps?q=' . rawurlencode($carteRequete) . '&z=16&output=embed&hl=' . lang();
$photosMax = (int) config('devis.photos_max');
?>
<section class="tete-page">
    <div class="conteneur">
        <h1><?= t('contact.titre') ?></h1>
        <p class="tete-page__chapeau"><?= t('contact.chapeau') ?></p>
    </div>
</section>

<section class="section section--serree">
    <div class="conteneur contact">
        <div class="contact__formulaire" id="formulaire">
<?php if ($succes): ?>
            <div class="succes" role="status" tabindex="-1" data-focus>
                <?= icon('qualite') ?>
                <h2><?= t('contact.succes.titre') ?></h2>
                <p><?= t('contact.succes.texte') ?></p>
                <p class="succes__reference"><?= t('contact.succes.reference') ?> : <strong><?= e($succes['ref']) ?></strong></p>
<?php if (!empty($succes['email'])): ?>
                <p><?= t('contact.succes.accuse') ?></p>
<?php endif; ?>
<?php if (!empty($succes['urgence'])): ?>
                <p class="succes__urgence"><?= t('contact.succes.urgence') ?></p>
<?php endif; ?>
                <div class="succes__actions">
                    <a class="btn btn--primaire" href="<?= e(wa_href(tx('ui.wa_general') . '(réf. ' . $succes['ref'] . ') ')) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><?= t('contact.succes.wa') ?></span></a>
<?php if (!empty($succes['urgence'])): ?>
                    <a class="btn btn--contour" href="<?= e(tel_href()) ?>" data-suivi="appel"><?= icon('telephone') ?><span><?= e(cfg('telephone')) ?></span></a>
<?php endif; ?>
                    <a class="lien-fort" href="<?= e(url('contact')) ?>#formulaire"><?= t('contact.succes.nouvelle') ?></a>
                </div>
            </div>
<?php else: ?>
            <h2><?= t('contact.form.titre') ?></h2>
            <p class="champ__aide"><?= t('contact.form.obligatoire') ?></p>

<?php if ($erreurs): ?>
            <div class="alerte" role="alert" tabindex="-1" data-focus>
                <?= icon('alerte') ?>
                <div>
                    <p><strong><?= e($erreurs['_global'] ?? tx('contact.erreurs.resume')) ?></strong></p>
<?php if (count($erreurs) > (isset($erreurs['_global']) ? 1 : 0)): ?>
                    <ul>
<?php foreach ($erreurs as $champ => $message): if ($champ === '_global') { continue; } ?>
                        <li><a href="#champ-<?= e($champ) ?>"><?= e($message) ?></a></li>
<?php endforeach; ?>
                    </ul>
<?php endif; ?>
                </div>
            </div>
<?php endif; ?>

            <form class="formulaire" method="post" action="<?= e(url('contact')) ?>#formulaire" enctype="multipart/form-data" novalidate data-devis
                  data-photos-max="<?= $photosMax ?>" data-envoi="<?= t('contact.form.envoi') ?>" data-retirer="<?= t('contact.form.photos_retirer') ?>">
                <?= csrf_field() ?>
                <div class="pot" aria-hidden="true">
                    <label for="site_web">Ne pas remplir ce champ</label>
                    <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
                </div>

                <fieldset>
                    <legend><?= t('contact.form.vous') ?></legend>
                    <div class="champ">
                        <label for="champ-nom"><?= t('contact.form.nom') ?> <span aria-hidden="true">*</span></label>
                        <input type="text" id="champ-nom" name="nom" value="<?= e($valeurs['nom']) ?>" autocomplete="name" required maxlength="100"<?= $attributs('nom') ?>>
                        <?= $champErreur('nom') ?>
                    </div>
                    <div class="champ">
                        <label for="champ-entreprise"><?= t('contact.form.entreprise') ?> <span class="champ__facultatif">(<?= t('contact.form.facultatif') ?>)</span></label>
                        <input type="text" id="champ-entreprise" name="entreprise" value="<?= e($valeurs['entreprise']) ?>" autocomplete="organization" maxlength="120">
                    </div>
                    <div class="champs-2">
                        <div class="champ">
                            <label for="champ-telephone"><?= t('contact.form.telephone') ?> <span aria-hidden="true">*</span></label>
                            <input type="tel" id="champ-telephone" name="telephone" value="<?= e($valeurs['telephone']) ?>" autocomplete="tel" inputmode="tel" placeholder="+225 07 00 00 00 00" required maxlength="24"<?= $attributs('telephone', 'aide-telephone') ?>>
                            <p class="champ__aide" id="aide-telephone"><?= t('contact.form.telephone_aide') ?></p>
                            <?= $champErreur('telephone') ?>
                        </div>
                        <div class="champ">
                            <label for="champ-email"><?= t('contact.form.email') ?> <span class="champ__facultatif">(<?= t('contact.form.facultatif') ?>)</span></label>
                            <input type="email" id="champ-email" name="email" value="<?= e($valeurs['email']) ?>" autocomplete="email" inputmode="email" maxlength="150"<?= $attributs('email', 'aide-email') ?>>
                            <p class="champ__aide" id="aide-email"><?= t('contact.form.email_aide') ?></p>
                            <?= $champErreur('email') ?>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><?= t('contact.form.besoin') ?></legend>

                    <div class="champ" id="champ-pole" role="radiogroup" aria-labelledby="libelle-pole"<?= isset($erreurs['pole']) ? ' aria-describedby="erreur-pole"' : '' ?>>
                        <p class="champ__libelle" id="libelle-pole"><?= t('contact.form.pole') ?> <span aria-hidden="true">*</span></p>
                        <div class="choix">
<?php foreach (array_keys(config('poles')) as $p): ?>
                            <label class="choix__option"><input type="radio" name="pole" value="<?= e($p) ?>"<?= $valeurs['pole'] === $p ? ' checked' : '' ?> required><span><?= t("poles.$p") ?></span></label>
<?php endforeach; ?>
                        </div>
                        <?= $champErreur('pole') ?>
                    </div>

                    <div class="champ">
                        <label for="champ-service"><?= t('contact.form.service') ?> <span aria-hidden="true">*</span></label>
                        <select id="champ-service" name="service" required<?= $attributs('service') ?>>
                            <option value=""><?= t('contact.form.service_choisir') ?></option>
<?php foreach (config('poles') as $p => $servicesPole): ?>
                            <optgroup label="<?= t("poles.$p") ?>" data-pole="<?= e($p) ?>">
<?php foreach ($servicesPole as $s): ?>
                                <option value="<?= e($s) ?>"<?= $valeurs['service'] === $s ? ' selected' : '' ?>><?= t("$p.services.$s.titre") ?></option>
<?php endforeach; ?>
                            </optgroup>
<?php endforeach; ?>
                            <option value="autre"<?= $valeurs['service'] === 'autre' ? ' selected' : '' ?>><?= t('contact.form.autre') ?></option>
                        </select>
                        <?= $champErreur('service') ?>
                    </div>

                    <div class="champ">
                        <label for="champ-lieu"><?= t('contact.form.lieu') ?> <span aria-hidden="true">*</span></label>
                        <input type="text" id="champ-lieu" name="lieu" value="<?= e($valeurs['lieu']) ?>" required maxlength="150"<?= $attributs('lieu', 'aide-lieu') ?>>
                        <p class="champ__aide" id="aide-lieu"><?= t('contact.form.lieu_aide') ?></p>
                        <?= $champErreur('lieu') ?>
                    </div>

                    <div class="champ" role="radiogroup" aria-labelledby="libelle-urgence">
                        <p class="champ__libelle" id="libelle-urgence"><?= t('contact.form.urgence') ?></p>
                        <div class="choix">
                            <label class="choix__option"><input type="radio" name="urgence" value="non"<?= $valeurs['urgence'] !== 'oui' ? ' checked' : '' ?>><span><?= t('contact.form.urgence_non') ?></span></label>
                            <label class="choix__option choix__option--alerte"><input type="radio" name="urgence" value="oui"<?= $valeurs['urgence'] === 'oui' ? ' checked' : '' ?>><span><?= t('contact.form.urgence_oui') ?></span></label>
                        </div>
                    </div>

                    <div class="champ">
                        <label for="champ-description"><?= t('contact.form.description') ?> <span aria-hidden="true">*</span></label>
                        <textarea id="champ-description" name="description" rows="5" required maxlength="3000"<?= $attributs('description', 'aide-description') ?>><?= e($valeurs['description']) ?></textarea>
                        <p class="champ__aide" id="aide-description"><?= t('contact.form.description_aide') ?></p>
                        <?= $champErreur('description') ?>
                    </div>

                    <div class="champ">
                        <label for="champ-photos"><?= t('contact.form.photos') ?> <span class="champ__facultatif">(<?= t('contact.form.facultatif') ?>)</span></label>
                        <div class="depot">
                            <?= icon('photo') ?>
                            <input type="file" id="champ-photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple<?= $attributs('photos', 'aide-photos') ?>>
                        </div>
                        <p class="champ__aide" id="aide-photos"><?= t('contact.form.photos_aide') ?></p>
                        <?= $champErreur('photos') ?>
                        <ul class="depot__apercus" data-apercus aria-live="polite"></ul>
                    </div>
                </fieldset>

                <div class="champ champ--case" id="champ-consentement">
                    <label class="case">
                        <input type="checkbox" name="consentement" value="1" required<?= $valeurs['consentement'] ? ' checked' : '' ?><?= $attributs('consentement') ?>>
                        <span><?= t('contact.form.consentement') ?> <span aria-hidden="true">*</span></span>
                    </label>
                    <p class="champ__aide"><a href="<?= e(url('confidentialite')) ?>" target="_blank" rel="noopener"><?= t('contact.form.confidentialite') ?></a></p>
                    <?= $champErreur('consentement') ?>
                </div>

<?php if (cfg('turnstile_site') !== '' && cfg('turnstile_secret') !== ''): ?>
                <div class="cf-turnstile" data-sitekey="<?= e(cfg('turnstile_site')) ?>" data-language="<?= lang() ?>"></div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer nonce="<?= NONCE ?>"></script>
<?php endif; ?>

                <button class="btn btn--primaire btn--grand btn--bloc" type="submit"><?= t('contact.form.envoyer') ?></button>
            </form>
<?php endif; ?>
        </div>

        <aside class="contact__direct" aria-labelledby="direct-titre">
            <h2 id="direct-titre"><?= t('contact.coordonnees.titre') ?></h2>
            <div class="contact__boutons">
                <a class="btn btn--fonce btn--bloc" href="<?= e(tel_href()) ?>" data-suivi="appel"><?= icon('telephone') ?><span><?= e(cfg('telephone')) ?></span></a>
                <a class="btn btn--contour btn--bloc" href="<?= e(wa_href((string) tx('ui.wa_general'))) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><?= t('ui.ecrire_whatsapp') ?></span></a>
            </div>
            <dl class="coordonnees">
                <div>
                    <dt><?= icon('email') ?><span><?= t('contact.coordonnees.email') ?></span></dt>
                    <dd><a href="mailto:<?= e(cfg('email')) ?>"><?= e(cfg('email')) ?></a></dd>
                </div>
                <div>
                    <dt><?= icon('repere') ?><span><?= t('contact.coordonnees.adresse') ?></span></dt>
                    <dd><?= e(cfg('ville')) ?>, <?= e(cfg_l('adresse')) ?></dd>
                </div>
                <div>
                    <dt><?= icon('horloge') ?><span><?= t('contact.coordonnees.horaires') ?></span></dt>
                    <dd><?= nl2br(e(cfg_l('horaires'))) ?><br><strong><?= t('contact.coordonnees.urgences') ?></strong></dd>
                </div>
            </dl>
            <div class="qr-bloc">
                <div class="qr-cadre"><?= qr_svg(carte_adresse(), (string) tx('carte.qr_alt')) ?></div>
                <div>
                    <h3><?= t('carte.contact_titre') ?></h3>
                    <p><?= t('carte.contact_texte') ?></p>
                    <a class="lien-fort" href="<?= e(url('carte')) ?>"><?= t('carte.voir') ?></a>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="section section--gris" aria-labelledby="carte-titre">
    <div class="conteneur">
        <div class="section__tete">
            <h2 id="carte-titre"><?= t('contact.carte.titre') ?></h2>
            <p><?= e(cfg('ville')) ?>, <?= e(cfg_l('adresse')) ?></p>
        </div>
        <div class="carte" data-carte="<?= e($carteIntegree) ?>" data-titre="<?= t('contact.carte.legende') ?>">
            <div class="carte__attente">
                <?= icon('repere') ?>
                <button class="btn btn--fonce" type="button" data-carte-afficher><?= t('contact.carte.afficher') ?></button>
                <p><?= t('contact.carte.note') ?></p>
            </div>
        </div>
        <p class="carte__lien"><a class="lien-fort" href="<?= e($carteLien) ?>" target="_blank" rel="noopener"><?= t('contact.carte.ouvrir') ?></a></p>
    </div>
</section>
