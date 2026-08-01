<?php

namespace App\Exports;

use App\Models\Jadwal;
use App\Models\Slot_waktu;
use App\Models\TahunAkademik;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use App\Helpers\ProdiFilter;

class JadwalExport implements FromCollection, WithStyles, WithTitle, WithDrawings
{
    public function __construct(private int $tahunAkademikId) {}

    public function title(): string
    {
        return 'Worksheet';
    }

    public function drawings()
    {
        if (!file_exists(public_path('1151.jpg'))) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo TSU');
        $drawing->setDescription('Logo TSU');
        $drawing->setPath(public_path('1151.jpg'));
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(15);
        $drawing->setOffsetY(8);

        return [$drawing];
    }

    public function collection()
    {
        $tahunAkademik = TahunAkademik::find($this->tahunAkademikId);
        $slots = Slot_waktu::orderBy('id_slot')->get();
        $totalSlots = $slots->count() ?: 18;

        $emptyRow = array_fill(0, 10 + $totalSlots, '');

        // Row 1-4: Title and Header Info
        $row1 = $emptyRow;
        $row1[0] = 'JADWAL KULIAH ' . strtoupper($tahunAkademik->nama_tahunakademik ?? 'SEMESTER GENAP 2025/2026');

        $row2 = $emptyRow;
        $row2[0] = 'FAKULTAS TEKNIK';

        $row3 = $emptyRow;
        $row3[0] = 'UNIVERSITAS TIGA SERANGKAI';

        $row4 = $emptyRow;
        $row4[0] = 'Waktu Cetak : ' . now()->locale('id')->translatedFormat('d F Y, H:i') . ' WIB';

        // Row 5: Table Header 1 (Time ranges)
        $header1 = [
            'Prodi', 'Smt', 'Kelas', 'Sesi', 'Kode MK',
            'Mata Kuliah', 'Jenis', 'SKS', 'Dosen', 'Hari'
        ];
        foreach ($slots as $slot) {
            $mulai   = substr($slot->waktu_mulai, 0, 5);
            $selesai = substr($slot->waktu_selesai, 0, 5);
            $header1[] = "{$mulai}-{$selesai}";
        }

        // Row 6: Table Header 2 (Slot numbers)
        $header2 = [
            'Prodi', 'Smt', 'Kelas', 'Sesi', 'Kode MK',
            'Mata Kuliah', 'Jenis', 'SKS', 'Dosen', 'Hari'
        ];
        foreach ($slots as $idx => $slot) {
            $header2[] = (string)($idx + 1);
        }

        $prodiId = ProdiFilter::getProdiId();
        $query = Jadwal::with([
                'kelas.matakuliah',
                'kelas.prodi',
                'kelas.dosen',
                'slotMulai',
                'hari',
                'ruangan',
            ])
            ->where('id_tahunakademik', $this->tahunAkademikId);

        if ($prodiId) {
            $query->whereHas('kelas', fn($q) => $q->where('id_prodi', $prodiId));
        }

        $sortedJadwals = $query->get()
            ->sortBy(function ($j) {
                return [
                    match (strtolower($j->hari?->nama_hari ?? '')) {
                        'senin'  => 1, 'selasa' => 2, 'rabu'  => 3,
                        'kamis'  => 4, 'jumat'  => 5, 'sabtu' => 6, default => 7,
                    },
                    strtolower($j->kelas?->matakuliah?->nama_matkul ?? ''),
                    strtolower($j->kelas?->dosen?->nama_dosen ?? ''),
                    strtolower($j->kelas?->nama_kelas ?? ''),
                    (int) $j->id_slot_mulai,
                ];
            });

        $jadwalRows = [];
        $lastDay = null;
        $emptyRow = array_fill(0, 10 + count($slots), '');

        foreach ($sortedJadwals as $j) {
            $currentDay = strtolower(trim($j->hari?->nama_hari ?? ''));

            if ($lastDay !== null && $currentDay !== $lastDay) {
                // 1 baris kosong sebagai pembatas hari
                $jadwalRows[] = $emptyRow;
            }
            $lastDay = $currentDay;

            $startSlot = (int) $j->id_slot_mulai;
            $sks       = (int) $j->durasi_sks;
            $ruangName = $j->ruangan?->nama_ruang ?? '-';

            $row = [
                $j->kelas->prodi->nama_prodi       ?? '-',
                $j->kelas->semester                ?? '-',
                $j->kelas->nama_kelas              ?? '-',
                $startSlot <= 11 ? 'Pagi' : 'Malam',
                $j->kelas->matakuliah->kode_matkul ?? '-',
                $j->kelas->matakuliah->nama_matkul ?? '-',
                $j->kelas->matakuliah->jenis       ?? 'teori',
                $sks,
                $j->kelas->dosen->nama_dosen       ?? '-',
                ucfirst($j->hari->nama_hari ?? '-'),
            ];

            foreach ($slots as $slot) {
                $slotNum = (int) $slot->id_slot;
                if ($startSlot > 0 && $slotNum >= $startSlot && $slotNum < ($startSlot + $sks)) {
                    $row[] = $ruangName;
                } else {
                    $row[] = '';
                }
            }

            $jadwalRows[] = $row;
        }

        if (!empty($jadwalRows)) {
            // Tambahkan 1 baris kosong pembatas di akhir tabel
            $jadwalRows[] = $emptyRow;
        }

        return collect(array_merge([
            $row1, $row2, $row3, $row4, $header1, $header2
        ], $jadwalRows));
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $totalSlots = Slot_waktu::count() ?: 18;
        $lastColIndex = 10 + $totalSlots;
        $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);

