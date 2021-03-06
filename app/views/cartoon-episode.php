<?php include 'header.php' ?>

    <div class="col-sm-9">
        <div class="card">
            <div class="card-header"><h1><?=$episode->name()?></h1></div>

            <img src="<?=$episode->image()?>" alt="">
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
            var episode_videos = <?php echo json_encode($episode->videos)?>;

            // addEventListener support for IE8
            function bindEvent(element, eventName, eventHandler) {
                if (element.addEventListener){
                    element.addEventListener(eventName, eventHandler, false);
                } else if (element.attachEvent) {
                    element.attachEvent('on' + eventName, eventHandler);
                }
            }

            // Listen to message from child window
            bindEvent(window, 'message', function (e) {
                console.log(e.data);
            });
        </script>
    </div>


<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>