(function ($, document, window) {
    $(document).ready(function () {

        $('.paginate').on('click', 'a', function () {
            var target = $(this).attr('data-target');
            var data_cont = $('#' + target);
            var pages = parseInt(data_cont.attr('data-pages'));
            var type = data_cont.attr('data-type');
            var page = parseInt(data_cont.attr('data-page'));
            var size = data_cont.attr('data-size');
            var one = parseInt($(this).attr('data-one'));
            page = page + one;
            if (page <= pages && page > 0) {
                data_cont.attr('data-page', page);
                var send = {"type": type, "page": page, "size": size};
                $.ajax({
                    type: "POST",
                    url: '/paginate',
                    data: send,
                    success: function (data) {
                        data_cont.hide();
                        data_cont.html(data);
                        data_cont.fadeIn(900);
                    },
                    statusCode: {
                        404: function () {
                            console.log("no data");
                        }
                    }
                });
            }
        });

        //Check to see if the window is top if not then display button
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('.scrollToTop').fadeIn();
            } else {
                $('.scrollToTop').fadeOut();
            }
        });

        //Click event to scroll to top
        $('.scrollToTop').click(function () {
            $('html, body').animate({scrollTop: 0}, 300);
            return false;
        });

        //Start video player code
        var player = $('#player');
        if (player.length) {
            var embed = function (host, id, p) {
                p = (typeof p !== 'undefined') ? p : 0;
                var no_disp = '';
                if (p > 1) {
                    no_disp = "' style='display:none'";
                }

                var code;
                if (host === 'trollvid') {
                    code = "<iframe " + no_disp + " src='//trollvid.net/embed/" + id + "' frameborder='0' allowfullscreen='true' scrolling='no'></iframe>";
                } else if (host === 'mp4.sh') {
                    code = "<iframe " + no_disp + " src='//trollvid.net/embedc/" + id + "' frameborder='0' allowfullscreen='true' scrolling='no'></iframe>";
                } else if (host === 'mp4upload') {
                    code = "<iframe " + no_disp + " src='//www.mp4upload.com/embed-" + id + ".html' FRAMEBORDER=0 MARGINWIDTH=0 MARGINHEIGHT=0 SCROLLING=NO WIDTH=1280 HEIGHT=720 allowfullscreen></iframe>";
                } else if (host === 'xstreamcdn') {
                    code = '<iframe ' + no_disp + ' src="https://www.xstreamcdn.com/v/'+ id +'" allowfullscreen="true" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>';
                } else if (host === 'vidstreaming') {
                    code = '<iframe ' + no_disp + ' src="//vidstreaming.io/streaming.php?id='+ id +'" allowfullscreen="true" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>';
                } else if (host === 'facebook') {
                    code = "<iframe " + no_disp + " src='https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Flayfon.alseif.16%2Fvideos%2F" + id + "%2F' frameborder='0' allowfullscreen='true' scrolling='no'></iframe>"
                } else if (host === 'upload2') {
                    code = "<iframe " + no_disp + " src='//upload2.com/embed/" + id + "' frameborder='0' allowfullscreen='true' scrolling='no' height='" + h + "' width='" + w + "'></iframe>";
                } else {
                    console.log('unsupported host');
                }

                return code;
            };

            episode.videos.forEach(function (video, i) {
                $('#mirrors').append("<a href='#' class='dropdown-item text-capitalize' data-index='" + i + "'>" + (++i) + ". " + video['host'] + " - " + video['type'] + " </a>");
            });

            setTimeout(function () {
                display_video(episode.videos[0]);
            }, 500);

            $('#mirrors').on('click', 'a', function () {
                $('#parts').html('');
                $('#refresh').attr("data-index", $(this).attr("data-index"));
                display_video(episode.videos[$(this).attr("data-index")]);
            });

            $('#parts').on('click', 'a', function () {
                var host = $(this).attr("data-host");
                var id = $(this).attr("data-id");
                $('#parts').find('a').removeClass('active');
                $(this).addClass('active');
                player.html(embed(host, id));
            });

            $('#theater').click(function () {
                $('#ep-cont').toggleClass('col-md-12', 'col-md-8');
                $('#sidebar').toggle();
                $(this).html($('#theater').text() == ' Theater Mode' ? '<i class="fas fa-columns"></i> Normal Mode' : '<i class="fas fa-tv"></i> Theater Mode');
            });

            $('#refresh').click(function () {
                display_video(episode.videos[$(this).attr("data-index")]);
            });

            function display_video(video) {
                var ids = video['id'].split(',');
                player.html('');
                if (ids.length > 1) {
                    ids.forEach(function (id, i) {
                        i++;
                        $('#parts').append('<a class="part nav-item nav-link pt-1" href="#" data-id="' + id + '" data-host="' + video["host"] + '">Part ' + i + '</a>');
                        player.append(embed(video['host'], id, i));
                    });

                } else {
                    player.append(embed(video['host'], video['id']));
                }
            }

            fetch('https://vid.xngine.com/api/episode/' + episode.slug)
                .then(response => {
                if (response.ok) return response.json();
            throw new Error('Network response was not ok.')
            }).then(function (data) {
                if (data.length > 0) {
                    let video = data[0];
                    episode.videos.push(video);
                    $('#mirrors').append("<a href='#' class='dropdown-item text-capitalize' data-index='" + (episode.videos.length-1) + "'>" + episode.videos.length + ". " + video['host'] + " - " + video['type'] + " </a>")
                }
            });
        }
        //End video player code

        //Search Form
        var sform = $('#search_form');
        $('#csbtn').on('click', function (e) {
            e.preventDefault();
            sform.attr("action", "/search?cartoon=true&term="+$('#search_value').val());
            window.location.href = sform.attr('action');
            return false;
        });

        $("#gcont").click(function(e){
            e.stopPropagation();
        });

        //Start anime/cartoon filter code
        if (onPage === 'animes' || onPage === 'cartoons') {

            var what = 'query';
            var model = $('input[name=model]').val();

            var anime_grid = $('#anime_grid');
            var anime_form = $('#anime_filter');
            var anime_pag = $('.sync-pagination');
            var loaded = false;

            anime_form.on('submit', function (e) {
                e.preventDefault();
                var type = $('#type-selector').val();
                var status = $('#status-selector').val();
                var lang = $('#language-selector').val();
                var order = $('#order-selector').val();
                var letter = $('#letter-selector').val();
                var genre = '';

                anime_grid.html("");
                loadSpinnerDOM();
                $('#spinner').show().jmspinner('large');

                $("#gcont").find("input[type=checkbox]:checked").each(function () {
                    genre += $(this).val() + ',';
                });
                genre = genre.replace(/,\s*$/, "");

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
                        "what": "query",
                        "model": model,
                        "size": anime_pag.attr('data-size'),
                        "letter": letter
                    },
                    success: function (data) {
                        var pages = data.pages;
                        anime_pag.attr('data-total', data.total);
                        anime_pag.attr('data-pages', pages);
                        anime_pag.attr('data-query', data.query);
                        data.results.forEach(function (anime) {
                            anime.english = anime.english || anime.title;
                            var add = '<li class="w-25 d-inline-block">\n' +
                                '         <a href="' + anime.url + '"><img src="' + anime.image + '" class="w-100"></a>\n' +
                                '         <p><small>' + anime.title + '</small></p>\n' +
                                '      </li>';
                            $("#anime_grid").append(add);
                        });
                        $('#spinner').hide().jmspinner(false);
                        if (loaded) {
                            var totalPages = data.pages;
                            anime_pag.twbsPagination('destroy');
                            anime_pag.twbsPagination($.extend({}, pagdefaults, {
                                startPage: 1,
                                totalPages: totalPages
                            }));

                        }
                        loaded = true;
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

            var pagdefaults = {
                totalPages: anime_pag.attr('data-pages'),
                visiblePages: 5,
                initiateStartPageClick: false,
                next: 'Next',
                prev: 'Prev',
                onPageClick: function (event, page) {
                    var letter = $('#letter-selector').val();
                    var size = anime_pag.attr('data-size');
                    var query = anime_pag.attr('data-query');
                    var send = {
                        "what": what,
                        "model": model,
                        "page": page,
                        "size": size,
                        "query": query,
                        "letter": letter
                    };
                    anime_grid.html("");
                    $('#spinner').show().jmspinner('large');
                    $.ajax({
                        type: "POST",
                        url: '/paginate',
                        data: send,
                        success: function (data) {
                            data.results.forEach(function (anime) {
                                anime.english = anime.english || anime.title;
                                var add = '<li class="w-25 d-inline-block">\n' +
                                    '         <a href="' + anime.url + '"><img src="' + anime.image + '" class="w-100"></a>\n' +
                                    '         <p><small>' + anime.title + '</small></p>\n' +
                                    '      </li>';
                                $("#anime_grid").append(add);
                            });
                            $('#spinner').hide().jmspinner(false);
                        },
                        statusCode: {
                            404: function () {
                                console.log("no data");
                            }
                        }
                    });
                }
            };
            anime_pag.twbsPagination(pagdefaults);
        }
        //End anime/cartoon filter code

        function loadSpinnerDOM() {
            $('<script />', {type: 'text/javascript', src: '/assets/js/jm.spinner.js'}).appendTo('head');
            $('<link/>', {rel: 'stylesheet', href: '/assets/css/jm.spinner.css'}).appendTo('head');
        }
    });
})(jQuery, document, window);

