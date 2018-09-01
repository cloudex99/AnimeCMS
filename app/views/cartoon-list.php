<?php include 'header.php' ?>

    <div class="col-sm-9">

        <div class="card">
            <div class="card-header">Cartoon List</div>
            <div class="card-body">
                <ul class='list-inline text-center'>

                </ul>

                <form class="form-inline" id="anime_filter" method="POST" action="/paginate">
                    <input type="hidden" name="model" value="Cartoon">
                    <div class="form-group mx-sm-1 mb-2">
                        <button type="button" class="btn btn-default btn-outline-secondary dropdown-toggle"
                                data-toggle="dropdown" data-flip="false"><span class="fa fa-cog"></span> <span class="caret"></span> Genre
                        </button>
                        <ul class="dropdown-menu p-3" id="gcont" style="max-width: 700px">
                            <?php
                            (function () {
                                $genres = Cartoon::genres();
                                foreach ($genres as $genre) {
                                    echo "<li class='w-25 float-left form-check justify-content-start'><input class='mr-1' type='checkbox' id='genre-$genre' name='genre[]' value='$genre'/><label for='genre-$genre' class='form-check-label'>$genre</label></li>";
                                }
                            })();
                            ?>
                        </ul>
                    </div>
                    <div class="form-group mb-2">
                        <select id="type-selector" name="type" class="custom-select my-1 mr-sm-1">
                            <option selected="" value="0">Type: All</option>
                            <option value="tv">TV</option>
                            <option value="movie">Movie</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <select id="status-selector" name="status" class="custom-select my-1 mr-sm-1">
                            <option selected="" value="0">Status: All</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <select id="order-selector" name="order" class="custom-select my-1 mr-sm-1">
                            <option selected="" value="title,asc">Sort: Default</option>
                            <option value="title,asc">A-Z Asc</option>
                            <option value="title,desc">A-Z Desc</option>
                            <option value="date,asc">Date Asc</option>
                            <option value="date,desc">Date Desc</option>
                        </select>
                    </div>

                    <div class="form-group mx-sm-1 mb-2">
                        <select id="letter-selector" name="letter" class="custom-select my-1 mr-sm-1">
                            <option selected="" value="all">Letter: All</option>
                            <?php
                            for ($i = 65; $i < 91; $i++) {
                                $l = chr($i);
                                echo "<option value='$l'>$l</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary mb-2">Filter</button>
                </form>

                <ul class='sync-pagination pagination flex-wrap' data-pages='<?=ceil(Cartoon::count()/30)?>' data-page='1' data-size='30' data-query='' data-total='<?=Cartoon::count()?>'></ul>
                <ul class="list-unstyled" id="anime_grid"></ul>
                <div class="row">
                    <div class="mx-auto" id="spinner" style="display: none"></div>
                </div>
                <ul class='sync-pagination pagination flex-wrap' data-pages='<?=ceil(Cartoon::count()/30)?>' data-page='1' data-size='30' data-query='' data-total='<?=Cartoon::count()?>'></ul>
            </div>
        </div>

    </div>

<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>