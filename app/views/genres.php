<?php include 'header.php' ?>

<?php
$genres = ['action','adventure','cars','comedy','dementia','demons','drama','ecchi','fantasy','game','harem','historical','horror','josei','kids','magic',
    'martial arts','mecha','military','music','mystery','parody','police','psychological','romance','samurai','school','sci-fi','seinen','shoujo','shoujo ai',
    'shounen','shounen ai','slice of life','space','sports','super power','supernatural','thriller','vampire','yuri'];
?>

    <div class="col-sm-9">

        <div class="card">
            <div class="card-header">Genres</div>
            <div class="card-body">

                <form id="genres_form" class="w-100 d-inline-block text-center mb-4" method="post">
                    <div class="text-left">
                        <?php foreach ($genres as $genre): ?>
                            <div class="form-check form-check-inline" style="width: 120px; margin-left: 10%">
                                <input class="form-check-input" type="checkbox" name='genres[]' id="<?=$genre?>" value="<?=$genre?>">
                                <label class="form-check-label text-capitalize" for="<?=$genre?>"><?=$genre?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input class="btn btn-primary" type="submit" value="Filter">
                </form>

                <div class="row"><div class="mx-auto" id="spinner" style="display: none"></div></div>
                <ul class="list-unstyled" id="genres_grid"></ul>
            </div>
        </div>

    </div>

<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>