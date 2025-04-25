jQuery(document).ready(function($) {
    // Uploading files
    var frame;
    var $imagesList = $('.gallery-images');
    var $idsInput = $('#project_gallery_ids');

    $('.upload-gallery').on('click', function(e) {
        e.preventDefault();
        
        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Images',
            button: {
                text: 'Use these images'
            },
            multiple: true
        });

        // When images are selected...
        frame.on('select', function() {
            var attachmentIds = [];
            var attachments = frame.state().get('selection').map(function(attachment) {
                attachment.toJSON();
                return attachment;
            });

            attachments.forEach(function(attachment) {
                attachmentIds.push(attachment.id);
            });

            $idsInput.val(attachmentIds.join(','));
            
            // Update preview
            $imagesList.empty();
            attachments.forEach(function(attachment) {
                var imgUrl = attachment.attributes.sizes.full ? attachment.attributes.sizes.full.url : attachment.attributes.url;
                $imagesList.append(
                    '<li>' +
                    '<input type="hidden" name="project_gallery_ids[]" value="' + attachment.id + '">' +
                    '<img src="' + imgUrl + '">' +
                    '<a href="#" class="change-image">Replace</a>' +
                    '<a href="#" class="remove-image">Remove</a>' +
                    '</li>'
                );
            });
        });

        // Open the media frame
        frame.open();
    });

    // Remove image
    $('.gallery-images').on('click', '.remove-image', function(e) {
        e.preventDefault();
        $(this).parent().remove();
        updateIdsInput();
    });

    function updateIdsInput() {
        var ids = [];
        $('.gallery-images li input').each(function() {
            ids.push($(this).val());
        });
        $idsInput.val(ids.join(','));
    }
});
