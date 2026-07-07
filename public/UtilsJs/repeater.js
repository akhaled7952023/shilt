// $(document).ready(function () {
//     $('.repeater-default').repeater({
//         initEmpty: false,
//         defaultValues: {
//             'link': '',
//             'icon': ''
//         },
//         show: function () {
//             $(this).slideDown();
//         },
//         hide: function (deleteElement) {
//             if (confirm("{{ __('dashboard.confirm_delete') }}")) {
//                 $(this).slideUp(deleteElement);
//             }
//         }
//     });
// });



$(document).ready(function () {
    $('.repeater-default').repeater({
        initEmpty: false,
        defaultValues: {
            'link': '',
            'icon': ''
        },
        show: function () {
            $(this).slideDown();
        },
        hide: function (deleteElement) {
            $(this).slideUp(deleteElement);
        }
    });
});
