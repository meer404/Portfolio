<?php
/**
 * Image Helper Class
 * Handles image validation, resizing, compression, and WebP conversion
 */
class ImageHelper {
    /**
     * Process an uploaded image
     * 
     * @param array $file The $_FILES['input_name'] array
     * @param string $uploadDir The directory to save the file to (relative or absolute)
     * @param string $filenameBase The prefix for the filename
     * @param int $maxWidth Maximum width for resizing (default 19200 to essentially disable resizing unless explicit, user wanted compression mostly. Let's start with 1920 defaults for "optimization", user said "image optimization")
     * @return string The generated filename
     * @throws Exception If validation or processing fails
     */
    public static function processUpload($file, $uploadDir, $filenameBase, $maxWidth = 1920) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Basic validation
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid upload parameters.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception('Image exceeded filesize limit.');
                default:
                    throw new Exception('Unknown upload error.');
            }
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('Image size must be less than 5MB.');
        }

        // MIME type validation
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid image type. Allowed: JPG, PNG, GIF, WebP.');
        }

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory.');
            }
        }

        // Load image resource
        $image = null;
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file['tmp_name']);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($file['tmp_name']);
                break;
        }

        if (!$image) {
            throw new Exception('Failed to load image resource.');
        }

        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // Resize if needed
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = floor($height * ($maxWidth / $width));
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency
            if ($mimeType == 'image/png' || $mimeType == 'image/webp' || $mimeType == 'image/gif') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $newImage;
        } else {
             // Ensure transparency is preserved even if not resized
             if ($mimeType == 'image/png' || $mimeType == 'image/webp') {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
             }
        }

        // Generate absolute unique filename
        $newFilename = $filenameBase . uniqid() . '.webp';
        $destination = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $newFilename;

        // Save as WebP with 80% quality
        if (!imagewebp($image, $destination, 80)) {
            imagedestroy($image);
            throw new Exception('Failed to save WebP image.');
        }

        // Retrieve valid pointer
        imagedestroy($image);

        return $newFilename;
    }
}
?>
