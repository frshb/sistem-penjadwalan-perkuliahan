<?php

namespace App\Exports;

use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DosenExport implements FromCollection, WithHeadings, WithMapping, WithDrawings, WithStyles, WithCustomStartCell, WithEvents
{
    private $rowNum = 0;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Dosen::all();
    }

    public function startCell(): string
    {
        return 'A6'; // Tabel mulai dari A6
    }

    /**
     * @var $dosen
     * @return array
     */
    public function map($dosen): array
    {
        return [
            ++$this->rowNum,
            $dosen->nama_dosen,
            $dosen->nidn,
            'Teknik Informatika', // Dummy Prodi
            'Fakultas Teknik',    // Dummy Fakultas
            $dosen->mata_kuliah,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Dosen',
            'NIDN',
            'Prodi',
            'Fakultas',
            'Mata Kuliah',
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo TSU');
        $drawing->setDescription('Logo TSU');
        $drawing->setPath(public_path('1151.jpg')); // Pastikan path ini benar/ada
        $drawing->setHeight(80);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    public function styles(Worksheet $sheet)
    {
        // Style Header Tabel (Row 6)
        $sheet->getStyle('A6:F6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0f766e'], // Teal-700 equiv
            ],
        ]);

        // Style Seluruh Tabel (Borders)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A6:F' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style Kolom No (Center)
        $sheet->getStyle('A6:A' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Auto Size Columns
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set Row Height untuk Tabel (Sedikit lebih tinggi)
        for ($i = 6; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(25);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Judul Besar di Atas
                $event->sheet->mergeCells('C2:F2');
                $event->sheet->setCellValue('C2', 'DAFTAR DOSEN');
                
                $event->sheet->mergeCells('C3:F3');
                $event->sheet->setCellValue('C3', 'SISTEM PENJADWALAN KULIAH TSU');

                // Style Judul
                $event->sheet->getStyle('C2:C3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
