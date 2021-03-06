<?php

/* ---

	ImgBrowz0r, a simple PHP5 Gallery class
	Version 0.3.4, September 10th, 2009
	http://61924.nl/projects/imgbrowz0r.html

	Copyright (c) 2008-2009 Frank Smit
	License: http://www.gzip.org/zlib/zlib_license.html

	This software is provided 'as-is', without any express or implied
	warranty. In no event will the authors be held liable for any damages
	arising from the use of this software.

	Permission is granted to anyone to use this software for any purpose,
	including commercial applications, and to alter it and redistribute it
	freely, subject to the following restrictions:

	1. The origin of this software must not be misrepresented; you must not
	   claim that you wrote the original software. If you use this software
	   in a product, an acknowledgment in the product documentation would be
	   appreciated but is not required.

	2. Altered source versions must be plainly marked as such, and must not be
	   misrepresented as being the original software.

	3. This notice may not be removed or altered from any source
	   distribution.

--- */

define('IMGBROWZ0R_VERSION', '0.3.4');

class imgbrowz0r
{
	protected $config, $cur_directory, $cur_page, $files, $page_count,
	          $count_files=0, $count_dirs=0, $count_imgs=0, $full_path,
	          $image_types=array('gif', 'jpg', 'jpeg', 'jpe', 'jif', 'jfif', 'jfi', 'png'); // All image types that browsers support

	public $status=200;

	// Check if GD is loaded and set configuration
	public function __construct($config)
	{
		// First check if GD is enabled
		if (!function_exists('gd_info'))
			exit('<p><a href="http://www.php.net/manual/en/book.image.php">GD</a> is not enabled!</p>');

		// Set configuration
		$this->config = array(
			// Directory settings
			'images_dir'               => isset($config['images_dir']) ? $config['images_dir'] : exit('You have to set the full path to the directory with images!'),
			'cache_dir'                => isset($config['cache_dir']) ? $config['cache_dir'] : exit('You have to set the full path to the cache directory!'),

			// Url settings
			'main_url'                 => isset($config['main_url']) ? $config['main_url'] : exit('You have to set a main url!'),
			'images_url'               => isset($config['images_url']) ? $config['images_url'] : exit('You have to set the url to the directory with images!'),
			'cache_url'                => isset($config['cache_url']) ? $config['cache_url'] : exit('You have to set the url to the cache!'),

			// Sorting settings
			'dir_sort_by'              => isset($config['dir_sort_by']) && in_array($config['dir_sort_by'], array(1, 2, 3)) ? $config['dir_sort_by'] : 3,
			'dir_sort_order'           => isset($config['dir_sort_order']) && $config['dir_sort_order'] === true ? SORT_ASC : SORT_DESC,

			'img_sort_by'              => isset($config['img_sort_by']) && in_array($config['img_sort_by'], array(1, 2, 3)) ? $config['img_sort_by'] : 3,
			'img_sort_order'           => isset($config['img_sort_order']) && $config['img_sort_order'] === true ? SORT_ASC : SORT_DESC,

			// Thumbnail settings
			'thumbs_per_page'          => isset($config['thumbs_per_page']) ? $config['thumbs_per_page'] : 12,
			'max_thumb_row'            => isset($config['max_thumb_row']) ? $config['max_thumb_row'] : 4,
			'max_thumb_width'          => isset($config['max_thumb_width']) ? $config['max_thumb_width'] : 200,
			'max_thumb_height'         => isset($config['max_thumb_height']) ? $config['max_thumb_height'] : 200,

			// Time settings
			'time_format'              => isset($config['time_format']) ? $config['time_format'] : 'F jS, Y',
			'time_zone'                => isset($config['time_zone']) ? $config['time_zone'] : 0,
			'enable_dst'               => isset($config['enable_dst']) && $config['enable_dst'] === true ? 1 : 0,

			// Misc settings
			'ignore_port'              => isset($config['ignore_port']) && $config['ignore_port'] === true  ? true : false,
			'dir_thumbs'               => isset($config['dir_thumbs']) && $config['dir_thumbs'] === true  ? true : false,
			'random_thumbs'            => isset($config['random_thumbs']) && $config['random_thumbs'] === true  ? true : false,
			'read_thumb_limit'         => isset($config['read_thumb_limit']) && is_numeric($config['read_thumb_limit'])
			                              && $config['read_thumb_limit'] >= 0 ? $config['read_thumb_limit'] : 0,
			'filesize_limit'           => isset($config['filesize_limit']) ? $config['filesize_limit'] : 20480,
		);

		if ($this->config['random_thumbs'] === false)
			$this->config['read_thumb_limit'] = 1;
	}

