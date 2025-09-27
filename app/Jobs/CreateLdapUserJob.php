<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Ldap\User as LdapUser;
use App\Ldap\Group;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\LdapUsersController;

class CreateLdapUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $userData;

    public function __construct(array $userData)
    {
        $this->userData = $userData;
    }
    private function getNextGidNumber(): int
    {
        try {
            $groups = Group::all();
            $numbers = $groups->map(function ($group) {
                $value = $group->getFirstAttribute('gidnumber');
                return (int) $value ?: 0;
            })->filter()->toArray();
            
            $start = 1000;
            $next = $numbers ? (max($numbers) + 1) : $start;
            
            // Walidacja unikalności
            while (in_array($next, $numbers)) {
                $next++;
            }
            
            Log::info("Generated next gidnumber: $next", ['existing_numbers' => $numbers]);
            return $next;
        } catch (\Exception $e) {
            Log::error("Error generating next gidnumber: " . $e->getMessage());
            return rand(1000, 9999);
        }
    }


    private function getNextNumber(string $attribute): int
    {
        // Cache numbers for better performance
        static $cachedNumbers = [];
        
        if (!isset($cachedNumbers[$attribute])) {
            $users = LdapUser::select(['uidnumber', 'gidnumber'])->get(); // Pobierz tylko potrzebne atrybuty
            $numbers = $users->map(function ($user) use ($attribute) {
                return (int) $user->getFirstAttribute($attribute) ?: 0;
            })->filter()->toArray();
            
            $cachedNumbers[$attribute] = $numbers;
        }
        
        $numbers = $cachedNumbers[$attribute];
        $start = 1000;
        $next = $numbers ? (max($numbers) + 1) : $start;
        
        // Walidacja unikalności
        while (in_array($next, $numbers)) {
            $next++;
        }
        
        // Update cache
        $cachedNumbers[$attribute][] = $next;
        
        return $next;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = LdapUser::create([
                'cn'           => $this->userData['cn'],
                'sn'           => $this->userData['sn'],
                'givenname'    => $this->userData['givenname'],
                'mail'         => $this->userData['mail'],
                'displayname'  => $this->userData['givenname'] . ' ' . $this->userData['sn'],
                'uidNumber'    => $this->getNextNumber('uidNumber'),
                'gidNumber'    => $this->getNextNumber('gidNumber'),
                'uid'          => $this->userData['uid'],
                'userpassword' => $this->userData['userpassword'],
                'homedirectory'=> '/home/uczniowie/' . $this->userData['uid'],
                
            ]);

            Log::info('Utworzono użytkownika LDAP', ['attributes' => $user->getAttributes()]);
            $user->save();

            // Przypisz użytkownika do wybranych grup
            if (!empty($this->userData['groups'])) {
                foreach ($this->userData['groups'] as $groupName) {
                    $group = Group::where('cn', '=', $groupName)->first();
                    if ($group) {
                        $group->addMember($user);
                        Log::info('Użytkownik przypisany do grupy', ['user' => $this->userData['uid'], 'group' => $groupName]);
                    } else {
                        $group = Group::create([
                            'cn' => $groupName,
                            'gidnumber' => $this->getNextGidNumber(),
                        ]);
                        $group->addMember($user);
                        $group->save();
                        Log::info('Utworzono nową grupę i przypisano do niej użytkownika', ['user' => $this->userData['uid'], 'group' => $groupName]);
                    }
                }
            } else {
                Log::info('Brak grup do przypisania', ['user' => $this->userData['uid']]);
            }
        } catch (\Exception $e) {
            Log::error("Błąd podczas tworzenia użytkownika LDAP: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'userData' => $this->userData,
            ]);
        }

    }
}
