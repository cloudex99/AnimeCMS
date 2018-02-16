<?php include 'header.php' ?>

    <div class="col-sm-9">

        <div class="card">
            <div class="card-header">Ongoing Anime</div>
            <div class="card-body">

                <ul class="list-unstyled">

                    <?php
                    $lists = Anime::ongoing();
                    foreach ($lists as $anime){
                        echo "<li class='w-50 d-inline-block'><a href='{$anime->url()}' title='Watch {$anime->name()}'>{$anime->name()}</a></li>";
                    }
                    ?>

                </ul>
            </div>
        </div>

    </div>

<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>