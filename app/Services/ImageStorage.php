<?php
namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

final class ImageStorage
{
    public function store(?UploadedFile $file, string $kind): ?string
    {
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) { return null; }
        if (!in_array($kind, ['products', 'avatars'], true)) { throw new \InvalidArgumentException('Invalid image category.'); }
        if (!$file->isValid() || $file->getSize() > 2 * 1024 * 1024) { throw new \InvalidArgumentException('Upload a valid image no larger than 2 MB.'); }
        $mime = $file->getMimeType();
        $allowed = ['image/jpeg' => ['jpg', 'jpeg'], 'image/png' => ['png'], 'image/webp' => ['webp']];
        if (!isset($allowed[$mime]) || !in_array(strtolower($file->getClientExtension()), $allowed[$mime], true)) {
            throw new \InvalidArgumentException('Only JPEG, PNG, and WebP images with matching file extensions are allowed.');
        }
        $info = @getimagesize($file->getTempName());
        if (!$info || $info[0] < 1 || $info[1] < 1 || $info[0] > 6000 || $info[1] > 6000 || $info[0] * $info[1] > 16000000) {
            throw new \InvalidArgumentException('Image dimensions must be at most 6000 pixels per side and 16 megapixels in total.');
        }
        if (($info['mime'] ?? '') !== $mime) { throw new \InvalidArgumentException('The uploaded image format is invalid.'); }
        $source = @imagecreatefromstring((string) file_get_contents($file->getTempName()));
        if (!$source) { throw new \InvalidArgumentException('The image cannot be decoded. Try a different image.'); }
        $width = imagesx($source); $height = imagesy($source);
        if ($kind === 'avatars') {
            $side = min($width, $height);
            $sx = (int) (($width - $side) / 2); $sy = (int) (($height - $side) / 2);
            $sw = $sh = $side; $dw = $dh = 240;
        } else {
            $ratio = min(1, 900 / max($width, $height));
            $dw = max(1, (int) round($width * $ratio)); $dh = max(1, (int) round($height * $ratio));
            $sx = $sy = 0; $sw = $width; $sh = $height;
        }
        $target = imagecreatetruecolor($dw, $dh);
        imagefill($target, 0, 0, imagecolorallocate($target, 245, 247, 250));
        imagecopyresampled($target, $source, 0, 0, $sx, $sy, $dw, $dh, $sw, $sh);
        $name = bin2hex(random_bytes(20)) . '.jpg';
        $dir = WRITEPATH . 'uploads/' . $kind;
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) { throw new \RuntimeException('The upload directory is not writable.'); }
        // Re-encode all files: user filenames, EXIF metadata and embedded payloads
        // are never copied to the stored image. No file is stored in public/.
        $ok = imagejpeg($target, $dir . '/' . $name, 85);
        imagedestroy($source); imagedestroy($target);
        if (!$ok) { throw new \RuntimeException('The prepared image could not be saved.'); }
        return $name;
    }
    public function delete(string $kind, ?string $name): void
    {
        if (in_array($kind, ['products', 'avatars'], true) && $name && preg_match('/^[a-f0-9]{40}\.jpg$/D', $name)) {
            $path = WRITEPATH . 'uploads/' . $kind . '/' . $name;
            if (is_file($path)) { unlink($path); }
        }
    }
}
