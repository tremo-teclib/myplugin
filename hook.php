<?php

/**
 * -------------------------------------------------------------------------
 * myplugin plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2025 by the myplugin plugin team.
 * @license   MIT https://opensource.org/licenses/mit-license.php
 * @link      https://github.com/pluginsGLPI/myplugin
 * -------------------------------------------------------------------------
 */

use GlpiPlugin\Myplugin\Superasset;
use GlpiPlugin\Myplugin\Superasset_Item;
use GlpiPlugin\Myplugin\Profile as Myplugin_profile;

/**
 * Plugin install process
 */
function plugin_myplugin_install(): bool
{
    // Creating the table of the Superasset itemtype
    global $DB;

    $default_charset = \DBConnection::getDefaultCharset();
    $default_collation = \DBConnection::getDefaultCollation();

    // instantiate migration with version
    $migration = new \Migration(PLUGIN_MYPLUGIN_VERSION);

    //create table if not exists
    $table = Superasset::getTable();
    if(!$DB->tableExists($table)){
        //table creation query
        $query = "CREATE TABLE `$table`(
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `is_deleted` TINYINT NOT NULL DEFAULT '0',
                    `name` VARCHAR(255) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB
                  DEFAULT CHARSET=$default_charset
                  COLLATE=$default_collation";
        $DB->doQuery($query);
    }
    // Creating the table of the Superasset itemtype

    //creating tha table of the Superasset_Item itemtype
    $table = Superasset_Item::getTable();
    if(!$DB->tableExists($table)){
        $query = "CREATE TABLE `$table` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `plugin_myplugin_superassets_id` int unsigned NOT NULL,
                    `itemtype` VARCHAR(255) NOT NULL,
                    `items_id` int unsigned NOT NULL,
                    PRIMARY KEY (`id`)
        ) ENGINE=InnoDB
            DEFAULT CHARSET=$default_charset
            COLLATE=$default_collation";
        $DB->doQuery($query);
    }
    // Creating the table of the Superasset itemtype

    //adding display preferences
    $table = DisplayPreference::getTable();
    $query = "insert into `$table` (itemtype, num, rank, users_id) values
                ( 'Superasset', 1, 1, 0 ),
                ( 'Superasset', 2, 2, 0 ),
                ( 'Superasset', 3, 3, 0)";
    $DB->doQuery($query);
    //adding display preferences

    //adding the plugin configuration fields and values
    \Config::setConfigurationValues('plugin:myplugin', [
        'myplugin_computer_tab' => 1,
        'myplugin_computer_form' => 1
    ]);
    //adding the plugin configuration fields and values

    //adding myplugin rights
    $rights = Myplugin_profile::getAllRights();
    $rightsNames = [Myplugin_profile::$rightname];
    foreach($rights as $right){
        $rightsNames[] = $right['field'];
    }
    \ProfileRight::addProfileRights($rightsNames);
    //adding myplugin rights

    //giving the right to modify the myplugin
    // rights to profiles who can view the profile menu (AKA super-admin)
    $migration->giveRight(Myplugin_profile::$rightname, 2, [
        'profile' => 23
    ]);


    //registering the automatic action
    CronTask::register(
        Superasset::class,
        'myaction',
        300,
        [
            'comment'   => 'comment of the automatic action',
            'mode'      => \CronTask::MODE_EXTERNAL
        ]
    );
    //registering the automatic action

    $migration->executeMigration();

    return true;
}

/**
 * Plugin uninstall process
 */
function plugin_myplugin_uninstall(): bool
{
    global $DB;

    $tables = [
        Superasset::getTable(),
        Superasset_Item::getTable()
    ];

    foreach($tables as $table){
        if($DB->tableExists($table)){
            $DB->doQuery(
                "DROP TABLE `$table`"
            );
        }
    }

    //deleting display preferences
    $table = DisplayPreference::getTable();
    $query = "Delete from `$table` where itemtype = 'Superasset'";
    $DB->doQuery($query);
    //deleting display preferences

    //removing the plugin config fields and values
    $config = new \Config();
    $config->deleteByCriteria(['context' => 'plugin:myplugin']);
    //removing the plugin config fields and values

    //removing myplugin rights
    $rights = Myplugin_profile::getAllRights();
    $rightsNames = [Myplugin_profile::$rightname];
    foreach($rights as $right){
        $rightsNames[] = $right['field'];
    }
    \ProfileRight::deleteProfileRights($rightsNames);
    //removing myplugin rights

    return true;
}

function plugin_myplugin_MassiveActions($type)
{
   $actions = [];
   switch ($type) {
      case Computer::class:
         $class = Superasset::class;
         $key   = 'computer_new_massive_action';
         $label = __("computer new massive action", 'example');
         $actions[$class. MassiveAction::CLASS_ACTION_SEPARATOR. $key] = $label;
         break;
    }
   return $actions;
}

function myplugin_computer_delete(Computer $item){
    $superasset_item = new Superasset_Item();
    $superasset_item->deleteByCriteria([
        "items_id" => $item->getID(),
        "itemtype" => 'Computer'
    ]);

    Session::addMessageAfterRedirect(__(
    "Toutes les liaisons de cet ordinateur avec les superassets ont été supprimés"));
}