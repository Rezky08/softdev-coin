<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:schema {schema*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'migrate with select schema';

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
     * @return mixed
     */
    public function handle()
    {
        // Artisan::call('migrate:install', ['--database' => $schema]);
        foreach ($this->argument('schema') as $schema) {
            $tables = DB::connection($schema)->select('SHOW TABLES');

            $this->info('Drop all table in ' . $schema);
            Schema::connection($schema)->disableForeignKeyConstraints();
            foreach ($tables as $tablein) {
                foreach ($tablein as $table) {
                    Schema::connection($schema)->drop($table);
                }
            }
            schema::enableForeignKeyConstraints();
            // $this->info('install migrate table in' . $schema);
        }
        Artisan::call('migrate:fresh');
        $this->info(Artisan::output());
        Artisan::call('passport:install --force');
        $this->info(Artisan::output());
    }
}
