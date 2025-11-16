<?php

namespace App\Exports;

use App\Models\MataKuliah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MataKuliahExport implements FromCollection, WithHeadings, WithMapping
{
    private $rowNum = 0;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Ambil semua data matkul
        return MataKuliah::all();
    }

    /**
    * @var $matkul
    * @return array
    */
    public function map($matkul): array
    {
        // Atur data untuk setiap baris
        return [
            ++$this->rowNum,
            $matkul->nama_matkul,
            $matkul->jumlah_sks,
            $matkul->tipe,
            $matkul->semester,
            $matkul->kode_matkul,
        ];
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        // Definisikan header kolom
        return [
            'No',
            'Mata Kuliah',
            'Jumlah SKS',
            'Tipe',
            'Semester',
            'Kode Matkul',
        ];
    }
}
