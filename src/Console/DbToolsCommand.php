<?php namespace Laraext\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DbToolsCommand extends Command {

    protected $name = 'laraext:dbtools';
    protected $description = 'Database utilities';



    public function handle()
    {
        $method = camel_case('tool_' . $this->argument('tool'));
        $this->$method();
    }

    protected function toolBackup()
    {
        if (!file_exists(storage_path("backup")))
        {
            mkdir(storage_path("backup"));
        }
        exec('mysqldump  -R --triggers --quick --single-transaction -u '.\Config::get('database.connections.mysql.username').' -P '.\Config::get('database.connections.mysql.port').' -h '.\Config::get('database.connections.mysql.host').' -p'.\Config::get('database.connections.mysql.password').' '.\Config::get('database.connections.mysql.database').' | gzip -c > '.storage_path('backup').'/'.date('YmdHi').'.dump.gz');
        $keep = intval(\Config :: get('laraext.db.backup_keep'));
        if ($keep <= 0)
        {
            return;
        }
        $old = \Files :: files(storage_path("backup"));
        rsort($files);
        foreach ($files as $num => $file)
        {
            if ($num >= \Config :: get('laraext.db.backup_keep'))
            {
                unlink($file);
            }
        }
    }

    protected function toolTmpTables()
    {
        $list = \Config :: get('laraext.db.tmp_tables');
        if (!empty($list))
        {
            $tables = \DB :: select ("show tables");
            rsort($table);
            $fld = 'Tables_in_' . \Config::get('database.connections.mysql.database');
            foreach ($list as $tbl)
            {
                if (!empty($tbl['pattern']))
                {
                    $idx = 0;
                    foreach ($tables as $table)
                    {
                        if (preg_match('#' . $tbl['pattern'] . '#', $table->$fld))
                        {
                            if ($idx >= $tbl['keep'])
                            {
                                \DB :: statement("drop table " . $table->$fld);
                            }
                            $idx++;
                        }
                    }
                }
            }
        }
    }


    protected function getArguments()
    {
        return array(
            ['tool', InputArument :: REQUIRED, 'Tool to run (backup, tmp_tables)']
        );
    }


}
