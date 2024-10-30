<?php
/**
 *
 */
namespace CivicLookup;

use League\Csv\Reader;
use League\Csv\Statement;
/**
 * wp autosafety podImportData
 * https://github.com/pods-framework/pods-convert
 */

class PodTable  {
    public $table;
    public $wpTable;
    var $idField = "id";
    var $titleField = "name";
    var $dateField;
    var $_controller = "/wp-admin/";
    var $gridEdit = true;
    var $allowBulkEdit = false;
    var $allowDelete = false;
    var $hideViewLink = true;
    var $useFieldCache = false;
    var $podJsonPath;
    public $id;
    public $title;
    public $podName;
    public $podDataJsonPath;
    public $importUrl;
    public $importCsvPath;
    public $yearField = 'modelyear';
    /**
     * The last api url used.
     */
    public $fetchUrl;
    public $pod;

    public function getWPTable() {
        global $wpdb,$table_prefix;
        $this->_wpTable = $table_prefix.$this->getTable();
        return $this->_wpTable;
    }

    public function getTableWp() {
        return $this->getWpTable();
    }

    public function getFields() {
        // if($this->fields) return $this->fields;
        if(!$this->podName) {
            dump('Please set podName for this datatype');
        }
        $pod = pods($this->podName);

        $fields = $pod->fields();
        return $fields;
        /*
        if($this->sourceFields) {
            $this->fields = explode(',',$this->sourceFields);
            return $this->fields;
        }
        if(!$this->podJsonPath) {
            echo "\n Please set podJsonPath\n";
        }
        $raw = file_get_contents($this->podJsonPath);
        $json = json_decode($raw);
        dump($json);
        */
    }

    public function getTable() {
        if($this->table) {
            return $this->table;
        }
        if($this->podName) {
            $this->table = 'pods_'.$this->podName;
            return $this->table;
        }
    }
    /**
     * http://localhost:8099/wp-admin/admin.php?page=autosafety-plugin&action=complaint_export
     */
    function fetchByYear($year,$field='date_submitted') {
        global $wpdb;
        $debug = false;
        if(!$field) return false;
        if(!$year) return false;

        $sql = "SELECT * FROM ".$this->getTableWP()."
        WHERE YEAR(`".$field."`) = ".(int)$year."
        ORDER BY id DESC";
        //dump($sql);
        $res =$wpdb->get_results($sql);
        return $res;
    }

    /**
     * Fetch all pods
     */
    public function fetchAll($sql = false) {
        global $wpdb;
        // Get the Pods object and run find()
        if(!$this->limit) {
            $limit = 100000;
        } else {
            $limit = (int)$this->limit;
        }

        $params = array(
            'orderby' => '',
            'limit' => $limit,
            'where' => ''
            );

        $all = pods( $this->podName, $params );
        $pods = $all->data();
        return $pods;

    }

    /**
     * wp autosafety truncate wp_pods_nhtsa_complaint;
     */
    public function wpReplaceInto($fs) {
        global $wpdb,$wp_error;
        $debug = false;
        foreach($fs AS $f=>$v) {
            if(strpos($v,'Date(')) {
                $d = new Date($v);
                $v = $d->get();
            }
            if($f=='date_submitted') {
                $v = date('Y-m-d',strtotime($v));
            }

            $names[] = $f;
            $formats[] = '%s';
            $valuesIn[] = $v;
        }
        foreach($valuesIn as $val) {
            $val = addslashes(trim($val));
            if(!$val) {
                $val = ' '; // overcome null issue
            }
            if($val=='yes') {
                $val=1;
            }
            if($val=='no') {
                $val=1;
            }
            $val = "'".$val."'";
            $values[] = $val;
        }

        if($debug) dump($values);

        // dump($table);
        $sql = "INSERT INTO {$this->getWPTable()} (".implode(',',$names).")
            VALUES (".implode(',',$values).")
            ON DUPLICATE KEY UPDATE id=id
        ";
        if($debug) {
            print_r($sql);
        }
        // var_dump($sql); // debug
        // $res = $wpdb->query($sql);
        if ( false === $wpdb->query($sql)) {
            if($wpdb->last_error) {
                echo "\n wpReplaceInto error: ";
                dump($wpdb->last_error);
            }

        }
        // dump($res);
    }

