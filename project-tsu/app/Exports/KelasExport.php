<?php

namespace App\Exports;

use App\Models\Kelas;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class KelasExport implements FromView, ShouldAutoSize, WithEvents, WithColumnWidths
{
    protected $isPdf;
    protected $prodiId;
    protected $tahunId;

    public function __construct(bool $isPdf = false, $prodiId = null, $tahunId = null)
    {
        $this->isPdf = $isPdf;
        $this->prodiId = $prodiId;
        $this->tahunId = $tahunId;
    }

    public function view(): View
    {
        $query = Kelas::with(['prodi', 'tahunAkademik', 'matakuliah', 'dosen']);
        if ($this->prodiId) {
            $query->where('id_prodi', $this->prodiId);
        }
        if ($this->tahunId) {
            $query->where('id_tahunakademik', $this->tahunId);
        }
        return view('exports.kelas', [
            'kelas' => $query->get(),
            'isPdf' => $this->isPdf
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15, // Nama Kelas
            'C' => 10, // Semester
            'D' => 45, // Mata Kuliah
            'E' => 10, // Kapasitas
            'F' => 35, // Dosen Pengampu
            'G' => 25, // Prodi
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
