<?php

use Slim\Http\UploadedFile;

class ImageManagement {

    static $directory = __DIR__ . "/images";
    
    public static function process_incoming_image(UploadedFile $uploadedFile, $client_name = null) {

        if ($uploadedFile->getClientFilename() != null) {
            self::ensure_directory_existence();

            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            if ($client_name != null){
                $basename = str_replace(" ", "_", strtolower($client_name)) . "_" . bin2hex(random_bytes(2));
            } else {
                $basename = bin2hex(random_bytes(8));
            }

            $filename = sprintf('%s.%0.8s', $basename, $extension);
            $uploadedFile->moveTo(self::$directory . DIRECTORY_SEPARATOR . $filename);
    
            return $filename;
            
        } else return null;

    }

    static function ensure_directory_existence() {
        if (!file_exists(__DIR__ . "/images")) {
            mkdir(__DIR__ . "/images");
        }
    }

}