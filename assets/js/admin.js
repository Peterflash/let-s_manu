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

    // Envoi d'un formulaire avec fichiers : un seul clic, avec retour visuel (l'envoi peut être long en 3G).
    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) { return; }
        var bouton = e.target.querySelector('button[type="submit"][data-envoi]');
        if (bouton) {
            bouton.setAttribute('data-texte', bouton.textContent);
            bouton.disabled = true;
            bouton.textContent = bouton.getAttribute('data-envoi');
        }
    });
    // Retour arrière du navigateur : la page peut revenir du cache avec le bouton encore désactivé.
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) { return; }
        Array.prototype.forEach.call(document.querySelectorAll('button[data-envoi][data-texte]'), function (b) {
            b.disabled = false;
            b.textContent = b.getAttribute('data-texte');
        });
    });

    /*
     * Photos d'un chantier : allègement dans le navigateur avant l'envoi.
     * Une photo de téléphone pèse 3 à 10 Mo ; réduite à 1600 px (la taille gardée par le site), elle passe
     * sous 500 Ko : l'envoi depuis le chantier est bien plus rapide et consomme beaucoup moins de données.
     * Le fichier réduit perd sa date de prise de vue : elle est lue avant, sur la photo d'origine, et envoyée
     * à part (photos_date[n]). Sans JavaScript ou sur un vieux navigateur, les photos partent telles quelles
     * et le serveur les réduit lui-même.
     */
    var champPhotos = document.querySelector('input[type="file"][data-reduire]');
    if (champPhotos && typeof DataTransfer === 'function' && window.Promise && window.URL && window.DataView) {
        var formulairePhotos = champPhotos.form;
        var statut = formulairePhotos.querySelector('[data-reduire-statut]');
        var apercus = formulairePhotos.querySelector('[data-reduire-apercus]');
        var zoneDates = formulairePhotos.querySelector('[data-reduire-dates]');
        var boutons = formulairePhotos.querySelectorAll('button[type="submit"]');
        var restantes = parseInt(champPhotos.getAttribute('data-reduire'), 10) || 0;
        var aideInitiale = statut.textContent;
        var COTE_MAX = 1600;

        var poids = function (octets) {
            return octets >= 1048576 ? (octets / 1048576).toFixed(1).replace('.', ',') + ' Mo' : Math.max(1, Math.round(octets / 1024)) + ' Ko';
        };

        var lireOctets = function (blob) {
            if (blob.arrayBuffer) { return blob.arrayBuffer(); }
            return new Promise(function (resoudre, rejeter) {
                var lecteur = new FileReader();
                lecteur.onload = function () { resoudre(lecteur.result); };
                lecteur.onerror = rejeter;
                lecteur.readAsArrayBuffer(blob);
            });
        };

        // Date de prise de vue (EXIF DateTimeOriginal, sinon DateTimeDigitized) d'une photo JPEG : « AAAA-MM-JJ » ou ''.
        // Le serveur vérifie ensuite que la date est plausible.
        var dateDansExif = function (v, tiff) {
            var petitBoutiste = v.getUint16(tiff) === 0x4949;
            var balises = function (ifd, recherchees) {
                var trouvees = {};
                var nombre = v.getUint16(ifd, petitBoutiste);
                for (var k = 0; k < nombre; k++) {
                    var entree = ifd + 2 + k * 12;
                    var balise = v.getUint16(entree, petitBoutiste);
                    if (recherchees.indexOf(balise) !== -1) { trouvees[balise] = v.getUint32(entree + 8, petitBoutiste); }
                }
                return trouvees;
            };
            var ifd0 = balises(tiff + v.getUint32(tiff + 4, petitBoutiste), [0x8769]);
            if (ifd0[0x8769] === undefined) { return ''; }
            var exif = balises(tiff + ifd0[0x8769], [0x9003, 0x9004]);
            var decalage = exif[0x9003] !== undefined ? exif[0x9003] : exif[0x9004];
            if (decalage === undefined) { return ''; }
            var texte = '';
            for (var i = 0; i < 10; i++) { texte += String.fromCharCode(v.getUint8(tiff + decalage + i)); }
            var m = /^(\d{4}):(\d{2}):(\d{2})$/.exec(texte);
            return m ? m[1] + '-' + m[2] + '-' + m[3] : '';
        };

        var lireDate = function (fichier) {
            if (fichier.type !== 'image/jpeg') { return Promise.resolve(''); }
            return lireOctets(fichier.slice(0, 262144)).then(function (tampon) {
                var v = new DataView(tampon);
                if (v.byteLength < 4 || v.getUint16(0) !== 0xFFD8) { return ''; }
                var pos = 2;
                while (pos + 10 <= v.byteLength) {
                    var marqueur = v.getUint16(pos);
                    if ((marqueur & 0xFF00) !== 0xFF00 || marqueur === 0xFFDA) { return ''; }
                    if (marqueur === 0xFFE1 && v.getUint32(pos + 4) === 0x45786966) { return dateDansExif(v, pos + 10); } // « Exif »
                    pos += 2 + v.getUint16(pos + 2);
                }
                return '';
            }).catch(function () { return ''; });
        };

        var alleger = function (fichier) {
            return new Promise(function (resoudre) {
                if (!/^image\/(jpeg|png|webp)$/.test(fichier.type) || fichier.size < 500 * 1024) { resoudre(fichier); return; }
                var adresse = URL.createObjectURL(fichier);
                var img = new Image();
                img.onload = function () {
                    var ratio = Math.min(1, COTE_MAX / Math.max(img.naturalWidth, img.naturalHeight));
                    var toile = document.createElement('canvas');
                    toile.width = Math.max(1, Math.round(img.naturalWidth * ratio));
                    toile.height = Math.max(1, Math.round(img.naturalHeight * ratio));
                    var contexte = toile.getContext('2d');
                    contexte.fillStyle = '#fff';
                    contexte.fillRect(0, 0, toile.width, toile.height);
                    contexte.drawImage(img, 0, 0, toile.width, toile.height);
                    URL.revokeObjectURL(adresse);
                    toile.toBlob(function (blob) {
                        toile.width = 0; // libère la mémoire tout de suite (téléphones d'entrée de gamme)
                        toile.height = 0;
                        if (!blob || blob.size >= fichier.size) { resoudre(fichier); return; }
                        resoudre(new File([blob], fichier.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg', lastModified: fichier.lastModified }));
                    }, 'image/jpeg', 0.85);
                };
                img.onerror = function () { URL.revokeObjectURL(adresse); resoudre(fichier); };
                img.src = adresse;
            });
        };

        var occupe = function (oui) {
            Array.prototype.forEach.call(boutons, function (b) { b.disabled = oui; });
        };

        champPhotos.addEventListener('change', function () {
            var choisies = Array.prototype.slice.call(champPhotos.files);
            var avertissement = '';
            if (choisies.length > restantes) {
                avertissement = ' Seules les ' + restantes + ' premières sont gardées : la fiche est limitée en nombre de photos.';
                choisies = choisies.slice(0, restantes);
            }
            apercus.innerHTML = '';
            zoneDates.innerHTML = '';
            if (!choisies.length) { statut.textContent = aideInitiale; return; }

            occupe(true);
            var pretes = [];
            var avant = 0;
            var apres = 0;
            var suite = Promise.resolve();
            // Une photo après l'autre : traitées ensemble, de grandes photos saturent la mémoire d'un petit téléphone.
            choisies.forEach(function (fichier, i) {
                suite = suite.then(function () {
                    statut.textContent = 'Préparation des photos… ' + (i + 1) + ' sur ' + choisies.length;
                    return Promise.all([lireDate(fichier), alleger(fichier)]);
                }).then(function (resultat) {
                    pretes.push({ fichier: resultat[1], date: resultat[0] });
                    avant += fichier.size;
                    apres += resultat[1].size;
                });
            });
            suite.then(function () {
                var transfert = new DataTransfer();
                pretes.forEach(function (p) { transfert.items.add(p.fichier); });
                champPhotos.files = transfert.files;
                pretes.forEach(function (p, i) {
                    if (p.date) {
                        var date = document.createElement('input');
                        date.type = 'hidden';
                        date.name = 'photos_date[' + i + ']';
                        date.value = p.date;
                        zoneDates.appendChild(date);
                    }
                    var item = document.createElement('li');
                    var vignette = document.createElement('img');
                    vignette.alt = '';
                    vignette.onload = function () { URL.revokeObjectURL(vignette.src); };
                    vignette.src = URL.createObjectURL(p.fichier);
                    var legende = document.createElement('span');
                    legende.textContent = poids(p.fichier.size);
                    item.appendChild(vignette);
                    item.appendChild(legende);
                    apercus.appendChild(item);
                });
                statut.textContent = pretes.length + (pretes.length > 1 ? ' photos prêtes' : ' photo prête') + ' : ' + poids(apres)
                    + (apres < avant ? ' à envoyer au lieu de ' + poids(avant) : '') + '. Enregistrez pour les ajouter.' + avertissement;
            }).catch(function () {
                // En cas d'échec, les photos d'origine partent telles quelles : le serveur les réduit.
                zoneDates.innerHTML = '';
                apercus.innerHTML = '';
                statut.textContent = aideInitiale;
            }).then(function () { occupe(false); });
        });
    }
})();
