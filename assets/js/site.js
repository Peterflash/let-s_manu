/**
 * LET'S MANU SERVICES — comportements du site public.
 * Aucun framework. Le site reste utilisable sans ce fichier (liens, formulaire, filtres).
 */
(function () {
    'use strict';

    var base = document.body.dataset.base || '';
    var $ = function (sel, racine) { return (racine || document).querySelector(sel); };
    var $$ = function (sel, racine) { return Array.prototype.slice.call((racine || document).querySelectorAll(sel)); };

    /* ---------- Menu mobile ---------- */
    var bouton = $('.menu-bouton');
    var menu = $('#menu');
    if (bouton && menu) {
        var basculer = function (ouvrir) {
            menu.classList.toggle('est-ouvert', ouvrir);
            bouton.setAttribute('aria-expanded', ouvrir ? 'true' : 'false');
        };
        bouton.addEventListener('click', function () {
            basculer(bouton.getAttribute('aria-expanded') !== 'true');
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && bouton.getAttribute('aria-expanded') === 'true') {
                basculer(false);
                bouton.focus();
            }
        });
    }

    /* ---------- Apparition douce au défilement ---------- */
    var reveles = $$('.revele');
    if ('IntersectionObserver' in window && reveles.length) {
        var observateur = new IntersectionObserver(function (entrees) {
            entrees.forEach(function (entree) {
                if (entree.isIntersecting) {
                    entree.target.classList.add('est-visible');
                    observateur.unobserve(entree.target);
                }
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
        reveles.forEach(function (el) { observateur.observe(el); });
    } else {
        reveles.forEach(function (el) { el.classList.add('est-visible'); });
    }

    /* ---------- Message de retour (succès ou erreurs du formulaire) : on y amène le focus ---------- */
    var aFocaliser = $('[data-focus]');
    if (aFocaliser) { aFocaliser.focus({ preventScroll: false }); }

    /* ---------- Suivi des clics : statistiques internes, et Google Analytics si accepté ---------- */
    document.addEventListener('click', function (e) {
        var cible = e.target.closest('[data-suivi]');
        if (!cible) { return; }
        var evenement = cible.dataset.suivi;
        try {
            var donnees = new FormData();
            donnees.append('e', evenement);
            if (navigator.sendBeacon) { navigator.sendBeacon(base + '/t', donnees); }
        } catch (err) { /* le suivi ne doit jamais gêner la navigation */ }
        if (typeof window.gtag === 'function') {
            window.gtag('event', 'clic_' + evenement, { page_path: location.pathname });
        }
    });

    /* ---------- Google Analytics 4, seulement après accord ---------- */
    var identifiantGa = document.body.dataset.ga;
    var bandeau = $('#consentement');
    var chargerGa = function () {
        if (!identifiantGa || window.gtag) { return; }
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', identifiantGa, { anonymize_ip: true });
        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(identifiantGa);
        document.head.appendChild(script);
    };
    if (identifiantGa && bandeau) {
        var choix = null;
        try { choix = localStorage.getItem('lm-consentement'); } catch (err) { /* stockage indisponible */ }
        if (choix === 'oui') { chargerGa(); }
        else if (choix !== 'non') { bandeau.hidden = false; }
        bandeau.addEventListener('click', function (e) {
            var reponse = e.target.closest('[data-consentement]');
            if (!reponse) { return; }
            try { localStorage.setItem('lm-consentement', reponse.dataset.consentement); } catch (err) { /* idem */ }
            bandeau.hidden = true;
            if (reponse.dataset.consentement === 'oui') { chargerGa(); }
        });
    }

    /* ---------- Assistant de questions rapides ---------- */
    var assistant = $('[data-assistant]');
    if (assistant) {
        var ouvrirAssistant = $('.assistant__bouton', assistant);
        var panneau = $('.assistant__panneau', assistant);
        var fermerAssistant = function () {
            panneau.hidden = true;
            ouvrirAssistant.setAttribute('aria-expanded', 'false');
            ouvrirAssistant.focus();
        };
        ouvrirAssistant.addEventListener('click', function () {
            panneau.hidden = false;
            ouvrirAssistant.setAttribute('aria-expanded', 'true');
            $('.assistant__fermer', panneau).focus();
        });
        $('.assistant__fermer', panneau).addEventListener('click', fermerAssistant);
        panneau.addEventListener('keydown', function (e) { if (e.key === 'Escape') { fermerAssistant(); } });
        $$('.assistant__questions button', panneau).forEach(function (question) {
            question.addEventListener('click', function () {
                var ouverte = question.getAttribute('aria-expanded') === 'true';
                $$('.assistant__questions button', panneau).forEach(function (autre) {
                    autre.setAttribute('aria-expanded', 'false');
                    document.getElementById(autre.getAttribute('aria-controls')).hidden = true;
                });
                if (!ouverte) {
                    question.setAttribute('aria-expanded', 'true');
                    document.getElementById(question.getAttribute('aria-controls')).hidden = false;
                }
            });
        });
    }

    /* ---------- Galerie filtrable ---------- */
    var galerie = $('[data-galerie]');
    if (galerie) {
        var fiches = $$('.fiche', galerie);
        var compte = $('[data-compte]', galerie);
        var vide = $('[data-vide]', galerie);
        var etat = { pole: '', secteur: '' };
        $$('[data-filtre][aria-current="true"]', galerie).forEach(function (lien) { etat[lien.dataset.filtre] = lien.dataset.valeur; });

        var appliquer = function () {
            var visibles = 0;
            fiches.forEach(function (fiche) {
                var montrer = (!etat.pole || fiche.dataset.pole === etat.pole) && (!etat.secteur || fiche.dataset.secteur === etat.secteur);
                fiche.hidden = !montrer;
                if (montrer) { visibles++; }
            });
            $$('[data-filtre]', galerie).forEach(function (lien) {
                if (etat[lien.dataset.filtre] === lien.dataset.valeur) { lien.setAttribute('aria-current', 'true'); }
                else { lien.removeAttribute('aria-current'); }
            });
            compte.textContent = visibles + ' ' + (visibles > 1 ? galerie.dataset.plusieurs : galerie.dataset.un);
            vide.hidden = visibles > 0;
            var requete = [];
            if (etat.pole) { requete.push('pole=' + encodeURIComponent(etat.pole)); }
            if (etat.secteur) { requete.push('secteur=' + encodeURIComponent(etat.secteur)); }
            history.replaceState(null, '', location.pathname + (requete.length ? '?' + requete.join('&') : ''));
        };

        galerie.addEventListener('click', function (e) {
            var lien = e.target.closest('[data-filtre]');
            var remise = e.target.closest('[data-reinitialiser]');
            if (lien) {
                e.preventDefault();
                etat[lien.dataset.filtre] = lien.dataset.valeur;
                appliquer();
            } else if (remise) {
                e.preventDefault();
                etat = { pole: '', secteur: '' };
                appliquer();
            }
        });
    }

    /* ---------- Visionneuse de photos ----------
       Chaque galerie [data-visionneuse] (fiche d'une réalisation, projets de la photothèque) porte la liste
       JSON de ses photos : titre du projet, légende, lieu, date. Sans JavaScript, les liens mènent aux images. */
    var visionneuse = $('.visionneuse');
    if (visionneuse && typeof visionneuse.showModal === 'function') {
        var image = $('img', visionneuse);
        var titre = $('[data-titre]', visionneuse);
        var legende = $('[data-legende]', visionneuse);
        var lieu = $('[data-lieu]', visionneuse);
        var date = $('[data-date]', visionneuse);
        var navigation = $$('.visionneuse__nav', visionneuse);
        var serie = [];
        var indice = 0;
        var declencheur = null;
        var renseigner = function (bloc, valeur) {
            $('[data-texte]', bloc).textContent = valeur || '';
            bloc.hidden = !valeur;
        };
        var montrer = function (i) {
            indice = (i + serie.length) % serie.length;
            var photo = serie[indice];
            image.src = photo.src;
            image.alt = photo.alt;
            titre.textContent = photo.titre || '';
            titre.hidden = !photo.titre;
            legende.textContent = (photo.legende ? photo.legende + ' — ' : '') + (indice + 1) + ' / ' + serie.length;
            renseigner(lieu, photo.lieu);
            renseigner(date, photo.date);
        };
        var lire = function (json) {
            try { var photos = JSON.parse(json); return Array.isArray(photos) ? photos : []; } catch (err) { return []; }
        };
        var ouvrir = function (photos, i, origine) {
            if (!photos.length) { return; }
            serie = photos;
            declencheur = origine;
            navigation.forEach(function (b) { b.hidden = serie.length < 2; });
            montrer(i);
            visionneuse.showModal();
        };

        $$('[data-visionneuse]').forEach(function (liste) {
            var photos = lire(liste.dataset.visionneuse);
            $$('a', liste).forEach(function (lien, i) {
                lien.addEventListener('click', function (e) {
                    if (!photos[i]) { return; }
                    e.preventDefault();
                    ouvrir(photos, i, lien);
                });
            });
        });

        visionneuse.addEventListener('click', function (e) {
            var action = e.target.closest('[data-action]');
            if (action) {
                if (action.dataset.action === 'fermer') { visionneuse.close(); }
                if (action.dataset.action === 'precedente') { montrer(indice - 1); }
                if (action.dataset.action === 'suivante') { montrer(indice + 1); }
            } else if (e.target === visionneuse) {
                visionneuse.close();
            }
        });
        visionneuse.addEventListener('keydown', function (e) {
            if (serie.length < 2) { return; }
            if (e.key === 'ArrowLeft') { montrer(indice - 1); }
            if (e.key === 'ArrowRight') { montrer(indice + 1); }
        });
        visionneuse.addEventListener('close', function () {
            image.removeAttribute('src');
            if (declencheur) { declencheur.focus(); }
        });
    }

    /* ---------- Carte Google Maps, chargée seulement à la demande ---------- */
    var carte = $('[data-carte]');
    if (carte) {
        $('[data-carte-afficher]', carte).addEventListener('click', function () {
            var cadre = document.createElement('iframe');
            cadre.src = carte.dataset.carte;
            cadre.title = carte.dataset.titre;
            cadre.loading = 'lazy';
            cadre.referrerPolicy = 'no-referrer-when-downgrade';
            carte.innerHTML = '';
            carte.appendChild(cadre);
        });
    }

    /* ---------- Formulaire de devis ---------- */
    var formulaire = $('[data-devis]');
    if (formulaire) {
        var service = $('#champ-service', formulaire);
        var groupes = $$('optgroup', service);

        // Le pôle choisi restreint la liste des services.
        var filtrerServices = function () {
            var coche = $('input[name="pole"]:checked', formulaire);
            var pole = coche ? coche.value : '';
            groupes.forEach(function (groupe) {
                var actif = !pole || groupe.dataset.pole === pole;
                groupe.hidden = !actif;
                groupe.disabled = !actif;
            });
            var choisie = service.options[service.selectedIndex];
            if (choisie && choisie.parentNode.disabled) { service.value = ''; }
        };
        $$('input[name="pole"]', formulaire).forEach(function (radio) { radio.addEventListener('change', filtrerServices); });
        // À l'inverse, choisir un service coche son pôle.
        service.addEventListener('change', function () {
            var groupe = service.options[service.selectedIndex].parentNode;
            if (groupe.dataset && groupe.dataset.pole) {
                var radio = $('input[name="pole"][value="' + groupe.dataset.pole + '"]', formulaire);
                if (radio && !radio.checked) { radio.checked = true; filtrerServices(); }
            }
        });
        filtrerServices();

        // Photos : aperçu, retrait, et allègement avant envoi (économie de données sur mobile).
        var champPhotos = $('#champ-photos', formulaire);
        var apercus = $('[data-apercus]', formulaire);
        var maximum = parseInt(formulaire.dataset.photosMax, 10) || 5;
        var photos = [];
        var peutRemplacer = typeof DataTransfer === 'function';

        var alleger = function (fichier) {
            return new Promise(function (resoudre) {
                if (!/^image\/(jpeg|png|webp)$/.test(fichier.type) || fichier.size < 400 * 1024) { resoudre(fichier); return; }
                var adresse = URL.createObjectURL(fichier);
                var img = new Image();
                img.onload = function () {
                    var ratio = Math.min(1, 1600 / Math.max(img.naturalWidth, img.naturalHeight));
                    var toile = document.createElement('canvas');
                    toile.width = Math.round(img.naturalWidth * ratio);
                    toile.height = Math.round(img.naturalHeight * ratio);
                    toile.getContext('2d').drawImage(img, 0, 0, toile.width, toile.height);
                    URL.revokeObjectURL(adresse);
                    toile.toBlob(function (blob) {
                        if (!blob || blob.size >= fichier.size) { resoudre(fichier); return; }
                        resoudre(new File([blob], fichier.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }));
                    }, 'image/jpeg', 0.8);
                };
                img.onerror = function () { URL.revokeObjectURL(adresse); resoudre(fichier); };
                img.src = adresse;
            });
        };

        var synchroniser = function () {
            var transfert = new DataTransfer();
            photos.forEach(function (f) { transfert.items.add(f); });
            champPhotos.files = transfert.files;
            apercus.innerHTML = '';
            photos.forEach(function (f, i) {
                var item = document.createElement('li');
                var vignette = document.createElement('img');
                vignette.src = URL.createObjectURL(f);
                vignette.alt = f.name;
                vignette.onload = function () { URL.revokeObjectURL(vignette.src); };
                var poids = document.createElement('span');
                poids.textContent = f.size > 1048576 ? (f.size / 1048576).toFixed(1) + ' Mo' : Math.round(f.size / 1024) + ' Ko';
                var retirer = document.createElement('button');
                retirer.type = 'button';
                retirer.setAttribute('aria-label', formulaire.dataset.retirer + ' ' + f.name);
                retirer.innerHTML = '<svg class="icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';
                retirer.addEventListener('click', function () { photos.splice(i, 1); synchroniser(); });
                item.appendChild(vignette);
                item.appendChild(poids);
                item.appendChild(retirer);
                apercus.appendChild(item);
            });
        };

        if (peutRemplacer) {
            champPhotos.addEventListener('change', function () {
                var nouvelles = Array.prototype.slice.call(champPhotos.files);
                Promise.all(nouvelles.map(alleger)).then(function (allegees) {
                    photos = photos.concat(allegees).slice(0, maximum);
                    synchroniser();
                });
            });
        }

        // Envoi : un seul clic, avec retour visuel.
        formulaire.addEventListener('submit', function () {
            var envoyer = $('button[type="submit"]', formulaire);
            envoyer.disabled = true;
            envoyer.textContent = formulaire.dataset.envoi;
        });
    }
})();
