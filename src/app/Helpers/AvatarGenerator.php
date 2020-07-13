<?php

namespace App\Helpers;

class AvatarGenerator
{
    /*
     * TODO
     */
    public function generate(string $name, string $subname): string
    {
        $name    = $this->formatting($name);
        $subname = $this->formatting($subname);

        return $name . $subname;
    }

    private function formatting(string $str): string
    {
        return mb_substr($str, 0, 1);
    }
}
