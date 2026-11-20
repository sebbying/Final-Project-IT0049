<?php
namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session('user_id')) { return redirect()->to(site_url('/')); }
        if ((new UserModel())->withDeleted()->countAllResults() === 0) { return redirect()->to(site_url('setup')); }
        return $this->render('auth/login', ['title' => 'Staff sign in']);
    }
    public function authenticate()
    {
        if (!service('throttler')->check('pos-login-' . hash('sha256', $this->request->getIPAddress()), 8, 60)) {
            return redirect()->to(site_url('login'))->with('error', 'Too many sign-in attempts. Wait one minute and try again.');
        }
        if (!$this->validateData($this->request->getPost(), ['username' => 'required|max_length[50]', 'password' => 'required|max_length[255]'])) {
            return redirect()->to(site_url('login'))->with('error', 'Enter your username and password.');
        }
        $model = new UserModel();
        $user = $model->where('username', trim((string) $this->request->getPost('username')))->first();
        // A fixed valid dummy hash avoids skipping password hashing for unknown accounts.
        $hash = $user['password'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';
        $verified = password_verify((string) $this->request->getPost('password'), $hash);
        if (!$user || !$verified) { return redirect()->to(site_url('login'))->with('error', 'Invalid username or password.'); }
        session()->regenerate(true);
        session()->set(['user_id' => (int) $user['id'], 'auth_stamp' => hash('sha256', $user['password'])]);
        return redirect()->to(site_url('/'))->with('success', 'Welcome back, ' . $user['full_name'] . '.');
    }
    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'));
    }
}
