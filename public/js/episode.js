(function ($, document, window) {
    $(document).ready(function () {
        var player_cont = $('#player');

        var embed = function (host, id, p) {
            p = (typeof p !== 'undefined') ? p : 0;
            var no_disp = '';
            if (p > 1) {
                no_disp = "' style='display:none'";
            }

            var code;
            if (host === 'trollvid') {
                code = "<iframe " + no_disp + " src='//trollvid.net/embed/" + id + "' frameborder='0' allowfullscreen='true' scrolling='no'></iframe>";
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

        function display_video(video) {

            var ids = video['id'].split(',');
            player_cont.html('');
            if (ids.length > 1) {
                ids.forEach(function (id, i) {
                    i++;
                    $('#parts').append('<a class="part nav-item nav-link pt-1" href="#" data-id="' + id + '" data-host="' + video["host"] + '">Part ' + i + '</a>');
                    player_cont.append(embed(video['host'], id, i));
                });

            } else {
                player_cont.append(embed(video['host'], video['id']));
            }

        }

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
            player_cont.html(embed(host, id));
        });

        $('#theater').click(function () {
            $('#ep-cont').toggleClass( 'col-md-12', 'col-md-8' );
            $('#sidebar').toggle();
            $(this).html($('#theater').text() == ' Theater Mode' ? '<i class="fas fa-columns"></i> Normal Mode' : '<i class="fas fa-tv"></i> Theater Mode');
        });

        $('#refresh').click(function () {
            display_video(episode_videos[$(this).attr("data-index")]);
        });

    });
})(jQuery, document, window);