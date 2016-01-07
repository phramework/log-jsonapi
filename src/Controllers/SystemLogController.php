<?php
/**
 * Copyright 2015 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Phramework\LogJSONAPI\Controllers;

use \Phramework\Phramework;
use \Phramework\Models\Request;
use \Phramework\LogJSONAPI\Models\SystemLog;

/**
 * Controller for system-log
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class SystemLogController extends \Phramework\JSONAPI\Controller
{
    /**
     * @var string
     */
    private static $systemLogModel = SystemLog::class;

    /**
     * Get collection
     * @param  array  $params   Request parameters
     * @param  string $method   Request method
     * @param  array  $headers  Request headers
     */
    public static function GET($params, $method, $headers)
    {
        return self::handleGET(
            $params,
            static::$systemLogModel,
            [],
            [],
            true
        );
    }

    /**
     * Get a resource by `id`
     * @param  array  $params  Request parameters
     * @param  string $method  Request method
     * @param  array $headers  Request headers
     * @throws \Phramework\Exceptions\NotFoundException If resource doesn't exist or is
     * inaccessible
     */
    public static function GETById($params, $method, $headers, $id)
    {
        $id = \Phramework\Validate\UnsignedIntegerValidator::parseStatic($id);

        return self::handleGETById(
            $params,
            $id,
            static::$systemLogModel,
            [],
            []
        );
    }

    /**
     * Get SystemLogModel class path
     * @return string
     */
    public function getSystemLogModel()
    {
        return static::$systemLogModel;
    }

    /**
     * Set SystemLogModel class path
     * @param string $systemLogModel
     */
    public function setSystemLogModel($systemLogModel)
    {
        static::$systemLogModel = $systemLogModel;
    }
}
