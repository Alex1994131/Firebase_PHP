
function view_image(obj) {
	var file_name = $(obj).data('name');
	var file_url = $(obj).data('url');
	var file_size = $(obj).data('size');

	$("#modal_title").empty().text(file_name + '  (' + file_size + 'KB)')
	$("#img_preview").attr('src', file_url);

	$("#preview-modal").modal();
}

function delete_image(obj) {
	var image_id = $(obj).data('key');
	var image_name = $(obj).data('name');

	if (confirm("Are you sure you are going to delete image?")) {

		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				image_id: image_id,
				image_name: image_name
			},
			success: function(response) {
				document.location.reload();
			}
		})
	}
}