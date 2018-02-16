<?php include 'header.php' ?>

<div class="col-sm-9">

    <div class="card">
        <div class="card-header">Anime List</div>
        <div class="card-body">
            <ul class='list-inline text-center'>
                <a class='btn btn-sm' href='#'>#</a>
                <?php
                for ($i = 65; $i < 91; $i++) {
                    $l = chr($i);
                    echo "<a class='btn btn-sm' href='#$l'>$l</a>";
                }
                ?>
            </ul>

            <ul class="list-unstyled">
                <h3 id='#'>#</h3>
                <?php
                    $list = Anime::query();
                    foreach ($list as $i => $anime){
                        $first = strtoupper($anime->name()[0]);
                        if(isset($list[$i-1])){
                            $next = strtoupper($list[$i-1]->name()[0]);
                        } else {
                            continue;
                        }
                        if($first!==$next && ctype_alpha($first)){
                            echo "<h3 id='$first'>$first</h3>";
                        }
                        echo "<li class='w-50 d-inline-block'><a href='{$anime->url()}' title='Watch {$anime->name()}'>{$anime->name()}</a></li>";
                    }
                ?>

            </ul>
        </div>
    </div>

</div>

<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>