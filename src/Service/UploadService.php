<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Permet d'effectuer un upload
 */

class UploadService{

  public function upload(UploadedFile $file, string $oldFile = null): string
  {
    $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

    //Slugification du nom de fichier
    $slugger = new AsciiSlugger();
    $safeFileName = $slugger->slug($originalFileName);
    $uniqueId = uniqid();
    
    //Nouveau nom de fichier
    $newFileName = "$safeFileName-$uniqueId.{$file->guessExtension()}";

    //Instancie le composant Symfony Filesystem
    $filesystem = new Filesystem();

    //Si l'argument $oldFile est different de null et que le fichier existe
    if($oldFile !== null && $filesystem->exists($oldFile) && $oldFile!== 'imgs/user_default.jpg'){
      $filesystem->remove($oldFile);
    }

    //Upload dans le dossier avatar
    $file->move('avatars', $newFileName);

    return $newFileName;
  }
}