<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ActivityTmsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithDrawings
{
    protected $month;
    protected $data;
    protected $drawings = [];

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
            ['ACTIVITY TMS' . date('Y')],
            [],
            [],
            [
                'NO',
                'ITEM MACHINE',
                'CODE',
                'LOCATION',
                'DATE',
                'SCOPE OF WORK',
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
            ],[
                '', '', '', '', '', // 5 kolom pertama kosong
                'SAFETY',
                'PRODUCTION',
                '', '', '', '', '', '', '', '', '', '', '',
            ]
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        // Kosongkan kolom foto, nanti digantikan gambar
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
            '', '', '', '', '', '', '', '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 🧭 Judul besar
        $sheet->mergeCells('A1:S1');
        $sheet->setCellValue('A1', 'ACTIVITY TMS - ' . date('F Y', strtotime($this->month . '-01')));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    
        // 🟦 Header tabel (dua baris: baris 4-5)
        $sheet->getStyle('A4:S5')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF305496');
        $sheet->getStyle('A4:S5')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A4:S5')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
    
        // 🔲 Merge cell header
        $sheet->mergeCells('A4:A5'); // NO
        $sheet->mergeCells('B4:B5'); // ITEM MACHINE
        $sheet->mergeCells('C4:C5'); // CODE
        $sheet->mergeCells('D4:D5'); // LOCATION
        $sheet->mergeCells('E4:E5'); // DATE
    
        // Scope of Work (gabung 2 kolom)
        $sheet->mergeCells('F4:G4');
        $sheet->setCellValue('F4', 'SCOPE OF WORK');
        $sheet->setCellValue('F5', 'SAFETY');
        $sheet->setCellValue('G5', 'PRODUCTION');
    
        // Maintenance Type (optional: biar rapi juga)
        $sheet->mergeCells('H4:K4');
        $sheet->setCellValue('H4', 'MAINTENANCE TYPE');
        $sheet->setCellValue('H5', 'CLEANING CRITICAL');
        $sheet->setCellValue('I5', 'JUST CLEANING');
        $sheet->setCellValue('J5', 'REPLACEMENT PART');
        $sheet->setCellValue('K5', 'PREVENTIVE (PM)');
    
        // Foto bagian (Before/After)
        $sheet->mergeCells('L4:M4');
        $sheet->setCellValue('L4', 'CLEANING CRITICAL');
        $sheet->setCellValue('L5', 'FOTO BEFORE');
        $sheet->setCellValue('M5', 'FOTO AFTER');
    
        $sheet->mergeCells('N4:O4');
        $sheet->setCellValue('N4', 'JUST CLEANING');
        $sheet->setCellValue('N5', 'FOTO BEFORE');
        $sheet->setCellValue('O5', 'FOTO AFTER');
    
        $sheet->mergeCells('P4:Q4');
        $sheet->setCellValue('P4', 'REPLACEMENT PART');
        $sheet->setCellValue('P5', 'FOTO BEFORE');
        $sheet->setCellValue('Q5', 'FOTO AFTER');
    
        $sheet->mergeCells('R4:S4');
        $sheet->setCellValue('R4', 'PM');
        $sheet->setCellValue('R5', 'FOTO BEFORE');
        $sheet->setCellValue('S5', 'FOTO AFTER');
    
        // 📏 Lebar kolom
        foreach (range('A', 'S') as $col) {
            if (in_array($col, ['L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'])) {
                $sheet->getColumnDimension($col)->setWidth(30);
            } else {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }
    
        // 📐 Tinggi baris dinamis untuk gambar
        $startRow = 6; // karena header sampai baris 5
        $maxPerRow = 2;
        $imgHeight = 130;
        $imgGap = 10;
    
        foreach ($this->data as $index => $row) {
            $rowNumber = $startRow + $index;
            $maxPhotos = 0;
    
            foreach (['cleaning_critical', 'just_cleaning', 'replacement_part', 'preventive'] as $type) {
                foreach (['before', 'after'] as $when) {
                    $count = count($row['documentation'][$type][$when] ?? []);
                    $maxPhotos = max($maxPhotos, $count);
                }
            }
    
            if ($maxPhotos > 0) {
                $rowsNeeded = ceil($maxPhotos / $maxPerRow);
                $rowHeight = ($rowsNeeded * ($imgHeight + $imgGap)) * 0.75;
                $sheet->getRowDimension($rowNumber)->setRowHeight($rowHeight);
            } else {
                $sheet->getRowDimension($rowNumber)->setRowHeight(25);
            }
        }
    
        // 🧱 Border semua tabel dari header sampai data terakhir
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:S{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);
    
        // 🎯 Rata tengah semua sel
        $sheet->getStyle("A1:S{$highestRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    
        return [];
    }
    

    public function registerEvents(): array
    {
        return [];
    }

    public function drawings()
    {
        $drawings = [];
        $startRow = 6;

        $maxPerRow = 2;  // 2 gambar per baris

        foreach ($this->data as $index => $row) {
            $rowNumber = $startRow + $index;
            $docs = $row['documentation'] ?? [];
            $addDrawing = function ($images, $col, $label, $rowNumber)
            use (&$drawings, $maxPerRow) {
            if (empty($images)) return;
        
            $images = array_values($images);
        
            // pengaturan layout gambar
            $imgWidth = 80;   // kecilin biar muat 2 gambar dalam 1 cell
            $imgHeight = 60;
            $xSpacing = 90;   // jarak antar gambar horizontal
            $ySpacing = 80;   // jarak antar gambar vertikal
            $xStart = 5;      // jarak dari sisi kiri cell
            $yStart = 5;
        
            foreach ($images as $i => $imgUrl) {
                $path = public_path(parse_url($imgUrl, PHP_URL_PATH));
                if (!file_exists($path)) continue;
        
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName($label);
                $drawing->setDescription($label);
                $drawing->setPath($path);
                $drawing->setResizeProportional(true);
        
                // kecilin gambar
                $drawing->setWidth($imgWidth);
                $drawing->setHeight($imgHeight);
        
                // posisi gambar supaya X X / X X rata tengah dan nggak keluar border
                $colOffset = ($i % $maxPerRow) * $xSpacing + $xStart;
                $rowOffset = floor($i / $maxPerRow) * $ySpacing + $yStart;
        
                $drawing->setCoordinates($col . $rowNumber);
                $drawing->setOffsetX($colOffset);
                $drawing->setOffsetY($rowOffset);
        
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

        // 🖼 Logo ADM kanan atas
        $logo = new Drawing();
        $logo->setName('ADM Logo');
        $logo->setDescription('Logo ADM');
        $logo->setPath(public_path('images/logoadm.png'));
        $logo->setHeight(45);
        $logo->setCoordinates('E1');
        $logo->setOffsetX(80);
        $logo->setOffsetY(-2);

        $drawings[] = $logo;

        return $drawings;
    }
}