    /**
     * Doesn't work
     */
    public function wpReplaceIntoPrepared($fs) {
        global $wpdb,$wp_error;
        $debug = false;
        foreach($fs AS $f=>$v) {
            $names[] = $f;
            $formats[] = '%s';
            $values[] = $v;
        }
        if($debug) dump($values);
        $table = $this->getTable();
        dump($table);
        $sql = "REPLACE INTO {$this->getTable()} (".implode(',',$names).") VALUES (".implode(',',$formats).")";
        if($debug) dump($sql);
        $prep = $wpdb->prepare($sql,$values[0],$values[1]);
        if($debug) dump($prep);
        // var_dump($sql); // debug
        // $res = $wpdb->query($sql);
        if ( false === $wpdb->query($prep)) {
            dump($wpdb->last_error);
            if ( $wp_error ) {
                return new WP_Error( 'db_query_error',
                __( 'Could not execute query' ), $wpdb->last_error );
            } else {
                dump($wpdb->last_error);
                dump('wpReplaceInto success');
            }
        }
        // dump($res);
    }

    function showCell($field,$place) {
        $field = strtolower($field);
        $field = str_replace(' ','_',$field);
        $field = str_replace("'",'',$field);
        // $this->dump($place);
        switch($field) {
            case"Place":
            case'place':
                return $place->data->post_title;
                break;
            default:
                return $place->data->{$field};
        }
    }

    /**
     * rgba(44, 62, 80, 0.9)
     */

    function showIcon($name) {

    }

    /**
     * CSV Fetch source flat file from remote
     *
     * wget -O /Users/jonahbaker/autosafety/wp-content/plugins/autosafety/srcimport/in/FLAT_TSBS.zip https://www-odi.nhtsa.dot.gov/downloads/folders/TSBS/FLAT_TSBS.zip
     */
    public function fetchSourceFile() {

        if(!$this->importUrl) {
            die('Please define importUrl');
        }
        if(!$this->fetchTarget) {
            die('Please define fetchTarget');
        }
        $relativeFetchTarget = $this->fetchTarget;
        $this->fetchTarget = dirname(__FILE__).'/../'.$this->fetchTarget;
        $this->r('rm '.$this->fetchTarget);
        $inDir = dirname(__FILE__)."/../import/in/";
        if(file_exists($this->fetchTarget)) {
            echo "\n Fetch target exists: ".$this->fetchTarget;
        } else {
            echo "\n Fetching ".$this->importUrl." to ".$this->fetchTarget."\n";
            $target = str_replace('.txt','.zip',$this->fetchTarget);
            $this->r('wget -O '.$target.' '.$this->importUrl);
            $this->r('unzip '.$target);
            $this->r('mv *.txt '.$inDir);
            $this->r('rm '.$target);
        }
        // $this->r('rm '.$this->fetchTarget);
        echo "\nFinished fetching source\n";
    }

    public function getFetchUrl() {
        return $this->fetchUrl;
    }
    public function setFetchUrl($in) {
        $this->fetchUrl = $in;
    }
    function dump($in=false) {
        /*
        ?><pre><?php
        print_r($in);
        ?></pre><?php
        */
        unset($this->_pod);
        dump($this);

    }
    function exportTableSchema() {
        r('wp db export db/'.$this->table.'.schema.sql --tables=wp_'.$this->table);
    }


    public function wpReplaceIntoExport($fs) {
        global $wpdb;
        $debug = false;
        foreach($fs AS $f=>$v) {
            $names[] = $f;
            $formats[] = '%s';
            $values[] = $v;
        }

        $sql = "REPLACE INTO {$this->getTable()} (".implode(',',$names).") VALUES (".implode(',',$formats).")";
        if($debug) dump($sql);
        //$sql = $wpdb->prepare($sql,$values[0],$values[1]);
        // var_dump($sql); // debug
        // $wpdb->query($sql);
    }

    /**
     *
     */
    public function getSourceFields() {
        if(is_array($this->sourceFields) && $this->sourceFields) return $this->sourceFields;
        if($this->sourceFields) {
            $fields = explode(",",$this->sourceFields);
            $this->sourceFields = $fields;
            return $fields;
        }
        // $r = $this->getSampleImportRecord();
        $fields = $this->getFields();
        foreach($fields as $field=>$info) {
            if($debug) {
                dump($field);
                dump($info);
            }
        }
    }

     public function getSampleImportRecord() {
         $out = $this->urlToArray('https://one.nhtsa.gov/webapi/api/Recalls/vehicle/modelyear/2000/make/saturn/model/LS?format=json');
         return $out;
     }

     /**
      * sourceType = json only
      * https://one.nhtsa.gov/webapi/api/Recalls/vehicle/modelyear/2012/make/acura/model/rdx?format=json
      */
      public function getSourceRecords($year=false,$make=false,$model=false) {
          $debug = false;
          if($this->vehicle) {
              $year = $this->vehicle->getYear();
              $make = $this->vehicle->getMake();
              $model = $this->vehicle->getModel();
          }
          // dump($this->vehicle);
          if(!$year) {
              dump('Error: no year found for vehicle');
              return false;
          }
          $url = $this->sourceUrlBase.$year.'/make/'.$make.'/model/'.$model.'?format=json';
          if($debug) {
              dump('getSourceRecords');
              dump($url);
          }
          // dump($url);
          // Fetch from json api for one vehicle
          $json = file_get_contents($url);
          $res = json_decode($json);
          // dump($res->Results);
          $rows = $res->Results;

          return $rows;
      }

