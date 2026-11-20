<?php
namespace App\Models;
use CodeIgniter\Model;

class SaleModel extends Model
{
    protected $table = 'sales';
    protected $returnType = 'array';
    protected $allowedFields = ['product_id', 'customer_id', 'sold_by', 'quantity', 'total_price', 'product_name', 'customer_name', 'staff_name'];
    protected $useTimestamps = true;
    protected $updatedField = '';

    public function history(): self
    {
        return $this->select('sales.*, products.name AS current_product_name, customers.full_name AS current_customer_name, users.full_name AS current_staff_name')
            ->join('products', 'products.id = sales.product_id')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->join('users', 'users.id = sales.sold_by')
            ->orderBy('sales.id', 'DESC');
    }
}
