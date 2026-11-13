<?php
/**
 * Assistant de questions rapides (niveau 1) : questions et réponses rédigées à l'avance,
 * gérées dans l'admin (FAQ), avec renvoi vers WhatsApp. Aucune IA.
 */
$questions = array_values(array_filter(store_read('faq'), fn ($q) => L($q['q'] ?? '') !== '' && L($q['r'] ?? '') !== ''));
if (!$questions) {
    return;
}
?>
<div class="assistant" data-assistant>
    <button class="assistant__bouton" type="button" aria-expanded="false" aria-controls="assistant-panneau">
        <?= icon('bulle') ?><span><?= t('assistant.ouvrir') ?></span>
    </button>
    <section class="assistant__panneau" id="assistant-panneau" aria-labelledby="assistant-titre" hidden>
        <header class="assistant__entete">
            <h2 id="assistant-titre"><?= t('assistant.titre') ?></h2>
            <button class="assistant__fermer" type="button" aria-label="<?= t('assistant.fermer') ?>"><?= icon('fermer') ?></button>
        </header>
        <div class="assistant__corps">
            <p class="assistant__intro"><?= t('assistant.intro') ?></p>
            <ul class="assistant__questions">
<?php foreach ($questions as $i => $question): ?>
                <li>
                    <button type="button" aria-expanded="false" aria-controls="assistant-reponse-<?= $i ?>"><?= e(L($question['q'])) ?></button>
                    <div class="assistant__reponse" id="assistant-reponse-<?= $i ?>" hidden>
                        <p><?= e(L($question['r'])) ?></p>
                    </div>
                </li>
<?php endforeach; ?>
            </ul>
        </div>
        <footer class="assistant__pied">
            <a class="btn btn--primaire btn--bloc" href="<?= e(wa_href((string) tx('ui.wa_question'))) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><?= t('assistant.whatsapp') ?></span></a>
        </footer>
    </section>
</div>
