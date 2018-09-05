<?php include 'header.php' ?>

<div class="col-sm-9">

    <div class="alert alert-success">
         <a href="https://github.com/cloudex99/animecms">Download source code from github.</a>
    </div>

    <div class="card">
        <div class="card-header">Latest Episode</div>
        <div class="card-body">
            <div class="row">

                <div class="col-sm-6">
                    <h6>Subbed</h6>
                    <ul class="list-unstyled">
                        <?php
                        (function () {
                            $episodes = Episode::latest('subbed', 24);
                            foreach ($episodes as $episode){
                                echo "<li><a href='{$episode->url('subbed')}'>{$episode->name()}</a></li>";
                            }
                        })();
                        ?>
                    </ul>
                </div>

                <div class="col-sm-6">
                    <h6>Dubbed</h6>
                    <ul class="list-unstyled">
                        <?php
                        (function () {
                            $episodes = Episode::latest('dubbed', 6);
                            foreach ($episodes as $episode){
                                echo "<li>
                                        <a href='{$episode->url('dubbed')}' class='d-block'><img src='{$episode->image()}' width='300'></a>
                                        <p><small>{$episode->name()}</small></p>
                                      </li>";
                            }
                        })();
                        ?>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <h6>Latest Cartoons</h6>
                    <ul class="list-unstyled">
                        <?php
                        (function () {
                            $cartoons = Cartoon::latest( 12);
                            foreach ($cartoons as $cartoon){
                                echo "<li><a href='{$cartoon->url()}'>{$cartoon->name()}</a></li>";
                            }
                        })();
                        ?>
                    </ul>
                </div>

                <div class="col-sm-6">
                    <h6>Recent Cartoon Episodes</h6>
                    <ul class="list-unstyled">
                        <?php
                        (function () {
                            $episodes = CartoonEpisode::latest( 12);
                            foreach ($episodes as $episode){
                                echo "<li><a href='{$episode->url()}'>{$episode->name()}</a></li>";
                            }
                        })();
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

