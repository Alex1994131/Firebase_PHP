
<?php
	
	require_once './vendor/autoload.php';

	use Kreait\Firebase\Factory;
	use Kreait\Firebase\Storage;
	use Kreait\Firebase\ServiceAccount;

	use Google\Cloud\Storage\StorageClient;

	$acc = ServiceAccount::fromJsonFile(__DIR__ . '/secret/sabella-ba873-847edfb1abfa.json');
    $firebase = (new Factory)->withServiceAccount($acc)->withDatabaseUri('https://sabella-ba873-default-rtdb.firebaseio.com')->create();

    $database = $firebase->getDatabase();
    $storage = $firebase->getStorage();

	if (isset($_POST['submit'])) {
	   	
	   	$file_name = $_FILES["file"]['name'];
        $file_type = $_FILES["file"]['type'];
        $file_size = $_FILES["file"]['size'];
        $file_content = $_FILES["file"]['tmp_name'];

        $file_name = time() .$file_name;
        
        $object = $storage->getBucket()->upload(file_get_contents($file_content), [
        	'name' => 'gallery/' . $file_name,
        	'predefinedAcl' => 'publicRead'
        ]);

        $publicUrl = "https://{$storage->getBucket()->name()}.storage.googleapis.com/{$object->name()}";

  		// $expiresAt = new \DateTime('today');
		// $imageReference = $storage->getBucket()->object('gallery/' . $file_name);
		// $image = $imageReference->signedUrl($expiresAt);

        $data = array(
        	'url' => $publicUrl,	
        	'name' => $file_name,
        	'type' => $file_type,
        	'size' => $file_size
        );
        
        $database->getReference('/gallery')->push($data);
        header("Location: index.php"); 
	}

	if(isset($_POST['image_id']) && $_POST['image_id'] != '') {
		$image_id = $_POST['image_id'];
		$image_name = $_POST['image_name'];

		$database->getReference('/gallery')->getChild($image_id)->remove(); 
		$imageDeleted = $storage->getBucket()->object('gallery/' . $image_name)->delete();
	}

	
	$images = array();
	if ($database->getReference()->getSnapshot()){
       $images = $database->getReference('/gallery')->getSnapshot()->getValue();
   	}
?>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Image Gallery</title>

  <link rel="stylesheet" type="text/css" href="./assets/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="./assets/css/fontAwesome/font-awesome.min.css" />
  <link rel="stylesheet" type="text/css" href="./assets/css/custom.css" />
</head>
<body>
	<div class="content">
		<div class="container">
			<div class="row page-header">
				<div class="col-md-12">
					<h2 class="page-title">Image Gallery</h2>		
				</div>
			</div>
			<div class="row" id="gallery_list">
				<?php if(!empty($images)) { ?>
				<?php foreach ($images as $key => $value) { ?>
					<div class="col-md-3">
						<div class="gallery_wrapper">
							<img src="<?php echo $value['url']; ?>" class="gallery_item img-thumbnail round" />
							<div class="gallery_tool">
						    	<button class="btn btn-pill" onclick="view_image(this)" data-url="<?php echo $value['url']; ?>" data-name="<?php echo $value['name']; ?>" data-size="<?php echo $value['size']; ?>">
						    		<span>
						    			<i class="fa fa-search"></i>
						    		</span>
						    	</button>
						    	<button class="btn btn-pill" onclick="delete_image(this)" data-key="<?php echo $key; ?>" data-name="<?php echo $value['name']; ?>">
						    		<span>
						    			<i class="fa fa-trash"></i>
						    		</span>
						    	</button>
						  	</div>
						</div>
					</div>
				<?php }} ?>
			</div>
			<div class="row">
				<div class="col-md-12">
					<form id="gallery_form" action="index.php" method="post" enctype="multipart/form-data">
					
						<div class="form-group">
							<input id="file" name="file" type="file" size="2000000" class="btn btn-danger" required>
							<button type="submit" name="submit" class="btn btn-primary">Upload Image</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="modal" id="preview-modal">
	  	<div class="modal-dialog modal-xl">
		    <div class="modal-content">
				<div class="modal-header">
		        	<h4 class="modal-title" id="modal_title"></h4>
		        	<button type="button" class="close" data-dismiss="modal">&times;</button>
		      	</div>

		      	<div class="modal-body">
		        	<img src="" class="img_preview" id="img_preview" />
		      	</div>
		     	<div class="modal-footer">
		        	<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
		      	</div>
		    </div>
  		</div>
	</div>
</body>

<script src="./assets/js/jquery.min.js"></script>
<script src="./assets/js/bootstrap.min.js"></script>
<!-- <script src="./assets/js/jquery-ui.js"></script> -->

<script src="./assets/script/gallery.js"></script>
</html>