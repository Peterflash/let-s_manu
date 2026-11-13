/**
 * LET'S MANU SERVICES — comportements de l'espace d'administration.
 */
(function () {
    'use strict';

    // Confirmation avant toute suppression
    document.addEventListener('submit', function (e) {
        var message = e.target.getAttribute('data-confirmer');
        if (message && !window.confirm(message)) {
            e.preventDefault();
        }
    });

    // Questions rapides : ajouter et retirer des lignes
    var zone = document.querySelector('[data-questions]');
    var modele = document.querySelector('[data-modele]');
    var ajouter = document.querySelector('[data-ajouter-question]');
    if (zone && modele && ajouter) {
        var compteur = Date.now();
        ajouter.addEventListener('click', function () {
            var enveloppe = document.createElement('div');
            enveloppe.innerHTML = modele.innerHTML.replace(/__i__/g, 'n' + (compteur++));
            var ligne = enveloppe.firstElementChild;
            zone.appendChild(ligne);
            ligne.querySelector('input').focus();
        });
        document.addEventListener('click', function (e) {
            var retirer = e.target.closest('[data-retirer-question]');
            if (retirer) {
                retirer.closest('.question').remove();
            }
        });
    }
})();
