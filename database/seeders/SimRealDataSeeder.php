<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SimRegion;
use App\Models\SimDepartment;
use App\Models\SimMarket;
use App\Models\SimProductCategory;
use App\Models\SimProduct;

class SimRealDataSeeder extends Seeder
{
    public function run(): void
    {
        // Vider les tables (ordre respectant les FK)
        DB::table('sim_mobile_collections')->delete();
        DB::table('sim_collector_assignments')->delete();
        DB::table('sim_products')->delete();
        DB::table('sim_product_categories')->delete();
        DB::table('sim_markets')->delete();
        DB::table('sim_departments')->delete();
        DB::table('sim_regions')->delete();

        $now = now();

        // ===== REGIONS =====
        $regions = [
            ['name' => 'Saint-Louis',    'code' => 'SL',  'display_order' => 1,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Matam',          'code' => 'MT',  'display_order' => 2,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Louga',          'code' => 'LG',  'display_order' => 3,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Thiès',          'code' => 'TH',  'display_order' => 4,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dakar',          'code' => 'DK',  'display_order' => 5,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diourbel',       'code' => 'DB',  'display_order' => 6,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Fatick',         'code' => 'FK',  'display_order' => 7,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kaolack',        'code' => 'KL',  'display_order' => 8,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kaffrine',       'code' => 'KF',  'display_order' => 9,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Tambacounda',    'code' => 'TC',  'display_order' => 10, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kédougou',       'code' => 'KD',  'display_order' => 11, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kolda',          'code' => 'KO',  'display_order' => 12, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sédhiou',        'code' => 'SD',  'display_order' => 13, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ziguinchor',     'code' => 'ZG',  'display_order' => 14, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('sim_regions')->insert($regions);

        $r = SimRegion::pluck('id', 'code');

        // ===== DEPARTEMENTS =====
        $depts = [
            // Saint-Louis
            ['sim_region_id' => $r['SL'], 'name' => 'Saint-Louis', 'code' => 'SL01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['SL'], 'name' => 'Dagana',      'code' => 'SL02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['SL'], 'name' => 'Podor',       'code' => 'SL03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Matam
            ['sim_region_id' => $r['MT'], 'name' => 'Matam',       'code' => 'MT01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['MT'], 'name' => 'Kanel',       'code' => 'MT02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['MT'], 'name' => 'Ranérou',     'code' => 'MT03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Louga
            ['sim_region_id' => $r['LG'], 'name' => 'Louga',       'code' => 'LG01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['LG'], 'name' => 'Kébémer',     'code' => 'LG02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['LG'], 'name' => 'Linguère',    'code' => 'LG03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Thiès
            ['sim_region_id' => $r['TH'], 'name' => 'Thiès',       'code' => 'TH01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['TH'], 'name' => 'Tivaouane',   'code' => 'TH02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['TH'], 'name' => 'Mbour',       'code' => 'TH03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Dakar
            ['sim_region_id' => $r['DK'], 'name' => 'Dakar',       'code' => 'DK01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['DK'], 'name' => 'Pikine',      'code' => 'DK02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['DK'], 'name' => 'Keur Massar', 'code' => 'DK03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['DK'], 'name' => 'Guédiawaye',  'code' => 'DK04', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['DK'], 'name' => 'Rufisque',    'code' => 'DK05', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Diourbel
            ['sim_region_id' => $r['DB'], 'name' => 'Diourbel',    'code' => 'DB01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['DB'], 'name' => 'Mbacké',      'code' => 'DB02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['DB'], 'name' => 'Bambey',      'code' => 'DB03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Fatick
            ['sim_region_id' => $r['FK'], 'name' => 'Fatick',      'code' => 'FK01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['FK'], 'name' => 'Gossas',      'code' => 'FK02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['FK'], 'name' => 'Foundiougne',  'code' => 'FK03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Kaolack
            ['sim_region_id' => $r['KL'], 'name' => 'Kaolack',     'code' => 'KL01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['KL'], 'name' => 'Nioro',       'code' => 'KL02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['KL'], 'name' => 'Guinguinéo',  'code' => 'KL03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Kaffrine
            ['sim_region_id' => $r['KF'], 'name' => 'Kaffrine',    'code' => 'KF01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['KF'], 'name' => 'Birkilane',   'code' => 'KF02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['KF'], 'name' => 'Koungheul',   'code' => 'KF03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['KF'], 'name' => 'Malem Hodar', 'code' => 'KF04', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Tambacounda
            ['sim_region_id' => $r['TC'], 'name' => 'Tambacounda', 'code' => 'TC01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['TC'], 'name' => 'Goudiry',     'code' => 'TC02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['TC'], 'name' => 'Koumpentoum', 'code' => 'TC03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['TC'], 'name' => 'Bakel',       'code' => 'TC04', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Kédougou
            ['sim_region_id' => $r['KD'], 'name' => 'Kédougou',    'code' => 'KD01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['KD'], 'name' => 'Salemata',    'code' => 'KD02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['KD'], 'name' => 'Saraya',      'code' => 'KD03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Kolda
            ['sim_region_id' => $r['KO'], 'name' => 'Kolda',       'code' => 'KO01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['KO'], 'name' => 'Vélingara',   'code' => 'KO02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['KO'], 'name' => 'MYF',         'code' => 'KO03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Sédhiou
            ['sim_region_id' => $r['SD'], 'name' => 'Sédhiou',     'code' => 'SD01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['SD'], 'name' => 'Bounkiling',  'code' => 'SD02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['SD'], 'name' => 'Goudomp',     'code' => 'SD03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            // Ziguinchor
            ['sim_region_id' => $r['ZG'], 'name' => 'Ziguinchor',  'code' => 'ZG01', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['ZG'], 'name' => 'Oussouye',    'code' => 'ZG02', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['sim_region_id' => $r['ZG'], 'name' => 'Bignona',     'code' => 'ZG03', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('sim_departments')->insert($depts);

        $d = SimDepartment::pluck('id', 'code');

        // ===== MARCHES =====
        $markets = [
            // Saint-Louis
            ['sim_department_id'=>$d['SL01'],'name'=>'Saint-Louis','slug'=>'saint-louis','commune'=>'Saint-Louis','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SL01'],'name'=>'Mpal','slug'=>'mpal','commune'=>'Saint-Louis','market_type'=>'rural_regroupement','market_day'=>'Vendredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SL02'],'name'=>'Ross Béthio','slug'=>'ross-bethio','commune'=>'Dagana','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SL02'],'name'=>'Dagana','slug'=>'dagana','commune'=>'Dagana','market_type'=>'rural_regroupement','market_day'=>'Vendredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SL03'],'name'=>'Thillé Boubacar','slug'=>'thille-boubacar','commune'=>'Podor','market_type'=>'rural_regroupement','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SL03'],'name'=>'Dodel','slug'=>'dodel','commune'=>'Podor','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Matam
            ['sim_department_id'=>$d['MT01'],'name'=>'Ourossogui','slug'=>'ourossogui','commune'=>'Matam','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['MT01'],'name'=>'Thiodaye','slug'=>'thiodaye','commune'=>'Matam','market_type'=>'rural_consommation','market_day'=>'Mardi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['MT02'],'name'=>'Orkodière','slug'=>'orkodiere','commune'=>'Kanel','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['MT02'],'name'=>'Kanel','slug'=>'kanel','commune'=>'Kanel','market_type'=>'rural_consommation','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['MT03'],'name'=>'Thionokh','slug'=>'thionokh','commune'=>'Ranérou','market_type'=>'rural_consommation','market_day'=>'Vendredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['MT03'],'name'=>'Ranérou','slug'=>'ranerou','commune'=>'Ranérou','market_type'=>'rural_consommation','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Louga
            ['sim_department_id'=>$d['LG01'],'name'=>'Louga','slug'=>'louga','commune'=>'Louga','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['LG01'],'name'=>'Gouille Mbeuth','slug'=>'gouille-mbeuth','commune'=>'Louga','market_type'=>'rural_regroupement','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['LG01'],'name'=>'Lompoul','slug'=>'lompoul','commune'=>'Louga','market_type'=>'rural_regroupement','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['LG02'],'name'=>'Sagatta Gueth','slug'=>'sagatta-gueth','commune'=>'Kébémer','market_type'=>'rural_regroupement','market_day'=>'Mercredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['LG03'],'name'=>'Dahra','slug'=>'dahra','commune'=>'Linguère','market_type'=>'rural_consommation','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['LG03'],'name'=>'Linguère','slug'=>'linguere','commune'=>'Linguère','market_type'=>'rural_consommation','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Thiès
            ['sim_department_id'=>$d['TH01'],'name'=>'Thiès','slug'=>'thies','commune'=>'Thiès','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH01'],'name'=>'Mbafaye','slug'=>'mbafaye','commune'=>'Thiès','market_type'=>'rural','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH01'],'name'=>'Touba Toul','slug'=>'touba-toul','commune'=>'Thiès','market_type'=>'rural_regroupement','market_day'=>'Samedi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH01'],'name'=>'Thilmakha','slug'=>'thilmakha','commune'=>'Thiès','market_type'=>'rural_consommation','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH02'],'name'=>'Noto Gouy Diama','slug'=>'noto-gouy-diama','commune'=>'Tivaouane','market_type'=>'rural_regroupement','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH03'],'name'=>'Mbour','slug'=>'mbour','commune'=>'Mbour','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH03'],'name'=>'Sandiara','slug'=>'sandiara','commune'=>'Mbour','market_type'=>'rural','market_day'=>'Samedi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Dakar
            ['sim_department_id'=>$d['DK01'],'name'=>'Tilène','slug'=>'tilene-dakar','commune'=>'Dakar','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK01'],'name'=>'Gueule Tapée PA','slug'=>'gueule-tapee','commune'=>'Dakar','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK01'],'name'=>'Castors','slug'=>'castors','commune'=>'Dakar','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK02'],'name'=>'Thiaroye','slug'=>'thiaroye','commune'=>'Pikine','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK03'],'name'=>'Marché Central','slug'=>'marche-central-keur-massar','commune'=>'Keur Massar','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK04'],'name'=>'Sahm','slug'=>'sahm','commune'=>'Guédiawaye','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK05'],'name'=>'Rufisque','slug'=>'rufisque','commune'=>'Rufisque','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Diourbel
            ['sim_department_id'=>$d['DB01'],'name'=>'Diourbel','slug'=>'diourbel','commune'=>'Diourbel','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DB01'],'name'=>'Ndindy','slug'=>'ndindy','commune'=>'Diourbel','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DB02'],'name'=>'Touba','slug'=>'touba','commune'=>'Mbacké','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DB02'],'name'=>'Keur Ibra Yacine','slug'=>'keur-ibra-yacine','commune'=>'Mbacké','market_type'=>'rural_regroupement','market_day'=>'Mercredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DB02'],'name'=>'Sadio','slug'=>'sadio','commune'=>'Mbacké','market_type'=>'rural_regroupement','market_day'=>'Vendredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DB03'],'name'=>'Bambey','slug'=>'bambey','commune'=>'Bambey','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DB03'],'name'=>'Ndagalma','slug'=>'ndagalma','commune'=>'Bambey','market_type'=>'rural_regroupement','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Fatick
            ['sim_department_id'=>$d['FK01'],'name'=>'Fatick','slug'=>'fatick','commune'=>'Fatick','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['FK01'],'name'=>'Diakhao','slug'=>'diakhao','commune'=>'Fatick','market_type'=>'rural_collecte','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['FK01'],'name'=>'Diouroup','slug'=>'diouroup','commune'=>'Fatick','market_type'=>'rural_regroupement','market_day'=>'Mercredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['FK02'],'name'=>'Gossas','slug'=>'gossas','commune'=>'Gossas','market_type'=>'rural_collecte','market_day'=>'Samedi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['FK02'],'name'=>'Mbar','slug'=>'mbar','commune'=>'Gossas','market_type'=>'rural_collecte','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['FK03'],'name'=>'Passy','slug'=>'passy','commune'=>'Foundiougne','market_type'=>'rural_collecte','market_day'=>'Samedi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Kaolack
            ['sim_department_id'=>$d['KL01'],'name'=>'Kaolack','slug'=>'kaolack','commune'=>'Kaolack','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KL01'],'name'=>'Ndoffane','slug'=>'ndoffane','commune'=>'Kaolack','market_type'=>'rural_regroupement','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KL02'],'name'=>'Porokhane','slug'=>'porokhane','commune'=>'Nioro','market_type'=>'rural_collecte','market_day'=>'Vendredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KL02'],'name'=>'Ndramé Escale','slug'=>'ndrame-escale','commune'=>'Nioro','market_type'=>'rural_collecte','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KL02'],'name'=>'Ndiba Ndiaye','slug'=>'ndiba-ndiaye','commune'=>'Nioro','market_type'=>'rural_collecte','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KL03'],'name'=>'Guinguinéo','slug'=>'guinguineo','commune'=>'Guinguinéo','market_type'=>'rural_regroupement','market_day'=>'Mercredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Kaffrine
            ['sim_department_id'=>$d['KF01'],'name'=>'Mabo','slug'=>'mabo','commune'=>'Kaffrine','market_type'=>'rural_consommation','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KF01'],'name'=>'Kaffrine','slug'=>'kaffrine','commune'=>'Kaffrine','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KF02'],'name'=>'Diamagadio','slug'=>'diamagadio','commune'=>'Birkilane','market_type'=>'rural_regroupement','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KF02'],'name'=>'Birkilane','slug'=>'birkilane','commune'=>'Birkilane','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KF03'],'name'=>'Missirah','slug'=>'missirah','commune'=>'Koungheul','market_type'=>'rural_collecte','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KF04'],'name'=>'Malem Hodar','slug'=>'malem-hodar','commune'=>'Malem Hodar','market_type'=>'rural_consommation','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Tambacounda
            ['sim_department_id'=>$d['TC01'],'name'=>'Tambacounda','slug'=>'tambacounda','commune'=>'Tambacounda','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TC02'],'name'=>'Goudiry','slug'=>'goudiry','commune'=>'Goudiry','market_type'=>'rural_regroupement','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TC03'],'name'=>'Mereto','slug'=>'mereto','commune'=>'Koumpentoum','market_type'=>'rural_collecte','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TC03'],'name'=>'Kouthiaba','slug'=>'kouthiaba','commune'=>'Koumpentoum','market_type'=>'rural_collecte','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TC04'],'name'=>'Bakel','slug'=>'bakel','commune'=>'Bakel','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Kédougou
            ['sim_department_id'=>$d['KD01'],'name'=>'Kédougou','slug'=>'kedougou','commune'=>'Kédougou','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KD01'],'name'=>'Mako','slug'=>'mako','commune'=>'Kédougou','market_type'=>'rural_regroupement','market_day'=>'Samedi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KD02'],'name'=>'Salemata','slug'=>'salemata','commune'=>'Salemata','market_type'=>'rural_regroupement','market_day'=>'Mardi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KD03'],'name'=>'Saraya','slug'=>'saraya','commune'=>'Saraya','market_type'=>'rural_regroupement','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Kolda
            ['sim_department_id'=>$d['KO01'],'name'=>'Kolda','slug'=>'kolda','commune'=>'Kolda','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KO01'],'name'=>'Saré Yoba','slug'=>'sare-yoba','commune'=>'Kolda','market_type'=>'rural_collecte','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KO02'],'name'=>'Diaobé','slug'=>'diaobe','commune'=>'Vélingara','market_type'=>'rural_collecte','market_day'=>'Mercredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KO03'],'name'=>'MYF','slug'=>'myf','commune'=>'MYF','market_type'=>'rural_collecte','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KO03'],'name'=>'Manda Douane','slug'=>'manda-douane','commune'=>'MYF','market_type'=>'rural_collecte','market_day'=>'Mardi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Sédhiou
            ['sim_department_id'=>$d['SD01'],'name'=>'Sédhiou','slug'=>'sedhiou','commune'=>'Sédhiou','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SD01'],'name'=>'Marsassoum','slug'=>'marsassoum','commune'=>'Sédhiou','market_type'=>'rural','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SD02'],'name'=>'Touba Mouride','slug'=>'touba-mouride-sd','commune'=>'Bounkiling','market_type'=>'rural_collecte','market_day'=>'Samedi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SD02'],'name'=>'Saré Alkaly','slug'=>'sare-alkaly','commune'=>'Bounkiling','market_type'=>'rural_collecte','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SD03'],'name'=>'Tanaff','slug'=>'tanaff','commune'=>'Goudomp','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Ziguinchor
            ['sim_department_id'=>$d['ZG01'],'name'=>'Saint-Maur','slug'=>'saint-maur','commune'=>'Ziguinchor','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['ZG01'],'name'=>'Tilène Ziguinchor','slug'=>'tilene-ziguinchor','commune'=>'Ziguinchor','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['ZG02'],'name'=>'Oussouye','slug'=>'oussouye','commune'=>'Oussouye','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['ZG03'],'name'=>'Bignona','slug'=>'bignona','commune'=>'Bignona','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
        ];
        DB::table('sim_markets')->insert($markets);

        // ===== CATEGORIES DE PRODUITS =====
        $categories = [
            ['name'=>'Céréales & produits céréaliers', 'slug'=>'cereales',     'display_order'=>1,  'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Tubercules & racines',            'slug'=>'tubercules',   'display_order'=>2,  'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Légumineuses',                    'slug'=>'legumineuses', 'display_order'=>3,  'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Légumes & feuilles vertes',       'slug'=>'legumes',      'display_order'=>4,  'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Fruits',                          'slug'=>'fruits',       'display_order'=>5,  'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Viande',                          'slug'=>'viande',       'display_order'=>6,  'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Tête & abats',                    'slug'=>'tete-abats',   'display_order'=>7,  'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Poissons & produits de mer',      'slug'=>'poissons',     'display_order'=>8,  'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Œufs',                            'slug'=>'oeufs',        'display_order'=>9,  'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Huiles & graisses',               'slug'=>'huiles',       'display_order'=>10, 'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Produits forestiers',             'slug'=>'forestiers',   'display_order'=>11, 'is_active'=>1, 'created_at'=>$now,'updated_at'=>$now],
        ];
        DB::table('sim_product_categories')->insert($categories);

        $c = SimProductCategory::pluck('id', 'slug');

        // ===== PRODUITS (50) =====
        $products = [
            // Céréales (11)
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Fonio',                         'slug'=>'fonio',                    'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>1,  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Maïs jaune (grains local)',     'slug'=>'mais-jaune-local',         'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>2,  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Maïs importé',                  'slug'=>'mais-importe',             'unit'=>'kg',    'origin_type'=>'import', 'display_order'=>3,  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Mil sanio',                     'slug'=>'mil-sanio',                'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>4,  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Mil souna',                     'slug'=>'mil-souna',                'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>5,  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Riz local décortiqué',          'slug'=>'riz-local-decortique',     'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>6,  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Riz importé ordinaire',         'slug'=>'riz-importe-ordinaire',    'unit'=>'kg',    'origin_type'=>'import', 'display_order'=>7,  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Riz importé brisé parfumé',     'slug'=>'riz-importe-brise-parfume','unit'=>'kg',    'origin_type'=>'import', 'display_order'=>8,  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Riz entier importé',            'slug'=>'riz-entier-importe',       'unit'=>'kg',    'origin_type'=>'import', 'display_order'=>9,  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Sorgho local',                  'slug'=>'sorgho-local',             'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>10, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['cereales'],    'name'=>'Sorgho importé',                'slug'=>'sorgho-importe',           'unit'=>'kg',    'origin_type'=>'import', 'display_order'=>11, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Tubercules (4)
            ['sim_product_category_id'=>$c['tubercules'],  'name'=>'Manioc',                        'slug'=>'manioc',                   'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>12, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['tubercules'],  'name'=>'Patate douce',                  'slug'=>'patate-douce',             'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>13, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['tubercules'],  'name'=>'Pomme de terre local',          'slug'=>'pomme-de-terre-local',     'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>14, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['tubercules'],  'name'=>'Pomme de terre importée',       'slug'=>'pomme-de-terre-importe',   'unit'=>'kg',    'origin_type'=>'import', 'display_order'=>15, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Légumineuses (4)
            ['sim_product_category_id'=>$c['legumineuses'],'name'=>'Arachides coque',               'slug'=>'arachides-coque',          'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>16, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['legumineuses'],'name'=>'Arachide décortiquée',          'slug'=>'arachide-decortiquee',     'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>17, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['legumineuses'],'name'=>'Niébé 1ère qualité',            'slug'=>'niebe-1ere-qualite',       'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>18, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['legumineuses'],'name'=>'Niébé 2ème qualité',            'slug'=>'niebe-2eme-qualite',       'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>19, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Légumes (5)
            ['sim_product_category_id'=>$c['legumes'],     'name'=>'Aubergines',                    'slug'=>'aubergines',               'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>20, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['legumes'],     'name'=>'Carotte',                       'slug'=>'carotte',                  'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>21, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['legumes'],     'name'=>'Oignon local',                  'slug'=>'oignon-local',             'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>22, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['legumes'],     'name'=>'Oignon importé',                'slug'=>'oignon-importe',           'unit'=>'kg',    'origin_type'=>'import', 'display_order'=>23, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['legumes'],     'name'=>'Tomate',                        'slug'=>'tomate',                   'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>24, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Fruits (5)
            ['sim_product_category_id'=>$c['fruits'],      'name'=>'Banane',                        'slug'=>'banane',                   'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>25, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['fruits'],      'name'=>'Mangue',                        'slug'=>'mangue',                   'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>26, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['fruits'],      'name'=>'Orange',                        'slug'=>'orange',                   'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>27, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['fruits'],      'name'=>'Pastèque',                      'slug'=>'pasteque',                 'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>28, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['fruits'],      'name'=>'Pomme rouge/vert',              'slug'=>'pomme',                    'unit'=>'kg',    'origin_type'=>'import', 'display_order'=>29, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Viande (3)
            ['sim_product_category_id'=>$c['viande'],      'name'=>'Viande de bœuf',                'slug'=>'viande-boeuf',             'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>30, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['viande'],      'name'=>'Viande de caprin',              'slug'=>'viande-caprin',            'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>31, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['viande'],      'name'=>'Viande de mouton',              'slug'=>'viande-mouton',            'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>32, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Tête (4)
            ['sim_product_category_id'=>$c['tete-abats'],  'name'=>'Poulet',                        'slug'=>'poulet',                   'unit'=>'unité', 'origin_type'=>'local',  'display_order'=>33, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['tete-abats'],  'name'=>'Tête bovin',                    'slug'=>'tete-bovin',               'unit'=>'unité', 'origin_type'=>'local',  'display_order'=>34, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['tete-abats'],  'name'=>'Tête ovin',                     'slug'=>'tete-ovin',                'unit'=>'unité', 'origin_type'=>'local',  'display_order'=>35, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['tete-abats'],  'name'=>'Tête caprin',                   'slug'=>'tete-caprin',              'unit'=>'unité', 'origin_type'=>'local',  'display_order'=>36, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Poissons (4)
            ['sim_product_category_id'=>$c['poissons'],    'name'=>'Chinchard frais (Diay)',        'slug'=>'chinchard-frais',          'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>37, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['poissons'],    'name'=>'Barracuda (Seud)',               'slug'=>'barracuda',                'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>38, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['poissons'],    'name'=>'Sardinella frais (Yaboye)',     'slug'=>'sardinella-frais',         'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>39, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['poissons'],    'name'=>'Poisson fumé/séché',            'slug'=>'poisson-fume-seche',       'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>40, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Œufs (2)
            ['sim_product_category_id'=>$c['oeufs'],       'name'=>'Œuf de poule locale',           'slug'=>'oeuf-poule-locale',        'unit'=>'unité', 'origin_type'=>'local',  'display_order'=>41, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['oeufs'],       'name'=>'Œuf de poule pondeuse',         'slug'=>'oeuf-poule-pondeuse',      'unit'=>'unité', 'origin_type'=>'local',  'display_order'=>42, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Huiles (5)
            ['sim_product_category_id'=>$c['huiles'],      'name'=>'Huile d\'arachide (Diw ségal)', 'slug'=>'huile-arachide-diw',       'unit'=>'litre', 'origin_type'=>'local',  'display_order'=>43, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['huiles'],      'name'=>'Huile d\'arachide raffinée',    'slug'=>'huile-arachide-raffinee',  'unit'=>'litre', 'origin_type'=>'local',  'display_order'=>44, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['huiles'],      'name'=>'Huile végétale',                'slug'=>'huile-vegetale',           'unit'=>'litre', 'origin_type'=>'import', 'display_order'=>45, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['huiles'],      'name'=>'Huile de tournesol',            'slug'=>'huile-tournesol',          'unit'=>'litre', 'origin_type'=>'import', 'display_order'=>46, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['huiles'],      'name'=>'Huile de palme',                'slug'=>'huile-palme',              'unit'=>'litre', 'origin_type'=>'import', 'display_order'=>47, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Produits forestiers (3)
            ['sim_product_category_id'=>$c['forestiers'],  'name'=>'Pain de singe',                 'slug'=>'pain-de-singe',            'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>48, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['forestiers'],  'name'=>'Sidemme',                       'slug'=>'sidemme',                  'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>49, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_product_category_id'=>$c['forestiers'],  'name'=>'Anacarde',                      'slug'=>'anacarde',                 'unit'=>'kg',    'origin_type'=>'local',  'display_order'=>50, 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
        ];
        DB::table('sim_products')->insert($products);

        $this->command->info('✅ Données SIM insérées : '.SimRegion::count().' régions, '.SimDepartment::count().' départements, '.SimMarket::count().' marchés, '.SimProduct::count().' produits');
    }
}
