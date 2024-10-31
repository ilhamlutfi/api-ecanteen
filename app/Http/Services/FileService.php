<?php

namespace App\Http\Services;

class FileService
{
    // proses upload
    public function upload($file, $path)
    {
        $uploaded = $file;
        $fileName = $uploaded->hashName();

        return $uploaded->storeAs($path, $fileName, 'public');
    }

    // proses delete
    public function delete($file)
    {
        $deleted = unlink(storage_path('app/public/' .  $file));

        return $deleted;
    }
}
