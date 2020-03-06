<?php
namespace App\Interfaces;

interface TankRepositoryInterface
{
    public function getAll();

    public function store($data);

    public function show($id);

    public function update($id, $data);

    public function delete($id);

    public function updateFuel($tank_id, $litres);
}

