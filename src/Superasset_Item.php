<?php

namespace GlpiPlugin\Myplugin;


use CommonDBTM;
use CommonGLPI;
use Glpi\Application\View\TemplateRenderer;
use Computer;
use Session;

class Superasset_Item extends CommonDBTM{

    public static $rightname = "myplugin::superasset_item";

    static function getTypeName($nb = 0){
        return _n('super-asset-item', 'super-asset-items', $nb);
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch($item->getType()){
            case Superasset::class:
                // $item is expected to be a Superasset instance
                $nb = countElementsInTable(self::getTable(), [
                    'plugin_myplugin_superassets_id' => $item->getID()
                ]);
                return self::createTabEntry(self::getTypeName($nb), $nb);

            case Computer::class:
                $has_to_show_tab = Config::getConfig()['myplugin_computer_tab'];
                if($has_to_show_tab == 1){
                    $nb = countElementsInTable(self::getTable(), [
                        "items_id" => $item->getID()
                    ]);
                    return self::createTabEntry(self::getTypeName($nb), $nb);
                }
                else{
                    break;
                }
        }
        return '';
    }


    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch($item->getType()){
            case Superasset::class:
                return self::showForSuperasset($item, $withtemplate);

            case Computer::class:
                return self::showForComputer($item, $withtemplate);
        }
    }

    static function showForSuperasset(Superasset $superasset, $withtemplate = 0){
        $id = $superasset->getID();
        $submitUrl = Superasset::getFormURL(true)."?id=$id";

        $existingItems = getAllDataFromTable(self::getTable(), [
            "plugin_myplugin_superassets_id" => $superasset->getID()
        ]);

        TemplateRenderer::getInstance()->display(
            '@myplugin/superasset_item.tab.html.twig',
            [
                'itemtype' => 'superasset',
                'submitUrl' => $submitUrl,
                'existingItems' => $existingItems,
            ]
        );
    }

    static function showForComputer(Computer $computer, $withtemplate = 0){
        $id = $computer->getID();

        $lines = getAllDataFromTable(self::getTable(), [
            "items_id" => $id
        ]);

        $superassets = [];
        foreach($lines as $num=>$line){
            $superassets[$num] = $line['plugin_myplugin_superassets_id'];
        }

        TemplateRenderer::getInstance()->display(
            '@myplugin/superasset_item.tab.html.twig',
            [
                'itemtype' => 'computer',
                'superassets' => $superassets
            ]
        );
    }

}