        // Set default font to Inter for the entire sheet
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Inter');

        // 1. Set column widths
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        for ($c = 11; $c <= $lastColIndex; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setWidth(13);
        }

        // 2. Format Top Title (Rows 1 to 3)
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->mergeCells("A4:{$lastCol}4");

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['name' => 'Inter', 'bold' => true, 'size' => 16, 'color' => ['argb' => 'FF0F766E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A2:{$lastCol}3")->applyFromArray([
            'font' => ['name' => 'Inter', 'bold' => true, 'size' => 13, 'color' => ['argb' => 'FF0F766E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => ['name' => 'Inter', 'bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1F2937']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getRowDimension(3)->setRowHeight(24);
        $sheet->getRowDimension(4)->setRowHeight(24); // Tinggi normal seperti baris lainnya

        // 3. Format Table Headers (Rows 5 and 6)
        foreach (range('A', 'J') as $col) {
            $sheet->mergeCells("{$col}5:{$col}6");
        }

        $sheet->getStyle("A5:{$lastCol}6")->applyFromArray([
            'font' => [
                'name'  => 'Inter',
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11, // Diperbesar
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0F766E'], // teal-700 (#0f766e)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF99F6E4'], // teal-200
                ],
            ],
        ]);

        // Rotated vertical headers for slot time ranges (Row 5, Col K onwards)
        $sheet->getStyle("K5:{$lastCol}5")->getAlignment()->setTextRotation(90);

        $sheet->getRowDimension(5)->setRowHeight(80);
        $sheet->getRowDimension(6)->setRowHeight(24);

        // 4. Format Data Rows (Row 7 to $lastRow)
        if ($lastRow >= 7) {
            for ($row = 7; $row <= $lastRow; $row++) {
                $valA = $sheet->getCell("A{$row}")->getValue();
                $valJ = $sheet->getCell("J{$row}")->getValue();
                $isSeparator = (($valA === null || $valA === '') && ($valJ === null || $valJ === ''));

                if ($isSeparator) {
                    // 1 baris full hitam kosong sebagai pembatas hari (tinggi normal 24px)
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['name' => 'Inter'],
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF000000'], // Full hitam
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['argb' => 'FF000000'],
                            ],
                        ],
                    ]);
                } else {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['name' => 'Inter'],
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFFFFF'], // Solid white, tanpa selang-seling warna
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['argb' => 'FFE5E7EB'], // gray-200
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                }
                $sheet->getRowDimension($row)->setRowHeight(24);
            }

            // Center align columns: Smt, Kelas, Sesi, Jenis, SKS, Hari
            $sheet->getStyle("B7:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G7:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J7:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Center align and highlight room names in slot columns
            $sheet->getStyle("K7:{$lastCol}{$lastRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'font' => ['name' => 'Inter', 'bold' => true, 'color' => ['argb' => 'FF0F766E']],
            ]);
        }

        // 5. AutoFilter & FreezePane
        $sheet->setAutoFilter("A6:{$lastCol}6");
        $sheet->freezePane('A7');

        return [];
    }
}

