<?php
namespace App\Controllers;

use App\Libraries\Money;
use App\Models\ProductModel;
use App\Services\ImageStorage;

class ProductController extends BaseController
{
    private array $rules = [
        'name' => 'required|max_length[100]',
        'price' => 'required|max_length[11]|regex_match[/^\d{1,8}(\.\d{1,2})?$/]',
        'stock_quantity' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[1000000]',
    ];
    public function index(): string
    {
        $model = new ProductModel();
        $q = mb_substr(trim((string) $this->request->getGet('q')), 0, 100);
        if ($q !== '') { $model->like('name', $q); }
        $products = $model->orderBy('id', 'DESC')->paginate(12);
        return $this->render('products/index', ['title' => 'Products', 'products' => $products, 'pager' => $model->pager, 'q' => $q]);
    }
    public function new(): string { return $this->render('products/form', ['title' => 'Add product', 'product' => null]); }
    public function edit(int $id): string
    {
        return $this->render('products/form', ['title' => 'Edit product', 'product' => $this->found((new ProductModel())->find($id))]);
    }
    public function create() { return $this->save(null); }
    public function update(int $id) { return $this->save($id); }
    private function save(?int $id)
    {
        $model = new ProductModel(); $old = $id ? $this->found($model->find($id)) : null;
        $back = $id ? 'products/' . $id . '/edit' : 'products/new';
        $rules = $this->rules;
        if ($id) { $rules['original_stock'] = 'required|integer|greater_than_equal_to[0]'; }
        if (!$this->validateData($this->request->getPost(), $rules)) { return $this->invalid($back); }
        $storage = new ImageStorage(); $image = null; $db = db_connect(); $inTransaction = false;
        try {
            $price = Money::decimal(Money::cents((string) $this->request->getPost('price')));
            $image = $storage->store($this->request->getFile('image'), 'products');
            $data = ['name' => trim((string) $this->request->getPost('name')), 'price' => $price, 'stock_quantity' => (int) $this->request->getPost('stock_quantity')];
            if ($image) { $data['image'] = $image; }
            if ($id) {
                $db->transBegin(); $inTransaction = true;
                $lock = $db->DBDriver === 'MySQLi' ? ' FOR UPDATE' : '';
                $fresh = $db->query('SELECT stock_quantity FROM products WHERE id = ? AND deleted_at IS NULL' . $lock, [$id])->getRowArray();
                if (!$fresh || (int) $fresh['stock_quantity'] !== (int) $this->request->getPost('original_stock')) {
                    throw new \InvalidArgumentException('Stock changed since this form was opened. Reload this page before editing stock.');
                }
                $ok = $model->update($id, $data);
                if (!$ok || !$db->transStatus() || !$db->transCommit()) { throw new \RuntimeException('Product update failed.'); }
                $inTransaction = false;
            } else { $ok = $model->insert($data); }
            if (!$ok) { throw new \RuntimeException('Product save failed.'); }
        } catch (\Throwable $e) {
            if ($inTransaction) { $db->transRollback(); }
            $storage->delete('products', $image);
            log_message('error', 'Product save: {message}', ['message' => $e->getMessage()]);
            return $this->backWithInput($back)->with('error', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'The product could not be saved. Check your image and try again.');
        }
        if ($image && $old) { $storage->delete('products', $old['image']); }
        return redirect()->to(site_url('products'))->with('success', $id ? 'Product updated.' : 'Product added.');
    }
    public function delete(int $id)
    {
        $model = new ProductModel(); $this->found($model->find($id));
        if (!$model->delete($id)) { return redirect()->to(site_url('products'))->with('error', 'The product could not be archived.'); }
        return redirect()->to(site_url('products'))->with('success', 'Product archived. Past sales are preserved.');
    }
}
