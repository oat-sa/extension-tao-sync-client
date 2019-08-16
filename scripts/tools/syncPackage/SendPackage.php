<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@1pt.com>
 */

namespace oat\taoSyncClient\scripts\tools\syncPackage;


use oat\oatbox\extension\script\ScriptAction;

class SendPackage extends ScriptAction
{
    protected function provideOptions()
    {
        return [
            'wet-run' => [
                'prefix' => 'w',
                'flag' => true,
                'longPrefix' => 'wet-run',
                'description' => 'Send all prepared packages to the server.',
            ],
            'verbose' => [
                'prefix' => 'v',
                'flag' => true,
                'longPrefix' => 'Verbose',
                'description' => 'Force script to be more details',
            ],
        ];
    }

    protected function provideDescription()
    {
        // TODO: Implement provideDescription() method.
    }

    protected function run()
    {
        // TODO: Implement run() method.

        // @todo we need to receive which data were synced on response
        // and update sync table according to this
    }
}
