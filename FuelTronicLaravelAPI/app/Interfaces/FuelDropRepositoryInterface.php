<?php
namespace App\Interfaces;

interface FuelDropRepositoryInterface
{
    public function getAll();

    public function store($data);

    public function show($id);

    public function update($id, $data);

    public function delete($id);

    public function create($siteId);

    public function edit($siteId);
}

