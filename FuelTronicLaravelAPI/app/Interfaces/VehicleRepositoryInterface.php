<?php
namespace App\Interfaces;

interface VehicleRepositoryInterface
{
    public function getAll();

    public function store($data);

    public function show($id);

    public function update($id, $data);

    public function delete($id);

    public function create();

    public function edit($vehicleId);
}

