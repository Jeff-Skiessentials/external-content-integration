<?php

namespace SkiEssentials\ContentIntegration\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        //Get the table
        $tableName = $installer->getTable('skiessentials_contentintegration');

        //Check Version
        if (version_compare($context->getVersion(), '1.0.3') < 0 ){
            $table = $installer->getConnection();
            $table->addColumn(
                $tableName,
                'additional_head_html',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'comment' =>'Additional Head HTML'
                ]
            );
            $table->addColumn(
                $tableName,
                'additional_footer_html',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Additional Footer HTML']
            );
        }
    }
}