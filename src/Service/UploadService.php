<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Permet d'effectuer un upload
 */

class UploadService{

  public function upload(UploadedFile $file): string
  {
    $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

    //Slugification du nom de fichier
    $slugger = new AsciiSlugger();
    $safeFileName = $slugger->slug($originalFileName);
    $uniqueId = uniqid();
    $newFileName = "$safeFileName-$uniqueId.{$file->guessExtension()}";

    //Upload
    $file->move('avatars', $newFileName);

    return $newFileName;
  }
}