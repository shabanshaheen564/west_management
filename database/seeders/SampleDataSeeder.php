<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Dumpsite;
use App\Models\Container;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Dumpsites
        $dumpsites = [
            ['code'=>'DS-001','name'=>'Northern Landfill','name_ar'=>'مكب الشمال','latitude'=>31.93,'longitude'=>35.19,'type'=>'landfill','status'=>'active','total_capacity'=>50000,'current_fill'=>22000,'fill_percentage'=>44,'address'=>'Northern Road'],
            ['code'=>'DS-002','name'=>'Recycling Center','name_ar'=>'مركز إعادة التدوير','latitude'=>31.88,'longitude'=>35.18,'type'=>'recycling_center','status'=>'active','total_capacity'=>10000,'current_fill'=>3500,'fill_percentage'=>35,'address'=>'West Zone'],
            ['code'=>'DS-003','name'=>'Transfer Station','name_ar'=>'محطة نقل مركزية','latitude'=>31.905,'longitude'=>35.21,'type'=>'transfer_station','status'=>'active','total_capacity'=>5000,'current_fill'=>2100,'fill_percentage'=>42,'address'=>'City Center'],
        ];
        foreach ($dumpsites as $d) Dumpsite::firstOrCreate(['code'=>$d['code']], $d);

        // Vehicles
        $vehicles = [
            ['plate_number'=>'WMS-101','brand'=>'Mercedes','model'=>'Actros','year'=>2021,'type'=>'compactor','capacity'=>10,'status'=>'active','fuel_type'=>'diesel','current_lat'=>31.905,'current_lng'=>35.203],
            ['plate_number'=>'WMS-102','brand'=>'Volvo','model'=>'FE','year'=>2020,'type'=>'truck','capacity'=>8,'status'=>'active','fuel_type'=>'diesel','current_lat'=>31.910,'current_lng'=>35.210],
            ['plate_number'=>'WMS-103','brand'=>'MAN','model'=>'TGS','year'=>2022,'type'=>'compactor','capacity'=>12,'status'=>'on_route','fuel_type'=>'diesel','current_lat'=>31.895,'current_lng'=>35.195],
        ];
        foreach ($vehicles as $v) Vehicle::firstOrCreate(['plate_number'=>$v['plate_number']], $v);

        // Drivers
        $drivers = [
            ['employee_id'=>'DRV-001','name'=>'Khalil Ibrahim','name_ar'=>'خليل إبراهيم','phone'=>'+970-59-100-0001','license_number'=>'LIC-001','license_class'=>'C','license_expiry'=>'2026-12-31','hire_date'=>'2020-01-15','status'=>'active','rating'=>4.8,'total_trips'=>0],
            ['employee_id'=>'DRV-002','name'=>'Omar Saleh','name_ar'=>'عمر صالح','phone'=>'+970-59-100-0002','license_number'=>'LIC-002','license_class'=>'C','license_expiry'=>'2026-08-20','hire_date'=>'2019-03-10','status'=>'active','rating'=>4.5,'total_trips'=>0],
        ];
        foreach ($drivers as $d) Driver::firstOrCreate(['employee_id'=>$d['employee_id']], $d);

        // Containers
        $zones = ['Zone A','Zone B','Zone C','Zone D'];
        $types = ['general','recyclable','organic','hazardous','electronic'];
        for ($i = 1; $i <= 20; $i++) {
            $lat = 31.900 + (($i % 5) * 0.005) + (rand(-3,3) * 0.001);
            $lng = 35.195 + (intdiv($i,5) * 0.005) + (rand(-3,3) * 0.001);
            Container::firstOrCreate(['code'=>'CNT-'.str_pad($i,3,'0',STR_PAD_LEFT)], [
                'name'       => 'Container '.str_pad($i,3,'0',STR_PAD_LEFT),
                'name_ar'    => 'حاوية '.$i,
                'type'       => $types[$i % 5],
                'capacity'   => [660,770,1100,1500][$i % 4],
                'fill_level' => rand(10,95),
                'latitude'   => round($lat,6),
                'longitude'  => round($lng,6),
                'zone'       => $zones[$i % 4],
                'status'     => $i % 10 === 0 ? 'maintenance' : 'active',
                'address'    => 'Street '.$i.', '.$zones[$i % 4],
            ]);
        }
    }
}