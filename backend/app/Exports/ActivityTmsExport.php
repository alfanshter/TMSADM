<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivityTmsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithDrawings
{
    protected $month;
    protected $data;

    public function __construct($month, $data)
    {
        $this->month = $month;
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            ['PM SCHEDULE - FY ' . date('Y')],
            [],
            [],
            [
                'NO',
                'ITEM MACHINE',
                'CODE',
                'LOCATION',
                'DATE',
                'SAFETY',
                'PRODUCTION',
                'CLEANING CRITICAL',
                'JUST CLEANING',
                'REPLACEMENT PART',
                'PREVENTIVE (PM)',
                'FOTO BEFORE (CLEANING CRITICAL)',
                'FOTO AFTER (CLEANING CRITICAL)',
                'FOTO BEFORE (JUST CLEANING)',
                'FOTO AFTER (JUST CLEANING)',
                'FOTO BEFORE (REPLACEMENT PART)',
                'FOTO AFTER (REPLACEMENT PART)',
                'FOTO BEFORE (PM)',
                'FOTO AFTER (PM)',
            ],
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $cc_before = ''; // kosongin karena nanti sudah pakai gambar
        $cc_after  = '';
        $jc_before = '';
        $jc_after  = '';
        $rp_before = '';
        $rp_after  = '';
        $pm_before = '';
        $pm_after  = '';

        return [
            $no,
            $row['name'] ?? '-',
            $row['code'] ?? '-',
            $row['location'] ?? '-',
            $row['date'] ?? '-',
            $row['scope_of_work']['safety'] ?? '',
            $row['scope_of_work']['production'] ?? '',
            $row['maintenance_type']['cleaning_critical'] ?? '',
            $row['maintenance_type']['just_cleaning'] ?? '',
            $row['maintenance_type']['replacement_part'] ?? '',
            $row['maintenance_type']['preventive_pm'] ?? '',
            $cc_before,
            $cc_after,
            $jc_before,
            $jc_after,
            $rp_before,
            $rp_after,
            $pm_before,
            $pm_after,
        ];
    }

    public function styles(Worksheet $sheet)
    {

        $startRow = 5; // 

        // 🔹 Judul besar
        $sheet->mergeCells('A1:S2');
        $sheet->setCellValue('A1', 'PM SCHEDULE - FY ' . date('Y'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // 🔹 Header Styling
        $sheet->getStyle('A4:S4')->getFont()->setBold(true);
        $sheet->getStyle('A4:S4')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF99');

        // 🔹 Center semua teks
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $range = "A1:{$highestCol}{$highestRow}";

        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // 🔹 Auto width kolom
        foreach (range('A', 'S') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 🔹 Tambahkan border
        $sheet->getStyle("A4:S{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // 🔹 Tinggi baris biar rapi
        for ($row = 1; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(28);
        }

        // Sesuaikan tinggi baris agar muat gambar 2 baris
        foreach (range($startRow, $highestRow) as $row) {
            $sheet->getRowDimension($row)->setRowHeight(150);
        }

        foreach ($this->data as $index => $row) {
            $rowNumber = $startRow + $index;
            $maxPhotos = 0;

            foreach (['cleaning_critical', 'just_cleaning', 'replacement_part', 'preventive'] as $type) {
                foreach (['before', 'after'] as $when) {
                    $count = count($row['documentation'][$type][$when] ?? []);
                    if ($count > $maxPhotos) $maxPhotos = $count;
                }
            }

            // 2 gambar per baris → hitung berapa baris vertikal yang dibutuhkan
            $rowsNeeded = ceil($maxPhotos / 2);
            $rowHeight = max(150 * $rowsNeeded, 28);
            $sheet->getRowDimension($rowNumber)->setRowHeight($rowHeight);
        }


        for ($row = 5; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(160);
        }
        

        return [];
    }

    public function registerEvents(): array
    {
        return [];
    }

    public function drawings()
    {

        $drawings = [];
        $startRow = 5; // Baris pertama data setelah heading

        foreach ($this->data as $index => $row) {
            $rowNumber = $startRow + $index;
            $docs = $row['documentation'] ?? [];

            // Helper untuk menambah gambar ke kolom tertentu
            // Helper untuk menambah gambar ke kolom tertentu
            $addDrawing = function ($images, $col, $label, $rowNumber) use (&$drawings) {
                if (empty($images)) return;

                $images = array_values($images); // reset index biar rapi
                $maxPerRow = 2; // maksimal 2 foto per baris
                $imgWidth = 150; // ukuran gambar (px)
                $imgHeight = 150;
                $xSpacing = 160; // jarak horizontal antar foto
                $ySpacing = 160; // jarak vertikal antar baris foto

                foreach ($images as $i => $imgUrl) {
                    $path = public_path(parse_url($imgUrl, PHP_URL_PATH));
                    if (!file_exists($path)) continue;

                    $drawing = new Drawing();
                    $drawing->setName($label);
                    $drawing->setDescription($label);
                    $drawing->setPath($path);
                    $drawing->setWidth($imgWidth);
                    $drawing->setHeight($imgHeight);
                    $drawing->setResizeProportional(false); // biar tidak melebar/panjang

                    // Hitung posisi kanan–kiri (x) dan atas–bawah (y)
                    $xOffset = ($i % $maxPerRow) * $xSpacing;   // geser kanan tiap foto kedua
                    $yOffset = floor($i / $maxPerRow) * $ySpacing; // geser ke bawah tiap baris baru

                    $drawing->setCoordinates($col . $rowNumber);
                    $drawing->setOffsetX($xOffset);
                    $drawing->setOffsetY($yOffset);

                    $drawings[] = $drawing;
                }
            };

            // Tambahkan semua kategori gambar
            $addDrawing($docs['cleaning_critical']['before']->toArray() ?? [], 'L', 'Before Cleaning Critical', $rowNumber);
            $addDrawing($docs['cleaning_critical']['after']->toArray() ?? [], 'M', 'After Cleaning Critical', $rowNumber);
            $addDrawing($docs['just_cleaning']['before']->toArray() ?? [], 'N', 'Before Just Cleaning', $rowNumber);
            $addDrawing($docs['just_cleaning']['after']->toArray() ?? [], 'O', 'After Just Cleaning', $rowNumber);
            $addDrawing($docs['replacement_part']['before']->toArray() ?? [], 'P', 'Before Replacement Part', $rowNumber);
            $addDrawing($docs['replacement_part']['after']->toArray() ?? [], 'Q', 'After Replacement Part', $rowNumber);
            $addDrawing($docs['preventive']['before']->toArray() ?? [], 'R', 'Before PM', $rowNumber);
            $addDrawing($docs['preventive']['after']->toArray() ?? [], 'S', 'After PM', $rowNumber);
        }

        // Logo perusahaan tetap ditambahkan
        $logo = new Drawing();
        $logo->setName('Company Logo');
        $logo->setDescription('Logo Perusahaan');
        $logo->setPath(public_path('images/logoadm.png'));
        $logo->setHeight(60);
        $logo->setCoordinates('E1');

        $drawings[] = $logo;

        return $drawings;
    }
}
