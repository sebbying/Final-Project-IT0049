<?php
namespace App\Controllers;

use App\Models\CustomerModel;

class CustomerController extends BaseController
{
    public function index(): string
    {
        $model = new CustomerModel();
        return $this->render('customers/index', ['title' => 'Customers', 'customers' => $model->orderBy('id', 'DESC')->paginate(15), 'pager' => $model->pager]);
    }
    public function new(): string { return $this->render('customers/form', ['title' => 'Add customer', 'customer' => null]); }
    public function edit(int $id): string
    {
        return $this->render('customers/form', ['title' => 'Edit customer', 'customer' => $this->found((new CustomerModel())->find($id))]);
    }
    public function create() { return $this->save(null); }
    public function update(int $id) { return $this->save($id); }
    private function save(?int $id)
    {
        $model = new CustomerModel(); if ($id) { $this->found($model->find($id)); }
        $back = $id ? 'customers/' . $id . '/edit' : 'customers/new';
        if (!$this->validateData($this->request->getPost(), [
            'full_name' => 'required|max_length[100]', 'email' => 'required|valid_email|max_length[100]',
            'phone' => 'permit_empty|max_length[20]|regex_match[/^[0-9+(). -]+$/]',
        ])) { return $this->invalid($back); }
        $data = ['full_name' => trim((string) $this->request->getPost('full_name')), 'email' => trim((string) $this->request->getPost('email')), 'phone' => trim((string) $this->request->getPost('phone'))];
        if (!($id ? $model->update($id, $data) : $model->insert($data))) {
            return $this->backWithInput($back)->with('error', 'The customer could not be saved. Please try again.');
        }
        return redirect()->to(site_url('customers'))->with('success', $id ? 'Customer updated.' : 'Customer added.');
    }
    public function delete(int $id)
    {
        $model = new CustomerModel(); $this->found($model->find($id));
        if (!$model->delete($id)) { return redirect()->to(site_url('customers'))->with('error', 'The customer could not be deleted.'); }
        return redirect()->to(site_url('customers'))->with('success', 'Customer deleted from the active list. Past sales are preserved.');
    }
}
