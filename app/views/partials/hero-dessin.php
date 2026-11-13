<?php
/**
 * Dessin d'exécution d'un portique de hangar : la signature visuelle du site.
 * Les traits se tracent au chargement (classe .trace, délai via --d). Sans JavaScript ou avec
 * « réduire les animations », le dessin s'affiche directement.
 */
?>
<svg class="dessin" viewBox="0 0 640 450" role="img" aria-label="<?= t('home.hero.dessin') ?>">
    <!-- Sol -->
    <g class="dessin__fin">
        <path class="trace" style="--d:0" pathLength="1" d="M40 380H600"/>
        <path d="M60 380l-12 14M90 380l-12 14M180 380l-12 14M210 380l-12 14M240 380l-12 14M270 380l-12 14M300 380l-12 14M330 380l-12 14M360 380l-12 14M390 380l-12 14M420 380l-12 14M450 380l-12 14M550 380l-12 14M580 380l-12 14" opacity=".5"/>
    </g>

    <!-- Portique : poteaux, arbalétriers, jarrets -->
    <g class="dessin__fort">
        <path class="trace" style="--d:.15" pathLength="1" d="M120 372V170L320 90l200 80v202"/>
        <path class="trace" style="--d:.35" pathLength="1" d="M140 372V232l52-56 128-51 128 51 52 56v140"/>
        <path class="trace" style="--d:.7" pathLength="1" d="M140 186l52-10M500 186l-52-10M320 90v35"/>
        <path class="trace" style="--d:.8" pathLength="1" d="M106 372h48v8h-48zM486 372h48v8h-48z"/>
    </g>

    <!-- Pannes et lisses -->
    <g class="dessin__fin">
        <path class="trace" style="--d:.9" pathLength="1" d="M156 148h9v-8h-9zM196 132h9v-8h-9zM236 116h9v-8h-9zM276 100h9v-8h-9zM355 100h9v-8h-9zM395 116h9v-8h-9zM435 132h9v-8h-9zM475 148h9v-8h-9z"/>
        <path d="M120 240h-7v-9h7M120 300h-7v-9h7M520 240h7v-9h-7M520 300h7v-9h-7" opacity=".7"/>
        <path d="M320 60v30" stroke-dasharray="10 4 2 4" opacity=".6"/>
        <path d="M130 396v22M510 396v22" stroke-dasharray="10 4 2 4" opacity=".6"/>
    </g>

    <!-- Cotes -->
    <g class="dessin__cote">
        <path d="M130 418h380M130 412v12M510 412v12"/>
        <path d="M130 418l9-4v8zM510 418l-9-4v8z" fill="currentColor" stroke="none"/>
        <text x="320" y="441" text-anchor="middle">20 000</text>
        <path d="M70 170v202M64 170h12M64 372h12"/>
        <path d="M70 170l-4 9h8zM70 372l-4-9h8z" fill="currentColor" stroke="none"/>
        <text transform="rotate(-90 56 271)" x="56" y="271" text-anchor="middle">6 000</text>
        <path d="M576 90v282M570 90h12M570 372h12"/>
        <path d="M576 90l-4 9h8zM576 372l-4-9h8z" fill="currentColor" stroke="none"/>
        <text transform="rotate(-90 598 231)" x="598" y="231" text-anchor="middle">8 000</text>
    </g>

    <!-- Symbole de soudure d'angle sur le jarret -->
    <g class="dessin__accent">
        <path class="trace" style="--d:1.3" pathLength="1" d="M186 196l56 62h92"/>
        <path d="M186 196l3 12 6-6z" fill="currentColor" stroke="none"/>
        <path d="M268 258v-16l16 16"/>
        <text x="292" y="252">a6</text>
        <circle cx="166" cy="181" r="13" stroke-dasharray="3 4"/>
    </g>

    <!-- Personnage à l'échelle, casque orange -->
    <g class="dessin__fort dessin__personne">
        <path d="M408 380v-24M420 380v-24M414 356v-20M414 340l-9 12M414 340l9 12"/>
        <circle cx="414" cy="330" r="5"/>
    </g>
    <path class="dessin__casque" d="M407 328a7 7 0 0 1 14 0z"/>
</svg>
