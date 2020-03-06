<?php
namespace App\Interfaces;

interface SupplierRepositoryInterface
{
    public function getAll();

    public function store($data);

    public function show($id);

    public function update($id, $data);

    public function delete($id);
}

