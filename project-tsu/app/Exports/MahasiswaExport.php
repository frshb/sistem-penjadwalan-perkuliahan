<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class MahasiswaExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $isPdf;
    protected $prodiId;

    public function __construct(bool $isPdf = false, $prodiId = null)
    {
        $this->isPdf = $isPdf;
        $this->prodiId = $prodiId;
    }

    public function view(): View
    {
        return view('exports.mahasiswa', [
            'data' => [], // Empty data
            'isPdf' => $this->isPdf
        ]);
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
