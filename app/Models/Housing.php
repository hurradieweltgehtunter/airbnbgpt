<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Factories\AgentFactory;

class Housing extends Model
{
    public $is_finished = false;

    protected $fillable = [
        'belongs_to',
        'name',
        'address_street',
        'address_street_number',
        'address_zip',
        'address_city',
        'address_country',
        'address_administrative_area_level_1',
        'address_sublocality',
        'address_sublocality_level_1',
        'lat',
        'lng'
    ];

    protected $guarded = ['is_finished'];

    // Ein Housing gehört zu einem Benutzer
    public function user()
    {
        return $this->belongsTo(User::class, 'belongs_to');
    }

    // Get all messages
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Get all contents
    public function contents()
    {
        return $this->hasMany(HousingContent::class);
    }

    public function agents()
    {
        return $this->morphMany(Agent::class, 'agentable');
    }

    // Get all rooms
    public function rooms()
    {
        return $this->hasMany(HousingRoom::class);
    }

    // Get all rooms
    public function images()
    {
        return $this->hasMany(HousingImage::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($housing) {
            $housing->created_at = $housing->freshTimestamp();
        });
    }

    public function getAddressCompleteAttribute()
    {

        if($this->address_street == '' || $this->address_zip == '' || $this->address_city == '' || $this->address_country == '' || $this->lat == null || $this->lng == null) {
            return false;
        }

        return true;
    }

    /**
     * Dynamic attribute to check if the housing is finished.
     * A housing is finished when:
     * - it has an agent with the name 'WriterAllinOne'
     * - the agent has finished
     *
     * TODO: Need to check how it can be done, when texts can be created again
     */
    public function getIsFinishedAttribute()
    {
        $agent = $this->agent; // Ersetze 'agent' durch die tatsächliche Beziehungsmethode

        return $agent && $agent->name == 'WriterAllinOne' && $agent->has_finished;
    }

    public static function createForUser($userId, $attributes = [])
    {
        $housing = new Housing(); // Erstellt eine neue Instanz des Housing-Models
        foreach($attributes as $key => $value) {
            $housing->$key = $value;
        }

        $housing->belongs_to = $userId;
        $housing->save();
        $housing->refresh();

        return $housing;
    }

    /**
     * Returns the address as a string
     */
    public function getAddressString()
    {
        // add each part of the address to the address string
        $address = $this->address_street . ' ' . $this->address_street_number . ', ' . $this->address_zip . ' ' . $this->address_city . ', ' . $this->address_country;

        return $address;
    }

    /**
     * Method to check, if the housing has a specific agent instance
     * @param $agentName Name of the agent to check
     *
     */
    public function hasAgent($agentName)
    {
        $hasAgent = $this->agents->contains(function($agent) use ($agentName) {
            return $agent->name === $agentName;
        });

        return $hasAgent;
    }
}

