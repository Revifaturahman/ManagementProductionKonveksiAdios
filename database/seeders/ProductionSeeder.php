<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {

        $now = Carbon::now();
        /*
        |--------------------------------------------------------------------------
        | WORKERS
        |--------------------------------------------------------------------------
        */

        DB::table('workers')->insert([
            [
                'name'            => 'Teh Ayu',
                'phone'           => '081234567801',
                'role'            => 'cutter',
                'overdeck_type'   => null,
                'rate_per_piece'  => 500,
                'address'         => 'Jl. Makam Caringin Gg. Masjid, Margahayu Utara, Bandung',
                'latitude'        => -6.946982419198253,
                'longitude'       => 107.57405900697829,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Hilmy',
                'phone'           => '081234567801',
                'role'            => 'cutter',
                'overdeck_type'   => null,
                'rate_per_piece'  => 500,
                'address'         => 'Jl. Dakota',
                'latitude'        => -6.946982419198253,
                'longitude'       => 107.57405900697829,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Teh Nia',
                'phone'           => '081234567802',
                'role'            => 'cutter',
                'overdeck_type'   => null,
                'rate_per_piece'  => 500,
                'address'         => 'Gg. Cirangrang Timur No.16, Cirangrang, Bandung',
                'latitude'        => -6.946982419198253,
                'longitude'       => 107.57405900697829,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Teh Eros',
                'phone'           => '081234567803',
                'role'            => 'cutter',
                'overdeck_type'   => null,
                'rate_per_piece'  => 500,
                'address'         => 'Jl. Kopo Sari, Cirangrang, Bandung',
                'latitude'        => -6.946982419198253,
                'longitude'       => 107.57405900697829,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Teh Entin',
                'phone'           => '081234567804',
                'role'            => 'cutter',
                'overdeck_type'   => null,
                'rate_per_piece'  => 500,
                'address'         => 'Margasuka, Babakan Ciparay, Bandung',
                'latitude'        => -6.946982419198253,
                'longitude'       => 107.57405900697829,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            // Obras
            [
                'name'            => 'Teh Elin',
                'phone'           => '081234567805',
                'role'            => 'obras',
                'overdeck_type'   => null,
                'rate_per_piece'  => 400,
                'address'         => 'Jl. Caringin No.35-39, Babakan Ciparay, Bandung',
                'latitude'        => -6.946500,
                'longitude'       => 107.579000,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Teh Bobi',
                'phone'           => '081234567806',
                'role'            => 'obras',
                'overdeck_type'   => null,
                'rate_per_piece'  => 400,
                'address'         => 'Jl. Cibaduyut Lama, Bojongloa Kidul, Bandung',
                'latitude'        => -6.950700,
                'longitude'       => 107.573200,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Teh Titik',
                'phone'           => '081234567807',
                'role'            => 'obras',
                'overdeck_type'   => null,
                'rate_per_piece'  => 400,
                'address'         => 'Gg. Taruna V 57, Margasuka, Bandung',
                'latitude'        => -6.951200,
                'longitude'       => 107.576000,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            // Tailor / Penjahit
            [
                'name'            => 'Mang Entis',
                'phone'           => '081234567808',
                'role'            => 'tailor',
                'overdeck_type'   => null,
                'rate_per_piece'  => 700,
                'address'         => 'Cirangrang, Babakan Ciparay, Bandung',
                'latitude'        => -6.948900,
                'longitude'       => 107.580900,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Asep Kopo',
                'phone'           => '081234567809',
                'role'            => 'tailor',
                'overdeck_type'   => null,
                'rate_per_piece'  => 700,
                'address'         => 'Gg. Melati 1 No.66, Margasuka, Bandung',
                'latitude'        => -6.952300,
                'longitude'       => 107.575100,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Asep Sadang',
                'phone'           => '081234567810',
                'role'            => 'tailor',
                'overdeck_type'   => null,
                'rate_per_piece'  => 700,
                'address'         => 'Jl. Sadang No.39, Margaasih, Bandung',
                'latitude'        => -6.960100,
                'longitude'       => 107.565500,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            // Overdeck
            [
                'name'            => 'Teh Sari',
                'phone'           => '081234567811',
                'role'            => 'overdeck',
                'overdeck_type'   => 'tangan',
                'rate_per_piece'  => 300,
                'address'         => 'Jl. Satria Raya, Margahayu Utara, Bandung',
                'latitude'        => -6.944333874486662,
                'longitude'       => 107.57916088350929,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Zaidan',
                'phone'           => '081234567811',
                'role'            => 'overdeck',
                'overdeck_type'   => 'tangan',
                'rate_per_piece'  => 300,
                'address'         => 'Jl. Satria Raya, Margahayu Utara, Bandung',
                'latitude'        => -6.944333874486662,
                'longitude'       => 107.57916088350929,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Teh Rina',
                'phone'           => '081234567812',
                'role'            => 'overdeck',
                'overdeck_type'   => 'bawah',
                'rate_per_piece'  => 300,
                'address'         => 'Jl. Caringin No.249, Margahayu Utara, Bandung',
                'latitude'        => -6.946900,
                'longitude'       => 107.581200,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Teh Wati',
                'phone'           => '081234567813',
                'role'            => 'overdeck',
                'overdeck_type'   => 'bawah',
                'rate_per_piece'  => 300,
                'address'         => 'Jl. Caringin No.249, Margahayu Utara, Bandung',
                'latitude'        => -6.946950,
                'longitude'       => 107.581250,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'A Agus',
                'phone'           => '081234567814',
                'role'            => 'overdeck',
                'overdeck_type'   => 'tangan',
                'rate_per_piece'  => 300,
                'address'         => 'Gg. Laksana 5, Kebon Lega, Bandung',
                'latitude'        => -6.944333874486662,
                'longitude'       => 107.57916088350929,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | COURIER LOGIN MOBILE
        |--------------------------------------------------------------------------
        */

        DB::table('users')->insert([
            [
                'name'       => 'admin',
                'username'   => 'dila',
                'password'   => Hash::make('123456'),
                'phone'      => '085659431655',
                'is_active'   => 1,
                'role'       => 'admin',
                'worker_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Agung',
                'username'   => 'agung',
                'password'   => Hash::make('123456'),
                'phone'      => null,
                'is_active'   => 0,
                'role'       => 'courier',
                'worker_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Rizky',
                'username'   => 'rizky',
                'password'   => Hash::make('123456'),
                'phone'      => null,
                'is_active'   => 0,
                'role'       => 'courier',
                'worker_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Dedi',
                'username'   => 'dedi',
                'password'   => Hash::make('123456'),
                'phone'      => null,
                'is_active'   => 0,
                'role'       => 'courier',
                'worker_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Teh Ayu',
                'username'   => 'ayu123',
                'password'   => Hash::make('ayu12345678'),
                'phone'      => '081234567801',
                'is_active'   => 1,
                'role'       => 'production',
                'worker_id'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Teh Elin',
                'username'   => 'elin123',
                'password'   => Hash::make('elin12345678'),
                'phone'      => '081234567805',
                'is_active'   => 1,
                'role'       => 'production',
                'worker_id'  => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Mang Entis',
                'username'   => 'entis123',
                'password'   => Hash::make('entis12345678'),
                'phone'      => '081234567806',
                'is_active'   => 1,
                'role'       => 'production',
                'worker_id'  => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Teh Sari',
                'username'   => 'sari123',
                'password'   => Hash::make('sari12345678'),
                'phone'      => '081234567806',
                'is_active'   => 1,
                'role'       => 'production',
                'worker_id'  => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Hilmy',
                'username'   => 'hilmy123',
                'password'   => Hash::make('hilmy12345678'),
                'phone'      => '081234567807',
                'is_active'   => 1,
                'role'       => 'production',
                'worker_id'  => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Zaidan',
                'username'   => 'zaidan123',
                'password'   => Hash::make('zaidan12345678'),
                'phone'      => '081234567811',
                'is_active'   => 1,
                'role'       => 'production',
                'worker_id'  => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | PRODUCT CATEGORY
        |--------------------------------------------------------------------------
        */

        DB::table('product_categories')->insert([

            [
                'name' => 'Kaos Oblong',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'name' => 'Kaos Berkerah',
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | PRODUCT
        |--------------------------------------------------------------------------
        */

        DB::table('products')->insert([

            [
                'id'=>1,
                'category_id'=>1,
                'name'=>'Kaos Oblong Pendek Dewasa',
                'ratio_per_kg'=>4,
                'allocation_ratio'=>2,
                'created_at'=>$now,
                'updated_at'=>$now,
            ],

            [
                'id'=>2,
                'category_id'=>1,
                'name'=>'Kaos Oblong Panjang Dewasa',
                'ratio_per_kg'=>3,
                'allocation_ratio'=>3,
                'created_at'=>$now,
                'updated_at'=>$now,
            ],

            [
                'id'=>3,
                'category_id'=>2,
                'name'=>'Kaos Berkerah Pendek Dewasa',
                'ratio_per_kg'=>4,
                'allocation_ratio'=>2,
                'created_at'=>$now,
                'updated_at'=>$now,
            ],

            [
                'id'=>4,
                'category_id'=>2,
                'name'=>'Kaos Berkerah Pendek Anak',
                'ratio_per_kg'=>8,
                'allocation_ratio'=>1,
                'created_at'=>$now,
                'updated_at'=>$now,
            ],

            [
                'id'=>5,
                'category_id'=>1,
                'name'=>'Kaos Oblong Pendek Anak',
                'ratio_per_kg'=>8,
                'allocation_ratio'=>1,
                'created_at'=>$now,
                'updated_at'=>$now,
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | VARIANTS
        |--------------------------------------------------------------------------
        */

        $variants = [];

        // product_id => safety_stock
        $safetyStocks = [
            1 => 34, // Kaos Oblong Pendek Dewasa
            2 => 27, // Kaos Oblong Panjang Dewasa
            3 => 34, // Kaos Berkerah Pendek Dewasa
            4 => 18, // Kaos Berkerah Pendek Anak
            5 => 18, // Kaos Oblong Pendek Anak
        ];

        $sizes = ['S', 'M', 'L', 'XL'];

        foreach ($safetyStocks as $productId => $safetyStock) {
            foreach ($sizes as $size) {
                $variants[] = [
                    'product_id'    => $productId,
                    'size'          => $size,
                    'minimum_stock' => $safetyStock,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        DB::table('product_variants')->insert($variants);


        /*
        |--------------------------------------------------------------------------
        | INITIAL STOCK
        |--------------------------------------------------------------------------
        */

        DB::table('semi_products')->insert([

            // Kaos Oblong Pendek Dewasa
            ['product_variant_id' => 1, 'qty' => 19, 'created_at' => now()], // S
            ['product_variant_id' => 2, 'qty' => 24, 'created_at' => now()], // M
            ['product_variant_id' => 3, 'qty' => 22, 'created_at' => now()], // L
            ['product_variant_id' => 4, 'qty' => 14, 'created_at' => now()], // XL

            // Kaos Oblong Panjang Dewasa
            ['product_variant_id' => 5, 'qty' => 14, 'created_at' => now()], // S
            ['product_variant_id' => 6, 'qty' => 19, 'created_at' => now()], // M
            ['product_variant_id' => 7, 'qty' => 17, 'created_at' => now()], // L
            ['product_variant_id' => 8, 'qty' => 11, 'created_at' => now()], // XL

            // Kaos Berkerah Pendek Dewasa
            ['product_variant_id' => 9,  'qty' => 18, 'created_at' => now()], // S
            ['product_variant_id' => 10, 'qty' => 23, 'created_at' => now()], // M
            ['product_variant_id' => 11, 'qty' => 21, 'created_at' => now()], // L
            ['product_variant_id' => 12, 'qty' => 13, 'created_at' => now()], // XL

            // Kaos Berkerah Pendek Anak
            ['product_variant_id' => 13, 'qty' => 11, 'created_at' => now()], // S
            ['product_variant_id' => 14, 'qty' => 15, 'created_at' => now()], // M
            ['product_variant_id' => 15, 'qty' => 13, 'created_at' => now()], // L
            ['product_variant_id' => 16, 'qty' => 8,  'created_at' => now()], // XL

            // Kaos Oblong Pendek Anak
            ['product_variant_id' => 17, 'qty' => 12, 'created_at' => now()], // S
            ['product_variant_id' => 18, 'qty' => 17, 'created_at' => now()], // M
            ['product_variant_id' => 19, 'qty' => 15, 'created_at' => now()], // L
            ['product_variant_id' => 20, 'qty' => 8,  'created_at' => now()], // XL

        ]);


        DB::table('finished_products')->insert([

            // Kaos Oblong Pendek Dewasa
            ['product_variant_id' => 1, 'qty' => 13, 'created_at' => now()], // S
            ['product_variant_id' => 2, 'qty' => 17, 'created_at' => now()], // M
            ['product_variant_id' => 3, 'qty' => 15, 'created_at' => now()], // L
            ['product_variant_id' => 4, 'qty' => 9,  'created_at' => now()], // XL

            // Kaos Oblong Panjang Dewasa
            ['product_variant_id' => 5, 'qty' => 8,  'created_at' => now()], // S
            ['product_variant_id' => 6, 'qty' => 13, 'created_at' => now()], // M
            ['product_variant_id' => 7, 'qty' => 11, 'created_at' => now()], // L
            ['product_variant_id' => 8, 'qty' => 7,  'created_at' => now()], // XL

            // Kaos Berkerah Pendek Dewasa
            ['product_variant_id' => 9,  'qty' => 12, 'created_at' => now()], // S
            ['product_variant_id' => 10, 'qty' => 16, 'created_at' => now()], // M
            ['product_variant_id' => 11, 'qty' => 14, 'created_at' => now()], // L
            ['product_variant_id' => 12, 'qty' => 8,  'created_at' => now()], // XL

            // Kaos Berkerah Pendek Anak
            ['product_variant_id' => 13, 'qty' => 6, 'created_at' => now()], // S
            ['product_variant_id' => 14, 'qty' => 9, 'created_at' => now()], // M
            ['product_variant_id' => 15, 'qty' => 8, 'created_at' => now()], // L
            ['product_variant_id' => 16, 'qty' => 4, 'created_at' => now()], // XL

            // Kaos Oblong Pendek Anak
            ['product_variant_id' => 17, 'qty' => 7, 'created_at' => now()], // S
            ['product_variant_id' => 18, 'qty' => 10, 'created_at' => now()], // M
            ['product_variant_id' => 19, 'qty' => 9, 'created_at' => now()], // L
            ['product_variant_id' => 20, 'qty' => 4, 'created_at' => now()], // XL

        ]);

        DB::table('company_profiles')->insert([
            'name' => 'Adios Konveksi',
            'address' => 'Jl. Cirangrang No. 1, Jakarta',
            'latitude' => '-6.954640499355522',
            'longitude' => '107.58503023034835',]);

        DB::table('raw_material_masters')->insert([
            'name' => 'Katun cvc',
            'description' => "Bahan Katun CVC",]);

        DB::table('raw_material_stocks')->insert([
            'raw_material_master_id' => 1,
            'stock_kg' => 100,]);
    }
        
        
};