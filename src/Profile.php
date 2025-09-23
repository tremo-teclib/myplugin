<?php


namespace GlpiPlugin\Myplugin;


use CommonDBTM;
use CommonGLPI;
use Profile as Glpi_Profile;
use Glpi\Application\View\TemplateRenderer;


class Profile extends CommonDBTM{
    public static $rightname = 'myplugin::profile';

    static function getTypeName($nb = 0){
        return __("My plugin profile", "myplugin");
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0){
        if($item instanceof Glpi_Profile && $item->getField('id')){
                return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {
        if (
            $item instanceof Glpi_Profile
            && $item->getField('id')
        ) {
            return self::showForProfile($item->getID());
        }
        return true;
    }

    static function getAllRights($all = false)
    {
        $rights = [
            [
                'itemtype' => Superasset::class,
                'label'    => Superasset::getTypeName(),
                'field'    => Superasset::$rightname
            ],
            [
                'itemtype' => Superasset_Item::class,
                'label'    => Superasset_Item::getTypeName(),
                'field'    => Superasset_Item::$rightname
            ]
        ];
        return $rights;
    }

    static function showForProfile($profile_id = 0)
    {
        $profile = new Glpi_Profile();
        $profile->getFromDB($profile_id);

        $matrix_options = [
            "canedit" => self::canUpdate()
        ];

        TemplateRenderer::getInstance()->display('@myplugin/profile.html.twig', [
            'canedit' => $matrix_options['canedit'],
            'profile'  => $profile,
            'rights'   => self::getAllRights(),
            'matrix_options' => $matrix_options
        ]);
    }
}