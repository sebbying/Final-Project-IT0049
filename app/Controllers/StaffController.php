<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Services\ImageStorage;

class StaffController extends BaseController
{
    public function index(): string
    {
        $model = new UserModel();
        return $this->render('staff/index', ['title' => 'Staff', 'staff' => $model->orderBy('id')->paginate(15), 'pager' => $model->pager]);
    }
    public function new(): string { return $this->render('staff/form', ['title' => 'Add staff member', 'staff' => null]); }
    public function edit(int $id): string
    {
        return $this->render('staff/form', ['title' => 'Edit staff member', 'staff' => $this->found((new UserModel())->find($id))]);
    }
    public function create() { return $this->save(null); }
    public function update(int $id) { return $this->save($id); }
    private function save(?int $id)
    {
        $model = new UserModel(); $old = $id ? $this->found($model->find($id)) : null;
        $back = $id ? 'staff/' . $id . '/edit' : 'staff/new';
        $unique = $id ? 'is_unique[users.username,id,' . $id . ']' : 'is_unique[users.username]';
        $rules = ['username' => 'required|alpha_dash|min_length[3]|max_length[50]|' . $unique, 'full_name' => 'required|max_length[100]', 'password' => ($id ? 'permit_empty' : 'required') . '|min_length[12]|max_length[72]'];
        if ((string) $this->request->getPost('password') !== '' || !$id) { $rules['password_confirm'] = 'required|matches[password]'; }
        if (!$this->validateData($this->request->getPost(), $rules)) { return $this->invalid($back); }
        $storage = new ImageStorage(); $avatar = null;
        try {
            $avatar = $storage->store($this->request->getFile('avatar'), 'avatars');
            $data = ['username' => trim((string) $this->request->getPost('username')), 'full_name' => trim((string) $this->request->getPost('full_name'))];
            if ((string) $this->request->getPost('password') !== '') { $data['password'] = password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT); }
            if ($avatar) { $data['avatar'] = $avatar; }
            if (!($id ? $model->update($id, $data) : $model->insert($data))) { throw new \RuntimeException('Staff save failed.'); }
        } catch (\Throwable $e) {
            $storage->delete('avatars', $avatar);
            log_message('error', 'Staff save: {message}', ['message' => $e->getMessage()]);
            return $this->backWithInput($back)->with('error', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'The staff member could not be saved. Check the username and image, then try again.');
        }
        if ($avatar && $old) { $storage->delete('avatars', $old['avatar']); }
        if ($id === (int) session('user_id') && isset($data['password'])) {
            session()->regenerate(true); session()->set('auth_stamp', hash('sha256', $data['password']));
        }
        return redirect()->to(site_url('staff'))->with('success', $id ? 'Staff member updated.' : 'Staff member added.');
    }
    public function delete(int $id)
    {
        if ($id === (int) session('user_id')) { return redirect()->to(site_url('staff'))->with('error', 'You cannot delete the account you are currently using.'); }
        $model = new UserModel(); $this->found($model->find($id)); $db = db_connect();
        $db->transBegin();
        try {
            $lock = $db->DBDriver === 'MySQLi' ? ' FOR UPDATE' : '';
            $active = $db->query('SELECT id FROM users WHERE deleted_at IS NULL ORDER BY id' . $lock)->getResultArray();
            if (count($active) <= 1) { throw new \RuntimeException('Keep at least one active staff account.'); }
            if (!$model->delete($id) || !$db->transStatus() || !$db->transCommit()) { throw new \RuntimeException('The staff member could not be deleted.'); }
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to(site_url('staff'))->with('error', 'Could not delete the staff member. Keep at least one active account and try again.');
        }
        return redirect()->to(site_url('staff'))->with('success', 'Staff member deleted. Their access is revoked and past sales are preserved.');
    }
}
