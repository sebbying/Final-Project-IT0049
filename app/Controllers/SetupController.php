<?php
namespace App\Controllers;

use App\Models\UserModel;

class SetupController extends BaseController
{
    public function index()
    {
        if ((new UserModel())->withDeleted()->countAllResults() > 0) { return redirect()->to(site_url('login')); }
        return $this->render('auth/setup', ['title' => 'Create your first staff account']);
    }
    public function create()
    {
        $users = new UserModel();
        if ($users->withDeleted()->countAllResults() > 0) { return redirect()->to(site_url('login')); }
        if (!service('throttler')->check('pos-setup-' . hash('sha256', $this->request->getIPAddress()), 5, 60)) {
            return redirect()->to(site_url('setup'))->with('error', 'Too many attempts. Wait one minute and try again.');
        }
        $expected = (string) env('POS_SETUP_TOKEN', '');
        $token = (string) $this->request->getPost('setup_token');
        if (strlen($expected) < 32 || str_contains($expected, 'REPLACE_') || !hash_equals($expected, $token)) {
            return redirect()->to(site_url('setup'))->with('error', 'Incorrect setup token. Use the generated POS_SETUP_TOKEN from your .env file.');
        }
        $rules = [
            'username' => 'required|alpha_dash|min_length[3]|max_length[50]',
            'full_name' => 'required|max_length[100]',
            'password' => 'required|min_length[12]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
        ];
        if (!$this->validateData($this->request->getPost(), $rules)) { return $this->invalid('setup'); }
        try {
            // A fixed first primary key also prevents two racing installers from
            // creating separate initial accounts. Later staff use AUTO_INCREMENT.
            $ok = db_connect()->table('users')->insert([
                'id' => 1, 'username' => trim((string) $this->request->getPost('username')),
                'full_name' => trim((string) $this->request->getPost('full_name')),
                'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            if (!$ok) { throw new \RuntimeException('Initial staff insert failed.'); }
        } catch (\Throwable $e) {
            log_message('error', 'Initial staff creation: {message}', ['message' => $e->getMessage()]);
            return redirect()->to(site_url('login'))->with('error', 'Could not create the first account. If it already exists, sign in. Otherwise check the database connection.');
        }
        return redirect()->to(site_url('login'))->with('success', 'Your staff account is ready. Sign in with the credentials you chose.');
    }
}
