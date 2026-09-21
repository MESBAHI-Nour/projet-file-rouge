<?php
/**
 * Pied de page commun a toutes les pages.
 *
 * $scripts : liste des fichiers JavaScript a charger (dans assets/js/).
 *            Par defaut, aucun : la galerie publique n'en a pas besoin.
 */
declare(strict_types=1);

$cheminBase = $cheminBase ?? '.';
$scripts    = $scripts    ?? [];
?>
</main>

<footer class="pied">
    <div class="conteneur">
        <p>Plateforme de vente de photos &mdash; projet fil rouge <?= date('Y') ?></p>
    </div>
</footer>

<?php foreach ($scripts as $script): ?>
    <script src="<?= e($cheminBase . '/assets/js/' . $script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
