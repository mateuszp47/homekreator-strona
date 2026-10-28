<?php
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class ImageProcessor {
 public function store(UploadedFile $file,string $field='image'): string {
  if(!function_exists('imagewebp'))throw ValidationException::withMessages([$field=>'Serwer wymaga rozszerzenia PHP GD z obsługą WebP.']);
  $size=@getimagesize($file->getRealPath());
  if(!$size||$size[0]*$size[1]>config('images.max_pixels'))throw ValidationException::withMessages([$field=>'Zdjęcie ma zbyt dużą rozdzielczość (maks. 24 megapiksele).']);
  $limit=ini_get('memory_limit');$bytes=$limit==='-1'?PHP_INT_MAX:(int)$limit*match(strtolower(substr($limit,-1))){'g'=>1073741824,'m'=>1048576,'k'=>1024,default=>1};
  if(memory_get_usage(true)+$size[0]*$size[1]*6+config('images.max_edge')**2*6>$bytes)throw ValidationException::withMessages([$field=>'Za mało pamięci serwera dla tego zdjęcia. Zmniejsz jego rozdzielczość przed wysłaniem.']);
  $source=@imagecreatefromstring(file_get_contents($file->getRealPath()));
  if(!$source)throw ValidationException::withMessages([$field=>'Nie można odczytać zdjęcia. Wybierz inny plik.']);
  $target=null;
  try {
   if(($size['mime']??'')==='image/jpeg'&&function_exists('exif_read_data')){
    $exif=@exif_read_data($file->getRealPath());$orientation=$exif['Orientation']??1;
    if(in_array($orientation,[2,4,5,7],true))imageflip($source,in_array($orientation,[4,5],true)?IMG_FLIP_VERTICAL:IMG_FLIP_HORIZONTAL);
    $angle=match($orientation){3=>180,5,6=>-90,7,8=>90,default=>0};
    if($angle){$rotated=imagerotate($source,$angle,0);imagedestroy($source);$source=$rotated;}
   }
   $width=imagesx($source);$height=imagesy($source);$ratio=min(1,config('images.max_edge')/max($width,$height));
   $w=max(1,(int)round($width*$ratio));$h=max(1,(int)round($height*$ratio));
   $target=imagecreatetruecolor($w,$h);imagealphablending($target,false);imagesavealpha($target,true);
   imagefilledrectangle($target,0,0,$w,$h,imagecolorallocatealpha($target,0,0,0,127));
   imagecopyresampled($target,$source,0,0,0,0,$w,$h,$width,$height);
   ob_start();try{$success=imagewebp($target,null,config('images.quality'));$data=ob_get_contents();}finally{ob_end_clean();}
   if(!$success||!$data)throw new \RuntimeException('Nie udało się przetworzyć zdjęcia.');
   $path='catalog/'.Str::uuid().'.webp';if(!Storage::disk('public')->put($path,$data))throw new \RuntimeException('Nie udało się zapisać zdjęcia.');return $path;
  } finally {imagedestroy($source);if($target)imagedestroy($target);}
 }
}
