<?php
namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\{RequestInterface, ResponseInterface};

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $user = (new UserModel())->find((int) $session->get('user_id'));
        if (!$user || !hash_equals(hash('sha256', $user['password']), (string) $session->get('auth_stamp'))) {
            $session->remove(['user_id', 'auth_stamp']);
            return redirect()->to(site_url('login'))->with('error', 'Please sign in to continue.');
        }
        return null;
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Cache-Control', 'no-store, private');
    }
}
