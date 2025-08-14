<?php

use Lib\Prisma\Classes\Prisma;

$prisma = Prisma::getInstance();

$page = $prisma->studioPage->findFirst([
    'where' => [
        'name' => 'Web'
    ]
]);

?>

<style>
    <?= $page->css ?? '' ?>
</style>

<?= $page->html ?? '' ?>