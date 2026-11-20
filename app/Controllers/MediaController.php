<?php
namespace App\Controllers;

class MediaController extends BaseController
{
    public function show(string $kind, string $name)
    {
        if (!in_array($kind, ['products', 'avatars'], true) || !preg_match('/^[a-f0-9]{40}\.jpg$/D', $name)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $path = WRITEPATH . 'uploads/' . $kind . '/' . $name;
        if (!is_file($path)) { throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
        return $this->response->setContentType('image/jpeg')->setHeader('X-Content-Type-Options', 'nosniff')->setBody(file_get_contents($path));
    }
}
