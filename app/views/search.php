<?php include 'header.php' ?>
    <!-- left -->
    <div class='col-md-9'>

        <div class='card'>
            <div class='card-header'><h1 class='h3'><?=$_GET['term']?></h1></div>
            <div class='card-body'>
                <ul class='list-inline'>
                    <?php

                    (function() {
                        if(isset($_GET['cartoon'])){
                            $list = Cartoon::search($_GET['term']);
                        } else {
                            $list = Anime::search($_GET['term']);
                        }

                        foreach ($list as $item){
                            echo "<li class='w-50 d-inline-block'><a href='{$item->url()}'>{$item->name()}</a>";
                        }
                    })();

                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>