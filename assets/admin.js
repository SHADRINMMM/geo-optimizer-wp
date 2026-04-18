/* GEO Optimizer admin page — Refresh Now button */
jQuery(function ($) {
    var $btn    = $('#causabi-refresh-btn');
    var $status = $('#causabi-refresh-status');
    var s       = causabiAjax.strings;

    $btn.on('click', function () {
        $btn.prop('disabled', true).text(s.analyzing);
        $status.css('color', '').text('');

        $.post(causabiAjax.ajaxUrl, {
            action: 'causabi_refresh',
            nonce:  causabiAjax.nonce,
        })
        .done(function (res) {
            if (res.success) {
                $status.css('color', 'green').text(s.done);
                setTimeout(function () { location.reload(); }, 1500);
            } else {
                $status.css('color', '#d63638').text('❌ ' + (res.data || s.error));
                $btn.prop('disabled', false).text(s.refresh);
            }
        })
        .fail(function () {
            $status.css('color', '#d63638').text(s.error);
            $btn.prop('disabled', false).text(s.refresh);
        });
    });
});
