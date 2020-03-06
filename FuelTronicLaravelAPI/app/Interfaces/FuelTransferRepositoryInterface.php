<?php
namespace App\Interfaces;

interface FuelTransferRepositoryInterface
{
    public function getAll();

    public function store($data);

    public function show($id);

    public function update($id, $data);

    public function delete($id);

    public function create($siteId);

    public function edit($siteId);
}