     /**
      *
      */
     public function getCount() {
         global $wpdb;
         $sql = "select count(*) as count from ".$this->getWpTable();
         // dump($sql);
         $res = $wpdb->get_results($sql);
         return $res[0]->count;
     }

     public function truncate() {
         global $wpdb;
         $count = $this->getCount();
         WP_CLI::line('Table had '.$count.' records');
         WP_CLI::line('Truncate table '.$this->getWpTable());
         $sql = "truncate ".$this->getWpTable();
         echo "\n".$sql;
         $wpdb->query($sql);
     }

     public function setPodName($in) {
         $this->podName = $in;
     }
     public function getPodName() {
         return $this->podName;
     }
     public function exportDataCLI() {

         r("wp pods save --pod=data_source --item=1 --title='Investigations'");
     }

     /**
      * wp autosafety load datasources
      */
     function importDataFromJson($file=false) {
         if(!$this->podDataJsonPath) {
             die('please specify podDataJsonPath');
         }
         $in = file_get_contents($this->podDataJsonPath);
         $im = json_decode($in);
         $data = $im->items->item;
         foreach($data as $row) {
             dump($row);
             $out = $this->wpReplaceInto($row);
             dump($out);
         }
     }
     /**
      * CSV
      */

     function fetch() {
         switch($this->sourceType) {
             case"csv":
                 $this->fetchCSV();
                 break;
             case"json":
                 $this->fetchJson();
                 break;
            default:
                dump('Please set sourceType');
         }

     }

     /**
      * Fetch the source file
      */
     function fetchCSV() {
         $this->fetchSourceFile();

         if(!$this->importCsvPath) {
             die('Please define importCsvPath');
         }
         $importPath = dirname(__FILE__)."/../".$this->importCsvPath;
         if(file_exists($importPath)) {
             echo "\n We have CSV source at ".$importPath;
         } else {
             echo "\n Could not find ".$importPath."";
             die('File not found error');
             /*
             $this->r('unzip '.$this->fetchTarget);
             $this->r('mv flat_inv.txt db/'.$this->podName."/");
             */
         }

         if(file_exists($importPath)) {
             echo $this->r('wc '.$importPath);
             if($this->limit) {
                 echo "\n Limit file via tail ".$this->limit;
                 echo $this->r('wc '.$importPath);
                 echo $this->r('tail -n '.$this->limit.' '.$importPath.' > '.$importPath.".limit");
                 echo $this->r('rm '.$importPath);
                 echo $this->r('mv '.$importPath.'.limit'.' '.$importPath);
                 echo $this->r('wc '.$importPath);
             }
             $this->loadCSV();
         }
     }

     function getYearField() {
         return $this->yearField;
     }

     public function getFetchTarget() {
         if($this->fetchTarget) return $this->fetchTarget;
         if($this->importCsvPath) return $this->importCsvPath;
     }

     public function getImportCSVPath() {
         if($this->importCsvPath) return $this->importCsvPath;
     }

