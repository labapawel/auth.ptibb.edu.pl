<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

<?php

class LdapUser
{
    public $uid;
    public $cn;
    public $mail;
    public $groups; // Dodano pole dla grup, do których należy użytkownik

    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
        // Jeśli chcesz, możesz od razu pobierać nazwy grup z atrybutów LDAP
        // i przypisywać je do tablicy wewnątrz $this->groups.
        // To zależy od tego, jak dokładnie Adldap zwraca informacje o grupach.
        // Poniżej przykład (może wymagać dostosowania):

        if (isset($attributes['memberof'])) {
             $this->groups = [];
             foreach ($attributes['memberof'] as $groupDn) {
                  // Przykładowe wydobycie nazwy grupy z DN.  Może wymagać dostosowania.
                  if (preg_match('/CN=([^,]+),/', $groupDn, $matches)) {
                      $this->groups[] = $matches[1];
                  }
             }
        } else {
            $this->groups = []; // Inicjalizacja na pustą tablicę, jeśli brak informacji o grupach
        }
    }

    // Możesz dodać metody pomocnicze do formatowania danych, jeśli potrzebujesz
}