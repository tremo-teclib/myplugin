<?php

use GlpiPlugin\Myplugin\Superasset;
use GlpiPlugin\Myplugin\Superasset_Item;
use Glpi\Dashboard\Grid;
include ('../../../inc/includes.php');

$superasset = new Superasset();

if(isset($_POST['add'])){
    $newID = $superasset->add($_POST);

    if($_SESSION['glpibackcreated'])
        \Html::redirect(Superasset::getFormURL()."?id=$newID");
    \Html::back();

} else if(isset($_POST['delete'])){
    $superasset->delete($_POST);
    $superasset->redirectToList();

} else if (isset($_POST['restore'])){
    $superasset->restore($_POST);
    $superasset->redirectToList();

} else if(isset($_POST['purge'])){
    $superasset->delete($_POST, true);
    $superasset->redirectToList();

} else if(isset($_POST['update'])){
    $superasset->update($_POST);
    \Html::back();
}
else if(isset($_POST['add_superasset_item'])){

    $superassetId = $_GET['id'];
    if(!isset($superassetId) || $superassetId == NULL){
        throw new Exception("l'id du superasset manque");
    }
    $superasset->getFromDB($superassetId);

    $values = [
        'plugin_myplugin_superassets_id' => $superassetId,
        'items_id' => $_POST['items_id'],
        'itemtype' => $_POST['itemtype']
    ];

    $superasset_item = new Superasset_Item();
    $superasset_item->add($values);
    \NotificationEvent::raiseEvent('computer_added', $superasset);
    \Html::redirect(Superasset::getFormURL()."?id=$superassetId");
}
else {
    //fill id, if missing
    isset($_GET['id'])
        ? $ID = intval($_GET['id'])
        : $ID = 0;

    Toolbox::logInfo(__("Text to translate", "myplugin"));

    //display form
    \Html::header(
        Superasset::getTypeName(),
        $_SERVER['PHP_SELF'],
        "plugins",
        Superasset::class,
        "superasset"
    );
    $superasset->display(['id'=>$ID]);
    \Html::footer();
}

