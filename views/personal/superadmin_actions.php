    <?php
    use webvimark\modules\UserManagement\components\GhostMenu;
    use webvimark\modules\UserManagement\UserManagementModule;
    use webvimark\modules\UserManagement\models\User;

    $this->title = "Admin Options";
    echo GhostMenu::widget([
        'encodeLabels'=>false,
        'activateParents'=>true,
        'items' => [
            [
                'label' => 'Backend routes',
                'items'=>UserManagementModule::menuItems()
            ],
           
        ],
    ]);
?>