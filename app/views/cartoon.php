<?php include 'header.php' ?>

    <div class="col-sm-9">

        <div class="card">
            <div class="card-header">
                <h1><?=$cartoon->title?></h1>
            </div>
            <div class="card-body">
                <img src="<?=$cartoon->image?>" align="left" class="mr-2">
                <?php //$cartoon->display_information() ?>
                <p><b>Summary:</b> <?=$cartoon->synopsis?></p>

                <h3>Videos</h3>
                <div class="row">
                    <ul>
                    <?php
                    $episodes = $cartoon->getEpisodes();
                    foreach ($episodes as $episode){
                        echo "<li><a href='{$episode->url()}'>{$episode->name()}</a></li>";
                    }
                    ?>
                    </ul>
                </div>
            </div>

        </div>

    </div>

<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>