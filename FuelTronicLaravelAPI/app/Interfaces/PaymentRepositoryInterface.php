<?php
namespace App\Interfaces;

interface PaymentRepositoryInterface
{
    public function getAll();

    public function store($data);

    public function show($id);

    public function update($id, $data);

    public function delete($id);
}

