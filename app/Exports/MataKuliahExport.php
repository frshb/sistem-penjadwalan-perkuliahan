<?php

namespace App\Exports;

use App\Models\MataKuliah;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class MataKuliahExport implements FromView, ShouldAutoSize, WithEvents, WithColumnWidths
{
    protected $isPdf;

    public function __construct(bool $isPdf = false)
    {
        $this->isPdf = $isPdf;
    }

    public function view(): View
    {
        return view('exports.matakuliah', [
            'matkuls' => MataKuliah::with(['kurikulum', 'prodi'])->get(),
            'isPdf' => $this->isPdf
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15, // Kode
            'C' => 45, // Nama Matkul
            'D' => 8,  // SKS
            'E' => 12, // Tipe
            'F' => 10, // Semester
            'G' => 25, // Kurikulum
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                
                // Hapus border default dari PhpSpreadsheet untuk bagian header (baris 1 s/d 6)
                if ($this->isPdf) {
                    $event->sheet->getStyle('A1:G6')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
                }
            },
        ];
    }
}