	public function init()
	{
		// Get current url
		$protocol = !isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off' ? 'http://' : 'https://';
		$port = $this->config['ignore_port'] === false && (isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT'] != '80'
			&& $protocol == 'http://') || ($_SERVER['SERVER_PORT'] != '443' && $protocol == 'https://'))
			&& strpos($_SERVER['HTTP_HOST'], ':') === false) ? ':'.$_SERVER['SERVER_PORT'] : '';
		$current_url = urldecode($protocol.$_SERVER['HTTP_HOST'].$port.$_SERVER['REQUEST_URI']);

		// Regex
		preg_match('/^'.str_replace('%PATH%', '(.*?)', preg_quote($this->config['main_url'], '/')).'$/i', $current_url, $matches);

		// Set current path/directory and page
		$raw_path = isset($matches[1]) ? trim($matches[1], " /\n\t") : false;

		if ($raw_path !== false)
		{
			$this->cur_directory = str_replace(array('<', '>', '"', '\'', '&',' ;'), '', substr($raw_path, 0, strrpos($raw_path, '/')).'/');
			$this->cur_page = (int) substr($raw_path, strrpos($raw_path, '/')+1);
		}
		else
		{
			$this->cur_directory = false;
			$this->cur_page = 1;
		}

		if ($this->cur_directory == '0/' || $this->cur_directory == '/')
			$this->cur_directory = false;

		$dirs = $imgs = array();
		$this->full_path = $this->cur_directory === false ? $this->config['images_dir'].'/' : $this->config['images_dir'].'/'.$this->cur_directory;

		if (is_dir($this->full_path) && ($handle = opendir($this->full_path)))
		{
			// Scan directories and files
			while (($file = readdir($handle)) !== false)
			{
				if (is_dir($this->full_path.'/'.$file))
				{
					// Exclude . and ..
					if ($file == '.' || $file == '..')
						continue;

					$dirs[] = array(0, $file, 'dir', filectime($this->full_path.'/'.$file));
				}
				else if (filesize($this->full_path.'/'.$file) <= $this->config['filesize_limit']*1024)
				{
					// Check if file is an supported image type
					$image_extension = imgbrowz0r::get_ext($file);
					if (!in_array($image_extension, $this->image_types))
						continue;

					$imgs[] = array(1, $file, $image_extension, filectime($this->full_path.'/'.$file));
				}
			}

			closedir($handle);

			// Sort arrays
			if (($this->count_dirs = count($dirs)) > 0)
			{
				foreach($dirs as $res) $sortAux[] = $res[$this->config['dir_sort_by']];
				array_multisort($sortAux, $this->config['dir_sort_order'], $dirs);
			}

			if (($this->count_imgs = count($imgs)) > 0)
			{
				foreach($imgs as $res2) $sortAux2[] = $res2[$this->config['dir_sort_by']];
				array_multisort($sortAux2, $this->config['dir_sort_order'], $imgs);
			}

			// Calculate pages
			$this->page_count = (int) ceil(($this->count_dirs + $this->count_imgs) / $this->config['thumbs_per_page']);
			$this->cur_page = $this->cur_page > 0 && $this->cur_page <= $this->page_count ? $this->cur_page : 1;

			// Merge and slice arrays
			$this->files = array_slice(
				array_merge($dirs, $imgs),
				($this->cur_page-1) * $this->config['thumbs_per_page'],
				$this->config['thumbs_per_page']);

			$this->count_files = count($this->files);
		}
		else
			$this->status = 404;
	}

	// Reads the gallery directories and files
	public function browse()
	{
		// Check status code and file count
		if ($this->status === 404)
			return '<div id="imgbrowz0r">'."\n\t".'<p class="img-directory-not-found">This directory does not exist!</p>'."\n".'</div>'."\n";
		else if ($this->count_files < 1)
			return '<div id="imgbrowz0r">'."\n\t".'<p class="img-empty-directory">There are no images or directories in this directory.</p>'."\n".'</div>'."\n";

		@set_time_limit(180); // 3 Minutes
		$row_count = 1;

		// Start capturing output
		ob_start();
		echo '<div id="imgbrowz0r">', "\n\t", '<div class="img-row">', "\n";

		foreach ($this->files as $k => $file)
		{
			if ($file[0] === 1)
			{
				$image_cache_dir = md5($this->cur_directory);
				$image_thumbnail = $image_cache_dir.'/'.$file[3].'_'.$file[1]; // The name of the thumbnail

				if (!is_dir($this->config['cache_dir'].'/'.$image_cache_dir))
					mkdir($this->config['cache_dir'].'/'.$image_cache_dir, 0777);

				if (!file_exists($this->config['cache_dir'].'/'.$image_thumbnail))
					$this->make_thumb($this->cur_directory, $file[1], $image_thumbnail);

				echo "\t\t", '<div class="img-thumbnail img-column-', $row_count, '"><a href="', $this->config['images_url'],
				     '/', $this->cur_directory, $file[1], '" style="background-image: url(\'', $this->config['cache_url'], '/', $image_thumbnail, '\')" class="gallery"></a></div>', "\n";
			}
			else
			{
				if ($this->config['dir_thumbs'] === true)
				{
					$dir_hash = md5($this->cur_directory.$file[1].'/');
					$dir_thumbs = $this->read_cache($dir_hash, $this->cur_directory.$file[1].'/');

					$dir_thumbnail = isset($dir_thumbs[0]) ? ' style="background-image: url(\''.$this->config['cache_url'].'/'.
					                 $dir_hash.'/'.$dir_thumbs[($this->config['random_thumbs'] === false ? 0 : mt_rand(0, count($dir_thumbs)-1))].'\')"' : null;

					echo "\t\t", '<div class="img-directory img-column-', $row_count, '"><a href="',
					     str_replace('%PATH%',  $this->cur_directory.$file[1].'/1', $this->config['main_url']), '"',
					     $dir_thumbnail, ' title="', $file[1], '">&nbsp;</a><span class="img-dir-name">', $file[1],
					     '</span><span class="img-thumb-date">', $this->format_time($file[3]), '</span></div>', "\n";
				}
				else
				{
					echo "\t\t", '<div class="img-directory img-column-', $row_count, '"><a href="',
					     str_replace('%PATH%',  $this->cur_directory.$file[1].'/1', $this->config['main_url']),
					     '" title="', $file[1], '"><span>', $file[1], '</span></a><span>', $this->format_time($file[3]),
					     '</span></div>', "\n";
				}
			}

			if ($row_count === $this->config['max_thumb_row'] && $k < ($this->count_files-1))
			{
				echo "\t", '</div>', "\n\t", '<div class="img-row">', "\n";
				$row_count = 0;
			}

			++$row_count;
		}

		echo "\t", '</div>', "\n\n\t", '<div class="clear">&nbsp;</div>', "\n", '</div>', "\n\n";

		// Stop capturing output
		return ob_get_clean();
	}

	// Returns the image/directory count
	public function statistics()
	{
		// Check status code
		if ($this->status === 404 || $this->count_files < 1)
			return;

		return '<div class="img-statistics">There '.($this->count_dirs !== 1 ? 'are '.$this->count_dirs.' directories' : 'is 1 directory').
		       ' and '.($this->count_imgs !== 1 ? $this->count_imgs.' images' : '1 image').' in this directory.</div>';
	}

	// Generate breadcrumbs
	public function breadcrumbs()
	{
		// Check status code
		if ($this->status === 404)
			return;

		$path_parts = $this->cur_directory !== false ? explode('/', trim($this->cur_directory, '/')) : array();

		if (isset($path_parts[0]))
			foreach ($path_parts as $k => $part)
				$output[] = '<a href="'.str_replace('%PATH%',  implode('/', array_slice($path_parts, 0, ($k+1))).'/1' , $this->config['main_url']).'">'.$part.'</a>';

		return '<div class="img-breadcrumbs"><span>Breadcrumbs: </span><a href="'.str_replace('%PATH%',  '0/1', $this->config['main_url']).'">Root</a>'.
		       (isset($output) ? ' / '.implode(' / ', $output) : null).'</div>';
	}

	// Generate page navigation
	public function pagination()
	{
		// Check status code and page count
		if ($this->status === 404 || $this->page_count < 2)
			return;

		$pages = array();
		$cur_dir = $this->cur_directory !== false ? rtrim($this->cur_directory, '/') : 0;
		$current_range = array(($this->cur_page < 5 ? 2 : $this->cur_page-3), ($this->cur_page+3 >= $this->page_count ? $this->page_count-1 : $this->cur_page+3));

		// Previous and next links
		$prev = $this->cur_page > 1 ? '<a href="'.str_replace('%PATH%', $cur_dir.'/'.($this->cur_page - 1), $this->config['main_url']).'">&laquo;</a>' : null;
		$next = $this->cur_page < $this->page_count ? '<a href="'.str_replace('%PATH%', $cur_dir.'/'.($this->cur_page + 1), $this->config['main_url']).'">&raquo;</a>' : null;

		// First and last page
		$first = $this->cur_page === 1 ? '<strong class="img-current-page">1</strong>' : '<a href="'.str_replace('%PATH%', $cur_dir.'/1', $this->config['main_url']).'">1</a>';
		$last = $this->cur_page === $this->page_count ? '<strong class="img-current-page">'.$this->page_count.'</strong>' : '<a href="'.
		        str_replace('%PATH%', $cur_dir.'/'.$this->page_count, $this->config['main_url']).'">'.$this->page_count.'</a>';

		// Other pages
		for ($x=$current_range[0];$x <= $current_range[1];++$x)
			$pages[] = '<a href="'.str_replace('%PATH%', $cur_dir.'/'.$x, $this->config['main_url']).'">'.($x == $this->cur_page ? '<strong>'.$x.'</strong>' : $x).'</a>';

		return '<div class="img-pagination"><span>Pages: </span>'.$prev.' '.$first.($this->cur_page > 5 ? ' ... ' : ' ').implode(' ', $pages).
		       ($this->cur_page < $this->page_count - 4 ? ' ... ' : ' ').$last.' '.$next.'</div>';
	}

	// Display description of the current directory
	// Html tags are stripped from the description except the following tags:
	// <p>, <strong>, <em>, <a>, <br />, <h1>, <h2> and <h3>
	public function description()
	{
		if (file_exists($this->full_path.'.desc'))
			return '<div class="img-description">'.
			strip_tags(file_get_contents($this->full_path.'.desc'), '<p><strong><em><a><br><h1><h2><h3>').
			'</div>';
	}

	// The legendary thumbnail generater
	protected function make_thumb($image_dir, $image_name, $image_thumbnail)
	{
		$image_dir = $image_dir !== false ? $image_dir.'/' : null;
		$image_info = imgbrowz0r::get_image_info($this->config['images_dir'].'/'.$image_dir.'/'.$image_name);

		// Check if file is an supported image type
		if (!in_array($image_info['extension'], $this->image_types))
			return false;

		// Open the image so we can make a thumbnail
		if ($image_info['type'] === 3)
			$image = imagecreatefrompng($this->config['images_dir'].'/'.$image_dir.$image_name);
		else if ($image_info['type'] === 2)
			$image = imagecreatefromjpeg($this->config['images_dir'].'/'.$image_dir.$image_name);
		else if ($image_info['type'] === 1)
			$image = imagecreatefromgif($this->config['images_dir'].'/'.$image_dir.$image_name);
		else
			return false;

		// Calculate new width and height
		$zoomw = $image_info['width'] / $this->config['max_thumb_width'];
		$zoomh = $image_info['height'] / $this->config['max_thumb_height'];
		$zoom = ($zoomw > $zoomh) ? $zoomw : $zoomh;

		if ($image_info['width'] < $this->config['max_thumb_width'] && $image_info['height'] < $this->config['max_thumb_height'])
		{
			$thumb_width = $image_info['width'];
			$thumb_height = $image_info['height'];
		}
		else
		{
			$thumb_width = $image_info['width'] / $zoom;
			$thumb_height = $image_info['height'] / $zoom;
		}

		// Create an image for the thumbnail
		$thumbnail = imagecreatetruecolor($thumb_width, $thumb_height);

		// Preserve transparency in PNG and GIF images
		if ($image_info['type'] === 3)
		{
			$alpha_color = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
			imagefill($thumbnail, 0, 0, $alpha_color);
			imagesavealpha($thumbnail, true);
		}
		else if ($image_info['type'] === 1 && ($transparent_index = imagecolortransparent($image)) >= 0)
		{
			$transparent_color = imagecolorsforindex($image, $transparent_index);
			$transparent_index = imagecolorallocate($thumbnail, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
			imagefill($thumbnail, 0, 0, $transparent_index);
			imagecolortransparent($thumbnail, $transparent_index);
		}

		// Copy and resize image
		imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $image_info['width'], $image_info['height']);

		// Save the thumbnail, but first check what kind of file it is
		if ($image_info['type'] === 3)
			imagepng($thumbnail, $this->config['cache_dir'].'/'.$image_thumbnail);
		else if ($image_info['type'] === 2)
			imagejpeg($thumbnail, $this->config['cache_dir'].'/'.$image_thumbnail, 85);
		else if ($image_info['type'] === 1)
		{
			imagetruecolortopalette($thumbnail, true, 256);
			imagegif($thumbnail, $this->config['cache_dir'].'/'.$image_thumbnail);
		}

		// Destroy
		imagedestroy($image);
		imagedestroy($thumbnail);
	}

	// Reads the cache folder for thumbnails. If none is found it makes one thumbnail
	protected function read_cache($cache_path, $category_path)
	{
		$thumbnails = array();

		// Look for thumbnails in the cache dir
		if (is_dir($this->config['cache_dir'].'/'.$cache_path) && ($handle = opendir($this->config['cache_dir'].'/'.$cache_path)))
		{
			$file_count = 0;

			while (($file = readdir($handle)) !== false)
			{
				$extension = imgbrowz0r::get_ext($file);
				if (!in_array($extension, $this->image_types))
					continue;

				$thumbnails[] = $file;
				++$file_count;

				if ($file_count === $this->config['read_thumb_limit'])
					break;
			}

			closedir($handle);
		}
		else
			mkdir($this->config['cache_dir'].'/'.$cache_path, 0777);

		// Generate a thumbnail if none is found
		if (count($thumbnails) < 1)
		{
			$file_count = 0;
			$handle = opendir($this->config['images_dir'].'/'.$category_path);

			while (($file = readdir($handle)) !== false)
			{
				$extension = imgbrowz0r::get_ext($file);
				if (!in_array($extension, $this->image_types))
					continue;

				$image_thumbnail = $cache_path.'/'.filectime($this->config['images_dir'].'/'.$category_path.'/'.$file).'_'.$file; // The name of the thumbnail;
				$thumbnails[] = basename($image_thumbnail);
				$this->make_thumb($category_path, $file, $image_thumbnail);

				++$file_count;

				if ($file_count === 1)
					break;
			}

			closedir($handle);
		}

		return $thumbnails;
	}

	// Format unix timestamp to a human readable date
	protected function format_time($timestamp)
	{
		return gmdate($this->config['time_format'], ($timestamp + $this->config['time_zone'] * 3600));
	}

	// Get info from image (width, height, type, extension)
	protected static function get_image_info($filepath)
	{
		$getimagesize = getimagesize($filepath);

		return array(
			'width' => $getimagesize[0],
			'height' => $getimagesize[1],
			'type' => $getimagesize[2],
			'extension' => imgbrowz0r::get_ext($filepath));
	}

	// Get extension from filename (returns the extension without the dot)
	protected static function get_ext($file_name)
	{
		return strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
	}
}

?>
