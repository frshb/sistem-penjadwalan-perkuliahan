<?php

namespace App\Exports;

use App\Models\Jadwal;
use App\Models\Slot_waktu;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class JadwalExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private int $tahunAkademikId) {}

    public function title(): string
    {
        return 'Jadwal Kuliah';
    }

    public function headings(): array
    {
        return [
            'Hari', 'Program Studi', 'Semester', 'Sesi',
            'Kelas', 'Kode MK', 'Mata Kuliah', 'Jenis',
            'SKS', 'Dosen', 'Ruangan', 'Jam Mulai', 'Jam Selesai',
        ];
    }

        public function collection()
    {
        return Jadwal::with([
                'kelas.matakuliah',
                'kelas.prodi',
                'kelas.dosen',
                'slotMulai',
                'hari',
                'ruangan',
            ])
            ->where('id_tahunakademik', $this->tahunAkademikId) // ← fix
            ->get()
            ->map(function ($j) {
                $slotId      = $j->id_slot_mulai; // ← fix
                $sks         = $j->durasi_sks;    // ← fix
                $slotSelesai = Slot_waktu::find($slotId + $sks - 1);

                return [
                    ucfirst($j->hari->nama_hari ?? '-'),
                    $j->kelas->prodi->nama_prodi       ?? '-',
                    $j->kelas->semester                ?? '-',
                    $slotId <= 11 ? 'Pagi' : 'Malam',
                    $j->kelas->nama_kelas              ?? '-',
                    $j->kelas->matakuliah->kode_matkul ?? '-',
                    $j->kelas->matakuliah->nama_matkul ?? '-',
                    $j->kelas->matakuliah->jenis ?? 'Teori',  // ← baca dari DB
                    $sks,
                    $j->kelas->dosen->nama_dosen       ?? '-',
                    $j->ruangan->nama_ruang            ?? '-',
                    $j->slotMulai->waktu_mulai         ?? '-',
                    $slotSelesai->waktu_selesai        ?? '-',
                ];
            })
            ->sortBy(fn ($r) => [
                match (strtolower($r[0])) {
                    'senin'  => 1, 'selasa' => 2, 'rabu'  => 3,
                    'kamis'  => 4, 'jumat'  => 5, default => 6,
                },
                $r[11],
            ])
            ->values();
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = 'M';

        // Auto width kolom
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header styling
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0F766E'], // teal-700
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFE5E7EB'],
                ],
            ],
        ]);

        // Row height header
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Zebra striping & border untuk data
        for ($row = 2; $row <= $lastRow; $row++) {
            $bgColor = ($row % 2 === 0) ? 'FFF0FDFA' : 'FFFFFFFF'; // teal-50 / white
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => $bgColor],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFE5E7EB'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Freeze baris header
        $sheet->freezePane('A2');

        return [];
    }
}
