function uploadImage(file) {
    return new Promise((resolve, reject) => {
        let formData = new FormData();
        formData.append('image', file);

        $.ajax({
            url: uploadImageUrl,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.url) {
                    resolve(response.url);
                } else {
                    reject('Image upload failed. No URL returned.');
                }
            },
            error: function(error) {
                reject('Image upload failed. Please try again.');
            }
        });
    });
}
