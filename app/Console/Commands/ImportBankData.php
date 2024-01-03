<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use App\Models\Deudores as DeudoresModel;
use App\Models\Instituciones as InstitucionesModel;

class ImportBankData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:importBankData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importación de archivos txt a mongo';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando la importación de los archivos...');
        $files = ['instituciones.txt', 'deudores.txt'];

        foreach ($files as $nameFile) {
            $path = 'storage/app/public/data_txt/' . $nameFile;

            if (file_exists($path)) {
                $this->importData($path, $nameFile);
            } else {
                $this->error('Archivo a importar no encontrado...');
            }
        }
    }

    private function importData(string $file, string $nameFile) {
        if (isset($file) && !is_null($file)) {
            if (str_contains($nameFile, 'deudores')) {
                $file_formatted = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
                if (count($file_formatted) > 0 && !is_null($file_formatted)) {
                    foreach ($file_formatted as $data) {
                        $data = explode(" ", $data);
    
                        $arrFinal = array_filter($data, function($item){
                           return (isset($item) && strlen($item) > 0) ? $item : null;
                        });
    
                        if (is_array($arrFinal) && count($arrFinal) > 0) { 
                            $nro_id = (!is_null($arrFinal[0])) ? substr($arrFinal[0], 13, -4) : null;
    
                            $situacion = substr($arrFinal[0], -2);
                            $situacion = (!is_null($situacion)) ? intval($situacion) : null;
    
                            $prestamo = str_replace(',', '.', $arrFinal[1]);
                            $prestamo = (!is_null($prestamo)) ? floatval($prestamo) : null;

                            $deudor = new DeudoresModel();
                            $deudor->nro_id = $nro_id;
                            $deudor->situacion = $situacion;
                            $deudor->prestamo = $prestamo;
                            $deudor->save();

                            $this->info('Deudor cargado con exito: Nro id: '. $deudor->nro_id);                        
                        } else {
                            $this->error('No se pudo obtener la data...');
                        }
                    }
                } else {
                    $this->error('No se pudo obtener la data...');
                }
            } 

            if (str_contains($nameFile, 'instituciones')) {
                $file_formatted = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
                if (count($file_formatted) > 0 && !is_null($file_formatted)) {
                    foreach ($file_formatted as $data) {
                        $data = explode(" ", $data);
    
                        $arrFinal = array_filter($data, function($item){
                           return (isset($item) && strlen($item) > 0) ? $item : null;
                        });

                        if (is_array($arrFinal) && count($arrFinal) > 0) { 
                            $cod_entidad   = (!is_null($arrFinal[0])) ? intval($arrFinal[0]) : null;
                            $sum_prestamos = (!is_null($arrFinal[1])) ? intval($arrFinal[1]) : null;
                            
                            $institucion = new InstitucionesModel();
                            $institucion->cod_entidad = $cod_entidad;
                            $institucion->sum_prestamos = $sum_prestamos;
                            $institucion->save();

                            $this->info('Institución cargada con exito: Nro entidad: '. $institucion->cod_entidad);                        
                        } else {
                            $this->error('No se pudo obtener la data...');
                        }
                    }
                } else {
                    $this->error('No se pudo obtener la data...');
                }
            } 
        } else {
            $this->error('No se pudo obtener el archivo...');
        }
    }
}
