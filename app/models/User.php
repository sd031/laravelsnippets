<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The fields who are mass assignable
	 *
	 * @var string
	 */
	protected $fillable = array(
		'username',
		'password',
		'first_name',
		'last_name',
		'email'
	);

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password');

	/**
	 * Config for eloquent sluggable package
	 * Reference: https://github.com/cviebrock/eloquent-sluggable
	 *
	 * @var array
	 */
    public static $sluggable = array(
        'build_from' => 'full_name',
        'save_to'    => 'slug',
    );

    /**
     * Define a one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function snippets()
	{
		return $this->hasMany('Snippet', 'author_id');
	}

    public function role()
    {
        return $this->belongsTo('Role');
    }

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
	}

    /**
     * Full name eloquent accessor
     *
     * @return string
     */
	public function getFullNameAttribute()
    {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function getAbsPhotoUrlAttribute()
    {
        if ( ! $this->photo_url) {
            return asset('/assets/images/default-user-avatar.jpeg');
        }

        $assetsDir = asset('/');
        return $assetsDir . $this->photo_url;
    }

    public function getSnippetsCountAttribute()
    {
        return $this->snippets()->where('approved', 1)->count();
    }

    /**
     * Password eloquent mutator
     *
     * @return string
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Checks if user is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active ? true : false;
    }

    /**
     * Activates a user
     *
     * @return boolean
     */
    public function activate($key)
    {
        if ($this->activation_key === $key)
        {

            $this->active = 1;

            if ($this->save()) {
                return true;
            }

            throw new \RuntimeException('Saving to database failed.');
        }

        return false;
    }

    /**
     * Checks if user is admin
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->role->name === 'admin';
    }

}