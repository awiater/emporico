<?php
/*
 *  This file is part of EMPORIKO CRM
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Helpers\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

/**
 * Int Bool Cast
 *
 * DB column: int (0/1) <--> Class property: bool
 */
final class IntBoolCast extends BaseCast
{
    /**
     * @param int $value
     */
    public static function get($value, array $params = []): bool
    {
        return $value==1 || $value=='1';
    }

    /**
     * @param bool|int|string $value
     */
    public static function set($value, array $params = []): int
    {
        return (int) $value;
    }
}