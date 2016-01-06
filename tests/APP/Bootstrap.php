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
namespace Phramework\QueryLogJSONAPI\APP;

use \Phramework\Phramework;

class Bootstrap
{
    /**
     * @return array
     */
    public static function getSettings()
    {
        $settings = [
            'debug' => true,
            'query-log' => (object)[
                'database' => (object)[
                    'adapter' => 'Phramework\\Database\\MySQL',
                    'host' => '',
                    'username' => '',
                    'password' => '',
                    'name' => '',
                    'port' => 3306
                ]
            ],
            'system-log' => (object)[
                'database-log' => (object)[
                    'adapter' => 'Phramework\\Database\\MySQL',
                    'host' => '',
                    'username' => '',
                    'password' => '',
                    'name' => '',
                    'port' => 3306
                ]
            ]
        ];

        if (file_exists(__DIR__.'/localsettings.php')) {
            include __DIR__.'/localsettings.php';
        }

        return $settings;
    }

    /**
     * Prepare a phramework instance.
     *
     * @uses Bootstrap::getSettings() to fetch the settings
     * @return Phramework
     */
    public static function prepare()
    {
        $settings = self::getSettings();

        $phramework = new Phramework(
            $settings,
            new \Phramework\URIStrategy\URITemplate([
                [
                    'query_log/',
                    \Phramework\QueryLogJSONAPI\Controllers\QueryLogController::class,
                    'GET',
                    Phramework::METHOD_GET
                ],
                [
                    'query_log/{id}',
                    \Phramework\QueryLogJSONAPI\Controllers\QueryLogController::class,
                    'GETById',
                    Phramework::METHOD_GET
                ],
                [
                    'system_log/',
                    \Phramework\QueryLogJSONAPI\Controllers\SystemLogController::class,
                    'GET',
                    Phramework::METHOD_GET
                ],
                [
                    'system_log/{id}',
                    \Phramework\QueryLogJSONAPI\Controllers\SystemLogController::class,
                    'GETById',
                    Phramework::METHOD_GET
                ]
            ])
        );

        return $phramework;
    }
}
