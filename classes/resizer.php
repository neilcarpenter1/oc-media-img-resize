<?php namespace Neilcarpenter1\Ocmediaimgresize\Classes;

use Storage;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * @param  $img String to file in medialibrary
 */
class Resizer {

	/* We want to get the original file from the given string and return a string to a resized image */

	public static function resizeimage($img, $mode = 'auto', $value1 = null, $value2 = null) {

		// If no image is available return an empty string
		if (!$img) {
			return '';
		}

		$mode = $mode ?: config('neilcarpenter1.ocmediaimgresize::default.mode');
		$value1 = $value1 ?: config('neilcarpenter1.ocmediaimgresize::default.size');
		
		// get the disk & path to the currently used storage for media
		$disk = config('cms.storage.media.disk');
		$disk_folder = config('cms.storage.media.folder');
		
		// set the full path for the original file
		$original_path = $disk_folder.$img;

		// check the file exists
		if (!Storage::disk($disk)->exists($original_path)) {
			return '';
		}

		// get the original file
		$original_file = Storage::disk($disk)->get($original_path);

		// set the directory name where resized images should be stored from this plugins config/config.php fuile
		$resized_folder = config('neilcarpenter1.ocmediaimgresize::folder');

		// append the directory to the currently used disk storage path
		$resized_imgs_dir = $disk_folder.'/'.$resized_folder.'/';

		// generate a new file name for the resized image
		$new_filename = str_replace('/', '-', substr($img, 1));
		$extension = pathinfo($original_path, PATHINFO_EXTENSION);
		$filesize = Storage::disk($disk)->size($original_path);
		$filetime = Storage::disk($disk)->lastModified($original_path);
		$filename = pathinfo($new_filename, PATHINFO_FILENAME);
		$version_string = $mode.'-'.$value1.'x'.$value2.'-'.$filesize.'-'.$filetime;
		$new_filename = $filename.'-'.md5($version_string).'.'.$extension;
		$new_path = $resized_imgs_dir.$new_filename;

		// Create the directory where resized images are saved if it doesn't exists
		if (!Storage::disk($disk)->exists($resized_imgs_dir)) {
			Storage::disk($disk)->makeDirectory($resized_imgs_dir);
		}

		$image = Image::make($original_file);

		// Create the resized image
		if (!Storage::disk($disk)->exists($new_path)) {
			try {
				switch ($mode) {
					case 'crop':
						$image->fit($value1, $value2);
						break;
					case 'height':
						$image->heighten($value1);
						break;
					case 'width':
						$image->widen($value1);
						break;
					default:
						$image->resize($value1, $value2, function($constraint){
							$constraint->aspectRatio();
						});
						break;
				}
				$image_stream = $image->stream($extension);
				Storage::disk($disk)->put($new_path, $image_stream->__toString());
			} catch (\Exception $e) {
				return 'Intervention image error : '.$e->getMessage();
			}
		}
		return asset(config('cms.storage.media.path').'/'.$resized_folder.'/'.$new_filename);
	}
}