<?php include 'header.php' ?>

<div class="col-sm-9">

    <div class="card">
        <div class="card-header">
            <h1><?=$anime->name()?></h1>
        </div>
        <div class="card-body">
            <img src="<?=$anime->image?>" align="left" class="mr-2">
            <?php $anime->display_information() ?>
            <p><b>Summary:</b> <?=$anime->synopsis?></p>

            <?php
            $episodes = $anime->getEpisodes();
            foreach ($episodes as $episode){
                if($episode->hasSubbed())
                    $sub_li .= "<li><a href='{$episode->url('subbed')}'>{$episode->name()} English Sub</a></li>";
                if($episode->hasDubbed())
                    $dub_li .= "<li><a href='{$episode->url('dubbed')}'>{$episode->name()} English Dub</a></li>";
            }
            ?>
            <h3>Videos</h3>
            <div class="row">
                <div class="col-sm-6">
                    <h5>Subbed</h5>
                    <ul class="list-unstyled ">
                        <?=$sub_li?>
                    </ul>
                </div>
                <div class="col-sm-6">
                    <h5>Dubbed</h5>
                    <ul class="list-unstyled">
                        <?=$dub_li?>
                    </ul>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>