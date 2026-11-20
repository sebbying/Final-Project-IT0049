<?php
namespace App\Controllers;

use App\Models\{ProductModel, CustomerModel, UserModel, SaleModel};

class DashboardController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        return $this->render('dashboard', [
            'title' => 'Overview',
            'productCount' => (new ProductModel())->countAllResults(),
            'customerCount' => (new CustomerModel())->countAllResults(),
            'staffCount' => (new UserModel())->countAllResults(),
            'revenue' => $db->table('sales')->selectSum('total_price', 'revenue')->get()->getRow('revenue') ?? 0,
            'lowStock' => (new ProductModel())->where('stock_quantity <=', 5)->orderBy('stock_quantity')->findAll(6),
            'recentSales' => (new SaleModel())->history()->findAll(6),
        ]);
    }
}
