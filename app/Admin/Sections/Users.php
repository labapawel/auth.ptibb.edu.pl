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
                    $permissions = [];
                    
                    if ($model->isVPNclient()) $permissions[] = 'VPN';
                    if ($model->isTaskPermission()) $permissions[] = 'Task';
                    if ($model->isPC()) $permissions[] = 'PC';
                    if ($model->isEmailPermission()) $permissions[] = 'Email';
                    if ($model->isAdmin()) $permissions[] = 'Admin';

                    return implode(', ', $permissions);
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
            \AdminFormElement::text('email', 'Email')->required()->addValidationRule('email')//->setReadonly(auth()->user()->role != 1)
            ,

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
    

    public function onCreate(array $data)
    {
        return $this->onEdit(null);
    }
}