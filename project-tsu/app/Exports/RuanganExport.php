<?php

namespace App\Exports;

use App\Models\Ruangan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RuanganExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Ruangan::with('gedung')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Ruangan',
            'Gedung',
            'Lokasi',
            'Fasilitas',
            'Kapasitas',
        ];
    }

    public function map($ruangan): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $ruangan->nama_ruang,
            $ruangan->gedung->nama_gedung ?? '-',
            $ruangan->gedung->lokasi ?? '-',
            $ruangan->fasilitas,
            $ruangan->kapasitas,
        ];
    }
}
