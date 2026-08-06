<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key'=>'app_name',       'value'=>'نظام إدارة النفايات الذكي','group'=>'general','type'=>'text','label'=>'Application Name','label_ar'=>'اسم التطبيق','is_public'=>true],
            ['key'=>'app_name_en',    'value'=>'Smart Waste Management',   'group'=>'general','type'=>'text','label'=>'App Name EN',    'label_ar'=>'الاسم بالإنجليزية','is_public'=>true],
            ['key'=>'primary_color',  'value'=>'#2d8a4e',                  'group'=>'general','type'=>'color','label'=>'Primary Color', 'label_ar'=>'اللون الرئيسي','is_public'=>true],
            ['key'=>'default_lat',    'value'=>'31.9038',                  'group'=>'gis',    'type'=>'number','label'=>'Default Latitude','label_ar'=>'خط العرض'],
            ['key'=>'default_lng',    'value'=>'35.2034',                  'group'=>'gis',    'type'=>'number','label'=>'Default Longitude','label_ar'=>'خط الطول'],
            ['key'=>'default_zoom',   'value'=>'13',                       'group'=>'gis',    'type'=>'number','label'=>'Default Zoom','label_ar'=>'مستوى التكبير'],
            ['key'=>'ors_api_key',    'value'=>'',                         'group'=>'gis',    'type'=>'text', 'label'=>'ORS API Key',  'label_ar'=>'مفتاح ORS'],
            ['key'=>'notify_fill_threshold','value'=>'80',                 'group'=>'notifications','type'=>'number','label'=>'Fill Alert %','label_ar'=>'حد التنبيه'],
            ['key'=>'items_per_page', 'value'=>'15',                       'group'=>'system', 'type'=>'number','label'=>'Items Per Page','label_ar'=>'عناصر لكل صفحة'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}