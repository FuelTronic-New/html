<?php
namespace App\Interfaces;

interface SiteusersRepositoryInterface
{
    public function getAll($except);

    public function store($data);

    public function show($id);

    public function update($id, $data);

    public function delete($id);
}

