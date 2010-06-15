<?php

class Key
{
    function AssignRandValue()
    {
        $pool = '1234567890abcdefghijklmnopqrstuvwxyz';
        $num_chars = strlen($pool);
        mt_srand((double)microtime() * 1000000);
        $index = mt_rand(0, $num_chars - 1);
        return $pool[$index];
    }

    function GenerateKey($length = 8)
    {
        if($length > 0)
        {
            $rand_id="";
            for($i = 1; $i <= $length; $i++)
            {
                $rand_id .= $this->AssignRandValue();
            }
        }
        return $rand_id;
    }
}