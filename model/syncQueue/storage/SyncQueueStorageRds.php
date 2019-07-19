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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use oat\oatbox\service\ConfigurableService;

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

    public function getAggregatedQueued($types = [], $limit = 10000)
    {
        $select = [
            'max(' . self::PARAM_ID . ') as ' . self::PARAM_ID,
            self::PARAM_SYNCHRONIZABLE_ID,
            self::PARAM_EVENT_TYPE,
            self::PARAM_SYNCHRONIZABLE_TYPE
        ];
        $query = $this->getQueryBuilder()
            ->select($select)
            ->from(self::TABLE_NAME)
            ->where(self::PARAM_SYNC_MIGRATION_ID . ' = 0 ');
        if (!empty($types)) {
            $query->where(self::PARAM_SYNCHRONIZABLE_TYPE . ' IN (:sync_type)')
                ->setParameter('sync_type', $types, Connection::PARAM_STR_ARRAY);
        }

        $query->groupBy([self::PARAM_SYNCHRONIZABLE_TYPE, self::PARAM_SYNCHRONIZABLE_ID, self::PARAM_EVENT_TYPE])
            ->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }

    public function getAll($limit = 10000, $offset = 0)
    {
        $query = $this->getQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME)
            ->orderBy(self::PARAM_CREATED_AT)
            ->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }

    /**
     * Returns the QueryBuilder
     *
     * @return QueryBuilder
     */
    private function getQueryBuilder()
    {
        return $this->getPersistence()->getPlatform()->getQueryBuilder();
    }

    public function insert(array $action)
    {
        return $this->getPersistence()->insert(self::TABLE_NAME, $action);
    }

    public function setMigrationId($migrationId, $queuedTasks = [])
    {
        foreach ($queuedTasks as $queuedTask) {
            $qb = $this->getPersistence()->getPlatForm()->getQueryBuilder();
            $qb->update(static::TABLE_NAME)
                ->set(static::PARAM_SYNC_MIGRATION_ID, ':migrationId')
                ->set(static::PARAM_UPDATED_AT, ':date')
                ->where(static::PARAM_ID . ' <=:id')
                ->where(static::PARAM_SYNCHRONIZABLE_ID . ' =:syncId')
                ->setParameter('migrationId', $migrationId)
                ->setParameter('date', date('Y-m-d H:i:s'))
                ->setParameter('id', $queuedTask[self::PARAM_ID])
                ->setParameter('syncId', $queuedTask[self::PARAM_SYNCHRONIZABLE_ID]);
            $qb->execute();
        }
        return 1;
    }

    public function isSynchronized($eventType = '', $synchronizedIds = [])
    {
        $query = $this->getQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where(self::PARAM_EVENT_TYPE, '=:eventType')
            ->setParameter('eventType', $eventType)
            ->where(self::PARAM_SYNCHRONIZABLE_ID, 'IN(:ids)')
            ->setParameter('ids', $synchronizedIds, Connection::PARAM_STR_ARRAY)
            ->where(self::PARAM_SYNC_MIGRATION_ID . ' = :syncMigrationId')
            ->setParameter('syncMigrationId', 0)
            ->orderBy(self::PARAM_CREATED_AT);

        return count($query->execute()->fetchAll()) === 0;
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
            $table->addColumn(self::PARAM_SYNC_MIGRATION_ID, 'integer', ['notnull' => true, 'default' => 0]);
            $table->addColumn(self::PARAM_CREATED_AT, Type::DATETIME, ['notnull' => true]);
            $table->addColumn(self::PARAM_UPDATED_AT, Type::DATETIME, ['notnull' => true]);

            $table->setPrimaryKey(array(self::PARAM_ID));
            $table->addIndex([self::PARAM_SYNCHRONIZABLE_ID, self::PARAM_SYNCHRONIZABLE_TYPE], 'IDX_' . self::TABLE_NAME . '_sync_id_type');
            $table->addIndex([self::PARAM_EVENT_TYPE], 'IDX_' . self::TABLE_NAME . '_event_type');
            $table->addIndex([self::PARAM_SYNC_MIGRATION_ID], 'IDX_' . self::TABLE_NAME . '_sync_migration_id');
            $table->addIndex([self::PARAM_CREATED_AT], 'IDX_' . self::TABLE_NAME . '_created_at');
            $table->addIndex([self::PARAM_UPDATED_AT], 'IDX_' . self::TABLE_NAME . '_updated_at');
        } catch (SchemaException $e) {
            $this->dropStorage();
            common_Logger::i('Database Schema for ' . self::TABLE_NAME . ' already up to date.');
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
            common_Logger::i('Database Schema for ' . self::TABLE_NAME . ' can\'t be dropped.');
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }
    }
}
