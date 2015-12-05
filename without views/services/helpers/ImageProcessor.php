<?php
namespace services\helpers;
class ImageProcessor{
  public function convertBase64ToImage($base64EncodedImage,$filepath)
  {
      $imageData = base64_decode($base64EncodedImage);
      $source    = imagecreatefromstring($imageData);
      $return    = imagejpeg($source,$filepath,100);
      return $return;
  }

}