<?php

namespace Services;

use Exception;

class ImageService
{
    public function uploadImage(array $image): string
    {
        $validTypes = ['image/jpeg', 'image/png'];
        $imgDir = '/img/recipes/';
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . $imgDir;

        if (!in_array($image['type'], $validTypes)) {
            throw new Exception('Invalid file type. Only JPEG and PNG are allowed.');
        }

        $fileExtension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension; // Generate unique filename
        $uploadPath = $uploadDir . $fileName;

        if (!move_uploaded_file($image['tmp_name'], $uploadPath)) {
            throw new Exception('Error uploading file.');
        } else {
            return $imgDir . $fileName;
        }
    }

    public function deleteImage(string $imgPath): void
    {
        if (!empty($imgPath)) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . $imgPath;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }
}