<?php
namespace App\Controllers;

use App\Models\{ProductModel, CustomerModel, SaleModel};
use App\Services\{SaleService, SaleException};

class SaleController extends BaseController
{
    public function index(): string
    {
        $model = new SaleModel();
        return $this->render('sales/index', ['title' => 'Sales history', 'sales' => $model->history()->paginate(20), 'pager' => $model->pager]);
    }
    public function new(): string
    {
        return $this->render('sales/form', ['title' => 'Record a sale', 'products' => (new ProductModel())->where('stock_quantity >', 0)->orderBy('name')->findAll(), 'customers' => (new CustomerModel())->orderBy('full_name')->findAll()]);
    }
    public function create()
    {
        if (!$this->validateData($this->request->getPost(), [
            'product_id' => 'required|is_natural_no_zero|less_than_equal_to[2147483647]',
            'customer_id' => 'permit_empty|is_natural_no_zero|less_than_equal_to[2147483647]',
            'quantity' => 'required|is_natural_no_zero|less_than_equal_to[1000000]',
        ])) { return $this->invalid('sales/new'); }
        $customer = $this->request->getPost('customer_id');
        try {
            $id = (new SaleService(db_connect()))->record((int) $this->request->getPost('product_id'), $customer !== '' && $customer !== null ? (int) $customer : null, (int) session('user_id'), (int) $this->request->getPost('quantity'));
        } catch (SaleException $e) {
            return $this->backWithInput('sales/new')->with('error', $e->getMessage());
        }
        return redirect()->to(site_url('sales'))->with('success', 'Sale #' . $id . ' recorded. Inventory has been updated.');
    }
}
