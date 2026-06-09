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
    protected $prodiId;

    public function __construct(bool $isPdf = false, $prodiId = null)
    {
        $this->isPdf = $isPdf;
        $this->prodiId = $prodiId;
    }

    public function view(): View
    {
        $query = Dosen::with(['prodi', 'mataKuliahs']);
        if ($this->prodiId) {
            $query->where('id_prodi', $this->prodiId);
        }
        return view('exports.dosen', [
            'dosens' => $query->get(),
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
            'F' => 40, // Mata Kuliah
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
