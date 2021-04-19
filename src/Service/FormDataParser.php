<?php

declare(strict_types=1);

namespace App\Service;

use LogicException;

class FormDataParser
{
    public function parse(string $string): array
    {
        $result = preg_match_all('/form-data; name="(.+?)"\r\n\r\n(.+?)\r\n/', $string, $matches, PREG_SET_ORDER);
        if ($result === false) {
            throw new LogicException('Cannot parse the string');
        }

        $dataMap = [];
        foreach ($matches as $match) {
            $dataMap[$match[1]] = $match[2];
        }

        return $dataMap;
    }
}
