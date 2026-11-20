<?php
namespace App\Models;
use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $returnType = 'array';
    protected $allowedFields = ['full_name', 'email', 'phone'];
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $updatedField = '';
}
