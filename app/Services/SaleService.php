<?php
namespace App\Services;

use App\Libraries\Money;
use CodeIgniter\Database\BaseConnection;

final class SaleService
{
    public function __construct(private BaseConnection $db) {}

    public function record(int $productId, ?int $customerId, int $staffId, int $quantity): int
    {
        if ($quantity < 1 || $quantity > 1000000) { throw new SaleException('Quantity must be from 1 to 1,000,000.'); }
        $db = $this->db;
        // InnoDB row locks serialize sales of the same product. The conditional
        // decrement below is an additional guard, including for SQLite tests.
        $lock = $db->DBDriver === 'MySQLi' ? ' FOR UPDATE' : '';
        if (!$db->transBegin()) { throw new SaleException('Could not start a sale. Please try again.'); }
        try {
            $staff = $db->query('SELECT id, full_name FROM users WHERE id = ? AND deleted_at IS NULL' . $lock, [$staffId])->getRowArray();
            if (!$staff) { throw new SaleException('Your staff account is unavailable. Please sign in again.'); }
            $product = $db->query('SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND deleted_at IS NULL' . $lock, [$productId])->getRowArray();
            if (!$product) { throw new SaleException('Choose an available product.'); }
            if ($quantity > (int) $product['stock_quantity']) {
                throw new SaleException('Insufficient stock: only ' . $product['stock_quantity'] . ' unit(s) of ' . $product['name'] . ' remain.');
            }
            $customer = null;
            if ($customerId !== null) {
                $customer = $db->query('SELECT id, full_name FROM customers WHERE id = ? AND deleted_at IS NULL' . $lock, [$customerId])->getRowArray();
                if (!$customer) { throw new SaleException('That customer is unavailable. Select another customer or Walk-in.'); }
            }
            $total = Money::total((string) $product['price'], $quantity);
            $updated = $db->query('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND deleted_at IS NULL AND stock_quantity >= ?', [$quantity, $productId, $quantity]);
            if (!$updated || $db->affectedRows() !== 1) { throw new SaleException('Stock changed while recording this sale. Please check the quantity and try again.'); }
            $saved = $db->table('sales')->insert([
                'product_id' => $productId, 'customer_id' => $customerId, 'sold_by' => $staffId,
                'quantity' => $quantity, 'total_price' => $total, 'created_at' => date('Y-m-d H:i:s'),
                'product_name' => $product['name'], 'customer_name' => $customer['full_name'] ?? null,
                'staff_name' => $staff['full_name'],
            ]);
            if (!$saved || !$db->transStatus()) { throw new \RuntimeException('Sale insert failed.'); }
            $id = (int) $db->insertID();
            if (!$db->transCommit()) { throw new \RuntimeException('Sale commit failed.'); }
            return $id;
        } catch (\Throwable $e) {
            $db->transRollback();
            if ($e instanceof SaleException) { throw $e; }
            if ($e instanceof \InvalidArgumentException) { throw new SaleException($e->getMessage(), 0, $e); }
            log_message('error', 'Sale rolled back: {message}', ['message' => $e->getMessage()]);
            throw new SaleException('The sale could not be saved. No stock was deducted. Please try again.', 0, $e);
        }
    }
}
