<?php

namespace MichaelDrennen\Geonames\Console;

use Illuminate\Console\Command;
use MichaelDrennen\Geonames\Models\BaseTrait;
use MichaelDrennen\Geonames\Models\GeoSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Test extends Command {

    use GeonamesConsoleTrait;

    /**
     * @var string The name and signature of the console command.
     */
    protected $signature = 'geonames:test';

    /**
     * @var string The console command description.
     */
    protected $description = "A testing ground for new functions.";

    /**
     * The name of our alternate names table in our database. Using constants here, so I don't need
     * to worry about typos in my code. My IDE will warn me if I'm sloppy.
     */
    const TABLE = 'geonames_alternate_names';

    /**
     * The name of our temporary/working table in our database.
     */
    const TABLE_WORKING = 'geonames_alternate_names_working';


    /**
     * Initialize constructor.
     */
    public function __construct() {
        parent::__construct();


    }


    /**
     * Execute the console command.
     */
    public function handle() {
        $this->line( "Starting " . $this->signature );

        $directory = '/Users/employee/Documents/GitHub/workbench/storage/geonames/splits';

        $files = scandir( $directory );

        //array_pop($files);
        //array_pop($files);


        Schema::dropIfExists( self::TABLE_WORKING );


        DB::statement( 'CREATE TABLE ' . self::TABLE_WORKING . ' LIKE ' . self::TABLE . ';' );
        $this->disableKeys( self::TABLE_WORKING );

        $this->enableKeys( self::TABLE_WORKING );
        Schema::dropIfExists( self::TABLE );
        Schema::rename( self::TABLE_WORKING, self::TABLE );
        return;


        foreach ( $files as $fileName ) {

            if ( '.' == substr( $fileName, 0, 1 ) ) {
                continue;
            }

            $this->line( "LOAD DATA LOCAL INFILE: " . $fileName );
            $filePath = $directory . '/' . $fileName;


            $query = "LOAD DATA LOCAL INFILE '" . $filePath . "'
    INTO TABLE " . self::TABLE_WORKING . "
        (   alternateNameId, 
            geonameid,
            isolanguage, 
            alternate_name, 
            isPreferredName, 
            isShortName, 
            isColloquial, 
            isHistoric,              
            @created_at, 
            @updated_at)
    SET created_at=NOW(),updated_at=null";

            //$this->line( "Running the LOAD DATA INFILE query. This could take a good long while." );

            try {
                $rowsInserted = DB::connection()->getpdo()->exec( $query );
            } catch ( \Exception $exception ) {

                print_r( DB::connection()
                           ->getpdo()
                           ->errorInfo(), true );
            }


        }

        $this->enableKeys( self::TABLE_WORKING );
        Schema::dropIfExists( self::TABLE );
        Schema::rename( self::TABLE_WORKING, self::TABLE );


        $this->line( "Finished " . $this->signature );
    }


}
