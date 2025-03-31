<?php

namespace App\Admin\Sections;

use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Section;
use SleepingOwl\Admin\Contracts\Initializable;
// use App\Admin\Form\Element\WeeklyCalendarElement;

class Users extends Section implements Initializable
{
    /**
     * @var string
     */
    protected $title ;
    protected $checkAccess = false;

    public function initialize() {
        $this->title =  __('lang.title.users');
      }
  
    /**
     * Display table for users.
     *
     * @return DisplayInterface
     */
    public function onDisplay()
    {
        $display = \AdminDisplay::datatablesAsync()
            ->setColumns([
                // \AdminColumn::custom('Rola', function ($model) {
                //     return  "<img src=\"".$model->avatar."?a".date('YmdH')."\" 
                //     alt=\"Zdjęcie profilowe\" class=\"img-thumbnail\" style=\"max-height: 100px;\">";
                // }),
                \AdminColumn::text('id', '#')->setWidth('30px'),

                \AdminColumn::link('name', 'Nazwa'),
                \AdminColumn::text('email', 'Email'),
            ])
            ->setDisplaySearch(true)
            ->paginate(20);
            // if (auth()->user()->isAdmin()) {
            //     $display->setApply(function ($query) {
            //         $query->where('id', auth()->id());
            //     });
            // }
     
        return $display;    
    }

    public function isDeletable($model)
        {
            // Tylko administratorzy mogą usuwać rekordy
            // return auth()->user()->isAdmin;
            return true;
        }

    /**
     * Form for creating/editing users.
     *
     * @return FormInterface
     */
    public function onEdit($id)
    {
        
        $pola = [
            \AdminFormElement::text('name', 'Nazwa')->required(),
            \AdminFormElement::text('email', 'Email')->required()->addValidationRule('email')//->setReadonly(auth()->user()->role != 1)
            ,

        ];

        $pola[] = \AdminFormElement::password('password', 'Hasło');

        $pola[]=             \AdminFormElement::select('permission', 'Rola', [
            '1' => 'User',
            '2' => 'Admin',
            '4' => 'SuperAdmin',
        
        ]);


            // ->setStartHour(7)
            // ->setEndHour(15)
            // ->setReadonly(auth()->user()->role != 1)
            // ->setValidationRules('required');
        return  \AdminForm::panel()->addBody($pola);
    }

    public function onCreate()
    {
        return $this->onEdit(null);
    }
}
