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

namespace oat\taoSyncClient\model\syncQueue\storage;


use common_Logger;
use common_persistence_Manager;
use common_persistence_SqlPersistence;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use oat\oatbox\service\ConfigurableService;
use PDO;

class SyncQueueStorageRds extends ConfigurableService implements SyncQueueStorageInterface
{
    const TABLE_NAME = 'sync_client_queue';
    const OPTION_PERSISTENCE = 'persistence';

    public function __construct($options = array())
    {
        // if initialized within other service we need to rewrite config
        if (!array_key_exists(self::OPTION_PERSISTENCE, $options)
            && array_key_exists(0, $options) && count($options) === 1) {
            $options = [self::OPTION_PERSISTENCE => current($options)];
        }
        parent::__construct($options);
    }

    public function getQueued($limit = 0)
    {
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ' . self::PARAM_SYNC_ID . ' = ? ORDER BY ' . self::PARAM_ID . ' LIMIT ?';
        $parameters = ['', $limit];
        $stmt = $this->getPersistence()->query($sql, $parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($limit = 1000, $offset = 0)
    {
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' ORDER BY ' . self::PARAM_ID . ' LIMIT ? OFFSET ?';
        $parameters = [0];
        $stmt = $this->getPersistence()->query($sql, $parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(array $action)
    {
        return $this->getPersistence()->insert(self::TABLE_NAME, $action);
    }

    public function setSyncId($id)
    {
        $sql = 'UPDATE ' . self::TABLE_NAME . ' SET ' . self::PARAM_SYNC_ID . ' = ?, '.self::PARAM_UPDATED_AT.'= ? WHERE ' . self::PARAM_ID . '= ?';
        $parameters = [1, date('Y-m-d H:i:s'), $id];
        return $this->getPersistence()->exec($sql, $parameters);
    }

    /**
     * @return common_persistence_SqlPersistence
     */
    private function getPersistence()
    {
        return $this->getServiceLocator()
            ->get(common_persistence_Manager::SERVICE_ID)
            ->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
    }

    /**
     * @return bool
     */
    public function createStorage()
    {
        $persistence = $this->getPersistence();
        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        try {
            $table = $schema->createTable(self::TABLE_NAME);
            $table->addOption('charset', 'utf8');
            $table->addOption('collate', 'utf8_unicode_ci');
            $table->addOption('engine', 'InnoDB');
            $table->addColumn(self::PARAM_ID, 'integer', ['notnull' => true, 'autoincrement' => true]);
            $table->addColumn(self::PARAM_SYNCHRONIZABLE_ID, 'string', ['notnull' => true, 'length' => 255]);
            $table->addColumn(self::PARAM_SYNCHRONIZABLE_TYPE, 'string', ['notnull' => true, 'length' => 255]);
            $table->addColumn(self::PARAM_EVENT_TYPE, 'string', ['notnull' => true, 'length' => 255]);
            $table->addColumn(self::PARAM_SYNC_ID, 'string', ['notnull' => true, 'length' => 255, 'default' => '']);
            $table->addColumn(self::PARAM_CREATED_AT, Type::DATETIME, ['notnull' => true]);
            $table->addColumn(self::PARAM_UPDATED_AT, Type::DATETIME, ['notnull' => true]);

            $table->setPrimaryKey(array(self::PARAM_ID));
            $table->addIndex([self::PARAM_SYNCHRONIZABLE_ID, self::PARAM_SYNCHRONIZABLE_TYPE], 'IDX_' . self::TABLE_NAME . '_sync_id_type');
            $table->addIndex([self::PARAM_EVENT_TYPE], 'IDX_' . self::TABLE_NAME . '_event_type');
            $table->addIndex([self::PARAM_SYNC_ID], 'IDX_' . self::TABLE_NAME . '_sync_id');
            $table->addIndex([self::PARAM_CREATED_AT], 'IDX_' . self::TABLE_NAME . '_created_at');
            $table->addIndex([self::PARAM_UPDATED_AT], 'IDX_' . self::TABLE_NAME . '_updated_at');
        } catch (SchemaException $e) {
            $this->dropStorage();
            common_Logger::i('Database Schema for '.self::TABLE_NAME.' already up to date.');
            return false;
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }

        return true;
    }

    public function dropStorage()
    {
        $persistence = $this->getPersistence();

        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        try {
            $schema->dropTable(self::TABLE_NAME);
        } catch (SchemaException $e) {
            common_Logger::i('Database Schema for '.self::TABLE_NAME.' can\'t be dropped.');
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }
    }
}
