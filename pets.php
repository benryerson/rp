<?php



error_reporting(E_ALL);



// Some stuff for generating stats

function file_size($size)

{

	$units = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb');



	for ($i = 0; $size > 1024; $i++)

		$size /= 1024;



	return round($size, 2).' '.$units[$i];

}



function get_microtime($microtime=false)

{

	if ($microtime === false)

		$microtime = microtime();



	list($usec, $sec) = explode(' ', $microtime);

	return ((float)$usec + (float)$sec);

}



$start_timer = microtime();



// Include class

require 'inc/imgbrowz0r.php';



// These are all settings (set to default). The settings are not validated since you have to configure everything.

// There is a chance that ImgBrowz0r stops working if you enter the wrong values.

$config = array(

	// Directory settings. These are required. Without trailing slash. (required)

	'images_dir'               => dirname(__FILE__).'/gallery/pets',

	'cache_dir'                => dirname(__FILE__).'/cache/pets',



	// Url settings. These are required. Without trailing slash. (required)

	// %PATH% is replaced with the directory location and page number

	'main_url'                 => 'index.php?q=%PATH%',

	'images_url'               => 'gallery/pets',

	'cache_url'                => 'cache/pets',



	// Sorting settings (optional)

	'dir_sort_by'              => 3, // 1 = filename, 2 = extension (dir), 3 = inode change time of file

	'dir_sort_order'           => false, // true = ascending, false = descending



	'img_sort_by'              => 3, // 1 = filename, 2 = extension (png, gif, etc.), 3 = inode change time of file

	'img_sort_order'           => false, // true = ascending, false = descending



	// Thumbnail settings (optional)

	'thumbs_per_page'          => 14, // Amount of thumbnails per page

	'max_thumb_row'            => 7, // Amount of thumbnails on a row

	'max_thumb_width'          => 100, // Maximum width of thumbnail

	'max_thumb_height'         => 100, // Maximum height of thumbnail



	// Time settings (optional)

	'time_format'              => 'F jS, Y', // Date formatting. Look at the PHP date() for help: http://nl3.php.net/manual/en/function.date.php

	'time_zone'                => 0, // Timezone. Example: 1

	'enable_dst'               => false, // Daylight saving time (DST). Set this to true to enable it.



	// Misc settings (optional)

	'ignore_port'              => false, // Ignore port in url. Set this to true to ignore the port.

	'dir_thumbs'               => true, // Show a thumbnail in a category box. Default is false.

	'random_thumbs'            => false, // Use random thumbnails for categories. Default is false.

	'read_thumb_limit'         => 0, // See README for information about this setting.

	'filesize_limit'           => 4069 // Filesize limit in kilobytes (4MB). Exclude images that are bigger than the filesize limit.

	);
	
$gallery = new imgbrowz0r($config);

?>
<?php include('header.php'); ?>

<div class="pets">

	<div class="main-content">
		<h2>Pets</h2>
		
		<?php
			$gallery->init();
            $gallery_breadcrumbs = $gallery->breadcrumbs();
            $gallery_pagination = $gallery->pagination();
            $gallery_statistics = $gallery->statistics();
            echo $gallery->browse();
        ?>
	
		<?php include('portfolio_nav.php'); ?>
	</div>

	<?php include('nav.php'); ?>
</div>

	<script src="js/mylibs/jquery.colorbox-min.js"></script>
	<script>
		jQuery(document).ready(function () {
		jQuery('a.gallery').colorbox({ opacity:0.5 , rel:'group1' });
		});
	</script>

<?php include('footer.php'); ?>