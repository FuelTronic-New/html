<?php
namespace App\Interfaces;

interface AtgTransactionRepositoryInterface
{
    public function getAll();

    public function store($data);

    public function show($id);

    public function update($guid, $data);

    public function delete($id);
}

