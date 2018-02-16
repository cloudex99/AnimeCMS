<?php include 'header.php' ?>

<div class="col-sm-9">

    <div class="card">
        <div class="card-header">Latest Episode</div>
        <div class="card-body">
            <div class="row">

                <div class="col-sm-6">
                    <h6>Subbed</h6>
                    <?php $subbed = Episode::latest('subbed', 24);?>
                    <ul class="list-unstyled">
                        <?php
                        foreach ($subbed as $i => $episode){
                            echo "<li><a href='{$episode->url('subbed')}'>{$episode->name()}</a></li>";
                        }
                        ?>
                    </ul>
                </div>

                <div class="col-sm-6">
                    <h6>Dubbed</h6>
                    <ul class="list-unstyled">
                        <?php
                        $dubbed = Episode::latest('dubbed', 6);
                            foreach ($dubbed as $episode){
                                echo "<li>
                                        <a href='{$episode->url('dubbed')}' class='d-block'><img src='{$episode->image()}' width='300'></a>
                                        <p><small>{$episode->name()}</small></p>
                                      </li>";
                            }
                        ?>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <br>

    <div class="card">
        <div class="card-header">Latest Anime</div>
        <div class="card-body">
            <ul class="list-unstyled row">
                <?php
                    $animes = Anime::latest('latest', 'subbed', 12);
                    foreach ($animes as $anime){
                        echo "<li class='w-25'>
                                <a href='{$anime->url()}'><img src='$anime->image' class='w-100'></a>
                                <p><small>{$anime->name()}</small></p>
                              </li>";
                    }
                ?>
            </ul>
        </div>
    </div>

</div>


<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>

