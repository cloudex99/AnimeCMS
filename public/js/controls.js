(function ($, document, window) {
    $(document).ready(function () {

        $('.paginate').on('click', 'a',function () {

            var data_cont = $('#episodes_subbed');

            var pages = parseInt(data_cont.attr('data-pages'));
            var type = data_cont.attr('data-type');
            var page = parseInt(data_cont.attr('data-page'));
            var size = data_cont.attr('data-size');
            var one = parseInt($(this).attr('data-one'));

            page = page+one;

            if(page <= pages && page > 0) {

                data_cont.attr('data-page', page);
                var send = {"type":type,"page":page,"size":size};
                $.ajax({
                    type: "POST",
                    url: '/paginate',
                    dataType: "json",
                    data: send,
                    success: function (data)
                    {
                        data_cont.html("");
                        data.forEach(function (episode) {
                            data_cont.append("<li><a href='"+episode.url+"'>"+episode.name.default+"</a></li>");
                        });
                        console.log(data);
                    },
                    statusCode: {
                        404: function() {
                            console.log("no data");
                        }
                    }
                });
            }

        });

    });
})(jQuery, document, window);