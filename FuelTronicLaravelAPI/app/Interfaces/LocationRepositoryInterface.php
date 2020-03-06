<?php
namespace App\Interfaces;

interface LocationRepositoryInterface
{
    public function getAll();

    public function store($data);

    public function show($id);

    public function update($id, $data);

    public function delete($id);

    public function create($site_id);

    public function edit($jobId);
}

