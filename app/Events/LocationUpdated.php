<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Location;

class LocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $location;

    /**
     * Create a new event instance.
     */
    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new PrivateChannel('location-tracking.' . $this->location->user_id);
    }

    /**
     * Data to send in the event.
     */
    public function broadcastWith()
    {
        return [
            'latitude' => $this->location->latitude,
            'longitude' => $this->location->longitude,
            'updated_at' => $this->location->updated_at->toDateTimeString()
        ];
    }
}

// Implementación en el frontrend

// Echo.private("location-tracking." + this.userId)
//   .listen("LocationUpdated", (event) => {
//     const { latitude, longitude } = event;
//     this.marker.setPosition(new google.maps.LatLng(latitude, longitude));
//     this.map.setCenter(new google.maps.LatLng(latitude, longitude));
//   });