     /**
      * wp autosafety import complaints
      * wp autosafety fetch investigations
      * wp autosafety fetch mancomms 10000
      */
     public function loadCSV() {
         $debug = false;
         $limit = $this->limit;
         $path = $this->getImportCSVPath();
         if(!$path) {
             $path = "/import/in/".$this->getPodName().".csv";
         }
         $importPath = dirname(__FILE__)."/../".$path;
         if(!$importPath) {
             die('load CSV needs path');
         }
         echo "\n Loading csv to sql from ".$importPath."\n\n";
         $fields = $this->getSourceFields();
         //echo $this->r('head -n 3 '.$this->getImportCSVPath());
         echo "\n 3 top records: ";
         echo $this->r('head -n 3 '.$importPath);
         echo "\n 3 bottom records: ";
         echo $this->r('tail -n 3 '.$importPath);
         // dump($fields);
         echo "\n Table started with ".$this->getCount()." records";
         echo "\n Read from source started at ".date('Y-m-d H:i:s');
         $reader = Reader::createFromPath($importPath, 'r');
         $reader->setHeaderOffset(0); //set the CSV header offset
         if($this->sourceDelimiter) {
             $reader->setDelimiter($this->sourceDelimiter);
         } else {
             $reader->setDelimiter("\t");
         }

         if(!$fields) {
             die('Please define sourceFields');
         }

         $records = Statement::create()->process($reader, $fields);
         $header = $records->getHeader(); // returns ['Prénom', 'Nom', 'E-mail'];
         $recordCount = count($records);
         echo "\n Importing ".$recordCount." records from csv starting at ".date('Y-m-d H:i:s')."\n";

         /*
         // file_put_contents('out.json',json_encode($csv));
         //get 25 records starting from the 11th row
         $stmt = Statement::create()
             ->offset(0)
             // ->limit(25)
             // ->where('ManufacurerComms::filterManComs')
         ;

         $records = $stmt->process($csv);
         */
         //$records = $csv->getRecords();
         $out = '';
         $count = 0;
         foreach ($records as $record) {
             // dump($record); die();
             $count++;
             $res = $this->wpReplaceInto($record);
             if($count==10000) {
                 echo "\nFinished 10,000 records at ".date('Y-m-d H:i:s');

             }
             if($count==100000) {
                 echo "\nFinished 100,000 records at ".date('Y-m-d H:i:s');
             }
             if($count==500000) {
                 echo "\nFinished 500,000 records at ".date('Y-m-d H:i:s');
             }
             if($count==1000000) {
                 echo "\nFinished 1,000,000 records at ".date('Y-m-d H:i:s');

             }
             // $out.= "\n$sql;";
         }
         /*
         $outPath = "db/".$this->getTableWP().".data.sql";
         echo "\n Writing to ".$outPath;
         file_put_contents($outPath,$out);
         */
         echo "\n Table now has ".$this->getCount()." records";
         echo "\n Finished at ".date('Y-m-d H:i:s')."\n";

     }

    public function addInsert($r) {
         foreach($r as $v) {
             $values[] = "'".addslashes($v)."'";
         }
         $sql = "REPLACE INTO {$this->getWpTable()} (".implode(',',$this->fields).") VALUES (".implode(',',$values).")";
         // print_r("\n\n".$sql);
         return $sql;
    }

    public function showNow() {
         echo date('H:i:s');
    }

    /**
    * http://localhost:8099/?vehicle_safety_check=2009-ford-crown-victoria
    * Should be 11:
    * http://localhost:8099/?vehicle_safety_check=1990-acura-integra
    */
    public function fetchByYearMakeModel($year,$make,$model) {
         $debug = false;
         $url = $this->sampleRecordUrl;
         if(!$url) {
             dump('Please define sampleRecordUrl for this data type');
             return false;
         }
         $url = str_replace('2012',$year,$url);
         $url = str_replace('acura',$make,$url);
         $url = str_replace('rdx',$model,$url);

         if($debug) {
             dump($year);
             dump($make);
             dump($model);
             dump($url);
         }
         $e = new Endpoint($url);
         $out = $e->get();
         $this->fetchUrl = $e->getUrl();
         return $out;
    }

    public function createUniqueIndex() {
        $sql = 'ALTER TABLE `wp_pods_nhtsa_complaint`
        ADD UNIQUE `unique_record` (';

             $fields = $this->getFields();
             foreach($fields as $field) {
             $parts[] = '`'.$field.'`';
             }
             $out = implode(',',$parts);
             $out = $sql.$out;
             $out .= ')';
        print_r($out);
    }

    /**
    *
    */
    public function showCreateTable() {
        global $wpdb;
        $sql = 'show create table '.$this->getTableWp();
        $res = $wpdb->get_results($sql);
        return $res[0];
    }

    /**
     * Run
     */
    function r($in) {
      	echo("\n ".$in."\n");
      	ob_start();
      	passthru($in);
      	$out = ob_get_clean();
      	return $out;
    }

    /**
     * Print
     */
    function p($in) {
        echo("\n ".$in."\n");
    }

    function getTitle() {
        return $this->title;
    }

    function getDateField() {
        return $this->dateField;
    }
    /**
     * wp autosafety index nhtsacomplaints
     * select * from wp_pods_nhtsa_complaint order by datecomplaintfiled DESC limit 100 \G
     */
    public function index() {
        global $wpdb;
        $sqls[] = 'ALTER TABLE '.$this->getTableWp().' DROP INDEX unique_record';

        $sqls[] = 'ALTER TABLE '.$this->getTableWp().'
                ADD UNIQUE `unique_record` (
                    `email_address`
                )
        ';
        /*
        $sqls[] = 'ALTER TABLE '.$this->getTableWp().'
            ADD INDEX `make_model_year` (`make`, `model`, `modelyear`)';
        */

        foreach($sqls as $sql) {
            dump($sql);
            $wpdb->query($sql);
        }

        // DELETE FROM wp_pods_nhtsa_complaint WHERE datecomplaintfiled < '1990-01-01';
        dump($this->showCreateTable());
    }
}
