<?php namespace Neilcarpenter1\Ocmediaimgresize\Classes;

use Storage;
use Intervention\Image\ImageManagerStatic as Image;

class Resizer {

	public static function resizeimage($img) {

		// If no image is available return an empty string
		if (!$img) {
			return '';
		}
		
		$resource = 'media';
		
		$uploads_path = config('cms.storage.uploads.path');

		if (substr($img, 0, strlen($uploads_path)) == $uploads_path) {
			$resource = 'uploads';
		}

		$disk = config('cms.storage.'.$resource.'.disk');
		$disk_folder = config('cms.storage.'.$resource.'.folder');

		$original_path = $disk_folder.$img;

		if ($resource == 'uploads') {
			$original_path = str_replace('cms.storage.'.$resource.'.path', '', $img);
			$original_path = $disk_folder.$original_path;
		}

		if (!Storage::disk($disk)->exists($original_path)) {
			return '';
		}

		$original_file = Storage::disk($disk)->get($original_path);

		$resized_folder = config('neilcarpenter1.ocmediaimgresize::folder');
		$resized_imgs_dir = $disk_folder.'/'.$resized_folder.'/';

		$new_filename = str_replace('/', '-', substr($img, 1));

		$last_dot_position = strrpos($new_filename, '.');

		$extension = substr($new_filename, $last_dot_position+1);

		$filename_body = str_slug(substr($new_filename, 0, $last_dot_position));

		$filesize = Storage::disk($disk)->size($original_path);
		$filetime = Storage::disk($disk)->lastModified($original_path);

		$version_string = $filesize.'-'.$filetime;

		$new_filename = $filename_body.'-'.md5($version_string).'.'.$extension;

		$new_path = $resized_imgs_dir.$new_filename;

		if (!Storage::disk($disk)->exists($resized_imgs_dir)) {
			Storage::disk($disk)->makeDirectory($resized_imgs_dir);
		}

		if (!Storage::disk($disk)->exists($new_path)) {
			try {
				$image = Image::make($original_file);
				$image->fit(200,200);
				$image_stream = $image->stream($extension);
				Storage::disk($disk)->put($new_path, $image_stream->__toString());
			} catch (\Exception $e) {
				return 'Intervention image error : '.$e->getMessage();
			}
		}

		return asset(config('cms.storage.'.$resource.'.path').'/'.$resized_folder.'/'.$new_filename);
	}
}