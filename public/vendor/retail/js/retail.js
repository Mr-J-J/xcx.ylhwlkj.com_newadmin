// $.fn.editable.defaults.params = function (params) {
//     params._token = LA.token;
//     params._editable = 1;
//     params._method = 'PUT';
//     return params;
// };

// $.fn.editable.defaults.error = function (data) {
//     var msg = '';
//     if (data.responseJSON.errors) {
//         $.each(data.responseJSON.errors, function (k, v) {
//             msg += v + "\n";
//         });
//     }
//     return msg
// };

// toastr.options = {
//     closeButton: true,
//     progressBar: true,
//     showMethod: 'slideDown',
//     timeOut: 4000
// };

$.pjax.defaults.timeout = 5000;
$.pjax.defaults.maxCacheLength = 0;

$(document).pjax('a:not(a[target="_blank"])', {
    container: '#pjax-container'
});

// NProgress.configure({parent: '#app'});

$(document).on('pjax:timeout', function (event) {
    event.preventDefault();
})

$(document).on('submit', 'form[pjax-container]', function (event) {
    $.pjax.submit(event, '#pjax-container')
});

$(document).on("pjax:popstate", function () {
    $(document).one("pjax:end", function (event) {
        $(event.target).find("script[data-exec-on-popstate]").each(function () {
            $.globalEval(this.text || this.textContent || this.innerHTML || '');
        });
    });
});

$(document).on('pjax:send', function (xhr) {
    if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
        $submit_btn = $('form[pjax-container] :submit');
        if ($submit_btn) {
            $submit_btn.button('loading')
        }
    }
    // NProgress.start();
});

$(document).on('pjax:complete', function (xhr) {
    if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
        $submit_btn = $('form[pjax-container] :submit');
        if ($submit_btn) {
            $submit_btn.button('reset')
        }
    }
    // NProgress.done();
});

$(function () {
    $('.sidebar-menu > a').on('click', function () {

        $(this).addClass('active').siblings().removeClass('active');
    });
    console.log((location.pathname + location.search + location.hash) )
    $('.sidebar-menu a[href="' + (location.pathname + location.search + location.hash) + '"]').addClass('active');

   
})

function toast(status,message = ''){
    classname = status?'toast-success':'toast-error';
    $('#liveToast .toast-body').text(message);
    $('#liveToast').addClass(classname);
    $('#liveToast').toast({delay:1500});
    $('#liveToast').toast('show');
}


    
