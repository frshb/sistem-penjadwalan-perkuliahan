<?php

namespace App\Exports;

use App\Models\Prodi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProdiExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Prodi::all();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Prodi',
            'Kode Prodi',
        ];
    }

    public function map($prodi): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $prodi->nama_prodi,
            $prodi->kode_prodi,
        ];
    }
}
