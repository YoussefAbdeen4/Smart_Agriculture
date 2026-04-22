<?php

namespace App\Http\Traits;

trait media
{
    public function uploadPhoto($file, $dir): string
    {
        $fileName = uniqid().'.'.$file->extension();
        $file->move(public_path("/img/$dir/"), $fileName);

        return $fileName;
    }

    public function deletePhoto($filePath): bool
    {
        if (file_exists($filePath)) {
            unlink($filePath);

            return true;
        }

        return false;
    }
}
