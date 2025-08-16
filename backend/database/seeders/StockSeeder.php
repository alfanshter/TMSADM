<?php

namespace Database\Seeders;

use App\Models\StockSparepart;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'nama_sparepart' => 'V-Belt A32',
                'spec' => '32 inch',
                'loc' => 'WH-01',
                'type' => 'Belt',
                'category' => 'Belting & House',
                'remark' => 'pcs',
            ],
            [
                'nama_sparepart' => 'Helm Safety',
                'spec' => 'SNI',
                'loc' => 'WH-02',
                'type' => null,
                'category' => 'Safety',
                'remark' => 'unit',
            ],
            [
                'nama_sparepart' => 'Kunci Inggris',
                'spec' => '12 inch',
                'loc' => 'WH-03',
                'type' => 'Tools',
                'category' => 'Tools',
                'remark' => 'pcs',
            ],
            [
                'nama_sparepart' => 'Bearing 6202',
                'spec' => 'SKF',
                'loc' => 'WH-04',
                'type' => null,
                'category' => 'Spare part & Cons',
                'remark' => 'pcs',
            ],
        ];

        foreach ($items as $item) {
            $stok_awal = rand(10, 50);
            $incoming = rand(0, 20);
            $usage = rand(0, $stok_awal);
            $end_month_stock = $stok_awal + $incoming - $usage;

            StockSparepart::create([
                'nama_sparepart'   => $item['nama_sparepart'],
                'spec'             => $item['spec'],
                'loc'              => $item['loc'],
                'type'             => $item['type'],
                'category'         => $item['category'],
                'stok'             => $stok_awal, // bisa diset stok awal juga
                'stok_awal'        => $stok_awal,
                'incoming'         => $incoming,
                'usage'            => $usage,
                'end_month_stock'  => $end_month_stock,
                'remark'           => $item['remark'],
                'created_at'       => Carbon::now(),
                'updated_at'       => Carbon::now(),
            ]);
        }
    }
}
