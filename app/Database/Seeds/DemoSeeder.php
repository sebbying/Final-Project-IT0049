<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        foreach ([['House blend coffee · 250 g', '289.00', 25], ['Ceramic daily mug', '199.50', 12], ['Oat cookies · 6 pack', '89.75', 4], ['Cold brew · 330 ml', '129.00', 18]] as [$name, $price, $stock]) {
            if ($this->db->table('products')->where('name', $name)->countAllResults() === 0) {
                $this->db->table('products')->insert(['name' => $name, 'price' => $price, 'stock_quantity' => $stock, 'created_at' => $now]);
            }
        }
        foreach ([['Alex Reyes', 'alex@example.test', '09171234567'], ['Sam Cruz', 'sam@example.test', '09181234567']] as [$name, $email, $phone]) {
            if ($this->db->table('customers')->where('email', $email)->countAllResults() === 0) {
                $this->db->table('customers')->insert(['full_name' => $name, 'email' => $email, 'phone' => $phone, 'created_at' => $now]);
            }
        }
        echo "Demo products and customers are ready. Create your first staff member at /setup.\n";
    }
}
