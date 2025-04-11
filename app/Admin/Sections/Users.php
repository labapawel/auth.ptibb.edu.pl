<?php
namespace App\Admin\Sections;

use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Section;
use SleepingOwl\Admin\Contracts\Initializable;
use App\Models\User;

class Users extends Section implements Initializable
{
    protected $title;
    protected $checkAccess = false;
    
    public function initialize() {}

    public function onDisplay()
    {
        $display = \AdminDisplay::datatablesAsync()
            ->setColumns([
                \AdminColumn::text('id', '#')->setWidth('30px'),
                \AdminColumn::link('name', 'Nazwa'),
                \AdminColumn::text('email', 'Email'),
    
                \AdminColumn::custom('Role', function ($model) {
                    $permissionMap = [
                        User::PERMISSION_VPN_CLIENT => 'VPN',
                        User::PERMISSION_TASK => 'Task',
                        User::PERMISSION_PC => 'PC',
                        User::PERMISSION_EMAIL => 'Email',
                        User::PERMISSION_ADMIN => 'Admin',
                    ];
                    
                    $userPermissions = $model->allPermissions();
                    $displayPermissions = [];
                    
                    foreach ($userPermissions as $permission) {
                        if (isset($permissionMap[$permission])) {
                            $displayPermissions[] = $permissionMap[$permission];
                        }
                    }
                    
                    return implode(', ', $displayPermissions);
                }),
            ])
            ->setDisplaySearch(true)
            ->paginate(20);

        return $display;
    }

    public function isDeletable($model)
    {
        
        return true;
    }

    public function onEdit($id)
    {
        
        $pola = [
            \AdminFormElement::text('name', 'Nazwa')->required(),
            \AdminFormElement::text('email', 'Email')->required()->addValidationRule('email'),
            \AdminFormElement::text('class', 'Klasa'),
            \AdminFormElement::checkbox('active', 'Aktywny'),

        ];

        $pola[] = \AdminFormElement::password('password', 'Hasło');
        
        $permissions = User::find($id)->allPermissions();
        
        // Make sure permissions is always an array
        if (!is_array($permissions)) {
            $permissions = $permissions ? explode(',', $permissions) : [];
        }
        
        $pola[] = \AdminFormElement::multiselect('permission', 'Rola', [
            User::PERMISSION_VPN_CLIENT => 'VPN',
            User::PERMISSION_TASK => 'Task',
            User::PERMISSION_PC => 'PC',
            User::PERMISSION_EMAIL => 'Email',
            User::PERMISSION_ADMIN => 'Admin',
        ])->setDefaultValue($permissions);
        
        


        return  \AdminForm::panel()->addBody($pola);
    }
    

    public function onCreate()
{
    $pola = [
        \AdminFormElement::text('name', 'Nazwa')->required(),
        \AdminFormElement::text('email', 'Email')->required()->addValidationRule('email'),
        \AdminFormElement::text('class', 'Klasa'),
        \AdminFormElement::checkbox('active', 'Aktywny')->setDefaultValue(true),
        \AdminFormElement::password('password', 'Hasło')->required(),
        
        \AdminFormElement::multiselect('permission', 'Rola', [
            User::PERMISSION_VPN_CLIENT => 'VPN',
            User::PERMISSION_TASK => 'Task',
            User::PERMISSION_PC => 'PC',
            User::PERMISSION_EMAIL => 'Email',
            User::PERMISSION_ADMIN => 'Admin',
        ])
    ];

    return \AdminForm::panel()->addBody($pola);
}

public function onCreateAndEdit($id = null)
{
    $model = $id ? User::find($id) : null;
    
    return \AdminSection::getModelConfigValue(User::class, $id ? 'onEdit' : 'onCreate');
}

}