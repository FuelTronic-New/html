<?php
namespace App\Repositories;

use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Payment;

class PaymentRepository implements PaymentRepositoryInterface
{
    protected $model;

    public function __construct(Payment $payment)
    {
        $this->model = $payment;
    }

    public function getAll()
    {
        return auth()->user()->sites()
            ->with(['payments', 'payments.customer' => function ($q) {
                $q->selectRaw("id,name,first_name,last_name");
            }, 'payments.supplier' => function ($q) {
                $q->selectRaw("id,name,first_name,last_name");
            }])->get();
    }

    public function store($Data)
    {
        return $this->model->create($Data);
    }

    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    public function update($id, $Data)
    {
        return $this->model->findOrFail($id)->update($Data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

}