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

        //Start genres code
        $('#genres_form').on('submit', function (e) {
            e.preventDefault();

            loadSpinnerDOM();

            var selected = false;
            $('#genres_form input:checked').each(function () {
                selected = true;
            });

            if (selected) {
                $("#genres_grid").html("");
                $('#spinner').show().jmspinner('large');
            }

            $.ajax({
                type: "POST",
                url: '/genres',
                dataType: "json",
                data: $(this).serialize(),
                success: function (data) {
                    data.forEach(function (anime) {
                        var add = '<li class="w-25 d-inline-block">\n' +
                            '         <a href="' + anime.url + '"><img src="' + anime.image + '" class="w-100"></a>\n' +
                            '         <p><small>' + anime.title + '</small></p>\n' +
                            '      </li>';
                        $("#genres_grid").append(add);
                    });
                    $('#spinner').hide().jmspinner(false);
                },
                statusCode: {
                    404: function () {
                        console.log("page not found");
                    }
                }
            });
            return false;

        });
        //End genres code

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
                } else if (host === 'facebook') {
                    code = "<iframe " + no_disp + " src='https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Flayfon.alseif.16%2Fvideos%2F" + id + "%2F' frameborder='0' allowfullscreen='true' scrolling='no'></iframe>"
                } else if (host === 'upload2') {
                    code = "<iframe " + no_disp + " src='//upload2.com/embed/" + id + "' frameborder='0' allowfullscreen='true' scrolling='no' height='" + h + "' width='" + w + "'></iframe>";
                } else {
                    console.log('unsupported host');
                }

                return code;
            };

            episode_videos.forEach(function (video, i) {
                $('#mirrors').append("<a href='#' class='dropdown-item text-capitalize' data-index='" + i + "'>" + (++i) + ". " + video['host'] + " - " + video['type'] + " </a>");
            });

            setTimeout(function () {
                display_video(episode_videos[0]);
            }, 500);

            $('#mirrors').on('click', 'a', function () {
                $('#parts').html('');
                $('#refresh').attr("data-index", $(this).attr("data-index"));
                display_video(episode_videos[$(this).attr("data-index")]);
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
                display_video(episode_videos[$(this).attr("data-index")]);
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
        }
        //End video player code

    });
})(jQuery, document, window);

function loadSpinnerDOM() {
    $('<script />', {type: 'text/javascript', src: '/js/jm.spinner.js'}).appendTo('head');
    $('<link/>', {rel: 'stylesheet', href: '/css/jm.spinner.css'}).appendTo('head');
}

