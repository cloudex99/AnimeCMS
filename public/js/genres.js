(function ($, document, window) {
    $(document).ready(function () {
        var head = document.getElementsByTagName("head")[0],
            cssLink = document.createElement("link");
        cssLink.href = "/css/jm.spinner.css";
        cssLink.rel="stylesheet";
        head.appendChild(cssLink);

        $('#genres_form').on('submit', function (e) {
            e.preventDefault();

            var selected = false;
            $('#genres_form input:checked').each(function() {
                selected = true;
            });

            if(selected){
                $("#genres_grid").html("");
                $('#spinner').show().jmspinner('large');
            }

            $.ajax({
                type: "POST",
                url: '/genres',
                dataType: "json",
                data: $(this).serialize(),
                success: function (data)
                {
                    data.forEach(function(anime) {
                        var add = '<li class="w-25 d-inline-block">\n' +
                            '         <a href="'+anime.url+'"><img src="'+anime.image+'" class="w-100"></a>\n' +
                            '         <p><small>'+anime.title+'</small></p>\n' +
                            '      </li>';
                        $("#genres_grid").append(add);
                    });
                    $('#spinner').hide().jmspinner(false);
                },
                statusCode: {
                    404: function() {
                        console.log("page not found");
                    }
                }
            });
            return false;

        });

    });
})(jQuery, document, window);