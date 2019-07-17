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
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

namespace oat\taoSyncClient\model\syncPackage\migration;


use common_Logger;
use common_persistence_Manager;
use common_persistence_SqlPersistence;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use oat\oatbox\service\ConfigurableService;
use PDO;

class RdsMigrationService extends ConfigurableService implements MigrationInterface
{
    const TABLE_NAME = 'sync_client_migrations';
    const OPTION_PERSISTENCE = 'persistence';

    /**
     * @return array
     */
    public function getNextMigration()
    {
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ' . self::PARAM_SYNC_ID . ' = ? ORDER BY ' . self::PARAM_ID . ' LIMIT ?';
        $parameters = ['', 1];
        $stmt = $this->getPersistence()->query($sql, $parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($packageName)
    {
        $result = $this->getPersistence()->insert(self::TABLE_NAME, [
            static::PARAM_PACKAGE_NAME => $packageName,
            static::PARAM_UPDATED_AT => date('Y-m-d H:i:s'),
            static::PARAM_CREATED_AT => date('Y-m-d H:i:s'),
        ]);
        return $result === 1;
    }

    public function sync($id, $syncId)
    {
        $qb = $this->getPersistence()->getPlatForm()->getQueryBuilder();
        $qb
            ->update(static::TABLE_NAME)
            ->set(static::PARAM_SYNC_ID, $syncId)
            ->set(static::PARAM_UPDATED_AT, date('Y-m-d H:i:s'))
            ->where(static::PARAM_ID . '=:id')
            ->setParameter('id', $id);

        return $this->getPersistence()->exec($qb->getSQL(), $qb->getParameters());
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
            $table->addColumn(self::PARAM_PACKAGE_NAME, 'string', ['notnull' => true, 'length' => 255, 'default' => '']);
            $table->addColumn(self::PARAM_SYNC_ID, 'string', ['notnull' => true, 'length' => 255, 'default' => '']);
            $table->addColumn(self::PARAM_CREATED_AT, Type::DATETIME, ['notnull' => true]);
            $table->addColumn(self::PARAM_UPDATED_AT, Type::DATETIME, ['notnull' => true]);

            $table->setPrimaryKey(array(self::PARAM_ID));
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
