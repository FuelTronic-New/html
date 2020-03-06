<?php
namespace App\Interfaces;

interface CustomerTransactionRepositoryInterface
{
    public function getAll();

    public function getTransactionsById($id);
    public function getTransactionsByIdWithDate($id,$start_date,$end_date);
    public function store($data);

    public function show($id);

    public function update($id, $data);

    public function delete($id);

    public function create($siteId);

    public function edit($siteId);
}

