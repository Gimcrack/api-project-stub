<?php

namespace App;

use App\Events\UserWasCreated;
use App\Events\UserWasDestroyed;
use App\Events\UserWasUpdated;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property bool $admin_flag
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string api_token
 */
class User extends Authenticatable
{
    use Notifiable;

    public static function boot()
    {
        parent::boot();

        static::creating( function(User $user) {
            $user->api_token = str_random(60);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    protected $casts = [
        'admin_flag' => 'bool'
    ];


    protected $dispatchesEvents = [
        'created' => UserWasCreated::class,
        'updated' => UserWasUpdated::class,
        'deleting' => UserWasDestroyed::class,
    ];

    /**
     * Is the user an admin?
     *
     * @return bool
     */
    public function isAdmin()
    {
        return (bool) $this->admin_flag;
    }

    /**
     * Promote the user to an admin
     * @method promoteToAdmin
     *
     * @return   void
     */
    public function promoteToAdmin()
    {
        $this->admin_flag = 1;
        $this->save();
    }

    /**
     * Promote the user to an admin
     * @method demoteToUser
     *
     * @return   void
     */
    public function demoteToUser()
    {
        $this->admin_flag = 0;
        $this->save();
    }
}