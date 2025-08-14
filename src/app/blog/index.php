<?php

use Lib\Prisma\Classes\Prisma;

$prisma = Prisma::getInstance();

$page = $prisma->studioPage->findFirst([
    'where' => [
        'name' => 'Blog'
    ]
]);

function saveForm($data) {
    return $data;
}

?>

<style>
    <?= $page->css ?? '' ?>
</style>

<?= $page->html ?? '' ?>

<script>
    const [myVar, setMyVar] = pphp.state('John Doe 2');

    export async function saveFormToDB() {
        const responseData = await pphp.fetchFunction('saveForm', { myVar });
        console.log("🚀 ~ saveFormToDB ~ responseData:", responseData)
    }
</script>