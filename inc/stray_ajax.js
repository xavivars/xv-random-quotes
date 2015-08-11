
var xv_random_quotes =  xv_random_quotes || {}

jQuery(document).ready(function($) {
    xv_random_quotes.newQuote = function (categories, linkphrase, id, strayurl, multi, offset, sequence, timer, disableaspect, loading, contributor) {

        if(!xv_random_quotes.ajax_url) {
            return;
        }

        var divheight = $("div.stray_quote-" + id).height();
        $("div.stray_quote-" + id).height(divheight/2);
        $("div.stray_quote-" + id).css('text-align','center');
        $("div.stray_quote-" + id).css('padding-top',divheight/2);
        $("div.stray_quote-" + id).fadeOut('slow');
        $("div.stray_quote-" + id).html(loading).fadeIn('slow', function () {

            var data = {
                'action': 'xv_random_quotes_new_quote',
                'xv_random_quote_action' : 'newquote',
                'categories' : categories,
                'sequence' : sequence,
                'linkphrase': linkphrase,
                'widgetid': id,
                'multi': multi,
                'offset': offset,
                'disableaspect' : disableaspect,
                'timer': timer,
                'contributor': contributor
            };

            $.post(xv_random_quotes.ajax_url, data, function(response){

                var html = $( response ).find( 'response_data' ).text();

                $("div.stray_quote-" + id).css('padding-top',null);
                $("div.stray_quote-" + id).css('height', null);
                $("div.stray_quote-" + id).after(html).remove();
            });
        });
    };
});