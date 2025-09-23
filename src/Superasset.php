<?php

namespace GlpiPlugin\Myplugin;


use CommonDBTM;
use Glpi\Application\View\TemplateRenderer;
use Session;
use Notepad;
use Log;
use Exception;
use Computer;
use MassiveAction;
use Html;
use PhpCsFixer\ToolInfo;
use Toolbox;

class Superasset extends CommonDBTM{

    const newRightNumber = 5000;

    static $rightname = 'myplugin::superasset';
    public $dohistory = true;

    static function getTypeName($nb = 0){
        return _n('Super-asset', 'Super-assets', $nb);
    }

    function showForm($ID, array $options = []){
        $this->initForm($ID, $options);
        // @myplugin is a shortcut to the **templates** directory of your plugin
        TemplateRenderer::getInstance()->display(
            '@myplugin/superasset.form.html.twig',
            [
                'item' => $this,
                'params' => $options
            ]
        );
        return true;
    }


    /**
     * Define menu name
     */
    static function getMenuName($nb = 0){
        return self::getTypeName($nb);
    }

    static function getMenuContent(){
        $title = self::getMenuName(Session::getPluralNumber());
        $search = self::getSearchURL(false);
        $form = self::getFormURL(false);

        $menu = [
            'title' => __("My plugin"),
            'page'  => $search,

            //define sub-options
            // we may have multiple pages under the Plugin > My type" menu
            'options' => [
                'superasset' => [
                    'title' => $title,
                    'page'  => $search,

                    //define standard icons in sub-menu
                    'links' => [
                        'search' => $search,
                        'add'    => $form
                    ]
                ]
            ]
        ];

        return $menu;
    }

    function getSpecificMassiveActions($checkitem = NULL)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);

        // add a single massive action
        $class        = __CLASS__;
        $action_key   = "superasset_new_massive_action";
        $action_label = "My new massive action";
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        return $actions;
    }

    static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case 'superasset_new_massive_action':
                Computer::dropdown();
                break;
            case 'computer_new_massive_action' :
                Superasset::dropdown();
                break;
        }
        return parent::showMassiveActionsSubForm($ma);
    }

    static function processMassiveActionsForOneItemtype(
        MassiveAction $ma, CommonDBTM $item, array $ids
    ) {
        switch ($ma->getAction()) {

            case 'superasset_new_massive_action':
                $computer_id = $ma->getInput()['computers_id'];
                foreach ($ids as $superasset_id) {
                    self::addSuperassetItem($superasset_id, $computer_id);
                }
                return;

            case 'computer_new_massive_action':
                $superasset_id = $ma->getInput()['plugin_myplugin_superassets_id'];
                foreach($ids as $computer_id)
                    self::addSuperassetItem($superasset_id, $computer_id);
                return;
        }
        return parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    private static function addSuperassetItem(int $superasset_id, int $computer_id){
        $superasset_item = new Superasset_Item();
        $superasset_item->add([
            "plugin_myplugin_superassets_id" => $superasset_id,
            "itemtype" => 'Computer',
            "items_id" => $computer_id
        ]);
    }

    //ading a possible right for this object
    function getRights($interface = 'central')
    {
        $rights = parent::getRights();
        $rights[self::newRightNumber] = __('new right');
        return $rights;
    }
    //ading a possible right for this object

    function defineTabs($options = []){
        $tabs = [];
        $this->addDefaultFormTab($tabs)
              ->addStandardTab(Notepad::class, $tabs, $options)
              ->addStandardTab(Log::class, $tabs, $options)
              ->addStandardTab(Superasset_Item::class, $tabs, $options);
        return $tabs;
    }

    function rawSearchOptions()
    {
        $options = [];

        $options[] = [
            'id' => 'common',
            'name' => __('Characteristics')
        ];

        $options[] = [
            'id' => 1,
            'table' => self::getTable(),
            'field' => 'name',
            'name' => __('Name'),
            'datatype' => 'itemlink'
        ];

        $options[] = [
            'id' => 2,
            'table' => self::getTable(),
            'field' => 'id',
            'name' => __('ID')
        ];

        $options[] = [
            'id' => 3,
            'table' => Superasset_Item::getTable(),
            'field' => 'id',
            'name'=> __('Superasset items'),
            'datatype' => 'count',
            'forcegroupby' => true,
            'usehaving' => true,
            'joinparams' => [
                'jointype' => 'child',
            ]
        ];

        return $options;
    }

    function pre_addInDB()
    {
        if(!isset($this->input['name']))
            throw  new Exception(__("Le nom du superasset doit être précisé"));
    }

    function pre_updateInDB()
    {
        if(!isset($this->input['name']))
            throw  new Exception(__("Le nom du superasset doit être précisé"));
    }

    function post_deleteItem()
    {
        $superasset_item  = new Superasset_Item();
        $superasset_item->deleteByCriteria([
            "plugin_myplugin_superassets_id" => $this->getID()
        ]);
        Session::addMessageAfterRedirect(
            "Tous les superasset_items de cet Superasset ont été supprimés");
    }

    function post_deleteFromDB()
    {
        $this->post_deleteItem();
    }


    static function cronInfo($name)
    {
        switch ($name) {
            case 'myaction':
                return ['description' => __('action desc', 'myplugin')];
        }
        return [];
    }

    static function cronMyaction($task = NULL)
    {
        Toolbox::logInfo("myaction est exécuté");
        return true;
    }

    function preItemForm($params){
        if($params->item->gettype() != Computer::class)
            return;

        \Toolbox::logDebug(print_r($params, true));
        $table = Superasset_Item::getTable();
        $id = $params->item->getID();

        if($id == -1 || !isset($id) || $id == null || $id == 0)
            return;

        $nb = countElementsInTable($table, [
            "items_id" => $id,
            "itemtype" => 'Computer'
        ]);

        $tabURL = Computer::getTabsURL(true).
                    "?id=$id&forcetab=GlpiPlugin\\Myplugin\\Superasset_item$1";

        echo "<a href=\"$tabURL\">$nb</a>";
    }

}
