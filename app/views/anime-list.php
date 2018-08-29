<?php include 'header.php' ?>

<div class="col-sm-9">

    <div class="card">
        <div class="card-header">Anime List</div>
        <div class="card-body">
            <ul class='list-inline text-center'>

            </ul>

            <form class="form-inline" id="anime_filter" method="POST" action="/paginate">

                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class='btn btn-secondary'>
                        <input type='radio' name='letter' id='letter-num' autocomplete='off' value='all'> All
                    </label>
                    <label class='btn btn-secondary'>
                        <input type='radio' name='letter' id='letter-num' autocomplete='off' value='#'> #
                    </label>
                    <?php
                    for ($i = 65; $i < 91; $i++) {
                        $l = chr($i);
                        echo "<label class='btn btn-secondary'>
                                <input type='radio' name='letter' id='letter-$l' autocomplete='off' value='$l'> $l
                              </label>";
                    }
                    ?>
                </div>

                <div class="form-group mx-sm-1 mb-2">
                    <button type="button" class="btn btn-default btn-outline-secondary dropdown-toggle" data-toggle="dropdown"><span class="fa fa-cog"></span> <span class="caret"></span> Genre</button>
                    <ul class="dropdown-menu p-3" id="gcont" style="width: 600px">
                        <?php
                        (function() {
                            $genres = Anime::genres();
                            foreach ($genres as $genre){
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
                        <option value="ova">OVA</option>
                        <option value="ona">ONA</option>
                        <option value="special">Special</option>
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
                    <select id="language-selector" name="lang" class="custom-select my-1 mr-sm-1">
                        <option selected="" value="0">Language: All</option>
                        <option value="eng">English</option>
                        <option value="jap">Japanese</option>
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

                <button type="submit" class="btn btn-primary mb-2">Filter</button>
            </form>

            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    var anime_grid = $('#anime_grid');
                    var anime_form =  $('#anime_filter');
                    var anime_pag = $('#anime_pagination');

                    //Start anime filter code
                    anime_form.on('submit', function (e) {
                        e.preventDefault();

                        loadSpinnerDOM();

                        console.log();

                        var type = $('#type-selector').val();
                        var status = $('#status-selector').val();
                        var lang = $('#language-selector').val();
                        var order = $('#order-selector').val();
                        var letter = $('input[name=letter]:checked').val();
                        var genre = '';
                        $("#gcont").find("input[type=checkbox]:checked").each(function() {
                            genre +=  $(this).val()+',' ;
                        });
                        genre = genre.replace(/,\s*$/, "");

                        anime_grid.html("");
                        $('#spinner').show().jmspinner('large');

                        $.ajax({
                            type: "POST",
                            url: '/paginate',
                            dataType: "json",
                            data: {
                                "query": {
                                   "type": type,
                                   "status": status,
                                    "lang": lang,
                                    "order": order,
                                    "genre": genre
                                },
                                "type": "anime_query",
                                "size": anime_pag.attr('data-size'),
                                "letter": letter
                            },
                            success: function (data) {
                                var pages = data.pages;
                                anime_pag.attr('data-pages', pages);
                                anime_pag.attr('data-query', data.query);
                                console.log(data.query);
                                data.results.forEach(function (anime) {
                                    var add = '<li class="w-25 d-inline-block">\n' +
                                        '         <a href="' + anime.url + '"><img src="' + anime.image + '" class="w-100"></a>\n' +
                                        '         <p><small>' + anime.title + '</small></p>\n' +
                                        '      </li>';
                                    $("#anime_grid").append(add);
                                });
                                $('#spinner').hide().jmspinner(false);


                                var totalPages = data.pages;

                                anime_pag.twbsPagination('destroy');
                                anime_pag.twbsPagination($.extend({}, pagdefaults, {
                                    startPage: 1,
                                    totalPages: totalPages
                                }));
                            },
                            statusCode: {
                                404: function () {
                                    console.log("page not found");
                                }
                            }
                        });
                        return false;

                    });
                    anime_form.submit();
                    //End anime filter code


                    var pagdefaults = {
                        totalPages: anime_pag.attr('data-pages'),
                        visiblePages: 5,
                        next: 'Next',
                        prev: 'Prev',
                        onPageClick: function (event, page) {
                            var type = 'anime_query';
                            var size = anime_pag.attr('data-size');
                            var query = anime_pag.attr('data-query');
                            var letter = $('input[name=letter]:checked').val();
                            var send = {"type":type,"page":page,"size":size,"query":query,"letter":letter};
                            console.log(query);
                            anime_grid.html("");
                            $('#spinner').show().jmspinner('large');
                            $.ajax({
                                type: "POST",
                                url: '/paginate',
                                data: send,
                                success: function (data)
                                {
                                    data.results.forEach(function (anime) {
                                        var add = '<li class="w-25 d-inline-block">\n' +
                                            '         <a href="' + anime.url + '"><img src="' + anime.image + '" class="w-100"></a>\n' +
                                            '         <p><small>' + anime.title + '</small></p>\n' +
                                            '      </li>';
                                        $("#anime_grid").append(add);
                                    });
                                    $('#spinner').hide().jmspinner(false);
                                },
                                statusCode: {
                                    404: function() {
                                        console.log("no data");
                                    }
                                }
                            });
                        }
                    };

                    anime_pag.twbsPagination(pagdefaults);
                });

            </script>
            <div class="row"><div class="mx-auto" id="spinner" style="display: none"></div></div>
            <ul class="list-unstyled" id="anime_grid"></ul>
            <ul id='anime_pagination' class='pagination-sm' data-pages='136' data-page='1' data-size='24' data-query=''></ul>
        </div>
    </div>

</div>

<?php include 'sidebar.php' ?>
<?php include 'footer.php' ?>