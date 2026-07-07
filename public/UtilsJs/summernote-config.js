// $(document).ready(function () {
//     // محرر الوصف بالعربية
//     if ($('#summernote_ar').length) {
//         $('#summernote_ar').summernote({
//             lang: 'ar-AR', // إعداد اللغة العربية
//             height: 400,
//             toolbar: [
//                 ['style', ['bold', 'italic', 'underline', 'clear']],
//                 ['font', ['strikethrough', 'superscript', 'subscript']],
//                 ['fontsize', ['fontsize']],
//                 ['color', ['color']],
//                 ['para', ['ul', 'ol', 'paragraph', 'justify']],
//                 ['height', ['height']],
//                 ['insert', ['link', 'picture', 'video']],
//                 ['view', ['fullscreen', 'codeview', 'help']],
//             ],
//             callbacks: {
//                 onImageUpload: function (files) {
//                     const editor = $(this); // المحرر الحالي
//                     uploadImage(files[0])
//                         .then((imageUrl) => {
//                             editor.summernote('insertImage', imageUrl); // إدراج الصورة
//                         })
//                         .catch((error) => {
//                             alert(error); // عرض رسالة خطأ إذا فشل الرفع
//                         });
//                 }
//             }
//         });
//     }

//     // محرر الوصف بالإنجليزية
//     if ($('#summernote_en').length) {
//         $('#summernote_en').summernote({
//             lang: 'en-US', // إعداد اللغة الإنجليزية
//             height: 400,
//             toolbar: [
//                 ['style', ['bold', 'italic', 'underline', 'clear']],
//                 ['font', ['strikethrough', 'superscript', 'subscript']],
//                 ['fontsize', ['fontsize']],
//                 ['color', ['color']],
//                 ['para', ['ul', 'ol', 'paragraph', 'justify']],
//                 ['height', ['height']],
//                 ['insert', ['link', 'picture', 'video']],
//                 ['view', ['fullscreen', 'codeview', 'help']],
//             ],
//             callbacks: {
//                 onImageUpload: function (files) {
//                     const editor = $(this);
//                     uploadImage(files[0])
//                         .then((imageUrl) => {
//                             editor.summernote('insertImage', imageUrl);
//                         })
//                         .catch((error) => {
//                             alert(error);
//                         });
//                 }
//             }
//         });
//     }
// });




$(document).ready(function() {
    // التحقق من اللغة
    const isArabic = $('html').attr('lang') === 'ar';

    // محرر الوصف بالعربية
    if ($('#summernote_ar').length) {
        $('#summernote_ar').summernote({
            lang: 'ar-AR', // إعداد اللغة العربية
            height: 400,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph', 'justify']],
                ['height', ['height']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ],
            callbacks: {
                onInit: function() {
                    const $editableArea = $('#summernote_ar').next('.note-editor').find('.note-editable');
                    $editableArea.css({
                        'direction': 'rtl',
                        'text-align': 'right'
                    });

                    // تحديد اتجاه النص بناءً على النص المكتوب
                    $editableArea.on('input', function() {
                        const text = $(this).text();
                        const isRtl = /[\u0600-\u06FF]/.test(text);
                        $(this).css({
                            'direction': isRtl ? 'rtl' : 'ltr',
                            'text-align': isRtl ? 'right' : 'left'
                        });
                    });
                },
                onImageUpload: function(files) {
                    const editor = $(this); // المحرر الحالي
                    uploadImage(files[0])
                        .then((imageUrl) => {
                            editor.summernote('insertImage', imageUrl); // إدراج الصورة
                        })
                        .catch((error) => {
                            alert(error); // عرض رسالة خطأ إذا فشل الرفع
                        });
                }
            }
        });
    }

    // محرر الوصف بالإنجليزية
    if ($('#summernote_en').length) {
        $('#summernote_en').summernote({
            lang: 'en-US', // إعداد اللغة الإنجليزية
            height: 400,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph', 'justify']],
                ['height', ['height']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ],
            callbacks: {
                onInit: function() {
                    const $editableArea = $('#summernote_en').next('.note-editor').find('.note-editable');
                    $editableArea.css({
                        'direction': 'ltr',
                        'text-align': 'left'
                    });

                    // تحديد اتجاه النص بناءً على النص المكتوب
                    $editableArea.on('input', function() {
                        const text = $(this).text();
                        const isRtl = /[\u0600-\u06FF]/.test(text);
                        $(this).css({
                            'direction': isRtl ? 'rtl' : 'ltr',
                            'text-align': isRtl ? 'right' : 'left'
                        });
                    });
                },
                onImageUpload: function(files) {
                    const editor = $(this);
                    uploadImage(files[0])
                        .then((imageUrl) => {
                            editor.summernote('insertImage', imageUrl);
                        })
                        .catch((error) => {
                            alert(error);
                        });
                }
            }
        });
    }
});
