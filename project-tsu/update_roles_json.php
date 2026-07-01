<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\Role::all() as $r){
    $p = $r->permissions;
    $changed=false;
    if(isset($p["management_data"]["items"])) {
        foreach($p["management_data"]["items"] as $k=>$v) {
            if($v["name"]=="Pengampu Mata Kuliah") {
                $p["management_data"]["items"][$k]["name"] = "Pengampu Kelas";
                $changed=true;
            }
        }
    }
    
    if(isset($p["modul_penjadwalan"]["items"])) {
        $hasPerbandingan = false;
        foreach($p["modul_penjadwalan"]["items"] as $item) {
            if($item["name"] === "Perbandingan Hasil") {
                $hasPerbandingan = true;
                break;
            }
        }
        if(!$hasPerbandingan) {
            $p["modul_penjadwalan"]["items"][] = ["name" => "Perbandingan Hasil", "enabled" => ($r->name==="Super Admin"||$r->name==="Kaprodi"), "access" => "edit"];
            $changed=true;
        }
    }

    if($changed) {
        $r->permissions = $p;
        $r->save();
        echo "Updated ".$r->name."\n";
    }
}
echo "Done\n";
