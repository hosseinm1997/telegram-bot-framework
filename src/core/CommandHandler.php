<?php

namespace Robot\Core;


class CommandHandler
{
    private  $current_command;
    private  $current_parameters = [];

    function __construct($arr_command_data)
    {
        if(count($arr_command_data) == 1)
            return false;

        $command_name = $arr_command_data[1];
        $command_parameters = array_diff($arr_command_data,[$arr_command_data[0],$arr_command_data[1]]);

        $this->current_command = $command_name;
        $this->current_parameters = $command_parameters;
    }

    public function doCommand($command_clause,\Closure $closure)
    {
        if(empty($command_clause))
            return false;

        $command_parts = explode(' ',$command_clause);
        $command_name = $command_parts[0];

        if($command_name!=$this->current_command)
            return false;

//        $command_parameters = array_diff($command_parts,$command_parts[0]);

        if(!empty($this->current_parameters))
            return $closure->call($this,$this->current_parameters);
        return $closure->call($this);
    }

}