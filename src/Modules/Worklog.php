<?php

namespace Fyi\Infinitum\Modules;

use Fyi\Infinitum\Infinitum;

class Worklog extends Infinitum
{
    protected $rest;

    public function __construct($rest)
    {
        $this->rest = $rest;
    }

    public function getWorklogFromUser($id)
    {
        return $this->rest->get('users/worklog/user/' . $id);
    }

    public function getWorklogFromUsers($params = null)
    {
        $toAppend = "";
        if(isset($params) ){
            $toAppend = "?";
            if(isset($params["ids"])){
                $toAppend .= "ids=" . $params["ids"] . "&";
            }
    
            if(isset($params["start_date"])){
                $toAppend .= "start_date=" . $params["start_date"] . "&";
            }
    
            if(isset($params["end_date"])){
                $toAppend .= "end_date=" . $params["end_date"] . "&";
            }

            if(isset($params["limit"])){
                $toAppend .= "limit=" . $params["limit"] . "&";
            }

            if(isset($params["offset"])){
                $toAppend .= "offset=" . $params["offset"] . "&";
            }

            $toAppend = substr($toAppend, 0, -1);
        }

        return $this->rest->get('users/worklog/user'.$toAppend);
    }

}
