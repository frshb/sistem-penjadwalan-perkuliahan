<?php

namespace App\Exports;

use App\Models\Prodi;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ProdiExport implements FromView, ShouldAutoSize, WithEvents, WithColumnWidths
{
    protected $isPdf;

    public function __construct(bool $isPdf = false)
    {
        $this->isPdf = $isPdf;
    }

    public function view(): View
    {
        return view('exports.prodi', [
            'prodis' => Prodi::all(),
            'isPdf' => $this->isPdf
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 40, // Program Studi
            'C' => 20, // Kode Prodi
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
            },
        ];
    }
}
