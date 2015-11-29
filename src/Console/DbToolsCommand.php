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
        $old = \File :: files(storage_path("backup"));
        rsort($old);
        foreach ($old as $num => $file)
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
            //krsort($tables);
            $fld = 'Tables_in_' . \Config::get('database.connections.mysql.database');
            $tmp_tables = [];
            foreach ($list as $tbl)
            {
                if (!empty($tbl['pattern']))
                {
                    //$idx = 0;
                    foreach ($tables as $table)
                    {
                        if (preg_match('#' . $tbl['pattern'] . '#', $table->$fld, $matches))
                        {
                            if ($tbl['numeric'])
                            {
                                $tmp_tables[intval($matches[1])] = $table->$fld;
                            }
                            else
                            {
                                $tmp_tables[] = $table->$fld;
                            }
//                            if ($idx >= $tbl['keep'])
//                            {
//                                \DB :: statement("drop table " . $table->$fld);
//                            }
//                            $idx++;
                        }
                    }
                    krsort($tmp_tables);
                    $idx = 0;
                    foreach ($tmp_tables as $tmp_table)
                    {
                        if ($idx >= $tbl['keep'])
                        {
                            \DB :: statement("drop table " . $tmp_table);
                        }
                        $idx++;
                    }
                }
            }
        }
    }


    protected function getArguments()
    {
        return array(
            ['tool', InputArgument :: REQUIRED, 'Tool to run (backup, tmp_tables)']
        );
    }


}
