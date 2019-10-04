<?php

namespace SkiEssentials\ContentIntegration\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        //Get the table
        $tableName = $installer->getTable('skiessentials_contentintegration');
        //Check if table exists
        if($installer->getConnection()->isTableExists($tableName) != true)
        {
            //create table
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'title',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,'default' => ''],
                    'Title'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,'default' => ''],
                    'Site Type'
                )
                ->addColumn(
                    'page_layout',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,'default' => ''],
                    'Page Layout'
                )
                ->addColumn(
                    'meta_keywords',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true,'default' => ''],
                    'Meta Keywords'
                )
                ->addColumn(
                    'meta_description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true,'default' => ''],
                    'Meta Description'
                )
                ->addColumn(
                    'identifier',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,'default' => ''],
                    'String Identifier'
                )
                ->addColumn(
                    'external_site_url',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,'default' => ''],
                    'External Site URL'
                )
                ->addColumn(
                    'creation_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => TABLE::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'update_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => TABLE::TIMESTAMP_INIT_UPDATE],
                    'Updated  At'
                )
                ->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false,'default' => '0'],
                    'Is Blog Active'
                )
                ->addColumn(
                    'sort_order',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false,'default' => 0],
                    'Sort Order'
                )
                ->addColumn(
                    'layout_update_xml',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true,'default' => ''],
                    'Layout Update Xml'
                )
                ->addColumn(
                    'custom_theme',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,'default' => ''],
                    'Custom Theme'
                )
                ->addColumn(
                    'custom_root_template',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,'default' => ''],
                    'Custom Root Template'
                )
                ->addColumn(
                    'custom_theme_from',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Custom Theme Active From Date'
                )
                ->addColumn(
                    'custom_theme_to',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Custom Theme Active To Date'
                )
                ->addColumn(
                    'meta_title',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,'default' => ''],
                    'Meta Title'
                )

                ->setComment('Integrated Content Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}