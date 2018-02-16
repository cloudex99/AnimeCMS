<div class="col-sm-3">

    <div class="card">
        <div class="card-header">Ongoing</div>
        <div class="card-body">
            <ul class="list-unstyled">
                <?php
                    $ongoing = Anime::ongoing(true); //Gives the subbed ongoing list. Use Anime::ongoing(true); for dubbed
                    foreach ($ongoing as $anime){
                        echo "<li><small><a href='{$anime->url()}'>{$anime->name()}</a></small></li>";
                    }
                ?>
            </ul>
        </div>
    </div>

</div>