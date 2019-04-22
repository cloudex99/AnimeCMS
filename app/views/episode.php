<?php include 'header.php' ?>

<div class="col-sm-9">
    <div class="card">
        <div class="card-header"><h1><?=$episode->name(true)?></h1></div>

        <div class="card-body">
            <div class="btn-group mb-1" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Video Mirrors</button>
                <div id="mirrors" class="dropdown-menu" aria-labelledby="btnGroupDrop1"></div>
            </div>
            <div class="nav nav-tabs float-right" id="parts"></div>
            <div class="clearfix"></div>
            <div id="player" class="embed-container" style="min-height: 300px"></div>

            <div class="clearfix m-1">
                <?php if ($episode->previous()) echo "<a class='btn btn-sm text-small btn-primary float-left' href='{$episode->previous()->url(true)}'>Prev (Ep {$episode->previous()->number})</a>" ?>

                <?php if ($episode->next()) echo "<a class='btn btn-sm text-small btn-primary float-right' href='{$episode->next()->url(true)}'>Next (Ep {$episode->next()->number})</a>" ?>
            </div>

            <p><?=$episode->date()?></p>
            <p><?=$episode->description?></p>
        </div>
    </div>
    <script type="text/javascript">
        <?php $episode->lang = Functions::matchSlug('subbed') ? 'subbed' : 'dubbed'; $episode->slug = $episode->slug();?>
        var episode = <?=json_encode($episode)?>;
        fetch('https://vid.xngine.com/api/episode/' + episode.slug)
            .then(response => {
            if (response.ok) return response.json();
        throw new Error('Network response was not ok.')
        })
        .then(function (data) {
            if (data.length > 0) {
                let video = data[0];
                episode.videos.push(video);
            }
        });
    </script>
</div>


<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>