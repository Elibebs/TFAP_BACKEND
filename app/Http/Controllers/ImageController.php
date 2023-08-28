<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\PartImage;
use App\Models\CategoryImage;
use App\Models\SystemUserImage;


class ImageController extends Controller
{
    /*
     * Extracts picture's data from DB and makes an image
     */
    public function showImage(Request $request,$part_id)
    {
        $picture = PartImage::where('part_id', $part_id)->first();

        if(!isset($picture)){
            return "image not found";
        }
        $pic = base64_decode($picture->base64);

        /*resize image
        $width= $request->get('width');
        $height= $request->get('height');

        if(isset($width)||isset($height)){
            $pic = Picture::resize($width,$height, $pic);
        }

        //crop
        $crop_width= $request->get('crop_width');
        $crop_height= $request->get('crop_height');

        if(isset($crop_width) && isset($crop_height)){
            $pic = Picture::crop($crop_width, $crop_height, $pic);
        }*/

        // //sharpen
        // $sharpen =$request->get('sharpen');

        // if(isset($sharpen)){
        //     $pic = Image::sharpen($sharpen, $pic);
        // }

        $response = Response::make($pic);

        //setting content-type
        $response->header('Content-Type','image/jpeg');
        return $response;
    }

        /*
     * Extracts picture's data from DB and makes an image
     */
    public function showCategoryImage(Request $request,$name)
    {
        $picture = CategoryImage::where('name', $name)->first();

        if(!isset($picture)){
            return "image not found";
        }
        $pic = base64_decode($picture->base64);


        $response = Response::make($pic);

        //setting content-type
        $response->header('Content-Type','image/jpeg');
        return $response;
    }



    public function showSystemUserImage(Request $request,$system_user_id)
    {
        $picture = SystemUserImage::where('system_user_id', $system_user_id)->first();

        if(!isset($picture)){
            return "image not found";
        }
        $pic = base64_decode($picture->base64);


        $response = Response::make($pic);

        //setting content-type
        $response->header('Content-Type','image/jpeg');
        return $response;
    }

    public function saveImage(Request $request)
    {

        //  $file = Input::file('img');
        //  $img = Image::make($file);
        //  Response::make($img->encode('jpeg'));

        //  $picture = new Picture;
        //  $picture->name = $request->get('name');
        //  $picture->img = $img;
        //  $picture->save();

        //  $response = Response::make($picture->img->encode('jpeg'));
        //  //setting content-type
        //  $response->header('Content-Type', 'image/jpeg');
        //  return $response;
    }

    public function mShowPartImage($name)
    {
        $image = PartImage::where('name', $name)->first();

        if(!isset($image)){
            return "image not found";
        }
        $pic = base64_decode($image->base64);

        $response = Response::make($pic);

        //setting content-type
        $response->header('Content-Type','image/jpeg');
        return $response;
    }

}
