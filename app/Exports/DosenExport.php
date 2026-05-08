<?php

namespace App\Exports;

use App\Models\Dosen;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class DosenExport implements FromView, ShouldAutoSize, WithEvents, WithColumnWidths
{
    protected $isPdf;

    public function __construct(bool $isPdf = false)
    {
        $this->isPdf = $isPdf;
    }

    public function view(): View
    {
        return view('exports.dosen', [
            'dosens' => Dosen::with('prodi')->get(),
            'isPdf' => $this->isPdf
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 35, // Nama Dosen
            'C' => 15, // NIDN
            'D' => 25, // Prodi
            'E' => 20, // Fakultas

        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                
                if ($this->isPdf) {
                    $event->sheet->getStyle('A1:G6')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
                }
            },
        ];
    }
}
