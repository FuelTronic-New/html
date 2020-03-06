<?php
namespace App\Interfaces;

interface GradeRepositoryInterface
{
    public function getAll();

    public function store($data);

    public function show($id);

    public function update($id, $data);

    public function delete($id);
}

