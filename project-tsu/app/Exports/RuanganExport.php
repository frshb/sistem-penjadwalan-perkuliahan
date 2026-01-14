<?php

namespace App\Exports;

use App\Models\Ruangan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class RuanganExport implements FromView, ShouldAutoSize, WithEvents, WithColumnWidths
{
    protected $isPdf;

    public function __construct(bool $isPdf = false)
    {
        $this->isPdf = $isPdf;
    }

    public function view(): View
    {
        return view('exports.ruangan', [
            'ruangans' => Ruangan::with('gedung')->orderBy('id_gedung')->get(),
            'isPdf' => $this->isPdf
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 25, // Nama Ruangan
            'C' => 20, // Gedung
            'D' => 20, // Lokasi
            'E' => 35, // Fasilitas
            'F' => 12, // Kapasitas
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            },
        ];
    }
}
