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
            \AdminFormElement::text('email', 'Email')->required()->addValidationRule('email'),
            \AdminFormElement::password('password', 'Hasło'),
        ];

        // Define permissions array
        $permissionsMap = [
            1 => ['value' => 'VPN', 'method' => 'isVPNclient'],
            2 => ['value' => 'Task', 'method' => 'isTaskPermission'],
            4 => ['value' => 'PC', 'method' => 'isPC'],
            8 => ['value' => 'Email', 'method' => 'isEmailPermission'],
            32 => ['value' => 'Admin', 'method' => 'isAdmin'],
        ];

        return \AdminForm::panel()->addBody($pola);

}
    public function onUpdate2($id, array $data)
    {
        dd($user->permission, $data['permission']);
        $permissionsMap = [1, 2, 4, 8, 32];
        $permissionsSum = 0;

        // Calculate total permission value based on checkbox states
        foreach ($permissionsMap as $value) {
            if (isset($data["permission_$value"]) && $data["permission_$value"]) {
                // If checkbox is checked, add this permission value to the sum
                $permissionsSum += $value;
            }
            unset($data["permission_$value"]);
        }

        // Pobierz model użytkownika
        $user = User::findOrFail($id);

        // Ustaw wartość pola 'permission'
        $user->permission = $permissionsSum;
        //sprawdz na ekranie wartosc usera i data
        // Zapisz zmiany w modelu
        $user->save();

        return $user; // Zwróć zaktualizowany model
    }
    

    public function onCreate(array $data)
    {
        $permissionsSum = 0;
        $permissionsMap = [1, 2, 4, 8, 32];
        $user = new User();
        
        foreach ($permissionsMap as $value) {
            if (isset($data["permission_$value"]) && $data["permission_$value"]) {
                $user->addPermission($value);
            }
            unset($data["permission_$value"]);
        }
        
        $data['permission'] = $user->permission;

        return parent::onCreate($data);
    }
}