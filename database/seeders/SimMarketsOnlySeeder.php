<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SimRegion;
use App\Models\SimDepartment;
use App\Models\SimMarket;

class SimMarketsOnlySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $d = SimDepartment::pluck('id', 'code');

        if ($d->isEmpty()) {
            $this->command->warn('Aucun departement trouve. Lancez d\'abord SimGeographySeeder.');
            return;
        }

        $markets = [
            // Saint-Louis
            ['sim_department_id'=>$d['SL01'],'name'=>'Saint-Louis','slug'=>'saint-louis','commune'=>'Saint-Louis','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SL01'],'name'=>'Mpal','slug'=>'mpal','commune'=>'Saint-Louis','market_type'=>'rural_regroupement','market_day'=>'Vendredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SL02'],'name'=>'Ross Bethio','slug'=>'ross-bethio','commune'=>'Dagana','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SL02'],'name'=>'Dagana','slug'=>'dagana','commune'=>'Dagana','market_type'=>'rural_regroupement','market_day'=>'Vendredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SL03'],'name'=>'Thille Boubacar','slug'=>'thille-boubacar','commune'=>'Podor','market_type'=>'rural_regroupement','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SL03'],'name'=>'Dodel','slug'=>'dodel','commune'=>'Podor','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Matam
            ['sim_department_id'=>$d['MT01'],'name'=>'Ourossogui','slug'=>'ourossogui','commune'=>'Matam','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['MT01'],'name'=>'Thiodaye','slug'=>'thiodaye','commune'=>'Matam','market_type'=>'rural_consommation','market_day'=>'Mardi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['MT02'],'name'=>'Orkodiere','slug'=>'orkodiere','commune'=>'Kanel','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['MT02'],'name'=>'Kanel','slug'=>'kanel','commune'=>'Kanel','market_type'=>'rural_consommation','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['MT03'],'name'=>'Thionokh','slug'=>'thionokh','commune'=>'Ranerou','market_type'=>'rural_consommation','market_day'=>'Vendredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['MT03'],'name'=>'Ranerou','slug'=>'ranerou','commune'=>'Ranerou','market_type'=>'rural_consommation','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Louga
            ['sim_department_id'=>$d['LG01'],'name'=>'Louga','slug'=>'louga','commune'=>'Louga','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['LG01'],'name'=>'Gouille Mbeuth','slug'=>'gouille-mbeuth','commune'=>'Louga','market_type'=>'rural_regroupement','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['LG01'],'name'=>'Lompoul','slug'=>'lompoul','commune'=>'Louga','market_type'=>'rural_regroupement','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['LG02'],'name'=>'Sagatta Gueth','slug'=>'sagatta-gueth','commune'=>'Kebemer','market_type'=>'rural_regroupement','market_day'=>'Mercredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['LG03'],'name'=>'Dahra','slug'=>'dahra','commune'=>'Linguere','market_type'=>'rural_consommation','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['LG03'],'name'=>'Linguere','slug'=>'linguere','commune'=>'Linguere','market_type'=>'rural_consommation','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Thies
            ['sim_department_id'=>$d['TH01'],'name'=>'Thies','slug'=>'thies','commune'=>'Thies','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH01'],'name'=>'Mbafaye','slug'=>'mbafaye','commune'=>'Thies','market_type'=>'rural','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH01'],'name'=>'Touba Toul','slug'=>'touba-toul','commune'=>'Thies','market_type'=>'rural_regroupement','market_day'=>'Samedi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH01'],'name'=>'Thilmakha','slug'=>'thilmakha','commune'=>'Thies','market_type'=>'rural_consommation','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH02'],'name'=>'Noto Gouy Diama','slug'=>'noto-gouy-diama','commune'=>'Tivaouane','market_type'=>'rural_regroupement','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH03'],'name'=>'Mbour','slug'=>'mbour','commune'=>'Mbour','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['TH03'],'name'=>'Sandiara','slug'=>'sandiara','commune'=>'Mbour','market_type'=>'rural','market_day'=>'Samedi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Dakar
            ['sim_department_id'=>$d['DK01'],'name'=>'Tilene','slug'=>'tilene-dakar','commune'=>'Dakar','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK01'],'name'=>'Gueule Tapee PA','slug'=>'gueule-tapee','commune'=>'Dakar','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK01'],'name'=>'Castors','slug'=>'castors','commune'=>'Dakar','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK02'],'name'=>'Thiaroye','slug'=>'thiaroye','commune'=>'Pikine','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK03'],'name'=>'Marche Central','slug'=>'marche-central-keur-massar','commune'=>'Keur Massar','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK04'],'name'=>'Sahm','slug'=>'sahm','commune'=>'Guediawaye','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DK05'],'name'=>'Rufisque','slug'=>'rufisque','commune'=>'Rufisque','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Diourbel
            ['sim_department_id'=>$d['DB01'],'name'=>'Diourbel','slug'=>'diourbel','commune'=>'Diourbel','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DB01'],'name'=>'Ndindy','slug'=>'ndindy','commune'=>'Diourbel','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DB02'],'name'=>'Touba','slug'=>'touba','commune'=>'Mbacke','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DB02'],'name'=>'Keur Ibra Yacine','slug'=>'keur-ibra-yacine','commune'=>'Mbacke','market_type'=>'rural_regroupement','market_day'=>'Mercredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['DB02'],'name'=>'Sadio','slug'=>'sadio','commune'=>'Mbacke','market_type'=>'rural_regroupement','market_day'=>'Vendredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
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
            ['sim_department_id'=>$d['KL02'],'name'=>'Ndrame Escale','slug'=>'ndrame-escale','commune'=>'Nioro','market_type'=>'rural_collecte','market_day'=>'Lundi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KL02'],'name'=>'Ndiba Ndiaye','slug'=>'ndiba-ndiaye','commune'=>'Nioro','market_type'=>'rural_collecte','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KL03'],'name'=>'Guinguineo','slug'=>'guinguineo','commune'=>'Guinguineo','market_type'=>'rural_regroupement','market_day'=>'Mercredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
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
            // Kedougou
            ['sim_department_id'=>$d['KD01'],'name'=>'Kedougou','slug'=>'kedougou','commune'=>'Kedougou','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KD01'],'name'=>'Mako','slug'=>'mako','commune'=>'Kedougou','market_type'=>'rural_regroupement','market_day'=>'Samedi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KD02'],'name'=>'Salemata','slug'=>'salemata','commune'=>'Salemata','market_type'=>'rural_regroupement','market_day'=>'Mardi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KD03'],'name'=>'Saraya','slug'=>'saraya','commune'=>'Saraya','market_type'=>'rural_regroupement','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Kolda
            ['sim_department_id'=>$d['KO01'],'name'=>'Kolda','slug'=>'kolda','commune'=>'Kolda','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KO01'],'name'=>'Sare Yoba','slug'=>'sare-yoba','commune'=>'Kolda','market_type'=>'rural_collecte','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KO02'],'name'=>'Diobe','slug'=>'diobe','commune'=>'Velingara','market_type'=>'rural_collecte','market_day'=>'Mercredi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KO03'],'name'=>'MYF','slug'=>'myf','commune'=>'MYF','market_type'=>'rural_collecte','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['KO03'],'name'=>'Manda Douane','slug'=>'manda-douane','commune'=>'MYF','market_type'=>'rural_collecte','market_day'=>'Mardi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Sedhiou
            ['sim_department_id'=>$d['SD01'],'name'=>'Sedhiou','slug'=>'sedhiou','commune'=>'Sedhiou','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SD01'],'name'=>'Marsassoum','slug'=>'marsassoum','commune'=>'Sedhiou','market_type'=>'rural','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SD02'],'name'=>'Touba Mouride','slug'=>'touba-mouride-sd','commune'=>'Bounkiling','market_type'=>'rural_collecte','market_day'=>'Samedi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SD02'],'name'=>'Sare Alkaly','slug'=>'sare-alkaly','commune'=>'Bounkiling','market_type'=>'rural_collecte','market_day'=>'Jeudi','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['SD03'],'name'=>'Tanaff','slug'=>'tanaff','commune'=>'Goudomp','market_type'=>'rural_regroupement','market_day'=>'Dimanche','is_permanent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Ziguinchor
            ['sim_department_id'=>$d['ZG01'],'name'=>'Saint-Maur','slug'=>'saint-maur','commune'=>'Ziguinchor','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['ZG01'],'name'=>'Tilene Ziguinchor','slug'=>'tilene-ziguinchor','commune'=>'Ziguinchor','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['ZG02'],'name'=>'Oussouye','slug'=>'oussouye','commune'=>'Oussouye','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['sim_department_id'=>$d['ZG03'],'name'=>'Bignona','slug'=>'bignona','commune'=>'Bignona','market_type'=>'urbain','market_day'=>null,'is_permanent'=>1,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
        ];

        DB::table('sim_markets')->insert($markets);

        $this->command->info('Marches inserees : ' . count($markets));
    }
}
